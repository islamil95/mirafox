<?php


namespace Strategy\Track\Help;


class GetPayerHelpStrategy extends AbstractGetHelpStrategy {

	protected function getDefaultBlocks(): array {
		return $this->standardStatus["payer"];
	}

	protected function getArbitrageBlock(): array {
		$trackType = $this->order->isCheck() ? "payer_check_arbitrage" : "payer_inprogress_arbitrage";
		$arbitrageLink = $this->getArbitrageLinkHtml($trackType);

		return [
			"title" => \Translations::t("Продавец отказывается выполнять работу в полном объеме"),
			"content" => \Translations::t("Если продавец отказывается выполнять работу в полном объеме, вы можете обратиться в %s. Модератор рассмотрит ваш спор и примет решение в пользу одной из сторон.", $arbitrageLink),
		];
	}
}