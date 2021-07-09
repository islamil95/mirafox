<?php

namespace Core\Traits;

use \Model\User;

/**
 * Методы связанные с авторизацией пользователя
 *
 * Trait AuthTrait
 * @package Core\Traits
 */
trait AuthTrait {

	private $user = null;
	private $userModel = null;

	/**
	 * @var null|int id текущего авторизованного пользователя
	 */
	private static $currentUserId = null;

	/**
	 * Получить текущего пользователя
	 *
	 * @return null|object
	 */
	protected function getUser() {
		if ($this->user === null) {
			$this->user = \UserManager::getCurrentUser();
		}
		return $this->user;
	}

	/**
	 * Получить текущего пользователя
	 *
	 * @return null|User
	 */
	protected function getUserModel() {
		if ($this->userModel === null) {
			$id = $this->getUserId();			
			$this->userModel = User::where("USERID", $id)
				->first();
		}
		return $this->userModel;
	}

	/**
	 * Авторизирован ли пользователь
	 *
	 * @return bool
	 */
	protected function isUserAuthenticated(): bool {
		return $this->getUser() !== null;
	}

	/**
	 * Не авторизирован ли пользователь
	 *
	 * @return bool
	 */
	protected function isUserNotAuthenticated(): bool {
		return ! $this->isUserAuthenticated();
	}

	/**
	 * Пользователь обладает ролью модератора или администратора
	 *
	 * @return bool
	 */
	protected function isUserAdminOrModer(): bool {
		return $this->isUserAuthenticated() && in_array($this->user->role, ["admin", "moder"]);
	}

	/**
	 * Пользователь не обладает ролью модератора или администратора
	 *
	 * @return bool
	 */
	protected function isUserNotAdminOrModer(): bool {
		return ! $this->isUserAdminOrModer();
	}

	/**
	 * Получить идентификатор текущего пользователя
	 *
	 * @return int|null
	 */
	protected function getUserId() {
		if ($this->isUserNotAuthenticated()) {
			return null;
		}
		return (int) $this->getUser()->id;
	}

	/**
	 * Получить имя текущего пользователя
	 *
	 * @return string|null Имя пользователя
	 */
	protected function getUsername() {
		if ($this->isUserNotAuthenticated()) {
			return null;
		}
		return $this->getUser()->username;
	}

	/**
	 * Получить роль текушего пользователя
	 *
	 * @return string|null роль пользователя
	 */
	protected function getUserRole() {
		if (!$this->isUserAuthenticated()) {
			return null;
		}
		return $this->getUser()->role;
	}

	/**
	 * Создать ссылку с рефераром текущего пользовтеля
	 *
	 * @return string реферальная ссылка
	 */
	protected function getRefUrl() {
		if ($this->isUserAuthenticated()) {
			return "?ref=" . $this->getUserId();
		}
		return "";
	}

	/**
	 * Проверка на виртуальный логин
	 *
	 * @return bool
	 */
	protected function isVirtual():bool {
		if (!$this->isUserAuthenticated()) {
			return false;
		}
		return $this->getUser()->isVirtual;
	}

	/**
	 * Логин не виртуальный
	 *
	 * @return bool
	 */
	protected function isNotVirtual():bool {
		return ! $this->isVirtual();
	}

	/**
	 * Имя модератора
	 *
	 * @return null|string
	 */
	protected function getModeratorName() {
		$user = $this->getUser();
		if (null === $user) {
			return null;
		}
		if (empty($user->fullname)) {
			return $user->username;
		}
		$fullname = explode(" ", $user->fullname, 2);
		$moderName = $fullname[0];
		if (count($fullname) > 1) {
			$moderName .= " " . mb_substr($fullname[1], 0, 1) . ".";
		}
		return $moderName;
	}

	/**
	 * Получить полное имя пользователя
	 *
	 * @return string
	 */
	protected function getFullnameOrUsername() {
		if ($this->isUserNotAuthenticated()) {
			return "";
		}
		$user = $this->getUser();
		return empty($user->fullname) ? $user->username : $user->fullname;
	}

	/**
	 * Получить тип пользователя
	 *
	 * @return null|string - тип пользователя
	 */
	protected function getUserType() {
		if ($this->isUserNotAuthenticated()) {
			return null;
		}
		$user = $this->getUser();
		return $user->type;
	}

