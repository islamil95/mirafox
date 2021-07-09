<?php


namespace Track\Factory;


use Core\Traits\AuthTrait;
use Core\Traits\SingletonTrait;
use Model\KworkReport;
use Model\Order;
use Model\Track;
use Strategy\Track\IsShowIntermediateReportStrategy;
use Track\Type;
use Track\View\EmptyView;
use Track\View\IView;

/**
 * Фабрика для отображения отчета
 *
 * Class ReportFactory
 * @package Track\Factory
 */
class ReportFactory implements IReviewToTrackViewFactory {

	use SingletonTrait, AuthTrait;

	/**
	 * Получить внешний вид для отчета
	 *
	 * @param Order $order заказ
	 * @return IView вид отчета
	 */
	public function getView(Order $order): IView {
		$isShowIntermediateReport = new IsShowIntermediateReportStrategy($order);
		if ($isShowIntermediateReport->get()) {
			/**
			 * @var Track $fakeTrack
			 */
			$fakeTrack = Track::hydrate([[
				Track::FIELD_ID => 0,
				Track::FIELD_USER_ID => $order->USERID,
				Track::FIELD_ORDER_ID => $order->OID,
				Track::FIELD_MESSAGE => "",
				Track::FIELD_TYPE => Type::WORKER_REPORT_NEW,
				Track::FIELD_STATUS => "",
				Track::FIELD_DATE_CREATE => time(),
				Track::FIELD_REASON_TYPE => "",
				Track::FIELD_PREV_REASON_TYPE => "",
				Track::FIELD_REPLY_TYPE => "",
				Track::FIELD_SUPPORT_ID => "",
				Track::FIELD_UNREAD => "",
			]])[0];
			$newReport = $order->reports->firstWhere(KworkReport::FIELD_STATUS, KworkReport::STATUS_NEW);
			$fakeTrack->setRelation("order", $order);
			$fakeTrack->setRelation("report", $newReport);
			return TrackViewFactory::getInstance()->getView($fakeTrack);
		}
		return new EmptyView(new Track());
	}
}