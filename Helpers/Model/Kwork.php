<?php

namespace Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model\Package\Kwork\KworkPackage;
use Model\Scopes\Category\WithoutCustomOffer;
use KworkManager;

/**
 * Модель кворка для таблицы posts
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int PID
 * @property int USERID
 * @property string gtitle
 * @property int active
 * @property string date_active
 * @property int feat - Активность кворка выставляемая продавцом
 * @property int category
 * @property string url
 * @property string photo
 * @property int time_added
 * @property string gdesc
 * @property string ginst
 * @property string gwork
 * @property int days
 * @property string youtube
 * @property int rating
 * @property int irating
 * @property int crating
 * @property int viewcount
 * @property string pip
 * @property float price
 * @property float rev
 * @property float ctp
 * @property int bookmark_count
 * @property int avgWorkTime
 * @property int queueCount
 * @property int moder_date
 * @property string linkType
 * @property int date_block
 * @property int date_modify
 * @property int done_order_count
 * @property int good_comments_count
 * @property int bad_comments_count
 * @property int rotation_weight
 * @property int similar_weight
 * @property int date_feat
 * @property bool is_package
 * @property int last_kwork_moder_id
 * @property bool is_quick
 * @property string pause_reason - Причина постановки кворка на паузу
 * @property string lang
 * @property int twin_id
 * @property bool weekends_pause - Метка что кворк был остановлен на выходные
 * @property int last_3_months_revenue
 * @property string bonus_text
 * @property int bonus_moderate_status
 * @property int bonus_moderate_last_date
 * @property int volume_type_id Идентификатор выбранного продавцом в кворке типа числового объема
 * @property int volume Числовой объем кворка
 * @property int worker_status_feat Значение поля feat перед включением статуса продавца "Занят"
 * @property boolean has_portfolio Имеет ли портфолио
 * @property int need_moderate Статус автомодерации
 * @property string photo_hash Хеш обложки кворка для поиска одинаковых изображений
 * @property string photo_crop Параметры кропа обложки кворка
 * @property bool photo_has_original Для обложки кворка есть оригинальный размер
 * @property string last_buyed_at Дата последнего заказа кворка
 * @property float min_volume_price Миниимальная стоимость заказа для кворка
 * @property bool $is_resizing Проводится ли оптимизация и ресайз изоражения
 *
 * Связанные модели
 *
 * @property Category kworkCategory
 * @property-read User user
 * @property-read Collection|Order[] orders Заказы
 * @property-read Collection|Rating[] comments Комментарии к заказам
 *
 * Локальные скоупы
 * @method static Builder|static listEnable() Получения видимых в каталоге кворков
 * @method static Builder|static notDraft() Получить созданные кворки, не черновики
 * @method static Builder|static notCustom() Получить кворки, исключив созданные для индивидуальных заказов
 * @method static Builder|static byLang($lang) Получить кворки по заданному языку
 */
class Kwork extends Model {
	/**
	 * Название таблицы
	 */
	const TABLE_NAME = "posts";

	/**
	 * Идентификатор кворка
	 */
	const FIELD_PID = "PID";

	/**
	 * Статус кворка
	 */
	const FIELD_ACTIVE = "active";

	/**
	 * Дата последнего изменения статуса
	 */
	const FIELD_DATE_ACTIVE = "date_active";

	/**
	 * Активность кворка выставляемая продавцом
	 */
	const FIELD_FEAT = "feat";

	/**
	 * Название кворка
	 */
	const FIELD_GTITLE = "gtitle";

	/**
	 * Описание кворка
	 */
	const FIELD_GDESC = "gdesc";

	/**
	 * ID категории
	 */
	const FIELD_CATEGORY = "category";

	/**
	 * Путь к первому изображению кворка
	 */
	const FIELD_PHOTO = "photo";

	/**
	 * Комиссия системы с заказа кворка
	 */
	const FIELD_CTP = "ctp";

	const FIELD_TIME_ADDED = "time_added";
	const FIELD_PRICE = "price";
	const FIELD_VIEW_COUNT = "viewcount";
	const FIELD_URL = "url";
	const FIELD_LANG = "lang";

	/**
	 * Статус активности кворка выставляемая продавцом
	 */
	const FEAT_ACTIVE = 1; // активен
	const FEAT_INACTIVE = 0; // не активен

	/**
	 * id пользователя-продавца
	 */
	const FIELD_USERID = "USERID";

	/**
	 * Рейтинг кворка
	 */
	const FIELD_RATING = "rating";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_PID;
	public $timestamps = false;

