{if $linksSites}
    {foreach from=$linksSites key=key item=site}
        <tr>
            <td>
                {if $showHostsForEdit || ($isModeratedKwork && $canModer)}
                    {* На форме редактирования показываем хост сайта *}
                    {(idn_to_utf8($site->getHost(), 0, INTL_IDNA_VARIANT_UTS46))}
                {else}
					{if $isKworkLinksSites == \Attribute\AttributeManager::IS_LINKS}
						{'Площадка'|t} {$key + $linksSitesStart + 1}
						{if $actor && $existOrderedLinksSites}
							{if in_array($site->getId(), $orderedLinksSites)}
								<span class="pull-right tooltipster mt-2" data-tooltip-text="{'Ранее вы уже покупали ссылку на данной площадке'|t}" data-tooltip-delay="300">
									<img class="v-align-m" width="16" height="16" src="{"/link_sites_cart.png"|cdnImageUrl}" srcset="{"/link_sites_cart_x2.png"|cdnImageUrl}" alt="">
								</span>
							{else}
								<span class="pull-right tooltipster mt-4" data-tooltip-text="{'В рамках других кворков вы не покупали ссылку на данной площадке'|t}" data-tooltip-delay="300">
									<img class="v-align-m" width="16" height="16" src="{"/link_sites_thumbs_up.png?v=2"|cdnImageUrl}" srcset="{"/link_sites_thumbs_up_x2.png?v=2"|cdnImageUrl}" alt="">
								</span>
							{/if}
						{/if}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_DOMAINS} {'Домен'|t} {$key + $linksSitesStart + 1}
					{elseif $isKworkLinksSites == \Attribute\AttributeManager::IS_SITES} {'Сайт'|t}	{$key + $linksSitesStart + 1}
					{/if}
                {/if}
            </td>

            {if $isKworkLinksSites|in_array:[\Attribute\AttributeManager::IS_DOMAINS, \Attribute\AttributeManager::IS_SITES] || $site->getAuditory() == \Kwork\KworkLinkSiteRelationManager::AUDITORY_RUNET}
                <td>
                    {if is_null($site->getSqi()) || $site->getSqi() < 0}
                        <span class="f10 color-gray">{'не определено'|t}</span>
                    {else}
                        {$site->getSqi()|zero}
                    {/if}
                </td>
            {/if}
            <td>
                {if is_null($site->getMajesticCitationFlow()) || $site->getMajesticCitationFlow() < 0}
                    <span class="f10 color-gray">{'не определено'|t}</span>
                {else}
                    {$site->getMajesticCitationFlow()|zero}
                {/if}
            </td>
            <td>
                {if  is_null($site->getSpam()) || $site->getTrust() < 0 || $site->getTrust() > 100}
                    <span class="f10 color-gray">{'не определено'|t}</span>
                {else}
                    {$site->getTrust()|zero}
                {/if}
            </td>
            <td>
                {if is_null($site->getSpam()) || $site->getSpam() < 0 || $site->getSpam() > 100}
                    <span class="f10 color-gray">{'не определено'|t}</span>
                {else}
                    <span class="{if $site->getSpam() < 7}track-green{elseif $site->getSpam() >= 7 && $site->getSpam() <= 12}light-green{else}orange-tooltip{/if}">
                        {$site->getSpam()|zero}
                    </span>
                {/if}
            </td>
			{if $isKworkLinksSites != \Attribute\AttributeManager::IS_DOMAINS}
            <td>
				{if Translations::isDefaultLang() && $site->getTraffic()/30 < 50}
					<span class="f10 color-gray">
						 {'менее %s'|t:50}
					</span>
                {elseif !Translations::isDefaultLang() && (empty($site->getTraffic()) || $site->getTraffic() < 30)}
                    <span class="f10 color-gray">
						{* для английской версии показываем трафик за месяц *}
						{'менее %s'|t:30}
                    </span>
                {else}
                    {if Translations::isDefaultLang()}
                        {* для русской версии показываем трафик за день *}
                        {($site->getTraffic()/30)|leading_zero}
                    {else}
                        {* для английской версии показываем трафик за месяц *}
                        {$site->getTraffic()|leading_zero}
                    {/if}
                {/if}
            </td>
			{/if}
            <td>
                {if empty($site->getLanguage())}
                    <span class="f10 color-gray">{'не определено'|t}</span>
                {else}
                    {$site->getLanguageLocalized()}
                {/if}
            </td>
        </tr>
    {/foreach}
{/if}