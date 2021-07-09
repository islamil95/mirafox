<?php


namespace Track\Factory;


use Model\Track;
use Track\View\IView;

/**
 * Получения вида для отображения трека
 *
 * Interface ITrackViewFactory
 * @package Track\Factory
 */
interface ITrackViewFactory {

	/**
	 * Получить отобжражение для трека
	 *
	 * @param Track $track трек
	 * @return IView внешний вид трека
	 */
	public function getView(Track $track):IView;

}