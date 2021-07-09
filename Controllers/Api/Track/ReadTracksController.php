<?php


namespace Controllers\Api\Track;


use Controllers\Api\AbstractApiController;
use Model\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReadTracksController
 * @package Controllers\Api\Track
 */
class ReadTracksController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		$orderId = $request->request->get("orderId");
		$order = Order::find($orderId);
		$itemIds = $request->request->get("itemIds");
		if (empty($order) || empty($itemIds)) {
			return [
				"success" => false,
			];
		}

		return [
			"success" => \TrackManager::readTracks($order, $itemIds),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Track.api_readTracks";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}