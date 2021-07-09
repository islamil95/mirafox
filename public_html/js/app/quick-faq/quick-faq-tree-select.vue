<template>
	<div class="articles">
		<!-- Список статей -->
		<div v-for="option of options" class="article-header" @click="selectArticle(option)">
			<div class="article-title">
				<!-- Заголовок -->
				<span>{{ useAltQuestion ? option.alt_question : option.question }}</span>
			</div>
			<!-- Иконка, обозначающая возможность перехода к дочерним статьям -->
			<div v-if="getHasChilds(option)" class="goto-childs-icon"></div>
		</div>
	</div>
</template>

<script>

/**
 * Компонент отображает дерево статей базы знаний с бесконечной вложенностью. Обязательные свойства:
 *   tree - дерево. Каждый элемент дерева должен иметь свойства id, parent_id и name (названия можно переопределить, 
 *     см. ниже)
 * Опционально можно задать:
 *   element-label - название свойства, содержащее метку элемента
 *   element-key - название свойства, содержащее уникальный инентификатор элемента
 *   element-parent-key - название свойства, содержащее ид родительского элемента
 *   customSort - метод для сортировки элементов (по умолчанию элементы не сортируются)
 *   use-alt-question - использовать альтернативный заголовок статьи (используется в поиске и популярных статьях)
 * Компонент геренирует события:
 *   input - пользователь выбрал статью в дереве, в событие передается выбранная статья
 */

// Алгоритмы работы с деревом
import treeMixin from "appJs/tree-mixin";

export default {
	mixins: [treeMixin],

	data () {
		return {

		};
	},

	props: {

		useAltQuestion: {
			type: Boolean,
			default: false,
		}

	},

	methods: {

		/**
		 * Выбрать статью
		 * @param {object} article
		 */
		selectArticle: function (article) {
			this.$emit("input", article);
		}

	},

};
</script>