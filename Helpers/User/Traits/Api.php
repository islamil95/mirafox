<?php
namespace User\Traits;

trait Api {

	/**
	 * Api функция для проверки email в настройках пользователя
	 * @global $actor 
	 * @param string post(email) - email для проверки
	 */
	public static function api_checkSettingsEmail() {
		global $actor;
		$result = ["success" => false, "message" => ""];
		$email = post("email");
		if (!$actor) {
			$result["message"] = "Ошибка. Пользователь не найден";
			return $result;
		}
		if (!$email) {
			$result["message"] = "Нужно ввести адрес электронной почты";
			return $result;
		}
		if (!verify_valid_email($email)) {
			$result["message"] = "Адрес электронной почты указан некорректно";
			return $result;
		}
		if ($actor->email != $email && self::checkEmailExists($email, $actor->id)) {
			$result["message"] = "Адрес электронной почты {$email} уже используется";
			return $result;
		}
		$result["success"] = true;
		return $result;
	}
}
