<?php


namespace Core\Validation\Rule;


use Illuminate\Contracts\Validation\Rule;

class EmailAlreadyInUsageRule implements Rule {

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {
		return ! \UserManager::checkEmailExists($value);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return \Translations::t("Адрес электронной почты, который Вы ввели, уже используется");
	}
}