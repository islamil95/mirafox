{strip}
	{if $kwork.is_package eq 0}
		{if !$editBlocked}
			<div class="gray-bg-border order-more-block-btn m-hidden mb20" style="background: white none repeat scroll 0 0;">
				{include file='view_order_bit.tpl'}
			</div>
		{/if}
	{else}
		<div class="package_cards_vert mb20">
			{if !$editBlocked && (isAllowToUser($kwork.USERID) || $canModer)}
				<div>
					<a href="{editKworkUrl($kwork.PID)}"
					   style="margin: auto; float: none;"
					   class="hugeGreenBtn GreenBtnStyle h50 mb10 edit-kwork-button wMax {if !empty($kwork.has_offer)}has-offers{/if}">
						{'Изменить'|t}
					</a>
				</div>
			{/if}
			{foreach $packages as $package}
				<div class="package_card_vert gray-bg-border order-more-block-btn m-hidden mb10">
					<div class="js_package_card_vert__toggleInfo package_card_vert__toggle">
						<div class="package_card_vert__price">
							{if isRu($kwork.lang)}
								<span class="package-{$package->type}-price_value">{$package->minVolumePrice|zero}</span>&nbsp;
								<span class="rouble">Р</span>
							{else}
								<span class="usd">$</span>
								<span class="package-{$package->type}-price_value">{$package->minVolumePrice|zero}</span>
							{/if}
						</div>
						<div class="package_card_vert__title">
							{getPackageName($package->type)}
						</div>
						<img class="package_card_vert__arrow" {if $package->opened}style="display: none;"{/if}
							 src="{"/arrow_right_blue.png"|cdnImageUrl}" alt=""/>
						<div class="clear"></div>
					</div>
					<div class="package_card_vert_info" {if !$package->opened}style="display:none"{/if}>
						<div class="package_card_vert_options">
							<div class="package_card_vert_option package_card_vert_option_time">
								<div class="package_card_vert_option__icon"><img src="{"/ico-time.png"|cdnImageUrl}" alt="" style="position:relative; top:2px;"></div>
								<div class="package_card_vert_option__title package_card_vert_option__title_preset">
									{insert name=declension value=a assign=days count=$package->minDuration form1="день" form2="дня" form3="дней"}
									<span class="package-{$package->type}-duration">{$package->minDuration} {$days}</span> {'на выполнение'|t}
								</div>
							</div>
							{if $kwork.avgWorkTime GT 0}
								<div class="package_card_vert_option package_card_vert_option_time mt-10">
									<div class="package_card_vert_option__icon"><img src="{"/ico-time.png"|cdnImageUrl}" alt="" style="position:relative; top:2px;"></div>
									<div class="package_card_vert_option__title package_card_vert_option__title_preset package_card_vert_option__title_avg">
										{'Обычно выполняет за'|t|mb_strtolower} {insert name=avg_work_time value=a assign=avgWorkTime time=$kwork.avgWorkTime} {$avgWorkTime}
									</div>
								</div>
							{/if}

							{* Список пакетных услуг *}
							{include file='view_right_bar_package_item_list.tpl'}

							{if (!$actor || $actor->id != $kwork.USERID) && !$canModer && $kwork.volume_type_id && $volumeType && $volumeInSelectedType}
								<div class="volume-flex ml6 mt0">
									<div>
										<span class="f13 lh13">
											{if $additionalVolumeTypes}
												{'Количество'|t}&nbsp;&nbsp;{include file="kwork/view/volume_type_tooltip.tpl" price=$package->price}
											{else}
												{* Сделано так чтобы значек тултипа не переносился отдельно на вторую строку*}
												{'Количество'|t} <span class="dib">{$volumeType->name_plural_11_19}&nbsp;&nbsp;{include file="kwork/view/volume_type_tooltip.tpl" price=$package->minVolumePrice minVolume=$package->minVolume}</span>
											{/if}
										</span>
									</div>
									{if $additionalVolumeTypes}
										{include file="kwork/view/additional_volume_types.tpl"}
									{/if}
									<div style="margin-left: auto;">
										{assign var=packageMaxKworkCount value=maxKworkCountForVolume($package->price, $kwork.lang)}
										<input 
											type="text" 
											data-package-type="{$package->type}" 
											data-price="{$package->price}" 
											id="volume-order-right-{$package->type}" 
											data-max-count="{$packageMaxKworkCount}" 
											data-max-count-default="{$packageMaxKworkCount}"
											data-max="{$packageMaxKworkCount*$volumeInSelectedType}"
											data-volume-multiplier="{$volumeInSelectedType}"
											data-volume-multiplier-default="{$volumeInSelectedType}"
											data-min-volume="{$package->minVolume}" 
											data-min-volume-default="{$package->minVolume}" 
											data-duration="{$package->duration}"
											class="kwork-save-step__field-input input input_size_s js-field-input js-only-numeric js-volume-order w80i p5 ml10" 
											placeholder="{$package->minVolume}">
									</div>
								</div>
							{/if}
						</div>
						{if !(isAllowToUser($kwork.USERID) || $canModer)}
							<div class="package_card_vert_options_controls">
								<input type="hidden" name="{UserManager::SESSION_CSRF_KEY}" value="{${UserManager::SESSION_CSRF_KEY}}">
								<div class="package_card_vert_options_button">
									{if $actor}
										<div class="order-block-cart">
											<a {if !$notCanOrder && !$actor->isVirtual} onclick="if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('CLICK-ORDER-TOP');}; make_package_order('{$kwork.PID}', '{$package->type}', 0);return false;"
												href="#"{/if}
															class="{if $basketEnable}order-block-cart_btn{/if} hugeGreenBtn GreenBtnStyle h50 js-make-order {if $notCanOrder}js-cannotorder{/if}">
												{'Заказать за'|t}&nbsp;
												{if isRu($kwork.lang)}
													<span class="newordext package-{$package->type}-price_value">{$package->price|zero}</span>
													&nbsp;
													<span class="rouble">Р</span>
												{else}
													<span class="usd">$</span>
													<span class="newordext package-{$package->type}-price_value">{$package->price|zero}</span>
												{/if}
											</a>
											{if $basketEnable}
												<div class="hugeGreenBtn GreenBtnStyle h50 order-block-cart_cart {if $notCanOrder}js-cannotorder{/if}" title="{'Добавить в корзину'|t}"
														 {if !$notCanOrder && !$actor->isVirtual}onclick="make_package_order('{$kwork.PID}', '{$package->type}', 1, this);"
														 data-pid="{$kwork.PID}" data-package-type="{$package->type}"
														 data-title="{$kwork.gtitle|stripslashes}" data-image="{$purl}/t3/{$kwork.photo}"
														 data-url="{$kwork.url}" data-price="{$package->price}"{/if}><i class="icon ico-cart"></i></div>
											{/if}
										</div>
									{* детали заказа: чужой ПАКЕТНЫЙ кворк, юзер залогинен *}
									{else}
										<div {if $basketEnable}class="order-block-cart"{/if}>
											<a {if !$notCanOrder}onclick="show_simple_signup('order', '{$package->type}');if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('CLICK-ORDER-TOP'); return true; }"
												 href="#"{/if}
												 class="order-block-cart_btn hugeGreenBtn GreenBtnStyle h50 {if $notCanOrder}js-cannotorder{/if}">
												{'Заказать за'|t}&nbsp;
												{if isRu($kwork.lang)}
													<span class="newordext package-{$package->type}-price_value">{$package->price|zero}</span>
													&nbsp;
													<span class="rouble">Р</span>
												{else}
													<span class="usd">$</span>
													<span class="newordext package-{$package->type}-price_value">{$package->price|zero}</span>
												{/if}
											</a>
											{if $basketEnable}
												<div class="hugeGreenBtn GreenBtnStyle h50 order-block-cart_cart {if $notCanOrder}js-cannotorder{/if}" onclick="notActorCreateNewCartItem(this, true);" data-pid="{$kwork.PID}"
														 data-package-type="{$package->type}" data-title="{$kwork.gtitle|stripslashes}"
														 data-image="{$purl}/t3/{$kwork.photo}" data-url="{$kwork.url}" data-price="{intval($kwork.price)}"><i class="icon ico-cart"></i></div>
											{/if}
										</div>
									{/if}
								</div>
							</div>
						{/if}
					</div>
				</div>
			{/foreach}
		</div>
	{/if}

	<div class="m-swap desktop-version {if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}" data-name="refund-guarantee">
		<div class="refund-guarantee white-bg-border p15-20">
			<div class="visible-part">
				<div>
					{if $pageSpeedMobile}
						<img src="{"/blank.png"|cdnImageUrl}" class="refund-icon lazy-load_scroll" data-src="{"/refund-{Translations::getLang()}.png"|cdnImageUrl}" width="100" height="100" alt="">
					{else}
						<img class="refund-icon" src="{"/refund-{Translations::getLang()}.png"|cdnImageUrl}" alt="">
					{/if}
				</div>
				<div>
					<h3 class="mb5">{'Гарантия возврата'|t}</h3>
					<p class="desc">{'Средства моментально вернутся на счет, если что&#8209;то пойдет не так'|t}</p>
					<div class="rolldown-text-title mt5" data-content=".guarantee-rolldown">{'Как это работает?'|t}</div>
				</div>
			</div>
			<div class="rolldown-text-content guarantee-rolldown mt10" data-no-rollup-link="true">
				<p>{'Kwork переводит деньги продавцу, только когда покупатель проверил и принял заказ.'|t}</p>
				<h4>{'Деньги можно вернуть:'|t}</h4>
				<ul>
					<li>{'Моментально, если заказ отменяется покупателем в первые 20 мин. после старта'|t}</li>
					<li>{'Моментально, если продавец просрочил заказ, и покупатель решил отменить его'|t}</li>
					<li>{'Моментально, если продавец и покупатель согласовали отмену заказа'|t}</li>
					<li>{'В течение нескольких часов, если заказ выполнен некачественно или не полностью'|t}</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="change-position-mobile_1">
		{include file="view_user_info.tpl"}
	</div>
	{include file="view_kwork_share.tpl"}

	{if UserManager::isAdmin() && $revenue}
		<div class="gray-bg-border p15-20 mt20 ta-left">
			{foreach from=$revenue key=key item=item}
				<div>
					{$key}: {$item}
				</div>
			{/foreach}
		</div>
	{/if}
{/strip}