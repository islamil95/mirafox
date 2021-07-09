<?php

namespace Controllers\Want\Worker;

use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Model\Offer;
use Model\Want;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер просмотра отправленных предложений на запросы
 *
 * Class OffersController
 * @package Controllers\Want
 */
class OffersController extends BaseController {

	use RoutingTrait;

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}
		$isUserHasOffers = \OfferManager::isUserHasOffers($this->getUserId(), \Translations::getLang());
		if (!$isUserHasOffers) {
			return new RedirectResponse($this->getUrlByRoute("projects_worker"));
		}

		//Если переход по ссылке из ЛС, нам нужно узнать номер страницы на которой ответ на проект, и сделать редирект
		//на эту страницу что бы раскрылось предложение #7376
		$projectId = $request->get("project");
		$page = 1;
		if ($projectId) {
			$offerList = Offer::select([Offer::FIELD_WANT_ID])
							->whereIn(Offer::FIELD_STATUS, [
								\OfferManager::STATUS_ACTIVE,
								\OfferManager::STATUS_CANCEL,
								\OfferManager::STATUS_DONE,
								\OfferManager::STATUS_REJECT,
							])
							->where(Offer::FIELD_USER_ID, $this->getUserId())
							->whereHas("want", function ($query) {
								$currentUser = \UserManager::getCurrentUser();
								// На RU сайте выводим RU и EN предложения (если юзер не отключил), на EN сайте - только EN предложения [тикет #6506]
								if (\Translations::isDefaultLang()) {
									$langs = [\Translations::DEFAULT_LANG];
									if (!$currentUser->disableEn) {
										$langs[] = \Translations::EN_LANG;
									}
								} else {
									$langs = [\Translations::EN_LANG];
								}
								$query->whereIn(\WantManager::F_STATUS, [Want::STATUS_ACTIVE, Want::STATUS_STOP, Want::STATUS_USER_STOP])
									->whereIn(\WantManager::F_LANG, $langs);
							})
							->orderByDesc(Offer::FIELD_ID)
							->pluck(Offer::FIELD_WANT_ID)
							->chunk(\App::config("per_page_items"))
							->toArray();

			for($i = 0; $i < count($offerList); $i++){
				if(in_array($projectId, $offerList[$i])){
					$page = $i + 1;
					$redirectUrl = "/offers?page=" . $page . "#project" . $projectId;
					return new RedirectResponse($redirectUrl);
				}
			}
		}

		if ($this->session->notEmpty("send-kwork-request-success")) {
			$this->addFlashMessage(\Translations::t('Предложение услуг отправлено'));
			$this->session->delete("send-kwork-request-success");
		}

		if ($this->getUserType() == \UserManager::TYPE_PAYER) {
			\UserManager::changeUserType();
		}
		$offers = \OfferManager::getUserOffers($this->getUserId());

		if (count($offers) > 0) {
			//Получаем список юзеров с запросов
			$offerUsers = array_map(function($offer) {
				return $offer->want[Want::FIELD_USER_ID];
			}, $offers->items());

			$userToOrder = \OrderManager::userOrdersWithList($offerUsers);

			//Выбираем последние сделанные/отмененные заказы
			if (count($userToOrder)) {
				// Вносим значения "работал ранее" в запросы
				foreach ($offers as $offer) {
					if ($userToOrder[$offer->want->user_id]) {
						$offer->alreadyWork = $userToOrder[$offer->want->user_id]["status"];
					}
				}
			}
		}
		$currentUserReviewsCount = \RatingManager::userCounts($this->getUserId(), \Translations::getLang());

		$parameters = [
			"offers" => $offers,
			"usersReviewsCounts" => [$this->getUserId() => $currentUserReviewsCount],
			"pagetitle" => \Translations::t("Биржа проектов"),
			"isMyOfferPage" => 1
		];
		return $this->render("wants/worker/offers/list_page", $parameters);
	}

}