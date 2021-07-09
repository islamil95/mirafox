<?php

namespace Track\Factory;

use Core\Traits\AuthTrait;
use Core\Traits\SingletonTrait;
use Model\Order;
use Model\Track;
use Track\Type;
use Track\View\Arbitrage\ArbitrageView;
use Track\View\EmptyView;
use Track\View\IView;
use Track\View\Order\AdminToInprogressView;
use Track\View\Order\InProgressCheckView;
use Track\View\Order\PayerDoneView;
use Track\View\Order\WorkerDoneView;
use Track\View\Payer\AdviceView;
use Track\View\Payer\NewInProgressView;
use Track\View\Portfolio\PortfolioView;
use Track\View\Report\PayerReportView;
use Track\View\Report\WorkerReportView;
use Track\View\Stages\CronCheckApproveStagesView;
use Track\View\Stages\PayerApproveStagesView;
use Track\View\Stages\PayerRejectStagesView;
use Track\View\Stages\PayerStagePaidView;
use Track\View\Stages\PayerStageUnpaidView;
use Track\View\Stages\StageAfterTextView;
use Track\View\Stages\StageBeforeTextView;
use Track\View\Stages\WorkerStageInprogressView;
use Track\View\Stages\WorkerStageUnpaidView;
use Track\View\SystemView;
use Track\View\Text\FirstTextView;
use Track\View\Text\TextView;
use Track\View\Tips\TipsView;

/**
 * Фабрика для получения представления трека
 *
 * Class TrackViewFactory
 * @package Track\Factory
 */
final class TrackViewFactory implements ITrackViewFactory {

	use AuthTrait, SingletonTrait;

	/**
	 * @inheritdoc
	 */
	public function getView(Track $track, Order $order = null): IView {
		if ($order != null) {
			$track->order = $order;
		}
		if ($this->isOnlyForPayer($track)) {
			return new EmptyView($track);
		}

		if ($this->isTextType($track->type)) {
			if (Type::TEXT_FIRST == $track->type) {
				return new FirstTextView($track);
			}
			return new TextView($track);
		}

		// Все отмены - туда, пусть он сам там разбирается.
		if ($this->isCancel($track->type)) {
			return CancelTrackViewFactory::getInstance()->getView($track);
		}

		// А все арбитражи - туда.
		if ($this->isArbitrage($track->type)) {
			return new ArbitrageView($track);
		}

		// Опции тоже, в свой класс.
		if ($this->isExtra($track->type)) {
			return ExtrasTrackViewFactory::getInstance()->getView($track);
		}

		switch ($track->type) {
			case Type::PAYER_ADVICE:
				return new AdviceView($track);

			case Type::PAYER_NEW_INPROGRESS:
				return new NewInProgressView($track);

			case Type::PAYER_CHECK_DONE:
			case Type::PAYER_INPROGRESS_DONE:
				if ($track->order->isPayer($this->getUserId())) {
					return new PayerDoneView($track);
				}
				return new WorkerDoneView($track);

			case Type::WORKER_REPORT_NEW:
				if ($track->order->isPayer($this->getUserId())) {
					return new PayerReportView($track);
				}
				return new WorkerReportView($track);

			case Type::WORKER_INPROGRESS_CHECK:
				return new InProgressCheckView($track);

			case Type::CREATE:
				return new EmptyView($track);

			case Type::PAYER_SEND_TIPS:
				return new TipsView($track);

			case Type::WORKER_PORTFOLIO:
				return new PortfolioView($track);

			case Type::PAYER_REJECT_STAGES:
			case Type::PAYER_REJECT_STAGES_INPROGRESS:
				return new PayerRejectStagesView($track);

			case Type::PAYER_APPROVE_STAGES:
			case Type::PAYER_APPROVE_STAGES_INPROGRESS:
				return new PayerApproveStagesView($track);

			case Type::CRON_CHECK_APPROVE_STAGE:
			case Type::CRON_CHECK_APPROVE_STAGE_INPROGRESS:
				return new CronCheckApproveStagesView($track);

			case Type::PAYER_UNPAID_INPROGRESS:
				if ($track->order->isWorker($this->getUserId())) {
					return new StageAfterTextView($track);
				}
				return new StageBeforeTextView($track);

			case Type::PAYER_CANCEL_INPROGRESS:
			case Type::PAYER_DONE_INPROGRESS:
			case Type::PAYER_DONE_INPROGRESS_UNPAID:
				if ($track->order->isWorker($this->getUserId())) {
					return new WorkerStageInprogressView($track);
				}
				return new StageBeforeTextView($track);

			case Type::STAGE_UNPAID:
				if ($track->order->isWorker($this->getUserId())) {
					return new WorkerStageUnpaidView($track);
				}
				return new PayerStageUnpaidView($track);

			case Type::PAYER_STAGE_PAID:
				return new PayerStagePaidView($track);

			case Type::ADMIN_CANCEL_INPROGRESS:
			case Type::ADMIN_DONE_INPROGRESS:
				return new AdminToInprogressView($track);

			default:
				return new SystemView($track);
		}
	}

	/**
	 * Трека со статусами отмены?
	 *
	 * @param string $type тип трека
	 * @return bool
	 */
	private function isCancel(string $type):bool {
		return false !== strpos($type, "inprogress_cancel") ||
			false !== strpos($type, "check_cancel") ||
			false !== strpos($type, "inprogress_inwork_cancel");
	}

	/**
	 * Трек с арбитражом?
	 *
	 * @param string $type тип трека
	 * @return bool
	 */
	private function isArbitrage(string $type):bool {
		return false !== strpos($type, "arbitrage");
	}

	/**
	 * Трек с дополнительными опциями?
	 *
	 * @param string $type тип трека
	 * @return bool
	 */
	private function isExtra(string $type):bool {
		return false !== strpos($type, "extra");
	}

	/**
	 * Трек с сообщениями?
	 *
	 * @param string $type тип трека
	 * @return bool
	 */
	private function isTextType(string $type):bool {
		$userTypes = Type::getUserTypes();
		return isset($userTypes[$type]);
	}

	/**
	 * Трек только для покупателя?
	 *
	 * @param Track $track
	 * @return bool
	 */
	private function isOnlyForPayer(Track $track):bool {
		return $track->order->isWorker($this->getUserId()) &&
			$this->isPayerOnlyType($track->type) ||
			empty($track->type);
	}

	/**
	 * Тип должен выводиться только у покупателя?
	 *
	 * @param string $type
	 * @return bool
	 */
	private function isPayerOnlyType(string $type):bool {
		$payerOnlyTypes = Type::getPayerOnlyTypes();
		return isset($payerOnlyTypes[$type]);
	}
}