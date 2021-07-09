<?php

namespace Controllers\Want\Payer;

use Controllers\BaseController;
use Core\Constants\Want\OfferSortTypeConst;
use Core\Exception\PageNotFoundException;
use Core\Exception\RedirectException;
use Core\Traits\Routing\RoutingTrait;
use Illuminate\Database\Eloquent\Collection;
use Model\Offer;
use Model\Order;
use Model\User;
use Model\Want;
use Model\Portfolio;
use Model\Kwork;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер просмотра предложенных кворков на запрос услуги
 *
 * Class OffersController
 * @package Controllers\Want\Payer
 */
class OffersController extends BaseController {

	use RoutingTrait;

	/**
	 * @var array Веса сортировки по статусам
	 * Сначала выполненные, потом активные, следом скрытые
	 */
	protected $statusSort = [
		\OfferManager::STATUS_DONE => 1,
		\OfferManager::STATUS_ACTIVE => 2,
		\OfferManager::STATUS_CANCEL => 3,
		\OfferManager::STATUS_DELETE => 4,
		\OfferManager::STATUS_REJECT => 5,
	];

	/**
	 * Получить список предложений
	 *
	 * @param int $wantId идентификатор запроса на услугу
	 *
	 * @return Collection|\Model\Offer[]
	 */
	private function getOffers($wantId) {
		$offers = Offer::where(Offer::FIELD_WANT_ID, $wantId)
			->whereNotIn(Offer::FIELD_STATUS, [\OfferManager::STATUS_REJECT, \OfferManager::STATUS_DELETE])
			->with([
				"order",
				"kwork",
				"user",
			])
			->orderBy(Offer::FIELD_STATUS)
			->orderByDesc(Offer::FIELD_ID)
			->get();

		return $offers;
	}

	/**
	 * Получить запрос на услуги
	 *
	 * @param int $wantId идентификатор запроса на услуги
	 *
	 * @return Want запрос на услуги
	 */
	private function getWant($wantId) {
		$want = Want::with([
			"category",
			"category.parentCategory",
			"user",
		])->find($wantId);

		if (empty($want)) {
			throw (new RedirectException())
				->setRedirectUrl($this->getUrlByRoute("manage_projects"));
		}
		return $want;
	}

	/**
	 * Получение существующих заказов (прямых и косвенных) по данному запросу по продавцам
	 *
	 * @param int $wantId Идентификатор запроса
	 *
	 * @return array [workerId => orderId, ...]
	 */
	private function getWantWorkerOrders(int $wantId):array {
		return Order::where(Order::FIELD_PROJECT_ID, $wantId)
			->where(Order::FIELD_STATUS, "!=", \OrderManager::STATUS_NEW)
			->pluck(Order::FIELD_OID, Order::FIELD_WORKER_ID)
			->toArray();
	}


	/**
	 * Количество фрилансеров, у которых было заказано выполнение этого проекта
	 * @param int $wantId
	 * @return int
	 */
	private function getCountWorkers(int $wantId) : int {
		return count(Order::select(Order::FIELD_WORKER_ID)
			->distinct()
			->where(Order::FIELD_PROJECT_ID, $wantId)
			->get()
			->toArray());
	}

	/**
	 * Сортировка предложений
	 *
	 * @param \Illuminate\Database\Eloquent\Collection|\Model\Offer[] $offers Предложения
	 * @param string $sortType Тип сортировки rating|date
	 * @param array $wantWorkerOrders Идентификаторы существующих заказов по данному запросу по исполнителям
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|\Model\Offer[]
	 */
	private function offersSort(Collection $offers, $sortType = "", array $wantWorkerOrders = []) {
		return $offers->sort(function (Offer $a, Offer $b) use ($sortType, $wantWorkerOrders) {
			// Сначала сортируем по весам статусов, с учетом косвенных закзов
			if ($this->getStatusWeight($a, $wantWorkerOrders) != $this->getStatusWeight($b, $wantWorkerOrders)) {
				return $this->getStatusWeight($a, $wantWorkerOrders) <=> $this->getStatusWeight($b, $wantWorkerOrders);
			}

			// Если веса статусов одинаковые то либо по рейтингу продавца
			if ($sortType == OfferSortTypeConst::SORT_BY_RATING) {
				return -($a->user->cache_rating <=> $b->user->cache_rating);
			}

			// Либо по дате предложения
			return -($a->id <=> $b->id);
		});
	}