	/**
	 * Тип портфолио кворка (из категории и атрибутов)
	 *
	 * @var string|null
	 */
	private $portfolioType;

	/**
	 * Атриубуты и классификации кворка
	 *
	 * @var \Model\Attribute[]|null
	 */
	private $classifications;

	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active == \KworkManager::STATUS_ACTIVE;
	}

	/**
	 * @return bool
	 */
	public function isNotActive(): bool {
		return !$this->isActive();
	}

	/**
	 * @return bool
	 */
	public function isFeat(): bool {
		return $this->feat == \KworkManager::FEAT_ACTIVE;
	}

	/**
	 * @return bool
	 */
	public function isNotFeat(): bool {
		return !$this->isFeat();
	}

	/**
	 * Является ли индивидаульным предложением
	 *
	 * @return bool
	 */
	public function isCustom(): bool {
		return $this->active == \KworkManager::STATUS_CUSTOM;
	}

	/**
	 * @return HasOne
	 */
	public function kworkCategory(): HasOne {
		return $this->hasOne(
			Category::class,
			\CategoryManager::F_CATEGORY_ID,
			self::FIELD_CATEGORY)
			->withoutGlobalScope(WithoutCustomOffer::class);
	}

	/**
	 * @return string
	 */
	public function getKworkUrl(): string {
		$kworkUrl = "/not_found";
		if ($this->lang == \Translations::getLang()) {
			$kworkUrl = $this->url;
		} elseif ($this->twin_id) {
			$kworkUrl = "";
		}
		return getAbsoluteURL($kworkUrl);
	}

	/**
	 * Локальный скоуп для получения видимых в каталоге кворков
	 * Реализует аналог StatusManager::kworkListEnable
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeListEnable($query) {
		return $query->where(\KworkManager::FIELD_ACTIVE, \KworkManager::STATUS_ACTIVE)
			->where(\KworkManager::FIELD_FEAT, \KworkManager::FEAT_ACTIVE);
	}

	/**
	 * Получить созданные кворки, не черновики
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeNotDraft(Builder $query) {
		return $query->where(\KworkManager::FIELD_ACTIVE, "!=", \KworkManager::STATUS_DRAFT);
	}

	/**
	 * Получить кворки, исключив созданные для индивидуальных заказов
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeNotCustom(Builder $query) {
		return $query->where(\KworkManager::FIELD_ACTIVE, '<>', \KworkManager::STATUS_CUSTOM);
	}

	/**
	 * Получить кворки по заданному языку
	 *
	 * @param Builder $query
	 * @param string $lang
	 * @return Builder
	 */
	public function scopeByLang(Builder $query, $lang) {
		return $query->where(\KworkManager::FIELD_LANG, $lang);
	}

	/**
	 * @return HasOne
	 */
	public function user(): HasOne {
		return $this->hasOne(User::class, self::FIELD_USERID, User::FIELD_USERID);
	}

	/**
	 * Заказы
	 *
	 * @return HasMany
	 */
	public function orders(): HasMany {
		return $this->hasMany(Order::class, Order::FIELD_PID, self::FIELD_PID);
	}

	/**
	 * Получение активных кворков пользователя для указанного языка
	 *
	 * @param string $lang
	 * @param int $userId
	 * @return array
	 */
	public static function getActiveKworksIds(string $lang, int $userId): array {
		return Kwork::where(Kwork::FIELD_USERID, $userId)
			->where(Kwork::FIELD_ACTIVE, \KworkManager::STATUS_ACTIVE)
			->where(Kwork::FIELD_FEAT, \KworkManager::FEAT_ACTIVE)
			->where(Kwork::FIELD_LANG, $lang)
			->pluck(Kwork::FIELD_PID)
			->all();
	}

	/**
	 * Получить объем кворка в выбранных продацом единицах
	 *
	 * @return int
	 */
	public function getVolumeInSelectedType() {
		return $this->convertVolumeToSelectedType($this->volume);
	}

	/**
	 * Конвертация объема в единицах храния (секундах) в выбранный в кворке тип объема (минуты, часы)
	 *
	 * @param int $volume
	 *
	 * @param \Model\VolumeType|null $volumeType Тип, в который нужно сконвертировать
	 * @return float|int
	 */
	public function convertVolumeToSelectedType($volume, VolumeType $volumeType = null) {
		if ($volume > 0) {
			$volumeType = $volumeType ?? $this->volumeType;
			if (!is_null($volumeType) && $volumeType->contains_value) {
				return $volume / $volumeType->contains_value;
			}
		}
		return $volume;
	}

	public function getPortfolioType() {
		return "none";
	}

}