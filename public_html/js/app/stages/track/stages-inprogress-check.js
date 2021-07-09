/**
 * Отправка задач на проверку
 */
export class StagesInprogressCheck {

	constructor() {

		/**
		 * Массив id задач со статусам "в резерве" и прогрессом  < 100%
		 * @type {Array}
		 */
		this.reservedStages = [];

		/**
		 * Массив id задач, которые отправляются на проверку
		 * @type {Array}
		 */
		this.stagesIds = [];

		/**
		 *
		 * @type {{formInprogressSend: string}} Форма для сдачи работы
		 * @type {{stageInprogressCheckModal: string}} Модальное окно выбора задачи для отправки на проверку
		 * @type {{stageInprogressSendModal: string}} Модальное окно подтверждения отправки выбранные задач на проверку
		 * @type {{stageInprogressCheckCheckbox: string}} Чекбоксы задач для отправки на проверку
		 * @type {{buttonStageInprogressCheck: string}} Кнопка отправки выбранные задачи на проверку
		 * @type {{buttonStageInprogressCheckClose: string}} Кнопка закрытия модалки выбора задач
		 * @type {{buttonStageInprogressSend: string}} Кнопка отправки на проверку
		 * @type {{blockChangeText: string}} Изменяемая строка с текстом об выбраных задач
		 */
		this.selectors = {
			formInprogressSend: 'form_pass_work',
			stageInprogressCheckButton: 'js-track-inprogress-check-button',
			stageInprogressCheckModal: 'js-stage-inprogress-check-modal',
			stageInprogressSendModal: 'js-stage-inprogress-send-modal',
			stageInprogressCheckCheckbox: 'js-stage-inprogress-check-checkbox',
			buttonStageInprogressCheck: 'js-stage-inprogress-check-submit',
			buttonStageInprogressCheckClose: 'js-stage-inprogress-check-close',
			buttonStageInprogressSend: 'js-stage-inprogress-send-submit',
			blockChangeText: 'js-change-text',
			trackStage: 'js-track-stage',
		};


		this.event();
	}

	event() {
		$(document)
			.on('click', '.' + this.selectors.stageInprogressCheckButton, (e) => {
				e.preventDefault();
				this.calcReservedStages();
				this.showModal();
			})
			.on('change', '.' + this.selectors.stageInprogressCheckCheckbox, (e) => {
				this.selectCheckbox($(e.target));
			})
			.on('click', '.' + this.selectors.buttonStageInprogressCheck, () => {
				this.showModalConfirm();
			})
			.on('click', '.' + this.selectors.buttonStageInprogressSend, (e) => {
				this.sendStagesToCheck(e);
			})
			.on('click', '.' + this.selectors.buttonStageInprogressCheckClose, () => {
				this.reservedStages = [];
				$('.' + this.selectors.stageInprogressCheckModal).modal('hide');
			})
			.on('hide.bs.modal', '.' + this.selectors.stageInprogressCheckModal, () => {
				$('.' + this.selectors.stageInprogressCheckCheckbox).prop('checked', false);
				$('.' + this.selectors.buttonStageInprogressCheck).prop('disabled', true).addClass('disabled');
			})
			.on('hide.bs.modal', '.' + this.selectors.stageInprogressSendModal, () => {
				this.reservedStages = [];
			});
	}

	/**
	 * Открытие модального окна при отправке заказа на проверку
	 */
	showModal() {
		this.stagesIds = [];

		// Нужно ли показывать попап для выбора задач
		if (this.countReservedStages() > 1) {
			this.showModalStageCheck();
		} else {
			this.stagesIds.push(this.reservedStages[0]);
			this.showModalConfirm();
		}
	}

	/**
	 * Показать модальное окно выбора задач, которые отправлять на проверку
	 */
	showModalStageCheck() {
		$('.' + this.selectors.stageInprogressCheckModal).modal('show');
	}

	/**
	 * Смена статуса чаекбокса задачи
	 */
	selectCheckbox($checkbox) {
		if ($('.' + this.selectors.stageInprogressCheckCheckbox).filter(':checked').length) {
			$('.' + this.selectors.buttonStageInprogressCheck).prop('disabled', false).removeClass('disabled');
		} else {
			$('.' + this.selectors.buttonStageInprogressCheck).prop('disabled', true).addClass('disabled');
		}

		if ($checkbox.is(':checked')) {
			this.stagesIds.push($checkbox.val());
		} else {
			this.stagesIds.splice(this.stagesIds.indexOf($checkbox.val()), 1);
		}
	}

	/**
	 * Показать модальное окно подтверждения отправки на проверку
	 */
	showModalConfirm() {
		$('.' + this.selectors.stageInprogressCheckModal).modal('hide');
		$('.' + this.selectors.stageInprogressSendModal).modal('show');

		this.changeTextForSend();
	}

	/**
	 * Изменить текст в первом условии отправки на проверку
	 */
	changeTextForSend() {
		let textAdd = [];
		$.each(this.stagesIds, (k, stageId) => {
			let $trackStage = $('.' + this.selectors.stageInprogressCheckModal).find('.' + this.selectors.trackStage).filter('[data-stage-id="' + stageId + '"]');
			if ($trackStage.length) {
				textAdd.push($trackStage.find('.js-stage-name').text());
			}
		});

		$('.' + this.selectors.blockChangeText).html('1. ' +
			t('{{0}} "{{1}}" соответствует заданию покупателя и выполнена в полном объеме.', [
				Utils.declOfNum(textAdd.length, [t('Работа над задачей'), t('Работа над задачами'), t('Работа над задачами')]),
				textAdd.join(', ')
			]));
	}

	/**
	 * Отправка задач на проверку
	 */
	sendStagesToCheck(e) {

		$('#' + this.selectors.formInprogressSend).find('input[name=stageIds]').val(this.stagesIds.join(','));
		TrackUtils.onInprogressConfirmSubmit(e);

		$('.' + this.selectors.stageInprogressSendModal).modal('hide');

		// убираем из модального окна задачи, которые были отправлены на проверку
		$.each(this.stagesIds, (k, stageId) => {
			$('.' + this.selectors.stageInprogressCheckModal).find('.' + this.selectors.trackStage).filter('[data-stage-id="' + stageId + '"]').remove();
		});

		// активируем кнопку, если еще остались задачи для проверки
		this.calcReservedStages();
		if (this.countReservedStages() > 0) {
			$('.' + this.selectors.stageInprogressCheckButton).show();
		} else {
			$('.' + this.selectors.stageInprogressCheckButton).hide();
		}
	}

	/**
	 * Кол-во зарезервированных задач не на проверке
	 * @returns {number}
	 */
	countReservedStages() {
		return this.reservedStages.length;
	}

	/**
	 * Собираем зарезервированные задачи не на проверке
	 * @returns {boolean}
	 */
	calcReservedStages() {
		this.reservedStages = [];
		let $trackStages = $('.' + this.selectors.stageInprogressCheckModal).find('.' + this.selectors.trackStage);
		$.each($trackStages, (k, v) => {
			this.reservedStages.push($(v).data('stage-id'));
		});
	}
}
