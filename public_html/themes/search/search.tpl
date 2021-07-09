{include file="header.tpl"}
{strip}
	{include file="fox_error7.tpl"}
	{Helper::registerFooterJsFile("/js/search_filter.js"|cdnBaseUrl)}
	<div>
		<div class="tagcloud tagcloud_search m-text-center m-mb10" style="min-height:0;">
			<div class="centerwrap lg-centerwrap">
				{if $total}

					<h2 class="m-fs22" style="padding-bottom:10px;margin-left:-2px;min-width: 100%;">
						{'Результаты поиска по запросу «%s»'|t:($tag|stripslashes|htmlspecialchars)}
					</h2>

					{if $addOrText = $searchValue != $originalValue}
						<a id="search-exact" href="">
							{'Искать вместо этого «%s»'|t:($originalValue|stripslashes|htmlspecialchars)}
						</a>
					{/if}

					{include file="search/inc/user_search.tpl" addOrText=$addOrText}
				{else}
					<h2 style="padding-bottom:10px;text-align:center;" class="m-fs20 wMax">
						{'К сожалению, поиск не дал результатов'|t}
					</h2>
				{/if}
				<div style="padding-bottom:25px;" class="m-hidden"></div>
			</div>
		</div>
		{if $total}
			<div class="centerwrap lg-centerwrap clearfix m-hidden">
				<div class="pull-right mt4">
					<a rel="nofollow" class="mr9" href="{$displayAsTable}">
						<i class="icon ico-type-grid{if $sdisplay == "list"} ico-type-grid_no-active{/if}"></i>
					</a>
					<a rel="nofollow" href="{$displayAsList}">
						<i class="icon ico-type-list{if !$sdisplay || $sdisplay == "table"} ico-type-list_no-active{/if}"></i>
					</a>
				</div>
				<div class="kwork-list-display-mode sort-by_container pull-right mt4{if Translations::getLang() == 'en'} sort-by_container__en{/if}">
					<div class="sort-by_block sort-type-block search-filter-sort">
						<label>{'Сортировать по'|t}</label>
						<div class="select-style-original none-style">
							<span class="sort-by_title">{if $s == "popular"}{'Рейтингу'|t}{elseif $s == "new"}{'Новизне'|t}{else}{'Рекомендуемые'|t}{/if}</span>
							<select class="js-sort-by">
								<option class="sort-option" data-type="popular" value="{$popularSearchUrl}" {if $s == "popular"}selected{/if}>{'Рейтингу'|t}</option>
								<option class="sort-option" data-type="new" value="{$newSearchUrl}" {if $s == "new"}selected{/if}>{'Новизне'|t}</option>
								{if $isSphinx}
									<option class="sort-option" data-type="x" value="{$relevantSearchUrl}" {if !$s || $s == "x"}selected{/if}>{'Рекомендуемые'|t}</option>
								{else}
									<option>{'Выбрать'|t}</option>
								{/if}
							</select>
						</div>
					</div>
				</div>
				{if $price_mode == "3"}
					{insert name=get_packs value=a assign=packs}
					<select onChange="fox_jumpMenu('parent',this,0)" style="font-size:16px; margin-top:1px; margin-left:2px;border: 1px;padding: 3px;">
						<option value="{getAbsoluteURL("/")}">
							{'Любая цена'|t}
						</option>
						{section name=p loop=$packs}
							{* TODO: Нужно привести в нормальный вид *}
							<option value="{$baseurl}/search?s=o&p={$packs[p].pprice|stripslashes}&query={$tag}&c={$c}{if $sdisplay == "list"}&sdisplay=list{/if}&sdeliverytime={$sdeliverytime}&stoprated={$stoprated}"
									{if $p == $packs[p].pprice|stripslashes}selected="selected"{/if}>
								{include file="utils/currency.tpl" lang=$actor->lang total=$packs[p].pprice}
							</option>
						{/section}
					</select>
				{/if}
			</div>
		{else}
			<div class="top-filters-placeholder"></div>
		{/if}
		<div class="clearfix centerwrap lg-centerwrap mb20 kwork-card-data-wrap page-filters" data-kwork-load-category="4" data-kworks-per-page="{\App::config("kwork.count_on_search_page")}">
			{if !empty($posts)}
				{include file="search/filter.tpl"}
				<div class="right-col">

					<div class="cusongslist cusongslist_3_column c3c pt0">
						{if $sdisplay eq "list"}
							{include file="fox_bit_list.tpl" posts=$posts}
						{else}
							{include file="fox_bit.tpl" posts=$posts wantBanner=true}
						{/if}
						<div class="clear"></div>
					</div>
					<div style="text-align:center;">
						<div class="loader"></div>
						<button onclick="loadKworks(true);" class="loadKworks mb0">
							{'Показать еще'|t}
						</button>
					</div>
					{if $actor->type != "worker"}
						<div class="request_block-js hidden mt30 mb30">
							<h2 style="padding-bottom:10px;text-align:center;">{'Нужно что-то другое?'|t}</h2>
							<div style="padding-bottom:10px;"></div>
							<div class="ta-center">
								<img class="db pb10 m-w100" style="margin:0 auto;" src="{"/nosearchresultspayer-small.jpg"|cdnImageUrl}" alt="">
								<div class="mt15">
									<div class="fontf-pnb fs18 w600 m-fs14" style="margin: 0 auto;">
										{'Не нашли то, что нужно? Разместите проект и получите десятки предложений от продавцов'|t}
									</div>
									<a href="{absolute_original_url route="new_project"}" style="border-radius:4px;" class="GreenBtnStyle left-panel-button fs20 mt20 js-button_create_task">
										{'Разместить проект'|t}
									</a>
								</div>
							</div>
						</div>
					{/if}
					<div class="clear"></div>
				</div>
			{elseif $actor->type == 'worker'}
				<div class="ta-center mAuto">
					<img class="db pb10 m-w100" style="margin:0 auto;" src="{"/nosearchresultsworker.png"|cdnImageUrl}"
						 alt="">
					<div>
						<div class="fontf-pnb fs18 mt10 m-fs14">
							{'Вероятно, никто ещё не создал такого кворка. Станьте первым!'|t}
						</div>
						{* TODO: сделать на роутинг *}
						<a href="/new?name={$tag}" style="border-radius:4px;"
							 class="GreenBtnStyle left-panel-button fs20 mt20">
							{'Создать кворк'|t}
						</a>
						{include file="search/inc/user_search.tpl" addOrBlock=true}
					</div>
				</div>
			{else}
				<div class="ta-center mAuto">
					<img class="db pb10 m-w100" style="margin:0 auto;" src="{"/nosearchresultspayer.png"|cdnImageUrl}"
						 alt="">
					<div class="mt15">
						<div class="fontf-pnb fs18 w600 m-fs14" style="margin: 0 auto;">
							{'Если вы не нашли то, что вам нужно, разместите проект и получите десятки предложений от продавцов'|t}
						</div>
						<a href="{absolute_original_url route="new_project"}" style="border-radius:4px;"
							 class="GreenBtnStyle left-panel-button fs20 mt20 js-sms-verification-action">
							{'Создать проект'|t}
						</a>
						{include file="search/inc/user_search.tpl" addOrBlock=true}
					</div>
				</div>
			{/if}
		</div>
	</div>

