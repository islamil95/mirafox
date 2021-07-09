<?php

namespace Controllers\User;


use Controllers\BaseController;

class CheckPayerPhoneVerification extends BaseController {

	public function __invoke() {
		$userId = $this->getCurrentUserId();
		if ($userId && \UserManager::isNeedPayerPhoneVerification($userId)) {
			return $this->failure();
		}
		return $this->success();
	}


}