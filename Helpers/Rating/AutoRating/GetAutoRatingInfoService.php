<?php declare(strict_types=1);

namespace Helpers\Rating\AutoRating;

use RatingManager;
use Track\Type;
use TrackManager;

/**
 * Class GetAutoRatingInfoService
 * @package Helpers\Rating\AutoRating
 */
final class GetAutoRatingInfoService {
	/**
	 * @param int $orderId
	 * @return AutoRatingInfo
	 */
	public function byOrderId(int $orderId): AutoRatingInfo {
		return $this->byTrackInfo(
			TrackManager::getLastTrackInfo($orderId)
		);
	}

	/**
	 * @param array $trackInfo
	 * @return AutoRatingInfo
	 */
	public function byTrackInfo(array $trackInfo): AutoRatingInfo {
		$this->validateTrackInfo($trackInfo);

		return new AutoRatingInfo(
			$this->getAutoMode($trackInfo),
			$this->getMessage($trackInfo)
		);
	}

	/**
	 * @param array $trackInfo
	 * @return string|null
	 */
	private function getAutoMode(array $trackInfo) {
		$type = $trackInfo["type"];
		$reasonType = $trackInfo["reason_type"];
		$isInWork = $trackInfo["in_work"];
		$isLate = $trackInfo["late"];

		if ($type === Type::CRON_INPROGRESS_INWORK_CANCEL) {
			return RatingManager::AUTO_MODE_INWORK_TIME_OVER;
		}

		if (
			$type === Type::PAYER_INPROGRESS_CANCEL
			&&
			$reasonType === TrackManager::REASON_TYPE_PAYER_TIME_OVER
		) {
			return RatingManager::AUTO_MODE_TIME_OVER;
		}

		if ($reasonType === TrackManager::REASON_PAYER_WORKER_CANNOT_EXECUTE_CORRECT) {
			if (in_array($type, [
				Type::WORKER_INPROGRESS_CANCEL_CONFIRM,
				Type::CRON_PAYER_INPROGRESS_CANCEL
			], true)) {
				return RatingManager::AUTO_MODE_INCORRECT_EXECUTE;
			}

			if (
				($isInWork || $isLate)
				&&
				$type === Type::PAYER_INPROGRESS_CANCEL
			) {
				return RatingManager::AUTO_MODE_INCORRECT_EXECUTE;
			}
		}

		if ($type === Type::ADMIN_ARBITRAGE_CANCEL) {
			return RatingManager::AUTO_MODE_ARBITRAGE_PAYER;
		}

		return null;
	}

	/**
	 * @param array $trackInfo
	 */
	private function validateTrackInfo(array $trackInfo) {
		if (
			!isset($trackInfo["type"])
			||
			!array_key_exists("reason_type", $trackInfo)
			||
			!array_key_exists("in_work", $trackInfo)
			||
			!array_key_exists("late", $trackInfo)
			||
			!array_key_exists("message", $trackInfo)
		) {
			throw new \RuntimeException("Invalid trackInfo");
		}
	}

	/**
	 * @param array $trackInfo
	 * @return string|null
	 */
	private function getMessage(array $trackInfo) {
		if ($trackInfo["type"] === Type::ADMIN_ARBITRAGE_CANCEL) {
			return ""; // Сообщение администратора не нужно копировать в отзыв
		}

		return $trackInfo["message"];
	}
}