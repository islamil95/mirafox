<?php


namespace Strategy\Track\Pull;


use Illuminate\Database\Eloquent\Collection;
use Model\Track;

class GetLastTrackStrategy extends AbstractPullTrackStrategy {

	/**
	 * @inheritdoc
	 * @return Collection|Track[]
	 */
	public function get() {
		$lastTracks = $this->order->tracks
			->filter(function ($track, $key) {
				/**
				 * @var Track $track
				 */
				if ($this->includeLastTrack) {
					return $track->MID >= $this->lastTrackId;
				}
				return $track->MID > $this->lastTrackId;
			})
			->sortBy(Track::FIELD_ID);
		return $lastTracks;
	}
}