<?php


namespace Strategy\Track\Pull;

use Model\Order;
use Model\Track;
use Track\Type;

class GetTracksToAddStrategy extends AbstractPullTrackStrategy {

	/**
	 * @var bool
	 */
	private $isTracksToReplaceEmpty;

	public function __construct(Order $order, $trackType, $lastTrackId, $forceReplace, $isTracksToReplaceEmpty) {
		parent::__construct($order, $trackType, $lastTrackId, $forceReplace);
		$this->isTracksToReplaceEmpty = $isTracksToReplaceEmpty;
	}

	/**
	 * @inheritdoc
	 * @return Track[]
	 */
	public function get() {
		$lastTracks = $this->getLastTracks();
		$tracksForAdding = [];

		foreach ($lastTracks as $track) {
			switch ($track->type) {
				case Type::EXTRA:
					// Если новый-то, то конечно на добавление.
					if (($track->isNew() || $track->isDone()) && $this->isNotForceReplace()) {
						$tracksForAdding[$track->MID] = $track;
					}
					break;

				case Type::WORKER_INPROGRESS_CANCEL_CONFIRM:
				case Type::PAYER_INPROGRESS_CANCEL_CONFIRM:
				case Type::WORKER_INPROGRESS_CANCEL_REQUEST:
				case Type::PAYER_INPROGRESS_CANCEL_REQUEST:
				case Type::WORKER_INPROGRESS_CANCEL_REJECT:
				case Type::PAYER_INPROGRESS_CANCEL_REJECT:
					if ($track->MID != $this->lastTrackId) {
						$tracksForAdding[$track->MID] = $track;
					}
					break;

				default:
					$tracksForAdding[$track->MID] = $track;
			}
		}
		// Автоматом может придти, а оно нам не нужно добавлять последний трек ещё раз.
		if ($this->isTracksToReplaceEmpty && isset($tracksForAdding[$this->lastTrackId])) {
			unset($tracksForAdding[$this->lastTrackId]);
		}
		return array_values($tracksForAdding);
	}
}