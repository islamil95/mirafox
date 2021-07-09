<?php


namespace Strategy\Track;


use Carbon\Carbon;
use Model\ArbitrageAssign;
use Model\Track;

/**
 * Получить доступные типы отзывов
 *
 * Class GetAvailableReviewTypeStrategy
 * @package Strategy\Track
 */
class GetAvailableReviewTypeStrategy extends AbstractTrackStrategy {

	/**
	 * Возвращает false, если по заказу нельзя оставить отзыв,
	 * RatingManager::CAN_ADD_REVIEW_BAD если по заказу можно оставить только отрицательный отзыв,
	 * RatingManager::CAN_ADD_REVIEW_ALL если по заказу можно оставить любой отзыв
	 * @return (false|RatingManager::CAN_ADD_REVIEW_BAD|RatingManager::CAN_ADD_REVIEW_ALL)
	 */
	public function get() {
		// Для продавца и заказа не завершенного и не отмененного
		if (!$this->order->isPayer($this->getUserId()) ||
			($this->order->isNotDone() && $this->order->isNotCancel())
		) {
			return false;
		}

		//Если заказ был отменен арбитражом в сторону покупателя, он может оставить отзыв (замена автоотзыва)
		if ($this->order->review->auto_mode == \RatingManager::AUTO_MODE_ARBITRAGE_PAYER) {
			return \RatingManager::CAN_ADD_REVIEW_ALL;
		}

		/**
		 * @var Track $track
		 */
		$track = $this->order->tracks
			->where(Track::FIELD_ID, $this->order->last_track_id)
			->first();

		// Если истек срок для оставления отзыва
		$isNotReviewCreateTime = new IsNotReviewCreateTimeStrategy($this->order);
		if ($isNotReviewCreateTime->get()) {
			// Отзыв оставить нельзя
			return false;
		}

		return \RatingManager::CAN_ADD_REVIEW_ALL;
	}
}