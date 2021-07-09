<?php

namespace Controllers\Kwork;

use Controllers\BaseController;
use Core\Exception\PageNotFoundException;
use Core\DB\DB;
use Model\Portfolio;
use Model\PortfolioLikeView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Страница просмотра кворка
 *
 * Class ViewKworkController
 * @package Controllers\Kwork
 */
class ViewKworkPortfolioController extends BaseController {

	private $portfolioSortedIds = null;

	/**
	 * Нужно ли скрыть категории связанные с SMM
	 *
	 * @param int $categoryId Идентификатор категории портфолио
	 *
	 * @return bool результат
	 */
	private function isHideSMMCategory($categoryId):bool {
		return $categoryId == \UserManager::CATEGORY_SMM_ID && \UserManager::isHideSocialSeo();
	}

	/**
	 * Получить массив идентификаторов портфолио отсортированных также как в слайдере кворка
	 *
	 * @param int $kworkId Идентификатор кворка
	 *
	 * @return array
	 */
	private function getPortfolioIdsSorted($kworkId) {
		if ($kworkId && is_null($this->portfolioSortedIds)) {
			$query = "SELECT IF(r.time_added IS NULL, UNIX_TIMESTAMP(p.date_create), r.time_added) AS date_portfolio, 
						p.id
                    FROM portfolio p
                    JOIN orders o ON o.OID = p.order_id
                    LEFT JOIN ratings r ON r.OID = p.order_id
                    WHERE p.kwork_id = :kworkId and p.status = 'active' 
                    ORDER BY date_portfolio DESC
                    LIMIT " . ViewKworkController::PORTFOLIO_ITEMS_LIMIT;
			$orderSortedIds = \App::pdo()->fetchAllByColumn($query, 1, ["kworkId" => $kworkId]);
			$kworkSortedIds = Portfolio::where(Portfolio::FIELD_KWORK_ID, $kworkId)
				->whereNull(Portfolio::FIELD_ORDER_ID)
				->orderBy(Portfolio::FIELD_POSITION)
				->pluck(Portfolio::FIELD_ID)
				->toArray();

			$this->portfolioSortedIds = array_merge($orderSortedIds, $kworkSortedIds);
		}
		return $this->portfolioSortedIds;
	}

