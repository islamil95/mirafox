<template>
	<div v-if="!track.html" class="track-item">
		<component :is="view" :track="track" :messageText="messageText" :isOwn="isOwn" :timerSeconds="timerSeconds"></component>
	</div>
	<div v-else v-html="track.html" class="track-item" :class="trackClasses"></div>
</template>

<script>
	import textFormatMixin from 'appJs/text-format-mixin.js';
	import messageChangeMixin from 'appJs/message-change-mixin.js';
	import TextView from './views/text.vue'
	import CompactTextView from './views/compact/text.vue'
	import messageTime from 'appJs/message-time.vue';
	import messageStatusBar from 'appJs/message-status-bar.vue';  // Статус сообщения
	import userAvatar from 'appJs/user-avatar.vue';  // Аватар

	export default {
		mixins: [messageChangeMixin, textFormatMixin],
		components: {
			'message-time': messageTime,
			'message-status-bar': messageStatusBar,
			'user-avatar': userAvatar,
			'text-view': TextView,
			'compact-text-view': CompactTextView,
		},

		props: {
			track: {
				type: Object,
				default: {},
			},

			timerSeconds: {
				type: Number,
				default: -1,
			},

			readedChangeDisableTime: {
				type: Number,
				default: 0,
			},
			isVisibleFormMessage: {
				type: Boolean,
				default: false,
			},
			isFocusGroupMember: {
				type: Boolean,
				default: false,
			}
		},
		data() {
			return {
				senderName: '',
				userAvatarColors: {},
				view:null,
				actorId: parseInt(window.actorId),
			};
		},

		computed: {
			isOwn() {
				return (this.actorId == this.track.author.USERID);
			},
			trackClasses() {
				let classes = [];
				if (this.canEdit) {
					classes.push('tr-editable');
				}
				if (this.canRemove) {
					classes.push('tr-removable');
				}
				if (this.canQuote) {
					classes.push('tr-quotable');
				}
				return classes;
			},

			messageText() {
				return this.formatText(this.track.message, {
					bbcode: false,
				});
			},
		},
		created(){
			this.view =	this.loadView();
		},
		methods:{
			loadView(){
				return this.isFocusGroupMember?`compact-${this.track.type}-view`:`${this.track.type}-view`
			}
		}
	}
</script>