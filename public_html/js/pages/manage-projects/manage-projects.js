$(document).ready(() => {
	$('.rollable-name').each((k, v) => {
		let el = $(v);
		let fullContentBlock = el.find('.wish_name');
		let innerContentBlock = fullContentBlock.find('div');
		let showMoreBlock = el.find('.wish-name-more');
		let files = el.siblings('.files-list');
		let updateRollableMode = () => {
			let wasRolled = false;
			if (el.hasClass('rolled')) {
				wasRolled = true;
				el.removeClass('rolled');
				files.addClass('hidden');
			}
			if (innerContentBlock.height() > fullContentBlock.height()) {
				el.addClass('rollable');
				files.addClass('hidden');
			} else {
				el.removeClass('rollable');
				files.removeClass('hidden');
			}
			if (wasRolled) {
				el.addClass('rolled');
				files.removeClass('hidden');
			}
		}
		let toggleMore = () => {
			if (!el.hasClass('rolled')) {
				el.addClass('rolled');
				files.removeClass('hidden');
			} else {
				el.removeClass('rolled');
				files.addClass('hidden');
				let headerSize = $('body > .header').outerHeight();
				let nameOffset = el.parent().find('.wants-card__header-title').offset().top;
				let windowScroll = $(window).scrollTop();
				if (nameOffset < windowScroll + headerSize) {
					$(window).scrollTop(nameOffset - headerSize - 15);
				}
			}
		}
		fullContentBlock.on('click', () => {
			if (el.hasClass('rolled')) {
				return;
			}
			toggleMore();
		});
		showMoreBlock.on('click', () => {
			toggleMore();
		});
		$(window).resize(() => {
			updateRollableMode();
		});
		updateRollableMode();
	});

	/**
	 * Показать/скрыть архивные проекты
	 */
	$(".js-archive-view").on("click", function(){
		let $this = $(this);
		$this.toggleClass("green-btn");
		$(".project_card.project-card--archive").toggleClass("hidden");
		$(".project_card_reason.project-card--archive").toggleClass("hidden");
	});

	$('.js-how-get-result-block-link').on('click', () => {
		$('.js-how-get-result-block-link').toggleClass('how-get-result-block__title--active');
		$('.js-js-how-get-result-block-content').toggleClass('how-get-result-block__content--active');
	});
});
