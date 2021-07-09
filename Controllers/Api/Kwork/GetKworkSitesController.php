<?php


namespace Controllers\Api\Kwork;


use Controllers\Api\AbstractApiController;
use Kwork\KworkLinkSiteRelationManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetKworkSitesController
 * @package Controllers\Api\Kwork
 */
class GetKworkSitesController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		return KworkLinkSiteRelationManager::api_getKworkSites(
			$request->query->getInt("kworkId"),
			$request->query->get("showHosts")
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Kwork\KworkLinkSiteRelation.api_getKworkSites";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return !$request->query->has("kworkId") ||
			!$request->query->has("showHosts") ||
			$request->query->getInt("kworkId") <= 0;
	}
}