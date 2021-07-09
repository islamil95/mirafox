{strip}
    <!-- значит кворк был удален-->
    <div class="gray-bg-border clearfix mb20">
        <div class="contentArea mb0 pb0">
            <div class="p15-20 sm-text-center">
                <div class="block-circle block-circle-60 {if $p.kworkBlock.block_type == "abuse"}block-circle_orange{else}block-circle_red{/if} dib v-align-m"><i class="ico-info"></i></div>
                <br class="m-visible"><br class="m-visible">
                <p class="f16 font-OpenSans dib v-align-m ml15 mw80p sm-margin-reset">
                    {if $p.kworkBlock.block_type == "abuse"}
                        {'К сожалению, продавец временно приостановил продажу данного кворка.'|t}<br>
                        {if is_array($u) && $u|count gt 0}
                            {'Посмотреть другие кворки'|t} <a href="{$baseurl}/{insert name=get_seo_profile value=a username=$p.username|stripslashes}">{$p.username|stripslashes}</a> {'или перейти в раздел'|t}
                            <a itemprop="item" href="{$baseurl}/{$catalog}/{$p.seo|lower|stripslashes}"><span itemprop="name"> {$p.name|stripslashes}</span></a>.
                        {else}
                            {'Смотрите похожие кворки в разделе'|t}
                            <a itemprop="item" href="{$baseurl}/{$catalog}/{$p.seo|lower|stripslashes}"><span itemprop="name"> {$p.name|stripslashes}</span></a>.
                        {/if}
                    {elseif $p.kworkBlock.block_type == "admin"}
                        {'К сожалению, продажа данного кворка приостановлена.'|t}<br>
						{'Смотрите похожие кворки в разделе'|t}
						<a itemprop="item" href="{$baseurl}/{$catalog}/{$p.seo|lower|stripslashes}"><span itemprop="name"> {$p.name|stripslashes}</span></a>.
                    {elseif UserManager::isKworksDisabled()}
                        {'Кворк заблокирован из-за того, что продавец не заходил на сайт 18 месяцев подряд'|t}
                    {/if}
                </p>
            </div>
        </div>
    </div>
{/strip}
