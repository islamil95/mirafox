<?php

namespace User;

use Core\DB\DB;
use Model\User;

class SettingManager
{
	/**
	 * Обновление основных настроек пользователя
	 * Помимо указанных параметров ожидаются загруженные файлы
	 * $_FILES["avatar-photo"] и $_FILES["cover-photo"]
	 *
	 * @param string|null $username Имя пользователя (логин)
	 * @param string|null $fullname Настоящее имя пользователя - по русски
	 * @param string|null $fullnameEn Настоящее имя пользователя - по английски
	 * @param int|null $timezoneId Идентификатор часового пояса
	 * @param string|null $email Электропочта
	 * @param string|null $details Информация о продавце
	 * @param string|null $detailsEn Информация о продавце по английски
	 * @param array|null $avatarPhotoSize Массив данных о ресайзе аватара [x,y,w,h]
	 * @param array|null $coverSize Массив данных о ресайзе баннера [x,y,w,h]
	 * @param string|null $country Название страны
	 * @param int|null $countryId Идентификатор страны
	 * @param string|null $city Название города
	 * @param int|null $cityId Идентификатор города
	 * @param string|null $paypal Идентификатор paypal (необязателен)
	 * @param string|null $alertpay Идентификатор alertpay (необязателен)
	 * @param string|null $firstLetterFromLastName Первая буква фамилии (рус.)
	 * @param string|null $firstLetterFromLastNameEn Первая буква фамилии (англ.)
	 *
	 * @return array [errors => [], message => "..."]
	 */
	public static function updateCommon($username, $fullname, $fullnameEn, $timezoneId, $email, $details, $detailsEn, $avatarPhotoSize, $coverSize, $country, $countryId, $city, $cityId, $paypal = "", $alertpay = "", $deletePhoto = [], $firstLetterFromLastName = null, $firstLetterFromLastNameEn = null, $profession = null): array
	{
		$actor = \UserManager::getCurrentUser();
		$user = User::with("data")->find($actor->id);

		$pattern = '/[^а-яА-ЯёЁ\s]+/u';
		if ($actor->lang == \Translations::EN_LANG) {
			$pattern = "/[^a-zA-Z'\-—\s]+/u";
		}
		if ($fullname) {
			$fullname = preg_replace($pattern, "", $fullname);
		}
		if ($fullnameEn) {
			$fullnameEn = preg_replace("/[^a-zA-Z'\-—\s]+/u", "", $fullnameEn);
		}

		$errors = [];
		$messages = [];

		if (!is_null($timezoneId)) {
			list($timezoneId, $timezone) = self::checkTimezone($timezoneId);
		}

		if (!is_null($country) || !is_null($countryId) || !is_null($city) || !is_null($cityId)) {
			if ($country == "") {
				$countryId = 0;
			} else {
				$countryId = (int)$countryId;
				if (!\CountryManager::exist($countryId)) {
					$errors[] = \Translations::t("Такой страны не существует.");
				}
			}
			if ($city == "") {
				$cityId = 0;
			} else {
				$cityId = (int)$cityId;
				$cityObj = \CityManager::get($cityId, $countryId);
				if ($cityObj == null) {
					$errors[] = \Translations::t("Такого города не существует.");
				} elseif ($countryId == 0) {
					$countryId = $cityObj->getCountryId();
				}
			}
		}

		if (!is_null($username)) {
			self::checkUsername($username, $errors);
		}

		if (!is_null($email)) {
			self::checkEmail($email, $errors);
		}

		// временно отключено
		if (false) {
			$enableDesc = self::checkActorDescriptionShow();

			if (!is_null($details)) {
				if ($enableDesc[\Translations::DEFAULT_LANG] &&
					mb_strlen($details, "utf-8") < \UserManager::MIN_USER_DESCRIPTION_LENGTH
				) {
					$errors[] = \Translations::t("Информация о себе (на русском) не должна быть меньше 100 символов");
				}
			}

			if (!is_null($detailsEn)) {
				if (\Translations::getLang() == \Translations::EN_LANG &&
					$enableDesc[\Translations::EN_LANG] &&
					mb_strlen($detailsEn) < \UserManager::MIN_USER_DESCRIPTION_LENGTH
				) {
					$errors[] = \Translations::t("Информация о себе (на английском) не должна быть меньше 100 символов");
				}
			}
		}

		if (!is_null($firstLetterFromLastName)) {
			if ($firstLetterFromLastName !== "" &&
				!preg_match("/^[а-яё]$/iu", $firstLetterFromLastName)
			) {
				$errors[] = \Translations::t("Нужно ввести одну русскую букву");
			}
		}

		if (!is_null($firstLetterFromLastNameEn)) {
			if ($firstLetterFromLastNameEn !== "" &&
				!preg_match("/^[a-z]$/i", $firstLetterFromLastNameEn)
			) {
				$errors[] = \Translations::t("Нужно ввести одну английскую букву");
			}
		}

		if (empty($errors)) {
			if (!is_null($username)) {
				$user->username = $username;
			}
			if (\Translations::isDefaultLang() && !is_null($fullname)) {
				$user->fullname = $fullname;
			}
			if (!\Translations::isDefaultLang() && !is_null($fullnameEn)) {
				$user->fullnameen = $fullnameEn;
			}
			if (!is_null($timezoneId)) {
				$user->timezone = $timezone;
				$user->timezone_id = $timezoneId;
			}
			if (!is_null($details)) {
				$user->description = $details;
			}
			if (!is_null($detailsEn)) {
				$user->descriptionen = $detailsEn;
			}
			if (!is_null($countryId)) {
				$user->country_id = $countryId;
			}
			if (!is_null($cityId)) {
				$user->city_id = $cityId;
			}

			// Обновлять email нужно после обновления полей username и fullname,
			// но до сохранения модели пользователя
			if (!is_null($email) && $email != $actor->email) {
				self::updateEmail($email, $user, $messages);
			}

			if (\Translations::isDefaultLang() && !is_null($firstLetterFromLastName)) {
				$user->data->first_letter_from_last_name = $firstLetterFromLastName;
			}

			if (!\Translations::isDefaultLang() && !is_null($firstLetterFromLastNameEn)) {
				$user->data->first_letter_from_last_name_en = $firstLetterFromLastNameEn;
			}

			if (!is_null($profession)) {
				$user->data->profession = $profession;
			}

			// если логин был изменен
			$isLoginUpdated = false;
			if (!is_null($username)) {
				if (mb_strtolower($username) != mb_strtolower($actor->username)) {
					self::loginUpdated();
					$isLoginUpdated = true;
				}
			}

			$isPhotoUpdated = false;
			$deleteAvatarPhoto = is_array($deletePhoto) && $deletePhoto["avatar-photo"];
			if ($deleteAvatarPhoto) {
				$user->profilepicture = \UserManager::PROFILE_PICTURE_DEFAULT;
				$actor->profilepicture = \UserManager::PROFILE_PICTURE_DEFAULT;
			}

			if ($isPhotoUpdated || $isLoginUpdated) {
				\InboxManager::updateDialogCountForUnreadMsg($actor->id);
			}

			$deleteCoverPhoto = is_array($deletePhoto) && $deletePhoto["cover-photo"];
			if ($deleteCoverPhoto) {
				$user->cover = "default.jpg";
				$actor->cover = "default.jpg";
			} else {
				$coverFile = count($_FILES['cover-photo']['name']) > 0 && $_FILES['cover-photo']['name'][0];
				if ($coverFile) {
					$arPhotos = $_FILES["cover-photo"];
					\PhotoManager::saveCoverPhoto($actor->id, $arPhotos, $coverSize);
				}
			}

			$user->save();
			$user->data->save();

			if ($user->wasChanged(\TakeAway\ProfilesManager::PROFILE_FIELDS)) {
				// проверим поля на наличие спец. слов по обмену контактами, если они были изменены
				\TakeAway\ProfilesManager::userChangeProfile($actor->id, $user->toArray(), (array)$actor);
			}

			\UserManager::reloadActor();
		}

		return [
			"errors" => $errors,
			"message" => implode("<br>", $messages),
		];
	}

