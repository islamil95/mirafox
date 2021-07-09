<template>
	<div class="emoji-btn">
		<div @click="clickSwitchEmojiPanel" v-if="isShowPanel" class="emoji-btn__close-area"></div>
		<div v-show="isShowPanel" :class="{'emoji-btn__emoji-panel': true, 'emoji-btn__emoji-panel_left': positionPanel == 'left'}" ref="emoji-panel">
			<emoji-panel @change="onChange($event)"></emoji-panel>
		</div>
		
		<!-- Кнопка по нажатию отображается панель с выбором эможи -->
		<div @click="clickSwitchEmojiPanel" :class="{'emoji-btn__switch-panel': true, 'emoji-btn__switch-panel_active': isShowPanel}">
			<img class="emoji-btn__emoji-img" :src="cdnImageUrl('/emoji/emoji-smile.svg')" alt="">
			<img class="emoji-btn__emoji-img-hover" :src="cdnImageUrl('/emoji/emoji-smile-hover.svg')" alt="">
		</div>
	</div>
</template>
<script>
	/**
	 * Компонент показывает кнопку и по клику открывается выбор эможи
	 * isShowPanel - для отображения панели с эможи
	 * emojiPanelMarginTop - корректируем положение панели если нижняя часть не помещяется в экран
	*/	
	
	// Панель с эможи, проверяем не объявлен ли этот компонент глобально ранее
	if(Vue.options.components["emoji-panel"]) {
		Vue.component("emoji-panel", require("appJs/emoji/emoji-panel.vue").default);
	}

	// Приведение относительных ссылок к абсолютным (CDN)
	import cdnMixin from "appJs/cdn-mixin.js";
	
	export default {
		mixins: [cdnMixin],
		data () {
			return {
				isShowPanel: false,
			}
		},
		
		props: {
			// Расположение панели
			positionPanel: false,
		},
		
		methods: {			
			
			/**
			 * Получаем и отправляем выбранный код эможи на родительский компонент.
			*/	
			onChange(code) {
				this.$emit('change', code);
				this.isShowPanel = false;
			},		
			
			/**
			 * Показываем/скрываем панель с выбором эможи
			*/	
			clickSwitchEmojiPanel() {
				this.isShowPanel = !this.isShowPanel;
			}
		},
	}
</script>