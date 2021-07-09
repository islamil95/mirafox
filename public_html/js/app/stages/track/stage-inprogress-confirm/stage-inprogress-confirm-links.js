import {StageInprogressConfirmModal} from 'appJs/stages/track/stage-inprogress-confirm/stage-inprogress-confirm-modal.js';

export class StageInprogressConfirmLinks {

	constructor() {

		this.trackStagesIds = [];

		this.selectors = {
			linkApproveBottom: 'js-stage-inprogress-approve-link-bottom',
			linkRejectBottom: 'js-stage-inprogress-reject-link-bottom',
			linkApproveTop: 'js-stage-inprogress-confirm-link-top',
			track: {
				block: 'checkWork-js',
				stagesCheckbox: 'js-track-stages-checkbox',
				linkApproveStages: 'js-track-approve-stage',
				linkRejectStages: 'js-track-reject-stage',
				formRejectStages: 'js-track-reject-stage-form',
			}
		};

		this.stageInprogressConfirmModal = new StageInprogressConfirmModal();

		this.event();
	}

	event() {
		$(document)
			// подтверждения внизу страницы и кнопки около формы
			.on('click', '.' + this.selectors.linkApproveBottom, (e) => {
				this.stagesApproveBottom($(e.target));
			})
			// задачи на доработку внизу страницы около формы
			.on('click', '.' + this.selectors.linkRejectBottom, (e) => {
				this.stagesRejectBottom($(e.target));
			})

			// подтверждения вверху страницы на строке задачи
			.on('click', '.' + this.selectors.linkApproveTop, (e) => {
				this.stageInprogressConfirmModal.initOptions({
					stagesIds: [$(e.target).data('stage-id')],
					type: 'approve-top',
					isCheck: $(e.target).closest('.js-track-stage').data('is-check'),
				});
				this.stageInprogressConfirmModal.initModal();
				this.stageInprogressConfirmModal.showModal();
			})

			// выбираем задачи в трэке
			.on('change', '.' + this.selectors.track.stagesCheckbox, (e) => {
				this.checkboxInprogressConfirmInTrack($(e.target));
			})
			// подтверждения в трэке
			.on('click', '.' + this.selectors.track.linkApproveStages, (e) => {
				this.stagesApproveTrack($(e.target));
			})
			// задачи на доработку в трэке
			.on('click', '.' + this.selectors.track.linkRejectStages, (e) => {
				this.stagesRejectTrack($(e.target));
			});
	}

	/**
	 * Выбор/снятие выбора в чекбоксах по задач
	 */
	checkboxInprogressConfirmInTrack($checkbox) {
		let $track = $checkbox.closest('.' + this.selectors.track.block);
		let $buttons = $track.find('a');

		if ($track.find('.' + this.selectors.track.stagesCheckbox + ':checked').length) {
			$buttons.prop('disabled', false).removeClass('disabled');
		} else {
			$buttons.prop('disabled', true).addClass('disabled');
		}

		if ($checkbox.is(':checked')) {
			this.trackStagesIds.push($checkbox.val());
		} else {
			this.trackStagesIds.splice(this.trackStagesIds.indexOf($checkbox.val()), 1);
		}
	}

	/**
	 * Подтверждение задач(и) внизу страницы
	 * @param $link
	 */
	stagesApproveBottom($link) {
		let stagesIds = [];

		if (!$link.data('is-multiple')) {
			stagesIds = [$link.data('first-stage')];
		}

		this.stageInprogressConfirmModal.initOptions({
			stagesIds: stagesIds,
			type: 'approve-bottom',
			isCheckbox: $link.data('is-multiple'),
		});
		this.stageInprogressConfirmModal.initModal();
		this.stageInprogressConfirmModal.showModal();
	}

	/**
	 * Отправка на доработку внизу страницы
	 * @param $link
	 */
	stagesRejectBottom($link) {
		let stagesIds = [];
		let isCheckbox = $link.data('is-multiple');

		if (!isCheckbox) {
			stagesIds = [$link.data('first-stage')];
		}

		this.stageInprogressConfirmModal.initOptions({
			stagesIds: stagesIds,
			type: 'reject-bottom',
			isCheckbox: isCheckbox,
		});

		this.stageInprogressConfirmModal.initModal();

		if (isCheckbox) {
			this.stageInprogressConfirmModal.showModal();
		} else {
			this.stageInprogressConfirmModal.submitForm();
		}
	}

	/**
	 * Подтверждение задачи из трэка
	 * @param $link
	 */
	stagesApproveTrack($link) {
		let track = $link.closest('.track--item');
		let self = this;
		track.find('.' + this.selectors.track.stagesCheckbox).each(function () {
			self.checkboxInprogressConfirmInTrack($(this)); 
		});
		if (!this.trackStagesIds.length) {
			this.trackStagesIds = [$link.data('stage-id')];
		}

		this.stageInprogressConfirmModal.initOptions({
			stagesIds: this.trackStagesIds,
			type: 'approve-track',
		});

		this.stageInprogressConfirmModal.initModal();

		// отображаем модальное окно
		this.stageInprogressConfirmModal.showModal();
	}

	/**
	 * Отправка на доработку из трэка
	 * @param $link
	 */
	stagesRejectTrack($link) {
		// если кнопка заблокирована, то ничего не делаем
		if ($link.hasClass("disabled")) {
			return;
		}

		if (!this.trackStagesIds.length) {
			this.trackStagesIds = [$link.data('stage-id')];
		}

		// блокируем кнопки, чтобы не отправить запрос повторно
		let $track = $link.closest('.' + this.selectors.track.block);
		let $buttons = $track.find('a');
		$buttons.prop('disabled', true).addClass('disabled');

		this.stageInprogressConfirmModal.initOptions({
			stagesIds: this.trackStagesIds,
			type: 'reject-track',
		});

		this.stageInprogressConfirmModal.initModal();

		// сразу отправляем форму из модального окна
		this.stageInprogressConfirmModal.submitForm();
	}
}
