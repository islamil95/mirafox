/** @namespace categoryInfo: {object} информация по каждой категории */
/** @namespace categoryChilds: {object} подкатегориий выбраной категории */
/** @namespace attributeTree: {object} дерево атрибутов */
/** @namespace categorySelected: {string} seo текущей категории */
/** @namespace selectedAttributes: {object} id текущего атрибута */
/** @namespace sortByType: {string|null} тип текущей сортировки  */
/** @namespace sortByTime: {string|null} сортирока по времени */

class TopFilter {

	constructor() {

		this.isFilterMobileShow = false;

		this.selectors = {
			filterTop: 'js-top-filters',
			filtersContainer: 'js-top-filters-container',
			filter: 'js-top-filter-item',
			filterCategory: 'js-top-filter-item-category',
			filterSubCategory: 'js-top-filter-item-subcategory',
			filterAttribute: 'js-top-filter-item-attribute',
			filterActive: 'top-custom-select_active',
			filterItemScroll: 'js-top-select-scroll',
			filterTitle: 'js-top-filter-title',
			filterTitleLink: 'js-top-filter-title-link',
			filterListItem: 'js-top-filter-list-item',
			filterListItemAll: 'js-top-filter-list-item-all',
			filterListItemActive: 'top-custom-select__list-item_active',
			filterMobileLink: 'js-top-filters-mobile-link',
			filterMobileLinkClose: 'js-top-filters-mobile-close',
			filterMobileBlock: 'js-top-filters-mobile',

			filterSelectedItem: 'js-top-filters-selected-item',

			sortByBlock: 'js-sort-by-block',
			sortBySelect: 'js-sort-by',
			sortByTitle: 'js-sort-by-title',
			sortByTime: 'sort-type-time',
			sortByType: 'sort-type-s',
		};

		this.event();
		this.updateParamFilter();
	}

	event() {
		$(document)
		// открываем/закрываем список с фильтрами
			.on('touchstart mouseenter', '.' + this.selectors.filterTitleLink, (e) => {
				this.toggleSelectList(e);
			})
			.on('mouseleave', '.' + this.selectors.filter, (e) => {
				if (!isMobile()) {
					$('.' + this.selectors.filter).removeClass(this.selectors.filterActive);
				}
			})
			.on('touchstart click', (e) => {
				if ($(e.target).closest('.' + this.selectors.filter).length) {
					return;
				}

				$('.' + this.selectors.filter).removeClass(this.selectors.filterActive);
				e.stopPropagation();
			})
			// открываем попап фильтров для мобилки
			.on('click', '.' + this.selectors.filterMobileLink, () => {
				this.filterMobileShow();
			});

		// закрываем попап фильтров для мобилки
		$('.' + this.selectors.filterMobileLinkClose).on('click', () => {
			this.filterMobileHide();
		});

		// изменение сортировки
		$('.' + this.selectors.sortBySelect).on('change', (e) => {
			this.changeSortBy($(e.target));
			this.updateParamFilter();
			CatFilterModule.onChangeSort($(e.target).attr('name'), $(e.target).val());
		});

		// при увеличении окана попа фильтров скрывается
		$(window).resize(() => {
			if ($(window).width() > 767 && this.isFilterMobileShow) {
				this.filterMobileHide();
			}
		});

		window.onpopstate = () => {
			this.updateFilters();
			window.CatFilterModule.updateParams([]);
			window.CatFilterModule.load();
		};

		// Выбираем категорию
		$('.' + this.selectors.filterCategory)
			.off('click')
			.on('click', '.' + this.selectors.filterListItem, (e) => {
				this.selectCategory($(e.target));
			})
			.on('click', '.' + this.selectors.filterListItemAll, () => {
				this.selectCategoryAll();
			});

		// Выбираем подкатегорию
		$('.' + this.selectors.filterSubCategory)
			.off('click')
			.on('click', '.' + this.selectors.filterListItem, (e) => {
				this.selectSubCategory($(e.target));
			})
			.on('click', '.' + this.selectors.filterListItemAll, () => {
				this.selectSubCategoryAll();
			});
	}

