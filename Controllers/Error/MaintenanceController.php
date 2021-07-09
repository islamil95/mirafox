<?php


namespace Controllers\Error;


use Controllers\BaseController;

/**
 * Контроллер для 5хх ошибок и не обработанных исключений
 *
 * Class MaintenanceController
 * @package Controllers\Error
 */
class MaintenanceController extends BaseController {

	public function __invoke() {
		return $this->render("errors/maintenance");
	}

}