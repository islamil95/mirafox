{strip}
    {if $o[i].isCancelRequest}
		<div class="btn-title btn-title_gray m-pull-reset floatright nowrap">
            {'Запрос отмены'|t}
		</div>
	{elseif $o[i].status eq "1"}
		<div class="btn-title btn-title_blue pull-rigt wMax position-r">
			{if $o[i].progress || $o[i].report.track_id}
				<div class="order-report-table-item">
					<div class="btn-title_right tooltipster" data-tooltip-content="#order-{$o[i].id}">?</div>
					<div style="display: none;">
						<div id="order-{$o[i].id}">
							{include file="manage_orders/block_stages.tpl" order=$o[i]}
							{if $o[i].report.track_id}{$o[i].report->track->message}{/if}
						</div>
					</div>
					<span class="order-page-fill-rectangle" style = "width: {$o[i].progress}%"></span>
					<span class="order-page-fill-title position-r">{"Готово на"|t} {$o[i].progress}%</span>
				</div>
			{else}
				<div class="btn-title_right tooltipster"
					data-tooltip-text="{if $o[i].in_work}{'Продавец приступил к работе над заказом'|t}{else}{'Заказ создан, уведомление об этом отправлено продавцу'|t}{/if}">?</div>
				{'В работе'|t}
			{/if}
		</div>
	{elseif $o[i].status eq "2"}
		<div class="btn-title btn-title_orange pull-rigt wMax nowrap">
			<div class="btn-title_right tooltipster"
				data-tooltip-text="{'Спор по заказу отправлен в Арбитраж. Ожидайте решения арбитров'|t}">?</div>
			{'Арбитраж'|t}
		</div>
	{elseif $o[i].status eq "3"}
		{include file="manage_orders/block_cancel_status.tpl" order=$o[i] userType="payer"}
	{elseif $o[i].status eq "4"}
		<div class="btn-title btn-title_orange pull-rigt wMax ">
			<div class="btn-title_right tooltipster"
				data-tooltip-text="{'Продавец сдал заказ на проверку. Проверьте выполнение заказа'|t}">?</div>
			{'На проверке'|t}
		</div>
	{elseif $o[i].status eq "5"}
		<div class="t-align-c">
			{if $o[i].lang != Translations::getLang()}
				<div class="green-btn disabled wMax nowrap tooltipster"
					data-tooltip-text="{'Данный кворк можно заказать только на &quot;%s&quot;'|t:Translations::translateCurrentHost()}">
					{'Заказать еще'|t}
				</div>
			{elseif $o[i].has_stages && !$o[i].notReservedStagesCount }
				{* Если заказ задачный, и в нем нет предстоящих задач. При нажатии на кнопку открывается страница трека заказа и стандартное окно для добавления задачи.*}
				<a href="{$baseurl}/track?id={$o[i].OID}&modal=new_stage"
				   class="green-btn color-white wMax nowrap">{'Добавить задачу'|t}</a>
			{elseif $o[i].has_stages && $o[i].isCanStagesReserved}
				{* Если заказ задачный, и в нем есть предстоящие задачи, но заказ завершен. *}
				<form class="js-reserve-stage-form"
					  action="{absolute_url route="reserve_stage" params=["orderStageId" => $o[i].notReservedStagesFirstId]}">
					<button type="submit"
							class="green-btn color-white wMax nowrap">{'Оплатить следующую задачу'|t}</button>
				</form>
			{elseif ($o[i].active == 1 || $o[i].active == 5) && $o[i].feat == 1}
				<a href="javascript:void(0)"
					 data-kwork-id="{$o[i].kworkId}"
					{if $o[i].is_package}
						 data-package="{if $o[i].packageType}{$o[i].packageType}{else}standard{/if}"
					{/if}
					 data-quick="{$o[i].is_quick}"
					 data-form="new-order-js"
				     data-order-id="{$o[i].OID}"
					 class="{if !$o[i].has_stages && $isStageTester}js-order-more-link{else}js-reorder{/if} green-btn color-white wMax nowrap">{'Заказать еще'|t}</a>
			{else}
				<div class="green-btn disabled wMax nowrap tooltipster"
					data-tooltip-text="{'Данный кворк временно не продается'|t}">
					{'Заказать еще'|t}
				</div>
			{/if}
			{if $o[i].can_add_review}
				<a class="link-color" href="{$baseurl}/track?id={$o[i].OID}&scroll=1">{'Написать отзыв'|t}</a>
			{/if}
		</div>
	{elseif $o[i].status eq "6"}
		<div class="btn-title btn-title_orange pull-rigt wMax ">
			<div class="btn-title_right tooltipster"
				 data-tooltip-text="{'Внесите оплату задачи, чтобы продавец мог продолжить работу над заказом.'|t}">?</div>
			{'Требуется оплата'|t}
		</div>
	{elseif $o[i].status eq "8"}
		<div class="btn-title btn-title_orange pull-rigt wMax nowrap">
			<div class="btn-title_right tooltipster"
				data-tooltip-text="{'Заказ создан и отправлен в работу'|t}">?</div>
			{'Ожидание'|t}
		</div>
	{/if}
{/strip}