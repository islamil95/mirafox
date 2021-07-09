<template>
	<div class="file-uploader" ref="uploadSection"
		 :class="{'file-uploader-blue': this.blueDesign, 'is-dragover': isDragover, 'animate': isAnimate, 'animated-progress': animatedProgress}">
		<template v-if="!nobutton && files.length < limitCount">
			<template v-if="blueDesign">
				<div class="file-uploader-blue_button" @click="select" ref="uploadSection">
					<img :src="cdnImageUrl('/files-plus.png?v=2')" alt="" width="50" height="50">
					<div class="file-uploader-blue_button-text" v-if="isEmpty">
						<div>{{uploadButtonLabel}}</div>
						<span class="hint">{{limitHint}}</span>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="d-flex justify-content-between align-items-center flex-wrap uploader-controls lh30">
					<!-- Кнопка загрузки файлов -->
					<div class="upload-button d-inline-flex align-items-center"
						 @click="select" :class="inactiveClass">
						<!-- Иконка -->
						<i class="icon ico-clip"></i>
						<!-- Текст -->
						<a class="ml-2">{{ uploadButtonLabel }}</a>

					</div>
					<!-- Подсказка с лимитом по кол-ву и размеру загружаемых файлов -->
					<span class="hint">{{ limitHint }}</span>
				</div>
			</template>
		</template>
		<transition-group name="animate-files" class="miniatures" tag="div">
			<div v-for="(v) in imageFiles" :key="v.file.localId">
				<div class="remove" @click="remove(v.index)">×</div>
				<div class="image attached-image-img sizeable" :data-src="imageViewSrc(v.file)" data-zoom-always="true" data-single-view="true" :style="miniatureBg(v.file)">
					<i-spinner mode="lite" bg="light" :class="{'hidden': (v.file.base64 != 'creating')}" />
					<div v-if="v.file.result == 'loading'" class="fu-progress-bar">
						<div :style="progressStyle(v.file.progress || 0)"></div>
					</div>
				</div>
			</div>
		</transition-group>
		<!-- Выбранные файлы -->
		<template class="file-uploader-blue-files" v-if="blueDesign">
			<div v-for="(file,index) in files" :key="file.file_id" class="file-uploader-blue-files_item"
				 :class="file.result"
				 :title="file.file_name">
				<img :src="file.file_path" v-if="isImage(file.file_extension) && file.result !== 'error'">
				<svg width="50" height="50" viewBox="0 0 50 50"
					 v-if="!isImage(file.file_extension) && file.result !== 'error'">
					<use xlink:href="#file-placeholder"></use>
				</svg>
				<div class="file-uploader-blue-files_item-remove" v-if="file.result != 'loading'"
					 @click="remove(index,file)"></div>
			</div>
			<div v-if="hasError && showError" class="file-uploader-blue-files_error">
				<span v-html="getError()"></span>
			</div>
		</template>
		<div v-else-if="notImageFiles.length > 0" class="file-names-list">
			<div v-for="(v) in notImageFiles" :key="v.index" :class="v.file.result"
				 class="file d-flex align-items-center justify-content-between flex-wrap mt-2 p-1">
				<div class="file-name d-inline-flex align-items-center">
					<!-- Иконка файла -->
					<i :class="getFileIconClass(v.file)"></i>
					<!-- Имя файла -->
					<span class="ml-2 file-name">{{ parseFileName(v.file.file_name) }}</span>
				</div>
				<!-- Кнопка удаления файла -->
				<div v-if="v.file.result != 'loading'" @click="remove(v.index)"
					 class="remove-button inline-block" :class="inactiveClass">×
				</div>
				<!-- Блок с ошибкой о превышении объема файла -->
				<div :class="v.file.result" class="file-error ml-4 mt-1" v-html="v.file.error">
				</div>
				<!-- Индикатор загрузки -->
				<div class="uploading-progress" :class="{'green-stlye': animatedProgress}" style="overflow: hidden;" :style="progressStyle(v.file.progress || 0)"></div>
			</div>
		</div>
		<!-- Скрытое поле для загрузки файлов -->
		<form :id="formId">
			<input @change="upload" class="d-none" type="file" :ref="inputName" multiple>
		</form>
		<div class="overlay">
			<div class="animated-icon">
				<img :src="cdnImageUrl('/upload-cloud-2_150.png')" :srcset="cdnImageUrl('/upload-cloud-2_300.png') + ' 2x'">
			</div>
			<div class="overlay-title">{{t("Загрузить на Kwork")}}</div>
			<div class="overlay-desc">{{t("Поместите сюда файлы для отправки")}}</div>
		</div>
	</div>
