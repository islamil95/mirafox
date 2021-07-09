<?php


namespace Controllers\Track;

use Controllers\Track\Strategy\GetFullOrderStrategy;
use Controllers\Track\Strategy\IGetOrderStrategy;
use Converter\PortfolioToJsonCoverter;
use Core\Statistic\Strategy\AdmitadThankYouStrategy;
use Core\Statistic\Strategy\DataLayerStrategy;
use Core\Statistic\Strategy\IsNeedDataLayerOrderIdStrategy;
use Core\Traits\ConfigurationTrait;
use InboxManager;
use Model\Order;
use Model\OrderStages\OrderStage;
use Model\User;
use Portfolio\PortfolioNumber;
use PrivateMessageManager;
use Strategy\Track\GetPageMessageStrategy;
use Strategy\Track\GetRecommendedKworksStrategy;
use Strategy\Track\GetRenderParametersStrategy;
use Strategy\Track\Help\GetHelpStrategy;
use Symfony\Component\HttpFoundation\Request;
use Track\TrackHistory;
use Translations;

/**
 * Страница трека
 *
 * Class TrackController
 * @package Controllers\Track
 */
class TrackController extends AbstractTrackController {
	use ConfigurationTrait;

	/**
	 * Пометить как прочитанные уведомления
	 *
	 * @param Order $order заказ
	 * @return $this
	 */
	protected function markNotificationAsRead(Order $order) {
		return $this;
	}

	/**
	 * Обработка гугл сервисов
	 *
	 * @param Order $order заказ
	 * @return $this
	 */
	protected function googleAdServices(Order $order) {
		return $this;
	}

	/**
	 * Получить заголовок страницы
	 *
	 * @param Order $order заказ
	 * @return string заголовок
	 */
	private function getPageTitle(Order $order) {
		return Translations::t("Статус заказа") . " #" . $order->OID;
	}

	/**
	 * Получить чаевые
	 *
	 * @param Order $order заказ
	 * @return \Tips чаевые
	 */
	private function getTips(Order $order):\Tips {
		return new \Tips($order);
	}

	/**
	 * @inheritdoc
	 */
	protected function processRequest(Request $request, Order $order) {
		if ($this->isWorker()) {
			$order->initFirstRework();
			$order->initThresholdMessage();
			$currentUserModel = $order->worker;
			$otherUserModel = $order->payer;
		}else{
			$currentUserModel = $order->payer;
			$otherUserModel = $order->worker;
		}

		$parameters = (new GetRenderParametersStrategy($order))->get();
		$parameters["trackHistory"] = collect((new TrackHistory($order->tracks, $this->getUserType()))->getHistory());
		$parameters["trackHistoryDescriptions"] = $parameters["trackHistory"]->map(function($item) {
			$item->description = $item->getShortDescription();
			return $item;
		})->pluck("description")->toArray();
		$parameters["pageMsg"] = (new GetPageMessageStrategy($order))->get();
		$parameters["recommendedKworks"] = (new GetRecommendedKworksStrategy($order))->get();
		$parameters["isShowRecommendations"] = $currentUserModel->data->show_recommendations_in_track_page;
		$parameters["pagetitle"] = $this->getPageTitle($order);
		$parameters["balance_popup"] = $request->query->get("balance");
		$parameters["is_track_page"] = 1;
		$parameters["helpBlocks"] = (new GetHelpStrategy($order))->get();
		$parameters["lastTrackType"] = $order->tracks->last()->type;
		// Авторский стиль сохранен
		$parameters["tips"] = $this->getTips($order);
		$parameters["inprogressCancelRejectCounter"] = $order->inprogressCancelRejectCounter();

		// #6318 Комиссии считаются по прогрессивной шкале
		$parameters["turnover"] = \OrderManager::getTurnover($order->worker_id, $order->USERID, $order->kwork->lang);
		$parameters["commissionRanges"] = "";
		$parameters["orderPrice"] = $order->price;
		$orderFiles = [];
		$hiddenCount = 0;
		foreach ($order->tracks as $track) {
			if ($track->getHidden()) {
				$hiddenCount++;
			}
		}
		$parameters["orderFiles"] = $orderFiles;
		$parameters["hiddenCount"] = $hiddenCount;

		// помечаем событие прочитанным
		if ($this->isNotVirtual()) {
			$this->markNotificationAsRead($order);
		}

		$parameters["isVolumeOrdered"] = $order->isExtraVolumeOrdered();

		$flashMessage = $this->session->get("flashMessage");
		$this->session->delete("flashMessage");
		if ($flashMessage) {
			$parameters["pageMsg"] = $flashMessage;
		}
		$parameters["categoryBasePrice"] = \CategoryManager::getCategoryBasePrice($order->kwork->category, $order->kwork->lang);

		if ($order->isDone() && $this->isPayer()) {
			// Данные нужны для отображения модального окна для отправки сообщения или запроса на индивидуальный заказ:
			$hasLangKworks = \UserManager::hasLangKworks($otherUserModel->USERID, $currentUserModel->lang);
			$parameters["isCustomRequest"] = ($hasLangKworks && \App::config("order.request_inbox_order"));

			$allowConversation = false;
			$parameters["showInboxAllowModal"] = $allowConversation["showInboxAllowModal"];

			$parameters["offerLang"] = $currentUserModel->lang;

			$parameters["ordersBetweenUsers"] = $this->getOrdersBetween($otherUserModel);

			$parameters["controlEnLang"] =
				$otherUserModel->lang === Translations::EN_LANG
				&&
				$currentUserModel->lang === Translations::DEFAULT_LANG;

			$parameters["userProfile"] = $otherUserModel;

			$parameters["hasConversation"] = false;
		}

		$this->googleAdServices($order);
		return $this->render("track/track", $parameters);
	}

	/**
	 * @inheritdoc
	 */
	protected function getOrderStrategy(int $orderId): IGetOrderStrategy {
		return new GetFullOrderStrategy($orderId);
	}

	/**
	 * Получить взаимные заказы между и текущим пользователем и другой строной заказа
	 *
	 * @param User $user профиль пользователя
	 * @return array|null массив заказов
	 */
	private function getOrdersBetween(User $user) {
		if ($user->USERID === $this->getUserId()) {
			return null;
		};
		return \UserManager::ordersBetweenUsers($this->getUserId(), $user->USERID);
	}
}