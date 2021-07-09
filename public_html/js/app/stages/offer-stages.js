/**
 * Работа с задачами
 */
export class OfferStages {

	constructor() {
		/**
		 * Изменять ли отображение ошибки. Призначении true: если скрыта - оставлять скрытой, если отображена - не скрывать.
		 * @type {boolean}
		 */
		this.notChangeShowErrorsBlock = false;

		/**
		 * Массив ошибок задач
		 * @type {Array}
		 * @property {string} target
		 * @property {number} position
		 * @property {string} text
		 */
		this.errorsStage = [];

		/**
		 * Заблокировать ли кнопку отправки формы
		 * @type {boolean}
		 */
		this.isDisableButtonSend = false;

		/**
		 * Итоговая стоимость заказа
		 * @type {number}
		 */
		this.totalPrice = 0;

		/**
		 * Наличие ошибки в добавлении срока
		 * @type {boolean}
		 */
		this.durationError = false;

		/**
		 * Тип пользователя - продавец
		 * @type {string}
		 * @const
		 */
		this.ACTOR_TYPE_WORKER = 'worker';

		/**
		 * Страница трека
		 * @type {string}
		 * @const
		 */
		this.PAGE_TRACK = 'track';

		/**
		 * Предложение индивидуального предложения
		 * @type {string}
		 * @const
		 */
		this.PAGE_OFFER = 'offer';

		/**
		 * Тип пользователя - покупатель
		 * @type {string}
		 * @const
		 */
		this.ACTOR_TYPE_PAYER = 'payer';

		this.selectors = {
			offerStages: '.js-offer-stages',
			stages: '.js-stages',
			stage: {
				itemDefault: '.js-stage-default',
				item: '.js-stage',
				infoBlock: '.offer-individual__stage-info',
				number: '.js-stage-number',
				numberSelect: '.js-stage-number-select',
				title: '.js-stage-name',
				titleEditor: '.js-content-editor',
				titleText: '.js-stage-name-text',
				titleBlock: '.offer-individual__stage-name',
				price: '.js-stage-price',
				priceText: '.js-stage-price-text',
				error: '.js-stage-error',
				add: '.js-stage-add',
				edit: '.js-stage-edit',
				save: '.js-stage-save',
				remove: '.js-stage-delete',
			},
			totalPrice: {
				block: '.js-stage-total-price',
				wrap: '.js-stage-total-price-wrap',
				price: '.js-offer-total-price',
				priceCommission: '.js-stage-total-price-commission',
				error: '.js-stage-total-price-error',
				addPayerBlock: '.js-stage-total-price-add-payer-block',
				addPayer: '.js-stage-total-price-add-payer',
				priceWithCommission: '.js-price-explanation',
			},
			textLimit: '.js-stage-limit',
			duration: '.js-stages-duration',
			durationValue: '.js-stages-duration-value',
			durationChange: '.js-stages-duration-change',
			durationChangeSelect: '.js-stages-duration-change-select',
			durationChangeError: '.js-stages-duration-change-error',
			customPrice: '.js-kwork-price',
		};

		this.classes = {
			stageItem: 'js-stage',
			stageEdit: 'stage-edit',
			stageError: 'stage-error',
			disableAddStage: 'disable',
			enStages: 'offer-stages--en',
			stageActive: 'offer-individual__stage--active',
			durationError: 'duration-error',
			durationErrorLight: 'duration-error-light',
		};

		/**
		 * @type {Object}
		 */
		this.stagesOfferBlock = $(this.selectors.offerStages);

		this.timerValidateTitle = null
	}

	init(options) {

		if (this.options) {
			this.prevOption = this.options;
		}

		/**
		 * @type {Object}
		 * @property {string} actorType
		 * @property {string} pageType
		 * @property {Object} stages
		 * @property {boolean} showOneStage
		 * @property {number} turnover
		 * @property {boolean} controlEnLang
		 * @property {number} countStages
		 * @property {Object} offer
		 * @property {number} offer.orderId
		 * @property {string} offer.lang
		 * @property {number} offer.stageMinPrice
		 * @property {number} offer.customMinPrice
		 * @property {number} offer.customMaxPrice
		 * @property {number} offer.offerMaxStages
		 * @property {number} offer.price
		 * @property {number} offer.duration
		 * @property {number} offer.initialDuration
		 * @property {number} offer.stageMaxIncreaseDays
		 * @property {number} offer.stageMaxDecreaseDays
		 */
		this.options = options;
	}

	/**
	 * Обновление значения параметра stageMinPrice
	 * @param value
	 */
	updateOptionsStageMinPrice(value) {
		this.options['offer']['stageMinPrice'] = value;
	}

	/**
	 * Инициализация предыдущих настроек.
	 * Актульано для диалогов в роли продавца. Когда пользователь может как принять предложение, так и содать свое
	 */
	initPrev() {
		if (this.prevOption) {
			this.options = this.prevOption;
		}
	}

