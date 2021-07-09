<template>
	<div class="card container py-3 interview-sidebar-block">

		<div class="row px-3">
			<!-- Заголовок -->
			<h3 class="col pb-2">{{ t('Ваше интервью на Kwork') }}</h3>
		</div>
		<div class="row px-3 content">
			<!-- Описание -->
			<ul class="pl-3">
				<li>{{ t('Повышает доверие покупателей') }}</li>
				<li>{{ t('Укрепляет лояльность клиентов') }}</li>
				<li>{{ t('Увеличивает продажи') }}</li>
			</ul>
		</div>
		<div class="row px-3 more">
			<!-- Кнопка для открытия модального окна с доп. информацией -->
			<button class="button button-success" @click="modalShow = true">{{ t('Узнать больше!') }}</button>
		</div>
		<!-- Модальное окно -->
		<b-modal :content-class="'interview-modal'" :title="t('Интервью на Kwork')" id="modal1" class="interview-modal" v-model="modalShow" no-fade hide-footer>
			<hr class="mb-3">
			<p>{{ t('Вы классный исполнитель? Расскажите о себе потенциальным клиентам. Выделитесь среди лучших продавцов! Дайте интервью Kwork.') }}</p>
			<p class="mic">{{ t('Интервью будет опубликовано в блоге Kwork. Потенциальные клиенты увидят в вас эксперта и приятного в общении человека. Это положительно отразится на лояльности и продажах!') }}</p>
			<p>{{ t('В вашем профиле появится специальный блок «Интервью».') }}</p>
			<!-- Блок с примерами профилей -->
			<div class="examples mb-2">
				<p>{{ t('Посмотрите, как выглядит интервью в профиле продавцов:') }}</p>
				<div class="profiles">
					<div v-for="interviewProfile of interviewProfiles">
						<a target="_blank" :href="interviewProfile.seo">
							<div>
								<img :src="interviewProfile.avatar" />
							</div>
							<div>
								<div>
									<div>{{ interviewProfile.login }}</div>
									<div class="name">{{ interviewProfile.name }}</div>
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>
			<p>{{ interviewSettings == '0' ? t('Только для продавцов уровня “Профессионал” / “Продвинутый” и выше') : t('Только для продавцов уровня “Профессионал”') }}</p>
			<!-- Большая кнопка - ссылка на форму интервью -->
			<div class="d-flex justify-content-center mb-2">
				<a rel="nofollow" target="_blank" @click="linkClicked()" href="https://goo.gl/forms/rzF43KdZKeMaUrOe2" class="button button-success">{{ t('Отправить заявку на интервью') }}</a>
			</div>
			<p class="notice">{{ t('Ответьте на несколько вопросов и получите возможность стать героем рубрики “Интервью” в блоге Kwork.') }}</p>
		</b-modal>
	</div>
</template>
<script>
	/**
	 * Компонент отображает блок-уведомление о возможности дать интервью, а также содержит модальное окно с
	 * дополнительной информацией, открываемое по клику на кнопку. Обязательные свойства:
	 *   interview-profiles-json - данные профилей-примеров для модального окна в JSON
	 */

	// Модальные диалоги
	import { BModal, VBModal } from "bootstrap-vue";
	Vue.component("b-modal", BModal);
	Vue.directive("b-modal", VBModal);
	
	// Локализация
	import i18nMixin from "appJs/i18n-mixin";
	
	export default {
		mixins: [i18nMixin],

		data () {
			return {
				// Отображение модального окна
				modalShow: false,

				// Локализация компонента
				i18n: {
					en: {
						// Уведомление в сайдбаре
						'Ваше интервью на Kwork': 'Your interview on Kwork',
						'Повышает доверие покупателей': 'increases buyers’ confidence',
						'Укрепляет лояльность клиентов': 'strengthens clients’ loyalty',
						'Увеличивает продажи': 'increases sales',
						'Узнать больше!': 'More info',
						// Модальное окно
						'Интервью на Kwork': 'Interview on Kwork',
						'Вы классный исполнитель? Расскажите о себе потенциальным клиентам. Выделитесь среди лучших продавцов! Дайте интервью Kwork.': 'Are you a great executive? Tell the perspective clients about yourself. Stand apart from the other sellers! Give interview to Kwork.',
						'Интервью будет опубликовано в блоге Kwork. Потенциальные клиенты увидят в вас эксперта и приятного в общении человека. Это положительно отразится на лояльности и продажах!': 'Your interview will be published on Kwork Blog. The perspective clients will see you as an expert and nice person to communicate with. It will positively influence the clients’ loyalty and your sales!',
						'В вашем профиле появится специальный блок «Интервью».': 'You will see a special “Interview” section in your profile.',
						'Посмотрите, как выглядит интервью в профиле продавцов:': 'See how interview looks like in the profiles of:',
						'Только для продавцов уровня “Профессионал” / “Продвинутый” и выше': 'Only for ”Advanced” and ”Professional” sellers',
						'Только для продавцов уровня “Профессионал”': 'Only for ”Professional” sellers',
						'Отправить заявку на интервью': 'Send a request for interview',
						'Ответьте на несколько вопросов и получите возможность стать героем рубрики “Интервью” в блоге Kwork.': 'Answer several questions and get the opportunity to become a star of “Interview” section on Kwork blog.'
					}
				},			
			};
		},

		props: [
			// Данные примеров профилей с интервью (JSON)
			"interviewProfilesJson",
			// Настройки отображения блока интервью
            "interviewSettings"
		],

		created: function () {
			// Инициализировать mixin локализации
			this.i18nInit();

			// Распарсить профили-примеры из JSON
			this.interviewProfiles = JSON.parse(atob(this.interviewProfilesJson));
		},

		methods: {
			linkClicked: function() {
				$.post('/settings', {action: 'on_click_interview', foxtoken: $('input[name="foxtoken"]')[0].value}, (r) => {
					this.modalShow = false;
				});
			}
		}
	}
</script>