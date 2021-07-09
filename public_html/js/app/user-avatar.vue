<template>
	<div class="user-avatar t-user-avatar">
		<template v-if="url === 'noprofilepicture.gif' && (Number.isInteger(parseInt(firstLetter)) || firstLetter === '_' || firstLetter === '-' || !firstLetter)">
			<img class="user-avatar__picture rounded" :src="avatarUrlDefault" :style="{background: backgroundColor}" alt="">
		</template>
		<template v-else-if="url === 'noprofilepicture.gif'">
			<div class="user-avatar__default" :style="{background: backgroundColor}">{{ firstLetter }}</div>
		</template>
		<template v-else>
			<img class="user-avatar__picture rounded" :src="avatarUrl" alt="">
		</template>
	</div>
</template>

<script>

export default {
	data () {
		return {
			userAvatarColors: window.userAvatarColors || {},
		};
	},

	props: {
		username: {
			type: String,
			default: '',
		},
		url: {
			type: String,
			default: '',
		},
		size: {
			type: String,
			default: 'medium',
		},
	},

	computed: {
		firstLetter() {
			if (this.username.length < 1) {
				return '';
			}
			return this.username[0];
		},

		avatarUrlDefault() {
            return '/images/avatar/' + this.size + '/noprofilepicture.png';
		},

		avatarUrl() {
			return window.config.users.profilePicUrl + '/' + this.size + '/' + this.url;
		},

		backgroundColor() {
			//console.log(this.userAvatarColors);
			let color = '#C2C2C2';
			if (this.username in this.userAvatarColors) {
				color = this.userAvatarColors[this.username];
			}
			return color;
		},
	}
};
</script>