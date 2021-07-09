<template>
	<div v-if="count > 0" class="new-messages-circle-container">
		<div @click="onClick" class="new-messages-circle">
			<i class="fl-down-open-big"></i>
			<div>{{ count }}</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			count: 0,
			circlePosition: null,
		};
	},

	mounted() {
		window.bus.$on('updateMessagesCount', (e) => {
			this.count = e;
		});
	},

	methods: {
		onClick() {
			window.bus.$emit('scrollToUnread');
		},
	},
}
</script>
