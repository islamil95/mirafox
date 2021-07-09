<template>
	<span v-show="options.length > 0">
		<radio-button-group :options="options" v-model="element" 
			:option-label="elementLabel" v-if="radioMode && !element" :class="radioButtonGroupClass">
		</radio-button-group>
		<div :class="vSelectClass" :style="vSelectStyle" v-if="!radioMode || element">
			<v-select v-model="element" :options="options" :element-label="elementLabel" 
				:element-key="elementKey" nullable>
			</v-select>
		</div>
		<!-- Перенос строки, если в строке уже columns компонент для выбора -->
		<br v-if="columnsComputed > 1 && level % columnsComputed == 0">
		<tree-select
			v-if="element && hasChilds" 
			:value="value"
			:parent-id="element[elementKey]" 
			:tree="tree"
			:element-label="elementLabel" 
			:element-key="elementKey"
			:element-parent-key="elementParentKey"
			:customSort="customSort"
			:level="level + 1"
			:columns="columns"
			:elementWidth="elementWidth"
			:radioMode="radioMode"
			@change="childElementChanged">
		</tree-select>
	</span>
</template>

<script>

/**
 * Компонент позволяет выбрать элемент из дерева с бесконечной вложенностью. Компонент 
 * поддерживает директиву v-model - выбранный элемент (в самом низу иерархии). Обязательные свойства:
 *   tree - дерево. Каждый элемент дерева должен иметь свойства id, parent_id и name (названия можно переопределить, 
 *     см. ниже)
  * Опционально можно задать:
 *   element-label - название свойства, содержащее метку элемента
 *   element-key - название свойства, содержащее уникальный инентификатор элемента
 *   element-parent-key - название свойства, содержащее ид родительского элемента
 *   customSort - метод для сортировки элементов для выбора (по умолчанию элементы не сортируются)
 *   columns - кол-во колонок, на которые нужно разбить компонент
 *   elementWidth - максимальная ширина поля для выбора (если колонок > 1)
 *   radioMode - режим выбора через radio-buttons. Если установлен (true) пустой v-select (в котором еще не 
 *     выбран элемент) отображается в виде группы radio-button, а после выбора элемента сворачивается в v-select. 
 *     По умолчанию режим отключен (false)
 */

// Алгоритмы работы с деревом
import treeMixin from "appJs/tree-mixin";
// Custom select
Vue.component("v-select", require("appJs/v-select.vue").default);
// RadioButton group
Vue.component("radio-button-group", require("appJs/radio-button-group.vue").default);

export default {
	mixins: [treeMixin],

	data () {
		return {
			// Ширина окна
			windowWidth: window.innerWidth,
		};
	},

	props: {

		// Кол-во колонок, на которые нужно разбить компонент
		// (если > 1, то необходимо обязательно указать свойство element-width)
		columns: {
			type: Number,
			default: 1,
		},		

		// Максимальная ширина поля для выбора
		// (имеет смысл, только если свойство columns > 1)
		elementWidth: {
			type: Number,
			default: 220,
		},

		// Режим выбора через radio-buttons. Если установлен (true) пустой v-select (в котором еще не 
 		// выбран элемент) отображается в виде группы radio-button, а после выбора элемента сворачивается в v-select. 
 		// По умолчанию режим отключен (false)
		radioMode: {
			type: Boolean,
			default: false,
		},

	},

	computed: {

		// Мобильная версия сайта
		mobile: function () {
			return this.windowWidth < 768;
		},

		// Кол-во колонок с учетом текущего размера окна
		// (для мобильной версии - всегда одна колонка независимо от настроек)
		columnsComputed: function () {
			return this.mobile ? 1 : this.columns;
		},

		// Класс для v-select
		vSelectClass: function () {
			return {
				// Если колонок > 1, располагать компоненты в строку
				"d-inline-block": this.columnsComputed > 1,
				// Добавить отступ над каждой строкой, кроме первой				
				"mt-2": this.level > this.columnsComputed,
				// Добавить отступ справа от каждого элемента, кроме последнего в строке
				"mr-2": this.level % this.columnsComputed != 0,
			};
		},

		// Стиль для v-select
		vSelectStyle: function () {
			var width = this.columnsComputed > 1 ? this.elementWidth + "px" : "inherit";
			return {
				// Если колонок > 1, ограничить максимальную и минимальную ширину v-select
				"max-width": width,
				"min-width": width,
			}
		},

		// Класс для radio-button-group
		radioButtonGroupClass: function () {
			return {
				// Добавить отступ над каждой группой radio-button кроме первой
				"mt-2": this.level > 1,
			};
		},

	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Отслеживать изменение ширины окна
		var self = this;
		this.$nextTick(function() {
			window.addEventListener('resize', function(e) {
				self.windowWidth = window.innerWidth;
			});
		});
	},

};
</script>