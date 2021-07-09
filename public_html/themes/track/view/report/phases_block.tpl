{strip}
	{Helper::registerFooterCssFile("/css/pages/phases.css"|cdnBaseUrl)}
	{if $phasesCanEdit}
		{Helper::registerFooterJsFile("/js/pages/phases.js"|cdnBaseUrl)}
	{/if}
	
	{* TODO: Проверка на тестера. После тестирования - удалить проверку и содержимое if*}
	{if $phases|count == 1 && !\Order\Stages\OrderStageOfferManager::isTester()}
		{$phase = $phases[0]}
		<div class="fw700">
			{'Задача'|t} № {$phase.number}. {$phase.name}
		</div>
		<div class="js-phases-item fw700 mt10" data-index="{$phase.number}" data-id="{$phase.id}" data-progress="{$phase.progress}">
			{'Прогресс'|t} <span class="js-progress-value">{$phase.progress}</span>%
			<div class="phases__progress js-phases-progress">
				{include file="track/view/progress_bar.tpl" trackMID=$track->MID progress=$phase.progress}
			</div>
		</div>
	{else}
		<table class="phases {if $phasesCanEdit} js-phases-wrap-show{/if}">
			<col width="40"/>
			<col width="auto"/>
			<col width="262"/>
			<tbody>
			{foreach from=$phases key=number item=phase}
				<tr class="js-phases-item" data-index="{$number}" data-id="{$phase.id}" data-progress="{$phase.progress}">
					<td class="phases__number">
						{if $phase.number}{$phase.number}{else}{$number + 1}{/if}
					</td>
					<td><span class="js-phases-name">{$phase.name}</span></td>
					<td>
						<div class="phases__row">
							<div class="phases__label">{'Прогресс'|t}</div>
							<div class="phases__progress js-phases-progress">
								{include file="track/view/progress_bar.tpl" trackMID=$track->MID progress=$phase.progress}
							</div>
							<span class="js-progress-value">{$phase.progress}</span>
							<div class="phases__percent">%</div>
						</div>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	{/if}
{/strip}
