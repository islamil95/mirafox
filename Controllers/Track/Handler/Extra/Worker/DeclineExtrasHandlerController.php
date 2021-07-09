<?php


namespace Controllers\Track\Handler\Extra\Worker;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Продавец отменяет предложенные опции
 *
 * Class DeclineExtrasHandlerController
 * @package Controllers\Track\Handler\Extra\Worker
 */
class DeclineExtrasHandlerController extends AbstractWorkerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		// Продавец отменяет предложенные опции
		$orderId = $this->getOrder()->OID;
		$trackId = $this->getRequest()->request->getInt("track_id");
		\TrackManager::workerDeclineExtras($orderId, $trackId);
		return new RedirectResponse($this->getRedirectUrl());
	}
}