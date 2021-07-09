{strip}
	{if $order.status eq "1"}
		{if $order.in_work eq "1"}
			<div class="pull-right mw100 m-pull-reset t-align-r">
				{if $order.deadline && $order.deadline - time() < Helper::ONE_DAY && !$order.isCancelRequest}
					<div class="attention tooltipster"
						data-tooltip-text="
							{if $order.timeLeftStr}
								{'Внимание! Осталось менее суток, чтобы сдать заказ на проверку.'|t}
							{else}
								{'Внимание! Заказ не сдан вовремя. Покупатель может в любой момент отменить заказ.'|t}
							{/if}
					">

						<i class="fa fa-exclamation-circle"></i>
					</div>
				{/if}
				<div class="btn-title btn-title_blue nowrap m-pull-reset position-r">
					<div class="order-report-table-item">
						<div class="btn-title_right">
							<div class="tooltipster" data-tooltip-content="#order-report-table-item-{$order.OID}">?</div>
							<div style="display: none;">
								<div id="order-report-table-item-{$order.OID}">
									{include file="manage_orders/block_stages.tpl" order=$order}
									{'Вы работаете над заказом. Пожалуйста, соблюдайте сроки выполнения'|t}
								</div>
							</div>
						</div>
						{if $order.progress}
							<span class="order-page-fill-rectangle" style="width: {$order.progress}%"></span>
							<span class="order-page-fill-title position-r">{"Готово на"|t} {$order.progress}%</span>
						{else}
							{'В работе'|t}
						{/if}
					</div>
				</div>
			</div>
		{else}
			<div class="pull-right mw100 m-pull-reset t-align-r">
				<div class="attention tooltipster" data-tooltip-text="
					{if $order.time_isLost == "1" && !$order.isCancelRequest}
						{'Внимание! Осталось менее %s, чтобы начать работу над заказом.'|t:$order['timeToCancelInWork']}
					{elseif $order.time_isLost == "-2" && !$order.isCancelRequest}
						{'Внимание! Заказ не сдан вовремя. Покупатель может в любой момент отменить заказ'|t}
					{else}
						{if $order.restarted && $order.has_stages}
							{'<strong>Возьмите заказ в работу как можно скорее или откажитесь от него!</strong><br> Покупатель предлагает вам вернуться к работе над заказом. Если вы не откажетесь от заказа через интерфейс или не приступите к нему в течение 1 суток, то произойдет автоотмена заказа. Автоотмена снижает рейтинг ответственности и негативно сказывается на продажах.'|t}
						{else}
							{'<strong>Возьмите заказ в работу как можно скорее!</strong><br> Если не начать работу в первые 24 часа, происходит автоотмена заказа. Автоотмена снижает ваш рейтинг ответственности и негативно сказывается на продажах.'|t}
						{/if}
					{/if}
				">
					<i class="fa fa-exclamation-circle"></i>
				</div>
				<div class="btn-title btn-title_gray nowrap m-pull-reset position-r">
					<div class="btn-title_right tooltipster"
						data-tooltip-text="{'Вы еще не приступили к работе над данным заказом. Приступите к работе как можно скорее'|t}">?</div>
					{'Не начат'|t}
				</div>
				<form action="{route route="track_worker_inwork"}" name="inprogress{$order.OID}{if $blockType}_{$blockType}{/if}" method="post" target="preview">
					<input type="hidden" name="action" value="worker_inwork">
					<input type="hidden" name="orderId" value="{$order.OID}">
				</form>
				<div class="floatright ml6 m-pull-reset m-ml10 mt3 mb3 m-m0">
					<a onclick="if (typeof (yaCounter32983614) !== 'undefined')
						yaCounter32983614.reachGoal('I-DO-WORK');
						document.inprogress{$order.OID}{if $blockType}_{$blockType}{/if}.submit()"
						class="link-color link-action"
						style="cursor:pointer;">
						{'Приступить к работе'|t}
					</a>
				</div>
			</div>
		{/if}
	{elseif $order.status eq "2"}
		<div class="btn-title btn-title_orange m-pull-reset floatright nowrap">
			<div class="btn-title_right tooltipster"
				data-tooltip-text="{'Спор по заказу отправлен в Арбитраж. Ожидайте решения арбитров'|t}">?</div>
			{'Арбитраж'|t}
		</div>
	{elseif $order.status eq "3"}
		{include file="manage_orders/block_cancel_status.tpl" order=$order userType="worker"}
	{elseif $order.status eq "4"}
		<div class="btn-title btn-title_orange m-pull-reset floatright nowrap">
			<div class="btn-title_right tooltipster"
				data-tooltip-text="{'Заказ сдан на проверку покупателю. Ожидайте результата'|t}">?</div>
			{'На проверке'|t}
		</div>
	{elseif $order.status eq "5"}
		<div class="btn-title btn-title_green m-pull-reset floatright nowrap">
			<div class="btn-title_right tooltipster" 
				data-tooltip-text="{'Выполнение заказа подтверждено покупателем'|t}">?</div>
			{'Выполнен'|t}
		</div>
		{if $order.can_add_portfolio}
			<div class="floatright m-pull-reset">
				<a href="{$baseurl}/track?id={$order.OID}&scroll=1" class="link-color link-action">
					{'Загрузить работу'|t}
				</a>
				<span class="tooltipster tooltip_circle tooltip-manage_orders dib tooltip_circle--hover bgWhiteNoImp ml5"
					data-tooltip-text="{'Загрузите результат этого заказа в качестве иллюстрации в ваш кворк. Кворки с реальными примерами покупают в несколько раз чаще.'|t}">?</span>
			</div>
		{/if}
	{elseif $o[i].status eq "6"}
		<div class="btn-title btn-title_orange pull-rigt wMax nowrap">
			<div class="btn-title_right tooltipster"
				 data-tooltip-text="{'Дождитесь, когда покупатель внесет оплату под следующую задачу. Только после этого приступайте к работе над ним.'|t}">?</div>
			{'Ожидается оплата'|t}
		</div>
	{/if}
{/strip}
