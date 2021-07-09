<?php

/**
 * Класс хранения методов получения списка языковых версий страниц
 */
class AlternateUrlManager {
	/**
	 * Получить языковые версии корневой страницы
	 * @return array [lang => href]
	 */
	public static function getIndex() : array {
		return Translations::getDomains();
	}

	/**
	 * Получить языковые версии страницы по запрошенному uri
	 * @return array [lang => href]
	 */
	public static function getByRequestUri() : array {
		return self::getByPath(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));
	}

	/**
	 * Получить языковые версии страницы по шаблону
	 * @param string $path Шаблон url
	 * @return array [lang => href]
	 */
	public static function getByPath(string $path = "") : array {
		$urls = self::getIndex();

		if ($path) {
			foreach ($urls as $lang => &$url) {
				$url .= $path;
			};
		}

		return $urls;
	}

	/**
	 * Получить языковые версии страницы по идентификатору категории
	 * @param int $categoryId Ид категории
	 * @param string $path Шаблон url страницы категории
	 * @return array [lang => href]
	 */
	public static function getByCategoryId(int $categoryId, string $path = "") : array {
		$categoryMap = self::getCategoryMap();

		$originalCategory = $categoryMap[$categoryId];
		$mappedCategory = $categoryMap[$originalCategory[CategoryManager::FIELD_MAPPED_CATEGORY_ID]];

		// если для категории есть языковая версия, и они привязаны крест-накрест (важно для гугла с яндексом)
		if (
			$originalCategory &&
			$mappedCategory &&
			$mappedCategory[CategoryManager::FIELD_MAPPED_CATEGORY_ID] == $originalCategory[CategoryManager::F_CATEGORY_ID]
		) {
			$urls = self::getByPath($path);

			foreach ([$originalCategory, $mappedCategory] as $category) {
				$urls[$category[CategoryManager::FIELD_LANG]] .= $category[CategoryManager::FIELD_SEO];
			}

			return $urls;
		}

		return [];
	}

	/**
	 * Получить карту соответствия категорий
	 * @return array Массив категорий
	 */
	public static function getCategoryMap() : array {
		$categoryMap = Model\Category::get([
			CategoryManager::F_CATEGORY_ID,
			CategoryManager::FIELD_LANG,
			CategoryManager::FIELD_SEO,
		])
		->keyBy(CategoryManager::F_CATEGORY_ID)
		->toArray();

		return $categoryMap;
	}
}
