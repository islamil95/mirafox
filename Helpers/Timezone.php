<?php

class Timezone {
	const TABLE_NAME = "timezone";
	const F_ID = "id";
	const F_UTC_OFFSET = "utc_offset";
	const F_NAME = "name";
	const F_NAME_EN = "name_en";

    /**
     * Возвращает отформатированную строку часового пояса
     * @param float $offset - Смещение от UTC
     * @param string $type - формат. long - "UTC +01:00", short - "+0100"
     * @return string
     */
    public static function formatString($offset, $type = "long") {
		$offsetSign = ($offset < 0) ? "" : "+";

		$offsetNumbers = abs($offset);
		$offsetNumbersHours = floor($offsetNumbers);
		$offsetNumbersMinutes = intVal(($offsetNumbers - $offsetNumbersHours) * 60);

		if ($offset > -10 && $offset < 10) {
			$offsetNumbersHours = "0" . $offsetNumbersHours;
		}
		if ($offsetNumbersMinutes < 10) {
			$offsetNumbersMinutes = "0" . $offsetNumbersMinutes;
		}

		$offsetNumbersHours = ($offset < 0 ? "-" : "") . $offsetNumbersHours;

		$pre = "";
		$post = "";
		if ($type == "long" || $type == "merged") {
			$pre = "UTC";
			if ($type == "long") {
				$pre .= " ";
			}
			$post = ":" . $offsetNumbersMinutes;
		} elseif ($type == "short") {
			$pre = "";
			$post = $offsetNumbersMinutes;
		}
		return  $pre . $offsetSign . $offsetNumbersHours . $post;
    }

    /**
     * Возвращает объект класса DateTime с учетом часового пояса
     * @param DateTime $datetime
     * @param null|float $timezone - смещение от UTC
     * @return DateTime
     */
    public static function setTimezone($datetime, $timezone = null) {
        global $actor;
        if (!App::config("module.timezone.enable")) return $datetime;
        if(is_null($timezone)) {
            if (!$actor) return $datetime;
            $timezone = $actor->timezone;
        }
        $serverTimezone = App::config("server.timezone");
        $timezoneDifference = $timezone - $serverTimezone;
        if($timezoneDifference == 0) return $datetime;

		$timezoneDifferenceAbs = abs($timezoneDifference);
		$timezoneDifferenceHours = floor($timezoneDifferenceAbs);
		$timezoneDifferenceString = $timezoneDifferenceHours . " hours";
		if($timezoneDifferenceMinutes = $timezoneDifferenceAbs - $timezoneDifferenceHours) $timezoneDifferenceString .= ($timezoneDifferenceMinutes*60) . " minutes";
		if($timezoneDifference > 0) {
			$datetime->add(date_interval_create_from_date_string($timezoneDifferenceString));
		} else {
			$datetime->sub(date_interval_create_from_date_string($timezoneDifferenceString));
		}

        return $datetime;
    }

    /**
     * То же самое, что и Timezone::setTimezone, только принимает и возвращает int
     * @param int $time - Время в unix формате
     * @param null|float $timezone - смещение от UTC
     * @return DateTime|int
     */
    public static function setTimezoneInt($time, $timezone = null) {
        $datetime = new DateTime();
        $datetime->setTimestamp($time);
        $datetime = self::setTimezone($datetime, $timezone);
        return $datetime->getTimestamp();
    }

    /**
     * Получить массив часовых поясов
	 * @param $lang
     * @return array
     */
    public static function getList(string $lang) {

        return App::pdo()->fetchAll("SELECT 
        								" . self::F_ID . " as 'id', 
        								" . self::F_UTC_OFFSET . " as 'utc_offset', 
        								" . self::getNameFieldByLang($lang) . " as 'name' 
										FROM " . self::TABLE_NAME . " 
										ORDER BY " . self::F_UTC_OFFSET);
    }

	/**
	 * Получить массив часовой пояс
	 * @param int $id
	 * @param string $lang
	 * @return array
	 */
	public static function getItem($id, string $lang = \Translations::DEFAULT_LANG) {
		$res = App::pdo()->fetchAll("SELECT 
										" . self::F_ID . " as 'id', 
										" . self::F_UTC_OFFSET . " as 'utc_offset', 
										" . self::getNameFieldByLang($lang) . " as 'name' 
										FROM " . self::TABLE_NAME . " 
										WHERE  " . self::F_ID . " = $id LIMIT 1");
		return !empty($res[0]) ? $res[0] : [];
	}

    private static function getNameFieldByLang(string $lang) {
    	return $lang == Translations::EN_LANG ? self::F_NAME_EN : self::F_NAME;
	}
}