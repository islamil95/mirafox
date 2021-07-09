export class OfferModal {

	constructor() {

		/**
		 * Выбрана оплата заказа с задачами
		 * @type {string}
		 * @const
		 */
		this.TYPE_OFFER_STAGES = 'stages';

		/**
		 * Выбрана полная оплата
		 * @type {string}
		 * @const
		 */
		this.TYPE_OFFER_ALL = 'all';

		/**
		 * Тип оплаты
		 * @type {string}
		 */
		this.selectType = '';

		this.modalBlock = $('.js-offer-modal');
		this.offerPrice = this.modalBlock.find('.js-offer-price');
		this.offerRadioSelect = this.modalBlock.find('.js-offer-radio');
		this.offerConfirm = this.modalBlock.find('.js-offer-continue');

		this.offerStagesBlock = this.modalBlock.find('.js-offer-stages');
		this.offerStages = this.offerStagesBlock.find('.js-stages');

		this.classes = {
			activeTypeOffer: 'offer-modal__radio-item--active',
			buttonDisabled: 'btn_disabled',
			offerStage: 'js-stage',
			offerStageName: 'js-stage-name',
			offerStagePrice: 'js-stage-price',
			offerStageTotalPrice: 'js-offer-total-price',
			offerStageDurationChange: 'js-stages-duration-change-select',
			offerHiddenText: '.js-offer-hidden-text',
		};

		this.options = [];

		this.events();
	}

	init(options) {
		this.options = options;
	}

	events() {
		//выбор типа оплаты
		this.offerRadioSelect.on('click', (e) => {
			this.offerRadioSelect.removeClass(this.classes.activeTypeOffer);
			$(e.currentTarget).addClass(this.classes.activeTypeOffer);

			// если оплата по задачам
			if ($(e.currentTarget).data('type') === this.TYPE_OFFER_STAGES) {
				this.selectType = this.TYPE_OFFER_STAGES;

				this.offerStagesBlock.show();
				this.unlockButton();
				this.offerConfirm.html(t('Оплатить <span>и активировать заказ</span>'));

				this.checkLockButton();
			} else {
				this.selectType = this.TYPE_OFFER_ALL;

				this.offerStagesBlock.hide();
				this.unlockButton();
				this.offerConfirm.html(t('Оплатить'));
				this.unlockButton();
			}
		});

		this.offerConfirm.on('click', () => {
			this.confirm();
		});

		// Событие на закрытие модального окна
		this.modalBlock.on('hide.bs.modal', () => {
			this.defaultModal();
		});
	}

	confirm() {

		if (this.xhr) {
			this.xhr.abort();
		}

		let formData = new FormData();

		$.each(this.options.form, (k, v) => {
			formData.append(k, v);
		});

		// если выбрана оплата по задачам, то отправляем задачи
		if (this.selectType === this.TYPE_OFFER_STAGES) {

			window.offerStages.reSaveStages();
			if (window.offerStages.checkDisableButtonSend()) {
				this.lockButton();

				return;
			}


			let $stages = $(this.offerStagesBlock).filter('[data-order-id="' + this.options.orderId + '"]');
			let $stage = $(this.offerStagesBlock).filter('[data-order-id="' + this.options.orderId + '"]').find('.' + this.classes.offerStage);
			$stage.each((k, v) => {
				let stageNameInput = $(v).find('.' + this.classes.offerStageName);
				let stagePriceInput = $(v).find('.' + this.classes.offerStagePrice);

				formData.append(stageNameInput.attr('name'), stageNameInput.val());
				formData.append(stagePriceInput.attr('name'), stagePriceInput.val());
			});

			// отправка на сколько изменять срок
			let stageDurationSelect = $stages.find('.' + this.classes.offerStageDurationChange);
			if (stageDurationSelect.val() !== null) {
				formData.append(stageDurationSelect.attr('name'), stageDurationSelect.val());
			}
		}

		this.lockButton();

		this.xhr = $.ajax({
			url: this.options.action,
			type: 'post',
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: (response) => {
				/**
				 * @param response
				 * @param {boolean} response.success
				 * @param {string} response.status
				 * @param {number} response.code
				 * @param {integer} response.needMoney
				 * @param {Object} response.data
				 * @param {string} response.redirectUrl
				 * @param {array} response.errors
				 * @param {array} response.stagedData
				 */
				if (!response.success && response.code === 124) {

					if (this.selectType === this.TYPE_OFFER_STAGES) {
						if (this.options.userId) {
							// если диалог
							window.conversationApp.$refs.conversationMessagesList.onEditMessage({
								'mid': this.options.form.inboxId,
								'from': this.options.userId
							});
						} else {
							// если биржа
							this.updateContent(this.options.orderId, response.stagedData);
						}
					}

					this.hideModal();

					let balancePopupFromType = 'project';
					if (this.options.userId) {
						balancePopupFromType = 'inbox';
					}
					show_balance_popup(response.needMoney, balancePopupFromType, undefined, response.orderId);

				} else if (!response.success && response.status === 'error') {

					window.offerStages.showBackendStageErrors(response.errors, false);

				} else if (typeof (response.redirectUrl) !== 'undefined') {

					document.location.href = response.redirectUrl;

				} else {

					document.location.reload();

				}
			},
			error: () => {
				document.location.reload();
			},
		});
	}

	updateContent() {
	}

	/**
	 * Инициализация задач
	 */
	initStages() {

		window.offerStages.init({
			actorType: 'payer',
			stages: this.options.stages,
			classDisableButton: 'js-offer-continue',
			offer: {
				orderId: this.options.orderId,
				lang: this.options.lang,
				stageMinPrice: this.options.stageMinPrice,
				customMinPrice: this.options.customMinPrice,
				customMaxPrice: this.options.customMaxPrice,
				offerMaxStages: this.options.offerMaxStages,
				stagesPriceThreshold: this.options.stagesPriceThreshold,
				price: this.options.price,
				duration: this.options.duration,
				initialDuration: this.options.initialDuration,
				stageMaxIncreaseDays: this.options.stageMaxIncreaseDays,
				stageMaxDecreaseDays: this.options.stageMaxDecreaseDays,
			},
			controlEnLang: this.options.controlEnLang,
			countStages: this.options.countStages
		});

		window.offerStages.generationStages();

		window.offerStages.onChangeStage = () => {
			this.checkLockButton();
		};
	}

	/**
	 * Блокировать ли кнопку отправки формы
	 */
	checkLockButton() {
		if (window.offerStages.checkDisableButtonSend()) {
			this.lockButton();
		} else {
			this.unlockButton();
		}
	}

	/**
	 * Заблокировать кнопку
	 */
	lockButton() {
		this.offerConfirm
			.attr('disabled', 'disabled')
			.addClass(this.classes.buttonDisabled);
	}

	/**
	 * Разблокировать кнопку
	 */
	unlockButton() {
		this.offerConfirm
			.removeAttr('disabled')
			.removeClass(this.classes.buttonDisabled);
	}

	/**
	 * Открытие модального окна
	 */
	showModal() {
		this.defaultModal();
		this.offerStagesBlock.data('order-id', this.options.orderId)
			.attr('data-order-id', this.options.orderId);
		this.offerPrice.html(Utils.priceFormatWithSign(this.options.price, this.options.lang, " ", '₽'));
		this.initStages();
		this.modalBlock.modal('show');
	}

	/**
	 * Закрытие модального окна
	 */
	hideModal() {
		this.modalBlock.modal('hide');
		this.defaultModal();
	}

	/**
	 * Привести модальное окно к дефолту
	 */
	defaultModal() {
		this.offerPrice.html('');
		this.offerRadioSelect.removeClass(this.classes.activeTypeOffer);

		this.lockButton();
		this.offerConfirm.html(t('Далее'));

		this.offerStagesBlock.data('order-id', '');
		this.offerStagesBlock.hide();
		this.offerStages.html('');

		if (_.isEmpty(this.options.stages)) {
			$(this.classes.offerHiddenText).removeClass('hidden');
		} else {
			$(this.classes.offerHiddenText).addClass('hidden');
		}

		window.offerStages.defaultNotChangeShowErrorsBlock();
		window.offerStages.setButtonSend(false);
		window.offerStages.hideTotalPriceError();
		window.offerStages.hideDurationChangeBlock();
		window.offerStages.reBuildDuration(0);
		window.offerStages.initStagesBlock(0);

		window.offerStages.initPrev();
	}

}

window.OfferModal = new OfferModal();
