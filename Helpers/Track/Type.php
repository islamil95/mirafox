<?php

namespace Track;


use Order\Stages\OrderStageManager;

/**
 * Class Type.
 *
 * Enum типов (и псевдотипов, вроде missing_data, которые не хранятся в базе) треков.
 *
 * @package Track
 */
class Type {

	/**
	 * Админ отменил заказ и присудил средтва покупателю
	 */
	const ADMIN_ARBITRAGE_CANCEL = 'admin_arbitrage_cancel';

	/**
	 * Админ отклонил арбитраж
	 */
	const ADMIN_ARBITRAGE_INPROGRESS = 'admin_arbitrage_inprogress';

	/**
	 * Администратор подтвердил выполнение заказа и присудил средства продавцу
	 */
	const ADMIN_ARBITRAGE_DONE = 'admin_arbitrage_done';

	/**
	 * Админ решил арбитраж пополам
	 */
	const ADMIN_ARBITRAGE_DONE_HALF = 'admin_arbitrage_done_half';

	/**
	 * Админ отменил заказ
	 */
	const ADMIN_CHECK_CANCEL = 'admin_check_cancel';

	/**
	 * Админ отменил заказ
	 */
	const ADMIN_INPROGRESS_CANCEL = 'admin_inprogress_cancel';

	/**
	 * Админ возвращает выполненный заказ в арбитраж
	 */
	const ADMIN_DONE_ARBITRAGE = 'admin_done_arbitrage';

	/**
	 * Админ возвращает отмененный заказ в арбитраж
	 */
	const ADMIN_CANCEL_ARBITRAGE = 'admin_cancel_arbitrage';

	/**
	 * Админ вернул отмененный заказ в работу
	 */
	const ADMIN_CANCEL_INPROGRESS = 'admin_cancel_inprogress';

	/**
	 * Администратор вернул выполненный заказ в работу
	 */
	const ADMIN_DONE_INPROGRESS = 'admin_done_inprogress';

	/**
	 * Крон отменяет заказ, если Покупатель запросил обоюдную отмену заказа, а Продавец игнорит 2 дня
	 */
	const CRON_PAYER_INPROGRESS_CANCEL = 'cron_payer_inprogress_cancel';

	/**
	 * Отмена заказа кроном, т.к. продавец не выполнил работу в течении отведённого времени выполнения + 14 дней
	 */
	const CRON_INPROGRESS_INWORK_CANCEL = 'cron_inprogress_inwork_cancel';

	/**
	 * Крон отменяет заказ по невзятию в работу продавцом
	 */
	const CRON_INPROGRESS_CANCEL = 'cron_inprogress_cancel';

	/**
	 * Крон принимает заказ, если Продавец сдал на проверку, а Покупатель игнорит 3 дня
	 */
	const CRON_WORKER_CHECK_DONE = 'cron_worker_check_done';

	/**
	 * Крон отменяет заказ, если Продавец запросил обоюдную отмену заказа, а Покупатель игнорит 2 дня
	 */
	const CRON_WORKER_INPROGRESS_CANCEL = 'cron_worker_inprogress_cancel';

	/**
	 * Продление заказа покупателем
	 */
	const PAYER_EXTEND = 'payer_extend';

	/**
	 * Подсказка покупателю, проставляется кроном через определенное время
	 */
	const PAYER_ADVICE = 'payer_advice';

	/**
	 * Покупатель отправил заказ в арбитраж
	 */
	const PAYER_CHECK_ARBITRAGE = 'payer_check_arbitrage';

	/**
	 * Покупатель отправил заказ в арбитраж
	 */
	const PAYER_INPROGRESS_ARBITRAGE = 'payer_inprogress_arbitrage';

	/**
	 * Покупатель принимает заказа
	 */
	const PAYER_CHECK_DONE = 'payer_check_done';

	/**
	 * Покупатель отправил заказ на доработку
	 */
	const PAYER_CHECK_INPROGRESS = 'payer_check_inprogress';

	/**
	 * Покупатель отменил заказ, если просрочен
	 */
	const PAYER_INPROGRESS_CANCEL = 'payer_inprogress_cancel';

	/**
	 * Покупатель согласился на обоюдную отмену заказа
	 */
	const PAYER_INPROGRESS_CANCEL_CONFIRM = 'payer_inprogress_cancel_confirm';

	/**
	 * Покупатель удалил свой запрос на отмену
	 */
	const PAYER_INPROGRESS_CANCEL_DELETE = 'payer_inprogress_cancel_delete';

	/**
	 * Покупатель отклонил запрос на обоюдную отмену заказа
	 */
	const PAYER_INPROGRESS_CANCEL_REJECT = 'payer_inprogress_cancel_reject';

	/**
	 * Покупатель запросил обоюдную отмену заказа
	 */
	const PAYER_INPROGRESS_CANCEL_REQUEST = 'payer_inprogress_cancel_request';

	/**
	 * Покупатель оплатил заказ
	 */
	const PAYER_NEW_INPROGRESS = 'payer_new_inprogress';

	/**
	 * Покупатель подтверждает выполнение заказа из состояния в работе
	 */
	const PAYER_INPROGRESS_DONE = 'payer_inprogress_done';

	/**
	 * Покупатель поднял уровень пакета
	 */
	const PAYER_UPGRADE_PACKAGE = 'payer_upgrade_package';

	/**
	 * Покупатель докупил количество пакетов
	 */
	const PAYER_BUY_PACKAGES = 'payer_buy_packages';

	/**
	 * Покупатель отправил продавцу бонус (чаевые)
	 */
	const PAYER_SEND_TIPS = 'payer_send_tips';

	/**
	 * Заказ был возвращен в работу, так как были заказаны дополнительные опции.
	 */
	const PAYER_INPROGRESS_ADD_OPTION = 'payer_inprogress_add_option';

	/**
	 * Заказ создан (не отображать в заказе)
	 * @deprecated
	 */
	const CREATE = 'create';

	/**
	 * Предоставлены инструкции покупателем
	 */
	const TEXT_FIRST = 'text_first';

	/**
	 * Псевдотип "Предоставьте информацию по заказу"
	 */
	const MISSING_DATA = 'missing_data';

	/**
	 * Предложение дополнительных опций
	 */
	const EXTRA = 'extra';

	/**
	 * Удаление опций из заказа
	 */
	const DELETE_EXTRA = 'delete_extra';

	/**
	 * Инструкции покупателя (не используется)
	 * @deprecated
	 */
	const INSTRUCTION = 'instruction';

