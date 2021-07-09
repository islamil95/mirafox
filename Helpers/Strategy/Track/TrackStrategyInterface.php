<?php


namespace Strategy\Track;


/**
 * Базовый интерфейс стратегии
 *
 * Interface TrackStrategyInterface
 * @package Strategy\Track
 */
interface TrackStrategyInterface {

	/**
	 * Получить результат
	 *
	 * @return mixed
	 */
	public function get();
}