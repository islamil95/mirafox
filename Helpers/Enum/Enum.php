<?php
/**
 * Created by PhpStorm.
 * User: uiouo
 * Date: 05.09.2017
 * Time: 14:36
 */

namespace Enum;


use Exception\EnumIllegalKeyException;

abstract class Enum
{
	private $element;

	/**
	 * Enum constructor.
	 * @param $constantName string Имя константы класса
	 * @throws EnumIllegalKeyException
	 */
	public function __construct(string $constantName) {
		$reflection = new \ReflectionClass(static::class);
		$constant = $reflection->getConstant($constantName);
		if($constant === false) {
			throw new EnumIllegalKeyException();
		}
		$this->element = $constant;
	}

	/**
	 * Получение значение выбранной константы
	 * @return mixed
	 */
	public function getValue() {
		return $this->element;
	}

}