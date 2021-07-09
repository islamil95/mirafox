<?php

namespace Controllers\Track\Handler\Draft;

use Model\File;
use Model\TrackDraft;
use Symfony\Component\HttpFoundation\Response;

/**
 * Создание\обновление черновика трека
 *
 * Class EditHandlerController
 * @package Controllers\Track\Handler\Draft
 */
class EditHandlerController extends AbstractTrackDraftHandlerController {
	/**
	 * Пустой пользовательский ввод
	 *
	 * @return bool
	 */
	private function isEmptyInput(): bool {
		return !($this->getMessage() || $this->isHaveAttachedFiles());
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		if ($this->isNotRealUser()) {
			return $this->getErrorResponse("Черновики недоступны для виртуальных пользователей");
		}

		if ($this->isNotValid()) {
			return $this->getErrorResponse("В данный заказ писать нельзя");
		}

		if ($this->isEmptyInput()) {
			return $this->getErrorResponse("Пустой пользовательский ввод");
		}

		if ($draft = $this->getDraft()) {
			// Если юзер прикрепил или ранее прикреплял файлы к черновику - обновляем
			$newFiles = \TrackManager::getAvailableFilesForTrack($draft->id);
			$oldFiles = $draft->files
				->pluck(File::FIELD_ID)
				->toArray();

			// Отвязываем от черновика то, что пользователь отвязал
			if ($unlinkFiles = array_diff($oldFiles, $newFiles)) {
				File::whereIn(File::FIELD_ID, $unlinkFiles)
					->update([File::FIELD_ENTITY_ID => null]);
			}

			if ($newFiles) {
				\TrackManager::attachUploadedFiles(
					$draft->id,
					["new" => $newFiles],
					File::ENTITY_TYPE_TRACK_DRAFT
				);
			}

			$draft->message = $this->getMessage();

			if ($draft->save()) {
				return $this->getSuccessResponse();
			} else {
				return $this->getErrorResponse("Ошибка при обновлении черновика");
			}
		} else {
			$draftId = TrackDraft::insertGetId([
				TrackDraft::FIELD_ORDER_ID => $this->getOrderId(),
				TrackDraft::FIELD_USER_ID => $this->getCurrentUserId(),
				TrackDraft::FIELD_MESSAGE => $this->getMessage(),
			]);

			if ($draftId) {
				\TrackManager::attachFilesToTrack($draftId, null, File::ENTITY_TYPE_TRACK_DRAFT);

				return $this->getSuccessResponse();
			} else {
				return $this->getErrorResponse("Ошибка при создании черновика");
			}
		}
	}
}