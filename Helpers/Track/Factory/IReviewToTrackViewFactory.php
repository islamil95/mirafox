<?php


namespace Track\Factory;


use Model\Order;
use Track\View\IView;

/**
 * Получение отзывов для заказа
 *
 * Interface IReviewToTrackViewFactory
 * @package Track\Factory
 */
interface IReviewToTrackViewFactory {

	/**
	 * @param Order $order заказ
	 * @return IView отзыв для отображения
	 */
	public function getView(Order $order): IView;
}