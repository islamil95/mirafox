<?php

namespace Core\Routing;

use \Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Router;

/**
 * Генератор URL
 *
 * Class UrlGeneratorSingleton
 * @package Core\Routing
 */
class UrlGeneratorSingleton
{

	private static $router = null;

	/**
	 * Инициализация
	 *
	 * @param Router $router
	 */
	public static function init(Router $router) {
		self::$router = $router;
	}

	/**
	 * Получить экземпляр класса
	 *
	 * @return null|UrlGenerator
	 */
	public static function getInstance() {
		return self::$router->getGenerator();
	}
}