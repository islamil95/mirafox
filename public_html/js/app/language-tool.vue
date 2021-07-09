<template>
	<b-modal v-model="modalShow" :title="t('Проверка ошибок')" size="lg" no-fade>
		<!-- Инструмент исправления ошибок -->		
		<div class="container language-tool">
			<div class="row" v-show="hasPotentialErrors">
				<div class="col-8 px-0">
					<!-- Текст с выделенными ошибками -->
					<textarea id="language-tool"></textarea>
				</div>
				<div class="col-4">
					<div v-if="selectedError">
						<!-- Описание ошибки -->
						<div class="message">
							{{ selectedError.message }}
						</div>
						<!-- Список замен для выбранной ошибки -->
						<div v-for="(replacement, index) in selectedError.replacements" 
							@click="applyReplacement(index)" class="replacement">
							{{ replacement }}
						</div>
						<!-- Кнопка Пропустить ошибку -->
						<div class="ignore" @click="applyReplacement(-1)">
							{{ t("Пропустить") }}
						</div>
					</div>
				</div>
			</div>
			<!-- Сообщение об отсутствии ошибок -->
			<div class="row" v-show="!hasPotentialErrors">
				<div class="col-12">
					{{ t("Проверка ошибок завершена") }}.
				</div>
			</div>
		</div>
		<!-- Кнопки -->
		<div slot="modal-footer" v-show="hasPotentialErrors" class="language-tool-footer">
			<!-- Назад -->
			<button class="button button-gray button-sm" @click="nextField(false)" :disabled="loading">
				{{ t("Предыдущее поле") }}
			</button>
			<!-- Далее -->
			<button class="button button-gray button-sm" @click="nextField(true)" :disabled="loading">
				{{ t("Следующее поле") }}
			</button>
			<!-- Отмена -->
			<button class="button button-gray button-sm" @click="discardChanges" :disabled="loading">
				{{ t("Отменить правки") }}
			</button>
		</div>
	</b-modal>
</template>

<script>

/**
 * Компонент позволяет проверить набор полей на офрографические и грамматические
 * ошибки при помощи LanguageTool. После запуска открывается модальное окно, в котором
 * отображаются 1) текст из проверяемого поля 2) кнопки для перехода к следующему/предыдущему
 * полю. В тексте выделены ошибки (красным, желтым или синим, в зависимости от важности). При
 * клике на ошибку справа от текста появляется описание ошибки и варианты исправлений. Пользователь
 * может как выбрать один из предложенных вариантов, так и исправить ошибку вручную. При переходе
 * к следующему/предыдущему полю исправленный текст записывается обратно в поле, из которого был
 * взят. Также пользователь может откатить все правки в данном поле, нажав на соответствующую
 * кнопку.
 *
 * Если во всех проверяемых полях процент "красных" и "желтых" ошибок будет меньше 30%,
 * процесс проверки не запустится и будет сгенерировано событие общей шины 
 * language-tool-check-passed, иначе language-tool-check-failed.
 *
 * Обязательные свойства:
 *   fieldIds - Ид полей для проверки через |. Порядок проверки полей будет определяться 
 *     по этому свойству
 *   formattedFieldIds - Ид полей с форматированием (редактор Trumbowyg) через |. 
 *     Порядок не играет роли
 *   lang - Язык, для которого проверяются ошибки (например, en-US)
 *   server - Сервер LanguageTool
 *
 * Запустить проверку можно, сгенерировав событие общей шины start-language-tool:
 *   window.bus.$emit("start-language-tool");
 *
 * При изменении поля в процессе правки ошибок, компонент генерирует событие общей
 * шины language-tool-field-changed и передает в него объект вида:
 * {
 *   id: "input-title",
 *   formatted: false,
 *   value: "some text", 
 * }
 */