	/**
	 * Обычное сообщение в треке
	 */
	const TEXT = 'text';

	/**
	 * Первое сообщение из лички в треке
	 */
	const FROM_DIALOG = 'from_dialog';

	/**
	 * Покупатель отправил заказ в арбитраж из состояния на проверке
	 */
	const WORKER_CHECK_ARBITRAGE = 'worker_check_arbitrage';

	/**
	 * Продавец отправил заказ в арбитраж из состояния в работе
	 */
	const WORKER_INPROGRESS_ARBITRAGE = 'worker_inprogress_arbitrage';

	/**
	 * Продавец вынуждено отменил заказ (по неуважительной причине)
	 */
	const WORKER_INPROGRESS_CANCEL = 'worker_inprogress_cancel';

	/**
	 * Продавец подтвердил обоюдный запрос на отмену
	 */
	const WORKER_INPROGRESS_CANCEL_CONFIRM = 'worker_inprogress_cancel_confirm';

	/**
	 * Продавец удалил свой запрос на отмену
	 */
	const WORKER_INPROGRESS_CANCEL_DELETE = 'worker_inprogress_cancel_delete';

	/**
	 * Продавец отклонил запрос на отмену от покупателя
	 */
	const WORKER_INPROGRESS_CANCEL_REJECT = 'worker_inprogress_cancel_reject';

	/**
	 * Продавец предложил запрос на отмену
	 */
	const WORKER_INPROGRESS_CANCEL_REQUEST = 'worker_inprogress_cancel_request';

	/**
	 * Продавец отправил заказ на проверку
	 */
	const WORKER_INPROGRESS_CHECK = 'worker_inprogress_check';

	/**
	 * Админ отклонил арбитраж и вернул заказ на проверку
	 */
	const ADMIN_ARBITRAGE_CHECK = "admin_arbitrage_check";

	/**
	 * Продавец взял заказ в работу
	 */
	const WORKER_INWORK = 'worker_inwork';

	/**
	 * Загрузка портфолио продавцом
	 */
	const WORKER_PORTFOLIO = 'worker_portfolio';

	/**
	 * При просрочке отправки отчета на 5 суток, заказ отмениться
	 * @deprecated Нет такого больше
	 */
	const WORKER_REPORT_DEADLINE_CANCEL = 'worker_report_deadline_cancel';

	/**
	 * Продавец предоставил отчет
	 */
	const WORKER_REPORT_NEW = 'worker_report_new';

	/**
	 * Покупатель отправил этапы на доработку (без изменения статуса заказа)
	 */
	const PAYER_REJECT_STAGES = "payer_reject_stages";

	/**
	 * Покупатель отправил этапы на доработку, при этом заказ изменил статус на "В работе"
	 */
	const PAYER_REJECT_STAGES_INPROGRESS = "payer_reject_stages_inprogress";

	/**
	 * Покупатель принял работу по этапу со статуса "на проверке"
	 */
	const PAYER_APPROVE_STAGES = "payer_approve_stages";

	/**
	 * Покупатель принял работу по этапу со статуса "на проверке", при этом заказ изменил статус на "В работе"
	 */
	const PAYER_APPROVE_STAGES_INPROGRESS = "payer_approve_stages_inprogress";

	/**
	 * Автоматическое принятие работы по этапу
	 */
	const CRON_CHECK_APPROVE_STAGE = "cron_check_approve_stage";

	/**
	 * Автоматическое принятие работы по этапу, при этом заказ изменил статус на "В работе"
	 */
	const CRON_CHECK_APPROVE_STAGE_INPROGRESS = "cron_check_approve_stage_inprogress";

	/**
	 * Автоматическая отмена неоплаченных долгое время заказов
	 */
	const CRON_UNPAID_CANCEL = "cron_unpaid_cancel";

	/**
	 * Требуется оплатить этап для продолжения заказа
	 */
	const STAGE_UNPAID = "stage_unpaid";

	/**
	 * Покупатель переводит заказа из состояния "неоплачен" в "в работе" при оплате
	 */
	const PAYER_UNPAID_INPROGRESS = "payer_unpaid_inprogress";

	/**
	 * Покупатель переводит заказа из состояния "выполнен" в "в работе" при оплате
	 */
	const PAYER_DONE_INPROGRESS = "payer_done_inprogress";

	/**
	 * Покупатель переводит заказа из состояния "выполнен" в "в работе" при оплате
	 * при этом заказ отменен из-за неоплаты в срок
	 */
	const PAYER_DONE_INPROGRESS_UNPAID = "payer_done_inprogress_unpaid";

	/**
	 * Решение по арбитражу этапов вынесено, заказ может быть продолжен
	 */
	const ADMIN_ARBITRAGE_STAGE_CONTINUE = "admin_arbitrage_stage_continue";

	/**
	 * Решение по арбитражу этапов вынесено, заказ отменен
	 */
	const ADMIN_ARBITRAGE_STAGE_CANCEL = "admin_arbitrage_stage_cancel";

	/**
	 * Решение по арбитражу этапов вынесено, заказ выполнен
	 */
	const ADMIN_ARBITRAGE_STAGE_DONE = "admin_arbitrage_stage_done";

	/**
	 * Автоматическая отмена перезапущенного из состояния "Выполнен" заказа
	 */
	const CRON_RESTARTED_INPROGRESS_CANCEL = "cron_restarted_inprogress_cancel";

	/**
	 * Покупатель переводит заказ из состояния "Отменен" в состояние "В работе" оплатой
	 * (доступно только после автоотмены неоплачанного заказа)
	 */
	const PAYER_CANCEL_INPROGRESS = "payer_cancel_inprogress";

	/**
	 * Покупатель оплатил этап
	 */
	const PAYER_STAGE_PAID = "payer_stage_paid";

