{strip}
{*	<div class="m-hidden m-clearfix rowRender">*}
{*		<td class="ellipsis-wrap">*}
{*			<div class="ellipsis ml10" data-id="{$order.OID}">*}
{*				{include file="manage_orders/block_order_name.tpl"} *}
{*				{if $order.canEditName eq 1}*}
{*					<i class="change-order-name-js fa fa-pencil tooltipster" data-tooltip-text="{'Изменить название заказа'|t}" rel="{$order.OID}"></i>*}
{*				{/if}*}
{*				{include file="components/orders_row_inbox_ico.tpl" order=$order}*}
{*				{include file="components/orders_row_report_ico.tpl" order=$order}*}
{*				{if $order.project}*}
{*					<img style="height:1em;" class="pl10 v-align-m tooltipster" alt="" src="{"/orderfromrequest_icon.png"|cdnImageUrl}"*}
{*						 data-tooltip-text='{'Заказ по запросу'|t} "{$order.project|truncate:80:"..."}"'>*}
{*				{/if}*}
{*				{include file="components/orders_row_promo_ico.tpl" order=$order}*}
{*				{include file="components/orders_row_stages_done_text.tpl" order=$order}*}
{*			</div>*}
{*		</td>*}
{*		<td class="pr">*}
{*			{include file="utils/currency.tpl" currencyId=$order.currencyId total=$order.displayCrt}*}
{*			{if $order.secondStagesPrice && $order.status !== "3" && $order.status !== "5"}*}
{*				{if $order.status eq "6"}*}
{*					{$tooltip_text={'Указана стоимость задачи, под который требуется зарезервировать средства. Ниже написана суммарная стоимость заказа.'|t}}*}
{*				{else}*}
{*					{$count=$order.stagesCount}*}
{*					{$tooltip_text={'Указана стоимость задачи, под который зарезервированы средства. Ниже написана суммарная стоимость заказа.'|tn:$count}}*}
{*				{/if}*}
{*				<span *}
{*					class="ml5 tooltipster kwork-icon icon-custom-help icon-custom-help_bg-white icon-custom-help_size-18"*}
{*					data-tooltip-text="{$tooltip_text}" *}
{*				></span>*}
{*				<br>*}
{*				<span class="nowrap color-gray f13 font-OpenSans">*}
{*					{'из'|t} {include file="utils/currency.tpl" currencyId=$order.currencyId total=$order.secondStagesPrice}*}
{*				</span>*}
{*			{/if}*}
{*		</td>*}
{*		<td class="nowrap">*}
{*			{include file="manage_orders/block_user.tpl"}*}
{*		</td>*}
{*		<td class="nowrap">*}
{*			{include file="manage_orders/block_data.tpl"}*}
{*		</td>*}

{*		{if $s eq 'active'}*}
{*			<td>*}
{*				{if !$order.isCancelRequest}*}
{*                    {if $order.timeLeftStr}*}
{*						<span class="tooltipster" data-tooltip-text="{'Осталось на выполнение заказа'|t}">*}
{*							{$order.timeLeftStr}*}
{*						</span>*}
{*                    {else}*}
{*                        {"Время вышло"|t}*}
{*                    {/if}*}
{*                {else}*}
{*					&nbsp;&nbsp;&mdash;*}
{*                {/if}*}
{*			</td>*}
{*		{/if}*}

