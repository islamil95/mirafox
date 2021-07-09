<?php


namespace Controllers\Kwork;


use Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;

class RatingsController extends BaseController {

	public function __invoke(Request $request) {

		$params["pagetitle"] = \Translations::t("Пересчёт рейтингов кворк");

		return $this->render("ratings_kworks", $params);
	}

}