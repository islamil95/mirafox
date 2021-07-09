<?php

namespace Session;

class SessionContainer {
	
	private static $sessionProvider = null;
	
	/**
	 * Получить базовый контейнер сессии
	 * 
	 * @return \Session\IBaseSessionProvider
	 */
	public static function getSession(): IBaseSessionProvider {
		if (null === self::$sessionProvider) {
			$sessionProvider = self::getPHPSessionProvider();
			self::$sessionProvider =  new SessionDecorator($sessionProvider);
		}
		return self::$sessionProvider;
	}
	
	private static function getPHPSessionProvider() {
		$factory = new PHPSessionProviderFactory();
		return $factory->getSessionProvider();
	}
}