</template>

<script>

	/**
	 * Компонент позволяет загрузить на сервер несколько файлов. Поддерживает директиву
	 * v-model - список загруженных файлов. Каждый файл представляет собой объект вида:
	 *   {
	 *     result: "success",
	 *     file_id: "1922524",
	 *     file_name: "document.pdf",
	 *     file_path: "http://kwork.local/files/uploaded/609e6535413da62a/document.pdf",
	 *     file_path_hash: "60/9e/6535413da62a",
	 *     file_extension: ".pdf"
	 *   }
	 * Объект формируется на сервере методом api?method=File.api_upload. При изменении
	 * серверной части изменится и вывод данного компонента.
	 *
	 * Необязательный свойства:
	 *   limitCount - максимальное кол-во файлов, которые можно загрузить (по умолчанию 10)
	 *   limitSize - максимальный размер файлов, которые можно загрузить в Мб (по умолчанию 12)
	 *   inputName - название input для загрузки файлов
	 *     (если на одной странице необходимо разместить несколько компонентов)
	 *   inactive - компонент неактивен (по умолчанию false)
	 *
	 * Cлоты:
	 *    upload-button - сюда можно передать свой шаблон кнопки.
	 *    files - сюда можно передать свой шаблон файлов.
	 */

	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	import mobileMixin from "appJs/mobile-mixin.js";
	// Приведение относительных ссылок к абсолютным (CDN)
	import cdnMixin from "appJs/cdn-mixin.js";

	import ISpinner from "appJs/ISpinner.vue";

	export default {
		mixins: [i18nMixin, mobileMixin, cdnMixin],

		components: {
			'i-spinner': ISpinner,
		},

		data() {
			return {
				// Признак что всё загружено
				allLoaded: true,
				// Свободный локальный ид
				freeLocalId: 1,
				// Файлы
				filesArray: [],
				isDragover: false,
				isAnimate: false,
				dragCounter: 0,
				dragAndDropCapable: false,
				// Токены отмены загрузок
				cancelTokens: [],
				// Иконка файла (имя класса) в зависимости от расширения файла
				fileIconClasses: {
					"ico-file-doc": ["doc", "xls", "rtf", "txt", "docx", "xlsx"],
					"ico-file-zip": ["zip", "rar"],
					"ico-file-image": ["png", "jpg", "gif", "psd", "jpeg"],
					"ico-file-audio": ["mp3", "wav", "avi"],
				},
				// Иконка файла (имя класса) по умолчанию (если расширение нам неизвестно)
				defaultFileIconClass: "ico-file-zip",
				// Локализация компонента
				i18n: {
					en: {
						"Прикрепить файлы": "Attach files",
						"Прикрепить ещё файл": "Attach another file",
						"до {{0}} файлов не более {{1}} Мб": "up to {{0}} files of size not greater than {{1}} Mb",
						"Файл больше {{0}}Мб не отправится. Используйте файлообменник, например, <a href=\"https://disk.yandex.ru/\" target=\"_blank\">Яндекс.Диск</a>.": "The file is larger than {{0}}Mb will not go. Use file sharing, for example, <a href=\"https://www.google.com/drive/\" target=\"_blank\">Google Drive</a>.",
						"Не загружен файл": "Not the downloaded file",
						"Тип файла недопустим для загрузки": "The file type is invalid to download",
						"Пользователь не авторизирован": "",
						"Загрузить на Kwork": "Upload to Kwork",
						"Поместите сюда файлы для отправки": "Drop files here",
						"Название файла должно быть на английском": "The file name must be in English",
						"Ошибка загрузки изображения": "Error loading image",
					}
				},
				errors: {
					default: t('Ошибка загрузки изображения'),
					file_size_exceed: t("Файл больше {{0}}Мб не отправится. Используйте файлообменник, например, <a href=\"https://disk.yandex.ru/\" target=\"_blank\">Яндекс.Диск</a>.", [this.limitSize]),
					no_file_uploaded: t('Не загружен файл'),
					not_allowed: t('Тип файла недопустим для загрузки'),
					user_non_authorized: t('Пользователь не авторизирован'),
					invalid_filename: t('Название файла должно быть на английском'),
				},
				notResend: false,
				isDestroyed: false,
				// Для чата
				isChat: false,
			};
		},
		
		props: {
			// Использовать drag'n'drop
			dragNDrop: {
				type: Boolean,
				default: false,
			},

			// Имя экземпляра загрузчика
			blueDesign: {
				type: Boolean,
				default: false,
			},

			showError: {
				type: Boolean,
				default: true,
			},

			imageExtensions: {
				type: Array,
				default: function () {
					return ['.jpg', '.jpeg', '.png']
				},
			},

			secondUserId: {
				type: Number,
				default: 0,
			},

			// Имя экземпляра загрузчика
			name: {
				type: String,
				default: '',
			},

			makethumbs: {
				type: Boolean,
				default: false,
			},

			withMiniature: {
				type: Boolean,
				default: false,
			},

			nobutton: {
				type: Boolean,
				default: false,
			},

			// Другой загрузчик для синхронизации между ними
			linkUploader: {
				type: Object,
				default: null,
			},

			// Максимальное кол-во файлов, которые можно загрузить
			limitCount: {
				type: Number,
				default: config.files.maxCount,
			},

			// Максимальный размер файлов, которые можно загрузить в Мб
			limitSize: {
				type: Number,
				default: config.files.maxSize,
			},

			// Название input для загрузки файлов
			// (если на одной странице необходимо разместить несколько компонентов)
			inputName: {
				type: String,
				default: "fileInput",
			},

			// Компонент неактивен
			inactive: {
				type: Boolean,
				default: false,
			},

			// Анимировать прогресс загрузки обычных файлов
			animatedProgress: {
				type: Boolean,
				default: true,
			},
		},

		beforeDestroy() {
			this.isDestroyed = true;
		},

		computed: {
			files: {
				get() {
					if (this.linkUploader) {
						return this.linkUploader.files;
					}
					return this.filesArray;
				},
				set(v) {
					if (this.linkUploader) {
						this.linkUploader.files = v;
					}
					this.filesArray = v;
				}
			},

			canUpload() {
				return this.files.length < this.limitCount;
			},
			hasError() {
				return this.files.find(file => file.result == 'error')
			},
			isEmpty() {
				return !this.files.length;
			},
			// Заголовок кнопки для загрузки файлов
			// (если файлов 0, то "Прикрепить файлы", иначе "Прикрепить еще файл")
			uploadButtonLabel: function () {
				return this.files.length == 0 ? this.t("Прикрепить файлы") : this.t("Прикрепить ещё файл");
			},

			isUploadAviable: function() {
				return (this.files.length < this.limitCount);
			},

			// Подсказка с лимитом по кол-ву и размеру загружаемых файлов
			limitHint: function () {
				return this.t("до {{0}} файлов не более {{1}} Мб", [this.limitCount, this.limitSize]);
			},

			// Ид формы, содержащей input для выбора файла
			formId: function () {
				return "form" + this.inputName;
			},

			// Класс для неактивного компонента
			inactiveClass: function () {
				return {
					"inactive": this.inactive,
				};
			},

			fileError: function () {
				return this.file.result;
			},

			imageFiles() {
				if (!this.makethumbs) {
					return [];
				}
				let files = [];
				_.forEach(this.files, (v, k) => {
					if (!v.base64 || v.base64 == 'none') {
						return true;
					}
					files.push({index: k, file: v});
				});
				return files;
			},

			notImageFiles() {
				let files = [];
				_.forEach(this.files, (v, k) => {
					if (v.result != 'error' && this.makethumbs && (!v.base64 || v.base64 != 'none')) {
						return true;
					}
					files.push({index: k, file: v});
				});
				return files;
			},
		},

		watch: {
			// Добавлены/удалены файлы
			files: {
				handler() {
					// Передать родителю успешно загруженные файлы
					let files = [];
					let allLoaded = true;
					if (this.files.length > 0) {
						_.forEach(this.files, (v, k) => {
							if (v.result != "error") {
								if (v.result != "loading") {
									files.push(v);
								} else {
									allLoaded = false;
								}
							} else if (v.result == "error") {
								allLoaded = false;
							}
						});
					}
					this.allLoaded = allLoaded;
					this.$emit("input", files);
					this.$emit("change", allLoaded);
					this.triggerChange();
					if (this.isChat) {
						setTimeout(chatModule.appMinHeight, 500);
					}
				},
				deep: true,
			},
		},

		/**
		 * Created event
		 */
		created() {
			// Инициализировать mixin локализации
			this.i18nInit();
			this.isDestroyed = false;
			this.isChat = window.isChat || false;

			if (this.makethumbs && PULL_MODULE_ENABLE) {
				PullModule.on(PULL_EVENT_FILE_MINIATURE_CREATED, this.handleMiniatureCreate);
			}
		},

		mounted() {
			this.checkAndEnableDraggable();
		},

		methods: {
			parseFileName(fileName) {
				let parsedFileName = fileName;
				parsedFileName = parsedFileName.replace('&#039;', '\'');
				parsedFileName = parsedFileName.replace('&mdash;', '—');
				return parsedFileName;
			},
			handleMiniatureCreate(e) {
				let fileId = parseInt(e.fileId || 0);
				if (!fileId) {
					return;
				}
				let file = this.getFileById(fileId);
				if (!file || file.base64 != 'creating') {
					return;
				}
				if (e.miniature_status == 'error') {
					file.base64 = '';
					return;
				}
				if (e.miniature_status != 'created' || !('miniatures' in e) || !Array.isArray(e.miniatures)) {
					return;
				}
				let miniature = _.find(e.miniatures, (v) => {
					return (v.bounding_width == 316 && v.bounding_height == 210 && v.scale == 'cover');
				});
				if (!miniature) {
					return;
				}
				file.base64 = miniature.url;
			},

			getFileById(id) {
				return _.find(this.files, (v) => {
					return (v.file_id == id);
				});
			},

			miniatureBg(v) {
				if (!v.base64 || v.base64 == 'creating') {
					return null;
				}
				return {'background-image': 'url(' + v.base64 + ')'};
			},

			imageViewSrc(file) {
				if ('file_path' in file && file.file_path) {
					return file.file_path;
				}
				let src = file.base64 || null;
				if (src == 'creating') {
					return null;
				}
				return src;
			},

			getError() {
				let fileWithError = this.files.find(file => file.result == 'error');
				if (fileWithError) {
					return fileWithError.error;
				}
				return '';
			},

			isImage(extension) {
				return this.imageExtensions.includes(extension);
			},

			checkAndEnableDraggable() {
				this.dragAndDropCapable = this.determineDragAndDropCapable();
				if (this.dragAndDropCapable) {
					['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (evt) {
						document.addEventListener(evt, function (e) {
							e.preventDefault();
							e.stopPropagation();
						});
					});
					document.addEventListener('dragenter', (e) => {
						if (!this.dragNDrop || this.mobileVersion) {
							return;
						}
						if (this.canUpload) {
							if (this.dragCounter < 1) {
								this.isDragover = true;
								this.isAnimate = false;
								setTimeout(() => {
									this.isAnimate = true;
								}, 0);
							}
							this.dragCounter++;
						}
					});
					document.addEventListener('dragend', evt => {
						this.isDragover = false;
						this.dragCounter = 0;
					});
					document.addEventListener('dragleave', evt => {
						this.dragCounter--;
						if (this.dragCounter < 1) {
							this.isDragover = false;
							this.dragCounter = 0;
						}
					});
					document.addEventListener('drop', e => {
						this.isDragover = false;
						this.dragCounter = 0;
						if (!this.dragNDrop || this.mobileVersion) {
							return;
						}
						this.upload(e);
					});
				}
			},

			determineDragAndDropCapable() {
				var div = document.createElement('div');
				return (('draggable' in div)
					|| ('ondragstart' in div && 'ondrop' in div))
					&& 'FormData' in window
					&& 'FileReader' in window;
			},
			/**
			 * isRemoteFiles - внешние файлы. Загруженные не через текущий загрузчик.
			 * 					В частности используется при предоставлении информации при выборе задания,
			 * 					если в задании есть файлы
			 */
			applyFileList(files, isNewMessage = false, isRemoteFiles = false) {
				if (!isRemoteFiles) {
					this.clearFiles();
				} else {

					for (let k = this.files.length - 1; k >= 0; --k) {
						if (this.files[k].file_is_remote) {
							this.remove(k, this.files[k]);
						}
					}

					// Если файлов больше лимита
					if (this.files.length + files.length > this.limitCount) {
						// Удалить лишние файлы
						files = _.slice(files, 0, this.limitCount - this.files.length);
					}
				}

				_.forEach(files, (v, k) => {
					let fname = v.fname || v.file_name || v.name;
					this.files.push({
						result: (isNewMessage ? 'success' : 'edited'),
						localId: v.localId || this.getFreeLocalId(),
						file_id: v.FID || v.file_id || v.id,
						file_name: fname,
						file_path: v.file_path || v.path || '',
						file_path_hash: v.s || v.file_path_hash || null,
						file_extension: '.' + fname.substring(fname.lastIndexOf('.') + 1),
						base64: v.miniatureUrl || v.base64 || 'none',
						file_is_remote: isRemoteFiles,
					});
				});
			},

			/**
			 * Показать диалог выбора файла
			 * @param {object} event
			 */
			select(event) {
				if (this.inactive) {
					return;
				}
				// Очистить input
				document.getElementById(this.formId).reset();
				this.$refs[this.inputName].value = '';
				// Показать диалог выбора файла
				this.$refs[this.inputName].click();
			},

			/**
			 * Обработчик события на выбор файла
			 * @param {object} event
			 */
			upload(event, pureData = false) {
				if (this.isDestroyed) {
					event.preventDefault();
					return;
				}
				var files = [];
				if (pureData) {
					files = event;
				} else {
					files = event.target.files || event.dataTransfer.files;
				}
				files = _.toArray(files);
				// Если пользователь не выбрал ни одного файла, выйти
				if (!files.length)
					return;
				// Если файлов больше лимита
				if (this.files.length + files.length > this.limitCount) {
					// Удалить лишние файлы
					files = _.slice(files, 0, this.limitCount - this.files.length);
				}
				// Загрузить файлы на сервер
				var self = this;
				_.forEach(files, (file) => {
					// Если файл по размеру меньше лимита
					if (file.size / 1024 / 1024 <= self.limitSize) {
						// Добавить файл в коллекцию в статусе "loading"
						// и запомнить индекс
						let fileDesc = self.getFileDescription(file, 'loading', '');
						if (this.makethumbs) {
							// Читаем изображение для создания превью
							let reader = new FileReader();
							reader.onload = () => {
								let image = new Image();
								image.src = reader.result;
								image.onload = () => {
									self.files.push(fileDesc);
									let currentIndex = self.files.indexOf(fileDesc);
									Vue.set(this.files[currentIndex], 'base64', reader.result);
									this.uploadFile(file, fileDesc);
								}
								image.onerror = () => {
									self.files.push(fileDesc);
									let currentIndex = self.files.indexOf(fileDesc);
									Vue.set(this.files[currentIndex], 'base64', 'none');
									this.uploadFile(file, fileDesc);
								}
							}
							reader.readAsDataURL(file);
						} else {
							self.files.push(fileDesc);
							this.uploadFile(file, fileDesc);
						}
					} else {
						// Если файл слишком большой
						self.files = _.concat(self.files, self.getFileDescription(file, "error", 'file_size_exceed'));
					}
				});
			},

			uploadFile(file, fileDesc) {
				// Сформировать запрос для отправки на сервер
				var formData = new FormData();
				formData.append("upload_files", file);
				if (this.makethumbs || this.withMiniature) {
					formData.append('with_miniature', 1);
					formData.append('second_user_id', this.secondUserId);
				}
				axios.post("/api/file/upload", formData, {
					cancelToken: new axios.CancelToken((c) => {
						this.cancelTokens.push(c);
						let currentIndex = this.files.indexOf(fileDesc);
						Vue.set(this.files[currentIndex], 'cancelToken', c);
					}),
					onUploadProgress: (progressEvent) => {
						let percentCompleted = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
						let currentIndex = this.files.indexOf(fileDesc);
						Vue.set(this.files[currentIndex], 'progress', percentCompleted);
					},
				}).then((response) => {
					let currentIndex = this.files.indexOf(fileDesc);
					Vue.set(this.files[currentIndex], 'progress', 100);
					// Файл загружен успешно
					if (response.data.result == "success") {
						_.assignIn(this.files[currentIndex], response.data);
					} else {
						// Ошибка загрузки файла
						Vue.set(this.files, currentIndex, this.getFileDescription(file, 'error', response.data.reason));
					}
				}).catch((e) => {});
			},

			triggerChange() {
				if (this.name) {
					window.bus.$emit('fileUploader-' + this.name + '.change');
				}
			},

			/**
			 * Удалить файл
			 * @param {Number} index
			 */
			remove: function (index, file = null) {
				if (this.inactive) {
					return;
				}
				if (this.files[index].result == 'edited') {
					this.files[index].result = 'deleted';
					this.triggerChange();
					return;
				}
				if (this.files[index].result == 'deleted') {
					this.files[index].result = 'edited';
					this.triggerChange();
					return;
				}
				let token = this.files[index].cancelToken || null;
				if (typeof token == 'function') {
					token();
				}
				this.files = _.filter(this.files, function (value, valueIndex) {
					return valueIndex != index;
				});
				this.triggerChange();
				if (file !== null) {
					this.$emit('removedFile', file)
				}

			},

			stopUploads: function () {
				_.forEach(this.cancelTokens, (v, k) => {
					v();
				});
				self.cancelTokens = [];
			},

			/**
			 * Очистить список файлов и остановить все загрузки
			 * @param {Number} index
			 */
			clearFiles: function () {
				this.stopUploads();
				this.files = [];
			},

			saveFiles: function () {
				this.stopUploads();
				return _.cloneDeep(this.files);
			},

			restoreFiles: function (files) {
				this.stopUploads();
				this.files = files;
			},

			/**
			 * Сформировать объект описания файла
			 * @param {object} file
			 * @param {string} status error|loading
			 * @param {string} typeError
			 * @return {object}
			 */
			getFileDescription: function (file, status, typeError) {
				var extension = "." + _.toLower(_.last(_.split(file.name, ".")));
				return {
					file_name: file.name,
					file_extension: extension,
					result: status,
					error: typeError ? this.errors[typeError] : '',
					localId: this.getFreeLocalId(),
					progress: 0,
				};
			},

			getFreeLocalId() {
				let id = this.freeLocalId;
				this.freeLocalId++;
				return this.name + id;
			},

			/**
			 * Иконка файла в зависимости от расширения
			 * @param {object} file
			 * @return {string} имя класса иконки
			 */
			getFileIconClass: function (file) {
				var extension = _.toLower(file.file_extension.slice(1));
				var className = null;
				_.forEach(this.fileIconClasses, function (extensions, otherClassName) {
					if (extensions.indexOf(extension) >= 0) {
						className = otherClassName;
					}
				});
				className = className ? className : this.defaultFileIconClass;
				className += ' file-icon';
				return className;
			},

			progressStyle(percent) {
				return {width: (this.animatedProgress ? percent : 100) + '%'}
			},
		},

	}
</script>
