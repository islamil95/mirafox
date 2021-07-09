<template>
	<div v-if="loaded" class="kb" :class="componentClass">
		<!-- Стили для иконок статей -->
		<div class="d-none" v-html="articlesIconsCss"></div>
		<!-- Шапка страницы, если выбрана роль -->
		<div class="bg-light py-5" v-if="role">
			<div class="container-fluid kb-header">
				<!-- Заголовок -->
				<div class="row">
					<div class="col-12 text-center">
						<span class="title">{{ componentTitle }}</span>
					</div>
				</div>
				<!-- Переключатель ролей -->
				<div class="row mt-4 mt-md-5" v-if="showRoleSelect">
					<div class="col-12 col-md-4 mb-2 mb-md-0 font-weight-bold">
						{{ roleSelectLabel }}:
					</div>
					<!-- Переключатель ролей -->					
					<div class="col-12 col-md-8">
						<kb-role-select :roles="roleOptions" :role="role" @input="onRoleSelected">
						</kb-role-select>
					</div>
				</div>
				<!-- Поиск -->
				<div class="row mt-4">
					<div class="col-12">
						<search suggestions-endpoint="/kb/suggest" :suggestions-data="{role: role.id}"
							clear-history-endpoint="/kb/clear-history"
							@search-clear="onSearch"
							@search-executed="onSearch" :placeholder="t('Найдите ответ на ваш вопрос')"
							:suggestions-header="t('Поиск статей')" ref="search" :class="searchClass">
							<div slot="search-button">
								<i class="fa fa-search fa-lg d-none d-md-inline-block"></i>
							</div>
						</search>
					</div>
				</div>
				<!-- Кнопка вызова переключателя ролей -->
				<div class="row mt-3" v-show="showRoleSelectButton">
					<div class="col-12 text-center">
						{{ roleSelectLabel }}:
						<!-- Выбранная в данный момент роль -->
						<span class="font-weight-bold">{{ role.localized_name }}.</span> 
						<!-- Кнопка - разворачивает компонент для выбора ролей -->
						<span class="role-select-button" @click="expandRoleSelect">{{ t("Сменить роль") }}</span>
					</div>
				</div>			
			</div>
		</div>

		<!-- Шапка страницы, если роль не выбрана -->
		<div class="bg-light py-5 text-center px-2" v-if="role == null">
			<!-- Заголовок -->
			<div class="title mb-3">{{ componentTitle }}</div>
			<!-- Подзаголовок -->
			<div class="subtitle" v-if="isFaq">
				{{ t("В этом разделе вы найдете ответы на самые популярные вопросы пользователей") }}
			</div>
		</div>		
		<div class="bg-white text-center py-5 px-2" v-if="role == null">
			<span class="role-select-title">{{ roleSelectLabel }}:</span>
			<!-- Переключатель ролей -->
			<div class="role-select-wrapper text-left mx-auto mt-5">
				<kb-role-select :roles="roleOptions" :role="initialRole" 
					@input="onInitialRoleSelected">
				</kb-role-select>
			</div>
			<!-- Ошибка, если роль не выбрана -->
			<div class="invalid-feedback py-3">
				{{ initialRoleError ? initialRoleError : '&nbsp;' }}
			</div>
			<div>
				<!-- Кнопка Далее (применяет выбранную роль) -->
				<button class="button button-success" @click="next">
					<span class="px-5">
						{{ t("Далее") }}
					</span>
				</button>
			</div>
		</div>

		<!-- Хлебные крошки -->		
		<div class="bg-white py-2 mb-4 border-bottom container-fluid" v-if="showBreadcrumbs && role">
			<div class="container-fluid breadcrumbs-wrapper">			
				<div class="row">
					<div class="col-12">			
						<breadcrumbs :breadcrumbs="path" @input="onArticleSelected"
							:breadcrumb-label="'question'">
							<span slot="home" class="home-button">{{ breadcrumbsHomeTitle }}</span>
						</breadcrumbs>
					</div>
				</div>
			</div>
		</div>

		<!-- Результаты поиска -->		
		<div class="bg-white pb-5" v-show="showSearchResults" v-if="role">
			<div class="container-fluid kb-search-results">
				<!-- Результаты поиска -->
				<div class="row">
					<div class="col-12">
						<kb-search-results :role="role" :search="search" :section="section"
							v-model="foundArticles" @articleSelected="onArticleSelected">
						</kb-search-results>
					</div>
				</div>
				<!-- Статьи базы знаний (если ничего не найдено) -->
				<div class="row" v-if="foundArticles.length == 0">
					<div class="col-12">
						<!-- Заголовок для корневых статей, если ни одна не выбрана -->
						<div class="root-articles-title" :class="treeViewClass(true)">{{ treeViewTitle }}</div>
					</div>
				</div>
				<div class="row" v-show="foundArticles.length == 0">
					<div class="col-12">
						<!-- Дерево статей FAQ -->
						<kb-tree-view v-if="isFaq"
							:value="null" ref="kbTreeView"
							:tree="filteredArticles" @input="onArticleSelected"
							:customSort="customArticlesSort" :role="role"
							:disableInputEventOnParentChangeValue="true">
						</kb-tree-view>
						<!-- Дерево статей Обращения в ТП и Арбитраж -->
						<div class="container-fluid" v-else>
							<support-tree-view :tree="filteredArticles"
								:parent-id="null"
								:customSort="customArticlesSort"
								@input="onArticleSelected">
							</support-tree-view>
						</div>
					</div>
				</div>				
			</div>
		</div>

		<!-- Статьи базы знаний -->
		<div class="bg-white pb-5" v-show="showTree" v-if="role">
			<div class="container-fluid">
				<div class="row" v-if="article == null || !isFaq">
					<div class="col-12">
						<!-- Заголовок -->
						<div class="root-articles-title" :class="treeViewClass(false)">{{ treeViewTitle }}</div>					
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<!-- Дерево статей FAQ -->
						<kb-tree-view v-if="isFaq"
							:value="article"
							:tree="filteredArticles" @input="onArticleSelected"
							:customSort="customArticlesSort" :role="role"
							ref="KbTreeView"
							:disableInputEventOnParentChangeValue="true">
						</kb-tree-view>
						<!-- Дерево статей Обращения в ТП и Арбитраж -->
						<div class="container-fluid" v-else>
							<!-- Навигация на уровень выше -->
							<div @click="goLevelUp" class="back-button mx-auto mb-3"
								v-if="article">
								< {{ t("Назад") }}
							</div>
							<!-- Дерево статей -->
							<support-tree-view :tree="filteredArticles"
								:parent-id="article ? article.id : null"
								:customSort="customArticlesSort"
								@input="onArticleSelected"
								ref="SupportTreeView">
							</support-tree-view>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Выбранная статья -->		
		<div class="bg-white container-fluid" v-if="showArticle && role" 
			:class="articleClass(article)">
			<!-- Выбранная статья - FAQ -->
			<div class="container-fluid article" :id="'article_' + article.id" v-if="isFaq">
				<div class="row">
					<div class="col-12">
						<!-- Вопрос -->
						<div class="question">
							{{ article.question }}
						</div>
						<!-- Ответ -->
						<div class="answer fr-view mt-4" 
							v-html="article.answer_formatted">
						</div>						
						<!-- Лайки -->
						<div class="text-center">
							<span class="like-text mr-2">{{ t("Была ли статья полезна для вас") }}?</span>
							<!-- Плюс -->
							<div class="like-button" @click="like(true)" :class="{'liked': liked}">
								<i class="fa fa-thumbs-up"></i>
							</div>
							<!-- Минус -->
							<div class="like-button" @click="like(false)" :class="{'liked': liked}">
								<i class="fa fa-thumbs-down"></i>
							</div>
						</div>
						<!-- Ваш голос учтен -->
						<div class="text-center mt-2" v-if="liked">
							<span v-if="positive">+</span><span v-else>-</span>1 {{ t("Ваш голос учтен") }}!
						</div>
					</div>
				</div>
			</div>
			<!-- Выбранная статья - Обращения в ТП и Арбитраж -->
			<div class="article-wrapper mx-auto" :id="'article_' + article.id" v-else>
				<!-- Заголовок -->
				<div class="article-title text-center mb-4" v-if="!article.show_support_form">
					{{ articleTitle }}
				</div>
				<!-- Навигация на уровень выше -->
				<div @click="goLevelUp" class="back-button mb-3">
					< {{ t("Назад") }}
				</div>
				<!-- Статья -->
				<div class="article" v-if="!article.show_support_form">
					<!-- Иконка и вопрос -->
					<div class="question d-flex align-items-center justify-content-between mb-3">
						<div>{{ article.question }}</div>
						<div class="attention">
							<i class="ico-info"></i>
						</div>	
					</div>
					<!-- Ответ -->
					<div class="answer fr-view" v-html="getArticleDescription(article, false)">
					</div>
				</div>
			</div>
		</div>

		<!-- Форма обращения в ТП -->
		<div class="support-form mx-auto bg-white pb-4" v-if="contactFormShown">
			<contact-form :is-arbitrage="isArbitrage" :support="support" :article="article" :role="role"
				:track-type="trackType" :order-id="orderId">
			</contact-form>
		</div>

		<!-- Кнопка связи с ТП -->		
		<div class="bg-light py-4" v-if="(isFaq && showArticle && role) || (!isFaq && showArticle && role && !contactFormShown)">
			<div class="container-fluid support">
				<div class="row">
					<div class="col-12">
						<!-- FAQ -->
						<div class="support-wrapper" v-if="isFaq">
							<div class="title mb-2">
								{{ t("Не нашли ответ на свой вопрос") }}?
							</div>
							<div class="description">
								<span class="link" @click="askQuestion">{{ t("Заполните форму") }}</span>. 
								<span>{{ t("Мы ответим вам настолько быстро, насколько это возможно") }}.</span>
							</div>
							<div class="support-icon d-none d-lg-block">
							</div>
							<div class="support-arrow-wrapper" @click="askQuestion">
								<div class="support-arrow">
								</div>
							</div>
						</div>
						<!-- Обращения в ТП и Арбитраж -->
						<div class="row support-wrapper mx-auto" v-else>
							<div class="col-12 col-md-6 text-center text-md-left font-weight-bold" 
								v-if="isArbitrage && article && article.arbitrage_allowed || isSupport">
								<!-- Заголовок Обращения в ТП -->
								<span v-if="isSupport">
									{{ t("Если подсказки недостаточно") }},<br>{{ t("обратитесь в службу поддержки") }}
								</span>
								<!-- Заголовок Арбитраж -->
								<span v-if="isArbitrage">
									{{ t("Если вы хотите открыть спор") }}<br>{{ t("по заказу, обратитесь в Арбираж") }}
								</span>
							</div>
							<div class="col-12 col-md-6 text-center text-md-right mt-3 mt-md-0"
								v-if="isArbitrage && article && article.arbitrage_allowed || isSupport">
								<!-- Кнопка Обращения в ТП -->
								<button class="button button-gray-success w-100" @click="showContactForm" v-if="isSupport">
									{{ t("Задайте вопрос") }}
								</button>
								<!-- Кнопка Арбитраж -->
								<button class="button button-gray-danger w-100" @click="showContactForm" v-if="isArbitrage">
									{{ t("Арбитраж") }}
								</button>
							</div>
							<!-- Арбитраж запрещен -->
							<div class="col-12 text-center font-weight-bold" 
								v-if="isArbitrage && article && !article.arbitrage_allowed">
								{{ t("Возможности написать обращение в арбитраж нет") }}
							</div>							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

