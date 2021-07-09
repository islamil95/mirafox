<?php

namespace Controllers\Kwork;

use Controllers\BaseController;
use Controllers\Catalog\AbstractViewController;
use Core\Exception\PageNotFoundException;
use Core\Traits\Kwork\KworkStatusTrait;
use Model\CurrencyModel;
use Model\Kwork;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
/**
 * Страница просмотра кворка
 *
 * Class ViewKworkController
 * @package Controllers\Kwork
 */
class ViewKworkController extends BaseController {

	use KworkStatusTrait;

	/**
	 * Включена ли страница на Topfreelancer (Kwork.Connect)
	 *
	 * @var bool
	 */
	protected $projectTopfreelancer = false;

	/**
	 * Лимит количества элементов портфолио загруженных в заказах
	 */
	const PORTFOLIO_ITEMS_LIMIT = 100;

	/**
	 * Общее кол-во рекомендованных кворков для вывода
	 */
	const RECOMMENDED_KWORK_ALL_COUNT = 21;
	/**
	 * Кол-во рекомендованных кворков в одном слайде
	 */
	const RECOMMENDED_KWORK_BLOCK_COUNT = 3;

	/** @var int Кол-во отзывов на страницу */
	const REVIEW_COUNT_ON_PAGE = 5;

	private $isPostModeration = null;
	private $isCanModer = null;

	/**
	 * Является ли кворк индивидуальным предложением
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isKworkCustom($kwork):bool {
		return $kwork["active"] == \KworkManager::STATUS_CUSTOM;
	}

	/**
	 * Нужно ли скрыть категории связанные с SMM
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isHideSMMCategory($kwork):bool {
		return $kwork["category"] == \UserManager::CATEGORY_SMM_ID && \UserManager::isHideSocialSeo();
	}

	/**
	 * Находится ли кворк на пост модерации
	 *
	 * @param int $kworkId идентфикатор кворка
	 * @return bool результат
	 */
	protected function isPostModeration($kworkId):bool {
		if ($this->isPostModeration == null) {
			$this->isPostModeration = false;
		}
		return false;
	}

	/**
	 * Можно ли модерировать
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function canModer($kwork):bool {
		if($this->isCanModer == null) {
			$this->isCanModer = false;
		}

		return $this->isCanModer;
	}

	/**
	 * Невозможно модерировать кворк
	 *
	 * @param array $kwork кворк
	 * @return bool
	 */
	protected function canNotModer($kwork):bool {
		return !$this->canModer($kwork);
	}

	/**
	 * Получить урл страницы кворка
	 *
	 * @param array $kwork кворк
	 * @return string урл
	 */
	protected function getKworkUrl($kwork):string {
		return \App::config("baseurl") . $kwork["url"];
	}

	/**
	 * Нужно ли показывать причину приостановки кворка
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isCouldViewPauseReason($kwork):bool {
		return $kwork["USERID"] == $this->getUserId() || \UserManager::isAdmin();
	}

	/**
	 * Получить заголовок страницы
	 *
	 * @param array $kwork кворк
	 * @return string заголовок страницы
	 */
	protected function getPageTitle($kwork) {
		$title = stripslashes($kwork["gtitle"]);
		$title = str_replace("%", "%%", $title);
		$pageTitle = mb_ucfirst($title) . " " . \KworkManager::getKworkTextPrice($kwork);

		return $pageTitle;
	}

	/**
	 * Отмодерирован ли кворк
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isModeratedKwork($kwork):bool {
		return ($kwork["active"] == \KworkManager::STATUS_MODERATION
			|| $this->isPostModeration($kwork["PID"]))
		&& $kwork["feat"] == 1;
	}

	/**
	 * Остановлен ли кворк
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isStoppedKwork($kwork):bool {
		return $kwork["feat"] == 0
		&& $kwork["active"] != \KworkManager::STATUS_DELETED;
	}

	/**
	 * Создан ли кворк текущим пользователем
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isKworkUser($kwork):bool {
		return $this->getUserId() == $kwork["USERID"];
	}

	/**
	 * Кворк не создан текущим пользователем
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isNotKworkUser($kwork):bool {
		return ! $this->isKworkUser($kwork);
	}

	/**
	 * Приостановлен ли кворк
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isKworkSuspended($kwork):bool {
		return $kwork["kwork_allow_status"] == "deny";
	}

	/**
	 * Нужно ли обновить
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isNeedUpdate($kwork):bool {
		return $this->isKworkUser($kwork)
		&& $this->isActiveAndFeatActive($kwork)
		&& \KworkManager::isNeedUpdate($kwork["PID"]);
	}

	/**
	 * Разрешен только метод GET, но и тонны ошибок в логах нам не нужны
	 *
	 * @param Request $request запрос
	 * @return bool|RedirectResponse
	 */
	private function allowOnlyGet(Request $request) {
		if ($request->getMethod() != Request::METHOD_GET) {
			return new RedirectResponse($request->getRequestUri());
		}
		return false;
	}

