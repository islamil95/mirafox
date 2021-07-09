import { PortfolioModal } from 'appJs/portfolio-upload/portfolio-modal.js';
import { Portfolio, Image } from 'appJs/portfolio-upload/sortable-card.js';

class AjaxPortfolioModal {
	constructor() {
		this.body = $('body');

		this.modal = new PortfolioModal();
		this.modal.onSave = (item) => {
			this.savePortfolio(this.modal.portfolioItem);
		};
		this.modal.getImagesHashes = (hash) => {
			return this.getImagesHashes(hash);
		};
		this.modal.getCoverHashes = (hash) => {
			return this.getCoverHashes(hash);
		};
		this.modal.checkCoverHash = (hash) => {
			return this.checkCoverHash(hash);
		};
		this.modal.checkImageHash = (hash) => {
			return this.checkImageHash(hash);
		};
		this.modal.checkVideoUrl = (url, pos) => {
			return this.checkVideoUrl(url, pos);
		};
		this.events();
	}

	events() {
		// Редактирование портфолио со страницы "Мои работы"
		this.body.on('click', '.portfolio-card .js-edit-portfolio, .portfolio-card-collage .js-edit-portfolio', (e) => {
			let portfolioId = $(e.target).closest('.portfolio-card, .portfolio-card-collage').data('id');
			this.openPortfolioModal(portfolioId);
		});

		// Удаление портфолио со страницы "Мои работы"
		this.body.on('click', '.portfolio-card .js-delete-portfolio, .portfolio-card-collage .js-delete-portfolio', (e) => {
			let portfolioId = $(e.target).closest('.portfolio-card, .portfolio-card-collage').data('id');
			this.deletePortfolioCardModal(e.target, portfolioId);
		});
		this.body.on('click', '.portfolio-card-delete-modal .js-portfolio-card-delete-cancel', (e) => {
			this.deletePortfolioCardModalClose();
		});
		this.body.on('click', '.js-portfolio-card-delete-confirm', (e) => {
			this.deletePortfolioCardModalConfirm();
		});

		// Редактирование из окна просмотра портфолио
		this.body.on('click', '.portfolio-large .js-edit-portfolio', (e) => {
			portfolioCard.close();

			setTimeout(() => {
				let portfolioId = $(e.target).closest('.portfolio-large').data('portfolioId');
				this.openPortfolioModal(portfolioId);
			}, 100);
		});

		this.body.on('click', '.js-new-portfolio', (e) => {
			this.openPortfolioModal(null);
		});
	}

	openPortfolioModal(portfolioId) {
		this.getPortfolioData(portfolioId, (data) => {
			let portfolio = new Portfolio(data.portfolio);
			this.modal.edit(portfolio, data.additional);
		});
	}

	deletePortfolioCardModal(el, portfolioId) {
		this.startLoader();
		$.ajax({
			type: 'GET',
			url: '/portfolio/can_delete/' + portfolioId,
			dataType: "json",
			success: (response) => {
				this.stopLoader();

				if (response.success) {
					let deleteModal;

					if (response.data.canDeletePortfolio) {
						//работу удалить можно
						deleteModal = $('.js-portfolio-card-can-delete-modal');
						deleteModal.find('.js-portfolio-card-delete-confirm').attr('data-id', portfolioId);
					} else {
						//работу удалить нельзя, т.к. не будет нужного количества работ в портфолио
						deleteModal = $('.js-portfolio-card-cant-delete-modal');

						let deleteModalText = '';
						let kworkName = $(el).data('name');
						let kworkUrl = $(el).data('url');

						let neededPortfolioCount = response.data.neededPortfolioCount ? response.data.neededPortfolioCount : '';
						if (neededPortfolioCount) {
							neededPortfolioCount = ' (' + t('минимум') + ' ' + neededPortfolioCount + ')';
						}

						if (kworkName && kworkName != '') {
							deleteModalText += '<p>' + t('Удалить работу нельзя, поскольку в кворке «{{0}}» не будет нужного количества работ в портфолио', [kworkName]) + neededPortfolioCount + '.</p>';
						} else {
							deleteModalText += '<p>' + t('Удалить работу нельзя, поскольку в кворке не будет нужного количества работ в портфолио') + neededPortfolioCount + '.</p>';
						}
						deleteModalText += '<p class="mt10">' + t('<a href="{{0}}">Добавьте в кворк</a> новую работу, чтобы удалить данную.', [kworkUrl]) + '</p>';

						deleteModal.find('.modal-body').html(deleteModalText);
					}

					deleteModal.modal('show');
				} else {
					// TODO view error
				}
			}
		});
	}