	/**
	 * Получение веса для сортировки
	 *
	 * @param \Model\Offer $offer Предложение
	 * @param array $wantWorkerOrders Идентификаторы существующих заказов по данному запросу по исполнителям
	 *
	 * @return int
	 */
	private function getStatusWeight(Offer $offer, array $wantWorkerOrders):int {
		// Если есть существующие заказы - приравниваем к статусу выполнено
		if (!empty($wantWorkerOrders[$offer->user_id])) {
			return $this->statusSort[\OfferManager::STATUS_DONE];
		}

		return $this->statusSort[$offer->status];
	}

	/**
	 * Получить массив категорий портфолио
	 * Используется для селекта категорий при показе портфолио на бирже
	 *
	 * @param array $userIds - одномерный массив идентификаторов
	 * @param string $lang
	 * @return array - [userId => [catId1, catId2, ...], ...]
	 */
	private static function getPortfolioCategoriesBulk($userIds, $lang)
	{
		// Перед получением категорий из posts сделаем фильтрацию по kwork_id.
		// По разным причинам в таблице portfolio могут отсутствовать записи с переданными kwork_id,
		// их нужно отбрасывать иначе может быть селект указывающий на пустое содержимое

		$kworkIds = Kwork::query()
			->select(Kwork::FIELD_PID)
			->whereIn(Kwork::FIELD_USERID, $userIds)
			->where(\KworkManager::FIELD_ACTIVE, "!=", \KworkManager::STATUS_DRAFT)
			->where(Kwork::FIELD_LANG, $lang)
			->pluck(Kwork::FIELD_PID)
			->toArray();

		$validKworkIds = Portfolio::query()
			->select(Portfolio::FIELD_KWORK_ID)
			->whereIn(Portfolio::FIELD_KWORK_ID, $kworkIds)
			->where(Portfolio::FIELD_STATUS, "<>", Portfolio::STATUS_DELETE)
			->pluck(Portfolio::FIELD_KWORK_ID)
			->toArray();

		// Получаем категории в posts

		$kworksCategories = Kwork::query()
			->select(Kwork::FIELD_USERID, Kwork::FIELD_CATEGORY)
			->whereIn(Kwork::FIELD_PID, $validKworkIds)
			->get()
			->toArray();

		$userCategory = self::filterAndMergePortfolioCats($kworksCategories);
		$langCategoryIds = \CategoryManager::getLangCategoryIds($lang);

		$unbindedPortfolioCategories = Portfolio::query()
			// переименовывание для того, чтобы использовать self::filterAndMergePortfolioCats
			->select(\Core\DB\DB::raw("user_id AS USERID"), \Core\DB\DB::raw("category_id AS category"))
			->where(Portfolio::FIELD_STATUS, "<>", Portfolio::STATUS_DELETE)
			->whereIn(Portfolio::FIELD_USER_ID, $userIds)
			->whereNull(Portfolio::FIELD_KWORK_ID)
			->whereIn(Portfolio::FIELD_CATEGORY_ID, $langCategoryIds)
			->get()
			->toArray();

		$userCategory = self::filterAndMergePortfolioCats($unbindedPortfolioCategories, $userCategory);
		return $userCategory;
	}

	/**
	 * Удалить дубли и преобразовать массив
	 *
	 * Преобразует  массив вида (результат sql запроса):
	 * [ ["USERID" => userId, "category" => cat1 ], ["USERID" => userId, "category" => cat2], ... ],
	 * к виду:
	 * [ userId => [cat1, cat2, ...], ...]
	 *
	 * Если параметр $userCategory - пустой, то выходной массив будет сформирован с нуля.
	 * Если параметр уже содержит массив, то этот массив будет использован при обработке.
	 *
	 * @param array $rows - [ ["USERID" => userId, "category" => cat1 ], ["USERID" => userId, "category" => cat2], ... ],
	 * @param array $userCategory - [ userId => [cat1, cat2, ...], ...]
	 * @return array - [ userId => [cat1, cat2, ...], ...]
	 */
	private static function filterAndMergePortfolioCats($rows, $userCategory = [])
	{
		foreach ($rows as $row) {
			$USERID = $row["USERID"];
			$category = $row["category"];

			if (!is_array($userCategory[$USERID])) {
				$userCategory[$USERID] = [];
			}

			if (in_array($category, $userCategory[$USERID])) {
				continue;
			}

			$userCategory[$USERID][] = $category;
		}
		return $userCategory;
	}

