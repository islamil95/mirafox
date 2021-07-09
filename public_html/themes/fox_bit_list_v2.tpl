{strip}
<style>
	.h3-title span{
		color: #4b4b4b;
	}
</style>

{literal}
<script>
	$(window).load(function(){
		$('.newfox').hover(
			function(){
				$(this).find('.h3-title span').css("color", "#457edb");
			},
			function(){
				$(this).find('.h3-title span').css('color','#4b4b4b');
			}
		);
	});
	function showOther(){
		$('.cusongslist .wrap_other_list').removeClass('hidden');
		$('.cusongslist .wrap_show_hide .show_other').addClass('hidden');
		$('.cusongslist .wrap_show_hide .hide_other').removeClass('hidden');
	}
	function hideOther(){
		$('.cusongslist .wrap_other_list').addClass('hidden');
		$('.cusongslist .wrap_show_hide .show_other').removeClass('hidden');
		$('.cusongslist .wrap_show_hide .hide_other').addClass('hidden');
	}
</script>
{/literal}
<div class="pb10 wrap_show_hide f14 no-other-kworks d-none">
	Нет других кворков в данной категории
</div>
<div class="pb10 wrap_show_hide f14 show-other-kworks-toggle">
	<span class="link_local show_other" onclick="showOther();">{'Показать'|t}</span>
	<span class="link_local hide_other hidden" onclick="hideOther();">{'Скрыть'|t}</span>
</div>
<div class="wrap_other_list hidden">
{section name=i loop=$posts}
	<div class="js-kwork-card newfox {if $smarty.section.i.iteration is even}newfoxnewcolor{/if}" data-id="{$posts[i].PID}" data-parent="{$posts[i].cat_data.parent_id}" data-cat="{$posts[i].cat_data.sub_id}">
		<div class="newfoximg">
			<a href="{$baseurl}{$posts[i].url}" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('SHOW-KWORK'); return true; }"><img src="{$purl}/t4/{$posts[i].photo}"
				{photoSrcset("t4", $posts[i].photo)}
				alt="{$posts[i].gtitle|stripslashes}" width="180" height="120"/></a>
		</div>
		<div class="newfoxdetails clearfix">
			<div class="clearfix">
				<div class="price pull-right color-green">{if $posts[i].is_package}от {/if}{include file="utils/currency.tpl" lang=$actor->lang total=$posts[i].min_volume_price}</div>
				<h3 class="h3-title">
					<a href="{$baseurl}{$posts[i].url}" {if strlen($posts[i].gtitle) > 50}title="{$posts[i].gtitle|stripslashes|upstring}"{/if} onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('SHOW-KWORK'); return true; }"><span class="first-letter dib"> {$posts[i].gtitle|stripslashes|mb_truncate:50:"...":'UTF-8'} </span></a>
				</h3>
				{if $posts[i].active eq "3"}
					<span class="status-block status-block_red first-letter dib"><span class="first-letter dib">{'удален'|t}</span></span>
				{elseif $posts[i].feat eq 1}
					{if $posts[i].active eq "1"}
						<span class="status-block status-block_green first-letter dib">{'активный'|t}</span>
					{elseif $posts[i].active eq "2"}
						<span class="status-block status-block_blue first-letter dib">{'заблокирован'|t}</span>                                       
					{elseif $posts[i].active eq "0"}
						<span class="status-block first-letter dib">{'на модерации'|t}</span>
					{elseif $posts[i].active eq "4"}
						<span class="status-block status-block_red first-letter dib"><span class="first-letter dib">{'отклонен модератором'|t}</span></span>
					{elseif $posts[i].active eq "5"}
						<span class="status-block first-letter dib">{'на паузе'|t}</span>
					{/if}
				{else}
					<span class="status-block status-block_blue first-letter dib">{'остановлен'|t}</span>
				{/if}
				<h5>
					&nbsp<span class="scriptomembittitle">
						{if $posts[i].cat_data['parent_id']}
							<a href="{$baseurl}/{$catalog}/{$posts[i].cat_data['parent_seo']}">{$posts[i].cat_data['parent_name']}</a>&nbsp;>&nbsp;
						{/if}
						<a href="{$baseurl}/{$catalog}/{$posts[i].cat_data['sub_seo']}">{$posts[i].cat_data['sub_name']}</a>
					</span>&nbsp;
				</h5>
			</div>
			<div class="clearfix breakwords" style='font-size: 14px;'>{$posts[i].gdesc|stripslashes|mb_truncate:250:"...":'UTF-8'}</div>
		</div>
		<div class="clear"></div>
	</div>
{/section}
</div>
{/strip}