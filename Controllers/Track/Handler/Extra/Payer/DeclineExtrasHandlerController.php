<?php


namespace Controllers\Track\Handler\Extra\Payer;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Покупатель отклоняет предложенные опции
 *
 * Class DeclineExtrasHandlerController
 * @package Controllers\Track\Handler\Extra\Payer
 */
class DeclineExtrasHandlerController extends AbstractPayerExtraHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function processExtras(): Response {
		// Покупатель отклоняет предложенные опции
		$orderId = $this->getOrder()->OID;
		$trackId = $this->getRequest()->request->getInt("track_id");
		\TrackManager::payerDeclineExtras($orderId, $trackId);
		return new RedirectResponse($this->getRedirectUrl());
	}
}