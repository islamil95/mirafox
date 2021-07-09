<?php


namespace Order;


/**
 * Class MyOrdersTabItem.
 *
 * Хранит определяющие конкретный таб на страницах заказов пользователей данные и атрибуты.
 * Все переменные публичные для простоты (первая итерация, всё-таки),
 * ибо это в больше степени аналог struct из C++, нежели полноценный класс.
 *
 * @package Order
 */
class MyOrdersTabItem {
	public $title = '';
	public $ordersNumCssClasses = '';
	public $tabCssClasses = '';
	
	/**
	 * MyOrdersTabItem constructor.
	 *
	 * @param string $title
	 * @param string $ordersNumCssClasses
	 * @param string $tabCssClasses
	 */
	public function __construct(string $title, string $ordersNumCssClasses = '', string $tabCssClasses = '') {
		$this->title = $title;
		$this->ordersNumCssClasses = $ordersNumCssClasses;
		$this->tabCssClasses = $tabCssClasses;
	}
}
