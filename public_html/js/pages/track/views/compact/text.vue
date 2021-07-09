<template>
	<div class="tr-track own">
		<div class="track--item text_message unread editable removable  out" data-track-id="resending">
			<div class="track--item__sidebar">
				<div class="track--item__sidebar-image">
					<user-avatar :url="track.author.profilepicture || ''"
								 :username="track.author.username"></user-avatar>
				</div>
			</div>
			<div class="track--item__main">
				<div class="track--item__title">
					<h3 class="f15 bold d-flex align-items-center"><i
							class="js-user-online-block dot-user-status dot-user-online mr7"
							:data-user-id="track.author.USERID"></i><a
							class="t-profile-link" href="#">{{ track.author.username }}</a></h3>
					<div class="track--item__date  color-gray">&nbsp;
						<message-time class="f14 color-gray mt3 floatright date-time" :time="parseInt(track.time)"
									  :updatedAt="parseInt(track.updatedAt)" :fullDate="false"/>
					</div>
				</div>
				<div class="track--item__content">
					<div v-if="track.quote.id" class="js-message-quote message-quote"
						 :data-quote-id="track.quote.id">
						<div class="message-quote__tooltip tooltipster m-hidden" data-tooltip-side="right"
							 data-tooltip-text="Нажмите, чтобы перейти к цитате"></div>
						<div class="message-quote__login">{{ track.quote.username }}</div>
						<div class="js-message-quote-text message-quote__text">
							<div v-html="track.quote.message"></div>
						</div>
						<div class="js-message-quote-remove message-quote__remove"></div>
					</div>
					<div class="step-block-order__text">
						<div class="f15 breakwords message pre-wrap" v-html="messageText"></div>
						<!-- Прикрепленные файлы -->
						<file-list v-if="track.filesArray" :files="track.filesArray"/>
						<!-- Статус сообщения -->
						<message-status-bar :showOnIncoming="false" :unread="track.unread" :sended="!!track.id"
											:own="isOwn" :timerSeconds="timerSeconds"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import textFormatMixin from 'appJs/text-format-mixin.js';
	import messageChangeMixin from 'appJs/message-change-mixin.js';
	import messageTime from 'appJs/message-time.vue';
	import messageStatusBar from 'appJs/message-status-bar.vue';  // Статус сообщения
	import userAvatar from 'appJs/user-avatar.vue';  // Аватар

	export default {
		mixins: [messageChangeMixin, textFormatMixin],
		components: {
			'message-time': messageTime,
			'message-status-bar': messageStatusBar,
			'user-avatar': userAvatar,
		},
		props: {
			track: {
				type: Object,
				default: {},
			},
			isOwn: {
				type: Boolean,
				default: false,
			},

			timerSeconds: {
				type: Number,
				default: -1,
			},

			messageText: {
				type: String,
				default: ''
			}
		}
	}
</script>