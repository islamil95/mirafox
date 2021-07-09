<?php

namespace Order;

use \Track\Type;

/**
 * Class AdminTreshold Для проверки частоты админских действий
 * @package Order
 */
class AdminTreshold {

	const MONTH = "month";
	const HALFYEAR = "halfyear";
	const YEAR = "year";

	/**
	 * Проверка частоты
	 * @param string $trackName Название трека создаваемого действием
	 * @param string $orderDate Дата изменения заказа date_done|date_cancel в формате Y-m-d H:i:s
	 * @throws \Exception
	 */
	public static function check(string $trackName, string $orderDate) {
		$orderDatetime = \DateTime::createFromFormat('Y-m-d H:i:s', $orderDate);
		if ($orderDatetime === false) {
			throw new \Exception("Некорректная дата заказа");
		}

		if (!array_key_exists($trackName, self::supportedTypes())) {
			throw new \Exception("Неподдерживаемый тип трека");
		}

		$orderInterval = self::getIntervalByDate($orderDate, $trackName);
		if (!$orderInterval) {
			throw new \Exception("Дата заказа была больше года назад");
		}

		$typeData = self::supportedTypes()[$trackName];
		$params = ["type" => $trackName];
		$placeholders = \App::pdo()->arrayToStrParams($typeData["prev"], $params);

		$sql = "SELECT MAX(prev_track.date_create) as date_create 
				FROM track as t
				JOIN track as prev_track 
					ON t.OID = prev_track.OID 
						AND prev_track.MID < t.MID 
						AND prev_track.type IN ($placeholders)
				WHERE t.type = :type
					AND t.date_create > now() - interval 1 month
				GROUP BY t.OID";

		$changedOrderDates = \App::pdo()->fetchAllByColumn($sql, 0, $params);

		$ordersTimeCounts = [
			self::MONTH => 0,
			self::HALFYEAR => 0,
			self::YEAR => 0
		];
		foreach ($changedOrderDates as $changedOrderDate) {
			$interval = self::getIntervalByDate($changedOrderDate, $trackName);
			if ($interval) {
				$ordersTimeCounts[$interval]++;
			}
		}

		$limit = $typeData["limits"][$orderInterval];

		if ($limit <= $ordersTimeCounts[$orderInterval]) {
			$intervalName = self::intervalNames()[$orderInterval];
			throw new \Exception("Превышен лимит интервала \"$intervalName\" - $limit заказов");
		}
	}

	/**
	 * Обрабатываемые типы треков с лимитами и типами предшествующих треков
	 * @return array
	 */
	private static function supportedTypes(): array {
		return [
			Type::ADMIN_CANCEL_ARBITRAGE => [
				"prev" => Type::cancelTypes(),
				"limits" => [
					self::MONTH => 50,
					self::HALFYEAR => 20,
					self::YEAR => 5
				]
			],
			Type::ADMIN_CANCEL_INPROGRESS => [
				"prev" => Type::cancelTypes(),
				"limits" => [
					self::MONTH => 100,
					self::HALFYEAR => 20,
					self::YEAR => 5
				]
			],
			Type::ADMIN_DONE_ARBITRAGE => [
				"prev" => Type::doneTypes(),
				"limits" => [
					self::MONTH => 100,
					self::HALFYEAR => 20,
					self::YEAR => 5
				]
			],
			Type::ADMIN_DONE_INPROGRESS => [
				"prev" => Type::doneTypes(),
				"limits" => [
					self::MONTH => 100,
					self::HALFYEAR => 20,
					self::YEAR => 5
				]
			]
		];
	}

	/**
	 * Возвращает название периода по дате
	 * @param string $date Дата в формате Y-m-d H:i:s
	 * @param string $trackName Тип трека
	 * @return string
	 */
	private static function getIntervalByDate($date, $trackName) {
		$timeAgo = time() - strtotime($date);
		$monthTreshold = \Helper::ONE_MONTH;
		if ($trackName == Type::ADMIN_DONE_ARBITRAGE) {
			$monthTreshold = \Helper::ONE_MONTH * 2;
		}

		if ($timeAgo <= $monthTreshold) {
			return self::MONTH;
		} elseif ($timeAgo <= (\Helper::ONE_MONTH * 6)) {
			return self::HALFYEAR;
		} elseif ($timeAgo <= \Helper::ONE_YEAR) {
			return self::YEAR;
		}
		return "";
	}

	/**
	 * Названия интервалов
	 * @return array
	 */
	private static function intervalNames(): array {
		return [
			self::MONTH => "Месяц",
			self::HALFYEAR => "Полгода",
			self::YEAR => "Год"
		];
	}

}