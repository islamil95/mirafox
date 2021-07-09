<template>
	<div v-if="isEdit" class="content-c">
		<!-- Блок с формой редактирования -->
		<div class="edit-message">
		
			<div v-if="quote && !deleteQuote" class="js-message-quote message-quote message-quote--conversation" :data-quote-id="quote.inbox_message_id">
				<div class="message-quote__tooltip tooltipster m-hidden"
						data-tooltip-side="right"
						:data-tooltip-text="t('Нажмите, чтобы перейти к цитате')"></div>
				<div class="message-quote__login">{{ quote.author.username }}</div>
				<div class="message-quote__text"><div>{{ quote.message }}</div></div>
				<div class="js-message-quote-remove message-quote__remove" @click="markQuoteToDelete" style="display: block;"></div>
			</div>
			<!-- Текстовое поле -->
			<textarea :maxlength="maxLength" class="message-box" v-model="editMessage" @focus="focusMessage"></textarea>
			
			<!-- Загрузчик файлов -->
			<file-uploader ref="editFileUploader" v-model="editFiles" @change="onChangeFileUploader($event)" :with-miniature="true" :second-user-id="secondUserId"></file-uploader>
			
			<!-- Кнопки отменить/отправить редактируемое сообщение -->
			<button @click="cancelEditMessage" class="white-btn" type="button">{{ t('Отмена правки') }}</button>
			<button v-if="isAccessEdit" @click="sendEditMessage" :disabled="!isDisabledSendBtn" class="green-btn send-btn" type="button">{{ t('Отправить') }}</button>
			<div class="clear"></div>
			
			<!-- Отображение ошибок -->
			<ul class="modal-error-list">
				<li v-for="error in errorsList">
					{{ error }}
				</li>
			</ul>		
		</div>
	</div>
</template>
<script>
	/*
	 * Компонент отображает форму редактирования сообщений
	*/
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	
	export default {
		mixins: [i18nMixin],
		data () {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'Отправить': 'Send',
						'Отмена правки': 'Cancel',
					}
				},
				// Текст сообщение
				editMessage: "",
				// В переменной записываем исходный текс сообщения для определения изменилось сообщение или нет
				oldEditMessage: "",
				// Список файлов
				editFiles: [],
				// В переменной записываем исходный список файлов для определения изменилось сообщение или нет
				oldEditFiles: [],
				// Список ошибок
				errorsList: [],
				// Переменная для понимния есть ли ошибки в файлах
				hasFileError: false,
				// Нужно ли удалить цитату
				deleteQuote: false,
				secondUserId: parseInt(window.conversationUserId),
				isChat: false,
			};
		},	

		props: {
			// Id сообщения
			id: [String, Number],
			// Текс сообщения
			message: {
				type: String,
				default: '',
			},
			// Файлы прикрепленные к сообщению
			files: {
				type: Array,
				default: () => [],
			},
			// Включен ли режим редактирования
			isEdit: {
				type: Boolean,
				default: false,
			},
			// Цитата
			quote: {
				type: Object,
				default: null
			},
			// Максимальная длина сообщения
			maxLength: {
				type: Number,
				default: 0
			}
		},
		
		watch: { 
			/**
			 * Если включен режим редактирования заполняем текстовую область и файлы
			 */
			isEdit: function (val) {
				if (val == true) {
					this.editMessage = this.oldEditMessage = he.decode(this.message);
					this.editFiles = [];
					this.oldEditFiles = [];
					Vue.nextTick(() => {
						this.$refs.editFileUploader.applyFileList(this.files);
						// Запоминаем какие файлы были в начале радактирования
						_.forEach(this.files, (v, k) => {
							this.oldEditFiles.push(v.FID);
						});
					});
				}				
			},
		},
		computed: {
			cleanEditMessage:function() {
				return this.editMessage.trim();
			},
			/**
			 * Проверяем можно ли посылать отредактированное сообщение
			 */
			isAccessEdit: function() {
				let files = [];
				// Получаем текущий список файлов
				_.forEach(this.editFiles, function(v, k) {
					if (v.result != 'deleted') {
						files.push(v.file_id);
					}
				});
				// ( Если список фалов не изменился и текст сообщения не изменился и есть цитата и не удаляем её ) или ( список файлов пустой и нет текста )
				if ((_.isEqual(this.oldEditFiles, files) && this.oldEditMessage == this.editMessage && (!this.quote || (this.quote && !this.deleteQuote))) || (files.length < 1 && this.editMessage == '')) {
					return false;
				}
				
				return true;
			},

			/**
			 * блоируется кнопка сохранения, если есть файлы с ошибкой
			 */
			isDisabledSendBtn: function () {
				return !this.hasFileError;
			},
		},

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();

			this.isChat = window.isChat || false;
		},
		
		methods: {	
			/**
			 * Пометить цитату к удалению
			 */
			markQuoteToDelete: function () {
				this.deleteQuote = true;
			},

			/**
			 * Отправить на редактирование
			 */
			onChangeFileUploader: function(state) {
				this.hasFileError = !state;
			},	
			
			/**
			 * Отправить на редактирование
			 */
			sendEditMessage: function() {
				if (!this.isAccessEdit) {
					return false;
				}
				let url = location.protocol + '//' + location.host + '/inbox_edit_message';
				let data = new FormData();


				data.append('message_body', this.cleanEditMessage);
				data.append('submg', '1');
				data.append('message_id', this.id);
				data.append('projectId', window.projectId);
				if (this.quote && !this.deleteQuote) {
					data.append('quoteId', this.quote.MID);
				}
				$.each(this.editFiles, (k, v) => {
					if (v.result == 'success') {
						data.append('conversations-files-edit[new][]', v.file_id);
					} else if (v.result == 'deleted') {
						data.append('conversations-files-edit[delete][]', v.file_id);
					}
				});
				// Отправляем событие на родительский контроллер, что произошла операция отправления
				window.bus.$emit('addSendingCount');
				axios.post(url, data).then((r) => {
					if (this.isChat) {
						if (!r.data.success) {
							return false;
						}
						//tmp - поправить после бэка
						r.data = JSON.parse(r.data.data);
					}

					let isEndEdit = true;
					if (typeof r.data === 'object' && r.data !== null) {
						if (r.data.errors != undefined) {
							this.errorsList = r.data.errors;
							isEndEdit = false;
						} else {
							window.bus.$emit('updateMessage', r.data);
						}
					}
					// Если не было ошибки скрываем режим редактирования
					if(isEndEdit) {
						this.$emit('end-edit');
					}
				}).catch(() => {}).then(() => {
					window.bus.$emit('removeSendingCount');		
				});
			},

			/**
			 * Отменить редактирование
			 */
			cancelEditMessage: function() {
				this.deleteQuote = false;
				this.$emit('end-edit');
			},

			/**
			 * Прячет поле ввода сообщения при фокусе в поле редактирования
			 */
			focusMessage: function () {
				if (!window.isChat) {
					return false;
				}
				chatModule.messageFormState('hide');
			},
		}
	}
</script>