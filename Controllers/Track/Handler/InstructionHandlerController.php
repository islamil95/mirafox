<?php


namespace Controllers\Track\Handler;


use Core\DB\DB;
use Core\Exception\JsonException;
use Model\Kwork;
use Model\Order;
use Model\Track;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Track\Type;

/**
 * Представление инструкций по заказу
 *
 * Class InstructionHandlerController
 * @package Controllers\Track\Handler
 */
class InstructionHandlerController extends AbstractTrackHandlerController {

	/**
	 * @var int идентификатор созданного трека
	 */
	private $createdTrackId;
	/**
	 * @var int идентификатор трека совета
	 */
	private $adviceTrackId;

	/**
	 * @inheritdoc
	 */
	protected function getTrackId() {
		return $this->createdTrackId;
	}

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * Заказ не верный
	 *
	 * @return bool
	 */
	private function isNotValid(): bool {
		// Продавец Покупатель написал сообщение
		$order = $this->getOrder();
		return ! (($order->isInProgress() ||
				$order->isCheck() ||
				$order->isDone() ||
				$order->isArbitrage()) &&
			($this->getMessage() || $this->isHaveAttachedFiles()));
	}

	/**
	 * Создание трека совета покупателя
	 *
	 * @return bool|int идентификатор созданного трека
	 */
	private function createAdviceTrack() {
		$order = $this->getOrder();
		if (in_array($order->data->kwork_category, [])) {
			return false;
		}
		$isHavePayerAdvice = $order->tracks->sum(function ($track) {
			/**
			 * @var Track $track
			 */
			return $track->type == Type::PAYER_ADVICE ? 1 : 0;
		});

		if ($isHavePayerAdvice) {
			return false;
		}

		$ordersCount = DB::table(Order::TABLE_NAME)
			->join(Kwork::TABLE_NAME, "posts.PID", "=", "orders.PID")
			->where("posts.category",$order->data->kwork_category)
			->whereIn("orders.status",[\OrderManager::STATUS_INPROGRESS])
			->where("orders.OID",$order->OID)
			->where("orders.USERID",$order->USERID)
			->count();

		if($ordersCount) {
			return false;
		}

		return \TrackManager::create($order->OID,Type::PAYER_ADVICE, "instruction");
	}

	/**
	 * @inheritdoc
	 */
	protected function getTracksList(): array {
		return [$this->createdTrackId, $this->adviceTrackId];
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		if ($this->isNotValid()) {
			return new RedirectResponse($this->getRedirectUrl());
		}
		$order = $this->getOrder();
		if (!$order->isPayer($this->getUserId())) {
			// Если пользователь не покупатель этого заказа, нельзя заходить в контроллер
			return new RedirectResponse($this->getRedirectUrl());
		}
		// если это инструкция от Покупателя, то помечаем его первым
		if (!$order->data_provided) {
			\OrderManager::setDataProvided($order->OID, $order->USERID);
		}
		if ($order->isNotDone()) {
			$this->createdTrackId = \TrackManager::create($order->OID, Type::TEXT_FIRST, $this->getMessage());
		} elseif ($order->isDone()) {
			$msg = \Translations::t("Вы не можете оставить сообщение, так как заказ был завершен.");
			throw (new JsonException())->setData(["status" => "error", "response" => $msg]);
		}

		$this->adviceTrackId = $this->createAdviceTrack();
		\OrderManager::fillProvidedHash($order->OID);
		return $this->getResponse();
	}
}