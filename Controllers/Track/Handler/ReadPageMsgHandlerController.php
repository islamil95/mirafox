<?php

namespace Controllers\Track\Handler;

use Core\Exception\RedirectException;
use Core\Response\BaseJsonResponse;
use Model\Order;
use Symfony\Component\HttpFoundation\Response;
use Strategy\Track\GetPageMessageStrategy;

/**
 * Прочитать через ajax сообщение, выводимое в треке
 *
 * Class ReadPageMsgHandlerController
 * @package Controllers\Track\Handler
 */
class ReadPageMsgHandlerController extends AbstractTrackHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}


	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	protected function processAction(): Response {
		$orderId = $this->getRequest()->request->getInt("orderId", 0);
		if ($orderId <= 0) {
			throw (new RedirectException())->setRedirectUrl("/");
		}
		
		$response = new BaseJsonResponse();
		$response->setStatus(true);
		$response->setResponseData([
			"pageMsg" => (new GetPageMessageStrategy($this->getOrder()))->get(),
		]);

		return $response;
	}
}