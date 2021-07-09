{strip}
<div class="centerwrap pb50 pt50">
    <div class="color-gray font-OpenSans f16">
        {if $total GT 0}
            {declension count=$total form1="Найден" form2="Найдено" form5="Найдено"} {declension count=$total form1="результат" form2="результата" form5="результатов" merge=true}
        {else}
            {'Результаты не найдены'|t}
        {/if}
    </div>
	{if $tag}
		<h1 class="mt25">{'Кворки по тегу «%s»'|t:($tag|stripslashes)}</h1>
		{include file="fox_error.tpl"}
		<div class="cusongslist cusongslist_4_column clearfix mt25">
			{include file="fox_bit.tpl"}
			<div class="clear"></div>
            <div class="t-align-c">
                <button onclick='loadKworks(true);' class='loadKworks'>{'Показать еще'|t}</button>
            </div>
		</div>
	{/if}
</div>
{literal}
<script>
    var nextpage = {/literal}{$currentpage}{literal};
    var items_per_page = {/literal}{$items_per_page}{literal};
    var total = {/literal}{$total}{literal};
    var sdisplay = "{/literal}{$sdisplay}{literal}";
    if(nextpage*items_per_page >= total)
    {
        $('.loadKworks').remove();
    }
</script>
{/literal}
{/strip}