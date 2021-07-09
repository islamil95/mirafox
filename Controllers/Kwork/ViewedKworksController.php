<?php

namespace Controllers\Kwork;

use Controllers\BaseController;
use Core\Exception\RedirectException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Просмотренные кворки
 *
 * Class ViewedKworksController
 * @package Controllers\Kwork
 */
class ViewedKworksController extends BaseController {

	/**
	 * Количество кворков для вывода
	 */
	const TOTAL_VIEWED_KWORKS = 50;

	public function __invoke() {
		if ($this->isUserNotAuthenticated()) {
			return new RedirectResponse("/");
		}

		$lastViewedKworkIds = \ViewManager::getViewsByUser($this->getUserId(),self::TOTAL_VIEWED_KWORKS);
		$kworks = \KworkManager::getListForCards($lastViewedKworkIds);
		$parameters = [
			"pagetitle" => \Translations::t("Просмотренные кворки"),
			"kworks" => $kworks,
		];
		return $this->render("kwork/viewed_kworks", $parameters);
	}
}