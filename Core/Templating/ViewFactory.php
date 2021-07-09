<?php

namespace Core\Templating;

use Symfony\Component\Templating\DelegatingEngine;
use Core\Templating\SmartyTemplating;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class ViewFactory {

	protected $_view;
	protected $_parameters;

	public function make($view, $parameters) {
		$this->_view = $view;
		$this->_parameters = $parameters;
		return $this;
	}

	public function render() {
		$filesystemLoader = new FilesystemLoader(__DIR__ . "/../../public_html/themes/%name%");
		$php = new PhpEngine(new TemplateNameParser(), $filesystemLoader);		
		$smarty = new SmartyTemplating();
		$templating = new DelegatingEngine([$php, $smarty]);
		return $templating->render($this->_view, $this->_parameters);
	}

}
