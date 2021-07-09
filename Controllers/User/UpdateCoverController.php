<?php


namespace Controllers\User;


use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка обновления шапки профиля
 *
 * Class UpdateCoverController
 * @package Controllers\User
 */
class UpdateCoverController extends BaseController {

	use RoutingTrait;

	public function __invoke(Request $request, $username) {
		$urlParameters = [
			"username" => $username,
		];
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse($this->getUrlByRoute("profile_view", $urlParameters));
		}
		/**
		 * @TODO PhotoManager::saveCoverPhoto и далее по стеку используют параметры из глобального массива, оставлено для совместимости, но нужно переделать
		 */
		$coverFile = count($_FILES["cover-photo"]["name"]) > 0 && $_FILES["cover-photo"]["name"][0];
		if ($coverFile) {
			$userCovers = $_FILES["cover-photo"];
			$coverSizes = $request->request->get("cover-photo-size");
			\PhotoManager::saveCoverPhoto($this->getUserId(), $userCovers, $coverSizes);
		}
		return new RedirectResponse($this->getUrlByRoute("profile_view", $urlParameters));
	}
}