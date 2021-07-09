<?php


namespace Controllers\Error;


use Controllers\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectController extends BaseController {

	private $redirectUrl;

	public function __construct($redirectUrl) {
		parent::__construct();
		$this->redirectUrl = $redirectUrl;
	}

	public function __invoke() {
		return new RedirectResponse($this->redirectUrl);
	}
}