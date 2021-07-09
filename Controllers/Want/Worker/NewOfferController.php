<?php


namespace Controllers\Want\Worker;

use Controllers\BaseController;
use Core\Traits\ConfigurationTrait;
use Core\Traits\Routing\RoutingTrait;
use Model\Want;
use OfferManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер предложения кворка на запрос
 *
 * Class NewOfferController
 * @package Controllers\Offer
 */
class NewOfferController extends BaseController {

	use RoutingTrait, ConfigurationTrait;

	/**
	 * Правильный ли запрос
	 *
	 * @param \Model\Want $want объект запроса
	 * @return bool результат
	 */
	private function isWantNotValid($want):bool {
		return empty($want) || \UserManager::getCurrentUserId() == $want->user_id || \WantManager::checkExistsOffer($want->id);
	}

	/**
	 * Получить квоки и пакеты пользователя
	 *
	 * @param \Model\Want $want объект запроса
	 * @return array набор кворков и пакетов
	 */
	private function getUserKworksAndPackages($want) {
		return \KworkManager::getUserKworksWithPackages($this->getUserId(), $want->lang, (int) $want->category_id);
	}

	/**
	 * Получение данных запроса
	 *
	 * @param int $wantId Идентификатор запроса
	 *
	 * @return Want|null
	 */
	private function getWant($wantId) {
		return Want::with([
				"category",
				"user",
			])
			->where(\WantManager::F_ID, $wantId)
			->where(\WantManager::F_STATUS, \WantManager::STATUS_ACTIVE)
			->first();
	}

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}

		$currentUser = $this->getUser();
		$projectId = $request->query->getInt("project");
		if(!$currentUser->isVirtual){
			\WantManager::addView([$projectId], $currentUser->id);
		}

		if ($this->getUserType() == \UserManager::TYPE_PAYER) {
			\UserManager::changeUserType();
		}

		$want = $this->getWant($projectId);

		if ($this->isWantNotValid($want)) {
			$this->addFlashError("По этому проекту нельзя сделать предложение");
			return new RedirectResponse($this->getUrlByRoute("projects_worker"));
		}
		$kworksAndPackages = $this->getUserKworksAndPackages($want);
		$kworks = $kworksAndPackages["kworks"];
		$kworkPackage = $kworksAndPackages["kworkPackage"];

		$parameters = [
			"want" => $want,
			"kworkPackage" => json_encode($kworkPackage),
			"kworks" => $kworks,
			"maxKworkCount" => \App::config('kwork.max_count'),
			"maxFileCount" => $this->getMaxFileCount(),
			"maxFileSize" => $this->getMaxFileSize(),
			"pagetitle" => \Translations::t('Предложить услугу'),
			"customMinPrice" => OfferManager::getMinCustomOfferPrice($want->lang, $want->price_limit, $want->category_id),
			"customMaxPrice" => OfferManager::getMaxCustomOfferPrice($want->lang, $want->price_limit),
			"stageMinPrice" => OfferManager::getMinCustomOfferPrice($want->lang, 0, $want->category_id),
			"offerLang" => $want->lang,
			"offerMaxStages" => \Order\Stages\OrderStageOfferManager::OFFER_MAX_STAGES,
			"isStageTester" => \Order\Stages\OrderStageOfferManager::isTester(),
			"controlEnLang" => $currentUser->lang == \Translations::DEFAULT_LANG && $want->lang == \Translations::EN_LANG,
			"turnover" => \OrderManager::getTurnover($currentUser->USERID, $want->user_id, $want->lang),
			"isPageNeedSmsVerification" => true,
		];
		return $this->render("wants/worker/offers/new_offer", $parameters);
	}
}