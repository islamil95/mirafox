<?php


namespace Controllers\Want\Payer\Handler;

use Core\Exception\PageNotFoundException;
use Model\Want;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обработка запроса на перезапуск запроса на услугу
 *
 * Class RestartWantHandlerController
 * @package Controllers\Want\Payer\Handler
 */
class RestartWantHandlerController extends AbstractActionHandlerController {

	/**
	 * Обработка перезапуска запроса на услуги
	 *
	 * @param Request $request HTTP запрос
	 * @return JsonResponse
	 */
	protected function processAction(Request $request): JsonResponse {
		$id = $request->query->get("id");
		if (!$id) {
			throw new PageNotFoundException();
		}

		$want = Want::find($id);
		if ($want->user_id != $this->getUserId()) {
			throw new PageNotFoundException();
		}

		if (empty($want) || !in_array($want->status, [Want::STATUS_STOP, Want::STATUS_USER_STOP, Want::STATUS_ARCHIVED])) {
			throw new PageNotFoundException();
		}

		$error = "";
		if (\WantManager::checkTitleClone($this->getUserId(), $want->name)) {
			$error .= "<li>" . \Translations::t("У вас уже есть активный проект с таким же названием.") . "</li>";
		}
		if (\WantManager::checkDescClonePercent($this->getUserId(), $want->desc)) {
			$error .= "<li>" . \Translations::t("У вас уже есть активный проект с похожим описанием.") . "</li>";
		}
		if (empty($error)) {
			\WantManager::restart($want->id);
		} else {
			$this->addFlashError($error);
		}

		return $this->successResponse();
	}
}