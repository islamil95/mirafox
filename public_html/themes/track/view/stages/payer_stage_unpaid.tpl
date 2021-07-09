{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{strip}
		<div class="f15 {if $config.track.isFocusGroupMember} pt10 {else}	mt15 {/if}">
			{if $showActualStages}
				<div class="t-align-l">
				{foreach $track->order->getNotReservedStages() as $stage}
					{include file="track/stages/stage.tpl" showOnlyButtonPay=true}
				{/foreach}
				</div>
			{/if}

			<p {if !$config.track.isFocusGroupMember} class="mt15" {/if}>
                {'Зарезервируйте оплату по задаче, чтобы продавец продолжил работу по заказу.'|t}

				{if $thresholdTime}
					&nbsp;{'На оплату дается не более %s. Осталось %s'|t:$unpaidCancelDays:$thresholdTime}
				{/if}
			</p>

			{if $showActualStages}
			<div class="mt10 d-flex align-items-center t-align-l">
				<img class="refund-icon mr15 h85" src="{"/refund-{Translations::getLang()}.png"|cdnImageUrl}" alt="">
				<p>{'Средства будут под надежной протекцией Kwork, пока вы не подтвердите, что работа по задаче выполнена в полном объеме и качественно.'|t}
					<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover ml5" data-tooltip-content=".track-stage-unpaid-tooltip">?</span>
				</p>
			</div>
			{/if}
		</div>

		<div class="hidden">
			<div class="track-stage-unpaid-tooltip">
				<p>{'%sЗаказ разбит на задачи%s и оплачивается постепенно по мере выполнения задач продавцом. Продавец получает оплату за задачу, когда вы 100%% довольны результатом.'|t:'<strong>':'</strong>'}</p>
			</div>
		</div>
	{/strip}
{/block}
