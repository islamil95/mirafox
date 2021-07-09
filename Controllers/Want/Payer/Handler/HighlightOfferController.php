<?php

namespace Controllers\Want\Payer\Handler;

use Controllers\BaseController;
use Core\Exception\PageNotFoundException;
use Core\Response\BaseJsonResponse;
use Core\Traits\Routing\RoutingTrait;
use Model\Offer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Выделение предложения (ajax)
 */
class HighlightOfferController extends BaseController {

	use RoutingTrait;

	/**
	 * Точка входа
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id Идентификатор предложения
	 *
	 * @return \Core\Response\BaseJsonResponse
	 */
	public function __invoke(Request $request, int $id) {
		if (!$this->isUserAuthenticated()) {
			throw new PageNotFoundException();
		}

		$currentUserId = \UserManager::getCurrentUserId();

		$offer = Offer::where(Offer::FIELD_ID, $id)
			->whereHas("want", function ($query) use ($currentUserId) {
				$query->where(\WantManager::F_USER_ID, $currentUserId);
			})
			->first();

		if (empty($offer)) {
			throw new PageNotFoundException();
		}

		$offer->highlighted = 1;
		if ($offer->status == \OfferManager::STATUS_CANCEL) {
			$offer->status = \OfferManager::STATUS_ACTIVE; // отменяем скрытие
		}
		$offer->save();

		return new BaseJsonResponse();
	}
}