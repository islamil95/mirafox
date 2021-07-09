<?php


namespace Strategy\Track;


use Carbon\Carbon;

/**
 * Проверка, не истекло ли время для написания отзыва
 *
 * Class IsNotReviewCreateTimeStrategy
 * @package Strategy\Track
 */
class IsNotReviewCreateTimeStrategy extends AbstractTrackStrategy {

	/**
	 * Проверка, не истекло ли время для написания отзыва по заказу
	 *
	 * @return boolean false, если отзыв еще можно оставить
	 */
	public function get() {
		if ($this->isVirtual()) {
			return false;
		}

		// Определить дату выполения/отмены заказа
		if ($this->order->isDone()) {
			$orderClosedDate = Carbon::parse($this->order->date_done);
		} else {
			$orderClosedDate = Carbon::parse($this->order->date_cancel);
		}
		// Если максимальное время на выполнение кворка < 30 дней
		// Если прошло больше месяца с момента принятия/отмены заказа
		$monthCountToWriteReview = \RatingManager::CREATE_REVIEW_PERIOD_MONTH_SHORT;
		// Если максимальное время на выполнение кворка >= 30 дней
		if ($this->order->kwork->kworkCategory->max_days >= 30) {
			// Если прошло более 2 месяцев с момента принятия/отмены заказа
			$monthCountToWriteReview = \RatingManager::CREATE_REVIEW_PERIOD_MONTH_LONG;
		}
		$orderClosedDate->addMonths($monthCountToWriteReview);
		// Отзыв оставить нельзя если дата закрытия заказа с учетом времени меньше текущей даты
		return $orderClosedDate->lessThan(Carbon::now());
	}
}