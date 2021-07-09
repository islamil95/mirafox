{extends file="track/view/base.tpl"}

{block name="mainContent"}
	{strip}
		<div class="f15 mt15">
			{$text}
		</div>
		<div class="f15 {if !$config.track.isFocusGroupMember} mt10 {/if}">
			{include file="track/view/stages/track_stages_rows.tpl"}
		</div>
	{/strip}
{/block}