<?php

namespace Order\Utils;

use \App;

trait CronActions {

	/**
	 * Отправить уведомление продавцам о том, что нужно взять заказы в работу, не берем те заказы которые на данный момент в статусе отмены
	 */
	public static function sendInworkNotification() {
		$sql = "SELECT
					o.OID as 'id',
					o.stime,
					p.PID,
					p.gtitle,
					o.date_inprogress
                FROM
					orders o
                JOIN
					posts p ON p.PID = o.PID
                LEFT JOIN
					track t ON t.MID = o.last_track_id
                WHERE
					o.status = 1
					AND o.in_work = 0
					AND o.date_inprogress IS NOT NULL
                    AND NOT EXISTS
					(
						SELECT 
							1 
						FROM 
							track 
						WHERE 
							OID = o.OID AND
							type IN ('worker_inprogress_cancel_request', 'payer_inprogress_cancel_request') AND
							status = 'new'
					)";

		$orders = App::pdo()->fetchAll($sql, [], \PDO::FETCH_OBJ);
		//получаем список ID заказов
		$orderIds = array_map(function($item) {
			return $item->id;
		}, $orders);

		//Получаем список задержек  у указанных ордеров
		$delay = \OrderManager::getMassOrdersPauseDuration($orderIds);
		if (!empty($orders)) {
			$countHoursFrom = time() - (App::config("kwork.need_worker_new_inwork_hours") + 1) * \Helper::ONE_HOUR;
			$countHoursTo = time() - App::config("kwork.need_worker_new_inwork_hours") * \Helper::ONE_HOUR;
			foreach ($orders as $order) {
				//Время создания заказа с учетом задержки
				$realTime = strtotime($order->date_inprogress) + $delay[$order->id];
				if ($realTime >= $countHoursFrom && $realTime < $countHoursTo) {
					$data = \OrderManager::getOrderData($order->id);
					$letter = new \Letter\Service\OneDayLeftToTakeKworkLetter($data->workerEmail, $data->workerLang);
					$letter->setKworkName($data->workerOrderName)
						->setOrderId($data->orderId)
						->setPayerName($data->payerLogin);
					\MailSender::send($letter);
					usleep(10000);
				}
			}
		}
	}

}
