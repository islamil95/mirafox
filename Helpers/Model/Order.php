<?php


namespace Model;

use App;
use Core\Traits\Formatter\PriceFormatTrait;
use Core\Traits\Price\TotalAmountTrait;
use Helper;
use Helpers\Rating\AutoRating\GetAutoRatingInfoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Order\MyOrders;
use Order\Stages\OrderStageManager;
use Order\Stages\OrderStageOfferManager;
use Strategy\Order\GetOrderPauseDurationStrategy;
use Strategy\Track\GetHideQuantityStrategy;
use Track\Type;
use TrackManager;

/**
 * Модель заказа
 *
 * @mixin \EloquentTypeHinting
 *
 * @property int OID
 * @property int USERID
 * @property int PID
 * @property int worker_id
 * @property int time_added
 * @property int status
 * @property int stime
 * @property float price
 * @property float crt
 * @property int cltime
 * @property int duration
 * @property int extended_time Количество секунд продления заказа администратором
 * @property int deadline
 * @property int count
 * @property string kwork_title
 * @property int kwork_days
 * @property int workTime
 * @property string date_inprogress
 * @property string date_arbitrage
 * @property string date_cancel
 * @property string date_check
 * @property string date_done
 * @property int in_work
 * @property int data_provided предоставлены ли покупателем данные по заказу
 * @property int last_track_id
 * @property int project_id
 * @property string portfolio_type
 * @property string data_provided_hash
 * @property bool is_quick
 * @property string rating_type тип отзыва у сделанного заказа: new - не определен, good - оставлен положительный отзыв, bad - оставлен отрицательный отзыв, first - без отзыва первичный, second - без отзыва повторный
 * @property int currency_id
 * @property float currency_rate
 * @property string source_type
 * @property bool rating_ignore
 * @property int expires
 * @property int payer_unread_tracks Количество непрочтенных сообщений для покупателя от продавца или арбитра
 * @property int worker_unread_tracks Количество непрочтенных сообщений для продавца от покупателя или арбитра
 * @property float worker_amount Сумма, которую получит продавец в своей валюте
 * @property float payer_amount Сумма, которую заплатил покупатель в своей валюте
 * @property string bonus_text
 * @property float stages_price Актуальная цена для покупателя в зависимости от состояния этапного заказа
 * @property float stages_crt Актуальная цена для продавца в зависимости от состояния этапного заказа
 * @property bool has_stages Заказ поэтапный (есть этапы)
 * @property bool has_payer_stages В заказе есть безакцептные этапы от покупателя (это добавляет причину отмены заказа)
 * @property bool show_as_inprogress_for_worker Отображать статус заказа как В работе в то время как он на проверке для продавца
 * @property float initial_offer_price Стоимость изначального предложения продавца
 * @property bool restarted Заказ был перезапущен в работу из состояния "Выполнен" со сбросом флага "Взят в работу"
 * @property int initial_duration Изначальный срок предложения продавца в секундах
 *
 * Связанные модели
 * @property-read User payer покупатель
 * @property-read User worker продавец
 * @property-read Kwork kwork кворк
 * @property-read OrderData data данные заказа
 * @property-read Collection|Track[] tracks треки заказа
 * @property-read Rating review отзыв
 * @property-read Collection|OrderNames[] orderNames
 * @property-read \Model\Track lastTrack Последний важный трек
 * @property-read \Model\Tips $tips Модель чаевых (бонусов)
 */
class Order extends Model {
	use PriceFormatTrait;
	use TotalAmountTrait;

	/**
	 * Таблица заказов
	 */
	const TABLE_NAME = "orders";

	/**
	 * Идетификатор зазказа
	 */
	const FIELD_OID = "OID";

	/**
	 * Идетификатор покупателя
	 */
	const FIELD_USERID = "USERID";

	/**
	 * Идентификатор кворка
	 */
	const FIELD_PID = "PID";

	/**
	 * Идентификатор продавца
	 */
	const FIELD_WORKER_ID = "worker_id";

	/**
	 * Время создания заказа
	 */
	const FIELD_TIME_ADDED = "time_added";

	/**
	 * Статус заказа
	 */
	const FIELD_STATUS = "status";

	/**
	 * Время начала заказа orders.stime
	 */
	const FIELD_STIME = "stime";

	/**
	 * Общая цена заказа
	 */
	const FIELD_PRICE = "price";

	/**
	 * Средства которые получит продавец после выполнения заказа
	 */
	const FIELD_CRT = "crt";

	const FIELD_CLTIME = "cltime";
	const FIELD_DURATION = "duration";
	/**
	 * Количество секунд продления заказа администратором
	 */
	const FIELD_EXTENDED_TIME = "extended_time";
	const FIELD_DEADLINE = "deadline";
	const FIELD_COUNT = "count";
	const FIELD_KWORK_TITLE = "kwork_title";
	const FIELD_KWORK_DAYS = "kwork_days";
	const FIELD_WORK_TIME = "workTime";
	const FIELD_DATE_INPROGRESS = "date_inprogress";
	const FIELD_DATE_ARBITRAGE = "date_arbitrage";
	const FIELD_DATE_CANCEL = "date_cancel";
	const FIELD_DATE_CHECK = "date_check";
	const FIELD_DATE_DONE = "date_done";
	const FIELD_IN_WORK = "in_work";
	/**
	 * Пометка того что заказчик предоставил необходимые для выполнения заказа данные
	 */
	const FIELD_DATA_PROVIDED = "data_provided";

	/**
	 * Id последнего трека в заказе
	 */
	const FIELD_LAST_TRACK_ID = "last_track_id";

	const FIELD_PROJECT_ID = "project_id";
	const FIELD_PORTFOLIO_TYPE = "portfolio_type";
	const FIELD_DATA_RPOVIDED_HASH = "data_provided_hash";
	/**
	 * @deprecated
	 */
	const FIELD_IS_QUICK = "is_quick";

	/**
	 * Поле состояния рейтинга заказа.
	 * good - оставлен положительный отзы.
	 * bad - оставлен отрицательный отзыв
	 * first - без отзыва первичный
	 * second - без отзыва повторный
	 * new - новый
	 */
	const FIELD_RATING_TYPE = "rating_type";

