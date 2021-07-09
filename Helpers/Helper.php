<?php

use Illuminate\Support\Str;

class Helper {

	private static $registerCssList = array();
	private static $registerJsList = array();
	private static $notStrictFieldsisUrl = [];

	/**
	 * Одна минута в секундах
	 */
	const ONE_MINUTE = 60;

	/**
	 * Один час в секундах
	 */
	const ONE_HOUR = 3600;

	/**
	 * Один день в секундах
	 */
	const ONE_DAY = 86400;

	/**
	 * Одна неделя в секундах = 60 * 60 * 24 * 7
	 */
	const ONE_WEEK = 604800;

	/**
	 * 30 дней в секундах = 60 * 60 * 24 * 30
	 */
	const ONE_MONTH = 2592000;

	/**
	 * один год в секундах = 60 * 60 * 24 * 365
	 */
	const ONE_YEAR = 31536000;

	/**
	 * Один килобайт
	 */
	const ONE_KB = 1024;
	const AUTOCANCEL_MODE_DB = 1;
	const AUTOCANCEL_MODE_TEXT = 2;
	const AUTOCANCEL_MODE_TEXT_IN = 3;
	const AUTOCANCEL_MODE_IN_DAYS = 4;
	const AUTOCANCEL_MODE_IN_DAYS_IN = 5;

	/**
	 * Не делать обратный редирект на эти страницы
	 */
	const DO_NOT_REDIRECT_BACK = [
		"/confirmemail",
		"/confirmoauthemail",
		"/kworks_deactivate",
	];

	/**
	 * Возвращает количество времени в секундах
	 *
	 * @param int        $seconds          количество секунд
	 * @param int        $significantDigit максимальное количество значащих чисел
	 * @param array|bool $textBefore       текст до, склонение цифры, оканчивающейся на 1.
	 *                                     0 - множественное число
	 *                                     1 - единственное число женский род (секунды, минуты)
	 *                                     2 - единственное число мужской род (всё остальное)
	 * @param string     $textAfter        текст после времени
	 *
	 * @return bool|string
	 */
	public static function timeLeft($seconds, $textBefore = false, $textAfter = 'назад', $significantDigit = 1, $short = false) {
		$dateFrom = new DateTime();
		$dateTo = new DateTime();

		$dateFrom->setTimestamp(0);
		$dateTo->setTimestamp($seconds);

		$interval = $dateFrom->diff($dateTo);
		if ($short == false) {
			$arguments = [
				'y' => ['plural' => [Translations::t('год'), Translations::t('года'), Translations::t('лет')]],
				'm' => ['plural' => [Translations::t('месяц'), Translations::t('месяца'), Translations::t('месяцев')]],
				'd' => ['plural' => [Translations::t('день'), Translations::t('дня'), Translations::t('дней')]],
				'h' => ['plural' => [Translations::t('час'), Translations::t('часа'), Translations::t('часов')]],
				'i' => ['plural' => [Translations::t('минута'), Translations::t('минуты'), Translations::t('минут')]],
				's' => ['plural' => [Translations::t('секунда'), Translations::t('секунды'), Translations::t('секунд')]],
			];
		} else {
			$arguments = [
				'y' => ['plural' => [Translations::t('г.'), Translations::t('г.'), Translations::t('г.')]],
				'm' => ['plural' => [Translations::t('мес.'), Translations::t('мес.'), Translations::t('мес.')]],
				'd' => ['plural' => [Translations::t('д.'), Translations::t('д.'), Translations::t('д.')]],
				'h' => ['plural' => [Translations::t('ч.'), Translations::t('ч.'), Translations::t('ч.')]],
				'i' => ['plural' => [Translations::t('мин.'), Translations::t('мин.'), Translations::t('мин.')]],
				's' => ['plural' => [Translations::t('сек.'), Translations::t('сек.'), Translations::t('сек.')]],
			];
		}

		$started = false;
		$timeResult = [];
		$totalDigits = 0;
		foreach ($arguments as $key => $arg) {
			if ($started) {
				$totalDigits++;
			}

			if ($interval->{$key} == 0) {
				continue;
			}

			if ($totalDigits == 0 && is_array($textBefore)) {
				if ($interval->{$key} % 10 == 0 || $interval->{$key} % 10 > 1 || $interval->{$key} == 11) {
					$textBefore = $textBefore[0];
				} elseif (in_array($key, ['i', 's'])) {
					$textBefore = $textBefore[1];
				} else {
					$textBefore = $textBefore[2];
				}
			}

			$started = true;
			if ($totalDigits >= $significantDigit) {
				break;
			}

			$timeResult[] = $interval->{$key} . ' ' . declension($interval->{$key}, $arg['plural']);
		}

		$result = implode(' ', $timeResult);

		if ($result) {
			if ($textBefore) {
				$result = $textBefore . ' ' . $result;
			}

			if ($textAfter) {
				$result .= " " . $textAfter;
			}

			return $result;
		}

		return false;
	}

	/**
	 * Вернуть строку - время в человеко-читаемом виде, округленное до одного параметра:
	 * дни, часы, минуты или секунды, или "никогда", если $seconds == 0
	 *
	 * @param int|null $seconds секунды
	 * @return string
	 */
	public static function getTimeLeft($seconds) {
		if (is_null($seconds)) {
			return "";
		}

		if ($seconds > 23 * self::ONE_HOUR) {
			return round($seconds/self::ONE_DAY)." ".Translations::t("д.");
		} elseif ($seconds >= self::ONE_HOUR) {
			return round($seconds/self::ONE_HOUR)." ".Translations::t("ч.");
		} elseif ($seconds >= self::ONE_MINUTE) {
			return round($seconds/self::ONE_MINUTE)." ".Translations::t("мин.");
		} elseif ($seconds > 0) {
			return $seconds." ".Translations::t("сек.");
		} else {
			return Translations::t("никогда");
		}
	}

	/**
	 * Получить человеко-читаемое время интервала в виде "5 секунд", "2 часа 36 минут" или
	 * "1 месяц 5 дней" и т.п. (т.е. используются 2 наибольшие ненулевые еденицы измерения времени).
	 *
	 * Сколько едениц измерения выводить - второй параметр.
	 *
	 * @param int $seconds кол-во секунд в интервале
	 * @param int $parts сколько едениц измерения выводить
	 *
	 * @return string
	 */
	public static function forHumans($seconds, int $parts = 2) {
		$seconds = intval($seconds);

		if (empty($seconds) || $seconds <= 0) {
			return "";
		}
		if ($parts < 1 || $parts > 4) {
			$parts = 2;
		}

		$interval = Carbon\CarbonInterval::seconds($seconds)->cascade();

		$result = "";
		$params = [];

		if ($interval->y) {
			$params[] = [
				"plural" => ["год", "года", "лет"],
				"value" => $interval->y,
			];
		}

		if ($interval->m) {
			$params[] = [
				"plural" => ["месяц", "месяца", "месяцев"],
				"value" => $interval->m,
			];
		}

		if ($interval->d) {
			$params[] = [
				"plural" => ["день", "дня", "дней"],
				"value" => $interval->d,
			];
		}

		if ($interval->h) {
			$params[] = [
				"plural" => ["час", "часа", "часов"],
				"value" => $interval->h,
			];
		}

		if ($interval->i) {
			$params[] = [
				"plural" => ["минута", "минуты", "минут"],
				"value" => $interval->i,
			];
		}

		if ($interval->s) {
			$params[] = [
				"plural" => ["секунда", "секунды", "секунд"],
				"value" => $interval->s,
			];
		}

		for ($i = 1; $i <= $parts; $i++) {
			$param = array_shift($params);

			if ($param) {
				$result .= sprintf("%d %s ", $param["value"],  Translations::t(declension($param["value"], $param["plural"])));
			}
		}

		return trim($result);
	}

	public static function getMonthDiff($dateFrom, $dateTo) {
		$datetime1 = new DateTime($dateFrom);
		$datetime2 = new DateTime($dateTo);
		$interval = $datetime1->diff($datetime2);

		return $interval->y * 12 + $interval->m;
	}

	/**
	 * Получить домен для конкретного языка сайта
	 *
	 * @param $lang
	 * @return string
	 */
	public static function getBaseurl($lang) {
		if ($lang == Translations::DEFAULT_LANG) {
			$url = App::config("rudomain");
		} else {
			$url = App::config("endomain");
		}
		//На локалке выводим baseurl
		if (App::config("app.mode") == "local") {
			return App::config("baseurl");
		} else {
			return "https://" . $url;
		}

	}

