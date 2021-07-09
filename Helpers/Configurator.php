<?php

use Enum\Config;

/**
 * Синглтон для доступа к конфигурациям приложения.
 * Большая часть конфигураций хранится в текстовых файлах public/file.cnf (для публичных),
 * private/.local.file.cnf (для приватных), domain.cache (для кэша).
 * Ключи в приватной конфигурации private/.local.file.cnf должны являться
 * поднмножеством ключей публичной конфигурации public/file.cnf.
 *
 * Файлы кэша и приватных конфигураций в репозиторий не попадают
 */
class Configurator {
	/*
	 * Экземпляр
	 */
	private static $_instance = null;
	/*
	 * Данные конфигурации, ключ - значение
	 */
	protected $data = [];
	/*
	 * Режим конфигуратора. Описывается константами MODE_*
	 */
	private $mode = null;

	/**
	 * @var null
	 */
	private $lastTypeForCaching = null;

	/**
	 * Была ли генерация кэша
	 * @var bool
	 */
	private $wasGeneratingCache = false;

	/*
	 * Таблица для хранения частичных данных конфигурации в бд
	 */
	const TABLE_NAME = 'config';

	/*
	 * Режим 'конфигурация из кеша'. Данные конфигурации будут загружены из файла кеша
	 * config/cnf/cache/*.cache.php
	 */
	const MODE_CACHE_USING = 'cache_using';

	/**
	 * Режим разработки, данные будут браться не из кеша
	 */
	const MODE_LOCAL = 'local_mode';

	/*
	 * Режим 'генерация кеша конфигурации'. Данные конфигурации будут загружены из файлов
	 * config/cnf/private/.local.*.cnf и config/cnf/public/*.cnf
	 */
	const MODE_CACHE_GENERATING = 'cache_generating';

	/*
	 * Тип конфигурации для хостов/доменов приложений.
	 */
	const TYPE_HOST = 'host';
	/*
	 * Тип конфигурации для основного домена.
	 * Является ключом в файлах конфигураций для типа self::TYPE_HOST
	 */
	const TYPE_BASE = 'rudomain';

	/*
	 * Базовый раздел файлов конфигурации
	 */
	const BASE_DIR = APP_ROOT . '/config/cnf';
	/*
	 * Расширение для файлов кэша
	 */
	const CACHE_EXTENSION = '.cache.php';
	/*
	 * Префикс названия для приватных файлов
	 */
	const PREFIX_PRIVATE = '.local.';

	/*
	 * Приватный файл
	 */
	const FILE_TYPE_PRIVATE = 'private';
	/*
	 * Публичный файл
	 */
	const FILE_TYPE_PUBLIC = 'public';
	/*
	 * Файл кэша
	 */
	const FILE_TYPE_CACHE = 'cache';

	/*
	 * Файл публичной конфигурации хостов приложений
	 */
	const FILE_HOST = 'host.cnf';
	/*
	 * Файл основной публичной конфигурации
	 */
	const FILE_MAIN = 'config.cnf';
	/*
	 * Файл конфигурации файловой системы
	 */
	const FILE_PATH = 'path.cnf';

	const FIELD_SETTING = 'setting';
	const FIELD_VALUE = 'value';

	/**
	 * Int. Время последнего найденого "плохого" email в логе exim
	 */
	const EXIM_LAST_BAD_TIME = 'exim_last_bad_time';

	/**
	 * int. Время последней отправки уведомления об упавших кронах
	 */
	const OPTION_LAST_SEND_FAIL_CRON = 'last_send_fail_cron';

	/**
	 * bool. Включен ли функционал числового объема кворка для продавцов (фильтр, заказ кворка)
	 */
	const ENABLE_VOLUME_TYPES_FOR_BUYERS = 'enable_volume_types_for_buyers';

	/**
	 * int. Вариант показа интервью  0 - продвинутым и выше, 1 - профессионалам, 2 - никому
	 */
	const INTERVIEW_SETTINGS = 'interview_settings';
	const SHOW_ADVANCED = '0';
	const SHOW_PROFY = '1';
	const DONT_SHOW = '2';

	/*
	 * Параметры активации epayments на kwork.ru и kwork.com
	 */
	const CONFIG_EPAYMENTS_KWORKRU = "epayments_kworkru";
	const CONFIG_EPAYMENTS_KWORKCOM = "epayments_kworkcom";

