<?php


namespace Controllers\Kwork;


use Controllers\BaseController;
use Core\Exception\RedirectException;
use Core\Exception\UnauthorizedException;
use KworkManager;
use Symfony\Component\HttpFoundation\Request;
use Translations;

class DeleteKworksController extends BaseController {

	public function __invoke(Request $request) {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			throw new UnauthorizedException();
		}
		$gig = $request->get("gig");
		$type = intval($request->get("type"));
		if (empty($type)) {
			$type = 4;
		}
		foreach ($gig as $kworkId) {
			$kworkId = intval($kworkId);
			if ($kworkId > 0) {
				KworkManager::delete($kworkId, $type);
				$this->addFlashMessage(Translations::t("Кворк удален"));
			}
		}
		throw new RedirectException("/manage_kworks");
	}

}