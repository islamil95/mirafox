<?php

namespace Controllers\Want\Payer;

use Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Model\Portfolio;

class PortfolioController extends BaseController
{
	/**
	 * Количество портфолио при первой загрузке
	 */
	const PAGE_LIMIT_FIRST = 6;
	/**
	 * Количество портфолио кроме первой загрузки
	 */
	const PAGE_LIMIT = 9;

	/**
	 * @param Request $request
	 * @return \Core\Response\BaseJsonResponse
	 * @throws \PHLAK\Config\Exceptions\InvalidContextException
	 */
	public function __invoke(Request $request)
	{
		$userId = (int)$request->request->get("userId");
		$categoryId = (int)$request->request->get("category");
		$page = (int)$request->request->get("page");
		$lang = $request->request->get("lang");

		if (!in_array($lang, [\Translations::EN_LANG, \Translations::DEFAULT_LANG])) {
			$lang = \Translations::DEFAULT_LANG;
		}

		if (empty($page)) {
			$page = 1;
		}

		$pageLimit = self::PAGE_LIMIT;
		if ($page == 1) {
			$pageLimit = self::PAGE_LIMIT_FIRST;
		}

		$offset = self::PAGE_LIMIT_FIRST + ($page - 2) * self::PAGE_LIMIT;
		if ($offset < 0) {
			$offset = 0;
		}

		$buider = \KworkManager::getCommonPortfolioBuider($userId, $categoryId, $lang);

		$totalCount = $buider->count();
		$portfolios = $buider
			->with(["category", "kwork", "kwork.kworkCategory", "videos"])
			->orderByDesc(Portfolio::FIELD_ID)
			->skip($offset)
			->take($pageLimit)
			->get();

		$curCount = $offset + count($portfolios);

		$haveNext = true;
		if ($curCount >= $totalCount) {
			$haveNext = false;
		}

		$items = [];
		// подготовка данных для фронта
		foreach ($portfolios as $item) {
			$items[] = (object)[
				"id" => $item->id,
				"title" => $item->title,
				"category" => $item->getCategoryAnyway()->name,
				"cover" => $item->getAllCoverSizeUrls(),
				"cover_path" => $item->cover,
				"comments_count" => (int)$item->comments_count,
				"likes_dirty" => (int)$item->likes_dirty,
				"views_dirty" => (int)$item->views_dirty,
				"videos" => $item->videos,
			];
		}

		$renderedItems = $this->render("wants/common/offer_portfolio_list.tpl", ["items" => $items]);

		$data = [
			"totalCount" => $totalCount,
			"offset" => $offset,
			"curCount" => $curCount,
			"haveNext" => (int)$haveNext,
			"portfolioItems" => $renderedItems->getContent(),
		];

		return $this->success($data);
	}
}