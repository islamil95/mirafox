{strip}
{* значит кворк неактивирован, показываем кнопку {'Активировать'|t} *}
    <div class="gray-bg-border clearfix mb20">
            <div class="contentArea mb0 pb0">
                    <div class="p15-20">
                            <div class="block-circle block-circle-60 block-circle_orange dib v-align-m"><i class="ico-info"></i></div>
                            <p class="f16 font-OpenSans dib v-align-m ml15 mw80p">{'Просмотрите как отображается Ваш кворк и нажмите Активировать, чтобы опубликовать кворк. После проверки модератором, кворк будет показан на сайте вверху списка кворков.'|t}</p>
                    </div>
            </div>
            <div class="sidebarArea">
                    <div class="p15-20">
                            <a onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('ACTIVATE-NEW-KWORK'); return true; }" href="/feature?id={$PID|stripslashes}&action=activate" class="hugeGreenBtn OrangeBtnStyle h50 pull-reset">{'Активировать'|t}</a>
                    </div>
            </div>
    </div>
{/strip}