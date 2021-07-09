<?php

namespace Smarty;

/**
 * Description of SmartyBridge
 *
 * @author jsosnovsky
 */
final class SmartyBridge {

	protected static $_instance;

	/**
	 * Получить главный объект smarty
	 * @return \Smarty
	 */
	public static function getMainInstance() {
		if (empty(self::$_instance)) {
			self::$_instance = self::getInstance();
		}
		return self::$_instance;
	}

	/**
	 * 
	 * @return \Smarty;
	 */
	public static function getInstance() {
		$compileDirectory = \App::config('basedir') . "/temporary";
		$allTemplates = \App::config('basedir') . "/themes";
		$mailTemplates = \App::config('basedir') . "/include/mail";
		$templateDirectory = [
			$allTemplates,
			$mailTemplates,
		];
		$instance = new \Smarty();
		$instance->setCompileDir($compileDirectory);
		$instance->setTemplateDir($templateDirectory);
		return $instance;
	}

}
