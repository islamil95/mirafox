<template>
	<div v-if="!timeUpdate" class="time-c">
		{{ timeStr }}
	</div>
	<div v-else class="time-c">
		<span class="tooltipster" data-tooltip-side="bottom" data-tooltip-theme="light" data-tooltip-interactive="false" :data-tooltip-text="timeUpdateTooltip">{{ t('изменено')}} {{ timeUpdate }}</span>
	</div>
</template>

<script>
import i18nMixin from 'appJs/i18n-mixin';  // Локализация
import dateMixin from 'appJs/date-mixin';  // Форматирование даты

export default {
	mixins: [dateMixin, i18nMixin],

	props: {
		time: {
			type: Number,
			default: 0,
		},

		updatedAt: {
			type: Number,
			default: 0,
		},

		fullDate: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			i18n: {
				en: {
				},
			},
		};
	},

	created() {
		this.i18nInit();
	},

	computed: {
		timeStr() {
			return (this.fullDate ? this.getDate(this.time, false) + ', ' : '') + this.getTime(this.time);
		},

		timeUpdate() {
			if (this.updatedAt) {
				return (this.fullDate ? this.getDate(this.updatedAt, false) + ', ' : '') + this.getTime(this.updatedAt);
			}
			return null;
		},
		
		timeUpdateTooltip() {
			if (this.timeUpdate) {
				return this.t('<p>{{0}}, {{1}}</p><p>Изменено: {{2}}, {{3}}</p>', [this.getDate(this.time), this.getTime(this.time), this.getDate(this.updatedAt), this.getTime(this.updatedAt)]);
			}
			return '';
		},
	},
}
</script>