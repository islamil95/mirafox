<?php

namespace Track\View\Tips;


use Model\CurrencyModel;
use Track\View\AbstractView;

class TipsView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getColor() {
		return "green";
	}

	/**
	 * @inheritdoc
	 */
	protected function getText() {
		if ($this->track->order->USERID == $this->getUserId()) {
			return \Translations::t("Вы отправили бонус исполнителю");
		}
		return \Translations::t("Покупатель отправил вам бонус<br>в знак благодарности за хорошую работу");
	}

	/**
	 * @inheritdoc
	 */
	protected function getIcon() {
		return "ico-check";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		if ($this->track->order->USERID == $this->getUserId()) {
			return \Translations::t("Бонус отправлен");
		}
		return \Translations::t("Получен бонус");
	}

	/**
	 * Получить сумму чаевых
	 *
	 * @return string
	 */
	private function getSum(): string {
		// если текущий пользователь является исполнителем по текущему заказу, то показываем сумму за вычетом комиссии
		if($this->track->order->isWorker($this->getUserId())) {
			$sum = $this->track->tips->crt;
		} else {
			$sum = $this->track->tips->amount;
		}
		$sum = \Helper::zero($sum);

		// определяем какую валюту показывать
		if($this->track->order->currency_id == CurrencyModel::RUB) {
			$sum = $sum . " ₽";
		} elseif($this->track->order->currency_id == CurrencyModel::USD) {
			$sum = "$" . $sum;
		}
		return $sum;
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"sum" => $this->getSum(),
			"message" => $this->track->tips->comment,
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/tips/tips";
	}
}