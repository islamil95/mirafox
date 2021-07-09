<?php

namespace Core\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Базовый JSON ответ
 *
 * Class BaseJsonResponse
 * @package Core\Response
 */
class BaseJsonResponse extends JsonResponse {

	protected $responseData;

	public function __construct(int $status = 200, array $headers = array(), bool $json = false) {
		parent::__construct(["success" => true], $status, $headers, $json);
		$this->responseData = ["success" => true];
	}

	/**
	 * Добавить сообщение в ответ
	 *
	 * @param string $message сообщение
	 * @return $this
	 */
	public function setMessage(string $message) {
		$this->responseData["message"] = $message;
		return $this->setData($this->responseData);
	}

	/**
	 * Добавить статус в ответ
	 *
	 * @param bool $status статус
	 * @return $this
	 */
	public function setStatus(bool $status) {
		$this->responseData["success"] = $status;
		return $this->setData($this->responseData);
	}

	/**
	 * Добавить произвольные данные в ответ
	 *
	 * @param mixed $data
	 * @return $this
	 */
	public function setResponseData($data) {
		$this->responseData["data"] = $data;
		return $this->setData($this->responseData);
	}

	public function setErrors($errors) {
		$this->responseData["errors"] = $errors;
		$this->responseData["success"] = false;
		return $this->setData($this->responseData);
	}

	public function setRedirectUrl($redirectUrl) {
		$this->responseData["redirect"] = $redirectUrl;
		return $this->setData($this->responseData);
	}

	public function setWantStatus($status) {
		$this->responseData["status"] = $status;
		return $this->setData($this->responseData);
	}
}