	/**
	 * Заблокирована ли английская версия
	 *
	 * @return bool|null результат
	 */
	protected function getUserDisableEn() {
		if ($this->isUserNotAuthenticated()) {
			return null;
		}
		$user = $this->getUser();
		return $user->disableEn;
	}

	/**
	 * Получить язык пользователя
	 *
	 * @return string|null язык
	 */
	protected function getUserLang() {
		if ($this->isUserNotAuthenticated()) {
			return null;
		}
		$user = $this->getUser();
		return $user->lang;
	}

	/**
	 * Больше ли уровень пользователя, чем новичок
	 *
	 * @return bool результат
	 */
	protected function isUserGradeThatNovice():bool {
		return ! $this->isUserNovice();
	}

	/**
	 * Пользователь новичок
	 *
	 * @return bool результат
	 */
	protected function isUserNovice():bool {

		if ($this->isUserNotAuthenticated()) {
			return false;
		}
		$user = $this->getUser();
		return $user->level == \UserLevelManager::LEVEL_NOVICE;
	}

	/**
	 * Обновить учетные данные
	 *
	 * @return $this
	 */
	protected function refreshCredentials() {
		$this->user = getActor();
		return $this;
	}

	/**
	 * Нужно ли подтверждать запросы на услуги
	 *
	 * @return bool результат
	 */
	protected function isWantConfirm():bool {
		if ($this->isUserNotAuthenticated()) {
			return false;
		}
		return (bool)true;
	}

	/**
	 * Получить остаток на счете
	 *
	 * @return int остаток на счете
	 */
	protected function getTotalFounds():int {
		if ($this->isUserNotAuthenticated()) {
			return 0;
		}
		$user = $this->getUser();
		return (int) $user->totalFunds;
	}

	/**
	 * Является ли пользователя админом
	 *
	 * @return bool
	 */
	protected function isUserAdmin():bool {
		return $this->isUserAuthenticated() && $this->user->role == "admin";
	}

	/**
	 * Пользователь не имеет роли администратора
	 *
	 * @return bool
	 */
	protected function isUserNotAdmin():bool {
		return ! $this->isUserAdmin();
	}

	/**
	 * Пользователь покупатель
	 *
	 * @return bool
	 */
	protected function isPayer():bool {
		return $this->getUserType() == \UserManager::TYPE_PAYER;
	}

	/**
	 * Пользователь продавец
	 *
	 * @return bool
	 */
	protected function isWorker():bool {
		return $this->getUserType() == \UserManager::TYPE_WORKER;
	}

	/**
	 * Текущий пользователь это пользователь Кворк
	 *
	 * @return bool
	 */
	protected function currentUserIsKworkUser():bool {
		return $this->getUserId() == \App::config("kwork.user_id");
	}

	/**
	 * Текущий пользователь это не пользователь Кворк
	 *
	 * @return bool
	 */
	protected function currentUserIsNotKworkUser():bool {
		return ! $this->currentUserIsKworkUser();
	}

	/**
	 * Текущий пользователь это пользователь службы поддержки
	 *
	 * @return bool
	 */
	protected function currentUserIsSupportUser():bool {
		return $this->getUserId() == \App::config("kwork.support_id");
	}

	/**
	 * Текущий пользователь это не пользователь службы поддержки
	 *
	 * @return bool
	 */
	protected function currentUserIsNotSupportUser():bool {
		return ! $this->currentUserIsSupportUser();
	}

	/**
	 * Возвращает id авторизованного пользователя
	 *
	 * @return null|int
	 */
	public function getCurrentUserId() : ?int {
		if (is_null(self::$currentUserId)) {
			self::$currentUserId = $this->getUserId();
		}
		return self::$currentUserId;
	}

	/**
	 * Проверен ли пользователь
	 *
	 * @return bool
	 */
	protected function isUserVerified(): bool {
		if ($this->isUserNotAuthenticated()) {
			return false;
		}
		$user = $this->getUser();
		return (bool) $user->verified;
	}

	/**
	 * Пользователь не проверен
	 *
	 * @return bool
	 */
	protected function isUserNotVerified(): bool {
		return ! $this->isUserVerified();
	}
}