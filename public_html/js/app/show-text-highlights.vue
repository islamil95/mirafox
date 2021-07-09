<template>
	<div ref="wrapper" class="wrapper">
		<!-- Текст с хайлайтами -->
		<div v-if="hasHighlights" class="d-flex align-items-center">
			<div class="left" :style="leftStyle" v-html="leftText"></div><!--
		 --><div class="center" :style="centerStyle">{{ centerText }}</div><!--
		 --><div class="right" :style="rightStyle" v-html="rightText"></div>
		</div>
		<!-- Текст без хайлайтов -->
		<div v-else>
			<div class="all-text" :style="centerStyle">{{ centerText }}</div>
		</div>
	</div>
</template>

<script>

/**
 * Компонент отображает текст с хайлайтами text таки образом, чтобы
 * первый хайлайт, отмеченный тэгами <b></b> всегда был в поле зрения, причем 
 * слева от хайлайта отображается только текущее предложение. При изменении
 * ширины компонента вначале урезается часть после хайлайта (ставится ... после
 * текста), потом часть перед хайлайтом (ставится ... до текста), потом
 * сам хайлайт. Компонент использует шрифты родителя.
 */

// Утилита для измерения ширины текста
import calculateSize from "calculate-size";

export default {
	data () {
		return {
			// Левая часть текста (до хайлайта)
			leftText: "",
			// Центральная часть текста (хайлайт)
			centerText: "",
			// Правая часть текста (поле хайлайта)
			rightText: "",
			// Ширина левой части
			leftWidth: 0,
			// Ширина центральной части
			centerWidth: 0,
			// Ширина правой части
			rightWidth: 0,
			// Ширина текста левой части
			leftTextWidth: 0,
			// Ширина текста центральной части
			centerTextWidth: 0,
			// Ширина компонента
			componentWidth: 0,
			// Минимальная ширина левой и правых частей
			minWidth: 0,
			// Шрифт обычного текста (не хайлайта)
			normalFont: null,
			// Шрифт хайлайта
			boldFont: null,
			// В тексте есть хайлайты
			hasHighlights: false,
		};
	},

	computed: {

		/**
		 * Стиль левой части (до хайлайта)
		 * @return {object}
		 */
		leftStyle: function () {
			return {
				"max-width": this.leftWidth + "px",
				// Скрыть левую часть, если ширина 0
				"display": this.leftWidth == 0 ? "none" : "inline-block",
			};
		},

		/**
		 * Стиль центральной части (хайлайт)
		 * @return {object}
		 */
		centerStyle: function () {
			if (this.centerWidth == this.componentWidth) {
				return {
					"max-width": this.centerWidth + "px",
				};
			} else {
				return {};
			}
		},

		/**
		 * Стиль правой части (после хайлайта)
		 * @return {object}
		 */
		rightStyle: function () {
			return {
				"max-width": this.rightWidth + "px",
				// Скрыть правую часть, если ширина 0
				"display": this.rightWidth == 0 ? "none" : "inline-block",
			};
		},		

	},

	props: {

		// Текст
		text: {
			type: String,
			default: "",
		},

	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Дождаться загрузки шрифтов, иначе измерения ширины текста
		// будут давать неверные значения
		var self = this;
		if (document.readyState === 'complete') {
			this.init();			
		} else {
			$(window).bind("load", function() {
				self.init();
			});
		}
		// Отслеживать изменение ширины окна
		this.$nextTick(function() {
			window.addEventListener("resize", function(e) {
				self.resize();
			});
		});
	},

	methods: {

		/**
		 * Инициализация компонента
		 */
		init: function () {
			var text = this.text;
			// Найти первое вхождение тэга <b>
			var hightlightIndex = text.indexOf("<b>");
			// Если в тексте есть хайлайты
			if (hightlightIndex >= 0) {
				this.hasHighlights = true;
				// Разбить текст по хайлайту на левую, центральную и правую части
				// Найти первый хайлайт в тексте
				var centerTextMatch = text.match(/( ?<b>.*?<\/b> ?)/);
				var centerText = centerTextMatch[1];
				var leftTextIndex = 0;
				// Найти начало предложения с хайлайтом
				for (var i = centerTextMatch.index; i >= 0; i--) {
					if (text[i] == ".") {
						leftTextIndex = i;
						break;
					}
				}
				// Сформировать левую часть (от начала предложения до хайлайта)
				leftTextIndex = leftTextIndex > 0 ? leftTextIndex + 1 : 0;
				var leftText = text.slice(leftTextIndex, centerTextMatch.index).trim();
				// Если левая часть заканчивается на знак препинания, перенести его
				// в начало строки, т.к. левая часть отображается в режиме RTL
				var punctuationMarks = [".", ",", ":", ";", "-", "!", "?", ")", "("];
				if (punctuationMarks.indexOf(leftText[leftText.length - 1]) >= 0 ) {
					var punctuationMark = leftText[leftText.length - 1];	
					// Заменить открывающую скобку на закрывающую и наоборот
					if (punctuationMark == "(") {
						punctuationMark = ")";
					} else if (punctuationMark == ")") {
						punctuationMark = "(";
					}							
					leftText = leftText.slice(0, leftText.length - 1);
					leftText = punctuationMark + leftText;
				}
				// Сформировать правую часть (от конца хайлайта до конца текста)
				var rightText = text.slice(centerTextMatch.index + centerText.length, text.length).trim();
				// Убрать тэги из хайлайта
				centerText = centerText.replace("<b>", "");
				centerText = centerText.replace("</b>", "");
				// Записать итоговые тексты в данные компонента
				this.leftText = leftText;
				this.centerText = centerText;
				this.rightText = rightText;
			} else {
				// Если в тексте нет хайлайтов
				// Записать в центральную часть весь текст целиком
				this.centerText = this.text;
			}
			// Получить шрифт из родительского компонента
			var style = getComputedStyle(this.$refs.wrapper);
			var fontFamily = style.fontFamily;
			var fontSize = style.fontSize;
			// Подобрать шрифт для обычного текста (как в родительском компоненте)
			// и выделенного текста (жирный шрифт)
			this.normalFont = {
			   font: fontFamily,
			   fontSize: fontSize,
			   fontWeight: "400",
			};
			this.boldFont = _.clone(this.normalFont);
			this.boldFont.fontWeight = "800";
			// Вычислить ширину левого и центрального текстов
			this.centerTextWidth = this.calculateWidth(this.centerText, this.boldFont);
			this.leftTextWidth = this.calculateWidth(this.leftText, this.normalFont);
			// Минимальная ширина правой/левой части
			this.minWidth = 50;			
			// Пересчитать ширину дочерних компонентов
			this.resize();
		},

		/**
		 * Пересчитать ширину дочерних компонентов
		 */
		resize: function () {
			if (this.$refs.wrapper === undefined) {
				return;
			}
			// Вычсилить текущую ширину компонента
			this.componentWidth = this.$refs.wrapper.clientWidth;
			// Если центральная часть с левой и правой частями, ужатыми до предела,
			// не влезают в компонент, оставить только центральную часть
			var minLeftAndRightPartsWidth = (this.leftTextWidth == 0 ? 0 : this.minWidth) +
				(this.rightTextWidth == 0 ? 0 : this.minWidth);
			if (minLeftAndRightPartsWidth + this.centerTextWidth > this.componentWidth) {
				this.centerWidth = this.componentWidth;
				this.leftWidth = 0;
				this.rightWidth = 0;
			} else {
				// Ширина центральной части по ширине текста
				// (должен помещаться целиком)
				this.centerWidth = this.centerTextWidth;
				// Если левая часть помещается полностью (с учетом того, что правая часть ужмется до минимума)
				var minRightPartWidth = this.rightTextWidth == 0 ? 0 : this.minWidth;
				if (this.leftTextWidth + this.centerTextWidth + minRightPartWidth < this.componentWidth) {
					// Взять ширину левой части по ширине текста
					this.leftWidth = this.leftTextWidth;
					// Правая часть должна занимать все оставшееся место
					this.rightWidth = minRightPartWidth == 0 ? 0 : this.componentWidth - this.centerWidth - this.leftWidth;
				} else {
					// Если левая часть полностью не помещается
					// Правая часть должна быть минимальной ширины
					this.rightWidth = minRightPartWidth;
					// Левая часть должна занимать все оставшееся место
					this.leftWidth = this.componentWidth - this.centerWidth - this.rightWidth;
				}
			}
		},

		/**
		 * Вычислить ширину текста в пикселях для заданного стиля
		 * (метод учитывает пробелы в начале и конце строки)
		 * @param {string} text
		 * @param {object} style
		 * @return {number}
		 */
		calculateWidth: function (text, style) {
			if (text == "") {
				return 0;
			}
			// Если строка начинается/заканчивается на пробел, добавить к ширине ширину пробелов
			var spacesCount = 0;
			if (text[text.length - 1] == " ") {
				spacesCount += 1;
			}
			if (text[0] == " ") {
				spacesCount += 1;
			}
			// Вычислить ширину пробелов
			var spacesWidth = 0;
			if (spacesCount > 0) {
				var delimiter = "#";
				var spacesTestString = new Array(spacesCount + 1).join(delimiter + " ") + delimiter;
				var delimitersTestString = new Array(spacesCount + 2).join(delimiter);
				spacesWidth = calculateSize(spacesTestString, style).width - 
					calculateSize(delimitersTestString, style).width;
			}
			return calculateSize(text, style).width + spacesWidth + 1;
		},

	},

};
</script>

<style>
	
	.wrapper {
		white-space: nowrap;
		overflow: hidden;
		min-width: initial;
	}

	.left, .center, .right, .all-text {
		display: inline-block;
		white-space: pre;                   
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.center {
		font-weight: bold;
	}

	.left {
	    direction: rtl;
	    text-align: right;
	}

</style>