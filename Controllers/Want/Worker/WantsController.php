<?php

namespace Controllers\Want\Worker;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Список запросов на услуги для продавцов
 *
 * Class WantsController
 * @package Controllers\Want\Worker
 */
class WantsController extends AbstractWantsController {

	/**
	 * @inheritdoc
	 */
	protected function getSelectedCategoryId(Request $request) {
		return $request->query->get("c");
	}

	/**
	 * @inheritdoc
	 */
	protected function getShowWantsForMyKworks(Request $request) {
		return $request->query->get("a");
	}

	/**
	 * @inheritdoc
	 */
	protected function getFavouriteSelectedCategoryId(Request $request) {
		return $request->query->get("fc");
	}

	/**
	 * @inheritdoc
	 */
	protected function getFilter(Request $request) {
		$filter = [];
		if($request->query->has("price-from")) {
			$filter["price_from"] = $request->query->get("price-from");
		}
		if($request->query->has("price-to")) {
			$filter["price_to"] = $request->query->get("price-to");
		}
		if($request->query->has("hiring-from")) {
			$hiringFrom = $request->query->get("hiring-from");
			if (is_numeric($hiringFrom)) {
				if ($hiringFrom > 99) {
					$hiringFrom = 99;
				}
				$filter["hiring_from"] = $hiringFrom;
			}
		}
		if ($request->query->has("kworks-filters")) {
			$filter["kworks_filters"] = $request->query->get("kworks-filters");
		}
		if ($request->query->has("prices-filters")) {
			$filter["prices_filters"] = $request->query->get("prices-filters");
		}
		return $filter;
	}

	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request) {
		if ($this->session->notEmpty("send-kwork-request-success")) {
			$type = $this->session->get("send-kwork-request-success");
			$message = ($type == "custom") ? \Translations::t("Ваше индивидуальное предложение отправлено") : \Translations::t("Предложение кворка отправлено");
			$this->addFlashMessage($message);
			$this->session->delete("send-kwork-request-success");
		}

		$params = [
			"isUserHasOffers" => $this->isUserNotAuthenticated() ? false : \OfferManager::isUserHasOffers($this->getUserId(), \Translations::getLang()),
			"pagetitle" => \Translations::t("Биржа проектов"),
		];
		$params = array_merge($params, $this->getWantsListParameters($request));

		return $this->render("wants/worker/wants_view_page", $params);
	}
}