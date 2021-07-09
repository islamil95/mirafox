<template>
	<div class="track-list">
		<template v-if="isFocusGroupMember">
			<track-list-compact :tracks="getConversationFromDialog()"
								:isVisibleFormMessage="isVisibleFormMessage"
								:showHiddenConversation="showHiddenConversation"
								:readedChangeDisableTime="readedChangeDisableTime"
								:timerSeconds="timerSeconds"></track-list-compact>

			<div class="t-align-c pt10 pb10" v-if="isFocusGroupMember" :class="{hide:!tracksHidden}">
				Показаны {{visibleTracks.length}} последних сообщений.
				&nbsp;<a href="javascript:void(0)" class="d-inline-block" @click="showHidden">Показать все</a>
			</div>
			<track-list-compact :tracks="visibleTracks"
								:isVisibleFormMessage="isVisibleFormMessage"
								:readedChangeDisableTime="readedChangeDisableTime"
								:timerSeconds="timerSeconds"></track-list-compact>
		</template>
		<template v-else>
			<track-item v-for="(v) in tracks" :key="v.localId" :track="v" :timerSeconds="timerSeconds"
						:readedChangeDisableTime="readedChangeDisableTime"
						:showHiddenConversation="showHiddenConversation"
						:isFocusGroupMember="isFocusGroupMember"
						:isVisibleFormMessage="isVisibleFormMessage"/>
		</template>
	</div>
