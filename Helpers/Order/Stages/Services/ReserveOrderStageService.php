<?php

namespace Helpers\Order\Stages\Services;

use Core\Exception\BalanceDeficitException;
use Core\Traits\AuthTrait;
use Model\OrderStages\OrderStage;
use OperationManager;
use Order\Stages\Actions\Dto\ReserveOrderStageActionDto;
use Order\Stages\Actions\PayerCancelInprogressAction;
use Order\Stages\Actions\PayerDoneInprogressAction;
use Order\Stages\Actions\PayerPaidStageAction;
use Order\Stages\Actions\PayerUnpaidInprogressAction;

/**
 * Резервирование средств на оплату этапа
 *
 * Class ReserveOrderStageService
 * @package Helpers\Order\Stages\Services
 */
final class ReserveOrderStageService {
	use AuthTrait;

	/**
	 * Зарезервировать средства на этап
	 * Выполняется в транзакции
	 *
	 * @param OrderStage $stage
	 * @param int $userId
	 * @param float $userTotalFunds
	 *
	 * @return ReserveOrderStageActionDto
	 * @throws \Throwable
	 */
	public function reserve(OrderStage $stage, int $userId, float $userTotalFunds): ReserveOrderStageActionDto {
		$order = $stage->order;
		$order->refresh();

		// Курс валюты и зависящие от него поля устанавливаются в момент оплаты
		$stage->setCurrencyFields($order);

		if ($userTotalFunds < $stage->payer_amount) {
			throw new BalanceDeficitException($userId, $stage->payer_amount - $userTotalFunds);
		}

		$payerCurrencyId = \Translations::getCurrencyIdByLang($order->payer->lang);
		if (!OperationManager::orderOutOperation($stage->payer_amount, $stage->order_id, 0, $payerCurrencyId, $stage->currency_rate, $order->currency_id, $stage->id)) {
			throw new \RuntimeException("Ошибка оплаты задачи");
		}

		$turnover = \OrderManager::getTurnover($order->worker_id, $order->USERID, $order->getLang());
		$turnover += $order->getReservedStages()->sum(OrderStage::FIELD_PAYER_PRICE);

		// Переустановка вознагражения продавца по текущему обороту в оплачиваемом этапе
		$turnover = $stage->setWorkerPriceByTurnover($turnover, $order);
		$stage->status = OrderStage::STATUS_RESERVED;
		if ($order->isInWork()) {
			$stage->inprogress_date = \Helper::now();
		}
		$stage->reserved_date = \Helper::now();
		$stage->save();

		// Переустановка вознаграждения продавца в остальных неоплаченных этапах
		$order->load("stages");
		foreach ($order->getNotReservedStages() as $notReservedStage) {
			$turnover = $notReservedStage->setWorkerPriceByTurnover($turnover, $order);
			$notReservedStage->save();
		}

		if ($order->isNotDone() && $order->isNotCancel()) {
			$order->setAmountsByAllStages();
			if ($order->isCheck() && $order->getReservedNotCheckStages()->count()) {
				$order->show_as_inprogress_for_worker = true;
			}
			$order->setStagesPrice();
			$order->save();
		}

		if ($order->isUnpaid()) {
			$action = new PayerUnpaidInprogressAction($order);
			$actionDto = $action->run($this->getCurrentUserId(), $stage->id);
		} elseif ($order->isCancel()) {
			$action = new PayerCancelInprogressAction($order);
			$actionDto = $action->run($this->getCurrentUserId(), $stage->id);
		} elseif ($order->isDone()) {
			$action = new PayerDoneInprogressAction($order);
			$actionDto = $action->run($this->getCurrentUserId(), $stage->id);
		} else {
			$action = new PayerPaidStageAction($order);
			$actionDto = $action->run($stage);
		}

		return $actionDto;
	}
}