<?php
namespace Model\Track;

class TrackHistoryItem {

	private $date;
	private $shortDescription;

	public function __construct(string $date, string $shortDescription) {
		$this->date = $date;
		$this->shortDescription = $shortDescription;
	}

	/**
	 * Дата трека
	 * @return string
	 */
	public function getDate(): string {
		return $this->date;
	}

	/**
	 * Короткое описание
	 * @return string
	 */
	public function getShortDescription(): string {
		return $this->shortDescription;
	}
}
