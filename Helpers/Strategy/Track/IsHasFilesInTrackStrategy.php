<?php


namespace Strategy\Track;


use Illuminate\Database\Eloquent\Collection;
use Model\Track;

/**
 * Есть ли в треках файлы
 *
 * Class IsHasFilesInTrackStrategy
 * @package Strategy\Track
 */
class IsHasFilesInTrackStrategy {

	/**
	 * @var Collection|Track[]
	 */
	private $tracks;

	/**
	 * IsHasFilesInTrackStrategy constructor.
	 * @param Collection|Track[] $tracks
	 */
	public function __construct($tracks) {
		$this->tracks = $tracks;
	}

	/**
	 * Результат
	 *
	 * @return bool
	 */
	public function get() {

		foreach ($this->tracks as $track) {
			if ($track->files->isNotEmpty()) {
				return true;
			}
		}
		return false;
	}

}