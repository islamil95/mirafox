<template>
	<div v-if="files.length > 0" class="conversation-files-block">
		<div v-if="!isChat" class="header-s">{{ t('Файлы диалога') }}</div>
		<div class="bgLightGray p15-20 f14 body-s">
			<div v-for="(v, k) in lastFiles" :key="k">
				<a target="_blank" :href="v.file_path || ''" class="color-text" :class="{'js-popup-file': (fileRetentionPeriodNoticeCount > 0)}" :download="fileRetentionPeriodNoticeCount > 0">
					<i v-if="!isChat" :class="uploadedFileIcon(v.file_name || v.fname)"></i>
					<span>{{ shortName(v.file_name || v.fname) }}</span>
				</a>
			</div>
		</div>
	</div>
</template>
<script>
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	import uploadedFileMixin from "appJs/uploaded-file-mixin";
	// Приведение относительных ссылок к абсолютным (CDN)
	import cdnMixin from "appJs/cdn-mixin.js";

	export default {
		mixins: [i18nMixin, uploadedFileMixin, cdnMixin],

		data () {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'Файлы диалога': 'Chat files',
					}
				},

				files: [],
				fileRetentionPeriodNoticeCount: -1,

				isChat: false,
			};
		},

		computed: {
			lastFiles: function() {
				let firstIndex = this.files.length - 10;
				if (firstIndex < 0) {
					firstIndex = 0;
				}
				return _.slice(this.files, firstIndex);
			},
		},

		methods: {
			shortName: function(fileName) {
				let name = he.decode(fileName);
				if(name.length > 25) {
					let dotPos = name.lastIndexOf('.');
					let ext = name.substring(dotPos + 1, name.length);
					name = name.substring(0, 19) + '...' + ext;
				}
				return name;
			},

			addFiles: function(files) {
				_.forEach(files, (v, k) => {
					this.files.push(v);
				});

				// Передает наличие файлов выбранного диалога
				window.bus.$emit('hasConversationFiles', this.files.length > 0);
			},
			
			removeFiles: function(files) {
				_.forEach(files, (v, k) => {
					let finded = -1;
					_.forEach(this.files, (v2, k2) => {
						if (v2.file_path == v.file_path) {
							finded = k2;
							return false;
						}
					});
					if (finded > -1) {
						this.files.splice(finded, 1);
					}
				});

				// Передает наличие файлов выбранного диалога
				window.bus.$emit('hasConversationFiles', this.files.length > 0);
			},

			/**
			 * Подгружает файлы выбранного диалога
			 * @param newValues
			 */
			loadConversationFiles: function(newValues) {
				_.assignIn(this, newValues);
			},
		},

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();

			// Получить данные со страницы
			this.files = window.conversationFiles || [];
			this.fileRetentionPeriodNoticeCount = window.fileRetentionPeriodNoticeCount || -1;

			this.isChat = window.isChat || false;

			// Событие, чтобы другие компоненты могли подгружать файлы выбранного диалога
			window.bus.$on('loadConversationFiles', (newValues) => {
				this.loadConversationFiles(newValues);
			});
		},
	}
</script>