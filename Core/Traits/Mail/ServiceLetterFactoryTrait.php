<?php

namespace Core\Traits\Mail;


use Factory\Letter\Service\ServiceLetterFactory;

trait ServiceLetterFactoryTrait {

	private $factory = null;

	protected function getServiceLetterFactory() {
		if (is_null($this->factory)) {
			$this->factory = new ServiceLetterFactory();
		}
		return $this->factory;
	}
}