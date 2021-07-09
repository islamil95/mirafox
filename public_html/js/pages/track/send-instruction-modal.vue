<template>
	<b-modal ref="modal"
				 class="modal-radius send-instruction-modal" v-model="showInstructions"
			 	:title="t('Отправьте информацию по заказу')"
				 centered
				 static
				 no-enforce-focus
				 no-close-on-backdrop
		>
		<template slot="modal-header-close">
			<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1 1.14453L8.5 8.67383M16 16.2031L8.5 8.67383M8.5 8.67383L1 16.2031L16 1.14453" stroke="white" stroke-opacity="0.7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</template>

		<div class="modal-message">
			<template v-if="!isEmptyMessage">
				<div class="modal-message__title">{{title}}:</div>
				<div class="modal-message__text" v-html="message"></div>
				<div class="modal-message__files" v-if="instructionFiles" v-html="instructionFiles"></div>
			</template>
			<template v-else>
				<div class="modal-message__text empty" v-html="t('Предоставьте продавцу дополнительную информацию, которая позволит лучше понять задачу и быстрее приступить к работе.')"></div>
			</template>
		</div>
		<template slot="modal-footer">
			<div class="modal-footer__title">{{ t('Введите данные') }}</div>
			<div class="similar" v-if="(similar !== null)">
				<div class="similar-text">
					<svg width="16" height="19" viewBox="0 0 16 19">
						<use xlink:href="#copy-paste"></use>
					</svg>
					{{ t("Скопировать задание из другого заказа")}}:
				</div>
				<div class="similar-select select-dropdown">
					<div class="select-dropdown_title" @click="toggleSimilar" :class="{show:showSimilar}">
						<span>{{similarTitle}}</span>
						<i class="fa fa-chevron-down"></i>
					</div>
					<div class="select-dropdown_list" :class="{show:showSimilar}">
						<div class="select-dropdown_list-item align-items-center"
							 :class="{'selected':(this.selectedSimilar == null)}" @click="loadSimilarNull">
							<i class="align-items-center justify-content-center"></i>
							<span>
								{{ t('Нет') }}
							</span>
						</div>
						<template v-for="(item, key) in similar.ordersProvidedData">
							<div class="select-dropdown_list-item align-items-center"
								 :class="{'selected':(key == selectedSimilar)}" @click="loadSimilar(item, key)">
								<i class="align-items-center justify-content-center"></i>
								<span>
									{{getDropdownItemMessage(item, key)}}
								</span>
							</div>
						</template>
					</div>
					<div class="select-dropdown_backdrop" v-if="showSimilar" @click="toggleSimilar"></div>
				</div>

			</div>
			<textarea :placeholder="placeholderInstructions"
					  v-model="instructions">
			</textarea>
			<div class="textarea-used" v-bind:class="{error: !validLimitMessage}">
				<span class="js-textarea-used">0</span> {{ limitCountMessageText }}
			</div>
			<div class="uploader">
				<file-uploader v-model="files" ref="file_uploader" :blueDesign="true" :showError="false" :dragNDrop="showInstructions" :limitCount="filesLimitCount" :limitSize="filesLimitSize">
				</file-uploader>
			</div>
			<div class="buttons">
				<template v-if="this.$refs && this.$refs.file_uploader">
					<div class="file-uploader_error" v-if="this.$refs.file_uploader.hasError"
						 v-html="this.$refs.file_uploader.getError()">
					</div>
				</template>
				<div v-if="(emptyInstructionsError)" class="file-uploader_error error">
					{{t('Добавьте информацию или файлы, которые необходимы продавцу для начала работы')}}
				</div>
				<button class="kwork-button kwork-button_theme_green-filled" @click="sendForm">
					{{ t("Отправить информацию") }}
				</button>
			</div>
		</template>
	</b-modal>
</template>

