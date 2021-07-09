/**
 * Позволяет выбрать/развернуть элемент дерева с бесконечной вложенностью. С помощью mixin можно
 * создавать компоненты как для простого отображения древовидных структур, так и для рекурсивного выбора
 * элементов из дерева.
 * Реализует директиву v-model - выбранный/развернутый элемент (в самом низу иерархии).
 * Обязательные свойства:
 *   tree - дерево (массив объектов). Каждый элемент дерева должен иметь свойства id, parent_id и name 
 *     (названия можно переопределить, см. ниже)
 * Опционально можно задать:
 *   element-label - название свойства, содержащее метку элемента
 *   element-key - название свойства, содержащее уникальный инентификатор элемента
 *   element-parent-key - название свойства, содержащее ид родительского элемента
 *   customSort - метод для сортировки элементов (по умолчанию элементы не сортируются)
 * Полезные методы:
 *   isSelected(element) - возвращает true, если element выбран/развернут в дереве
 *   isLast(element) - возвращает true, если element последний в списке (на своем уровне)
 * Примеры реализции:
 *   appJs/tree-select
 *   moduleJs/kb/kb-tree-view
 * TODO:
 *   добавить возможность множественного выбора
 */

export default {
	data () {
		return {
			// Выбранный элемент
			element: null,
			// Выбранный дочерний элемент
			childElement: null,
			// Генерировать события при изменении элемента
			emitEventOnElementChange: true,
		};
	},

	props: {

		// Дерево
		tree: {
			type: Array,
			default() {
				return [];
			},
		},

		// Выбранный элемент
		value: {
			default: null,
		},

		// Название свойства, содержащее метку элемента
		elementLabel: {
			type: String,
			default: "name",
		},

		// Название свойства, содержащее уникальный инентификатор элемента
		elementKey: {
			type: String,
			default: "id",
		},	

		// Название свойства, содержащее ид родительского элемента
		elementParentKey: {
			type: String,
			default: "parent_id",
		},	

		/**
		 * Ксатомная сортировка элементов для выбора
		 * (по умолчанию опции не сортируются)
		 * @param {array} options
		 * @return {array}
		 */
		customSort: {
			type: Function,
			default (options) {
				return options;
			}
		},

		// Ид родительского элемента
		parentId: {
			default: null,
		},

		// Текущий уровень вложенности
		level: {
			default: 1,
		},

		// Отключить событие input при изменении родителем
		// свойства value. По умолчанию событие input генерируются
		// и при выборе элемента пользователем, и при изменении элемента
		// программно родительским компонентом через свойство value.
		disableInputEventOnParentChangeValue: {
			type: Boolean,
			default: false,
		}

	},

	computed: {

		// Родительский элемент
		parentElement: function () {
			return _.find(this.tree, [this.elementKey, this.parentId]);
		},

		// У родительского элемента есть дочерние
		hasChilds: function () {
 			return _.findIndex(this.tree, [this.elementParentKey, this.parentId]) >= 0;
		},

		// Элементы для выбора
		options: function () {
			// Найти элементы с родителем parentId в дереве
			var self = this;
			var options = _.filter(this.tree, function(option) {
				return option[self.elementParentKey] == self.parentId;
			});
			// Отсортировать элементы
			options = this.customSort(options);		
			// Если текущего выбранного элемента больше нет в отфильтрованном списке, сбросить выбор
			if (this.element && _.findIndex(options, [this.elementKey, this.element[this.elementKey]]) == -1) {
				this.element = null;
			}
			// Вернуть отфильтрованные элементы для выбора
			return options;
		},

	},

	watch: {

		// Изменен выбраный элемент
		element: function (newVal, oldVal) {
			// Элемент может быть выбран методом selectValue (элемент выбрал родительский компонент)
			// или непосредственно пользователем. Если выбранного элемента нет в value, значит,
			// элемент выбрал пользователь, тогда необходимо сгенериовать событие на изменение
			// выбранного элемента
			if (this.element) {
				if (this.value) {
					var path = this.getPath(this.value);
					if (path.indexOf(this.element[this.elementKey]) == -1) {
						this.$emit(this.getEventName(), this.element);
					}
				} else {
					this.$emit(this.getEventName(), this.element);
				}
			} else {
				if (this.disableInputEventOnParentChangeValue) {
					if (this.emitEventOnElementChange) {
						this.$emit(this.getEventName(), this.element);
					} else {
						this.emitEventOnElementChange = true;
					}
				} else {
					this.$emit(this.getEventName(), this.element);
				}
			}
		},

		// Родительский компонент изменил выбранный элемент
		value: function (val) {
			this.selectValue();
		},

	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		this.selectValue();
	},

	methods: {

		/**
		 * Выбрать элемент value со всеми родителями
		 */
		selectValue: function () {
			if (this.disableInputEventOnParentChangeValue) {
				this.emitEventOnElementChange = false;
				this.element = null;
			}
			if (!this.value) {
				return;
			}
			// Собрать путь до value
			var path = this.getPath(this.value);
			// Цикл по элементам пути
			var self = this;
			_.forEach(path, function(id) {
  				// Найти элемент по ид
  				var element = _.find(self.tree, [self.elementKey, id]);
  				if (element !== undefined) {
  					// Если элемент имеет родителя parentId, выбрать элемент
  					if (element[self.elementParentKey] == self.parentId) {
  						self.element = element;
  					}
  				}
			});
		},

		/**
		 * Очистить выбранный элемент
		 */
		clearValue: function () {
			this.element = null;
		},

		/**
		 * Обработчик события на изменение дочернего элемента
		 * (обеспечивает передачу на самый верх выбранного элемента
		 * в самом низу иерархии)
		 */
		childElementChanged: function (val) {
			// Если дочерний элемент выбран
			if (val) {
				// Передать родительскому компоненту дочерний выбранный элемент
				this.$emit(this.getEventName(), val);
			} else {
				// Если дочерний элемент не выбран,
				// передать родительскому компоненту текущий выбранный элемент
				this.$emit(this.getEventName(), this.element);
			}
		},

		/**
		 * Собрать путь до элемента
		 * @param {object} element
		 * @return {array} массив ид элементов от корневого до element
		 */
		getPath: function (element) {
			var path = [];
			if (!element) {
				return path;
			}
			// Добавить ид текущего элемента
			path.push(element[this.elementKey]);
			// Собрать ид родителей
			var parentId = element[this.elementParentKey];
			while (parentId) {
				var parent = _.find(this.tree, [this.elementKey, parentId]);
				path.push(parent[this.elementKey]);
				parentId = parent[this.elementParentKey];
			}
			return path;
		},

		/**
		 * Собрать путь до элемента (сами элементы, а не их ид как в методе getPath)
		 * @param {object} element
		 * @return {array} массив элементов от корневого до element
		 */
		getPathElements: function (element) {
			var pathIds = this.getPath(element);
			var path = [];
			var self = this;
			_.forEach(pathIds, function (id) {
				var article = _.find(self.tree, [self.elementKey, id]);
				path.push(article);
			});
			return path;
		},

		/**
		 * Получить имя события, которое должно быть сгенерировано при выборе элемента
		 * (для корневого - input, для дочернего - change)
		 * @return {string}
		 */
		getEventName: function () {
			return this.parentId ? "change" : "input";
		},

		/**
		 * Возвращает true, если element выбран
		 * @param {object} element
		 * @return {boolean}
		 */
		isSelected: function (element) {
			return this.element && element[this.elementKey] == this.element[this.elementKey];
		},

		/**
		 * Возвращает true, если element последний в списке
		 * @param {object} element
		 * @return {boolean}
		 */
		isLast: function (element) {
			return _.findIndex(this.options, [this.elementKey, element[this.elementKey]]) == this.options.length - 1;
		},

		/**
		 * Определить, есть ли у элемента дочерние
		 * @return {object}
		 */
		getHasChilds: function (element) {
			if (element) {
 				return _.findIndex(this.tree, [this.elementParentKey, element[this.elementKey]]) >= 0;
 			} else {
 				return true;
 			}
		},

	},

};