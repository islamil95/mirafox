{strip}
	{if $packages && $packages|count > 0}
		<div class="bgWhite clearfix mb0 b-package" style="border-top:none;">
			<div class="clearfix mb20">
				<br>
				<h2 class="pl15 pr15">{'Выберите вариант кворка'|t}</h2>

				<div class=" package_tabs m-visible mt20">
					{foreach $packages as $package}
						<div data-type="{$package->type}">
							{if isRu($kwork.lang)}
								<span>{$package->price|zero}</span>
								&nbsp;
								<span class='rouble'>Р</span>
							{else}
								<span>$</span>
								{$package->price|zero}
							{/if}
						</div>
					{/foreach}
				</div>

				{$showPackageDesc = false}
				{foreach $packages as $package}
					{if $package->desc != ""}
						{$showPackageDesc = true}
					{/if}
				{/foreach}
				<div class="pl15 pr15 ">

					<div class="package_cards">
						<div class="m-visible">
							{foreach $packages as $package}
								<b class="b-package_variant show-{$package->type} mb10 ">
									{getPackageName($package->type)}
								</b>
							{/foreach}
						</div>

						<div class="view-packages-container">
							<input type="hidden" name="{UserManager::SESSION_CSRF_KEY}" value="{${UserManager::SESSION_CSRF_KEY}}">
							<div class="view-packages-row view-packages-head m-hidden">
								<div class="view-packages-cell view-package-label"></div>
								{foreach $packages as $package}
									<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if}">
										{getPackageName($package->type)}
									</div>
								{/foreach}
							</div>
							{if $showPackageDesc}
								<div class="view-packages-row view-package-description">
									<div class="view-packages-cell view-package-label">
										{'Краткое описание'|t}
									</div>
									{foreach $packages as $package}
										<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if} show-{$package->type}">
											<div class="word-break-word wMax">{$package->desc}</div>
										</div>
									{/foreach}
								</div>
							{/if}
							{foreach $uniquePackageItemIds as $item}
								<div class="view-packages-row">
									<div class="view-packages-cell view-package-label {if $package->highlighted}{*border-highlighted*}{/if} show-{$package->type}">
									<span class="{if $item->hint != ''} tooltipster{/if}{if $item->packageItemType === 'custom' && $canModer} custom-package-label{/if}"
											{if $item->hint != ''}
										data-tooltip-side="right"
										data-tooltip-text="{$item->hint}"
										data-tooltip-theme="dark"
											{/if}>
										{$item->name}
									</span>
									</div>
									{foreach $packages as $package}
										<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if} show-{$package->type}">
											{$packageItem = $package->items[$item->packageItemType][$item->pi_id]}
											{if $packageItem}
												{if $packageItem->type == 'text'}
													{if $packageItem->value != ''}
														{$packageItem->value}
													{else}
														<span style="font-weight:normal">&mdash;</span>
													{/if}
												{elseif $packageItem->type == 'int'}
													{if $packageItem->value > 0 || ($packageItem->required && $packageItem->value >= 0)}
														{if $packageItem->can_lower && $packageItem->value > 1}
															{'до'|t}
														{/if} {if $packageItem->value|is_numeric}{$packageItem->value|zero}{else}{$packageItem->value}{/if}
													{else}
														<span style="font-weight:normal">&mdash;</span>
													{/if}
												{elseif $packageItem->type == 'label'}
													{if $packageItem->value > 0}
														<img src="{"/greengalka.png"|cdnImageUrl}" alt=""/>
													{else}
														<span style="font-weight:normal">&mdash;</span>
													{/if}
												{elseif $packageItem->type == 'list'}
													{if $packageItem->value}
														<span style="font-weight:normal">{$packageItem->value}</span>
													{else}
														<span style="font-weight:normal">&mdash;</span>
													{/if}
												{/if}
											{else}
												<span style="font-weight:normal">&mdash;</span>
											{/if}
										</div>
									{/foreach}
								</div>
							{/foreach}
							<div class="view-packages-row">
								<div class="view-packages-cell view-package-label">
									<span>{'Срок выполнения'|t}</span>
								</div>
								{foreach $packages as $package}
									<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if} show-{$package->type} package-{$package->type}-duration">
										{$package->minDuration} {declension count=$package->minDuration form1="день" form2="дня" form5="дней"}
									</div>
								{/foreach}
							</div>
							{if $kwork.volume_type_id && $volumeType && $volumeInSelectedType}
								<div class="view-packages-row">
									<div class="view-packages-cell view-package-label">
										<span>
											{if $additionalVolumeTypes}
												{'Количество'|t}
												{include file="kwork/view/additional_volume_types.tpl"}
											{else}
												{'Количество %s'|t:$volumeType->name_plural_11_19}
											&nbsp;
											<span class="tooltipster tooltip_circle tooltip_circle--size14 tooltip_circle--light tooltip_circle--hover m-hidden" data-tooltip-side="right" data-tooltip-text="{'В пакет включено до %s %s.'|t:$volumeInSelectedType:$volumeType->getPluralizedNameGenetive($volumeInSelectedType)}" data-tooltip-theme="light">?</span>
											{/if}
										</span>
										{foreach $packages as $package}
											<span class="view-package-tooltip-mobile show-{$package->type}">
												{include file="kwork/view/volume_type_tooltip.tpl" price=$package->minVolumePrice minVolume=$package->minVolume}
											</span>
										{/foreach}
									</div>
									{foreach $packages as $package}
										<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if} show-{$package->type}">
											{assign var=packageMaxKworkCount value=maxKworkCountForVolume($package->price, $kwork.lang)}
											{if $canModer || $actor->id == $kwork.USERID}
												{$package->minVolume}
											{else}
												<input
													id="table-volume-{$package->type}"
													data-package-type="{$package->type}"
													data-price="{$package->price}"
													type="text"
													data-max-count="{$packageMaxKworkCount}"
													data-max-count-default="{$packageMaxKworkCount}"
													data-max="{$packageMaxKworkCount * $volumeInSelectedType}"
													data-volume-multiplier="{$volumeInSelectedType}"
													data-volume-multiplier-default="{$volumeInSelectedType}"
													data-min-volume="{$package->minVolume}"
													data-min-volume-default="{$package->minVolume}"
													data-duration="{$package->duration}"
													class="kwork-save-step__field-input input input_size_s js-field-input js-volume-order js-only-numeric pl10i"
													placeholder="{$package->minVolume}">
											{/if}
										</div>
									{/foreach}
								</div>
							{/if}
							<div class="view-packages-row view-packages-footer m-hidden">
								<div class="view-packages-cell view-package-label"></div>
								{foreach $packages as $package}
									<div class="view-packages-cell view-package-field {if $package->highlighted}border-highlighted{/if} show-{$package->type}">
										<div class="w100p">
											<div class="package_card__price">
												{if isRu($kwork.lang)}
													<span class="package-{$package->type}-price_value">{$package->minVolumePrice|zero}</span>
													&nbsp;
													<span class='rouble'>Р</span>
												{else}
													<span>$</span>
													<span class="package-{$package->type}-price_value">{$package->minVolumePrice|zero}</span>
												{/if}
											</div>
											{if (!$actor || isNotAllowUser($kwork.USERID)) && !$canModer}
												{if $actor}
													<span {if !$notCanOrder && !$actor->isVirtual}onclick="make_package_order('{$kwork.PID}', '{$package->type}', 0);"{/if}
														  class="green-btn package-card-option__button {if $notCanOrder}js-cannotorder{/if}">
														{'Заказать'|t}
													</span>
												{else}
													<span onclick="show_simple_signup('order', '{$package->type}');" class="green-btn package-card-option__button">
														{'Заказать'|t}
													</span>
												{/if}
											{/if}
										</div>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="m-visible{if $actor && $actor->id eq $kwork.USERID} hidden{/if}">
						{foreach $packages as $package}
							<div class="b-package_variant show-{$package->type}">
								{if isNotAllowUser($kwork.USERID) && !$canModer}
								{if $actor}
								<span {if !$notCanOrder && !$actor->isVirtual}onclick="make_package_order('{$p.PID}', '{$package->type}', 0);"{/if}
									  class="green-btn package-card-option__button {if $notCanOrder}js-cannotorder{/if}">
											{'Заказать за'|t}&nbsp;
									{else}
									<span onclick="show_simple_signup('order', '{$package->type}');"
										  class="green-btn package-card-option__button">
											{'Заказать за'|t}&nbsp;
										{/if}
										{if isRu($kwork.lang)}
											<span class="package-{$package->type}-price_value">{$package->price|zero}</span>
											&nbsp;
											<span class='rouble'>Р</span>
								{else}
									<span>$</span>
											<span class="package-{$package->type}-price_value">{$package->price|zero}</span>
										{/if}
									</span>
									{/if}
							</div>
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}