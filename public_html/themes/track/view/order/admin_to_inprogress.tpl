{extends file="track/view/system.tpl"}

{block name="additionalMessage"}
	{if $showStages}
		<div class="f15 mt15">
			{"Возвращены в работу задачи:"|t}
            {include file="track/view/stages/track_stages_rows.tpl"}
		</div>
	{/if}
{/block}