	/**
	 * Возвращает массив с записями из portfolio
	 *
	 * @param array $userKworks - [userId => [kworkId1, kworkId2, ...], ... ]
	 * @param array $portfolioSelectedCategory - [userId => categoryId, ... ]
	 * @param string $lang
	 * @return array - [ portfolioId => row ,  ... ], где row - ['id' => int, 'user_id' => int, 'kwork_id' => int, 'category_id' => int]
	 */
	private static function getPortfolioBulk(array $userKworks, array $portfolioSelectedCategory, $lang)
	{
		$catIds = implode(",", \CategoryManager::getLangCategoryIds($lang));
		$caseItems = [];
		foreach ($portfolioSelectedCategory as $userId => $catId) {

			if (isset($userKworks[$userId]) && count($userKworks[$userId])) {
				$kworkIds = implode(",", $userKworks[$userId]);
				// у юзера есть кворки в портфолио
				if (!$catId) { // категория не выбрана
					$caseItems[] = "WHEN user_id='{$userId}' THEN kwork_id IN ({$kworkIds}) OR kwork_id IS NULL AND category_id IN ($catIds)";
				} else { // категория выбрана
					$caseItems[] = "WHEN user_id='{$userId}' THEN kwork_id IN ({$kworkIds}) OR category_id = '{$catId}'";
				}
			} else { // у юзера нет кворков в портфолио
				if (!$catId) { // категория не выбрана
					$caseItems[] = "WHEN user_id='{$userId}' THEN  kwork_id IS NULL AND category_id IN ($catIds)";
				} else { // категория выбрана
					$caseItems[] = "WHEN user_id='{$userId}' THEN  category_id = '{$catId}'";
				}
			}
		}

		$case = implode("\n", $caseItems);

		$portfoliosRows = Portfolio::query()
			->select(Portfolio::FIELD_ID, Portfolio::FIELD_USER_ID, Portfolio::FIELD_KWORK_ID, Portfolio::FIELD_CATEGORY_ID)
			->where(Portfolio::FIELD_STATUS, "<>", Portfolio::STATUS_DELETE)
			->whereIn(Portfolio::FIELD_USER_ID, array_keys($portfolioSelectedCategory))
			->whereRaw("CASE \n $case END")
			->orderByDesc(Portfolio::FIELD_ID)
			->get()
			->keyBy(Portfolio::FIELD_ID)
			->toArray();
		return $portfoliosRows;
	}

	/**
	 * Сгруппировать по userId и ограничить количество.
	 * На выходе 2 массива:
	 * 1) [userId => [portfolioRow1, portfolioRow2, ...], ...]
	 * 2) [userId => 1, userId => 0, ...]
	 *
	 * @param array $portfolioRows - [ portfolioRow1, portfolioRow2, ...], где portfolioRowN = ['user_id' => int, ...]
	 * @return array - см. описание  метода.
	 */
	private static function groupPortfoliosByUserId($portfolioRows)
	{
		$rowsGroupByUserId = [];
		$counts = [];
		$haveNext = [];
		foreach ($portfolioRows as $row) {

			$userId = $row["user_id"];

			if (!isset($rowsGroupByUserId[$userId])) {
				$rowsGroupByUserId[$userId] = [];
				$counts[$userId] = 0;
				$haveNext[$userId] = 0;
			}

			if ($counts[$userId] >= \Controllers\Want\Payer\PortfolioController::PAGE_LIMIT_FIRST) {
				$haveNext[$userId] = 1;
				continue; // записей достаточно
			}

			$rowsGroupByUserId[$userId][] = $row;
			$counts[$userId]++;
		}
		return [$rowsGroupByUserId, $haveNext];
	}


	/**
	 * Проинициализировать записи перед отправкой в шаблон.
	 * На выходе: [userId => [row1, row2, ...], ...],
	 * где row:
	 * ["id" => int, "title" => string, "category" => string, "cover" => array, "comments_count" => int, "likes_dirty" => int, "views_dirty" => int]
	 *
	 * @param array $portfolioGroupByUserId - [userId => [portfolioRow1, portfolioRow2, ...], ...]
	 * @param Collection|\Model\Portfolio[] $portfolioModels - результат операции Portfolio::query()...->keyBy('id')
	 * @return array - см. описание метода.
	 */
	private static function fillFrontPortfolioItems($portfolioGroupByUserId, $portfolioModels)
	{
		$frontPortfolioItems = [];
		foreach ($portfolioGroupByUserId as $userId => $portfolioRows) {
			foreach ($portfolioRows as $row) {
				$portfolioId = $row["id"];
				$item = $portfolioModels[$portfolioId];
				$tmp = (object)[
					"id" => $portfolioId,
					"title" => $item->title,
					"category" => $item->getCategoryAnyway()->name,
					"cover" => $item->getAllCoverSizeUrls(),
					"is_resizing" => $item->is_resizing,
					"cover_path" => $item->cover,
					"comments_count" => (int)$item->comments_count,
					"likes_dirty" => (int)$item->likes_dirty,
					"views_dirty" => (int)$item->views_dirty,
					"videos" => $item->videos,
				];

				$frontPortfolioItems[$userId][] = $tmp;
			}
		}
		return $frontPortfolioItems;
	}