	/**
	 * Идентификатор валюты
	 */
	const FIELD_CURRENCY_ID = "currency_id";

	/**
	 * Курс валюты на момент заказа
	 */
	const FIELD_CURRENCY_RATE = "currency_rate";

	/**
	 * Откуда был создан заказ
	 */
	const FIELD_SOURCE_TYPE = "source_type";

	/**
	 * Игнорировать ли заказ при расчете рейтинга
	 */
	const FIELD_RATING_IGNORE = "rating_ignore";


	const FIELD_EXPIRES = "expires";

	/**
	 * Количество непрочтенных сообщений для покупателя от продавца или арбитра
	 */
	const FIELD_PAYER_UNREAD_TRACKS = "payer_unread_tracks";

	/**
	 * Количество непрочтенных сообщений для продавца от покупателя или арбитра
	 */
	const FIELD_WORKER_UNREAD_TRACKS = "worker_unread_tracks";

	/**
	 * Описание бонуса к заказу
	 */
	const FIELD_BONUS_TEXT = "bonus_text";


	/**
	 * 	Сумма, которую получит продавец в своей валюте
	 */
	const FIELD_WORKER_AMOUNT = "worker_amount";

	/**
	 * 	Сумма, которую заплатил покупатель в своей валюте
	 */
	const FIELD_PAYER_AMOUNT = "payer_amount";

	/**
	 * Актуальная цена в зависимости от состояния этапного заказа
	 */
	const FIELD_STAGES_PRICE = "stages_price";

	/**
	 * Заказ этапный (есть этапы)
	 */
	const FIELD_HAS_STAGES = "has_stages";

	/**
	 * В заказе есть безакцептные этапы от покупателя (это добавляет причину отмены заказа)
	 */
	const FIELD_HAS_PAYER_STAGES = "has_payer_stages";

	/**
	 * Отображать статус заказа как "В работе" в то время как он "на проверке" для продавца
	 */
	const FIELD_SHOW_AS_INPROGRESS_FOR_WORKER = "show_as_inprogress_for_worker";

	/**
	 * Стоимость изначального предложения продавца
	 */
	const FIELD_INITIAL_OFFER_PRICE = "initial_offer_price";

	/**
	 * Заказ был перезапущен в работу из состояния "Выполнен" со сбросом флага "Взят в работу"
	 */
	const FIELD_RESTARTED = "restarted";

	/**
	 * Изначальный срок предложения продавца в секундах
	 */
	const FIELD_INITIAL_DURATION = "initial_duration";

	protected $table = self::TABLE_NAME;
	protected $primaryKey = self::FIELD_OID;
	public $timestamps = false;
	private $extrasCountInDB = 0;

	/**
	 * Коллекция доступных для докупки опций кворка (для кеширования)
	 *
	 * @var \Illuminate\Support\Collection|\Model\Extra[]
	 */
	private $upgradeExtras;

	/**
	 * Тип объёма в котором ведётся заказ
	 * @var \Model\VolumeType
	 */
	private $orderVolumeType = null;

	public function inWorkTimeCancelString():string {
		if ($this->isInWork() ||
			($this->timeFromOrder() > $this->timeToAttention())) {
			return "";
		}
		return \Helper::timeLeft($this->timeToAttention(), false, false);
	}

	/**
	 * Пора ли выводить сообщение о том, что нудно взять заказ в работу
	 *
	 * @return bool
	 */
	public function inWorkAttention():bool {
		return  $this->isNotInWork() && ($this->timeFromOrder() < $this->timeToAttention());
	}

	/**
	 * Cколько времени на принятие заказа в работу
	 *
	 * @return mixed|string|null
	 */
	public function timeToTake() {
		if ($this->isInWork()) {
			return null;
		}
		return \Helper::autoCancelString(\Helper::AUTOCANCEL_MODE_TEXT_IN, $this->restarted, $this->hoursToGetInwork());
	}

	/**
	 * Сколько времени прошло с момента подтверждения заказа покупателем, учитывая паузы
	 *
	 * Метод не учитывает паузы для перезапущенных заказов.
	 * Возвращает количество секунд.
	 * @return false|float|int
	 */
	public function timeFromOrder() {
		$timeFromOrder = \Helper::getAutoChancelHours($this->restarted) * \Helper::ONE_HOUR - (time() - strtotime($this->date_inprogress));
		if (!$this->restarted) {
			$getOrderPauseDurationStrategy = new GetOrderPauseDurationStrategy($this);
			//Если кворк был на паузе нужно учитывать это время
			$pauseDuration = $getOrderPauseDurationStrategy->get();
		} else {
			$pauseDuration = 0;
		}
		return $timeFromOrder + $pauseDuration;
	}

	/**
	 * Количество оставшихся часов с момента создания заказа до взятия в работу учитывая паузы
	 * 
	 * @return int
	 */
	public function hoursToGetInwork() {
		if ($this->restarted) {
			$autoCancelTime = OrderStageManager::RESTARTED_AUTOCANCEL_THRESHOLD;
			$lastRestartTrack = $this->tracks->whereIn(Track::FIELD_TYPE, Type::getOrderRestartTypes())->last();
			$timeFromCreated = 0;
			if ($lastRestartTrack instanceof Track) {
				$timeFromCreated = time() - $lastRestartTrack->date_create_unix();
			} else {
				\Log::daily("Restarted order {$this->OID} without restart track", "error");
			}
			// Для перезапущенных заказов не считается время на паузы
			$timeLeft = $autoCancelTime - $timeFromCreated;
		} else {
			$timeFromCreated = time() - intval($this->stime);
			$getOrderPauseDurationStrategy = new GetOrderPauseDurationStrategy($this);
			$pauseDuration = $getOrderPauseDurationStrategy->get();
			$timeLeft = \Helper::getAutoChancelHours($this->restarted) * \Helper::ONE_HOUR - $timeFromCreated + $pauseDuration;
		}
		//если время уже прошло
		if ($timeLeft < 0) {
			$timeLeft = 0;
		}
		return ceil($timeLeft / \Helper::ONE_HOUR);
	}