	/**
	 * Обрезка строки до нужной длины
	 *
	 * @param        $string
	 * @param int    $length
	 * @param string $etc
	 * @param string $charset
	 * @param bool   $break_words
	 * @param bool   $middle
	 *
	 * @return mixed|string
	 */
	public static function truncateText($string, $length = 80, $etc = '...', $charset = 'UTF-8', $break_words = false, $middle = false) {
		if ($length == 0)
			return '';

		$replaces = [
			',' => ', ',
			'!' => '! ',
			'?' => '? ',
			';' => '; '
		];

		$string = strtr($string, $replaces);
		$string = preg_replace("/[ ]+/", ' ', $string);
		if (mb_strlen($string) > $length) {
			$length -= min($length, mb_strlen($etc));
			if (!$break_words && !$middle) {
				$string = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($string, 0, $length + 1, $charset));
			}
			if (!$middle) {
				return mb_substr($string, 0, $length, $charset) . $etc;
			} else {
				return mb_substr($string, 0, $length / 2, $charset) . $etc . mb_substr($string, -$length / 2, (mb_strlen($string) - $length / 2), $charset);
			}
		} else {
			return $string;
		}
	}

	/**
	 * Получить SQL строку для фильтрации по диапазону
	 *
	 * @param string|bool|false $dateFrom начальная дата
	 * @param string|bool|false $dateTo   конечная дата
	 * @param string            $type     тип результата:
	 *                                    date - возвращает дату
	 *                           timestamp - возвращает timestamp
	 *
	 * @param bool   $withTime учитывать дату и время / только дату
	 *
	 * @return bool|string
	 */
	public static function getSqlDate($dateFrom = false, $dateTo = false, $type = 'date', $withTime = false) {
		$sqlDate = $sqlDateInt = '';
		$format = 'Y-m-d';

		if ($withTime) {
			$format = 'Y-m-d H:i:s';
		}

		$dateFrom = strtotime($dateFrom);
		$dateTo = strtotime($dateTo);
		if ($dateFrom && $dateTo) {
			$sqlDate = " BETWEEN '" . mres(date($format, $dateFrom)) . "' AND '" . mres(date($format, $dateTo)) . "'";
			$sqlDateInt = " BETWEEN " . mres($dateFrom) . " AND " . mres($dateTo);
		} elseif ($dateFrom) {
			$sqlDate = " >= '" . mres(date($format, $dateFrom)) . "'";
			$sqlDateInt = " >= '" . mres($dateFrom) . "'";
		} elseif ($dateTo) {
			$sqlDate = " <= '" . mres(date($format, $dateTo)) . "'";
			$sqlDateInt = " <= '" . mres($dateTo) . "'";
		}

		if ($type == 'timestamp') {
			return $sqlDateInt;
		} elseif ($type == 'date') {
			return $sqlDate;
		}

		return false;
	}

	/**
	 * Генерирует случайную строку заданой длины
	 *
	 * @param int $length длина строки
	 *
	 * @return string
	 */
	public static function randomString($length = 6) {
		$str = "";
		$characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}

		return $str;
	}

	/**
	 * Генерирует случайный хэш заданной длины (бувквы, цифры, специальные знаки)
	 *
	 * @param int $length длина хэша
	 * @return string
	 */
	public static function randomHash(int $length = 60): string {
		return Str::random($length);
	}

	/**
	 * Генерирует строку для meta description
	 *
	 * @param int $string исходная строка
	 *
	 * @return string
	 */
	public static function getMetaDescription($string) {
		$string = str_replace('"', '', str_replace("'", "", strip_tags(html_entity_decode($string, ENT_QUOTES, "UTF-8"))));
		if (mb_strlen($string) > 300) {
			$string = Helper::truncateText($string, 300);
		}
		return $string;
	}

	/**
	 * Убирает из строки теги, недопустимые для хранения в базе и экранирует остальные
	 *
	 * @param int $string исходная строка
	 *
	 * @return string
	 */
	public static function getSqlHtmlString($string) {
		$string = self::clearStyle($string);
		$string = str_replace("&amp;nbsp;", ' ', htmlentities(strip_tags(stripslashes($string), '<p><i><strong><b><br><span><em><ol><ul><li><div>'), ENT_QUOTES, "UTF-8"));
		$string = str_replace("&amp;#92;", '&#92;', $string);
		return $string;
	}

	/**
	 * Закрыть открытые HTML-теги в строке
	 * @param $html
	 * @return bool|string
	 */
	public static function closeTags($html) {
		preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		$openedtags = $result[1];
		preg_match_all('#</([a-z]+)>#iU', $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		if (count($closedtags) == $len_opened) {
			return $html;
		}
		$openedtags = array_reverse($openedtags);
		for ($i = 0; $i < $len_opened; $i++) {
			if (!in_array($openedtags[$i], $closedtags)) {
				$html .= '</' . $openedtags[$i] . '>';
			} else {
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}

		return $html;
	}
	/**
	 * Очистка атрибута style у тегов
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function clearStyle($string) {
		return preg_replace('/(<[^>]+) style=["\'].*?["\']/i', '$1', $string);
	}

	/**
	 * Меняет местами значения двух переменных
	 *
	 * @return string
	 */
	public static function swap(&$variable1, &$variable2) {
		$temp = $variable1;
		$variable1 = $variable2;
		$variable2 = $temp;
	}

	/**
	 * Возвращает ключ массива с наименьшим значением
	 *
	 * @return string
	 */
	public static function minArrayKey(array $array) {
		$theKey = null;
		if (!empty($array)) {
			$minValue = $array[0];
			$theKey = 0;
			foreach ($array as $key => $value) {
				if ($value < $minValue) {
					$theKey = $key;
					$minValue = $value;
				}
			}
		}
		return $theKey;
	}

	/**
	 * Возвращает строку для лендинга
	 *
	 * @return string
	 */
	public static function getLandSeoString($string) {
		if (Translations::getLang() == Translations::EN_LANG) {
			return $string;
		}
		$firstLetter = mb_substr($string, 0, 1);
		$secondLetter = mb_substr($string, 1, 1);
		if ($firstLetter == mb_strtoupper($firstLetter) && $secondLetter == mb_strtoupper($secondLetter)) {
			return $string;
		} else {
			return mb_strtolower($firstLetter) . mb_substr($string, 1);
		}
	}

	public static function getLandInfSeoString($string) {
		$infArray = [
			'Как'
		];
		foreach ($infArray as $infEnd) {
			if (mb_strtolower(mb_substr(explode(' ', $string)[0], -mb_strlen($infEnd))) == mb_strtolower($infEnd)) {
				$string = mb_substr($string, mb_strlen($infEnd) + 1);
				$string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
				break;
			}
		}
		return $string;
	}

	public static function intArray(array $ids) {
		return array_map('intval', $ids);
	}

	public static function intArrayNoEmpty(array $ids) {
		return array_filter(self::intArray($ids));
	}

	public static function dateFormat($string, $format = null, $default_date = '', $formatter = 'auto', $mode = 'auto') {
		//if ($format === null) {
		//    $format = Smarty::$_DATE_FORMAT;
		//}
		if ($format === null) {
			$format = '%e %B %Y';
		}
		if (Translations::isDefaultLang()) {
			if ($formatter != 'strftime') {
				$formatter = 'rus';
			}
		} else {
			$format = '%B %e, %Y'; // Формат по умолчанию для английского сайта
		}
		/**
		 * require_once the {@link shared.make_timestamp.php} plugin
		 */
		require_once(SMARTY_PLUGINS_DIR . 'shared.make_timestamp.php');
		if ($string != '' && $string != '0000-00-00' && $string != '0000-00-00 00:00:00') {
			$timestamp = smarty_make_timestamp($string);
		} elseif ($default_date != '') {
			$timestamp = smarty_make_timestamp($default_date);
		} else {
			return;
		}
		if ($formatter == 'rus') {
			$months = array(
				1 => 'января',
				2 => 'февраля',
				3 => 'марта',
				4 => 'апреля',
				5 => 'мая',
				6 => 'июня',
				7 => 'июля',
				8 => 'августа',
				9 => 'сентября',
				10 => 'октября',
				11 => 'ноября',
				12 => 'декабря');

			if ($mode == 'balance') {
				$month = mb_substr($months[(int) date('m', $timestamp)], 0, 3);
				$format = str_replace(["%m", '%B'], $month, $format) . ', %R';
			} elseif ($mode == "short") {
				$monthsShort = array(
					1 => 'янв.',
					2 => 'февр.',
					3 => 'мар.',
					4 => 'апр.',
					5 => 'мая',
					6 => 'июн.',
					7 => 'июл.',
					8 => 'авг.',
					9 => 'сент.',
					10 => 'окт.',
					11 => 'нояб.',
					12 => 'дек.');
				$month = $monthsShort[(int) date('m', $timestamp)];
				$format = str_replace(["%m", '%B'], $month, $format);
			} else {
				$month = $months[(int) date('m', $timestamp)];
				$format = str_replace(["%m", '%B'], $month, $format);
			}

			$formatter = 'auto';
		}
		$timestamp = Timezone::setTimezoneInt($timestamp);
		if ($formatter == 'strftime' || ($formatter == 'auto' && strpos($format, '%') !== false)) {
			if (DIRECTORY_SEPARATOR == '\\') {
				$_win_from = array('%D', '%h', '%n', '%r', '%R', '%t', '%T');
				$_win_to = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
				if (strpos($format, '%e') !== false) {
					$_win_from[] = '%e';
					$_win_to[] = sprintf('%\' 2d', date('j', $timestamp));
				}
				if (strpos($format, '%l') !== false) {
					$_win_from[] = '%l';
					$_win_to[] = sprintf('%\' 2d', date('h', $timestamp));
				}
				$format = str_replace($_win_from, $_win_to, $format);
			}

			return strftime($format, $timestamp);
		} else {
			return date($format, $timestamp);
		}
	}

	public static function date($date, $format = "j M Y, H:i", $setTimeZone = true) {
		if (!$date) {
			return "";
		}

		$date = strlen($date) == 19 ? strtotime($date) : $date;

		if (date("Y", time()) == date("Y", $date)) {
			$format = str_replace(" Y", "", $format);
		} elseif ($date < time() - Helper::ONE_MONTH) {
			$format = str_replace(", H:i", "", $format);
		}

		if ($setTimeZone) {
			$date = Timezone::setTimezoneInt($date);
		}

		if(Translations::getLang() == Translations::EN_LANG) {
			$format = str_replace("j F", "F j", $format);
		}

		$date = date($format, $date);

		// русский месяц
		{
			if (Translations::isDefaultLang()) {
				$monthLongEn = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
				$monthLongRu = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
				$date = str_replace($monthLongEn, $monthLongRu, $date);

				$monthShortEn = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
				$monthShortRu = ["янв", "фев", "март", "апр", "мая", "июн", "июл", "авг", "сент", "окт", "ноя", "дек"];
				$date = str_replace($monthShortEn, $monthShortRu, $date);
			}
		}

		return $date;
	}

	/**
	 * Получить отформатированную дату по указанному языку
	 * @param int $date Метка даты
	 * @param string $format формат даты
	 * @param string $lang Язык
	 * @param bool $setTimeZone флаг "Установить зону"
	 * @param bool $forceFormat Не менять формат
	 * @return DateTime|false|int|mixed|string
	 */
	public static function dateByLang($date, $format = "j M Y, H:i", $lang = Translations::DEFAULT_LANG, $setTimeZone = true, $forceFormat = false) {
		if (!$date) {
			return "";
		}

		$date = strlen($date) == 19 ? strtotime($date) : $date;

		if (!$forceFormat) {
			if (date("Y", time()) == date("Y", $date)) {
				$format = str_replace(" Y", "", $format);
			} elseif ($date < time() - Helper::ONE_MONTH) {
				$format = str_replace(", H:i", "", $format);
			}
		}

		if ($setTimeZone) {
			$date = Timezone::setTimezoneInt($date);
		}

		$date = date($format, $date);

		// русский месяц
		{
			if ($lang === Translations::DEFAULT_LANG) {
				$monthLongEn = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
				$monthLongRu = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
				$date = str_replace($monthLongEn, $monthLongRu, $date);

				$monthShortEn = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
				$monthShortRu = ["янв", "фев", "март", "апр", "мая", "июн", "июл", "авг", "сент", "окт", "ноя", "дек"];
				$date = str_replace($monthShortEn, $monthShortRu, $date);
			}
		}

		return $date;
	}

	public static function buildQuery($queryString, $add = false, $remove = false) {
		$query = [];
		parse_str($queryString, $query);

		if (is_array($remove) && count($remove)) {
			foreach ($remove as $item) {
				unset($query[$item]);
			}
		}

		if ($add) {
			if (is_array($add)) {
				$arAdd = $add;
			} else {
				parse_str($add, $arAdd);
			}

			foreach ($arAdd as $key => $item) {
				$query[$key] = $item;
			}
		}

		$result = http_build_query($query);
		$result = $result ? $result : '';

		return $result;
	}

	/**
	 * Перемешивает элементы массива в случайном порядке
	 * @param array $array Массив для перемешивания
	 * @param string        $stricted          перемешивание происходит внутри элементов, совпадающих по значениям с указанным полем
	 * @param bool        $isObjects          являются ли элементы массива объектами
	 * @return array
	 */
	public static function arrayShuffle($array, $stricted = '', $isObjects = false) {
		$shuffleKeysGroups = [];
		if (empty($stricted)) {
			$shuffleKeysGroups = [array_keys($array)];
		} else {
			foreach ($array as $key => $item) {
				if ($isObjects) {
					$shuffleKeysGroups[$item->{$stricted}][] = $key;
				} else {
					$shuffleKeysGroups[$item[$stricted]][] = $key;
				}
			}
		}
		foreach ($shuffleKeysGroups as $group) {
			foreach ($group as $groupKey) {
				$randomKey = $group[rand(0, count($group) - 1)];
				if ($groupKey != $randomKey) {
					self::swap($array[$groupKey], $array[$randomKey]);
				}
			}
		}
		return $array;
	}

	/**
	 * Чистит строку от эмоджи, которые могут не записаться в базу и сломать сообщение
	 *
	 * @param string $string
	 *
	 * @return mixed
	 */
	public static function clearEmoji($string) {
		return preg_replace('/[' .
			self::uniChr(0x1F600) . '-' . self::uniChr(0x1F64F) .
			self::uniChr(0x1F300) . '-' . self::uniChr(0x1F5FF) .
			self::uniChr(0x1F680) . '-' . self::uniChr(0x1F6FF) .
			self::uniChr(0x2600) . '-' . self::uniChr(0x26FF) .
			self::uniChr(0x2700) . '-' . self::uniChr(0x27BF) .
			']/u', '', $string);
	}

	private static function uniChr($i) {
		return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
	}

	/**
	 * Проверяет URL в BB-коде для защиты от XSS.
	 * Пропускаются только валидные адреса, со схемой http(s), содержащие допустимые символы.
	 * Для доменов, содержащих слово kwork, более строгие правила для входящих в адрес символов.
	 *
	 * @param string $url Проверяемый url.
	 *
	 * @return bool Возвращает true, если проверяемую строку можно использовать как url, и false, если нет.
	 */
	private static function checkBBUrl($url): bool {
		$parsedUrl = parse_url($url);

		if (!$parsedUrl) {
			return false;
		}

		if (empty($parsedUrl["host"])) {
			return false;
		}

		if (strpos($parsedUrl["host"], "kwork") !== false) {
			$reURL = "/^https?:\/\/[a-zа-яё0-9:\?#\/._\-=&]+$/i";
		} else {
			$reURL = "/^https?:\/\/[a-zа-яё0-9:\?#\/._\-=&%+,\(\)\[\]]+$/i";
		}

		if (!preg_match($reURL, $url)) {
			return false;
		}

		return true;
	}

	/**
	 * Возвращает true если BB URL содержит внешнюю ссылку,
	 * которая в последствии не обрабатывается методом @see Helper::replaceBBCode()
	 *
	 * @param string $string
	 * @return bool
	 */
	public static function isBBUrlHasExternalLink(string $string): bool {
		if (strpos($string, "[url=") === false) {
			return false;
		}

		$result = false;
		if (preg_match_all("(\[url=(.+?)\](.+?)\[/url\])is", $string, $matches)) {
			foreach ($matches[1] as $url) {
				if (!self::checkBBUrl($url)) {
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Заменяет в тексте некоторые BB-коды на их HTML-аналоги.
	 *
	 * @param string $text Текст.
	 * @param bool $replaceNewLines - true если нужно ли заменять переносы на <br>
	 *
	 * @return string Возвращает модифицированный текст.
	 */
	public static function replaceBBCode($text, bool $replaceNewLines = true) {
		$text = preg_replace_callback_array([
			"(\[b\](.+?)\[/b\])is" => function($matches) {
				return "<b>" . $matches[1] . "</b>";
			},
			"(\[i\](.+?)\[/i\])is" => function($matches) {
				return "<i>" . $matches[1] . "</i>";
			},
			"(\[size=([0-9]+)\](.+?)\[/size\])is" => function($matches) {
				return "<span style=\"font-size:" . intval($matches[1]) . "px\">" . $matches[2] . "</span>";
			},
			"(\[color=([a-z]+)\](.+?)\[/color\])is" => function($matches) {
				return "<span style=\"color:" . htmlspecialchars($matches[1], ENT_QUOTES) . "\">" . $matches[2] . "</span>";
			},
		], $text);

		$text = preg_replace_callback("(\[img\](.+?)\[/img\])is", function ($matches) {
			if (self::checkBBUrl($matches[1])) {
				return " <img src=\"" . $matches[1] . "\" alt=\"\"> ";
			} else {
				return $matches[0];
			}
		}, $text);

		$text = preg_replace_callback("(\[url=(.+?)\](.+?)\[/url\])is", function ($matches) {
			if (self::checkBBUrl($matches[1])) {
				return "<noindex><a href=\"" . $matches[1] . "\" target=\"_blank\" rel=\"nofollow\">" . $matches[2] . "</a></noindex>";
			} else {
				return $matches[0];
			}
		}, $text);

		if ($replaceNewLines) {
			$text = str_replace("\r\n", "\n", $text);
			$text = str_replace("\n", "<br>", $text);
		}

		return $text;
	}

	/**
	 * Заменить в тексте bbcode ссылки с доменами kwork.ru, kwork.com на html теги
	 *
	 * @param string $text текст
	 * @return string
	 */
	public static function replaceBBCodeKworkUrl($text) {
		$text = preg_replace_callback('/\[url=(https?:\/\/(?:(?:www|dev|d[0-9]+)\.)?(?:'.App::config("rudomain").'|'.App::config("endomain").')\/[^"]+?)\]([^\[]+?)\[\/url\]/si', function ($matches) {
			if (self::checkBBUrl($matches[1])) {
				return "<a href=\"" . $matches[1] . "\" target=\"_blank\">" . $matches[2] . "</a>";
			} else {
				return $matches[0];
			}
		}, $text);

		return $text;
	}

	/**
	 * Конвертирует некоторые html теги в BB-код
	 * @param string $string
	 * @return string
	 */
	public static function htmlToBBCode(string $string): string {
		$string = str_replace("<br>", "\n", $string);
		$string = strip_tags($string, "<b><i><a>");
		$string = preg_replace('#<(/?)(b|i)>#', '[$1$2]', $string);
		$string = preg_replace('#<a[^>]* href="([^"]+)"[^>]*>([^<]+)</a>#',"[url=$1]$2[/url]", $string);

		return $string;
	}

	public static function clearXmlStr($s) {
		$s = html_entity_decode($s);
		$s = str_replace("\r", " ", $s);
		$s = str_replace("\n", " ", $s);
		$s = str_replace("\t", " ", $s);
		$s = str_replace("", " ", $s);
		$s = preg_replace("/(<[^>]+>)/i", " ", $s);
		$s = preg_replace("/[ ]+/i", " ", $s);
		$s = trim($s);

		$a['"'] = '&quot;';
		$a['&'] = '&amp;';
		$a['>'] = '&gt;';
		$a['<'] = '&lt;';
		$a["'"] = '&apos;';
		$s = str_replace(array_keys($a), array_values($a), $s);

		$s = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $s);

		return $s;
	}

	public static function getArrayFromFiles($arFiles) {
		if (empty($arFiles)) {
			return [];
		}
		$result = [];
		foreach ($arFiles['name'] as $key => $fileName) {
			if ($arFiles['error'][$key] != 0 || $fileName == '') {
				continue;
			}
			$result[] = [
				'name' => $fileName,
				'type' => $arFiles['type'][$key],
				'size' => $arFiles['size'][$key],
				'tmp_name' => $arFiles['tmp_name'][$key]
			];
		}
		return $result;
	}

	/**
	 * Return clean html string without empty tags with nested
	 * @param string $htmlString
	 * @return mixed
	 */
	public static function removeEmptyNestedTags($htmlString) {
		$pattern = '/<([^>\s]+)[^>]*>(?:\s*(?:<br \/>|&nbsp;|&thinsp;|&ensp;|&emsp;|&#8201;|&#8194;|&#8195;)\s*)*<\/\1>/';
		while (preg_match($pattern, $htmlString)) {
			$htmlString = preg_replace($pattern, '', $htmlString);
		}
		return $htmlString;
	}

	/**
	 * Удалить пустые строки в html верстке редактора
	 * @param string $htmlString html верстка редактора
	 * @param bool $isEncodedHtml $htmlString экранированная строка через htmlentities
	 * @return string отформатированная html строка
	 */
	public static function removeEmptyLines($htmlString, $isEncodedHtml = false) {
		if ($isEncodedHtml) {
			$htmlString = html_entity_decode($htmlString);
		}

		$ps = array_map('trim', explode('<p>', $htmlString));
		$set = array();
		foreach ($ps as $pBlock) {
			$pBlock = preg_replace('!((<br(\s+[^>]*)?>)*)(</p(\s+[^>]*)?>)!', '$4$1', $pBlock);
			$pBlock = preg_replace('!(<br(\s+[^>]*)?>)+$!', '', $pBlock);
			$pBlock = preg_replace('!</p>!', '', $pBlock);
			if (!empty($pBlock)) {
				foreach (explode('<br>', $pBlock) as $addBlock) {
					if (empty($addBlock)) {
						$addBlock = '<br>';
					}
					$set[] = $addBlock;
				}
			}
		}
		$set = array_filter(array_map('trim', $set));

		$last = false;
		foreach ($set as $num => $value) {
			if ($last === false) {
				$last = $value;
				continue;
			} elseif ($last == $value && strip_tags($last) == '') {
				unset($set[$num]);
			} else {
				$last = $value;
			}
		}

		$return = '<p>' . implode('</p><p>', $set) . '</p>';
		if ($isEncodedHtml) {
			$return = htmlentities($return);
		}
		return $return;
	}

	public static function isFieldDb($field) {
		if (!empty($field) && preg_replace("/[^a-z0-9_\.*]/i", "", $field) == $field) {
			return true;
		}

		return false;
	}

	/**
	 * Возвращает исходную строку длиной не более <i>$lenght</i> символов.<br/>
	 * Если исходная строка более <i>$lenght</i> символов возвращаются первые
	 * <i>$lenght</i> символов и троеточие
	 *
	 * @todo: метод назван неудачно, надо что-нибудь вроде getTruncatedText.
	 *
	 * @param string $text Исходная строка любой длины
	 * @param int $lenght Максимально допустимая строка.
	 * @return string
	 */
	public static function getTextLength($text, $lenght) {
		$printText = $text;
		if (mb_strlen($printText, 'utf-8') > $lenght) {
			$printText = mb_substr($printText, 0, $lenght, 'utf-8') . '...';
		}
		return $printText;
	}

	/**
	 * Дата/время в формате mysql
	 * @param int|false $time Время|текущее время
	 * @param string|false $format Формат даты|формат mysql
	 * @return string
	 */
	public static function now($time = false, $format = false) {
		if (!$time)
			$time = time();
		if (!$format)
			$format = 'Y-m-d H:i:s';
		return date($format, $time);
	}

	/**
	 * Форматирует дату в строку, пригодную для использования в MySQL в полях типа DateTime
	 * @param DateTime $date - Дата
	 * @return string - Дата в формате "Y-m-d H:i:s"
	 */
	public static function dateTimeToMysqlString(DateTime $date): string {
		return $date->format("Y-m-d H:i:s");
	}

	/**
	 * Форматирует дату в строку, пригодную для использования в MySQL в полях типа Date
	 * @param DateTime $date - Дата
	 * @return string - Дата в формате "Y-m-d"
	 */
	public static function dateTimeToMysqlDateString(DateTime $date): string {
		return $date->format("Y-m-d");
	}

	/**
	 * Отправка письма разработчику, где описывается возможная проблема, если ничего не будет предпринято
	 * @param string $mailText Текст письма
	 * @param string|false $email Кому отправлять письма| Всем
	 */
	public static function sendHintMailToDeveloper($mailText, $email = false) {
		$developersEmailList = App::config('mail.developer_emails') ?? [];
		if (!empty($email)) {
			$developersEmailList = (array) $email;
		}

		$builder = new \Builder\Letter\Developer\HitToDeveloper($mailText);
		foreach ($developersEmailList as $_email) {
			$builder->setEmail($_email);
			MailSender::send($builder->build());
		}
	}

	/**
	 * Является ли обращение из консоли (в 99% крон)
	 * @return boolean true - да; false - нет
	 */
	public static function isConsoleRequest() {
		if (isset($_SERVER['argv']) && !empty($_SERVER['argv']))
			return true;
		return false;
	}

	/**
	 * Находится ли данный урл в списке не обрабатываемых функцией strict_fields
	 *
	 * @return bool
	 */
	private static function isInNoStrictFieldsUrl() {
		foreach (self::$notStrictFieldsisUrl as $url) {
			if (strpos($_SERVER["REQUEST_URI"], $url) !== false)
				return true;
		}
		return false;
	}

	/**
	 * Нужно ли обрабатывать данные функцией strict_fields
	 * @return boolean true - да; false - нет
	 */
	public static function needStrictFieldsis() {
		if (self::isConsoleRequest() || self::isInNoStrictFieldsUrl())
			return false;
		return true;
	}

	/**
	 * Является ли это ajax запросом
	 * @return boolean true - да; false - нет
	 */
	public static function isAjaxRequest() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	 * Получить массив значений массива по колонке
	 * @param array $array исходный массив
	 * @param string $columName название ключа
	 * @return array $resArr массив значений
	 */
	public static function getArrayByColumn($array, $columName) {
		if (!empty($array)) {
			$resArr = [];
			foreach ($array as $item) {
				if ($item[$columName] != null) {
					$resArr[] = $item[$columName];
				}
			}
			return !empty($resArr) ? array_unique($resArr) : null;
		}
		return null;
	}

	// возвращает из массива список из колонки по названию
	public static function getIdsStringByColumn($array, $column) {
		$ids = [];
		foreach ($array as $item) {
			if (is_array($item)) {
				if ($item[$column])
					$ids[] = (int) $item[$column];
			}
			else {
				if ($item->$column)
					$ids[] = (int) $item->$column;
			}
		}
		$ids = array_unique($ids);

		return implode(",", $ids);
	}

	/**
	 * Записать в файл и выставить права на файл
	 * @param string $putFile Имя файла
	 * @param string $fileData Записываемые данные
	 * @param int $flags Флаги записи
	 * @return mixed file_put_contents
	 */
	public static function filePutContents($putFile, $fileData, $flags = 0) {
		$resetRules = false;
		if (!file_exists($putFile)) {
			$resetRules = true;
		}
		// @todo: как это - !file_exists и дальше file_put_contens? А если в пути файла папка, которой нет?
		$return = file_put_contents($putFile, $fileData, $flags);
		if ($resetRules) {
			chmod($putFile, 0666);
		}
		return $return;
	}

	/**
	 * Валидный ли ip
	 * @param string $ip IP адрес
	 * @return mixed preg_match
	 */
	public static function isValidIp($ip) {
		return preg_match('!^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$!i', $ip);
	}

	/**
	 * Получить ip нужной длины
	 * @param string $ip
	 * @param int $numbs сколько чисел в ip оставить
	 * @return string
	 */
	public static function getPartForIp(string $ip, int $numbs): string {
		$numbsOfSubnet = explode(".", $ip);
		if (!$numbsOfSubnet) {
			return $ip;
		}
		$numbsOfSubnet = array_slice($numbsOfSubnet, 0, $numbs);
		$subnet = implode('.', $numbsOfSubnet);

		return $subnet;
	}

	/**
	 * Получить полный урл на картинку (CDN) по ее названию
	 *
	 * @param string $imageName название картинки
	 * @return string урл
	 */
	public static function cdnImageUrl($imageName) {
		static $url;
		if (!$url) {
			$url = App::config("imageurl");
		}
		return $url . $imageName;
	}

	/**
	 * Получить имя файла со временем его изменения
	 * @param string $filePath Путь до файла
	 * @return string $filePath с временной меткой, если возможно
	 */
	public static function fileWithTime(string $filePath): string {
		if ($filePath[0] === "/") {
			if ($filePath[1] === "/") { // Если путь начинается с //, значит это ссылка на внешний ресурс
				return $filePath;
			}
			$projectFilePath = $filePath;
		} elseif (0 === strpos($filePath, $url = App::config("baseurl"))
			|| 0 === strpos($filePath, $url = App::config("cdn.base_url"))) {
			$projectFilePath = substr($filePath, strlen($url));
		} else {
			return $filePath;
		}
		// |-----o костыль для js админки
		//файлы админки лежит в /action
		if (strpos($projectFilePath, "/administrator/") === 0) {
			$projectFilePath = "/action" . $projectFilePath;
		}
		// |-----o

		$realFilePath = DOCUMENT_ROOT . $projectFilePath;
		if (!file_exists($realFilePath)) {
			// ToDo: После тестирования можно убрать логирование
			\Log::write(sprintf("%s %s - file not found: %s", $_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"], $realFilePath), "static_not_found");
			return $filePath;
		}
		$changeFileTime = filemtime($realFilePath);
		if (empty($changeFileTime)) {
			return $filePath;
		}
		return $filePath . "?ver=" . $changeFileTime;
	}

	/**
	 * Получить HTML тэг link с абсолютным путем в атрибуте href и версией файла
	 *
	 * @param string $filePath относительный путь к файлу
	 * @param string $media значение атрибтуа media у тэга link
	 * @return string Готовый тэг link
	 */
	public static function printCssFile(string $filePath, string $media = "all") {
		return "<link rel=\"stylesheet\" href=\"" . Helper::fileWithTime($filePath) . "\" type=\"text/css\"" . ($media ? " media=\"$media\"" : "") . ">";
	}

	/**
	 * Получить HTML тэг script с абсолютным путем в атрибуте src и версией файла
	 *
	 * @param string $filePath относительный путь к файлу
	 * @param bool $isDefer определяет, добавлять атрибут defer к тэгу или нет
	 * @return string Готовый тэг script
	 */
	public static function printJsFile(string $filePath, $isDefer = false) {
		return "<script src=\"" . Helper::fileWithTime($filePath) . "\" type=\"text/javascript\"" . ($isDefer ? " defer" : "") . "></script>";
	}

	/**
	 * Зарегистрировать подключение CSS в футере
	 * @param string $cssLink Путь до CSS файла
	 */
	public static function registerFooterCssFile($cssLink) {
		if (isset(self::$registerCssList[$cssLink]) && self::$registerCssList[$cssLink] === false) {
			return false;
		}
		self::$registerCssList[$cssLink] = true;
	}

	/**
	 * Зарегистрировать подключение JS в футере
	 * @param string $jsLink Путь до JS файла
	 */
	public static function registerFooterJsFile($jsLink) {
		if (isset(self::$registerJsList[$jsLink]) && self::$registerJsList[$jsLink] === false) {
			return false;
		}
		self::$registerJsList[$jsLink] = true;
	}

	/**
	 * Получить список CSS файлов.
	 * @return string HTML-код
	 */
	public static function printCssFiles() {
		$cssFileTemplate = '<link rel="stylesheet" href="{filePath}" type="text/css" />';
		$return = array();
		foreach (self::$registerCssList as $cssLink => $allowAdd) {
			if ($allowAdd) {
				self::$registerCssList[$cssLink] = false;
				$return[] = strtr($cssFileTemplate, [
					'{filePath}' => Helper::fileWithTime($cssLink),
				]);
			}
		}
		return implode("\n", $return);
	}

	/**
	 * Получить список JS файлов
	 * @return string HTML-код
	 */
	public static function printJsFiles(array $onlyPrintList = array()) {
		global $smarty;
		$pageSpeedDesktop = false;
		if ($smarty->getTemplateVars('pageSpeedDesktop')) {
			$pageSpeedDesktop = true;
		}
		$jsFileTemplate = "<script src=\"{filePath}\" type=\"text/javascript\"" . ($pageSpeedDesktop ? " defer" : "") . "></script>";
		$return = array();
		if (empty($onlyPrintList)) {
			$onlyPrintList = array_keys(self::$registerJsList);
		}
		foreach ($onlyPrintList as $jsLink) {
			if (self::$registerJsList[$jsLink]) {
				self::$registerJsList[$jsLink] = false;
				$return[] = strtr($jsFileTemplate, [
					"{filePath}" => Helper::fileWithTime($jsLink),
				]);
			}
		}
		return implode("\n", $return);
	}

	public static function getZeroNumber($number, $zeroCount = 5) {
		return str_pad($number, $zeroCount, '0', STR_PAD_LEFT);
	}

	/**
	 * Проверка формата md5-хеша
	 * @param string $hash проверяемая строка
	 * @return int preg_match соответствует ли строка шаблону
	 */
	public static function isMd5($hash) {
		return preg_match('!^[0-9a-f]{32}$!i', $hash) ? true : false;
	}
	/**
	 * Получить сколько используется оперативной памяти (с Мб)
	 * @param string $textLabel Текст перед количеством используемой памяти
	 * @return boolean false если не выведено
	 */
	public static function memoryUsage($textLabel = false) {
		$mb = memory_get_usage() / (1024 * 1024);
		$return = '';
		if ($textLabel)
			$return = $textLabel . ': ';
		return $return . round($mb, 3);
	}

	/**
	 * Проверить от чьего имени запускает файл и кто владелец запускаемого файла
	 */
	public static function checkProcessOwner() {
		if (!App::isProductionMode()) {
			return false;
		}

		$mustUser = App::config('app.must_user');

		if (self::isConsoleRequest()) {
			$callScript = $_SERVER['argv'][0];
			$callType = 'console';
		} else {
			$callScript = $_SERVER['SCRIPT_FILENAME'];
			$callType = 'nginx';
		}
		if (!empty($callScript) && file_exists($callScript)) {
			$scriptOwner = posix_getpwuid(fileowner($callScript));
			if ($scriptOwner['name'] != 'kwork') {
				Log::daily("checkProcessOwner: {$callScript}. callType: {$callType}; scriptOwner: {$scriptOwner['name']}; must user: kwork", 'error');
			}
		}

		$currentExecuter = posix_getpwuid(posix_geteuid());
		if ($currentExecuter['name'] != $mustUser) {
			Log::daily("checkProcessOwner: {$callScript}. callType: {$callType}; currentExecuter: {$currentExecuter['name']}; must user: {$mustUser}", 'error');
		}
	}

	public static function SSCardExpireDate($expire) {
		if (!$expire) {
			return '';
		}
		$array = explode('/', $expire);
		$monthArray = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
		$month = intval($array[0]) - 1;

		return $monthArray[$month] . ' ' . 20 . $array[1];
	}

	/**
	 * Соединить элементы массива в строку, используя $separator как разделитель
	 * @param array $array массив
	 * @param string $separator разделитель
	 * @return string
	 */
	public static function arrayToString(array $array, string $separator = ','): string {
		return implode($separator, $array);
	}

	/**
	 * Получить форму слова по числу
	 * @param int $number Число
	 * @param string $formOne Форма для 1
	 * @param string $formFew Форма для 2-4
	 * @param string $formMany Форма 5-9
	 * @return string|null
	 */
	public static function pluralNumber($number, $formOne, $formFew, $formMany) {
		if ($number % 10 == 1 && $number % 100 != 11)
			return $formOne;
		if ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 12 || $number % 100 > 14))
			return $formFew;
		if ($number % 10 == 0 || ($number % 10 >= 5 && $number % 10 <= 9) || ($number % 100 >= 11 && $number % 100 <= 14))
			return $formMany;
	}

	/**
	 * Получить название месяца по номеру
	 * @param int $number - номер месяца
	 * @param bool $firsUpperCase - в верхнем ли регистре первую букву
	 * @param string $lang - язык на котором получать название. По дефолту русский
	 * @return string
	 */
	public static function getMonthNameByNumber($number, $firsUpperCase = false, $lang = "ru") {
		$lang = strtolower($lang);
		$monthArray = [
			1 => $lang == "ru" ? "январь" : "january",
			2 => $lang == "ru" ? "февраль" : "february",
			3 => $lang == "ru" ? "март" : "march",
			4 => $lang == "ru" ? "апрель" : "april",
			5 => $lang == "ru" ? "май" : "may",
			6 => $lang == "ru" ? "июнь" : "june",
			7 => $lang == "ru" ? "июль" : "july",
			8 => $lang == "ru" ? "август" : "august",
			9 => $lang == "ru" ? "сентябрь" : "september",
			10 => $lang == "ru" ? "октябрь" : "october",
			11 => $lang == "ru" ? "ноябрь" : "november",
			12 => $lang == "ru" ? "декабрь" : "december"
		];

		return $firsUpperCase ? mb_ucfirst($monthArray[$number]) : $monthArray[$number];
	}

	/**
	 * Является ли дата нерабочим временем (с 18:00 пятницы по 8:00 понедельника)
	 * @param DateTime|null $date - дата
	 * @return boolean
	 */
	public static function isWeekends($date = null) {
		$timestamp = ($date) ? $date->getTimestamp() : time();
		return self::isWeekendTime($timestamp);
	}

	/**
	 * Является ли метка времени нерабочим временем (с 18:00 пятницы по 8:00 понедельника)
	 * @param int $timestamp - метка времени
	 * @return boolean
	 */
	public static function isWeekendTime($timestamp): bool {
		$dayNumber = (int)date('N', $timestamp);
		if ($dayNumber > 5) { // суббота и воскресенье
			return true;
		}
		$dayHour = (int)date('H', $timestamp);
		if ($dayNumber === 5 && $dayHour >= 18) { // пятница с 18:00
			return true;
		}
		if ($dayNumber === 1 && $dayHour < 8) { // понедельник до 8:00
			return true;
		}
		return false;
	}

	/**
	 * Возвращает склоненную строку по количеству оставшегося времени до принятие заказа в работу
	 *
	 * @param int $mode формат вывода строки
	 * @param bool $orderRestarted заказ переоткрыт
	 * @param null|int $hours колтичество часво, для которых необходимо вырнуть склоенненную строку.
	 * @return mixed|string
	 */
	public static function autoCancelString($mode, $orderRestarted = false, $hours = null) {
		if ($hours === null) {
			$hours = self::getAutoChancelHours($orderRestarted);
		}
		$days = self::getAutoCancelDays($orderRestarted);
		if ($mode == self::AUTOCANCEL_MODE_DB) {
			return $hours . " HOUR";
		} elseif ($mode == self::AUTOCANCEL_MODE_TEXT) {
			return $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]);
		} elseif ($mode == self::AUTOCANCEL_MODE_TEXT_IN) {
			return $hours . " " . declension($hours, [Translations::t("часа"), Translations::t("часов"), Translations::t("часов")]);
		} elseif ($mode == self::AUTOCANCEL_MODE_IN_DAYS) {
			return $days . " " .  Helper::pluralNumber($days, Translations::t("день"), Translations::t("дня"), Translations::t("дней"));
		} elseif ($mode == self::AUTOCANCEL_MODE_IN_DAYS_IN) {
			return $days . " " .  Helper::pluralNumber($days, Translations::t("дня"), Translations::t("дней"), Translations::t("дней"));
		}
		return $hours;
	}

	/**
	 * Выводит сообщение о количестве часов с момента заказа, когда у продавца появляется уведомление о необходимости взять заказ в работу
	 * @return string
	 */
	public static function inworkHoursString() {
		$hours = App::config("kwork.need_worker_new_inwork_hours");

		return $hours . " " . declension($hours, [Translations::t("часа"), Translations::t("часов"), Translations::t("часов")]);
	}

	/**
	 * Просрочен ли ответ на сообщение
	 *
	 * @param int $timestamp метка времени сообщения
	 * @param boolean $includeWeekends учитывать выходные (по умолчанию true)
	 * @param int $hoursLimit допустимое количество часов для ответа
	 * @param int $endTime метка времени, до которой будут считаться часы
	 *
	 * @return boolean true - сообщение просрочено, false - сообщение не просрочено
	 */
	public static function isDialogOverdue($timestamp, $includeWeekends = true, $hoursLimit = null, $endTime = null) {
		if ($hoursLimit === null) {
			$hoursLimit = self::getAutoChancelHours();
		}
		if ($endTime === null) {
			$endTime = time();
		}

		// подсчёт количества прошедших "рабочих" часов до текущего момента
		$elapsedHours = 0;
		if ($includeWeekends) {
			$elapsedHours = floor(($endTime - $timestamp) / Helper::ONE_HOUR);
		} else {
			// если сообщение отправлено в выходные - ведём отсчёт от понедельника 8:00
			if (Helper::isWeekendTime($timestamp)) {
				$dateTime = new \DateTime();
				$dateTime->setTimestamp($timestamp);
				if (date('N', $timestamp) > 1) {
					$dateTime->modify('next monday');
				}
				$dateTime->setTime(8,0);
				$timestamp = $dateTime->getTimestamp();
			}
			while ($timestamp < $endTime && $elapsedHours < $hoursLimit) {
				$timestamp += self::ONE_HOUR;
				if (false == Helper::isWeekendTime($timestamp)) {
					$elapsedHours++;
				}
			}
		}

		return ($elapsedHours >= $hoursLimit);
	}

	/**
	 * Возвращает метку времени, до которой нужно дать ответ на сообщение
	 *
	 * @param int $timestamp метка времени сообщения
	 * @param boolean $includeWeekends учитывать выходные (по умолчанию true)
	 * @param int $hoursLimit допустимое количество часов для ответа
	 *
	 * @return int метка времени, до которого необходимо дать ответ на сообщение
	 */
	public static function getAnswerTimestamp($timestamp, $includeWeekends = true, $hoursLimit = null) {
		if ($hoursLimit === null) {
			$hoursLimit = self::getAutoChancelHours();
		}

		if ($includeWeekends) {
			$timestamp += self::ONE_HOUR * $hoursLimit;
		} else {
			// если сообщение отправлено в выходные - ведём отсчёт от понедельника 8:00
			if (Helper::isWeekendTime($timestamp)) {
				$dateTime = new \DateTime();
				$dateTime->setTimestamp($timestamp);
				if (date('N', $timestamp) > 1) {
					$dateTime->modify('next monday');
				}
				$dateTime->setTime(8,0);
				$timestamp = $dateTime->getTimestamp();
			}
			$elapsedHours = 0;
			while ($elapsedHours < $hoursLimit) {
				$timestamp += self::ONE_HOUR;
				if (false == Helper::isWeekendTime($timestamp)) {
					$elapsedHours++;
				}
			}
		}

		return $timestamp;
	}

	/**
	 * Сгрупировать массив
	 * @param array $rows Исходный массив
	 * @param string $keyColumn Поле групировки
	 * @param mixed $valueColumn Значения групы. false - весь массив
	 * @return array Исхдный массив, разбитый на подмасивы. ключи $keyColumn
	 */
	public static function groupArray(array $rows, $keyColumn, $valueColumn = false) {
		$return = array();
		foreach ($rows as $row) {
			if (!isset($row[$keyColumn])) {
				continue;
			}
			if ($valueColumn !== false && !isset($row[$valueColumn])) {
				continue;
			}
			if (empty($return[$row[$keyColumn]])) {
				$return[$row[$keyColumn]] = array();
			}
			if ($valueColumn !== false) {
				$return[$row[$keyColumn]][] = $row[$valueColumn];
			} else {
				$return[$row[$keyColumn]][] = $row;
			}
		}
		return $return;
	}

	/**
	 * Отформатировать текст: удалить абзацы, добавить пробелы
	 * @param string $text Текст
	 * @return string Отформатированный $text
	 */
	public static function formatText($text) {
		$punctuationMarks = ',!?:;';
		$text = preg_replace(RegexpPatternManager::REGEX_URL, '{{{$0}}}', $text); //Обернуть ссылки в {{{url}}}

		$specialCases = array_merge(RegexpPatternManager::DOMAIN_ZONES, RegexpPatternManager::NO_SPACE_AFTER_DOT);
		$specialCasePattern = "((\.)(?!" . implode("\b|", $specialCases) . "\b))";
		$text = preg_replace('/([\.]{3}|[' . preg_quote($punctuationMarks, '!') . ']\b!([A-Z]:)|' . $specialCasePattern . ')[\f\t ]*/i', '$1 ', $text);

		// удалить пробел после "www." в имени домена
		$fixWwwPattern = "/(www\.)\s([-\w]+\.)(?=".implode("|", RegexpPatternManager::DOMAIN_ZONES).")/ui";
		$text = preg_replace($fixWwwPattern, "$1$2$3", $text);

		//Если в тексте попадается цифры-версии (eq 2.3), то убираем пробел после точки
		$text = preg_replace('/(\d{1,})(\.) (\d{1,})/', '$1$2$3', $text);
		$text = preg_replace_callback('!({{{(.*)}}})!U', //Удалить пробелы у ссылок
			function($matches) {
				return str_replace(' ', '', $matches[2]);
			}, $text);
		$text = preg_replace("/&nbsp;[\s]/", "&nbsp;", $text);
		$text = preg_replace("/\xC2\xA0/", " ", $text);
		return $text;
	}

	/**
	 * Отформатировать текст для попапа нового портфолио: удалить абзацы, добавить пробелы
	 * @param string $text Текст
	 * @return string Отформатированный $text
	 */
	public static function formatTextPortfolio($text) {
		$punctuationMarks = '.,!?:;';
		$text = preg_replace(RegexpPatternManager::REGEX_URL, '{{{$0}}}', $text); //Обернуть ссылки в {{{url}}}

		$text = preg_replace('!([\.]{3}|[' . preg_quote($punctuationMarks, '!') . '])[\f\t ]*!', '$1 ', $text);
		//Если в тексте попадается цифры-версии (eq 2.3), то убираем пробел после точки
		$text = preg_replace('/(\d{1,})(.) (\d{1,})/', '$1$2$3', $text);
		$text = preg_replace_callback('!({{{(.*)}}})!U', //Удалить пробелы у ссылок
			function($matches) {
				return str_replace(' ', '', $matches[2]);
			}, $text);
		$text = preg_replace("/\xc2\xa0/", " ", $text);
		return $text;
	}

	/**
	 * Сделать заглавную букву в html тексте
	 * @param string $text
	 * @return string
	 */
	public static function firstUpHtml($text) {
		$tempText = self::replaceBBCode($text);
		$firstChar = mb_substr(strip_tags($tempText), 0, 1);
		if (mb_ucfirst($firstChar) == $firstChar) {
			return $text;
		}
		$text = preg_replace("/$firstChar/", mb_ucfirst($firstChar), $text, 1);
		return $text;
	}

	public static function moneyRound($money) {
		return round($money * 100) / 100.0;
	}

	/*
	 * Получить из строки все ссылки в виде массива
	 * @param string $string
	 * @return array
	 */
	public static function getLinksFromString($string) {
		preg_match_all(RegexpPatternManager::REGEX_URL, $string, $matches);
		return array_map("trim", $matches[0]);
	}

	/**
	 * Удаляет из строки baseurl, делая из абсолютной ссылки относительную
	 * @param string $string
	 * @return string
	 */
	public static function makeLinkInternal(string $string): string {
		// убедимся что в baseurl нет слеша на конце
		if(preg_match("/\/$/", App::config('baseurl'))) {
			$replace = "/";
		} else {
			$replace = "";
		}

		return str_replace(App::config('baseurl'), $replace, $string);
	}

	/**
	 * Возвращает array всегда
	 *
	 * @param array|bool $pdoResult
	 *
	 * @return array
	 */
	public static function toArray($pdoResult): array
	{
		if (is_array($pdoResult)) {
			return $pdoResult;
		}
		return [];
	}

	public static function zero($price, $digit = 2, $space = true)
	{
		if(!$price)
			return $price;

		// если копейки не нужны, то округляю по правилам арифметики
		if($digit == 0)
			$price = round($price);

		$space = $space ? " " : "";

		$price = number_format((double)$price, $digit, ".", "");

		// если копеек нет, то их не показываю
		if ((int)$price == (float)$price) {
			$price = number_format((double)$price, 0, ".", $space);
		} else {
			$price = number_format((double)$price, $digit, ".", $space);
		}

		return $price;
	}

	/**
	 * Возвращает строку содержающую html, обрезанную до заданного кол-ва символов с учетом html_entry.
	 * @param $html
	 * @param $symbolsLimit
	 * @param $charLimit
	 * @return string
	 */
	public static function trimHtmlWidth($html, $symbolsLimit, $charLimit) {
		$html = mb_ereg_replace("\r\n", "\n", $html);
		$html = html_entity_decode($html);
		$html = mb_strimwidth($html,0,$symbolsLimit);
		$html = cleanit($html);
		$html = mb_strimwidth($html,0,$charLimit);
		//удаляем побитые html entities в конце
		if (mb_strlen($html) === $charLimit) {
			$html = mb_ereg_replace("&[a-zA-Z]{0,10}$", "", $html);
			$html = mb_ereg_replace("&#[\d]{0,10}$", "", $html);
		}
		return $html;
	}

	/**
	 * Обрезает строку до нужного количества символов, не учитывая переносы строк
	 * @param string $text Текст
	 * @param int $charLimit Ограничение по кол-ву символов
	 * @return string Обрезанная строка
	 */
	public static function trimTextWidth($text, $charLimit) {
		$trimmed_lines = [];
		$lines = explode("\n", $text);
		foreach($lines as $line) {
			$trimmed_line = mb_substr($line, 0, $charLimit);
			$charLimit -= mb_strlen($trimmed_line);
			$trimmed_lines[] = $trimmed_line;
			if($charLimit < 1) break;
		}
		return implode("\n", $trimmed_lines);
	}

	/**
	 * Перевод секунд в дни, часы, минуты, секунды
	 * @param int $seconds
	 * @param string $format
	 * @return string
	 */
	public static function secondsToTime($seconds, $format = '%a Дней, %h часов, %i минут, %s секунд') {
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format($format);
	}

	/**
	 * Перевод секунд в дни, часы, минуты, секунды с исключением нулевых значений
	 *
	 * @param int $seconds
	 * @return string - строка вида "25 суток, 5 часов, 10 минут, 12 секунд"
	 */
	public static function secondsToTimeShort(int $seconds): string {
		$format = [];
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		$diff = $dtF->diff($dtT);
		if ($diff->format("%a") != 0) {
			$format[] = "%a " . self::pluralNumber($diff->format("%a"), "сутки", "суток", "суток");
		}
		if ($diff->format("%h") != 0) {
			$format[] = "%h " . self::pluralNumber($diff->format("%h"), "час", "часа", "часов");
		}
		if ($diff->format("%i") != 0) {
			$format[] = "%i " . self::pluralNumber($diff->format("%i"), "минута", "минуты", "минут");
		}
		if ($diff->format("%s") != 0) {
			$format[] = "%s " . self::pluralNumber($diff->format("%s"), "секунда", "секунды", "секунд");
		}

		return $dtF->diff($dtT)->format(implode(", ", $format));
	}

	/**
	 * Получение формата для перевода секунд в дни, часы, минуты, секунды
	 * @param int $seconds
	 * @return string
	 */
	public static function getSecondsToTimeFormat($seconds) {
		$format = "%S";
		$items = [
			self::ONE_MINUTE => "%I:",
			self::ONE_HOUR => "%H:",
			self::ONE_DAY => "%d "
		];
		foreach ($items as $k => $v) {
			if ($seconds < $k) {
				return $format;
			}
			$format = $v . $format;
		}
		return $format;
	}

	/**
	 * Обрезает знаки препинания в конце предложения
	 *
	 * @param string $str
	 * @param string $customRegexp
	 * @return mixed|string
	 */
	public static function trimEndOfSentencePunctuationMarks($str, $customRegexp = '') {
		if (!is_string($str)) {
			return '';
		}

		$regexp = '~[\!\.\?\;\:\,\s\f\t]*$~';
		if ($customRegexp) {
			$regexp = $customRegexp;
		}

		return preg_replace($regexp, '', rtrim($str));
	}

	/**
	 * Метод который проверяет были ли какие-нибудь изменения в тексте.
	 * Считает текст измененным, если были добавлены/удалены словообразующие символы или цифры
	 *
	 * @param $oldString
	 * @param $newString
	 * @return bool
	 */
	public static function checkIfTextWasChanged($oldString, $newString) {
		$oldString = preg_replace('~[\s]~', '', $oldString);
		$newString = preg_replace('~[\s]~', '', $newString);

		$fromStart = strspn($oldString ^ $newString, "\0");
		$fromEnd = strspn(strrev($oldString) ^ strrev($newString), "\0");

		$oldEnd = strlen($oldString) - $fromEnd;
		$newEnd = strlen($newString) - $fromEnd;

		$newDiff = substr($newString, $fromStart, $newEnd - $fromStart);
		$oldDiff = substr($oldString, $fromStart, $oldEnd - $fromStart);

		$newDiff = is_bool($newDiff) ? $newDiff : (bool) preg_match('~[a-zA-Zа-яА-Я0-9]~', $newDiff);
		$oldDiff = is_bool($oldDiff) ? $oldDiff : (bool) preg_match('~[a-zA-Zа-яА-Я0-9]~', $oldDiff);

		return $newDiff || $oldDiff;
	}

	/**
	 * ucfirst для UTF-8
	 * @param string $str
	 * @return string
	 */
	public static function ucfirst_utf8($str) {
		return mb_substr(mb_strtoupper($str, 'utf-8'), 0, 1, 'utf-8') . mb_substr($str, 1, mb_strlen($str) - 1, 'utf-8');
	}

	/**
	 * Вернет процент равенства 2х строк
	 * @param $first
	 * @param $second
	 * @return float
	 */
	public static function compareText($first, $second): float {
		similar_text(self::strNormalize($first), self::strNormalize($second), $percentage);

		return $percentage;
	}

	/**
	 * Изменить ключ массива
	 * @param string $oldKey старый ключ
	 * @param string $newKey новый ключ
	 * @param array $array массив
	 * @return void
	 */
	public static function changeArrayKey(string $oldKey,string $newKey,array &$array) {
		if (array_key_exists($oldKey, $array)) {
			$array[$newKey] = $array[$oldKey];
			unset($array[$oldKey]);
		}
	}

	/**
	 * Изменить параметр в url
	 * @param sring $url
	 * @param string $parameter имя параметра
	 * @param string $value новое значение параметра
	 * @return string url с новым значением параметра
	 */
	public static function changeUrlParameter($url, $parameter, $value) {
		$url = parse_url($url);
		parse_str($url["query"], $parameters);
		unset($parameters[$parameter]);
		$parameters[$parameter] = $value;
		return $url["path"] . "?" . http_build_query($parameters);
	}

	/**
	 * нормализация текста
	 * @param $str
	 * @return string
	 */
	public static function strNormalize($str) {
		$n = str_word_count(mb_strtolower($str), 1,
			'1234567890абвгдеёжзийклмнопрстуфхцчшщъыьэюя');
		sort($n, SORT_LOCALE_STRING);

		return implode(' ', $n);
	}

	/**
	 * Считает не английские буквенные символы в строке
	 * @param $string
	 * @return int
	 */
	public static function countNotEnSymbols($string) {
		return (int) preg_match_all("/[^a-z]/ui", preg_replace("/(\W|[0-9_])/ui", "", $string));
	}

	/**
	 * Возвращает процент русских букв в строке
	 * @param string $string
	 * @return float
	 */
	public static function getRuSymbolsPercentInString(string $string): float {
		$string = strip_tags(html_entity_decode($string));
		$cRu = preg_match_all( '/[а-яё]/ui', $string);
		$cAll = preg_match_all("/\w/ui", preg_replace("/[0-9_]/", "", $string)); // считаем общее число буквенных символов

		return (float) $cRu / $cAll * 100;
	}

	/**
	 * Удаляет ссылки из строки
	 * @param string $string
	 * @return string
	 */
	public static function removeLinks(string $string): string {
		return preg_replace(RegexpPatternManager::REGEX_URL, "", $string);
	}

	/**
	 * Нормализация строки для хранения ее без тегов
	 *
	 * @param string $string Строка для обработки
	 *
	 * @return string
	 */
	public static function normalizeToNoTags($string) {
		$string = html_entity_decode($string);
		$string = strip_tags($string);
		$string = preg_replace("/\s{2,}/u", " ", html_entity_decode($string));
		$string = preg_replace("/\s/", " ", $string);
		$string = trim($string);
		return $string;
	}

	/**
	 * Входит ли данный IP в список разрешенных
	 *
	 * @param string $ip Проверяемый IP
	 * @param array $rules Список допустимых IP (возможны маски)
	 *
	 * @return bool
	 */
	public static function isAllowIp($ip, $rules) {
		if(!$ip || !$rules) {
			return false;
		}

		$replaces = ['.' => '\\.', '*' => '[0-9]{1,3}'];

		foreach ($rules as $rule) {
			$rule = strtr($rule, $replaces);
			if (preg_match('!^' . $rule . '$!', $ip)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Заменяет символом звездочки (*) часть кошельков пользователя
	 * @param string|null $string кошелек
	 * @param string $type тип кошелька (webmoney или qiwi)
	 * @return string|null
	 */
	public static function walletSecure(?string $string, string $type): ?string {
		if (empty($string)) {
			return $string;
		}
		$string = stripslashes($string);
		if ($type == UserManager::PAYMENT_WM) {
			//WebMoney: R, 1 цифра, 7 цифр, 4 последние цифры
			//Пример: R2*******5214
			$string = substr($string, 0, 2) . str_repeat('*', 7) . substr($string, -4);

		} elseif ($type == UserManager::PAYMENT_QIWI) {
			//Qiwi: первая цифра, 8 звезд, 2 последние цифры
			//Пример: 7********25
			$string = substr($string, 0, 1) . str_repeat('*', 8) . substr($string, -2);

		} elseif ($type == UserManager::PAYMENT_CARD) {
			// Карта - 6 первых цифр, звездочки вместо оставшихся цифр, 4 последние цифры
			$string = substr($string, 0, 6) . str_repeat("*", (strlen($string) - 10)) . substr($string, -4, 4);
			// Разбить на группы по 4 символа, разделённые пробелом
			$string = implode(" ", (array)str_split($string, 4));

		} elseif ($type == UserManager::PAYMENT_YANDEX) {
			// Карта - звездочки вместо цифр, 4 последние цифры
			$string = str_repeat("*", (strlen($string) - 4)) . substr($string, -4, 4);
		}
		return $string;
	}

	/**
	 * Посчитать статистику слова
	 * @param array $wordsInfo Массив слов из self::explodeDescToWords
	 * @return array  array( <word> => <count> )
	 */
	public static function calcWordStat(array $wordsInfo) {
		$return = [];
		foreach ($wordsInfo as $word) {
			if (!isset($return[$word])) {
				$return[$word] = 0;
			}
			$return[$word]++;
		}

		return $return;
	}

	/**
	 * Разбить текст на слова
	 * @param string $desc Текст. Можно html
	 * @return array Массив слов
	 */
	public static function explodeDescToWords($desc) {
		$desc = mb_strtolower($desc, 'utf-8');
		$desc = strtr(htmlspecialchars_decode($desc), [
			'<' => ' <',
		]);
		$desc = strip_tags($desc);
		$desc = preg_replace('![^a-zа-яё ]!ui', ' ', $desc);
		$return = explode(' ', $desc);
		$return = array_map('trim', $return);
		$return = array_filter($return);

		return $return;
	}

	/**
	 * Посчитать процент схожести $stat1 с $stat2
	 * @param array $stat1 Массив из calcWordStat
	 * @param array $stat2 Массив из calcWordStat
	 * @return float Процент схожести масивов
	 */
	public static function calcDescSimilarityPercent($stat1, $stat2) {
		if (empty($stat1) || empty($stat2)) {
			return 0;
		}
		$compareCount = 0;
		foreach ($stat1 as $word => $cnt) {
			if (isset($stat2[$word])) {
				$compareCount += min($cnt, $stat2[$word]);
			}
		}

		return ($compareCount / array_sum($stat1)) * 100;
	}

	/**
	 * Округлить число до n знака слева
	 * @param int $number Число
	 * @param int $symbols Количество знаков слева
	 * @return int Округлённое число
	 */
	public static function roundLeft($number, $symbols) {
		$symbolsCount = strlen((string)$number);
		$symbolsToRound = $symbolsCount - $symbols;
		$divider = pow(10, $symbolsToRound);
		$number /= $divider;
		$number = round($number);
		$number *= $divider;
		return $number;
	}

	/**
	 * Заменить html-параграфы в тексте на переносы строк
	 * @param string $html Текст с html-параграфами
	 * @return string Текст с переносами строк
	 */
	public static function p2nl($html) {
		$html = str_replace(["\r\n", "\n"], '', $html);
		$lines = preg_split('/(<\/p>|<br>|<br\/>|<br \/>)/i', $html);
		$out_lines = [];
		foreach($lines as $v) {
			$line = html_entity_decode(trim(strip_tags($v)));
			if(strlen($line)) {
				$out_lines[] = $line;
			}
		}
		return implode("\n", $out_lines);
	}

	/**
	 * Заменить переносы строк в тексте на html-параграфы
	 * @param string $text Текст с переносами строк
	 * @return string Текст с html-параграфами
	 */
	public static function nl2p($text) {
		return '<p>' . str_replace(["\r\n", "\r", "\n"], '</p><p>', $text) . '</p>';
	}

	/**
	 * Преобрзовывает экранируемые двойные слеши \\ в одиночные, для вывода.
	 * @param $string
	 * @return mixed
	 */
	public static function unescapeSlashes($string){
		return str_replace("\\\\", "\\", $string);
	}

	/**
	 * Заменяет в url http:\\ на https:\\
	 * @param string $url
	 * @return string Преобразованный url
	 */
	public static function httpToHttps($url) {
		$pattern = '/http\:\/\//i';
		$replacement = 'https://';
		return preg_replace($pattern, $replacement, $url);
	}

	/**
	 * Безопасный TRUNCATE TABLE для использования в кластере
	 * @param string $tableName название таблицы
	 * @param string|null $connectionName коннект к БД
	 */
	public static function safeTruncate($tableName, $connectionName = null) {
		$tempTableName = $tableName . "_" . self::randomString(8);
		$oldTableName = "{$tempTableName}_old";

		\Core\DB\DB::connection($connectionName)->statement("CREATE TABLE `{$tempTableName}` LIKE `{$tableName}`");
		\Core\DB\DB::connection($connectionName)->statement("RENAME TABLE `{$tableName}` TO `{$oldTableName}`, `{$tempTableName}` TO `{$tableName}`");
		\Core\DB\DB::connection($connectionName)->statement("DROP TABLE `{$oldTableName}`");
	}

	/**
	 * Скопировать данные таблицы из одной БД в другую
	 *
	 * @param string $table название таблицы
	 * @param string $connectionFrom из БД
	 * @param string $connectionTo в БД
	 * @param int $chunk чанками по столько
	 * @param string $orderBy поле primary key из таблицы
	 * @param bool $showProgress выводить ли прогресс
	 * @return void
	 */
	public static function copyTable($table, $connectionFrom, $connectionTo, $chunk = 500, $orderBy = "id", $showProgress = false) {
		$processed = 0;
		$total = \Core\DB\DB::connection($connectionFrom)
			->table($table)
			->count();

		\Core\DB\DB::connection($connectionFrom)
			->table($table)
			->orderBy($orderBy)
			->chunk($chunk, function($items) use ($table, &$processed, $total, $showProgress, $connectionTo) {
				$insert = [];

				foreach ($items as $item) {
					$insert[] = self::prepareItem($item);

					if ($showProgress) {
						echo "\rCopying $total rows from $table ... " . round2(++$processed / $total * 100) . "%  ";
					}
				}

				App::pdo($connectionTo)->insertRows($table, $insert, ["ignore" => true]);
			});

		if ($showProgress) {
			echo "\n";
		}
	}

	/**
	 * Подготовить данные (ассоц. массив) для инсерта в БД
	 *
	 * @param mixed $item
	 * @return mixed
	 */
	private static function prepareItem($item) {
		$item = (array) $item;

		foreach ($item as $key => $value) {
			if (is_null($value)) {
				$item[$key] = ["value" => null, "PDOType" => PDO::PARAM_NULL];
			}
		}

		return $item;
	}

	/**
	 * Удалить домены из текста (например: domain.ru или www.domain.ru)
	 * @param $text string
	 * @return string
	 */
	public static function cutDomains($text) {
		$domainPattern = implode("|", RegexpPatternManager::DOMAIN_ZONES);
		$pattern = "!([-\w]+\.)+({$domainPattern})!iu";
		$text = preg_replace($pattern, "", $text);
		return $text;
	}

	/**
	 * Проверка включен ли модуль module.chat
	 * @return bool
	 * @throws \PHLAK\Config\Exceptions\InvalidContextException
	 */
	public static function isModuleChatEnable() :bool {
		return boolval(App::config("module.chat.enable"));
	}

	/**
	 * Проверить принадлежит ли значение $value закрытому интервалу [$left, $right]
	 *
	 * @param int $value – Проверяемое значение
	 * @param int $left – Левая граница интервала
	 * @param int $right – Правая граница интервала
	 * @return bool – true, если значение принадлежит интервалу, false – иначе
	 */
	public static function between(int $value, int $left, int $right) {
		return ($value - $left) * ($value - $right) <= 0;
	}

	/**
	 * Возвращает количество часов, оставшихся до принятия заказа исполнителем
	 *
	 * @param bool $orderRestarted заказ переоткрыт
	 * @return int
	 */
	public static function getAutoChancelHours($orderRestarted = false) : int {
		return 3;
	}

	/**
	 * Возвращает количество дней, до автоотмены заказа
	 *
	 * @param bool $orderRestarted Был ли заказ перезапущен
	 *
	 * @return int
	 */
	public static function getAutoCancelDays($orderRestarted = false) : int {
		return Helper::getAutoChancelHours($orderRestarted) / 24;
	}

	/**
	 * Выполнение комады с возвратом кода и stderr
	 *
	 * @param string $command Команда
	 * @param null $stdout Выходной параметр в котором будет после выполнения stdout
	 * @param null $stderr Выходной параметр в котором будет после выполнения stderr
	 *
	 * @return int Код возврата UNIX (0 - все ок, иначе код ошибки)
	 */
	public static function execute(string $command, &$stdout = null, &$stderr = null) {
		$proc = proc_open($command, [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		], $pipes);

		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		return proc_close($proc);
	}

	/**
	 * TODO: Удалить метод после тестового этапа "Сделать отдельную страницу gallery."
	 * Является ли текущий пользователь тестером?
	 * @return bool
	 */
	public static function isGalleryTester()
	{
		$user = UserManager::getCurrentUser();
		$isTrackTester = in_array($user->USERID, \App::config("gallery.testers"));
		return $isTrackTester;
	}

	/**
	 * TODO: Удалить метод после тестового этапа "Улучшение страницы трека заказа"
	 * Является ли текущий пользователь тестером?
	 * @return bool
	 */
	public static function isTrackTester()
	{
		global $actor;
		$isTrackTester = in_array($actor->id, \App::config("track.testers"));
		return $isTrackTester;
	}

	/**
	 * Получить список возможных падежей класса NCL
	 * @return array
	 */
	public static function getAllowedDeclensionsNCL() {
		return [
			\NCL\NCL::$IMENITLN,
			\NCL\NCL::$RODITLN,
			\NCL\NCL::$DATELN,
			\NCL\NCL::$VINITELN,
			\NCL\NCL::$TVORITELN,
			\NCL\NCL::$PREDLOGN,
		];
	}

	/**
	 * Проверить текст на наличае ссылок в нём
	 * Ищем по использованию отдельно стоящих (отделённых знаками препинания) доменных зон
	 *
	 * @param string|null $text Проверяемый текст
	 * @return bool true - есть ссылки, false - нет ссылок
	 */
	public static function checkTextForLinks(?string $text) {
		if (empty($text)) {
			return false;
		}
		return (bool)preg_match('/\b('.implode("|", RegexpPatternManager::DOMAIN_ZONES).')\b/i', $text);
	}

	/**
	 * Возвращает число с единицей измерения в правильном склонении
	 *
	 * @param mixed         $count Число, которое выводится и для которого вычисляется склонение
	 * @param array[3]    $forms Массив склонений (час, часа, часов)
	 * @param string      $separator Разделитель числа и единицы измерения
	 * @param string|null $lang
	 * @return string
	 */
	public static function getCountWithUnit($count, array $forms, string $separator = " ", string $lang = null) {
		return $count . " " . declension((int)$count, $forms, $lang);
	}
}
