<?php


namespace Controllers\Track\Handler;


use Controllers\BaseController;
use Core\Exception\JsonException;
use Core\Exception\RedirectException;
use Core\Traits\ConfigurationTrait;
use Core\Traits\Mail\ServiceLetterFactoryTrait;
use Core\Traits\Routing\RoutingTrait;
use DbLock\DbLock;
use DbLock\LockEnum;
use Model\Order;
use Model\Track;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Track\Factory\TrackViewFactory;

/**
 * Абстрактный обработчик треков
 *
 * Class AbstractTrackHandlerController
 * @package Controllers\Track\Handler
 */
abstract class AbstractTrackHandlerController extends BaseController {

	use ServiceLetterFactoryTrait, RoutingTrait, ConfigurationTrait;

	/**
	 * @var Request|null HTTP запрос
	 */
	private $request;

	/**
	 * @var Order|null модель заказа
	 */
	private $order;

	/**
	 * Блокировка
	 *
	 * @var DbLock
	 */
	private $lock;

	public function __construct() {
		parent::__construct();
		$this->request = null;
		$this->order = null;
	}

	/**
	 * Получить HTTP запрос
	 *
	 * @return Request
	 */
	protected function getRequest(): Request {
		return $this->request;
	}

	/**
	 * Задать HTTP запрос
	 *
	 * @param Request $request HTTP запрос
	 * @return AbstractTrackHandlerController
	 */
	private function setRequest(Request $request):self {
		$this->request = $request;
		return $this;
	}

	/**
	 * Получить идентификатор заказа
	 *
	 * @return int
	 */
	protected function getOrderId(): int {
		return $this->getRequest()->request->getInt("orderId", 0);
	}

	/**
	 * Получить модель заказа
	 *
	 * @return Order
	 */
	protected function getOrder(): Order {
		if (is_null($this->order)) {
			$this->order = Order::find($this->getOrderId());
		}
		return $this->order;
	}

	/**
	 * Это аякс запрос?
	 *
	 * @return bool
	 */
	protected function isAjax(): bool {
		return $this->getRequest()->request->has("is_ajax");
	}

	/**
	 * Это не аякс запрос?
	 *
	 * @return bool
	 */
	protected function isNotAjax(): bool {
		return ! $this->isAjax();
	}

	/**
	 * Проверка безопасности
	 *
	 * @return AbstractTrackHandlerController
	 */
	private function securityValidation(): self {
		if ($this->isUserNotAuthenticated() ||
			$this->getOrderId() == 0) {
			throw (new RedirectException())->setRedirectUrl("/");
		}
		return $this;
	}

	/**
	 * Проверка заказа
	 *
	 * @return AbstractTrackHandlerController
	 */
	private function orderValidation(): self {
		$order = $this->getOrder();
		if (is_null($order) || $order->isNew() || $order->isCancel()) {
			if ($this->isAjax()) {
				throw (new JsonException())->setData([
					"status" => "error",
					"response" => \Translations::t("Вы не можете оставить сообщение, так как заказ был отменен.")
				]);
			}
			throw (new RedirectException())->setRedirectUrl("/");
		}
		if ($order->isNotBelongToPayerOrWorker($this->getUserId()) &&
			$this->currentUserIsNotKworkUser() &&
			$this->currentUserIsNotSupportUser()) {
			throw (new RedirectException())->setRedirectUrl("/");
		}

		return $this;
	}

	/**
	 * Тип ответа (согласие/не согласие)
	 *
	 * @return string
	 */
	protected function getReplyType(): string {
		if ($this->getRequest()->request->get("agree_reason") == \TrackManager::REPLY_TYPE_DISAGREE) {
			return \TrackManager::REPLY_TYPE_DISAGREE;
		}
		return \TrackManager::REPLY_TYPE_AGREE;
	}

	/**
	 * Получить отправленное сообщение
	 *
	 * @return static
	 */
	protected function getMessage() {
		return trim($this->getRequest()->request->get("message"));
	}

	/**
	 * Получить преданный идентификатор трека
	 *
	 * @return int
	 */
	protected function getTrackId() {
		return $this->getRequest()->request->getInt("trackId");
	}

	/**
	 * Получить идентификатор цитируемого сообщения
	 *
	 * @return int
	 */
	protected function getQuoteId() {
		return $this->getRequest()->request->getInt("quoteId");
	}

	/**
	 * Поставить блокировку с базе данных
	 *
	 * @return AbstractTrackHandlerController
	 * @throws \Exception
	 */
	private function lock(): self {
		$this->lock = new DbLock(LockEnum::getWithId(LockEnum::ORDER, $this->getOrderId()));
		return $this;
	}

	/**
	 * Точка входа
	 *
	 * @param Request $request HTTP запрос
	 * @throws \Exception
	 */
	public function __invoke(Request $request) {
		$this->setRequest($request)
			->securityValidation()
			->orderValidation();
		// Если реальный пользователь что-то делает в треках,
		// то он прочитал все непрочитанные.
		if ($this->isNotVirtual()) {
			if(!empty($this->order)){
				\TrackManager::setReadInTracks($this->order);
			}
		}

		if ($this->shouldLock()) {
			$this->lock();
		}

		/**
		 * Авторский подход сохранен
		 *
		 * // @todo: пуш о создании трека отправляется в TrackManager::create(),
		 * // потому что он там и должен отправляться (создали - отправили).
		 * // Но здесь трек сначала создается, а потом, через полстраницы(!), создаются файлы этого трека.
		 * // И у нас пуш и последующий пул запросто успевают прогнать трек без файлов.
		 * // Поэтому файлы трека надо создавать там же, где и создается трек.
		 * // Но чтобы их там получить, их надо передать во все функции, которые обертывают создание трека
		 * // и это не только нереально, но и очень нехорошо, ибо их много.
		 * // Поэтому сделал глобальной переменной (что тоже нехорошо!).
		 *
		 * #7576 - переделал с глобальной переменной на статический кэш TrackManager::$requestFiles
		 */

		return $this->processAction();
	}

	/**
	 * URL для перенаправления
	 *
	 * @return string
	 */
	protected function getRedirectUrl(): string {
		return $this->getUrlByRoute("track", [
			"id" => $this->getOrder()->OID,
			"scroll" => 1
		]);
	}

	/**
	 * Получить массив идентификаторов треков для рендера
	 *
	 * @return array
	 */
	protected function getTracksList(): array {
		return [$this->getTrackId()];
	}

	/**
	 * Сформировать ответ
	 *
	 * @return Response HTTP ответ
	 */
	protected function getResponse(): Response {
		$post = $this->getRequest()->request;
		if ($post->has("is_ajax") && $this->getTrackId()) {

			$tracksId = $this->getTracksList();

			$tracks = Track::whereIn(Track::FIELD_ID, $tracksId)
				->where(Track::FIELD_ORDER_ID, $this->getOrder()->OID)
				->get();
			$tracksResponse = "";
			$trackViewFactory = TrackViewFactory::getInstance();
			foreach ($tracks as $track) {
				$tracksResponse .= $trackViewFactory->getView($track)->render();
			}
			return new Response($tracksResponse);
		}
		return new RedirectResponse($this->getRedirectUrl());
	}

	/**
	 * Нужно ли блокировать базу данных на время исполнения контроллера
	 *
	 * @return bool
	 */
	abstract protected function shouldLock(): bool;

	/**
	 * Обработка логики контроллера наследника
	 *
	 * @return Response HTTP ответ
	 */
	abstract protected function processAction(): Response;
}