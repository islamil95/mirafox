<?php


namespace Track\View\Text;


use Converter\TrackFilesToJsonConverter;
use Model\User;
use Track\View\AbstractView;

/**
 * Сообщение
 *
 * Class TextView
 * @package Track\View\Text
 */
class TextView extends AbstractView {

	const DIRECTION_IN = "in";
	const DIRECTION_OUT = "out";

	/**
	 * @var array статус онлайн участников трека
	 */
	private static $isOnline = [];

	/**
	 * Автор сообщения
	 *
	 * @return User
	 */
	private function getAuthor():User {
		if ($this->track->order->isPayer($this->track->user_id)) { // Если покупатель
			return $this->track->order->payer;
		} else if ($this->track->order->isWorker($this->track->user_id)) { // Если продавец
			return $this->track->order->worker;
		}
		// Если это кто-то еще, например, модератор или арбирт
		return User::find($this->track->user_id);
	}

	/**
	 * Статус онлайн
	 *
	 * @return bool|mixed
	 */
	private function getOnline() {
		if (!isset(self::$isOnline[$this->track->user_id])) {
			self::$isOnline[$this->track->user_id] = \UserManager::checkOnlineTime($this->track->user_id);
		}
		return self::$isOnline[$this->track->user_id];
	}

	/**
	 * Получить направление
	 *
	 * @return string
	 */
	private function getDirection() {
		return ($this->getCurrentUserId() == $this->track->user_id) ?
			self::DIRECTION_OUT :
			self::DIRECTION_IN;
	}

	/**
	 * @inheritdoc
	 */
	protected function getParameters(): array {
		return [
			"author" => $this->getAuthor(),
			"direction" => $this->getDirection(),
			"isEditable" => $this->track->isEditable($this->getCurrentUserId()),
			"isRemovable" => $this->track->isRemovable($this->getCurrentUserId()),
			"isAuthorOnline" => $this->getOnline(),
			"jsonFiles" => json_encode(new TrackFilesToJsonConverter($this->track->files)),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTemplateName(): string {
		return "track/view/text/text";
	}

	/**
	 * @inheritdoc
	 */
	protected function isUnread(): bool {
		return $this->track->unread;
	}
}