<?php


namespace Controllers\Want\Payer;


use Controllers\BaseController;
use Model\Offer;
use Model\Want;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Отображеие списка запросов на услуги для покупателя
 *
 * Class WantsController
 * @package Controllers\Want\Payer
 */
class WantsController extends BaseController {

	const WANTS_PER_PAGE = 20;

	public function __invoke(Request $request) {
		
		if ($request->query->get("success_registration") == 1) {
			$this->addFlashMessage(\Translations::t('Вы успешно зарегистрированы в системе. На адрес электронной почты выслано письмо с паролем и подтверждением регистрации'));
		}

		if ($request->query->get("project_count_limit") == 1) {
			$this->addFlashError(\Translations::t("К сожалению, вы можете создать не более 30 проектов в сутки"));
		}
		$status = $request->query->get("status", null);
		if (empty($this->getCurrentUserId())) {
			return new RedirectResponse("/new_project");
		}
		$wantsCount = \WantManager::getWantsCount($this->getCurrentUserId(), $status);
		$query = \WantManager::getWants($this->getCurrentUserId(), $status);

		$wants = $query->paginate(self::WANTS_PER_PAGE);
		$wantIds = array_map(function($want) {
			return $want->id;
		}, $wants->items());

		$newOffers = [];
		if (count($wantIds) > 0) {
			$newOffers = Offer::selectRaw("count(IF(is_read = 0, 1, null)) as unread, count(IF(is_read = 1, 1, null)) as isread, " . Offer::FIELD_WANT_ID)
				->whereIn(Offer::FIELD_WANT_ID, $wantIds)
				->groupBy(Offer::FIELD_WANT_ID)
				->get()
				->keyBy(Offer::FIELD_WANT_ID)
				->toArray();
		}

		if ($status != Want::STATUS_ARCHIVED) {
			$archivedCount = \WantManager::getWantsCount($this->getCurrentUserId(), Want::STATUS_ARCHIVED);
		} else {
			$archivedCount = $wantsCount;
			$wantsCount = \WantManager::getWantsCount($this->getCurrentUserId());
		}

		if ($wantsCount == 0 && $archivedCount == 0) {
			return new RedirectResponse("/new_project");
		}
		if (!is_null($status)) {
			$wants->appends(['status' => $status]);
		}

		$parameters = [
			"wants" => $wants,
			"wantsCount" => $wantsCount,
			"archivedCount" => $archivedCount,
			"pagetitle" => \Translations::t("Мои проекты"),
			"status" => $status,
			"newOffers" => $newOffers,
		];
		return $this->render("wants/payer/manage/list", $parameters);
	}
}