<?php


namespace Controllers\Error;


use Controllers\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonController extends BaseController {

	private $jsonData;

	public function __construct($jsonData = []) {
		parent::__construct();
		$this->jsonData = $jsonData;
	}

	public function __invoke() {
		return new JsonResponse($this->jsonData);
	}
}