{$reservedStages = $order->getReservedStages()}
{$notReservedStages = $order->getNotReservedStages()}
{$notReservedStagesWithDeleted = $order->getNotReservedStagesWithDeleted()}
{if isAllowToUser($order->USERID)}
	{$notReservedStagesForPrint = $notReservedStages}
{else}
	{$notReservedStagesForPrint = $notReservedStagesWithDeleted}
{/if}

{$endStages = $order->getEndStages()}
{$notReservedStagesCount = $notReservedStages|count}
{$notReservedStagesWithDeletedCount = $notReservedStagesWithDeleted|count}

<div class="track-stages-financial-result">
	<div class="track-stages-financial-result__item">
		<div class="track-stages-financial-result__title">{'В резерве'|t}</div>
		<div class="track-stages-financial-result__value">
			{if isAllowToUser($order->USERID)}
				{$reservedSumm = $reservedStages->sum("payer_price")}
			{else}
				{$reservedSumm = $reservedStages->sum("worker_price")}
			{/if}

			{include file="utils/currency.tpl" total=$reservedSumm currencyId=$order->currency_id lang=''}
		</div>
	</div>
	<div class="track-stages-financial-result__item">
		<div class="track-stages-financial-result__title">{'Оплачено'|t}</div>
		<div class="track-stages-financial-result__value">
			{if isAllowToUser($order->USERID)}
				{$paidSumm = $order->getPaidStages()->sum("payer_price")}
			{else}
				{$paidSumm = $order->getPaidStages()->sum("worker_price")}
			{/if}

			{include file="utils/currency.tpl" total=$paidSumm currencyId=$order->currency_id lang=''}

			{if $order->tips}
				<span class="track-stages-financial-result__value-tips">
					{'+ бонус '|t} {include file="utils/currency.tpl" total=$order->tips->amount currencyId=$order->tips->currency_id lang=''}
				</span>
			{/if}
		</div>
	</div>
	<div class="track-stages-financial-result__item">
		<div class="track-stages-financial-result__title">{'Осталось'|t}</div>
		<div class="track-stages-financial-result__value">
			{if isAllowToUser($order->USERID)}
				{$leftSumm = $notReservedStages->sum("payer_price")}
			{else}
				{$leftSumm = $notReservedStages->sum("worker_price")}
			{/if}

			{include file="utils/currency.tpl" total=$leftSumm currencyId=$order->currency_id lang=''}
		</div>
	</div>
</div>

<div class="track-stages-wave-border">
	<div class="waveborder bottom"></div>
	<div class="track-spacer"></div>
	<div class="track-spacer"></div>
	<div class="waveborder top"></div>
</div>

{$minOrderPrice = ($order->initial_offer_price > 0) ? $order->initial_offer_price : $order->price}

{* Минимальная общая стоимость предстоящих задач *}
{$customMinPrice = $minOrderPrice - $reservedStages->sum("payer_price") - $order->getPaidStages()->sum("payer_price")}
{* Максимальная общая стоимость предстоящих задач *}
{$customMaxPrice = \KworkManager::getCustomMaxPrice($order->getLang()) - $reservedStages->sum("payer_price") - $order->getPaidStages()->sum("payer_price")}
{* Минимальная стоимость задачи *}
{$stageMinPrice = $order->getStageMinPrice()}
{* Минимальная общая стоимость при добавлении задач *}
{$customMinPriceAdd = $order->getMinPrice()}
{* Максимальная общая стоимость при добавлении задач *}
{$customMaxPriceAdd = $customMaxPrice - $notReservedStages->sum("payer_price")}
<div class="js-track-stages track-stages"
	 data-stages='{array_values($notReservedStages->toArray())|@json_encode|htmlspecialchars}'
	 data-order-id="{$order->OID}"
	 data-lang="{$order->getLang()}"
	 data-stage-min-price="{$stageMinPrice}"
	 data-custom-min-price="{$customMinPrice}"
	 data-custom-max-price="{$customMaxPrice}"
	 data-price="{$notReservedStages->sum("payer_price")}"
	 data-duration='{$order->duration}'
	 data-initial-duration='{if $order->initial_duration}{$order->initial_duration}{else}{$order->duration}{/if}'
	 data-stages-max-increase-days="{$order->kwork->kworkCategory->max_days}"
	 data-stages-max-decrease-days="{$order->getMaxDecreaseDays()}"
	 data-control-en-lang="{$order->getLang() == Translations::EN_LANG}"
	 data-count-stages="{count($order->stages)}"
