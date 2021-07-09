<?php


namespace Controllers\Want\Payer;

use Core\DB\DB;
use Core\Exception\RedirectException;
use Model\Want;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер для создания нового запроса на услуги
 *
 * Class NewWantController
 * @package Controllers\Want
 */
class NewWantController extends AbstractCreateUpdateWantController {

	/**
	 * Может ли пользователя создавать новые запросы на услуги
	 *
	 * @return bool результат
	 */
	private function isUserCannotCreateWants():bool {
		return !\WantManager::canCreate($this->getUserId());
	}

	/**
	 * @inheritdoc
	 */
	protected function beforeRender(Request $request) {
		if ($this->isUserAuthenticated()) {
			if ($this->isUserCannotCreateWants()) {
				throw (new RedirectException())
					->setRedirectUrl(
						$this->getUrlByRoute("manage_projects", ["project_count_limit" => 1]));
			}
		}
		
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function getWant(Request $request) {
		$wantId = $request->get("id");
		if ($wantId) {
			return Want::find($wantId);
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	protected function getPageTitle(): string {
		return \Translations::t("Добавить задание на биржу Kwork");
	}

	/**
	 * @inheritdoc
	 */
	protected function isNew(): bool {
		return true;
	}

}