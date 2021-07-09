<?php


namespace Controllers\Track\Handler\Extra\Payer;


use Controllers\Track\Handler\Extra\Worker\AbstractWorkerExtraHandlerController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель докупает опции
 *
 * Class AddExtrasHandlerController
 * @package Controllers\Track\Handler\Extra\Payer
 */
class AddExtrasHandlerController extends AbstractPayerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		// Покупатель докупает опции
		$extras = \ExtrasManager::extrasFromPost();
		if (empty($extras)) {
			return new RedirectResponse($this->getRedirectUrl());
		}
		$orderId = $this->getOrder()->OID;
		$asVolume = $this->getRequest()->request->get("as_volume");
		$answer = \OrderManager::payer_buy_extras($orderId, $extras, $asVolume);
		if ($answer["status"] == "error") {
			$jsonResponse = [
				"result" => "false",
				"error" => "funds",
				"difference" => $answer["difference"],
				"payment_id" => $answer["payment_id"],
			];
		} else {
			\KworkReportManager::userUpdateOrder($orderId);
			\Pull\PushManager::sendNewOrderTrack($this->getOrder()->worker_id, $orderId, \Track\Type::EXTRA);
			$jsonResponse = [
				"result" => "success",
				"redirectUrl" => $this->getRedirectUrl(),
			];
		}
		return new JsonResponse($jsonResponse);
	}
}