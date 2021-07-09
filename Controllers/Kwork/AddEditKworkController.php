<?php


namespace Controllers\Kwork;


use App;
use Controllers\BaseController;
use Core\Exception\UnauthorizedException;
use Helpers\KworkSaveManager;
use KworkManager;
use Model\Category;
use Symfony\Component\HttpFoundation\Request;
use Translations;

class AddEditKworkController extends BaseController {

	public function __invoke(Request $request) {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			throw new UnauthorizedException();
		}
		$lang = Translations::getLang();

		$kworkId = (int)$request->get("id");

		if ($kworkId) {
			$kwork = new KworkManager($kworkId);
			$lang = $kwork->getLang();
			$mode = KworkSaveManager::MODE_EDIT;
		} else {
			$kwork = new KworkManager();
			$mode = KworkSaveManager::MODE_ADD;
		}

		if ($kwork->getId()) {
			$lang = $kwork->getLang() ? $kwork->getLang() : $lang;
		}

		$kworkFields = ['id', 'title', 'description', 'url', 'price', "isQhuick"];

		$kworkAssignArray = [];
		foreach ($kworkFields as $field) {
			$kworkAssignArray[$field] = $kwork->get($field);
		}
		$params = [];
		$params["kwork"] = $kworkAssignArray;

		$portfolioItems = [];
		if ($mode == KworkSaveManager::MODE_EDIT) {
			$params["pagetitle"] = Translations::t("Изменить");
		} else {
			$params["pagetitle"] = Translations::t("Создать кворк");
		}

		$tooltipPrice = KworkManager::getDefaultPrice($lang);
		$realprice = $tooltipPrice - KworkManager::getCtp($tooltipPrice);

		$params["realprice"] = $realprice;
		$params["tooltipPrice"] = $tooltipPrice;


		$params["isQuickEnable"] = App::config('module.quick.enable');

		// Сервер для LanguageTool
		$languageToolServer = \App::config("language_tool.server_" . \Translations::getLang());
		$params["languageToolServer"] = $languageToolServer;

		$categories = Category::where(Category::FIELD_LANG, $lang)
			->where(Category::FIELD_PARENT, 0)
			->get();

		$params["categories"] = $categories;
		$params["allowDescriptionFiles"] = false;

		return $this->render("new_edit_kwork", $params);
	}

}