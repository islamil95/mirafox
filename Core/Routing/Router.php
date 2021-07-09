<?php


namespace Core\Routing;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router as SymfonyRouter;
use \App;

final class Router {

	/**
	 * @var RouterListener
	 */
	private static $routerListener;

	private static $requestStack;

	public static function boot() {
		$baseDirectory = App::config("basedir");
		$locator = new FileLocator([$baseDirectory . "/.."]);
		$loader = new YamlFileLoader($locator);
		$requestContext = new RequestContext();
		self::$requestStack = new RequestStack();
		$options = [
			"cache_dir" => $baseDirectory . "/temporary/routes"
		];
		$routeYmlPath = "routes.yml";

		if (self::hasChanges($locator->locate($routeYmlPath), $locator->locate('routes'), $options["cache_dir"])) {
			self::removeCache($options["cache_dir"]);
		}
		$router = new SymfonyRouter(
			$loader,
			$routeYmlPath,
			$options,
			$requestContext
		);
		$matcher = $router->getMatcher();
		UrlGeneratorSingleton::init($router);
		self::$routerListener = new RouterListener($matcher, self::$requestStack);
	}

	public static function getRouterListener(): RouterListener {
		return self::$routerListener;
	}

	public static function getRequestStack(): RequestStack {
		return self::$requestStack;
	}

	/**
	 * Были ли измения в роутингах?
	 * @param string $baseYml Путь но основого файла с роутами
	 * @param string $routeDir Путь до папки c роутами
	 * @param string $cachePath Путь до папки с кешем роутов
	 * @return bool true - изменения были; false - изменений не было
	 */
	protected static function hasChanges($baseYml, $routeDir, $cachePath) {
		#$routeDir чтобы не парсить файл, что собственно и тормозит
		$lastChange = self::findNewestRouteFile($routeDir);
		$lastChange = max($lastChange, filemtime($baseYml));
		$cacheTime = self::getCacheTimestamp($cachePath);
		if ($lastChange > $cacheTime) {
			return true;
		}
		return false;
	}

	/**
	 * Получить время последнего формирования кеша
	 * @param string $cachePath Путь до папки с кешем роутов
	 * @return bool|int filemtime самого нового файла в указанной папке
	 */
	protected static function getCacheTimestamp($cachePath) {
		$files = scandir($cachePath, SCANDIR_SORT_DESCENDING);
		$newestFile = $files[0];
		return filemtime($cachePath . "/{$newestFile}");
	}

	/**
	 * timestamp последнего измененного файла в $path
	 * @param string $path
	 * @return int|mixed
	 */
	protected static function findNewestRouteFile($path) {
		$directory = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($directory);
		$return = 0;
		foreach ($iterator as $info) {
			/** @var $info \SplFileInfo */
			$fileName = $info->getFilename();
			if (in_array($fileName, ['.', '..'])) {
				continue;
			}
			$return = max($return, filemtime($info->getPathname()));
		}
		return $return;
	}

	/**
	 * Удалит кеш роутов
	 * @param string $cachePath Путь до папки с кешем роутов
	 */
	protected static function removeCache($cachePath) {
		$files = scandir($cachePath);
		foreach ($files as $file) {
			if (strpos($file, '.php') > 0) {
				unlink($cachePath . "/{$file}");
			}
		}
	}

}