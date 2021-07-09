<?php


namespace Controllers\Order\Traits;


use Symfony\Component\HttpFoundation\Request;

/**
 * Трейт для получения лимита страницы
 * уменьшаем дублирование кода
 */
trait PageLimitTrait {

	/**
	 * Получение лимита из запроса или сохранненного в настройках и сохранение текущего
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request Запрос
	 * @param string $saveName Название найстройки для сохранения
	 *
	 * @return int
	 */
	public function getPageLimitAndSave(Request $request, string $saveName) {
		$limit = $request->query->get("limit");
		if ($limit == "") {
			$limit = \UserManager::getCustomParam($saveName);
		}
		if (!in_array($limit, ['10', '20', '50'])) {
			$limit = 10;
		}
		\UserManager::setCustomParam($saveName, $limit);
		return $limit;
	}
}