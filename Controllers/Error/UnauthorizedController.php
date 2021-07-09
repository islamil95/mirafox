<?php

namespace Controllers\Error;

use Controllers\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Helper;
use \Translations;

/**
 * 401 страница
 */
class UnauthorizedController extends BaseController {

	public function __invoke() {
		if (Helper::isAjaxRequest()) {
			return new JsonResponse(Translations::t("Доступ запрещен"), 401);
		} else {
			// TODO: сверстать страницу (пока выдается 404)
			$response = $this->render("not_found", [
					"pagetitle" => Translations::t("Страница не найдена"),
				]);
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			return $response;
		}
	}
}