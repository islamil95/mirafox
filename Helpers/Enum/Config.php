<?php

namespace Enum;


class Config {

	/**
	 * Использовать ли новый поиск (по таблицам kwork_search_*_(one|two) ?
	 */
	const USE_NEW_CATEGORY_SEARCH = 'use_new_category_search';

	/** @var string Список Postal-серверов */
	const MAIL_POSTAL_SERVERS = "mail.postal.servers";

	/** @var string Запускать ли кроны при локальной разработке */
	const LOCAL_CRON_ENABLE = "app.local_cron_enable";

	/** @var string Опция включения технических работ */
	const TECHNICAL_WORKS_EVENT = "technical_works.event";

	/** @var string Текст уведомления о технических работах, префикс */
	const TECHNICAL_WORKS_TEXT = "technical_works.text_";

	/**
	 * Минимальная стоимость заказа при которой возможно разбитие на этапы
	 * для русского заказа
	 */
	const PHASES_MIN_ORDER_PRICE_RU = "phases_min_order_price";

	/**
	 * Минимальная стоимость заказа при которой возможно разбитие на этапы
	 * для английского заказа
	 */
	const PHASES_MIN_ORDER_PRICE_EN = "phases_min_order_price_en";

	/**
	 * ID пользователя тех поддержки
	 */
	const SUPPORT_ID = "kwork.support_id";

	/** @var string Какую базу использует nginx */
	const REDIS_BAN_DATABASE = 'redis.db_ban_ip.server';

	/** @var string Список серверов Redis */
	const REDIS_SERVERS = 'redis_servers';

	/**
	 * date. Дата последнего обновления ИКС
	 */
	const SITES_LAST_SQI_UPDATE_DATE = 'last_sites_sqi_update_date';

	/**
	 * Путь к логам индексации Full индексов Sphinx
	 */
	const SPHINX_INDEXER_FULL_LOG_PATH = "sphinx.indexer_full_log";

	/**
	 * Путь к логам индексации Delta индексов Sphinx
	 */
	const SPHINX_INDEXER_DELTA_LOG_PATH = "sphinx.indexer_delta_log";

	/**
	 * mysql timestamp. Время последнего поиска сообщений по уводу пользователей с кворк
	 */
	const SPHINX_TAKE_AWAY_MESSAGES_SEARCH = "sphinx_take_away_messages_search";

	/**
	 * mysql timestamp. Время последнего поиска предложений по уводу пользователей с кворк
	 */
	const SPHINX_TAKE_AWAY_OFFERS_SEARCH = "sphinx_take_away_offers_search";

	/**
	 * Качество jpeg для изображений большого размера например t3 в кворке
	 */
	const OPTIMIZE_IMAGES_BIG_QUALITY = "optimize_images.big_jpeg_quality";

	/**
	 * Качество jpeg для изображений маленького размера например t4 в кворке
	 */
	const OPTIMIZE_IMAGES_SMALL_QUALITY = "optimize_images.small_jpeg_quality";

	/**
	 * Качество jpeg для изображений ретина изображений маленького размера например t4_r в кворке
	 */
	const OPTIMIZE_IMAGES_RETINA_QUALITY = "optimize_images.retina_jpeg_quality";

	/**
	 * Путь к директории с подключаемыми файлами Sphinx
	 */
	const SPHINX_INC = "sphinx.inc";

	/**
	 * Количество дней через которое автоматически отменяется заказ в статусе "Требует оплаты"
	 */
	const ORDER_STAGES_UNPAID_CANCEL_DAYS = "order_stages.unpaid_cancel_days";

	/**
	 * Путь до папки с логами, со стандартной ротацией.
	 */
	const ROTATE_LOGS_PATH = 'rotate_logs';

	/**
	 * Текущая таблица сортировки Рекомендуемые в оптимизированном варианту
	 */
	const KWORK_RATING_GROUP_TABLE = "kwork_rating_group_table";

	/**
	 * Настройка выбора группы пользователей для переключения между системами
	 * ввода денежных средств Unitpay/Paymore в формате JSON
	 */
	const REFILL_USER_GROUPS = "refill_user_groups";

	/**
	 * Настройка выбора группы пользователей для переключения между системами
	 * вывода денежных средств SolarStaff/Paymore
	 */
	const WITHDRAW_USER_GROUPS = "withdraw_user_groups";

	/**
	 * Время жизни черновика сообщения с момента последнего изменения, в днях
	 */
	const INBOX_DRAFT_TTL = "inbox.draft.ttl";

	/**
	 * Solar: Минимальная сумма вывода на Webmoney
	 */
	const PURSE_WEBMONEY3_MIN_AMOUNT = "purse.webmoney3.min_amount";

	/**
	 * Solar: Минимальная сумма вывода на QIWI
	 */
	const PURSE_QIWI3_MIN_AMOUNT = "purse.qiwi3.min_amount";

