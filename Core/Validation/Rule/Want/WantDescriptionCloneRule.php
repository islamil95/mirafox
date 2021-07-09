<?php


namespace Core\Validation\Rule\Want;


use Illuminate\Contracts\Validation\Rule;

class WantDescriptionCloneRule implements Rule {

	private $userId;
	private $wantId;

	public function __construct($userId, $wantId = null) {
		$this->userId = $userId;
		$this->wantId = $wantId;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {
		return ! \WantManager::checkDescClonePercent($this->userId, $value, $this->wantId);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return \Translations::t("У вас уже есть проект с похожим описанием");
	}
}