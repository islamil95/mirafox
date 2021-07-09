<?php


namespace Strategy\Track\Pull;


use Core\Traits\Templating\RenderViewTrait;
use Strategy\Track\GetTimeLeftAsStringStrategy;
use Strategy\Track\Help\GetHelpStrategy;
use Strategy\Track\IsHasFilesInTrackStrategy;
use Strategy\Track\IsInCancelRequestStrategy;
use Track\Status;

class GetSideBarStrategy extends AbstractPullTrackStrategy {

	use RenderViewTrait;

	/**
	 * Получить результат
	 *
	 * @return mixed
	 */
	public function get() {
		$trackStatus = new Status($this->order, $this->getUserId());
		$hasFilesInTrackStrategy = new IsHasFilesInTrackStrategy($this->getLastTracks());
		$other = [];

		$parameters = [
			"order" => $this->order,
			"timeLeftStr" => (new GetTimeLeftAsStringStrategy($this->order))->get(),
			"isCancelRequest" => (new IsInCancelRequestStrategy($this->order))->get(),
			"statusTitle" => $trackStatus->title(),
			"statusDesc" => $trackStatus->description(),
		];
		$other["sidebar-order-state"] = $this->renderView("track/sidebar_order_state", $parameters);

		// Блок с файлами в сайдбаре.
		if ($hasFilesInTrackStrategy->get()) {
			$other["order-files"] = $this->renderView("track/sidebar_files", $parameters);
		}
		$getHelpStrategy = new GetHelpStrategy($this->order);
		$helpBlocks = $getHelpStrategy->get();
		// Блок помощи с текстом про арбитраж.
		if (isset($helpBlocks["arbitrage"])) {
			$blockParams = [
				"id" => "arbitrage",
				"block" => $helpBlocks["arbitrage"],
				"isFirst" => false,
				"isLast" => false,
			];
			$other["arbitrage"] = $this->renderView("track/help_block", $blockParams);
		}
		return $other;
	}
}