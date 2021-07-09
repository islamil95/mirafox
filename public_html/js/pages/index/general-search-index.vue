<template>
	<search suggestions-endpoint="/general-search/suggest" clear-history-endpoint="/general-search/clear-history"
		@search-executed="onSearchExecuted" :placeholder="t('Какая услуга вас интересует?')"
		:suggestions-header="t('Поиск услуг')" class="general-search-index d-none d-md-block">
		<!-- Кастомный крестик (без крестика) -->
		<template slot="clear-button">
		</template>
		<!-- Кастомная кнопка поиска (стандартная зеленая кнопка вместо лупы) -->
		<template slot="search-button">
			<button class="button button-success">{{ t("Найти услуги") }}</button>
		</template>
	</search>
</template>

<script>

/**
 * Компонент отображает строку поиска на главной странице сайта.
 */

// Поиск
Vue.component("search", require("appJs/search.vue").default);
// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Локализация компонента
			i18n: {
				en: {
					"Найти услуги": "Search",
					"Какая услуга вас интересует?": "What service are you looking for?",
					"Поиск услуг": "Services",
				}
			},
		};
	},

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
	},

	methods: {

		/**
		 * Обработчик события на выполнение поиска
		 * @param {string} search строка поиска
		 */
		onSearchExecuted: function (search) {
			// Добавление открытия окна регистрации для незареганых
			let additionalArgs = '';
			let userId = USER_ID || 0;
			userId = parseInt(userId);
			if (userId < 1) {
				Cookies.set('registerPopupForce', '1', { expires: 2, path: '/', SameSite:'Lax' });
			}
			// Сформировать url для запуска поиска
			let encodedQuery =  $.param({'query': search});
			var url = window.location.origin + "/search?" + encodedQuery + "&c=0";
			// Редирект на сформированный url
			window.location.href = url;
		},

	},

};
</script>