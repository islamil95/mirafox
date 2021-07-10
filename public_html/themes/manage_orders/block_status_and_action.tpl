{strip}
	{if $order.status eq "1"}
		{if $order.in_work eq "1"}
			<div class="col-2 status-block-col status-success-block status-success" >
				<div class="status-name">
					В работе
				</div>
			</div>
		{else}
			<div class="col-2 status-block-col status-success-block status-gray" >
						<div class="status-name">
							Не начат
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
		<div class="col-2 status-block-col status-success-block status-danger" >
			<div class="status-name">
			х Отменен
			</div>
		</div>
	{elseif $order.status eq "4"}
		<div class="mobilflex col-2 status-block-col status-success-block status-processing" >
			<div class="status-name">
				Сдан на проверку
			</div>
		</div>
	{elseif $order.status eq "5"}
		<div class="col-2 status-block-col status-success-block status-success" >
			<div class="status-name">
				Выполнен
			</div>
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
