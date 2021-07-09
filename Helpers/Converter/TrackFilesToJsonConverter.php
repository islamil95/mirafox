<?php


namespace Converter;


use Illuminate\Database\Eloquent\Collection;
use Model\File;

class TrackFilesToJsonConverter implements \JsonSerializable {

	/**
	 * @var Collection|File[] $files
	 */
	private $files;

	/**
	 * TrackFilesToJsonConverter constructor.
	 * @param Collection|File[] $files
	 */
	public function __construct($files) {
		$this->files = $files;
	}

	public function jsonSerialize() {
		$file2Json = [];

		return $file2Json;
	}
}