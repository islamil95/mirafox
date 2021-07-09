/**
 * Текстовой/числовой объем кворка
 * Требует VolumeTypesModule
 *
 * @type {{init, setToggle, setRequiredVolumeTypeId, setAllowedAdditionalVolumeTypesIds}}
 */
function VolumeTypeInputModule (options) {

	var _initialVolumeTypeId;
	var _volumeTypeInputId = "step2-volume-type-id";
	var _volumeTypeInputName = "volume_type_id";
	var _selectors = {
		volumeSizeInput: '#step2-volume',
		volumeTypeBlock: '#js-volume-type-block',
		volumeWrapBlock: '#volume-block',
		packageVolumeInput: 'input.package_volume',
		textVolumeWrap: '#service-size-block',
		textVolumeInput: '#step2-service-size',
		textVolumeEditor: 'div.input-service-size',
		cardMinVolume: '.card__min-volume',
	};

	/**
	 * Последний выбранный тип числового объема кворка
	 */
	var _lastSelectedVolumeType;

	/**
	 * Идентификатор необходимого типа числового объем
	 */
	var _requiredVolumeTypeId;

	/**
	 * Сбрасывать ли объем
	 *
	 * @type {boolean}
	 * @private
	 */
	var _dropVolume = false;

	/**
	 * Идентификаторы дополнительных допустимых числовых типов объема
	 */
	var _allowedAdditionalVolumeTypesIds = [];

	/**
	 * Переключение в режим заполнения числового объема
	 * @private
	 */
	var _enableVolume = function (volumeTypeId, additionalVolumeTypesIds) {
		var volumeType = VolumeTypesModule.getVolumeType(volumeTypeId);
		if (volumeType === undefined) {
			return;
		}

		var additionalVolumeTypes = [];
		if (additionalVolumeTypesIds !== undefined && additionalVolumeTypesIds.length) {
			for (var i = 0; i < additionalVolumeTypesIds.length; i++) {
				var additionalVolumeType = VolumeTypesModule.getVolumeType(additionalVolumeTypesIds[i]);
				if (additionalVolumeType !== undefined) {
					additionalVolumeTypes.push(additionalVolumeType);
				}
			}
		}

		// Если нет предыдущего выбранного типа - берем из кворка
		if (!_lastSelectedVolumeType && _initialVolumeTypeId) {
			_lastSelectedVolumeType = parseInt(_initialVolumeTypeId);
		}

		var prevSelectValue = $('#' + _volumeTypeInputId).val();
		// Если есть селект предыщий то берем из селекта
		if (prevSelectValue) {
			_lastSelectedVolumeType = parseInt(prevSelectValue);
		}

		//Сбрасываем числовой объем, если был изменен тип объема
		var clear = _dropVolume;
		if (_lastSelectedVolumeType) {
			if (additionalVolumeTypes.length) {
				var found = false;
				//Не забываем проверить совпадение типа объема с базовым.
				if(_requiredVolumeTypeId === _lastSelectedVolumeType){
					found = true;
				}else{
					for (var i = 0; i < additionalVolumeTypes.length; i++) {
						if (_lastSelectedVolumeType === additionalVolumeTypes[i].id) {
							found = true;
							break;
						}
					}
				}

				if (!found) {
					clear = true;
				}
			} else {
				if (_lastSelectedVolumeType !== volumeType.id) {
					clear = true;
				}
			}
		}

		if (clear) {
			$(_selectors.volumeSizeInput).val("");
			$(_selectors.textVolumeInput).val("");
			$(_selectors.packageVolumeInput).val("");
		}

		var html = "";
		var volume = $(_selectors.volumeSizeInput).val();
		if (additionalVolumeTypes.length) {
			var selected = "";
			if (parseInt(_lastSelectedVolumeType) === volumeType.id) {
				selected = 'selected="selected"';
			}

			html = '<select id="' + _volumeTypeInputId + '" class="input input_size_s js-field-select w80i" name="' + _volumeTypeInputName + '">' +
				'            <option data-volume-type-id="' + volumeType.id + '" value="' + volumeType.id + '" ' + selected + '>' + volumeType.name_short + '</option>';
			for (var i = 0; i < additionalVolumeTypes.length; i++) {
				selected = "";
				if (parseInt(_lastSelectedVolumeType) === additionalVolumeTypes[i].id) {
					selected = 'selected="selected"';
				}
				html += '<option data-volume-type-id="' + volumeType.id + '" value="' + additionalVolumeTypes[i].id + '" ' + selected + '>' + additionalVolumeTypes[i].name_short + '</option>';
			}
			html += '</select>';
		} else {
			html = '<div data-volume-type-id="' + volumeType.id + '" class="volume-type-name">' + VolumeTypesModule.pluralVolumeById(volumeType.id, volume) + '</div>' +
				'<input type="hidden" id="'+_volumeTypeInputId+'" name="' + _volumeTypeInputName + '" value="' + volumeType.id + '">';
		}

		$(_selectors.volumeTypeBlock).html(html);
		$('select#' + _volumeTypeInputId).chosen({disable_search: true, width: '65px'});

		$(_selectors.textVolumeWrap).addClass('hidden');
		$(_selectors.textVolumeInput).data('checkTextValid', false);
		$(_selectors.volumeWrapBlock).removeClass('hidden');
		// Показываем блок выбора минимального объема
		_showCardMinVolume();
	};

	/**
	 * Переключение в режим заполнения текстового объема
	 * @private
	 */
	var _disableVolume = function () {
		$(_selectors.volumeWrapBlock).addClass('hidden');
		$(_selectors.textVolumeWrap).removeClass('hidden');
		// Скрываем блок выбора минимального объема
		_hiddenCardMinVolume();
		$(_selectors.textVolumeInput).data('checkTextValid', true);

		if (_lastSelectedVolumeType) {
			$(_selectors.textVolumeEditor).html("");
			$(_selectors.textVolumeInput).val("");
		}
	};

	/**
	 * Проверка нужно ли включить числовой объем кворка или текстовый
	 *
	 * @private
	 */
	var _toggleVolumeType = function () {
		if (_requiredVolumeTypeId) {
			_enableVolume(_requiredVolumeTypeId, _allowedAdditionalVolumeTypesIds);
		} else {
			_disableVolume();
		}
	};

	/**
	 * Установка требуемого типа числового объема
	 *
	 * @param volumeTypeId
	 * @private
	 */
	var _setRequiredVolumeTypeId = function (volumeTypeId) {
		_requiredVolumeTypeId = parseInt(volumeTypeId);
	};

	/**
	 * Установка дополнительных допустимых типов числового объема
	 *
	 * @param additionalVolumeTypesIds
	 * @private
	 */
	var _setAllowedAdditionalVolumeTypesIds = function(additionalVolumeTypesIds) {
		_allowedAdditionalVolumeTypesIds = additionalVolumeTypesIds;
	};

	/**
	 * Включение/отключение числового объема
	 *
	 * @private
	 */
	var _setToggle = function () {
		return _toggleVolumeType();
	};

	/**
	 * Установка признака необходимости сбросить объем
	 *
	 * @private
	 */
	var _setDropVolume = function () {
		_dropVolume = true;
	};	
	
	/**
	 * Расчет минимального объема
	 */
	var _calculationsMinVolume = function() {
		var minVolumePrice = parseInt($('#min_volume_price').val(), 10);
		var price = MIN_PRICE;
		var volumeTypeId;
		var volume;
		var lang = $('#lang').val();
		// Если пакетный кворк
		if ($('.js-step-bundle').hasClass('kwork-save-step__bundle-only')) {
			volumeTypeId = $('#package_volume_type_id').val();
			var volume = $('.js-bundle-item__input.package_volume').val();
		} 
		else {
			volumeTypeId = $('#js-volume-type-block div').data("volumeTypeId");
			//Если id не найден значит тип берем из выпадающего списка
			if(volumeTypeId === undefined){
				volumeTypeId = $('#step2-volume-type-id').val();				
			}
			volume = $('#step2-volume').val();		
		}
		volume = (volume === '' || typeof volume === 'undefined') ? '0' : volume;
		
		if(volume === '0') {
			$('.js-min-volume-block').hide();
		} else {
			$('.js-min-volume-block').show();			
		}
		
		volume = parseInt(volume.replace(/[^0-9]/gim,''));	
		var minValume = Math.floor(minVolumePrice/price)*volume;
		$('.js-min-volume').html(Utils.bigNumberToString(minValume));
		$('#min_volume').val(Utils.bigNumberToString(minValume));
		// Если русский кворк то оставляем склонения только для 1 и 10
		if (lang === "ru") {
			minValume = (minValume == 1) ? 2 : 10;
		}
		$('.js-min-volume-type').html(VolumeTypesModule.pluralVolumeById(volumeTypeId, minValume));
		
	};
	
	/**
	 * Показываем блок с минимальным объемом и вычисляем значения
	 */
	var _showCardMinVolume = function() {	
		$(_selectors.cardMinVolume).removeClass('hidden');
		_calculationsMinVolume();		
	};
	
	/**
	 * Показываем блок с минимальным объемом и вычисляем значения
	 */
	var _hiddenCardMinVolume = function() {	
		$(_selectors.cardMinVolume).addClass('hidden');
		$('#min_volume').val(0);
	};

	var _init = function(options) {
		if (options.lastSelectedVolumeType) {
			_lastSelectedVolumeType = parseInt(options.lastSelectedVolumeType);
		}
		if (options.requiredVolumeTypeId) {
			_requiredVolumeTypeId = options.requiredVolumeTypeId;
		}
		if (options.allowedAdditionalVolumeTypesIds) {
			_allowedAdditionalVolumeTypesIds = options.allowedAdditionalVolumeTypesIds;
		}
		if (options.volumeTypeInputId) {
			_volumeTypeInputId = options.volumeTypeInputId;
		}
		if (options.volumeSizeInput) {
			_selectors.volumeSizeInput = options.volumeSizeInput;
		}
		if (options.volumeTypeBlock) {
			_selectors.volumeTypeBlock = options.volumeTypeBlock;
		}
		if (options.volumeWrapBlock) {
			_selectors.volumeWrapBlock = options.volumeWrapBlock;
		}
		if (options.volumeTypeInputName) {
			_volumeTypeInputName = options.volumeTypeInputName;
		}
	};

	_init(options);

	return {
		init: _init,
		setToggle: _setToggle,
		setRequiredVolumeTypeId: _setRequiredVolumeTypeId,
		setAllowedAdditionalVolumeTypesIds: _setAllowedAdditionalVolumeTypesIds,
		setDropVolume: _setDropVolume,
		calculationsMinVolume: _calculationsMinVolume,
		hiddenCardMinVolume: _hiddenCardMinVolume,
	}
};

