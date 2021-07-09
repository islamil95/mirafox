{extends file="layout.tpl"}
{block "content"}
    {strip}
		{Helper::printJsFile("/js/pages/land.js"|cdnBaseUrl, $pageSpeedDesktop)}
		{Helper::registerFooterJsFile("/js/slick.min.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("https://www.youtube.com/iframe_api")}
		{Helper::registerFooterJsFile("/js/portfolio_view_popup.js"|cdnBaseUrl)}

        <main id="landpage">
            <section id="whereami">
                {assign var="backgroundImage" value=""}
                {if $category['seo']=="advertising-pr"}
                    {$backgroundImage="pr"}
                {else}
                    {$backgroundImage=$category['seo']}
                {/if}
                <div class="background land-bg_{$backgroundImage}">
                    <div class="overlay"></div>
                </div>
                <div class="aligner"></div>
                <div class="content">
                    <h1>{$need}</h1>
                    <h2>
                        {"Kwork - это удобный магазин фриланс-услуг."|t}<br>
                        {"Адекватные цены, скорость, а главное, 100%% гарантия возврата средств!"|t}
                    </h2>
                    <a href="/{$catalog}/{$category["seo"]}"><span class="green-btn big f21">{"Смотреть каталог предложений"|t}</span></a>
                </div>
            </section>
            <section id="howitworks"{if $pageSpeedMobile} class="lazy-load_scroll-wrapper"{/if}>
                <div class="centerwrap">
                    <h3>
                        <span class="mb15 semibold">{"Заказать %s от 500 руб. легко,"|t:$land["seo_v"]}</span>&nbsp;
                        <span class="mb30 f22">{"как купить товар в интернет-магазине"|t}</span>
                    </h3>
                    <div class="landing-how-it-works">
                        {include file='how_it_works_content.tpl'}
                    </div>
                </div>
            </section>

			{if $posts}
				<section id="kworks"{if $pageSpeedDesktop} class="lazy-load_scroll-wrapper"{/if}>
					<div class="lg-centerwrap centerwrap main-wrap m-margin-reset">
						<h3>{"Лучшие предложения от фрилансеров"|t}</h3>
						<div class="cusongs ta-center">
							<div class="cusongslist cusongslist_4_column c4c">
								{include file="fox_bit.tpl"}
								<div class="ta-center">
									{if !$loadkworksButtonHide}
										<button onclick="loadKworksPaging();" class="loadKworks mb0">{'Показать еще'|t}</button>
									{/if}
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
				</section>
			{/if}
			
            {if $portfolioList}
                <section id="portfolio">
                    <h3>{"Последние выполненные работы"|t}</h3>
                    <div id="slider" class="kwork-slider {if $portfolioList} kwork-slider_portfolio{/if} ">
                        {foreach $portfolioList as $key=>$portfolioItem}
                            {include file="portfolio_slide.tpl" portfolioItem=$portfolioItem index=$key}
                        {/foreach}
                    </div>
                    <div class="clearfix"></div>
                </section>
            {/if}
			
            <section id="compare">
                <div class="centerwrap">
                    <h3>{"Закажите на Kwork - сэкономьте время, деньги и нервы"|t}</h3>
                    {if !$pageSpeedMobile}
                    <div class="scroll-table m-hidden">
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{"СТУДИИ"|t}</th>
                                    <th>{"KWORK"|t}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{"Скорость выполнения"|t}</td>
                                    {insert name=countdown_short value=a assign=work_time time=SystemStatisticsManager::get("avg_work_time") type="duration"}
                                    <td><img src="{"/finger_down_small.png"|cdnImageUrl}" alt=""> <p>{"7 - 30 дней"|t}</p></td>
                                    <td><img src="{"/finger_up_small.png"|cdnImageUrl}" alt=""> <p>{"В среднем"|t} {$work_time}</p></td>
                                </tr>
                                <tr>
                                    <td>{"Цена"|t}</td>
                                    <td><img src="{"/finger_down_small.png"|cdnImageUrl}" alt=""> <p>{"От 5000 руб."|t}</p></td>
                                    <td><img src="{"/finger_up_small.png"|cdnImageUrl}" alt=""> <p>{"От 500 руб."|t}</p></td>
                                </tr>
                                <tr>
                                    <td>{"Выбор исполнителей"|t}</td>
                                    <td><img src="{"/finger_down_small.png"|cdnImageUrl}" alt=""> <p>{"3-20 сотрудников"|t}</p></td>
                                    <td><img src="{"/finger_up_small.png"|cdnImageUrl}" alt=""> <p>{"Из 70 000+ исполнителей"|t}</p><div style="font-weight:normal;font-size:10px;margin-top:-5px;">{"отсортированных по ответственности и отзывам"|t}</div></td>
                                </tr>
                                <tr>
                                    <td>{"Простота заказа"|t}</td>
                                    <td><img src="{"/finger_down_small.png"|cdnImageUrl}" alt=""> <p>{"Долгие согласования, длинные ТЗ, подписание договора и другая головная боль"|t}</p></td>
                                    <td><img src="{"/finger_up_small.png"|cdnImageUrl}" alt=""> <p>{"Пара кликов - заказ готов. Минимум обсуждений"|t}</p></td>
                                </tr>
                                <tr>
                                    <td>{"Возврат средств"|t}<div style="font-weight:normal;font-size:12px;margin-top:-5px;">{"в случае просрочки"|t}</div></td>
                                    <td><img src="{"/finger_down_small.png"|cdnImageUrl}" alt=""> <p>{"Вероятность стремится к нулю"|t}</p></td>
                                    <td><img src="{"/finger_up_small.png"|cdnImageUrl}" alt=""> <p>{"100%% гарантия возврата в 1 клик"|t}</p></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
					{/if}
                    <div class="m-visible">
                        <div class="land-compare">
                            <div class="land-compare__item-wrapper">
                                <div class="land-compare__item">
                                    <div class="land-compare__title">{"Скорость выполнения"|t}</div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">{"Студии"|t}:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-down"></i>
                                            {"7 - 30 дней"|t}
                                        </div>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">Kwork:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-up"></i>
                                            {"В среднем"|t} {$work_time}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="land-compare__item-wrapper">
                                <div class="land-compare__item">
                                    <div class="land-compare__title">{"Цена"|t}</div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">{"Студии"|t}:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-down"></i>
                                            {"От 5000 руб."|t}
                                        </div>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">Kwork:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-up"></i>
                                            {"От 500 руб."|t}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="land-compare__item-wrapper">
                                <div class="land-compare__item">
                                    <div class="land-compare__title">{"Выбор исполнителей"|t}</div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">{"Студии"|t}:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-down"></i>
                                            {"3-20 сотрудников"|t}
                                        </div>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">Kwork:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-up"></i>
                                            {"Из 70 000+ исполнителей"|t}
                                            <small class="land-compare__small-text">{"отсортированных по ответственности и отзывам"|t}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="land-compare__item-wrapper">
                                <div class="land-compare__item">
                                    <div class="land-compare__title">{"Простота заказа"|t}</div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">{"Студии"|t}:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-down"></i>
                                            {"Долгие согласования, длинные ТЗ, подписание договора и другая головная боль"|t}
                                        </div>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">Kwork:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-up"></i>
                                            {"Пара кликов - заказ готов. Минимум обсуждений"|t}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="land-compare__item-wrapper">
                                <div class="land-compare__item">
                                    <div class="land-compare__title">
                                        {"Возврат средств"|t}
                                        <small class="land-compare__small-text">{"в случае просрочки"|t}</small>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">{"Студии"|t}:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-down"></i>
                                            {"Вероятность стремится к нулю"|t}
                                        </div>
                                    </div>
                                    <div class="land-compare__content clearfix">
                                        <div class="land-compare__dl">Kwork:</div>
                                        <div class="land-compare__dt">
                                            <i class="land-compare__icon kwork-icon icon-thumbs-up"></i>
                                            {"100%% гарантия возврата в 1 клик"|t}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="land-statistics mt50 pb10">
                <div class="centerwrap">
                    {include file="land_statistics.tpl"}
                </div>
            </section>

            {if $cases}
                <section id="cases">
                    <div class="lg-centerwrap centerwrap main-wrap m-margin-reset">
                        <h3 class="t-align-c mb20">{$land["seo"]} - {'реальные кейсы'|t}</h3>
                        <div class="f22 t-align-c mb30">{'Или как  Kwork прокачивает бизнес'|t}</div>
                        <div class="real-case horizontal">
                            {foreach from=$cases item=case}
                                <div class="real-case_item">
                                    <div class="real-case_image">
                                        {if $pageSpeedMobile}
                                            <img src="{"/blank.png"|cdnImageUrl}" class="lazy-load" data-src="{"/cases/avatars/{$case.user_login}.jpg?0"|cdnImageUrl}" width="180" height="180" alt="{$case.title}">
                                        {else}
                                            <img src="{"/cases/avatars/{$case.user_login}.jpg?0"|cdnImageUrl}" width="180" height="180" alt="{$case.title}">
                                        {/if}
                                    </div>
                                    <div class="real-case_info">
                                        <div class="real-case_name">{$case.user_name}</div>
                                        <div class="js-multi-elipsis real-case_title" title="{$case.title}">
                                            <div class="bold">{$case.title}</div>
                                        </div>
                                        <div class="clearfix">
                                            <a href="/cases/{$case.id}" class="real-case_link">{'Читать полностью'|t}</a>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                        <div class="t-align-c"><a href="/cases" class="green-btn inactive real-case_btn">{"Смотреть все кейсы"|t}</a><div class="pb20 m-visible"></div></div>
                    </div>
                </section>
            {/if}

            <div class="dark-gray-wrap land-footer">
                <section id="seo">
                    <div class="centerwrap">
                        <div class="fontf-pnl pb40">
							<h3>
                            	{if !$pageSpeedMobile}<i class="icon ico_retina ico-kwork footer_logo mr20"></i>{/if}
                            	<span class="fs22 dib after-logo-text fontf-pnr color-white">{"Кворк.ру – супер фриланс"|t}</span>
							</h3>
                            <div class="mt20 land-info-block">{$land["info"]|stripslashes|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'}</div>
                            <a href="javascript:;" class="m-visible link_local">{'Далее '|t}</a>{* пробел нужен, т.к. в en.po есть другой перевод "Далее" *}
                        </div>
                    </div>
                </section>
            </div>
        </main>
    {/strip}
    {literal}
        <script>
            var isActor = "{/literal}{$actor|boolval}{literal}";
        </script>
    {/literal}
{/block}