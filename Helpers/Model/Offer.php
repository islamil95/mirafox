<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Предложение на запрос услуг
 *
 * @property int $id Идентификатор
 * @property int $user_id Идентификатор пользователя (продавца)
 * @property int $want_id Идентификатор запроса
 * @property int $order_id Идентификатор сформированного заказа-предложения в таблице orders
 * @property int $kwork_id Идентификатор кворка в предложении
 * @property string $comment Комментарий для покупателя
 * @property string $status Статус active|delete|cancel|done|reject
 * @property string $date_create Дата создания
 * @property bool $highlighted Выделенный
 * @property bool $comment_doubles_description Комментарий к предложению дублирует опписание кворка
 * @property bool $is_read - прочитанно ли предложение
 *
 * Связанные модели
 *
 * @property-read \Model\Kwork $kwork Модель кворка
 * @property-read \Model\User $user Модель пользователя создателя (продавца)
 * @property-read \Model\Want $want Модель запроса на услуги
 * @property-read \Model\Order $order Модель заказа-предложения
 *
 * @mixin \EloquentTypeHinting
 */
class Offer extends Model {

	/**
	 * Название таблицы
	 */
	const TABLE_NAME = "offer";

	/**
	 * Идентификатор
	 */
	const FIELD_ID = "id";

	/**
	 * Идентификатор пользователя (продавца)
	 */
	const FIELD_USER_ID = "user_id";

	/**
	 * Идентификатор запроса
	 */
	const FIELD_WANT_ID = "want_id";

	/**
	 * Идентификатор заказа
	 */
	const FIELD_ORDER_ID = "order_id";

	/**
	 * Идентификатор кворка
	 */
	const FIELD_KWORK_ID = "kwork_id";

	/**
	 * Комментарий для покупателя
	 */
	const FIELD_COMMENT = "comment";

	/**
	 * Дата создания
	 */
	const FIELD_DATE_CREATE = "date_create";

	/**
	 * Статус active|delete|cancel|done|reject
	 */
	const FIELD_STATUS = "status";

	/**
	 * Выделенный ли
	 */
	const FIELD_HIGHLIGHTED = "highlighted";

	/**
	 * Прочтено ли предложение продавца
	 */
	const FIELD_IS_READ = "is_read";

	/**
	 * Комментарий к предложению дублирует опписание кворка
	 * (для предложений сделанных через новую форму индивидуального предложения)
	 */
	const FIELD_COMMENT_DOUBLES_DESCRIPTION = "comment_doubles_description";

	/**
	 * Период актуальности предложения
	 */
	const ACTUAL_PERIOD = \Helper::ONE_DAY * 30;


	/**
	 * @var string Настройка модели - установка названия таблицы
	 */
	protected $table = self::TABLE_NAME;

	/**
	 * @var bool Отключаем встроенную обработку created_at, updated_at
	 */
	public $timestamps = false;

	/**
	 * Связь c кворками
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function kwork() {
		return $this->belongsTo(Kwork::class, self::FIELD_KWORK_ID, Kwork::FIELD_PID);
	}

	/**
	 * Связь с пользователем
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, self::FIELD_USER_ID, User::FIELD_USERID);
	}

	/**
	 * Связь с запросами
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function want() {
		return $this->belongsTo(Want::class, self::FIELD_WANT_ID, Want::FIELD_ID);
	}

	/**
	 * Связь с заказами (предложениями)
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function order() {
		return $this->belongsTo(Order::class, self::FIELD_ORDER_ID, Order::FIELD_OID);
	}

	/**
	 * Предложение принято?
	 *
	 * @return bool
	 */
	public function isDone(): bool {
		return $this->status === \OfferManager::STATUS_DONE;
	}

	/**
	 * Предложение активно?
	 *
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->status === \OfferManager::STATUS_ACTIVE;
	}

	/**
	 * Может ли пользователь редактировать предложение
	 * @return bool
	 */
	public function isCanEdit(): bool {
		$allowEditTime = strtotime($this->date_create) + \App::config("offer.edit_time") * \Helper::ONE_MINUTE;
		if ($allowEditTime < time()) {
			return false;
		}

		return $this->status == \OfferManager::STATUS_ACTIVE;
	}

	/**
	 * Вернёт true если срок предложения не истёк
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function isActual(): bool {
		$actualEndDate = time() - self::ACTUAL_PERIOD;
		$dateCreate = (new \DateTime($this->date_create))->getTimestamp();
		return ($dateCreate > $actualEndDate);
	}

	/**
	 * Вернёт true если срок предложения истёк
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function isNotActual(): bool {
		return !$this->isActual();
	}
}