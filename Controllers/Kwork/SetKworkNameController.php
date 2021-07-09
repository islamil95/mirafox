<?php


namespace Controllers\Kwork;

use Controllers\BaseController;
use Helper;
use KworkManager;
use Model\Kwork;
use Symfony\Component\HttpFoundation\Request;
use Translations;

class SetKworkNameController extends BaseController {

	/**
	 * @var int Идентификатор кворка
	 */
	protected $kworkId;

	/**
	 * @var string Новое название
	 */
	protected $title;

	/**
	 * @var array Ошибки вадилации
	 */
	protected $errors;

	public function __invoke(Request $request) {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			return $this->failure(["text" => "Пользователь не авторизирован"]);
		}

		$this->setKworkId($request);

		$this->setTitle($request);

		if ($this->validateTitle()) {
			$updated = Kwork::whereKey($this->kworkId)
				->update([
					Kwork::FIELD_GTITLE => $this->title,
				]);
			return $updated ? $this->success() : $this->failure(["text" => "Не удалось сохранить кворк"]);
		} else {
			return $this->failure($this->errors);
		}
	}

	/**
	 * Установить индетификатор выбранного кворка
	 *
	 * @param Request $request
	 */
	public function setKworkId(Request $request) {
		$kworkId = (int)$request->get("kwork_id");
		if (!$kworkId) {
			throw new \RuntimeException("Не задан идентификатор кворка");
		}
		$this->kworkId = $kworkId;
	}

	/**
	 * Установить новое название
	 *
	 * @param Request $request
	 */
	public function setTitle(Request $request) {
		$title = $request->get("name");
		$title = strip_tags(html_entity_decode($title));
		$title = str_replace("&nbsp;", " ", $title);
		$title = preg_replace("/[\t\r\n ]+/", " ", $title);
		$title = trim($title, ",. ");
		$title = force_lower($title);
		$title = mb_ucfirst(Helper::formatText($title));
		
		$this->title = $title;
	}

	/**
	 * Валидация названия кворка, все ошибки накапливаются в $this->errors
	 * 
	 * @return bool Есть ли ошибки
	 */
	public function validateTitle(): bool {
		$this->errors = [];
		if (empty($this->title)) {
			$this->errors[] = [
				"target" => "title",
				"text" => Translations::t("Введите название кворка")
			];
		} else {
			if (preg_match(KworkManager::REGEXP_TITLE, $this->title)) {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("Название кворка не должно содержать символы кроме букв, цифр, точки и запятой")
				];
			}
			if (seo_clean_titles($this->title) == "") {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("Недопустимое название кворка")
				];
			}
			if (KworkManager::checkWithExcludes($this->title) || preg_match(KworkManager::REGEXP_SYMBOL_DUPLICATE, $this->title)) {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("Название кворка не соответствует нормам русского языка"),
					"mistakes" => KworkManager::wrapDuplicateSymbols($this->title, $cnt, true),
				];
			}
			if (preg_match(KworkManager::REGEXP_BIG_WORD, $this->title)) {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("Превышена максимальная длина слов"),
					"mistakes" => KworkManager::wrapBigWord($this->title),
				];
			}
			if (preg_match(KworkManager::REGEXP_SMALL_WORD, $this->title)) {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("Текст не соответствует нормам русского языка"),
					"mistakes" => KworkManager::wrapSmallWord($this->title),
				];
			}
			if (KworkManager::checkTitleClone($this->getCurrentUserId(), $this->title, $this->kworkId)) {
				$this->errors[] = [
					"target" => "title",
					"text" => Translations::t("У вас уже есть кворк с таким названием. Измените заголовок."),
				];
			}
		}

		return empty($this->errors);
	}
}