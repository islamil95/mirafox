<?php

namespace Controllers\User;

use Controllers\BaseController;
use Core\DB\DB;
use Core\Exception\PageNotFoundException;
use Model\User;

/**
 * Страница профиля пользователя
 *
 * Class ProfileController
 * @package Controllers\User
 */
class ProfileController extends BaseController {

	const REVIEW_COUNT_ON_PAGE = 5;

	/**
	 * Хранит модель пользователя чей профиль смотрим
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Получить профиль пользователя
	 *
	 * @param string $username имя пользователя
	 * @return User профиль
	 */
	private function getUserProfile($username) {
		$user = User::where(User::FIELD_USERNAME, $username)
			->where(User::FIELD_STATUS, User::USER_STATUS_ACTIVE)
			->first();

		if (is_null($user) || in_array($user->USERID, [\App::config("kwork.support_id"), \App::config("kwork.moder_id")])) {
			throw new PageNotFoundException();
		}

		$this->user = $user;

		return $user;
	}

	/**
	 * Показывать ли уровень продавца
	 *
	 * @param User $user профиль пользователя
	 * @return bool результат
	 */
	private function isShowPayerLevel($user):bool {
		return false;
	}

	/**
	 * Получить взаимные заказы между пользователем чей профиль просматриваетс и текущим пользователем
	 *
	 * @param User $user профиль пользователя
	 * @return array|null массив заказов
	 */
	private function getOrderBetween($user) {
		if ($user->USERID == $this->getUserId()) {
			return null;
		};
		return \UserManager::ordersBetweenUsers($this->getUserId(), $user->USERID);
	}

	/**
	 * Получить заголовок страницы
	 *
	 * @param User $user профиль пользователя
	 * @return string заголовок страницы
	 */
	private function getPageTitle($user):string {
		return \Translations::t("Профиль пользователя") . " " . $user->username;
	}

	/**
	 * Получить максимальное число отзывов на странице
	 *
	 * @return int число отзывов
	 */
	private function getReviewsCountOnPage() {
		return self::REVIEW_COUNT_ON_PAGE;
	}

	/**
	 * Получить положительные отзывы
	 *
	 * @param int $userId идентификатор пользователя
	 * @return array|false отзывы
	 */
	private function getGoodReviews($userId) {
		$options = [
			"type" => "positive",
			"offset" => 0,
			"limit" => $this->getReviewsCountOnPage(),
		];
		return \RatingManager::getByUserId($userId, $options);
	}

	/**
	 * Получить негативные отзывы
	 *
	 * @param int $userId идентификатор пользователя
	 * @return array|false отзывы
	 */
	private function getBadReviews($userId) {
		$options = [
			"type" => "negative",
			"offset" => 0,
			"limit" => $this->getReviewsCountOnPage(),
		];
		return \RatingManager::getByUserId($userId, $options);
	}

	/**
	 * Является ли текущий пользователь и профиль просматриваемый одним и тем же пользователем
	 *
	 * @param User $user профиль пользователя
	 * @return bool результат
	 */
	private function isSameUser($user):bool {
		return $user->USERID == $this->getUserId();
	}

	/**
	 * Получить класс CSS для кнопки в шаблоне
	 *
	 * @param bool $privateMessageStatus статут отправки личных сообщений
	 * @return string название класса
	 */
	private function getSendMessageButtonClass($privateMessageStatus):string {
		if ($this->isUserAuthenticated() && $privateMessageStatus) {
			return "js-individual-message__popup-link";
		}
		return "signup-js";
	}