	/**
     * Количество оставшегося времени до автопринятия
     *
     * @return string
	 */
    public function timeUntilAutoaccept() {
        $dateCheck = \OrderManager::getDateForAutoAccept($this);
        $dateCheck = $dateCheck - (int)App::config("kwork.autoaccept_days") * Helper::ONE_DAY;
        $dateCheck = date("Y-m-d H:i:s", $dateCheck);

        return  \OrderManager::timeUntilAutoaccept($dateCheck, $this->kwork->lang);
	}

	/**
	 * Количество секунд с момента заказа, когда у продавца появляется уведомление о необходимости взять заказ в работу
	 *
	 * @return float|int
	 */
	public function timeToAttention() {
		return \Helper::ONE_HOUR * \App::config("kwork.need_worker_new_inwork_hours");
	}

	/**
	 * Скрываем скопированные из диалогов сообщения в треке заказа
	 * @return $this
	 */
	public function initHiddenConversation() {
		$hideConversationState = "none";
		$this->tracks->each(function($track, $index) use (&$hideConversationState) {
			/**
			 * @var Track $track
			 */
			$track->setHiddenConversation("");
			if ($track->type == Type::FROM_DIALOG) {
				$hideConversationState = "started";
				return true; // переходим к следующему сообщению
			}

			if ($track->type == Type::TEXT) {
				if ($hideConversationState == "started") {
					$hideConversationState = "proceed";
					$track->setHiddenConversation("start");
				}

				if ($hideConversationState == "proceed") {
					$track->setHiddenConversation("proceed");
				}

				if ($hideConversationState == "proceed" &&
					($index + 1 == $this->tracks->count() ||
						$this->tracks->get($index + 1)->type != Type::TEXT)) {
					$hideConversationState = "ended";
					$track->setHiddenConversation("end");
				}
			} else {
				$hideConversationState = "ended";
			}
		});

		$hideQuantity = (new GetHideQuantityStrategy($this))->get();
		$counter = 0;
		$this->tracks->each(function ($track, $key) use ($hideQuantity, &$counter) {
			/**
			 * @var Track $track
			 */
			if (empty($track->getHiddenConversation())) {
				if ($counter < $hideQuantity) {
					$track->setHide(true);
				}
				$counter++;
			}
		});

		return $this;
	}

	/**
	 * Просрочен ли заказ
	 *
	 * @return bool
	 */
	public function late():bool {
		if (!$this->deadline) {
			return false;
		}

		$endTime = time();
		if ($this->status == \OrderManager::STATUS_DONE) {
			$endTime = strtotime($this->date_done);
		} elseif ($this->status == \OrderManager::STATUS_CANCEL) {
			$endTime = strtotime($this->date_cancel);
		} elseif ($this->status == \OrderManager::STATUS_CHECK) {
			$endTime = strtotime($this->date_check);
		} elseif ($this->status == \OrderManager::STATUS_ARBITRAGE) {
			$endTime = strtotime($this->date_arbitrage);
		}
		return $this->deadline < $endTime;
	}

	/**
	 * Принадлежит ли заказ пользователю с этим идентификатором
	 *
	 * @param int $userId идентификатор пользователя
	 * @return bool
	 */
	public function isBelongToPayerOrWorker($userId) {
		return $this->isWorker($userId) || $this->isPayer($userId);
	}

	/**
	 * Не принадлежит ли заказ пользователю с этим идентификатором
	 *
	 * @param int $userId идентификатор пользователя
	 * @return bool
	 */
	public function isNotBelongToPayerOrWorker($userId) {
		return ! $this->isBelongToPayerOrWorker($userId);
	}

	/**
	 * Заказ свзан с продацом
	 *
	 * @param int $userId идентификатор проверяемого пользователя
	 * @return bool
	 */
	public function isWorker($userId) {
		return $this->worker_id == $userId;
	}

	/**
	 * Заказа связан с покупателем
	 *
	 * @param int $userId идентификатор проверяемого пользователя
	 * @return bool
	 */
	public function isPayer($userId) {
		return $this->USERID == $userId;
	}

	public function isNeedHideAddExtrasList():bool {

		return true;
	}

	/**
	 * Сгруппированные опции заказа без опий объема
	 *
	 * @return \Model\GroupedExtra[]
	 */
	public function getGroupedExtrasWithoutVolume() {
		$grouped = [];
		$extrasWithoutVolume = $this->extrasWithoutVolume()
			->groupBy(OrderExtra::FIELD_EXTRA_ID);
		/**
		 * @var \Illuminate\Support\Collection|\Model\OrderExtra[] $extras
		 */
		foreach ($extrasWithoutVolume as $extraId => $extras) {
			$groupedExtra = new GroupedExtra();
			$groupedExtra->extraId = $extraId;
			$groupedExtra->title = $extras->first()->extra_title;
			$groupedExtra->id = $extras->first()->id;
			$groupedExtra->count = $extras->sum(function (OrderExtra $extra) {
				return $extra->count;
			});
			$groupedExtra->payerPrice = $extras->sum(function (OrderExtra $extra) {
				return $extra->buyerPrice();
			});
			$groupedExtra->workerPrice = $extras->sum(function (OrderExtra $extra) {
				return $extra->workerPrice();
			});
			$groupedExtra->duration = $extras->sum(function (OrderExtra $extra) {
				return $extra->extra_duration;
			});
			$grouped[] = $groupedExtra;
		}

		return $grouped;
	}

	public function isRu():bool {
		return $this->currency_id == CurrencyModel::RUB;
	}

	public function isNotRu():bool {
		return $this->currency_id == CurrencyModel::USD;
	}

