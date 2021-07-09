<?php


namespace Track\View\Stages;


use Model\Track;
use Order\Stages\OrderStageManager;
use Track\Type;
use Track\View\AbstractView;
use TrackManager;

class PayerStageUnpaidView extends AbstractView {

	/**
	 * Время до отмены (сек.)
	 * @var int
	 */
	private $timeLeft = null;

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/payer_stage_unpaid";
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендера
	 */
	protected function getParameters(): array {
		return [
			"showActualStages" => $this->isLastTrack(),
			"showWarning" => $this->isShowWarning(),
			"thresholdTime" => $this->getTresholdTime(),
			"unpaidCancelDays" => $this->getUnpaidCancelDays(),
			"showWarranty" => $this->isShowWarranty(),
		];
	}

	/**
	 * Показывали ли инфо-блок про гаантию возврата денежных стредств
	 *
	 * @return bool
	 */
	private function isShowWarranty() : bool{
		$lastThisTypeTrack = $this->track->order->tracks->where(Track::FIELD_TYPE, $this->track->type)->last();
		// Если это последний трек с таким типом - показывать
		return $lastThisTypeTrack->MID == $this->track->MID;
	}

	/**
	 * Получить время до отмены по треку
	 * @return int
	 */
	private function getTimeLeft() {
		if (is_null($this->timeLeft)) {
			$time = strtotime($this->track->date_create);
			$time += OrderStageManager::getUnpaidCancelDays() * \Helper::ONE_DAY;

			$this->timeLeft = $time - time();
		}
		return $this->timeLeft;
	}

	/**
	 * Показывать ли красное предупреждени
	 *
	 * @return bool
	 */
	private function isShowWarning() {
		$warningThreshold = \Helper::ONE_DAY * 3;

		$timeLeft = $this->getTimeLeft();

		return $this->track->order->isUnpaid() &&
			$this->isLastTrack() &&
			$timeLeft < $warningThreshold;
	}

	/**
	 * Является ли последним важным треком
	 *
	 * @return bool
	 */
	private function isLastTrack() {
		return $this->track->order->last_track_id == $this->track->MID;
	}

	/**
	 * Оставшееся время для оплаты или false если оплатить уже нельзя
	 * @return bool|string
	 */
	private function getTresholdTime() {
		$timeLeft = $this->getTimeLeft();

		if ($this->track->order->isUnpaid() && $this->isLastTrack() && $timeLeft > 0) {
			$hoursLeftRaw = $timeLeft / \Helper::ONE_HOUR;

			$hoursLeft = round($hoursLeftRaw);
			$daysUntilCancel = (int)($hoursLeft/24);
			$hoursUntilCancel = $hoursLeft % 24;

			return ($daysUntilCancel > 0 ? $daysUntilCancel . " " . \Translations::t("д.") : "") .
				($daysUntilCancel > 0 && $hoursUntilCancel > 0 ? " " : "") .
				($hoursUntilCancel > 0 ? $hoursUntilCancel . " " . \Translations::t("ч.") : "");
		} else {
			return false;
		}
	}

	/**
	 * Получить из настройки кол-во дней для оплаты этапа и вывести в строку
	 * @return string
	 */
	private function getUnpaidCancelDays() {
		return OrderStageManager::getUnpaidCancelDays() . " " . declension(OrderStageManager::getUnpaidCancelDays(), ["дня", "дней", "дней"]);
	}

}