	/**
	 * Получение максимальной ширины изображений портфолио
	 *
	 * @param \Model\Portfolio $portfolio Модель портфолио
	 *
	 * @return int
	 */
	private function getPortfolioMaxContentWidth(Portfolio $portfolio) {
		$imagesWidth = [];
		foreach ($portfolio->images as $portfolioImage) {
			if (!empty($portfolioImage->width)) {
				$imagesWidth[] = $portfolioImage->width;
			} else {
				if (file_exists($portfolioImage->getMaxSizePath())) {
					$imageSize = getimagesize($portfolioImage->getMaxSizePath());
					if ($imageSize) {
						$portfolioImage->width = $imageSize[0];
						$portfolioImage->height = $imageSize[1];
						$portfolioImage->save();
						$imagesWidth[] = $portfolioImage->width;
					}
				}
			}
		}
		
		$resultWidth = 0;
		if (!empty($imagesWidth)) {
			$resultWidth = max($imagesWidth);
		}	
		
		// Если портфолио бдуте меньше 600, то окно будет обрезаться
		if ($resultWidth < 600 && $resultWidth > 0) {
			$resultWidth = 600;
		}			

		return $resultWidth;
	}

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $kworkId Идентфикатор кворка
	 * @param int $portfolioId Идентификатор элемента портфолио
	 * @param bool $isModal В модальном ли окне открывать
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request, int $kworkId = null, int $portfolioId = null, bool $isModal = true) {
		$actor = \UserManager::getCurrentUser();

		$portfolio = null;
		if ($portfolioId) {
			$portfolio = Portfolio::with(["images","videos", "kwork", "user"])
				->find($portfolioId);
			$kworkId = $portfolio->kwork_id;
		} else if($kworkId) {
			$portfolioSortedIds = $this->getPortfolioIdsSorted($kworkId);
			if (!empty($portfolioSortedIds)) {
				$portfolio = Portfolio::with(["images","videos", "kwork", "user"])
					->find($portfolioSortedIds[0]);
			}
		}

		if (is_null($portfolio)) {
			throw new PageNotFoundException();
		}

		/* Сохраняем информацию о просмотрах */
		$pview = new LikeKworkPortfolioController();
		$viewExist = $pview->viewExist($actor->USERID, $portfolio->id);
		
		$pview->addView($actor->USERID, $portfolio->id);

		$likeExist = false;
		if($actor->USERID){
			$likeExist = DB::table(PortfolioLikeView::TABLE_NAME)
				->where('user_id', '=' ,$actor->USERID)
				->where('portfolio_id', '=', $portfolio->id)
				->where('type', '=', PortfolioLikeView::TYPE_LIKE)
				->exists();
		}

		if ($kworkId) {
			if (\BanManager::banAdwords($kworkId)) {
				return new RedirectResponse("/not_access.php");
			}
			if (!$portfolio->kwork) {
				throw new PageNotFoundException();
			}
			if (\BanManager::banAdwords($kworkId)) {
				return new RedirectResponse("/not_access.php");
			}
		}

		if ($this->isHideSMMCategory($portfolio->getCategoryIdAnyway())) {
			throw new PageNotFoundException();
		}

		// если категория запрещена для зеркала, то редирект
		if (\App::isMirror() && $portfolio->getCategoryAnyway() && !$portfolio->getCategoryAnyway()->allow_mirror) {
			throw new PageNotFoundException();
		}

		$isFullPortfolio = $request->get('mode') === 'portfolio';
		$portfolioSortedIds = $isFullPortfolio ? explode(",", $request->get("ids", "")) : null;

		$portfolioSortedIds = $portfolioSortedIds ?: $this->getPortfolioIdsSorted($kworkId);
		$portfolioSortedIds = $portfolioSortedIds ?: [];

		$allPortfolioItems = Portfolio::with(["images"])
			->whereIn(Portfolio::FIELD_ID, $portfolioSortedIds)
			->get()
			->keyBy(Portfolio::FIELD_ID);

		$allPortfolioItemsSorted = [];
		foreach ($portfolioSortedIds as $portfolioSortedId) {
			$allPortfolioItemsSorted[$portfolioSortedId] = $allPortfolioItems->get($portfolioSortedId);
		}

		// Получить количество отзывов пользователя
		$portfolio["user"]["allGoodReviews"] = \RatingManager::userCounts($portfolio["user"]["USERID"])["good"];
		$portfolio["user"]["allBadReviews"] = \RatingManager::userCounts($portfolio["user"]["USERID"])["bad"];

		$parameters = [];
		$parameters["allPortfolioIds"] = array_keys($allPortfolioItemsSorted);
		$parameters["allPortfolioItems"] = $allPortfolioItemsSorted;
		$parameters["likeExist"] = $likeExist;
		$parameters["viewExist"] = $viewExist;
		$parameters["portfolioContentMaxWidth"] = $this->getPortfolioMaxContentWidth($portfolio);

		// Отзывы о работе добавляем перед комментариями
		$comments = \PortfolioCommentManager::getByPortfolioId($portfolio->id, ["orderId" => $portfolio->order_id]);
		$parameters["portfolioComments"] = $comments["comments"];
		$portfolio["hasReview"] = $comments["hasReview"];

		$parameters["portfolio"] = $portfolio;
		$parameters["onPage"] = $comments["count"];
		$parameters["total"] = $comments["total"];
		$parameters["portfolioId"] = $portfolio->id;

		$parameters["portfolioIsModal"] = $isModal;

		if ($isModal) {
			$portfolioTpl = $this->render("portfolio/portfolio_view_popup", $parameters);
		} else {
			$parameters["pagetitle"] = \Translations::t("Просмотр работы");
			$portfolioTpl = $this->render("portfolio/portfolio_view_page", $parameters);
		}

		return $portfolioTpl;
	}
}