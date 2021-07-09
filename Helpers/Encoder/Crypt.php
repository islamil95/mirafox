<?php

namespace Encoder;


class Crypt
{
	/**
	 * Сериализовать любой тип кроме объекта
	 *
	 * @param $data
	 * @return string
	 */
	public static function encodeData($data)
	{
		self::tryLogObject($data, "Метод Crypt::encodeData, ожидаемый тип: любой, кроме объекта");
		return json_encode($data);
	}

	/**
	 * Десериализовать любой тип кроме объекта
	 *
	 * @param string $data
	 * @return mixed массив или скалярное значение
	 */
	public static function decodeData($data)
	{
		if (!$data) {
			return $data;
		}
		$res = json_decode($data, true);
		// Если найдены два элемента - это признак, что данные закодированны старым способом
		$catchJsonEncoder = false;
		if (isset($res['data']) && isset($res['type'])) {
			$catchJsonEncoder = true;
		}
		if(empty($res) || $catchJsonEncoder){
			self::tryLogFailDecode($data, "Метод Crypt::decodeData, ожидаемые данные - json_encode, фактические в логе:");
			$res = \Encoder\JsonEncoder::decode($data);
		}

		return $res;
	}

	/**
	 * Сериализовать обьект
	 *
	 * @param $data
	 * @param bool $tryLog
	 * @return string
	 */
	public static function encodeObj($data, $tryLog = true)
	{
		if ($tryLog) {
			self::tryLogObject($data, "Метод Crypt::encodeObj, ожидаемый тип: объект");
		}
		return serialize($data);
	}

	/**
	 * Десериализовать объект
	 *
	 * @param string $data
	 * @param bool $tryLog
	 * @return mixed Если был сериализован объект - вернется объект, если массив - массив, или обычное скалярное значение
	 */
	public static function decodeObj($data, $tryLog = true)
	{
		if (!$data) {
			return $data;
		}
		$res = unserialize($data);
		// Если не получилось раскодировать, возможно это данные закодированные старым способом
		// 'i:0;' - это сериализованный ноль
		if (!$res && $data !== 'i:0;') {
			if ($tryLog) {
				self::tryLogFailDecode($data, "Метод Crypt::decodeObj, ожидаемые данные - serialize, фактические в логе:");
			}
			$res = \Encoder\JsonEncoder::decode($data);
		}
		return $res;
	}

	/**
	 * Запишет в лог $data, если в конфиге выставлен флаг encoder.catch.fail_decode.on
	 * @param mixed $data
	 * @param string $logMessage комментарий
	 */
	public static function tryLogFailDecode($data, $logMessage = "")
	{
		if (\App::config("encoder.catch.fail_decode.on")) {
			$data = "- - - {$logMessage} \r\n" . print_r($data, true) . "\r\n" . \Log::getStackCall();
			\Log::write($data, 'encoder.fail_decode');
		}
	}


	/**
	 * Запишет в лог $data, если в конфигурации encoder.catch.object.on = true
	 * @param mixed $data
	 * @param string $logMessage комментарий
	 */
	public static function tryLogObject($data, $logMessage = "")
	{
		if (\App::config("encoder.catch.object.on")) {
			if (self::seekObject($data)) {
				$data = "- - - {$logMessage} \r\n" . print_r($data, true) . "\r\n" . \Log::getStackCall();
				\Log::write($data, 'encoder.object');
			}
		}
	}

	/**
	 * Если найден объект возвращает true
	 *
	 * @param mixed $data
	 * @return bool
	 */
	public static function seekObject($data)
	{
		if (is_object($data)) {
			return true;
		}

		if (!is_array($data)) {
			return false;
		}

		$catchObject = false;
		array_walk_recursive($data, function ($v, $k) use (&$catchObject) {
			if (is_object($v)) {
				$catchObject = true;
			}
		});
		return $catchObject;
	}
}