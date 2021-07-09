<?php


namespace Controllers\Kwork;


use Controllers\BaseController;
use Core\Exception\UnauthorizedException;
use KworkManager;
use Model\Kwork;
use Model\UserData;
use Paging;
use Symfony\Component\HttpFoundation\Request;
use Translations;

class ManageKworksController extends BaseController {

	public function __invoke(Request $request) {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			throw new UnauthorizedException();
		}

		//Получаем все кворки продавца с доп.даными по ним.
		$kworks = Kwork::where(Kwork::FIELD_USERID, $actor->id)
			->whereNotIn(Kwork::FIELD_ACTIVE, [KworkManager::STATUS_DELETED, KworkManager::STATUS_CUSTOM])
			->orderByDesc(Kwork::FIELD_PID)
			->get()
			->all();

		$total = count($kworks);

		$params = [];
		$params["total"] = $total;
		$params["posts"] = $kworks;
		$params["pagetitle"] = \Translations::t("Мои кворки");

		return $this->render("manage_kworks", $params);
	}

}