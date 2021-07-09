<?php

namespace Controllers\Index;

use Controllers\BaseController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Model\Category;
use Model\Kwork;
use Model\Order;
use Symfony\Component\HttpFoundation\Request;
use \Model\User;
use \Model\UserFavouriteCategories;
use \Model\Want;

/**
 * Контроллер главной страницы основного сайта
 */
class IndexController extends BaseController {

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function __invoke(Request $request) {
		global $isNeedPageSpeed;

		$actor = \UserManager::getCurrentUser();

		$params = [
			"pageName" => "index",
			"mtitle" => stripslashes(\App::config('site_slogan')),
			"mdesc" => $this->getMetaDescription(),
		];

		/*
		 * Проверка необходимости перехода в режим ускорения загрузки страницы:
		 * Подробнее: http://wikicode.kwork.ru/optimizaciya-skorosti-zagruzki-klyuchevyx-stranic-po-google-pagespeed/
		 *
		 * Также получаем критические стили
		 */
		if ($isNeedPageSpeed) {
			$params["pageSpeedMobile"] = true;
			$params["criticalStyles"] = getCriticalStyles("index");
		}
		if (!$actor) {
			$params["pageSpeedDesktop"] = true;
		}

		$params = array_merge($params, $this->getAuthorizedUserParams($actor));

		return $this->render("index.tpl", $params);
	}

	/**
	 * Получение метатега для английской версии
	 *
	 * @return string
	 */
	private function getMetaDescription() {
		if (!\Translations::isDefaultLang()) {
			return \Translations::t('Add services to your cart and order them with just a couple of clicks.<br> Tens of thousands of services, quick delivery and guaranteed refunds: never has freelance been so enjoyable!');
		}
		$text = "Маркетплейс и биржа фриланс-услуг Kwork. Все услуги фрилансеров от 500 руб. Более 200 000";
		$text .= " исполнителей. Четкие сроки, высокая скорость выполнения. Контроль качества, арбитраж в случае";
		$text .= " споров. 100% гарантия возврата средств в случае срыва заказа. Строгая система рейтингов";
		$text .= " исполнителей. В результате — в каталоге предложения только лучших фрилансеров.";
		return $text;
	}

	/**
	 * Получение данных для общесайтовой статистики
	 *
	 * @return array
	 */
	private function getStatisticsParams() {
		$actuveKworsQuery = Kwork::where(Kwork::FIELD_ACTIVE, \KworkManager::STATUS_ACTIVE);
		$week = date_create("today - 1 week");
		return [
			"stat_act_kworks_count" => $actuveKworsQuery->count(),
			"stat_act_kworks_to_week_count" => $actuveKworsQuery->where(Kwork::FIELD_TIME_ADDED, ">=",  $week->getTimestamp())->count(),
			"stat_wants_per_week" => \WantManager::getNewWantsPerWeek(\Translations::getLang()),
		];
	}

	/**
	 * Получение параметров для авторизованного пользователя
	 *
	 * @param \stdClass $actor Текущий пользователь
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getAuthorizedUserParams($actor) {
		$wants = $this->getWants($actor->id);
		return [
			"kworks" => $this->getPopularKworks(),
			"wants" => $wants,
			"wantsCount" => count($wants),
		];
	}

	/**
	 * Получение проектов для покупателя
	 *
	 * @param int $userId Идентификатор пользователя
	 *
	 * @return \Model\Want[]
	 */
	private function getWants(int $userId = null) {
		return Want::query()
			->visible()
			->with(["orders" => function(HasMany $query) {
				// реальные заказы, а не предложения, необходимо для определения альтернативного статуса
				$query->where(Order::FIELD_STATUS, "!=", \OrderManager::STATUS_NEW);
			}])
			->take(10)
			->get()
			->all();
	}

	/**
	 * Получение популярных кворков
	 *
	 * @return array
	 */
	private function getPopularKworks() {
		$kworks = Kwork::notCustom()
			->orderByDesc(Kwork::FIELD_RATING)
			->with("user")
			->take(10)
			->get();

		$result = [];
		foreach ($kworks as $kwork) {
			$tmp = $kwork->toArray();
			$tmp["username"] = $kwork->user->username;
			$result[] = $tmp;
		}

		return $result;
	}

}