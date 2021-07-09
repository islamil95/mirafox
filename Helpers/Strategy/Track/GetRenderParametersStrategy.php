<?php

namespace Strategy\Track;

use Converter\OrderToJsonConverter;
use Track\Status;
use Model\Order;

/**
 * Получить массив необходимых для рендера трека параметров
 *
 * Class GetRenderParametersStrategy
 * @package Strategy\Track
 */
class GetRenderParametersStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get() {
		$trackStatus = new Status($this->order, $this->getUserId());
		$isInCancelRequestStrategy = new IsInCancelRequestStrategy($this->order);

		// #6318 Комиссии считаются по прогрессивной шкале
		$turnover = \OrderManager::getTurnover($this->order->worker_id, $this->order->USERID, $this->order->kwork->lang);

		\TrackManager::setOrderTrack($this->order);
		$toReturn = [
			"Track" => json_encode(new OrderToJsonConverter($this->order)),
			// TrackManager::assignVars
			"order" => $this->order,
			"multi_kwork_rate" => \OrderManager::getMultiKworkRate($this->order->data->category),
			"isKworkUser" => $this->currentUserIsKworkUser(),
			"inCancelRequest" => $isInCancelRequestStrategy->get(),
			//Сворачиваемый блок предварительных сообщений
			"hasHidableConversation" => (new HasHiddenConversation($this->order))->get(),
			"shownThenHided" => (new GetShownThenHidedStrategy($this->order))->get(),
			"statusTitle" => $trackStatus->title(),
			"statusDesc" => $trackStatus->description(),
			"canWriteMessage" => (new CanWriteMessageStrategy($this->order))->get(),
			"isDoneConvAllow" => (new IsDoneConversationAllowStrategy($this->order))->get(),
			"arbitrage_enable" => $this->config("arbitrage.enable"),
			"canWriteReview" => (new CanAddReviewStrategy($this->order))->get(),
			"editTypeReview" => (new GetAvailableReviewTypeStrategy($this->order))->get(),
			"similarOrderData" => (new GetSimilarOrderDataStrategy($this->order))->get(),
			"hasCountbox" => (new HasCountdownStrategy($this->order))->get(),
			"workTimeWarning" => (new GetWorkTimeWarningStrategy($this->order))->get(),
			"isShowWorkTimeWarning" => (new GetIsShowWorkTimeWarningStrategy($this->order))->get(),
			"workTimeError" => (new GetWorkTimeErrorStrategy($this->order))->get(),
			"stopedTimeCancelRequest" => (new GetStoppedTimeCancelRequestStrategy($this->order))->get(),
			"deadlineTimeCancelRequest" => (new GetDeadlineTimeCancelRequestStrategy($this->order))->get(),
			"timeLeftStr" => (new GetTimeLeftAsStringStrategy($this->order))->get(),
			"canWriteToSeller" => CanWriteToSellerStrategy::getInstance($this->order)->get(),
			"cancelReasons" => \TrackManager::getCancelReasonsByOrder($this->order),
			"canReOrder" => (new CanReOrderStrategy($this->order))->get(),
			"isCancelRequest" => $isInCancelRequestStrategy->get(),
			"optionPrices" => (new GetOptionPricesStrategy($this->order))->get($turnover, true),
			"needHideAddExtrasList" => $this->order->isNeedHideAddExtrasList(),
			"maxPhotos" => $this->order->kwork->kworkCategory->max_photo_count,
			"maxKworkCount" => $this->config("kwork.max_count"),
			"priceFor" => $this->getUserType(),
			"isStageTester" => \Order\Stages\OrderStageOfferManager::isTester(),
		];

		return $toReturn;
	}
}