/**
 * Компонент отображает страницу базы знаний для пользователя. Страница включает в себя переключатель ролей,
 * строку поиска и результаты поиска, дерево статей базы знаний и кнопку связи с ТП. Обязательные свойства:
 *   role-options-json - доступные для выбора роли в JSON
 *   articles-json - статьи в JSON
 *   guest-json - пользователь - гость (не авторизован)
 *   section - секция базы знаний
 *     faq - FAQ
 *     support - Обращения в ТП
 *     arbitrage - Арбитраж
 *   Также можно задать несколько секций через разделитель _ (нижнее подчеркивание)
 * Опционально можно передать:
 *   role-json - выбранная роль в JSON
 *   article-json - статья, на которой должно раскрыться дерево в JSON
 *   liked-json - пользователь лайкнул статью до перезагрузки страницы
 *   positive-json - отзыв пользователя (позитивный или негативный) до перезагрузки страницы
 *   support-json - пользователь техподдержки в JSON. Если компонент используется в режиме
 *     Обращений в ТП, данное свойство является обязательным
 *   track-type - тип трека для арбитража
 *   order-id - ид заказа для арбитража
 */

// Общий функционал компонента для пользователя и админа
import KBMixin from "./kb-mixin";
// Учет показов статьи в обращениях в ТП
import ArticleHitMixin from "./support/article-hit-mixin";
// Отформатированное описание статьи в зависимости от текущего раздела
import kbArticleDescriptionMixin from "moduleJs/kb/kb-article-description-mixin";
// Переключатель ролей
Vue.component("kb-role-select", require("moduleJs/kb/kb-role-select.vue").default);
// Поиск
Vue.component("search", require("appJs/search.vue").default);
// Результаты поиска
Vue.component("kb-search-results", require("moduleJs/kb/kb-search-results.vue").default);
// Статьи базы знаний - FAQ
Vue.component("kb-tree-view", require("moduleJs/kb/kb-tree-view.vue").default);
// Статьи базы знаний - Обращения в ТП
Vue.component("support-tree-view", require("moduleJs/kb/support/support-tree-view.vue").default);
// Хлебные крошки
Vue.component("breadcrumbs", require("appJs/breadcrumbs.vue").default);
// Форма связи
Vue.component("contact-form", require("moduleJs/kb/contact-form.vue").default);

