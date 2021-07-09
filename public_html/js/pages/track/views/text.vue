<template>
	<div class="tr-track own">
		<div class="step-block-order_item text_message unread editable removable  out" data-track-id="resending">
			<div class="block-circle block-circle_gray pull-left">
				<user-avatar :url="track.author.profilepicture || ''" :username="track.author.username"></user-avatar>
			</div>
			<div class="block-circle block-circle_gray pull-left">
				<img class="rounded" src="" alt="">
			</div>
			<div class="font-OpenSans ml80">
				<message-time class="f14 color-gray mt3 floatright date-time" :time="parseInt(track.time)" :updatedAt="parseInt(track.updatedAt)" :fullDate="true" />
				<div class="f16 font-OpenSans online-status">
					<i class="js-online-icon icon ico-online" data-user-id="{$author->USERID}"></i><span class="username f16">{{ track.author.username }}</span>
				</div>
			</div>
			<div class="clear"></div>
			<div v-if="track.quote.id" class="js-message-quote message-quote message-quote--write" :data-quote-id="track.quote.id">
				<div class="message-quote__tooltip tooltipster m-hidden" data-tooltip-side="right" data-tooltip-text="Нажмите, чтобы перейти к цитате"></div>
				<div class="message-quote__login">{{ track.quote.username }}</div>
				<div class="js-message-quote-text message-quote__text"><div v-html="track.quote.message"></div></div>
				<div class="js-message-quote-remove message-quote__remove"></div>
			</div>
			<div class="step-block-order__text font-OpenSans ml80 m-ml0 m-mt10">
				<div class="f15 breakwords message pre-wrap" v-html="messageText"></div>
				<!-- Прикрепленные файлы -->
				<file-list v-if="track.filesArray" :files="track.filesArray" />
				<!-- Статус сообщения -->
				<message-status-bar :showOnIncoming="false" :unread="track.unread" :sended="!!track.id" :own="isOwn" :timerSeconds="timerSeconds" />
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
		props:{
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

			messageText:{
				type: String,
				default:''
			}
		}
	}
</script>