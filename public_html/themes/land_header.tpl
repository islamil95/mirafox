{strip}
<div class="banner cat-fon-{if $land->category_parent_id}{$land->category_parent_id}{else}{$land->category_id}{/if} land-cat-fon-repeat landcloud">
	<div class="centerwrap relative">
		<div class="headertext">
            {if $land->name == 'svadba'}
                <h1 class="f34 fw600">{'Kwork открывает свадебный сезон!'|t}</h1>
            {else}
                {if $land->mode == 'infinitive'}
                    <h2 class="f34 t-align-c fw600">{if $land->seo_v}{$land->seo_v|t}{else}{$land->seo_i|t}{/if} {'от 500'|t}{if Translations::isDefaultLang()}&nbsp;<span class="rouble">Р</span>{/if}</h2>
                {else}
					<h2 class="f34 t-align-c fw600">{'Заказать'|t} {if $land->seo_v}{$land->seo_v|t|land_seo_string}{else}{$land->seo_i|t|land_seo_string}{/if} {'от 500'|t}{if Translations::isDefaultLang()}&nbsp;<span class="rouble">Р</span>{/if}</h2>
                {/if}
            {/if}
			<span class="headertext_subtitle">
                {if $land->name == 'svadba'}
                    {'Здесь вы найдете множество услуг по организации незабываемого праздника, сможете заказать подарок новобрачным всего от 500 руб.'|t}<br/>{'Это может быть дизайнерское оформление подарка, LoveStory или слайдшоу, стихотворное поздравление или красивая песня.'|t}<br/>{'Создайте романтическое настроение вместе с Kwork!'|t}
                {else}
                    {if Translations::isDefaultLang()}{'Kwork - это по-настоящему удобный магазин услуг.'|t}<br>{/if}
                    {if $land->mode == 'infinitive'}
                        {if $land->seo_v}{$land->seo_v|t|land_inf_seo_string}{else}{$land->seo_i|t|land_inf_seo_string}{/if} {'здесь даже проще, чем купить наушники в интернет-магазине.'|t}
                    {else}
						{'Заказать  '|t}{if $land->seo_v}{$land->seo_v|t|land_seo_string}{else}{$land->seo_i|t|land_seo_string}{/if} {'здесь даже проще, чем купить наушники в интернет-магазине.'|t}
                    {/if} <!--noindex-->{if Translations::isDefaultLang()}{'Адекватные цены, скорость исполнения, а главное, 100%% гарантия возврата средств!'|t}{else}<br />{'Десятки тысяч услуг, скорость исполнения и гарантия возврата средств - никогда еще фриланс не был таким приятным!'|t}{/if}<!--/noindex-->
                {/if}
			</span>
			<!--noindex-->
				<div class="color-white f16 font-OpenSans t-align-c mt20 index-advantage-block">
					<div class="dib v-align-m t-align-l    banner-icon outline-none">
						<i class="icon v-align-m ico-about-price"></i>
						<span class="dib v-align-m ml10">{'Тысячи услуг<br> от 500'|t}{if Translations::isDefaultLang()}&nbsp;<span class="rouble">Р</span>{/if}</span>
					</div>
					<div class="dib ml28 v-align-m t-align-l  banner-icon outline-none">
						<i class="icon v-align-m ico-about-term"></i>
						<span class="dib v-align-m ml10">{'Быстрый заказ без<br>долгих обсуждений'|t}</span>
					</div>
					<div  class="dib v-align-m t-align-l ml28  banner-icon outline-none">
						<i class="icon v-align-m ico-about-warranty "></i>
						<span class="dib v-align-m ml10">{'Оплата без риска<br>с гарантией возврата'|t}</span>
					</div>
				</div>
			<!--/noindex-->
		</div>
	</div>
</div>
{/strip}