export default {
	mixins: [KBMixin, ArticleHitMixin, kbArticleDescriptionMixin],

	data () {
		return {
			// Строка поиска
			search: "",
			// Найденные через поиск статьи
			foundArticles: [],
			// Путь до выбранной статьи (массив статей)
			path: [],	
			// Переключатель ролей развернут
			roleSelectExpanded: false,
			// Первая роль, которую выбирает пользователь
			// (если при открытии страницы ни одна роль еще не выбрана)
			initialRole: null,
			// Если пользователь не выбрал роль и нажал Далее,
			// будет отображаться ошибка
			initialRoleError: null,
			// Пользователь лайкнул статью
			liked: false,
			// Отзыв пользователя (позитивный или негативный)
			positive: false,
			// Последнее действие из истории пользователя, на которое
			// откатился пользователь
			lastHistoryRecord: null,
			// Показывать форму связи
			contactFormShown: false,			
		};
	},

	computed: {

	    /**
	     * Показывать дерево статей
	     * @return {boolean}
	     */
	    showTree: function () {
			// Если строка поиска не задана и
			// если статья не выбрана или выбрана статья, 
			// у которой есть дочерние в текущей роли
			if (this.search == "" && 
				(this.article === null || 
				this.article !== null && this.article.has_childs.indexOf(this.role.id) >= 0)) {
				return true;
			}
			return false;
	    },

	    /**
	     * Показывать результаты поиска
	     * @return {boolean}
	     */
	    showSearchResults: function () {
	    	return this.search != "";
	    },

	    /**
	     * Показывать выбранную статью
	     * @return {boolean}
	     */
	    showArticle: function () {
	    	// Если выбрана статья, у которой нет дочерних в текущей роли
	    	return this.article && this.article.has_childs.indexOf(this.role.id) == -1 &&
				// Если не задана строка поиска
	    		this.search == "";
	    },

		/**
		 * Показывать хлебные крошки
		 * @return {boolean}
		 */
		showBreadcrumbs: function () {
			// Если выбрана хотя бы одна статья или выполнен поиск
			return this.article != null || this.search != "";
		},

		/**
		 * Показывать переключатель ролей
		 * @return {boolean}
		 */
		showRoleSelect: function () {
			// Если пользователь сам развернул переключатель ролей
			return (this.roleSelectExpanded ||
				// Или не выбрана ни одна статья и при этом не задана строка поиска
				(this.article == null && this.search == "")) &&
				// Показывать переключатель только для секции FAQ
				this.isFaq;
		},

		/**
		 * Показывать кнопку вызова переключателя ролей
		 * @return {boolean}
		 */
		showRoleSelectButton: function () {
			return !this.showRoleSelect &&
				// Показывать переключатель только для секции FAQ
				this.isFaq;			
		},

		/**
		 * Класс для строки поиска
		 * (большое или маленькое поле)
		 * @return {object}
		 */
		searchClass: function () {
			return {
				// Сделать строку поиска больше, если показан
				// переключатель ролей
				"big": this.showRoleSelect,
			};
		},

		/**
		 * Заголовок для переключателя ролей
		 * @return {string}
		 */
		roleSelectLabel: function () {
			return this.isFaq ? this.t("Вы ищите ответ как") : this.t("Вы обращаетесь как");
		},

		/**
		 * Заголовок компонента (первый и самый крупный на странице)
		 * @return {string}
		 */
		componentTitle: function () {
			return this.isArbitrage ? this.t("Отправка заказа в Арбитраж") : this.t("Как мы можем вам помочь?");
		},

		/**
		 * Класс для всего компонента
		 * @return {object}
		 */
		componentClass: function () {
			// Для Арбитражей используются все стили как для Обращений в ТП + кастомизация
			return {
				"faq": this.isFaq,
				"support": this.isSupport || this.isArbitrage,
				"arbitrage": this.isArbitrage,
			};
		},

		/**
		 * Заголовок для дерева статей
		 * @return {string}
		 */
		treeViewTitle: function () {
			return this.isFaq ? this.t("Ответы на частые вопросы") + " " + this.role.localized_name_for_title : 
				this.t("Уточните тему для обращения");
		},

		/**
		 * Заголовок кнопки Домой в хлебных крошках
		 * @return {string}
		 */
		breadcrumbsHomeTitle: function () {
			return this.isArbitrage ? this.t("Обращение в Арбитраж") : this.t("Помощь");
		},

		/**
		 * Заголовок над выбранной статьей (если статья конечная в дереве)
		 * @return {string}
		 */
		articleTitle: function () {
			return this.isArbitrage ? this.t("Правила Kwork для данной ситуации") : this.t("Подсказка по вашему вопросу");
		},

	},

	watch: {

		// Выбрана статья из дерева
		article: function () {
			// Пересчитать путь до статьи
			this.path = this.getPath(this.article);
			// Записать в историю показ статьи пользователю
			if ((this.isFaq || this.isSupport) && !this.guest) {
				this.logHit();
			}
			// Если отображается раздел Обращения в ТП и выбрана статья
			if (this.isSupport) {
				// Если у статьи установлен флаг show_support_form, сразу показать форму отрпавки сообщения
				if (this.article && this.role && 
					this.article.has_childs.indexOf(this.role.id) == -1 &&
					this.article.role_ids.indexOf(this.role.id) >= 0 &&
					this.article.show_support_form) {
					this.showContactForm();
				}
			}
		},
	
	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Сформировать хлебные крошки
		this.breadcrumbs = this.getPath();
		// Сбросить лайки пользователя
		if (this.likedInitial) {
			this.likedInitial = false;
			this.positive = this.positiveInitial;
			this.liked = true;
		} else {
			this.liked = false;
		}
		// Сохранить начальное состояние компонента в истории
		this.saveCurrentState(true);
		// Подписаться на событие нажатия на кнопки Назад и Вперед в браузере
		var self = this;
		window.onpopstate = function(event) {
			self.popState(event.state);
		};		
	},

	methods: {

		/**
		 * Получить путь до статьи
		 * @param  {object} article
		 * @return {array} все родители статьи вплоть до корня
		 */
		getPath: function (article) {
			// Если выполнен поиск
			if (this.search != "") {
				return [{
					question: this.t("Поиск по вопросу"),
				}];
			}
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
			// Выбрать новую статью
			this.setArticleById(article ? article.id : null);
			// Очистить строку поиска
			this.clearSearch();
			// Сбросить лайки пользователя
			this.liked = false;		
			// Если выбрана статья, свернуть переключатель ролей
			if (this.article) {
				this.roleSelectExpanded = false;
			}
			// Скрыть форму связи
			this.contactFormShown = false;
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentArticleState();				
		},

		/**
		 * Выбрана роль
		 * @param {object} role
		 */
		onRoleSelected: function (role) {
			// Переключить роль
			this.role = role;
			// Очистить строку поиска
			this.clearSearch();
			// Очистить выбранную статью
			this.article = null;
			// Сбросить лайки пользователя
			this.liked = false;
			// Свернуть переключатель ролей
			this.roleSelectExpanded = false;
			// Скрыть форму связи
			this.contactFormShown = false;
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentRoleState();	
		},

		/**
		 * Выбрана первая роль (на экране, где еще ни одна роль не выбрана,
		 * в случае с неавторизованным пользователем)
		 * @param {object} role
		 */
		onInitialRoleSelected: function (role) {
			// Переключить роль
			this.initialRole = role;
		},

		/**
		 * Обработчик события на выполнение поиска
		 * @param {string} search поисковый запрос
		 */
		onSearch: function (search) {
			// Обновить строку поиска
			this.search = search;
			// Пересчитать путь до статьи
			this.path = this.getPath(this.article);			
			// Сбросить лайки пользователя
			this.liked = false;
			// Свернуть переключатель ролей
			this.roleSelectExpanded = false;	
			// Скрыть форму связи
			this.contactFormShown = false;	
			// Сбросить выбранную статью в дереве статей, которое отображается,
			// если не найдено ни одной статьи в поиске
			if (this.$refs.kbTreeView) {
				this.$refs.kbTreeView.selectValue(null);
			}
			if (this.$refs.SupportTreeView) {
				this.$refs.SupportTreeView.selectValue(null);
			}			
			// Сохранить текущее состояние компонента в сессии
			this.saveCurrentSearchState();
		},

		/**
		 * Сохранить текущее состояние компонента в истории при изменении роли
		 */
		saveCurrentRoleState: function () {
			var record = this.lastHistoryRecord;
			if (record) {
				// Если изменилась роль
				var roleId = this.role ? this.role.id : null;
				if (roleId != record.role) {
					this.saveCurrentState();
				}
			} else {
				this.saveCurrentState();
			}
		},

		/**
		 * Сохранить текущее состояние компонента в истории при изменении статьи
		 */
		saveCurrentArticleState: function () {
			var record = this.lastHistoryRecord;
			if (record) {			
				// Если изменилась статья
				var articleId = this.article ? this.article.id : null;
				if (articleId != record.article) {
					this.saveCurrentState();
				}
			} else {
				this.saveCurrentState();
			}			
		},

		/**
		 * Сохранить текущее состояние компонента в истории при выполнении поиска
		 */
		saveCurrentSearchState: function () {
			var record = this.lastHistoryRecord;
			if (record) {			
				// Если изменилась строка поиска
				if (this.search != record.search) {
					this.saveCurrentState();
				}
			} else {
				this.saveCurrentState();
			}			
		},

		/**
		 * Сохранить текущее состояние компонента в истории
		 * @param {boolean} replace - True - заменить текущую запись,
		 * False - создать новую
		 */
		saveCurrentState: function (replace = false) {
			this.saveState(this.getCurrentState(), replace);
		},

		/**
		 * Сохранить состояние компонента в истории
		 * @param {object} record
		 * @param {boolean} replace - True - заменить текущую запись,
		 * False - создать новую
		 */
		saveState: function (record, replace = false) {
			var url = this.isFaq ? "faq2" : (this.isSupport ? "support2" : "arbitrage");
			if (record.role && record.article) {
				url += "?role=" + record.role + "&article=" + record.article;
			}
			if (replace) {
				window.history.replaceState(record, "", url);
			} else {
				window.history.pushState(record, "", url);
			}
		},

		/**
		 * Получить текущее состояние компонента (информацию, достаточную для
		 * возобновления работы компонента с того же состояния при перезагрузке
		 * страницы)
		 * @return {object}
		 */
		getCurrentState: function () {
			return {
				role: this.role ? this.role.id : null,
				article: this.article ? this.article.id : null,
				search: this.search,
			};
		},

		/**
		 * Пользователь нажал кнопку Назад или Вперед в браузере - 
		 * восстановить предыдущее состояние компонента из истории
		 * @param {object} state
		 */
		popState: function (record) {
			this.lastHistoryRecord = record;
			// Если задана строка поиска
			if (record.search != "") {
				// Восстановить поиск
				this.$refs.search.setSearch(record.search);
				this.onSearch(record.search);
			} else {
				// Если строка поиска не задана
				// Восстановить роль и статью
				var role = _.find(this.roleOptions, ["id", record.role]);
				var article = _.find(this.articles, ["id", record.article]);
				this.onRoleSelected(role);
				this.onArticleSelected(article);				
			}
		},

		/**
		 * Сбросить текущий поиск
		 */
		clearSearch: function () {
			this.setSearch("");
		},

		/**
		 * Задать текущий поиск
		 * @param {string} search строка поиска
		 */
		setSearch: function (search) {
			this.$refs.search.setSearch(search);
			this.search = search;
		},

		/**
		 * Выбрать роль по ид
		 * @param {int} roleId
		 */
		setRoleById: function (roleId) {
			this.role = roleId ? _.find(this.roleOptions, ["id", roleId]) : null;
		},

		/**
		 * Выбрать статью по ид
		 * @param {?int} articleId ид статьи или null
		 */
		setArticleById: function (articleId) {
			this.article = articleId ? _.find(this.articles, ["id", articleId]) : null;
		},

	    /**
	     * Задать вопрос ТП
	     */
	    askQuestion: function () {
			var url = this.guest ? "/contact" : "/conversations/Support";
	    	var url = window.location.origin + url;
	    	window.location.href = url;
	    	/* TODO-5265: открыть новую форму обращения в ТП, как будет готова
	    	var url = window.location.origin + "/support2";
		    url = url + "?role=" + this.role.id;	    	
	    	if (this.showTree || this.showArticle) {
		    	url = this.article ? url + "&article=" + this.article.id : url;
	    	}
	    	window.location.href = url;
	    	*/
	    },

	    /**
	     * Показать переключатель ролей
	     */
	    expandRoleSelect: function () {
	    	this.roleSelectExpanded = true;
	    },

	    /**
	     * Пользователь нажал на кнопку Далее
	     * при выборе роли (пока ни одна роль еще не выбрана)
	     * @param {object} role
	     */
	    next: function () {
	    	// Если роль выбрана
	    	if (this.initialRole) {
	    		this.role = this.initialRole;
	    		this.initialRoleError = null;
	    		this.initialRole = null;
	    		this.saveCurrentRoleState();
	    	} else {
		    	// Если роль не выбрана
		    	// Показать ошибку
		    	this.initialRoleError = this.t("Выберите, пожалуйста, роль");
	    	}
	    },

		/**
		 * Пользователь лайкнул статью
		 * @param {boolean} positive true - лайк, false - дислайк
		 */
		like: function (positive) {
			if (this.liked) {
				return;
			}
			// Если пользователь авторизован
			if (!this.guest) {
				this.likeSend(positive);
			} else {
				// Если пользователь не авторизован
				// Показть диалог авторизации
				show_login(true, positive ? this.likeSendPositive : this.likeSendNegative);
			}
		},

		/**
		 * Отправить на сервер лайк
		 */
		likeSendPositive: function () {
			this.likeSend(true);
		},

		/**
		 * Отправить на сервер дислайк
		 */
		likeSendNegative: function () {
			this.likeSend(false);
		},

		/**
		 * Отправить на сервер лайк/дислайк
		 * @param {boolean} positive true - лайк, false - дислайк
		 */
		likeSend: function (positive) {
  			// Отметить, что пользователь лайкнул статью
  			this.liked = true;
  			this.positive = positive;		
  			// Отправить запрос на сервер
		    axios.post("/kb/like", {
		    	like: positive,
		    	article: this.article.id,
		    })
	      		.then( (response) => {	      			
	      			// Если пользователь не авторизован, перезагрузить страницу
	      			if (this.guest) {
						var url = window.location.origin + "/faq2";
					    url = url + "?role=" + this.role.id + "&article=" + this.article.id + 
					    	"&liked=1&positive=" + (this.positive ? "1" : "0");
						window.location.href = url;
					}
				});			
		},

		/**
		 * Перейти в дереве статей на уровень выше
		 */
		goLevelUp: function () {
			var parentArticle = null;
			if (this.article && this.article.parent_id) {
				parentArticle = _.find(this.articles, ["id", this.article.parent_id]);
			}
			this.onArticleSelected(parentArticle);
		},

		/**
		 * Показать форму связи
		 */
		showContactForm: function () {
			this.contactFormShown = true;
		},

		/**
		 * Класс для заголовка дерева статей
		 * @param {boolean} search
		 * @return {string}
		 */
		treeViewClass: function (search) {
			if (search) {
				return this.isFaq ? "my-5" : "mt-5 mb-4";
			} else {
				var supportAndArbitrageClass = this.article == null ? "mt-5 mb-4" : "mb-4";
				return this.isFaq ? "my-5" : supportAndArbitrageClass;
			}
		},

		/**
		 * Класс блока с выбранной статьей (если статья конечная в дереве)
		 * @return {string}
		 */
		articleClass: function (article) {
			if (this.isFaq) {
				return "pb-4";
			} else if (!article.show_support_form) {
				return "pb-5";	
			} else {
				return "";
			}
		},

	},

};
</script>