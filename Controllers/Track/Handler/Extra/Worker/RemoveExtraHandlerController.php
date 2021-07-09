<?php


namespace Controllers\Track\Handler\Extra\Worker;


use Pull\PushManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец отменяет добавленные опции покупателем
 *
 * Class RemoveExtraHandlerController
 * @package Controllers\Track\Handler\Extra\Worker
 */
class RemoveExtraHandlerController extends AbstractWorkerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function accessNotAllowed(): bool {
		return (!$this->getOrder()->isWorker($this->getUserId()) &&
			$this->isNotVirtual()) ||
			$this->getOrder()->isNotInProgress();
	}

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		// Продавец отменяет докупляемые покупателем опции.
		$extraId = post("extra_id");
		\OrderExtraManager::cancel($extraId);
		$params = [
			"action" => "remove_extra",
			"extraId" => $extraId,
		];
		$orderId = $this->getOrder()->OID;
		PushManager::sendOrderUpdated($this->getOrder()->USERID, $orderId, $params);
		\KworkReportManager::userUpdateOrder($orderId);
		return new RedirectResponse($this->getRedirectUrl());
	}
}