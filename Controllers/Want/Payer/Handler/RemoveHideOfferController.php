<?php

namespace Controllers\Want\Payer\Handler;

use Controllers\BaseController;
use Core\Exception\PageNotFoundException;
use Core\Response\BaseJsonResponse;
use Core\Traits\Routing\RoutingTrait;
use Model\Offer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Убрать скрытие предложения (ajax)
 */
class RemoveHideOfferController extends BaseController {

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
			->where(Offer::FIELD_STATUS, \OfferManager::STATUS_CANCEL)
			->whereHas("want", function ($query) use ($currentUserId) {
				$query->where(\WantManager::F_USER_ID, $currentUserId);
			})
			->first();

		if (empty($offer)) {
			throw new PageNotFoundException();
		}

		$offer->status = \OfferManager::STATUS_ACTIVE;
		$offer->save();

		return new BaseJsonResponse();
	}
}