	deletePortfolioCardModalClose() {
		$('.portfolio-card-delete-modal').modal('hide');
	}

	deletePortfolioCardModalConfirm() {
		if (window.portfolioList.modal.page === 'my-portfolios') {
			let formData = new FormData(),
				portfolioId = $('.js-portfolio-card-delete-confirm').attr('data-id');

			formData.append('portfolio_id', portfolioId);
			//formData.append('unlink', 'true');
			$.ajax({
				url: '/portfolio/delete',
				data: formData,
				async: true,
				contentType: false,
				processData: false,
				type: 'POST',
				complete: (jXhr, status) => {
					var rj = {};
					try {
						rj = JSON.parse(jXhr.responseText);
					} catch(e) {}
					if ('success' in rj && rj.success === true) {
						$('.js-portfolio-card[data-id="'+ portfolioId +'"]').remove();
						$('.header_top').append('<div class="fox_success" style="display:none"><div class="text-center"><p>Работа успешно удалена</p></div></div>');
						$('.fox_success').slideDown(300);
						setTimeout(function(){ // показываем блок успешного удаления на 3 секунды
							$('.fox_success').slideUp(300);
							setTimeout(function(){ // удаляем его сразу по автоматическому закрытию
								$('.fox_success').remove();
							}, 400);
						}, 2000);
					}
				}
			});
		}

		this.deletePortfolioCardModalClose();
	}

	checkCoverHash(hash) {
		let hashes = this.getCoverHashes();
		return ($.inArray(hash, hashes) == -1);
	}

	checkImageHash(hash) {
		let hashes = this.getImagesHashes();
		return ($.inArray(hash, hashes) == -1);
	}

	checkVideoUrl(url, pos) {
		let urls = this.getVideoUrls(pos);
		return ($.inArray(url, urls) == -1);
	}

	getCoverHashes() {
		return this.modal.additionalData.coverHashes || [];
	}

	getImagesHashes() {
		let hashes = [];
		$.each(this.modal.portfolio.images, (k, v) => {
			hashes.push(v.hash);
		});
		hashes = hashes.concat(this.modal.additionalData.imagesHashes || []);
		return hashes;
	}

	getVideoUrls(pos) {
		let urls = [];
		$.each(this.modal.portfolio.videos, (k, v) => {
			if(k == pos) {
				return true;
			}
			urls.push(v);
		});
		urls = urls.concat(this.modal.additionalData.anotherVideos || []);
		return urls;
	}

	savePortfolio(portfolioItem) {
		return this.modal.coverHashes;
	}
	
	/**
	 * Подтянуть данные по портфолио
	 * 
	 * @param {*} portfolioId Id портфолио (null - будет как новый)
	 * @param {*} successCallback Коллбеэк при успехе, возвращает данные
	 */
	getPortfolioData(portfolioId = null, successCallback) {
		this.startLoader();
		$.ajax({
			type: "POST",
			url: '/portfolio/get_popup',
			data: {
				portfolioId: portfolioId
			},
			dataType: "json",
			success: (response) => {
				this.stopLoader();

				if (response.success) {
					if (successCallback) {
						successCallback(response.data);
					}
				} else {
					// TODO view error
				}
			}
		});
	}

	startLoader() {
		lockBodyForPopup();
		this.body.append(
			'<div class="portfolio-loader-wrapper">'
				+ ' <div class="portfolio-loader portfolio-loader-white">'
					+ '<div class="ispinner ispinner--gray ispinner--animating ispinner--large">'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
					+ '</div>'
				+ '</div>'
			+'</div>'
		);
	}

	stopLoader() {
		unlockBodyForPopup();
		this.body.find('.portfolio-loader-wrapper').remove();
	}

}

$(document).ready(() => {
	window.portfolioList = new AjaxPortfolioModal();
});