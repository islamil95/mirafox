{if $linksSites}
	{if !$showHostsForEdit}
		{* На форме редактирования не показываем этот заголовок*}
		<div class="mt20">
			<b>
				{if $isKworkLinksSites == \Attribute\AttributeManager::IS_LINKS} {'Список площадок:'|t}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS} {'Список доменов на продажу:'|t}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_SITES} {'Список сайтов на продажу:'|t}
				{/if}
			</b> {$linksSitesCount}
		</div>
	{/if}

	{assign var="firstSite" value=$linksSites.0}
	<div class="scroll-table mt10">
		<table class="kwork-links-sites-table">
			<thead>
			<tr>
				<th>
					{if $isKworkLinksSites == \Attribute\AttributeManager::IS_LINKS} {'Площадка'|t}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS} {'Домен'|t}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_SITES} {'Сайт'|t}
					{/if}
				</th>
				{if $isKworkLinksSites|in_array:[\Attribute\AttributeManager::IS_DOMAINS, \Attribute\AttributeManager::IS_SITES] || $firstSite->getAuditory() == \Kwork\KworkLinkSiteRelationManager::AUDITORY_RUNET}
					<th>
						{'ИКС'|t}
						<div class="tooltipster dib" data-tooltip-content="#tooltip-links-sites-tic">
							<div style="display: none;">
								<div id="tooltip-links-sites-tic">
									{'Индекс качества сайта по версии Яндекса.'|t}
									{if $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS}
										{'У домена может быть ИКС, если раньше на домене был сайт.'|t}
									{/if}
									<ul class="list-marked mt5">
										<li>{'до 50 – сайт начального уровня'|t}</li>
										<li>{'от 50 до 500 – хороший сайт'|t}</li>
										<li>{'от 500 – мощный сайт'|t}</li>
									</ul>
								</div>
							</div>
							<span class="tooltip_circle tooltip_circle--light tooltip_circle--hover">?</span>
						</div>
					</th>
				{/if}
				<th>
					{'Majestic'|t}
					<div class="tooltipster dib" data-tooltip-content="#tooltip-links-sites-majastic-cf">
						<div style="display: none;">
							<div id="tooltip-links-sites-majastic-cf">
								{'Majestic Citation Flow (поток цитирования) – показатель, помогающий определить долю ссылок на сайте и их силу при международном продвижении в Google и др. поисковых системах.'|t}
								<ul class="list-marked mt5">
									<li>{'до 5 – сайт начального уровня'|t}</li>
									<li>{'от 6 до 24 – хороший сайт'|t}</li>
									<li>{'от 25 до 100 – мощный сайт'|t}</li>
								</ul>
							</div>
						</div>
						<span class="tooltip_circle tooltip_circle--light tooltip_circle--hover">?</span>
					</div>
				</th>
				<th>
					{'Траст'|t}
					<div class="tooltipster dib" data-tooltip-content="#tooltip-links-sites-trust">
						<div style="display: none;">
							<div id="tooltip-links-sites-trust">
								{if $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS}
									{'Показатель качества ссылочного профиля домена. Чем он больше, тем выше качество ссылочного профиля. Проверяется по Check Trust.'|t}
									{'У домена может быть Траст, если раньше на домене был сайт.'|t}
								{else}
									{'Показатель качества ссылочного профиля сайта. Чем он больше, тем выше качество ссылочного профиля. Проверяется по Check Trust.'|t}
								{/if}
								<ul class="list-marked mt5">
									{if $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS}
										<li>{'до 30 – обычный домен'|t}</li>
										<li>{'от 31 до 50 – мощный домен'|t}</li>
										<li>{'от 51 до 100 – очень мощный домен'|t}</li>
									{else}
										<li>{'до 30 – обычный сайт'|t}</li>
										<li>{'от 31 до 50 – мощный сайт'|t}</li>
										<li>{'от 51 до 100 – очень мощный сайт'|t}</li>
									{/if}
								</ul>
							</div>
						</div>
						<span class="tooltip_circle tooltip_circle--light tooltip_circle--hover">?</span>
					</div>
				</th>
				<th>
					{'Спам'|t}
					<div class="tooltipster dib" data-tooltip-content="#tooltip-links-sites-spam">
						<div style="display: none;">
							<div id="tooltip-links-sites-spam">
								{'Относительное количество исходящих ссылок с сайта. Чем меньше показатель спама, тем «чище» сайт. Проверяется по Check Trust.'|t}
								{if $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS}
									{'У домена могут быть исходящие ссылки, если раньше на домене был сайт.'|t}
								{/if}
								<ul class="list-marked mt5">
									{if $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS}
										<li>{'<b class="track-green">до 7</b> – чистый домен с минимальным количеством исходящих ссылок'|t}</li>
										<li>{'<b class="light-green">от 7 до 12</b> – домен с допустимым количеством исходящих ссылок'|t}</li>
										<li>{'<b class="orange-tooltip">от 12 до 100</b> – домен с повышенным количеством исходящих ссылок'|t}</li>
									{else}
										<li>{'<b class="track-green">до 7</b> – чистый сайт с минимальным количеством исходящих ссылок'|t}</li>
										<li>{'<b class="light-green">от 7 до 12</b> – сайт с допустимым количеством исходящих ссылок'|t}</li>
										<li>{'<b class="orange-tooltip">от 12 до 100</b> – сайт с повышенным количеством исходящих ссылок'|t}</li>
									{/if}
								</ul>
							</div>
						</div>
						<span class="tooltip_circle tooltip_circle--light tooltip_circle--hover">?</span>
					</div>
				</th>
				{if $isKworkLinksSites != \Attribute\AttributeManager::IS_DOMAINS}
				<th>
					{'Трафик'|t}
					<div class="tooltipster dib" data-tooltip-content="#tooltip-links-sites-traffic">
						<div style="display: none;">
							<div id="tooltip-links-sites-traffic">
								{if Translations::isDefaultLang()}
									{* для русской версии показваем трафик за день *}
									{'Среднее количество посетителей сайта в сутки. Проверяется по Semrush.'|t}
									<ul class="list-marked mt5">
										<li>{'до 200 – низкий трафик'|t}</li>
										<li>{'от 200 до 2000 – хороший трафик'|t}</li>
										<li>{'более 2000 – высокий трафик'|t}</li>
									</ul>
								{else}
									{'Средний трафик сайта в месяц. Проверяется по Semrush.'|t}
									{* для английской версии показваем трафик за месяц *}
								{/if}
							</div>
						</div>
						<span class="tooltip_circle tooltip_circle--light tooltip_circle--hover">?</span>
					</div>
				</th>
				{/if}
				<th>{'Язык'|t}</th>
			</tr>
			</thead>
			<tbody>
				{include file='kwork/kwork_links_sites_rows.tpl'}
			</tbody>
		</table>
	</div>
	<div class="f12 t-align-c color-gray italic mt10">
		{'Параметры площадок обновляются раз в месяц, поэтому некоторые актуальные параметры могут отличаться от указанных.'|t}
	</div>
	{if count($linksSites) < $linksSitesCount}
		<div class="t-align-c f14 mt10">
			<a class="link_local" href="javascript:void(0);" {if $showHostsForEdit} data-show-hosts="1" {/if} id="more-kwork-links-sites" data-total="{$linksSitesCount}" data-id="{$firstSite->getKworkId()}">
				{'Показать'|t} {declension($linksSitesCount - count($linksSites), [Translations::t("остальную"), Translations::t("остальные"), Translations::t("остальные")])} {$linksSitesCount - count($linksSites)} {declension($linksSitesCount - count($linksSites), [Translations::t("площадку"), Translations::t("площадки"), Translations::t("площадок")])}
			</a>
			<img class="hidden" src="{"/ajax-loader.gif"|cdnImageUrl}" width="20" height="20" alt="">
			{if $linksSitesCount > \Kwork\KworkLinkSiteManager::TABLE_MAX_SHOW_LIMIT}
				<span class="hidden">{'Отображено %s %s из %s'|t:\Kwork\KworkLinkSiteManager::TABLE_MAX_SHOW_LIMIT:(declension(\Kwork\KworkLinkSiteManager::TABLE_MAX_SHOW_LIMIT, [Translations::t("площадка"), Translations::t("площадки"), Translations::t("площадок")])):$linksSitesCount}</span>
			{/if}
		</div>
	{/if}
{/if}