	updateParamFilter() {
		let param = [];
		if (categorySelected !== null) {
			param['rcat'] = categorySelected;
		}

		if (selectedAttributes !== null) {
			param['attribute_id'] = selectedAttributes.join(',');
		}

		if (sortByType !== null) {
			param['s'] = sortByType;
		}

		if (sortByTime !== null) {
			param['time'] = sortByTime;
		}

		window.CatFilterModule.updateParams(param);
	}

	/**
	 * Обновление фильтров из гет-параметров
	 */
	updateFilters() {
		this.updateFilterSortFromGetParams();
		this.updateFilterCategoryFromGetParams();
		this.updateFilterAttributeFromGetParams();
	}

	/**
	 * Обновление фильтра сортировки из гет-параметров
	 */
	updateFilterSortFromGetParams() {
		let getParams = getGetParams();

		let sortType = getParams['s'] || sortByType || 'popular';
		let sortTime = getParams['time'] || sortByTime || 'week';

		let $selectSortType = $('#' + this.selectors.sortByType).find('.' + this.selectors.sortBySelect);
		$selectSortType.val(sortType);
		this.changeSortBy($selectSortType);

		if (sortType === 'popular') {
			let $selectSortTime = $('#' + this.selectors.sortByTime).find('.' + this.selectors.sortBySelect);
			$selectSortTime.val(sortTime);
			this.changeSortBy($selectSortTime);
		}
	}

	/**
	 * Обновление фильтров категорий из гет-параметров
	 */
	updateFilterCategoryFromGetParams() {
		let getParams = getGetParams();

		let category = getParams['rcat'] || categorySelected || 'all';
		let categoryId = this.getCategoryIdBySeo(category);
		let parentId = categoryInfo[categoryId] ? categoryInfo[categoryId].parent : '0';
		let $listItem;

		// если категория
		if (
			(categoryInfo[categoryId] && parentId === '0')
			|| categoryId === 0
		) {
			$listItem = $('.' + this.selectors.filterCategory).find('.' + this.selectors.filterListItem).filter('[data-id="' + categoryId + '"]');

			this.updateSubCategorySelectList(categoryId);
			this.selectSubCategoryAll();
			this.updateActiveSelectItem($listItem);
		} else {
			// если подкатегория
			$listItem = $('.' + this.selectors.filterCategory).find('.' + this.selectors.filterListItem).filter('[data-id="' + parentId + '"]');
			this.updateActiveSelectItem($listItem);
			this.updateSubCategorySelectList(parentId);

			$listItem = $('.' + this.selectors.filterSubCategory).find('.' + this.selectors.filterListItem).filter('[data-id="' + categoryId + '"]');
			this.updateActiveSelectItem($listItem);
		}
	}

	/**
	 * Обновление фильтров классификаций из гет-параметров
	 */
	updateFilterAttributeFromGetParams() {
		let getParams = getGetParams();

		let attributeIds;
		if (getParams['attribute_id']) {
			attributeIds = getParams['attribute_id'].split(',');
		}
		window.TopFiltersAttributeUpdate.setSelectedAttributes(attributeIds);
	}

	/**
	 * Получить id категрии по его seo-параметру
	 * @param categorySeo
	 * @returns {number}
	 */
	getCategoryIdBySeo(categorySeo) {
		let categoryId = 0;
		$.each(categoryInfo, (id, values) => {
			if (values.seo === categorySeo) {
				categoryId = id;
			}
		});

		return categoryId;
	}

	/**
	 * Открываем/закрываем список с фильтрами
	 * @param e
	 */
	toggleSelectList(e) {
		if (isMobile() && e.type !== "touchstart") {
			return;
		}

		//закрываем другие списки
		let customSelectAll = $(e.target).closest('.' + this.selectors.filter).siblings();
		customSelectAll.removeClass(this.selectors.filterActive);

		// убираем скролл у всех списков
		customSelectAll.each((k, v) => {
			let scrollFilter = $(v).find('.' + this.selectors.filterItemScroll);
			if (scrollFilter.find('.jspContainer').length) {
				scrollFilter.data('jsp').destroy();
			}
		});

		// Текущий селект
		let customSelect = $(e.target).closest('.' + this.selectors.filter);
		if (isMobile()) {
			customSelect.toggleClass(this.selectors.filterActive);
		} else {
			if (!customSelect.hasClass(this.selectors.filterActive)) {
				// отрабатываем текущий список
				customSelect.toggleClass(this.selectors.filterActive);

				// добавляем скролл для текущего списка
				customSelect.find('.' + this.selectors.filterItemScroll)
					.css({'overflow': 'inherit'})
					.jScrollPane();
			}
		}
	}