	/**
	 * Обновление общих настроек пользователя.
	 *
	 * @param string $username Имя пользователя (логин)
	 * @param int $timezoneId Идентификатор часового пояса
	 * @param string $email Электропочта
	 * @param string $password Пароль
	 * @param string $password2 Подтверждение пароля
	 *
	 * @return array [errors => [], message => "..."]
	 */
	public static function updateLoginData($username, $timezoneId, $email, $password, $password2) {
		$actor = \UserManager::getCurrentUser();
		$user = User::with("data")->find($actor->id);

		$errors = [];
		$messages = [];

		self::checkUsername($username, $errors);

		self::checkEmail($email, $errors);

		list($timezoneId, $timezone) = self::checkTimezone($timezoneId);

		if ($password !== "" || $password2 !== "") {
			$updatePassword = true;
			self::checkPassword($password, $password2, $username, $errors);
		}

		if (empty($errors)) {
			$user->username = $username;
			$user->timezone = $timezone;
			$user->timezone_id = $timezoneId;

			// Обновлять email нужно после обновления полей username и fullname,
			// но до сохранения модели пользователя
			if ($email != $actor->email) {
				self::updateEmail($email, $user, $messages);
			}

			$user->save();

			if (mb_strtolower($username) != mb_strtolower($actor->username)) {
				self::loginUpdated();
				\InboxManager::updateDialogCountForUnreadMsg($actor->id);
			}

			if ($updatePassword) {
				$userData = [
					"id" => $actor->id,
					"email" => $email,
					"lang" => $actor->lang,
				];
				\UserManager::updatePassword($password, (object) $userData);
				$messages[] = \Translations::t("На Ваш текущий email отправлено письмо для подтверждения изменений");
			}

			\UserManager::reloadActor();
		}

		return [
			"errors" => $errors,
			"message" => implode("<br>", $messages),
		];
	}

