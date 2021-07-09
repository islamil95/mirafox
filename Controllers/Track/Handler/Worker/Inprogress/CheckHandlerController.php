<?php


namespace Controllers\Track\Handler\Worker\Inprogress;


use Controllers\Track\Handler\AbstractTrackHandlerController;
use Core\Exception\JsonException;
use Model\Track;
use Symfony\Component\HttpFoundation\Response;
use Track\Type;

/**
 * Продавец отправил работу на проверку
 *
 * Class CheckHandlerController
 * @package Controllers\Track\Handler\Worker\Inprogress
 */
class CheckHandlerController extends AbstractTrackHandlerController {

	/**
	 * @var int идентификатор созданного трека
	 */
	private $trackId = 0;

	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTrackId() {
		return $this->trackId;
	}

	/**
	 * Получение массива идентификаторов этапов (для этапных заказов)
	 *
	 * @return array
	 */
	private function getOrderStagesIds() {
		return \Helper::intArrayNoEmpty(explode(",", $this->getRequest()->request->get("stageIds")));
	}

	/**
	 * @inheritdoc
	 */
	protected function getTracksList(): array {
		$tracksList = [$this->getTrackId()];

		// Для продавца проверим есть ли открытый запрос на отмену от покупателя
		if ($this->getUserId() == $this->getOrder()->worker_id) {
			$payerInprogressCancelTrack = Track::where(Track::FIELD_ORDER_ID, $this->getOrder()->OID)
				->where(Track::FIELD_TYPE, Type::PAYER_INPROGRESS_CANCEL_REQUEST)
				->where(Track::FIELD_STATUS, \TrackManager::STATUS_NEW)
				->first();
			if ($payerInprogressCancelTrack) {
				$tracksList[] = $payerInprogressCancelTrack->MID;
			}
		}
		sort($tracksList);
		return $tracksList;
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		$order = $this->getOrder();
		$message = $this->getMessage();
		if ($order->isNotInWork()) {
			throw (new JsonException())->setData([
				"status" => "error",
				"response" => \Translations::t("Необходимо взять заказ в работу."),
			]);
		}
		$this->trackId = \OrderManager::worker_inprogress_check($order->OID, $message, $this->getOrderStagesIds());
		return $this->getResponse();
	}
}