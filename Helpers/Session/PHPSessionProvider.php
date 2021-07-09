<?php

namespace Session;

class PHPSessionProvider extends AbstractSessionProvider {

	protected function init() {
		//Установка на сессию httpOnly
		$sessionParams = session_get_cookie_params();
		session_set_cookie_params(
			$sessionParams['lifetime'],
			$sessionParams['path'],
			$sessionParams['domain'],
			$sessionParams['secure'],
			true);
		$this->setMysqlSessionHandler();
	}

	protected function setMysqlSessionHandler() {
		if ((\App::config("session.engine") != 'mysql')) {
			return true;
		}
		$sessionHandler = MysqlSessionHandler::getInstance();
		session_set_save_handler($sessionHandler);
	}

	public function delete($id): bool {
		if ($this->isExist($id)) {
			unset($_SESSION[$id]);
		}
		return true;
	}

	public function get($id, $defaultValue = null) {
		if ($this->isExist($id)) {
			return $_SESSION[$id];
		}
		return $defaultValue;
	}

	public function getSessionId(): string {
		return session_id();
	}

	public function isExist($id): bool {
		return isset($_SESSION[$id]);
	}

	/**
	 * Устанавливает значение параметра сессии
	 *
	 * @param string $id ключ значения
	 * @param mixed $value значение
	 */
	public function set($id, $value) {
		$_SESSION[$id] = $value;
	}

	/**
	 * Старт сессии пользователя
	 *
	 * @param null|int $lifeTime время жизни сессии
	 * @return bool
	 */
	public function start($lifeTime = null): bool {
		session_start();
		//setcookie(session_name(), session_id(), time() + $lifeTime, "/");
		return true;
	}

	public function stop(): bool {
		return true;
	}

	public function validateSessionId(): bool {
		return !empty(session_id());
	}

	public function isEmpty($id): bool {
		return !$this->notEmpty($id);
	}

	public function notEmpty($id): bool {
		return $this->isExist($id) && !empty($_SESSION[$id]);
	}

	public function notExist($id): bool {
		return !$this->isExist($id);
	}

}
