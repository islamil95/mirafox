<?php


namespace Order;


/**
 * Class MyOrders.
 *
 * Класс для формирования табов на страницах моих заказов,
 * управляет набором табов и их состояниями.
 *
 * Сам по себе используется для страницы продавца и попутно служит базовым
 * для класса формирования табов на странице покупателя
 * (не стал множить сущности и создавать отдельный класс для покупателя, хотя это и неверно).
 *
 * Называется MyOrders, а не, скажем, MyOrdersTabs потому,
 * что класс может использоваться и для просто хранения статистики
 * по пользовательским состояниям заказов.
 * Это не очень хорошо, но достаточно для первой итерации.
 *
 * @package Order
 */
class MyOrders {
	/** @var bool для отладки, чтобы можно было учитывать все вкладки: и с заказами и без. */
	protected $debugRenderWithOrdersOnly = true;

	/** @var array Когда пользователь заходит на страницу его заказов,
	 * у него будет активен набор заказов с каким-то пользовательским состоянием
	 * (зависит от роли пользователя и наличия заказов с таким состоянием).
	 * Этот список определяет такие состояния и их порядок
	 * (если есть заказы с первым в списке состоянием, то будет активен их таб,
	 * если с первым нет, но есть со вторым - будет активен таб с ними и т.д., вплоть до "Все").
	 */
	protected $defaultActiveStates = [
		'active',
		'delivered',
		'completed',
		'cancelled',
		'all',
	];

	/** @var MyOrdersTabItem[] */
	protected $statesData = [];

	/** @var array Таблица соответствия настоящих состояний заказов
	 * (OrderManager::STATUS_INPROGRESS и проч.)
	 * с пользовательскими псевдосостояниями
	 * ('active', 'delivered' и проч.).*/
	static protected $statesRelations = [
		\OrderManager::STATUS_INPROGRESS => 'active',
		\OrderManager::STATUS_ARBITRAGE => 'delivered',
		\OrderManager::STATUS_CANCEL => 'cancelled',
		\OrderManager::STATUS_CHECK => 'delivered',
		\OrderManager::STATUS_DONE => 'completed',
		\OrderManager::STATUS_UNPAID => 'unpaid',
	];

	/**
	 * MyOrders constructor.
	 */
	public function __construct() {
		$this->statesData = [
			'all' => new MyOrdersTabItem(\Translations::t('Все')),
		];
	}

	/**
	 * Возвращает пользовательское псевдосостояние заказа по его настоящему состоянию.
	 *
	 * @param int $orderState
	 *
	 * @return bool|string false, если соответствующее пользовательское состояние не найдено.
	 */
	static public function getUserOrderStateFromOrderStatus(int $orderState) {

		if (isset(self::$statesRelations[$orderState])) {
			return self::$statesRelations[$orderState];
		}

		return false;
	}

	/**
	 * Увеличивает счетчики кол-ва заказов для указанного состояния заказа.
	 *
	 * @param int|string $state либо настоящее состояние заказов,
	 * 		либо пользовательское (строка 'active', 'delivered' и проч.).
	 * @param int $count добавляемое кол-во заказов.
	 */
	public function addStateOrdersCount($state, int $count) {
		$this->statesData['all']->count += $count;
	}

	/**
	 * Выставляет объект таба в активное состояние.
	 * Вызывающий код должен самостоятельно убедиться, что никакой другой таб не остаётся активным.
	 *
	 * @param string $userOrderState пользовательское состояние заказов.
	 */
	public function setActive(string $userOrderState) {
		if (isset($this->statesData[$userOrderState])) {
			$this->statesData[$userOrderState]->isActive = true;
			$this->statesData[$userOrderState]->tabCssClasses = 'active';
		}
	}

	/**
	 * Устанавливает класс css для элемента с кол-вом заказов таба.
	 *
	 * @param string $userOrderState пользовательское состояние заказов нужного таба.
	 * @param string $cssClass класс css.
	 */
	public function setOrdersNumCssClass(string $userOrderState, string $cssClass) {
		if (isset($this->statesData[$userOrderState])) {
			$this->statesData[$userOrderState]->ordersNumCssClasses = $cssClass;
		}
	}

	/**
	 * Возвращает кол-во заказов для указанного пользовательского их состояния.
	 *
	 * @param string $userOrderState
	 *
	 * @return int
	 */
	public function getUserStateOrdersCount(string $userOrderState): int {
		if (isset($this->statesData[$userOrderState])) {
			return (int)$this->statesData[$userOrderState]->count;
		}

		return 0;
	}

	/**
	 * Есть ли заказы для указанного пользовательского состояния заказов.
	 *
	 * @param string $userOrderState пользовательское состояние заказов.
	 *
	 * @return bool
	 */
	public function hasUserStateOrders(string $userOrderState): bool {
		if (isset($this->statesData[$userOrderState])) {
			return $this->statesData[$userOrderState]->count > 0;
		}

		return false;
	}

	/**
	 * Возвращает строку пользовательского состояния заказов,
	 * которое должно быть активным при первоначальном заходе пользователя на страницу с заказами.
	 *
	 * @return string
	 */
	public function getDefaultActiveUserOrdersState(): string {
		foreach($this->defaultActiveStates as $state) {
			if ($this->hasUserStateOrders($state)) {
				return $state;
			}
		}

		return end($this->defaultActiveStates);
	}

	/**
	 * Возвращает набор данных для табов только по тем состояниям, в которых есть заказы.
	 *
	 * @return MyOrdersTabItem[] ассоциативный массив с пользовательскими состояниями в качестве ключей:
	 *      'active' (MyOrdersTabItem) => объект таба "В работе"
	 *      'delivered' (MyOrdersTabItem) => объект таба "На проверке"
	 *      и т.д.
	 */
	public function getHasOrdersItems(): array {
		if (!$this->debugRenderWithOrdersOnly) {
			return $this->statesData;
		}

		$statesData = [];

		foreach($this->statesData as $userOrderState => $item) {
			if ($item->count > 0) {
				// Именно нужны клоны, потому что в дальнейшем свойства объектов
				// могут (будут) изменяться.
				$statesData[$userOrderState] = clone $item;
			}
		}

		return $statesData;
	}

	/**
	 * Возвращает простой ассоциативный массив вида $userOrderState => $ordersCount
	 *
	 * @param array $statsData массив данных, с которым работает этот класс.
	 *
	 * @return array
	 */
	static public function simpleUserOrderStatesStats(array $statsData): array {
		$stats = [];
		foreach($statsData as $userOrderState => $item) {
			$stats[$userOrderState] = $item->count;
		}

		return $stats;
	}
}
