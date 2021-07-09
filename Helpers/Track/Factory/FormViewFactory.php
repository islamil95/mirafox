<?php


namespace Track\Factory;


use Core\Traits\SingletonTrait;
use Model\Order;
use Model\Track;
use Track\View\Form\FormView;
use Track\View\Form\IFormView;

/**
 * Фабрика форм
 *
 * Class FormViewFactory
 * @package Track\Factory
 */
class FormViewFactory implements IFormViewFactory {

	use SingletonTrait;

	/**
	 * @inheritdoc
	 */
	public function getView(Order $order): IFormView {
		return new FormView($order);
	}
}