<template>
	<div class="search">
		<!-- Строка поиска -->
		<input v-model="search" type="text" ref="searchInput"
			:class="{'has-text': search != ''}">
		<!-- Placeholder -->
		<div class="placeholder">
			<!-- Иконка с лупой -->
			<i class="fa fa-search" aria-hidden="true"></i>
			<!-- Текст подсказки -->
			<span>{{ t("Задайте свой вопрос") }}</span>			
		</div>
	</div>
</template>

<script>

/**
 * Компонент позволяет выполнить поиск по базе знаний. Обязательные свойства:
 *   role - роль, в рамках которой будет производиться поиск
 *   initialSearch - строка поиска по умолчанию (будет подставлена при первой 
 *     загрузке компонента)
 * Компонент генерирует события:
 *   input - выполнен поиск, в событие передается объект вида:
 *     {
 *       search: "some query",  // строка поиска
 *       foundArticles: [192, 342, 89],  // ид найденных статей
 *     }
 */

// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],
		
	data () {
		return {
			// Строка поиска
			search: "",
			// Выполнять поиск при изменении строки поиска
			// (переключить в false, если необходимо сбросить или изменить
			// поисковый запрос без выполнения поиска)
			searchOnInput: true,
			// Локализация компонента
			i18n: {
				en: {
					"Задайте свой вопрос": "What do you need help with?",
				}
			},
		};
	},

	props: [
		// Роль, в рамках которой будет производиться поиск
		"role",
		// Строка поиска по умолчанию (будет подставлена при первой загрузке компонента)
		"initialSearch",
	],

	watch: {

		// Изменилась строка поиска
		search: function (val) {
			if (!this.searchOnInput) {
				this.searchOnInput = true;
				return;
			}
			this.onInput();
		},

	},

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
		// Создать функцию для отложенного запуска поиска при вводе пользователем запроса
		var self = this;
		this.debouncedInput = _.debounce(function () {
			self.findArticles();
		}, 350);
		// Установить initialSearch
		this.setSearch(this.initialSearch);
	},

	methods: {

		/**
		 * Выполнить поиск статей в базе знаний
		 */
		findArticles: function () {
			// Если строка поиска пустая, очистить результаты поиска
			if (this.search == "") {
      			this.$emit("input", {
      				search: "",
      				foundArticles: null,
      			});
				return;
			}
			// Если в строке поиска меньше 3 символов (без учета пробелов), ничего не делать
			if (this.search.replace(" ", "").length < 3) {
				return;
			}
			// Получить результаты поиска с сервера
			this.axiosRequest = axios.CancelToken.source();
		    axios.post("/quick-faq/search", {
		    	role: this.role.id,
		    	query: this.search,
		    }, { cancelToken: this.axiosRequest.token })
	      		.then( (response) => {
			    	// Отдать родителю список найденных статей и строку поиска,
			    	// по которой они были найдены
	      			this.$emit("input", {
	      				search: this.search,
	      				foundArticles: response.data.data.articles,
	      			});
				}).catch(function (thrown) {});
		},

	    /**
	     * Обработчик события на ввод текста в строку поиска
	     */
		onInput: function () {
			this.debouncedInput();
		},

		/**
		 * Изменить строку поиска без выполнения поиска
		 */
		setSearch: function (search) {
			this.searchOnInput = false;
			this.search = search;
		},

		/**
		 * Остановить асинхронные процессы:
		 * 1) debounce ввода в строке поиска
		 * 2) загрузку результатов поиска
		 */
		stopAsyncProcesses: function () {
			this.debouncedInput.cancel();
			if (this.axiosRequest) {
				this.axiosRequest.cancel();
			}
		},

	},

};
</script>