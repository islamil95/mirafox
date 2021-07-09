{extends file="track/view/base.tpl"}

{block name="mainContent"}
    {strip}
		<div class="f15 {if $config.track.isFocusGroupMember} pt10 {else}mt15{/if}">
            {if $showActualStages}
				<div class="t-align-l">
                    {foreach $track->order->getNotReservedStages() as $stage}
                        {include file="track/stages/stage.tpl" showOnlyButtonPay=true}
                    {/foreach}
				</div>
				{if $config.track.isFocusGroupMember}
				<div class="pt10">
				{/if}
					<p class="mt15">{'Когда покупатель внесет оплату задачи, Kwork сразу уведомит вас об этом. До этого момента приостановите свою работу над заказом.'|t}</p>
					<p class="mt10">{'Покупателю дается до %s суток на внесение оплаты.'|t:$cancelDays}</p>
				{if $config.track.isFocusGroupMember}
				</div>
				{/if}
			{/if}
		</div>
    {/strip}
{/block}