	/**
	 * Метод возвращает массив ключами en, ru которые обозначают активность поля описания для соответствющего пользователя в настройках
	 * @return array
	 */
	public static function checkActorDescriptionShow() {
		$actor = \UserManager::getCurrentUser();
		$enableRuDesc = false;
		$enableEnDesc = false;
		//Смотрим какие описания в профиле отображаются у пользователя (английское и русское или какое то одно)
		if ($actor->lang == \Translations::DEFAULT_LANG && !$actor->disableEn && (\App::config("module.lang.enable") || $actor->isLanguageTester)) {
			$enableRuDesc = true;
			$enableEnDesc = true;
		} elseif ($actor->lang == \Translations::DEFAULT_LANG) {
			$enableRuDesc = true;
		} else {
			$enableEnDesc = true;
		}
		return [\Translations::DEFAULT_LANG => $enableRuDesc, \Translations::EN_LANG => $enableEnDesc];
	}

	/**
	 * Проверка имени пользователя.
	 *
	 * @param string $username Имя пользователя (логин)
	 * @param array &$errors Ссылка на массив ошибок
	 *
	 * @return void
	 */
	private static function checkUsername(string $username, array &$errors): void {
		$actor = \UserManager::getCurrentUser();

		if ($username == "") {
			$errors[] = \Translations::t("Нужно ввести логин.");
		} elseif (!preg_match("/^[a-zA-Z0-9-_]*$/i", $username)) {
			$errors[] = \Translations::t("Логин может содержать только латинские буквы, цифры и знаки - и _");
		} elseif (mb_strlen($username) < 4) {
			$errors[] = \Translations::t("Логин должен быть не короче 4-х символов");
		} elseif (mb_strlen($username) > \UserManager::USER_LOGIN_LENGTH) {
			$errors[] = \Translations::t("Логин не может быть длиннее %s символов.", \UserManager::USER_LOGIN_LENGTH);
		} elseif (mb_strtolower($username) != mb_strtolower($actor->username) && \UserManager::isLoginChange()) {
			$errors[] = \Translations::t("Вы уже изменяли логин.");
		} elseif (mb_strtolower($username) != mb_strtolower($actor->username) && !\UserManager::checkLoginExists($username, $actor->id)) {
			$errors[] = \Translations::t("Этот логин уже используется.");
		}
	}

