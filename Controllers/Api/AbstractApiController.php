<?php

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Traits\ConfigurationTrait;
use Exception\JsonException;
use Illuminate\Database\QueryException;
use Mobile\Exception\Unexpected;
use Model\ApiLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Log;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Абстрактный класс для вызова API методов
 * @package Controllers\Api
 */
abstract class AbstractApiController extends BaseController {

	use ConfigurationTrait;

	const LOG_API_PARAMETERS_FILTER = "api_filterParams";
	const LOG_API = "api";
	const LOG_ERROR = "error";

	/**
	 * @var float время начала запроса
	 */
	private $startTime;

	/**
	 * Запуск мониторинга производительности
	 *
	 * @return AbstractApiController
	 */
	private function startPerformanceMonitoring(): self {
		$this->startTime = microtime(true);
		return $this;
	}

	/**
	 * Сохранить данные о производительности
	 *
	 * @param Request $request HTTP запрос
	 * @return AbstractApiController
	 */
	private function savePerformanceMonitoringData(Request $request): self {
		$methodName = $this->getMethodName();
		$apiLog = new ApiLog();
		$apiLog->time = round((microtime(true) - $this->startTime) * 1000);
		$apiLog->ip = $request->getClientIp();
		$apiLog->name = $methodName;
		if ($this->isUserAuthenticated()) {
			$apiLog->user_id = $this->getUserId();
			$methodName .= " - " . $this->getUserId();
		}
		Log::write($methodName, self::LOG_API);
		$apiLog->save();
		return $this;
	}

	/**
	 * Нужно ли производить мониторинг производительности
	 *
	 * @return bool результат
	 */
	private function isPerformanceMonitoringEnable(): bool {
		return (bool) $this->config("api.performance_monitoring");
	}

	/**
	 * Получить данные об ошибке для лога
	 *
	 * @param Exception $exception ошибка
	 * @return string ошибка в строковом представлении
	 */
	private function getExceptionLogData(Exception $exception): string {
		// Лог ошибок Illuminate\Database\QueryException (могут возникать при работе с базой данных через
		// модели или QueryBuilder). По умолчанию print_r($exception, true) выводит слишком много данных
		// об исключении (в т.ч. пароли), поэтому нужен кастомный формат
		if ($exception instanceof QueryException) {
			$errorData = [
				"exception" => get_class($exception),
				"message" => $exception->getMessage(),
				"sql" => $exception->getSql(),
				"bindings" => $exception->getBindings(),
			];
			return print_r($errorData, true) . Log::getStackCall();
		}
		return $exception->getMessage()."\n".$exception->getTraceAsString();
	}

	/**
	 * Обработка ошибок
	 *
	 * @param Exception $exception ошибка
	 * @return JsonResponse ответ
	 */
	private function handleException(Exception $exception) {
		if ($exception instanceof \Core\Exception\JsonException) {
			return new JsonResponse($exception->getData());
		} elseif ($exception instanceof JsonException) {
			return new JsonResponse($exception);
		}

		Log::daily($this->getExceptionLogData($exception), self::LOG_ERROR);
		return new JsonResponse(new Unexpected());
	}

	private function isJsonResponse(Request $request): bool {
		return $request->query->get("json") != "no";
	}

	/**
	 * Вход в контроллер
	 *
	 * @param Request $request HTTP запрос
	 * @return JsonResponse|Response HTTP ответ
	 */
	public function __invoke(Request $request) {
		if ($this->isParametersNotValid($request)) {
			$this->logWrongParameters($request);
		}

		if ($this->isPerformanceMonitoringEnable()) {
			$this->startPerformanceMonitoring();
		}

		try {
			$methodCallResult = $this->callMethod($request);
		} catch (Exception $exception) {
			return $this->handleException($exception);
		}

		if ($this->isPerformanceMonitoringEnable()) {
			$this->savePerformanceMonitoringData($request);
		}
		if ($this->isJsonResponse($request)) {
			return new JsonResponse($methodCallResult);
		}
		return new Response($methodCallResult);
	}

	/**
	 * Лог HTTP данных при ошибке
	 *
	 * @param Request $request HTTP запрос
	 * @return AbstractApiController
	 */
	private function logWrongParameters(Request $request): self {
		$getParameters = json_encode($request->query->all(), JSON_PRETTY_PRINT);
		Log::daily("Incorrect params: " . $getParameters, self::LOG_API_PARAMETERS_FILTER);
		return $this;
	}

	/**
	 * Получить данные от нужного метода
	 *
	 * @param Request $request HTTP запрос
	 * @return mixed
	 * @throws QueryException
	 * @throws JsonException
	 * @throws Exception
	 */
	protected abstract function callMethod(Request $request);

	/**
	 * Имя метода
	 *
	 * @return string
	 */
	protected abstract function getMethodName(): string;

	/**
	 * Парамеры для данного метода не верные
	 *
	 * @param Request $request HTTP запрос
	 * @return bool результат, false - параметры верные, true - параметры не верные
	 */
	protected abstract function isParametersNotValid(Request $request): bool;
}