	/**
	 * Плановое и текущее соотношение unitpay/epayments
	 */
	const CONFIG_EPAYMENTS_PLANNED = "epayments_planned";
	const CONFIG_EPAYMENTS_RATIO = "epayments_ratio";

	/**
	 * Текущая таблица рейтинга портфолио
	 */
	const CONFIG_ACTIVE_PORTFOLIO_RATING_TABLE = "portfolio_rating_table";

	/**
	 * bool Включение режима автоматического повышения видимости пакетных фильтров
	 * если в классификации более 40% кворков с заполненными значениями
	 */
	const ENABLE_AUTO_VISIBLE_PACKAGE_FILTERS = "enable_auto_visible_package_filters";

	/**
	 * @TODO 6620 убрать
	 * string. Текущая таблица для использования в сортировке по группам
	 */
	const KWORK_RATING_GROUP_CURRENT_TABLE = "kwork_rating_group_current_table";

	/**
	 * Получить список всех файлов публичных конфигураций (*.cnf).
	 * Ключи – Тип конфигурации. Определяются константанами self::TYPE_*
	 * Значения – Список файлов публичных конфигураций (*.cnf), необходимых для данного типа.
	 * Загрузка перезапись данных из файлов осуществляется с учетом указанного порядка.
	 *
	 * @return array
	 */
	private function getPublicFiles() {
		return [
			self::TYPE_HOST => [
				self::FILE_HOST
			],
			self::TYPE_BASE => [
				self::FILE_MAIN,
				self::FILE_PATH
			],
		];
	}

	/**
	 * Конструктор. Определяет и загружает необходимую конфигурацию.
	 * Закрытый метод. Паттерн синглтон.
	 *
	 * Configurator constructor.
	 * @param string $mode – Режим конфигуратора. Определяется константами self::MODE_*
	 */
	private function __construct(string $mode = self::MODE_CACHE_USING) {
		$this->mode = $mode;
		$this->load();
	}

