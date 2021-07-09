{*
 * Есть 5 типов посадочных страниц, на которые льется реклама: главная, категория 2 уровня, кворк, лэндинг и каталог.
 *
 * Для этих страниц для НЕАВТОРИЗОВАННОГО пользователя подключаем только нужные js-файлы
 * с атрибутом defer (выполнение после загрузки страницы).
 *
 * В Helper::registerFooterJsFile для этих страниц атрибут defer добавляется автоматически.
 *
 * Подробнее: http://wikicode.kwork.ru/optimizaciya-skorosti-zagruzki-klyuchevyx-stranic-po-google-pagespeed/
 *}
{strip}

	{assign var="pageSpeedTypes" value=array("index", "cat", "view", "land", "catalog")}
	{if $pageName && $pageName|in_array:$pageSpeedTypes && !$actor}
		{* подключаем только используемые на посадочных страницах скрипты *}
		{assign var="pageSpeedScripts" value=array(
		"/js/jquery.min.1.9.1.js",
		"/js/fox.js",
		"/js/js.cookie.js",
		"/js/jquery.touchSwipe.min.js",
		"/js/pagespeed.js"
		)}

		{if $pageName != "indexTopfreelancer"}
			{$pageSpeedScripts[]="/js/dist/general-search.js"}
		{/if}

		{if $pageName == "index" || $pageName == "cat" || $pageName == "land"}
			{$pageSpeedScripts[]="/js/slick.min.js"}
		{/if}

		{if $pageName == "cat" && !$parent && $CATID != "all"}
			{$pageSpeedScripts[]="/js/jquery.kworkcarousel.min.js"}
		{/if}

		{if $pageName == "view"}
			{$pageSpeedScripts[]="/js/portfolio_view_popup.js"}
		{/if}

		{if $pageName == "catalog" && $pageSpeedMobile}
			{$pageSpeedScripts[]="/js/components/allAttributes.min.js"}
			{$pageSpeedScripts[]="/js/components/tooltipster.bundle.min.js"}
		{/if}

		{* десктопная версия *}
		{if !$pageSpeedMobile}
			{$pageSpeedScripts[]="/js/chosen-compatible.jquery.min.js"}
			{$pageSpeedScripts[]="/js/components/allAttributes.min.js"}
			{$pageSpeedScripts[]="/js/components/tooltipster.bundle.min.js"}
			{$pageSpeedScripts[]="/js/chosen.jquery.js"}
		{/if}

		{foreach from=$pageSpeedScripts item=pageSpeedScript}
			{Helper::printJsFile($pageSpeedScript|cdnBaseUrl, 1)}
		{/foreach}

		<script>
			{if $pageName == "cat" || $pageName == "land"}{literal}
			//проверить, поддерживает ли браузер формат webp
			!function(){var A=new Image;A.onload=A.onerror=function(){2===A.height&&(document.getElementsByTagName("body")[0].className+=" is_webp")},A.src="data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA"}();
			{/literal}{/if}{literal}

			window.addEventListener('DOMContentLoaded', function() {
				{/literal}{if $pageName == "catalog"}{literal}
				//отложенная подгрузка изображений при попадании во viewport
				lazyLoadImgViewport();
				window.addEventListener('scroll', lazyLoadImgViewport);
				{/literal}{/if}{literal}

				//отложенная подгрузка изображений при скроле
				lazyLoadImgScroll();
			});

			{/literal}
		</script>

	{else}

		{*
		 * Внимание! Следующая информация носит информативный характер и не претендует на 100% истинность.
		 * Использовать как ориентир, но не панацею.
		 *
		 * youtube.com/iframe_api
		 * api ютюба
		 * используется для обложек видео на страницах кворков
		 *
		 * jquery.min.1.9.1.js
		 * собственно, jQuery :)
		 * основа всего на проекте
		 *
		 * jquery.mb.browser.min.js
		 * определение браузера, далеко не всегда точное,
		 * например, мобильный Chrome на iOS определяет как Safari
		 * используется в плагине для выделения/обрезки области изображения + в мобильной переписке
		 *
		 * jquery.jscrollpane.min.js
		 * кастомный сколлбар
		 * используется в корзине для авторизованного пользователя
		 *
		 * slick.min.js
		 * слайдер
		 * используется на многих страницах
		 *
		 * chosen-compatible.jquery.min.js
		 * адаптация chosen для мобильных
		 * используется на ВСЕХ страницах при регистрации на англоверсии сайта
		 *
		 * fox.js
		 * основной файл наших функций
		 * используется на ВСЕХ страницах
		 *
		 * formDataFilter.js
		 * хак для input type="file" в Safari в iOS/macOS Safari
		 * используется на странице заказа + при создании/редактировании кворков и проектов
		 *
		 * jquery.mousewheel.min.js
		 * древний плагин, добавляющий кроссбраузерную поддержку колеса мыши с нормализацей дельты
		 * используется для блокировки скролла при наведении курсора на всплывающую корзину у авторизованного пользователя
		 * при использовании chosen для кастомных селектов в Firefox замедляет скролл :)
		 * решение: jQuery(chosenSelector).find('.chosen-results').unbind('mousewheel')
		 *
		 * portfolio_view_popup.js
		 * попап большого портфолио
		 * используется на страницах с портфолио
		 *
		 * js.cookie.js
		 * JavaScript API для кук
		 * используется по всему сайту
		 *
		 * cropper.min.js и jquery-cropper.min.js
		 * плагин для выделения/обрезки области изображения
		 * используется при создании/редактировании кворков
		 *
		 * jquery.touchSwipe.min.js
		 * модуль взаимодействия пальцев с экраном для мобильных
		 * используется для открытия меню свайпом слева направо + в каруселях кворков
		 *
		 * tabs-interaction.js
		 * модуль взаимодействия вкладок
		 * используется в модуле уведомлений для авторизованного пользователя
		 *
		 * allAttributes.min.js
		 * плагин для возврата всех атрибутов без указания аргументов
		 * используется для тултипов в десктопной версии
		 *
		 * tooltipster.bundle.min.js
		 * плагин для тултипов
		 * используется повсеместно в десктопной версии
		 *
		 * general-search.js
		 * верхний поиск
		 * используется на ВСЕХ страницах
		 *}

		{* остальные страницы *}
		{Helper::printJsFile("/js/jquery.min.1.9.1.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/jquery.mb.browser.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/jquery.jscrollpane.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/chosen-compatible.jquery.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/url-search-params.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/formDataFilter.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/fox.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/jquery.mousewheel.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/js.cookie.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/cropper.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/jquery-cropper.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/jquery.touchSwipe.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/components/allAttributes.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/components/tooltipster.bundle.min.js"|cdnBaseUrl)}

		{if $needToGetFingerprint}
			{* Пока это нужно только для авторизованных пользователей раз в день*}
			{* Используем название fing.js чтобы не наводить подозрения всяких автоблокировщиков*}
            {Helper::registerFooterJsFile("/js/dist/fing.js"|cdnBaseUrl)}
		{/if}
	{/if}

	{if $isWorkbayApp}
		{Helper::registerFooterJsFile("/js/dist/workbay.js")}
	{/if}

	{if App::config("app.mode") == "dev"}
		<meta name="robots" content="noindex">
	{/if}

{/strip}