<?php


namespace Controllers\Want\Payer\Handler;

use Attribute\AttributeManager;
use Controllers\BaseController;
use Core\DB\DB;
use Core\Response\BaseJsonResponse;
use Core\Traits\Routing\RoutingTrait;
use Core\Validation\Rule\EmailAlreadyInUsageRule;
use Core\Validation\Rule\CategoryRequiredAttributesRule;
use Core\Validation\Rule\Want\WantDescriptionLengthRule;
use Core\Validation\Rule\Want\WantDescriptionCloneRule;
use Core\Validation\Rule\Want\WantTitleCloneRule;
use Illuminate\Validation\Rule;
use Model\Want;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Базовый контроллер по обработки данных от форм редактирование и создания запроса на услугу
 *
 * Class AbstractCreateUpdateWantHandlerController
 * @package Controllers\Want
 */
abstract class AbstractCreateUpdateWantHandlerController extends BaseController {

	use RoutingTrait;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * Удалить файлы прикрепленные к запросу
	 *
	 * @param Request $request запрос
	 * @param int $wantId идентификатор запроса на услугу
	 * @return $this
	 */
	abstract protected function deleteAttachedFiles(Request $request, int $wantId);

	/**
	 * Перед валидацией данных
	 *
	 * @param Request $request запрос
	 * @return $this
	 */
	abstract protected function beforeValidation(Request $request);

	/**
	 * Получить ссылку для перехода в случае ошибки
	 *
	 * @param Request $request запрос
	 * @return string ссылка для перехода
	 */
	abstract protected function getRedirectionUrl(Request $request):string;

	/**
	 * Обработка данных формы создания или редактирования запроса на услуги
	 *
	 * @param Request $request запрос
	 * @return bool результат
	 */
	abstract protected function processRequest(Request $request):bool;

	/**
	 * Количество файлов доступных для загрузки
	 *
	 * @param Request $request запрос
	 * @return int количество файлов для загрузки
	 */
	abstract protected function getMaxUploadedFiles(Request $request);

	/**
	 * Набор правил валидации
	 *
	 * @param int|null $wantId Идентификатор запроса
	 * @param string $wantLang Язык запроса
	 * @param int $minPriceLimit Минимальная цена в выбранной категории
	 *
	 * @return array правила
	 */
	protected function getValidationRules($wantId = null, string $wantLang, $minPriceLimit = null):array {
		if (is_null($minPriceLimit)) {
			$minPriceLimit = 300;
		}
		$maxPriceLimit = 300;

		$rules = [
			"title" => ["required", "string", "max:55"],
			"description" => ["required", "string", new WantDescriptionLengthRule(1500)],
		];

		if ($this->isUserAuthenticated()) {
			array_push($rules["description"], new WantDescriptionCloneRule($this->getUserId(), $wantId));
			array_push($rules["title"], new WantTitleCloneRule($this->getUserId(), $wantId));
		} else {
			$rules["email"] = ["required", "email", new EmailAlreadyInUsageRule()];
		}
		return $rules;
	}

	/**
	 * Набор сообщений об ошибке
	 *
	 * @param string $wantLang Язык запроса
	 * @param int $minPriceLimit Минимальная цена в выбранной категории
	 *
	 * @return array сообщения
	 */
	protected function getValidationMessages(string $wantLang, $minPriceLimit = null):array {
		if (is_null($minPriceLimit)) {
			$minPriceLimit = number_format(\WantManager::getMinPriceLimit($wantLang), 0, ",", " ");
		}
		$maxPriceLimit = number_format(\WantManager::getMaxPriceLimit($wantLang), 0, ",", " ");
		if ($wantLang == \Translations::EN_LANG) {
			$priceLimitError = \Translations::t("Допустимая цена от $%s до $%s", $minPriceLimit, $maxPriceLimit);
		} else {
			$priceLimitError = \Translations::t("Допустимая цена от %s до %s руб.", $minPriceLimit, $maxPriceLimit);
		}
		return [
			"title.required" => \Translations::t("Введите название проекта"),
			"title.string" => \Translations::t("Введите название проекта"),
			"title.max" => \Translations::t("Максимальная длина названия - 55 символов"),
			"description.required" => \Translations::t("Введите описание проекта"),
			"description.string" => \Translations::t("Введите описание проекта"),
			"attributes.array" => \Translations::t("Некорректный формат отправки атрибутов"),
			"attributes.exists" => \Translations::t("Некорректные атрибуты"),
			"price_limit.required" => \Translations::t("Введите цену"),
			"price_limit.numeric" => \Translations::t("Некорректное значение"),
			"price_limit.between" => $priceLimitError,
			"email.required" => \Translations::t("Нужно ввести адрес электронной почты"),
			"email.email" => \Translations::t("Адрес электронной почты указан некорректно"),
		];
	}

	/**
	 * Получить название запроса
	 *
	 * @param Request $request запрос
	 * @return string название
	 */
	protected function getWantTitle(Request $request):string {
		return mb_ucfirst(trim(force_lower($request->request->get("title"))));
	}

