<?php

namespace Operations;

use Core\DB\DB;
use Illuminate\Support\Collection;
use Model\Operation;
use OperationManager;

/**
 * Класс-помощник, который получает и хранит операции в статусе "done" и данные по ним, по одному конкретному заказу.
 * Использование допустимо только в обычных, не поэтапных заказах
 */
class DoneOperations {


	/**
	 * Операции в статусе done
	 * @var \Illuminate\Database\Eloquent\Collection|\Model\Operation[]
	 */
	protected $doneOperations;

	/**
	 * Сумма операций по выплате продавцу (тип: order_in)
	 * @var float
	 */
	protected $workerAmount;

	/**
	 * Сумма операций по списанию с покупателя (тип: order_out)
	 * @var float
	 */
	protected $payerAmount;

	/**
	 * Сумма Операции возврата средств покупателю которые нужно отменять (тип: refund)
	 * Последняя операция возврата + последняя операция возврата по чаевым
	 * @var float
	 */
	protected $refundAmount;

	/**
	 * Операции начисления продавцу (включая чаевые)
	 *
	 * @var \Illuminate\Database\Eloquent\Collection|\Model\Operation[]
	 */
	protected $workerOperations;

	/**
	 * Операции возврата средств продавцу которые нужно отменять
	 * Последняя операция возврата + последняя операция возврата по чаевым
	 *
	 * @var \Illuminate\Database\Eloquent\Collection|\Model\Operation[]
	 */
	protected $refundOperations;

	public function __construct(int $orderId) {
		$this->load($orderId);
	}

	/**
	 * Получает все необходимые данные и сохраняет в объекте
	 *
	 * @param int $orderId
	 */
	protected function load(int $orderId) {
		$this->doneOperations = Operation::where(Operation::FIELD_ORDER_ID, $orderId)
			->where(Operation::FIELD_STATUS, OperationManager::FIELD_STATUS_DONE)
			->orderByDesc(Operation::FIELD_ID)
			->get();

		$this->workerOperations = $this->doneOperations
			->where(Operation::FIELD_TYPE, OperationManager::TYPE_ORDER_IN);

		$this->workerAmount = $this->workerOperations
			->sum(Operation::FIELD_AMOUNT);

		$payerTotalOutSum = $this->doneOperations
			->where(Operation::FIELD_TYPE, OperationManager::TYPE_ORDER_OUT)
			->sum(Operation::FIELD_AMOUNT);

		$payerTotalRefundSum = $this->doneOperations
			->where(Operation::FIELD_TYPE, OperationManager::TYPE_REFUND)
			->sum(Operation::FIELD_AMOUNT);

		$this->payerAmount = $payerTotalOutSum - $payerTotalRefundSum;

		// Последняя операция возврата по заказу (не чаевые)
		$lastRefundOperation = $this->doneOperations
			->where(Operation::FIELD_TYPE, OperationManager::TYPE_REFUND)
			->where(Operation::FIELD_IS_TIPS, false)
			->first();

		$this->refundOperations = new Collection();
		$this->refundOperations->push($lastRefundOperation);

		// Если есть операция возврата чаевых - добавляем к операциям возврата
		$lastTipsRefundOperation = $this->doneOperations
			->where(Operation::FIELD_TYPE, OperationManager::TYPE_REFUND)
			->where(Operation::FIELD_IS_TIPS, true)
			->first();
		if ($lastTipsRefundOperation) {
			$this->refundOperations->push($lastTipsRefundOperation);
		}

		$this->refundAmount = $this->refundOperations
			->sum(Operation::FIELD_AMOUNT);
	}

	/**
	 * Есть ли операции по начислению продавцу в статусе done
	 * Операции по чаевым не учитываются
	 *
	 * @return bool
	 */
	public function isWorkerOperationExists(): bool {
		return $this->workerOperations
			->where(Operation::FIELD_IS_TIPS, false)
			->isNotEmpty();
	}

	/**
	 * Возвращает сумму операций начислиний для продавца
	 *
	 * @return mixed
	 */
	public function getWorkerAmount() {
		return $this->workerAmount;
	}

	/**
	 * Возвращает сумму операциям списания с покупателя
	 *
	 * @return mixed
	 */
	public function getPayerAmount() {
		return $this->payerAmount;
	}

	/**
	 * Возвращает сумму возвращённых покупателю средств
	 *
	 * @return mixed
	 */
	public function getRefundAmount() {
		return $this->refundAmount;
	}

	/**
	 * Возвращает массив ID операций начислений продавцу
	 *
	 * @return array|int[]
	 */
	public function getWorkerOperationIds(): array {
		return $this->workerOperations
			->pluck(Operation::FIELD_ID)
			->toArray();
	}

	/**
	 * Возвращает массив ID операций списаний с покупателя
	 *
	 * @return array|int[]
	 */
	public function getRefundOperationIds(): array {
		return $this->refundOperations
			->pluck(Operation::FIELD_ID)
			->toArray();
	}
}