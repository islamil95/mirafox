<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteAttachmentController
 * @package Controllers\Api\Kwork
 */
class DeleteAttachmentController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return \KworkManager::api_deleteAttachment();
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork.api_deleteAttachment";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}