	/**
	 * Получить список идентификаторов кворков, для которых доступно портфолио.
	 *
	 * @param array $portfolioSelectedCategory - [userId => categoryId, ... ]
	 * @param string $lang
	 * @return array - [userId => categoryId, ... ]
	 */
	private static function getKworkIdsWithPortfolioAvailableBulk($portfolioSelectedCategory, $lang)
	{
		$case = "";
		foreach ($portfolioSelectedCategory as $userId => $catId) {
			$then = "1";
			if ($catId) {
				$then = Kwork::FIELD_CATEGORY . "={$catId}";
			}
			$case .= " WHEN " . Kwork::FIELD_USERID . "={$userId} THEN " . $then . " \n";
		}

		$builder = Kwork::query()
			->select(Kwork::FIELD_PID, Kwork::FIELD_USERID)
			->whereIn(Kwork::FIELD_USERID, array_keys($portfolioSelectedCategory))
			->where(\KworkManager::FIELD_ACTIVE, "!=", \KworkManager::STATUS_DRAFT)
			->where(Kwork::FIELD_HAS_PORTFOLIO, 1)
			->whereRaw("CASE \n $case END");


		if ($lang) {
			$builder->where(Kwork::FIELD_LANG, $lang);
		}

		$rows = $builder->get()->toArray();

		// преобразование к виду USERID => array kworkIds
		$userKworks = [];
		foreach ($rows as $row) {
			$userId = $row[Kwork::FIELD_USERID];
			$kworkId = $row[Kwork::FIELD_PID];
			if (!isset($userKworks[$userId])) {
				$userKworks[$userId] = [];
			}
			$userKworks[$userId][] = $kworkId;
		}

		return $userKworks;
	}

	/**
	 * Запрос создан пользователем
	 * @param Want $want
	 * @return bool
	 */
	private function isUserWant(Want $want) {
		$user = $this->getUser();
		if ($user->id == $want->user_id) {
			return true;
		}
		return false;
	}

