<?php


namespace Controllers\Want\Payer\Handler;


use Core\DB\DB;
use Model\Want;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обрабобтка запроса на удаление запроса на услугу
 *
 * Class StopWantHandlerController
 * @package Controllers\Want\Payer\Handler
 */
class StopWantHandlerController extends AbstractActionHandlerController {

	/**
	 * Обработка остановки запроса на услуги
	 *
	 * @param Request $request HTTP запрос
	 * @return JsonResponse
	 */
	protected function processAction(Request $request): JsonResponse {
		$wantId = $this->getWantId($request);
		$want = Want::find($wantId);

		if (
			!$want
			|| !$this->getUserId() === $want->user_id
			|| $want->isArchive()
			|| $want->isDeleted()
		) {
			return $this->failure(\Translations::t("Доступ запрещен."));
		}

		$want->status = Want::STATUS_USER_STOP;
		$want->save();

		// зафиксируем изменение в WantLog
		$wantLog = new \Model\WantLog();
		$wantLog->user_id = $want->user_id;
		$wantLog->want_id = $wantId;
		$wantLog->status = Want::STATUS_USER_STOP;
		$wantLog->save();

		\WantManager::clearViews($wantId);

		return $this->successResponse();
	}
}