{strip}
	{assign var="isRejected" value=($p.active == KworkManager::STATUS_REJECTED)}
	<!-- значит кворк активный, админу показываем кнопку модерации -->
	<div class="{if $isRejected}reject-info-wrapper{else}gray-bg-border clearfix mb20{/if}">
		{if $isRejected}
            {control name="kwork\kwork_notice\kwork_notice_rejected" p=$kwork hideWrapper=true}
		{else}
        <div class="contentArea mb0 pb0" {if $isPostModeration}style="width: 420px;"{/if}>
                <div class="p15-20">
                        <div class="block-circle block-circle-60 block-circle_{if $p.active == 1}green{elseif $p.active == 4}red{elseif $p.active == 0}orange{/if} dib v-align-m"><i class="ico-info"></i></div>
                        <p class="f16 font-OpenSans dib v-align-m ml15 mw80p">
                            {if $p.active == 0}
                                {'Кворк на модерации'|t}
                            {elseif $p.active == 1 && $p.need_moderate == 0}
                                {'Активный (после автомодерации)'|t}
                            {elseif $p.active == 1 && $isPostModeration}
                                {'Кворк на постмодерации'|t}
                                <br>
                                ({'активный'|t})
                            {elseif $p.active == 1}
                                {'Кворк активный'|t}
                            {/if}
                        </p>
                </div>
        </div>
        {/if}
        {if $isPostModeration}
            <div class="floatleft m-text-center pt20 postmoder-decide-block">
                <div class="pull-reset clearfix">
                    <form action="/moder_kwork/decide" method="post">
                        <input type="hidden" name="onPostModeration" value="1">
                        <input type="hidden" name="entity" value="kwork">
                        <input type="hidden" name="id" value="{$p.PID}">
                        <button class ="hugeGreenBtn GreenBtnStyle h50 done-button" type="submit">{'Готово'|t}</button>
                    </form>
                    <a href="/moder_kwork/{if $p.active == 0}take_kwork{else}moder{/if}?kwork_id={$p.PID}" class="hugeGreenBtn OrangeBtnStyle h50 pull-reset i-floatright">{'Модерировать'|t}</a>
                </div>
            </div>
        {else}
            <div class="sidebarArea">
                <div class="p20 dib" style="width: 266px;">
                    <a href="/moder_kwork/{if $p.active == 0}take_kwork{else}moder{/if}?kwork_id={$p.PID}" class="hugeGreenBtn OrangeBtnStyle h50 pull-reset">{'Модерировать'|t}</a>
                </div>
            </div>
        {/if}
</div>
{/strip}