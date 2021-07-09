<template>
	<div>
		<span v-for="page of pages">
			<span v-if="!disabled">
				<a v-if="page.type == 'page'" @click="pageSelected(page.page)">{{ page.page }}</a>
				<a v-if="page.type == 'first'" @click="pageSelected(page.page)">{{ t("Первая") }}</a>
				<a v-if="page.type == 'last'" @click="pageSelected(page.page)">{{ t("Последняя") }}</a>
				<a v-if="page.type == 'next'" @click="pageSelected(page.page)">{{ t("Следующая") }}</a>
				<a v-if="page.type == 'previous'" @click="pageSelected(page.page)">{{ t("Предыдущая") }}</a>
			</span>
			<span v-else>
				<span v-if="page.type == 'page'">{{ page.page }}</span>
				<span v-if="page.type == 'first'">{{ t("Первая") }}</span>
				<span v-if="page.type == 'last'">{{ t("Последняя") }}</span>
				<span v-if="page.type == 'next'">{{ t("Следующая") }}</span>
				<span v-if="page.type == 'previous'">{{ t("Предыдущая") }}</span>
			</span>
			<span v-if="page.type == 'current'">{{ page.page }}</span>
			<span v-if="page.type == 'more'">...&nbsp</span>
		</span>
	</div>
</template>

<script>

/**
 * Компонент выводит пагинатор на основании переданного объекта pages. При клике на
 * странице компонент генерирует событие pageSelected с номером выбранной странице.
 * Свойство pages содержит массив страниц, где каждая страница описана в формате:
 *   {
 *     type = (page|current|more|first|last|next|previous),
 *     page = <номер страницы>
 *   }
 * Опционально можно передать свойство disbaled, которое сделает все сссылки на
 * страницы неактивными.
 */

// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Локализация компонента
			i18n: {
				en: {
					"Первая": "First",
					"Последняя": "Last",
					"Следующая": "Next",
					"Предыдущая": "Previous",
				}
			},			
		};
	},

	props: {

		// Описание страниц для пагинатора
		pages: {
			type: Array,
			default: function () {
				return [];
			},
		},

		// Компонент неактивен
		disabled: {
			type: Boolean,
			default: false,
		}

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
		 * Выбрана страница
		 * @param  {int} page
		 */
		pageSelected: function (page) {
			this.$emit("pageSelected", page);
		},

	},

};
</script>