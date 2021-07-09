<?php


namespace Controllers\Kwork;


use Controllers\BaseController;
use KworkManager;
use Symfony\Component\HttpFoundation\Request;

class UpdateRatingsController extends BaseController {

	public function __invoke(Request $request) {
		$updated = KworkManager::calculateRatings();
		return $this->success($updated);
	}
}