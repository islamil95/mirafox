<?php


namespace Controllers\Want\Worker;

use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Model\Offer;
use Model\Want;
use OfferManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер редактирования предложения кворка на запрос
 *
 * Class EditOfferController
 * @package Controllers\Offer
 */
class EditOfferController extends BaseController {

	use RoutingTrait;

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}

		$currentUser = $this->getUser();

		if ($this->getUserType() == \UserManager::TYPE_PAYER) {
			\UserManager::changeUserType();
		}

		$projectId = $request->query->getInt("project");
		$want = Want::find($projectId);
		if (empty($want->id)) {
			return new RedirectResponse($this->getUrlByRoute("offers"));
		}
		$offer = Offer::where(Offer::FIELD_USER_ID, "=", $currentUser->USERID)
			->where(Offer::FIELD_WANT_ID, "=", $want->id)
			->first();

		//Если предложение создано больше чем 10 минут назад, его нельзя редактировать
		if (!$offer->isCanEdit()) {
			return new RedirectResponse($this->getUrlByRoute("offers"));
		}

		$kworksAndPackages = \KworkManager::getUserKworksWithPackages($this->getUserId(), $want->lang, (int)$want->category_id);
		$kworks = $kworksAndPackages["kworks"];
		$kworkPackage = $kworksAndPackages["kworkPackage"];
		//Костыль нужен т.к. в базу у нас описание сохраняется обернутым в <p></p>
		$kwork_desc = $offer->order->data->kwork_desc;
		$kwork_desc = html_entity_decode($kwork_desc);
		$kwork_desc = str_replace("</p>", "\n", $kwork_desc);
		$offer->order->data->kwork_desc = strip_tags($kwork_desc);

		$showKworkPanelEdit = false;
		if ($offer->kwork->active != \KworkManager::STATUS_CUSTOM) {
			$showKworkPanelEdit = true;
		}

		$parameters = [
			"want" => $want,
			"offer" => $offer,
			"showKworkPanelEdit" => $showKworkPanelEdit,
			"optionsPrices" => \OptionPriceManager::getOptionPrices($want->lang),
			"kworks" => $kworks,
			"kworkPackage" => json_encode($kworkPackage),
			"maxKworkCount" => \App::config('kwork.max_count'),
			"pagetitle" => \Translations::t('Редактирование предложения'),
			"customMinPrice" => OfferManager::getMinCustomOfferPrice($want->lang, $want->price_limit, $want->category_id),
			"customMaxPrice" => OfferManager::getMaxCustomOfferPrice($want->lang, $want->price_limit),
			"stageMinPrice" => OfferManager::getMinCustomOfferPrice($want->lang, 0, $want->category_id),
			"offerMaxStages" => \Order\Stages\OrderStageOfferManager::OFFER_MAX_STAGES,
			"offerLang" => $want->lang,
			"isStageTester" => \Order\Stages\OrderStageOfferManager::isTester(),
			"controlEnLang" => $currentUser->lang == \Translations::DEFAULT_LANG && $want->lang == \Translations::EN_LANG,
			"turnover" => \OrderManager::getTurnover($currentUser->USERID, $want->user_id, $want->lang),
			"commissionRanges" => \CommissionRanges::toJson($want->lang),
		];
		return $this->render("wants/worker/offers/new_offer", $parameters);
	}
}