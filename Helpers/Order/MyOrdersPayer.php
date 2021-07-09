<?php


namespace Order;


/**
 * Class MyOrdersPayer.
 *
 * @package Order
 */
class MyOrdersPayer extends MyOrders {

	/** {@inheritDoc} */
	protected $defaultActiveStates = [
		'missing_data',
		'delivered',
		'active',
		'unpaid',
		'completed',
		'cancelled',
		'all',
	];

	/**
	 * MyOrdersPayer constructor.
	 */
	public function __construct() {
		parent::__construct();

		$missingDataItem = new MyOrdersTabItem(\Translations::t('Предоставьте данные'), 'live-tabs__item-number--yellow');

		$workerStatesData = [];
		foreach($this->statesData as $key => $item) {
			$workerStatesData[$key] = $item;
			if ('active' == $key) {
				$workerStatesData['missing_data'] = $missingDataItem;
			}
		}

		// Заменяем название
		$workerStatesData['unpaid'] = new MyOrdersTabItem(\Translations::t('Нужна оплата'), 'live-tabs__item-number--yellow');

		$this->statesData = $workerStatesData;
	}
}
