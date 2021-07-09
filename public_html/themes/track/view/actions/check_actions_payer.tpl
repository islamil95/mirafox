{strip}
	<br class="m-visible">
	<br class="m-visible">
	<a class="js-track-reject orange-btn v-align-m ml10 sm-margin-reset sx-wMax check-action-button"
	   data-track-id="{$track->MID}">
		{'Отправить на доработку'|t}
	</a>
	<div class="clear m-visible mt10"></div>
	<div class="step-block-order__or dib f12 color-gray v-align-m ml10 mr10 sm-margin-reset sx-wMax sx-text-center">{'или'|t}</div>
	<a class="js-track-form__popup-confirm-link green-btn green-btn--rubber btn-flex v-align-m sx-wMax check-action-button">
		{'Подтвердить выполнение'|t}
	</a>
	<hr class="gray {if $config.track.isFocusGroupMember} mb10 mt10 {/if}">
	<div class="f12 color-gray v-align-m ml10 t-align-l">
		{'Если в течение %s суток (%s, если больше суток приходится на выходные или праздники) вы не примете решение, заказ будет принят автоматически.'|t:$autoAcceptDays:($autoAcceptDays+1)}	<strong>{'Осталось %s до автопринятия.'|t:$timeUntilAutoaccept}</strong>
	</div>
{/strip}
