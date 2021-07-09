<template>
	<div>
		<template v-for="(dayTracks,day) in getGroupedByDayTracks(tracks)">
			<div class="clear track--item__date-hr sticky-top" v-if="hasVisibleTracksInDay(day,dayTracks)">
				<span>{{day}}</span></div>
			<div class="track--item__user"
				 :data-user_id="trackGroupKey.split('-').shift()"
				 v-for="(trackGroup,trackGroupKey) in dayTracks" :key="day + trackGroupKey"
				 v-if="trackGroup.filter(i=>!i.isHiddenConversation).length">
				<template v-for="(v) in trackGroup">
							<span class="track--item__unread"
								  v-if="showUnreadLine(v)"><span>Новые сообщения</span></span>
					<track-item :key="v.localId" :track="v" :timerSeconds="timerSeconds"
								:class="{show_hidden:showHiddenConversation}"
								:isVisibleFormMessage="isVisibleFormMessage"
								:isFocusGroupMember="isFocusGroupMember"
								:readedChangeDisableTime="readedChangeDisableTime" v-if="!v.isHiddenConversation"/>
				</template>
			</div>
		</template>
	</div>
</template>
<script>
	import TrackItem from './track-item.vue';  // Сообщение в треке
	export default {
		components: {
			'track-item': TrackItem,
		},
		props: {
			tracks: {
				type: Array,
				default: [],

			},
			timerSeconds: {
				type: Number,
				default: -1,
			},
			readedChangeDisableTime: {
				type: Number,
				default: 0,
			},
			showHiddenConversation: {
				type: Boolean,
				default: false,
			},
			isVisibleFormMessage: {
				type: Boolean,
				default: false,
			}
		},
		data() {
			return {
				isFocusGroupMember: window.config.track.isFocusGroupMember || false,

			}
		},
		methods: {
			groupTracksByDayAndMessages: function (tracks, lastUserId, lastTrackType, i) {
				let groupByDay = _.groupBy(tracks, 'created_day');
				for (let key in groupByDay) {
					let day = groupByDay[key].map(item => {
						if (item.type !== 'text' || lastUserId !== item.author.USERID || (lastTrackType !== null && lastTrackType !== 'text')) {
							i++;
						}
						item.groupKey = item.author.USERID + "-" + i;
						lastUserId = item.author.USERID;
						lastTrackType = item.type;
						return item;
					});
					groupByDay[key] = _.groupBy(day, 'groupKey')
				}
				return groupByDay;
			},

			hasVisibleTracksInDay(day, tracks) {
				return day.length && _.flatten(Object.values(tracks)).filter(track => !this.isTrackFromDialog(track)).length;
			},
			showUnreadLine(track) {
				return window.actorId !== track.author.USERID && this.newTracksBeginsAt == track.id;
			},

			isTrackFromDialog(track) {
				return track.type == 'from_dialog' || track.getHiddenConversation.length
			},
			getGroupedByDayTracks: function (tracks) {
				let i = 0;
				let lastUserId = null;
				let lastTrackType = null;
				return this.groupTracksByDayAndMessages(tracks, lastUserId, lastTrackType, i);
			},
		}
	}

</script>