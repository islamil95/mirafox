<template>
	<div>
		<div v-for="(item, index) in items" class="collapse-block" :class="{'active': blockClass(index)}" :key="index">
			<div class="collapse-block__title" @click="stateToggle(index)" v-html="item.title"></div>
			<div class="collapse-block__body">
				<div class="collapse-block__body-inner" v-html="item.content"></div>
			</div>
		</div>
	</div>
</template>
<script>

/**
 * Компонент формирует список раскрываемых блоков по клику на заголовок.
*/

export default {
	data () {
		return {
			blocksState: {},
		};
	},

	props: {

		// Массив объектов
		items: {
			type: Array,
			default: function () {
				return [];
			},
		},

		// Заголовок блока
		title: {
			type: String,
			default: "title",
        },

        // Контент в раскрывающимся блоке (может содержать html теги)
		content: {
			type: String,
			default: "content",
		},

	},

	methods: {
		blockClass(index) {
			return this.blocksState[index] || false;
		},
		stateToggle: function (index) {
			this.$set(this.blocksState, index, !(this.blocksState[index] || false));
		},
	},

};
</script>