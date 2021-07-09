<?php


namespace Controllers\Want\Payer\Handler;


use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Базовый абстрактный класс для обрабтки действий с запросами на услуги
 *
 * Class AbstractActionHandlerController
 * @package Controllers\Want\Payer\Handler
 */
abstract class AbstractActionHandlerController extends BaseController {

	use RoutingTrait;

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function __invoke(Request $request) {
		return $this->processAction($request);
	}

	/**
	 * Логика обработки запроса
	 *
	 * @param Request $request HTTP запрос
	 * @return JsonResponse
	 */
	abstract protected function processAction(Request $request): JsonResponse;

	/**
	 * Получить идентификатор запроса
	 *
	 * @param Request $request HTTP запрос
	 * @return int идентификатор
	 */
	protected function getWantId(Request $request):int {
		return $request->request->getInt("id");
	}

	/**
	 * @return JsonResponse
	 */
	protected function successResponse(): JsonResponse {
		return new JsonResponse([
			"result" => "success",
		]);
	}
}