<?php


namespace Controllers\Want\Worker;


use Core\Response\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * AJAX подгрузка параметров страницы списка проектов
 *
 * Class AjaxLoadingController
 * @package Controllers\Want\Worker
 */
class AjaxWantsListParametersController extends AjaxLoadingController {
	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return BaseJsonResponse
	 */
	public function __invoke(Request $request) {
		$parameters = $this->getWantsListParameters($request);
		return $this->success($parameters);
	}
}