	/**
	 * Цена кворка для продавца
	 * (тут цена должна считаться независимо от order->crt,
	 * потому что бывает уменьшение стоимости заказа при арбитраже с частичным возвратом)
	 *
	 * @return string
	 */
	public function kworkWorkerPrice() {
		$kworkBuyerPrice = (float)$this->kworkBuyerPrice();
		$extrasBuyerPrice = $this->getExtrasPricesForBuyer();

		$orderBuyerFullPrice = $kworkBuyerPrice + $extrasBuyerPrice;

		$fullOrderCommissionPercent = $this->crt / $this->price;

		$fullOrderWorkerPrice = $orderBuyerFullPrice * $fullOrderCommissionPercent;

		$kworkWorkerPrice = $fullOrderWorkerPrice - $this->getExtrasPricesForWorker();

		return $this->formatPrice($kworkWorkerPrice);
	}

	/**
	 * Цена кворка для покупателя
	 * (тут цена должна считаться независимо от order->price,
	 * потому что бывает уменьшение стоимости заказа при арбитраже с частичным возвратом)
	 *
	 * @return string
	 */
	public function kworkBuyerPrice() {
		$price = $this->getCurrentKworkPrice();

		if ($this->processLikeVolumedKwork()) {
			$totalVolume = $this->data->volume;
			$volumeExtras = $this->extrasWithVolume();
			$buyerPrice = 0;
			if ($volumeExtras) {
				foreach($volumeExtras as $extra) {
					if (!is_null($extra->volume)) {
						$totalVolume -= $extra->volume;
						$buyerPrice += $this->kwork->getVolumedPrice($extra->volume, $price);
					}
				}
			}
			if ($totalVolume > 0) {
				$buyerPrice += $this->kwork->getVolumedPrice($totalVolume, $price);
			}

		} else {
			$buyerPrice = $this->getTotalAmount($price, $this->count);
		}

		return $this->formatPrice($buyerPrice);
	}

	/**
	 * Получение суммарной стоимости опций для продавца
	 *
	 * @return float
	 */
	public function getExtrasPricesForWorker() {
		return 0;
	}

	/**
	 * Получение суммарной цены опций для покупателя
	 *
	 * @return float
	 */
	public function getExtrasPricesForBuyer() {
		return 0;
	}

	public function isDone():bool {
		return $this->status == \OrderManager::STATUS_DONE;
	}

	public function isNotDone():bool {
		return ! $this->isDone();
	}

	public function isNew():bool {
		return $this->status == \OrderManager::STATUS_NEW;
	}

	public function isNotNew():bool {
		return ! $this->isNew();
	}

	public function isCancel():bool {
		return $this->status == \OrderManager::STATUS_CANCEL;
	}

	public function isNotCancel():bool {
		return ! $this->isCancel();
	}

	public function isInProgress():bool {
		return $this->status == \OrderManager::STATUS_INPROGRESS;
	}

	public function isNotInProgress() {
		return ! $this->isInProgress();
	}

	/**
	 * Количество сообщений от продавца в треке
	 *
	 * @return int количество сообщений
	 */
	public function getWorkerMessagesCount() {
		return Track::where(Track::FIELD_USER_ID, $this->worker_id)
			->where(Track::FIELD_ORDER_ID, $this->OID)
			->where(Track::FIELD_MESSAGE, "<>", null)
			->where(Track::FIELD_DATE_CREATE, ">", date("Y-m-d H:i:s", $this->stime))
			->where(Track::FIELD_TYPE, "text")
			->count();
	}

	/**
	 * Разрешено ли покупателю принять заказ
	 *
	 * @return bool
	 */
	public function isAllowedAccept() {
		if (!$this->isDone() && !$this->isCancel() && !$this->isUnpaid()
			&& ($this->data_provided
			|| $this->isCheck()
			|| ($this->isInProgress() && $this->in_work)
			|| $this->isInArbitrage()
			|| (!$this->in_work && $this->isInProgress() && $this->getWorkerMessagesCount() > 0))
		) {
			return true;
		}
	}

	/**
	 * Находится ли заказ в статусе доступном для докупки опций или апгрейда пакета
	 *
	 * @return bool
	 */
	public function isInBuyStatus():bool {
		$allowedToBuyStatuses = [
			\OrderManager::STATUS_INPROGRESS,
			\OrderManager::STATUS_CHECK,
			\OrderManager::STATUS_ARBITRAGE,
		];
		return in_array($this->status, $allowedToBuyStatuses);
	}

	/**
	 * Заказ находится в статусе в котором недоступна докупка опции или апгрейд пакетов
	 *
	 * @return bool
	 */
	public function isNotInBuyStatus():bool {
		return ! $this->isInBuyStatus();
	}

	public function isInArbitrage():bool {
		return $this->status == \OrderManager::STATUS_ARBITRAGE;
	}

	public function isInWork():bool {
		return $this->in_work == 1;
	}

	public function isNotInWork():bool {
		return ! $this->isInWork();
	}

	public function isCheck():bool {
		return $this->status == \OrderManager::STATUS_CHECK;
	}

	public function isNotCheck():bool {
		return ! $this->isCheck();
	}

	public function isArbitrage(): bool {
		return $this->status == \OrderManager::STATUS_ARBITRAGE;
	}

	public function isNotArbitrage(): bool {
		return ! $this->isArbitrage();
	}

	/**
	 * Находится ли заказ в статусе "Требует оплаты"
	 *
	 * @return bool
	 */
	public function isUnpaid() {
		return $this->status == \OrderManager::STATUS_UNPAID;
	}

	/**
	 * Не находится в статусе "Требует оплаты"
	 *
	 * @return bool
	 */
	public function isNotUnpaid() {
		return ! $this->isUnpaid();
	}

	public function getCreateDate() {
		return $this->stime;
	}

	/**
	 * Получить максимальный объём, который можно указать при дозаказе опций
	 * @return float|int|null
	 */
	public function maxOrderVolume() {
		// Масимальный объем который можно указать при дозаказе опций
		$kworkPrice = $this->getCurrentKworkPrice();

		if ($kworkPrice == 0 || $this->data->kwork_volume == 0) {
			return null;
		}

		$kworkVolume = $this->getVolumeInCustomType($this->data->kwork_volume);
		$multiplier = $this->data->kwork_volume / $kworkVolume;

		$lang = \Translations::getLangByCurrencyId($this->currency_id);
		$maxTotal = \App::config("order.volume_max_total_$lang");
		return floor($maxTotal / $kworkPrice) * $this->data->kwork_volume * $multiplier;
	}

