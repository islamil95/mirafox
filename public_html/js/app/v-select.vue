<template>
	<select :data-placeholder="placeholder" :disabled="disabled">
		<option v-if="nullable" value="-1"></option>
		<option v-for="option of options" :value="option[elementKey]">{{ option[elementLabel] }}</option>
	</select>
</template>

<script>

/**
 * Компонент отображает раскрывающийся список для выбора элементов. Поддерживает директиву v-model -
 * выбранный элемент. Обязательные свойства:
 *   options - список элементов для выбора. Каждый элемент должен иметь свойства id и name (названия можно 
 *     переопределить, см. ниже). Важно: в списке не должно быть элемента с id = -1, он зарезервирован
 *     для выбора "пустого" значения, когда пользователь сбрасывает выбор (см. ниже свойство nullable)
 * Опционально можно задать:
 *   element-label - название свойства, содержащее метку элемента
 *   element-key - название свойства, содержащее уникальный инентификатор элемента
 *   placeholder - текст, который будет отображаться, если не выбран ни один элемент
 *   nullable - возможность сбросить выбор, по умолчанию опция отключена
 *   searchable - возможность поиска, по умолчанию опция отключена
 *   disabled - компонент активный/неактивный
 */

import chosen from "script-loader!oldJs/chosen.jquery.js";

export default {
	data () {
		return {

		};
	},

	props: {

		// Список опций для выбора
		options: {
			type: Array,
			default: function () {
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

		// Placeholder
		placeholder: {
			type: String,
			// Если нужно вывести пустой placeholder - пробел обязателен,
			// без него chosen выведет свой placeholder по умолчанию
			default: " ",
		},		

		// Возможность сбросить выбор
		nullable: {
			type: Boolean,
			default: false,
		},

		// Компонент активный/неактивный
		disabled: {
			type: Boolean,
			default: false,
		},

		// Возможность поиска
		searchable: {
			type: Boolean,
			default: false,
		},

	},

	watch: {

		// Изменился выбранный элемент
		value: function () {
			// Выбрать элемент в компоненте chosen
			this.$nextTick(function () {
				$(this.$el).trigger('chosen:updated');
			});
		},

		// Изменился список опций для выбора
		options: function () {
			// Реинициализировать компонент chosen
			this.$nextTick(function () {
				this.destroy().init();
			});
		},

		// Компонент стал активным/неактивным
		disabled: function () {
			this.$nextTick(function () {
				this.destroy().init();
			});			
		},

	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Инициализировать компонент chosen
		var self = this;
		$(this.$el).change(function (evt, params) {
			//Если мультиселект послыем массив значений
			if(self.$el.multiple) {
				self.updateValue($(self.$el).val().map(Number));
			} else {
				self.updateValue(this.value);
			}
		});
		this.init();
	},

	methods: {

		/**
		 * Инициализировать компонент chosen
		 */
	    init: function () {
	    	// Общие настройки
			$(this.$el).chosen({
				// Растянуть на всю ширину (ширину должен контролировать родитель)
				width: '100%',
				// Отключить поиск
				disable_search: !this.searchable,
				// TODO
				allow_single_deselect: true,
			});
			// Выбрать элемент в компоненте chosen
			var self = this;
			this.$nextTick(function () {
				// Если мультиселект то self.value должен быть массивом
				if(self.$el.multiple) {
					let values = [];
					for(let item of self.value) {
						values.push(item[self.elementKey]);
					}
					$(self.$el).val(values).trigger("chosen:updated");
				} else {				
					self.$el.value = self.value ? self.value[self.elementKey] : -1;
					$(self.$el).trigger('chosen:updated');
				}
			});
			return this;
		},

		/**
		 * Уничтожить компонент chosen
		 */
	    destroy: function () {
			$(this.$el).chosen('destroy');
			return this;
		},

		/**
		 * Отправить родителю сообщение input
		 * об изменении выбранного элемента
		 * @param {object} value
		 */
	    updateValue: function (value) {
	    	// Выбран пустой элемент
	    	if (value == -1) {
	    		this.$emit('input', null);
	    	} else {
	    		// Выбран элемент из списка
	    		var self = this;
				// Если мультиселект формируем массив объектов
				if(self.$el.multiple) {
					var option = _.filter(this.options, function (val) {
						return value.includes(val[self.elementKey]);
					});
				} 
				// Если обычный формируем объект
				else {
					var option = _.find(this.options, function (val) {
						return val[self.elementKey] == value;
					});
				}
		    	this.$emit('input', option);
	    	}
		},

	},

};
</script>

<style>
	.chosen-container-single .chosen-single {
	    height: 32px!important;
	    padding-top: 3px!important;
	    font-size: 14px;
	}

	.chosen-drop {
	    font-size: 14px;    
	}

	.chosen-container .chosen-single.chosen-single-with-deselect abbr.search-choice-close {
	    background-image: url('/images/exit.png');
	    background-size: 10px 10px !important;
	    top: 10px;
	    right: 22px;
	}

	.chosen-container .chosen-single.chosen-single-with-deselect abbr.search-choice-close:hover {
		background-position: inherit !important;
	}

	.chosen-container.chosen-with-drop .chosen-single.chosen-single-with-deselect abbr.search-choice-close {
		display: none;
	}

	.chosen-container {
		float: none!important;		
	}

	.chosen-drop ul {
	    padding-left: 4px!important;
	    margin-bottom: 4px!important;
	}

	.chosen-drop li {
		list-style-type: none!important;
	}

	.chosen-container.chosen-disabled	{
		opacity: 1!important;
	}

	.chosen-container.chosen-disabled .chosen-single div {
		display: none;
	}

	.chosen-container .chosen-drop {
		z-index: 1070!important;
	}

</style>