<?php

namespace Session;

class PHPSessionProviderFactory implements ISessionProviderFactory {

	/**
	 * Возвращает провайдера сессии
	 *
	 * @return ISessionProvider
	 */
	public function getSessionProvider(): ISessionProvider {
		return new PHPSessionProvider();
	}

}
