<?php


namespace Track\View\Order;


use Model\Track;
use Track\View\AbstractView;

/**
 * Отображение для заказа в процессе принятия
 *
 * Class InProgressCheckView
 * @package Track\View\Order
 */
class InProgressCheckView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"haveActions" => $this->isHaveActions(),
			"infoForWorker" => $this->getInfoForWorker(),
			"messageClasses" => $this->getMessageCSSClasses(),
			"hasStages" => $this->hasStages(),
			"hasMultipleStages" => $this->hasMultipleStages(),
			"firstStage" => $this->getFirstStage(),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/inprogress_check";
	}

	/**
	 * Получить нужные css классы
	 *
	 * @return string CSS классы
	 */
	private function getMessageCSSClasses():string {
		if (mb_strlen($this->track->message) > 100) {
			return "t-align-l ml80";
		}
		return "t-align-c";
	}

	/**
	 * Получить сообщение для продавца
	 *
	 * @return null|string сообщение
	 */
	private function getInfoForWorker() {
		if ($this->track->order->isPayer($this->getUserId())) {
			return null;
		}

		$timeUntilAutoAcceptLeftForHuman = mb_lcfirst($this->track->order->timeUntilAutoaccept());

		return \Translations::t("Если в течение 3 суток (4, если больше суток приходится на выходные или праздники) покупатель не примет решение, заказ будет принят автоматически. Осталось %s до автопринятия.", $timeUntilAutoAcceptLeftForHuman);
	}

	/**
	 * Отображать кнопки действий?
	 *
	 * @return bool
	 */
	private function isHaveActions():bool {
		return $this->track->order->isPayer($this->getUserId()) &&
			$this->track->status == \TrackManager::STATUS_NEW &&
			$this->track->order->isCheck();
	}

	/**
	 * Переопределение заголовка трека для поэтапных заказов
	 *
	 * @return string
	 */
	protected function getTitle() {
		if ($this->hasStages()) {
			if ($this->hasMultipleStages()) {
				return \Translations::t("Работа по этапам сдана на проверку");
			}
			return \Translations::t("Работа по этапу сдана на проверку");
		}

		return parent::getTitle();
	}

	/**
	 * Есть ли этапы
	 *
	 * @return bool
	 */
	private function hasStages() {
		return $this->track->order->has_stages && $this->track->trackStages && $this->track->trackStages->count();
	}

	/**
	 * Несколько ли этапов
	 *
	 * @return bool
	 */
	private function hasMultipleStages() {
		return false;
	}

	/**
	 * Получение первого этапа
	 *
	 * @return \Model\OrderStages\OrderStage|null
	 */
	private function getFirstStage() {
		return null;
	}


}