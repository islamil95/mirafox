<?php


namespace Core\Validation\Rule\Want;


use Illuminate\Contracts\Validation\Rule;

class WantDescriptionLengthRule implements Rule {

	private $maxLength;
	private $minLength;

	public function __construct(int $maxLength, int $minLength = 0) {
		$this->maxLength = $maxLength;
		$this->minLength = $minLength;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {
		$descriptionLength = mb_strlen(html_entity_decode(str_replace(["\r\n", "\n", "\t", "\r"], " ", $value)));
		return $descriptionLength > $this->minLength &&
				$descriptionLength <= $this->maxLength;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return \Translations::t("Максимальная длина описания - %s символов", $this->maxLength);
	}
}