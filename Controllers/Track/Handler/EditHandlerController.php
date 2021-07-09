<?php


namespace Controllers\Track\Handler;

use Model\Track;
use Pull\PushManager;
use Strategy\Track\GetTrackRecipientIdStrategy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Редактирование сообщения
 *
 * Class EditHandlerController
 * @package Controllers\Track\Handler
 */
class EditHandlerController extends AbstractTrackHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		$track = Track::find($this->getTrackId());

		if (!$track) {
			return $this->failure("Трек не найден");
		}

		if ($track->isEditable($this->getUserId())) {
			$track->message = $this->getMessage();
			$track->date_update = date("Y-m-d H:i:s", time());
			$quoteId = $this->getQuoteId();
			if($quoteId && \TrackManager::checkQuoteTrack($track->type, $track->OID, $quoteId)) {
				$track->quote_id = $quoteId;
			} else {
				$track->quote_id = null;
			}
			$updatedTracksCount = $track->save();

			$messageFiles = $this->getRequest()->request->get("conversations-files-edit");
			if (isset($messageFiles["new"]) || isset($messageFiles["delete"])) {
				\TrackManager::attachUploadedFiles($this->getTrackId(), $messageFiles);
				$updatedTracksCount = 1;
			}
			if ($updatedTracksCount) {
				$getTrackRecipientId = new GetTrackRecipientIdStrategy($track);
				PushManager::sendTrackChanged(
					$getTrackRecipientId->get(),
					$this->getTrackId(), [
						"action" => \TrackManager::CHANGE_TEXT_MESSAGE,
						"text" => $this->getMessage(),
						"from_user_id" => $this->getUserId(),
						"order_id" => $this->getOrderId(),
					]
				);
			}
		}

		return $this->getResponse();
	}

}