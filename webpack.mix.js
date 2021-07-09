let mix = require("laravel-mix");

mix.setPublicPath('./');

// Алиасы до папок
mix.webpackConfig({
	resolve: {
		alias: {
			// Модули NPM
			npm: path.resolve(__dirname, "node_modules"),
			// Модуль NPM Bootstrap 4
			npmBootstrap4: path.resolve(__dirname, "node_modules/bootstrap/scss"),			
			// CSS старые
			oldCss: path.resolve(__dirname, "public_html/css"),
			// JS старые
			oldJs: path.resolve(__dirname, "public_html/js"),			
			// JS Пользователь и общие
			appJs: path.resolve(__dirname, "public_html/js/app"),
			// CSS Пользователь и общие
			appCss: path.resolve(__dirname, "public_html/css/app"),
			// JS Наши модули - Пользователь и общие
			moduleJs: path.resolve(__dirname, "public_html/js/pages"),
			// CSS Наши модули - Пользователь и общие
			moduleCss: path.resolve(__dirname, "public_html/css/pages"),
		}
	}
});

// Общий поиск
mix.js("public_html/js/pages/general-search/bootstrap.js", "public_html/js/dist/general-search.js")
	.sass("public_html/css/pages/general-search/bootstrap.scss", "public_html/css/dist/general-search.css");

// Категории первого уровня
mix.sass("public_html/css/pages/parent-category/bootstrap.scss", "public_html/css/dist/parent-category.css");

// Страница кворка - пользователь
mix.js("public_html/js/pages/kwork-view/bootstrap.js", "public_html/js/dist/kwork-view.js")
	.sass("public_html/css/pages/kwork-view/bootstrap.scss", "public_html/css/dist/kwork-view.css");

// Страница просмотра портфолио
mix.js("public_html/js/pages/portfolio-view/bootstrap.js", "public_html/js/dist/portfolio-view.js")
	.sass("public_html/css/pages/portfolio-view/bootstrap.scss", "public_html/css/dist/portfolio-view.css");
	
// Всплывающее окно
mix.sass("public_html/css/jquery.kworkpopup.scss", "public_html/css/dist/jquery.kworkpopup.css");

// Создание или редактирование кворка - пользователь
mix.js("public_html/js/pages/kwork-edit/bootstrap.js", "public_html/js/dist/kwork-edit.js")
	.sass("public_html/css/pages/kwork-edit/bootstrap.scss", "public_html/css/dist/kwork-edit.css");

// Главная
mix.js("public_html/js/pages/index/bootstrap.js", "public_html/js/dist/index.js")
	.sass("public_html/css/pages/index/bootstrap.scss", "public_html/css/dist/index.css");

// Проекты
mix.sass("public_html/css/pages/campaigns/bootstrap.scss", "public_html/css/dist/campaigns.css");

// Настройки профиля - пользователь
mix.js("public_html/js/pages/profile-settings/bootstrap.js", "public_html/js/dist/profile-settings.js")
	.sass("public_html/css/pages/profile-settings/bootstrap.scss", "public_html/css/dist/profile-settings.css");

// Просмотр профиля - пользователь
mix.js("public_html/js/pages/profile/bootstrap.js", "public_html/js/dist/profile.js")
	.sass("public_html/css/pages/profile/bootstrap.scss", "public_html/css/dist/profile.css");

// Переписка
mix.js("public_html/js/pages/conversations/bootstrap.js", "public_html/js/dist/conversations.js")
	.sass("public_html/css/pages/conversations/bootstrap.scss", "public_html/css/dist/conversations.css");

// Переписка (только личка)
mix.js("public_html/js/pages/conversations/bootstrap-conversations-bit.js", "public_html/js/dist/conversations-bit.js");

// Просмотр категорий (Кворки)
mix.sass("public_html/css/pages/categories/bootstrap.scss", "public_html/css/dist/categories.css");

// Просмотр категорий (Работы)
mix.js("public_html/js/pages/portfolio-categories/bootstrap.js", "public_html/js/dist/portfolio-categories.js")
	.sass("public_html/css/pages/portfolio-categories/bootstrap.scss", "public_html/css/dist/portfolio-categories.css");

