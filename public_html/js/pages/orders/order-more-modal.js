class OrderMoreModal {

	constructor() {

		this.selectors = {
			orderMoreLink: 'js-order-more-link',
			orderMoreModal: 'js-order-more-modal',
			orderMoreRadio: 'js-order-more-radio',
			orderMoreSubmit: 'js-order-more-submit',
		};

		/**
		 * "Продолжить работу в этом заказе"
		 * @type {string}
		 */
		this.TYPE_CURRENT = 'current';
		/**
		 * "Сделать повторный заказ"
		 * @type {string}
		 */
		this.TYPE_NEW = 'new';

		this.typeRadio = this.TYPE_CURRENT;
		this.orderId = 0;
		this.kworkId = 0;
		this.packageType = 0;
		this.isQuick = 0;

		this.events();
	}

	events() {

		$(document)
			.on('click', '.' + this.selectors.orderMoreLink, e => this.showModal(e))
			.on('click', '.' + this.selectors.orderMoreRadio, (e) => {
				this.typeRadio = $(e.target).data('type');

				this.unlockButton();
			})
			.on('click', '.' + this.selectors.orderMoreSubmit, () =>this.submitModal());

		// Событие на закрытие модального окна
		$('.' + this.selectors.orderMoreModal).on('hide.bs.modal', () => this.defaultModal());
	}

	/**
	 * Отображаем модальное окно
	 */
	showModal(e) {
		this.defaultModal();

		this.samePage = $(e.target).data('same-page');
		this.orderId = $(e.target).data('order-id');
		this.kworkId = $(e.target).data('kwork-id');
		this.packageType = $(e.target).data('package');
		this.isQuick = !!$(e.target).data('quick');

		$('.' + this.selectors.orderMoreModal).modal('show');
	}

	/**
	 * Заказываем повторно заказ
	 */
	submitModal() {
		if (this.typeRadio === this.TYPE_CURRENT) {
			if (this.samePage) {
				$('.' + this.selectors.orderMoreModal).modal('hide');
				$('.js-track-stage-add-link').click();
			} else {
				// открывается страница заказа и функционал для добавления и оплаты задачи
				document.location.href = base_url + '/track?id=' + this.orderId + '&modal=new_stage';
			}
		} else {
			// стандартная повторная покупка заказа
			orderCreate({
				kworkId: this.kworkId,
				packageType: this.packageType,
				stagesType: 0,
				isQuick: this.isQuick,
				button: $('.' + this.selectors.orderMoreSubmit),
			});
		}
	}

	/**
	 * Заблокировать кнопку
	 */
	lockButton() {
		$('.' + this.selectors.orderMoreSubmit)
			.attr('disabled', 'disabled')
			.addClass('disabled');
	}

	/**
	 * Разблокировать кнопку
	 */
	unlockButton() {
		$('.' + this.selectors.orderMoreSubmit)
			.removeAttr('disabled')
			.removeClass('disabled');
	}

	/**
	 * Привести модальное окно к дефолту
	 */
	defaultModal() {
		this.lockButton();
		$('.' + this.selectors.orderMoreRadio).prop('checked', false);

		this.typeRadio = this.TYPE_CURRENT;
		this.orderId = 0;
		this.kworkId = 0;
		this.packageType = 0;
		this.isQuick = 0;
	}
}

window.OrderMoreModal = new OrderMoreModal();
