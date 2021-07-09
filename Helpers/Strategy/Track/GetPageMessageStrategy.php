<?php


namespace Strategy\Track;

/**
 * Получить данные сообщения для блока нотификаций
 * Class GetPageMessageStrategy
 * @package Strategy\Track
 */
class GetPageMessageStrategy extends AbstractTrackStrategy {
	/**
	 * Получить данные сообщения для блока нотификаций
	 *
	 * @return array|bool
	 */
	public function get() {
		if ($this->order->isInProgress()) {
			return $this->getMessageDataForInProgress() ?: false;
		}

		if ($this->order->isCheck()) {
			return $this->getMessageDataForCheck() ?: false;
		}

		if ($this->order->isDone()) {
			return $this->getMessageDataForDone() ?: false;
		}

		return false;
	}

	/**
	 * Получить данные сообщения для заказов в процессе
	 *
	 * @return array
	 */
	private function getMessageDataForInProgress(): array {
		if ($this->order->isPayer($this->getUserId())) {
			return $this->getPayerMessageDataForInProgress();
		}

		if ($this->order->isWorker($this->getUserId())) {
			return $this->getWorkerMessageDataForInProgress();
		}

		return [];
	}

	/**
	 * Получить данные сообщения для заказов на проверке
	 *
	 * @return array
	 */
	private function getMessageDataForCheck(): array {
		// Для покупателя
		if ($this->order->isPayer($this->getUserId())) {
			if ($this->order->has_stages) {
				$content = \Translations::tn('Работа по этапу сдана. Проверьте выполнение и примите работу.', $this->order->getCheckStages()->count())
					. '<button class="white-btn ml20 btn-hover-orange white-btn_no-hover" onclick="js_scrollToCheckWork();" style="border:none">'
					. \Translations::t("Подтвердить выполнение")
					. '</button>';
			} else {
				$content = \Translations::t('Заказ сдан. Проверьте выполнение и примите работу <button class="white-btn ml20 btn-hover-orange white-btn_no-hover" onclick="js_scrollToCheckWork();" style="border:none">Подтвердить выполнение</button>');
			}

			return [
				"type" => "success_attention",
				"content" => $content,
			];
		}

		return [];
	}

	/**
	 * Получить данные сообщения для завершенных заказов
	 *
	 * @return array
	 */
	private function getMessageDataForDone(): array {
		$canWriteReview = (new CanAddReviewStrategy($this->order))->get();

		if ($canWriteReview) {
			return [
				"type" => "success_attention",
				"content" => \Translations::t('Поздравляем! Заказ выполнен. Не забудьте оставить отзыв <button class="orange-btn inactive ml20 noOutline" onclick="js_scrollToSendReview();" style="border:none">Написать отзыв</button>'),
			];
		}

		return [];
	}

	/**
	 * Продавцу. Получить данные для заказов в процессе
	 *
	 * @return array
	 */
	private function getPayerMessageDataForInProgress(): array {
		$isCancelRequest = (new IsInCancelRequestStrategy($this->order))->get();

		if (!$isCancelRequest && !$this->order->data_provided) {
			if ($this->order->deadline) {
				return [
					"type" => "attention",
					"content" => \Translations::t('Пожалуйста, предоставьте необходимую информацию <a class="white-btn ml20" onclick="js_scrollToInstructions();" style="border:none">Предоставить данные</a>'),
					"name" => "add-instructions",
				];
			}

			return [
				"type" => "attention",
				"content" => \Translations::t('Заказ не будет начат, пока вы не предоставите необходимую информацию <a class="white-btn ml20" onclick="js_scrollToInstructions();" style="border:none">Предоставить данные</a>'),
				"name" => "add-instructions",
			];
		}

		return [];
	}

	/**
	 * Покупателю. Получить данные для заказов в процессе
	 *
	 * @return array
	 */
	private function getWorkerMessageDataForInProgress(): array {
		$isCancelRequest = (new IsInCancelRequestStrategy($this->order))->get();

		if (!$isCancelRequest) {
			if ($this->order->isNotInWork() && $this->order->data_provided == 1) {
				return [
					"type" => "attention",
					"content" => \Translations::t('Начните работу над заказом как можно скорее <button class="white-btn ml20" onclick="js_sendInWork();" style="border:none">Начать работу</button>'),
					"name" => "send-in-work"
				];
			}

			$workTimeError = (new GetWorkTimeErrorStrategy($this->order))->get();

			if ($workTimeError) {
				return [
					"type" => "warning",
					"content" => \Translations::t('Срок выполнения заказа истек. Срочно сдайте выполненную работу на проверку <a class="white-btn ml20" onclick="js_scrollToSendCheck();" style="border:none">Сдать работу</a>'),
					"name" => "send-check-work",
				];
			}

			$workTimeWarning = (new GetWorkTimeWarningStrategy($this->order))->get();

			if ($workTimeWarning) {
				return [
					"type" => "attention",
					"content" => \Translations::t('Срок выполнения заказа подходит к концу. Сдайте выполненную работу на проверку <a class="white-btn ml20" onclick="js_scrollToSendCheck();" style="border:none">Сдать работу</a>'),
					"name" => "send-check-work",
				];
			}
		}

		return [];
	}
}
