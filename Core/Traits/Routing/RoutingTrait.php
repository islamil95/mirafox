<?php

namespace Core\Traits\Routing;


use Core\Routing\UrlGeneratorSingleton;

/**
 * Методы для маршрутизации
 *
 * Trait RoutingTrait
 * @package Core\Traits\Routing
 */
trait RoutingTrait {

	/**
	 * Создать относительный url по маршруту
	 *
	 * @param string $routeName назавние маршрута
	 * @param array $urlParameters параметры
	 * @return string урл
	 */
	protected function getUrlByRoute(string $routeName, array $urlParameters = []):string {
		return UrlGeneratorSingleton::getInstance()
			->generate($routeName, $urlParameters);
	}

	/**
	 * Создать абсолютный урл по имени маршрута
	 *
	 * @param string $routeName назавние маршрута
	 * @param array $urlParameters параметры
	 * @return string урл
	 */
	protected function getAbsoluteUrlByRoute(string $routeName, array $urlParameters = []):string {
		$baseUrl = \App::config("baseurl");
		$relativeUrl = $this->getUrlByRoute($routeName, $urlParameters);
		return $baseUrl . $relativeUrl;
	}

	/**
	 * Создать абсолютный урл по имени маршрута на оригинальный хост
	 *
	 * @param string $routeName название маршрута
	 * @param array $urlParameters параметры урл
	 * @return string урл
	 */
	protected function getOriginalUrlByRoute(string $routeName, array $urlParameters = []):string {
		$originalUrl = \App::config("originurl");
		$relativeUrl = $this->getUrlByRoute($routeName, $urlParameters);
		return $originalUrl . $relativeUrl;
	}
}