	events() {
		this.stagesOfferBlock
			.off('click change input')

			.on('change', this.selectors.stage.numberSelect, (e) => {
				this.defaultNotChangeShowErrorsBlock();

				this.validateStageNumber($(e.target));
				this.isEnableAddStage(true);
				this.onChangeStage();
			})
			.on('input', this.selectors.stage.title, (e) => {
				this.defaultNotChangeShowErrorsBlock();
				let $stage = $(e.target).parents(this.selectors.stage.item);

				this.controlLang($(e.target));

				clearTimeout(this.timerValidateTitle);
				this.timerValidateTitle = setTimeout(() => {
					this.validateStageName($(e.target));
					this.showCurrentError($(e.target));
					this.isEnableAddStage(true);
					this.onChangeStage();
					this.onEditStage($stage);
				}, 500);
			})
			.on('input', this.selectors.stage.price, (e) => {
				this.defaultNotChangeShowErrorsBlock();
				let $stage = $(e.target).parents(this.selectors.stage.item);
				this.limitMaxPrice($(e.target), this.selectors.stage.item);

				this.calcTotalPrice();
				this.changeDurationChange();
				this.validateTotalPrice();

				// создание сообщения об ошибке только у полей с данными
				$(this.selectors.stage.price).each((i) => {
					let $element = $(this.selectors.stage.price).eq(i);
					if ($element.val()) {
						this.validateStagePrice($element);
						this.showCurrentError($element);
					} else {
						this.validateStagePrice($element, true);
						this.showCurrentError($element);
					}
				});
				this.isEnableAddStage(true);
				this.onChangeStage();
				this.onEditStage($stage);
				this.calcPlaceholder();

				if (this.options.pageType === this.PAGE_OFFER) {
					this.updateCustomPrice();
				}
			})
			.on('click', this.selectors.stage.add, () => {
				this.defaultNotChangeShowErrorsBlock();

				let isAddStage = this.addStage();
				if (isAddStage) {
					this.isEnableAddStage(true);
					this.onChangeStage();
				}
			})
			.on('click', this.selectors.stage.remove, (e) => {
				this.defaultNotChangeShowErrorsBlock();
				let $stage = $(e.target).parents(this.selectors.stage.item);
				this.removeStage($stage);
				this.changeDurationChange();
				this.validateTotalPrice();
				this.updateControlHide();
				this.isEnableAddStage(true);
				this.onChangeStage();
				this.validateStagesPrices();
				this.calcPlaceholder();
				this.onDeleteStage($stage);
			})
			.on('click', this.selectors.stage.edit, (e) => {
				let $stage = $(e.target).parents(this.selectors.stage.item);

				this.defaultNotChangeShowErrorsBlock();

				this.hideTotalPriceErrorByFirstStage();
				this.editStage($stage);
			})
			.on('click', this.selectors.stage.save, (e) => {
				if (window.offerForm && window.offerForm.isBusy) {
					return;
				}
				this.defaultNotChangeShowErrorsBlock();

				this.saveStage($(e.target).parents(this.selectors.stage.item));
				this.validateTotalPrice();
				this.isEnableAddStage(true);
				this.onChangeStage();
			})
			.on('change', this.selectors.durationChangeSelect, () => {
				this.validateDurationChange();
				this.onChangeStage();
			});

		$(window).resize(() => {
			this.reBuildStages();
		});

		this.stagesOfferBlock
			.on('keypress', '.js-content-editor', (e) => {
				this.eventEnter(e);
			})
			.on('keypress', this.selectors.stage.title, (e) => {
				this.eventEnter(e);
			})
			.on('keypress', this.selectors.stage.price, (e) => {
				this.eventEnter(e);
			})
	}

	/**
	 * Событие при нажатии на Enter
	 * Сохраняем текущую задачу
	 */
	eventEnter(e) {
		if (e.keyCode === 13) {
			e.preventDefault();
			$(e.target).parents(this.selectors.stage.item).find(this.selectors.stage.save).click();
		}
	}

	/**
	 * Событие при изменении задачи
	 */
	onChangeStage() {
	}

	/**
	 * Событие при удалении задачи
	 * @param $stage
	 */
	onDeleteStage($stage) {
	}

	/**
	 * Событие при редактировании задачи
	 * @param $stage
	 */
	onEditStage($stage) {
	}

	/**
	 * Сгенерировать задач
	 */
	generationStages() {
		this.initStagesBlock(this.options.offer.orderId);

		this.stagesOfferBlock.removeClass(this.classes.enStages);
		if (this.options.offer.lang === 'en') {
			this.stagesOfferBlock.addClass(this.classes.enStages);
		}

		// если переданы данные задач, то отображаем их
		if (this.options.stages && Object.keys(this.options.stages).length) {
			$.each(this.options.stages, (k, values) => {
				this.buildStage(k + 1, values.number);
				this.setDataStage(values);
			});

			// на странице трэка открыть задачи сразу для редактирования
			if (this.options.pageType === this.PAGE_TRACK) {
				this.stagesOfferBlock.find(this.selectors.stage.item).each((key, value) => {
					this.showStageInput($(value));
				});
			}
		} else if (this.options.pageType === this.PAGE_TRACK) {
			// на странице трека при нажатии на кнопку "Добавить задачу"
			this.buildStage(1, this.options.countStages + 1);
		} else if (this.options.actorType === this.ACTOR_TYPE_PAYER || this.options.pageType === this.PAGE_OFFER) {
			// если покупатель и в предложении нет задач, то показываем три пустых задачи (биржа и диалоги)
			this.buildStage(1, 1);
			this.buildStage(2, 2);
			this.buildStage(3, 3);

			this.setPlaceholderTitle();
		} else {
			this.buildStage(1, 1);
		}

		this.calcTotalPrice();
		this.updateControlHide();
		this.isEnableAddStage(true);
		this.reBuildStages();

		this.initTooltip();
		this.setDuration();

		this.events();
	}

	initStagesBlock(orderId) {
		this.stagesOfferBlock = $(this.selectors.offerStages).filter('[data-order-id="' + orderId + '"]');
	}

	/**
	 * Инициализация тултипа для иконок задачи
	 */
	initTooltip() {
		if ($.fn.tooltipster) {
			this.stagesOfferBlock.children('div:not(' + this.selectors.stage.itemDefault + ')').find('.tooltip:not(.tooltipstered)').tooltipster(TOOLTIP_CONFIG);
		}
	}

	/**
	 * Установить текущий срок заказа в днях
	 */
	setDuration() {
		let durationDays = this.options.offer.duration / 86400;
		durationDays = durationDays.toFixed(0); // округляем до целого
		let durationText = durationDays + ' ' + Utils.declOfNum(durationDays, [t('день'), t('дня'), t('дней')]);
		this.stagesOfferBlock.find(this.selectors.durationValue).html(durationText);
	}


