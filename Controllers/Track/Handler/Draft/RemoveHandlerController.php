<?php

namespace Controllers\Track\Handler\Draft;

use Symfony\Component\HttpFoundation\Response;

/**
 * Удаление черновика трека
 *
 * Class RemoveHandlerController
 * @package Controllers\Track\Handler\Draft
 */
class RemoveHandlerController extends AbstractTrackDraftHandlerController {
	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		if ($this->isNotRealUser()) {
			return $this->getErrorResponse("Черновики недоступны для виртуальных пользователей");
		}

		if ($draft = $this->getDraft()) {
			if (!$draft->removeWithFiles()) {
				return $this->getErrorResponse("Ошибка удаления черновика");
			}
		}

		return $this->getSuccessResponse();
	}
}