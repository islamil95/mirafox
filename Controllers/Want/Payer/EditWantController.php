<?php


namespace Controllers\Want\Payer;

use Core\DB\DB;
use Core\Exception\PageNotFoundException;
use Core\Exception\RedirectException;
use Symfony\Component\HttpFoundation\Request;
use Model\Want;

/**
 * Контроллер для редактирования запросов на услуги
 *
 * Class EditWantController
 * @package Controllers\Want
 */
class EditWantController extends AbstractCreateUpdateWantController {

	/**
	 * @inheritdoc
	 */
	protected function beforeRender(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			throw new PageNotFoundException();
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function getWant(Request $request) {
		$wantId = $request->query->get("id");

		$want = Want::query()
			->where(\WantManager::F_ID, $wantId)
			->where(\WantManager::F_USER_ID, $this->getUserId())
			->first();

		if (!$want || $want->isArchive()) {
			throw (new RedirectException())
				->setRedirectUrl($this->getUrlByRoute("manage_projects"));
		}

		return $want;
	}

	/**
	 * @inheritdoc
	 */
	protected function getPageTitle(): string {
		return \Translations::t("Редактирование проекта");
	}

	/**
	 * @inheritdoc
	 */
	protected function isNew(): bool {
		return false;
	}

}