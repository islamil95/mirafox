<?php

namespace Session;

interface IStartStop {
	
	/**
	 * Cтарт сессии
	 */
	public function start():bool;
	
	/**
	 * Стоп сессии
	 */
	public function stop():bool;
}
