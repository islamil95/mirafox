<template>
		<b-modal ref="modal"
				 v-model="deleteAccount"
				 :title="t('Удаление аккаунта')"
				 :modal-class="['modal-radius', 'delete-account-modal', {'delete-account-modal--show-image': deleteStep === DELETE_STEP_PROCESS_CONFIRM && isSendBySms}]"
				 centered
				 no-enforce-focus
				 no-close-on-backdrop
		>
			<template slot="modal-header-close">
				<svg class="icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14"><path d="M1 1l6 6-6 6M13 1L7 7l6 6"/></svg>
			</template>

			<template v-if="deleteStep === DELETE_STEP_PROCESS_CONFIRM && isSendBySms">
				<img :src="cdnImageUrl('/man_with_phone.png')" alt="" width="215" height="451">
			</template>

			<div>
				<!-- начальный блок при открытии модалки  -->
				<template v-if="deleteStep === DELETE_STEP_START">
					<template v-if="isWorkerTypeActor">
						<p>{{ t('Вы действительно хотите удалить свой аккаунт на Kwork?') }}</p>
						<p>{{ t('Все активные заказы будут отменены, а аккаунт будет безвозвратно удален. Kwork прикладывает большие усилия для борьбы с аккаунтами-клонами, поэтому использовать в новых аккаунтах email, телефон и финансовые реквизиты, которые были привязаны к удаленному аккаунту, будет невозможно. Эти данные в обезличенном виде добавляются в стоп-лист Kwork.') }}</p>
					</template>
					<template v-else>
						<p>{{ t('Вы действительно хотите удалить свой аккаунт на Kwork?') }}</p>
						<p>{{ t('Ваш аккаунт будет безвозвратно удален. Kwork прикладывает большие усилия для борьбы с аккаунтами-клонами, поэтому использовать в новых аккаунтах email, телефон и финансовые реквизиты, которые были привязаны к удаленному аккаунту, будет невозможно. Эти данные в обезличенном виде добавляются в стоп-лист Kwork.') }}</p>
					</template>
				</template>
				<!-- пользователь нажал удалить и прошла проверка на наличие денег и активных заказов -->
				<template v-if="deleteStep === DELETE_STEP_BALANCE_OR_ORDERS">
					<!-- если у пользователя есть невыведенные деньги -->
					<div v-if="hasMoney">
						<p>{{ t('Упс… Кажется, на вашем балансе еще остаются средства. Если вы окончательно и бесповоротно решили удалить аккаунт, то сначала выведите все средства с баланса себе на карту или электронный кошелек.') }}</p>
					</div>
					<!-- если у пользователя есть активные заказы -->
					<div v-if="orders.total">
						<template v-if="orders.total === 1">
							<template v-if="hasMoney"><p>{{ t('Также у вас остался незавершенный заказ на Kwork:') }}</p></template>
							<template v-else><p>{{ t('Похоже, у вас остался незавершенный заказ на Kwork:') }}</p></template>
						</template>
						<template v-else>
							<template v-if="hasMoney"><p>{{ t('Также у вас остались незавершенные заказы на Kwork:') }}</p></template>
							<template v-else><p>{{ t('Похоже, у вас остались незавершенные заказы на Kwork:') }}</p></template>
						</template>

						<div class="delete-account-modal__orders-list" v-if="orders.worker.length">
							<template v-for="(v, k) in orders.worker">
								<a :href="'/track?id=' + v.id">{{ v.name}}</a>
							</template>
						</div>
						<div class="delete-account-modal__orders-list" v-if="orders.payer.length">
							<template v-for="(v, k) in orders.payer">
								<a :href="'/track?id=' + v.id">{{ v.name}}</a>
							</template>
						</div>

						<template v-if="!hasMoney && !orders.payer.length">
							<template v-if="orders.total === 1">
								<p>{{ t('Вы уверены, что хотите отменить заказ и удалить учетную запись?') }}</p>
							</template>
							<template v-else>
								<p>{{ t('Вы уверены, что хотите отменить заказы и удалить учетную запись?') }}</p>
							</template>
						</template>
						<template v-else-if="hasMoney && !orders.payer.length">
							<template v-if="orders.total === 1">
								<p>{{ t('При удалении учетной записи, заказ будет отменен.') }}</p>
							</template>
							<template v-else>
								<p>{{ t('При удалении учетной записи, заказы будут отменены.') }}</p>
							</template>
						</template>
						<template v-else-if="orders.payer.length">
							<template v-if="orders.total === 1">
								<p>{{ t('Чтобы удалить учетную запись, дождитесь выполнения заказа или отмените его. После этого выведите средства с баланса.') }}</p>
							</template>
							<template v-else>
								<p>{{ t('Чтобы удалить учетную запись, дождитесь выполнения заказов или отмените их. После этого выведите средства с баланса.') }}</p>
							</template>
						</template>
					</div>
				</template>
				<!-- подтверждение удаления. Появляется этот блок если нет денег на балансе и нет активных заказов -->
				<template v-else-if="deleteStep === DELETE_STEP_PROCESS_CONFIRM">
					<template v-if="isSendByEmail">
						<p>{{ t('Для подтверждения удаления аккаунта на вашу почту было отправлено письмо с ссылкой. Пожалуйста, перейдите по ней для удаления аккаунта.') }}</p>
					</template>
					<template v-if="isSendBySms">
						<p>{{ t('Для подтверждения удаления аккаунта на номер телефона') }}</p>
						<p class="delete-account-modal__phone">{{ this.phone }}</p>
						<p>{{ t('отправлено SMS-сообщение с кодом подтверждения. Введите код активации и подтвердите удаление аккаунта. Плата за SMS не взимается.') }}</p>

						<div class="send-code">
							<label>
								<the-mask type="text" class="styled-input delete-account-confirmation" :tokens="smsCodeTokens" :mask="smsCodeMask" :placeholder="smsCodeMask" v-model="smsCode" @focus.native="onFocusSmsCode"/>
								<span>{{ t('Введите код из SMS')}}</span>
							</label>
							<div class="send-code__repeat" v-show="this.isSendRepeatSms()">
								<span class="send-code__repeat-link" @click="deleteAccountAndRepeatSms('repeat')">{{ t('Выслать SMS еще раз') }}</span>
							</div>
							<span class="send-code__error">
								<span>{{ getErrorRepeatSms }}</span>
								<span v-html="errorConfirmSms"></span>
							</span>
						</div>
					</template>
				</template>
				<!-- пользователь дошел до этапа непосредственного удаления, но удалить нельзя из-за баланса или активных заказов -->
				<template v-else-if="deleteStep === DELETE_STEP_BALANCE_OR_ORDERS_REPEAT">
					<div v-if="hasMoney && orders.total">
						<p>{{ t('Удаление не выполнено, необходимо завершить все активные заказы и вывести средства с баланса.') }}</p>
					</div>
					<div v-else-if="hasMoney">
						<p>{{ t('Удаление не выполнено, необходимо вывести средства с баланса.') }}</p>
					</div>
					<div v-else-if="orders.total">
						<p>{{ t('Удаление не выполнено, необходимо завершить все активные заказы.') }}</p>
					</div>

					<template v-if="orders.total">
						<div class="delete-account-modal__orders-list" v-if="orders.worker.length">
							<template v-for="(v, k) in orders.worker">
								<a :href="'/track?id=' + v.id">{{ v.name}}</a>
							</template>
						</div>
						<div class="delete-account-modal__orders-list" v-if="orders.payer.length">
							<template v-for="(v, k) in orders.payer">
								<a :href="'/track?id=' + v.id">{{ v.name}}</a>
							</template>
						</div>
					</template>
				</template>
				<!-- при успешном подтверждении по СМС, если были у пользователя фин. операции за последние 6 мес. -->
				<template v-else-if="deleteStep === DELETE_STEP_DELETED_FROM_SMS">
					<p>{{ t('В целях безопасности учетная запись будет удалена через 30 дней. Уже сейчас ваш аккаунт недоступен. Восстановление доступа после истечения этого срока будет невозможно.') }}</p>
				</template>
			</div>

			<template slot="modal-footer">

				<!-- начальный блок при открытии модалки  -->
				<template v-if="deleteStep === DELETE_STEP_START">
					<button class="kwork-button" type="button" @click.prevent="closeModal">{{ t('Отмена') }}</button>
					<button class="kwork-button kwork-button_theme_red-filled" :class="{onload: onLoad}" type="button" @click="canUserSelfDelete()">{{ t('Удалить') }}</button>
				</template>
				<!-- пользователь нажал удалить и прошла проверка на наличие денег и активных заказов -->
				<template v-if="deleteStep === DELETE_STEP_BALANCE_OR_ORDERS">
					<button class="kwork-button" type="button" @click.prevent="closeModal">{{ t('Отмена') }}</button>

					<button v-if="!hasMoney && !orders.payer.length" type="button" class="kwork-button kwork-button_theme_red-filled" :class="{onload: onLoad}" @click="deleteAccountAndRepeatSms('first')">{{ t('Все равно удалить') }}</button>
					<button v-if="!hasMoney && orders.payer.length" type="button" class="kwork-button kwork-button_theme_red-filled" @click="redirectToTrack">{{ t('Перейти к заказу') }}</button>
					<button v-if="hasMoney" type="button" class="kwork-button kwork-button_theme_red-filled" @click="redirectToBalance">{{ t('Вывести деньги') }}</button>
				</template>
				<!-- подтверждение удаления. Появляется этот блок если нет денег на балансе и нет активных заказов -->
				<template v-else-if="deleteStep === DELETE_STEP_PROCESS_CONFIRM">
					<button v-if="isSendByEmail" class="kwork-button kwork-button--center kwork-button_theme_red-filled" type="button" @click.prevent="closeModal">{{ t('ОК') }}</button>
					<button v-if="isSendBySms" class="kwork-button kwork-button--center kwork-button_theme_red-filled" :class="{onload: onLoad}" type="button" @click.prevent="confirmSms">{{ t('Подтвердить') }}</button>
				</template>
				<!-- пользователь дошел до этапа непосредственного удаления, но удалить нельзя из-за баланса или активных заказов -->
				<template v-else-if="deleteStep === DELETE_STEP_BALANCE_OR_ORDERS_REPEAT">
					<button class="kwork-button" type="button" @click.prevent="closeModal">{{ t('Отмена') }}</button>

					<button v-if="!hasMoney && orders.payer.length" type="button" class="kwork-button kwork-button_theme_red-filled" @click="redirectToTrack">{{ t('Перейти к заказу') }}</button>
					<button v-if="hasMoney" type="button" class="kwork-button kwork-button_theme_red-filled" @click="redirectToBalance">{{ t('Вывести деньги') }}</button>
				</template>
				<!-- при успешном подтверждении по СМС, если были у пользователя фин. операции за последние 6 мес. -->
				<template v-else-if="deleteStep === DELETE_STEP_DELETED_FROM_SMS">
					<button class="kwork-button kwork-button--center kwork-button_theme_red-filled" type="button" @click.prevent="logoutAccount">{{ t('ОК') }}</button>
				</template>
			</template>
		</b-modal>
