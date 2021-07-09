<?php


namespace Core\Templating;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Шаблоны на Smarty
 *
 * Class SmartyTemplating
 * @package Core\Templating
 */
class SmartyTemplating implements EngineInterface {

	protected $smartyInstance;

	public function __construct() {
		global $smarty;
		$this->smartyInstance = $smarty;
	}

	/**
	 * @inheritdoc
	 * @throws \SmartyException
	 */
	public function render($name, array $parameters = []) {
		$this->smartyInstance->assign($parameters);
		return $this->smartyInstance->fetch($this->getTemplateName($name));
	}

	/**
	 * @inheritdoc
	 */
	public function exists($name) {
		return $this->smartyInstance->templateExists($name);
	}

	/**
	 * @inheritdoc
	 */
	public function supports($name) {
		return true;
	}

	private function addFileExtension(string $templateName) {
		return $templateName . ".tpl";
	}

	private function getTemplateName($name) {
		if (is_string($name)) {
			if ($this->exists($name)) {
				return $name;
			}
			if ($this->exists($this->addFileExtension($name))) {
				return $this->addFileExtension($name);
			}
		}
		if ($name instanceof TemplateReferenceInterface) {
			if ($this->exists($name->getPath())) {
				return $name->getPath();
			}
			if ($this->exists($this->addFileExtension($name->getPath()))) {
				return $this->addFileExtension($name->getPath());
			}
			if ($this->exists($name->getLogicalName())) {
				return $name->getLogicalName();
			}
			if ($this->exists($this->addFileExtension($name->getLogicalName()))) {
				return $this->addFileExtension($name->getLogicalName());
			}
		}
		throw new \RuntimeException("Данный шаблон не существует");
	}
}