var Bookmarks = (function ($) {
	var _selectors = {
			filterSelect: '.js-bookmarks-category',
			linkLoadKworks: '.js-link-load-kworks',
			containerKworks: '.js-bookmark-kworks',
			filterCategoryLink: '.js-bookmarks-category-link',
		},
		
		_init = function () {
			_loadHistory();
				
			$(document)
				.on('change', _selectors.filterSelect, function () {
					_loadKworks($(this).val());
				})
				.on('click', _selectors.linkLoadKworks, function () {
					loadKworks();
				})
				.on('click', _selectors.filterCategoryLink, function () {
					_loadKworks($(this).data('href'))
				});

			$(window).scroll(_scrollWindow);
		},

		_scrollWindow = function () {
			if (
				(
					!window.matchMedia("(max-width:767px)").matches
					&& !isMobile()
				)
				|| $(_selectors.linkLoadKworks).hasClass('hidden')
				|| $(_selectors.linkLoadKworks).hasClass('onload')
			) {
				return false;
			}
			
			if (
				(($(window).scrollTop() + $(window).height()) + 250) >= $(document).height()
			) {
				loadKworks();
			}
		},

		_setHistory = function (url, response) {
			
			window.history.pushState({
				url: url,
				response: response,
			}, "", $(_selectors.filterSelect).val());
		},

		_loadHistory = function () {

			window.onpopstate = function (event) {
				console.log(event);
				if (event.state) {
					_showKworks(event.state.response);
					_updateFilter(event.state.url);
				}
			};
		},

		_loadKworks = function (url) {
			$(_selectors.containerKworks).html('');
			$(_selectors.containerKworks).preloader("show");
			$(_selectors.linkLoadKworks).addClass('hidden');

			_updateFilter(url);

			$.ajax({
				url: url,
				type: 'post',
				data: {},
				dataType: 'json',
				success: function (response) {
					_setHistory(url, response);
					_showKworks(response);
				}
			});
		},
		
		_showKworks = function (response) {
			if (typeof response === "undefined") {
				location.reload();
				return;
			}

			if(response.paging.page * response.paging.items_per_page < response.paging.total)
			{
				$(_selectors.linkLoadKworks).removeClass('hidden');
			}
			
			$(_selectors.containerKworks).html(response.html);
		},
		
		_updateFilter = function (dataHref) {
			$(_selectors.filterCategoryLink).removeClass('link-color');
			$(_selectors.filterCategoryLink).filter("[data-href='" + dataHref + "']").addClass('link-color');
			
			$(_selectors.filterSelect).val(dataHref);
		};

	return {
		init: _init
	}
})(jQuery);

Bookmarks.init();
