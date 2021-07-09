<?php


namespace Core\Exception;


use GuzzleHttp\Exception\RequestException;

class ExternalApiException extends RequestException {

	/**
	 * @var string
	 */
	protected $apiMessage = "";

	/**
	 * @param string $message
	 */
	public function setApiMessage(string $message) {
		$this->apiMessage = $message;
	}

	/**
	 * @return string
	 */
	public function getApiMessage():string {
		return $this->apiMessage;
	}

}