	/**
	 * Валидация задач
	 * @param notChangeShowErrors
	 */
	validateStages(notChangeShowErrors) {
		this.notChangeShowErrorsBlock = (typeof notChangeShowErrors === "boolean") ? notChangeShowErrors : false;
		this.errorsStage = [];

		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			this.validateStage($(v));
		});

		this.showAllStageErrors();
	}

	/**
	 * Валидация всех цен
	 */
	validateStagesPrices() {
		this.calcTotalPrice();

		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			let stagePrice = $(v).find(this.selectors.stage.price);
			if (stagePrice.val() !== '') {
				this.defaultNotChangeShowErrorsBlock();
				this.validateStagePrice(stagePrice);
				this.showCurrentError(stagePrice);
				this.isEnableAddStage(true);
			}
		});
	}

	/**
	 * Валидация задачи
	 * @param {Object} $stage
	 */
	validateStage($stage) {
		let isErrorName = this.validateStageName($stage.find(this.selectors.stage.title));
		let isErrorPrice = this.validateStagePrice($stage.find(this.selectors.stage.price));
		let isErrorNumber = this.validateStageNumber($stage.find(this.selectors.stage.numberSelect));

		return isErrorName && isErrorPrice && isErrorNumber;
	}

	/**
	 * Валидация названия задачи
	 * @param {Object} $input
	 * @returns {boolean}
	 */
	validateStageName($input) {
		let idStage = $input.parents(this.selectors.stage.item).data('number');

		this.hideStageInputError($input);

		let backendError = $input.data('backendError');
		if (backendError) {
			this.addErrorStage({
				target: "title",
				text: backendError,
				position: idStage,
			});
			return false;
		}

		if ($input.val() === "" || StopwordsModule._testContacts($input.val()).length !== 0) {
			this.addErrorStage({
				target: "title",
				text: t('Укажите название задачи'),
				position: idStage,
			});

			return false;
		}

		this.removeErrorStage({
			target: "title",
			position: idStage
		});

		return true;
	}

	/**
	 * Валидация стоимости задачи
	 * @param {Object} $input
	 * @returns {boolean}
	 */
	validateStagePrice($input, clearErrors) {
		let price = $input.val();
		let idStage = $input.parents(this.selectors.stage.item).data('number');
		
		if (idStage === null) {
			return false;
		}

		if (clearErrors) {
			// удаление ошибки задачи
			this.removeErrorStage({
				target: "payer_price",
				position: idStage
			});
			return true;
		}
		
		price = (price == '') ? 0 : price;
		
		this.hideStageInputError($input);

		// ввод только цифр
		if (/[^0-9]/ig.test(price)) {
			$input.val(price.replace(/[^0-9]/ig, ''));

			return false;
		}

		if (isNaN(price) || price === '') {

			this.addErrorStage({
				target: "payer_price",
				text: t('Укажите цену'),
				position: idStage
			});

			return false;
		}

		// формирование текста ошибки при вводе стоимости задачи
		if (price < this.options.offer.stageMinPrice || price > this.options.offer.customMaxPrice) {
			var stageMaxCount = this.options.offer.customMaxPrice - this.totalPrice + parseInt(price);
			var stageMinPrice = Utils.priceFormatWithSign(this.options.offer.stageMinPrice, this.options.offer.lang);
			var stageMaxPrice = Utils.priceFormatWithSign(stageMaxCount, this.options.offer.lang);
			var errorText = '';

			// текст ошибки в зависимости от условий
			if (this.options.offer.stageMinPrice === this.options.offer.customMaxPrice || stageMaxCount === this.options.offer.stageMinPrice) {
				errorText = t('Стоимость должна быть равна {{0}}', [stageMinPrice]);
			} else if (stageMaxCount <= this.options.offer.stageMinPrice) {
				if (this.options.offer.customMaxPrice <= this.totalPrice) {
					errorText = t('Достигнута максимальная общая стоимость задач. Отредактируйте стоимость предыдущих задач, либо удалите текущую задачу.');
				} else {
					errorText = t('Стоимость задачи должна быть от {{0}}', [stageMinPrice]);
				}
			} else {
				errorText = t('Стоимость задачи должна быть от {{0}} до {{1}}', [
					stageMinPrice,
					stageMaxPrice
				]);
			}

			// добавление ошибки задачи
			this.addErrorStage({
				target: "payer_price",
				text: errorText,
				position: idStage
			});

			this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
			return false;
		}

		// удаление ошибки задачи
		this.removeErrorStage({
			target: "payer_price",
			position: idStage
		});
		return true;
	}

	/**
	 * Обрезаем стоимость по максимальному значению
	 * @param {Object} $input
	 * @param {String} stageSelector
	 * @returns {Void}
	 */
	limitMaxPrice($input, stageSelector) {
		let inputValue = parseInt($input.val());
		let stageList = $(stageSelector);
		let otherStagesPrice = 0;
		let passStagesPrice = false;
		for (let i = 0; i < stageList.length; i++) {
			let inputVal = $(stageList[i]).find('.js-stage-price').val();
			inputVal = inputVal || 0;
			let stagePrice = parseInt(inputVal);
			if (stagePrice && stagePrice !== inputValue || passStagesPrice) {
				otherStagesPrice = otherStagesPrice + stagePrice;
			} else if (stagePrice === inputValue) {
				passStagesPrice = true;
			}
		}

		if (isNaN(inputValue)) {
			inputValue = '';
		}
		
		if (inputValue > this.options.offer.customMaxPrice - otherStagesPrice) {
			// устанавливаем максимальное значение в текущем инпуте
			// в зависимости от максимальной стоимости и уже введенной стоимости в других задач
			inputValue = this.options.offer.customMaxPrice - otherStagesPrice;
		}

		$input.val(inputValue);
	}

	/**
	 * Если дошли до предела максимльной цены, то блокируем кнопку добавления задачи
	 * @returns {boolean}
	 */
	checkSumTotalPrice() {
		return this.totalPrice === this.options.offer.customMaxPrice
			|| this.options.offer.customMaxPrice - this.totalPrice < this.options.offer.stageMinPrice;
	}

	/**
	 * Валидация итоговой стоимости заказа
	 * @returns {boolean}
	 */
	validateTotalPrice() {
		this.hideTotalPriceError();
		this.enableButtonSend();

		if (this.totalPrice > this.options.offer.customMaxPrice) {
			this.showTotalPriceError(t('Стоимость заказа достигла предельной суммы ') + Utils.priceFormatWithSign(this.options.offer.customMaxPrice, this.options.offer.lang));
			this.disableAddStage();
			this.disableButtonSend();

			this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');

			return false;
		}

		if (this.totalPrice < this.options.offer.customMinPrice) {
			this.showTotalPriceError(t('Итоговая цена заказа не может быть ниже ') + Utils.priceFormatWithSign(this.options.offer.customMinPrice, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>'));
			this.disableButtonSend();

			this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
		}

		return true;
	}

	/**
	 * Валидация селекта номера задачи
	 */
	validateStageNumber($select) {
		let returnValue = true;
		let idStageSelect = $select.parents(this.selectors.stage.item).data('number');

		// перибираем задачи по порядку и ищем с одинаково выбраным номером
		$.each($(this.selectors.stage.numberSelect).filter(':visible'), (k1, v1) => {
			let idStage1 = $(v1).parents(this.selectors.stage.item).data('number');
			this.removeErrorStage({
				position: idStage1,
				target: 'number'
			});
			this.hideStageInputError($(v1));

			$.each($(this.selectors.stage.numberSelect).filter(':visible'), (k2, v2) => {
				let idStage2 = $(v2).parents(this.selectors.stage.item).data('number');

				// если значения номеров одинаково
				// и это не одина и та же задача,
				// то ошибка
				if (
					$(v1).val() === $(v2).val()
					&& idStage1 !== idStage2
				) {

					this.addErrorStage({
						position: idStage1,
						target: 'number',
						text: t('Задачи имеют одинаковый номер. Исправьте их последовательность.')
					});

					this.showCurrentError($(v1));

					// если ошибка в текущей задачи,
					// то возвращаем false, чтобы нельзя было сохранить задачу
					if (idStage1 === idStageSelect) {
						returnValue = false;
					}
				}
			});
		});

		this.reBuildStages();

		return returnValue;
	}

	/**
	 * Валидация селекта "Добавить (удалить) срок к заказу"
	 * @returns {boolean}
	 */
	validateDurationChange() {
		if (
			this.stagesOfferBlock.find(this.selectors.durationChange).is(':visible')
			&& isNaN(parseInt(this.stagesOfferBlock.find(this.selectors.durationChangeSelect).val()))
		) {
			return false;
		}

		this.hideDurationChangeError();
		return true;
	}

	/**
	 * Добавить задачу
	 */
	addStage() {
		if (this.stagesOfferBlock.find(this.selectors.stage.add).hasClass('disable')
			|| this.isEnableAddStage(false) === false
		) {
			return false;
		}

		this.reSaveStages();

		let countStages = this.stagesOfferBlock.find(this.selectors.stage.item).length;
		let maxNumberResult = this.options.countStages || 0;
		// актуально для странице трэка. Если при редактировании удаляются все задачи, то для новой задачи надо кол-во задачи
		if (countStages) {

			let {minNumber, maxNumber} = this.getMaxAndMinNumber();

			if (maxNumber >= maxNumberResult) {
				maxNumberResult = maxNumber;
			}
		}

		this.buildStage(countStages + 1, parseInt(maxNumberResult) + 1);

		this.initTooltip();

		this.stagesOfferBlock.find(this.selectors.stage.item).css({'z-index': 1});
		this.stagesOfferBlock.find(this.selectors.stage.item).last().css({'z-index': 0});

		this.reBuildStages();

		return true;
	}

	/**
	 * Удалить задачи
	 * @param {Object} $stage
	 */
	removeStage($stage) {
		$stage.remove();

		let {minNumber, maxNumber} = this.getMaxAndMinNumber();
		let countStages = this.stagesOfferBlock.find(this.selectors.stage.item).length;

		this.updateSortStages($stage, maxNumber + 1, countStages + 1);
		this.calcTotalPrice();

		if (this.options.pageType === this.PAGE_OFFER) {
			this.updateCustomPrice();
		}
	}

	/**
	 * Редактировать задачи
	 * @param {Object} $stage
	 */
	editStage($stage) {

		// для покупателя нужно включать редактирование всех задач. А текущий выделяется
		if (this.options.actorType === this.ACTOR_TYPE_PAYER) {
			this.stagesOfferBlock.find(this.selectors.stage.item).each((key, value) => {
				this.showStageInput($(value));
				this.updateNumberSelectStage($(value));
				this.showNumberSelectStage($(value));
			});

			this.selectCurrentStage($stage);
		} else {
			this.showStageInput($stage);
			this.updateNumberSelectStage($stage);
			this.showNumberSelectStage($stage);
		}

		this.reBuildStages();
	}

	/**
	 * Сохранить задачу
	 * @param {Object} $stage
	 */
	saveStage($stage) {

		if (!this.validateStage($stage)) {

			this.showCurrentError($stage.find(this.selectors.stage.title));
			this.showCurrentError($stage.find(this.selectors.stage.price));

			return false;
		}

		$stage.removeClass(this.classes.stageError);
		$stage.removeClass(this.classes.stageActive);

		this.showStageText($stage);
		this.saveNumberSelectStage($stage);
		this.hideNumberSelectStage($stage);

		this.reBuildStages();
	}

	/**
	 * Сохранить все задачи
	 */
	reSaveStages() {
		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			this.saveStage($(v));
		});
	}

	/**
	 * Отслеживает количество отображаемых задач
	 * @returns {boolean}
	 */
	checkStageCountLimit() {
		let countStage = this.stagesOfferBlock.find(this.selectors.stage.item).length;

		if (!this.options.offer.offerMaxStages || countStage < this.options.offer.offerMaxStages) {
			$(this.selectors.textLimit).addClass('hidden');

			return true;
		} else {
			$(this.selectors.textLimit).removeClass('hidden');

			return false;
		}
	}

	/**
	 * Подсчитать итоговую цену предложения
	 * TODO: разбить на методы по-хорошемы бы
	 */
	calcTotalPrice() {
		let countStage = this.stagesOfferBlock.find(this.selectors.stage.item).length;

		this.stagesOfferBlock.find(this.selectors.totalPrice.block).addClass('hidden');
		this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).addClass('hidden');
		if (this.options.actorType === this.ACTOR_TYPE_WORKER && countStage > 1) {
			this.stagesOfferBlock.find(this.selectors.totalPrice.block).removeClass('hidden');
		}

		// Для покупател показывать итоговую стоимость только, если он заполнил 2 задачи.
		// Необходимо в случае если он принимает безэтпное предложение.
		// В без задачном предложении отображается 3 пустых задачи (реализовано в generationStages)
		if (
			this.options.actorType === this.ACTOR_TYPE_PAYER
			&& countStage > 1
			&& this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.price).val() !== ''
			&& this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='2']").find(this.selectors.stage.price).val() !== ''
		) {
			this.stagesOfferBlock.find(this.selectors.totalPrice.block).removeClass('hidden');
		}

		if (
			this.options.actorType === this.ACTOR_TYPE_WORKER
			&& this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.price).val() !== ''
		) {
			this.stagesOfferBlock.find(this.selectors.totalPrice.priceCommission).removeClass('hidden');
		}

		let totalPrice = 0;

		this.stagesOfferBlock.find(this.selectors.stage.price).each((k, v) => {
			let value = parseInt($(v).val());

			if (!isNaN(value)) {
				totalPrice += value;
			}
		});
		this.totalPrice = totalPrice;

		this.stagesOfferBlock.find(this.selectors.totalPrice.price).html(Utils.priceFormatWithSign(totalPrice, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>'));

		this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock).addClass('hidden');
		// Если стоимость предложения продавца превышает итоговую стоимость
		// и если итоговую стоимость не превышает свой лимит,
		// то показываем уточнение для покупателя на сколько увеличится стоимсоть
		if (
			this.options.actorType === this.ACTOR_TYPE_PAYER
			&& this.totalPrice > this.options.offer.customMinPrice
			&& this.totalPrice <= this.options.offer.customMaxPrice
			&& this.options.pageType !== this.PAGE_TRACK
		) {
			let diff = Math.abs(this.options.offer.customMinPrice - this.totalPrice);
			let formatDiff = Utils.priceFormatWithSign(diff, this.options.offer.lang, '&nbsp;', '<span class="rouble">Р</span>');
			this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock).removeClass('hidden');
			this.stagesOfferBlock.find(this.selectors.totalPrice.addPayer).html('+' + formatDiff);

			let priceForTooltip = formatDiff.replace(/\.+$/, '');
			const tooltipContent = '<div class="stage-tooltip-content">'+t('Итоговая цена, которую вы готовы заплатить, превысила ставку продавца на {{0}}. Это допустимо, особенно, если вы добавили задачи, которые не предусмотрели заранее.', [priceForTooltip])+'</div>'
			this.stagesOfferBlock.find(this.selectors.totalPrice.addPayerBlock)
				.find('.tooltipster')
				.tooltipster('content', tooltipContent);
		}

		if (this.options.actorType === this.ACTOR_TYPE_WORKER) {
			this.calcPriceWithCommission(totalPrice);
		} else {
			this.clearPriceWithCommission();
		}
	}

	/**
	 * Обновление общей стоимости инд. предложения. Актуально только при отправке предложения
	 */
	updateCustomPrice() {
		// через window, чтобы можно было использовать в другом месте
		if (window.offerStages.totalPrice > window.offerStages.options.offer.stagesPriceThreshold) {
			$(window.offerStages.selectors.customPrice).val(window.offerStages.totalPrice);
		}
	}

	/**
	 * Подсчет цены с комиссией
	 * @param {number} summ
	 */
	calcPriceWithCommission(summ) {

		let price = summ ^ 0;
		let commission = calculateCommission(price, this.options.turnover);
		let ctp = price - commission.priceKwork;

		let message = t('Вы получите {{0}}', [Utils.priceFormatWithSign(ctp, this.options.offer.lang)]);
		message += ' <a href="javascript:void(0);" class="js-price-explanation kwork-price-explanation">' + t('Подробнее') + '</a>';

		$(this.selectors.totalPrice.priceCommission).html(message);

		this.stagesOfferBlock.find(this.selectors.totalPrice.priceWithCommission).data('commission', commission);
	}

	/**
	 * Очистка блока цены с комиссией
	 */
	clearPriceWithCommission() {
		$(this.selectors.totalPrice.priceCommission).html('');
	}

	/**
	 * Изменение блока "Добавить (уменьшить) срок к заказу"
	 */
	changeDurationChange() {
		this.hideDurationChangeBlock();

		// если общая стоимость задач меньше минимальной стоимости заказа
		// или равна текущей цене заказа
		// то ничего не делаем

		let $durationChangeSelect = this.stagesOfferBlock.find(this.selectors.durationChangeSelect);
		let countOptions;
		let textErrorDuration;
		let statusErrorDuration;
		let labelDuration;

		if (this.options.actorType !== this.ACTOR_TYPE_PAYER) {
			return;
		} else if (this.totalPrice >= this.options.offer.customMinPrice && this.totalPrice < parseInt(this.options.offer.price) && this.options.offer.stageMaxDecreaseDays) {
			textErrorDuration = t('Уменьшение срока не обязательно, но допустимо.');
			statusErrorDuration = 'green';
			labelDuration = t('Уменьшить срок заказа на');
			countOptions = this.options.offer.stageMaxDecreaseDays;
		} else if (this.totalPrice > parseInt(this.options.offer.price) && this.options.offer.stageMaxIncreaseDays) {
			textErrorDuration = t('Увеличьте срок, поскольку сумма заказа станет больше.');
			statusErrorDuration = 'red';
			labelDuration = t('Добавить срок к заказу');
			countOptions = this.options.offer.stageMaxIncreaseDays;
		} else {
			return;
		}

		this.showDurationChangeBlock();

		if (
			$durationChangeSelect.val()
			&& $durationChangeSelect.find('option').length === countOptions + 1
		) {
			return;
		}

		this.showDurationChangeError(textErrorDuration, statusErrorDuration);
		this.stagesOfferBlock.find(this.selectors.durationChange).find('label').text(labelDuration);
		this.reBuildDuration(countOptions);
	}

	/**
	 * Обновить селект в блоке "Добавить (уменьшить) срок к заказу"
	 * @param countOptions
	 */
	reBuildDuration(countOptions) {
		let $durationChangeSelect = this.stagesOfferBlock.find(this.selectors.durationChangeSelect);

		$durationChangeSelect.html('');

		let options = '<option value="" disabled selected>' + t('Выберите срок') + '</option>';
		for (let i = 1; i <= countOptions; i++) {
			options += '<option value="' + i + '">' + i +' '+ declension(i, t('день'), t('дня'), t('дней'), 'ru' ) +'</option>';
		}

		$durationChangeSelect.append(options);
		$durationChangeSelect.chosen({width: "100%", disable_search: true});
		$durationChangeSelect.trigger('chosen:updated');
	}

	/**
	 * Скрыть блок "Добавить (уменьшить) срок к заказу"
	 */
	hideDurationChangeBlock() {
		this.stagesOfferBlock.find(this.selectors.durationChange).addClass('hidden');
		this.hideDurationChangeError();
	}

	/**
	 * Показать блок "Добавить (уменьшить) срок к заказу"
	 */
	showDurationChangeBlock() {
		this.stagesOfferBlock.find(this.selectors.durationChange).removeClass('hidden');
	}

	/**
	 * Показать задачу в виде инпутов
	 * @param {Object} $stage
	 */
	showStageInput($stage) {
		$stage.addClass(this.classes.stageEdit);
	}

	/**
	 * Показать задачу в виде текста
	 * @param {Object} $stage
	 */
	showStageText($stage) {

		$stage.removeClass(this.classes.stageEdit);

		$stage.find(this.selectors.stage.titleText).html($stage.find(this.selectors.stage.title).val());
		$stage.find(this.selectors.stage.priceText).text(Utils.priceFormat($stage.find(this.selectors.stage.price).val()));
	}

	/**
	 * Выделить текущую задачу
	 * @param $stage
	 */
	selectCurrentStage($stage) {
		$(this.selectors.stage.item).removeClass(this.classes.stageActive);
		$stage.addClass(this.classes.stageActive);
	}

	/**
	 * Заблокировать кнопку добавления задачи
	 */
	disableAddStage() {
		this.stagesOfferBlock.find(this.selectors.stage.add).addClass(this.classes.disableAddStage);
	}

	/**
	 * Разблокировать кнопку добавления задачи
	 */
	enableAddStage() {
		this.stagesOfferBlock.find(this.selectors.stage.add).removeClass(this.classes.disableAddStage);
	}

	/**
	 * Разблокировать ли кнопку добавления задач. Валидация всех задач без отображения ошибок
	 * @param {boolean} notChangeShowErrors
	 * @returns {boolean}
	 */
	isEnableAddStage(notChangeShowErrors) {
		this.validateStages(notChangeShowErrors);
		if (this.errorsStage.length || this.checkSumTotalPrice() || !this.checkStageCountLimit()) {
			this.disableAddStage();

			return false;
		} else {
			this.enableAddStage();

			return true;
		}
	}

	/**
	 * Построить html задачи
	 * @param {number} position
	 * @param {number} numberId
	 * @returns {string}
	 */
	buildStage(position, numberId) {

		let htmlDefault = this.stagesOfferBlock.find(this.selectors.stage.itemDefault).html();
		this.stagesOfferBlock.find(this.selectors.stages).append(htmlDefault);

		let stageHtml = this.stagesOfferBlock.find(this.selectors.stages).children().eq(position - 1);

		stageHtml
			.data('number', numberId)
			.attr('data-number', numberId)
			.data('position', position)
			.attr('data-position', position)
			.attr('data-name', 'stage-' + numberId + '-title')
			.addClass(this.classes.stageItem);
		stageHtml.find(this.selectors.stage.number).text(numberId + '.');
		stageHtml.find(this.selectors.stage.title).attr('name', 'stages[' + numberId + '][title]');
		stageHtml.find(this.selectors.stage.price).attr('name', 'stages[' + numberId + '][payer_price]')
			.attr('placeholder', this.options.offer.stageMinPrice === this.options.offer.customMaxPrice ? this.options.offer.stageMinPrice : Utils.priceFormat(this.options.offer.stageMinPrice) + ' - ' + Utils.priceFormat(this.options.offer.customMaxPrice - this.totalPrice));
		stageHtml.show();

		stageHtml.addClass(this.classes.stageEdit);

		if (window.offerForm) {
			window.offerForm.initEventListeners(this.stagesOfferBlock);
		}
	}
	
	/**
	 * Меняем плесходлдер в зависимости от введенных стоимостей других задач
	 * @returns {Void}
	 */
	calcPlaceholder() {
		var placeholderText = '';
		var stageMinPrice = this.options.offer.stageMinPrice;
		var customMaxPrice = this.options.offer.customMaxPrice;
		if (stageMinPrice === customMaxPrice || customMaxPrice - this.totalPrice <= stageMinPrice) {
			placeholderText = stageMinPrice;
		} else {
			placeholderText = Utils.priceFormat(stageMinPrice) + ' - ' + Utils.priceFormat(customMaxPrice - this.totalPrice);
		}
		$(this.selectors.stage.price).attr('placeholder', placeholderText)
	}

	/**
	 * Заполнить данными задачу
	 * @param {Object} values
	 * @param {number} values.number - порядковый номер задачи
	 * @param {string} values.title - название задачи
	 * @param {string} values.payer_price - стоимость задачи
	 */
	setDataStage(values) {
		let $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-number='" + values.number + "']");
		$stage
			.data('id', values.id)
			.attr('data-id', values.id);
		$stage.find(this.selectors.stage.title).val(values.title);
		$stage.find(this.selectors.stage.titleText).html(values.title);
		$stage.find(this.selectors.stage.titleEditor).html(values.title);
		$stage.find(this.selectors.stage.price).val(parseInt(values.payer_price));
		$stage.find(this.selectors.stage.priceText).text(Utils.priceFormat(values.payer_price));
		$stage.removeClass(this.classes.stageEdit);
	}

	/**
	 * Проставить плейсхолеры для чистых задач для покупателя, если в предложении не было задач
	 */
	setPlaceholderTitle() {
		this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='1']").find(this.selectors.stage.title).attr('placeholder', t('Первая задача'));
		this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='2']").find(this.selectors.stage.title).attr('placeholder', t('Вторая задача'));
		this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='3']").find(this.selectors.stage.title).attr('placeholder', t('Третья задача'));
	}

	/**
	 * Обноление плейсхолдера для цены задачи
	 * Актуально в диалогах, когда меняется категрия. В зависимости от категрии может быть разная минимальная стоимость задачи
	 */
	updatePlaceholderPrice() {
		$.each($(this.selectors.stage.item), (k, v) => {
			let stage = $(v);
			let stageId = stage.data('position');
			stage.find(this.selectors.stage.price).attr('name', 'stages[' + stageId + '][payer_price]')
				.attr('placeholder', this.options.offer.stageMinPrice === this.options.offer.customMaxPrice ? this.options.offer.stageMinPrice : Utils.priceFormat(this.options.offer.stageMinPrice) + ' - ' + Utils.priceFormat(this.options.offer.customMaxPrice));
		});
	}

	/**
	 * Обновление контролов управления задачи
	 */
	updateControlHide() {
		// для всех задач показываем кнопку удаления
		this.stagesOfferBlock.find(this.selectors.stage.remove).removeClass('hidden');

		// на странице трэка покупатель не может удалить первую задачу при редактировании, если минимально возможной стоимости > 0
		// или на бирже и диалогах первую задачу удалить нельзя
		if (
			this.options.pageType === this.PAGE_TRACK && this.options.offer.customMinPrice > 0
			|| this.options.pageType !== this.PAGE_TRACK
		) {
			this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]').find(this.selectors.stage.remove).addClass('hidden');
		}

		// покупатель должен заполнить минимум 2 задачи при выборе задачной оплаты на бирже или в диалогах
		if (this.options.actorType === this.ACTOR_TYPE_PAYER && this.options.pageType !== this.PAGE_TRACK) {
			this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="2"]').find(this.selectors.stage.remove).addClass('hidden');
		}
	}

	getMaxAndMinNumber() {
		let maxNumber = 0;
		let minNumber = this.stagesOfferBlock.find(this.selectors.stage.item).eq(0).data('number');
		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			let curNumber = $(v).data('number');
			if (parseInt(curNumber) > parseInt(maxNumber)) {
				maxNumber = curNumber;
			}

			if (parseInt(curNumber) < parseInt(minNumber)) {
				minNumber = curNumber;
			}
		});

		return {maxNumber: parseInt(maxNumber), minNumber: parseInt(minNumber)};
	}

	/**
	 * Обновить селект порядквых номеров задачи
	 * @param {Object} $stage
	 */
	updateNumberSelectStage($stage) {
		let $select = $stage.find('select');
		let options = '';
		let stageId = parseInt($stage.data('number'));

		if ($select.val() === stageId) {
			return;
		}

		$select.html('');

		let {minNumber, maxNumber} = this.getMaxAndMinNumber();

		for (let i = minNumber; i <= maxNumber; i++) {
			let id = i;
			let position = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + i + '"]').data('position');
			options += '<option data-position="' + position + '" value="' + id + '"' + (stageId === id ? "selected" : "") + '>' + id + '</option>';
		}

		$select.append(options);
	}

	/**
	 * Показать селект выбора порядкового номера задачи
	 * @param {Object} $stage
	 */
	showNumberSelectStage($stage) {
		// на странице трэка нельзя менять порядковый номер задачи
		if (this.stagesOfferBlock.find(this.selectors.stage.item).length > 1 && this.options.pageType !== this.PAGE_TRACK) {
			$stage.find(this.selectors.stage.numberSelect).removeClass('hidden');
			$stage.find(this.selectors.stage.number).addClass('hidden');
		}
	}

	/**
	 * Скрыть селект выбора порядкового номера задачи
	 * @param {Object} $stage
	 */
	hideNumberSelectStage($stage) {
		$stage.find(this.selectors.stage.numberSelect).addClass('hidden');
		$stage.find(this.selectors.stage.numberSelect).val([]);
		$stage.find(this.selectors.stage.number).removeClass('hidden');
	}

	/**
	 * Сохранить новый порядковый номер задачи
	 * @param {Object} $stage
	 */
	saveNumberSelectStage($stage) {
		let $select = $stage.find('select');
		let newNumber = parseInt($select.val());

		if (isNaN(newNumber)) {
			newNumber = $stage.data('number');
		}

		let {minNumber, maxNumber} = this.getMaxAndMinNumber();

		if (maxNumber < newNumber) {
			newNumber = maxNumber;
		}

		let newPosition = parseInt($select.find('option[value="' + newNumber + '"]').data('position'));

		this.updateSortStages($stage, newNumber, newPosition);
		this.updateControlHide();
	}

	/**
	 * Обновление сортировки задач
	 * @param {Object} $stage
	 * @param {number} newNumber
	 * @param {number} newPosition
	 */
	updateSortStages($stage, newNumber, newPosition) {
		let oldNumber = $stage.data('number');

		OfferStages.setStageNewNumber($stage, newNumber);
		OfferStages.setStageNewPosition($stage, newPosition);
		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			let $curStage = $(v);
			let curNumber = parseInt($curStage.data('number'));
			let curPosition = parseInt($curStage.data('position'));

			if (curNumber < oldNumber && curNumber >= newNumber) {
				OfferStages.setStageNewNumber($curStage, curNumber + 1);
				OfferStages.setStageNewPosition($curStage, curPosition + 1);
			}

			if (curNumber > oldNumber && curNumber <= newNumber) {
				OfferStages.setStageNewNumber($curStage, curNumber - 1);
				OfferStages.setStageNewPosition($curStage, curPosition - 1);
			}
		});

		this.updateStageNumber();

		this.reBuildStages();
	}

	/**
	 * Устанавливаем новый номер задачи
	 * @param {Object} $stage
	 * @param {number} number
	 */
	static setStageNewNumber($stage, number) {
		$stage
			.attr('data-new-number', number)
			.data('new-number', number);
	}

	/**
	 * Устанавливаем новую позицию задачи
	 * @param {Object} $stage
	 * @param {number} position
	 */
	static setStageNewPosition($stage, position) {
		$stage
			.attr('data-new-position', position)
			.data('new-position', position);
	}

	/**
	 * Обновляем номера задач
	 */
	updateStageNumber() {
		this.stagesOfferBlock.find(this.selectors.stage.item).each((k, v) => {
			let $stage = $(v);
			let number = $stage.data('new-number') || $stage.data('number');
			let position = $stage.data('new-position') || $stage.data('position');
			$stage
				.attr('data-position', position)
				.data('position', position)
				.attr('data-number', number)
				.data('number', number);
			$stage.find(this.selectors.stage.number).text(number + '.');
			$stage.find(this.selectors.stage.title).attr('name', 'stages[' + number + '][title]');
			$stage.find(this.selectors.stage.price).attr('name', 'stages[' + number + '][payer_price]');
		});
	}

	/**
	 * Перестраиваем задачи по высоте
	 */
	reBuildStages() {
		let heightStages = 0;

		for (let i = 1; i <= this.stagesOfferBlock.find(this.selectors.stage.item).length; i++) {
			let $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter("[data-position='" + i + "']");
			let heightName = $stage.find(this.selectors.stage.titleBlock).height();
			let heightError = $stage.find(this.selectors.stage.error).height();
			let heightStage = $(window).width() < 768 ? 40 : 40;

			// если задача не редактируется и высота названия больше заданной
			if (!$stage.hasClass(this.classes.stageEdit) && heightName > heightStage) {
				heightStage = heightName;
			}

			// если задача редактируется
			if ($stage.hasClass(this.classes.stageEdit)) {
				heightStage = $(window).width() < 768 ? 80 : 40;
			}

			let heightInfo = heightStage;

			// если есть ошибки в задаче
			if ($stage.hasClass(this.classes.stageError)) {
				heightInfo = $(window).width() < 768 ? 80 : 40;
				heightStage = heightInfo + heightError;
			}

			heightStages = heightStages + heightStage;

			$stage.css({top: heightStages - heightStage, height: heightStage + 'px'});
			$stage.find(this.selectors.stage.infoBlock).css({height: heightInfo});
		}

		this.stagesOfferBlock.find(this.selectors.stages).css({height: heightStages})
	}

	/**
	 * Для некотрых заказов нельзя вводить кириллицу
	 * @param $input
	 */
	controlLang($input) {

		if (this.options.controlEnLang) {
			let newValue = $input.val().replace(/[А-Яа-яЁё]/g, '');
			$input.val(newValue);
		}
	}

	/**
	 * Добавление ошибки в массив
	 * @param {Object} values
	 */
	addErrorStage(values) {
		this.removeErrorStage(values);

		this.errorsStage.push(values);
	}

	/**
	 * Удаляем ошибки из массива
	 * @param {Object} values
	 */
	removeErrorStage(values) {
		this.errorsStage = this.errorsStage.filter(function (v) {
			return !(v.target === values.target && v.position === values.position);
		});
	}

	/**
	 * Показать ошибки задач с бэка
	 * @param {Array} errors
	 * @param {boolean} isScrollToError
	 */
	showBackendStageErrors(errors, isScrollToError = true) {
		if (errors.length) {
			this.defaultNotChangeShowErrorsBlock();

			$.each(errors, (k, v) => {
				if (v.target === 'price') {
					this.showTotalPriceError(v.text);

					return true;
				}

				// если ошибка при изменении срока покупателем
				if (v.target === 'days') {
					this.showDurationChangeError(v.text, 'red');

					return true;
				}
				this.showStageError(v);
				this.editStage(this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + v.position + '"]'));
			});

			if (isScrollToError) {
				OfferStages.scrollToError(this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + errors[0].position + '"]'));
			}
		}
	}

	/**
	 * Показать ошибки задач
	 */
	showAllStageErrors() {
		if (this.notChangeShowErrorsBlock || !this.errorsStage.length) {
			return;
		}

		$.each(this.errorsStage, (k, v) => {
			this.showStageError(v);

			// если ошибка в цене
			if (v.target === 'payer_price') {
				this.showTotalPriceError('');
			}
		});

		this.reBuildStages();
	}

	/**
	 * Отображение ошибки для текущего изменяемого поля
	 * @param {Object} $input
	 */
	showCurrentError($input) {
		let currentTarget = $input.data('target');
		let currentPosition = $input.parents(this.selectors.stage.item).data('number');
		let currentError = this.errorsStage.filter((item) => {
			return item.target === currentTarget && item.position === currentPosition;
		});

		this.hideStageInputError($input);
		if (currentError.length) {
			this.showStageError(currentError[0]);
		}

		this.reBuildStages();
	}

	/**
	 * Показать одну ошибку задачи
	 * @param {Object} values
	 */
	showStageError(values) {
		let position = values.position || 1;
		let $stage = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-number="' + position + '"]');
		$stage.addClass(this.classes.stageError);
		$stage.find('input[data-target="' + values.target + '"]').addClass('error');
		$stage.find('select[data-target="' + values.target + '"]').addClass('error');
		$stage.find(this.selectors.stage.error).find('[data-target="' + values.target + '"]').html(values.text.replace(/\.+\s*$/, '') + '. ');
	}

	/**
	 * Скрыть ошибку задачи
	 * @param {Object} $input
	 */
	hideStageInputError($input) {
		let $stage = $input.parents(this.selectors.stage.item);

		if (!this.notChangeShowErrorsBlock) {
			// убираем отображение ошибки
			$input.removeClass('error');
			$stage.find(this.selectors.stage.error).find('[data-target="' + $input.data('target') + '"]').html('');

			if ($stage.find(this.selectors.stage.error).find('span').text() === '') {
				$stage.removeClass(this.classes.stageError);
			}

			this.reBuildStages();
		}
	}

	/**
	 * Блокирвать ли кнопку отправки формы
	 * @returns {boolean}
	 */
	checkDisableButtonSend() {
		return this.errorsStage.length !== 0 || this.isDisableButtonSend || this.durationError;
	}

	/**
	 * Скролл до задачи с ошибкой
	 * @param {Object} $stage
	 */
	static scrollToError($stage) {
		if ($stage.length) {
			$('html, body').animate({
				scrollTop: $stage.offset().top - $('.header').height() - 23
			}, 'slow');
		}
	}

	/**
	 * Показать ошибку для изменения срока для покупателя
	 * @param {string} error
	 * @param {string} level
	 */
	showDurationChangeError(error, level) {
		this.stagesOfferBlock.find(this.selectors.durationChangeError).text(error);

		if (level === 'red') {
			this.stagesOfferBlock.find(this.selectors.duration).addClass(this.classes.durationError);
			this.durationError = true;
		} else {
			this.stagesOfferBlock.find(this.selectors.duration).addClass(this.classes.durationErrorLight);
		}
	}

	/**
	 * Скрыть ошибку для изменения срока для покупателя
	 */
	hideDurationChangeError() {
		this.durationError = false;

		this.stagesOfferBlock.find(this.selectors.durationChangeError).text('');
		this.stagesOfferBlock.find(this.selectors.duration).removeClass(this.classes.durationError)
			.removeClass(this.classes.durationErrorLight);
	}

	/**
	 * Показать ошибку итоговой стоимости
	 * @param {string} error
	 */
	showTotalPriceError(error) {
		if (this.notChangeShowErrorsBlock) {
			return;
		}

		// если задача одина, то отображать ошибку итоговой стоимости под этой задачи
		if (this.stagesOfferBlock.find(this.selectors.stage.item).length === 1) {
			let firstStageNumber = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]').data('number');

			// если задача одина, то отображать ошибку под этой задачи
			this.showStageError({
				position: firstStageNumber,
				target: 'payer_price',
				text: error,
			});

			this.addErrorStage({
				position: firstStageNumber,
				target: 'payer_price',
				text: error,
			});
			this.reBuildStages();

			// при добавлении 2ой задачи нужно ошибку около 1ой задачи убирать
			this.hideTotalPriceError();
		} else {
			$(this.selectors.totalPrice.wrap).addClass('error');
			$(this.selectors.totalPrice.error).html(error);
			$(this.selectors.totalPrice.priceCommission).addClass('hidden');

			// если задач больше чем один, то скрывать ошибку итоговой стоимости под этих задач
			this.hideTotalPriceErrorByFirstStage();
		}

	}
	/**
	 * Скрыть ошибку итоговой цены
	 */
	hideTotalPriceError() {
		if (this.notChangeShowErrorsBlock) {
			return;
		}

		$(this.selectors.totalPrice.wrap).removeClass('error');
		$(this.selectors.totalPrice.error).html('');
	}

	/**
	 * Скрыть ошибку итоговой цены по первой задачи
	 */
	hideTotalPriceErrorByFirstStage() {
		let $firstStage = this.stagesOfferBlock.find(this.selectors.stage.item).filter('[data-position="1"]');
		let firstStageNumber = $firstStage.data('number');

		this.hideStageInputError($firstStage.find(this.selectors.stage.price));
		this.removeErrorStage({
			position: firstStageNumber,
			target: 'payer_price',
		});
	}

	/**
	 * Привести к дефолтному значению переменную notChangeShowErrorsBlock
	 */
	defaultNotChangeShowErrorsBlock() {
		this.notChangeShowErrorsBlock = false;
	}

	/**
	 * Заблокировать кнопку отправки
	 */
	disableButtonSend() {
		$(this.options.classDisableButton).prop('disabled', true).addClass('disabled');
		this.setButtonSend(true);
	};

	/**
	 * Разблокировать кнопку отправки
	 */
	enableButtonSend() {
		$(this.options.classDisableButton).prop('disabled', '').removeClass('disabled');
		this.setButtonSend(false);
	};

	/**
	 * Установить значение для переменной isDisableButtonSend
	 * @param value
	 */
	setButtonSend(value) {
		this.isDisableButtonSend = value;
	}
}

window.offerStages = new OfferStages();
