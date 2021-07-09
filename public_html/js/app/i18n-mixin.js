/**
 * Mixin добавляет возможность локализации компонент. Перед использованием необходимо
 * вызвать метод i18nInit (например, в событии created). Далее в шаблоне .vue можно
 * локализовать строки при помощи метода t() или в скрипте, вызвав this.t().
 * 
 * В локализуемых строках можно использовать подстановки, например:
 *   t("это строка с первой {{0}} и второй {{1}} подстановками", ["подстановка 1", "подстановка 2"]);
 *   
 * Каждый компонент должен сам задавать свои переводы, например:
 *   data () {
 *     return {
 *       i18n: {
 *         en: {
 *           "строка на русском": "строка на английском",
 *            ...
 *         },
 *       },
 *     };
 *   },
 *
 * Если для строки требуется множественное число, то переводы задаются следующим образом:
 *   data () {
 *     return {
 *       i18n: {
 *         ru: {
 *           "{{0}} яблоко": {
 *             0: "{{0}} яблоко",
 *             1: "{{0}} яблока",
 *             2: "{{0}} яблок",
 *           },
 *         },
 *         en: {
 *           "{{0}} яблоко": {
 *             0: "{{0}} apple",
 *             1: "{{0}} apples",
 *           },
 *         },
 *       },
 *     };
 *   },
 */

export default {
	data () {
		return {
			// Данные для переводов
			i18n: null,
			// Текущая локаль
			locale: "ru",
			// Локаль по умолчанию
			defaultLocale: "ru",
		};
	},

	methods: {

		/**
		 * Инициализация mixin
		 */
		i18nInit: function () {
			// Получить текущую локаль
			this.locale = document.documentElement.lang;
		},

		/**
		 * Форсировать локаль
		 */
		i18nForceLocale: function (locale) {
			this.locale = locale;
		},

		/**
		 * Локализовать сообщение
		 * @param {string} msgid
		 * @param {array} placeholders
		 * @return {string}
		 */
		t: function (msgid, placeholders) {
			// Если текущая локаль совпадает с дефолтной
			if (this.locale == this.defaultLocale) {
				return this.replacePlaceholders(msgid, placeholders);				
			}
			// Если не совпадает, должны быть заданы переводы
			if (this.i18n) {
				// Для текущей локали
				if (this.i18n.hasOwnProperty(this.locale)) {
					// Для конкретной строки
					if (this.i18n[this.locale].hasOwnProperty(msgid)) {
						var message = this.i18n[this.locale][msgid];
						// Вернуть локализованную строку с замененными placeholders
						return this.replacePlaceholders(message, placeholders);
					}
				}
			}
			return msgid;
		},

		/**
		 * Локализовать сообщение для множественного чила
		 * @param {string} msgid
		 * @param {number} count
		 * @param {array} placeholders
		 * @return {string}
		 */
		tn: function (msgid, count, placeholders) {
			// Должны быть заданы переводы
			if (this.i18n) {
				// Для текущей локали
				if (this.i18n.hasOwnProperty(this.locale)) {	
					// Для конкретной строки
					if (this.i18n[this.locale].hasOwnProperty(msgid)) {
						// Должны быть заданы все формы, а не конкретный перевод
						var pluralForms = this.i18n[this.locale][msgid];
						if (Array.isArray(pluralForms)) {
							// Кол-во форм должно соответствовать локали
							// (для русской - 3, для английской - 2)
							if (pluralForms.length == this.getPluralFormsCount()) {
								// Определить форму по кол-ву
								var pluralFormIndex = this.getPluralForm(count);
								// Найти перевод для формы
								var message = this.i18n[this.locale][msgid][pluralFormIndex];
								// Вернуть локализованную строку с замененными placeholders
								return this.replacePlaceholders(message, placeholders);								
							}
						}
					}
				}
			}
			return msgid;
		},

		/**
		 * Получить форму для множественного числа
		 * (в русском - 3 формы, в английском - 2)
		 * @param {number} count
		 * @return {number}
		 */
		getPluralForm: function (count) {
			if (this.locale == "ru") {
				if (count % 10 == 1 && count % 100 != 11) {
					return 0;
				} else {
					if (count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 10 || count % 100 >= 20)) {
						return 1;
					} else {
						return 2;
					}
				}				
			} else {
				return count > 1 ? 1 : 0;
			}
		},

		/**
		 * Кол-во форм множественного числа для текущей локали
		 * @return {number}
		 */
		getPluralFormsCount: function () {
			return this.locale == "ru" ? 3 : 2;
		},

		/**
		 * Заменить placeholders ({{0}}, {{1}} и т.д.)
		 * @param {string} message
		 * @param {array} placeholders
		 * @return {string}
		 */
		replacePlaceholders: function (message, placeholders) {
			if (!placeholders) {
				return message;
			}
	        for (var i = 0; i < placeholders.length; i++) {
	            message = message.replace('{{' + i + '}}', placeholders[i]);
	        }
	        return message;
		},

	},

};