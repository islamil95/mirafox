{strip}
    <div class="gray-bg-border clearfix mb20">
        <div class="contentArea mb0 w100p pb0">
            <div class="p15-20 sm-text-center">
                <div class="block-circle block-circle-60 block-circle_orange dib v-align-t"><i class="ico-info"></i></div>
                <br class="m-visible"><br class="m-visible">
                <p class="f16 font-OpenSans dib v-align-m ml15 mw90p sm-margin-reset">
                    {'Кворк отправлен на модерацию. Он будет доступен покупателям сразу после того, как модератор проверит его. Вы можете просматривать и изменять статусы ваших кворков на странице <a href="/manage_kworks">Мои кворки</a>.'|t}</br>
					{'<strong>Внимание!</strong> Срок модерации может составлять от нескольких минут до 3 дней'|t}
                </p>
            </div>
        </div>
    </div>
	{if App::config("extention_time_event.enable")}
		{control name="event" type="moder_extention_time"}
	{/if}
{/strip}