<?php


namespace Track\View\Payer;


use Model\Track;
use Track\Type;
use Track\View\AbstractView;

/**
 * Создан новый заказ
 *
 * Class NewInProgressView
 * @package Track\View\Payer
 */
class NewInProgressView extends AbstractView {

	/**
	 * @inheritdoc
	 */
	public function __construct(Track $track) {
		parent::__construct($track);
		$this->setAddTrackIdAttribute(! $this->isMissingData());
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		$missingDataView = $this->getViewData(Type::MISSING_DATA, $this->track->order->source_type);
		return [
			"isMissingData" => $this->isMissingData(),
			"missingTitle" => $missingDataView["title"],
			"missingColor" => $missingDataView["color"],
			"missingIcon" => $missingDataView["icon"],
			"missingText" => $missingDataView[$this->getAccessKey()],
			"missingDate" => $this->track->order->stime,
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/payer/new_inprogress";
	}

	/**
	 * Не предосталены данные для начала работы
	 *
	 * @return bool
	 */
	protected function isMissingData():bool {

		return ($this->track->order->isCheck() || $this->track->order->isInProgress())
			&& $this->track->order->isPayer($this->getUserId())
			&& empty($this->track->order->data_provided);
	}
}