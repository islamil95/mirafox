<template>
	<div class="conversation-message-block" :class="{'is-edit': isEdit }">
		<!-- Отображение статьи из базы знаний -->
		<div v-if="message.kb_article_id && message.article" class="kb support conversation-message mt5 mb10" :data-assign="(message.assign_id ? message.assign_id : 0)">
			<div class="article-wrapper mx-auto p0">
				<div class="article">
					<div class="question d-flex align-items-center justify-content-between">
						<div>
							<div class="f14 color-gray">{{ t('Тема обращения') }}</div>
							<b>{{ message.article.question }}</b>
						</div>
						<div class="floatright">
							<div class="f14 color-gray">{{ t('Статус') }}</div>
							<div>{{ kbStatusText }}</div>
						</div>
					</div>
					<div v-if="message.article.answer_formatted.trim()" class="answer fr-view" v-html="message.article.answer_formatted"></div>
				</div>
			</div>
		</div>
		<!-- Блок информации о новом(ых) сообщении(ях) -->
		<div v-if="isChat && unreadCount > 0 && message.isFirstUnread" class="conversation-new-messages-block">
			<span class="conversation-new-messages-block__item">
				{{ unreadCount > 1 ? t('Новые сообщения') : t('Новое сообщение') }}
			</span>
		</div>
		<!-- Блок информации о переписке в завершенном заказе -->
		<div v-if="this.message.hasInfoBlock && this.message.infoBlockPosition === 'top'" class="mb5" :class="{mt40: !isChat}" v-html="getInfoBlockHtml(false)"></div>
		<!-- Блок информации о срочном ответе -->
		<div v-if="hasChatWarning" class="conversation-warning-block" @click="showChatWarningPopup">
			{{ t('Быстрее ответьте на сообщение, чтобы повысить шанс продать услугу!') }}
			<span class="tooltipster chat__link chat__link_color_blue"
				  :data-tooltip-text="chatWarningContent">
					{{ t('Подробнее...') }}
				</span>
		</div>
		<!-- Блок самого сообщения -->
		<div v-if="(!message.hidden || timerSeconds > -1) && (message.type != types.offerKworkNew || message.offerOrderData)" :class="{'cm-unsended': !message.MID, 'cm-message': true, 'cm-unread': (message.unread == 1 && actorId != message.MSGFROM), 'cm-take-away' : actorIsVirtual && message.takeAway, 'cm-hover-disabled': (isChat && !hasChatActions)}" @mouseover="mouseOverMessage" @mouseleave="mouseLeaveMessage">
			<!-- Шапка с кнопками управления -->
			<div v-if="isChat" class="header-c">
				<div v-if="isHoverMessage && hasChatActions" class="header-c__icons">
					<div v-if="!message.type && actorId == message.MSGTO && !isGuestDialog"
						 class="header-c__icons-item tooltipster tooltipster-dark"
						 data-tooltip-theme="dark"
						 :data-tooltip-text="t('Пожаловаться')"
						 @click="$emit('complain', message)">
						<span class="fl-complain"></span>
					</div>
					<template v-else-if="message.MID && message.MID > 0 && !message.type && actorId == message.MSGFROM  && !isEdit && hasOperations">
						<!-- Редактирование сообщения -->
						<div v-if="message.unread == 1"
							 class="header-c__icons-item tooltipster tooltipster-dark"
							 data-tooltip-theme="dark"
							 :data-tooltip-text="t('Изменить')"
							 @click="isHoverMessage ? beginEdit() : null">
							<span class="fa fa-pencil"></span>
						</div>
						<!-- Удаление сообщения -->
						<div class="header-c__icons-item tooltipster tooltipster-dark"
							 data-tooltip-theme="dark"
							 :data-tooltip-text="t('Удалить')"
							 @click="$emit('delete', message)">
							<span class="fl-trash"></span>
						</div>
					</template>
					<!-- Подозрительное сообщение -->
					<template v-if="actorIsVirtual">
						<div v-if="!message.takeAway && !message.takeAwayPenalty"
							 class="header-c__icons-item tooltipster tooltipster-dark"
							 data-tooltip-theme="dark"
							 :data-tooltip-text="t('Отметить как подозрительное')"
							 @click="$emit('mark_take_away', message)">
							<span class="fa fa-minus-circle"></span>
						</div>
						<div v-else-if="message.takeAway"
							 class="header-c__icons-item header-c__icons-item_cursor_default tooltipster tooltipster-dark"
							 data-tooltip-theme="dark"
							 :data-tooltip-text="t('Подозрительное')">
							<span class="fa fa-minus-circle"></span>
						</div>
						<div v-else-if="message.takeAwayPenalty"
							 class="header-c__icons-item header-c__icons-item_cursor_default tooltipster tooltipster-dark"
							 data-tooltip-theme="dark"
							 :data-tooltip-text="t('Был назначен штраф за контакты')">
							<span class="fa fa-minus-circle color-green"></span>
						</div>
					</template>
				</div>
			</div>
			<div v-else class="header-c">
				<i class="fa fa-quote-right" v-if="!message.type && !isEdit && hasQuote && isHoverMessage" :title="t('Цитировать')" @click="$emit('quote', message)"></i>

				<template v-if="!message.type && actorId == message.MSGTO && !isGuestDialog">  
					<i class="fa fa-exclamation-triangle" :title="t('Пожаловаться')" @click="$emit('complain', message)"></i> 
				</template>
				<template v-else-if="message.MID && message.MID > 0 && !message.type && actorId == message.MSGFROM  && !isEdit && isHoverMessage && hasOperations">
					<i class="kwork-icon icon-pencil" :title="t('Изменить')" @click="isHoverMessage ? beginEdit() : null"></i>
					<i class="kwork-icon icon-bin" :title="t('Удалить')" @click="$emit('delete', message)"></i>
				</template>
				<template v-if="actorIsVirtual">
					<i v-if="!message.takeAway && !message.takeAwayPenalty" class="fa fa-minus-circle" :title="t('Отметить как подозрительное')" @click="$emit('mark_take_away', message)"></i>
					<i v-else-if="message.takeAway" class="fa fa-minus-circle" :title="t('Подозрительное')"></i>
					<i v-else-if="message.takeAwayPenalty" class="fa fa-minus-circle color-green" style="cursor: default;" :title="t('Был назначен штраф за контакты')"></i>
				</template>
			</div>
			<!-- Тело сообщения -->
			<div class="body-c">
				<!-- Аватар -->
				<div class="cm-avatar">
					<a v-if="!isSupportUser" :href="'/' + message.userProfileUrl" :title="message.mfrom">
						<user-avatar :url="message.profilepicture" :username="senderName"></user-avatar>
					</a>
					<a v-else>
						<user-avatar :url="message.profilepicture" :username="senderName"></user-avatar>
					</a>
				</div>
				<!-- Основная часть -->
				<div class="main-c">
					<!-- Имя отправителя и время -->
					<div class="info-c">
						<div v-if="isSupportUser" class="username-c">
							{{ senderName }}
						</div>
						<div v-else class="username-c">
							<i v-if="isOnline[message.MSGFROM]" class="dot-user-status dot-user-online inbox-online-ico m-ml0"></i>
							<i v-else class="dot-user-status dot-user-offline_dark inbox-online-ico m-ml0"></i>
							<a class="t-profile-link" :href="'/user/' + senderName.toLowerCase()">{{ senderName }}</a>
						</div>
						<message-time :time="parseInt(message.time)" :updatedAt="parseInt(message.updated_at)" />
					</div>
					<!-- Содержимое сообщения -->
					<div v-if="!isEdit" class="content-c">
						<!-- Запрос на индивидуальный кворк -->
						<div v-if="message.type == types.customRequest" class="content-offer-c">
							<div class="t-align-c">
								<i class="ico-quest-info"></i>
								<h3 class="font-OpenSansSemi mb15 js-message-text"
									:class="{'content-offer-c__title': isChat}">
									{{ message.typeTitle }}
								</h3>
								<div v-if="actorId != message.MSGFROM">
									{{ requestMessageText }}
								</div>
								<div v-else :class="{'content-offer-c__subtitle': isChat}">
									{{ t('Вы отправили запрос на индивидуальный кворк') }}
								</div>
							</div>
						</div>
						<!-- Предложение квока -->
						<div v-else-if="message.type == types.offerKworkNew" class="content-offer-c">
							<div class="t-align-c">
								<i class="ico-green-extras"></i>
								<h3 class="fw600 mb15 js-message-text">{{ message.typeTitle }}</h3>
							</div>
							<div v-html="message.offerOrderData.html"></div>
							<div v-if="message.offerOrderData.status == 'new' && proposeInboxOrder">
								<div v-if="actorId == message.offerOrderData.orderData.USERID">
									<div class="pt15 pb25 overflow-hidden">
										<button v-if="message.offerOrderData.canBeStaged && this.isOrderStageTester" class="green-btn pull-right ml10" v-bind:class="{ disabled: sendingApproveOffer }" @click="acceptOffer()">{{ t('Купить') }}</button>
										<button v-else class="green-btn pull-right ml10" v-bind:class="{ disabled: sendingApproveOffer }" @click="acceptOffer()">{{ t('Купить за ') }} <price-with-currency :value="message.offerOrderData.orderData.price" :currency="message.offerOrderData.orderData.currency_id"></price-with-currency></button>
										<button class="orange-btn inactive pull-right" @click="$emit('refuse', message)">{{ t('Не нужно, спасибо') }}</button>
									</div>
								</div>
								<div v-else>
									<div class="pt15 pb25 overflow-hidden">
										<button class="white-btn pull-right ml10" @click="$emit('cancel', message)">{{ t('Отменить предложение') }}</button>
									</div>
								</div>
							</div>
						</div>
						<!-- Заказ создан -->
						<div v-else-if="message.type == types.offerKworkDone" class="content-offer-c">
							<div class="t-align-c">
								<i class="ico-more-info"></i>
								<h3 class="font-OpenSansSemi mb15 js-message-text">{{ message.typeTitle }}</h3>
							</div>
							<div style="padding-top:15px; padding-bottom:25px;">
								{{ orderCreatedText }} <a :href="'/track?id=' + message.created_order_id">{{ t('Перейти к заказу') }}</a>
							</div>
						</div>
						<!-- Вы отменили предложение индивидуального кворка -->
						<div v-else-if="message.type == types.offerKworkPayerCancel || message.type == types.offerKworkWorkerCancel" class="content-offer-c">
							<div class="t-align-c">
								<i class="ico-red-extras"></i>
								<h3 class="font-OpenSansSemi mb15 js-message-text">{{ typeTitle }}</h3>
							</div>
						</div>
						<!-- Ответ на предложение -->
						<offer-quote v-if="message.additional && message.additional.offer" :offer="message.additional.offer"></offer-quote>
						<!-- Цитата -->
						<div v-if="message.quote" class="js-message-quote message-quote message-quote--write message-quote--conversation" :data-quote-id="message.quote_id">
							<div class="message-quote__tooltip tooltipster m-hidden"
								 data-tooltip-side="right"
								 :data-tooltip-text="t('Нажмите, чтобы перейти к цитате')"></div>
							<div class="message-quote__login">{{ message.quote.author.username }}</div>
							<div class="js-message-quote-text message-quote__text"><div v-html="messageQuoteHtml"></div></div>
						</div>
						<!-- Текст сообщения -->
						<div class="cm-message-html" :class="{'cm-message-html_empty': isChat && !messageText.length}" v-html="messageText"></div>
						<!-- Бюджет и срок запроса индивидуального кворка -->
						<div class="cs-budget-details" v-if="message.type == types.customRequest && ((message.budget && message.currency_id) || message.duration)">
							<div class="fw600">{{ t('Бюджет') }}: <price-with-currency :value="message.budget" :currency="message.currency_id"></price-with-currency></div>
							<div class="fw600">{{ t('Срок (дней)') }}: <span v-html="duration"></span></div>
						</div>
						<!-- Прикрепленные файлы -->
						<file-list v-if="message.status != 'deleted' && message.filesArray" :files="message.filesArray" />
						<!-- Кнопки для разрешения переписки -->
						<div class="cs-allow-buttons" v-if="message.type == types.inboxAllowRequest">
							<div v-if="!allowRequestSended && message.allowRequestInboxId">
								<div>
									<button type="button" class="btn green-btn" @click="allowConversationChoose(true)">{{ t('Разрешить переписку') }}</button>
									<button type="button" class="btn red-btn" @click="allowConversationChoose(false)">{{ t('Не разрешать') }}</button>
								</div>
							</div>
							<span v-if="allowRequestSended">{{ allowRequestSendedText }}</span>
						</div>
						<!-- Выставление звёзд -->
						<div class="cm-score" v-if="message.MSGFROM == supportUserId && !actorIsVirtual && message.supportName">
							<div v-if="scoreEdit || !message.support_score || message.support_score.score <= 0" class="cm-rate">
								<div class="cm-rating d-flex">
									<div class="cm-text">{{ t('Оцените работу специалиста:') }}</div>
									<div class="cm-stars">
										<div v-for="i in 5" :key="i" @mouseover="overStar(i)" @mouseout="outStar()" @click="clickStar(i)" :class="{'active': messageScore >= i}"></div>
									</div>
									<div class="cm-score-text">{{ scoreText }}</div>
								</div>
								<div class="cm-visibility d-flex flex-wrap">
									<div>{{ t('Оценку видит:') }}</div>
									<div><input type="checkbox" class="styled-checkbox" checked disabled><label>{{ t('администрация') }}</label></div>
									<div><input type="checkbox" :checked="messageAccess == 2" class="styled-checkbox"><label @click="changeAccess()">{{ t('специалист службы поддержки') }}</label></div>
								</div>
							</div>
							<div v-else class="cm-rated">
								{{ t('Оценка принята.') }} <a class="requiredInfo-attention-link cur" @click="editRate">{{ t('Изменить?') }}</a>
							</div>
						</div>
						<!-- Подсказки -->
						<div class="cs-tips" v-if="message.MSGTO == actorId && ((message.PID && message.showText) || message.type == types.customRequest)">
							<div class="request-not-correspond request-not-correspond_theme_track">
								<div class="request-not-correspond__title bold">
									<div v-if="message.type == types.customRequest">
										{{ t('Запрос покупателя не соответствует ни одному вашему кворку?') }}
									</div>
									<div v-else>
										{{ t('Запрос покупателя не соответствует кворку?') }}
									</div>
									<i class="ico-arrow-down request-not-correspond__title-icon"></i>
								</div>
								<div class="request-not-correspond__more-text">
									<p>{{ t('Kwork лучше ранжирует и чаще показывает покупателям кворки, по которым совершено больше покупок.') }} <span class="request-not-correspond__warning">{{ t('Отказываясь от заказа, вы теряете возможность повысить конверсию и уступаете место другим продавцам.') }}</span></p>
									<p><b>{{ t('Что делать?') }}</b></p>
									<ol class="request-not-correspond__list">
										<li class="request-not-correspond__list-item">{{ t('Создайте новые опции в своем кворке и предложите их покупателю. Покупатели приветствуют принцип «любой каприз за ваши деньги».') }}</li>
										<li class="request-not-correspond__list-item">{{ t('Создайте новый кворк, который покрывает задачу покупателя. Возможно и другие покупатели в будущем закажут его.') }}</li>
										<li v-if="actorIsAllowCustomRequest && actorLevel > 1" class="request-not-correspond__list-item" v-html="individualKworkTip"></li>
									</ol>
								</div>
							</div>
						</div>
						<!-- Статус сообщения -->
						<message-status-bar :unread="(message.unread == 1)" :sended="!!message.MID" :own="isOwn" :timerSeconds="timerSeconds" />
					</div>
					<edit-message :maxLength="4000" :is-edit="isEdit" :id="message.MID" :message="message.rawMessage" :files="message.filesArray" :quote="message.quote" @end-edit="endEdit()"></edit-message>
				</div>
			</div>
		</div>
		<!-- Блок информации о переписке в завершенном заказе -->
		<div v-if="this.message.hasInfoBlock && this.message.infoBlockPosition === 'bottom'" class="mb5" v-html="getInfoBlockHtml(false)"></div>
	</div>
