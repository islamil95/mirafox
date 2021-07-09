<?php

namespace Controllers;

use \App;

class PageController {

	/**
	 * Экземпляр Smarty
	 * @var \Smarty 
	 */
	protected $smarty;

	/**
	 * Инизиацлизация
	 */
	public function __construct() {
		$this->smarty = \Smarty\SmartyBridge::getMainInstance();
	}

	/**
	 * Вернуть ответ для ajax обращения
	 * @param string $text Строка для вывода
	 */
	public function ajax($text) {
		echo $text;
		App::end();
	}

	/**
	 * Вернуть ответ для ajax обращения в json формате
	 * @param string $value Строка для вывода
	 */
	public function ajaxJson($value) {
		echo json_encode($value);
		App::end();
	}

	/**
	 * Вернуть ajax ответ (success => true, data => JSON(<data>)) в JSON
	 * @param string|array $data Строка или массив который будет передан в data.
	 */
	public function success($data = null) {
		$result = array('success' => true);
		if (!is_null($data)) {
			$result['data'] = $data;
		}
		echo json_encode($result);
		App::end();
	}

	/**
	 * Вернуть ajax ответ (success => false, data => JSON(<data>)) в JSON
	 * @param string|array $data Строка или массив который будет передан в data.
	 */
	public function failure($data = null) {
		$result = array('success' => false);
		if (!is_null($data)) {
			$result['data'] = $data;
		}
		echo json_encode($result);
		App::end();
	}

	/**
	 * Вывести шаблон
	 * @param string $template Путь до шаблона smarty
	 */
	public function renderPartial($template) {
		$this->smarty->display($template);
	}

	/**
	 * Вывести шаблон с header и footer
	 * @param string $template Путь до шаблона smarty
	 */
	public function render($template) {
		$this->renderPartial('header.tpl');
		$this->renderPartial($template);
		$this->renderPartial('footer.tpl');
	}

	/**
	 * "Умный" вывод шаблона. При ajax обращении только шаблон. иначе с шапкой и футером
	 * @param string $template Путь до шаблона smarty
	 */
	public function smartRender($template) {
		if (\Helper::isAjaxRequest()) {
			$this->renderPartial($template);
		} else {
			$this->render($template);
		}
	}

	/**
	 * Задать переменную для smarty
	 * @param string $name Имя переменной
	 * @param mixed $value Значение переменной
	 */
	public function assign($name, $value) {
		$this->smarty->assign($name, $value);
	}

	/**
	 * Выполнение редиректа
	 * @param string $page страница, куда будет выполнен редирект. ДОЛЖНА начинаться с символа "/"
	 */
	public static function redirect($page) {
		redirect($page);
		App::end();
	}

}
