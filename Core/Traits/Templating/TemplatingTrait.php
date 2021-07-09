<?php

namespace Core\Traits\Templating;

use Core\Templating\SmartyTemplating;
use Symfony\Component\Templating\DelegatingEngine;

trait TemplatingTrait {

	private $templating = null;

	private function templating() {
		if (is_null($this->templating)) {
			$this->templating = new DelegatingEngine([new SmartyTemplating()]);
		}
		return $this->templating;
	}
}