</template>
<script>
	import i18nMixin from 'appJs/i18n-mixin.js';  // Локализация
	import textFormatMixin from 'appJs/text-format-mixin.js';

	import messageTime from 'appJs/message-time.vue';
	import messageStatusBar from 'appJs/message-status-bar.vue';
	import userAvatar from 'appJs/user-avatar.vue';

	export default {
		mixins: [i18nMixin, textFormatMixin],

		components: {
			'message-time': messageTime,
			'message-status-bar': messageStatusBar,
			'user-avatar': userAvatar,
		},

		data () {
			return {
				// Локализация компонента
				i18n: {
					en: {
						'Тема обращения': 'The topic of your request',
						'Статус': 'Status',
						'Пожаловаться': 'Complaint(s)',
						'Изменить': 'Change',
						'Удалить': 'Delete',
						'Вы отправили запрос на индивидуальный кворк': 'You have submitted your request for a Kwork offer',
						'Купить': 'Buy',
						'Купить за ': 'Buy for ',
						'Не нужно, спасибо': 'No, thank you',
						'Отменить предложение': 'Cancel the Kwork offer',
						'Перейти к заказу': 'Go to the order',
						'Бюджет': 'Budget',
						'Срок (дней)': 'Term (days)',
						'Разрешить переписку': 'Allow Correspondence',
						'Не разрешать': 'Do not allow',
						'Оцените работу специалиста:': 'Rate your service specialist:',
						'Оценку видит:': 'This rating will be seen by:',
						'администрация': 'administration',
						'специалист службы поддержки': 'Support Team specialist',
						'Оценка принята.': 'Rating has been accepted.',
						'Изменить?': 'Change?',
						'Запрос покупателя не соответствует ни одному вашему кворку?': 'The buyer\'s request does not match any of your Kworks?',
						'Запрос покупателя не соответствует кворку?': 'The buyer\'s request does not match your Kwork?',
						'Kwork лучше ранжирует и чаще показывает покупателям кворки, по которым совершено больше покупок.': 'Kworks are better ranked and most often shown to customers if they have a lot delivered orders.',
						'Отказываясь от заказа, вы теряете возможность повысить конверсию и уступаете место другим продавцам.': 'By refusing the order, you lose the opportunity to increase the conversion. You give way to other sellers.',
						'Что делать?': 'What should you do?',
						'Создайте новые опции в своем кворке и предложите их покупателю. Покупатели приветствуют принцип «любой каприз за ваши деньги».': 'Create new options in your Kwork and offer them to the buyer. It is important for buyers to see that you are ready to help them.',
						'Создайте новый кворк, который покрывает задачу покупателя. Возможно и другие покупатели в будущем закажут его.': 'Create a new Kwork that can solve the buyer\'s task. Other buyers will have an opportunity to order it in the future.',
						'Завершено': 'Closed',
						'Активно': 'Active',
						'Предложите <a href="/faq#question-{{0}}">индивидуальный кворк</a> под задачу покупателя.': 'Offer <a href="/faq#question-{{0}}">an individual kwork</a> to the buyer’s request.',
						'Спасибо, уведомление пользователю {{0}} отправлено.': 'Thank you, {{0}} notified.',
						'Заказ {{0}} создан.': 'The Kwork order {{0}} has been created.',
						'Вы отменили предложение индивидуального кворка': 'You canceled the offer of an individual kwork',
						'Вы отклонили предложение индивидуального кворка': 'You declined the offer of an individual kwork',
						'Покупатель {{0}} прислал запрос на индивидуальный кворк. Пожалуйста, ответьте покупателю как можно скорее': 'A buyer {{0}} has sent you a request for a Kwork offer. Please reply to the buyer as soon as possible',
						'Менеджер': 'Manager',
						'Служба поддержки': 'Support Team',
						'Запрос на разрешение переписки': 'Request for permission to communicate',
						'Автоуведомление': 'Automatic notification',
						'Отметить как подозрительное': 'Mark as take away',
						'Подозрительное': 'Take away',
						'Был назначен штраф за контакты': 'Penalty was assigned for contacts',
						'Новое сообщение': 'New message',
						'Новые сообщения': 'New messages',
					}
				},
				scoreVisibleForSupport: false,
				supportUserId: null,
				types: {},
				isGuestDialog: {},
				actorId: null,
				actorIsAllowCustomRequest: false,
				actorLevel: 0,
				actorIsVirtual: false,
				allowRequestSended: false,
				individualKworkUrl: '',
				proposeInboxOrder: '',
				scoreTexts: [
					'очень плохо',
					'плохо',
					'средне',
					'хорошо',
					'очень хорошо',
				],
				tempScore: 0,
				tempAccess: 0,
				scoreEdit: false,
				sendingApproveOffer: false,
				isOrderStageTester: false,
				
				// Для фиксация наведения мышкой на сообщение
				isHoverMessage: false,
				// Для пометки что мы в режиме радктирования
				isEdit: false,
				// Для кнопку удаления
				removable: true,
				// Отображать ли кнопку цитирования сообщения
				hasQuote: true,

				// Для чата
				isChat: false,
				isActorAvailableAtWeekends: false,
				isWeekends: false,
				chatWarningMessages: [],
			};
		},

		props: {
			message: {
				type: Object,
				required: true,
			},
			isOnline: {
				type: Object,
				required: true,
			},
			timerSeconds: {
				type: Number,
				required: true,
			},
			getInfoBlockHtml: {
				type: Function,
				required: true,
			},
			conversationMessageProps: {
				type: Object,
				required: true,
			},
			unreadCount: {
				type: Number,
				required: true,
			},
			userCsrf: {
				type: String,
				required: true
			}
		},

		watch: {
			/*
			 * Если сообщение прочитанно, выходим из режима редактирования
			*/
			unread: function(val) {
				if(val == 0 && !this.removable) {
					this.isEdit = false;
				}
			},
			/**
			 * Обновление данных для аттрибута тултипа data-tooltip-text
			 * @details https://michaelnthiessen.com/force-re-render/
			 */
			timeUpdateTooltip(newValue) {
				this.tooltipsterKey++;
			}
		},

		computed: {
			isOwn() {
				return (this.message.MSGFROM == this.actorId);
			},

			kbStatusText() {
				if (this.message.kbOpened) {
					return this.t('Активно');
				}
				return this.t('Завершено');
			},

			individualKworkTip() {
				return this.t('Предложите <a href="/faq#question-{{0}}">индивидуальный кворк</a> под задачу покупателя.', [this.individualKworkUrl]);
			},

			allowRequestSendedText() {
				return this.t('Спасибо, уведомление пользователю {{0}} отправлено.', [this.message.allowRequestUsername])
			},

			orderCreatedText() {
				return this.t('Заказ {{0}} создан.', [this.message.created_order_id]);
			},

			typeTitle() {
				if (this.message.typeTitle) {
					return this.t(this.message.typeTitle);
				}
				if (this.message.type == this.types.offerKworkWorkerCancel) {
					return  this.t('Вы отменили предложение индивидуального кворка');
				} else if(this.message.type == this.types.offerKworkPayerCancel) {
					return  this.t('Вы отклонили предложение индивидуального кворка');
				}
				return '';
			},

			messageQuoteHtml() {
				if (this.message.status === 'deleted') {
					return '';
				}

				let messageText = this.message.quote.message;
				if (messageText === '') {
					$.each(this.message.quote.files, (k, v) =>  {
						messageText += v.fname + ', ';
					});
					messageText = messageText.replace(/, $/g, '');
				}

				return messageText;
			},

			messageText() {
				if (this.message.status == 'deleted') {
					return '';
				}
				return this.formatText((this.message.rawMessage || ''), {
					bbcode: this.isSupportUser,
				});
			},

			requestMessageText() {
				return t('Покупатель {{0}} прислал запрос на индивидуальный кворк. Пожалуйста, ответьте покупателю как можно скорее', [this.message.mfrom]);
			},

			messageScore() {
				if (this.tempScore > 0) {
					return this.tempScore;
				} else if (this.message.support_score) {
					return this.message.support_score.score;
				}
				return 0;
			},

			messageAccess() {
				if (this.tempAccess > 0) {
					return this.tempAccess;
				} else if (this.message.support_score) {
					return this.message.support_score.access;
				}
				return 2;
			},

			scoreText() {
				return this.scoreTexts[this.tempScore - 1] || '';	
			},

			isSupportUser() {
				return (this.message.MSGFROM == this.supportUserId);
			},

			duration() {
				return (this.message.duration ? Math.round(this.message.duration / 86400) : '&mdash;');
			},

			senderName() {
				if (this.isSupportUser) {
					if (this.message.type == this.types.support) {
						if (this.message.supportTitle) {
							return this.message.supportTitle;
						} else if (this.message.supportName) {
							return t('Менеджер') + ' ' + this.message.supportName;
						} else {
							return t('Служба поддержки');
						}
					} else if (this.message.type == this.types.inboxAllowRequest) {
						return t('Запрос на разрешение переписки');
					} else if (this.message.type == this.types.auto || !this.message.type) {
						return t('Автоуведомление');
					}
				} else {
					return this.message.mfrom;
				}
				return '';
			},
			
			/*
			 * Нужно ли показывать кнопки редактировани/удаления сообщения
			*/
			hasOperations() {
				return this.message.unread == 1 || this.removable;
			},
			
			/*
			 * Переменную unread что бы следить за изменениями переменной message.unread
			*/
			unread() {
				return this.message.unread
			},

			/**
			 * Проверяет наличие варнинга о срочном ответе
			 * @returns boolean
			 */
			hasChatWarning: function () {
				if (!this.isChat) {
					return false;
				}
				return this.chatWarningMessages.indexOf(this.message.MID) !== -1 && (this.isActorAvailableAtWeekends || (!this.isActorAvailableAtWeekends && !this.isWeekends));
			},

			/**
			 * Проверяет наличие действий с сообщениями
			 * @returns boolean
			 */
			hasChatActions: function () {
				let chatActions = false;
				if (
					// Жалоба
					(!this.message.type && this.actorId == this.message.MSGTO && !this.isGuestDialog) ||
					// Редактирование / Удаление
					(this.message.MID && this.message.MID > 0 && !this.message.type && this.actorId == this.message.MSGFROM  && !this.isEdit && this.hasOperations) ||
					// Подозрительное сообщение
					(this.actorIsVirtual &&
						(!this.message.takeAway && !this.message.takeAwayPenalty) ||
						(this.message.takeAway) ||
						(this.message.takeAwayPenalty)
					)
				) {
					chatActions = true;
				}
				return chatActions;
			},

			/**
			 * SVG-иконки
			 */
			getIconDelete: {
				get: function () {
					return this.iconDelete;
				},
				set: function (newValue) {
					this.iconDelete = newValue;
				}
			},
			getIconComplain: {
				get: function () {
					return this.iconComplain;
				},
				set: function (newValue) {
					this.iconComplain = newValue;
				}
			},
		},

		created() {
			// Инициализировать mixin локализации
			this.i18nInit();

			this.supportUserId = window.supportUserId || null;
			this.types = window.conversationMessageTypes || null;
			this.isGuestDialog = window.isGuestDialog || false;
			this.actorId = window.actorId || null;
			this.actorIsVirtual = window.actorIsVirtual || null;
			this.actorIsAllowCustomRequest = window.actorIsAllowCustomRequest || null;
			this.actorLevel = window.actorLevel || null;
			this.individualKworkUrl = window.individualKworkUrl || '';
			this.proposeInboxOrder = window.proposeInboxOrder || false;
			this.fileRetentionPeriodNoticeCount = window.fileRetentionPeriodNoticeCount || -1;
			this.fileStatusActive = window.fileStatusActive || null;

			/**
			 * Переносим эти параметры в conversationMessageProps, т.к. зависят от выбранного диалога в чате
			 *
			 * this.conversationUserId = window.conversationUserId;
			 * this.isOrderStageTester = offer.isOrderStageTester;
			 * this.userAvatarColors = window.userAvatarColors || {};
			 */
			this.isChat = window.isChat || false;
			this.isActorAvailableAtWeekends = window.isActorAvailableAtWeekends || false;
			this.isWeekends = window.isWeekends || false;
			_.assignIn(this, this.conversationMessageProps);

			this.setRemovable();
		},

		mounted() {
			if (window.QuoteMessage) {
				this.$nextTick(function () {
					window.QuoteMessage.messageQuoteCropEllipsis($(this.$el));

					this.hasQuote = $('.message_body').is(':visible');
				});
			}
		},

		methods: {
			checkRead(store) {
				let el = $(this.$el);
				if (el.is(':within-viewport-bottom') && el.is(':within-viewport-top')) {
					if (this.readTimeout) {
						return;
					}
					this.readTimeout = setTimeout(() => {
						store.messagesToRead.push(mid);
					}, 1500);
				} else {
					if (v.readTimeout) {
						clearTimeout(v.readTimeout);
						v.readTimeout = null;
					}
				}
			},

			allowConversationChoose(allow) {
				if (this.message.allowConversationSending) {
					return;
				}
				this.message.allowConversationSending = true;
				let isAccept = (allow ? 1 : 0);
				let data = new FormData();
				data.append('action', 'allowRequest');
				data.append('inboxId', this.message.MID);
				data.append('page', $('#currentPage').text());
				data.append('isAccept', isAccept);
				axios.post('', data).then(() => {
					this.allowRequestSended = true;
				}).catch(() => {}).then(() => {
					this.message.allowConversationSending = false;
				});
			},

			overStar(i) {
				this.tempScore = i;
			},

			outStar() {
				this.tempScore = 0;
			},

			clickStar(i) {
				this.tempScore = i;
				this.sendRate();
			},

			changeAccess() {
				if (this.messageAccess != 1) {
					this.tempAccess = 1;
				} else {
					this.tempAccess = 2;
				}
				if (this.messageScore > 0) {
					this.sendRate();
				}
			},

			sendRate() {
				this.scoreEdit = false;
				this.$emit('rate', {
					score: this.messageScore,
					access: this.messageAccess,
				});
				this.tempScore = 0;
				this.tempAccess = 0;
			},

			editRate() {
				this.scoreEdit = true;
			},

			acceptOffer() {
				if (this.sendingApproveOffer) {
					return false;
				}

				let message = this.message;
				let csrf = this.userCsrf;

                if (message.offerOrderData.canBeStaged && this.isOrderStageTester) {

					window.OfferModal.init({
						price: message.offerOrderData.orderData.price,
						lang: message.offerOrderData.lang,
						action: "/approve_inbox_offer",
						orderId: message.offerOrderData.orderData.OID,
						userId: this.conversationUserId,

						form: {
							inboxId: message.MID,
							orderId: message.offerOrderData.orderData.OID,
							user_csrf: csrf,
						},

						stages: message.offerOrderData.orderData.stages,
						duration: message.offerOrderData.orderData.duration,
						initialDuration: message.offerOrderData.orderData.initial_duration ? message.offerOrderData.orderData.initial_duration : message.offerOrderData.orderData.duration,
						stageMaxIncreaseDays: message.offerOrderData.stageMaxIncreaseDays,
						stageMaxDecreaseDays: message.offerOrderData.stageMaxDecreaseDays,
						stageMinPrice: message.offerOrderData.stageMinPrice,
						customMinPrice: Math.round(message.offerOrderData.orderData.initial_offer_price) ? message.offerOrderData.orderData.initial_offer_price : message.offerOrderData.orderData.price,
						customMaxPrice: message.offerOrderData.customMaxPrice,
						offerMaxStages: offer.offerMaxStages,
						stagesPriceThreshold: offer.stagesPriceThreshold,
						controlEnLang: controlEnLang,
					});
					window.OfferModal.showModal();
				} else {
					this.sendActiveOffer();
				}
			},

			/**
			 * Активация предложенного кворка
			 */
			sendActiveOffer() {
				let that = this;
				let message = this.message;
				let csrf = this.userCsrf;
				let data = {
					inboxId: message.MID,
					orderId: message.offerOrderData.orderData.OID,
					user_csrf: csrf,
				};
				this.sendingApproveOffer = true;

				axios.post("/approve_inbox_offer", data)
					.then(function (response) {
						if (!response.data.success && response.data.code === 124) {
							show_balance_popup(response.data.needMoney, 'inbox', undefined, response.data.orderId);
						} else if (typeof (response.data.redirectUrl) !== 'undefined') {
							window.location.href = response.data.redirectUrl;
						} else {
							window.location.reload();
						}
					})
					.catch(function () {
						that.sendingApproveOffer = false;
					});
			},
			
			
			/**
			 * Наводим мышкой на сообщение
			 */
			mouseOverMessage() {
				// если находимся в режиме редактирова, то игнорируем событие
				if (!this.isEdit) {
					// Ждем 100 милисекунд чтобы в мобильной версии при клике на сообщения в облость редактирования
					// не срабатывало события клика на редактирование
					setTimeout(() => {
						this.isHoverMessage = true;
					}, 100);
				}
			},
			
			/**
			 * Увод мышки из области сообщения
			 */
			mouseLeaveMessage() {
				// Ждем 100 милисекунд чтобы в мобильной версии при клике на сообщения в облость редактирования
				// не срабатывало события клика на редактирование
				setTimeout(() => {
					this.isHoverMessage = false;
				}, 100);		
			},
			
			/**
			 * Начало редактирования сообщения
			 */
			beginEdit() {
				this.isEdit = true;
				if (this.isChat) {
					Vue.nextTick(() => {
						this.$emit('scrollToMessage', this.message.MID, true);
					});
				}
			},
			
			/**
			 * Конец редактирования сообщения
			 */
			endEdit(message) {
				this.isEdit = false;
				this.isHoverMessage = false;

				if (this.isChat) {
					chatModule.messageFormState('show');
				}
			},
			
			/**
			 * Определяем нужно ли закрывать функционал удаления сообщения или через какое время его убрать
			 */
			setRemovable() {
				if(this.message.time_since_create < 120) {
					var removableDelay = 120 - this.message.time_since_create;
					setTimeout(() => {
						this.removable = false;
						// Посылаем событие о невозможности удалить сообщение
						this.$emit("unremovable");
						this.endEdit();
					}, removableDelay * 1000);
				} else {
					this.removable = false;
				}
			},

			/**
			 * Показывает попап предупреждения о срочном ответе
			 */
			showChatWarningPopup: function () {
				if (!this.isChat) {
					return false;
				}
				if (jQuery(window).width() > 767) {
					return false;
				}
				jQuery('.chat-warning-popup').modal('show');
			},
		}
	}
</script>