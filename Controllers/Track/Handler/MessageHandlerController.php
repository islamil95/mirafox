<?php


namespace Controllers\Track\Handler;


use Core\Exception\JsonException;
use Core\Exception\RedirectException;
use Model\CurrencyModel;
use Model\Track;
use Model\Track\TrackCreateData;
use Strategy\Track\GetOpponentIdStrategy;
use Strategy\Track\IsAllowConversationInDoneOrderStrategy;
use Strategy\Track\IsCurrentUserCanWriteMessageStrategy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Track\Type;

/**
 * Отправка сообщений
 *
 * Class MessageHandlerController
 * @package Controllers\Track\Handler
 */
class MessageHandlerController extends AbstractTrackHandlerController {

	/**
	 * @var int идентификатор созданного трека
	 */
	private $createdTrackId;

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	protected function getTrackId() {
		return $this->createdTrackId;
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
	 * Предложены опции
	 *
	 * @return bool результат
	 */
	private function isHaveSuggestedOptions(): bool {
		$request = $this->getRequest()->request;
		return $request->has("suggestextrassubmited");
	}

	/**
	 * Заказа не валиден
	 *
	 * @return bool
	 */
	private function isNotValid(): bool {
		// Продавец Покупатель написал сообщение
		$order = $this->getOrder();
		return ! (($order->isInProgress() ||
				$order->isCheck() ||
				$order->isDone() ||
				$order->isArbitrage() ||
				$order->isUnpaid()) &&
			($this->getMessage() || $this->isHaveAttachedFiles()) || $this->isHaveSuggestedOptions());
	}

	/**
	 * @inheritdoc
	 */
	protected function getRedirectUrl(): string {
		return $this
			->getUrlByRoute("track",
				["id" => $this->getOrderId()]
			);
	}

	/**
	 * Оставить сообщение по арбитражу
	 *
	 * @return MessageHandlerController
	 */
	private function processArbitrageAsKworkUser(): self {
		$adminData = \AdminManager::getCurrent();
		if (!$adminData) {
			throw (new RedirectException())
				->setRedirectUrl($this->getRedirectUrl());
		}
		$order = $this->getOrder();
		$this->createdTrackId = \TrackManager::create(
			$order->OID,
			Type::TEXT,
			$this->getMessage(),
			null,
			null,
			null,
			null,
			null,
			$this->config("kwork.user_id"),
			$adminData->ADMINID);
		return $this;
	}

	/**
	 * Обработка отправленных опций продавцом
	 *
	 * @return MessageHandlerController
	 */
	private function processOptions(): self {
		//Продавец предлагает опции
		$request = $this->getRequest()->request;
		$order = $this->getOrder();
		if ($request->has("suggestextrassubmited")) {
			$orderLang = $order->currency_id == CurrencyModel::RUB ? \Translations::DEFAULT_LANG : \Translations::EN_LANG;
			\TrackManager::workerSuggestExtras(
				$order->OID,
				\ExtrasManager::extrasFromPost(),
				\ExtrasManager::customExtrasFromPost($orderLang),
				$request->get("as_volume"),
				$request->get("upgrade-package")
			);
			// Если трека нет, то будет редирект
			$this->createdTrackId = null;
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function processAction(): Response {
		if ($this->isNotValid()) {
			if ($this->isAjax()) {
				// Если запрос был через ajax, то отправляем пусто запрос без редиректа
				return new Response("");
			}
			return new RedirectResponse($this->getRedirectUrl());
		}
		$order = $this->getOrder();
		$isAllowConversationInDoneOrderStrategy = new IsAllowConversationInDoneOrderStrategy($order);
		if ($order->isDone() && !$isAllowConversationInDoneOrderStrategy->get()) {
			return new RedirectResponse($this->getRedirectUrl());
		}

		// Пользователь Kwork оставляет комментарий по арбитражу
		if (($this->currentUserIsKworkUser() || $this->isVirtual()) && $order->isArbitrage()) {
			$this->processArbitrageAsKworkUser();
		} else {
			if ($order->isNotDone() && ($this->getMessage() || $this->isHaveAttachedFiles())) {
				$data = new TrackCreateData();
				$data->orderId = $order->OID;
				$data->type = Type::TEXT;
				$data->message = $this->getMessage();
				$data->quoteId = $this->getQuoteId() ?: null;
				$this->createdTrackId = \TrackManager::createViaData($data);
				// уведомления
				$userId = $order->isPayer($this->getUserId()) ? $order->worker_id : $order->USERID;
			} elseif ($order->isDone()) {
				$msg = \Translations::t("Вы не можете оставить сообщение, так как заказ был завершен.");
				throw (new JsonException())->setData(["status" => "error", "response" => $msg]);
			}
			// Обработка предложения доп опций
			if ($order->isInProgress() && $order->isWorker($this->getUserId())) {
				$this->processOptions();
			}
		}
		return $this->getResponse();
	}
}