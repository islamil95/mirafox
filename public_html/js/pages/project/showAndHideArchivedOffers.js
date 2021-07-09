export class ShowAndHideArchivedOffers {

	constructor() {


		this.selectors = {
			link: 'js-offer-item-archive-link',
			linkActive: 'offers__archived-link--active',
			block: 'js-offer-item-archive-block',
			showArchivedClass: 'offers__archived--show',
		};

		this.events();
	}

	events() {
		$(document).on('click', '.' + this.selectors.link, () => {
			if ($('.' + this.selectors.block).hasClass(this.selectors.showArchivedClass)) {
				this.hideArchivedBlock();
			} else {
				this.showArchivedBlock();
			}
		});
	}

	/**
	 * Показать архивные предложения
	 */
	showArchivedBlock() {
		$('.' + this.selectors.block).addClass(this.selectors.showArchivedClass);
		$('.' + this.selectors.link)
			.addClass(this.selectors.linkActive)
			.text(t('Скрыть архивные предложения'));
	}

	/**
	 * Скрыть архивные предложения
	 */
	hideArchivedBlock() {
		$('.' + this.selectors.block).removeClass(this.selectors.showArchivedClass);
		$('.' + this.selectors.link)
			.removeClass(this.selectors.linkActive)
			.text(t('Показать архивные предложения'));
	}
}

window.ShowAndHideArchivedOffers = new ShowAndHideArchivedOffers();
