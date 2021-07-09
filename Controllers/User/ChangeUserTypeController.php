<?php


namespace Controllers\User;


use Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChangeUserTypeController
 * @package Controllers\User
 */
class ChangeUserTypeController extends BaseController {

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			return $this->failure();
		}
		$userType = (int)$request->get("usertype") == 1 ? "payer" : "worker";
		$user = \UserManager::getCurrentUserModel();
		if (!$user) {
			return $this->failure();
		}

		$user->type = $userType;
		$user->save();

		return $this->success();
	}

}