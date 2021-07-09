<?php

namespace Controllers\Track\Handler;

use Core\Exception\RedirectException;
use Core\Response\BaseJsonResponse;
use Model\File;
use Model\Track;
use Model\TrackRemove;
use Pull\PushManager;
use Strategy\Track\GetTrackRecipientIdStrategy;
use Symfony\Component\HttpFoundation\Response;
use Track\Factory\TrackViewFactory;
use Track\Type;

/**
 * Удаление трека
 *
 * Class RemoveHandlerController
 * @package Controllers\Track\Handler
 */
class RemoveHandlerController extends AbstractTrackHandlerController {

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * Удаление трека вместе с файлами
	 *
	 * @param int $trackId идентификатор трека
	 * @return bool результат
	 * @throws \Exception
	 */
	private function removeTrackWithFiles(int $trackId): bool {
		$track = Track::where(Track::FIELD_ORDER_ID, $this->getOrderId())->find($trackId);

		$session = \Session\SessionContainer::getSession();
		if ($track && ($track->isRemovable($this->getUserId()) || ($track->isRemovableByVirtual() && \AdminManager::canDeleteTrack()))) {
			$params = [
				"action" => "remove",
				"from_user_id" => $this->getUserId(),
				"order_id" => $this->getOrderId(),
			];

			if ($track->files->count()) {
				foreach ($track->files as $file) {
					if (\UserManager::isVirtual()) {
						$file->status = File::STATUS_DELETED;
						$file->save();
					} else {
						$file->safeDelete();
					}
				}
			}
			if ($track->type == Type::TEXT_FIRST) {
				$track->order->data_provided = false;
				$track->order->save();
			}
			$track->delete();

			if (\UserManager::isVirtual()) {
				$trackRemove = new TrackRemove();
				$trackRemove->track_id = $trackId;
				$trackRemove->order_id = $this->getOrderId();
				$trackRemove->admin_id = $session->get("ADMINID");
				$trackRemove->save();
			}

			$getTrackRecipientIdStrategy = new GetTrackRecipientIdStrategy($track);
			$recipientId = $getTrackRecipientIdStrategy->get();
			$params["action"] = "remove";
			PushManager::sendTrackChanged($recipientId, $trackId, $params);
			\TrackManager::calcUnreadTracksCount($track->OID);

			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	protected function processAction(): Response {
		$itemId = $this->getRequest()->request->getInt("itemId", 0);
		if ($itemId === 0) {
			throw (new RedirectException())->setRedirectUrl("/");
		}

		$response = new BaseJsonResponse();
		$responseStatus = $this->removeTrackWithFiles($itemId);
		$response->setStatus($responseStatus);

		$tracksToReplace = [];
		// Для продавца проверим есть ли открытый запрос на отмену от покупателя
		if ($this->getUserId() == $this->getOrder()->worker_id) {
			$payerInprogressCancelTrack = Track::where(Track::FIELD_ORDER_ID, $this->getOrder()->OID)
				->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
				->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
				->first();
			if ($payerInprogressCancelTrack) {
				$trackViewFactory = TrackViewFactory::getInstance();
				$tracksToReplace[$payerInprogressCancelTrack->MID] = $trackViewFactory->getView($payerInprogressCancelTrack)->render();
			}
		}

		$response->setResponseData([
			"tracksToReplace" => $tracksToReplace,
			"message" => !$responseStatus ? (!\AdminManager::canDeleteTrack() ? "Превышен месячный лемит удаления треков модераторами!" : "Трек не удален!") : NULL,
		]);

		return $response;
	}
}