	public function __invoke(Request $request, $seo, $kworkId, $kworkSeoTitle) {
		if ($response = $this->allowOnlyGet($request)) {
			return $response;
		}
		$actor = \UserManager::getCurrentUser();
		$parameters = [];

		if (!$kworkId) {
			throw new PageNotFoundException();
		}

		$kworkData = \KworkManager::getViewData($kworkId);
		if (!$kworkData) {
			throw new PageNotFoundException();
		}

		if ($this->isKworkCustom($kworkData)){
			throw new PageNotFoundException();
		}

		if ($this->isHideSMMCategory($kworkData)) {
			$catalog = AbstractViewController::DEFAULT_VIEW;
			$url = "/$catalog/" . \App::config("category.smm_redirect_cat_name");
			return new RedirectResponse($url);
		}

		// если категория запрещена для зеркала, то редирект
		if (\App::isMirror() && !$kworkData["allow_mirror"]) {
			throw new PageNotFoundException();
		}

		// Запретить открытие кворков которые не соответствуют версии сайта
		// Исключение для тестеров - авторов кворка
		if (\Translations::getLang() != $kworkData["lang"] && !(\UserManager::isLanguageTester($actor->id) && $actor->id == $kworkData['USERID'])) {
			if ($kworkData["twinUrl"]) {
				return new RedirectResponse($kworkData["twinUrl"]);
			} else {
				throw new PageNotFoundException();
			}
		}

		// правильный url кворка
		$curUrlParts = explode("?", $request->getRequestUri(), 2);
		$curUrl = \App::config("baseurl") . urldecode($curUrlParts[0]);
		$correctUrl = \App::config("baseurl") . $kworkData["url"];

		if ($curUrl != $correctUrl) {
			return new RedirectResponse($correctUrl, 301);
		}

		// В категориях с возможностью создания пакетных кворков
		// непакетный кворк содержит только пакет standard
		$kwork = Kwork::find($kworkData["PID"]);

		// canonical
		$parameters["canonicalUrl"] = $this->getKworkUrl($kworkData);

		$parameters["canModer"] = $this->canModer($kworkData);
		$parameters["isPostModeration"] = $this->isPostModeration($kworkId);
		$parameters["showAllData"] = !$kworkData['was_moder_confirmed'];

		//откуда пришли
		$referer = $_SERVER['HTTP_REFERER'];
		if (strstr($referer, "/search?query") || strstr($referer, "/categories/")) {
			$parameters["mobileReferer"] = $referer;
		}

		$kworkData["gdesc"] = \KworkManager::getViewHtmlText($kworkData["gdesc"]);
		$kworkData["ginst"] = \KworkManager::getViewHtmlText($kworkData["ginst"]);

		// title и dscription страницы
		$parameters["mtitle"] = "Фриланс: " . $this->getPageTitle($kworkData);
		$mdesc = $kworkData["name"] . " в магазине фриланс-услуг Кворк. Все услуги от 500 руб. Более 200 000";
		$mdesc .= " исполнителей. Высокая скорость и четкое соблюдение сроков. Контроль качества, арбитраж в";
		$mdesc .= " случае споров. 100% гарантия возврата средств в случае срыва заказа. Строгая система рейтингов";
		$mdesc .= " фрилансеров. В результате - вы видите предложения только лучших исполнителей. Услуги худших";
		$mdesc .= " скрываются из каталога.";
		$parameters["mdesc"] = trim(htmlspecialchars(cut_desc_for_meta(strip_tags(htmlspecialchars_decode($mdesc)))));

		$parameters["pageName"] = "view";

		/*
		 * Проверка необходимости перехода в режим ускорения загрузки страницы:
		 * Подробнее: http://wikicode.kwork.ru/optimizaciya-skorosti-zagruzki-klyuchevyx-stranic-po-google-pagespeed/
		 *
		 * Также получаем критические стили
		 */
		global $isNeedPageSpeed;
		if ($isNeedPageSpeed) {
			$parameters["pageSpeedMobile"] = true;
			$parameters["criticalStyles"] = getCriticalStyles("view");
		}
		if (!$actor) {
			$parameters["pageSpeedDesktop"] = true;
		}

		$parentCategory = $kworkData["parent_cat"];
		$parameters["parent_cat"] = $parentCategory;

		$parameters["order_done_count"] = $kworkData["order_done_count"];

		$parentCategoryName = (isset($parentCategory["name"]) ? ", " . $parentCategory["name"] : "");
		$parameters["metakeywords"] = $kworkData["name"] . $parentCategoryName;

		// Другие кворки продавца
		$moreUserPosts = [];
		if(!$this->canModer($kworkData)) {
			$moreUserPosts = \KworkManager::getViewDataMoreUserKworks($kworkId, $kworkData["USERID"], 20, $this->canModer($kworkData));
		}
		$parameters["otherUserKworks"] = $moreUserPosts;

		// Похожие кворки
		$user_kworks_ids = [];
		foreach ($moreUserPosts as $item) {
			$user_kworks_ids[] = $item["PID"];
		}
		$sameCategoryPosts = \KworkManager::getSameCategoryKworks($kworkId, $user_kworks_ids, 20);
		$parameters["sameKworks"] = $sameCategoryPosts;

		$allGoodReviews = $this->getAllGoodReviews($kworkData["USERID"]);
		$allBadReviews = $this->getAllBadReviews($kworkData["USERID"]);

		$goodReviews = \RatingManager::getByKworkId($kworkId, ["type" => "positive", "offset" => 0, "limit" => self::REVIEW_COUNT_ON_PAGE]);
		$badReviews = \RatingManager::getByKworkId($kworkId, ["type" => "negative", "offset" => 0, "limit" => self::REVIEW_COUNT_ON_PAGE]);
		$tab = count($goodReviews["reviews"]) ? "positive" : "negative";

		$parameters["reviews"] = $goodReviews["reviews"]? : $badReviews["reviews"];
		$parameters["count_reviews"] = self::REVIEW_COUNT_ON_PAGE;
		$parameters["reviewsType"] = $tab;
		$parameters["goodReviews"] = $goodReviews["total"];//rgrat
		$parameters["badReviews"] = $badReviews["total"];//rbrat
		$parameters["allGoodReviews"] = $allGoodReviews["total"];//rgrat
		$parameters["allBadReviews"] = $allBadReviews["total"];//rbrat

		if (\Translations::isDefaultLang()) {
			$langs = [\Translations::DEFAULT_LANG];
		} else {
			$langs = [\Translations::DEFAULT_LANG, \Translations::EN_LANG];
		}
		$parameters["foxtotalvotes"] = \RatingManager::getRatingCount($kworkData["USERID"], $langs);

		if ($this->isCouldViewPauseReason($kworkData)) {
			if ($kworkData["pause_reason"] == \KworkManager::PAUSE_REASON_DEFAULT) {
				$parameters["kwork_pause_on"] = \App::config("kwork.pause.on");
				$parameters["kwork_pause_off"] = \App::config("kwork.pause.off");
			}
		}

		//передаем в шаблон данные о сделанном заказе, чтобы воспроизвести выбранные каунтеры

		$orderData = [];
		$parameters["balance_popup"] = $request->get("balance");
		$parameters["authOrder"] = $orderData;
		$parameters["convertedOrderPrice"] = $actor ? \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
			$orderData["price"],
			\Translations::getCurrencyIdByLang($kworkData["lang"]),
			\Translations::getCurrencyIdByLang($actor->lang)
		) : $orderData["price"];

