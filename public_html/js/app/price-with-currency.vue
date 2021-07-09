<template>
	<span v-html="formattedPrice"></span>
</template>

<script>
export default {
	data () {
		return {
			currencyIds: {},
			langIds: {},
		};
	},

	props: {
		format: {
			type: String,
			default: 'symbol',
		},
		value: {
			default: 0,
		},
		currency: {
			type: String,
			default: "0",
		},
		lang: {
			type: String,
			default: '',
		},
	},

	computed: {
		formattedPrice: function () {
			let html = '';
			if ((this.currency && this.currency == this.currencyIds.usd) || (this.lang && this.lang == this.langIds.en)) {
				html += '$';
			}
			let langId = lang;
			_.forOwn(this.langIds, (v, k) => {
				if(v == this.lang) {
					langId = k;
				}
			});
			let value = 0;
			if (this.value) {
				value = Utils.priceFormat(this.value, langId);
				html += value;
			}
			if ((this.currency && this.currency == this.currencyIds.rub) || (this.lang && this.lang == this.langIds.ru)) {
				if (this.format == 'full') {
					html += ' ' + declension(value, 'рубль', 'рубля', 'рублей', 'ru');
				} else {
					html += '<span class="rouble">Р</span>';
				}
			}
			return html;
		}
	},

	created: function() {
		this.currencyIds = window.currencyIds || {};
		this.langIds = window.langIds || {};
	}
};
</script>