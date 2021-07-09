<?php
namespace Session;

class SessionDecorator implements IBaseSessionProvider {
	
	private $sessionProvider;
	private $started;

	public function __construct(ISessionProvider $sessionProvider) {
		if (!$sessionProvider instanceof IStartStop) {
			throw new Exception("Класс " . get_class($sessionProvider) ." должен реализовать IStartStop");
		}
		if (!$sessionProvider instanceof IBaseSessionProvider) {
			throw new Exception("Класс " . get_class($sessionProvider) ." должен реализовать IBaseSessionProvider");
		}

		$this->sessionProvider = $sessionProvider;
		$this->started = false;

	}

	public function __destruct() {
		if ($this->isStarted()) {
			$this->sessionProvider->stop();
			$this->setStarted(false);
		}
	}
	
	protected function isStarted():bool {
		return $this->started;
	}
	
	protected function isStoped():bool {
		return !$this->started;
	}

	/**
	 * Возобновить либо запустить сессию, если в нее нужно записать какие-то данные
	 *
	 * @param bool $isSetData наличие данных для записи в сессию
	 * @param null|int $lifeTime задает время сессии
	 */
	protected function checkAndStart($isSetData = false, $lifeTime = null) {
		if ($this->isStoped() && ($isSetData || \CookieManager::get("PHPSESSID") || $lifeTime)) {
			$this->sessionProvider->start($lifeTime);
			$this->setStarted(true);
		}
	}

	protected function setStarted(bool $started) {
		$this->started = $started;
		return $this;
	}

	/**
	 * Останавливает и возобновляет сессию пользователя
	 *
	 * @param null $lifeTime устанавливает время жизни сессии
	 */
	public function restartSession($lifeTime = null) {
		$this->sessionProvider->stop();
		$this->setStarted(false);
		$this->checkAndStart(false, $lifeTime);
	}

	public function delete($id): bool {
		$this->checkAndStart();
		return $this->sessionProvider->delete($id);
	}

	public function get($id, $defaultValue = null) {
		$this->checkAndStart();
		return $this->sessionProvider->get($id, $defaultValue);
	}

	public function getSessionId(): string {
		$this->checkAndStart();
		return $this->sessionProvider->getSessionId();
	}

	public function isExist($id): bool {
		$this->checkAndStart();
		return $this->sessionProvider->isExist($id);
	}

	public function set($id, $value) {
		$this->checkAndStart(true);
		return $this->sessionProvider->set($id, $value);
	}

	public function validateSessionId(): bool {
		$this->checkAndStart();
		return $this->sessionProvider->validateSessionId();
	}

	public function isEmpty($id): bool {
		$this->checkAndStart();
		return $this->sessionProvider->isEmpty($id);
	}

	public function notEmpty($id): bool {
		$this->checkAndStart();
		return $this->sessionProvider->notEmpty($id);
	}

	public function notExist($id): bool {
		$this->checkAndStart();
		return $this->sessionProvider->notExist($id);
	}

}
