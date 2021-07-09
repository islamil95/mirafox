<template>
	<div v-if="!isLoaderShow" class="chat__message-status">
		<div v-if="privateMessageStatus === true">
			<!-- Блок c активными заказами между пользователями -->
			<div v-if="ordersBetweenUsers.length" class="t-align-c sppbox p10-20 mt20">
				<div v-if="ordersBetweenUsers.length === 1">
					<div class="mt10 fw700">
						{{ ordersBetweenUsersText }}
					</div>
					<a class="green-btn btn--big18 hoverMe mb12 mt12"
					   :href="'/track?id=' + ordersBetweenUsers[0].id + ''">
						{{ t('ОК, перейти в заказ') }}
					</a>
				</div>
				<div v-else>
					<div class="mt10 fw700">
						{{ ordersBetweenUsersText }}
					</div>
					<div class="mt12 mb12 t-align-l">
						{{ t('Перейдите в нужный заказ, чтобы просмотреть переписку или написать новое сообщение:') }}
						<div v-for="(item, index) in ordersBetweenUsers" :key="item.id">
							<div class="mt5">
								<a class="link"
								   :href="'/track?id=' + item.id">
									{{ item.kwork_title }}
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Блок информации о времени работы техподдержки -->
			<div v-if="isSupportDialog" class="mt20">
				{{ t('Техподдержка отвечает на вопросы в будни с 8:00 до 21:00 (МСК), а в выходные – с 13:00 до 17:00') }}
			</div>
		</div>
		<div v-else-if="privateMessageStatus === privateMessageStatuses.support" class="mt20">
			{{ t('Предыдущее обращение в службу поддержки закрыто по истечении времени.') }}
		</div>
		<div v-else :class="{'t-align-c sppbox p10-20 mt20': privateMessageStatus}">
			<div v-if="privateMessageStatus === privateMessageStatuses.default || privateMessageStatus === privateMessageStatuses.orderCancel">
				{{ t('Возможности связи с данным пользователем нет. Если связаться с пользователем необходимо, напишите в') }}
				<span class="chat__link chat__link_color_blue noselect nowrap" v-on:click="allowConversation">
				{{t('Службу поддержки') }}.
			</span>
			</div>
			<div v-else-if="privateMessageStatus === privateMessageStatuses.conversationTime">
				{{ t('Возможности связи с данным пользователем нет, поскольку с момента последней переписки с этим пользователем прошло более месяца. Если связаться с пользователем необходимо, напишите в') }}
				<span class="chat__link chat__link_color_blue noselect nowrap" v-on:click="allowConversation">
				{{t('Службу поддержки') }}.
			</span>
			</div>
			<div v-else-if="privateMessageStatus === privateMessageStatuses.orderTime">
				{{ t('Возможности связи с данным пользователем нет, поскольку с момента закрытия последнего заказа с этим пользователем прошло более месяца. Если связаться с пользователем необходимо, напишите в') }}
				<span class="chat__link chat__link_color_blue noselect nowrap" v-on:click="allowConversation">
				{{t('Службу поддержки') }}.
			</span>
			</div>
			<div v-else-if="privateMessageStatus === privateMessageStatuses.orderRating">
				{{ t('Возможности связи с данным пользователем нет, так как по заказу оставлен отрицательный отзыв.') }}
			</div>
		</div>
		<div class="mt15 js-send-error-assign-cancel"
			 :class="{hidden: privateMessageStatus !== privateMessageStatuses.support}">
			<div class="clearfix"></div>
			<div class="t-align-c sppbox">
				<strong class="mt10 f15">
					{{ t('Для обращения за помощью перейдите в раздел') }}
				</strong><br>
				<a class="green-btn btn--big18 hoverMe mb12 mt12" href="/support">
					{{ t('Помощь и служба поддержки') }}
				</a>
			</div>
		</div>
	</div>
