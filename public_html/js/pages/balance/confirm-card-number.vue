<template>
	<b-modal class="wrapper-b-modal vue-b-modal"
			 :title="t('Подтверждение номера карты')"
			 v-model="modalShow"
			 id="confirmCardNumber"
			 size="md"
			 hide-footer
			 centered
			 static
	>
		<p class="mt5">{{ t('В целях безопасности просим вас подтвердить номер карты для вывода средств. Пожалуйста, введите недостающие цифры.') }}</p>

		<form @submit.prevent="send">
			<div class="confirm-card-number">
				<div class="confirm-card-number-field">
					<div class="field-title">Номер карты</div>
					<label>
						<input class="field-span" disabled type="text" :value="beginCardNumber" :style="widthCardNumber(beginCardNumber)"/>
						<the-mask type="text" :tokens="cardNumberTokens" :mask="secretCardNumber" v-model="enterCardNumber" ref="inputCardNumber" :placeholder="secretCardNumber" :style="widthCardNumber(secretCardNumberForWidth)"/>
						<input class="field-span" disabled type="text" :value="endCardNumber" :style="widthCardNumber(endCardNumber)"/>
					</label>
					<div class="field-error text-danger" v-if="error">{{ error }}</div>
					<div class="field-message text-success" v-if="message">{{ message }}</div>
				</div>
			</div>

			<footer class="modal-footer">
				<button type="submit" class="button button-success">{{ t('Подтвердить') }}</button>
			</footer>
		</form>
	</b-modal>
</template>

<script>
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	// Модальное окно
	import { BModal, VBModal } from "bootstrap-vue";

	// маска для полей
	import TheMask from 'vue-the-mask';

	Vue.component("b-modal", BModal);
	Vue.directive("b-modal", VBModal);

	export default {
		mixins: [i18nMixin],

		data() {
			return {
				i18n: {
					en: {
						'Подтверждение номера карты': '',
						'В целях безопасности просим вас подтвердить номер карты для вывода средств. Пожалуйста, введите недостающие цифры.': '',
						'Подтвердить': '',
						'Введите недостающие цифры': ''
					}
				},
				modalShow: false,
				beginCardNumber: '',
				endCardNumber: '',
				secretCardNumber: '',
				secretCardNumberForWidth: '',
				enterCardNumber: '',
				error: '',
				message: '',
				errors: {
					empty: t('Введите недостающие цифры')
				},
				windowWidth: window.innerWidth,
			}
		},

		props: {
			cardNumber: String,
			foxtoken: {
				type: String,
				default: '',
			},
		},

		computed: {
			fullCardNumber() {
				return (this.beginCardNumber + this.enterCardNumber + this.endCardNumber).replace(/\s+/g, '');
			},
		},

		created() {
			// Инициализировать mixin локализации
			this.i18nInit();

			// Разбиваем секретный номер на части
			if (this.cardNumber) {
				let partNumbers = /^(.[^\*]+)([\* ]+)(.+)$/.exec(this.cardNumber);

				this.beginCardNumber = partNumbers[1];
				this.secretCardNumber = partNumbers[2].replace(/\*/g, 'X').trim();
				this.secretCardNumberForWidth = partNumbers[2].replace(/\*/g, '9');
				this.endCardNumber = partNumbers[3];
			}

			this.cardNumberTokens = {
				X: {
					pattern: /[0-9*]/
				}
			};
		},

		mounted() {
			window.onresize = () => {
				this.windowWidth = window.innerWidth;
			};
		},

		methods: {
			/**
			 * Отправка формы
			 */
			send() {
				let self = this;
				if (this.enterCardNumber.length <= 0) {
					this.error = this.errors.empty;
					return;
				}

				axios.post('/settings_update_card_full_number', {
					solar_card_full_number: this.fullCardNumber,
					foxtoken: this.foxtoken
				}).then((response) => {
					if (response.data.success) {
						// если все ok, то выставить window.needConfirmCardNumber = false;
						window.needConfirmCardNumber = false;

						// после успешного подтверждения карты выводим введенную сумму
						$('#purse-submit').click();

						if (response.data.data && response.data.data.messages.length) {
							self.error = '';
							self.message = response.data.data.messages[0];
						}

						setTimeout(() => {
							self.modalShow = false;
						}, 2000);
					} else {
						self.error = response.data.data.errors[0];
					}
				});
			},

			/**
			 * Уставливаем ширину инпута в зависимости от кол-ва символов, которые необходимо ввести
			 */
			widthCardNumber(textForWidth) {
				let fontSize = this.windowWidth <= '767' ? '20px' : '30px';
				let $divText = $('<div ></div>')
						.text(textForWidth)
						.css({
							'position': 'absolute',
							'white-space': 'pre',
							'visibility': 'hidden',
							'font-size': fontSize,
							'letter-spacing': '1px',
						})
						.appendTo($('body')),
					divTextWidth = $divText.width() + 2;

				$divText.remove();

				return {
					"width": divTextWidth + 'px'
				}
			}
		},
	};
</script>
