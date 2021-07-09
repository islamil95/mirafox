<template>
    <div class="file-list" v-if="files.length > 0">
		<div v-for="(v, k) in files" :key="k">
			<div>
				<a v-if="!v.status || v.status == fileStatusActive" target="_blank" :href="convertHashTags(v.file_path)" class="color-text" :class="{'js-popup-file': (fileRetentionPeriodNoticeCount > 0)}">
					<i :class="uploadedFileIcon(v.fname || v.file_name)"></i>
					<span>{{ shortName(v.fname || v.file_name) }}</span>
				</a>
				<a v-else>
					<i :class="uploadedFileIcon(v.fname || v.file_name)"></i>
					<span class="text-muted">{{ t('Срок хранения файла истек') }}</span>
				</a>
			</div>
		</div>
	</div>
</template>

<script>
import i18nMixin from "appJs/i18n-mixin";  // Локализация
import uploadedFileMixin from "appJs/uploaded-file-mixin";  // Прикреплённые файлы

export default {
	mixins: [uploadedFileMixin, i18nMixin],

	props: {
		files: {
			type: Array,
			default: [],
		},
    },

	data() {
		return {
			i18n: {
				en: {
					'Срок хранения файла истек': 'Retention period of the file is expired',
				},
			},
			fileRetentionPeriodNoticeCount: -1,
			fileStatusActive: null,
		};
	},

	created() {
		this.i18nInit();
		this.fileRetentionPeriodNoticeCount = window.fileRetentionPeriodNoticeCount || -1;
		this.fileStatusActive = window.fileStatusActive || null;
	},

	methods: {
		shortName(fileName) {
			let name = he.decode(fileName);
			if(name.length > 60) {
				let dotPos = name.lastIndexOf('.');
				let ext = name.substring(dotPos + 1, name.length);
				name = name.substring(0, 54) + '...' + ext;
			}
			return name;
		},

		/**
		 * На случай если в имени картинки символ #
		 * @param file_path
		 * @returns {*}
		 */
		convertHashTags(file_path) {
			return file_path.replace(/#/g, "%23")
		},
	},
}
</script>