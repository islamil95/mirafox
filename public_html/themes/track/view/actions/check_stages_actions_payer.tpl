{strip}
	<br class="m-visible">
	<div class="d-flex flex-wrap justify-content-center align-items-center {if $config.track.isFocusMember} mt10 {/if}">

		<a href="javascript:;" class="js-track-reject-stage orange-btn sm-margin-reset m-mb10 sx-wMax {if $hasMultipleStages && !$enabled}disabled{/if}"
		   data-stage-id="{if !$hasMultipleStages}{$firstStage->id}{/if}">
			{'Отправить на доработку'|t}
		</a>

		<div class="step-block-order__or f12 color-gray ml10 sm-margin-reset">{'или'|t}</div>

		<a href="javascript:;" class="js-track-approve-stage green-btn ml10 green-btn--rubber m-wMax {if $hasMultipleStages and !$enabled}disabled{/if}"
				data-stage-id="{if !$hasMultipleStages}{$firstStage->id}{/if}">
			{'Подтвердить выполнение'|t}
		</a>
		
	</div>
	<hr class="gray">
	<div class="f12 color-gray v-align-m ml10 t-align-l">
		{if $hasMultipleStages}
			{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) вы не примете решение, задачи будут приняты автоматически. '|t:$autoAcceptDays:($autoAcceptDays+1)} <strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
		{else}
			{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) вы не примете решение, задача будет принята автоматически. '|t:$autoAcceptDays:($autoAcceptDays+1)} <strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
		{/if}
	</div>
{/strip}
