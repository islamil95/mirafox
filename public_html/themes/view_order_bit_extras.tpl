{strip}
    <form action="{$baseurl}/" method="post" name="newextform" id="newextform">
        <input name="EPID" type="hidden" value="{$kwork.PID}"/>
        <div style="{if $extras|count GT 0}padding: 0 20px 10px 20px;{else}padding: 0 0 10px 0;{/if}" class="select-extra">
            {if $extras|count GT 0}
                <h2 class="pt20">
                    {if $extras|@count GT 0}
                        {'Детали заказа и выбор дополнительных услуг'|t}
                    {else}
                        {'Детали заказа'|t}
                    {/if}
                </h2>
                <br>
            {/if}
        </div>
        {if $extras|@count GT 0}
            {section name=i loop=$extras}
                <input type="hidden" id="newe{$extras[i]->getId()}" value="{$extras[i]->getPrice()}"/>
                <input type="hidden" id="newt{$extras[i]->getId()}" value="{$extras[i]->getDuration()}"/>
            {/section}
            <div style="background: #fafafa none repeat scroll 0 0; border-top: 1px solid #e8e8e8;">
                <div class="order-extras order-extras-mobile" id="newextrachecks">
                    <ul class="order-extras-list">
                        {section name=i loop=$extras}
                            {if $patchData}
                                {assign var=addedExtra value=$patchData && !$patchData['extras'][$extras[i]->getId()]}
                                {assign var=editedExtra value=$patchData && $patchData['extras'][$extras[i]->getId()] && ($patchData['extras'][$extras[i]->getId()]->getTitle() != $extras[i]->getTitle() || $patchData['extras'][$extras[i]->getId()]->getPrice() != $extras[i]->getPrice())}
                            {/if}
                            <li class="cur order-extra-item {if $addedExtra}patch-border__extras-green{elseif $editedExtra} patch-border__extras-orange{/if}"  id="order-extra-block-{$extras[i]->getId()}">
                                <input class="styled-checkbox" onchange="if (typeof (yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('CLICK-EXTRAS-BOTTOM'); } recalculatePrice();" id="order-extras_{$extras[i]->getId()}" data-price="{$extras[i]->getLocalizedPrice($kwork.lang)}" data-time="{$extras[i]->getDuration()}" name="gextras[]" type="checkbox" value="{$extras[i]->getId()}"/>
								<label for="order-extras_{$extras[i]->getId()}" class="w460 option-item__text" data-option-id="{$extras[i]->getId()}">
									{if $extras[i]->getIsPopular()}
										<i class="icon extra-popular v-align-t mr5 mt-3" title="{'Часто заказываемая опция'|t}"></i>
									{/if}

									{if $addedExtra}
										<span class="patch-outline__extras-green">{Helper::formatText($extras[i]->getTitle()|stripslashes|html_entity_decode|mb_ucfirst)}</span>
									{elseif $editedExtra}
										<span class="patch-outline__extras-yellow">{Helper::formatText($extras[i]->getTitle()|stripslashes|html_entity_decode|mb_ucfirst)}</span>
									{else}
										{Helper::formatText($extras[i]->getTitle()|stripslashes|html_entity_decode|mb_ucfirst)}
									{/if}
								</label>
								<div class="option-item__price m-visible">+{$extras[i]}</div>
                                <div class="order-extras__select-block" data-id="{$extras[i]->getId()}">
                                    <div class="m-hidden floatright">
                                        <select class="h25 styled chosenselect" onchange="recalculatePrice();" id="extra_count{$extras[i]->getId()}" name="extra_count{$extras[i]->getId()}">
                                            {for $i=1 to $maxExtrasCount}
                                                <option value="{$i}">{$i} ({$extras[i]->getLocalizedPriceString($i, $kwork.lang)|stripslashes})</option>
                                            {/for}
                                        </select>
                                    </div>
                                </div>
								<div class="kwork-count-wrapper clearfix">
									<div class="bold pull-left">{'Выберите кол-во'|t}</div>
									<div class="kwork-count pull-right">
										<a href="javascript:;" class="kwork-count__link kwork-count_minus js-kwork-count-link" onclick=""></a>
										<input type="text" value="1" class="kworkcnt_mobile" readonly data-max="{$maxKworkCount}" id="mobile_extra_count{$extras[i]->getId()}">
										<a href="javascript:;" class="kwork-count__link kwork-count_plus js-kwork-count-link" onclick=""></a>
									</div>
								</div>
                            </li>
                        {/section}
                    </ul>
                </div>
            </div>
        {/if}
        {if (!$actor || $actor->id != $kwork.USERID) && !$canModer}
			<div style="border-top: 1px solid #e8e8e8; {if $extras|count GT 0}padding: 0 20px;{else}padding: 0 0 20px;{/if}" class="select-extra select-extra-mobile">
				<div style="padding: 20px 0 0;" class="font-OpenSans f15 clearfix {if $volumeInSelectedType && $volumeType && App::config(Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)} hidden{/if}">
                    <div class="kwork-count pull-right m-visible">
						<a href="javascript:;" class="kwork-count__link kwork-count_minus js-kwork-count-link" onclick=""></a>
                        <input type="text" value="1" class="kworkcnt_mobile" id="kworkcntmobile" readonly data-max="{$maxKworkCount}">
						<a href="javascript:;" class="kwork-count__link kwork-count_plus js-kwork-count-link" onclick=""></a>
                    </div>
                    <div class="m-hidden floatright">
                        <select onchange="recalculatePrice();" id="kworkcnt" name="kworkcnt" class=" h25 styled chosenselect">
                            {for $i=1 to $maxKworkCount}
                                <option value="{$i}">{$i}</option>
                            {/for}
                        </select>
                    </div>
                    <div class="font-OpenSansBold kwork-count-text">{'Количество:'|t}</div>
                </div>
            </div>
        {/if}
        {if !$actor || ($actor->id != $kwork.USERID && !$canModer)}
            {if $volumeInSelectedType && $volumeType && App::config(Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)}
				{assign var=kworkMinPrice value=max($kwork.price, $kwork.min_volume_price)}
                <div class="mobile-p20 mobile-pb20" style="{if $extras|@count GT 0}padding: 20px 20px 0 0;{else}padding: 20px 0;{/if}">
                    <div class="lh28 floatright">
                    <span class="f13">
						{if $additionalVolumeTypes}
							{'Количество'|t}
						{else}
                        	<b>{'Количество %s'|t:$volumeType->name_plural_11_19}</b>
						{/if}
                    </span>
                        &nbsp;
                        {include file="kwork/view/volume_type_tooltip.tpl" price=$kworkMinPrice minVolume=$minKworkCount|max:$volumeInSelectedType}

						{if $additionalVolumeTypes}
							{include file="kwork/view/additional_volume_types.tpl"}
						{/if}
                        &nbsp;
                        <input
							type="text"
							id="volume-order-extras"
							data-max-count="{$maxKworkCount}"
							data-max-count-default="{$maxKworkCount}"
							data-max="{$maxKworkCount*$volumeInSelectedType}"
							data-min-volume="{$minKworkCount}"
							data-min-volume-default="{$minKworkCount}"
							data-volume-multiplier="{$volumeInSelectedType}"
							data-volume-multiplier-default="{$volumeInSelectedType}"
							class="kwork-save-step__field-input input input_size_s js-field-input js-volume-order js-only-numeric w108i pl10i floatright ml10"
							placeholder="{$minKworkCount}">
                    </div>
                    <div class="clear"></div>
                </div>
            {/if}
        {/if}
    </form>

	{if !$editBlocked}
		<div class="{if $extras|@count GT 0}pb20{/if} {if $extras|@count GT 0}pt20{/if} order-more-block-btn_top pl20 {if $extras|count GT 0}pr20{/if}">
			{if $actor && $actor->id eq $kwork.USERID || $canModer}
				<div style="height: 50px;">
					<a href="{$baseurl}/edit?id={$kwork.PID}" style="min-width: 282px;" class="hugeGreenBtn GreenBtnStyle h50 pull-right edit-kwork-button {if !empty($kwork.has_offer)}has-offers{/if}">{'Изменить'|t}</a>
				</div>
			{elseif $actor}
				<div style="height: 50px;" class="clear">
					{if $basketEnable}
						<div class="order-more-block-btn__cart hugeGreenBtn GreenBtnStyle h50 pull-right pl5 {if $notCanOrder}js-cannotorder{/if}" title="{'Добавить в корзину'|t}" {if !$notCanOrder && !$actor->isVirtual}onclick="CartModule.addItem();" data-title="{$kwork.gtitle|stripslashes}" data-image="{$purl}/t3/{$kwork.photo}" data-url="{$kwork.url}" data-price="{$kwork.price|zero}"{/if}><i class="icon ico-cart"></i></div>
					{/if}
					<a {if !$notCanOrder && !$actor->isVirtual}onclick="if (typeof (yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('CLICK-ORDER-BOTTOM'); };make_order();" href="#"{/if} class="{if $basketEnable}order-block-cart_btn{/if} hugeGreenBtn GreenBtnStyle h50 pull-right order-more-block-btn__mobile-btn js-make-order {if $notCanOrder}js-cannotorder{/if}">
						{'Заказать за'|t}&nbsp;
						{include file="kwork/view/price.tpl"}
					</a>
				</div>
			{else}
				<div style="height: 50px;">
					{if $basketEnable}
						<div class="order-more-block-btn__cart hugeGreenBtn GreenBtnStyle h50 pull-right pl5 {if $notCanOrder}js-cannotorder{/if}" title="{'Добавить в корзину'|t}" {if !$notCanOrder} onclick="notActorCreateNewCartItem(this, false);" data-title="{$kwork.gtitle|stripslashes}" data-image="{$purl}/t3/{$kwork.photo}" data-url="{$kwork.url}" data-price="{$kwork.price|zero}"{/if}>
							<i class="icon ico-cart"></i>
						</div>
					{/if}
					<a {if !$notCanOrder}onclick="{if Translations::getLang() == Translations::DEFAULT_LANG}show_simple_signup('order');{else}show_login();{/if} if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('CLICK-ORDER-BOTTOM'); return true; }"{/if} class="hugeGreenBtn GreenBtnStyle h50 pull-right cur order-more-block-btn__mobile-btn {if $notCanOrder}js-cannotorder{/if}">
						{'Заказать за'|t}&nbsp;
						{include file="kwork/view/price.tpl"}
					</a>
				</div>
			{/if}
			<div class="clear"></div>
		</div>
	{/if}
{/strip}