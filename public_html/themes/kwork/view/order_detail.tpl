{strip}
<div class="package_card_vert__order-detail">
	<span class="package_card_vert__price">{include file="kwork/view/price.tpl"}</span>
	<span class="package_card_vert__title">{'Детали заказа'|t}</span>
	<div class="package_card_vert_options">
		{if $kwork.avgWorkTime GT 0}
			<div class="package_card_vert_option package_card_vert_option_time">
				<div class="package_card_vert_option__icon"><img src="{"/ico-time.png"|cdnImageUrl}" alt="" style="position:relative; top:2px;"></div>
				<div class="package_card_vert_option__title package_card_vert_option__title_preset package_card_vert_option__title_avg">
					{'Обычно выполняет за'|t|mb_strtolower} {insert name=avg_work_time value=a assign=avgWorkTime time=$kwork.avgWorkTime} {$avgWorkTime}
				</div>
			</div>
		{/if}
		{* Услуги для однопакетного кворка *}
	</div>
	{if $actor->id != $kwork.USERID && $volumeInSelectedType && $volumeType && App::config(Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)}
		<div class="volume-flex">
			<div>
				<span class="f13 lh13">
					{* Сделано так чтобы значек тултипа не переносился отдельно на вторую строку*}
					{capture assign=volumeNameWithTooltip}
						{assign var=kworkMinPrice value=max($kwork.price, $kwork.min_volume_price)}
						<span class="dib">{$volumeType->name_plural_11_19}&nbsp;&nbsp;{include file="kwork/view/volume_type_tooltip.tpl" price=$kworkMinPrice minVolume=$minKworkCount|max:$volumeInSelectedType}</span>
					{/capture}
					{'Количество %s'|t:$volumeNameWithTooltip}
				</span>
			</div>
			{if $additionalVolumeTypes}
				{include file="kwork/view/additional_volume_types.tpl"}
			{/if}
			<div style="margin-left: auto;">
				<input 
					type="text" 
					name="volume" 
					id="volume-order-right" 
					data-max-count="{$maxKworkCount}" 
					data-max-count-default="{$maxKworkCount}"
					data-max="{$maxKworkCount*$volumeInSelectedType}" 
					data-volume-multiplier="{$volumeInSelectedType}" 
					data-volume-multiplier-default="{$volumeInSelectedType}"
					data-min-volume="{$minKworkCount}" 
					data-min-volume-default="{$minKworkCount}" 
					class="kwork-save-step__field-input input input_size_s js-field-input js-only-numeric js-volume-order w80i p5 ml10" 
					placeholder="{$minKworkCount}">
			</div>
		</div>
	{/if}
</div>
{/strip}