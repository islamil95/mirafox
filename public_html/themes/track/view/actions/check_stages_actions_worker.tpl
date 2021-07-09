{strip}
	<div class="f12 color-gray v-align-m ml10 t-align-l">
		{if $hasMultipleStages}
			{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) покупатель не примет решение, задачи будут приняты автоматически. '|t:$autoAcceptDays:($autoAcceptDays+1)} <strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
		{else}
			{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) покупатель не примет решение, задача будет принята автоматически. '|t:$autoAcceptDays:($autoAcceptDays+1)} <strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
		{/if}
	</div>
{/strip}