	/**
	 * Выбираем категорию
	 * @param $listItem
	 */
	selectCategory($listItem) {
		this.updateActiveSelectItem($listItem);
		let categoryId = $listItem.data('id');
		window.CatFilterModule.onChangeCategory(categoryInfo[categoryId] ? categoryInfo[categoryId].seo : 'all');
	}

	/**
	 * Выбираем подкатегорию
	 * @param $listItem
	 */
	selectSubCategory($listItem) {
		this.updateActiveSelectItem($listItem);
		window.TopFiltersAttributeUpdate.removeFilterAttribute();
		let categoryId = $listItem.data('id');
		let category = categoryId != 0 ? categoryInfo[categoryId].seo : 'all';

		window.CatFilterModule.onChangeCategory(category);
	}

	/**
	 * Выбираем пункт "Все категории" и скрываем остальные подселекты
	 */
	selectCategoryAll() {
		this.selectValueAll($('.' + this.selectors.filterSubCategory));
		this.selectSubCategoryAll();
	}

	/**
	 * Выбираем пункт "Все подкатегории" и скрываем остальные подселекты
	 */
	selectSubCategoryAll() {
		window.TopFiltersAttributeUpdate.removeFilterAttribute();
	}

	/**
	 * Устанавливаем значение "Все ..."
	 * @param $filter
	 */
	selectValueAll($filter) {
		let listItem = $filter.find('.' + this.selectors.filterListItem + ':not(.' + this.selectors.filterListItemAll + ')');
		$filter.find('.' + this.selectors.filterTitle).text($filter.find('.' + this.selectors.filterListItemAll).text());
		listItem.remove();
		$filter.addClass('hidden');
	}

	updateActiveSelectItem($listItem) {
		// делаем активным текущий пункт
		$listItem.closest('.' + this.selectors.filter)
			.find('.' + this.selectors.filterListItemActive).removeClass(this.selectors.filterListItemActive);
		$listItem.addClass(this.selectors.filterListItemActive);

		// скрываем список
		let filter = $listItem.closest('.' + this.selectors.filter);
		filter.removeClass(this.selectors.filterActive);

		// обновляем загловок селекта
		let filterTitle = filter.find('.' + this.selectors.filterTitle);
		filterTitle.text($listItem.text());
	}

	/**
	 * Обновить список подкатегорий
	 * @param categoryId
	 */
	updateSubCategorySelectList(categoryId) {

		let subCategory = $('.' + this.selectors.filterSubCategory);
		this.selectValueAll(subCategory);

		if (categoryId && categoryChilds[categoryId]) {
			subCategory.removeClass('hidden');

			$.each(categoryChilds[categoryId], (i, subcategoryId) => {
				let subcategory = categoryInfo[subcategoryId];

				let newListItem = '<li class="' + this.selectors.filterListItem + ' top-custom-select__list-item"' +
					' data-id="' + subcategoryId + '">' +
					subcategory.name +
					'</li>';

				subCategory.find('ul').append(newListItem);
			});

			subCategory.find('.' + this.selectors.filterListItemAll).addClass(this.selectors.filterListItemActive);
			subCategory.find('.' + this.selectors.filterListItemAll).data('id', categoryId);
			subCategory.find('.' + this.selectors.filterListItemAll).attr('data-id', categoryId);
		}
	}

	/**
	 * Отображаем фильтры в виде попапа для мобилки
	 */
	filterMobileShow() {
		this.isFilterMobileShow = true;

		$('.' + this.selectors.filterTop).addClass('popup-filter');
		changeBodyScrollbar('lock');
	}

