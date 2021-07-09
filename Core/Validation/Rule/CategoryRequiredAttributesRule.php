<?php


namespace Core\Validation\Rule;


use Attribute\AttributeManager;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Model\Attribute;

/**
 * Правило валидации для обязательного заполнения атрибутов
 */
class CategoryRequiredAttributesRule implements Rule, ImplicitRule {

	/**
	 * @var int Идентификатор категории
	 */
	private $categoryId;

	/**
	 * @var string Минимальный уровень видимости, см константы AttributeManager::VISIBILITY_LEVEL
	 */
	private $visibilityLevel;

	/**
	 * CategoryRequiredAttributesRule constructor.
	 *
	 * @param int $categoryId Идентификатор категории
	 * @param string $visibilityLevel Минимальный уровень видимости, см константы AttributeManager::VISIBILITY_LEVEL
	 */
	public function __construct($categoryId, string $visibilityLevel = AttributeManager::VISIBILITY_LEVEL_ALL) {
		$this->categoryId = $categoryId;
		if (!in_array($visibilityLevel, [AttributeManager::VISIBILITY_LEVEL_ALL, AttributeManager::VISIBILITY_LEVEL_SELLERS])) {
			throw new \RuntimeException("Некорректный уровень видимости атрибутов");
		}
		$this->visibilityLevel = $visibilityLevel;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param array $value
	 * @return bool
	 */
	public function passes($attribute, $value) {
		$attributeIds = is_array($value) ? \Helper::intArrayNoEmpty($value) : [];
		$attributes = AttributeManager::getByIds($attributeIds);
		$validationClassificatons = $this->getValidationsClassifications($attributes);

		foreach ($validationClassificatons as $classification) {
			$classificationAttributes = array_filter($attributes, function (Attribute $attribute) use ($classification) {
				return $attribute->getParentId() == $classification->getId();
			});

			if ($classification->isRequired() && empty($classificationAttributes)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return \Translations::t("Выберите обязательные атрибуты");
	}

	/**
	 * Получение классификаций категори
	 *
	 * @return \Model\Attribute[]
	 */
	private function getCategoryClassifications() {
		if ($this->categoryId) {
			return AttributeManager::getTopByCategoryId($this->categoryId, $this->visibilityLevel);
		}
		return [];
	}

	/**
	 * Получить классификации для валидации
	 *
	 * @param \Model\Attribute[] $attributes Выбранные пользователем атрибуты
	 *
	 * @return \Model\Attribute[]
	 */
	private function getValidationsClassifications(array $attributes) {
		$categoryClassificatons = $this->getCategoryClassifications();
		$parentsIds = AttributeManager::getParentIdsFromModels($attributes);
		// Загружаем только тех родителей которые не являются уже загруженными в $categoryClassificatons
		$parentsIds = array_diff($parentsIds, AttributeManager::getIdsFromModels($categoryClassificatons));
		if ($parentsIds) {
			$parentClassifications = AttributeManager::getByIds($parentsIds, $this->visibilityLevel);
			$vaidationClassifications = array_merge($categoryClassificatons, $parentClassifications);
		} else {
			$vaidationClassifications = $categoryClassificatons;
		}

		// Загружаем потомков, чтобы отфильтровать родительские классификации не имеющией видимых потомков
		$children = AttributeManager::getByParentIds(AttributeManager::getIdsFromModels($vaidationClassifications), $this->visibilityLevel);
		$validationClassificationWithChildren = [];
		foreach ($vaidationClassifications as $vaidationClassification) {
			foreach ($children as $child) {
				if ($vaidationClassification->getId() == $child->getParentId()) {
					$validationClassificationWithChildren[] = $vaidationClassification;
				}
			}
		}

		return $validationClassificationWithChildren;
	}

}