<?php

namespace Track\View\Payer;

use Track\View\AbstractView;

/**
 * Совет покупателю
 *
 * Class AdviceView
 * @package Track\View\Payer
 */
class AdviceView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/payer/advice";
	}

	/**
	 * @inheritdoc
	 */
	protected function getColor() {
		return "green";
	}

	/**
	 * @inheritdoc
	 */
	protected function getIcon() {
		return "ico-track-tip-info";
	}

	/**
	 * @inheritdoc
	 */
	protected function getTitle() {
		switch($this->track->message) {
			case "instruction":
				return \Translations::t("Чтобы получить максимально качественный результат в кратчайшие сроки, воспользуйтесь советом:");

			case "overdue":
				return \Translations::t("Чтобы не зависеть от одного продавца и не срывать сроки, воспользуйтесь советом:");

			case "bad_review":
				return \Translations::t("Чтобы не зависеть от одного продавца и всегда получать отличный результат, воспользуйтесь советом:");

			default:
				return "";
		}
	}
}