// Модальные диалоги
import { BModal, VBModal } from "bootstrap-vue";
Vue.component("b-modal", BModal);
// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Поля для проверки в формате
			// [ {id: "...", formatted: false}, {...} ]
			fields: [],
			// Индекс поля, которое проверяется в данный момент
			selectedFieldIndex: 0,
			// Текущее направление перебора полей - вперед (True) или назад (False)
			iterateForward: true,
			// LanguageTool Url
			url: "",
			// Ошибки, которые нам не интересны
			// (в частности, которые возникают при замене тэгов на пробелы)
			ignoredMistakes: [
				"WHITESPACE_RULE", // Двойной пробел
				"COMMA_PARENTHESIS_WHITESPACE", // Пробел перед точкой
			],
			// Ошибки
			errors: [],
			// Выбранная ошибка
			selectedError: null,
			// Максимальное кол-во предлагаемых замен
			maxReplacementCount: 5,
			// Наличие потенциальных ошибок в тексте 
			// (или ошибка найдена, или какое-либо поле еще не проверено)
			hasPotentialErrors: true,
			// Процент текста, содержащего ошибки, при котором запускается
			// процесс правки ошибок пользователем
			minTextPercentToCorrect: 0.3,
			// Переменная для открытия/зарытия модального окна
			modalShow: false,
			// Идет проверка
			loading: false,
			// Локализация компонента
			i18n: {
				en: {
					"Проверка ошибок": "Spelling and Grammar",
					"Следующее поле": "Next Field",
					"Предыдущее поле": "Previous field",
					"Отменить правки": "Undo edit",
					"Проверка ошибок завершена": "The spell check is complete",
					"Пропустить": "Ignore",
				}
			},			
		};
	},

	props: {

		// Ид полей для проверки через |. Порядок проверки полей будет
		// определяться по этому свойству
		fieldIds: {
			type: String,
			default: "",
		},

		// Ид полей с форматированием (редактор Trumbowyg) через |. Порядок
		// не играет роли
		formattedFieldIds: {
			type: String,
			default: "name",
		},

		// Язык, для которого проверяются ошибки
		lang: {
			type: String,
			default: "en-US",
		},

		// Сервер LanguageTool
		server: {
			type: String,
			default: "https://langtool-dev.kwork.ru",
		},

	},

	/**
	 * Crteated event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
		// Сформировать url для проверок через сервер LanguageTool
		this.url = this.server + "/v2/check";
		// Распарсить свойства fieldIds и formattedFieldIds в массив вида:
		// [ {id: "...", formatted: false}, {...} ]
		var self = this;
		var fieldIds = this.fieldIds.toLowerCase().split("|");
		var formattedFieldIds = this.formattedFieldIds.toLowerCase().split("|");
		_.forEach(fieldIds, function (fieldId) {
			self.fields.push({
				id: fieldId,
				formatted: formattedFieldIds.indexOf(fieldId) >= 0,
			});
		});
		// Подписаться на событие выбора ошибки в редекторе Trumbowyg
		window.bus.$on("error-selected", this.errorSelected);
		// Подписаться на событие запуска проверки ошибок
		window.bus.$on("start-language-tool", this.init);
	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Инициализирвоать редактор Trumbowyg
        $("#language-tool").trumbowyg({
            fullscreenable: false,
            closable: false,
            btns: [],
            removeformatPasted: true
        });		
	},

	methods: {

		/**
		 * Обработчик события на выбор ошибки в редакторе Trumbowyg
		 * @param {int} errorId
		 */
		errorSelected: function (errorId) {
			this.selectedError = _.find(this.errors, ["id", parseInt(errorId)]);
		},

		/**
		 * Выбрать ошибку
		 * @param  {int} errorId
		 */
		selectError: function (errorId) {
			var selector = "word-error[data-error-id=" + errorId + "]";
  			$(selector).addClass("selected");
  			window.bus.$emit("error-selected", errorId);
		},

		/**
		 * Удалить ошибку (была произведена замена)
		 * @param {int} errorId
		 */
		deleteError: function (errorId) {
			// Удалить ошибку
			_.remove(this.errors, function (error) {
				return error.id == errorId;
			});
			// Если была удалена текущая выбранная ошибка
			if (this.selectedError.id == errorId) {
				// Сбросить выбранную ошибку
				this.selectedError = null;
				// Удалить ошибки из массива errors, которых больше нет
				// в самом редакторе в тэгах word-error (пользователь мог
				// удалить их вручную вместе с прочим текстом)
				this.deleteErrorsNotPresentInTheEditor();				
				// Если ошибки еще остались
				if (this.errors.length > 0) {
					// Перейти к первой ошибке из оставшихся
					this.selectError(this.errors[0].id);
				}
			}
		},

		/**
		 * Удалить ошибки из массива errors, которых больше нет
		 * в самом редакторе в тэгах word-error (пользователь мог
		 * удалить их вручную вместе с прочим текстом)
		 */
		deleteErrorsNotPresentInTheEditor: function () {
			var self = this;
			// Получить текст из редактора
			var text = $("#language-tool").trumbowyg("html");
			// Удалить ошибки
			_.remove(this.errors, function (error) {
				var pattern = self.getRegExpErrorPattern(error.id);
				return text.search(pattern) == -1;
			});
		},

		/**
		 * Сформировать паттерн для поиска ошибок по ид
		 * @param {int} errorId
		 * @return {string}
		 */
		getRegExpErrorPattern: function (errorId) {
			return new RegExp("<word-error[^<]+?data-error-id=['\"]" + errorId + "['\"]>(.*?)<\\/word-error>");			
		},

		/**
		 * Заменить ошибку на выбранный вариант исправления
		 * @param {int} replacementId если -1, игнорировать ошибку
		 */
		applyReplacement: function (replacementId) {
			// Получить текст из редактора
			var text = $("#language-tool").trumbowyg("html");
			var pattern = this.getRegExpErrorPattern(this.selectedError.id);
			// Пользователь выбрал вариант исправления
			if (replacementId >= 0) {
				// Заменить тэг с ошибкой на вариант исправления
				text = text.replace(pattern, this.selectedError.replacements[replacementId]);
			} else {
				// Пользователь попросил пропустить ошибку
				// Получить текст внутри тэга с ошибкой
				var matches = text.match(pattern);
				if (matches && matches.length >= 2) {
					var error = text.match(pattern)[1];
					// Заменить тэг с ошибкой на исходную ошибку (удалить тэги)
					text = text.replace(pattern, error);
				}
			}
			// Вернуть исправленный текст в редактор
			$("#language-tool").trumbowyg("html", text);
			// Привязать обработчик события по клику к тэгам, 
			// которыми выделены ошибки
  			this.bindEventsToErrorTags();
  			// Удалить текущую ошибку
  			this.deleteError(this.selectedError.id)
		},

		/**
		 * Привязать обработчик события по клику к тэгам, 
		 * которыми выделены ошибки
		 */
		bindEventsToErrorTags: function () {
  			$("word-error").click(function (event) {
  				// Определить ид ошибки
  				var errorId = event.currentTarget.dataset.errorId;
  				// Снять выделение со всех ошибок
  				$("word-error").removeClass("selected");
  				// Выделить текущую ошибку
  				$(event.currentTarget).addClass("selected");
  				// Сообщить vue, что в редакторе выбрана ошибка с ид errorId
  				window.bus.$emit("error-selected", errorId);
  			});
		},

		/**
		 * Сохранить все правки в поле
		 * @param {object} field
		 */
		commitChanges: function (field) {
			// Получить текст из редактора
			var text = $("#language-tool").trumbowyg("html");
			// Убрать тэги ошибок
			text = this.stripErrorTags(text);
			// Записать текст в поле
			this.setValue(this.fields[this.selectedFieldIndex], text);
			// Сгенерировать событие общей шины об изменении поля
			window.bus.$emit("language-tool-field-changed", {
				id: field.id,
				formatted: field.formatted,
				value: text,
			});
		},

		/**
		 * Отменить все правки в текущем поле
		 */
		discardChanges: function () {
			this.check(this.getValue(this.fields[this.selectedFieldIndex]));
		},

		/**
		 * Перейти к проверке следующего поля
		 * @param {bool} iterateForward - направление перебора полей, 
		 * True - вперед, False - назад
		 */
		nextField: function (iterateForward) {
			var selectedField = this.fields[this.selectedFieldIndex];
			// Сохранить изменения в текущем поле
			if (selectedField.has_potential_errors) {
				this.commitChanges(selectedField);
			}
			// Если ошибок больше нет
			if (this.potentialErrorsExist()) {
				this.hasPotentialErrors = false;
				return;
			}
			// Сохранить текущее направление проверки
			this.iterateForward = iterateForward;
			// Получить индекс нового поля для проверки
			if (iterateForward) {
				this.selectedFieldIndex = this.selectedFieldIndex == this.fields.length - 1 ? 
					0 : this.selectedFieldIndex + 1;
			} else {
				this.selectedFieldIndex = this.selectedFieldIndex == 0 ? 
					this.fields.length - 1 : this.selectedFieldIndex - 1;
			}
			var newSelectedField = this.fields[this.selectedFieldIndex];
			// Если в новом поле еще есть потенциальные ошибки, проверить
			if (newSelectedField.has_potential_errors) {
				this.check(this.getValue(newSelectedField));
			} else {
				// Иначе перейти к следующему полю
				this.nextField(iterateForward);
			}
		},

		/**
		 * Возвращает True, если ни в одном из полей не осталось
		 * потенциальных ошибок (т.е. все поля проверены, исправлены
		 * пользователем, проверены заново и ошибок в них не найдено)
		 * @return {bool}
		 */
		potentialErrorsExist: function () {
			return _.findIndex(this.fields, "has_potential_errors") == -1;
		},

		/**
		 * Получить значение поля
		 * @param {object} field
		 * @return {string}
		 */
		getValue: function (field) {
			var text;
			// Если это поле с форматированием (редактор Trumbowyg)
			if (field.formatted) {
				text = $("#" + field.id).trumbowyg("html");
				text = text === false ? "" : text;
			} else {
				// Если это обычное поле без форматирования
				text = $("#" + field.id).val();
			}
			// Убрать тэги ошибок
			text = this.stripErrorTags(text);
			// Раскодировать спец символы html
			text = he.decode(text);
			return text;
		},

		/**
		 * Изменить значение поля
		 * @param {object} field
		 * @param {string} value
		 */
		setValue: function (field, value) {
			// Если это поле с форматированием (редактор Trumbowyg)
			if (field.formatted) {
				return $("#" + field.id).trumbowyg("html", value);
			}
			// Если это обычное поле без форматирования
			return $("#" + field.id).val(value);
		},

		/**
		 * Проверить текст на ошибки и выделить
		 * ошибки в тексте специальными тэгами
		 * @param {string} formattedText
		 */
		check: function (formattedText) {
			this.loading = true;
			// Удалить тэги из текста
			var res = this.stripTags(formattedText);
			var plainText = res.plainText;
			var replacedIndexes = res.replacedIndexes;
			// Найти ошибки
		    axios.get(this.url, {
			    params: {
			    	language: this.lang,
			    	text: plainText,
			    }
		    })
	      		.then( (response) => {
	      			// Вытащить ошибки из ответа LanguageTool
	      			var errors = this.getErrorsFromLanguageToolResponse(response);
	      			// Если ошибок нет, перейти к следующему/предыдущему полю
	      			if (errors.length == 0) {
	      				// Отметить, что ошибок в этом поле нет и больше его проверять не нужно
	      				this.fields[this.selectedFieldIndex].has_potential_errors = false;
	      				// Перейти к следующему полю
	      				this.nextField(this.iterateForward);
	      			} else {
		      			this.errors = errors;
		      			// Выделить ошибки в тексте
		      			var formattedTextWithErrors = this.markErrors(formattedText, replacedIndexes, errors);
		      			// Показать ошибки в редакторе
		      			$("#language-tool").trumbowyg("html", formattedTextWithErrors);
						// Привязать обработчик события по клику к тэгам, 
						// которыми выделены ошибки
		      			this.bindEventsToErrorTags();
		      			// Выделить первую ошибку
		      			this.selectError(0);
		      			// Если модальное окно еще не открыто, открыть
		      			if (!this.modalShow) {
		      				this.modalShow = true;
		      			}
	      			}
	      			this.loading = false;
				});
		},

		/**
		 * Удаляет из строки все тэги, возращает строку без тэгов
		 * и массив позиций символов в оригинальной строке с учетом
		 * удаленных тэгов. Например, для строки "abc<b>def</b>gh"
		 * метод вернет:
		 * {
		 *   plainText: "abc def gh"
		 *   replacedIndexes: [0,1,2,3,6,7,8,9,13,14]
		 * }
		 * @param {string} formattedText
		 * @return {object}
		 */
		stripTags: function (formattedText) {
			// Построить массив с оригинальными позициями символов (до замены)
			var originalIndexes = [];
			for (var i = 0; i < formattedText.length; i++) {
				originalIndexes[i] = i;
			}
			// Найти все тэги
			var htmlTagsPattern = /<.+?>/gi;
			var match;
			var matches = [];
			while ((match = htmlTagsPattern.exec(formattedText)) != null) {
				matches.push({
					index: match.index,
					len: match[0].length
				});
			}
			// Удалить из массива с позициями символов индексы, соответствующие
			// заменяемым тэгам. Каждый тэг заменяется на пробел (один символ)
			var replacedIndexes = originalIndexes.slice(0);
			for (var i = matches.length - 1; i >= 0; i--) {
				replacedIndexes.splice(matches[i].index + 1, matches[i].len - 1);
			}
			// Удалить тэги из текста
			var plainText = formattedText.replace(htmlTagsPattern, " ");
			return {
				plainText: plainText,
				replacedIndexes: replacedIndexes,
			};
		},

		/**
		 * Удалить из текста все тэги ошибок
		 * @param {string} textWithErrorTags
		 * @return {string}
		 */
		stripErrorTags: function (textWithErrorTags) {
			var text = textWithErrorTags;
			text = text.replace(/<word-error.*?>/gi, "");
			text = text.replace(/<\/word-error>/gi, "");
			return text;
		},

		/**
		 * Получить ошибки из ответа LanguageTool в формате:
		 * [
		 *   {
		 *     index: 5,
		 *     len: 7,
		 *     message: "Spelling mistake",
		 *     replacements: ["word", "world", ...],
		 *   }, 
		 *   ...
		 * ]
		 * @param {object} response
		 * @return {array}
		 */
		getErrorsFromLanguageToolResponse: function (response) {
			var errors = [];
			var self = this;
			var i = 0;
			_.forEach(response.data.matches, function (match) {
				if (self.ignoredMistakes.indexOf(match.rule.id) == -1) {
					var replacements = [];
					_.forEach(match.replacements, function (replacement) {
						replacements.push(replacement.value);
					});
					replacements = _.take(replacements, self.maxReplacementCount);
					errors.push({
						id: i,
						index: match.offset,
						len: match.length,
						message: match.message,
						replacements: replacements,
						level: self.getErrorLevel(match),
					});
					i++;
				}
			});
			return errors;
		},

		/**
		 * Выделить ошибки в тексте
		 * @param {string} formattedText форматированный текст
		 * @param {array} replacedIndexes массив позиций символов в форматированном тексте
		 * @param {array} errors ошибки с позициями в тексте без тэгов
		 * @return {string}
		 */
		markErrors: function (formattedText, replacedIndexes, errors) {
			// Разбить форматированный текст на чатсти в соответствии с ошибками,
			// добавить тэги выделения ошибок
			var errorOpenTag = "<word-error class='{0}' data-error-id='{1}'>";
			var errorCloseTag = "</word-error>";
			var parts = [];
			var part;
			var originalIndexStart;
			var originalIndexEnd;
			var previousErrorEndIndex;
			for (var i = 0; i < errors.length; i++) {
				// Первая ошибка
				if (i == 0) {
					previousErrorEndIndex = 0;
				} else {
					// Вторая и последующие ошибки
					previousErrorEndIndex = replacedIndexes[errors[i - 1].index + errors[i - 1].len];;
				}
				// Определить индекс начала ошибки в форматированном тексте
				originalIndexStart = replacedIndexes[errors[i].index];
				// Взять текст с начала строки (или с конца предыдущей ошибки) до индекса
				part = formattedText.substring(previousErrorEndIndex, originalIndexStart);
				parts.push(part);
				// Добавить открывающий тэг ошибки
				var tag = errorOpenTag.replace("{0}", errors[i].level);
				var tag = tag.replace("{1}", errors[i].id);
				parts.push(tag);
				// Определить индекс конца ошибки в форматированном тексте
				originalIndexEnd = replacedIndexes[errors[i].index + errors[i].len];
				// Взять текст между началом и концом ошибки
				part = formattedText.substring(originalIndexStart, originalIndexEnd);
				parts.push(part);
				// Добавить закрывающий тэг ошибки
				parts.push(errorCloseTag);				
				// Последняя ошибка
				if (i == errors.length - 1) {
					// Если после последней ошибки еще есть текст, добавить его
					if (errors[i].index + errors[i].len < replacedIndexes.length) {
						// Определить индекс конца ошибки в форматированном тексте
						originalIndexEnd = replacedIndexes[errors[i].index + errors[i].len];
						// Добавить весь текст до конца строки		
						part = formattedText.substring(originalIndexEnd);
						parts.push(part);
					}
				}
			}
			var formattedTextWithErrors = parts.join("");
			return formattedTextWithErrors;
		},

		/**
		 * Определить серьезность ошибки (severe|normal|optional)
		 * @param {object} error
		 * @return {string}
		 */
		getErrorLevel: function (error) {
			// Взято из официального плагина LanguageTool
            if (error.rule.id.indexOf("SPELLER_RULE") >= 0 || 
            	error.rule.id.indexOf("MORFOLOGIK_RULE") == 0 || 
            	error.rule.id == "HUNSPELL_NO_SUGGEST_RULE" || 
            	error.rule.id == "HUNSPELL_RULE" || 
            	error.rule.id == "FR_SPELLING_RULE") {
                return "severe";
            } else if (error.rule.issueType === 'style' || 
            	error.rule.issueType === 'locale-violation' || 
            	error.rule.issueType === 'register') {
                return "optional";
            } else {
                return "normal";
            }			
		},


		/**
		 * Проверить все поля одним запросом и определить
		 * процент текста, нуждающегося в правке. Если процент
		 * больше порогового, запустить процесс исправления ошибок
		 */
		init: function () {
			// Собрать все тексты из всех полей
			var texts = [];
			var self = this;
			_.forEach(this.fields, function (field) {
				texts.push(self.getValue(field));
			});
			texts = texts.join(" ");
			// Удалить тэги
			texts = texts.replace(/<.+?>/gi, " ");
			// Найти ошибки
		    axios.get(this.url, {
			    params: {
			    	language: this.lang,
			    	text: texts,
			    }
		    })
	      		.then( (response) => {
	      			// Вытащить ошибки из ответа LanguageTool
	      			var errors = this.getErrorsFromLanguageToolResponse(response);
	      			// Определить кол-во текста (в символах), нуждающегося в правке
	      			var textAmountInSymbolsToCorrect = 0;
	      			_.forEach(errors, function (error) {
	      				if (error.level == "severe" || error.level == "normal") {
	      					textAmountInSymbolsToCorrect += error.len;
	      				}
	      			});
	      			// Определить процент текста, содержащего ошибки
	      			var textPercentToCorrect = textAmountInSymbolsToCorrect/texts.length;
	      			// Если процент превышает пороговое значение, запустить процесс
	      			// исправления ошибок пользователем
	      			if (textPercentToCorrect > this.minTextPercentToCorrect) {
	      				this.selectedFieldIndex = 0;
	      				this.iterateForward = true;
	      				this.selectedError = null;
	      				this.hasPotentialErrors = true;
	      				this.loading = false;
						// Сбросить признак has_potential_errors у всех полей
						var self = this;
						_.forEach(this.fields, function (field) {
							field.has_potential_errors = true;
						});
						// Запустить проверку первого поля
						this.check(this.getValue(this.fields[0]));
						// Сообщить через шину, что ошибок слишком много и запущен процесс исправления ошибок
						window.bus.$emit("language-tool-check-failed");
	      			} else {
	      				// Сообщить через шину, что ошибок приемлемое количество
	      				window.bus.$emit("language-tool-check-passed");
	      			}
				});
		},

	},

};
</script>