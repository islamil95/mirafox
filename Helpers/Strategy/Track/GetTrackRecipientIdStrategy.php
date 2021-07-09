<?php


namespace Strategy\Track;


use Model\Track;
use Track\Type;

/**
 * Получить идентификатор пользователя, которому предназначается трек
 *
 * Class GetTrackRecipientIdStrategy
 * @package Strategy\Track
 */
class GetTrackRecipientIdStrategy extends AbstractTrackStrategy {

	private $track;

	public function __construct(Track $track) {
		parent::__construct($track->order);
		$this->track = $track;
	}

	/**
	 * @inheritdoc
	 */
	public function get() {
		$trackCreatorId = $this->track->user_id;
		$trackOpponentId = $this->order->isPayer($trackCreatorId) ? $this->order->worker_id : $this->order->USERID;
		// Трек предложения может меняться прямо по месту, при этом создатель его остается неизменным.
		// По текущему состоянию пытаемся определить текущего же получателя.
		if (Type::isExtra($this->track->type)) {
			switch ($this->track->status) {
				// Если предложение создано, то получатель - противная создателю сторона.
				// Отменить преложение может только его создатель, следовательно получатель - тоже противная создателю сторона.
				case \TrackManager::STATUS_NEW:
				case \TrackManager::STATUS_CANCEL:
					return $trackOpponentId;

				// Покупатель отказался от предложения, получатель - создатель этого самого предложения.
				case \TrackManager::STATUS_CLOSE:
					return $trackCreatorId;

				// Покупатель принял предложение, получатель - создатель этого самого предложения.
				case \TrackManager::STATUS_DONE:
					// Если создатель этого трека покупатель (заказал доп. опции),
					// то получатель - противная создателю сторона.
					// В противном случае это принятие предложения продавца, который его создатель,
					// а теперь и получатель.
					return $this->order->isPayer($trackCreatorId) ? $trackOpponentId : $trackCreatorId;

				// Сюда никогда не попадем.
				default:
					break;
			}
		}
		// Для обычных треков получатель - противная создателю трека сторона.
		return $trackOpponentId;
	}
}