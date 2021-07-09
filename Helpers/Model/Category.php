<?php

namespace Model;

use Attribute\AttributeManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Model\Scopes\Category\WithoutCustomOffer;
use VolumeType\CategoryAdditionalVolumeType;

/**
 * Категория кворков
 *
 * @mixin \EloquentTypeHinting Добавляем подсказки к магическим статическим методам
 *
 * @property int $CATID Идентификатор
 * @property string $name Название
 * @property string $seo Алиас
 * @property string $details Описание
 * @property string $mobile_description Короткое описание категории, для мобильного приложения
 * @property string $seo_i Название в именительном падеже, для лендинга
 * @property string $seo_v Название в винительном падеже, для лендинга
 * @property int $parent Идентификатор родительской категории
 * @property string $mtitle Описание для тега title
 * @property string $mdesc описание для тега meta description
 * @property string $mtags описание для тега meta keywords
 * @property string $minfo Описание внизу страницы depricated
 * @property int $use_count количество созданных кворков за месяц + количество созданных заказов за месяц
 * @property int $order_month количество созданных заказов за месяц
 * @property int $order_3_day количество созданных заказов за 3 последних дня
 * @property int $max_days максимальное количество дней на выполнение кворка в этой категории
 * @property int $max_photo_count Максимальное количество элементов портфолио в кворке этой категории
 * @property int $allow_mirror показывать ли категорию и кворки этой категории на зеркале kworks.ru
 * @property int $land_count количество лендингов относящихся к этой категории
 * @property int $kwork_count количество кворков относящихся к этой категории
 * @property int $land_view_count количество просмотров лендингов относящихся к этой категории за последние 30 дней
 * @property int $is_package разрешены ли пакеты в кворках этой категории
 * @property int $package_standard_mult множитель стоимости в пакете Эконом
 * @property int $package_medium_mult множитель стоимости в пакете Стандарт
 * @property int $package_premium_mult множитель стоимости в пакете Бизнес
 * @property string $portfolio_type разрешено ли портфолио в кворках этой категории: none — нет, photo — фото, video — видео
 * @property int $month_done_orders_count количество завершенных заказов за последний месяц
 * @property int $rotation_weight сумма весов кворков в категории
 * @property int $custom_offer Индивидуальный заказ (для скрытия в списке)
 * @property string $collage_image Изображение категории для коллажа
 * @property int $base_volume Базовый объем
 * @property int $min_volume Минимальный объем
 * @property int $max_volume Максимальный объем
 * @property int $min_volume_type_id Идентификатор типа минимального цифрового объема кворка
 * @property int $max_volume_type_id Идентификатор типа максимального цифрового объема кворка
 * @property int $volume_type_id Идентификатор единицы измерения объема услуги
 * @property float $conversion Средняя конверсия по категории
 * @property int $is_package_free_price Включены ли свободные цены в категории
 * @property string $lang Язык
 * @property int|null $twin_category_id Идентификатор категории-близнеца в другом языке
 * @property int|null $mapped_category_id Идентификатор категории-соответствия в другом языке
 * @property float $quality_rating_relative Средний рейтинг качества на заказ по категории
 * @property float $month_revenue Месячный оборот категории
 * @property string $short_name Короткое название категории - для отображения на главной странице в коллажах
 * @property int $portfolio_avaliable портфолио доступно
 * @property float $avg_offers_count Среднее количество предложений по категории
 * @property int|null response_time медианное время ответа на сообщения, требующие ответа в течении 24 часов, в секундах
 * @property int $is_kwork_full_rate Включен ли расчет полного времени на работы по кворку
 *
 * Связанные модели
 * @property-read \Illuminate\Database\Eloquent\Collection|\Model\VolumeType[] $additionalVolumeTypes Связанные модели дополнительных доспустимых типов цифрового объема
 * @property-read \Model\VolumeType|null $volumeType Связанная модель основного типа числового объема
 * @property-read \Model\Category $parentCategory Родительская категория
 * @property-read \Model\Category $children Дочерние категории
 *
 * Локальные скоупы
 * @method static Builder|static byLang($lang) Получение категорий по указанному языку
 */
class Category extends Model {
	const TABLE_NAME = "categories";