	/**
	 * Закрытие модального окна с фильтром
	 */
	filterMobileHide() {
		this.isFilterMobileShow = false;

		$('.' + this.selectors.filterTop).removeClass('popup-filter');
		changeBodyScrollbar('unlock');
	}


	/**
	 * Изменение сортировки
	 */
	changeSortBy($select) {
		let sortByBlock = $select.closest('.' + this.selectors.sortByBlock);
		let value = sortByBlock.find('option:selected').val();
		sortByBlock.find('.' + this.selectors.sortByTitle).text(sortByBlock.find('option:selected').text());
		if (sortByBlock.attr("id") === 'sort-type-s') {
			if (value === 'new') {
				// Скрываем сортировку по времени
				$('#' + this.selectors.sortByTime).addClass('hidden');
			} else {
				// Показываем сортировку по времени
				$('#' + this.selectors.sortByTime).removeClass('hidden');
			}
		}
	}
}

class TopFiltersAttributeUpdate {

	constructor() {
		this.tree = [];
		this.selectors = {
			filtersContainer: 'js-top-filters-container',
			filter: 'js-top-filter-item',
			filterActive: 'top-custom-select_active',
			filterSubCategory: 'js-top-filter-item-subcategory',
			filterAttribute: 'js-top-filter-item-attribute',
			filterItemScroll: 'js-top-select-scroll',
			filterTitle: 'js-top-filter-title',
			filterTitleLink: 'js-top-filter-title-link',
			filterListItem: 'js-top-filter-list-item',
			filterListItemSub: 'js-top-filter-list-item-sub',
			filterListItemAll: 'js-top-filter-list-item-all',
			filterListItemActive: 'top-custom-select__list-item_active',

			filterSelected: 'js-top-filters-selected',
			filterSelectedItem: 'js-top-filters-selected-item',
		};

		this.selectedAttributesIds = [];

		this.init(attributeTree, selectedAttributes);
	}

	eventSelectAttribute() {
		// Выбираем классификацию
		$('.' + this.selectors.filterAttribute)
			.off('click')
			.on('click', '.' + this.selectors.filterListItem, (e) => {
				let attributeResult = this.getAttributeResult($(e.target));
				window.CatFilterModule.onChangeAttribute(Utils.uniqueArray(attributeResult).join(','));

				// скрываем список
				$(e.target).closest('.' + this.selectors.filter).removeClass(this.selectors.filterActive);
			});

		// Удаляем классификацию
		$('.' + this.selectors.filterSelectedItem)
			.off('click')
			.on('click', (e) => {
				this.removeFilterSelected($(e.target));
			});
	}

	init(tree, selectedAttributesIds) {
		if (tree && tree.length) {
			this.tree = tree;
			this.setSelectedAttributes(selectedAttributesIds);
		}
	}

	/**
	 * Получение массива атрибутов при клике на атрибут
	 * @param $element
	 * @returns {Array}
	 */
	getAttributeResult($element) {
		let attributeId = $element.data('id');
		let parentId = $element.closest('.' + this.selectors.filter).data('id');
		let attributeResult = [];

		if (!$element.hasClass(this.selectors.filterListItemSub)) {
			attributeResult.push(attributeId);
		} else {
			$('.' + this.selectors.filterAttribute + ' .' + this.selectors.filterListItemActive).each((k, v) => {
				let activeParentId = $(v).closest('.' + this.selectors.filter).data('id');
				if (activeParentId !== parentId) {
					attributeResult.push($(v).data('id'));
				}
			});

			attributeResult.push(attributeId);
		}

		return attributeResult;
	}

	/**
	 * Выбираем атрибут
	 * @param {Array} attributesIds
	 */
	setSelectedAttributes(attributesIds) {
		this.updateSelectedAttributesIds(attributesIds);
		this.removeFilterAttribute();
		this.findNeededClassificationsBySelected();
	}