/**
 * Модуль получения данных о необходимом типе числового объема в категории
 *
 * @type {{init, getRequiredVolumeType, getAdditionalVolumeTypes}}
 */
var CategoriesVolumeTypesModule = (function () {
	var _volumeCategories = [];

	var _searchCategoryById = function(categoryId){
		categoryId = parseInt(categoryId);
		if (_volumeCategories !== undefined && categoryId > 0) {
			for (var i = 0; i < _volumeCategories.length; i++) {
				if (_volumeCategories[i].CATID === categoryId) {
					return _volumeCategories[i];
				}
			}
		}
	};

	return {
		init: function (volumeCategories) {
			_volumeCategories = volumeCategories;
		},
		getRequiredVolumeType: function (categoryId) {
			var category = _searchCategoryById(categoryId);
			if (category) {
				return category.volume_type_id;
			}
		},
		getAdditionalVolumeTypes: function (categoryId) {
			var category = _searchCategoryById(categoryId);
			if (category) {
				return category.additionalVolumeTypesIds;
			}
		},
	}
})();


/**
 * Модуль получения данных по типу числового объема
 *
 * @type {{init, getVolumeType, pluralVolumeById}}
 */
var VolumeTypesModule = (function () {

	var _volumeTypes = [];

	/**
	 * Получение типа числового объема по идентификатору
	 *
	 * @param volumeTypeId Идентификатор числового объема кворка
	 * @returns {*}
	 * @private
	 */
	var _getVolumeType = function (volumeTypeId) {
		volumeTypeId = parseInt(volumeTypeId);
		if (_volumeTypes !== undefined && _volumeTypes.length > 0) {
			for (var i = 0; i < _volumeTypes.length; i++) {
				if (_volumeTypes[i].id === volumeTypeId) {
					return _volumeTypes[i];
				}
			}
		}
	};

	/**
	 * Склонение названия типа цифрового объема
	 *
	 * @param volumeType Тип цифрового объема - объект
	 * @param volume Объем
	 * @returns {*} Склоненное название типа объема
	 * @private
	 */
	var _pluralVolumeType = function (volumeType, volume) {
		if (volumeType === undefined) {
			return;
		}
		return declension(volume, volumeType.name, volumeType.name_plural_2_4, volumeType.name_plural_11_19);
	};	

	return {
		init: function (volumeTypes) {
			_volumeTypes = volumeTypes;
		},
		getVolumeType: _getVolumeType,
		pluralVolumeById: function (volumeTypeId, volume) {
			return _pluralVolumeType(_getVolumeType(volumeTypeId), volume);
		},
	}
})();
