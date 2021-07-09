{strip}
	<hr class="gray">
	<div class="f12 color-gray v-align-m ml10 t-align-l">
		{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) покупатель не примет решение, заказ будет принят автоматически.'|t:$autoAcceptDays:($autoAcceptDays+1)}	<strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
	</div>
{/strip}
