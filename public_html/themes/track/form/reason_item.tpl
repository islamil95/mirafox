{strip}
<div class="clearfix">
	<input name="reason"
		   class="styled-radio js-required js-popup-cancel-form__reason js-cancel-order-reason-input"
		   id="reason-{$id}-placeholder" type="radio" value="{$id}"
		   data-is-payer-unrespectful="{$reason.is_payer_unrespectful|intval}"
		   data-is-payer="{isNotAllowUser($order->worker_id)}"/>
	<label for="reason-{$id}" {if $boldItem}class="bold"{/if}>
		<i class="fa fa-question-circle-o"></i>
		{$reason.name}&nbsp;<span class="tooltip-wrapper-mobile ml5">
			<span class="tooltip_circle dib tooltipster tooltip_circle--hover tooltip_circle--light"
			  data-tooltip-text="{$reason.tooltip|t}"
			  data-tooltip-side="{if $isMobile && !$onlyDesktopVersion}top{else}right{/if}"
			  data-tooltip-theme="dark">?</span>
			</span>
	</label>
</div>
{/strip}