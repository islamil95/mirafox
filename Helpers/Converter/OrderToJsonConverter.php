<?php


namespace Converter;

use Core\Traits\AuthTrait;
use Core\Traits\ConfigurationTrait;
use Model\Order;
use Strategy\Track\CanAddReviewStrategy;
use Strategy\Track\CanDeletePortfolioStrategy;
use Strategy\Track\CanReOrderStrategy;
use Strategy\Track\CanSendPortfolioStrategy;
use Strategy\Track\CanWriteMessageStrategy;
use Strategy\Track\CanWriteToSellerStrategy;
use Strategy\Track\GetAvailableReviewTypeStrategy;
use Strategy\Track\GetDeadlineTimeCancelRequestStrategy;
use Strategy\Track\GetOpponentIdStrategy;
use Strategy\Track\GetStoppedTimeCancelRequestStrategy;
use Strategy\Track\HasCountdownStrategy;
use Strategy\Track\IsDoneConversationAllowStrategy;
use Strategy\Track\IsInCancelRequestStrategy;

class OrderToJsonConverter implements \JsonSerializable {

	use ConfigurationTrait,
		AuthTrait;

	/**
	 * @var Order $order
	 */
	private $order;

	public function __construct(Order $order) {
		$this->order = $order;
	}

	public function jsonSerialize() {
		$isInCancelRequestStrategy = new IsInCancelRequestStrategy($this->order);
		$portfolioImages = [];
		if ($this->order->portfolio) {
			$portfolioImages = $this->order->portfolio->images;
		}
		return [
			"maxFileCount" => $this->getInt("files.max_count"),
			"maxFileSize" => \TrackManager::MAX_FILE_SIZE,
			"multiKworkRate" => \OrderManager::getMultiKworkRate($this->order->data->category),
			"isKworkUser" => $this->currentUserIsKworkUser(),
			"orderId" => (int) $this->order->OID,
			"orderStatus" => (int) $this->order->status,
			"opponentId" => (new GetOpponentIdStrategy($this->order))->get(),
			"kworkLang" => $this->order->kwork->lang,
			"inCancelRequest" => $isInCancelRequestStrategy->get(),
			"canWriteMessage" => (new CanWriteMessageStrategy($this->order))->get(),
			// для возможности 2 недели после завершения заказа вести переписку в треке или личке
			"isDoneConvAllow" => (new IsDoneConversationAllowStrategy($this->order))->get(),
			// доступен ли арбитраж
			"isArbitrageEnabled" => $this->getBool("arbitrage.enable"),
			"canWriteReview" => (new CanAddReviewStrategy($this->order))->get(),
			"editTypeReview" => (new GetAvailableReviewTypeStrategy($this->order))->get(),
			"deadline" => $this->order->deadline,
			"hasCountbox" => (new HasCountdownStrategy($this->order))->get(),
			"stopedTimeCancelRequest" => (new GetStoppedTimeCancelRequestStrategy($this->order))->get(),
			"deadlineTimeCancelRequest" => (new GetDeadlineTimeCancelRequestStrategy($this->order))->get(),
			//Может ли покупатель связаться с продавцом
			"canWriteToSeller" => CanWriteToSellerStrategy::getInstance($this->order)->get(),
			"canSendPortfolio" => (new CanSendPortfolioStrategy($this->order))->get(),
			"canDeletePortfolio" => (new CanDeletePortfolioStrategy($this->order))->get(),
			"canReOrder" => (new CanReOrderStrategy($this->order))->get(),
			"isCancelRequest" => $isInCancelRequestStrategy->get(),
			"needHideAddExtrasList" => $this->order->isNeedHideAddExtrasList(),
			"orderedExtras" => $this->order->extras,
			"maxKworkCount" => $this->getInt("kwork.max_count"),
			"actorType" => $this->getUserType(),
			"time" => time(),
			"portfolioImages" => $portfolioImages,
			"loyalityLateVisible" => \TrackManager::isLoyalityLateVisible($this->isWorker(), $this->order->deadline),
			"inprogressCancelRejectCounter" => $this->order->inprogressCancelRejectCounter(),
			"stages" => $this->order->has_stages ? $this->order->stages : [],
		];
	}
}