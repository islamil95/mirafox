<?php


namespace Track\View;

/**
 * Интерфейс для представления трека
 *
 * Interface IView
 * @package Track\View
 */
interface IView {

	/**
	 * Рендер трека
	 *
	 * @return string HTML трека
	 */
	public function render():string;
}