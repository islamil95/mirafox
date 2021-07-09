<?php


namespace Controllers\Want\Payer\Handler;


use Core\DB\DB;
use Model\Want;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка запроса на удаление запроса на услугу
 *
 * Class DeleteWantHandlerController
 * @package Controllers\Want\Payer\Handler
 */
class DeleteWantHandlerController extends AbstractActionHandlerController {

	/**
	 * Обработка удаление запроса на услуги
	 *
	 * @param Request $request HTTP запрос
	 * @return JsonResponse
	 */
	protected function processAction(Request $request): JsonResponse {
		$wantId = $this->getWantId($request);
		$want = Want::find($wantId);
		$wantStatus = \WantManager::STATUS_DELETE;

		DB::table(\WantManager::TABLE_NAME)
			->where(\WantManager::F_ID, $wantId)
			->where(\WantManager::F_USER_ID, $this->getUserId())
			->update([
				\WantManager::F_STATUS => $wantStatus,
			]);

		// зафиксируем изменение в WantLog
		$wantLog = new \Model\WantLog();
		$wantLog->user_id = $want->user_id;
		$wantLog->want_id = $wantId;
		$wantLog->status = $wantStatus;
		$wantLog->save();

		return new JsonResponse([
			"result" => "success",
			"redirectUrl" => $this->getAbsoluteUrlByRoute('manage_projects')
		]);
	}
}