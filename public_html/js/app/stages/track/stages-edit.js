export class StagesEdit {

	constructor() {

		/**
		 * action добавления задачи
		 * @type {string}
		 */
		this.ACTION_ADD = 'add';

		/**
		 * action редактирования задач
		 * @type {string}
		 */
		this.ACTION_EDIT = 'edit';

		/**
		 * action удаления задач
		 * @type {string}
		 */
		this.ACTION_DELETE = 'delete';

		/**
		 * Добавление задачи, если заказ быд завершен
		 * @type {string}
		 */
		this.TYPE_ACTION_ADD_DONE = 'track-add-done';

		/**
		 * Добавление задач
		 * @type {string}
		 */
		this.TYPE_ACTION_ADD = 'track-add';

		this.options = {};

		/**
		 * Id задач для удаления
		 * @type {Array}
		 */
		this.deleteStages = [];

		/**
		 * Id редактируемых задач
		 * @type {Array}
		 */
		this.editeStages = [];

		this.classes = {
			trackStages: 'js-track-stages',
			stagesBlock: 'js-offer-stages',
			stageEditLink: 'js-track-stage-edit-link',
			stageAddLink: 'js-track-stage-add-link',
			stageEditModal: 'js-track-stage-edit-modal',
			stageEditForm: 'js-edit-stages-form',
			stageEditTitle: 'js-track-stage-edit-title',
			stageEditConfirm: 'js-track-stage-edit-confirm',

			stage: 'js-stage',
			offerStageName: 'js-stage-name',
			offerStagePrice: 'js-stage-price',
			offerStageDurationChange: 'js-stages-duration-change-select',

			buttonDisabled: 'disabled',
		};

		this.events();

		this.closeMessage();
	}

	events() {
		$(document)
			.on('click', '.' + this.classes.stageEditLink, (e) => {
				// если клик был по кнопки внутри задачи или по подсказке, то задача не редактируем
				if (!$(e.target).is('.js-stages-button, .tooltipster')) {
					this.initOptions($('.' + this.classes.trackStages));
					this.buildModal(this.ACTION_EDIT);
					this.showModal();
				}
			})
			.on('click', '.' + this.classes.stageAddLink, () => {
				// не рендерить модальное окно повторно
				if (!this.options || $.isEmptyObject(this.options)) {
					this.initOptions($('.' + this.classes.stageAddLink));
					this.buildModal(this.ACTION_ADD);
				}
				this.showModal();
			})
			.on('submit', '.' + this.classes.stageEditForm, (e) => {
				e.preventDefault();

				this.confirm();
			});
	}

	initOptions($elementOptions) {
		this.options = {
			action: $elementOptions.data('action'),
			typeAction: $elementOptions.data('type-action'),
			actorType: 'payer',
			pageType: 'track',
			stages: $elementOptions.data('stages'),
			classDisableButton: this.classes.stageEditConfirm,
			offer: {
				orderId: $elementOptions.data('order-id'),
				lang: $elementOptions.data('lang'),
				stageMinPrice: $elementOptions.data('stage-min-price'),
				customMinPrice: $elementOptions.data('custom-min-price'),
				customMaxPrice: $elementOptions.data('custom-max-price'),
				price: $elementOptions.data('price'),
				duration: $elementOptions.data('duration'),
				initialDuration: $elementOptions.data('initial-duration'),
				stageMaxIncreaseDays: $elementOptions.data('stages-max-increase-days'),
				stageMaxDecreaseDays: $elementOptions.data('stages-max-decrease-days'),
			},
			controlEnLang: $elementOptions.data('controlEnLang'),
			countStages: $elementOptions.data('countStages'),
		};
	}

	/**
	 * Подтверждаем изменения. Отправляем запрос
	 */
	confirm() {
		if (this.xhr) {
			this.xhr.abort();
		}

		this.lockButton();

		this.xhr = $.ajax({
			url: '/order_stages/payer_auto_approve/' + this.options.offer.orderId,
			type: 'post',
			data: this.getDataConfirm(),
			dataType: 'json',
			processData: false,
			contentType: false,
			success: (response) => {
				/**
				 * @param response
				 * @param {boolean} response.success
				 * @param {string} response.status
				 * @param {array} response.errors
				 */
				if (!response.success && response.status === 'error') {
					window.offerStages.showBackendStageErrors(response.errors, false);
				} else if (response.error === "funds") {
					$('.' + this.classes.stageEditModal).modal('hide');

					// Устанавливаем форму для повторной отправки после успешной оплаты через check_balance_payment_payed()
					recentFormToSubmit = $('.' + this.classes.stageEditForm);

					show_balance_popup(response.difference, '', response.payment_id, response.orderId);
				} else {
					document.location.href = location.href.split("&scroll")[0].split("&modal=new_stage")[0];
				}
			},
			error: () => {
				document.location.href = location.href.split("&scroll")[0].split("&modal=new_stage")[0];
			},
		});
	}

	/**
	 * Собрать все данные для отправки для изменения задач
	 * @returns {FormData}
	 */
	getDataConfirm() {
		let formData = new FormData();

		let $stagesBlock = $('.' + this.classes.stagesBlock);
		let $stages = $stagesBlock.find('.' + this.classes.stage);

		// какие задачи добавляются
		$stages.each((k, v) => {
			let $stage = $(v);
			let stageNameInput = $stage.find('.' + this.classes.offerStageName);
			let stagePriceInput = $stage.find('.' + this.classes.offerStagePrice);
			let numberStage = $stage.data('number');
			let idStage = $stage.data('id');

			if (!idStage) {
				formData.append('offers[' + numberStage + '][title]', stageNameInput.val());
				formData.append('offers[' + numberStage + '][payer_price]', stagePriceInput.val());
				formData.append('offers[' + numberStage + '][action]', this.ACTION_ADD);
			}
		});

		// какие задачи редактировать
		$.each(this.editeStages, (key, idStage) => {
			let $editStage = $stages.filter('[data-id="' + idStage + '"]');
			if (idStage && $editStage.length) {
				let numberStage = $editStage.data('number');
				let stageNameInput = $editStage.find('.' + this.classes.offerStageName);
				let stagePriceInput = $editStage.find('.' + this.classes.offerStagePrice);

				formData.append('offers[' + numberStage + '][title]', stageNameInput.val());
				formData.append('offers[' + numberStage + '][payer_price]', stagePriceInput.val());
				formData.append('offers[' + numberStage + '][order_stage_id]', idStage);
				formData.append('offers[' + numberStage + '][action]', this.ACTION_EDIT);
			}
		});

		// какие задачи удалять
		$.each(this.deleteStages, (key, idStage) => {
			if (idStage) {
				formData.append('offers[' + key + '][order_stage_id]', idStage);
				formData.append('offers[' + key + '][action]', this.ACTION_DELETE);
			}
		});

		// на сколько изменять срок
		let stageDurationSelect = $stagesBlock.find('.' + this.classes.offerStageDurationChange);
		formData.append(stageDurationSelect.attr('name'), stageDurationSelect.val());

		return formData;
	}

	/**
	 * Set modal title, button and attribute
	 * @param {String} action Action type
	 * @returns {Void}
	 */
	buildModal(action) {
		this.defaultModal();
		this.changeTitle(action);
		this.changeButton(action);
		$('.' + this.classes.stagesBlock).data('order-id', this.options.offer.orderId)
			.attr('data-order-id', this.options.offer.orderId);
		this.initStages();
	}
	
	/**
	 * Show modal
	 * @returns {Void}
	 */
	showModal() {
		$('.' + this.classes.stageEditModal).modal('show');
	}

	/**
	 * Инициализация задач
	 */
	initStages() {

		window.offerStages.init(this.options);

		window.offerStages.generationStages();

		window.offerStages.onChangeStage = () => {
			this.checkLockButton();
		};

		window.offerStages.onDeleteStage = ($stage) => {
			this.deleteStages.push($stage.data('id'));
		};

		window.offerStages.onEditStage = ($stage) => {
			this.editeStages.push($stage.data('id'));
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
	 * Изменяем тайтл модального окна
	 * @param action
	 */
	changeTitle(action) {
		if (action === this.ACTION_ADD) {
			if (this.options.typeAction === this.TYPE_ACTION_ADD_DONE) {
				$('.' + this.classes.stageEditTitle).text(t('Добавить и активировать задачу'));
			} else {
				$('.' + this.classes.stageEditTitle).text(t('Добавить задачу'));
			}
		} else if (this.options.stages.length === 1) {
			$('.' + this.classes.stageEditTitle).text(t('Редактировать задачу'));
		} else {
			$('.' + this.classes.stageEditTitle).text(t('Редактировать задачи'));
		}
	}

	/**
	 * Изменяем кнопку в зависимости от типа события
	 */
	changeButton() {
		if (this.options.typeAction === this.TYPE_ACTION_ADD_DONE) {
			$('.' + this.classes.stageEditConfirm).text(t('Оплатить и активировать задачу'));
		} else {
			$('.' + this.classes.stageEditConfirm).text(t('Сохранить'));
		}
	}

	/**
	 * Привести модальное окно к дефолту
	 */
	defaultModal() {
		$('.' + this.classes.stageEditModal).find('.js-stages').html('');

		this.lockButton();

		this.deleteStages = [];
		this.editeStages = [];

		window.offerStages.defaultNotChangeShowErrorsBlock();
		window.offerStages.hideTotalPriceError();
		window.offerStages.hideDurationChangeBlock();
		window.offerStages.reBuildDuration(0);
		window.offerStages.initStagesBlock(0);

		window.offerStages.initPrev();
	}

	/**
	 * Заблокировать кнопку подтверждения изменений
	 */
	lockButton() {
		$('.' + this.classes.stageEditConfirm)
			.attr('disabled', 'disabled')
			.addClass(this.classes.buttonDisabled);
	}

	/**
	 * Разблокировать кнопку
	 */
	unlockButton() {
		$('.' + this.classes.stageEditConfirm)
			.removeAttr('disabled')
			.removeClass(this.classes.buttonDisabled);
	}

	/**
	 * Скрываем сообщение вверху страницы о добавлении задач
	 */
	closeMessage() {
		if (!$(".fox_success").filter('[data-name="add-stages"]').length) {
			return;
		}

		setTimeout(() => {
			closeMessageByName('add-stages');
		}, 16000);

		setTimeout(() => {
			getMessageTrackByOrderId($('.' + this.classes.trackStages).data('order-id'));
		}, 14000);
	}
}
