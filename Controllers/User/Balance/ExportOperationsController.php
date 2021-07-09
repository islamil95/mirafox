<?php


namespace Controllers\User\Balance;


use Controllers\BaseController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;
use OperationManager;


/**
 * Экспорт операций пользователя с учетом фильтра
 *
 * Class ExportOperationsController
 * @package Controllers\Users\Balance
 */
class ExportOperationsController extends BaseController {

	use AuthTrait;

	public function __invoke(Request $request) {
		$user = $this->getUserModel();
		$filter = array();
		$filter["dateFrom"] = $request->query->get("date_from", false);
		$filter["dateTo"] = $request->query->get("to_date", false);
		$filter["typeOperation"] = explode(",", $request->query->get("type_operation"));
		$filter["kwork"] = $request->query->get("name_kwork", false);

		$tr = new \Translations();

		$operations = OperationManager::getUserOperations(0, 0, $filter);
		$toCSV = array();
		$toCSV[] = [$tr::t('Дата'), $tr::t('Описание'), $tr::t('Сумма'), $tr::t('Статус')];

		//Определяем тип операции
		foreach ($operations['operations'] as $o) {
			$descr = "";
			$date = \Helper::date($o['time'], "Y-m-d H:i:s");
			$o['gtitle'] = "'" . $o['gtitle'] . "'";
			switch ($o["type"]) {
				case "refill": {
					if ($o['bonusCode'] == "arbitr500") {
						$descr = $tr::t("Начисление лояльности по арбитражу");
					} elseif ($o['payment'] && $o['payment'] != OperationManager::FIELD_PAYMENT_ADMIN) {
						$descr = $tr::t("Пополнение с") . " " . insert_get_payment_name($o);
					} elseif ($o['bonus_id']) {
						$descr = $tr::t("Зачисление на бонусный счет по промокоду") . " " . $o['bonus_code'];
					} elseif ($o['contest_id']) {
						$descr = $tr::t("Подарок в конкурсе") . " " . $o['contest_name'];
					} elseif ($o["sub_type"] == \PaypalManager::TYPE_PAYPAL_REFILL) {
						$descr = $tr::t("Пополнение с PayPal или карты");
					} else {
						$descr = $tr::t("Пополнение");
					}
					break;
				}
				case "refill_referal": {
					$descr = $tr::t("Начисление по реферальной программе за пользователя");
					break;
				}
				case "refill_moder_kwork": {
					$descr = $tr::t("Пополнение за модерацию кворка") . " " . $o['gtitle'];
					break;
				}
				case "refill_moder_request": {
					$descr = $tr::t("Пополнение за модерацию проекта");
					break;
				}
				case "order_out": {
					if ($o['is_extra'] == 1) {
						$descr = $tr::t("Оплата продавцу") . " " . $o['worker_name'] . " " . $tr::t("за опции к заказу") . " " . $o['gtitle'];
					} elseif ($o['is_tips'] == 1) {
						$descr = $tr::t("Оплата бонуса для") . " " . $o["worker_name"] . " " . $tr::t("за заказ") . " " . $o['gtitle'];
					} else {
						$descr = $tr::t("Оплата продавцу") . " " . $o['worker_name'] . " " . $tr::t("за заказ") . " " . $o['gtitle'];
					}
					break;
				}
				case "order_in": {
					if ($o['is_tips'] == 1) {
						$descr = $tr::t("Получение бонуса от");
					} else {
						$descr = $tr::t("Получение оплаты от покупателя");
					}
					$descr .=  " " . $o['payer_name'] . " " . $tr::t("за заказ") . " " . $o['gtitle'];
					break;
				}
				case "refund": {
					$descr = $tr::t("Возврат оплаты заказа") . " " . $o['gtitle'];
					break;
				}
				case "withdraw": {
					$paymentName = insert_get_payment_name($o);
					$descr = $tr::t("Вывод на") . " " . $paymentName;

					break;
				}
				case "moneyback": {
					$paymentName = insert_get_payment_name($o);
					$descr = $tr::t("Возврат на") . " " . $paymentName;
					break;
				}
				case "cancel_bonus": {
					$descr = sprintf($tr::t("Списание с бонусного счета неиспользованных в течении %s дней бонусов по промокоду %s"), $o['bonus_code_day_count'], $o['bonus_code']);
					break;
				}
				default: {
					break;
				}
			}
			//Массив статусов операции
			$statusArray = array("cancel_bonus" => $tr::t("Выполнено"), "cancel" => $tr::t("Отменено"), "done" => $tr::t("Выполнено"), "new" => $tr::t("В процессе"));

			$send = array($date, $descr, $o['operationAmount'], $statusArray[$o['operation_status']]);
			$toCSV[] = $send;
		}
		$toCSV[] = array($tr::t("ИТОГО") . ":", "", $operations['totalSum']);
		$currentDate = date("d-m.Y H:i:s");
		$cCsv = new \ExportCsv($toCSV, "balance_report", false);
		$cCsv->show();
		return $this->success(null);
	}
}