	/**
	 * @param $username
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke($username) {
		$actor = $this->getUser();
		$user = $this->getUserProfile($username);

		// Если юзеру заблокировали кворки, то был ли он заблокирован администратором?
		// Для этого нужно проверить таблицы kwork_unblock и user_kwork_block
		$isUserBlockByAdmin = false;

		// Юзер ожидает разблокировки ?
		$isUserWaitUnblock = false;

		if ($isUserWaitUnblock) {
			$whoIsBlockUser = DB::table('user_kwork_block')
				->select('block_type')
				->where("user_id", $user->USERID)
				->orderBy("id", "desc")
				->limit(1)
				->value('block_type');

			if ($whoIsBlockUser == 'admin') {
				$isUserBlockByAdmin = true;
			}
		}

		if ($isUserWaitUnblock && !$isUserBlockByAdmin) {
			// Если пользователь не заблокирован админом, то инициатор разблокировки он сам.
			// Разрешаем ему показать кворки в профиле (но не в категориях).
			$userKworksData = \KworkManager::getByUser($user->USERID, 0, 0, true);
		} else {
			// В противном случае, если была блокировка, то не показывваем кворки в профиле.
			$userKworksData = \KworkManager::getByUser($user->USERID);
		}

		$userKworks = $userKworksData["kworks"];
		$totalUserKworkCount = $userKworksData["total"];

		$goodReviews = $this->getGoodReviews($user->USERID);
		$badReviews = $this->getBadReviews($user->USERID);

		// [#7036] на РУ-кворке считаем только русские отзывы, на ЕН-кворке - русские и англ.
		if (\Translations::isDefaultLang()) {
			$langs = [\Translations::DEFAULT_LANG];
		} else {
			$langs = [\Translations::DEFAULT_LANG, \Translations::EN_LANG];
		}
		$ratingCount = \RatingManager::getRatingCount($user->USERID, $langs);

		$reviews = $goodReviews["reviews"] ?: $badReviews["reviews"];

		$currentUserLang = "";
		$currentUser = \UserManager::getCurrentUser();
		if ($currentUser) {
			$currentUserLang = $currentUser->lang;
		}

		$offerLang = $user->lang;

		$hasLangKworks = \UserManager::hasLangKworks($user->USERID, $currentUserLang);

		$allowConversation = $hasConversation = false;

		$parameters = [
			"userProfile" => $user,
			"showPayerLevel" => $this->isShowPayerLevel($user),
			"ordersBetweenUsers" => $this->getOrderBetween($user),
			"userKworks" => $userKworks,
			"pagetitle" => $this->getPageTitle($user),
			"reviews" => $reviews,
			"isWorker" => $this->isSameUser($user),
			"goodReviewsCount" => $goodReviews["total"],
			"badReviewsCount" => $badReviews["total"],
			"totalReviewsCount" => $ratingCount,
			"reviewsOnPage" => $this->getReviewsCountOnPage(),
			"reviewsType" => count($goodReviews["reviews"]) ? "positive" : "negative",
			"totalKworks" => $totalUserKworkCount,
			"hasConversation" => $hasConversation,
			"controlEnLang" => $user->lang == \Translations::EN_LANG && $actor->lang == \Translations::DEFAULT_LANG,
			"max_file_count" => \App::config("files.max_count"),
			"avg_work_time" => \UserManager::getAvgWorkTime($user->USERID),
			"withPortfolio" => true,
			"isCustomRequest" => ($hasLangKworks && \App::config("order.request_inbox_order")),
			"privateMessageStatus" => $allowConversation["privateMessageStatus"],
			"allowCustomRequest" => $hasLangKworks,
			"sendMessageClass" => $this->getSendMessageButtonClass($allowConversation["privateMessageStatus"]),
			"customMinPrice" => \KworkManager::getCustomMinPrice($offerLang),
			"customMaxPrice" => \KworkManager::getCustomMaxPrice($offerLang),
			"offerLang" => $offerLang,
			"showInboxAllowModal" => $allowConversation["showInboxAllowModal"],
			"fullName" => $this->getFullName(),
			"payerOrdersCount" => \OrderManager::payerOrdersCount($user->USERID),
			"workerOrdersCount" => \OrderManager::workerOrdersCount($user->USERID),
			// Необходимо подготовить попап верификации если авторизованный пользователь не свой профиль смотрит, не общались ранее, и разрешено писать
			"isPageNeedSmsVerification" => $actor && $actor->id !== $user->USERID && !$hasConversation && $allowConversation["privateMessageStatus"] === true,
			"isProjectTopfreelancer" => $this->isProjectTopfreelancer(),
			"showKworks" => $totalUserKworkCount > 0,
		];
		$portfolioParams = $this->getPortfolios($user->USERID);
		$parameters = array_merge($parameters, $portfolioParams);

		return $this->render("user/user", $parameters);
	}

	/**
	 * Возвращает полное имя пользователя для текущей локализации
	 *
	 * @return string
	 */
	private function getFullName(): string {
		if ($this->isFullNameHide()) {
			// не будем показывать поле другим пользователям если оно скрыто
			$result = "";
		} else {
			$result = $this->user->getTranslatedFullname();
		}

		return $result;
	}

	/**
	 * Возвращает скрытые от других поля пользователя
	 *
	 * @return array
	 */
	private function getHiddenFields(): array {
		return [];
	}

	/**
	 * Проверяет скрыто ли полное имя для текущей локализации от других пользователей
	 *
	 * @return bool
	 */
	private function isFullNameHide(): bool {
		if (\Translations::isDefaultLang()) {
			$fieldName = User::FIELD_FULLNAME;
		} else {
			$fieldName = User::FIELD_FULLNAME_EN;
		}

		return array_search($fieldName, $this->getHiddenFields()) !== false;
	}

	/**
	 * Проверяет скрыто ли описание для текущей локализации от других пользователей
	 *
	 * @return bool
	 */
	private function isDescriptionHide(): bool {
		if (\Translations::isDefaultLang()) {
			$fieldName = User::FIELD_DESCRIPTION;
		} else {
			$fieldName = User::FIELD_DESCRIPTION_EN;
		}

		return array_search($fieldName, $this->getHiddenFields()) !== false;
	}

	/**
	 * Портфолио для страницы профиля пользователя
	 *
	 * @param $userId
	 * @return array
	 */
	private function getPortfolios($userId) {
		return [];
	}
}