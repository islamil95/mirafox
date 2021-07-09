<?php


namespace Controllers\Balance;


use Controllers\BaseController;
use Core\Exception\RedirectException;
use Core\Exception\UnauthorizedException;
use Symfony\Component\HttpFoundation\Request;

class AddController extends BaseController {

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			throw new UnauthorizedException();
		}
		$sum = $request->request->getInt("sum");
		\UserManager::refillByAdmin(1, $this->getCurrentUserId(), $sum);

		throw new RedirectException("/balance");
	}

}