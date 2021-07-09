<template>
	<div v-if="loaded" class="quick-faq">
		<!-- Виджет -->
		<div class="widget" v-show="shown">
			<div class="widget-header">
				<!-- Кнопка Назад -->
				<div @click="back" v-if="history.length > 0" class="back-wrapper">
					<div class="back"></div>
				</div>
				<!-- Заголовок виджета -->
				<div class="role-select">
					<div class="selected-role">
						<b>{{ t("Вопросы - Ответы") }}</b>
					</div>
				</div>			
				<!-- Поиск -->
				<quick-faq-search :role="role" :articles="articles" 
					:initial-search="initialSearch" ref="searchComponent"
					@input="onSearch">
				</quick-faq-search>
			</div>
			<div class="widget-body">
				<!-- Хлебные крошки -->
				<breadcrumbs v-show="showBreadcrumbs" :breadcrumbs="path" 
					@input="onArticleSelected" :breadcrumb-label="'question'">
				</breadcrumbs>
				<!-- Результаты поиска -->
				<div v-show="showSearchArticles">
					<div class="section-title">
						{{ t("Результаты поиска по запросу") }} "{{ search }}"
					</div>
					<quick-faq-tree-select 
						:tree="foundArticles" 
						:use-alt-question="true" 
						@input="onArticleSelected">
					</quick-faq-tree-select>
				</div>
				<!-- Поиск не дал результатов -->
				<div v-show="showSearchNotFoundMessage" class="section-title">
					{{ t("К сожалению, по этому запросу ничего не найдено. Сформируйте другой запрос.") }}
				</div>
				<!-- Популярные статьи -->
				<div v-show="showFeaturedArticles">
					<div class="section-title">
						{{ t("Популярные статьи") }}
					</div>
					<quick-faq-tree-select
						:tree="filteredFeaturedArticles" 
						:use-alt-question="true" 
						@input="onArticleSelected">
					</quick-faq-tree-select>
				</div>
				<!-- Дерево статей -->
				<div v-show="showTree">
					<div v-if="showFeaturedArticles" class="section-title">
						{{ t("Вопросы - Ответы") }}
					</div>
					<quick-faq-tree-select 
						:tree="filteredArticles"
						:parent-id="article ? article.id : null"
						:customSort="customArticlesSort"
						@input="onArticleSelected">
					</quick-faq-tree-select>
				</div>
				<!-- Выбранная статья -->
				<div v-if="showArticle" class="article">
					<div class="question">{{ article.question }}</div>
					<div class="fr-view answer" v-html="article.answer_formatted"></div>
				</div>
			</div>
		</div>
		<!-- Кнопка для вызова/закрытия виджета -->
		<div class="start-button" @click="toggleWidget">
			<i class="fa" :class="{'fa-question': !shown, 'fa-times animate': shown}" aria-hidden="true" id="testicon"></i>
		</div>
	</div>
</template>

<script>

/**
 * Компонент отображает виджет "Быстрый FAQ". Виджет запускается при клике на значке
 * вопроса в нижнем правом углу экрана. После запуска пользователь может посмотреть
 * популярные статьи базы знаний, все статьи (с навигацией по дереву при помощи
 * хлебных крошек), а также быстро найти любую статью в базе. Состояние виджета сохраняется
 * в сессии и восстанавливается при перезагрузке страницы или переходе на другие страницы,
 * где есть данный виджет.
 */