	/**
	 * Solar: Минимальная сумма вывода на рублевую российскую карту
	 */
	const PURSE_CARD3_MIN_AMOUNT_RUB_RU = "purse.card3.min_amount_rub_ru";

	/**
	 * Минимальная сумма вывода на рублевую не-российскую карту
	 */
	const PURSE_CARD3_MIN_AMOUNT_RUB_OTHER = "purse.card3.min_amount_rub_other";

	/**
	 * Solar: Минимальная сумма вывода В ДОЛЛАРАХ с долларового счета на долларовую карту
	 */
	const PURSE_CARD3_MIN_AMOUNT_USD = "purse.card3.min_amount_usd";

	/**
	 * Касса: Минимальная сумма вывода для карт РФ
	 */
	const PURSE_CARD4_MIN_AMOUNT_RU = 'purse.card4.min_amount.ru';

	/**
	 * Касса: Минимальная сумма вывода для карт Украины
	 */
	const PURSE_CARD4_MIN_AMOUNT_UA = 'purse.card4.min_amount.ua';

	/**
	 * Касса: Минимальная сумма вывода для карт других стран
	 */
	const PURSE_CARD4_MIN_AMOUNT_OTHER = 'purse.card4.min_amount.other';

	/**
	 * Касса: Минимальная сумма вывода Qiwi
	 */
	const PURSE_QIWI4_MIN_AMOUNT = 'purse.qiwi4.min_amount';

	/**
	 * Касса: Минимальная сумма вывода Webmoney
	 */
	const PURSE_WEBMONEY4_MIN_AMOUNT = 'purse.webmoney4.min_amount';

	/**
	 * Solar: Комиссия Webmoney
	 */
	const PURSE_WEBMONEY3_COMISSION = 'purse.webmoney3.comission';

	/**
	 * Solar: Комиссия QIWI
	 */
	const PURSE_QIWI3_COMISSION = 'purse.qiwi3.comission';

	/**
	 * Solar: Комиссия для карт
	 */
	const PURSE_CARD3_COMISSION = 'purse.card3.comission';

	/**
	 * Касса: Комиссия для карт РФ
	 */
	const PURSE_CARD4_COMMISSION_RU = 'purse.card4.commission.ru';

	/**
	 * Касса: Комиссия для карт Украины
	 */
	const PURSE_CARD4_COMMISSION_UA = 'purse.card4.commission.ua';

	/**
	 * Касса: Комиссия для карт других стран
	 */
	const PURSE_CARD4_COMMISSION_OTHER = 'purse.card4.commission.other';

	/**
	 * Касса: Комиссия Webmoney
	 */
	const PURSE_WEBMONEY4_COMMISSION = 'purse.webmoney4.commission';

	/**
	 * Касса: Комиссия QIWI
	 */
	const PURSE_QIWI4_COMMISSION = 'purse.qiwi4.commission';

	/**
	 * Solar: Лимит суммы на одну транзакцию Webmoney
	 */
	const PURSE_WEBMONEY3_LIMIT_SINGLE = 'purse.webmoney3.limit.single';

	/**
	 * Solar: Лимит суммы на одну транзакцию QIWI
	 */
	const PURSE_QIWI3_LIMIT_SINGLE = 'purse.qiwi3.limit.single';

	/**
	 * Solar: Лимит суммы на одну транзакцию для российских карт
	 */
	const PURSE_CARD3_LIMIT_SINGLE_RUB_RU = 'purse.card3.limit.single_rub_ru';

	/**
	 * Solar: Лимит суммы на одну транзакцию для иностранных других карт в рублях
	 */
	const PURSE_CARD3_LIMIT_SINGLE_RUB_OTHER = 'purse.card3.limit.single_rub_other';

	/**
	 * Solar: Лимит суммы на одну транзакцию для карт в долларах
	 */
	const PURSE_CARD3_LIMIT_SINGLE_USD = 'purse.card3.limit.single_usd';

	/**
	 * Касса: Лимит суммы на одну транзакцию в рублях для российских карт
	 */
	const PURSE_CARD4_LIMIT_SINGLE_RUB_RU = 'purse.card4.limit.single_rub_ru';

	/**
	 * Касса: Лимит суммы на одну транзакцию в рублях дли иностранных карт
	 */
	const PURSE_CARD4_LIMIT_SINGLE_RUB_OTHER = 'purse.card4.limit.single_rub_other';

	/**
	 * Касса: Лимит суммы на одну транзакцию QIWI
	 */
	const PURSE_QIWI4_LIMIT_SINGLE = 'purse.qiwi4.limit.single';

	/**
	 * Касса: Лимит суммы на одну транзакцию Webmoney
	 */
	const PURSE_WEBMONEY4_LIMIT_SINGLE = 'purse.webmoney4.limit.single';
}