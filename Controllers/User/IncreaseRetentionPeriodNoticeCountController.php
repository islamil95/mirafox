<?php

namespace Controllers\User;

use \Controllers\BaseController;
use \Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;

/**
 * #5140 Увеличение счетчика показов уведомлений о сроке хранения файлов
 *
 * Если пользователь отметил галочку "Больше не показывать" или после
 * увеличения счетчика его значение стало больше или равно максимальному,
 * то в БД сохраняется значение -1.
 *
 * Class IncreaseRetentionPeriodNoticeCountController
 * @package Controllers\User
 */
class IncreaseRetentionPeriodNoticeCountController extends BaseController {

	use AuthTrait;

	public function __invoke(Request $request) {
		$user = $this->getUser();

		if (!$user) {
			return $this->failure();
		}

		if ($request->query->get("stop") === "true") {
			$count = -1;
		} else {
			$count = \UserManager::getRetentionPeriodNoticeCount();
			$count++;
			if ($count >= \FileManager::MAX_RETENTION_PERIOD_NOTICE_COUNT) {
				$count = -1;
			}
		}

		$sql = "UPDATE user_data
				SET file_retention_period_notice_count = :count
				WHERE user_id = :userId";
		\App::pdo()->execute($sql, ["userId" => $user->id, "count" => $count]);

		return $this->success(["count" => $count]);
	}

}