	/**
	 * Получить единственный экземпляр. Паттерн синглтон.
	 *
	 * @param string $mode – Режим конфигуратора. Определяется константами self::MODE_*
	 * @return Configurator
	 */
	public static function getInstance(string $mode = self::MODE_CACHE_USING) {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($mode);
		}
		if (self::$_instance->mode != $mode) {
			self::$_instance->mode = $mode;
		}
		return self::$_instance;
	}

	/**
	 * Запрет на клонирование. Паттерн синглтон.
	 */
	final private function __clone() {}

	/**
	 * Загрузить данные конфигурации.
	 * Определить тип конфигурации по хосту и загрузить соответствующие данные.
	 */
	private function load() {
		// Загружаем данные хостов
		$this->loadHost();
		// Определяем по хосту тип конфигурации
		$type = $this->getType();
		// Загружаем кэш или файлы конфигурации соответствующего типа
		$this->loadByType($type);

		// при локальной разработке берём настройки из файлов конфигураций
		if (Configurator::get("app.mode") == "local") {
			$this->mode = self::MODE_LOCAL;
			$this->data = [];
			$this->loadHost();
			$this->loadByType($type);
		}
	}

	/**
	 * Загрузить данные конфигурации для определенного типа
	 * В зависимости от режима конфигуратора будут загружены данные из кеша или файлов *.cnf
	 *
	 * @param string $type – Тип конфигурации. Определяется константами self::TYPE_*
	 */
	private function loadByType(string $type) {
		if (self::MODE_CACHE_USING === $this->mode) {
			$this->loadCache($type);
		} elseif (in_array($this->mode, [self::MODE_LOCAL, self::MODE_CACHE_GENERATING])) {
			$this->loadFiles($type);
		}
	}

	/**
	 * Загрузить данные конфигурации для типа self::TYPE_HOST
	 */
	private function loadHost() {
		$this->loadByType(self::TYPE_HOST);
	}

	/**
	 * Определить по хосту тип конфигурации.
	 * Предполагается, что заранее были уже загружены данные методом loadHost()
	 * Если для текущего хоста не удается найти соответствие, то возвратить тип self::TYPE_BASE
	 *
	 * @return string – Тип конфигурации. Описывается константами self::TYPE_*
	 */
	private function getType() {
		return self::TYPE_BASE;
	}

	/**
	 * Сгенерировать кеш для всех типов конфигураций (хостов)
	 * Доступен только для режима конфигурара self::MODE_CACHE_GENERATING
	 *
	 * @return string[] – Список типов конфигураций, для которых сгенерирован кеш
	 */
	public function generate() {
		$result = [];
		if (self::MODE_CACHE_GENERATING !== $this->mode) {
			return $result;
		}

		$types = array_keys($this->getPublicFiles());
		foreach ($types as $type) {
			$this->data = [];
			$this->loadHost();
			$this->loadFiles($type);
			if ($this->generateCache($type)) {
				$result[] = $this->getCacheName($type);
			}
		}

		return $result;
	}

	/**
	 * Генерирует кеш по типу конфигурации (для хоста)
	 *
	 * @param string $type
	 */
	public function generateByType(string $type) {
		$this->loadHost();
		$this->loadFiles($type);
		$this->generateCache($type);
	}

	/**
	 * Получить название кэш файла для данного типа конфигурации.
	 *
	 * @param string $type – Тип конфигурации. Определяется константами self::TYPE_*
	 * @return string|false – Имя кэш файла, в случае, если получилось определить имя,
	 * false – иначе
	 */
	private function getCacheName(string $type) {
		$name = (self::TYPE_HOST === $type) ? $type : $this->get($type);
		return !empty($name) ? $name . self::CACHE_EXTENSION : false;
	}

	/**
	 * Получить путь до файла конфигурации (*.cnf) или кэша (*.cache.php)
	 *
	 * @param string $name – Имя файла конфигурации или кэша
	 * @param string $type – Тип файла конфигурации. Определяются константами self::FILE_TYPE_*
	 * @return string – Путь до файла
	 */
	private function getPath(string $name, string $type) {
		$dir = self::BASE_DIR . DIRECTORY_SEPARATOR . $type;
		$name = (self::FILE_TYPE_PRIVATE === $type) ? self::PREFIX_PRIVATE . $name : $name;
		return $dir . DIRECTORY_SEPARATOR . $name;
	}

	/**
	 * Загрузить данные из файла кэша.
	 * В случае отсутствия файла кэша перехватывается Warning и генерируется кеш для заданного типа
	 *
	 * @param string $type – Тип конфигурации. Определяется константами self::TYPE_*
	 */
	private function loadCache(string $type) {
		$name = $this->getCacheName($type);
		$cache = $this->getPath($name, self::FILE_TYPE_CACHE);

		$this->lastTypeForCaching = $type;
		set_error_handler([$this, "cacheFileNotExists"], E_WARNING);
		$data = include_once($cache);
		if (!$this->wasGeneratingCache) {
			$this->data = !is_array($data) ? [] : $data;
			$this->wasGeneratingCache = false;
		}
		restore_error_handler();
	}

	/**
	 * Выполняется в случае обращения к файлу кэша которого не существует
	 */
	private function cacheFileNotExists() {
		$tmpMode = $this->mode;
		$this->mode = self::MODE_CACHE_GENERATING;
		$this->generateByType($this->lastTypeForCaching);
		$this->mode = $tmpMode;
		$this->wasGeneratingCache = true;
	}

	/**
	 * Загрузить данные из файлов конфигураций (приватных и публичных) для определенного типа
	 *
	 * @param string $type – Тип конфигурации. Определяется константами self::TYPE_*
	 * @return bool – true, если загружены все файлы, false – иначе
	 */
	private function loadFiles(string $type): bool {
		$files = $this->getPublicFiles();
		if (!array_key_exists($type, $files)) {
			return false;
		}
		foreach ($files[$type] as $file) {
			$this->loadFile($file, self::FILE_TYPE_PUBLIC); // загружаем публичные данные
			$this->loadFile($file, self::FILE_TYPE_PRIVATE); // перезаписываем приватными данными
		}
		return true;
	}

	/**
	 * Корректно убрать комментарии вида ';' из строки
	 *
	 * @param string $value – Строка
	 * @return string – Строка без комментариев
	 */
	private function removeComments(string $value): string {
		$arValue = explode(";", $value);
		return trim($arValue[0]);
	}

	/**
	 * Корректно убрать двойные ковычки из строки
	 *
	 * @param string $value – Строка
	 * @return bool|string – Строка без двойных ковычек
	 */
	private function clearQuotes(string $value): string {
		if (mb_strlen($value) >= 2 && $value[0] == '"') {
			$value = substr($value, 1, mb_strlen($value) - 2);
		}
		return $value;
	}

	/**
	 * Записать сериализованные данные в кэш с соотвествующим именем для типа конфигурации
	 *
	 * @param string $type – Тип конфигурации. Определяется константами self::TYPE_*
	 * @return bool – true в случае успеха, false – иначе
	 */
	private function generateCache(string $type) {
		$name = $this->getCacheName($type);
		if (empty($name)) {
			return false;
		}
		$path = $this->getPath($name, self::FILE_TYPE_CACHE);

		if ($fileResource = fopen($path, "w")) {
			$content = "<?php\nreturn " . var_export($this->data, true) . ";";
			fwrite($fileResource, $content);
			fclose($fileResource);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузить и распарсить данные файла конфигурации.
	 * Данные разных файлов перезаписываются в порядке, указанном в getPublicFiles()
	 * для конкретного типа конфигурации
	 *
	 * @param string $file – Имя файла
	 * @param string $type – Тип файла конфигурации. Приватная или публичная.
	 * Определяется константами self::FILE_TYPE_*
	 * @return bool – true, если удалось загрузить данные, false – иначе
	 */
	private function loadFile(string $file, string $type = self::FILE_TYPE_PUBLIC): bool {
		$path = $this->getPath($file, $type);

		if (!file_exists($path) || !is_readable($path)) {
			return false;
		}

		$data = file_get_contents($path);
		$rows = explode("\n", $data);

		// цикл заполнения временного массива
		$tmp = [];
		foreach ($rows as $row) {
			list($key, $value, $isArray) = $this->parseRow($row);
			if (empty($key)) {
				continue;
			}
			if ($isArray) {
				$tmp[$key][] = $value;
			} else {
				$tmp[$key] = $value;
			}
		}

		// цикл заполнения $data
		foreach ($tmp as $key => $row) {
			$this->data[$key] = $row;
		}

		return true;
	}

	/**
	 * Распарсить строку файла конфигурации
	 * @param string $row - строка из файла конфигурации
	 * @return array [$key, $value, $isArray]
	 */
	public function parseRow($row): array {
		$row = trim($row);
		if ($row == "" || $row[0] == ";") {
			return ["", "", 0];
		}
		$eqPos = strpos($row, "=");
		$key = trim(substr($row, 0, $eqPos));
		$value = trim(substr($row, $eqPos + 1));

		$value = $this->removeComments($value);
		$value = $this->clearQuotes($value);
		if (substr($key, -2) == "[]") {  // массив
			$key = substr($key, 0, mb_strlen($key) - 2);
			return [$key, $value, 1];
		}
		// единичные данные
		return [$key, $value, 0];
	}

	/**
	 * Получить значение в конфигурации по ключу
	 *
	 * @param string $param – Ключ
	 * @return mixed – Значение
	 */
	public function get(string $param) {
		$param = trim($param);
		$result = $this->data[$param];
		if ($result != "" && $result[0] == '"') {
			$result = substr($result, 1, mb_strlen($result) - 2);
		}

		if ($result === "true") {
			$result = true;
		}
		if ($result === "false") {
			$result = false;
		}

		return $result;
	}

	/**
	 * Установить значение для конфигурации по ключу
	 * @param string $param – Ключ
	 * @param string $value – Значение
	 * @return void
	 */
	public function set(string $param, string $value): void {
		$param = trim($param);
		$this->data[$param] = $value;
	}

	/**
	 * Список опцией, которые можно менять в таблицее config
	 * @return array array ( <name> => <bool> ) Массив имя и разрешено ли менять. Если явно не указано - запрещено.
	 */
	public function allowChangeList() {
		return [
			self::EXIM_LAST_BAD_TIME => true,
			self::OPTION_LAST_SEND_FAIL_CRON => true,
			self::ENABLE_VOLUME_TYPES_FOR_BUYERS => true,
			self::INTERVIEW_SETTINGS => true,
			self::ENABLE_AUTO_VISIBLE_PACKAGE_FILTERS => true,
			self::KWORK_RATING_GROUP_CURRENT_TABLE => true,
			self::CONFIG_EPAYMENTS_KWORKRU => true,
			self::CONFIG_EPAYMENTS_KWORKCOM => true,
			self::CONFIG_EPAYMENTS_PLANNED => true,
			self::CONFIG_EPAYMENTS_RATIO => true,
			self::CONFIG_ACTIVE_PORTFOLIO_RATING_TABLE => true,
			Config::SPHINX_TAKE_AWAY_MESSAGES_SEARCH => true,
			Config::SPHINX_TAKE_AWAY_OFFERS_SEARCH => true,
			Config::SITES_LAST_SQI_UPDATE_DATE => true,
			Config::PHASES_MIN_ORDER_PRICE_RU => true,
			Config::PHASES_MIN_ORDER_PRICE_EN => true,
			Config::KWORK_RATING_GROUP_TABLE => true,
			Config::REFILL_USER_GROUPS => true,
			Config::WITHDRAW_USER_GROUPS => true,
			Config::PURSE_WEBMONEY3_MIN_AMOUNT => true,
			Config::PURSE_QIWI3_MIN_AMOUNT => true,
			Config::PURSE_CARD3_MIN_AMOUNT_RUB_RU => true,
			Config::PURSE_CARD3_MIN_AMOUNT_RUB_OTHER => true,
			Config::PURSE_CARD3_MIN_AMOUNT_USD => true,
			Config::PURSE_CARD4_MIN_AMOUNT_RU => true,
			Config::PURSE_CARD4_MIN_AMOUNT_UA => true,
			Config::PURSE_CARD4_MIN_AMOUNT_OTHER => true,
			Config::PURSE_QIWI4_MIN_AMOUNT => true,
			Config::PURSE_WEBMONEY4_MIN_AMOUNT => true,
			Config::PURSE_WEBMONEY3_COMISSION => true,
			Config::PURSE_QIWI3_COMISSION => true,
			Config::PURSE_CARD3_COMISSION => true,
			Config::PURSE_CARD4_COMMISSION_RU => true,
			Config::PURSE_CARD4_COMMISSION_UA => true,
			Config::PURSE_CARD4_COMMISSION_OTHER => true,
			Config::PURSE_WEBMONEY4_COMMISSION => true,
			Config::PURSE_QIWI4_COMMISSION => true,
			Config::PURSE_WEBMONEY3_LIMIT_SINGLE => true,
			Config::PURSE_QIWI3_LIMIT_SINGLE => true,
			Config::PURSE_CARD3_LIMIT_SINGLE_RUB_RU => true,
			Config::PURSE_CARD3_LIMIT_SINGLE_RUB_OTHER => true,
			Config::PURSE_CARD3_LIMIT_SINGLE_USD => true,
			Config::PURSE_CARD4_LIMIT_SINGLE_RUB_RU => true,
			Config::PURSE_CARD4_LIMIT_SINGLE_RUB_OTHER => true,
			Config::PURSE_QIWI4_LIMIT_SINGLE => true,
			Config::PURSE_WEBMONEY4_LIMIT_SINGLE => true,
			Config::PURSE_WEBMONEY4_MIN_AMOUNT => true,
		];
	}

	/**
	 * Изменить значение опции
	 * @param string $name Название редактируемой опции
	 * @param string $value Новое значение
	 * @return mixed pdo()->execute
	 */
	public function setInTable($name, $value) {
		$allowChangeList = $this->allowChangeList();
		if (empty($allowChangeList[$name])) {
			return false;
		}

		$this->data[$name] = $value;
		$sql = "UPDATE " . self::TABLE_NAME . " 
			SET " . self::FIELD_VALUE . "=:value
			WHERE " . self::FIELD_SETTING . "=:name";
		return App::pdo()->execute($sql, [
			'value' => $value,
			'name' => $name,
		]);
	}

	/**
	 * Получить переменные по маске имени
	 * @param string $mask
	 * @param bool $isExcludeArray - Исключить из результата массивы
	 * @return array [key => value, key => value, ...]
	 */
	public function getByKeyMask($mask, $isExcludeArray = true) {
		$res = [];
		foreach ($this->data as $key => $value) {
			if (!preg_match("|$mask|", $key, $patt)) {
				continue;
			}
			if ($isExcludeArray && is_array($value)) {
				continue;
			}
			$res[$key] = $value;
		}
		return $res;
	}

	/**
	 * Найти пароли в конфигурации
	 * @return array [password1, password2, ...]
	 */
	public function seekValidPasswords() {
		$passwords = Configurator::getInstance()->getByKeyMask("password");
		$passwords = array_values($passwords);
		$passwords = array_unique($passwords);
		$truePasswords = [];
		foreach ($passwords as $pass) {
			$pass = trim($pass);
			if (preg_match("|^[\*]+$|", $pass, $patt)) { // целиком состоит из звездочек
				continue;
			}
			if (empty($pass)) {
				continue;
			}
			if (strlen($pass) < 8) {
				continue;
			}
			$truePasswords[] = $pass;
		}
		return $truePasswords;
	}
}