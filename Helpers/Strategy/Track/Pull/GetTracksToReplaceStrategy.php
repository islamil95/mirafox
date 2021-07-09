<?php


namespace Strategy\Track\Pull;


use Model\Track;
use Track\Type;
use TrackManager;

class GetTracksToReplaceStrategy extends AbstractPullTrackStrategy {

	/**
	 * @inheritdoc
	 * @return Track[]
	 */
	public function get() {
		$lastTracks = $this->getLastTracks();
		$tracksForReplace = [];

		foreach ($lastTracks as $track) {
			/**
			 * @var Track $track
			 */
			switch ($track->type) {
				case Type::EXTRA:
					if (($track->isNotNew() && $track->isNotDone()) || $this->isForceReplace()) {
						$tracksForReplace[] = $track;
					}
					break;

				case Type::WORKER_INPROGRESS_CANCEL_CONFIRM:
				case Type::PAYER_INPROGRESS_CANCEL_CONFIRM:
				case Type::WORKER_INPROGRESS_CANCEL_REQUEST:
				case Type::PAYER_INPROGRESS_CANCEL_REQUEST:
				case Type::WORKER_INPROGRESS_CANCEL_REJECT:
				case Type::PAYER_INPROGRESS_CANCEL_REJECT:
					if ($track->MID == $this->lastTrackId) {
						$tracksForReplace[] = $track;
					}
					break;

				case Type::WORKER_INPROGRESS_CANCEL_DELETE:
					$trackCancelRequest = TrackManager::getClosedTrackByType($track->OID, Type::WORKER_INPROGRESS_CANCEL_REQUEST);
					if ($trackCancelRequest) {
						$tracksForReplace[] = $trackCancelRequest;
					}
					break;

				case Type::PAYER_INPROGRESS_CANCEL_DELETE:
					$trackCancelRequest = TrackManager::getClosedTrackByType($track->OID, Type::PAYER_INPROGRESS_CANCEL_REQUEST);
					if ($trackCancelRequest) {
						$tracksForReplace[] = $trackCancelRequest;
					}
					break;

				case Type::PAYER_UNPAID_INPROGRESS:
					$trackStageUnpaid = TrackManager::getNewTrackByType($track->OID, Type::STAGE_UNPAID);
					if ($trackStageUnpaid) {
						$tracksForReplace[] = $trackStageUnpaid;
					}
					break;

				default:
					break;
			}
		}
		return $tracksForReplace;
	}
}