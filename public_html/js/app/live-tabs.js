let LiveTabsModule = (function() {
	'use strict';

	let $tabs = $('.js-live-tabs');
	let _item = 'live-tabs__item';
	let _itemMore = 'live-tabs__item--more';
	let _itemActive = 'live-tabs__item--active';
	let _itemNotActive = 'js-live-tabs-item-not-active';
	let _subMenu = 'live-tabs__item--sub-menu';
	let _tabPrev = 'live-tabs__prev';
	let _tabNext = 'live-tabs__next';
	let _allTabsWidth = 0;
	let _timeoutResizeCalculation = 500;

	let classes = {
		_item: '.' + _item,
		_itemMore: '.' + _itemMore,
		_itemActive: '.' + _itemActive,
		_subMenu: '.' + _subMenu,
		_tabPrev: '.' + _tabPrev,
		_tabNext: '.' + _tabNext,
	};

	/**
	 * Ширина всех табов
	 * @returns {*|string}
	 * @private
	 */
	let _getContentWidth = function() {
		return $tabs.width() || '';
	};

	/**
	 * Удалить таб "Еще"
	 * @private
	 */
	let _removeShowMore = function() {
		$tabs.find(classes._itemMore).remove();
	};

	/**
	 * Добавить таб "Еще"
	 * @private
	 */
	let _addShowMore = function() {
		let $itemMore = $tabs.find(classes._itemMore);

		if ($itemMore.length === 0) {
			$tabs.append(
				'<div class="' + _item + ' ' + _itemMore + '" style="display: none;">'
				+ '<a href="javascript: void(0);">'
				+ '<span class="live-tabs__item-title">' + t('Ещё') + '</span>'
				+ '</a>'
				+ '<div class="' + _subMenu + '"></div>'
				+ '</div>'
			);
		}
	};

	/**
	 * Подсчитать ширину табов
	 * @private
	 */
	let _initTabSize = function() {
		let marginLeft = 5;

		$tabs.find('> ' + classes._item + ':not(' + classes._itemMore + ')').each(function(i) {
			let $clone = $(this).clone().css('visibility', 'hidden').show().appendTo($tabs);
			let tabWidth = Math.ceil($clone[0].getBoundingClientRect().width);

			if (i > 0) {
				tabWidth += marginLeft;
			}
			_allTabsWidth += tabWidth;

			$(this).attr('data-width', tabWidth);
			$clone.remove();
		});
	};

	/**
	 * Подсичтываем отображение табов
	 * @private
	 */
	let _tabDisplayCalculation = function() {
		_initTabSize();
		if (isMobile()) {
			$tabs.find(classes._item).show();
			_removeShowMore();

			return;
		} else {
			_addShowMore();
		}

		let $showMore = $tabs.find(classes._itemMore);
		let showMoreWidth = $showMore.outerWidth(true);

		let contentWidth = _getContentWidth();
		let contentWidthShort = contentWidth - showMoreWidth;

		let visibleTabsWidth = 0;
		$tabs.find('> ' + classes._item + ':not(' + classes._itemMore + ')').each(function() {
			let tabWidth = $(this).attr('data-width') || 0;

			visibleTabsWidth += parseInt(tabWidth);

			let widthForComparison = (_allTabsWidth > contentWidth) ? contentWidthShort : contentWidth;
			if (visibleTabsWidth > widthForComparison) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});

		let countHiddenItems = $tabs.find('> ' + classes._item + ':hidden:not(' + classes._itemMore + ')').length;
		if (countHiddenItems === 0) {
			$showMore.hide();
		} else {
			$showMore.show();
		}

		$tabs.css({overflow: 'initial'});
	};

	/**
	 * Делаем активным таб
	 * @private
	 */
	let _activeItem = function() {
		if ($(this).hasClass(_itemActive) || $(this).hasClass(_itemMore) || $(this).hasClass(_itemNotActive)) {
			return;
		}

		$(classes._item).removeClass(_itemActive);
		$(this).addClass(_itemActive);

		_activeItemHidden($(this).data('category-id'));
		_activeItemMore();
	};

	/**
	 * Если ативен таб в "Еще", то таб "Еще" выделяем активным
	 */
	let _activeItemMore = function() {
		let subItem = $(classes._item + ':hidden:not(' + classes._itemMore + ')');
		if (subItem.hasClass(_itemActive)) {
			$(classes._itemMore).addClass(_itemActive);
		}
	};

	let _activeItemHidden = function(categoryId) {
		$(classes._item).filter('[data-category-id="' + categoryId + '"]').addClass(_itemActive);
	};

	/**
	 * Прокрутка таба влево для мобильной версии
	 */
	let _prevLiveTabs = function() {
		let widthTabs = $tabs.width();
		let currentScroll = $tabs.scrollLeft();

		$tabs.animate({scrollLeft: currentScroll - widthTabs}, 800);
	};

	/**
	 * Прокрутка таба вправо для мобильной версии
	 */
	let _nextLiveTabs = function() {
		let widthTabs = $tabs.width();
		let currentScroll = $tabs.scrollLeft();

		$tabs.animate({scrollLeft: currentScroll + widthTabs}, 800);
	};

	let _init = function() {
		_tabDisplayCalculation();

		$('body')
			.on('mouseenter', classes._itemMore, function() {
				$(classes._subMenu).html('');
				$(classes._item + ':hidden:not(' + classes._itemMore + ')')
					.clone()
					.show()
					.appendTo(classes._subMenu);
			})
			.on('click', classes._item, _activeItem)
			.on('click', classes._tabPrev, _prevLiveTabs)
			.on('click', classes._tabNext, _nextLiveTabs);

		$(window).resize(function() {
			$tabs.css({overflow: 'hidden'});

			if (window.resizeTabDisplayCalculation) {
				clearTimeout(window.resizeTabDisplayCalculation);
			}
			window.resizeTabDisplayCalculation = setTimeout(function() {
				_tabDisplayCalculation();
			}, _timeoutResizeCalculation);
		});

		// Прокрутка меню к активному элементу (мобильная версия)
		if ($tabs.find(classes._itemActive).length) {
			$tabs.animate({
				scrollLeft: $tabs.find(classes._itemActive).offset().left - 40 || 0
			}, 0);
		}

		_activeItemMore();
	}();
});

new LiveTabsModule();

