{strip}


    {if $parent eq 0 && $scatsCnt > 0}
        <div class="foxmobilecats m-visible t-align-c categoty-select-mobile">
            {control name=mobile_sub_categories}
        </div>
    {/if}
    <div class="m-visible mt20"></div>
    <div class="m-hidden mt40"></div>
    <div class="lg-centerwrap centerwrap main-wrap m-margin-reset">
        <div class="cusongs ">
            <div class="cusongslist cusongslist_4_column c4c">
                {include file="fox_bit.tpl"}
                <div style="text-align:center;">
                    <button onclick='loadKworks(true);' class='loadKworks'>{'Показать еще'|t}</button>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
    {if $land->info != ''}
        <div class="dark-gray-wrap">
            <div class="centerwrap">
                <div class="fontf-pnl m-text-center">
                    <i class="icon ico-kworkLogoSmall footer_logo"></i><span class="ml20 fs22 dib after-logo-text fontf-pnr">{'Кворк.ру - магазин фриланс услуг по фиксированной цене'|t}</span>
                    {if Translations::isDefaultLang()}
                        <div class="mt20 land-info-block">{$land->info|t|stripslashes|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'}</div>
                    {/if}
                    <div class="mt20">&nbsp;</div>
                </div>
            </div>
        </div>
    {/if}
{literal}
    <script>
        $('document').ready(function(){
            {/literal}{if $actor}{literal}
            share.updateContent({
                url:base_url + "?ref={/literal}{$actor->id}"{literal}
            });
            {/literal}{/if}{literal}
        });

        $('#invite-friends-b').on('click', function(){
            $(this).remove();
            $('#invite-bs-block').show();
            return false;
        });

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