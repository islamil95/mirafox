<?php


namespace Strategy\Track;


/**
 * Проверяет, может ли пользователь оставить отзыв
 *
 * Class CanAddReviewStrategy
 * @package Strategy\Track
 */
class CanAddReviewStrategy extends AbstractTrackStrategy {

	/**
	 * Проверяет, может ли пользователь оставить отзыв
	 *
	 * @return bool|string
	 */
	public function get() {
		$availableReviewTypeStrategy = new GetAvailableReviewTypeStrategy($this->order);
		$availableReviewType = $availableReviewTypeStrategy->get();

		if($availableReviewType == false) {
			return false;
		}
		$allowAutoReasons = [
			\RatingManager::AUTO_MODE_ARBITRAGE_PAYER,
			\RatingManager::AUTO_MODE_TIME_OVER,
			\RatingManager::AUTO_MODE_INCORRECT_EXECUTE
		];
		//Если отзыв не был оставлен, либо был оставлен автоматический отзыв по арбитражу в сторону покупателя
		if(empty($this->order->review) || in_array($this->order->review->auto_mode, $allowAutoReasons)){
			return $availableReviewType;
		}

		return false;
	}
}