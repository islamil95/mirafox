<?php

namespace Controllers\User;

use Controllers\BaseController;
use Controllers\Kwork\ViewKworkController;
use Model\User;
use Symfony\Component\HttpFoundation\Request;
use Model\Portfolio;

/**
 * Портфолио на странице профиля пользователя
 *
 * Class ProfileController
 * @package Controllers\User
 */
class PortfolioController extends BaseController {

	/**
	 * Количество портфолио на странице
	 */
	const DEFAULT_PAGE_LIMIT = 12;

	/**
	 * @param Request $request
	 *
	 * @return \Core\Response\BaseJsonResponse
	 */
	public function __invoke(Request $request) {
		$userId = (int)$request->request->get("userId");
		$categoryId = (int)$request->request->get("category");
		$portfoliosIds =  \Helper::intArrayNoEmpty(explode(",", (string)$request->request->get("portfoliosIds")));
		$limit = $request->request->getInt("limit", self::DEFAULT_PAGE_LIMIT);

		$userExist = User::where(User::FIELD_USERID, $userId)->exists();
		if (!$userExist || in_array($userId, [\App::config("kwork.support_id"), \App::config("kwork.moder_id")])) {
			return $this->failure(\Translations::t("Пользователь не найден"));
		}

		// Получим query builder
		$query = \PortfolioManager::getCommonPortfolioWithCommentsCountBuider($userId, $categoryId, \Translations::getLang());
		$query->orderByDesc(Portfolio::FIELD_ID);

		// Получим все идентификаторы портфолио (не более 100)
		$allIds = $query->limit(ViewKworkController::PORTFOLIO_ITEMS_LIMIT)
			->pluck(Portfolio::FIELD_ID)
			->toArray();

		// Объединяем полученные идентификаторы с присланными и сортируем
		$allIds = array_unique(array_merge($portfoliosIds, $allIds));
		rsort($allIds);

		// получаем на 1 больше чем необходимо, чтобы  понять есть ли еще
		$limit = $limit + 1;
		$portfolios = $query->whereNotIn(Portfolio::FIELD_ID, $portfoliosIds)
			->with(["user", "category", "kwork", "kwork.kworkCategory"])
			->limit($limit)
			->get();

		$haveNext = false;
		if ($portfolios->count() == $limit) {
			$haveNext = true;
			$portfolios->forget($limit - 1);
		}

		$content = $this->render("user/portfolio-list", ["portfolio" => $portfolios])
			->getContent();

		return $this->success([
			"content" => $content,
			"portfolio_have_next" => (int)$haveNext,
			"portfolioAllIds" => $allIds,
		]);
	}

}