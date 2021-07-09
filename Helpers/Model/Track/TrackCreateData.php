<?php


namespace Model\Track;

/**
 * Данные для создания сообщения
 * @package Model\Track
 */
class TrackCreateData {
	/**
	 * Идентификатор заказа, в котором создаётся трека
	 * @var int
	 */
	public $orderId = null;

	/**
	 * Тип создаваемого трека
	 * @var string
	 */
	public $type = null;

	/**
	 * Сообщение в треке
	 * @var null|string
	 */
	public $message = null;

	/**
	 * Причина отмены заказа
	 * @var string|null
	 */
	public $reasonType = null;

	/**
	 * Согласен ли с причиной отмены заказа agree|disagree
	 * @var string|null
	 */
	public $replyType = null;

	/**
	 * Статус трека new|close|done|cancel
	 * @var string|null
	 */
	public $status = null;

	/**
	 * Идентификатор покупателя (для UserKwork::set())
	 * @var int|null
	 */
	public $payerId = null;

	/**
	 * Идентификатор кворка (для UserKwork::set())
	 * @var int|null
	 */
	public $kworkId = null;

	/**
	 * Идентификатор пользователя - автора трека
	 * @var int|null
	 */
	public $userId = null;

	/**
	 * Идентификатор саппорта
	 * @var int|null
	 */
	public $supportId = null;

	/**
	 * Идентификатор статьи по арбитражам в базе KB
	 * @var int|null
	 */
	public $articleId = null;

	/**
	 * Идентификатор роли пользователя в статьях по арбитражам KB
	 * @var int|null
	 */
	public $roleId = null;

	/**
	 * Идентификатор цитируемого трека
	 * @var int|null
	 */
	public $quoteId = null;
}