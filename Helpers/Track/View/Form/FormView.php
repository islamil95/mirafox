<?php

namespace Track\View\Form;

use Core\Traits\AuthTrait;
use Core\Traits\ConfigurationTrait;
use Core\Traits\Templating\RenderViewTrait;
use Model\Order;
use Strategy\Track\CanAddReviewStrategy;
use Strategy\Track\CanReOrderStrategy;
use Strategy\Track\CanWriteMessageStrategy;
use Strategy\Track\CanWriteToSellerStrategy;
use Strategy\Track\GetOptionPricesStrategy;
use Strategy\Track\GetSimilarOrderDataStrategy;
use Strategy\Track\IsInCancelRequestStrategy;

/**
 * Получить отображение для формы
 *
 * Class FormView
 * @package Track\View\Form
 */
class FormView implements IFormView {

	use RenderViewTrait, AuthTrait, ConfigurationTrait;

	private $formMD5Hash = null;
	private $order;

	public function __construct(Order $order) {
		$this->order = $order;
	}

	/**
	 * @inheritdoc
	 */
	public function render(): string {
		$isInCancelRequestStrategy = new IsInCancelRequestStrategy($this->order);

		// #6318 Комиссии считаются по прогрессивной шкале
		$turnover = \OrderManager::getTurnover($this->order->worker_id, $this->order->USERID, $this->order->kwork->lang);

		$parameters = [
			"isFormRender" => true,
			"isKworkUser" => $this->currentUserIsKworkUser(),
			"inCancelRequest" => $isInCancelRequestStrategy->get(),
			"allowedCancel" => \OrderManager::isNewCancelRequestExist($this->order->OID),
			"canWriteMessage" => (new CanWriteMessageStrategy($this->order))->get(),
			"canWriteReview" => (new CanAddReviewStrategy($this->order))->get(),
			"similarOrderData" => (new GetSimilarOrderDataStrategy($this->order))->get(),
			"canWriteToSeller" => CanWriteToSellerStrategy::getInstance($this->order)->get(),
			"cancelReasons" => \TrackManager::getCancelReasonsByOrder($this->order),
			"canReOrder" => (new CanReOrderStrategy($this->order))->get(),
			"optionPrices" => (new GetOptionPricesStrategy($this->order))->get($turnover, true),
			"maxKworkCount" => $this->config("kwork.max_count"),
		];

		$renderedForm = $this->renderView($this->getTemplateName(), $parameters);

		$this->formMD5Hash = md5($renderedForm);

		return $renderedForm;
	}

	/**
	 * @inheritdoc
	 */
	private function getTemplateName(): string {
		return "track/form/form";
	}

	/**
	 * @inheritdoc
	 */
	public function getFormMD5Hash(): string {
		return $this->formMD5Hash;
	}
}