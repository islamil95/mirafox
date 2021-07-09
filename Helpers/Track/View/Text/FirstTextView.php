<?php


namespace Track\View\Text;


use Model\Track;

/**
 * Начало выполнения заказа
 *
 * Class FirstTextView
 * @package Track\View\Text
 */
class FirstTextView extends TextView {

	/**
	 * @inheritdoc
	 */
	public function __construct(Track $track) {
		parent::__construct($track);
		$this->setAddTrackIdAttribute(false);
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/text/first_text";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		if ($this->track->order->isPayer($this->getUserId())) {
			return \Translations::t("Вы предоставили нужные данные продавцу");
		}
		return \Translations::t("Получена информация от покупателя");
	}
}