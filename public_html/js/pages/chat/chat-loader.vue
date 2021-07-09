<template>
	<div class="ispinner ispinner--gray ispinner--animating dib" :class="{ 'ispinner--large': !isLoaderSmall }">
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
		<div class="ispinner__blade"></div>
	</div>
</template>
<script>
	export default {
		props: {
			isLoaderSmall: {
				type: Boolean,
				required: false,
				default: false
			},
		},
	}
</script>