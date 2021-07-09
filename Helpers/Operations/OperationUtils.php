<?php
namespace Operations;

/**
 * Трейт для дополнительных возможностей класса operationManager, чтобы не засорять 
 * основной класс для операций
 */
trait OperationUtils {

	/**
	 * Массив начальных цифр у беларусских телефонных номеров
	 * @var array 
	 */
	public $belarusNumberPrefix = [
		"37524",
		"37525",
		"375291",
		"375292",
		"375293",
		"375294",
		"375295",
		"375296",
		"375297",
		"375298",
		"375299",
		"37533",
		"37544"
	];

	/**
	 * Относится ли телефонный номер к беларусскому
	 * @param type $number
	 * @return boolean
	 */
	public function isBelarusQiwiNumber($number) {
		$number = (string) $number;
		if ($number && strlen($number) == 12) {
			foreach ($this->belarusNumberPrefix as $prefix) {
				if (substr($number, 0, mb_strlen($prefix)) == $prefix) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Массив кодов стран снг
	 * @var array 
	 */
	public $ussrList = [
		"RU", "AZ", "AM", "BY",
		"KZ", "KG", "MD", "TJ",
		"TM", "UA", "UZ"
	];

	/**
	 * Относится ли код страны к странам СНГ
	 * @param $code
	 * @return bool
	 */
	public function isCountryCodeForeign($code) {
		return !in_array($code, $this->ussrList);
	}

	/**
	 * Заблокировано ли добавление белорусских киви карт
	 * @param string $number - телефонный номер
	 * @return true
	 */
	public function isBlockQiwiBlr($number) {
		return \App::config("solar_staff.block_blr_qiwi") && $this->isBelarusQiwiNumber($number);
	}
}
