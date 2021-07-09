{strip}
<!-- значит кворк был удален-->
<div class="gray-bg-border clearfix mb20">
        <div class="contentArea mb0 pb0">
                <div class="p15-20 sm-text-center">
                        <div class="block-circle block-circle-60 block-circle_red dib v-align-m"><i class="ico-info"></i></div>
                        <br class="m-visible"><br class="m-visible">
                        <p class="f16 font-OpenSans dib v-align-m ml15 mw80p sm-margin-reset">
                                {if $mode == 'package'}
                                        {'Кворк на паузе, после сдачи заказа будет вновь доступен в каталоге кворков'|t}
                                {else}
                                        {'Кворк на паузе за большую очередь'|t}
                                {/if}
                        </p>
                </div>
        </div>
</div>
{/strip}