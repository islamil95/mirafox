<template>
	<div class="copy-link tooltipster d-inline-block fast" data-tooltip-side="right" data-tooltip-interactive="false" 
		:title="t('Скопировать')" :id="tooltipId">
		<i class="fa fa-anchor" @click.stop="copyLink"></i>
	</div>
</template>

<script>

/**
 * Компонент отображает кнопку для копирования ссылки. При наведении на кнопку показывается тултип
 * с локализованным текстом "скопировать". При клике на кнопку ссылка копируется в буфер обмена и
 * тултип меняется на "скопировано". Если тултипы недоступны, анимируется иконка с якорем.
 * Обязательные свойства:
 *   url - относительная ссылка (без имени сайта и ведущего символа /)
 */

// Локализация
import i18nMixin from "appJs/i18n-mixin";
// Буфер обмена
import clipboard from "appJs/clipboard-mixin";
// Анимации
import animateMixin from "appJs/animate-mixin";

export default {
	mixins: [i18nMixin, clipboard, animateMixin],

	data () {
		return {
			// Ид для тултипа
			tooltipId: "",
			// Локализация компонента
			i18n: {
				en: {
					"Скопировать": "Copy",
					"Скопировано": "Copied",
				}
			},			
		};
	},

	props: {

		// URL ссылки для копирования
		url: {
			type: String,
			default: "",
		},

	},

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();		
		// Сгенерировать случайный ид для тултипа
		this.tooltipId = "tooltip_" + Math.floor(Math.random()*10000);
	},

	methods: {

		/**
	     * Скопировать ссылку
	     */
	    copyLink: function () {
	    	if ("tooltipster" in $) {
	    		$("#" + this.tooltipId).tooltipster("content", this.t("Скопировано"));
	    	} else {
	    		this.animate(this.tooltipId, "bounce");
	    	}
	    	this.copyToClipboard(window.location.origin + "/" + this.url);
	    },

	},

};
</script>