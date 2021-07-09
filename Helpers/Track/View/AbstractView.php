<?php

namespace Track\View;

use Core\Traits\AuthTrait;
use Core\Traits\Templating\RenderViewTrait;
use Model\Track;
use Strategy\Track\GetTrackRecipientIdStrategy;
use Track\Type;

/**
 * Абстрактное представление трека
 *
 * Class AbstractView
 * @package Track\View
 */
abstract class AbstractView implements IView {

	use RenderViewTrait, AuthTrait;

	/**
	 * @var Track $track;
	 */
	protected $track;
	/**
	 * @var bool Отображать ли идентификатор трека
	 */
	private $isAddTrackIdAttribute;
	/**
	 * @var array Данные о треке для отображения
	 */
	private $viewData = [];

	/**
	 * AbstractView constructor.
	 * @param Track $track трек
	 */
	public function __construct(Track $track) {
		$this->track = $track;
		$this->isAddTrackIdAttribute = true;
		$this->viewData = $this->getViewData($this->getType(), $track->order->source_type);
	}

	/**
	 * Получить параметры для рендера
	 *
	 * @return array пареметры рендераа
	 */
	abstract protected function getParameters():array;

	/**
	 * Название шаблона
	 *
	 * @return string название шаблона
	 */
	abstract protected function getTemplateName():string;

	/**
	 * Трек не прочитан?
	 *
	 * @return bool
	 */
	protected function isUnread(): bool {
		if (Type::WORKER_INPROGRESS_CHECK == $this->track->type &&
			$this->track->order->isWorker($this->getUserId())) {
			return false;
		}
		if (Type::isCronType($this->track->type)) {
			return $this->track->order->isWorker($this->getUserId()) ? $this->track->cron_worker_unread : $this->track->unread;
		} else {
			$recipientId = (new GetTrackRecipientIdStrategy($this->track))->get();
			return ($this->getUserId() == $recipientId && $this->track->unread);
		}
	}

	/**
	 * Задать отображение идентификатора трека
	 *
	 * @param bool $isAddTrackIdAttribute отображать ли идентификатор трека
	 * @return $this
	 */
	public function setAddTrackIdAttribute(bool $isAddTrackIdAttribute) {
		$this->isAddTrackIdAttribute = $isAddTrackIdAttribute;
		return $this;
	}

	/**
	 * Получить данные об отображение идентификаторе трека
	 *
	 * @return bool
	 */
	public function getAddTrackIdAttribute():bool {
		return $this->isAddTrackIdAttribute;
	}

	/**
	 * Получить заголовок трека
	 *
	 * @return string|null заголовок
	 */
	protected function getTitle() {
		return $this->viewData["title"] ?: null;
	}

	/**
	 * Получить цвет
	 *
	 * @return string|null цвет
	 */
	protected function getColor() {
		return $this->viewData["color"] ?: null;
	}

	/**
	 * Получить иконку
	 *
	 * @return string|null иконка
	 */
	protected function getIcon() {
		return $this->viewData["icon"] ?: null;
	}

	/**
	 * Получить текст трека
	 *
	 * @return string|null текст
	 */
	protected function getText() {
		return $this->viewData[$this->getAccessKey()] ?: null;
	}

	/**
	 * Получить ключ для доступа к данным (отображать для покупателя или продавца)
	 *
	 * @return string ключ для получения данных
	 */
	protected function getAccessKey():string {
		return $this->track->order->isPayer($this->getUserId()) ? "payer" : "worker";
	}

	/**
	 * Получить данные о треке по типу трека
	 *
	 * @param string $type тип трека
	 * @param string $source_type источник заказа
	 * @return array данные
	 */
	protected function getViewData(string $type, string $source_type = null) : array {
		$params = $source_type ? ["source_type" => $source_type] : [];
		$data = Type::getTracksDesc($params);
		return $data[$type] ?: [];
	}

	/**
	 * Получить тип трека
	 *
	 * @return string тип трека
	 */
	protected function getType():string {
		return $this->track->type;
	}

	/**
	 * Получить дату
	 *
	 * @return int дата
	 */
	protected function getDate() {
		return $this->track->date_create;
	}

	/**
	 * @inheritdoc
	 */
	public function render():string {
		$parameters = $this->getParameters();
		$parameters["track"] = $this->track;
		$parameters["isUnread"] = $this->isUnread();
		$parameters["setTrackId"] = $this->getAddTrackIdAttribute();
		$parameters["title"] = $this->getTitle();
		$parameters["color"] = $this->getColor();
		$parameters["icon"] = $this->getIcon();
		$parameters["text"] = $this->getText();
		$parameters["date"] = $this->getDate();

		return $this->renderView($this->getTemplateName(), $parameters);
	}

	/**
	 * Получить данные для фронтенда, свойственные конкрентого этому View
	 *
	 * @return array Массив с данными
	 */
	public function getFrontendData() {
		return [];
	}

}