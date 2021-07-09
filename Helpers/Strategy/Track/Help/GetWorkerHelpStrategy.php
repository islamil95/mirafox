<?php


namespace Strategy\Track\Help;


class GetWorkerHelpStrategy extends AbstractGetHelpStrategy {

	protected function getDefaultBlocks(): array {
		return $this->standardStatus["worker"];
	}

	protected function getArbitrageBlock(): array {
		$trackType = $this->order->isCheck() ? "worker_check_arbitrage" : "worker_inprogress_arbitrage";
		$arbitrageLink = $this->getArbitrageLinkHtml($trackType);

		return [
			"title" => \Translations::t("Покупатель отказывается принимать работу"),
			"content" => \Translations::t("Если вы выполнили работу в полном объеме, но покупатель отказывается принимать ее, вы можете обратиться в %s. Модератор рассмотрит ваш спор и примет решение в пользу одной из сторон.", $arbitrageLink),
		];
	}
}