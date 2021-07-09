{strip}
	{if $kworks}
		{insert name=is_online_from_array assign=is_online value=a posts=$kworks}
		<div class="kwork-carousel kwork-card-data-wrap" data-kwork-load-category="{$kworkLoadCategory}" data-carousel-name="{$carouselName}">
			<div class="js-kwork-carousel-container-{$carouselName} {if $kworks|@count < 5}kwork-carousel__container_flex{/if} kwork-carousel__container">
				{foreach from=$kworks key=k item=kwork}
					{if $k == 3 && $pageName == "cat" && $pageSpeedMobile}{break}{/if}

					{include file='_blocks/kwork/kwork_card.tpl' is_online=$is_online kwork=$kwork user_kwork_marks=$user_kwork_marks}
				{/foreach}
			</div>
		</div>
		<script>
			{* Для проверки готовности DOM использовать window.addEventListener вместо jQuery ready *}
			{literal}
			window.addEventListener('DOMContentLoaded', function() {
				if (!window.carousel) {
					window.carousel = {};
				}

				jQuery('.kwork-carousel[data-carousel-name="{/literal}{$carouselName}{literal}"] .cusongsblock').each(function() {
					var kworkId = jQuery(this).data('id');
					if (!window.carousel['{/literal}{$carouselName}{literal}']) {
						window.carousel['{/literal}{$carouselName}{literal}'] = [];
					}
					if (kworkId) {
						window.carousel['{/literal}{$carouselName}{literal}'].push(kworkId);
					}
				});

				var responsiveDots = {
					768: {items: 2},
					860: {items: 3},
					1110: {items: 4},
					1350: {items: 5}
				};


				if (jQuery('.all_page').hasClass('is_index')) {
					responsiveDots[320] = {items: 1};
					responsiveDots[550] = {items: 2};
				}
				jQuery('.js-kwork-carousel-container-{/literal}{$carouselName}{literal}').kworkCarousel({
					margin: 0,
					staticFirst: true,
					items: 9999999,
					responsive: responsiveDots
				});
			});
			{/literal}
		</script>
	{/if}
{/strip}