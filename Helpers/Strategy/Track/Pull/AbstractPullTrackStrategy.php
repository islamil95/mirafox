<?php


namespace Strategy\Track\Pull;


use Illuminate\Database\Eloquent\Collection;
use Model\Order;
use Model\Track;
use Strategy\Track\AbstractTrackStrategy;
use Track\Type;

/**
 * Абстрактный класс для работы с пуш-уведомлениями
 *
 * Class AbstractPullTrackStrategy
 * @package Strategy\Track\Pull
 */
abstract class AbstractPullTrackStrategy extends AbstractTrackStrategy {

	/**
	 * @var string тип последнего трека
	 */
	protected $trackType;

	/**
	 * @var int идентификатор последнего трека
	 */
	protected $lastTrackId;

	/**
	 * @var bool включать ли последний трек
	 */
	protected $includeLastTrack = true;

	/**
	 * @var bool
	 */
	protected $forceReplace;

	public function __construct(Order $order, $trackType, $lastTrackId, $forceReplace) {
		parent::__construct($order);
		$this->trackType = $trackType;
		$this->lastTrackId = $lastTrackId;
		$this->forceReplace = $forceReplace;
		// Обычно выбираем все новые треки и сам последний, для замены.
		// Единственный случай, когда выбрать надо только новые, без последнего.
		// Это ситуация, когда у продавца последний трек 'create',
		// а у покупателя после 'create' есть ещё два системных трека.
		if (Type::PAYER_INPROGRESS_CANCEL_REQUEST == $this->trackType) {
			$this->includeLastTrack = false;
		}
	}

	protected function isForceReplace():bool {
		return $this->forceReplace;
	}

	protected function isNotForceReplace(): bool {
		return !$this->forceReplace;
	}

	/**
	 * Получить все треки начиная с последнего
	 *
	 * @return Collection|Track[]
	 */
	protected function getLastTracks() {
		$getLastTrackStrategy = new GetLastTrackStrategy($this->order, $this->trackType, $this->lastTrackId, $this->forceReplace);
		$lastTracks = $getLastTrackStrategy->get();
		return $lastTracks;
	}
}