	/**
	 * Получение языка заказа (по валюте заказа)
	 *
	 * @return string
	 */
	public function getLang() {
		return \Translations::getLangByCurrencyId($this->currency_id);
	}

	public function isExplainDate():bool {
		return $this->stime > 1461700000;
	}

	public function isNotExplainDate():bool {
		return ! $this->isExplainDate();
	}

	public function getFilesCount() {
		return $this->tracks->sum(function($track) {
			/**
			 * @var Track $track;
			 */
			return $track->files->count();
		});
	}

	public function getOrderTitleForUser($userId): string {
		if ($this->orderNames->isEmpty()) {
			return $this->kwork_title;
		}
		/**
		 * @var OrderNames|null $orderName
		 */
		$orderName = $this->orderNames->whereIn(OrderNames::FIELD_USER_ID, $userId)->first();
		if ($orderName) {
			return $orderName->order_name;
		}
		return $this->kwork_title;
	}

	/**
	 * Получение переименованного названия заказа для покупателя
	 *
	 * @return string
	 */
	public function getPayerOrderName() {
		return $this->getOrderTitleForUser($this->USERID);
	}

	/**
	 * Получение переименованного названия заказа для продавца
	 *
	 * @return string
	 */
	public function getWorkerOrderName() {
		return $this->getOrderTitleForUser($this->worker_id);
	}


	/**
	 * Предложен ли покупателю апгрейд пакетов
	 *
	 * @return bool
	 */
	public function isBetterPackageExtraSuggested(): bool {
		if ($this->relationLoaded("tracks")) {
			foreach ($this->tracks as $track) {
				if (TrackManager::STATUS_NEW === $track->status
					&&
					Type::EXTRA === $track->type
					&&
					$track->upgradePackageExtra
				) {
					return true;
				}
			}

			return false;
		}

		return $this
			->tracks()
			->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
			->where(Track::FIELD_TYPE, Type::EXTRA)
			->has("upgradePackageExtra")
			->exists();
	}

	/**
	 * Треки в заказе
	 *
	 * @return HasMany
	 */
	public function tracks() {
		return $this
			->hasMany(Track::class, Track::FIELD_ORDER_ID, self::FIELD_OID)
			->orderBy(Track::FIELD_ID);
	}

	public function payer() {
		return $this->hasOne(User::class, User::FIELD_USERID, self::FIELD_USERID);
	}

	public function worker() {
		return $this->hasOne(User::class, User::FIELD_USERID, self::FIELD_WORKER_ID);
	}

	public function kwork() {
		return $this->hasOne(Kwork::class, Kwork::FIELD_PID, self::FIELD_PID);
	}

	public function data() {
		return $this->hasOne(OrderData::class, OrderData::FIELD_ORDER_ID, self::FIELD_OID);
	}

	public function review() {
		return $this->hasOne(Rating::class, Rating::FIELD_ORDER_ID, self::FIELD_OID);
	}

	public function tips() {
		return $this->hasOne(Tips::class, Tips::F_ORDER_ID, self::FIELD_OID);
	}

	public function orderNames() {
		return $this->hasMany(OrderNames::class, OrderNames::FIELD_ORDER_ID, self::FIELD_OID);
	}

	/**
	 * Связь с последним важным треком
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function lastTrack() {
		return $this->hasOne(Track::class, Track::FIELD_ID, self::FIELD_LAST_TRACK_ID);
	}

	/**
	 * Найти и пометить первую доработку
	 */
	public function initFirstRework() {
		$firstRework = $this->tracks
			->whereIn(Track::FIELD_TYPE, Type::reworkTypes())
			->first();
		if ($firstRework instanceof Track) {
			$firstRework->setIsFirstRework(true);
		}
	}

	/**
	 * Найти и пометить первое сообщение покупателя после превышения порогового значения для категории
	 */
	public function initThresholdMessage() {
		$threshold = 100;

		$afterPay = false;
		$payerMessagesCount = 0;
		foreach ($this->tracks as $track) {
			if ($track->type == Type::PAYER_NEW_INPROGRESS) {
				$afterPay = true;
			}
			if ($afterPay && $track->type == Type::TEXT && $track->user_id == $this->USERID) {
				$payerMessagesCount++;
				if ($payerMessagesCount > $threshold) {
					$track->setIsMessageThreshold(true);
					break;
				}
			}
		}
	}

	/**
	 * Получение типа отмены заказа
	 * Внимание! Если использовать сразу после создания трека через TrackManager::create(),
	 * то в заказе может быть неактуальный last_track_id и соответственно неверный вывод
	 *
	 * @return int
	 * TrackManager::CANCEL_REASON_BAD_SERVICE
	 * TrackManager::CANCEL_REASON_BAD_SERVICE_REVIEW
	 * TrackManager::CANCEL_REASON_BAD_REVIEW
	 * TrackManager::CANCEL_REASON_NORMAL
	 */
	public function getCancelReasonMode() {
		$legacyAdaptedData = [];
		$lastTrack = $this->lastTrack;
		if ($lastTrack) {
			$legacyAdaptedData = $lastTrack->toArray();
		}

		$legacyAdaptedData["late"] = $this->late();
		$legacyAdaptedData["in_work"] = $this->in_work;

		return TrackManager::getCancelReasonMode($legacyAdaptedData);
	}

	/**
	 * Дата окончания работы над заказом date_done или date_cancel в зависимости от статуса
	 *
	 * @return string|null
	 */
	public function getFinishDate() {
		$date = null;
		if ($this->status == \OrderManager::STATUS_DONE) {
			$date = $this->date_done;
		} elseif ($this->status == \OrderManager::STATUS_CANCEL) {
			$date = $this->date_cancel;
		}
		return $date;
	}

