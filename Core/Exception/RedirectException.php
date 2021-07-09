<?php


namespace Core\Exception;

/**
 * После этой ошибки будет произведен редирект
 *
 * Class RedirectException
 * @package Core\Exception
 */
class RedirectException extends \RuntimeException {

	private $redirectUrl = null;

	public function __construct($url = "", $message = "", $code = 0, \Throwable $prev = null) {
		parent::__construct($message, $code, $prev);

		if ($url) {
			$this->setRedirectUrl($url);
		}
	}

	public function setRedirectUrl($redirectUrl) {
		$this->redirectUrl = $redirectUrl;
		return $this;
	}

	public function getRedirectUrl() {
		return $this->redirectUrl;
	}
}