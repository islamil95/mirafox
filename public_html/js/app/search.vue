<template>
	<div class="custom-search">
		<!-- Поле для ввода строки поиска -->
		<input class="form-control" 
			:class="inputClass"
			v-model="search" type="text" :placeholder="placeholder"
			v-on:input="search = $event.target.value"
			@keydown.up.prevent="onArrowUp" 
			@keydown.down.prevent="onArrowDown" @keydown.enter.prevent="onEnter"
			@keyup.esc.prevent="onEscape"
			@blur="onBlur" @focus="onFocus">
		<!-- Кнопка сброса -->
		<span class="clear-button" @click.prevent="onClear" v-if="showClearButton">
			<slot name="clear-button">×</slot>
		</span>
		<!-- Кнопка поиска -->
		<span class="search-button" @click.prevent="onEnter" 
			:class="[hasSuggestions ? 'has-suggestions' : '']">
			<slot name="search-button">
				<span class="fa-stack text-success">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-search fa-stack-1x fa-inverse"></i>
				</span>
			</slot>
		</span>
		<!-- Выпадающий список с подсказками -->
		<div class="dropdown" v-show="suggestions.length > 0">
			<!-- Заголовок "Недавний поиск" и кнопка для очистки недавнего поиска -->
			<div class="history d-flex justify-content-between" v-if="historySuggested">
				<div class="d-inline-block">{{ t('Недавний поиск') }}</div>
				<!-- Кнопка очистки истории запросов -->
				<div class="d-inline-block clear-history-button" 
					@mousedown="onDropdownMouseDown" @click="clearHistory">
					{{ t('Очистить') }}
				</div>
			</div>
			<!-- Заголовок для обычных поисковых подсказок (не из недавнего поиска) -->
			<div class="suggestions-header" v-if="suggestionsHeader && !historySuggested">
				<div>{{ suggestionsHeader }}</div>
			</div>
			<div class="suggestions">
				<div v-for="suggestion of suggestions" @mouseover="onMouseOver(suggestion.suggestion)" 
					@mouseleave="onMouseLeave" @mousedown="onDropdownMouseDown" 
					@click="onSuggestionClick" 
					:class="{'selected': suggestion.suggestion == selectedSuggestion}"
					class="suggestion d-flex justify-content-start">
					<!-- Текст подсказки -->
					<div class="d-inline-block">
						<span v-html="suggestion.excerpt"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

/**
 * Поле для ввода поисковой строки с подсказками. Обязательные свойства:
 *   suggestionsEndpoint - url для загрузки поисковых подсказок
 *   suggestionsData - дополнительные данные для загрузки поисковых подсказок (объект),
 *   перед передачей в url в объект будут добавлены свойства:
 *     query - строка поиска
 *     spellerResponse - ответ спеллеря Яндекса (результат проверки строки поиска на ошибки)
 *   clearHistoryEndpoint - url для очистки истории запросов
 *  Опционально можно задать:
 *    placeholder - плейсходер для поля ввода
 *    defaultSearch - запрос по умолчанию, который появляется в строке поиска при сбросе 
 *      (кнопка с крестиком). Если не задано, то при сбросе строка поиска очищается
 *    suggestionsHeader - заголовок для выпадающего списка с поисковыми подсказками
 *  Методы:
 *    clear - очистить строку поиска
 *  Компонент генерирует события:
 *    search-executed - пользователь попросил выполнить поиск по строке, в событие передается
 *      строка поиска (набранная пользователем вручную или из поисковых подсказок)
 */

// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Строка поиска
			search: "",
			// Поисковые подсказки
			suggestions: [],
			// Выбранная поисковая подсказка
			selectedSuggestion: "",
			// Запрос поисковых подсказок
			// (для возможности отмены)
			axiosRequest: null,
			// Запросы спеллера/поисковых подсказок в данный момент выполняются
			// (не запускать новые)
			requestsExecuting: false,
			// Можно ли выводить подсказки при изменении строки поиска. Нужно
			// в следующих случаях:
			// 1) Пользователь выбрал подсказку. В этот момент строка поиска должна
			// измениться на выбранную подсказку, но повторно давать подсказки уже
			// не нужно
			// 2) Пользователь нажал на кнопку "очистить поиск". В этот момент нужно обнулить
			// строку поиска, но при этом не выдавать историю запросов
			suggestWhenSearchChanged: true,
			// Можно ли очищать подсказки при потере фокуса строкой поиска. Нужно
			// в случае, если пользователь кликнул мышкой (не выбрал стрелками на
			// клавиатуре и нажал Enter, а именно кликнул) на подсказке. В этом случае
			// событие blur срабатывает раньше click и очищает список подсказок (click,
			// соответственно, не вызывается, т.к. элемента с подсказкой уже нет на
			// странице)
			clearSuggestionsOnBlur: true,
			// Флаг устанавливается, если в данный момент показаны подсказки
			// по пустому запросу (т.е. история запросов пользователя)			
			historySuggested: true,
			// Локализация компонента
			i18n: {
				en: {
					"Недавний поиск": "Recent search",
					"Очистить": "Delete",
				}
			},			
		};
	},

	props: [
		// Url для загрузки поисковых подсказок
		"suggestionsEndpoint",
		// Дополнительные данные для загрузки поисковых подсказок
		"suggestionsData",
		// Url для очистки истории запросов
		"clearHistoryEndpoint",		
		// Плейсходлер для поля ввода
		"placeholder",
		// Запрос по умолчанию, который появляется в строке
		// поиска при сбросе (кнопка с крестиком). Если не задано, 
		// то при сбросе строка поиска очищается
		"defaultSearch",
		// Заголовок для выпадающего списка с поисковыми подсказками
		"suggestionsHeader",
		// Имя поиска
		"searchName",
	],

	computed: {

		// Показать кнопку сброса поиска, если текущий запрос отличается
		// от запроса по умолчанию (или, если не задан запрос по умолчанию,
		// то от пустой строки)
		showClearButton: function () {
			return this.search != "";
		},

		// Для текущей строки поиска есть хотя бы одна подсказка
		// (развернут выпадающий список с подсказками)
		hasSuggestions: function () {
			return this.suggestions.length > 0;
		},

		// В настоящий момент выбрана одна из поисковых подсказок
		suggestionSelected: function () {
			return this.selectedSuggestion != ''
		},

		// Классы для поля ввода
		inputClass: function () {
			return {
				"has-suggestions": this.hasSuggestions,
				"suggestion-selected": this.suggestionSelected,
				"has-text": this.search != "",
			};
		},

	},

	watch: {

		// Изменилась строка поиска
		search: function (val) {
			window.bus.$emit('search-' + this.searchName + '-change', this.search);
			if (this.suggestWhenSearchChanged) {
				this.onInput();
			} else {
				this.suggestWhenSearchChanged = true;
			}
		},

	},

	/**
	 * Created event
	 */
	created: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
		// Заполнить значение строки поиска по умолчанию
		var defaultSearch = this.defaultSearch ? this.defaultSearch : "";		
		this.changeSearchWithoutSuggesting(defaultSearch);

		window.bus.$on('search-' + this.searchName + '-change', (val) => {
			this.search = val;
		});
	},

	methods: {

	    /**
	     * Обработчик события на ввод текста в строку поиска
	     */
		onInput: function () {
			if (this.requestsExecuting) {
				return;
			}
			this.requestsExecuting = true;
			var searchBeforeRequest = this.search;
			// Для строки поиска в 1 и 2 символа никаких подсказок не выводим
			if (this.search.length < 3 && this.search.length > 0) {
				this.historySuggested = false;
				this.suggestions = [];
				this.selectedSuggestion = "";
				this.requestsExecuting = false;
				return;
			}
			this.axiosRequest = axios.CancelToken.source();
			var suggestionsData = this.suggestionsData ? this.suggestionsData : {};
  			suggestionsData.query = this.search;
		    axios.post(this.suggestionsEndpoint, suggestionsData, { 
		    	cancelToken: this.axiosRequest.token,
		    })
	      		.then( (response) => {
	      			this.historySuggested = this.search == "";
	      			this.suggestions = response.data.data.suggestions;
	      			this.selectedSuggestion = "";
	      			this.requestsExecuting = false;
	      			// Если за время выполнения запросов строка поиска изменилась,
	      			// повтроить вызов
	      			if (searchBeforeRequest != this.search) {
	      				this.onInput();
	      			}
				}).catch(function (thrown) {});
		},

	    /**
	     * Обработчик события на нажатие кнопки вниз
	     */
		onArrowDown: function () {
			if (this.suggestions.length == 0) {
				return;
			}
			var index = _.findIndex(this.suggestions, ["suggestion", this.selectedSuggestion]);
			if (index == -1) {
				index = 0;
			} else if (index >= this.suggestions.length - 1) {
				index = this.suggestions.length - 1;
			} else {
				index++;
			}
			this.selectedSuggestion = this.suggestions[index].suggestion;
		},

	    /**
	     * Обработчик события на нажатие кнопки вверх
	     */
		onArrowUp: function () {
			if (this.suggestions.length == 0) {
				return;
			}	
			var index = _.findIndex(this.suggestions, ["suggestion", this.selectedSuggestion]);
			if (index == -1) {
				return;
			} else if (index > 0) {
				index--;
				this.selectedSuggestion = this.suggestions[index].suggestion;				
			} else {
				this.selectedSuggestion = "";
			}
		},

	    /**
	     * Обработчик события на нажатие Enter
	     */
		onEnter: function () {
			// Остановить процесс формирования поисковых подсказок
			this.stopSuggestingProcess();
			// Если подсказка выбрана, взять ее, иначе текст из строки поиска
			var val = this.selectedSuggestion != "" ? this.selectedSuggestion : this.search;
			// Изменить строку поиска без вывода подсказок
			this.changeSearchWithoutSuggesting(val);	
			// Сообщить родителю, что строка поиска изменилась 
			// (можно выполнять поиск)
			this.$emit("search-executed", val);
			// Очистить список подсказок и выбранную подсказку
			this.clearSuggestions();
		},

	    /**
	     * Обработчик события на нажатие Escape
	     */
		onEscape: function () {
			this.stopSuggestingProcess();
			this.clearSuggestions();
		},

	    /**
	     * Обработчик события на наведение мышкой на поисковую подсказку
	     */
		onMouseOver: function (val) {
			this.selectedSuggestion = val;
		},

	    /**
	     * Обработчик события на покидание мышки поисковой подсказки
	     */
		onMouseLeave: function () {
			this.selectedSuggestion = "";
		},

	    /**
	     * Обработчик события на клик на поисковой подсказке
	     */
		onSuggestionClick: function () {
			this.onEnter();
		},

		/**
		 * Обработчик события mousedown на элементе выпадающего списка
		 * (кнопка "очистить историю", поисковая подсказка и т.д.)
		 */
		onDropdownMouseDown: function () {
			// Не очищать список подсказок при потере строкой поиска
			// фокуса (чтобы сработало событие click на элементе выпадающего списка)
			this.clearSuggestionsOnBlur = false;
		},

	    /**
	     * Обработчик события на нажатие кнопки "Сбросить поиск"
	     */
		onClear: function () {
			var emptySearch = "";
			// Сбросить строку поиска без вывода подсказок
			this.changeSearchWithoutSuggesting(emptySearch);
			// Сообщить родителю, что строка поиска сбросилась 
			// (например, чтобы закрыть результаты поиска)
			this.$emit("search-clear", emptySearch);
			// Очистить список подсказок и выбранную подсказку
			this.clearSuggestions();
		},

		/**
		 * Обработчик события на потерю фокуса полем
		 * для ввода строки поиска
		 */
		onBlur: function () {
			this.stopSuggestingProcess();
			if (this.clearSuggestionsOnBlur) {
				this.clearSuggestions();
			} else {
				this.clearSuggestionsOnBlur = true;
			}	
		},

		/**
		 * Обработчик события на фокуса поля
		 * для ввода строки поиска
		 */
		onFocus: function () {
			this.onInput();
		},

		/**
		 * Очистить список подсказок и выбранную подсказку
		 */
		clearSuggestions: function () {
			this.suggestions = [];
			this.selectedSuggestion = "";
		},

		/**
		 * Изменить строку поиска без вывода подсказок
		 * @param {string} val новое значение для строки поиска
		 */
		changeSearchWithoutSuggesting: function (val) {
			if (this.search == val) {
				return;
			}
			// Сбросить флаг suggestWhenSearchChanged, чтобы компонент не начал
			// заново формировать подсказки
			this.suggestWhenSearchChanged = false;
			// Изменить строку поиска
			this.search = val;
		},

		/**
		 * Остановить процесс формирования поисковых подсказок:
		 * 1) debounce ввода в строке поиска
		 * 2) проверку спеллера
		 * 3) загрузку подсказок
		 */
		stopSuggestingProcess: function () {
			if (this.axiosRequest) {		
				this.axiosRequest.cancel();
			}
			this.requestsExecuting = false;	
		},

		/**
		 * Сбросить строку поиска
		 */
		clear: function () {
			this.onClear();
		},

		/**
		 * Программно установить строку поиска без вывода подсказок
		 * @param {string} search
		 */
		setSearch: function (search) {
			this.changeSearchWithoutSuggesting(search);
		},

		/**
		 * Очистить историю запросов пользователя
		 */
		clearHistory: function () {
			this.clearSuggestions();
		    axios.post(this.clearHistoryEndpoint).then( (response) => {}).catch(function (thrown) {});
		},

	}

};
</script>