<?php

use Model\OfferEdit;

class OfferEditManager {

	/**
	 * За какой период храним данные в таблице
	 */
	const STORAGE_PERIOD = \Helper::ONE_MONTH;

	/**
	 * Добавить/Изменить время изменения предложения
	 *
	 * @param int $offerId
	 * @param int $time
	 */
	public static function updateLastEditTime(int $offerId, int $time = null) {
		if (!$time) {
			$time = \Helper::now();
		}

		OfferEdit::updateOrInsert([
			OfferEdit::FIELD_OFFER_ID => $offerId
		], [
			OfferEdit::FIELD_LAST_EDIT_TIME => $time
		]);
	}

	/**
	 * Удаляем устаревшие данные
	 */
	public static function cronRemoveExpiredData() {
		$expiredDate = \Helper::now(time() - self::STORAGE_PERIOD);
		OfferEdit::where(OfferEdit::FIELD_LAST_EDIT_TIME, "<", $expiredDate)->delete();
	}
}