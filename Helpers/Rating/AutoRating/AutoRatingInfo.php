<?php declare(strict_types=1);

namespace Helpers\Rating\AutoRating;

/**
 * Class AutoRatingInfo
 * @package Helpers\Rating\AutoRating
 */
final class AutoRatingInfo {
	/**
	 * @var string
	 */
	private $autoMode;

	/**
	 * @var string
	 */
	private $comment;

	/**
	 * @param string|null $autoMode
	 * @param string|null $comment
	 */
	public function __construct(string $autoMode = null, string $comment = null) {
		$this->autoMode = $autoMode;
		$this->comment = $comment;
	}

	/**
	 * @return string|null
	 */
	public function getAutoMode() {
		return $this->autoMode;
	}

	/**
	 * @return string|null
	 */
	public function getComment() {
		return $this->comment;
	}
}