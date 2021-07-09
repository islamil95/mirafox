<template>
	<search v-if="loaded" suggestions-endpoint="/general-search/suggest" clear-history-endpoint="/general-search/clear-history"
		search-name="header-search"
		@search-executed="onSearchExecuted" :placeholder="t('Найти услуги')" :default-search="defaultSearch"
		:suggestions-header="t('Поиск услуг')" class="general-search">
		<!-- Кастомный крестик -->
		<template slot="clear-button">
			<div class="ico-close-12"></div>
		</template>
		<!-- Кастомная кнопка поиска (лупа) -->
		<template slot="search-button">
			<div class="icon ico-search-icon"></div>
		</template>		
	</search>
</template>

<script>

/**
 * Компонент отображает строку поиска в шапке сайта.
 */

// Поиск
Vue.component("search", require("appJs/search.vue").default);
// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Запускать поиск при изменении строки поиска. Нужно для
			// первоначального задания строки поиска по url (строка изменится,
			// но поиск не должен быть запущен)
			executeSearchOnChange: true,
			// Значение строки поиска по умолчанию (при загрузке компонента)
			defaultSearch: "",
			// Локализация компонента
			i18n: {
				en: {
					"Найти услуги": "Find services",
					"Поиск услуг": "Services",
				}
			},
			// Компонент готов к загрузке дочерних компонент
			loaded: false,
		};
	},

	props: [
		// Значение строки поиска по умолчанию (при загрузке компонента) в urlencode
		"defaultSearchEncoded",
	],

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();		
		// Если задано, раскодировать значение строки поиска по умолчанию
		if (this.defaultSearchEncoded) {
			this.defaultSearch = decodeURIComponent(this.defaultSearchEncoded);
		}
		// Компонент готов к загрузке дочерних компонент
		this.loaded = true;
	},

	methods: {

		/**
		 * Обработчик события на выполнение поиска
		 * @param {string} search строка поиска
		 */
		onSearchExecuted: function (search) {
			if (this.defaultSearch != "" && this.defaultSearch == search) {
				return;
			}
			// Сформировать url для запуска поиска
			let encodedQuery =  $.param({'query': search});
			// Редирект на сформированный url
			window.location.href = window.location.origin + "/search?" + encodedQuery + "&c=0";
		}
	},

};
</script>