	/**
	 * Получить описание запроса
	 *
	 * @param Request $request запрос
	 * @return string описание
	 */
	protected function getWantDescription(Request $request):string {
		$description = $request->request->get("description");
		$description = preg_replace("/(\r\n)+/", "\r\n", $description);
		$description = preg_replace("/(\n)+/", "\n", $description);
		return force_lower($description);
	}

	/**
	 * Получить идентификатор для проверки загруженного файла
	 *
	 * @param UploadedFile $file загруженный файл
	 * @return string идентификатор
	 */
	private function getFileCheckId($file):string {
		return "sizeFile" . $file->getClientOriginalName() . $file->getSize();
	}

	/**
	 * Проверка, что файл загружен не правильно.
	 *
	 * @param UploadedFile $file загруженный файл
	 * @param Request $request запрос
	 * @param array $uploadedFiles список уже обработанных файлов
	 * @return bool результат (true если загружен не верно)
	 */
	private function isUploadedFileNotValid(UploadedFile $file, Request $request, array $uploadedFiles):bool {
		$fileCheckId = $this->getFileCheckId($file);
		return !$file->isValid() ||
			! in_array($fileCheckId, $request->request->all()) ||
			in_array($fileCheckId, $uploadedFiles);
	}

	/**
	 * Обработка загруженных файлов
	 *
	 * @param Request $request запрос
	 * @param Want $want запрос на услугу
	 * @return $this
	 */
	protected function processUploadFiles(Request $request, Want $want) {
		return $this;
	}

	/**
	 * Получить статус для запроса
	 *
	 * @return string статус
	 */
	protected function getWantStatus():string {
		return $this->isWantConfirm() ? \WantManager::STATUS_ACTIVE : \WantManager::STATUS_NEW;
	}

	/**
	 * Изменить тип пользователя
	 *
	 * @return $this
	 */
	private function changeUserType() {
		if ($this->getUserType() == \UserManager::TYPE_WORKER) {
			\UserManager::changeUserType();
		}
		return $this;
	}

	/**
	 * Получить идентификатор запроса на услуги
	 *
	 * @param Request $request запрос
	 * @return int идентификатор
	 */
	protected function getWantId(Request $request) {
		return $request->request->get("want_id");
	}

	/**
	 * Получить идентификатор категории где расположен запрос на услуги
	 *
	 * @param Request $request запрос
	 * @return int идентификатор
	 */
	protected function getCategoryId(Request $request) {
		return $request->request->get("category");
	}

	public function __invoke(Request $request) {
		$this->request = $request;
		$this->beforeValidation($request);
		$wantId = $this->getWantId($request);
		$lang = \Translations::getLang();
		$wantStatusOld = $wantStatus = "";
		if ($wantId) {
			$lang = Want::where(\WantManager::F_ID, $wantId)->value(\WantManager::F_LANG);
			$wantStatusOld = Want::where(\WantManager::F_ID, $wantId)->value(\WantManager::F_STATUS);
		}
		$minPriceLimit = \CategoryManager::getCategoryBasePrice($this->getCategoryId($request), $lang);
		$validation = $this->validate($request, $this->getValidationRules($wantId, $lang, $minPriceLimit), $this->getValidationMessages($lang, $minPriceLimit));
		if ($validation->fails()) {
			return (new BaseJsonResponse())
				->setStatus(false)
				->setErrors($validation->errors());
		}
		$this->changeUserType();

		$redirectUrl = $this->getRedirectionUrl($request);

		if ($this->isUserNotAuthenticated() || !$this->processRequest($request)) {
			return $this->unHandleError();
		}

		if ($wantId) {
			if ($wantStatusOld == "cancel") {
				$wantStatus = \WantManager::STATUS_NEW;
			}
		} else {
			$wantStatus = $this->getWantStatus();
		}

		return (new BaseJsonResponse())
			->setRedirectUrl($redirectUrl)
			->setWantStatus($wantStatus);
	}

	/**
	 * Получение лимита цены запроса
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $lang Язык запроса
	 *
	 * @return float
	 */
	protected function getPriceLimit(Request $request, string $lang) {
		$priceLimit = $request->request->get("price_limit");
		$priceLimit = preg_replace("/\s+/", "", $priceLimit);
		$priceLimit = (float)str_replace(",", ".", $priceLimit);
		if ($lang == \Translations::DEFAULT_LANG && $priceLimit >= 500) {
			if ($priceLimit <= 5000) {
				$priceLimit = floor($priceLimit / 50) * 50;
			} elseif ($priceLimit <= 100000) {
				$priceLimit = floor($priceLimit / 1000) * 1000;
			} else {
				$priceLimit = floor($priceLimit / 5000) * 5000;
			}
		}
		return $priceLimit;
	}

	protected function unHandleError(){
		return (new BaseJsonResponse())
			->setStatus(false)
			->setMessage(\Translations::t("Не удалось выполнить запрос. Попробуйте позже."));
	}

	/**
	 * Получение атрибутов из запроса
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return array
	 */
	protected function getAttributes(Request $request) {
		return (array)$request->request->get("attributes", []);
	}

}