<?php

namespace Controllers\Track\Handler\Payer\Inprogress;

use Controllers\Track\Handler\AbstractTrackHandlerController;
use Exception;
use Helpers\PayerLevel\Dto\UpdatePayerLevelResultDto;
use Helpers\PayerLevel\GetPayerLevelInfoService;
use Model\User;
use OrderManager;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Покупатель принял заказ
 *
 * Class DoneHandlerController
 * @package Controllers\Track\Handler\Payer\Inprogress
 */
class DoneHandlerController extends AbstractTrackHandlerController {
	/**
	 * Результаты обновления уровня покупателя и статуса суперпокупатель
	 *
	 * @var UpdatePayerLevelResultDto
	 */
	private $updatePayerResult;

	/**
	 * Нужно ли блокировать базу данных на время исполнения контроллера
	 *
	 * @return bool
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * Обработка логики контроллера
	 *
	 * @return Response HTTP ответ
	 * @throws Exception
	 * @throws Throwable
	 */
	protected function processAction(): Response {
		$this->updatePayerResult = OrderManager::payer_inprogress_done(
			$this->getOrderId(),
			$this->getAllowPortfolioPopup(),
			$this->getMessage()
		);

		if (!$this->updatePayerResult) {
			return $this->failure();
		}

		$this->getOrder()->refresh();

		return $this->getResponse();
	}

	/**
	 * Сформировать ответ
	 *
	 * @return Response HTTP ответ
	 * @throws Exception
	 */
	protected function getResponse(): Response {
		$payerLevelInfo = new GetPayerLevelInfoService($this->updatePayerResult, $this->getOrder());

		return new JsonResponse([
			"success" => true,
			"data" => [
				"workTime" => $payerLevelInfo->getWorkTimeForHumans(),
				"countOrders" => $payerLevelInfo->getPaidOrderCountForHumans($payerLevelInfo->getPaidOrderCount()),
				"level" => $payerLevelInfo->getPayerLevelLabel(),
				"nextLevelText" => $payerLevelInfo->getNextLevelText(),
				"badge" => $payerLevelInfo->getPayerBadgeImagePath(),
			],
		]);
	}

	/**
	 * Получить текущего пользователя
	 *
	 * @return User
	 */
	protected function getUserModel(): User {
		$user = parent::getUserModel();

		if (!$user) {
			throw new RuntimeException("Current user can not be null. Check controller authorization.");
		}

		return $user;
	}

	/**
	 * Разрешено ли добавлять в портфолио
	 *
	 * @return bool
	 */
	private function getAllowPortfolioPopup(): bool {
		return (bool)$this->getRequest()->request->get("allow_portfolio_item_popup");
	}
}