	/**
	 * Считаем количество вхождений статуса "worker_inprogress_cancel_reject" (Продавец не согласился на отмену заказа)
	 *
	 * @return int
	 */
	public function inprogressCancelRejectCounter(): int {
		$counter = 0;
		$this->tracks->each(function ($track) use (&$counter) {
			if ($track->type == "worker_inprogress_cancel_reject") {
				$counter++;
			}
		});
		return $counter;
	}

	/**
	 * Находится ли сейчас заказ в состоянии запроса отмены
	 *
	 * @return bool
	 */
	public function isInCancelRequest():bool {
		foreach ($this->tracks as $track) {
			if (Type::isInprogressCancel($track->type) && $track->status == TrackManager::STATUS_NEW) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Есть/был ли у заказа автоматический отзыв
	 *
	 * @return bool
	 */
	public function hasAutoRating(): bool {
		return (bool)(new GetAutoRatingInfoService())->byOrderId(
			$this->{self::FIELD_OID}
		)->getAutoMode();
	}

	/**
	 * Этапы заказа на доработке
	 *
	 * @return Collection|OrderStage[]
	 */
	public function getRejectedStages(): Collection {
		return new Collection();
	}

	/**
	 * Является ли поданый идентификатор идентификатором покупателя или продавца
	 *
	 * @param int|null $userId Иденфтикатор пользователя
	 *
	 * @return bool
	 */
	public function isWorkerOrPayer($userId) {
		return $this->worker_id == $userId || $this->USERID == $userId;
	}

	/**
	 * Предложения по изменению этапов заказа покупателя на согласовании
	 *
	 * @return Collection|OrderStageOffer[]
	 */
	public function getPayerOrderStageOffers() {
		return $this->stageOffers
			->where(OrderStageOffer::FIELD_USER_ID, $this->USERID)
			->where(OrderStageOffer::FIELD_STATUS, OrderStageOffer::STATUS_NEW);
	}

	/**
	 * Предложения по изменению этапов заказа продавца на согласовании
	 *
	 * @return Collection|OrderStageOffer[]
	 */
	public function getWorkerOrderStageOffers() {
		return $this->stageOffers
			->where(OrderStageOffer::FIELD_USER_ID, $this->worker_id)
			->where(OrderStageOffer::FIELD_STATUS, OrderStageOffer::STATUS_NEW);
	}

	/**
	 * Получение оплаченной покупателем суммы
	 *
	 * @return float
	 */
	public function getPaidOrderStagesSum() {
		return 0;
	}

	/**
	 * Выдает поля модели в качестве stdClass в том формате в котором было принято ранее
	 */
	public function getLegacyStdClass() {
		$fields = $this->getAttributes();
		$fields["id"] = $this->OID;
		$fields["kworkId"] = $this->PID;
		$fields["userId"] = $this->USERID;
		$fields["workerId"] = $this->worker_id;
		$fields["totalprice"] = $this->price;
		return (object) $fields;
	}

	/**
	 * Находится ли заказ в состоянии "в работе" с точки зрения продавца
	 * (для этапных заказов)
	 *
	 * @return bool
	 */
	public function isInprogressForWorker() {
		return $this->status == \OrderManager::STATUS_INPROGRESS;
	}

	/**
	 * Можно ли сейчас покупателю резервировать средства под этапы
	 *
	 * @return bool
	 */
	public function isCanStagesReserved() {
		if (!$this->has_stages) {
			return false;
		}
		return $this->isStagesEditable();
	}

	/**
	 * Заказ находится в статусе при котором возможно редактирование/добавление этапов
	 *
	 * @return bool
	 */
	public function isStagesEditable() {
		if ($this->isNew()) {
			return false;
		}
		$reservedStageSum = $this->getReservedStages()->sum(OrderStage::FIELD_PAYER_PRICE);
		$paidStageSum = $this->getPaidStages()->sum(OrderStage::FIELD_PAYER_PRICE);
		$customMaxPrice = \KworkManager::getCustomMaxPrice($this->getLang()) - $reservedStageSum - $paidStageSum;
		//  Минимальная общая стоимость при добавлении этапов
		$customMinPriceAdd = \KworkManager::getCustomMinPrice($this->getLang());

		//Максимальная общая стоимость при добавлении этапов
		$customMaxPriceAdd = $customMaxPrice - $this->getNotReservedStages()->sum(OrderStage::FIELD_PAYER_PRICE);

		if ($customMaxPriceAdd < $customMinPriceAdd) {
			return false;
		}

		if ($this->isNotCancel()) {
			return true;
		}

		return $this->isCancel() && $this->lastTrack && $this->lastTrack->type == Type::CRON_UNPAID_CANCEL;
	}

	/**
	 * Получение этапов с заразервированной оплатой
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|OrderStage[]
	 */
	public function getReservedStages() {
		return new Collection();
	}

	/**
	 * Получение зарезервированных этапов не на проверке
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|OrderStage[]
	 */
	public function getReservedNotCheckStages() {
		return new Collection();
	}

	/**
	 * Получение незарезервированных этапов
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|OrderStage[]
	 */
	public function getNotReservedStages() {
		return new Collection();
	}

	/**
	 * Есть этапы для продолжения работы по заказа
	 *
	 * @return bool
	 */
	public function hasStagesToWork() {
		return $this->hasReservedStages() || $this->hasNotReservedStages();
	}

	/**
	 * Есть этапы со статусом "Зарезервирован"
	 *
	 * @return bool
	 */
	public function hasReservedStages() {
		return false;
	}

	/**
	 * Есть этапы со статусом "Не зарезервирован"
	 *
	 * @return bool
	 */
	public function hasNotReservedStages() {
		return false;
	}

	/**
	 * Получение завершенных этапов со статусами "Выплачено" и "Возвращена"
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|OrderStage[]
	 */
	public function getEndStages() {
		return new Collection();
	}

	/**
	 * Получить этапы на проверке
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|OrderStage[]
	 */
	public function getCheckStages() {
		return new Collection();
	}

	/**
	 * Есть этапы на проверке
	 *
	 * @return bool
	 */
	public function hasCheckStages() {
		return $this->getCheckStages()->count() > 0;
	}

	/**
	 * Получение оплаченных этапов
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getPaidStages() {
		return new Collection();
	}

	/**
	 * Есть ли в заказе оплаченные этапы
	 *
	 * @return bool
	 */
	public function hasPaidStages() {
		return $this->has_stages && $this->getPaidStages()->count() > 0;
	}

	/**
	 * Получение всех этапов, кроме заданных статусов
	 *
	 * @param array $excludeStatus - Массив статусов, для которых исключить выборку
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getStagesExcludeStatus($excludeStatus) {
		return new Collection();
	}

	/**
	 * Установить стоимость заказа исходя из стоимости этапов
	 * для актуализации при изменении цен этапов
	 */
	public function setAmountsByAllStages() {
		$this->setAmountsByStages($this->stages);
	}

	/**
	 * Установить стоимость заказа исходя из стоимости выполненных этапов
	 * для того чтобы в выполненных заказах стоимость была верной
	 */
	public function setAmountsByPaidStages() {
		$this->setAmountsByStages($this->getPaidStages());
	}

	/**
	 * Установить стоимость заказа исходя из стоимости всех этапов, кроме тех, которые переданы в массив
	 *
	 * @param array $excludeStatus - Массив статусов для которых не выполнять расчет
	 */
	public function setAmountsExcludeStatus($excludeStatus) {
		$this->setAmountsByStages($this->getStagesExcludeStatus($excludeStatus));
	}

	/**
	 * Получение коллекции моделей опций которые можно докупить в заказе
	 * (Внимание! метод модифицирует поле ctp в extras)
	 *
	 * @return \Illuminate\Support\Collection|\Model\Extra[]
	 */
	public function getUpgradeExtras() {
		if (is_null($this->upgradeExtras)) {
			// Опции кворка, клонируем чтобы данные модификации не отразились kwork->extras
			$kworkExtrasClones = $this->kwork->extras->map(function (Extra $extra) {
				return clone $extra;
			});
			// Опции увеличения объема заказа (обычно она одна)
			$orderOnlyExtras = Extra::where(Extra::FIELD_ORDER_ID, $this->OID)
				->where(Extra::FIELD_IS_VOLUME, 1)
				->get();

			$this->upgradeExtras = $orderOnlyExtras->concat($kworkExtrasClones);

			$turnover = \OrderManager::getTurnover($this->worker_id, $this->USERID, $this->getLang());
			// Изменяем цену для продавца, (почемуто только в обычных опциях, без увеличения объема, так было раньше)
			foreach ($this->upgradeExtras as $extra) {
				if ($extra instanceof Extra && $extra->PID == $this->PID) {
					$commission = \OrderManager::calculateCommission($extra->eprice, $turnover, $this->getLang());
					$extra->ctp = $commission->priceKwork;
				}
			}
		}
		return $this->upgradeExtras;
	}

	/**
	 * Расчёт времени выполнения заказа по загруженным трекам
	 *
	 * @return int
	 */
	public function calculateWorkTime() {
		$tracksWithoutText = $this->tracks->where(Track::FIELD_TYPE, "!=", Type::TEXT);
		// В случае если заказ в работе то отработанное время у него каждую секунду увеличивается
		if ($this->isInProgress() && ($this->in_work || $this->data_provided) && $this->deadline) {
			$pauseTime = \OrderManager::getOrderPauseDuration($this->OID, $tracksWithoutText->all());
			$deadlineDifference = $this->deadline - time();
			return $this->duration - $deadlineDifference - $pauseTime;
		}

		return \OrderManager::calculateWorkTime($this->OID, $tracksWithoutText);
	}

	/**
	 * Получение максимального количества дней на которое можно уменьшить срок заказа
	 */
	public function getMaxDecreaseDays() {
		// уменьшить  срок можно до первоначального предложенного срока
		$maxDecrease = OrderStageOfferManager::getMaxDecreaseDayForOffer($this->initial_duration, $this->duration);

		// Если заказ уже начат то уменьшить его можно лишь так чтобы оставался как минимум 1 день на исполнение
		if (!$this->isNew() && $maxDecrease > 0) {
			$workedTime = $this->calculateWorkTime(); // тут все треки запрашиваются
			$remainingTime = $this->duration - $workedTime;
			$maxDecreaseByWorktime = floor($remainingTime / \Helper::ONE_DAY - 1);
			if ($maxDecreaseByWorktime < $maxDecrease) {
				$maxDecrease = $maxDecreaseByWorktime;
			}
		}

		// Вместо отрицательных выводим 0
		if ($maxDecrease < 0) {
			$maxDecrease = 0;
		}
		return $maxDecrease;
	}

	/**
	 * Установить цену заказа по этапам заказа
	 *
	 * @param Collection|OrderStage[] $stages Этапы заказа которые нужно использовать для установления цены заказа
	 */
	private function setAmountsByStages($stages) {
		if (empty($stages) || !count($stages)) {
			throw new \RuntimeException("Некорректное использование метода setAmountByStages - нет этапов");
		}

		$orderPrice = 0;
		$orderCRT = 0;
		$orderPayerAmount = 0;
		$orderWorkerAmount = 0;
		foreach ($stages as $stage) {
			$orderPrice += $stage->payer_price;
			$orderCRT += $stage->worker_price;
			$orderPayerAmount += $stage->payer_amount;
			$orderWorkerAmount += $stage->worker_amount;
		}

		$this->price = $orderPrice;
		$this->crt = $orderCRT;
		$this->payer_amount = $orderPayerAmount;
		$this->worker_amount = $orderWorkerAmount;
	}

	/**
	 * Обрабатывать заказ как заказ кворка с фиксированным объёмом
	 * @return bool
	 */
	public function processLikeVolumedKwork(): bool {
		return \App::config(\Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS) &&
			$this->kwork->volume_type_id &&
			$this->kwork->volume;
	}

	/**
	 * Получить текущую цену кворка заказа
	 * @return float
	 */
	public function getCurrentKworkPrice() {
		$price = $this->data->kwork_price ?: $this->kwork->price;
		if ($this->orderPackage) {
			$price = $this->orderPackage->price;
		}
		return $price;
	}

	/**
	 * Установить аткуальную цену по этапам в замисимости от состояния заказа
	 */
	public function setStagesPrice() {
		// Определим в каком стостоянии находится заказ
		$state = MyOrders::getUserOrderStateFromOrderStatus($this->status);

		// Выбирать этапы для суммирования будем по состоянию заказа
		if ($state == "cancelled") {
			// Все этапы
			$stateStages = $this->stages;
		} elseif ($state == "completed") {
			// Все оплаченные этапы
			$stateStages = $this->stages->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_PAID);
		} elseif ($state == "unpaid") {
			// Первый для которого нет резерва
			$stateStages = new Collection();
			foreach ($this->stages as $stage) {
				if ($stage->status == OrderStage::STATUS_NOT_RESERVED) {
					$stateStages->put($stage->id, $stage);
					break;
				}
			}
		} else {
			// Все этапы, под которые зарезервированы средства
			$stateStages = $this->stages->where(OrderStage::FIELD_STATUS, OrderStage::STATUS_RESERVED);
		}
		$this->stages_price = $stateStages->sum(OrderStage::FIELD_PAYER_PRICE);
		$this->stages_crt = $stateStages->sum(OrderStage::FIELD_WORKER_PRICE);
	}

	/**
	 * Определить докуплен ли доп. объём
	 * @return bool
	 */
	public function isExtraVolumeOrdered() {
		return $this->data->volume > $this->kwork->getVolumeInSelectedType();
	}

	/**
	 * Получить тип объёма, который используется в заказе
	 * @return Collection|Model|VolumeType|VolumeType[]|null
	 */
	public function getOrderVolumeType() {
		if (!empty($this->orderVolumeType)) {
			return $this->orderVolumeType;
		}
		if ($this->data->volumeType) {
			$this->orderVolumeType = $this->data->volumeType;
		}
		if (!empty($this->data->custom_volume_type_id)) {
			$orderVolumeType = VolumeType::find($this->data->custom_volume_type_id);
			if ($orderVolumeType) {
				$this->orderVolumeType = $orderVolumeType;
			}
		}
		return $this->orderVolumeType;
	}

	/**
	 * @return int
	 */
	public function getOrderBaseTime() {
		if ($this->data->volumeType->contains_value) {
			return $this->data->volumeType->contains_value;
		} else {
			return 1;
		}
	}

	/**
	 * @return int
	 */
	public function getOrderAdditionalTime() {
		if ($this->orderVolumeType && $this->data->volumeType->id != $this->orderVolumeType->id) {
			return $this->orderVolumeType->contains_value;
		} else {
			return 0;
		}
	}

	/**
	 * Получить значение объёма, которое показывается в треке для пользователей
	 * @return float|int
	 */
	public function getDisplayVolume() {
		return $this->data->custom_volume > 0 ? $this->data->custom_volume : $this->data->volume;
	}

	/**
	 * Получить объем кворка в выбранных покупателем единицах
	 * @param float $volume Исходный объём
	 * @return int
	 */
	public function getVolumeInCustomType($volume) {
		$orderVolumeType = $this->getOrderVolumeType();
		if (!empty($orderVolumeType)) {
			$quotient = $divider = 1;
			if ($orderVolumeType->contains_value > 0) {
				$quotient = $orderVolumeType->contains_value;
			}
			if ($this->kwork->volumeType->contains_value > 0) {
				$divider = $this->kwork->volumeType->contains_value;
			}
			$volume = $volume * ($quotient / $divider);
		}
		return $volume;
	}


	/**
	 * Нужно ли при отмене заказа статус заказа делать "Выполнен"
	 * использовать только после добавления трека инициирующего отмену заказа
	 *
	 * @return bool
	 */
	public function isCancelAsDone() {
		// Используется через TrackManager потомучто Track::create() изменяет last_track_id в заказе а модель об этом не знает
		return $this->has_stages &&
			OrderStageManager::isOrderHasPaidStages($this->OID) &&
			TrackManager::isCancelReasonModeNormalByOrderId($this->OID);
	}

	/**
	 * Может ли продавец отменить заказ
	 * @return bool
	 */
	public function isCancelAvailableForWorker() {
		return in_array($this->status, \OrderManager::getCancelAvailableStatusesForWorker());
	}

	/**
	 * Может ли покупатель отменить заказ
	 * @return bool
	 */
	public function isCancelAvailableForPayer() {
		return in_array($this->status, \OrderManager::getCancelAvailableStatusesForPayer());
	}

	/**
	 * Может ли крон автоотменить заказ по запросу от проподавца
	 * @return bool
	 */
	public function isAutoCancelAvailableByWorker() {
		return in_array($this->status, [
			\OrderManager::STATUS_INPROGRESS,
			\OrderManager::STATUS_UNPAID,
		]);
	}

	/**
	 * Может ли крон автоотменить заказ по запросу от покупателя
	 * @return bool
	 */
	public function isAutoCancelAvailableByPayer() {
		return in_array($this->status, [
			\OrderManager::STATUS_INPROGRESS,
			\OrderManager::STATUS_UNPAID,
			\OrderManager::STATUS_CHECK,
		]);
	}

	/**
	 * Были ли уже заказ в статусе "выполнен" (\OrderManager::STATUS_DONE)
	 *
	 * @return bool
	 */
	public function wasCompletedEarlier(): bool {
		return Track::query()
			->where(Track::FIELD_ORDER_ID, $this->getKey())
			->whereIn(Track::FIELD_TYPE, Type::doneTypes())
			->exists();
	}

	/**
	 * Находится ли заказ в состоянии запроса отмены
	 *
	 * @return bool
	 */
	public function isCancelRequest(): bool {
		return $this->isInProgress() && $this->tracks()
				->whereIn(Track::FIELD_TYPE, [Type::WORKER_INPROGRESS_CANCEL_REQUEST, Type::PAYER_INPROGRESS_CANCEL_REQUEST])
				->where(Track::FIELD_STATUS, TrackManager::STATUS_NEW)
				->exists();
	}

	public function getDeadlineAttribute() {
		return time() + Helper::ONE_DAY;
	}
}