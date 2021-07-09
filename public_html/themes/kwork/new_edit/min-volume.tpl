<div class="card__min-volume hidden pt15 js-min-volume-block" style="display: none;">
	<div class="kwork-save-step__field-block">
		<div class="kwork-save-step__field-label kwork-save-step__field-label_fill-line">
			<div class="dib">
			<span class="tooltipster tooltip_circle tooltip_circle--light d-flex justify-content-center align-items-center" data-tooltip-side="bottom" data-tooltip-theme="light" data-tooltip-interactive="false" data-tooltip-text="<p>{'Вы можете установить минимальную сумму и объем заказа, которые готовы взять в работу. Например, 10 секунд видео у продавца стоят %s ₽, но он указывает что принимает заказы от 30 секунд на сумму от %s ₽'|t:($tooltipPrice|zero):(3*$tooltipPrice|zero)}.</p>">?</span></div>&nbsp;&nbsp;<label class="kwork-save-step__field-label-name" for="min_volume">{'Принимаю заказы от'|t}</label>&nbsp;&nbsp;
			<select name="min_volume_price" id="min_volume_price">
					{foreach from=$minVolumePrices item=row key=index}
						{*Проверяем есть ли в массиве такая цена(на тот случай если изменятся ценовой шаг)*}
						<option class="standard_price_option" value="{$row}" {if $index == 0 && !in_array($minVolumePrice,$minVolumePrices)}selected{elseif $row == $minVolumePrice}selected{/if}>
							{$row}
						</option>
					{/foreach}
			</select>&nbsp;&nbsp;
			<span class="kwork-save-step__field-label-name">{'с объемом от'|t} <span class="js-min-volume"></span> <span class="js-min-volume-type"></span>.</span>
			<input type="hidden" id="min_volume" name="min_volume" value="0" />
		</div>
	</div>
</div>