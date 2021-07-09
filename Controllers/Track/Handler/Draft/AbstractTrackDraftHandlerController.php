<?php

namespace Controllers\Track\Handler\Draft;

use Controllers\Track\Handler\AbstractTrackHandlerController;
use Core\Response\BaseJsonResponse;
use Model\TrackDraft;
use Strategy\Track\IsAllowConversationInDoneOrderStrategy;

/**
 * Абстрактный обработчик черновиков треков
 *
 * Class AbstractTrackDraftHandlerController
 * @package Controllers\Track\Handler
 */
abstract class AbstractTrackDraftHandlerController extends AbstractTrackHandlerController {
	/**
	 * @var BaseJsonResponse|null JSON ответ
	 */
	private $response;

	public function __construct() {
		parent::__construct();
		$this->response = new BaseJsonResponse();
	}

	/**
	 * @inheritdoc
	 */
	protected function shouldLock(): bool {
		return false;
	}

	/**
	 * Заказ не валиден
	 *
	 * @return bool
	 */
	protected function isNotValid(): bool {
		$order = $this->getOrder();

		return 	$order->isDone() ||
				! (
					$order->isInProgress() ||
					$order->isCheck() ||
					$order->isArbitrage() ||
					$order->isUnpaid()
				);
	}

	/**
	 * Работает не реальный пользователь
	 *
	 * @return bool
	 */
	protected function isNotRealUser(): bool {
		return $this->currentUserIsKworkUser() || $this->isVirtual();
	}

	/**
	 * Получить черновик трека для текущего заказа и пользователя
	 *
	 * @return TrackDraft|null
	 */
	protected function getDraft():? TrackDraft {
		return \TrackDraftManager::getDraftByOrderId($this->getOrderId());
	}

	/**
	 * Получить ошибочный ответ
	 *
	 * @param string $error Текст ошибки
	 *
	 * @return BaseJsonResponse
	 */
	protected function getErrorResponse(string $error = ""): BaseJsonResponse {
		return $this->response->setErrors([$error]);
	}

	/**
	 * Получить корректный ответ
	 *
	 * @return BaseJsonResponse
	 */
	protected function getSuccessResponse(): BaseJsonResponse {
		return $this->response->setStatus(true);
	}
}