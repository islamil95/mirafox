<?php


namespace Controllers\Security;


use Controllers\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Обработка выхода
 *
 * Class LogoutController
 * @package Controllers\Security
 */
class LogoutController extends BaseController {

	public function __invoke() {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}

		\UserManager::logout();
		/**
		 * @TODO: Перевести на роутинг
		 */
		return new RedirectResponse("/");
	}

}