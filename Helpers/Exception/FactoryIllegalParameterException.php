<?php

namespace Exception;

class FactoryIllegalParameterException extends \Exception{

	/**
	 * FactoryIllegalParameterException constructor.
	 * @param string $stringData Строка с данными, которые привели к ошибке
	 */
    public function __construct(string $stringData = "") {
		parent::__construct("Illegal parameter in abstract factory, value: {$stringData}");
    }
}
