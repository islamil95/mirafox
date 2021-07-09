{extends file="track/view/base.tpl"}

{block name="mainContent"}
	<div class="f15 {if !$config.track.isFocusGroupMember}mt15{/if}">
		{$text}
	</div>
	<div class="f15 {if !$config.track.isFocusGroupMember}mt15{/if}">
		{"Сумма"|t}: {$sum}
	</div>
	<div class="f15 {if !$config.track.isFocusGroupMember}mt15 mb15{/if}">
		{if $message}
			{"Комментарий"|t}: {$message}
		{/if}
	</div>
{/block}