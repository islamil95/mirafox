<?php


namespace Track\Factory;


use Model\Order;
use Track\View\Form\IFormView;

/**
 * Фаблика получения форм
 *
 * Interface IFormViewFactory
 * @package Track\Factory
 */
interface IFormViewFactory {

	/**
	 * Получить форму
	 *
	 * @param Order $order заказ
	 * @return IFormView форма для отображения
	 */
	public function getView(Order $order): IFormView;
}