</template>
<script>
	import i18nMixin from 'appJs/i18n-mixin'; // Локализация

	export default {
		mixins: [i18nMixin],

		props: {
			chatPrivateMessageStatusProps: {
				type: Object,
				required: true,
			},
		},

		data() {
			return {
				i18n: {
					en: {
						'Службу поддержки': 'Support Team',
						'Возможности связи с данным пользователем нет. Если связаться с пользователем необходимо, напишите в': 'Chat with this user is not available. If you need to communicate with this user, contact our',
						'Возможности связи с данным пользователем нет, поскольку с момента последней переписки с этим пользователем прошло более месяца. Если связаться с пользователем необходимо, напишите в': 'Direct contact with this user is unavailable as the last messages were sent over a month ago. If you wish to contact this user, address our',
						'Возможности связи с данным пользователем нет, поскольку с момента закрытия последнего заказа с этим пользователем прошло более месяца. Если связаться с пользователем необходимо, напишите в': 'Direct contact with this user is unavailable as the last messages were sent over a month ago. If you need to communicate the user, please contact our',
						'Возможности связи с данным пользователем нет, так как по заказу оставлен отрицательный отзыв.': 'Your message cannot be sent, as the buyer has left a negative review on the order.',
						'Предыдущее обращение в службу поддержки закрыто по истечении времени.': 'The request is closed.',
						'Для обращения за помощью перейдите в раздел': 'If you have any questions, please visit Help & Support',
						'Помощь и служба поддержки': 'Help and Support Team',
						'У вас есть активный заказ с этим {{0}}. Переписка будет вестись в заказе, пока он в работе, чтобы избежать путаницы с сообщениями.': 'You have the active order with this {{0}}. Conversation will be conducted in the order while it is in operation to avoid confusion with messages.',
						'У вас есть несколько активных заказов с этим {{0}}. Переписка будет вестись в нужном заказе, чтобы избежать путаницы с сообщениями.': 'You have several active orders with this {{0}}. Conversation will be conducted in the order while it is in operation to avoid confusion with messages.',
						'покупателем': 'buyer',
						'продавцом': 'seller',
						'ОК, перейти в заказ': 'OK, go to the order',
						'Перейдите в нужный заказ, чтобы просмотреть переписку или написать новое сообщение:': 'Go to the desired order to view the conversation or write a new message:',
						'Техподдержка отвечает на вопросы в будни с 8:00 до 21:00 (МСК), а в выходные – с 13:00 до 17:00': 'Thank you for your message! We will get back to you as soon as possible.',
						'Прошу разрешить переписку с пользователем {{0}}': 'I ask to allow correspondence with user {{0}}',
					},
				},

				privateMessageStatus: false,
				privateMessageStatuses: [],
				ordersBetweenUsers: [],
				isSupportDialog: false,
				userName: null,

				supportUserId: null,
				actorId: null,
				isLoaderShow: false,
			}
		},

		computed: {
			/**
			 * Выводит текст для блока c активными заказами между пользователями
			 */
			ordersBetweenUsersText: function () {
				let actorTypeName = this.ordersBetweenUsers[0]['worker_id'] === this.actorId ? t('покупателем') : t('продавцом');
				return this.ordersBetweenUsers.length === 1 ?
					t('У вас есть активный заказ с этим {{0}}. Переписка будет вестись в заказе, пока он в работе, чтобы избежать путаницы с сообщениями.', [actorTypeName]) :
					t('У вас есть несколько активных заказов с этим {{0}}. Переписка будет вестись в нужном заказе, чтобы избежать путаницы с сообщениями.', [actorTypeName]);
			}
		},

		created: function () {
			this.i18nInit();

			this.supportUserId = window.supportUserId || null;
			this.actorId = window.actorId || null;

			_.assignIn(this, this.chatPrivateMessageStatusProps);
		},

		methods: {
			/**
			 * Открывает чат со службой поддержки
			 */
			allowConversation: function () {
				window.bus.$emit('loadConversation', this.supportUserId, t('Прошу разрешить переписку с пользователем {{0}}', [this.userName]));
			},
		},
	}
</script>