		$parameters["isKworkPage"] = true;
		$kworkData["meta_gdesc"] = \Helper::getMetaDescription($kworkData["gdesc"]);

		if ($this->canModer($kworkData)) {
			if(isset($patch["descriptionFiles"]) && $parameters["descriptionFiles"] != $patch["descriptionFiles"])
				$parameters["patchDescFiles"] = true;
			if(isset($patch["instructionFiles"]) && $parameters["instructionFiles"] != $patch["instructionFiles"])
				$parameters["patchInstructionFiles"] = true;
		}

		// есть ли предложения, чобы при редактировании предупредить продавца что предложения удалятся
		if ($this->isUserAuthenticated() && $this->isKworkUser($kworkData) || $this->canModer($kworkData)) {
			$offerData = \KworkManager::getWithOffers(array($kworkData["PID"]));
			$kworkData["has_offer"] = empty($offerData[$kworkData["PID"]]) ? false : true;
		}

		$parameters["imgAltTitle"] = $kworkData["gtitle"];

		// рейтинг пользователя
		$parameters["userRating"] = $kworkData["cache_rating"];

		$parameters["rate"] = \OrderManager::getMultiKworkRate($kwork->kworkCategory);

		$spellCheck = $this->canModer($kworkData) && \App::config("speller.enable");
		$parameters["spellCheck"] = (int)$spellCheck;

