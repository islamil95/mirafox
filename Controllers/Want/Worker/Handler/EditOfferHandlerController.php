<?php


namespace Controllers\Want\Worker\Handler;

use Controllers\BaseController;
use Core\Exception\JsonValidationException;
use Core\Exception\SimpleJsonException;
use Model\Offer;
use Model\OrderStages\OrderStage;
use OfferManager;
use Order\Stages\OrderStageManager;
use Order\Stages\OrderStageOfferManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Core\Exception\PageNotFoundException;
use Core\Exception\UnauthorizedException;
use Validator\OrderStageValidator;

/**
 * Обработка сохранения предложения
 *
 * Class EditWantHandlerController
 * @package Controllers\Want
 */
class EditOfferHandlerController extends BaseController {

	/**
	 * Вход в контроллер
	 *
	 * @param Request $request HTTP запрос
	 * @return Response
	 */
	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			throw new UnauthorizedException();
		}
		$wantId = $request->request->get("wantId");
		if (empty($wantId)) {
			throw new PageNotFoundException();
		}

		$offer = Offer::where(Offer::FIELD_WANT_ID, "=", $wantId)
			->where(Offer::FIELD_USER_ID, "=", \UserManager::getCurrentUserId())
			->first();
		if (empty($offer) || empty($offer->want)) {
			throw new PageNotFoundException();
		}

		if (!$offer->isCanEdit()) {
			throw new SimpleJsonException(\Translations::t("Редактирование доступно в течение %s мин.", \App::config("offer.edit_time")));
		}
		$oldComment = $offer->comment;

		$lang = $offer->order->getLang();
		$description = stripslashes($request->request->get("description"));
		$description = \OfferManager::commentFilter($description);

		$commentErrors = \OfferManager::checkComment($description, $offer->want->category_id, $lang, $offer->kwork->isCustom());
		if (count($commentErrors) > 0) {
			throw new JsonValidationException($commentErrors);
		}

		//Если у нас индвидуальный кворк
		if ($offer->kwork->isCustom()) {
			$kworkName = $request->request->get("kwork_name");
			$kworkDuration = $request->request->get("kwork_duration");
			$kworkPrice = $request->request->get("kwork_price");
			$kworkDesc = stripslashes($request->request->get("kwork_desc"));
			$kworkDesc = \OfferManager::kworkDescFilter($kworkDesc);
			$stages = post("stages") ? (array)post("stages") : [];
			if (OrderStageOfferManager::isTester()) {
				$kworkDesc = $description;
				$kworkName = $offer->want->name;
			}

			if (is_array($stages) && !empty($stages)) {
				$minPrice = OfferManager::getMinCustomOfferPrice($lang, $offer->want->price_limit, $offer->want->category_id);
				$maxPrice = OfferManager::getMaxCustomOfferPrice($lang, $offer->want->price_limit);
				$validator = new OrderStageValidator($lang, $minPrice, $maxPrice, [], $offer->want->category_id);
				if (!$validator->validate($stages)) {
					throw new JsonValidationException($validator->errors());
				}
				if (count($stages) === 1) {
					$kworkName = array_first($stages)[OrderStage::FIELD_TITLE];
				}
				// Считаем сумму заказов как сумму этапов
				$kworkPrice = 0;
				foreach ($stages as $stage) {
					$kworkPrice += $stage[OrderStage::FIELD_PAYER_PRICE];
				}
			}

			\OfferManager::checkCustomOfferPrice($kworkPrice, $lang, $offer->want->price_limit, $offer->want->category_id);

			$kwork = new \KworkManager($offer->order->PID);
			$turnover = \OrderManager::getTurnover($this->getCurrentUserId(), $offer->order->USERID, $lang);
			$comission = \OrderManager::calculateCommission($kworkPrice, $turnover, $lang);

			$kwork
				->setTitle($kworkName)
				->setDescription($kworkDesc)
				->setWorkTime($kworkDuration)
				->setCustomOfferPrice($kworkPrice)
				->setCustomCtp($comission->priceKwork);

			if ($kwork->save() === false) {
				throw new JsonValidationException(\OfferManager::customKworkRenameErrorsFromKworkManager($kwork->get('errors')));
			} else {

				//Если сохранение индивидуалного кворка прошло успешно, тогда изменяем данные заказа.
				$offer->comment = $description;
				$offer->order->kwork_title = $kwork->get("title");
				$offer->order->crt = $comission->priceWorker;
				$offer->order->data->kwork_desc = $kwork->get("description");
				$offer->order->data->kwork_ctp = $comission->priceKwork;
				$offer->order->data->kwork_price = $kworkPrice;
				$offer->order->initial_offer_price = $kworkPrice;
				$offer->order->kwork_days = $kworkDuration;
				$offer->order->duration = \OrderManager::getDuration($kworkDuration, 1, $offer->want->category) * \Helper::ONE_DAY;
				$offer->order->price = $kworkPrice;
				$offer->order->data->save();
				$offer->order->save();
				$offer->save();

				$offer->order->stages()->delete(); // удаление существующих этапов
				// Изменение этапов
				if (is_array($stages) && count($stages) > 1) {
					OrderStageManager::saveStages($offer->order->OID, $stages); // добавление отредактированных
				}
			}
		} else {
			//При заданном кворке меняем только комментарий
			$offer->comment = htmlspecialchars($description, ENT_QUOTES);
			$offer->save();
		}

		// сохраним время изменения предложения
		\OfferEditManager::updateLastEditTime($offer->id);

		// проверяем предложение на контактные данные
		\TakeAway\OffersManager::checkChangeOffer($offer->id, $offer->comment, $oldComment);

		return $this->success();
	}


}