	/**
	 * Проверка электропочты.
	 *
	 * @param string $email Электропочта
	 * @param array &$errors Ссылка на массив ошибок
	 *
	 * @return void
	 */
	private static function checkEmail(string $email, array &$errors): void {
		$actor = \UserManager::getCurrentUser();

		if ($email == "") {
			$errors[] = \Translations::t("Нужно ввести адрес электронной почты.");
		} elseif (!verify_valid_email($email)) {
			$errors[] = \Translations::t("Адрес электронной почты указан некорректно.");
		} elseif ($email != $actor->email && \UserManager::checkEmailExists($email, $actor->id)) {
			$errors[] = \Translations::t("Адрес электронной почты %s уже используется. Укажите, пожалуйста, другой адрес.", $email);
		}
	}

	/**
	 * Проверка или получение часового пояса и его названия.
	 *
	 * @param int $timezoneId Идентификатор часового пояса
	 *
	 * @return array [$timezoneId, $timezone]
	 */
	private static function checkTimezone(int $timezoneId): array {
		if (\App::config("module.timezone.enable")) {
			if (!$timezoneId) {
				$timezone = \App::config("server.timezone");
				$timezoneId = \App::config("server.timezone_id");
			}

			$timezoneModel = \Model\Timezone::find($timezoneId);

			if (!$timezoneModel) {
				$timezone = \App::config("server.timezone");
				$timezoneId = \App::config("server.timezone_id");
			} else {
				$timezone = $timezoneModel->utc_offset;
				$timezoneId = $timezoneModel->id;
			}
		} else {
			$timezone = \App::config("server.timezone");
			$timezoneId = \App::config("server.timezone_id");
		}

		return [$timezoneId, $timezone];
	}

	/**
	 * Проверка пароля.
	 *
	 * @param string $password Пароль
	 * @param string $password2 Подтверждение пароля
	 * @param string $username Имя пользователя (логин)
	 * @param array &$errors Ссылка на массив ошибок
	 *
	 * @return void
	 */
	private static function checkPassword(string $password, string $password2, string $username, array &$errors): void {
		if ($password == "") {
			$errors[] = \Translations::t("Пожалуйста, введите новый пароль");
		} elseif ($password2 == "") {
			$errors[] = \Translations::t("Подтвердите новый пароль");
		} elseif (mb_strlen($password) < 5 || mb_strlen($password2) < 5) {
			$errors[] = \Translations::t("Длина пароля должна быть не менее 5 символов");
		} elseif ($password != $password2) {
			$errors[] = \Translations::t("Пароль и подтверждение пароля не совпадают");
		} elseif (\UserManager::checkSimplePassword($password, $username)) {
			$errors[] = \Translations::t("Такой пароль легко взломать. Укажите, пожалуйста, другой");
		}
	}

	/**
	 * Обновление электропочты.
	 *
	 * @param string $email Электропочта
	 * @param User &$user Ссылка на модель пользователя
	 * @param array &$messages Ссылка на массив сообщений
	 *
	 * @return void
	 */
	private static function updateEmail(string $email, User &$user, array &$messages): void {
		$actor = \UserManager::getCurrentUser();

		$encodedEmail = \Crypto::encodeString($email);
		$verifyCode = \MembersVerifyCode::insertEmail($actor->id, $encodedEmail);
		$factory = new \Factory\Letter\Service\ServiceLetterFactory();

		if (!$actor->email) {
			$user->email = $encodedEmail;
			$user->verified = "0";

			$userName = $user->fullname ?: $user->username;
			$letter = $factory->confirmations()->createConfirmationEmail($email, $userName, $verifyCode, $actor->lang);
		} else {
			$letter = $factory->confirmations()->createChangeEmail($actor->email, $email, $verifyCode, $actor->lang);
			$messages[] = \Translations::t("На Ваш текущий email отправлено письмо для подтверждения изменений. После перехода по ссылке из письма email будет изменен.");
		}

		\MailSender::send($letter);
	}

	/**
	 * Действия после обновления имени пользователя (логина).
	 *
	 * @return void
	 */
	private static function loginUpdated(): void {
		$actor = \UserManager::getCurrentUser();

		$update = DB::table("login_history")
			->where("user_id", $actor->id)
			->where("login", $actor->username)
			->update(["date_create" => DB::Raw("NOW()")]);

		if (!$update) {
			DB::table("login_history")
				->insert(["user_id" => $actor->id, "login" => $actor->username]);
		}

		\UserManager::updateLoginChange($actor->id);

		\UserManager::resetAuthHash($actor->id);
	}
}
