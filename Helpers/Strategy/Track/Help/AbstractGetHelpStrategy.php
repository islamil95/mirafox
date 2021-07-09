<?php


namespace Strategy\Track\Help;


use Model\Order;
use Strategy\Track\AbstractTrackStrategy;
use Strategy\Track\IsAllowArbitrageStrategy;

abstract class AbstractGetHelpStrategy extends AbstractTrackStrategy {


	/**
	 * @var array $standardStatus
	 */
	protected $standardStatus;

	public function __construct(Order $order) {
		parent::__construct($order);
		$this->standardStatus = [
			"payer" => [
				"does-not-respond" => [
					"title" => \Translations::t("Продавец не отвечает на мои сообщения"),
					"content" => \Translations::t("Ответ некоторых продавцов может занять большее время, чем вы ожидали из-за разницы во времени или выходных. Если вы чувствуете, что продавец отвечает слишком долго, вы можете создать запрос на отмену заказа. Либо, если работа над заказом не была начата, дождаться автоотмены заказа через %s.", \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT, $order->restarted)),
				],
				"wrong-work" => [
					"title" => \Translations::t("Работа, которую сдал продавец, отличалась от того, какой он её описывал"),
					"content" => \Translations::t('У вас есть три дня, чтобы проверить сданную на проверку работу. Если вы видите, что работа не выполнена или выполнена не в том объеме - верните ее на доработку, указав причины и список доработок требования. Если работа выполнена совсем не так, как утверждалось, вы можете отменить заказ по согласованию с продавцом. Для этого нажмите "Отменить заказ" внизу страницы и выберите соответствующую причину.'),
				],
			],
			"worker" => [
				"missing-data" => [
					"title" => \Translations::t("Покупатель не предоставил необходимую для начала работы информацию"),
					"content" => \Translations::t('Чтобы избежать ожидания предоставления покупателем необходимой информации и исключить риск сдачи с опозданием, заполняйте поле "Инструкции покупателю" в каждом из ваших заданий. Если же информация не предоставлена, а время взятия заказа в работу подходит к концу, отправьте запрос на отмену заказа с соответствующей причиной.'),
				],
				"does-not-respond" => [
					"title" => \Translations::t("Покупатель не отвечает на мои сообщения"),
					"content" => \Translations::t("Иногда ответ покупателей может занять некоторое время из-за разницы во времени или выходных. Если вы напомнили о вопросе покупателю уже несколько раз, а ответа все нет, и без ответа на вопрос вы не можете продолжать работу, отправьте запрос на отмену заказа с соответствующей причиной."),
				],
			],
		];

	}

	abstract protected function getDefaultBlocks():array;
	abstract protected function getArbitrageBlock():array;

	protected function getArbitrageLinkHtml(string $trackType) {
		$linkTags = [
			"data-track_type=\"{$trackType}\"",
			"data-order_id=\"{$this->order->OID}\"",
			"id=\"arbitrage-link\"",
			"href=\"javascript:void(0)\"",
		];
		$linkAttr = implode(" ", $linkTags);
		$text = \Translations::t("Арбитраж");
		return "<a $linkAttr>$text</a>";
	}

	public function get() {
		$blocks = $this->getDefaultBlocks();
		$isAllowArbitrage = (new IsAllowArbitrageStrategy($this->order))->get();
		if ($isAllowArbitrage) {
			$blocks["arbitrage"] = $this->getArbitrageBlock();
		}

		/**
		 * @TODO: переделать с роутингом
		 */
		$content = \Translations::t("Если вы не нашли ответа на свой вопрос, посмотрите список всех %sвопросов-ответов%s или %sзадайте вопрос%s специалисту поддержки пользователей", '<a href="/faq" target="_blank">', '</a>', '<a href="/contact" target="_blank">', '</a>');
		$blocks["send-somewhere"] = [
			"title" => "",
			"content" => $content,
		];

		return $blocks;
	}
}