{literal}
	<script>
		window.loadMoreFunction = loadKworks;
	</script>
{/literal}

{literal}
	<script>
		$(function() {
			var href = window.location.href;
			// Поменять в URL параметр query на исправленную спеллером яндекса строку
			// (иначе не будет работать кнопка Еще, которая берет параметры из строки
			// запроса)
			var searchValue = decodeURIComponent("{/literal}{rawurlencode($searchValue)}{literal}");
			var originalValue = decodeURIComponent("{/literal}{rawurlencode($originalValue)}{literal}");
			if (searchValue != originalValue) {
				var newUrl = href.replace(/query=.*?[&|$]/, "query=" + searchValue + "&");
				window.history.pushState({urlPath: newUrl}, "", newUrl);
				// Сформировать URL для ссылки "Искать вместо этого..."
				var originalValueEncoded = "{/literal}{rawurlencode($originalValue)}{literal}";
				var searchExactUrl = href.replace(/query=.*?[&|$]/, "query=" + originalValueEncoded + "&") + "&exact=1";
				$("#search-exact").attr("href", searchExactUrl);
			}
		});
	</script>
{/literal}

{literal}
	<script>
		var nextpage = {/literal}{$currentpage}{literal};
		var items_per_page = {/literal}{$items_per_page}{literal};
		var total = {/literal}{$total}{literal};
		var sdisplay = "{/literal}{$sdisplay}{literal}";

		$(document).ready(function () {
			if (nextpage * items_per_page >= total) {
				$(".loadKworks").remove();
				if ($(".request_block-js")) {
					$(".request_block-js").removeClass("hidden");
				}
			}
		});

		function fox_jumpMenu(targ, selObj, restore) {
			eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
			if (restore) {
				selObj.selectedIndex = 0;
			}
		}
	</script>
{/literal}
{/strip}
{include file="footer.tpl"}