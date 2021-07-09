<?php


namespace Controllers\Track\Handler\Extra\Payer;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель повышает уровень кворка
 *
 * Class UpgradePackageHandlerController
 * @package Controllers\Track\Handler\Extra\Payer
 */
class UpgradePackageHandlerController extends AbstractPayerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		// Покупатель повышает уровень кворка
		$orderId = $this->getOrder()->OID;
		$package = $this->getOrder()->orderPackage;

		if (! $this->getOrder()->kwork->is_package || empty($package)) {
			return new RedirectResponse($this->getRedirectUrl());
		}

		$answer = null;
		$upgradeLevel = $this->getRequest()->request->get("upgrade_package_type");
		if (empty($upgradeLevel)) {
			$upgradeLevel = \PackageManager::nextLevel($package->type);
		}

		if (in_array($upgradeLevel, \PackageManager::getBetterPackagesTypes($package->type))) {
			$answer = \OrderManager::payer_upgrade_package($orderId, $upgradeLevel);
		}

		if (!empty($answer) && is_array($answer) && $answer["status"] == "error") {
			$jsonResponse = [
				"result" => "false",
				"error" => "funds",
				"difference" => $answer["difference"],
				"payment_id" => $answer["payment_id"]
			];
		} elseif($answer === true) {
			\KworkReportManager::userUpdateOrder($orderId);
			\Pull\PushManager::sendOrderUpdated($this->getOrder()->worker_id, $orderId);
			$jsonResponse = [
				"result" => "success",
				"redirectUrl" => $this->getRedirectUrl(),
			];
		} else {
			return new RedirectResponse($this->getRedirectUrl());
		}
		return new JsonResponse($jsonResponse);
	}
}