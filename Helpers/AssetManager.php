<?php

class AssetManager {

	const CSS_FOLDER = "/css/";
	const CSS_MINIFIED = "minified.css";
	const CSS_MINIFIED_BASIC = "minified_basic.css";

	/**
	 * Получает массив склеиваемых файлов в зависимости от страницы $page
	 *
	 * @param string $page
	 * @return array
	 */
	private static function cssFiles($page = "all") {
		if ($page == "basic") {
            return [
                "fox_style_v7.css",
                "response.css",
                "font-awesome.min.css",
                "chosen.css",
                "slick-theme.css",
                "slick.css",
                "icon.css",
                "kwork-set-icon.css",

                "components/tooltipster.bundle.min.css",
                "components/tooltipster.themes.css",

                "dist/general-search.css",
                "dist/index.css",
            ];
		} else {
			return [
				"fox_style_v7.css",
				"response.css",
				"font-awesome.min.css",
                "jquery.jscrollpane.css",
                "analytics.css",
                "authorized.css",
                "c3.min.css",
                "chosen.css",
                "imgareaselect-default.css",
                "jquery-ui.css",
                "manage_kworks.css",
                "project.css",
                "queries.css",
                "slick-theme.css",
                "slick.css",
                "track.css",
                "user.css",
                "view.css",
                "widget_constructor.css",
                "icon.css",
                "jquery.kworkcarousel.css",
                "kwork-set-icon.css",
                "cropper.min.css",

                "components/file-uploader.css",
                "components/js-tooltip.css",
                "components/tooltipster.bundle.min.css",
                "components/tooltipster.themes.css",

				"dist/general-search.css",

				"pages/polls/polls.css",
				"pages/balance/balance.css",
				"pages/cases/detail.css",
				"pages/support/support_rating.css",
				"pages/conversations.css",
				"pages/promo.css",
				"pages/save_kwork.css",

				// portfolio
				"pages/portfolio/portfolio_view_popup.css",
				"pages/portfolio/portfolio_page.css",
				"pages/portfolio/portfolio_card.css",
				"pages/portfolio/portfolio_card_collage.css",
			];
		}
	}

	/**
	 * Получает путь до файла
	 *
	 * @param string $page
	 * @return string
	 */
	private static function minifiedCssPath($page = "all") {
		return App::config('basedir') . self::CSS_FOLDER . ($page == "basic" ? self::CSS_MINIFIED_BASIC : self::CSS_MINIFIED);
	}

	/**
	 *  Для dev окружений - проверяет были ли изменения в файлах и запускает
	 *  объедениение и минификацию файлов
	 *
	 * @param string $page
	 * @return void
	 */
	public static function devCheckAndMinify($page = "all") {
		if(App::config("app.mode") == "stage") {
			return;
		}

		$minifiedModificationDate = filemtime(self::minifiedCssPath($page));

		$files = self::cssFiles($page);
		foreach ($files as $item) {
			$absolutePath = App::config('basedir') . self::CSS_FOLDER . $item;
			if(filemtime($absolutePath) > $minifiedModificationDate) {
				self::minifyCss($page);
				break;
			}
		}
	}

	/**
	 * Объединяет и минифицирует css файлы
	 *
	 * @param string $page
	 * @return void
	 */
	public static function minifyCss($page = "all") {
		$minifier = new MatthiasMullie\Minify\CSS();

		foreach (self::cssFiles($page) as $item) {
			$absolutePath = App::config('basedir') . self::CSS_FOLDER . $item;
			if (file_exists($absolutePath)) {
				$minifier->add(file_get_contents($absolutePath));
			}
		}

		file_put_contents(self::minifiedCssPath($page), $minifier->minify());
	}
}
