<template>
	<div class="breadcrumbs">
		<!-- Кнопка "Домой" -->
		<span @click="followBreadcrumb(null)"><!--
		 --><slot name="home"><!--
		 --><i class="fa fa-home home-button" aria-hidden="true"></i><!--
		 --></slot><!--
	 --></span><!--
	 --><!-- Хлебные крошки --><!--
	 --><span v-for="(breadcrumb, index) in breadcrumbs" class="breadcrumb">
			<!-- Разделитель для хлебных крошек -->
			<!-- Активная (кликабельная) ссылка -->
			<div class="delimiter" :class="{'first': index == 0}"></div><span v-if="isBreadcrumbActive(index)" class="active"
				@click.prevent="followBreadcrumb(breadcrumb)">{{ breadcrumb[breadcrumbLabel] }}</span>
			<!-- Неактивная ссылка -->
			<span v-else class="inactive">{{ breadcrumb[breadcrumbLabel] }}</span>
		</span>
	</div>
</template>

<script>

/**
 * Компонент выводит хлебные крошки для навигации.
 * Обязательные свойства:
 *   breadcrumbs - хлебные крошки (массив объектов)
 * Опционально можно задать:
 *   breadcrumb-label - имя свойства объекта из breadcrumbs, содержащее метку для вывода в хлебных крошках
 * Компонент генерирует события:
 *   input - пользователь перешел по хлебной крошке, в событие передается
 *     соответствующий объект из breadcrumbs или null, если пользователь кликнул по кнопке "Домой"
 * Слоты:
 *   home - кнопка "Домой"
 */

export default {
	data () {
		return {

		};
	},

	props: {

		// Хлебные крошки (массив объектов)
		breadcrumbs: {
			type: Array,
			default: function () {
				return [];
			},
		},

		// Имя свойства объекта из breadcrumbs, содержащее
		// метку для вывода в хлебных крошках
		breadcrumbLabel: {
			type: String,
			default: "label",
		}

	},

	methods: {

		/**
		 * Перейти по хлебной крошке
		 * @param {object} article
		 */
		followBreadcrumb: function (breadcrumb) {
			this.$emit("input", breadcrumb);
		},

		/**
		 * Метод проверят, должна ли быть активна (кликабельна) хлебная
		 * крошка с индексом index
		 * @param {int} index
		 * @return {boolean}
		 */
		isBreadcrumbActive: function (index) {
			var isLastBreadcrumb = index == this.breadcrumbs.length - 1;
			return !isLastBreadcrumb;
		},

	},

};
</script>