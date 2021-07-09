<?php


namespace Track\Factory;


use Core\Traits\AuthTrait;
use Core\Traits\SingletonTrait;
use Model\Order;
use Track\View\EmptyView;
use Track\View\IView;
use Track\View\Review\ReviewView;

/**
 * Фабрика для отображения отзывов
 *
 * Class ReviewToTrackViewFactory
 * @package Track\Factory
 */
class ReviewToTrackViewFactory implements IReviewToTrackViewFactory {

	use AuthTrait, SingletonTrait;

	/**
	 * Отзыв не доступен
	 *
	 * @param Order $order заказ
	 * @return bool результат
	 */
	private function isReviewIsNotEnabled(Order $order):bool {
		return is_null($order->review) ||
			($order->review->auto_mode && $order->isPayer($this->getUserId()));
	}

	/**
	 * @inheritdoc
	 */
	public function getView(Order $order): IView {
		if ($this->isReviewIsNotEnabled($order)) {
			return new EmptyView($order->tracks->first());
		}
		//Исполнитель читает отзыв
		if ($order->review->unread && $order->isWorker($this->getUserId())) {
			$order->review->unread = false;
			$order->review->save();
		}
		//Покупатель читает ответ на отзыв
		if (!$order->review->unread && $order->review->answer->unread && $order->isPayer($this->getUserId())) {
			$order->review->answer->unread = false;
			$order->review->answer->save();
		}
		return new ReviewView($order->tracks->first());
	}
}