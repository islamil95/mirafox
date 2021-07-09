{strip}
	<form action="{$baseurl}/" method="post" name="newextformside" id="newextformside"
		  data-pause-on="{$kwork_pause_on}" data-pause-off="{$kwork_pause_off}"
		  data-name="{$kwork.gtitle}" data-categories='{$categories}'
	>
		<div class="p15-20-0-20">
			{include file="kwork/view/order_detail.tpl"}
			{* детали заказа: свой кворк *}
			{if $actor && $actor->id == $kwork.USERID || $canModer}
				<a href="{$baseurl}/edit?id={$kwork.PID}" class="hugeGreenBtn GreenBtnStyle h50 pull-right mb10
				edit-kwork-button {if !empty($kwork.has_offer)}has-offers{/if}">{'Изменить'|t}</a>
			{* детали заказа: чужой кворк, юзер залогинен *}
			{elseif $actor}
				{* вынесли html в отдельный файл, чтобы избежать дублирования кода *}
				<div class="order-block-cart">
					<a {if !$notCanOrder && !$actor->isVirtual} onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('CLICK-ORDER-TOP');} make_order();" href="#"{/if} class="{if $basketEnable}order-block-cart_btn{/if} hugeGreenBtn GreenBtnStyle h50 js-make-order {if $notCanOrder}js-cannotorder{/if}">
						{'Заказать за'|t}&nbsp;
						{include file="kwork/view/price.tpl"}
					</a>
					{if $basketEnable}
						<div class="hugeGreenBtn GreenBtnStyle h50 order-block-cart_cart {if $notCanOrder}js-cannotorder{/if}" title="{'Добавить в корзину'|t}" {if !$notCanOrder && !$actor->isVirtual}onclick="CartModule.addItem();" data-title="{$kwork.gtitle|stripslashes}" data-image="{$purl}/t3/{$kwork.photo}" data-url="{$kwork.url}" data-price="{$kwork.price|zero}"{/if}><i class="icon ico-cart"></i></div>
					{/if}
				</div>
			{* детали заказа: чужой кворк, юзер не залогинен *}
			{else}
				<div {if $basketEnable}class="order-block-cart"{/if}>
					<a {if !$notCanOrder}onclick="{if Translations::isDefaultLang()}show_simple_signup('order');{else}show_login('order');{/if} if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('CLICK-ORDER-TOP'); return true; }" href="#"{/if} class="order-block-cart_btn hugeGreenBtn GreenBtnStyle h50 {if $notCanOrder}js-cannotorder{/if}">
						{'Заказать за'|t}&nbsp;
						{include file="kwork/view/price.tpl"}
					</a>

					{if $basketEnable}
						<div class="hugeGreenBtn GreenBtnStyle h50 order-block-cart_cart {if $notCanOrder}js-cannotorder{/if}" {if !$notCanOrder}onclick="notActorCreateNewCartItem(this, false);" data-title="{$kwork.gtitle|stripslashes}" data-image="{$purl}/t3/{$kwork.photo}" data-url="{$kwork.url}" data-price="{$kwork.price|zero}"{/if}><i class="icon ico-cart"></i></div>
					{/if}
				</div>
			{/if}
			<div class="font-OpenSans f15">
				{if !$actor || $actor->id != $kwork.USERID}
					<div class="max-kwork-cnt mt7 {if $volumeInSelectedType && $volumeType && App::config(Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)} hidden{/if}">
						<span class="pt5 dib">{'Количество:'|t}</span>
						<select onchange="recalculatePrice('side');" id="kworkcntside" name="kworkcnt" class="floatright h25 styled chosenselect">
							{for $i=1 to $maxKworkCount}
								<option value="{$i}" {if $authOrder && $authOrder.kworkCount eq $i}selected{/if}>{$i}</option>
							{/for}
						</select>
					</div>
					<div class="extras-form-div">
                        {if $isQuick}
							<div>
								<input onclick="recalculatePrice('side');" id="order-is_quick_right" data-title="Срочность" name="is_quick" type="checkbox" value="1"/>
								<label class="lh15 label-text-overflow" style="background-position-y: 4px;" for="order-is_quick_right">
									Срочность
								</label>
							</div>
                        {/if}
					</div>
				{else}
					<div class="mt20 h25 dib"></div>
				{/if}
			</div>
		</div>
		<div class="order-extras mt15 db"></div>
		<input name="EPID" type="hidden" value="{$kwork.PID}"/>
	</form>
{/strip}