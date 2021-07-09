{strip}
	{if $actor}
		{Helper::printCssFile("/css/dist/categories.css"|cdnBaseUrl)}
	{/if}

	{if $pageSpeedDesktop}
		{Helper::printJsFile("/js/libs/withinviewport.js"|cdnBaseUrl, 1)}
		{Helper::printJsFile("/js/libs/jquery.withinviewport.min.js"|cdnBaseUrl, 1)}
	{/if}
	{Helper::registerFooterJsFile("/js/dist/categories.js"|cdnBaseUrl)}

	{if $parent eq 0 && $scatsCnt > 0 && $CATID != "all"}
		<div class="foxmobilecats m-visible t-align-c categoty-select-mobile">
			{control name=mobile_sub_categories}
		</div>
	{/if}

	<div class="clear m-visible"></div>

	<div class="clear"></div>
	<div class="lg-centerwrap clearfix centerwrap mb20 page-filters">
		<div class="right-col">
			<div class="cusongslist cusongslist_3_column c3c cusongslist_p_none kwork-card-data-wrap clearfix kwork-list-{$sdisplay}" data-kwork-load-category="2" data-kworks-per-page="{\App::config("kwork.per_page")}">
				{if $sdisplay eq "list"}
					{include file="fox_bit_list.tpl"}
				{else}
					{include file="fox_bit.tpl" wantBanner=false}
				{/if}
			</div>
			<div class="clear"></div>
			<div class="preloader_kwork">
				<div class="preloader__ico prealoder__ico_kwork mAuto-i" ></div>
			</div>
			<div class="t-align-c">
				<button onclick='loadKworks(true);' class='loadKworks mb0'>{'Показать еще'|t}</button>
			</div>
			<div class="clear"></div>
			<div class="kworks-filter ta-center no-results {if $total}hidden{/if}">
				{'По выбранным фильтрам, к сожалению, ничего не найдено.'|t}<br>
				{'В магазине Kwork %s активных кворков. Попробуйте найти нужную услугу, изменив немного фильтры поиска.'|t:$stat_act_kworks_count}
			</div>
		</div>
	</div>
	{Helper::registerFooterJsFile("/js/riveted.min.js"|cdnBaseUrl)}

{literal}
	<script>
		{/literal}
		{to_js name="nextpage" var=$currentpage}
		{to_js name="items_per_page" var=$items_per_page}
		{to_js name="total" var=$total}
		{to_js name="sdisplay" var=$sdisplay}		
		{literal}

		window.ad_category = "{/literal}{$CATID}{literal}";   // required

		window.addEventListener('DOMContentLoaded', function() {
			if (nextpage * items_per_page >= total) {
				$('.loadKworks').addClass('hidden');
			}

			$('.js-sort-by').change(function () {
				location.href = $(this).val();
				var title = $(this).find('option:selected').text();
				$(this).closest('.sort-by_title').text(title);
			});

			var isSafari =
				!!navigator.userAgent.match(/safari/i) &&
				!navigator.userAgent.match(/chrome/i) &&
				typeof document.body.style.webkitFilter !== "undefined";

			if (isSafari) {
				$('.select-style-original').addClass('safari');
				$('.sort-by_container').addClass('safari');
			}

			riveted.init({
				reportInterval: 5,   // Default: 5
				idleTimeout: 5,      // Default: 30
				eventHandler: function (data) {
					if (typeof yaCounter32983614 === 'object') {
						yaCounter32983614.params([{'time_spent': data}]);
					}
				}
			});
		});
	</script>
{/literal}
{/strip}