	/**
	 * Проверка доступности запроса
	 * @param Want $want
	 */
	private function checkWantStatus(Want $want) {
		if ($want->status == Want::STATUS_DELETE ||
			(in_array($want->status, [Want::STATUS_NEW, Want::STATUS_CANCEL]) && !$this->isUserWant($want))) {
			throw new PageNotFoundException();
		}
	}


	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id ID запроса
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request, int $id = null) {
		global $actor;

		if (is_null($id) && $request->query->getInt("id")) {
			throw (new RedirectException())->setRedirectUrl(($this->getUrlByRoute("view_offers_all", ["id" => $request->query->getInt("id")])));
		}
		$wantId = intval($id);

		$sortType = $request->query->get("s");
		$want = $this->getWant($wantId);


		/** @var User $user */
		$this->checkWantStatus($want);
		$offers = $this->getOffers($wantId);
		$hasHidedOffers = $offers->firstWhere(Offer::FIELD_STATUS, \OfferManager::STATUS_CANCEL);
		$usersReviewsCounts = \RatingManager::getUsersReviewsCount($offers->pluck(Offer::FIELD_USER_ID)->toArray(), $want->lang);
		$wantWorkerOrders = $this->getWantWorkerOrders($wantId);
		$offers = $this->offersSort($offers, $sortType, $wantWorkerOrders);
		//Отметим все предложения как прочтенные если просматривает создатель проекта
		if($this->getUserId() == $want->user_id && $this->isNotVirtual()){
			if(count($offers) > 0){
				$needUpdate = false;
				foreach($offers as $offer){
					if(!$offer->is_read){
						$needUpdate = true;
					}
				}
				if($needUpdate){
					Offer::where(Offer::FIELD_WANT_ID, "=", $wantId)
						->update([Offer::FIELD_IS_READ => 1]);
				}
			}

		}
		//Получаем список юзеров с запросов
		$offerUsers = array_map(function($offer) {
			return $offer[Offer::FIELD_USER_ID];
		}, $offers->toArray());

		if (count($offerUsers) > 0) {
			$userToOrder = \OrderManager::userOrdersWithList($offerUsers);
			//Выбираем последние сделанные/отмененные заказы
			$isSumAmountOrderInTester = $actor && $actor->isVirtual;
			if (count($userToOrder) || $isSumAmountOrderInTester) {
				// Вносим значения "работал ранее" в запросы
				foreach ($offers as $offer) {
					if ($userToOrder[$offer->user_id]) {
						$offer->alreadyWork = $userToOrder[$offer->user_id]["status"];
					}
					if ($isSumAmountOrderInTester) {
						$offer->user->sumAmountOrderIn = \OperationManager::sumOperations($offer->user_id);
						$offer->user->sumAmountOrderInOnYear = \OperationManager::sumOperations($offer->user_id, date("Y-m-d H:i:s", strtotime("-1 year")));
					}
				}
			}
		}
		
		$offersNotActual = [];
		if (!$want->isArchive()) {
			// разделяет предложения на актуальные и не актуальные
			$offersNotActual = $this->filterNotActualOffers($offers);
			$offers = $this->filterActualOffers($offers);
		}
		$hasHidedOffers = $offers->firstWhere(Offer::FIELD_STATUS, \OfferManager::STATUS_CANCEL);

		// #7446 - Добавить просмотр работ портфолио на биржу

		$categoryNames = [];
		$usersPortfolioCategories = [];
		$portfolioSelectedCategory = [];
		$frontPortfolioItems = [];
		$haveNext = [];

		$parameters = [
			"sortType" => $sortType,
			"hasHidedOffers" => !empty($hasHidedOffers),
			"usersReviewsCounts" => $usersReviewsCounts,
			"want" => $want,
			"offers" => $offers,
			"offersNotActual" => $offersNotActual,
			"wantWorkersOrders" => $wantWorkerOrders,
			"isStageTester" => \Order\Stages\OrderStageOfferManager::isTester(),
			"customMinPrice" => \OfferManager::getMinCustomOfferPrice($want->lang, 0, $want->category_id),
			"customMaxPrice" => \OfferManager::getMaxCustomOfferPrice($want->lang),
			"stageMinPrice" => \OfferManager::getMinCustomOfferPrice($want->lang, 0, $want->category_id),
			"offerMaxStages" => \Order\Stages\OrderStageOfferManager::OFFER_MAX_STAGES,
			"offerLang" => $want->lang,
			"maxKworkCount" => \App::config('kwork.max_count'),
			"mdesc" => \Helper::truncateText($want->desc, 150),
			"usersPortfolioCategories" => $usersPortfolioCategories,
			"categoryNames" => $categoryNames,
			"portfolioSelectedCategory" => $portfolioSelectedCategory,
			"portfolioItems" => $frontPortfolioItems,
			"haveNext" => $haveNext,
			"isUserWant" => $this->isUserWant($want),
			"isUserAuthenticated" => $this->isUserAuthenticated(),
			"workerCount" => $this->getCountWorkers($wantId),
			"waitPenaltyMessage" => null,
		];

		$parameters["mtitle"] = "Фриланс проект: " . $want->name . ". Оплата: ";
		$parameters["mtitle"] .= \OfferManager::getMinCustomOfferPrice($want->lang, 0, $want->category_id) . " ₽";

		if ($request->query->has("balance")) {
			$parameters["balance_popup"] = $request->query->get("balance");
			$balanceAmount = 0;
			if ($this->session->isExist("balance_popup_amount")) {
				$balanceAmount = $this->session->get("balance_popup_amount");
				$this->session->delete("balance_popup_amount");
			}
			$parameters["balance_popup_amount"] = $balanceAmount;
		}

		return $this->render("wants/payer/offers/view", $parameters);
	}
	
	/**
	 * Фильтрует предложения, оставляя только актуальные
	 *
	 * @param Collection $offers
	 * @return Collection
	 */
	private function filterActualOffers(Collection $offers): Collection {
		return $offers->filter(function(Offer $item) {
			return $item->isActual();
		});
	}
	
	/**
	 * Фильтрует предложения, оставляя только НЕ актуальные
	 *
	 * @param Collection $offers
	 * @return Collection
	 */
	private function filterNotActualOffers(Collection $offers): Collection {
		return $offers->filter(function(Offer $item) {
			return $item->isNotActual();
		});
	}

}