{*		<td class="pr10 nowrap">*}
{*			{include file="manage_orders/block_status_and_action.tpl"} //////////////////////////////////////////////////*}
{*		</td>*}

{*		<td class="pr10 nowrap">*}
{*			<div class="progress-line">*}
{*				{foreach item=histItem from=$order.trackHistory name=history}*}
{*					{$histItem->getShortDescription()} {$histItem->getDate()|date:"j M, H:i"}<br>*}
{*				{/foreach}*}
{*                {if $order.status == \OrderManager::STATUS_DONE}*}
{*					<div class="progress-line--item">*}
{*						<div class="progress-line--item__icon "></div>*}
{*						<div class="progress-line--item__content">Опубликован в портфолио</div>*}
{*					</div>*}
{*                {/if}*}
{*				{if !in_array((int)$order.status,[\OrderManager::STATUS_CANCEL,\OrderManager::STATUS_DONE])}*}
{*					{foreach \Track\TrackHistory::getMustHaveTypes() as $type}*}
{*						{if !in_array($type, $order.trackHistoryDescriptions) }*}
{*							<div class="progress-line--item">*}
{*								<div class="progress-line--item__icon "></div>*}
{*								<div class="progress-line--item__content">{$type}</div>*}
{*							</div>*}
{*						{/if}*}
{*					{/foreach}*}
{*				{/if}*}
{*				{if $order.timeInWork}*}
{*				<br>Выполнено за {$order.timeInWork} сек.*}
{*				{/if}*}
{*			</div>*}
{*		</td>*}
{*	</div>*}

	<div class="m-hidden m-clearfix rowRender w-100">
		<div class="row m-0" style="margin-left: 24px!important;  margin-top: 16px!important;">
			<div class="col m-0 p-0 avatar-img-block"   >
				<img src="/files/avatar/2.png" class="avatar-img">
			</div>
			<div class="col m-0 p-0 "style="margin-left: 24px!important;" >
				<div class="workName row m-0 p-0 " style="display: unset;">
					{include file="manage_orders/block_order_name.tpl"}
				</div>
				<div class="row m-0 p-0 user-status-block align-items-center" >Покупатель онлайн <small class="indicator-user-status"></small></div>
				<div class="push-message-block"><div class="push-message-name">Отправить сообщение</div></div>
			</div>
				{include file="manage_orders/block_status_and_action.tpl"}
		</div>
		<div class="row render-row-block-content" {if $order.status eq "1"} {if $order.in_work eq "1"} style="display: flex;padding: 0px 29px;" {else} style="display: none;padding: 0px 29px;" {/if}{elseif $order.status eq "4"} style="display: flex;padding: 0px 29px;" {/if} style="display: none; padding: 0px 29px;">
			<div class="row p-0 m-0">
				<div class="order-status-name" style="width: 72px;display: flex;align-items: center;flex-wrap: wrap;">
					<div class="w-100">Заказ</div>
					<div class="w-100">создан</div>
				</div>
				<div style="margin-left: 10px;margin-right:8px;"  class="align-items-center d-flex">—</div>
				<div class="col justify-content-start align-items-center p-0 d-flex order-status-text-bold">Получена информация от покупателя</div>
				<div class="order-status-text-small w-100" style="margin-top: 5px;">Покупатель следовал вашим инструкциям. Если отправленной информации недостаточно, уточните ее, отправив сообщение покупателю.</div>
				<div class="order-status-text-small w-100" style="margin-top: 10px;">Если информации достаточно, приступайте</div>
			</div>
			<div class="row p-0 m-0" style="margin-top: 19px!important;">
				<div class="order-status-name" style="width: 72px;display: flex;align-items: center;flex-wrap: wrap;">
					<div class="w-100">Взят</div>
					<div class="w-100">в работу</div>
				</div>
				<div style="margin-left: 10px;margin-right:8px;" class="align-items-center d-flex">—</div>
				<div class="col justify-content-start align-items-center p-0 d-flex order-status-text-bold">Вы приступили к работе над заказом</div>
				<div class="order-status-text-small w-100" style="margin-top: 5px;">Приложите результат, когда будете готовы</div>
			</div>
			<div class="row p-0 m-0" style="margin-top: 61px!important;">
				<div class="order-status-name" style="width: 72px;display: flex;align-items: center;flex-wrap: wrap;">
					<div class="w-100">Сдан </div>
					<div class="w-100">на проверку</div>
				</div>
				<div style="margin-left: 10px;margin-right:8px;" class="align-items-center d-flex">—</div>
				<div class="col justify-content-start align-items-center p-0 d-flex order-status-text-bold">Заказ отправлен на проверку</div>
			</div>
		</div>
		<div class="row m-0     justify-content-end" >
			<div class="rowRenderButtonDiv">
				<div  {if $order.status eq "1"} {if $order.in_work eq "1"} class="rowRenderButton"  {/if}{elseif $order.status eq "4"} class="rowRenderButton" {/if} class="rowRenderButton rowRenderButtonTop"></div>
			</div>
			</div>
	</div>

	{* mobile version *}
	<div class="m-visible rowRenderM w-100">
		<div class="w-100">
			<div class="row m-0" style="margin-left: 8px!important;  margin-top: 16px!important;">
				<div class="col m-0 p-0 avatar-img-block"   >
					<img src="/files/avatar/2.png" class="avatar-img">
				</div>
				<div class="col m-0 p-0 "style="margin-left: 24px!important;" >
					<div class="workName row m-0 p-0 " style="display: unset;">
						{include file="manage_orders/block_order_name.tpl"}
					</div>
					<div class="row m-0 p-0 user-status-block align-items-center" >Покупатель онлайн <small class="indicator-user-status"></small></div>
					<div class="push-message-block"><div class="push-message-name">Отправить сообщение</div></div>
				</div>
				{include file="manage_orders/block_status_and_action.tpl"}
			</div>
			<div class="row render-row-block-content" {if $order.status eq "1"} {if $order.in_work eq "1"} style="display: flex" {else} style="display: none" {/if}{elseif $order.status eq "4"} style="display: flex" {/if} style="display: none">Контент</div>
			<div class="row m-0     justify-content-end" >
				<div class="rowRenderButtonDiv">
					<div  {if $order.status eq "1"} {if $order.in_work eq "1"} class="rowRenderButton"  {/if}{elseif $order.status eq "4"} class="rowRenderButton" {/if} class="rowRenderButton rowRenderButtonTop"></div>
				</div>
			</div>
		</div>
	</div>
{/strip}
