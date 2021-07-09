{extends file="track/view/base.tpl"}

{block name="mainContent"}
    {block name="message"}
		<div {if $config.track.isFocusGroupMember}class="f15" {else}class="f15 mt15"{/if}>
            {$text}
            {if $actor->id == $track->order->worker_id && $track->type == "worker_inwork"}
                {* подсказка продавцу *}
                {include file="track/view/advice/worker.tpl"}
            {/if}
		</div>
    {/block}
    {if $track->message && !(in_array($track->type, ["worker_inprogress_cancel_confirm", "cron_payer_inprogress_cancel"]) && $track->reason_type == "payer_worker_cannot_execute_correct")}
		<div class="f15 {if !$config.track.isFocusGroupMember}mt15{/if} {if $track->type|in_array:['payer_check_inprogress', 'admin_done_arbitrage','admin_cancel_arbitrage', "admin_done_inprogress", "admin_cancel_inprogress"]}italic{/if}">
            {"Комментарий:"|t} {$track->message|bbcode|stripslashes|code_to_emoji}
		</div>
    {/if}
    {if $track->isFirstRework()}
        {include file="track/view/loyality/loyality_rework.tpl" loyalityVisible=true}
    {/if}
    {if $track->isCancelRatingMessage()}
		<div class="f15 {if !$config.track.isFocusGroupMember}mt15{/if} {if !$track->isCancelReasonModeNormal()}track-red{/if}">
            {$track->getCancelRatingMessage(isAllowToUser($track->order->worker_id))}
		</div>
    {/if}
    {block name="additionalMessage"}{/block}
{/block}