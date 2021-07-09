<?php


namespace Controllers\Track\Handler;


use Core\Exception\RedirectException;
use Symfony\Component\HttpFoundation\Response;
use Track\Type;

/**
 * Обработка отмены заказа
 *
 * Class CancelOrderHandlerController
 * @package Controllers\Track\Handler
 */
class CancelOrderHandlerController extends AbstractTrackHandlerController {

	/**
	 * @var int созданный трек
	 */
	private $createdTrackId;

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTrackId() {
		return $this->createdTrackId;
	}

	/**
	 * Проверка причин отмены заказа
	 *
	 * @return CancelOrderHandlerController
	 */
	private function reasonTypeValidate(): self {
		$parentReasonType = $this->getParentReason();
		$availableReasons = \TrackManager::getAvailCancelReasons($this->getOrder());
		$reasonType = $this->getRequest()->request->get("reason");
		$combinedReason = $parentReasonType . "-" . $reasonType;
		if ($parentReasonType) {
			if (!$reasonType || !in_array($combinedReason, $availableReasons)) {
				throw (new RedirectException())->setRedirectUrl("/");
			}
		} else {
			if ($reasonType && !in_array($reasonType, $availableReasons)) {
				throw (new RedirectException())->setRedirectUrl("/");
			}
		}

		return $this;
	}

	/**
	 * Получить название нужного действия по отмене заказа
	 *
	 * @return string|null
	 */
	private function getAction() {
		$reasonType = $this->getRequest()->request->get("reason");
		if ($this->getParentReason()) {
			$reason = \TrackManager::getCancelReason($this->getParentReason(), $this->getUserType());
			if(is_array($reason) && isset($reason["subtypes"][$reasonType])) {
				return $reason["subtypes"][$reasonType]["track_status"];
			}
		} else {
			$reason = \TrackManager::getCancelReason($reasonType,  $this->getUserType());

			if (is_array($reason)) {
				return $reason["track_status"];
			}
		}
		return null;
	}

	/**
	 * Получить причину отмены заказа
	 *
	 * @return string
	 */
	private function getReason() {
		$reasonType = $this->getRequest()->request->get("reason");
		if ($this->getParentReason()) {
			$reason = \TrackManager::getCancelReason($this->getParentReason(), $this->getUserType());
			if (is_array($reason) &&
				isset($reason["subtypes"][$reasonType])) {
				return $this->getParentReason() . "-" . $reasonType;
			} else {
				return "";
			}
		} else {
			$reason = \TrackManager::getCancelReason($reasonType, $this->getUserType());
			if (! is_array($reason)) {
				return "";
			}
		}

		return $reasonType;
	}

	/**
	 * Получить родительскую причину отмены заказа
	 *
	 * @return mixed
	 */
	private function getParentReason() {
		return $this->getRequest()->request->get("parentReason");
	}

	/**
	 * Обработка галки "скрыть кворки продавца"
	 */
	private function handleHideUserKworks() {
		$hideUser = $this->getRequest()->request->get("hide_all_user_kworks");
		if ($hideUser) {
			\HiddenManager::add($this->getUserId(), $hideUser, \HiddenManager::HIDDEN_TYPE_USER);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		// Проверка
		$this->reasonTypeValidate();
		$action = $this->getAction();
		$reasonType = $this->getReason();
		switch ($action) {
			// Покупатель отменил заказ, если просрочен или заказал по ошибке
			case Type::PAYER_INPROGRESS_CANCEL:
				if ($reasonType) {
					$this->createdTrackId = \OrderManager::payer_inprogress_cancel($this->getOrderId(), $this->getMessage(), $reasonType);
				}
				break;

			// Покупатель запросил обоюдную отмену заказа
			case Type::PAYER_INPROGRESS_CANCEL_REQUEST:
				if ($reasonType && ($reasonType != "payer_other_no_guilt" || $this->getMessage())) {
					if ($this->getOrder()->late() && in_array($reasonType, \TrackManager::getImmidiateCancelAfterLateReasons())) {
						// Если просрочен и причина изменяющая после посрочки на мгновенную отмену - то отменяем сразу
						$this->createdTrackId = \OrderManager::payer_inprogress_cancel($this->getOrderId(), $this->getMessage(), $reasonType);
					} else {
						$this->createdTrackId = \OrderManager::payer_inprogress_cancel_request($this->getOrderId(), $this->getMessage(), $reasonType);
					}
				}
				break;

			// Продавец вынужденно отменил заказ
			case Type::WORKER_INPROGRESS_CANCEL:
				if ($this->getMessage() && $reasonType) {
					$this->createdTrackId = \OrderManager::worker_inprogress_cancel($this->getOrderId(), $this->getMessage(), $reasonType);
				}
				break;

			// Продавец запросил обоюдную отмену заказа
			case Type::WORKER_INPROGRESS_CANCEL_REQUEST:
				if ($this->getMessage() && $reasonType) {
					$this->createdTrackId = \OrderManager::worker_inprogress_cancel_request($this->getOrderId(), $this->getMessage(), $reasonType);
				}
				break;
		}

		if ($this->createdTrackId) {
			// Обработка галки "скрыть кворки продавца"
			$this->handleHideUserKworks();
		}
		return $this->getResponse();
	}
}