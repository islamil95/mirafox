<?php


namespace Core\Traits;


trait ConfigurationTrait {

	/**
	 * @param $param
	 * @return mixed
	 */
	protected function config($param) {
		return \App::config($param);
	}

	/**
	 * @param string $param
	 * @return int
	 */
	protected function getInt($param) : int {
		return (int) $this->config($param);
	}

	/**
	 * @param string $param
	 * @return bool
	 */
	protected function getBool($param) : bool {
		return (bool) $this->config($param);
	}

	/**
	 * @return bool доступен ли редис
	 */
	protected function isRedisEnable() : bool {
		return $this->getBool("redis.enable");
	}

	/**
	 * @param bool $convertToMb преобразовать в целое число мегабайт
	 * @return int максимальный размер прикрепляемого файла
	 */
	protected function getMaxFileSize(bool $convertToMb = false) : int {
		$maxFileSize = $this->getInt("files.max_size");
		if ($convertToMb) {
			return round($maxFileSize / 1048576);
		} else {
			return $maxFileSize;
		}
	}

	/**
	 * @return int максимальное количество прикрепляемых файлов
	 */
	protected function getMaxFileCount() : int {
		return $this->getInt("files.max_count");
	}
}