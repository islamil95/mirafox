<?php declare(strict_types=1);

namespace Order\Stages;

use Core\DB\DB;
use Model\Notification\Notification;
use Model\OrderStages\OrderStage;
use NotityManager;

final class OrderStageObserver {
	/**
	 * @param OrderStage $orderStage
	 */
	public function deleted(OrderStage $orderStage): void {
		$getNotificationsQuery = DB::table(Notification::TABLE_NAME)
			->where(Notification::F_STAGE_ID, $orderStage->getKey());

		$notifications = $getNotificationsQuery->get();

		$userIds = $notifications
			->unique(Notification::F_USERID)
			->map(function($notification) {
				return (int)$notification->USERID;
			});

		// Удаляем нотификации, связанные с удаленным этапом
		$getNotificationsQuery->delete();

		foreach ($userIds as $userId) {
			// Пересчитываем количество входящих
			NotityManager::recalcUserUnreadCount($userId);
		}
	}
}
