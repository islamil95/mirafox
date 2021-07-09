<?php


namespace Track\View\Order;


use Strategy\Track\IsDoneConversationAllowStrategy;
use Track\Type;
use Track\View\AbstractView;

/**
 * Работа принята (для продавца)
 *
 * Class WorkerDoneView
 * @package Track\View\Order
 */
class WorkerDoneView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return \Translations::t("Поздравляем! Этот заказ выполнен.");
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		$disablePortfolio = Type::PAYER_CHECK_DONE == $this->track->type &&
			$this->track->order->kwork->kworkCategory->portfolio_type == "deny";

		$payerUsername = mb_strtolower($this->track->order->payer->username);
		return [
			"isDoneConvAllow" => (new IsDoneConversationAllowStrategy($this->track->order))->get(),
			"conversationUrl" => "<a href=\"/conversations/$payerUsername?goToLastUnread=1\">",
			"disablePortfolio" => $disablePortfolio,
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/order/worker_order_done";
	}
}