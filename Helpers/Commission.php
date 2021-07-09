<?php

/**
 * #6318 Класс-представление результата расчета комиссии.
 */
class Commission {

	/**
	 * Полная стоимость (стоимость для покупателя).
	 *
	 * @var float 
	 */
	public $price;

	/**
	 * Доля продавца.
	 *
	 * @var float 
	 */
	public $priceWorker;

	/**
	 * Доля Kwork (комиссия).
	 *
	 * @var float 
	 */
	public $priceKwork;

	/**
	 * Оборот между продавцом и покупателем.
	 *
	 * @var float 
	 */
	public $turnover;

	/**
	 * Диапазоны комиссий с расчетом долей продавца и Kwork.
	 * Используется только для отладки.
	 *
	 * @var \CommissionRange[] 
	 */
	public $ranges;

	/**
	 * Конструктор.
	 */
	public function __construct(float $price, float $priceWorker, float $priceKwork, float $turnover, array $ranges) {
		$this->price = $price;
		$this->priceWorker = $priceWorker;
		$this->priceKwork = $priceKwork;
		$this->turnover = $turnover;
		$this->ranges = $ranges;
	}

}
