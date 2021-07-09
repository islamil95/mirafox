<?php


namespace Controllers\Want\Payer;


use Attribute\AttributeManager;
use Controllers\BaseController;
use Core\DB\DB;
use Core\Traits\ConfigurationTrait;
use Core\Traits\Routing\RoutingTrait;
use Enum\Redis\RedisAliases;
use Model\Category;
use Model\Kwork;
use Model\User;
use Model\Want;
use Symfony\Component\HttpFoundation\Request;

/**
 * Общий контроллер для добавления новых запросов на услуги и редактирования имеющихся
 *
 * Class AbstractCreateUpdateWantController
 * @package Controllers\Want
 */
abstract class AbstractCreateUpdateWantController extends BaseController {

	use RoutingTrait, ConfigurationTrait;

	/**
	 * Логика до рендера страницы
	 *
	 * @param Request $request запрос
	 *
	 * @return $this
	 */
	abstract protected function beforeRender(Request $request);

	/**
	 * Получить запрос на услуги
	 *
	 * @param Request $request запрос
	 *
	 * @return mixed объект запроса на услуги
	 */
	abstract protected function getWant(Request $request);

	/**
	 * Получить заголовок страницы
	 *
	 * @return string заголовок страницы
	 */
	abstract protected function getPageTitle(): string;

	/**
	 * Новый запрос на услуги или редактирование стратого
	 *
	 * @return bool новый запрос на услуги или радектирование старого
	 */
	abstract protected function isNew(): bool;

	/**
	 * Точка входа в контроллер
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function __invoke(Request $request) {
		$this->beforeRender($request);
		$want = $this->getWant($request);
		$lang = \Translations::getLang();
		if ($want && $want->lang) {
			$lang = $want->lang;
			$minPriceLimit = 300;
		} else {
			$minPriceLimit = 300;
		}
		$user = null;
		if ($this->getUserId()) {
			$user = User::find($this->getUserId());
		}

		$parameters = [
			"maxFileCount" => $this->getMaxFileCount(),
			"maxFileSize" => $this->getMaxFileSize(),
			"want" => $want,
			"isNew" => $this->isNew(),
			"pagetitle" => $this->getPageTitle(),
			"minPriceLimit" => $minPriceLimit,
			"maxPriceLimit" => \WantManager::getMaxPriceLimit($lang),
			"wantLang" => $lang,
			"minPrices" => \CategoryManager::getBasePriceByCategory($lang),
			"user" => $user,
		];

		return $this->render("wants/payer/create_edit_want", $parameters);
	}
}