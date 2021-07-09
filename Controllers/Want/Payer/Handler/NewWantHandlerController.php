<?php

namespace Controllers\Want\Payer\Handler;

use Contest\Contests\Contest2019WantManager;
use Core\Exception\JsonValidationException;
use Core\Response\BaseJsonResponse;
use Model\Want;
use Model\WantLog;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка создания нового запроса на услуги
 *
 * Class NewWantHandlerController
 * @package Controllers\Want
 */
class NewWantHandlerController extends AbstractCreateUpdateWantHandlerController {

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
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function getRedirectionUrl(Request $request): string {
		$urlParams = [];
		if ($this->isUserNotAuthenticated()) {
			$userEmail = $request->request->get("email");
			// Проверка выполнена выше
			$response = \UserManager::simpleSignUp($userEmail, \UserManager::TYPE_PAYER);
			if (!$response["success"] && $response["message"]) {
				throw new JsonValidationException(["email"=> [$response["message"]]]);
			}
			$this->refreshCredentials();
			$urlParams["success_registration"] = 1;
		}
		return $this->getUrlByRoute("manage_projects", $urlParams);
	}

	/**
	 * Создание запроса
	 *
	 * @param Request $request
	 * @param int $userId
	 * @return bool
	 */
	protected function processRequest(Request $request, $userId = null): bool {
		$userId = is_null($userId) ? $this->getUserId() : $userId;
		$want = new Want();
		$want->user_id = $userId;
		$want->desc = $this->getWantDescription($request);
		$want->category_id = 1;
		$want->name = $this->getWantTitle($request);
		$want->status = $this->getWantStatus();
		$want->lang = \Translations::getLang();
		$want->date_create = \Helper::now();
		$want->price_limit = $this->getPriceLimit($request, $want->lang);

		if ($this->isWantConfirm()) {
			$want->date_confirm = date("Y-m-d H:i:s");
		}
		$want->save();
		$wantId = $want->id;
		if (!$wantId) {
			return false;
		}

		// зафиксируем изменение в WantLog
		$wantLog = new WantLog();
		$wantLog->user_id = $want->user_id;
		$wantLog->want_id = $want->id;
		$wantLog->status = $want->status;
		if ($want->status == \WantManager::STATUS_NEW) {
			$wantLog->moder_type = WantLog::MODER_TYPE_STAY_ON_PREMODER;
		} elseif ($want->status == \WantManager::STATUS_ACTIVE && $want->need_postmoder == 1) {
			$wantLog->moder_type = WantLog::MODER_TYPE_STAY_ON_AUTOMODER;
		}
		$wantLog->save();

		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function getMaxUploadedFiles(Request $request) {
		return \App::config("files.max_count");
	}

	/**
	 * Добавление запроса
	 *
	 * @param Request $request
	 * @return AbstractCreateUpdateWantHandlerController|NewWantHandlerController|BaseJsonResponse
	 */
	public function __invoke(Request $request) {
		if (\App::isMirror() ) {
			$this->request = $request;
			return $this->mirrorHandle($request);
		}
		return parent::__invoke($request);
	}

	/**
	 * Добавление запроса на зеркале
	 *
	 * @param Request $request
	 * @return NewWantHandlerController|BaseJsonResponse
	 * @throws \PHLAK\Config\Exceptions\InvalidContextException
	 */
	private function mirrorHandle(Request $request) {
		$lang = \Translations::getLang();
		$minPriceLimit = \CategoryManager::getCategoryBasePrice($this->getCategoryId($request), $lang);
		$validation = $this->validate($request, $this->getValidationRules(null, $lang, $minPriceLimit), $this->getValidationMessages($lang, $minPriceLimit));
		if ($validation->fails()) {
			return (new BaseJsonResponse())
				->setStatus(false)
				->setErrors($validation->errors());
		}
		$userEmail = $request->request->get("email");
		$result = \Usermanager::simpleSignUp($userEmail, \UserManager::TYPE_PAYER);
		if (!empty($result["user_id"])) {
			$token = \App::genMirrorAuthData($result["user_id"], $_SERVER["HTTP_REFERER"],["addTime" => $result["addTime"]]);
			$redirect = $this->getUrlByRoute("manage_projects", ["success_registration" => 1]);
			$tokenData = [
				"user_id"       => $result["user_id"],
				"redirect"      => $this->getUrlByRoute("manage_projects", ["success_registration" => 1]),
				"pushGtmSource" => 1,
				"addTime"       => $result["addTime"]
			];
			if (!$this->processRequest($request, $result["user_id"])) {
				return $this->unHandleError();
			}
			return (new BaseJsonResponse())
				->setRedirectUrl(\App::config("originurl") . "$redirect&mirror=" . $token);
		} else {
			return (new BaseJsonResponse())
				->setStatus(false)
				->setMessage($result["message"]);
		}
	}

}