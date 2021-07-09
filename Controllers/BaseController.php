<?php

namespace Controllers;

use Core\Exception\PageNotFoundException;
use Core\Traits\AuthTrait;
use Core\Traits\Templating\RenderViewTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Core\Validation\Validator;
use \Illuminate\Validation\Validator as IlluminateValidator;
use \Core\Response\BaseJsonResponse;

/**
 * Базовый контроллер
 *
 * Class BaseController
 * @package Controllers
 */
class BaseController {
	use AuthTrait;
	use RenderViewTrait;

	protected $session;

	/**
	 * Включена ли страница на Topfreelancer (Kwork.Connect)
	 *
	 * @var bool
	 */
	protected $projectTopfreelancer = true;

	/**
	 * Включена ли страница в проекте Kwork
	 *
	 * @var bool
	 */
	protected $projectKwork = true;

	public function __construct() {
		$this->registerNewrelicTrace();
		$this->session = \Session\SessionContainer::getSession();
	}

	/**
	 * Добавить сегментацию роутингов для newrelic
	 */
	private function registerNewrelicTrace() {
		$class = strtr(get_class($this), [
			'Controllers\\' => '',
			'\\' => '/',
		]);

		if (extension_loaded('newrelic') && function_exists('newrelic_name_transaction')) {
			newrelic_name_transaction("/{$class}");
		}
	}

	/**
	 * Задать урл возврата на основе запрошенной страницы
	 *
	 * @param string $backUrl
	 * @return $this
	 */
	protected function setBackUrl($backUrl = false) {
		$request = Request::createFromGlobals();
		$url = $backUrl ? $backUrl : $request->getRequestUri();
		$this->session->set("backURL", $url);
		return $this;
	}

	/**
	 * Добавить всплывающее сообщение об ошибке
	 *
	 * @param string $error ошибка
	 * @return $this
	 */
	protected function addFlashError(string $error) {
		$this->session->set("flashError", $error);
		return $this;
	}

	/**
	 * Добавить всплывающее сообщение
	 *
	 * @param string $message сообщение
	 * @return $this
	 */
	protected function addFlashMessage(string $message) {
		$this->session->set("flashMessage", $message);
		return $this;
	}

	/**
	 * Добавить в массив параметров флеш сообщения об ошибках и прочие уведомления
	 *
	 * @param array $parameters старые параметры
	 * @return mixed новые параметры
	 */
	protected function addFlash(array $parameters = []) {
		// flash message
		if ($this->session->notEmpty("flashMessage")) {
			$parameters["message"] = $this->session->get("flashMessage");
			$this->session->delete("flashMessage");
		}
		// flash error
		if ($this->session->notEmpty("flashError")) {
			$parameters["error"] = $this->session->get("flashError");
			$this->session->delete("flashError");
		}
		return $parameters;
	}

	/**
	 * Рендер шаблона и ответ
	 *
	 * @param string $template шаблон
	 * @param array $parameters параметры
	 * @return Response ответ
	 */
	protected function render(string $template, array $parameters = []): Response {
		// проверка на доступность страницы на текущем домене
		$this->validateProject();

		// передаем в шаблон переменную, указывающую, что страница в общем доступе
		if ($this->projectKwork && $this->projectTopfreelancer) {
			$parameters["isMultiProject"] = true;
		}

		$renderedTemplate = $this->renderView($template, $this->addFlash($parameters));
		return new Response($renderedTemplate);
	}

	/**
	 * Вернуть JSON ответ {success: true, data: $data}
	 *
	 * @param mixed $data
	 * @return BaseJsonResponse
	 */
	public function success($data = null) {
		$response = new BaseJsonResponse();
		return $response->setStatus(true)
			->setResponseData($data);
	}

	/**
	 * Вернуть JSON ответ {success: false, data: $data}
	 *
	 * @param mixed $data
	 * @return BaseJsonResponse
	 */
	public function failure($data = null) {
		$response = new BaseJsonResponse();
		return $response->setStatus(false)
			->setResponseData($data);
	}

	/**
	 * Проверить параметры запроса/массив параметров
	 *
	 * @param array \Symfony\Component\HttpFoundation\Request|array $data данные для проверки - запрос или массив параметров
	 * @param array $rules правила проверки параметров
	 * @param array $messages кастомные сообщения об ошибках
	 * @param array $customAttributes кастомные названия параметров
	 * @return IlluminateValidator
	 */
	public function validate($data, array $rules, array $messages = [], array $customAttributes = []): IlluminateValidator {
		if (is_array($data)) {
			$parameters = $data;
		} else {
			$parameters = array_merge(
				$data->query->all(),
				$data->request->all(),
				$data->files->all()
			);
		}
		return Validator::make($parameters, $rules, $messages, $customAttributes);
	}

	/**
	 * Проверяет, если отображение идет на проекте Kwork
	 *
	 * @return bool
	 */
	public function isProjectKwork(): bool {
		return !\App::isConnectSubdomain();
	}

	/**
	 * Проверяет, если отображение идет на проекте Topfreelancer
	 *
	 * @return bool
	 */
	public function isProjectTopfreelancer(): bool {
		return \App::isConnectSubdomain();
	}

	/**
	 * Проверка и выброс 404 Not Found в случае, если страница отображается на
	 * проекте, к которому не принадлежит
	 *
	 * @throws PageNotFoundException
	 */
	public function validateProject() {
		if ($this->isProjectTopfreelancer() && !$this->projectTopfreelancer) {
			throw new PageNotFoundException();
		}

		if ($this->isProjectKwork() && !$this->projectKwork) {
			throw new PageNotFoundException();
		}
	}

}