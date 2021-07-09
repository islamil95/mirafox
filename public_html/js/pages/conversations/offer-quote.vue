<template>
	<div class="cm-offer-quote">
		<div><b>{{ t('На бирже размещен проект') }} &quot;<a :href="offer.want_url">{{ wantName }}</a>&quot;</b></div>
		<div>{{ wantDesc }}</div>
		<br />
		<div><span v-html="offerText"></span><price-with-currency format="full" :value="offer.price" :lang="offer.lang || 'ru'"></price-with-currency>.</div>
		<br />
		<div><b>{{ t('Комментарий') + ':' }}</b> {{ offerComment }}</div>
	</div>	
</template>

<script>
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";

	export default {
		mixins: [i18nMixin],

		data () {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'На бирже размещен проект': 'On the exchange posted the project',
						'Продавец откликнулся на этот проект,': 'The seller responded to this request',
						'предложив кворк': 'by offering the kwork',
						'сделав предложение': 'by offering',
						'с дополнительными опциями, всего': 'with additional options,',
						'на сумму': 'in total',
						'Комментарий': 'Comment',
					},
				},
			}
		},

		watch: {
			offer: function() {
				this.updateQuoteLang();
			},
		},

		props: [
			'offer',
		],

		computed: {
			wantName() {
				let name = this.offer.want_name || '';
				return he.decode(name);	
			},

			wantDesc() {
				let desc = this.offer.want_desc || '';
				return he.decode(desc);
			},

			offerComment() {
				let comment = this.offer.comment || '';
				return he.decode(comment);
			},

			offerText() {
				let isCustom = this.toBool(this.offer.is_custom);
				let hasOptions = this.toBool(this.offer.has_options);
				let text = '<b>' + this.t('Продавец откликнулся на этот проект,');
				if (!isCustom) {
					text += ' ' + this.t('предложив кворк') + ' &quot;<a' + (this.offer.kwork_url ? ' href="' + this.offer.kwork_url + '"' : '') + '>' + _.escape(this.offer.title) + '</a>&quot;';
				} else {
					text += ' ' + this.t('сделав предложение') + ' &quot;' + _.escape(this.offer.title) + '&quot;';
				}
				text += '</b>';
				if (!isCustom && hasOptions) {
					text += ' ' + this.t('с дополнительными опциями, всего');
				}
				text += ' ' + this.t('на сумму') + ' ';
				return text;
			},
		},

		created: function() {
			// Инициализировать mixin локализации
			this.i18nInit();
			this.updateQuoteLang();
		},

		methods: {
			updateQuoteLang() {
				if (this.offer.lang) {
					this.i18nForceLocale(this.offer.lang);
				}
			},

			toBool(val) {
				if (val == '1') {
					val = true;
				} else if (val == '0') {
					val = false;
				}
				return val;
			},
		}
	}
</script>