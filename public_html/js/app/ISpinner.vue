<template>
	<div v-if="mode == 'lite'" class="ispinner-lite" :class="{'ispinner-light': bg == 'light'}"></div>
	<div v-else class="ispinner ispinner--gray ispinner--animating ispinner--large">
		<div v-for="k in 12" :key="k" class="ispinner__blade"></div>
	</div>
</template>

<script>
export default {
	props: {
		mode: {
			type: String,
			default: '',
		},

		bg: {
			type: String,
			default: '',
		},
	},
}
</script>
