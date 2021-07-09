<?php

namespace Model;

/**
 * Class RatedOrder Оцененный заказ
 * @package Admin\Statistic
 */
class RatedOrder
{
	/**
	 * Типы исходов заказов в рамках оценки рейтинга
	 */
	const GOOD_REVIEW = 1;
	const CANCEL_WITH_REVIEW = 2;
	const BAD_REVIEW = 3;
	const CANCEL_WO_REVIEW = 4;
	const CANCEL_ARBITRAGE = 5;
	const CANCEL_ARBITRAGE_NEW = 55;
	const ONE_DONE_WO_REVIEW = 6;
	const REPEAT_DONE_WO_REVIEW = 7;
	const NOT_RATED = 8;
	const TIPS = 9;

	/**
	 * Описания к типам исходов заказов в рамках оценки рейтинга
	 */
	const ORDER_RATES_LABELS = [
		self::GOOD_REVIEW => "Положительный отзыв",
		self::CANCEL_WITH_REVIEW => "Отрицательный отзыв на отмененный заказ",
		self::BAD_REVIEW => "Отрицательный отзыв на принятый заказ",
		self::CANCEL_WO_REVIEW => "Просроченный заказ без отзыва",
		self::CANCEL_ARBITRAGE => "Отменен в пользу покупателя через арбитраж",
		self::CANCEL_ARBITRAGE_NEW => "Отменен в пользу покупателя через арбитраж",
		self::ONE_DONE_WO_REVIEW => "Первый заказ покупателя без отзыва",
		self::REPEAT_DONE_WO_REVIEW => "Последующий заказ покупателя без отзыва",
		self::NOT_RATED => "Заказ не влияет на рейтинг",
		self::TIPS => "Подтвержден. Без отзывов, с бонусом"
	];

	/**
	 * Оценки типов исходов заказов
	 */
	const ORDER_RATES = [
		self::GOOD_REVIEW => 1,
		self::CANCEL_WITH_REVIEW => -7,
		self::BAD_REVIEW => -5,
		self::CANCEL_WO_REVIEW => -3,
		self::CANCEL_ARBITRAGE => -1,
		self::CANCEL_ARBITRAGE_NEW => -5,
		self::ONE_DONE_WO_REVIEW => -0.7,
		self::REPEAT_DONE_WO_REVIEW => 0.8,
		self::NOT_RATED => 0,
		self::TIPS => 0.8,
	];

	/**
	 * @var int Идентификатор заказа
	 */
	private $id;

	/**
	 * @var int Идентификатор типа исхода
	 */
	private $type;

	/**
	 * @var float Балл заказа
	 */
	private $rate;

	/**
	 * RatedOrder constructor. Происходит оценка заказа и сохранение значимых данных
	 * @param array $order Ожидается массив с полями
	 * [OID, RID, good, bad, status, type, reason_type, hasPrevDone]
	 */
	public function __construct(array $order)
	{
		$this->id = (int)$order["OID"];
		$this->type = self::NOT_RATED;

		if (!is_null($order['RID'])) {                              //если у заказа есть отзыв
			if ($order['good']) {
				$this->type = self::GOOD_REVIEW;
			} else {
				if ($order['status'] == \OrderManager::STATUS_CANCEL) {
					$this->type = self::CANCEL_WITH_REVIEW; //плохой отзыв на отмененный заказ
				} else {
					$this->type = self::BAD_REVIEW;//плохой отзыв на принятый заказ
				}
			}
		} else {
			if ($order['status'] == \OrderManager::STATUS_CANCEL) {  //если это отмененный заказ
				if (in_array($order['reason_type'], ['payer_time_over', 'payer_no_communication_with_worker'])) {   //просрочен
					$this->type = self::CANCEL_WO_REVIEW;
				} elseif ($order['type'] == 'admin_arbitrage_cancel') { //отменен в пользу покупателя через арбитраж
					$this->type = self::CANCEL_ARBITRAGE;

					// Задача #5083 - Для новых арбиражей меняется бал
					// todo: сделать новый бал для всех арбитражей и удалить функционал в 2019
					$arbitrage = \ArbitrageAssignManager::getByOrder($this->id);
					if(!empty($arbitrage)) {
						$dateArbitrage = new \DateTime($arbitrage["date_create"]);
						if($dateArbitrage->getTimestamp() > \ArbitrageAssignManager::FROM_TASK_DATE) {
							$this->type = self::CANCEL_ARBITRAGE_NEW;
						}
					}
				}
			} else {
				if (!$order["hasPrevDone"]) {
					$this->type = self::ONE_DONE_WO_REVIEW;//если это первый заказ этого покупателя без отзыва
				} else {
					$this->type = self::REPEAT_DONE_WO_REVIEW;//если это последующие заказы этого покупателя без отзыва
				}
			}
		}

		$this->rate = (float)self::ORDER_RATES[$this->type];
	}

	/**
	 * Идентификатор заказа
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * Балл заказа
	 * @return float
	 */
	public function getRate(): float
	{
		return $this->rate;
	}

	/**
	 * Идентификатор типа исхода
	 * @return int
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * Описание типа исхода
	 * @return string
	 */
	public function getTypeLabel(): string
	{
		return self::ORDER_RATES_LABELS[$this->type];
	}

}