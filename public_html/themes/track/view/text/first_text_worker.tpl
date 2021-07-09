{extends file="track/view/base.tpl"}

{block name="mainContent"}
	<div class="f15 {if !$config.track.isFocusGroupMember}mt15 {/if}">
		{'Покупатель следовал вашим инструкциям. Если отправленной информации недостаточно, уточните ее, отправив сообщение покупателю.'|t}
		<br>

        {if !$track->order->in_work}
            {'Уведомьте покупателя о том, что приступили к работе, нажав кнопку%s"Приступить к работе"'|t:"<br>"}
        {/if}
	</div>
	{* подсказка продавцу *}
	{include file="track/view/advice/worker.tpl"}
{/block}