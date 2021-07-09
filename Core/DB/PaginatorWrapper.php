<?php


namespace Core\DB;

use Core\Templating\ViewFactory;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Обертка над пагинатором
 *
 * Class PaginatorWrapper
 * @package Core\DB
 */
class PaginatorWrapper {

	/**
	 * Инициализация
	 * @param Request $request HTTP запрос
	 */
	public static function boot(Request $request) {

		// Paginator
		// Имя параметра, в котором хранится номер текущей страницы
		Paginator::currentPageResolver(function () use ($request) {
			return $request->get("page");
		});

		// URL в ссылках пагинатора
		Paginator::currentPathResolver(function () use ($request) {
			return strtok($request->getUri(), "?");
		});

		// Factory для рендеринга ссылок пагинатора
		Paginator::viewFactoryResolver(function () {
			return new ViewFactory();
		});

		// Шаблон для рендеринга ссылок пагинатора по умолчанию
		Paginator::defaultView("paginators/default.tpl");
	}
}