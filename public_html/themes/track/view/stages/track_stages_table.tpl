{strip}
	<table class="phases {if !$config.track.isFocusGroupMember}mt15{/if}">
		<col width="40"/>
		<col width="auto"/>
		<col width="262"/>
		<col width="20"/>
		<tbody>
		{foreach $track->trackStages as $trackStage}
		<tr>
			<td class="phases__number">{$trackStage->stage->number}</td>
			<td>{$trackStage->stage->title}</td>
			<td>
				<div class="phases__row">
					<div class="phases__label">
						{'Прогресс'|t}
					</div>
					<div class="phases__progress js-phases-progress">
						{include file="track/view/progress_bar.tpl" progress=$progress}
					</div>
					<span class="current-percent-value">{$progress}</span>
					<div class="phases__percent">%</div>
				</div>
			</td>
			{if $showCheckbox && $track->isNew()}
			{*Для покупателя показываем еще чекбоксы для отмечания задач*}
			<td class="phases__checkbox">
				{if $trackStage->stage->isCheck()}
				<input class="js-track-stages-checkbox styled-checkbox" data-id="{$trackStage->order_stage_id}" id="track-{$track->MID}-stages-checkbox-check-{$trackStage->order_stage_id}" type="checkbox" {if $checked}checked{/if} name="stageIds" value="{$trackStage->order_stage_id}">
				<label for="track-{$track->MID}-stages-checkbox-check-{$trackStage->order_stage_id}">&nbsp;</label>
				{/if}
			</td>
			{/if}
		</tr>
		{/foreach}
		</tbody>
	</table>
{/strip}