<script>
	import i18nMixin from "appJs/i18n-mixin";
	import { BModal } from "bootstrap-vue";
	import fileUploader from 'appJs/file-uploader.vue';

	export default {
		name: 'SendInstructionModal',
		
		mixins: [i18nMixin],

		props: {
			limitCountMessage: {
				type: Number,
				default: 4000,
			},
		},

		data() {
			return {
				showInstructions: false,
				showSimilar: false,
				selectedSimilar: null,
				emptyInstructionsError: false,
				validLimitMessage: true,
				instructions: '',
				files: [],
				i18n: {
					en: {
						"Отправить информацию по заказу": "Send order information",
						"Скопировать задание из другого заказа": "Copy the text of the job from the order",
						"Отправить информацию продавцу": "Provide information on your order",
						"Отправить информацию": "Send information",
						"от": "from",
						"Введите информацию по заказу": "Enter order information",
						"Продавец запросил данные": "The seller requested the data",
						"Добавьте информацию или файлы, которые необходимы продавцу для начала работы": "Add the information and files that the seller needs to get started.",
						"Выберите заказ": "Choose an order",
						"из {{0}}": "from {{0}}",
						"Нет": "No",
						'Предоставьте продавцу дополнительную информацию, которая позволит лучше понять задачу и быстрее приступить к работе.': 'Provide the seller with additional information that will help you better understand the task and get to work faster.',
					}
				}
			}
		},
		
		computed: {
			title: function () {
				return t('Продавец запросил данные');
			},
			notValidFiles: function () {
				return !this.files.length;
			},
			isValidateLimitMessage: function() {
				return this.instructions.trim().length <= this.limitCountMessage;
			},
			message: function () {
				return this.$root.message;
			},
			instructionFiles: function () {
				return this.$root.instructionFiles;
			},
			similar: function () {
				return this.$root.similar
			},
			similarTitle: function () {
				let similarTitle = t("Выберите заказ");

				if (this.selectedSimilar) {
					similarTitle = this.getSimilarSelectedName(this.selectedSimilar);
				}

				return similarTitle;
			},

			limitCountMessageText: function () {
				return t('из {{0}}', [this.limitCountMessage]);
			},

			isEmptyMessage () {
				return this.message.trim().length === 0;
			},

			placeholderInstructions() {
				return !this.isEmptyMessage ? t('Предоставьте продавцу дополнительную информацию, которая позволит лучше понять задачу и быстрее приступить к работе.') : t('Введите информацию по заказу');
			},
			filesLimitCount() {
				return config.track.fileMaxCount;
			},
			filesLimitSize() {
				return config.track.fileMaxSize;
			}
		},

		watch: {
			showInstructions() {
				if (window.appFiles) {
					window.appFiles.setDragNDropBlocked(this.showInstructions);
				}
			},

			instructions: function () {
				let value = this.instructions;
				$(".js-textarea-used").html(value.trim().length);

				$('#message_body1').val(value);
				$('#mobile_message').val(value);

				this.validLimitMessage = this.isValidateLimitMessage;

				this.emptyInstructionsError = false;
			},
			files: function (value) {
				if (!Object.values(value).filter(file => file.isSimilar).length) {
					this.cleanSimilarHtml()
				}
				window.appFiles.files = this.$refs.file_uploader.files;

				this.emptyInstructionsError = false;
			}
		},

		created: function () {
			this.i18nInit();
		},

		mounted() {
			this.enableCloseOnEsc();
			this.$root.$on('bv::modal::shown', (bvEvent, modalId) => {
				bvEvent.vueTarget.$refs.content.removeAttribute('tabindex');
			});

			let $modalOpenButton = $('.js-send-instruction-link');
			if ($modalOpenButton.length > 0) {
				$modalOpenButton.off().on('click', () => this.openModal());
			}
		},

		methods: {
			enableCloseOnEsc() {
				document.addEventListener('keydown', evt => {
					if (evt.keyCode === 27 && this.showInstructions) {
						this.closeModal()
					}
				})
			},

			getSimilarSelectedName(id) {
				let selectedSimilarOption = this.similar.ordersProvidedData[id];
				let pid = this.similar.similarOrderInfo[id];
				let similarTitle = '';

				if (selectedSimilarOption.message) {

					similarTitle = selectedSimilarOption.message;
				} else if (this.similar.similarDataKworks[pid]) {

					similarTitle = this.similar.similarDataKworks[pid];
				}

				return similarTitle.replace(/&quot;/g,'"')
					.replace(/&amp;/g, "&")
					.replace(/&gt;/g, ">")
					.replace(/&lt;/g, "<")
					.replace(/\\(.?)/g, "\\");
			},

			getDropdownItemMessage(item, key) {

				let date = new Date();
				date.setTime(parseInt(this.similar.similarDataOrders[key]) * 1000);

				let optionName = this.getSimilarSelectedName(key);

				return `${optionName.slice(0, 20) + (optionName.length > 20 ? '... ' : ' ')} ${t('от')} ${date.toLocaleDateString()}`;
			},
			openModal() {
				this.showInstructions = true;
			},
			loadSimilar(item, key) {
				this.selectedSimilar = key;
				let self = this;
				$.post('/api/order/getorderprovideddata', {
					'orderId': key
				}, function (answer) {
					if (answer !== false) {
						self.files = [];
						self.instructions = answer.message.replace(/\\(.?)/g, "\\");

						if (Object.values(answer.files).length) {
							answer.files = Object.values(answer.files).map(item => {
								item.isSimilar = true;
								return item;
							});
							self.files = answer.files;
							self.cleanSimilarHtml();
						}

						self.$refs.file_uploader.applyFileList(self.files, true, true);
						window.appFiles.files = self.$refs.file_uploader.files;
					}

				}, 'JSON');
				this.showSimilar = false;
			},
			loadSimilarNull() {
				this.selectedSimilar = null;

				this.instructions = '';

				this.$refs.file_uploader.applyFileList([], true, true);
				window.appFiles.files = this.$refs.file_uploader.files;

				this.showSimilar = false;
			},
			cleanSimilarHtml: function () {
				$('.similar_files_data').html('');
			},
			toggleSimilar() {
				this.showSimilar = !this.showSimilar;
			},
			closeModal() {
				this.showInstructions = false;
			},
			sendForm() {
				this.validLimitMessage = this.isValidateLimitMessage;
				if (this.notValidFiles && this.instructions.trim().length === 0) {
					this.emptyInstructionsError = true;

					return;
				}

				if (!this.validLimitMessage) {
					return;
				}
				
				this.emptyInstructionsError = false;
				TrackUtils.submitInstructionForm();
				this.closeModal();
			}
		},
		components: {
			'b-modal': BModal,
			'file-uploader': fileUploader,
		}
	}
</script>