</template>
<script>
	import changeDisableMixin from 'appJs/change-disable-mixin.js';  // Отключение возможности изменения
	import timerMixin from 'appJs/timer-mixin.js';  // Таймер
	import sendingMixin from 'appJs/sending-mixin.js';  // Счётчик отправки запросов
	import TrackItem from './track-item.vue';  // Сообщение в треке
	import TrackInitActions from './track-init-actions.js';  // Действия при инициализации сообщений трека
	import TrackListCompact from './track-list-compact.vue';  // Сообщение в треке

	export default {
		components: {
			'track-item': TrackItem,
			'track-list-compact': TrackListCompact,
		},
		mixins: [timerMixin, sendingMixin, changeDisableMixin],

		data() {
			return {
				tracks: [],
				freeLocalId: 1,
				tracksHidden: true,
				newTracksBeginsAt: null,
				hasHidableConversation: window.hasHidableConversation || false,
				isFocusGroupMember: window.config.track.isFocusGroupMember || false,
				actorId: parseInt(window.actorId),
				showHiddenConversation: false,

				/** отображается ли форма ввода сообщения */
				isVisibleFormMessage: false,
				updatePlanned: false,
			};
		},

		watch: {
			tracks: {
				handler() {
					if (this.updatePlanned) {
						return;
					}
					let unreads = this.tracks.filter(track => this.actorId !== track.author.USERID && track.unread);
					if (unreads.length) {
						this.newTracksBeginsAt = unreads.shift().id;
					} else {
						this.newTracksBeginsAt = null;
					}
					this.updatePlanned = true;
					this.$nextTick(() => {
						this.updatePlanned = false;
						newContentProcess($('#app-tracks'));
						_.forEach(this.callbacks, (v, k) => {
							v();
						});
						readTrackService.updateTitleUnreadCount();
						readTrackService.updateMessagesCircleCount();
						this.callbacks = [];
					});
				},
				deep: true,
			},
			showHiddenConversation(value) {
				this.tracks = this.tracks.map(track => {
					if (track.getHiddenConversation.length) {
						track.isHiddenConversation = !value;
					}
					return track;
				})
			},
			sendingCount() {
				if (this.sendingCount < 1) {
					this.applyQueuedData();
				}
			},
		},
		created() {
			this.dataQueue = [];
			this.callbacks = [];
			this.applyContent(window.trackList || []);

			window.bus.$on('bgSendMessages', (result) => {
				this.applyContent(result);
			});
			this.tracksHidden = this.tracks.filter(track => {
				return !this.isTrackFromDialog(track);
			}).length > 10 ? true : false;
		},
		mounted() {
			this.$nextTick(() => {
				// Проверка наличия кнопки "Показать все сообщения"
				const $moreButton = $('#show-more-track');
				if ($moreButton.length) {
					this.transferMoreButton($moreButton);
				}
			});

			if ($('.message-submit-button').is(':visible')) {
				this.updateVisibleFormMessage(true);
			}
		},

		computed: {
			visibleTracks: function () {
				if (this.tracksHidden) {
					return _.takeRight(this.tracks.filter(track => {
						return !this.isTrackFromDialog(track);
					}), 10);
				}
				return this.tracks.filter(track => {
					return !this.isTrackFromDialog(track);
				});
			},
			lastTextMessageUserId() {
				let last = _.last(this.tracks);
				return  last && last.type == 'text' ? last.author.USERID : null;
			}
		},
		methods: {

			isBusy() {
				return (this.sendingCount > 0);
			},
			getConversationFromDialog() {
				return this.tracks.filter(track => {
					return this.isTrackFromDialog(track);
				})
			},
			isTrackFromDialog(track) {
				return track.type == 'from_dialog' || track.getHiddenConversation.length
			},
			showHidden() {
				this.tracksHidden = false;
				this.$nextTick(() => {
					$('.step-block-order_item').removeClass('hide');
					newContentProcess($('#app-tracks'));
					initPortfolioList();
				});
			},
			toggleHiddenConversation() {
				this.showHiddenConversation = !this.showHiddenConversation;
			},
			removeTrack(trackId) {
				trackId = parseInt(trackId);
				this.tracks = this.tracks.filter(track => track.id !== trackId);
			},
			isBusy() {
				return (this.sendingCount > 0);
			},
			applyQueuedData() {
				this.applyContent(this.dataQueue);
				this.dataQueue = [];
			},

			removeQueuedItem(id) {
				let index = _.findIndex(this.dataQueue, (v) => {
					return v.id == id;
				});
				if (index >= 0) {
					this.dataQueue.splice(index, 1);
				}
			},

			applyContent(data, callback, force = false) {
				if (callback) {
					this.callbacks.push(callback);
				}
				if (Array.isArray(data)) {
					_.forEach(data, (v, k) => {
						this.applyItem(v, force);
					});
					return;
				}
				return this.applyItem(data, force);
			},

			applyItem(item, force = false) {
				if (!item) {
					return;
				}
				item.id = parseInt(item.id || 0);
				if (item.author) {
					item.author.USERID = parseInt(item.author.USERID || 0);
				}
				if ('unread' in item) {
					item.unread = Utils.toBool(item.unread);
				}
				item.time = parseInt(item.time || 0);

				let track = _.find(this.tracks, (v) => {
					return ((v.key && v.key == item.key) || (v.id && v.id == item.id));
				});
				if (track) {
					if (item.id) {
						this.removeQueuedItem(item.id);
					}
					if (!force && track.isEdited) {
						this.dataQueue.push(item);
						return;
					}
					track.filesArray = [];
					let unread = track.unread;
					_.assignInWith(track, item, (v1, v2, k) => {
						if (k == 'unread') {
							return v1;
						}
						return v2;
					});
					return;
				}
				if (this.sendingCount > 0 && item.author && item.author.USERID == this.actorId) {
					this.dataQueue.push(item);
					return;
				}
				if ((this.actorId !== item.author.USERID) && item.type == 'worker_portfolio') {
					return;
				}
				let localId = this.freeLocalId;
				this.freeLocalId++;
				this.tracks.push(_.assignIn({
					localId: localId,
					id: 0,
					author: {
						USERID: this.actorId,
						username: '',
						profilepicture: '',
					},
					html: '',
					created_day: this.lastTextMessageUserId == this.actorId ? _.last(this.tracks).created_day : '',
					key: null,
					unread: true,
					time: 0,
					message: '',
					type: 'text',
					getHiddenConversation: '',
					filesArray: [],
					quote: {
						id: 0,
						username: '',
						message: '',
					},
					isEdited: false,
				}, item));
				try {
					if (item.initAction) {
						this.$nextTick(() => {
							TrackInitActions.doInitAction(item);
						});
					}
				} catch (e) {
					console.warn(e);
				}

				return localId;
			},

			setEdited(id, status) {
				let track = this.getTrackById(id);
				if (!track) {
					return;
				}
				track.isEdited = status;
			},
			replaceTrackHTML(id, HTML) {
				id = parseInt(id);
				let index = this.tracks.findIndex(track => track.id == id);
				let item = this.tracks.find(item => item.id == id);
				item.html = HTML;
				this.tracks[index] = item;
			},
			removeMessageByLocalId(localId) {
				let index = _.findIndex(this.tracks, (v) => {
					return (v.localId && v.localId == localId);
				});
				if (index >= 0) {
					this.tracks.splice(index, 1);
				}
			},

			getLastTrackId() {
				let lastTrack = _.findLast(this.tracks, (v) => {
					return (v.id > 0);
				});
				if (lastTrack) {
					return lastTrack.id;
				}
				return 0;
			},
			getTrackById(id) {
				let track = null;
				track = _.find(this.tracks, (v) => {
					return v.id == id;
				});
				return track;
			},

			setItemRead(id) {
				let track = this.getTrackById(id);
				if (!track) {
					return;
				}
				track.unread = false;
			},

			getQuoteData(id) {
				let track = this.getTrackById(id);
				if (!track) {
					return {};
				}
				return {
					id: id,
					username: track.author.username,
					message: track.message,
				};
			},
			transferMoreButton($moreButton) {
				// Функция для правильного позиционирования
				// Кнопки "Показать сообщения" когда есть кнопка "Показать сообщения из диалога"

				const $dialogToggle = $('#dialog-toggle');

				if ($dialogToggle.length) {
					const $lastDialogTrack = $('.last-from-dialog');

					if ($lastDialogTrack.length) {
						let $clone = $moreButton.clone();
						$clone.removeClass('hide');
						$lastDialogTrack.parent().parent().after($clone);
						$moreButton.remove();
					} else {
						const $firstDialogTrack = $('.is-first-dialog-message');

						if ($firstDialogTrack.length) {
							let $clone = $moreButton.clone();
							$clone.removeClass('hide');
							$firstDialogTrack.parent().parent().after($clone);
							$moreButton.remove();
						} else {
							$moreButton.removeClass('hide');
						}
					}
				} else {
					$moreButton.removeClass('hide');
				}
			},
			updateVisibleFormMessage(value) {
				this.isVisibleFormMessage = value;
			}
		}
	}

</script>