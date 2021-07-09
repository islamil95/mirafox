<?php

namespace Controllers\Kwork;

use Core\DB\DB;
use Controllers\BaseController;
use Core\Exception\PageNotFoundException;
use Kwork\KworkLinkSiteRelationManager;
use Kwork\Patch\PatchManager;
use Core\Traits\Kwork\KworkStatusTrait;
use Model\KworkLinksSite;
use Model\Notification\Notification;
use Model\Notification\NotificationType;
use Model\Portfolio;
use Model\PortfolioLikeView;
use Model\VolumeType;
use Order\OrderLinksSitesManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Kwork\Similar\ImportantWordsSelect;
use VolumeType\AttributeAdditionalVolumeType;
use VolumeType\CategoryAdditionalVolumeType;

/**
 * Страница сбора лайков и просмотров работ в портфолио
 *
 * Class ViewKworkController
 * @package Controllers\Kwork
 */
class LikeKworkPortfolioController extends BaseController {

	use KworkStatusTrait;

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $portfolioId Идентификатор элемента портфолио
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request, int $portfolioId = null) {
		$actor = \UserManager::getCurrentUser();
		if (empty($portfolioId)) {
			return $this->failure("Portfolio id is empty");
		}
		$portfolio = Portfolio::where('id', $portfolioId)->first();

		if (empty($portfolio)) {
			return $this->failure("Wrong portfolio id");
		}
		$toUpdate = [];
		$toUpdate["likes_dirty"] = $portfolio['likes_dirty'] + 1;

		$response = "error";
		//Если авторизированный пользователь
		if ($actor->USERID) {
			$isDirty = \UserManager::isDirty($actor->USERID);

			//Если существует лайк в базе
			$isExistUserLike = DB::table(PortfolioLikeView::TABLE_NAME)
				->where('user_id', '=', $actor->USERID)
				->where('portfolio_id', '=', $portfolioId)
				->where('type', '=', PortfolioLikeView::TYPE_LIKE)
				->exists();

			if (!$isExistUserLike) {
				$response = "add";
				if (!$isDirty) {
					$toUpdate["likes"] = $portfolio['likes'] + 1;
				}

				DB::table(PortfolioLikeView::TABLE_NAME)->insert(
					[
						'user_id' => $actor->USERID,
						'portfolio_id' => $portfolioId,
						'type' => PortfolioLikeView::TYPE_LIKE,
						'is_dirty' => (int) $isDirty,
					]
				);

			} else {
				//Если существует лайк в базе, удаляем его
				$response = "delete";
				$toUpdate["likes_dirty"] = $portfolio['likes_dirty'] - 1;

				if ($toUpdate["likes_dirty"] < 0) {
					$toUpdate["likes_dirty"] = 0;
				}

				if (!$isDirty) {
					$toUpdate["likes"] = $portfolio['likes'] - 1;
					if ($toUpdate["likes"] < 0) {
						$toUpdate["likes"] = 0;
					}
				}

				//Удаляем лайк
				DB::table(PortfolioLikeView::TABLE_NAME)
					->where('user_id', '=', $actor->USERID)
					->where('portfolio_id', '=', $portfolioId)
					->where('type', '=', PortfolioLikeView::TYPE_LIKE)
					->delete();

				DB::table(Notification::TABLE_NAME)
					->where(Notification::F_USERID, "=", $portfolio->user_id)
					->where(Notification::F_TYPE, "=", NotificationType::PORTFOLIO_NEW_LIKE)
					->where(Notification::F_UNREAD, "=", "1")
					->orderByDesc(Notification::F_NID)
					->limit(1)
					->delete();
			}
			if (!empty($toUpdate)) {
				Portfolio::where('id', '=', $portfolioId)
					->update($toUpdate);

			}
		} else {
			return $this->failure("Unauthenticated user");
		}

		return $this->success($response);
	}

	/**
	 * Метод добавляет просмотр к портфолио
	 * @param $userId
	 * @param $portfolioId
	 * @return bool
	 */
	public function addView($userId, $portfolioId) {
		//Если авторизированный пользователь
		$toUpdate = [];
		if (empty($portfolioId)) {
			return false;
		}
		$portfolio = Portfolio::where('id', $portfolioId)->first();
		if (!empty($userId)) {
			$isExistUserView = DB::table(PortfolioLikeView::TABLE_NAME)
				->where('user_id', '=', $userId)
				->where('portfolio_id', '=', $portfolioId)
				->where('type', '=', PortfolioLikeView::TYPE_VIEW)
				->exists();

			if (!$isExistUserView) {
				$isDirty = \UserManager::isDirty($userId);
				$toUpdate["views_dirty"] = $portfolio['views_dirty'] + 1;
				if (!$isDirty) {
					$toUpdate["views"] = $portfolio['views'] + 1;
				}
				DB::table(PortfolioLikeView::TABLE_NAME)->insert(
					[
						'user_id' => $userId,
						'portfolio_id' => $portfolioId,
						'type' => PortfolioLikeView::TYPE_VIEW,
						'is_dirty' => (int) $isDirty,
					]
				);
			} else {
				return false;
			}
		} else {
			$view_want_item = $this->session->get("view_porftolio_item", []);
			//Для неавторизированных юзеров записываем просмотр портфолио в сессию
			if (!isset($view_want_item[$portfolioId])) {
				$view_want_item[$portfolioId] = 1;
				$this->session->set("view_porftolio_item", $view_want_item);
				$toUpdate["views_dirty"] = $portfolio['views_dirty'] + 1;
				DB::table(PortfolioLikeView::TABLE_NAME)->insert(
					[
						'portfolio_id' => $portfolioId,
						'type' => PortfolioLikeView::TYPE_VIEW,
						'is_dirty' => 1,
					]
				);
			}
		}
		if (!empty($toUpdate)) {
			Portfolio::where('id', '=', $portfolioId)
				->update($toUpdate);
		}
	}
	
	/**
	 * Метод узнаем просматривал ли юзер портфолио
	 * @param int $userId
	 * @param int $portfolioId
	 * @return bool
	 */
	public function viewExist($userId, $portfolioId) {
		$portfolio = Portfolio::where("id", $portfolioId)->first();
		$isExistView = false;
		//Если авторизированный пользователь
		if (!empty($userId)) {
			$isExistView = DB::table(PortfolioLikeView::TABLE_NAME)
				->where("user_id", "=", $userId)
				->where("portfolio_id", "=", $portfolioId)
				->where("type", "=", PortfolioLikeView::TYPE_VIEW)
				->exists();
		} else {
			//Для неавторизированных юзеров записываем просмотр портфолио в сессию
			$view_want_item = $this->session->get("view_porftolio_item", []);
			$isExistView = isset($view_want_item[$portfolioId]);
		}
		return $isExistView;
	}
}