</template>

<script>
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	// маска для полей
	import TheMask from "vue-the-mask";
	// Приведение относительных ссылок к абсолютным (CDN)
	import cdnMixin from "appJs/cdn-mixin.js";

	const DELETE_STEP_START = 1;
	const DELETE_STEP_BALANCE_OR_ORDERS = 2;
	const DELETE_STEP_PROCESS_CONFIRM = 3;
	const DELETE_STEP_BALANCE_OR_ORDERS_REPEAT = 4;
	const DELETE_STEP_DELETED_FROM_SMS = 5;

	/**
	 * Компонент отображает модальное окно, в котором происходит процедура удаление аккаунта.
	 * Обязательные свойства:
	 *   is-worker-type-actor - тип пользователя продавец?
	 *   phone - телефон пользователя, если подтвержден
	 */
	export default {
		mixins: [i18nMixin, cdnMixin],

		data() {
			return {
				deleteAccount: false,
				smsCode: '',
				/** Этапы удаления. для отображения разных блоков */
				deleteStep: DELETE_STEP_START,
				/** Есть ли деньги на счетае пользователя */
				hasMoney: false,
				/**
				 * Активные заказы пользователя
				 *
				 * @type {Object} orders
				 * @property {object} orders.payer
				 * @property {boolean} orders.total
				 * @property {object} orders.worker
				 */
				orders: {},
				/** Подтверждение через СМС */
				isSendBySms: false,
				/** Подтверждение через почту */
				isSendByEmail: false,
				/** Ошибка подтверждения СМС */
				errorConfirmSms: '',
				/** Ошибка при повторном вызове СМС */
				errorRepeatSms: '',
				/** Таймер ожидания отправки повтотной СМС */
				timerRepeatSms: null,
				/** Сколько осталось секунд до возможности повтороной отправки СМС */
				currentTimeRepeatSms: 0,
				/** Есть ли фин. операции за последние 6 мес. */
				haveOperations: false,
				/** маска для воода кода из СМС для подтверждения удаления аккаунта */
				smsCodeMask: '****',
				/** были ли у пользователя фин. операции за последние 6 мес. */
				isHaveOperations: false,
				/** идет загрузка запроса */
				onLoad: false,

				/** первоначальный блок при открытии модалки */
				DELETE_STEP_START: DELETE_STEP_START,
				/** запрос на проверку баланса и заказов. Отображается если есть баланс или заказы */
				DELETE_STEP_BALANCE_OR_ORDERS: DELETE_STEP_BALANCE_OR_ORDERS,
				/** подтверждение по смс или email */
				DELETE_STEP_PROCESS_CONFIRM: DELETE_STEP_PROCESS_CONFIRM,
				/** Удаление не выполнено. Отображается если есть баланс или заказы при попытке удаления */
				DELETE_STEP_BALANCE_OR_ORDERS_REPEAT: DELETE_STEP_BALANCE_OR_ORDERS_REPEAT,
				/** аккаунт удален через СМС */
				DELETE_STEP_DELETED_FROM_SMS: DELETE_STEP_DELETED_FROM_SMS,
			}
		},

		props: {
			isWorkerTypeActor: {
				type: Boolean,
				default: true,
			},
			phone: {
				type: String,
				default: '',
			}
		},

		computed: {

			/**
			 * Получаем ошибку при повторном вызове отправки СМС
			 */
			getErrorRepeatSms() {
				if (this.currentTimeRepeatSms > 0) {
					return t('Вы можете отправить sms повторно через {{0}} сек.', [this.currentTimeRepeatSms])
				} else {
					return this.errorRepeatSms;
				}
			},
		},

		watch: {
			/**
			 * Отслеживаем сколько времени осталось, чтобы можно было снова отправить СМС
			 */
			currentTimeRepeatSms(time) {
				if (time <= 0) {
					this.stopTimerSms();
				}
			},
		},

		created() {
			this.i18nInit();

			this.smsCodeTokens = {
				'*': {
					pattern: /[0-9*]/
				}
			};


		},

		mounted() {
			$(document).ready(() => {
				$('.js-delete-account-link').on('click', () => {
					this.deleteAccount = true;
				})
			});
		},

		destroyed() {
			this.stopTimerSms()
		},

		methods: {

			canUserSelfDelete() {
				this.onLoad = true;
				axios.post("/can_user_self_delete").then((response) => {
					this.onLoad = false;

					/**
					 * @type {Object} responseData
					 * @property {object} responseData.orders
					 * @property {boolean} responseData.balance
					 * @property {boolean} responseData.isSuccess - true означает возможность удалить
					 */
					let responseData = response.data.data;

					if (!this.canDelete(responseData)) {
						// если есть деньги на блансе или активные заказы
						this.deleteStep = this.DELETE_STEP_BALANCE_OR_ORDERS;
					} else {
						this.deleteAccountAndRepeatSms('first');
					}

				}).catch((e) => {
					console.log(e);
				});
			},

			/**
			 * Запрос на проверку может ли аккаунт самоудалиться.
			 * Повторная отправка СМС
			 */
			deleteAccountAndRepeatSms(step) {
				if (!this.isSendRepeatSms()) {
					return;
				}
				this.errorRepeatSms = '';

				// если не потоврный запрос СМС
				if (step !== 'repeat') {
					this.onLoad = true;
				}

				axios.post("/user_self_delete").then((response) => {
					this.onLoad = false;

					/**
					 * @type {Object} responseData
					 * @property {boolean} responseData.data.sendBySms
					 * @property {integer} responseData.data.time_left - колько осталось секунд до возвожности отправки следующей СМС
					 * @property {string} responseData.data.text - ошибка при отправке СМС
					 * @property {boolean} responseData.success
					 */
					let responseData = response.data;

					if (!this.canDelete(responseData.data)) {
						this.deleteStep = this.DELETE_STEP_BALANCE_OR_ORDERS_REPEAT;

						return;
					}

					if (responseData.data.sendBySms === false) {
						// если email успешно отправлено
						this.deleteStep = this.DELETE_STEP_PROCESS_CONFIRM;
						this.isSendByEmail = true;
					} else if (responseData.data.sendBySms && step === 'first') {
						this.deleteStep = this.DELETE_STEP_PROCESS_CONFIRM;
						this.isSendBySms = true;
					} else if (responseData.data.time_left && step !== 'first') {
						// ожидание следующей возможности отправки СМС
						this.deleteStep = this.DELETE_STEP_PROCESS_CONFIRM;
						this.isSendBySms = true;
						this.currentTimeRepeatSms = responseData.data.time_left;
						this.startTimerSms();
					} else if (responseData.success === false) {
						// если другая ошибка при отправке СМС
						this.deleteStep = this.DELETE_STEP_PROCESS_CONFIRM;
						this.isSendBySms = true;
						this.errorRepeatSms = responseData.data.text;
					}

				}).catch((e) => {
					console.log(e);
				});
			},

			/**
			 * Запускаем таймер ожидания отправки повтотнойСМС
			 */
			startTimerSms() {
				this.timerRepeatSms = setInterval(() => {
					this.currentTimeRepeatSms--
				}, 1000)
			},

			/**
			 * Останавливаем таймер ожидания отправки повтотной СМС
			 */
			stopTimerSms() {
				clearTimeout(this.timerRepeatSms)
			},

			/**
			 * Подтверждение кода из СМС
			 */
			confirmSms() {
				if (!this.smsCode) {
					this.errorConfirmSms = t('Введите код');

					return;
				}
				this.onLoad = true;
				axios.post("/self_delete_check_sms", {
					code: this.smsCode
				}).then((response) => {
					this.onLoad = false;

					let responseData = response.data;

					if (!this.canDelete(responseData.data)) {
						this.deleteStep = this.DELETE_STEP_BALANCE_OR_ORDERS_REPEAT;

						return;
					}

					// если у пользователя были фин. операции за последние 6 мес.
					if (responseData.success && responseData.data.haveOperations) {
						this.deleteStep = this.DELETE_STEP_DELETED_FROM_SMS;
						this.isHaveOperations = true;

						// автоматический выход из аккаунта происходит спустя 7 секунд
						setInterval(() => {
							this.logoutAccount();
						}, 7000);
					} else if (responseData.success){
						this.logoutAccount();
					} else if (!responseData.success) {
						this.errorConfirmSms = responseData.data.text;
					}
				}).catch((e) => {
					console.log(e);
				});
			},

			/**
			 * Можно ли удалить аккаунт
			 */
			canDelete(responseData) {
				this.orders = responseData.orders;
				this.hasMoney = responseData.balance;
				// нельзя удалить аккаунт, если есть заказы или деньги
				return !((this.orders && this.orders.total > 0) || this.hasMoney);
			},

			/**
			 * Возможен ли повторный запрос СМС
			 */
			isSendRepeatSms() {
				return this.currentTimeRepeatSms <= 0 && !this.errorRepeatSms;
			},

			/**
			 * Фокус на поле ввода кода из СМС
			 */
			onFocusSmsCode() {
				this.errorConfirmSms = '';
			},

			/**
			 * Выход из аккаунта. Сам выход происходит на бэке, на фронте достаточно перезагрузить страницу.
			 */
			logoutAccount() {
				this.closeModal();
				window.location.href = '/';
			},

			redirectToBalance() {
				window.location.href = '/balance#withdrawMoney';
			},

			redirectToTrack() {
				window.location.href = '/orders';
			},

			/**
			 * Закрыть форму
			 */
			closeModal: function () {
				this.deleteAccount = false;
			},
		}
	}
</script>