	/**
	 * Обновляем массив selectedAttributesIds
	 * @param attributesIds
	 */
	updateSelectedAttributesIds(attributesIds) {
		this.selectedAttributesIds = [];
		if (attributesIds && attributesIds.length) {
			for (let attributeId of attributesIds) {
				let attributeObj = this.findInTreeRecursive(attributeId, this.tree);
				this.selectedAttributesIds.push({
					parent_id: attributeObj.parent_id,
					attribute_id: attributeId
				});
			}
		}
	}

	/**
	 * Удалить фильтры атрибутов
	 */
	removeFilterAttribute() {
		$('.' + this.selectors.filterAttribute).remove();
		$('.' + this.selectors.filterSelectedItem).remove();
	}

	findNeededClassificationsBySelected() {
		if (this.selectedAttributesIds && this.selectedAttributesIds.length) {
			let firstAttributeId = this.selectedAttributesIds[0].attribute_id;

			//все родители
			let parentsIds = this.findAllParentsIds(this.tree, firstAttributeId);
			// выводим первого родителя
			let firstParent = this.findInTreeRecursive(parentsIds[0], this.tree);
			if (firstParent.is_classification) {
				this.makeClassificationSelect(firstParent);
			}

			// выводим всех детей родителей текущего атрибута
			for (let parentId of parentsIds) {
				//ближайшие дети
				let childrenParent = this.findSelectedChildren(this.tree, parentId);
				for (let childParent of childrenParent) {
					if (childParent.is_classification) {
						this.makeClassificationSelect(childParent, 'sub');
					}
				}
			}

			//ближайшие дети
			let children = this.findSelectedChildren(this.tree, firstAttributeId);
			for (let child of children) {
				if (child.is_classification) {
					this.makeClassificationSelect(child, 'sub');
				}
			}
		} else {
			for (let attribute of this.tree) {
				this.makeClassificationSelect(attribute);
			}
		}

		this.eventSelectAttribute();
	}

	/**
	 * построение селекта атрибутов
	 * @param classification
	 * @param level
	 */
	makeClassificationSelect(classification, level) {
		if (classification && classification.id && classification.is_classification) {
			let selectedAttribute;

			let htmlListItems = '';
			for (let child of classification.children) {
				let selectedClass = '';

				let attributeFilter = this.selectedAttributesIds.filter((param) => {
					return param.attribute_id == child.id;
				});

				if (this.selectedAttributesIds.length && attributeFilter.length) {
					selectedClass = this.selectors.filterListItemActive;
					selectedAttribute = child;
				}
				htmlListItems += '<li class="' + this.selectors.filterListItem + ' ' +
					(level === 'sub' ? this.selectors.filterListItemSub : '') +
					' top-custom-select__list-item ' + selectedClass + '" ' +
					'data-id="' + child.id + '">' + child.title + '</li>';
			}

			let html = '<div class="' + this.selectors.filter + ' ' + this.selectors.filterAttribute + ' top-filters__item top-custom-select" ' +
				'data-id="' + classification.id + '" ' +
				'data-parent-id="' + classification.parent_id + '">' +
				'<div class="' + this.selectors.filterTitleLink + ' top-custom-select__title">' +
				'<span class="m-visible">' +
				t('Классификация') + ':' +
				'</span>' +
				'<span class="' + this.selectors.filterTitle + '">' +
				(selectedAttribute ? selectedAttribute.title : classification.title) +
				'</span>' +
				'</div>' +
				'<div class="top-custom-select__wrap">' +
				'<ul class="' + this.selectors.filterItemScroll + ' top-custom-select__list">';

			html += '<li class="' + this.selectors.filterListItem + ' ' + this.selectors.filterListItemAll + ' top-custom-select__list-item ' +
				(selectedAttribute ? '' : this.selectors.filterListItemActive) + ' ' +
				(level === 'sub' ? this.selectors.filterListItemSub : '') + '" ' +
				'data-id="' + classification.parent_id + '">' + t('Все классификации') + '</li>';

			html += htmlListItems;

			html += '</ul></div></div>';

			$('.' + this.selectors.filtersContainer).append(html);

			//добавляем выбранные атрибуты в виде кнопок под верхним фильтром
			this.topFiltersSelectedHtml();
		}
	}