>
	<div class="track-stages__title">{'Задачи заказа'|t}
		<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".track-stages-tooltip"></span>
	</div>

	{if $reservedStages|count > 0}
		<div class="track-stages__group">{'Активные задачи'|tn:$reservedStages->count()} {if $reservedStages|count > 1 }({$reservedStages|count}){/if}</div>
		{foreach $reservedStages as $stage}
			{include file="track/stages/stage.tpl" spellcheck=$spellcheck}
		{/foreach}
	{/if}

	{$onlyUnreadDeleteStages = $notReservedStagesWithDeletedCount > 0 && !isAllowToUser($order->USERID) && $notReservedStagesCount == 0 && $order->getUnreadedDeletedStages()}
	{if $notReservedStagesCount > 0 || $onlyUnreadDeleteStages}
		{if $order->status != OrderManager::STATUS_CANCEL }
			<div class="js-track-stages-group-not-reserved {if $onlyUnreadDeleteStages}js-track-stages-group-not-reserved-hide{/if} track-stages__group">{'Предстоящие задачи'|tn:$notReservedStages->count()} {if $notReservedStagesCount > 1 }({$notReservedStagesCount}){/if}</div>
		{else}
			<div class="track-stages__group">{'Неначатые задачи'|tn:$notReservedStages->count()} {if $notReservedStagesCount > 1 }({$notReservedStagesCount}){/if}</div>
		{/if}
		<div class="js-group-stage-block-not-reserved">
			{$iterationStage = 1}
			{$isShowMoreBlock = false}
			{foreach from=$notReservedStagesForPrint item=stage name=notReservedStages}
				{* если предстоящих задач больше 3х, то начиная с 4ого скрываем под спойлер *}
				{if $notReservedStagesCount > 3 && $iterationStage == 4 && !$isShowMoreBlock}
					{$isShowMoreBlock = true}
					<div class="js-not-reserved-stages-more track-stages__not-reserved-stages-more">
				{/if}
				{if !$stage->deleted_at ||
					($stage->deleted_at && $logRecords[$stage->id]['action'] == Model\OrderStages\ChangeLog::ACTION_DELETE && $logRecords[$stage->id]['unread'])
				}
					{include file="track/stages/stage.tpl" spellcheck=$spellcheck}

				{/if}
				{if $smarty.foreach.notReservedStages.last && $isShowMoreBlock}
					</div>
				{/if}
				{if !$stage->deleted_at}
					{$iterationStage = $iterationStage + 1}
				{/if}
			{/foreach}
		</div>
	{/if}

	{if $notReservedStagesCount > 3
		|| (isAllowToUser($order->USERID) && $customMaxPriceAdd >= $customMinPriceAdd && $order->isCanStagesReserved())}
	<div class="track-stages__controls">
		<div class="track-stages__control">
			{if $notReservedStagesCount > 3}
				<div class="track-stages__not-reserved-stages-block">
					<a href="javascript:;" class="js-not-reserved-stages-link track-stages__not-reserved-stages-link">
						<span class="track-stages__not-reserved-stages-show">{'Показать еще %s предстоящих задач'|tn:{$notReservedStagesCount - 3}:{$notReservedStagesCount - 3}}</span>
						<span class="track-stages__not-reserved-stages-hide">{'Свернуть'|t}</span>
					</a>
				</div>
			{/if}
		</div>
		<div class="track-stages__control track-stages__control--center">
			{if isAllowToUser($order->USERID) && $customMaxPriceAdd >= $customMinPriceAdd && $order->isCanStagesReserved()}
			
				<a href="javascript:;" class="js-track-stage-add-link track-stages__add"
				   data-type-action='{if $order->isDone()}track-add-done{else}track-add{/if}'
				   data-stages=''
				   data-order-id="{$order->OID}"
				   data-lang="{$order->getLang()}"
				   data-stage-min-price="{$stageMinPrice}"
				   data-custom-min-price="{$customMinPriceAdd}"
				   data-custom-max-price="{$customMaxPriceAdd}"
				   data-price="0"
				   data-duration='{$order->duration}'
				   data-initial-duration='{if $order->initial_duration}{$order->initial_duration}{else}{$order->duration}{/if}'
				   data-stages-max-increase-days="{$order->kwork->kworkCategory->max_days}"
				   data-stages-max-decrease-days="0"
				   data-control-en-lang="{$order->getLang() == Translations::EN_LANG}"
				   data-count-stages="{count($order->stages)}">
					<img src="{"/plus.png"|cdnImageUrl}" width="18" height="18" class="mr5 icon rounded-circle" alt="">
					{if $order->isDone()}
					{'Добавить и активировать задачу'|t}
					{else}
					{'Добавить задачу'|t}
					{/if}
				</a>
			{/if}
		</div>
	</div>
	{/if}

	{if $endStages|count > 0}
		<div class="js-group-stage-link track-stages__group track-stages__group-link">{'Завершенные задачи'|tn:$endStages->count()} {if $endStages|count > 1 }({$endStages|count}){/if}</div>
		<div class="js-group-stage-block track-stages__group-block track-stages__group-block--last-border-none">
		{foreach $endStages as $stage}
			{include file="track/stages/stage.tpl" spellcheck=$spellcheck}
		{/foreach}
		</div>
	{/if}

</div>

{include file="track/view/stages/modal_change_progress.tpl"}
{include file="track/view/stages/modal_edit_stages.tpl"}
{include file="track/stages/stages_tooltip.tpl" isAllowToUser=isAllowToUser($order->USERID)}