	/**
	 * Получение описаний статусов треков.
	 *
	 * @param array $params Параметры, которые будут подставлены в описания
	 *     [<тип_трека> => [<тип_описания> => [<параметр1>, <параметр2>, ...]]]
	 * @return array
	 */
	public static function getTracksDesc(array $params = []): array {
		return [
			self::ADMIN_ARBITRAGE_CANCEL => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Решение арбитража"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Решение арбитража"),
				"worker" => \Translations::t("Решение арбитража"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::ADMIN_ARBITRAGE_INPROGRESS => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Заказ возвращен в работу продавцу"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Заказ возвращен в работу продавцу"),
				"worker" => \Translations::t("Заказ возвращен в работу продавцу"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::ADMIN_ARBITRAGE_CHECK => [
				"title" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Заказ возвращен покупателю на проверку"),
				"payer_mail" => \Translations::t("Модератор отклонил обращение в арбитраж и вернул заказ на проверку. У вас есть 3 дня на то, чтобы проверить и принять работу, либо вернуть ее на доработку. Обращаем ваше внимание на то, что доработки возможны только в рамках описания кворка и условий заказа."),
				"worker" => \Translations::t("Заказ возвращен покупателю на проверку"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::ADMIN_ARBITRAGE_DONE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Решение арбитража"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Решение арбитража"),
				"worker" => \Translations::t("Решение арбитража"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::ADMIN_ARBITRAGE_DONE_HALF => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Решение арбитража"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Решение арбитража"),
				"worker" => \Translations::t("Решение арбитража"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::ADMIN_CHECK_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"admin" => \Translations::t("Заказ был отменен администраторами Kwork"),
				"admin_short" => \Translations::t("Заказ отменен администраторами Kwork"),
				"payer" => \Translations::t("Заказ был отменен администраторами Kwork"),
				"worker" => \Translations::t("Заказ был отменен администраторами Kwork"),
				"payer_has_paid_stage" => \Translations::t("Заказ был остановлен администраторами Kwork"),
				"worker_has_paid_stage" => \Translations::t("Заказ был остановлен администраторами Kwork"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::ADMIN_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"admin" => \Translations::t("Заказ был отменен модератором"),
				"admin_short" => \Translations::t("Заказ отменен модератором"),
				"payer" => \Translations::t("Заказ был отменен модератором"),
				"worker" => \Translations::t("Заказ был отменен модератором"),
				"payer_has_paid_stage" => \Translations::t("Заказ был остановлен модератором"),
				"worker_has_paid_stage" => \Translations::t("Заказ был остановлен модератором"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::ADMIN_DONE_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"worker" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::ADMIN_CANCEL_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"worker" => \Translations::t("Модератор отправил заказ в арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::ADMIN_CANCEL_INPROGRESS => [
				"title" => \Translations::t("В работе"),
				"admin" => \Translations::t("Модератор вернул заказ в работу"),
				"admin_short" => \Translations::t("В работе"),
				"payer" => \Translations::t("Модератор вернул заказ в работу"),
				"worker" => \Translations::t("Модератор вернул заказ в работу"),
				"color" => "orange",
				"icon" => "ico-orange-box"
			],
			self::ADMIN_DONE_INPROGRESS => [
				"title" => \Translations::t("В работе"),
				"admin" => \Translations::t("Модератор вернул заказ в работу"),
				"admin_short" => \Translations::t("В работе"),
				"payer" => \Translations::t("Модератор вернул заказ в работу"),
				"worker" => \Translations::t("Модератор вернул заказ в работу"),
				"color" => "orange",
				"icon" => "ico-orange-box"
			],
			self::CRON_PAYER_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"admin" => \Translations::t("Заказ был отменен автоматически, т.к. продавец предложил отмену заказа, а покупатель не отклонил его заявку в течение 2-х дней"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Заказ был отменен автоматически, т.к. вы предложили продавцу отмену заказа, а продавец не отклонил вашу заявку в течение 2-х дней"),
				"worker" => \Translations::t("Заказ был отменен автоматически, т.к. покупатель предложил отмену заказа, а вы не отклонили его заявку в течение 2-х дней"),
				"payer_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. вы предложили продавцу остановку заказа, а продавец не отклонил вашу заявку в течение 2-х дней"),
				"worker_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. покупатель предложил остановку заказа, а вы не отклонили его заявку в течение 2-х дней"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::CRON_INPROGRESS_INWORK_CANCEL => [
				"title" => \Translations::t("Заказ был отменен автоматически"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически"),
				"admin" => \Translations::t("Автоматическая отмена заказа. Заказ просрочен на 14 дней, нет активности в заказе"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Продавец задержал сдачу заказа и в заказе долгое время не было никакой активности. Заказ не выполнен и был отменен"),
				"worker" => \Translations::t("Вы задержали сдачу заказа и в заказе долгое время не было никакой активности. Заказ не выполнен и был отменен"),
				"payer_has_paid_stage" => \Translations::t("Продавец задержал сдачу заказа и в заказе долгое время не было никакой активности. Заказ не выполнен и был остановлен"),
				"worker_has_paid_stage" => \Translations::t("Вы задержали сдачу заказа и в заказе долгое время не было никакой активности. Заказ не выполнен и был остановлен"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::CRON_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Автоматическая отмена заказа. Заказ не взят в работу"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Заказ был отменен автоматически, т.к. не был взят продавцом в работу в течение  %s", \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN)),
				"worker" => \Translations::t("Заказ был отменен автоматически, т.к. не был взят вами в работу в течение %s", \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN)),
				"payer_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. не был взят продавцом в работу в течение  %s", \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN)),
				"worker_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. не был взят вами в работу в течение %s", \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN)),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::CRON_RESTARTED_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ автоматически отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ автоматически остановлен"),
				"admin" => \Translations::t("Автоматическая отмена заказа. Заказ не взят в работу"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Так как оплата с вашей стороны вовремя не поступила, у продавца могли измениться планы. К сожалению, он не продолжил работу над заказом. Тем не менее, заказ отменен со снижением рейтинга продавца."),
				"worker" => \Translations::t("В течение %s суток вы не подтвердили согласие продолжить заказ. К сожалению, заказ отменен со снижением рейтинга вашего аккаунта.", \Helper::getAutoCancelDays(true)),
				"payer_has_paid_stage" => \Translations::t("Так как оплата с вашей стороны вовремя не поступила, у продавца могли измениться планы. К сожалению, он не продолжил работу над заказом. Тем не менее, заказ остановлен со снижением рейтинга продавца."),
				"worker_has_paid_stage" => \Translations::t("В течение %s суток вы не подтвердили согласие продолжить заказ. К сожалению, заказ остановлен со снижением рейтинга вашего аккаунта.", \Helper::getAutoCancelDays(true)),
				"color" => "red",
				"icon" => "ico-close",
			],
			self::CRON_WORKER_CHECK_DONE => [
				"title" => \Translations::t("Заказ принят"),
				"admin" => \Translations::t("Получение оплаты за сделанный кворк (заказ был принят автоматически, т.к. продавец сдал выполненную работу на проверку, а покупатель не принял или не отклонил ее в течение 3-х дней)"),
				"admin_short" => \Translations::t("Получение оплаты за сделанный кворк"),
				"payer" => \Translations::t("Заказ был принят автоматически, т.к. продавец сдал вам выполненную работу на проверку, а вы не приняли или не отклонили ее в течение 3-х дней"),
				"worker" => \Translations::t("Заказ был принят автоматически, т.к. вы сдали выполненную работу на проверку, а покупатель не принял или не отклонил ее в течение 3-х дней"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::CRON_WORKER_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Заказ был отменен автоматически, т.к. покупатель предложил продавцу отмену заказа, а продавец не отклонил заявку в течение 2-х дней"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Заказ был отменен автоматически, т.к. продавец предложил отмену заказа, а вы не отклонили его заявку в течение 2-х дней"),
				"worker" => \Translations::t("Заказ был отменен автоматически, т.к. вы предложили покупателю отмену заказа, а покупатель не отклонил вашу заявку в течение 2-х дней"),
				"payer_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. продавец предложил остановку заказа, а вы не отклонили его заявку в течение 2-х дней"),
				"worker_has_paid_stage" => \Translations::t("Заказ был остановлен автоматически, т.к. вы предложили покупателю остановку заказа, а покупатель не отклонил вашу заявку в течение 2-х дней"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::PAYER_EXTEND => [
				"title" => \Translations::t("Заказ был продлён"),
				"admin" => \Translations::t("Покупатель продлил заказ"),
				"payer" => \Translations::t("Вы продлили заказ"),
				"worker" => \Translations::t("Покупатель продлил заказ"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::PAYER_CHECK_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Покупатель перевел заказ в Арбитраж"),
				"payer" => \Translations::t("Вы перевели заказ в Арбитраж"),
				"worker" => \Translations::t("Заказ передан в Арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::PAYER_INPROGRESS_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Покупатель перевел заказ в Арбитраж"),
				"payer" => \Translations::t("Вы перевели заказ в Арбитраж"),
				"worker" => \Translations::t("Заказ передан в Арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::PAYER_CHECK_DONE => [
				"title" => \Translations::t("Заказ выполнен"),
				"admin" => \Translations::t("Получение оплаты за сделанный кворк (покупатель принял работу)"),
				"payer" => \Translations::t("Вы проверили и приняли работу"),
				"worker" => \Translations::t("Покупатель проверил и принял работу"),
				"color" => "green",
				"icon" => "ico-check"
			],
			self::PAYER_CHECK_INPROGRESS => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы вернули заказ на доработку.</br>Продавец внесет исправления и снова отправит работу на проверку"),
				"worker" => \Translations::t("Покупатель вернул заказ на доработку. Пожалуйста, внесите правки в работу и сдайте ее на повторную проверку"),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::PAYER_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Покупатель отменил заказ"),
				"admin_short" => \Translations::t("Покупатель отменил заказ"),
				"payer" => \Translations::t("Вы отменили заказ"),
				"worker" => \Translations::t("Покупатель отменил заказ"),
				"payer_has_paid_stage" => \Translations::t("Вы остановили заказ"),
				"worker_has_paid_stage" => \Translations::t("Покупатель остановил заказ"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::PAYER_INPROGRESS_CANCEL_CONFIRM => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Заказ отменён по согласованию сторон (предложил продавец)"),
				"admin_short" => \Translations::t("Заказ отменён по согласованию"),
				"payer" => \Translations::t("Заказ отменён по согласованию сторон"),
				"worker" => \Translations::t("Заказ отменён по согласованию сторон"),
				"payer_has_paid_stage" => \Translations::t("Заказ остановлен по согласованию сторон"),
				"worker_has_paid_stage" => \Translations::t("Заказ остановлен по согласованию сторон"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::PAYER_INPROGRESS_CANCEL_DELETE => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы передумали отменять заказ"),
				"worker" => \Translations::t("Покупатель передумал отменять заказ"),
				"payer_has_paid_stage" => \Translations::t("Вы передумали останавливать заказ"),
				"worker_has_paid_stage" => \Translations::t("Покупатель передумал останавливать заказ"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::PAYER_INPROGRESS_CANCEL_REJECT => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы отказались отменять заказ"),
				"worker" => \Translations::t("Покупатель отказался отменять заказ"),
				"payer_has_paid_stage" => \Translations::t("Вы отказались останавливать заказ"),
				"worker_has_paid_stage" => \Translations::t("Покупатель отказался останавливать заказ"),
				"color" => "orange",
				"icon" => "ico-reject-info"
			],
			self::PAYER_INPROGRESS_CANCEL_REQUEST => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы предложили отменить заказ"),
				"worker" => \Translations::t("Покупатель предложил отменить заказ"),
				"payer_has_paid_stage" => \Translations::t("Вы предложили остановить заказ"),
				"worker_has_paid_stage" => \Translations::t("Покупатель предложил остановить заказ"),
				"color" => "red",
				"icon" => "ico-quest-red-info"
			],
			self::PAYER_NEW_INPROGRESS => [
				"title" => \Translations::t("Создан новый заказ"),
				"payer" => \Translations::t("Вы создали заказ"),
				"worker" => \Translations::t("Покупатель создал заказ"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::PAYER_INPROGRESS_DONE => [
				"title" => \Translations::t("Заказ выполнен"),
				"admin" => \Translations::t("Получение оплаты за сделанный кворк (покупатель принял работу)"),
				"payer" => \Translations::t("Вы приняли работу"),
				"worker" => \Translations::t("Покупатель принял работу"),
				"color" => "green",
				"icon" => "ico-check"
			],
			self::PAYER_UPGRADE_PACKAGE => [
				"title" => \Translations::t("Повышение уровня кворка"),
				"payer" => \Translations::t("Вы повысили уровень кворка"),
				"worker" => \Translations::t("Покупатель повысил уровень кворка"),
				"color" => "green",
				"icon" => "ico-green-rocket"
			],
			self::PAYER_BUY_PACKAGES => [
				"title" => \Translations::t("Покупка дополнительных пакетов кворка"),
				"payer" => \Translations::t("Вы купили дополнительные пакеты кворка"),
				"worker" => \Translations::t("Покупатель купил дополнительные пакеты кворка"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::PAYER_INPROGRESS_ADD_OPTION => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Заказ был возвращен в работу, т.к. изменен состав заказа."),
				"worker" => \Translations::t("Заказ был возвращен в работу, т.к. изменен состав заказа."),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::WORKER_CHECK_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Продавец перевел заказ Арбитраж"),
				"payer" => \Translations::t("Заказ передан в Арбитраж"),
				"worker" => \Translations::t("Вы перевели заказ в Арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::WORKER_INPROGRESS_ARBITRAGE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Продавец перевел заказ в Арбитраж"),
				"payer" => \Translations::t("Заказ передан в Арбитраж"),
				"worker" => \Translations::t("Вы перевели заказ в Арбитраж"),
				"admin_short" => \Translations::t("Арбитраж"),
				"color" => "orange",
				"icon" => "ico-quest-info"
			],
			self::WORKER_INPROGRESS_CANCEL => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Продавец отменил заказ"),
				"admin_short" => \Translations::t("Продавец отменил заказ"),
				"payer" => \Translations::t("Продавец отменил заказ"),
				"worker" => \Translations::t("Вы отменили заказ"),
				"payer_has_paid_stage" => \Translations::t("Продавец остановил заказ"),
				"worker_has_paid_stage" => \Translations::t("Вы остановили заказ"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::WORKER_INPROGRESS_CANCEL_CONFIRM => [
				"title" => \Translations::t("Заказ был отменен"),
				"title_has_paid_stage" => \Translations::t("Заказ был остановлен"),
				"admin" => \Translations::t("Заказ отменён по согласованию сторон (предложил покупатель)"),
				"admin_short" => \Translations::t("Заказ отменён по согласованию"),
				"payer" => \Translations::t("Заказ отменён по согласованию сторон"),
				"worker" => \Translations::t("Заказ отменён по согласованию сторон"),
				"payer_has_paid_stage" => \Translations::t("Заказ остановлен по согласованию сторон"),
				"worker_has_paid_stage" => \Translations::t("Заказ остановлен по согласованию сторон"),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::WORKER_INPROGRESS_CANCEL_DELETE => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Продавец передумал отменять заказ"),
				"worker" => \Translations::t("Вы передумали отменять заказ"),
				"payer_has_paid_stage" => \Translations::t("Продавец передумал останавливать заказ"),
				"worker_has_paid_stage" => \Translations::t("Вы передумали останавливать заказ"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::WORKER_INPROGRESS_CANCEL_REJECT => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Продавец не согласился на отмену заказа"),
				"worker" => \Translations::t("Вы не согласились на отмену заказа"),
				"payer_has_paid_stage" => \Translations::t("Продавец не согласился на остановку заказа"),
				"worker_has_paid_stage" => \Translations::t("Вы не согласились на остановку заказа"),
				"color" => "orange",
				"icon" => "ico-reject-info"
			],
			self::WORKER_INPROGRESS_CANCEL_REQUEST => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Продавец предложил отменить заказ"),
				"worker" => \Translations::t("Вы предложили отменить заказ"),
				"payer_has_paid_stage" => \Translations::t("Продавец предложил остановить заказ"),
				"worker_has_paid_stage" => \Translations::t("Вы предложили остановить заказ"),
				"color" => "red",
				"icon" => "ico-quest-red-info"
			],
			self::WORKER_INPROGRESS_CHECK => [
				"title" => \Translations::t("Заказ сдан на проверку"),
				"payer" => \Translations::t("Продавец отправил работу на проверку"),
				"payer_mail" => \Translations::t("Продавец отправил выполненную работу на проверку. У вас есть 3 дня на то, чтобы проверить и принять работу, либо вернуть ее на доработку. Обращаем ваше внимание на то, что доработки возможны только в рамках описания кворка и условий заказа."),
				"worker" => \Translations::t("Вы отправили работу на проверку"),
				"color" => "green",
				"icon" => "ico-green-box"
			],
			self::WORKER_INWORK => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Продавец приступил к работе над заказом"),
				"worker" => \Translations::t("Вы приступили к работе над заказом"),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::WORKER_REPORT_NEW => [
				"title" => \Translations::t("Промежуточный отчет"),
				"payer" => \Translations::t("Продавец отправил отчет по заказу"),
				"worker" => \Translations::t("Вы отправили отчет по заказу"),
				"color" => "green",
				"icon" => "ico-quest-info"
			],
			self::WORKER_REPORT_DEADLINE_CANCEL => [
				"title" => \Translations::t("Заказ был отменен автоматически"),
				"payer" => \Translations::t("Продавец задержал сдачу отчета на 5 суток. Заказ не выполнен и отменен."),
				"worker" => \Translations::t("Отчет не сдан в течении 5 суток. Заказ не выполнен и отменен."),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::CREATE => [
				"title" => \Translations::t("Создан новый заказ"),
				"payer" => \Translations::t("Вы создали заказ"),
				"worker" => \Translations::t("Покупатель создал заказ"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::TEXT_FIRST => [
				"title" =>  \Translations::t("Данные предоставлены"),
				"payer" => \Translations::t('Вы предоставили нужные данные продавцу'),
				"worker" => \Translations::t('Получена информация от покупателя'),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::MISSING_DATA => [
				"title" => \Translations::t('Предоставьте информацию по заказу'),
				"payer" => in_array($params["source_type"], [\OrderManager::SOURCE_WANT_PRIVATE, \OrderManager::SOURCE_INBOX_PRIVATE]) ? "" : \Translations::t('Для работы над заказом продавцу нужна следующая информация:'),
				"worker" => '',
				"color" => "green",
				"icon" => "ico-quest-info"
			],
			self::PAYER_UNPAID_INPROGRESS => [
				"title" => \Translations::t("Оплата этапа зарезервирована"),
				"payer" => \Translations::t("Продавец продолжает работу над заказом."),
				"worker" => \Translations::t("Теперь можно приступать к работе над следующим этапом заказа. Деньги на оплату зарезервированы:"),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::PAYER_DONE_INPROGRESS => [
				"title" => \Translations::t("Оплата этапа зарезервирована"),
				"payer" => \Translations::t("Продавцу отправлен запрос на согласие продолжить заказ. Ответ поступит не позднее %s суток.", \Helper::getAutoCancelDays(true)),
				"worker" => \Translations::t("Покупатель внес оплату для возобновления заказа. Подтвердите готовность продолжить работу над заказом."),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::PAYER_DONE_INPROGRESS_UNPAID => [
				"title" => \Translations::t("Оплата этапа зарезервирована"),
				"payer" => \Translations::t("На внесение оплаты ушло более %s суток. Поэтому продавцу отправлен запрос на согласие продолжить заказ. Ответ поступит не позднее %s суток.", OrderStageManager::getUnpaidCancelDays(), \Helper::getAutoCancelDays(true)),
				"worker" => \Translations::t("Покупатель внес оплату для возобновления заказа. Подтвердите готовность продолжить работу над заказом."),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::PAYER_CANCEL_INPROGRESS => [
				"title" => \Translations::t("Оплата этапа зарезервирована"),
				"payer" => \Translations::t("На внесение оплаты ушло более %s суток. Работа над заказом будет продолжена после подтверждения готовности продавцом.", OrderStageManager::getUnpaidCancelDays()),
				"worker" => \Translations::t("На внесение оплаты покупателем ушло более %s суток. Подтвердите готовность продолжить работу над заказом.", OrderStageManager::getUnpaidCancelDays()),
				"color" => "green",
				"icon" => "ico-green-truck"
			],
			self::STAGE_UNPAID => [
				"title" => \Translations::t("Требуется оплата этапа"),
				"payer" => \Translations::t("Необходимо оплатить работу над следующим этапом, чтобы продавец мог продолжить свою работу. До момента оплаты таймер заказа останавливается."),
				"worker" => \Translations::t("Ожидается пополнение баланса покупателем"),
				"color" => "green",
				"icon" => "ico-quest-info"
			],
			self::CRON_UNPAID_CANCEL => [
				"title" => \Translations::t("Заказ автоматически остановлен"),
				"admin" => \Translations::t("Автоматическая отмена заказа. Покупатель не зарезервировал средства для оплаты заказа"),
				"admin_short" => \Translations::t("Заказ отменен автоматически"),
				"payer" => \Translations::t("Время ожидания оплаты истекло. Однако вы можете возобновить заказ, в том числе отредактировать или добавить этапы."),
				"worker" => \Translations::t("Время ожидания оплаты от покупателя истекло. Однако в дальнейшем покупатель может внести оплату для возобновления заказа."),
				"color" => "red",
				"icon" => "ico-close"
			],
			self::PAYER_APPROVE_STAGES => [
				"title" => \Translations::t("Этапы выполнены"),
				"payer" => \Translations::t("Вы проверили и подтвердили выполнение этапов"),
				"worker" => \Translations::t("Покупатель проверил и принял работу по этапам"),
				"color" => "green",
				"icon" => "ico-check"
			],
			self::PAYER_APPROVE_STAGES_INPROGRESS => [
				"title" => \Translations::t("Этапы выполнены"),
				"payer" => \Translations::t("Вы проверили и подтвердили выполнение этапов"),
				"worker" => \Translations::t("Покупатель проверил и принял работу по этапам"),
				"color" => "green",
				"icon" => "ico-check"
			],
			self::CRON_CHECK_APPROVE_STAGE => [
				"title" => \Translations::t("Этапы выполнены"),
				"payer" => \Translations::t("Продавец сдал вам выполненную работу по этапам на проверку, а вы не приняли или не отклонили ее в течение 3-х дней. Этапы приняты автоматически."),
				"worker" => \Translations::t("Этапы приняты автоматически"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::CRON_CHECK_APPROVE_STAGE_INPROGRESS => [
				"title" => \Translations::t("Этапы выполнены"),
				"payer" => \Translations::t("Продавец сдал вам выполненную работу по этапам на проверку, а вы не приняли или не отклонили ее в течение 3-х дней. Этапы приняты автоматически."),
				"worker" => \Translations::t("Этапы приняты автоматически"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
			self::PAYER_REJECT_STAGES => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы вернули этапы на доработку"),
				"worker" => \Translations::t("Покупатель вернул сданную работу по этапам на доработку"),
				"color" => "green",
				"icon" => "ico-green-truck",
			],
			self::PAYER_REJECT_STAGES_INPROGRESS => [
				"title" => \Translations::t("В работе"),
				"payer" => \Translations::t("Вы вернули этапы на доработку"),
				"worker" => \Translations::t("Покупатель вернул сданную работу по этапам на доработку"),
				"color" => "green",
				"icon" => "ico-green-truck",
			],
			self::ADMIN_ARBITRAGE_STAGE_CONTINUE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Арбитраж решен с продолжением заказа"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Арбитраж решен с продолжением заказа"),
				"worker" => \Translations::t("Арбитраж решен с продолжением заказа"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::ADMIN_ARBITRAGE_STAGE_CANCEL => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Арбитраж решен с отменой заказа"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Арбитраж решен с отменой заказа"),
				"worker" => \Translations::t("Арбитраж решен с отменой заказа"),
				"payer_has_paid_stage" => \Translations::t("Арбитраж решен с остановкой заказа"),
				"worker_has_paid_stage" => \Translations::t("Арбитраж решен с остановкой заказа"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::ADMIN_ARBITRAGE_STAGE_DONE => [
				"title" => \Translations::t("Арбитраж"),
				"admin" => \Translations::t("Арбитраж решен с завершением заказа"),
				"admin_short" => \Translations::t("Арбитраж"),
				"payer" => \Translations::t("Арбитраж решен с завершением заказа"),
				"worker" => \Translations::t("Арбитраж решен с завершением заказа"),
				"color" => "orange",
				"icon" => "ico-check"
			],
			self::PAYER_STAGE_PAID => [
				"title" => \Translations::t("Оплата этапа зарезервирована"),
				"color" => "green",
				"icon" => "ico-more-info"
			],
		];
	}

	/**
	 * Типы, которые инициируются из админки (видимо).
	 *
	 * @return array
	 *   тип => тип
	 */
	public static function getAdminTypes(): array {
		$types = [
			self::ADMIN_ARBITRAGE_CANCEL,
			self::ADMIN_ARBITRAGE_INPROGRESS,
			self::ADMIN_ARBITRAGE_CHECK,
			self::ADMIN_ARBITRAGE_DONE,
			self::ADMIN_ARBITRAGE_DONE_HALF,
			self::ADMIN_CHECK_CANCEL,
			self::ADMIN_INPROGRESS_CANCEL,
			self::ADMIN_DONE_ARBITRAGE,
			self::ADMIN_CANCEL_ARBITRAGE,
			self::ADMIN_CANCEL_INPROGRESS,
			self::ADMIN_DONE_INPROGRESS,
			self::ADMIN_ARBITRAGE_STAGE_CONTINUE,
			self::ADMIN_ARBITRAGE_STAGE_DONE,
			self::ADMIN_ARBITRAGE_STAGE_CANCEL,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы, которые инициируются кроном.
	 *
	 * @return array
	 *   тип => тип
	 */
	public static function getCronTypes(): array {
		$types = [
			self::CRON_PAYER_INPROGRESS_CANCEL,
			self::CRON_INPROGRESS_INWORK_CANCEL,
			self::CRON_INPROGRESS_CANCEL,
			self::CRON_RESTARTED_INPROGRESS_CANCEL,
			self::CRON_WORKER_CHECK_DONE,
			self::CRON_WORKER_INPROGRESS_CANCEL,
			self::CRON_CHECK_APPROVE_STAGE,
			self::CRON_CHECK_APPROVE_STAGE,
			self::CRON_CHECK_APPROVE_STAGE_INPROGRESS,
			self::CRON_UNPAID_CANCEL,
			self::STAGE_UNPAID,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы, которые инициируются покупателем.
	 *
	 * @return array
	 *   тип => тип
	 */
	public static function getPayerTypes(): array {
		$types = [
			self::PAYER_EXTEND,
			self::PAYER_ADVICE,
			self::PAYER_CHECK_ARBITRAGE,
			self::PAYER_INPROGRESS_ARBITRAGE,
			self::PAYER_CHECK_DONE,
			self::PAYER_CHECK_INPROGRESS,
			self::PAYER_INPROGRESS_CANCEL,
			self::PAYER_INPROGRESS_CANCEL_CONFIRM,
			self::PAYER_INPROGRESS_CANCEL_DELETE,
			self::PAYER_INPROGRESS_CANCEL_REJECT,
			self::PAYER_INPROGRESS_CANCEL_REQUEST,
			self::PAYER_NEW_INPROGRESS,
			self::PAYER_INPROGRESS_DONE,
			self::PAYER_UPGRADE_PACKAGE,
			self::PAYER_BUY_PACKAGES,
			self::TEXT_FIRST,
			self::PAYER_APPROVE_STAGES,
			self::PAYER_APPROVE_STAGES_INPROGRESS,
			self::PAYER_REJECT_STAGES,
			self::PAYER_REJECT_STAGES_INPROGRESS,
			self::PAYER_INPROGRESS_ADD_OPTION,
			self::PAYER_SEND_TIPS,
			self::PAYER_UNPAID_INPROGRESS,
			self::PAYER_DONE_INPROGRESS,
			self::PAYER_DONE_INPROGRESS_UNPAID,
			self::PAYER_CANCEL_INPROGRESS,
			self::PAYER_STAGE_PAID,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы треков, который показываются только покупателю
	 * @return array
	 */
	public static function getPayerOnlyTypes(): array {
		$types = [
			self::PAYER_ADVICE,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы, которые инициируются продавцом.
	 *
	 * @return array
	 *   тип => тип
	 */
	public static function getWorkerTypes(): array {
		$types = [
			self::WORKER_CHECK_ARBITRAGE,
			self::WORKER_INPROGRESS_ARBITRAGE,
			self::WORKER_INPROGRESS_CANCEL,
			self::WORKER_INPROGRESS_CANCEL_CONFIRM,
			self::WORKER_INPROGRESS_CANCEL_DELETE,
			self::WORKER_INPROGRESS_CANCEL_REJECT,
			self::WORKER_INPROGRESS_CANCEL_REQUEST,
			self::WORKER_INPROGRESS_CHECK,
			self::WORKER_INWORK,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы, которые относятся к решению арбитража админом
	 *
	 * @return array
	 *   тип => тип
	 */
	public static function getAdminArbitrageTypes(): array {
		$types = [
			self::ADMIN_ARBITRAGE_DONE,
			self::ADMIN_ARBITRAGE_CANCEL,
			self::ADMIN_ARBITRAGE_DONE_HALF,
			self::ADMIN_ARBITRAGE_INPROGRESS,
			self::ADMIN_ARBITRAGE_STAGE_CONTINUE,
			self::ADMIN_ARBITRAGE_STAGE_CANCEL,
			self::ADMIN_ARBITRAGE_STAGE_DONE,
			self::ADMIN_ARBITRAGE_CHECK,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы пользовательских сообщений.
	 *
	 * @return array
	 * 	 тип => тип
	 */
	public static function getUserTypes(): array {
		$types = [
			self::TEXT,
			self::TEXT_FIRST,
			self::FROM_DIALOG,
		];

		return array_combine($types, $types);
	}

	public static function isExtra(string $type) {
		return self::EXTRA == $type || self::DELETE_EXTRA == $type;
	}

	public static function isInprogressCancel(string $type) {
		return in_array($type, self::cancelRequestTypes());
	}

	/**
	 * Трек этого типа сразу создается прочитанным?
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isCreatedImmediatelyRead(string $type) {
		$types = [
			self::CREATE,
			self::PAYER_NEW_INPROGRESS,
			self::PAYER_ADVICE,
		];

		return in_array($type, $types);
	}

	/**
	 * Типы треков, создание которых переводит заказ в выполняемое состояние.
	 *
	 * @return array
	 */
	public static function getToRunOrderTypes() {
		$types = [
			self::TEXT_FIRST,
			self::WORKER_INWORK,
			self::ADMIN_ARBITRAGE_INPROGRESS,
			self::ADMIN_CANCEL_INPROGRESS,
			self::ADMIN_DONE_INPROGRESS,
			self::PAYER_CHECK_INPROGRESS,
			self::PAYER_INPROGRESS_ADD_OPTION,
			self::PAYER_INPROGRESS_CANCEL_DELETE,
			self::PAYER_INPROGRESS_CANCEL_REJECT,
			self::WORKER_INPROGRESS_CANCEL_DELETE,
			self::WORKER_INPROGRESS_CANCEL_REJECT,
			self::PAYER_UNPAID_INPROGRESS,
			self::PAYER_REJECT_STAGES_INPROGRESS,
			self::PAYER_APPROVE_STAGES_INPROGRESS,
			self::CRON_CHECK_APPROVE_STAGE_INPROGRESS,
			self::PAYER_DONE_INPROGRESS,
			self::PAYER_DONE_INPROGRESS_UNPAID,
			self::PAYER_CANCEL_INPROGRESS,
			self::PAYER_UNPAID_INPROGRESS,
		];

		return array_combine($types, $types);
	}

	/**
	 * Типы треков, создание которых переводит заказ в состояние паузы.
	 *
	 * @return array
	 */
	public static function getToPauseOrderTypes() {
		$types = [
			self::PAYER_CHECK_ARBITRAGE,
			self::PAYER_INPROGRESS_ARBITRAGE,
			self::PAYER_INPROGRESS_CANCEL_REQUEST,
			self::WORKER_INPROGRESS_ARBITRAGE,
			self::WORKER_INPROGRESS_CANCEL_REQUEST,
			self::WORKER_INPROGRESS_CHECK,
			self::STAGE_UNPAID,
			self::WORKER_CHECK_ARBITRAGE,
		];

		return array_combine($types, $types);
	}

	/**
	 * Трек этого типа переводит заказ в выполняемое состояние?
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isToRunOrder(string $type): bool {
		$toRunTypes = self::getToRunOrderTypes();

		return isset($toRunTypes[$type]);
	}

	/**
	 * Трек этого типа переводит заказ в состояние паузы?
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isToPauseOrder(string $type): bool {
		$toPauseTypes = self::getToPauseOrderTypes();

		return isset($toPauseTypes[$type]);
	}

	/**
	 * Трек этого типа запускает заказ на выполнение и инициирован админом?
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isAdminRunOrderType(string $type): bool {
		$types = [
			self::ADMIN_ARBITRAGE_INPROGRESS,
			self::ADMIN_ARBITRAGE_CHECK,
			self::ADMIN_CANCEL_INPROGRESS,
			self::ADMIN_DONE_INPROGRESS,
		];

		return in_array($type, $types);
	}

	/**
	 * Типы треков которые приводят к статусу done
	 * @return array
	 */
	public static function doneTypes(): array {
		return [
			Type::ADMIN_ARBITRAGE_DONE,
			Type::ADMIN_ARBITRAGE_DONE_HALF,
			Type::PAYER_CHECK_DONE,
			Type::PAYER_INPROGRESS_DONE,
			Type::CRON_WORKER_CHECK_DONE,
		];
	}

	/**
	 * Типы треков которые приводят к статусу cancel
	 * (Внимание, в случае если в заказе есть выполненные этапы то некоторые из этих треков могут приводить к статусу done)
	 *
	 * @return array
	 */
	public static function cancelTypes(): array {
		return [
			Type::ADMIN_ARBITRAGE_CANCEL,
			Type::ADMIN_CHECK_CANCEL,
			Type::ADMIN_INPROGRESS_CANCEL,
			Type::CRON_PAYER_INPROGRESS_CANCEL,
			Type::CRON_INPROGRESS_INWORK_CANCEL,
			Type::CRON_INPROGRESS_CANCEL,
			Type::CRON_RESTARTED_INPROGRESS_CANCEL,
			Type::CRON_WORKER_INPROGRESS_CANCEL,
			Type::PAYER_INPROGRESS_CANCEL,
			Type::PAYER_INPROGRESS_CANCEL_CONFIRM,
			Type::WORKER_INPROGRESS_CANCEL,
			Type::WORKER_INPROGRESS_CANCEL_CONFIRM,
		];
	}

	/**
	 * Типы треков не являющиеся значимыми для состояния заказа
	 * их не должно быть в order.last_track_id
	 *
	 * @return array
	 */
	public static function unimportantTypes(): array {
		return [
			Type::TEXT,
			Type::TEXT_FIRST,
			Type::WORKER_PORTFOLIO,
			Type::PAYER_ADVICE,
			Type::WORKER_REPORT_NEW,
		];
	}

	/**
	 * Типы треков служащие инициатором арбитража
	 *
	 * @return array
	 */
	public static function arbitrageInitateTypes(): array {
		return [
			Type::PAYER_CHECK_ARBITRAGE,
			Type::PAYER_INPROGRESS_ARBITRAGE,
			Type::WORKER_CHECK_ARBITRAGE,
			Type::WORKER_INPROGRESS_ARBITRAGE,
			Type::ADMIN_DONE_ARBITRAGE,
			Type::ADMIN_CANCEL_ARBITRAGE,
		];
	}

	/**
	 * Типы треков которые означают отправку в доработку
	 *
	 * @return array
	 */
	public static function reworkTypes(): array {
		return [
			Type::PAYER_CHECK_INPROGRESS,
			Type::PAYER_REJECT_STAGES,
			Type::PAYER_REJECT_STAGES_INPROGRESS,
		];
	}

	/**
	 * Треки запроса на отмену заказа
	 *
	 * @return array
	 */
	public static function cancelRequestTypes():array {
		return [
			Type::WORKER_INPROGRESS_CANCEL_REQUEST,
			Type::PAYER_INPROGRESS_CANCEL_REQUEST,
		];
	}

	/**
	 * Возвращает список псевдонимов отмены заказа для сообщения о снижении рейтинга
	 *
	 * @return array
	 */
	public static function getCancelTypesForRatingMessage(): array {
		return [
			Type::PAYER_INPROGRESS_CANCEL,
			Type::PAYER_INPROGRESS_CANCEL_CONFIRM,
			Type::CRON_INPROGRESS_CANCEL,
			Type::CRON_INPROGRESS_INWORK_CANCEL,
			Type::CRON_PAYER_INPROGRESS_CANCEL,
			Type::ADMIN_INPROGRESS_CANCEL,
			Type::WORKER_INPROGRESS_CANCEL,
			Type::WORKER_INPROGRESS_CANCEL_CONFIRM,
			Type::CRON_UNPAID_CANCEL,
		];
	}

	/**
	 * Определить является ли тип трека типом, формируемым кронами
	 * @param string $type Тип трека
	 * @return bool
	 */
	public static function isCronType($type) {
		return in_array($type, Type::getCronTypes());
	}

	/**
	 * Возвращает типы треков которые бывают при рестарте заказа
	 *
	 * @return array
	 */
	public static function getOrderRestartTypes(): array {
		return [
			Type::PAYER_DONE_INPROGRESS_UNPAID,
			Type::PAYER_DONE_INPROGRESS,
			Type::PAYER_CANCEL_INPROGRESS,
		];
	}

	/**
	 * Возвращает типы треков которые приводят к завершению заказа (выполнен или отменен)
	 *
	 * @return array
	 */
	public static function getOrderFinishTypes(): array {
		return array_unique(array_merge(self::doneTypes(), self::cancelTypes()));
	}

	/**
	 * Получить ключ с суффиксом в зависимость от того есть ли оплаченные этапы
	 *
	 * @param string $accessKey Ключ в trackDesc title|payer|worker
	 * @param bool $hasPaidStages Есть ли оплаченные этапы в заказе
	 *
	 * @return string
	 */
	public static function getAccessKeysWithSuffix(string $accessKey, bool $hasPaidStages) {
		if ($hasPaidStages) {
			return $accessKey . "_has_paid_stage";
		}
		return $accessKey;
	}
	/**
	 * Список текстовых типов трека
	 * @return array
	 */
	public static function getTextTypes(): array {
		return [
			Type::TEXT,
			Type::TEXT_FIRST,
			Type::FROM_DIALOG,
		];
	}
}