		$maxKworkCount = \App::config("kwork.max_count");

		$category = \CategoryManager::getData($kworkData["category"]);

		$parameters["category"] = $category;
		$parameters["disallow_mirror"] = !$kworkData["allow_mirror"];
		$parameters["viewpage"] = 1;
		$parameters["kwork"] = $kworkData;
		$parameters["maxKworkCount"] = $maxKworkCount;
		$parameters["maxExtrasCount"] = \App::config("kwork.max_count");
		$parameters["minKworkCount"] = 1;
		$parameters["minKworkDays"] = 0;
		$parameters["isPostModerKwork"] = $this->isPostModeration($kworkId);
		$parameters["isModeratedKwork"] = $this->isModeratedKwork($kworkData);
		$parameters["isStoppedKwork"] = $this->isStoppedKwork($kworkData);
		$parameters["isAdmin"] = \UserManager::isAdmin();
		$parameters["isModer"] = \UserManager::isModer();
		$parameters["isKworkUser"] = $this->isKworkUser($kworkData);
		$parameters["isWorker"] = $parameters["isKworkUser"];
		$parameters["isSuspended"] = $this->isKworkSuspended($kworkData);
		$parameters["isActiveAndFeatActive"] = $this->isActiveAndFeatActive($kworkData);
		$parameters["isNeedUpdate"] = $this->isNeedUpdate($kworkData);
		$parameters["recommendedKworks"] = $this->getRecommendedKworks($kworkData["PID"]);

		$parameters["p"] = [
			"PID" => $kworkData["PID"],
			"lang" => $kworkData["lang"],
			"twin_id" => $kworkData["twin_id"],
			"twinUrl" => $kworkData["twinUrl"],
		];

		$categories = [];
		if (!empty($kworkData["parent_cat"]["name"])) {
			$categories[] = $kworkData["parent_cat"]["name"];
		}
		if (!empty($kworkData["name"])) {
			$categories[] = $kworkData["name"];
		}
		if (!empty($parameters["classifications"][0]) && !empty($parameters["classifications"][0]->getChildren()[0])) {
			$categories[] = $parameters["classifications"][0]->getChildren()[0]->getTitle();
		}
		$parameters["categories"] = json_encode($categories);

		$parameters["isQuick"] = $kwork->is_quick;

		return $this->render("kwork/view", $parameters);
	}

	/**
	 * Получить положительные отзывы
	 *
	 * @param int $userId идентификатор пользователя
	 * @return array|false отзывы
	 */
	private function getAllGoodReviews($userId) {
		$options = [
			"type" => "positive",
			"offset" => 0,
			"limit" => self::REVIEW_COUNT_ON_PAGE,
		];
		return \RatingManager::getByUserId($userId, $options, \Translations::isDefaultLang() ? null : [CurrencyModel::RUB, CurrencyModel::USD]);
	}

	/**
	 * Получить негативные отзывы
	 *
	 * @param int $userId идентификатор пользователя
	 * @return array|false отзывы
	 */
	private function getAllBadReviews($userId) {
		$options = [
			"type" => "negative",
			"offset" => 0,
			"limit" => self::REVIEW_COUNT_ON_PAGE,
		];
		return \RatingManager::getByUserId($userId, $options, \Translations::isDefaultLang() ? null : [CurrencyModel::RUB, CurrencyModel::USD]);
	}

	/**
	 * Получить список рекомендованных кворков
	 *
	 * @param int $kworkId ID текущего кворка
	 * @return mixed
	 */
	private function getRecommendedKWorks($kworkId) {
		return Kwork::whereKeyNot($kworkId)
			->orderByDesc(Kwork::FIELD_RATING)
			->take(self::RECOMMENDED_KWORK_BLOCK_COUNT)
			->get();
	}
}