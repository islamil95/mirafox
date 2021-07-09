<?php


namespace Strategy\Track;


use Model\Order;
use Model\Track;
use Track\Factory\TrackViewFactory;

/**
 * Получить треки в виде массива с HTML
 *
 * Class GetTracksAsHtmlStrategy
 * @package Strategy\Track
 */
class GetTracksAsHtmlStrategy implements TrackStrategyInterface {

	private $tracks;

	/**
	 * @var \Model\Order|null
	 */
	private $order;

	/**
	 * GetTracksAsHtmlStrategy constructor.
	 * @param Track[] $tracks
	 * @param Order|null $order
	 */
	public function __construct(array $tracks, Order $order = null) {
		$this->tracks = $tracks;
		$this->order = $order;
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get() {
		$renderedTracks = [];
		try {
			foreach ($this->tracks as $track) {
				$renderedTracks[$track->MID] = TrackViewFactory::getInstance()->getView($track, $this->order)->render();
			}
		} catch(\Exception $e) {
			return [];
		}
		return $renderedTracks;
	}
}