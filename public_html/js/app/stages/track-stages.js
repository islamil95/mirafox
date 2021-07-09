import {StageProgressModal} from 'appJs/stages/track/stage-progress-modal.js';
import {StagesInprogressCheck} from 'appJs/stages/track/stages-inprogress-check.js';
import {StageInprogressConfirmLinks} from 'appJs/stages/track/stage-inprogress-confirm/stage-inprogress-confirm-links.js';
import { StagesEdit } from 'appJs/stages/track/stages-edit.js';

export class TrackStages {

	constructor() {

		/**
		 * @type {{linkChangeProgress: string}} Ссылка изменения прогресса в таблице задач
		 */
		this.classes = {
			trackStages: 'js-track-stages',
			trackStage: 'js-track-stage',
			linkChangeProgress: 'js-track-stage-change-progress-link',
			reserveStageForm: 'js-reserve-stage-form',
			reserveStageLink: 'js-reserve-stage-link',

			// показать еще
			more: {
				link: 'js-not-reserved-stages-link',
				linkActive: 'track-stages__not-reserved-stages-link--active',
				block: 'js-not-reserved-stages-more',
				blockActive: 'track-stages__not-reserved-stages-more--active',
			},

			stageHighlight: 'track-stage--highlight',
			stageChanged: 'track-stage--changed',
			stageChangedHide: 'track-stage--hide',
			stageChangedNew: 'js-track-changed-new',
			stageChangedDelete: 'js-track-stage-deleted',
			stageChangedHideStatus: 'track-stage__changed--hide',
		};

		this.stageProgressModal = new StageProgressModal();
		this.stagesInprogressCheck = new StagesInprogressCheck();
		this.stageInprogressConfirmLinks = new StageInprogressConfirmLinks();
		this.stagesEdit = new StagesEdit();

		this.events();

		let readTimeout;
		let postSetUnread;
	}

	events() {

		$(document)
			.on('click', '.' + this.classes.linkChangeProgress, (e) => {
				let stageId = $(e.target).parents('.' + this.classes.trackStage).data('stage-id');

				this.stageProgressModal.initModal(stageId);
				this.stageProgressModal.showModal();
			})
			.on('click', '.js-group-stage-link', () => {
				$('.js-group-stage-link').toggleClass('track-stages__group-link--active');
				$('.js-group-stage-block').toggleClass('track-stages__group-block--active');
			})
			.on('click', '.' + this.classes.more.link, () => {
				$('.' + this.classes.more.block).toggleClass(this.classes.more.blockActive);
				$('.' + this.classes.more.link).toggleClass(this.classes.more.linkActive);
			})
			// зарезервирование средств задачи
			.on('click', '.' + this.classes.reserveStageLink, (e) => {
				$(e.target).closest('.' + this.classes.reserveStageForm).submit();
			});

		$(document).ready(() => {
			// при get параметре modal=new_stage показывать модальное окно добавления задачи
			let modal = new RegExp('[\?&]modal=([^&#]*)').exec(window.location.href);
			if (modal && modal[1] === 'new_stage') {
				$('.js-track-stage-add-link').click();
			}

			this.updateChangedStages();
		});

		$(window).bind("scroll focus", () => {
			this.updateChangedStages();
		});
	}

	/**
	 * Расскрыть блок с предстоящими этапами "показать еще"
	 */
	showMoreStages() {
		$('.' + this.classes.more.block).addClass(this.classes.more.blockActive);
		$('.' + this.classes.more.link).addClass(this.classes.more.linkActive);
	}

	/**
	 * Обновляем статус измененных этапов для продавца
	 */
	updateChangedStages() {
		let $trackStage = $('.' + this.classes.trackStage);
		let $trackStages = $('.' + this.classes.trackStages);
		let $trackStagesGroupNotReserved = $('.js-track-stages-group-not-reserved');

		let orderId = $trackStages.data('order-id');

		// если есть непрочитаннаые изменения этапов
		// и окно активно
		// и этапы находят в рамках видимости
		if (
			$trackStage.hasClass(this.classes.stageChanged) &&
			Utils.isActiveWindow() &&
			$trackStagesGroupNotReserved.is(':within-viewport-top')
		) {
			// отображаем скрытые этапы
			this.showMoreStages();

			this.readTimeout = setTimeout(() => {
				if (this.postSetUnread) {
					this.postSetUnread.abort();
				}

				this.postSetUnread = $.post('/order_stages/set_unread/' + orderId, {}, (response) => {
					$trackStage.removeClass(this.classes.stageChanged); // убираем статус "непрочтен"
					$trackStage.removeClass(this.classes.stageHighlight); // убираем выделение
					$trackStage.filter('.' + this.classes.stageChangedNew).find('.track-stage__changed').addClass(this.classes.stageChangedHideStatus); // скрываем статус "новый"
					$trackStage.filter('.' + this.classes.stageChangedDelete).addClass(this.classes.stageChangedHide); // скрываем удаленный этап

					if ($trackStagesGroupNotReserved.hasClass('js-track-stages-group-not-reserved-hide')) {
						setTimeout(() => {
							$trackStagesGroupNotReserved.hide();
						}, 900);
					}

					if ($trackStage.filter('.' + this.classes.stageChangedNew).find('.track-stage__changed').hasClass(this.classes.stageChangedHideStatus)) {
						setTimeout(() => {
							$trackStage.filter('.' + this.classes.stageChangedNew).find('.track-stage__changed').hide();
						}, 500);
					}
				});
			}, 3000);
		} else {
			clearTimeout(this.readTimeout);

			if (this.postSetUnread) {
				this.postSetUnread.abort();
			}
		}
	}
}

window.TrackStages = new TrackStages();
