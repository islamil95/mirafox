<?php


namespace Core\Traits\Templating;


trait RenderViewTrait {

	use TemplatingTrait;

	/**
	 * Рендер шаблона в строрку
	 *
	 * @param string $viewName имя шаблона
	 * @param array $parameters праметры
	 * @return string результат рендеринга
	 */
	protected function renderView(string $viewName, array $parameters = []): string {
		// Добавить в параметры текущий язык
		$parameters["i18n"] = \Translations::getLang();
		return $this->templating()->render($viewName, $parameters);
	}
}