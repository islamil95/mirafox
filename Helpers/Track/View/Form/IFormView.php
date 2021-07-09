<?php


namespace Track\View\Form;


use Track\View\IView;

/**
 * Внешний вид для форм
 *
 * Interface IFormView
 * @package Track\View\Form
 */
interface IFormView extends IView {

	/**
	 * Получить хеш формы
	 *
	 * @return string хеш формы
	 */
	public function getFormMD5Hash(): string;
}