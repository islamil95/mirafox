<template>
	<div class="emoji-panel">
	
		<!-- Навигация по эможи -->
		<nav class="emoji-panel__nav">
			<button type="button" v-for="(type, index) in emojiList" :key="index" :class="{ 'emoji-panel__nav-item_active': index === navActiveIndex, 'emoji-panel__nav-item': 1 }" @click="clickTypeEmoji(index)"><i :class="type.icon"></i></button>
		</nav>
		
		<!-- Список эможи -->
		<div v-scroll="scrollEmoji" class="emoji-panel__list" ref="emojiList">
			<!-- Раздел эможи -->
			<div v-for="(type, index1) in emojiList" :key="index1" ref="emojiType">
				<div class="emoji-panel__title">{{ type.name }}</div>
				<ul>
					<li v-for="(emoji, index2) in type.list" :key="index2" @click="clickEmoji(emoji.code)" :class="'emoji-panel__emoji emoji-panel__emoji_' + emoji.code" tabindex="-1">
					<img :src="cdnImageUrl('/emoji/emoji-blank.png')" alt=""/>
					</li>
				</ul>
			</div>
		</div>
	</div>
</template>
<script>
	/**
	 * Компонент отображает форму редактирования сообщений
	 * navActiveIndex - индекс активной вкладки(тип эможи) вычисляется во время скрола
	*/
	
	// Список emoji
	import emojiListMixin from "appJs/emoji/emoji-list-mixin";

	// Приведение относительных ссылок к абсолютным (CDN)
	import cdnMixin from "appJs/cdn-mixin.js";

	export default {
		mixins: [emojiListMixin, cdnMixin],
		data () {
			return {			
				navActiveIndex: 0,
			}
		},
			
		directives: {
			/**
			 * Событие для скрола определения типа группы эможи
			*/
			scroll: {
				inserted: function (el, binding) {
					let f = function (evt) {
						binding.value(evt, el);
					}
					el.addEventListener('scroll', f);				
				}
			},
		},
		
		methods: {		
			/**
			 * При скроле списка эможи, определяем какие типы эможи просматриваем и помечаем соответственную вкладку
			*/
			scrollEmoji: function (evt, el) {
				let emojiType = this.$refs.emojiType;
				let visibleIndex = 0;
				for (let i=0; i<emojiType.length; i++) {
					if (el.scrollTop >= emojiType[i].offsetTop - 1) {
						visibleIndex = i;
					}
				}
				this.navActiveIndex = visibleIndex;
				return false
			},
			
			/**
			 * Отображаем выбранный тип эможи
			*/			
			clickTypeEmoji: function (index) {
				this.$refs.emojiList.scrollTop = this.$refs.emojiType[index].offsetTop;
			},			
			
			/**
			 * Отправляем выбранный код эможи на родительский компонент.
			*/	
			clickEmoji: function(code) {
				this.$emit('change', code);
			}
			
		},
	}
</script>