// Статьи базы знаний
Vue.component("quick-faq-tree-select", require("appJs/quick-faq/quick-faq-tree-select.vue").default);
// Хлебные крошки
Vue.component("breadcrumbs", require("appJs/breadcrumbs.vue").default);
// Поиск
Vue.component("quick-faq-search", require("appJs/quick-faq/quick-faq-search.vue").default);
// Фильтрация и сортировка статей базы знаний
import KBArticlesFilterMixin from "moduleJs/kb/kb-articles-filter-mixin";
// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [KBArticlesFilterMixin, i18nMixin],

	data () {
		return {
			// Выбранная роль
			role: null,
			// Статьи базы знаний
			articles: [],
			// Выбранная статья
			article: null,
			// У выбранной статьи есть потомки в выбранной роли
			has_childs: true,
			// Путь до выбранной статьи (массив статей)
			path: [],
			// Найденные через поиск статьи
			// (null - поиск не был выполнен, [] - поиск был выполнен, но вернул пустой список)
			foundArticles: null,
			// Поисковый запрос
			search: "",
			// Выполнить поиск по запросу initialSearch при первой загрузке компонента
			initialSearch: "",
			// Ид популярных статей
			featuredArticles: [],
			// История действий пользователя
			history: [],
			// Запросы на сервер
			// (для возможности отмены)
			axiosRequest: null,
			// Виджет открыт
			shown: false,
			// Компонент готов к загрузке дочерних компонент
			loaded: false,
			// Локализация компонента
			i18n: {
				en: {
					"Результаты поиска по запросу": "Search Results for",
					"К сожалению, по этому запросу ничего не найдено. Сформируйте другой запрос.": "Sorry, we couldn't find any results for your search.",
					"Популярные статьи": "Featured Articles",
					"Вопросы - Ответы": "Questions and Answers",
				}
			},			
		};
	},

	computed: {

		// Показывать результаты поиска
		showSearchResults: function () {
			return this.foundArticles !== null;
		},

		// Показывать результаты поиска - список статей
		showSearchArticles: function () {
			// Если поиск выполнялся и найдена хотя бы одна статья
			return this.showSearchResults && this.foundArticles.length > 0;
		},

		// Показывать результаты поиска - сообщение о том, что ничего не найдено
		showSearchNotFoundMessage: function () {
			// Если поиск выполнялся и не найдено ни одной статьи
			return this.showSearchResults && this.foundArticles.length == 0;
		},

		// Показывать популярные статьи
		showFeaturedArticles: function () {
			// Если не выбрана статья и в текущий момент не отображаются
			// результаты поиска
			return (this.article === null && !this.showSearchResults) || this.showSearchNotFoundMessage;
		},

		// Показывать хлебные крошки
		showBreadcrumbs: function () {
			// Если выбрана какая-нибудь статья
			// и в текущий момент не отображаются результаты поиска
			return this.article !== null && !this.showSearchResults;
		},

		// Показывать дерево статей
		showTree: function () {
			// Если у выбранной статьи есть дочерние
			// и в текущий момент не отображаются результаты поиска
			return this.has_childs && !this.showSearchResults;
		},

		// Показывать выбранную статью
		showArticle: function () {
			// Если у выбранной статьи нет дочерних и
			// в текущий момент не отображаются результаты поиска
			return !this.has_childs && !this.showSearchResults;
		},

		// Популярные статьи
		filteredFeaturedArticles: function () {
			var articlesIds = this.featuredArticles;
			var articles = this.getFlatArticleListByIds(articlesIds);
			// Если популярная статья - это раздел (содержит дочерние статьи в выбранной роли),
			// добавить к популярной статье фейковую дочернюю, чтобы иконка у популярной статьи
			// изменилась с "документа" на "папку"
			var self = this;
			_.forEach(articles, function (article) {
				if (article.has_childs.indexOf(self.role.id) >= 0) {
					var childFakeArticle = _.cloneDeep(article);
					childFakeArticle.parent_id = childFakeArticle.id;
					childFakeArticle.id = -childFakeArticle.id;
					articles.push(childFakeArticle);
				}
			});
			return articles;	
		},

	},

	watch: {

		// Выбрана статья из дерева
		article: function () {
			// Пересчитать путь до статьи и определить наличие дочерних статей
			this.has_childs = this.getHasChilds(this.article);
			this.path = this.getPath(this.article);
		},

	},

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
		// Подписаться на клики ссылок внутри статей, которые ведут
		// на другие статьи, чтобы они "открывались" в окне виджета
		var self = this;
		window.bus.$on("quickFaqHyperlinkClicked", function (data) {
			self.onHyperlinkClicked(data);
		});
		// Загрузить данные для инициализации компонента
	    axios.post("/quick-faq/init", {
	    	// Относительный путь без / в начале
	    	page: window.location.pathname.substring(1),
	    })
      		.then( (response) => {
						// Если виджет недоступен для данной старницы, выйти
						if (!response || !response.data || !response.data.data)
							return;
      			if (!response.data.data.enabled) {      				
      				return;
      			}
      			this.role = response.data.data.role;
      			var articles = response.data.data.articles;
      			// Добавить статьям альтернативный заголовок, если заголовок
      			// равен "Я - покупатель" или "Я - продавец" (в т.ч. для англокворка)
      			var self = this;
      			_.forEach(articles, function (article) {
      				article.alt_question = article.question;
      				if (article.question == "Я - покупатель" || 
      					article.question == "Я - продавец" || 
      					article.question == "I Am a Buyer" || 
      					article.question == "I Am a Seller") {
      					if (article.parent) {
      						article.alt_question = article.parent.question;
      					}
      				}
      			});
				this.articles = articles;
					this.featuredArticles = response.data.data.featuredArticles;
		    	// Заменить ссылки внутри статей, которые ведут на другие статьи из базы знаний
		    	// на JavaScript код, генерирующий событие в общую шину, чтобы ссылки
		    	// "открывались" внутри виджета
      			_.forEach(this.articles, function (article, index) {
							if (article.answer_formatted) {
								var hyperlynk = "<a onClick='window.bus.$emit(\"quickFaqHyperlinkClicked\", [$1, $2])' href='#'>";
								var regExp = /<a href="[^>]*?\?role=(\d+)&article=(\d+).*?">/g;
								var answerFormatted = article.answer_formatted.replace(regExp, hyperlynk);
								self.articles[index].answer_formatted = answerFormatted;
							}
      			});
		    	// Восстановить последнее состояние компонента из сессии
		    	var state = response.data.data.state;
		    	if (state && !this.featuredArticles) {
		    		// Выбрать роль
		    		this.setRoleById(state.role);
		    		// Если задана строка поиска
					if (state.search != "") {
						var self = this;
						// Загрузить результаты поиска
						this.search = state.search;
						this.initialSearch = state.search;
						this.foundArticles = this.getFlatArticleListByIds(state.foundArticles);
					} else {
						// Выбрать статью
						this.setArticleById(state.article);
					}
		    	}
		    	// Компонент готов к загрузке дочерних компонент
      			this.loaded = true;
			});
	},

	methods: {

		/**
		 * Определить, есть ли у статьи дочение в выбранной роли
		 * @param {object} article
		 * @return {boolean}
		 */
		getHasChilds: function (article) {
			if (article) {
 				return _.findIndex(this.filteredArticles, ["parent_id", article.id]) >= 0;
 			} else {
 				return true;
 			}
		},

		/**
		 * Получить путь до статьи
		 * @param  {object} article
		 * @return {array} все родители статьи вплоть до корня
		 */
		getPath: function (article) {
			if (!article) {
				return [];
			}
			var path = [];
			// Добавить текущую статью
			path.push(article);
			// Собрать родителей
			var parentId = article.parent_id;
			while (parentId) {
				var parent = _.find(this.articles, ["id", parentId]);
				path.push(parent);
				parentId = parent.parent_id;
			}
			// Инвертировать путь
			path = path.reverse();
			return path;
		},

		/**
		 * Выбрана статья из дерева
		 * @param {object} article
		 */
		onArticleSelected: function (article) {
			// Остановить поиск
			this.$refs.searchComponent.stopAsyncProcesses();
			// Записать действие пользователя в историю
			this.log();
			// Выбрать новую статью
			this.setArticleById(article ? article.id : null);
			// Очистить строку поиска
			this.clearSearch();			
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentState();
			// Прокрутить содержимое виджета наверх
			$(".widget-body").scrollTop(0);
		},

		/**
		 * Выбрана роль
		 * @param {object} role
		 */
		onRoleSelected: function (role) {
			// Остановить поиск
			this.$refs.searchComponent.stopAsyncProcesses();			
			// Записать действие пользователя в историю
			this.log();
			// Переключить роль
			this.role = role;
			// Очистить строку поиска
			this.clearSearch();
			// Очистить выбранную статью
			this.article = null;
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentState();
			// Прокрутить содержимое виджета наверх
			$(".widget-body").scrollTop(0);			
		},

		/**
		 * Пользователь кликнул на ссылку внутри статьи, которая
		 * ведет на другую статью
		 * @param {array} data массив из 2 элементов - ид роли и ид статьи,
		 * которые нужно открыть в виджете
		 */
		onHyperlinkClicked: function (data) {
			var roleId = data[0];
			var articleId = data[1];
			// Остановить поиск
			this.$refs.searchComponent.stopAsyncProcesses();			
			// Записать действие пользователя в историю
			this.log();
			// Переключить роль
			this.setRoleById(roleId);
			// Выбрать новую статью
			this.setArticleById(articleId);
			// Очистить строку поиска
			this.clearSearch();
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentState();
			// Прокрутить содержимое виджета наверх
			$(".widget-body").scrollTop(0);				
		},

		/**
		 * Обработчик события на выполнение поиска
		 * @param {object} data поисковый запрос и найденные статьи
		 */
		onSearch: function (data) {
			// Записать действие пользователя в историю
			this.log();
			// Обновить строку поиска
			this.search = data.search;
			// Обновить найденные статьи
			this.foundArticles = this.getFlatArticleListByIds(data.foundArticles);
			// Если поисковый запрос не пустой, сбросить выбранную статью
			if (this.search != "") {
				this.article = null;
			}
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentState();
			// Прокрутить содержимое виджета наверх
			$(".widget-body").scrollTop(0);			
		},

		/**
		 * Откатить последнее действие пользователя
		 */
		back: function () {
			var self = this;
			// Остановить поиск
			this.$refs.searchComponent.stopAsyncProcesses();			
			// Взять последнюю запись из истории (и удалить из истории)
			var record = this.history.pop();
			// Сохранить новое состояние компонента в сессии
			this.saveState(record);
			// Выбрать роль
			this.setRoleById(record.role);
			// Если задана строка поиска, загрузить результаты
			if (record.search != "") {
				this.setSearch(record.search, record.foundArticles);
			} else {
				// Если строка поиска не задана
				// Сбросить текущий поиск
				this.clearSearch();				
				// Выбрать статью
				this.setArticleById(record.article);
			}
			// Прокрутить содержимое виджета наверх
			$(".widget-body").scrollTop(0);			
		},

		/**
		 * Записать последнее действие пользователя в историю
		 */
		log: function () {
			this.history.push(this.getCurrentState());
		},

		/**
		 * Сохранить текущее состояние компонента в сессии
		 */
		saveCurrentState: function () {
			this.saveState(this.getCurrentState());
		},

		/**
		 * Сохранить состояние компонента в сессии
		 * @param {object} record
		 */
		saveState: function (record) {
		    axios.post("/quick-faq/log", {record: record}).then( (response) => {} );
		},

		/**
		 * Получить текущее состояние компонента (информацию, достаточную для
		 * возобновления работы компонента с того же состояния при перезагрузке
		 * страницы)
		 * @return {object}
		 */
		getCurrentState: function () {
			return {
				role: this.role.id,
				article: this.article ? this.article.id : null,
				search: this.search,
				foundArticles: this.foundArticles ? _.map(this.foundArticles, "id") : null,
			};
		},

		/**
		 * Сбросить текущий поиск
		 */
		clearSearch: function () {
			this.setSearch("", null);
		},

		/**
		 * Задать текущий поиск
		 * @param {string} search строка поиска
		 * @param {array} foundArticels массив ид найденных статей
		 */
		setSearch: function (search, foundArticles) {
			this.$refs.searchComponent.setSearch(search);
			this.search = search;
			this.foundArticles = this.getFlatArticleListByIds(foundArticles);
		},

		/**
		 * Выбрать роль по ид
		 * @param {int} roleId
		 */
		setRoleById: function (roleId) {

		},

		/**
		 * Выбрать статью по ид
		 * @param {?int} articleId ид статьи или null
		 */
		setArticleById: function (articleId) {
			this.article = articleId ? _.find(this.articles, ["id", articleId]) : null;
		},

		/**
		 * Получить список статей по их ид и обнулить parent_id для вывода 
		 * сплошным списком независимо от места в иерархии
		 * @param {array} articlesIds
		 * @return {array}
		 */
		getFlatArticleListByIds: function (articlesIds) {
			if (articlesIds === null) {
				return null;
			}
			var self = this;
  			// Найти статьи по ид
  			var articles = [];
			_.forEach(articlesIds, function (articleId) {
				var article = _.find(self.articles, ["id", articleId]);
				if (article) {
					articles.push(article);
				}
			});
  			// Обнулить у всех статей parent_id, чтобы они отображались
  			// в дереве одним списком, а не деревом
  			var clonedArticles = _.cloneDeep(articles);
	    	_.forEach(clonedArticles, function (article) {
	    		article.parent_id = null;
	    	});
	    	return clonedArticles;
		},

		/**
		 * Показать/скрыть виджет
		 */
		toggleWidget: function () {
			this.shown = !this.shown;
		},

	},

};
</script>