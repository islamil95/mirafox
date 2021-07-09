<?php
require_once dirname(__FILE__) . "/include/config.php";

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler as DebugExceptionHandler;
use Symfony\Component\Debug\DebugClassLoader;
use Core\Handler\ExceptionHandler;
use \Core\DB\Eloquent;
use \Core\Validation\Validator;
use \Core\DB\PaginatorWrapper;
use \Core\Routing\Router;

// Обработчик ошибок
if (\App::isDebugEnable()) {
	Debug::enable(E_ALL ^ E_NOTICE ^ E_STRICT);
	ErrorHandler::register();
	DebugExceptionHandler::register();
	DebugClassLoader::enable();
}

$request = Request::createFromGlobals();
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(Router::getRouterListener());
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Парсинг данных запроса в JSON формате
if (strpos($request->headers->get("Content-Type"), "application/json") === 0) {
	$data = json_decode($request->getContent(), true);
	$request->request->replace(is_array($data) ? $data : []);
}

// Eloquent
$capsule = Eloquent::getCapsule();

// Пагинация
PaginatorWrapper::boot($request);

// Validator
Validator::boot($capsule);

$kernel = new HttpKernel($dispatcher, $controllerResolver, Router::getRequestStack(), $argumentResolver);
$response = null;
try {
	$response = $kernel->handle($request);
} catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception) {
	require_once "404.php";
	die();
} catch (\Core\Exception\PageNotFoundException $exception) {
	require_once "404.php";
	die();
} catch (\Core\Exception\UnauthorizedException $exception) {
	require_once "unauthorized.php";
	die();
} catch (Exception $exception) {
	Log::routingError($exception);
	$exceptionHandler = new ExceptionHandler();
	$response = $exceptionHandler->handler($exception);
} finally {
	$response->send();
	$kernel->terminate($request, $response);
}

require_once DOCUMENT_ROOT . "/include/profile/end.php";