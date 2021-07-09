<?php

namespace Controllers\Api\Track;


use Controllers\Api\AbstractApiController;
use Controllers\Track\Strategy\GetFullOrderStrategy;
use Converter\OrderToJsonConverter;
use Model\Track;
use Strategy\Track\GetIsShowWorkTimeWarningStrategy;
use Strategy\Track\GetTracksAsHtmlStrategy;
use Strategy\Track\GetWorkTimeWarningStrategy;
use Strategy\Track\Pull\GetSideBarStrategy;
use Strategy\Track\Pull\GetTracksToAddStrategy;
use Strategy\Track\Pull\GetTracksToReplaceStrategy;
use Symfony\Component\HttpFoundation\Request;
use Track\Factory\FormViewFactory;
use Track\Type;

/**
 * Class GetNewTracksController
 * @package Controllers\Api\Track
 */
class GetNewTracksController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return [
				"success" => false,
				"message" => \Translations::t("Необходима авторизация"),
			];
		}

		$orderId = $request->request->getInt("orderId");
		$lastTrackId = $request->request->getInt("lastTrackId");
		$newTrackType = $request->request->get("newTrackType");
		$forceReplace = $request->request->getBoolean("forceReplace");
		$getOrderStrategy = new GetFullOrderStrategy($orderId);

		try {
			$order = $getOrderStrategy->getOrder();
			if ($this->isWorker()) {
				$order->initFirstRework();
				$order->initThresholdMessage();
			}
			$tracksToReplaceStrategy = new GetTracksToReplaceStrategy($order, $newTrackType, $lastTrackId, $forceReplace);
			$tracksToReplace = $tracksToReplaceStrategy->get();
			$tracksToReplaceIds = array_column($tracksToReplace, Track::FIELD_ID);

			// Для покупателя проверим есть ли открытый запрос на отмену от покупателя
			if ($this->getUserId() == $order->USERID) {
				$payerInprogressCancelTrack = $order->tracks
					->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
					->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
					->first();
				if ($payerInprogressCancelTrack instanceof Track && !in_array($payerInprogressCancelTrack->MID, $tracksToReplaceIds)) {
					$tracksToReplace[] = $payerInprogressCancelTrack;
				}
			}

			// Для продавца если новый трек завершающий заказ, и заказ не взят в работу
			// то обновлянем треки возобновляющие заказ, для того чтобы убрались кнопочки
			if ($order->isWorker($this->getUserId()) && $order->isNotInWork() && in_array($newTrackType, Type::getOrderFinishTypes())) {
				$lastOrderRestartTrack = $order->tracks
					->where(Track::FIELD_ID, "<=", $lastTrackId)
					->whereIn(Track::FIELD_TYPE, Type::getOrderRestartTypes())
					->last();
				if ($lastOrderRestartTrack instanceof Track && !in_array($lastOrderRestartTrack->MID, $tracksToReplaceIds)) {
					$tracksToReplace[] = $lastOrderRestartTrack;
				}
			}

			$tracksToAddStrategy = new GetTracksToAddStrategy($order, $newTrackType, $lastTrackId, $forceReplace, empty($tracksToReplace));
			$tracksToAdding = $tracksToAddStrategy->get();

			$sideBarStrategy = new GetSideBarStrategy($order, $newTrackType,$lastTrackId, $forceReplace);
			$sideBar = $sideBarStrategy->get();
			$formView = FormViewFactory::getInstance()->getView($order);

			return [
				"success" => true,
				"tracksToAdd" => (new GetTracksAsHtmlStrategy($tracksToAdding, $order))->get(),
				"tracksToReplace" => (new GetTracksAsHtmlStrategy($tracksToReplace, $order))->get(),
				"other" => $sideBar,
				"formHtml" => $formView->render(),
				"formHtmlMD5" => $formView->getFormMD5Hash(),
				"Track" => new OrderToJsonConverter($order),
				"workTimeWarning" => (new GetWorkTimeWarningStrategy($order))->get(),
				"isShowWorkTimeWarning" => (new GetIsShowWorkTimeWarningStrategy($order))->get(),
			];
		} catch (\Exception $e) {
			return [
				"success" => false,
				"message" => \Translations::t("Ошибка получения треков"),
			];
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Track.api_getNewTracks";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return $request->request->getInt("orderId") <= 0 ||
			$request->request->getInt("lastTrackId") <= 0;
	}
}