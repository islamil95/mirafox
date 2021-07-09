<?php

/**
 * Объект хранит данные для пагинации
 */
class PaginationData {
	/**
	 * Номер первой записи на странице
	 * @var int
	 */
	public $start;

	/**
	 * Текущая страница
	 * @var int
	 */
	public $currentPage;

	/**
	 * Строка со ссылками навигации
	 * @var string
	 */
	public $pageLinks;

	/**
	 * Номер последней записи на странице
	 * @var int
	 */
	public $end;

	/**
	 * Всего записей
	 * @var int
	 */
	public $total;
}