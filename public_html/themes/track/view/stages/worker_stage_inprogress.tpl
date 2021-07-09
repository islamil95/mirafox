{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{strip}
		<div class="f15 {if !$config.track.isFocusGroupMember}	mt15 {/if}">
			{include file="track/view/stages/track_stages_rows.tpl"}
		</div>
		<div class="f15 {if !$config.track.isFocusGroupMember}	mt10 {/if}">
			{$text}
		</div>
		{if $isNeedShowAction}
			<div class="f15 mt10">
				<button onclick="js_sendInWork();" class="green-btn" type="button">
					{'Взять в работу'|t}
				</button>
			</div>
			{if $autoCancelTimeLeftString}
				<div class="mt10 {if $timeIsRed}red-error{/if}">
					{include file="track/view/stages/autocancel_tooltip_text.tpl" assign="tooltipText"}
					{'Осталось %s до автоотмены заказа'|tn:$timeLeftFirstNumber:$autoCancelTimeLeftString}&nbsp;
					<span class="v-align-m tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover"
						data-tooltip-side="right"
						data-tooltip-text="{$tooltipText|htmlspecialchars:ENT_QUOTES}">?</span>
				</div>
			{/if}
		{/if}
	{/strip}
{/block}