	/**
	 * Добавляем выбранные атрибуты в виде кнопок под верхним фильтром
	 */
	topFiltersSelectedHtml() {
		let topFiltersSelectedHtml = '';
		let filterEl = $('.' + this.selectors.filterAttribute);

		filterEl.find('.' + this.selectors.filterListItemActive).filter(':not(.' + this.selectors.filterListItemAll + ')').each((k, v) => {
			topFiltersSelectedHtml += '<span class="' + this.selectors.filterSelectedItem + ' top-filters-selected__item" ' +
				'data-id="' + $(v).data('id') + '" ' +
				'data-parent-id="' + $(v).closest('.' + this.selectors.filterAttribute).data('id') + '">' +
				$(v).text() +
				'</span>';
		});

		$('.' + this.selectors.filterSelected).html(topFiltersSelectedHtml);
	}

	/**
	 * Удаляем кнопки выбранных атрибутов под верхним фильтром
	 */
	removeFilterSelected($element) {
		let attributeId = parseInt($element.data('id'));
		let parentId = parseInt($element.data('parent-id'));
		let parentClassification = this.findParentClassification(this.tree, attributeId);

		$('.' + parentClassification[0].class + '[data-id="' + parentId + '"] .' + this.selectors.filterListItemAll).trigger('click');
	}

	/**
	 * Поиск атрибута в дереве по идентификатору
	 *
	 * @param attributeId
	 * @param tree
	 * @returns {*}
	 */
	findInTreeRecursive(attributeId, tree) {
		for (let attribute of tree) {
			if (attribute.id === parseInt(attributeId)) {
				return attribute;

				//получаем ВСЕХ потомков


			} else if (attribute.children.length) {
				let finded = this.findInTreeRecursive(attributeId, attribute.children);
				if (finded) {
					return finded;
				}
			}
		}
	}

	/**
	 * Поиск всех родителей
	 * @param tree
	 * @param attributeId
	 * @returns {Array}
	 */
	findAllParentsIds(tree, attributeId) {
		for (let attribute of tree) {
			if (attribute.id === parseInt(attributeId)) {
				if (attribute.parent_id) {
					return [attribute.parent_id];
				} else {
					return [];
				}
			} else if (attribute.children.length) {
				let parents = this.findAllParentsIds(attribute.children, attributeId);
				if (parents.length) {
					if (attribute.parent_id) {
						parents.unshift(attribute.parent_id);
					}
					return parents;
				}
			}
		}

		return [];
	}

	/**
	 * Поиск ближайших детей
	 * @param tree
	 * @param attributeId
	 * @returns {Array}
	 */
	findSelectedChildren(tree, attributeId) {
		for (let attribute of tree) {
			if (attribute.id === parseInt(attributeId)) {
				if (attribute.children.length) {
					return attribute.children
				} else {
					return [];
				}
			} else if (attribute.children.length) {
				let children = this.findSelectedChildren(attribute.children, attributeId);
				if (children.length) {
					if (attribute.id === parseInt(attributeId)) {
						children.push(attribute);
					}
					return children;
				}
			}
		}

		return [];
	}

	/**
	 * поиск ближайшей родительской классификации
	 * @param tree
	 * @param attributeId
	 * @returns {Array|{length}|*|{id: number, class: string}[]
	 */
	findParentClassification(tree, attributeId) {
		for (let attribute of tree) {
			if (attribute.id === parseInt(attributeId)) {
				if (attribute.parent_id) {
					return [{
						id: attribute.parent_id,
						class: this.selectors.filterAttribute
					}];
				} else {
					return [{
						id: attribute.category_id,
						class: this.selectors.filterSubCategory
					}];
				}
			} else if (attribute.children.length) {
				let parents = this.findParentClassification(attribute.children, attributeId);
				if (parents.length) {
					if (attribute.parent_id && attribute.id === parseInt(attributeId)) {
						parents.push(attribute.parent_id);
					}
					return parents;
				}
			}
		}

		return [];
	}
}

window.TopFilter = new TopFilter();
window.TopFiltersAttributeUpdate = new TopFiltersAttributeUpdate();
