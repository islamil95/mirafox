/**
 * Указание прогресса выполнения задачи в модальном окне
 */
export class StageProgressModal {

	constructor() {

		this.classes = {
			trackStages: 'js-track-stages',
			trackStage: 'js-track-stage',
			newReport: 'new-report',
			stageProgressModal: 'js-stage-progress-modal',
			buttonSendProgress: 'js-stage-progress-send',
			progressBarFill: 'progress-bar-fill',
			progressBarMinActive: 'min-active-value',
		};
	}

	events() {

		this.buttonSendProgress
			.off('click')
			.on('click', () => {
				this.stageSendProgress();
			});

		this.modalMassegeInput.on('input', () => {
			this.modalError.html('');
		});

		this.progressBar.filter(':not(.' + this.classes.progressBarFill + ')').on('click', () => {
			this.modalError.html('');
		});

		this.modal.on('hide.bs.modal', () => {
			this.defaultModal();
		});
	}

	initModal(stageId) {
		this.stageId = stageId;
		this.modal = $('.' + this.classes.stageProgressModal);
		this.modalStageTitle = this.modal.find('.js-stage-progress-title');
		this.modalStageProgress = this.modal.find('.js-progress-value');
		this.modalMassegeInput = this.modal.find('.js-field-input');
		this.modalMassegeInputHint = this.modal.find('.js-field-input-hint');
		this.modalError = this.modal.find('.js-stage-progress-error');
		this.buttonSendProgress = this.modal.find('.js-stage-progress-send');
		this.progressBar = this.modal.find('.progress-bar');

		this.trackStages = $('.' + this.classes.trackStages);
		this.trackStage = this.trackStages.find('.' + this.classes.trackStage + '[data-stage-id="' + this.stageId + '"]');
		this.progressValueCurrent = this.trackStage.find('.js-stage-progress').text();

		this.events();
	}

	/**
	 * Показать модальное окно прогресса задачи
	 */
	showModal() {
		this.defaultModal();

		this.modalStageTitle.html(t('Задача') + ' № ' + this.trackStage.find('.js-stage-number').text() + '. ' + this.trackStage.find('.js-stage-name').text());
		this.modalStageProgress.html(this.progressValueCurrent);
		this.updateProgressBar(this.progressValueCurrent);
		// сколько осталось максимум написать символов
		descAreaHint.init(this.modalMassegeInput, this.modalMassegeInputHint, 0, 350);

		this.modal.modal('show');

	}

	/**
	 * Обновление значения в прогресс баре
	 * @param progress
	 */
	updateProgressBar(progress) {
		this.progressBar.each((k, v) => {
			if ($(v).data('value') <= progress) {
				$(v).addClass(this.classes.progressBarFill);
				$(v).addClass(this.classes.progressBarMinActive);
			} else {
				$(v).removeClass(this.classes.progressBarFill);
				$(v).removeClass(this.classes.progressBarMinActive);
			}
		});
	}

	/**
	 * Привести модальное окно к дефолту
	 */
	defaultModal() {
		this.modalStageTitle.html('');
		this.modalStageProgress.html('');
		this.updateProgressBar(0);
		this.modalMassegeInput.val('');
		this.modalError.html('');
	}

	validateModal() {
		if (this.progressValueCurrent === this.modalStageProgress.text()) {
			this.modalError.text(t('Необходимо изменить прогресс'));
			return false;
		}

		if (this.modalMassegeInput.val() === '') {
			this.modalError.text(t('Необходимо ввести комментарий'));
			return false;
		}

		return true;
	}

	/**
	 * Отправить прогресс задачи
	 */
	stageSendProgress() {
		if (this.xhr) {
			this.xhr.abort();
		}

		if (!this.validateModal()) {
			return;
		}

		let formData = new FormData();
		formData.append('orderId', this.trackStage.data('order-id'));
		formData.append('stages', JSON.stringify([{id: this.stageId, progress: this.modalStageProgress.text()}]));
		formData.append('message', this.modalMassegeInput.val());

		this.xhr = $.ajax({
			url: '/track/action/worker_report_new',
			type: 'post',
			data: formData,
			dataType: 'html',
			processData: false,
			contentType: false,
			success: (result) => {
				try {
					result = JSON.parse(result);
					if (result.success === false || result.status === 'error') {
						let message;
						if (result.errors) {
							message = result.errors[0].text;
						} else {
							message = result.response;
						}
						this.modalError.text(message);
					}
				} catch (e) {
					this.modal.modal('hide');
					let trackId = $(result).data('trackId');
					window.appTracks.$refs.trackList.applyContent({
						id: trackId,
						html: result,
					}, () => {
						window.scrollTo(0, getElementTopToScroll($('.step-block-order_item:last')));
						this.removeNewReport();
					});
				}
			},
		});
	}

	/**
	 * Если пргресс отправляется через таблицу,
	 * то блок запроса промежуточного отчета удаляется
	 */
	removeNewReport() {
		$('.' + this.classes.newReport).remove();
	}
}
