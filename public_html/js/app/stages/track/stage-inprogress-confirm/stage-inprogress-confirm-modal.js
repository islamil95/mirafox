export class StageInprogressConfirmModal {

	constructor() {

		/**
		 * Массив id задач для отправки
		 * @type {Array}
		 */
		this.sendStagesIds = [];

		/**
		 * Массив id задач, которые нужно показать в модалке
		 * @type {Array}
		 */
		this.showStagesIds = [];

		/**
		 * Опции для модального окно
		 * @type {Array}
		 */
		this.options = [];

		/**
		 * Подтверждение задачи вверху страницы, в таблице задач (кнопка "Подтвердить работу")
		 * @type {string}
		 */
		this.TYPE_APPROVE_STAGES_TOP = 'approve-top';

		/**
		 * Подтверждение задачи внизу страницы (кнопки "Подтвердить выполнение ...")
		 * @type {string}
		 */
		this.TYPE_APPROVE_STAGES_BOTTOM = 'approve-bottom';

		/**
		 * Отправка на доработку задачи внизу страницы (кнопки "Отправить на доработку")
		 * @type {string}
		 */
		this.TYPE_REJECT_STAGES_BOTTOM = 'reject-bottom';

		/**
		 * Подтверждение задачи в трэке (кнопки "Подтвердить выполнение ...")
		 * @type {string}
		 */
		this.TYPE_APPROVE_STAGES_TRACK = 'approve-track';

		/**
		 * Отправка на доработку в трэке (кнопки "Отправить на доработку")
		 * @type {string}
		 */
		this.TYPE_REJECT_STAGES_TRACK = 'reject-track';

		this.selectors = {

			stage: 'js-track-stage',

			stageInprogressConfirm: {
				modal: 'js-stage-inprogress-confirm-modal',
				checkbox: 'js-stage-inprogress-confirm-checkbox',
				submitApprove: 'js-stage-inprogress-approve-submit',
				submitReject: 'js-stage-inprogress-reject-submit',
				form: 'js-stage-inprogress-confirm-form',
				total: 'js-stage-inprogress-confirm-total',
				totalValue: 'js-stage-inprogress-confirm-total-value',

				pluralText: 'js-stage-confirm-text-plural',
				singularText: 'js-stage-confirm-text-singular',
				rejectText: 'js-stage-confirm-text-reject',
				forRejectHide: 'js-stage-confirm-text-reject-hide',
				approveBottomText: 'js-stage-confirm-text-approve-bottom',
				notCheckText: 'js-stage-confirm-text-not-check',
			},
		};

		this.cloneModal = {};

		this.event();
	}

	event() {
		$(document)
			.off('change', '.' + this.selectors.stageInprogressConfirm.checkbox)
			.on('change', '.' + this.selectors.stageInprogressConfirm.checkbox, (e) => {
				this.selectCheckboxInprogressConfirm($(e.target));
			})
			.on('click', '.' + this.selectors.stageInprogressConfirm.submitApprove, (e) => {
				e.preventDefault();
				this.submitForm(true);
			})
			.on('click', '.' + this.selectors.stageInprogressConfirm.submitReject, () => {
				this.submitForm();
			});
	}

	/**
	 * Инициализировать параметры модального окна
	 * @param options
	 */
	initOptions(options) {
		this.options = options;
	}

	/**
	 * Заполняем модальное окно корректными данными
	 */
	initModal() {
		let modal = $('#track-form').find('.' + this.selectors.stageInprogressConfirm.modal).clone();
		let $body = $('body');

		this.sendStagesIds = [];
		this.showStagesIds = this.options.stagesIds;

		$body.children('.' + this.selectors.stageInprogressConfirm.modal).remove();

		$body.append(modal);

		this.cloneModal = $body.children('.' + this.selectors.stageInprogressConfirm.modal);
		this.cloneModal.find('.tooltipster').tooltipster(TOOLTIP_CONFIG);

		this.changeModalByOptions();
	}

	/**
	 * Показываем модальное окно
	 */
	showModal() {
		this.cloneModal.modal('show');
	}

	/**
	 * Изменпить модальное окно в зависимости от параметров
	 */
	changeModalByOptions() {

		// Изменяем action формы на подтверждение или доработку
		this.changeFormAction();

		// Отображать только переданные задачи
		this.showOnlySelectStages();

		// Добавляем чекбоксы, если необходимо
		this.addCheckbox();

		// Проставляем id задач в input для отправки
		this.setInputStageIds();

		// Изменять текстовки в модальном окне в зависимости от числа задач
		this.changeTextModalByCountStages();

		// Изменять текстовки в модальном окне в зависимости от типа
		this.changeTextModalByType();

		// Изменить кнопки в зависимости от типа
		this.changeButton();
	}

	/**
	 * Изменяем action формы на подтверждение или доработку
	 */
	changeFormAction() {
		let $form = this.cloneModal.find('form');

		if (this.options.type === this.TYPE_APPROVE_STAGES_BOTTOM
			|| this.options.type === this.TYPE_APPROVE_STAGES_TRACK
			|| this.options.type === this.TYPE_APPROVE_STAGES_TOP) {
			$form.attr('action', $form.data('action-approve'));
		} else if (this.options.type === this.TYPE_REJECT_STAGES_BOTTOM || this.options.type === this.TYPE_REJECT_STAGES_TRACK) {
			$form.attr('action', $form.data('action-reject'));
		}
	}

	/**
	 * Отображать только переданные задачи
	 */
	showOnlySelectStages() {
		if (this.showStagesIds.length) {
			this.cloneModal.find('.' + this.selectors.stage).filter((k, el) => {
				let removeStage = true;

				$.each(this.showStagesIds, (k, stageId) => {
					if ($(el).data('stage-id') === parseInt(stageId)) {
						removeStage = false;

						return false;
					}
				});

				return removeStage;
			}).remove();
		}
	}

	/**
	 * Добавляем чекбоксы, если необходимо
	 */
	addCheckbox() {
		if (this.options.isCheckbox) {
			this.cloneModal.find('.' + this.selectors.stage).filter((k, el) => {
				let stageIdCurrentElement = $(el).data('stage-id');

				$(el).find('.track-stage__status-order').append(
					'<input class="js-stage-inprogress-confirm-checkbox styled-checkbox" data-id="' + stageIdCurrentElement + '" data-price="' + $(el).data('payer-price') + '" id="stage-inprogress-confirm-checkbox-' + stageIdCurrentElement + '" type="checkbox" value="' + stageIdCurrentElement + '">' +
					'<label for="stage-inprogress-confirm-checkbox-' + stageIdCurrentElement + '">&nbsp;</label>'
				);

				this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitApprove).prop('disabled', true).addClass('disabled');
				this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitReject).prop('disabled', true).addClass('disabled');
			});
		}
	}

	/**
	 * Проставляем id задач в input для отправки
	 */
	setInputStageIds() {
		if (
			(this.options.type !== this.TYPE_APPROVE_STAGES_BOTTOM && this.options.type !== this.TYPE_REJECT_STAGES_BOTTOM)
			||
			(this.options.type === this.TYPE_APPROVE_STAGES_BOTTOM || this.options.type === this.TYPE_REJECT_STAGES_BOTTOM && !this.options.isCheckbox)
		) {
			this.cloneModal.find('input[name=stageIds]').val(this.showStagesIds.join(","));
		}
	}

	/**
	 * Изменять текстовки в модальном окне в зависимости от числа задач
	 */
	changeTextModalByCountStages() {
		if (this.options.type === this.TYPE_REJECT_STAGES_BOTTOM) {
			return;
		}

		if (this.showStagesIds.length > 1) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.pluralText).removeClass('hidden');
		} else {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.singularText).removeClass('hidden');
		}
	}

	/**
	 * Изменять текстовки в модальном окне в зависимости от типа
	 */
	changeTextModalByType() {
		if (this.options.type === this.TYPE_APPROVE_STAGES_BOTTOM) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.approveBottomText).removeClass('hidden');
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.notCheckText).removeClass('hidden');
		} else if (this.options.type === this.TYPE_REJECT_STAGES_BOTTOM) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.rejectText).removeClass('hidden');
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.forRejectHide).addClass('hidden');
		} else if (this.options.isCheck) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.notCheckText).removeClass('hidden');
		}
	}

	/**
	 * Изменить кнопки в зависимости от типа
	 */
	changeButton() {
		if (this.options.type === this.TYPE_APPROVE_STAGES_BOTTOM
			|| this.options.type === this.TYPE_APPROVE_STAGES_TRACK
			|| this.options.type === this.TYPE_APPROVE_STAGES_TOP) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitApprove).removeClass('hidden');
		} else if (this.options.type === this.TYPE_REJECT_STAGES_BOTTOM) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitReject).removeClass('hidden');
		}
	}

	/**
	 * Выбор/снятие выбора в чекбоксах по задачам при подтверждении оплаты
	 */
	selectCheckboxInprogressConfirm($checkbox) {

		if (this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.checkbox + ':checked').length) {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitApprove).prop('disabled', false).removeClass('disabled');
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitReject).prop('disabled', false).removeClass('disabled');
			if (this.options.type !== this.TYPE_REJECT_STAGES_BOTTOM) {
				this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.total).removeClass('hidden');
				this.calculateTotalPrice();
			}
		} else {
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitApprove).prop('disabled', true).addClass('disabled');
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.submitReject).prop('disabled', true).addClass('disabled');
			this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.total).addClass('hidden');
		}

		if ($checkbox.is(':checked')) {
			this.sendStagesIds.push($checkbox.val());
		} else {
			this.sendStagesIds.splice(this.sendStagesIds.indexOf($checkbox.val()), 1);
		}

		this.cloneModal.find('input[name=stageIds]').val(this.sendStagesIds.join(","));
	}

	/**
	 * Посчитать стоимость задач для оплаты
	 */
	calculateTotalPrice() {
		let $prices = $('.' + this.selectors.stageInprogressConfirm.checkbox + ':checked');
		let totalPrice = 0;

		$prices.each((k, v) => {
			totalPrice += parseInt($(v).data('price'));
		});

		$('.' + this.selectors.stageInprogressConfirm.totalValue).html(Utils.priceFormatWithSign(totalPrice, lang));
	}

	/**
	 * Отправка формы
	 */
	submitForm(isSendAjax = false) {
		let $form = this.cloneModal.find('.' + this.selectors.stageInprogressConfirm.form);
		if ('undefined' !== typeof yaCounter32983614) {
			if (this.options.type === this.TYPE_APPROVE_STAGES_BOTTOM || this.options.type === this.TYPE_APPROVE_STAGES_TRACK) {
				yaCounter32983614.reachGoal('WORK-DONE');
			} else if (this.options.type === this.TYPE_REJECT_STAGES_BOTTOM) {
				yaCounter32983614.reachGoal('WORK-NOT-DONE');
			}
		}

		// очищаем черновик
		if (window.draft) {
			window.draft.clear();
		}

		// копируем поле сообщения в форму отправки
		let messageVal = $("#message_body1").val();
		
		// Обрабатываем поле сообщения, убираем теги и замняем эможи на код
		if ($("#message_body1").hasClass('trumbowyg-textarea')) {
			messageVal = window.emojiReplacements.preSubmitMessage(messageVal);
		}
		
		$form.find('[name="message"]').val(messageVal);

		// копируем файлы в форму отправки
		let filesUploader = _getFileUploader();
		$.each(filesUploader.files, (k, v) => {
			$form.append('<input type="hidden" name="file-input[new][]" value="' + v.file_id + '">');
		});
		filesUploader.$refs.fileUploader.clearFiles();
		

		if (isSendAjax) {
			// Закрываем модальное окно
			$('.modal').modal('hide');
			// Функция отправляет ajax запрос и показывает попапы о успешном заказе
			sendAjaxConfirmOrder($form);
		}
		else {
			$form.submit();
		}
	}
}
