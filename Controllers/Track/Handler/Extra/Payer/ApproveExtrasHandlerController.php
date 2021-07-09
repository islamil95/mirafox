<?php

namespace Controllers\Track\Handler\Extra\Payer;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель принимает предложенные опции
 *
 * Class ApproveExtrasHandlerController
 * @package Controllers\Track\Handler\Extra\Payer
 */
class ApproveExtrasHandlerController extends AbstractPayerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		//Покупатель принимает предложенные опции
		$result = \TrackManager::payerApproveExtras(
			$this->getOrderId(),
			$this->getRequest()->request->getInt("track_id"));
		\Pull\PushManager::sendOrderUpdated($this->getOrder()->worker_id, $this->getOrderId());
		\KworkReportManager::userUpdateOrder($this->getOrderId());
		return new JsonResponse($result);
	}
}