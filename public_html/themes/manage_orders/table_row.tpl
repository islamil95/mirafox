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
{*			{include file="manage_orders/block_status_and_action.tpl"}*}
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

	<div class="m-hidden m-clearfix rowRender">
		<div class="row m-0" style="margin-left: 24px!important;  margin-top: 16px!important;">
			<div class="col m-0 p-0 avatar-img-block"   >
				<img src="/files/avatar/2.png" class="avatar-img">
			</div>
			<div class="col m-0 p-0 "style="margin-left: 24px!important;" >
				<div class="workName row m-0 p-0 ">
					Баннер для сайта
				</div>
				<div class="row m-0 p-0 user-status-block align-items-center" >Покупатель онлайн <small class="indicator-user-status"></small></div>
				<div class="push-message-block"><div class="push-message-name">Отправить сообщение</div></div>
			</div>
			<div class="col-2 status-block-col" >
				<div class="status-success">
					В работе
				</div>
			</div>
		</div>
{*		<div class="row m-0">Контент</div>*}
		<div class="row m-0     justify-content-end" >
			<div class="rowRenderButtonDiv">
				<div class="rowRenderButton rowRenderButtonTop" ></div>
			</div>
			</div>
	</div>

	{* mobile version *}
	<div class="m-visible">
		<div>
			<div class="data">
				{include file="manage_orders/block_data.tpl"}
			</div>
			<div class="order-name" data-id="{$order.OID}">
				{include file="manage_orders/block_order_name.tpl"}
				{include file="components/orders_row_stages_done_text.tpl" order=$order}
			</div>
			<div>
				{include file="manage_orders/block_user.tpl"}
			</div>
		</div>
		<div>
			<div>
				{include file="manage_orders/block_status_and_action.tpl" blockType="mobile"}
			</div>
			<div class="time">
				{if !$order.isCancelRequest && $order.status === "1"}
					{if $order.timeLeftStr}
						{$order.timeLeftStr}
					{else}
						<span>{"Время вышло"|t}</span>
					{/if}
				{/if}
			</div>
			<div class="price">
				{include file="utils/currency.tpl" currencyId=$order.currencyId total=$order.displayCrt}
				{if $order.secondStagesPrice && $order.status !== "3" && $order.status !== "5"}
					{if $order.status eq "6"}
						{$tooltip_text={'Указана стоимость задачи, под который требуется зарезервировать средства. Ниже написана суммарная стоимость заказа.'|t}}
					{else}
						{$count=$order.stagesCount}
						{$tooltip_text={'Указана стоимость задачи, под который зарезервированы средства. Ниже написана суммарная стоимость заказа.'|tn:$count}}
					{/if}
					<div class="dibi ml5 tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover"
						 data-tooltip-text="{$tooltip_text}">?</div>
					<br><span class="nowrap color-gray f13">{'из'|t} {include file="utils/currency.tpl" currencyId=$order.currencyId total=$order.secondStagesPrice}</span>
				{/if}
			</div>
		</div>
	</div>
{/strip}
