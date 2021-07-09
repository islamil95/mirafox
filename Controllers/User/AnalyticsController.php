<?php


namespace Controllers\User;

use Controllers\BaseController;
use Model\Category;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Страница аналитики продаж пользователя
 *
 * Class AnalyticsController
 * @package Controllers\User
 */
class AnalyticsController extends BaseController {

	/**
	 * Доступна ли страница для просмотра
	 *
	 * @return bool
	 */
	private function isPageDisabled():bool {
		return $this->isUserNotAuthenticated() ||
			$this->getUserModel()->isAnalyticsDisabled();
	}

	/**
	 * Настройка того, что показывать в аналитике
	 *
	 * @return array настройка
	 */
	private function getAnalyticConfiguration():array {
		return [
			"default" => $this->getUserModel()->order_done_count == 0,
			"reviews" => $this->getUserModel()->order_done_count <= 8,
			"responsibility" => $this->getUserModel()->order_done_count <= 5,
		];
	}

	/**
	 * Получить JSON категории
	 *
	 * @return string JSON категории
	 */
	private function getCategoriesAsJson():string {
		return Category::select([\CategoryManager::F_CATEGORY_ID, \CategoryManager::F_NAME])
			->get()->toJson();
	}

	/**
	 * Получить месяц по-умолчанию
	 *
	 * @param array $periods периоды
	 * @return array
	 */
	private function getDefaultMonth($periods) {
		$firstPeriod = current($periods);
		$defaultMonth = $firstPeriod["month_count"];
		foreach ($periods as $period) {
			if ($period["default"]) {
				$defaultMonth = $period["month_count"];
			}
		}
		return $defaultMonth;
	}

	public function __invoke() {
		if ($this->isPageDisabled()) {
			return new RedirectResponse("/");
		}

		$periods = \UserStatisticManager::getAvailPeriods($this->getUserId());
		$defaultMonth = $this->getDefaultMonth($periods);
		$userStatistic = \UserStatisticManager::getStatistic($defaultMonth);

		$parameters = [
			"pageModuleName" => "analytics",
			"pagetitle" => \Translations::t("Аналитика продаж"),
			"userTopClass" => " ", // Для того, чтобы на мобильных устройствах показывалась шапка
			"userDescriptionType" => "level", // Настрока описания в шапке
			"isNewJson" => json_encode($this->getAnalyticConfiguration()),
			"isNew" => $this->getAnalyticConfiguration(),
			"categoryListJson" => $this->getCategoriesAsJson(),
			"commonStatistic" => \UserStatisticManager::getCommonStatistic($this->getUserId()),
			"userAnalytic" => $userStatistic,
			"userAnalyticJson" => json_encode($userStatistic),
			"periods" => $periods,
			"defaultMonth" => $this->getDefaultMonth($periods),
		];

		$lastUpdateDateTime = \UserStatisticManager::getLastUpdateDate($this->getUserId());
		if ($lastUpdateDateTime) {
			$lastUpdateDateTime = \Timezone::setTimezone($lastUpdateDateTime, $this->getUser()->timezone);
			$parameters["lastUpdateDate"] = \Translations::isDefaultLang() ?
				\Helper::dateByLang($lastUpdateDateTime->getTimestamp(), "j F", \Translations::getLang(), true, true) :
				\Helper::dateByLang($lastUpdateDateTime->getTimestamp(), "F j", \Translations::getLang(), true, true);
			$parameters["lastUpdateTime"] = $lastUpdateDateTime->format("H:i");
		}

		return $this->render("user/analytics", $parameters);
	}
}