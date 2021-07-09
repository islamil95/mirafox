<?php


namespace Track\View\Stages;


use Model\Track;
use Track\Type;
use Track\View\AbstractView;
use TrackManager;

class WorkerStageInprogressView extends AbstractView {

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	protected function getTemplateName(): string {
		return "track/view/stages/worker_stage_inprogress";
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	protected function getParameters(): array {
		$autoCancelTimeLeftString = $this->getTimeLeftString();
		return [
			"autoCancelTimeLeftString" => $autoCancelTimeLeftString,
			"stageCancelReasonName" => $this->getStageCancelReasonName(),
			"timeLeftFirstNumber" => (int)$autoCancelTimeLeftString, // для правильного склонение
			"timeIsRed" => $this->track->order->hoursToGetInwork() <= 24,
			"isNeedShowAction" => $this->isNeedShowAction(),
		];
	}

	/**
	 * Получение строки дней, часов
	 *
	 * @return string
	 */
	private function getTimeLeftString() {
		$hoursLeft = $this->track->order->hoursToGetInwork();
		$timeLeft = $hoursLeft * \Helper::ONE_HOUR;
		return \Helper::timeLeft($timeLeft, false, false, 2);
	}

	/**
	 * Получение названия причины отмены которую рекомендуется выбрать в подсказке
	 *
	 * @return string
	 */
	private function getStageCancelReasonName() {
		return TrackManager::getCancelReason(TrackManager::REASON_TYPE_WORKER_DISAGREE_STAGES)["name"];
	}

	/**
	 * Определение нужно ли показывать кнопку и отсчет времени
	 *
	 * @return bool
	 */
	private function isNeedShowAction() {
		if ($this->track->order->isNotInProgress()) {
			return false;
		}

		if ($this->track->order->isInWork()) {
			return false;
		}
		//если последний трек не запрос на отмену
		$lastTrack = $this->track->order->tracks->last();
		if (in_array($lastTrack->type, [Type::WORKER_INPROGRESS_CANCEL_REQUEST, Type::PAYER_INPROGRESS_CANCEL_REQUEST])) {
			return false;
		}

		// Поиск последнего трека с таким типом
		$lastThisTypeTrack = $this->track->order->tracks->where(Track::FIELD_TYPE, $this->track->type)->last();
		if(!$lastThisTypeTrack instanceof Track) {
			\Log::dailyErrorException(new \RuntimeException("Загруженные в order треки не содержат целевого трека"));
		}

		// Если это последний трек с таким типом - показывать
		return $lastThisTypeTrack->MID == $this->track->MID;
	}

}