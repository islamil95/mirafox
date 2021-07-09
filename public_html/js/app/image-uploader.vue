<template>
	<div class="image-uploader d-inline-block" :style="componentStyle">
		<croppa ref="croppa"
			:initial-image="initialImage"
			@new-image="imageSelected"
			@image-remove="imageRemoved"
			@move="imageChanged"
			@zoom="imageChanged"
			:accept="'image/jpeg,image/jpg,image/png,image/gif,image'"
			:file-size-limit="4194304"
			:show-remove-button="false"
			:replace-drop="true"
			:width="width"
			:height="height"
			:placeholder="''"
			:style="fitWidthAndHeightStyle"
			@file-size-exceed="fileSizeExceed">
			<!-- Плейсхолдер -->
			<div class="image-placeholder-wrapper d-flex justify-content-center align-items-center"
				@click="selectImage" v-if="!hasImage">
				<div class="image-placeholder"></div>
			</div>
		</croppa>
		<!-- Кнопки для компактного компонента -->
		<div v-if="compact" class="d-inline-block align-top">
			<div class="icon ico-edit d-block mb-1" @click="selectImage"></div>
			<div class="icon ico-trash-18 d-block" v-if="hasImage" @click="removeImage"></div>
		</div>
		<!-- Ошибка -->
		<div class="invalid-feedback" v-if="error" :style="fitWidthStyle">
			{{ t(error) }}
		</div>
		<!-- Кнопки для полноразмерного компонента -->
		<div v-if="!compact" class="d-flex justify-content-center" :style="fitWidthStyle">
			<span @click="selectImage" class="link-button">{{ t("Выбрать") }}</span>
			<span @click="removeImage" class="link-button ml-3" v-if="hasImage">{{ t("Удалить") }}</span>
		</div>
	</div>
</template>

<script>

/**
 * TODO
 */

// vue-croppa
import Croppa from "vue-croppa";
Vue.use(Croppa);

// Локализация
import i18nMixin from "appJs/i18n-mixin";

export default {
	mixins: [i18nMixin],

	data () {
		return {
			// Пользователь изменил изображение
			// (удалил, загрузил новое или изменил существующее)
			changed: false,
			// В данный момент в компонент загружено изображение
			hasImage: false,
			// Ссылка на изображение по умолчанию
			initialImage: null,
			// Ошибка загрузки изображения
			error: null,
			// Локализация компонента
			i18n: {
				en: {
					"Выбрать": "Select",
					"Удалить": "Delete",
					"Размер файла не должен превышать 4Мб": "Your file size should not exceed four Mb",
				}
			},			
		};
	},

	props: {

		// Изображение по умолчанию
		src: {
			type: String,
			default: "",
		},

		// Ширина изображения
		width: {
			type: Number,
			default: 640,
		},

		// Высота изображения
		height: {
			type: Number,
			default: 480,
		},

		// Компактный режим (кнопки удаления/изменения изображения
		// справа и в виде иконок, а не текста)
		compact: {
			type: Boolean,
			default: false,
		}

	},

	computed: {

		// Задать ширинуэлемента по ширине загружаемого 
		// изображения с учетом рамки в 1px
		fitWidthStyle: function () {
			return {
				"width": this.width + 2 + "px",
			};
		},

		// Задать ширину и выстоу элемента по 
		// ширине и высоте загружаемого изображения
		// с учетом рамки в 1px
		fitWidthAndHeightStyle: function () {
			return {
				"width": this.fitWidthStyle.width,
				"height": this.height + 2 + "px",
			};
		},

		// Стиль компонента
		componentStyle: function () {
			// Установить минимальную ширину компонента
			// (для компактного режима - добавить справа место под кнопки - 25px)
			return {
				"min-width": this.width + 2 + (this.compact ? 25 : 0) + "px",
			};
		},

	},

	/**
	 * Mounted event
	 */
	mounted: function () {
		// Инициализировать mixin локализации
		this.i18nInit();
		// Загрузить изображение по умолчанию
		this.setImage();
	},

	methods: {

		/**
		 * Получить загруженное изображение
		 */
		getImage: async function () {
			return {
				changed: this.changed,
				blob: this.changed ? await this.$refs.croppa.promisedBlob() : null,
			};
		},

		/**
		 * Установить изображение по умолчанию
		 */
		setImage: function () {
			this.initialImage = this.src;
			this.$refs.croppa.refresh();
			this.hasImage = this.src != "" ? true : false;
		},

		/**
		 * Пользователь изменил изображение
		 * (удалил, загрузил новое или изменил существующее)
		 */
		imageChanged: function () {
			this.changed = true;
		},

		/**
		 * Пользователь загрузил новое изображение
		 */
		imageSelected: function () {
			this.error = null;
			this.hasImage = true;
			this.imageChanged();
		},

		/**
		 * Пользователь удалил изображение
		 */
		imageRemoved: function () {
			this.hasImage = false;
			this.imageChanged();
		},

		/**
		 * Выбрать новое изображение
		 */
		selectImage: function () {
			this.$refs.croppa.chooseFile();
		},

		/**
		 * Удалить изображение
		 */
		removeImage: function () {
			this.error = null;			
			this.$refs.croppa.remove();
		},

		/**
		 * Выбран слишком большой файл
		 */
		fileSizeExceed: function () {
			this.error = "Размер файла не должен превышать 4Мб";
		},

	},

};
</script>