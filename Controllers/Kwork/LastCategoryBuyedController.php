<?php

namespace Controllers\Kwork;

use Controllers\BaseController;
use Kwork\LastCategoryBuyedManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ajax контроллер для получения отрендеренного списка последних заказанных кворков в родительской категории
 * (используется для получения замены при скрытии кворка)
 */
class LastCategoryBuyedController extends BaseController {

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request) {
		$excludeIds = \Helper::intArrayNoEmpty(explode(",", $request->request->get("excludeIds")));
		$limit = $request->request->getInt("limit");
		$categoryId = $request->request->getInt("categoryId");
		if (empty($limit)) {
			$limit = 1;
		}

		$manager = new LastCategoryBuyedManager();

		$kworks = $manager->getByParentCategory($categoryId, $limit, $excludeIds);

		return $this->render("fox_bit", ["posts" => $kworks]);
	}

}