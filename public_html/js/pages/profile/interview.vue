<template>
	<div class="interview-part">
		<div class="interview-block">
			<!-- Заголовок блока -->
			<h2>{{ t('Интервью с продавцом') }}</h2>
			<div class="card">
				<!-- Подзаголовок в углу блока -->
				<div class="corner">{{ t('Интервью') }}</div>
				<div class="content">
					<div>
						<!-- Изображение -->
						<img :src="interview.image" />
					</div>
					<div>
						<!-- Заголовок анонса -->
						<a class="title" :href="interview.link">{{ interview.title }}</a>
						<!-- Текст анонса -->
						<div class="text" v-clampy="4">{{ interview.text }}&hellip;</div>
						<!-- Ссылка на полную статью -->
						<a class="link" :href="interview.link">{{ t('Читать интервью в блоге Kwork') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	/**
	 * Компонент отображает блок с анонсом интервью в профиле пользователя. Обязательные свойства:
	 *   interview-data-json - данные интервью в JSON
	 */

	// Компонент для обрезки текста с использованием многоточия
	import clampy from '@clampy-js/vue-clampy';
	Vue.use(clampy);

	// Локализация
	import i18nMixin from "appJs/i18n-mixin";

	export default {
		directives: {
			clampy
		},
		mixins: [i18nMixin],

		data () {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'Интервью с продавцом': 'Interview with the seller',
						'Интервью': 'Interview',
						'Читать интервью в блоге Kwork': 'Read the interview in Kwork blog',
					}
				},

				// Статьи базы знаний
				interview: {},
			};
		},

		props: [
			// Данные интервью (JSON)
			"interviewDataJson"
		],

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();

			// Распарсить данные интервью из JSON
			this.interview = JSON.parse(atob(this.interviewDataJson));
		}
	}
</script>