	const FIELD_ID = "CATID";
	const FIELD_NAME = "name";
	const FIELD_CUSTOM_OFFER = "custom_offer";
	const FIELD_COLLAGE_IMAGE = "collage_image";
	const FIELD_PARENT = "parent";
	const FIELD_META_TITLE = "mtitle";
	const FIELD_META_DESCRIPTION = "mdesc";
	const FIELD_META_KEYWORDS = "mtags";
	const FIELD_SEO = "seo";
	const FIELD_LANG = "lang";
	const FIELD_VOLUME_TYPE_ID = "volume_type_id";
	const FIELD_BASE_VOLUME = "base_volume";
	const FIELD_ALLOW_MIRROR = "allow_mirror";
	const FIELD_TWIN_CATEGORY_ID = "twin_category_id";
	const FIELD_PORTFOLIO_AVAILABLE = "portfolio_avaliable";

	/**
	 * Имя таблицы
	 * @var string
	 */
	protected $table = \CategoryManager::TABLE_NAME;

	/**
	 * Первичный ключ
	 * @var string
	 */
	protected $primaryKey = \CategoryManager::F_CATEGORY_ID;

	/**
	 * @var bool Отключаем встроенную обработку created_at, updated_at
	 */
	public $timestamps = false;

	protected $guarded = [];

	/**
	 * Связанные модели дополнительных допустимых типов числового объема
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function additionalVolumeTypes() {
		return $this->belongsToMany(VolumeType::class,
			CategoryAdditionalVolumeType::TABLE_NAME,
			CategoryAdditionalVolumeType::FIELD_CATEGORY_ID,
			CategoryAdditionalVolumeType::FIELD_VOLUME_TYPE_ID);
	}

	/**
	 * Связанная модель основного типа числового объема
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function volumeType() {
		return $this->belongsTo(VolumeType::class, \CategoryManager::F_VOLUME_TYPE_ID);
	}

	/**
	 * Родительская категория
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function parentCategory() {
		return $this->hasOne(Category::class, \CategoryManager::F_CATEGORY_ID, \CategoryManager::F_PARENT)
			->withoutGlobalScopes();
	}

	/**
	 * Дочерние категории
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function children() {
		return $this->hasMany(Category::class, \CategoryManager::F_PARENT, \CategoryManager::F_CATEGORY_ID)
			->withoutGlobalScopes();
	}

	/**
	 * Добавить в коллекцию категорий идентификаторы доступных дополнительных типов числового объема
	 *
	 * @param \Illuminate\Database\Eloquent\Collection|\Model\Category[] $collection
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function addAdditionalTypesIdsToCollection(\Illuminate\Database\Eloquent\Collection $collection) {
		$ids = $collection->map(function (Category $category) {
			return $category->CATID;
		});
		if (count($ids)) {
			$volumeTypesByCategory = CategoryAdditionalVolumeType::getAdditionalVolumeTypesForMany($ids->toArray());
			$collection->each(function (Category $category) use ($volumeTypesByCategory) {
				if (array_key_exists($category->CATID, $volumeTypesByCategory)) {
					$category->additionalVolumeTypesIds = $volumeTypesByCategory[$category->CATID];
				}
			});
		}

		return $collection;
	}

	/**
	 * Все категории по языку
	 * @param Builder $query
	 * @param string $lang
	 * @return Builder
	 */
	public function scopeByLang(Builder $query, $lang) {
		return $query->where(\CategoryManager::FIELD_LANG, $lang);
	}

	/**
	 * Проверить разрешена ли загрузка портфолио для категории с учетом "глубины"
	 * То есть разрешена ли загрузка непосредственно для категории 2 уровня или
	 * хотя бы одного ее атрибута из дерева
	 *
	 * @param bool $filterByLang учитывать ли языковую принадлежность категории
	 * @return bool
	 */
	public function isCategoryHasPortfolioAvailable(bool $filterByLang = false) {
		 //Считаем, что для категорий 1 уровня загрузка портфолио невозможна
		if (0 === $this->parent) {
			return false;
		}
		if ($this->portfolio_avaliable) {
			return true;
		}
		return AttributeManager::isCategoryHasPortfolioAvailableAttributes($this->CATID, $this->lang, $filterByLang);
	}

	/**
	 * @return bool
	 */
	public function isRootCategory(): bool {
		return !$this->isSubcategory();
	}

	/**
	 * @return bool
	 */
	public function isSubcategory(): bool {
		return (bool)$this->parent;
	}

	/**
	 * @return bool
	 */
	public function isKworkFullRate(): bool {
		return (bool)$this->is_kwork_full_rate;
	}
}
