<?php


namespace Track\View;


use Model\Track;

/**
 * Пустой трек заглушка
 *
 * Class EmptyView
 * @package Track\View
 */
class EmptyView implements IView {

	/**
	 * @inheritdoc
	 */
	public function __construct(Track $track) {
	}

	/**
	 * @inheritdoc
	 */
	public function render(): string {
		return "";
	}
}