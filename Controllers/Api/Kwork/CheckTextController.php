<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckTextController
 * @package Controllers\Api\Kwork
 */
class CheckTextController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		$data = \Helper::unescapeSlashes($request->request->get("data"));
		$lang = $request->request->get("lang");
		$checkAll = $request->request->get("checkAll");
		return \KworkManager::api_checkText($data, $lang, $checkAll);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork.api_checkText";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}