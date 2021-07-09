<?php


namespace Controllers\Want\Payer\Handler;

use Core\DB\DB;
use Core\Exception\PageNotFoundException;
use Model\File;
use Model\Want;
use Model\WantLog;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка формы редактирования запроса на услуги
 *
 * Class EditWantHandlerController
 * @package Controllers\Want
 */
class EditWantHandlerController extends AbstractCreateUpdateWantHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function deleteAttachedFiles(Request $request, int $wantId) {
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function beforeValidation(Request $request) {
		if ($this->isUserNotAuthenticated()) {
			throw new PageNotFoundException();
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function getRedirectionUrl(Request $request): string {
		return $this->getUrlByRoute("manage_projects");
	}

	/**
	 * @inheritdoc
	 */
	protected function processRequest(Request $request): bool {
		$want = Want::where(\WantManager::F_ID, $this->getWantId($request))
			->where(\WantManager::F_USER_ID, $this->getUserId())
			->whereIn(\WantManager::F_STATUS, [
				\WantManager::STATUS_NEW,
				\WantManager::STATUS_CANCEL,
				\WantManager::STATUS_ACTIVE,
				\WantManager::STATUS_STOP,
				Want::STATUS_USER_STOP,
			])->first();

		if (is_null($want) || $want->isArchive()) {
			return false;
		}

		$want->desc = $this->getWantDescription($request);
		$want->name = $this->getWantTitle($request);
		$want->price_limit = $this->getPriceLimit($request, $want->lang);
		$moderType = "";
		if ($want->status == \WantManager::STATUS_CANCEL) {
			$want->status = \WantManager::STATUS_NEW;
			$moderType = WantLog::MODER_TYPE_STAY_ON_REMODER;
		} elseif ($want->status != \WantManager::STATUS_NEW && $want->need_postmoder == 0) {
			$moderType = WantLog::MODER_TYPE_STAY_ON_POSTMODER;
		}
		$saved = $want->save();

		if ($moderType) {
			// зафиксируем изменение в WantLog если установлен тип модерации
			$wantLog = new WantLog();
			$wantLog->user_id = $want->user_id;
			$wantLog->want_id = $want->id;
			$wantLog->status = $want->status;
			$wantLog->moder_type = $moderType;
			$wantLog->save();
		}

		return $saved;
	}

	/**
	 * Количество файлов доступных для загрузки
	 *
	 * @param Request $request запрос
	 * @return int количество файлов для загрузки
	 */
	protected function getMaxUploadedFiles(Request $request) {
		// TODO: Implement getMaxUploadedFiles() method.
	}
}