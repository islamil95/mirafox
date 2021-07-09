export default class {
	constructor(selector, args = {}) {
		this.onUpdate = args.onUpdate;

		this.formEl = $(selector);
		this.bottomError = this.formEl.find('.vf-bottom-error');

		this.textCheckErrors = {
			'bad_words': t('Исключите запрещенные слова: '),
			'duplicate_symbols': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
			'big_word': t('Превышена максимальная длина слов'),
			'small_word': t('Текст не соответствует нормам русского языка.\nОтредактируйте слова, подчеркнутые красным.'),
			'word_mistakes': t('Необходимо исправить ошибки или опечатки в тексте.\nСлова с ошибками подчеркнуты красным.'),
		};

		this.plannedCheck = {};
		this.textCheckTimeout = null;
		this.textCheckXhr = null;

		this.fields = {};
		this.isBusy = false;

		this.initEventListeners();
	}

	isValidated(fieldNames) {
		if (this.isBusy) {
			return false;
		}
		let validated = true;
		$.each(this.fields, (k, v) => {
			if (fieldNames) {
				if ($.inArray(k, fieldNames) == -1) {
					return true;
				}
			}
			if (v.status != 'success') {
				validated = false;
				return false;
			}
		});
		return validated;
	}

	initEventListeners(startEl) {
		let rootEl = this.formEl;
		if (startEl) {
			rootEl = startEl;
		}
		rootEl.find('.vf-block').each((k, v) => {
			let block = $(v);
			let name = block.data('name');
			if (!name) {
				return true;
			}
			let initialized = block.data('initialized');
			if (initialized) {
				return true;
			}
			let el = block.find('.vf-field');
			let isLong = (el.data('mistakePercentLong') === true ? 1 : 0);
			let noHint = (el.data('noHint') ? 1 : 0);
			let fieldId = el.data('fieldId');
			let errorOutputToData = false;
			let errorOutputDataBlock = null;
			let errorOutputSelector = block.data('errorOutput');
			if (errorOutputSelector) {
				errorOutputDataBlock = block.find(errorOutputSelector);
				if (errorOutputDataBlock.length) {
					errorOutputToData = true;
				} else {
					errorOutputDataBlock = null;
				}
			}
			let errorOutput = block.find('.vf-error');
			let errorNoHide = (errorOutput.length > 0 ? errorOutput.hasClass('no-hide') : false);
			let en = el.data('en');
			this.fields[name] = {
				block: $(v),
				errorOutput: errorOutput,
				errorNoHide: errorNoHide,
				el: el,
				isLong: isLong,
				noHint: noHint,
				fieldId: fieldId,
				status: 'success',
				en: en,
				errorOutputToData: errorOutputToData,
				errorOutputDataBlock: errorOutputDataBlock,
			};
						
			el.on('input', () => {
				this.checkField(name);
			});

			block.data('initialized', true);
		});
	}

	checkField(name) {
		let field = this.fields[name];

		// Скрываем текст ошибки
		if (!field.errorOutputToData && (!field.errorNoHide || field.errorOutput.data('isTemp')) && field.errorOutput.length > 0) {
			field.errorOutput.text('');
		}

		this.isBusy = true;
		if (this.onUpdate) {
			this.onUpdate();
		}

		if (this.textCheckXhr) {
			this.textCheckXhr.abort();
		}
		let html = field.el.html();

		let fData = {
			string: html,
			isLong: field.isLong,
			noHint: field.noHint,
		};
		if (field.fieldId) {
			fData.field = field.fieldId;
		}
		this.plannedCheck[name] = fData;
		if (this.textCheckTimeout) {
			clearTimeout(this.textCheckTimeout);
			this.textCheckTimeout = null;
		}
		this.textCheckTimeout = setTimeout(() => {
			let data = {
				data: this.plannedCheck,
				lang: (field.en ? 'en' : 'ru'),
				checkAll: true,
			};
			this.textCheckXhr = $.post('/api/kwork/checktext', data, (r) => {
				this.textCheckXhr = null;
				this.plannedCheck = {};

				// Скрываем текст ошибки
				if (!field.errorOutputToData) {
					if (field.errorNoHide && field.errorOutput.length > 0) {
						field.errorOutput.text('');
					}
				} else {
					field.errorOutputDataBlock.data('backendError', '');
				}

				if (!('data' in r)) {
					return;
				}

				let sel = rangy.getSelection();
				let ranges = sel.getAllRanges();
				let range = _.last(ranges);
				let editor = null;
				if (range) {
					let rangeEl = $(range.startContainer);
					editor = rangeEl.closest('.js-content-editor');
				}

				var needCursorSave = false;
				if (editor && editor.length > 0) {
					needCursorSave = true;
				}

				// если курсор находится в поле для валидации (не в обычном input, например)
				let keepedSelection = null;
				if (needCursorSave) {
					// Сохраняем позицию курсора
					try {
						keepedSelection = rangySelectionSaveRestore.saveSelection();
					} catch (e) {}
				}

				this.isBusy = false;
				this.handleBackendValidation(r);

				if (needCursorSave) {
					// восстанавливаем курсор
					if (keepedSelection) {
						try {
							rangySelectionSaveRestore.restoreSelection(keepedSelection);
						} catch (e) {}
					}
				}
			});
		}, 1000);
	}

	reset() {
		this.formEl.find('.vf-error').text('');
	}

	handleBackendValidation(r) {
		let bottomErrorText = r.error || r.response || '';
		let data = r.data || r.errorsDetail || r.errors || [];

		$.each(data, (k, v) => {
			let target = v.target || k;
			let errorText = '';

			// Орфографические ошибки
			let validError = v.validError || v.text || '';
			if (validError) {
				errorText = validError;
				if (validError in this.textCheckErrors) {
					errorText = this.textCheckErrors[validError];
				}
			}

			// Наличие недопустимых слов
			if('badWords' in v && v.badWords) {
				errorText = this.textCheckErrors['bad_words'] + v.string;
			}
			
			// Если поля нет - переводим ошибку под кнопку отправки
			if (!(target in this.fields)) {
				if (errorText.length > 0) {
					bottomErrorText += '<div>' + errorText + '</div>';
				}
				return true;
			}
			let field = this.fields[target];
			
			// Визуализируем ошибки
			let elHtml = field.el.html();
			let mistakes = v.mistakes || [];
			let newHtml = applyWordErrors(elHtml, mistakes);
			field.el.html(newHtml);
		
			// Меняем статус поля
			field.status = (errorText.length > 0 ? 'error' : 'success');

			if (!field.errorOutputToData) {
				if (field.errorOutput.length > 0) {
					field.errorOutput.html(errorText).data('isTemp', false);
				} else {
					bottomErrorText += '<div>' + errorText + '</div>';
				}
			} else {
				field.errorOutputDataBlock.data('backendError', errorText);
				field.errorOutputDataBlock.trigger('input');
				// Скрываем текст ошибки
				if ((!field.errorNoHide || field.errorOutput.data('isTemp')) && field.errorOutput.length > 0) {
					field.errorOutput.text('');
				}
			}
		});

		if (bottomErrorText.length > 0 && this.bottomError.length > 0) {
			this.bottomError.html(bottomErrorText);
		}

		if (this.onUpdate) {
			this.onUpdate();
		}
	}
}