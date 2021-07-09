<?php

namespace Session;

abstract class AbstractSessionProvider implements ISessionProvider{
	
	public function __construct() {
		$this->init();
	}

	protected abstract function init();
}
