<?php

namespace Controllers\Error;

use Controllers\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Helper;
use \Translations;

/**
 * 404 страница
 *
 * Class PageNotFoundController
 * @package Controllers\Error
 */
class PageNotFoundController extends BaseController {

	public function __invoke() {
		if (Helper::isAjaxRequest()) {
			return new JsonResponse(Translations::t("Страница не найдена"), 404);
		} else {
			$response = $this->render("not_found", [
					"pagetitle" => Translations::t("Страница не найдена"),
				]);
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			return $response;
		}
	}
}