// Трек заказа
mix.js("public_html/js/pages/track/bootstrap.js", "public_html/js/dist/track.js")
	.sass("public_html/css/pages/track/bootstrap.scss", "public_html/css/dist/track.css");

// Мои работы
mix.js("public_html/js/pages/my-portfolios/bootstrap.js", "public_html/js/dist/my-portfolios.js")
.sass("public_html/css/pages/my-portfolios/bootstrap.scss", "public_html/css/dist/my-portfolios.css");

// Черновики изображений - страница тестирования
mix.js("public_html/js/pages/temp-image-test/bootstrap.js", "public_html/js/dist/temp-image-test.js")
	.sass("public_html/css/pages/temp-image-test/bootstrap.scss", "public_html/css/dist/temp-image-test.css");

// Мои заказы - покупатель (orders)
mix.js("public_html/js/pages/orders/bootstrap.js", "public_html/js/dist/orders.js")
	.sass("public_html/css/pages/orders/bootstrap.scss", "public_html/css/dist/orders.css");

// Заказы - продавец (manage_orders)
mix.js("public_html/js/pages/manage_orders/bootstrap.js", "public_html/js/dist/manage_orders.js")
	.sass("public_html/css/pages/manage_orders/bootstrap.scss", "public_html/css/dist/manage_orders.css");

// Предложения (offers)
mix.js("public_html/js/pages/offers/bootstrap.js", "public_html/js/dist/offers.js")
	.sass("public_html/css/pages/offers/bootstrap.scss", "public_html/css/dist/offers.css");

// Биржа - продавец (/projects /new_offer /edit_offer)
mix.js("public_html/js/pages/projects/bootstrap.js", "public_html/js/dist/projects.js")
	.sass("public_html/css/pages/projects/bootstrap.scss", "public_html/css/dist/projects.css");

// Биржа - покупатель - список проектов (/manage_projects)
mix.js("public_html/js/pages/manage-projects/bootstrap.js", "public_html/js/dist/manage-projects.js")
.sass("public_html/css/pages/manage-projects/bootstrap.scss", "public_html/css/dist/manage-projects.css");

// Биржа - покупатель - просмотр предложения (/project)
mix.js("public_html/js/pages/project/bootstrap.js", "public_html/js/dist/project.js")
	.sass("public_html/css/pages/project/bootstrap.scss", "public_html/css/dist/project.css");

// Аналитика продаж
mix.sass("public_html/css/pages/analytics/bootstrap.scss", "public_html/css/dist/analytics.css");

// Создание (редактирование) запроса на услугу (new_project)
mix.js("public_html/js/pages/create-edit-want/bootstrap.js", "public_html/js/dist/create-edit-want.js")
	.sass("public_html/css/pages/create-edit-want/bootstrap.scss", "public_html/css/dist/create-edit-want.css");

// Предложить услугу (new_offer?)
mix.js("public_html/js/pages/new-offer/bootstrap.js", "public_html/js/dist/new-offer.js")
	.sass("public_html/css/pages/new-offer/bootstrap.scss", "public_html/css/dist/new-offer.css");

// promo страница /newyear2019
mix.sass("public_html/css/pages/promo/bootstrap-newyear2019.scss", "public_html/css/dist/promo-newyear2019.css");

// Избранное /bookmarks
mix.js("public_html/js/pages/bookmarks/bootstrap.js", "public_html/js/dist/bookmarks.js")
	.sass("public_html/css/pages/bookmarks/bootstrap.scss", "public_html/css/dist/bookmarks.css");

// компонент загрузки файлов
mix.js("public_html/js/components/file-uploader.js", "public_html/js/dist/components/file-uploader.js");

// Обучение продавцов
mix.js("public_html/js/pages/education/bootstrap.js", "public_html/js/dist/education.js")
	.sass("public_html/css/pages/education/bootstrap.scss", "public_html/css/dist/education.css");

// Мои кворки
mix.js("public_html/js/pages/kwork-manage/bootstrap.js", "public_html/js/dist/kwork-manage.js")
	.sass("public_html/css/pages/kwork-manage/bootstrap.scss", "public_html/css/dist/kwork-manage.css");