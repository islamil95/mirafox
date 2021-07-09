{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{strip}
		<div class="f15 {if !$config.track.isFocusGroupMember} mt15 {/if}">
			{include file="track/view/stages/track_stages_rows.tpl"}
		</div>
		<div class="f15 {if !$config.track.isFocusGroupMember} mt10 {/if}">
			{$text}
		</div>
	{/strip}
{/block}