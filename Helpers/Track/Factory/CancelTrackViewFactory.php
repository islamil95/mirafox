<?php


namespace Track\Factory;


use Core\Traits\AuthTrait;
use Core\Traits\SingletonTrait;
use Model\Track;
use Track\Type;
use Track\View\IView;
use Track\View\Order\Cancel\BaseCancelView;
use Track\View\Order\Cancel\InProgressCancelRequestView;
use Track\View\Order\Cancel\InProgressCancelView;
use Track\View\Order\Cancel\OrderCancelView;
use Track\View\Order\Cancel\PayerCronRestartedInprogressCancelView;
use Track\View\Order\Cancel\PayerInProgressCancelConfirmView;
use Track\View\SystemView;

/**
 * Фабрика для орбраотки случает отказа
 *
 * Class CancelTrackViewFactory
 * @package Track\Factory
 */
class CancelTrackViewFactory implements ITrackViewFactory {

	use AuthTrait, SingletonTrait;

	/**
	 * @inheritdoc
	 */
	public function getView(Track $track): IView {
		// Шаблон содержимого трека свой, а обертка обычная, системная.
		switch($track->type) {
			case Type::WORKER_INPROGRESS_CANCEL_REQUEST:
			case Type::WORKER_INPROGRESS_CANCEL:
				return new InProgressCancelView($track);
				break;

			case Type::PAYER_INPROGRESS_CANCEL_REQUEST:
				return new InProgressCancelRequestView($track);
				break;

			case Type::CRON_RESTARTED_INPROGRESS_CANCEL:
				if ($track->order->isPayer($this->getUserId())) {
					return new PayerCronRestartedInprogressCancelView($track);
				}
				return new OrderCancelView($track);

			case Type::PAYER_INPROGRESS_CANCEL_CONFIRM:
				return new PayerInProgressCancelConfirmView($track);
				break;

			// Это все здесь только из-за inprogress_cancel, чтобы такие типы были в одном месте.
			// А по сути - это обычные системные сообщения, для ViewSystem.
			case Type::WORKER_INPROGRESS_CANCEL_CONFIRM:
			case Type::WORKER_INPROGRESS_CANCEL_REJECT:
			case Type::WORKER_INPROGRESS_CANCEL_DELETE:
			case Type::PAYER_INPROGRESS_CANCEL_REJECT:
			case Type::PAYER_INPROGRESS_CANCEL_DELETE:
			case Type::CRON_INPROGRESS_INWORK_CANCEL:
				return new OrderCancelView($track);
				break;

			default:
				// Базовый шаблон для всех остальных типов
				return new BaseCancelView($track);
		}
	}
}