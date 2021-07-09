{if $o|@count eq "0"}
    {'По выбранному критерию нет операциий'|t}
{else}
    <table class="table-style  response-table m-order-table">
        <thead>
        <tr>
            <td class="w19p" >
                <div class="ml20"><span class="js-table-head__filter" data-filter="date">{'Дата'|t} </span></div>
            </td>
            <td class="w52p"><span class="js-table-head__filter" data-filter="description">{'Описание'|t} </span></td>
            <td class="w12p"><span class="js-table-head__filter" data-filter="sum">{'Сумма'|t} </span></td>
            <td class="pr0"><span class="js-table-head__filter" data-filter="status">{'Статус'|t} </span></td>
        </tr>
        </thead>
        <tbody>
        {section name=i loop=$o}
            <tr>
                <td class="pl20  m-text-right">{insert name=get_time_to_days_ago value=a time=$o[i].time|date}</td>
                <td class="clearfix">
                    {if $o[i].type eq "refill" || $o[i].type eq "refill_bill"}
                        {'Пополнение'|t}
                    {elseif $o[i].type eq "order_in" and $o[i].is_tips eq 0}
                        {'Получение оплаты от покупателя'|t}
                        {if $userBlocked}
                            {$o[i].payer_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].payer_name|lower}">{$o[i].payer_name}</a>
                        {/if}
                        {if $o[i].orderStageName}
                            {'за задачу '|t} {$o[i].orderStageNumber}. "{$o[i].orderStageName}" {'по заказу'|t}
                        {else}
                            {'за задачу'|t}
                        {/if}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "order_in" and $o[i].is_tips eq 1}
                        {'Получение бонуса от'|t}
                        {if $userBlocked}
                            {$o[i].payer_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].payer_name|lower}">{$o[i].payer_name}</a>
                        {/if}
                        {'за заказ'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "order_out" and $o[i].is_extra eq 0 and $o[i].is_tips eq 0}
                        {'Оплата продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name|lower}">{$o[i].worker_name}</a>
                        {/if}
                        {if $o[i].orderStageName}
                            {'за задачу '|t} {$o[i].orderStageNumber}. "{$o[i].orderStageName}" {'по заказу'|t}
                        {else}
                            {'за заказ'|t}
                        {/if}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "order_out" and $o[i].is_extra eq 1}
                        {'Оплата продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name|lower}">{$o[i].worker_name}</a>
                        {/if}
                        {'за опции к заказу'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "order_out" and $o[i].is_tips eq 1}
                        {'Оплата бонуса для'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name|lower}">{$o[i].worker_name}</a>
                        {/if}
                        {'за заказ'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "refund" and $o[i].is_tips eq 0}
                        {if $o[i].orderStageName}
                            {'Возврат оплаты за задачу'|t} {$o[i].orderStageNumber}. "{$o[i].orderStageName}" {'по заказу'|t}
                        {else}
                            {'Возврат оплаты заказа'|t}
                        {/if}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "refund" and $o[i].is_tips eq 1}
                        {'Возврат оплаты бонуса'|t} {'за заказ'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type eq "withdraw"}
                        {insert name=get_payment_name value=a assign=payment_name payment=$o[i].payment type=$o[i].type}
                        {'Вывод на'|t} {$payment_name}
                    {elseif $o[i].type eq "moneyback"}
                        {insert name=get_payment_name value=a assign=payment_name payment=$o[i].payment type=$o[i].type}
                        {'Возврат на'|t} {$payment_name}
                    {elseif $o[i].type == 'order_out_bonus' and $o[i].is_extra eq 0}
                        {'Оплата с бонусного счёта продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name}">{$o[i].worker_name}</a>
                        {/if}
                        {'за заказ'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type == 'order_out_bonus' and $o[i].is_extra eq 1}
                        {'Оплата с бонусного счёта продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name}">{$o[i].worker_name}</a>
                        {/if}
                        {'за опции к заказу'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type == 'order_out_bill' and $o[i].is_extra eq 0}
                        {'Оплата с безналичного счёта продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name}">{$o[i].worker_name}</a>
                        {/if}
                        {'за заказ'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type == 'order_out_bill' and $o[i].is_extra eq 1}
                        {'Оплата с безналичного счёта продавцу'|t}
                        {if $userBlocked}
                            {$o[i].worker_name}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/user/{$o[i].worker_name}">{$o[i].worker_name}</a>
                        {/if}
                        {'за опции к заказу'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$baseurl}/track?id={$o[i].OID}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type == 'refill_moder_kwork'}
                        {'Пополнение за модерацию кворка'|t}
                        {if $userBlocked}
                            {$o[i].gtitle|mb_ucfirst}
                        {else}
                            <a class="purse-history-link" href="{$o[i].kworkUrl}">{$o[i].gtitle|mb_ucfirst}</a>
                        {/if}
                    {elseif $o[i].type == 'refill_moder_request'}
                        {'Пополнение за модерацию проекта'|t}
                    {elseif $o[i].type == 'refill_referal'}
                        {'Начисление по реферальной программе за пользователя '|t}
                        {if $o[i].referal_name}
                            {if $userBlocked}
                                {$o[i].referal_name}
                            {else}
                                <a class="purse-history-link" href="{$baseurl}/user/{$o[i].referal_name}">{$o[i].referal_name}</a>
                            {/if}
                        {/if}
                    {elseif $o[i].type == 'cancel_bonus'}
                        {'Списание с бонусного счета неиспользованных в течении %s дней бонусов по промокоду %s'|t:$o[i].bonus_code_day_count:$o[i].bonus_code}
                    {/if}
                </td>
                <td class="m-visible clearfix p0"></td>
                <td class="m-w50p m-pull-right  m-text-right">
                    {if in_array($o[i].type, [
                            'order_in',
                            'refill',
                            'refill_bonus',
                            'refill_bill',
                            'refund',
                            'refill_referal',
                            'refill_moder_kwork',
                            'refill_moder_request'
                        ])
                    } {else}-{/if}
                    {include file="utils/currency.tpl" lang=$actor->lang total=$o[i].price}
                </td>
                {if $o[i].type eq "refill" || $o[i].type eq "refill_referal" || $o[i].type eq "refill_moder_kwork" || $o[i].type eq "refill_moder_request"}
                    {if $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Ваш баланс пополнен'|t}">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Операция отменена'|t}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {elseif $o[i].type eq "order_out" || $o[i].type eq "order_out_bonus" || $o[i].type eq "order_out_bill"}
                    {if $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{if $o[i].is_tips}{'Бонус продавцу оплачен'|t}{else}{'Заказ оплачен. Продавец получит оплату после выполнения заказа'|t}{/if}">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Операция отменена'|t}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}

				{elseif $o[i].type eq "withdraw"}
					{if $o[i].operation_status == "new" && $o[i].wd_status == "new"}
                        <td class="pr0">
                            <div class="btn-title btn-title_gray pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right"
                                     data-tooltip-text="{'Заявка создана. Деньги выводятся 2 раза в неделю (по понедельникам и четвергам), кроме официальных выходных и праздничных дней РФ.'|t}">
                                    ?
                                </div>
								{'Заявка создана'|t}
                            </div>
                            <div class="ta-center">
                                <form action="" method="POST">
                                    <input type="hidden" name="action" value="declineWithdraw">
                                    <input type="hidden" name="operationId" value="{$o[i].id}">
                                    <a href="javascript: void(0);" class="purse-history-link" onclick="this.parentNode.submit();
                               return false;">{'Отменить вывод'|t}</a>
                                </form>
                            </div>
                        </td>
					{elseif $o[i].wd_status eq "inprogress" || ($o[i].operation_status eq "inprogress" && $o[i].wd_status eq "cancel")}
						{if in_array($o[i].payment, [OperationManager::FIELD_PAYMENT_QIWI3, \Withdraw\WithdrawQiwi4::TYPE])}
							{assign var="hintPayment" value="Зачисление на QIWI занимает до 3 рабочих дней."}
						{elseif in_array($o[i].payment, [OperationManager::FIELD_PAYMENT_WEBMONEY3, \Withdraw\WithdrawWebmoney4::TYPE])}
							{assign var="hintPayment" value="Зачисление на Webmoney занимает до 3 рабочих дней."}
						{elseif in_array($o[i].payment, [OperationManager::FIELD_PAYMENT_CARD3, \Withdraw\WithdrawCard4::TYPE])}
							{assign var="hintPayment" value="Зачисление на карту занимает до 7 рабочих дней."}
						{/if}

						<td class="pr0">
							{if in_array($o[i].payment, [\Withdraw\WithdrawCard4::TYPE, \Withdraw\WithdrawQiwi4::TYPE, \Withdraw\WithdrawWebmoney4::TYPE])
								&& $withdrawSystem == User\UserWithdrawSystemManager::PAYMORE}
								{$tooltip = 'Средства отправлены в платежную систему Kassa.com'|t}
							{else}
								{$tooltip = 'Средства отправлены в платежную систему Solar Staff'|t}
							{/if}
							<div class="btn-title btn-title_orange pull-right sm-pull-reset">
								<div class="tooltipster btn-title_right" data-tooltip-text="{$tooltip} {Helper::date($o[i].date_done, "j F")}. {$hintPayment|t}">
									?
								</div>
								{'В процессе'|t}
							</div>
						</td>
                    {elseif $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Платежная система сообщила, что средства выведены<br>%s в %s'|t:{Helper::date($o[i].date_done, "j F")}:{Helper::date($o[i].date_done, "H:i")}}.">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{if $o[i].wd_reason}{$o[i].wd_reason|nl2br|t}{/if}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {elseif $o[i].type eq "moneyback"}
                    {if $o[i].operation_status == "new"}
                        <td class="pr0">
                            <div class="btn-title btn-title_gray pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right"
                                     data-tooltip-text="{'Заявка создана. Деньги возвращаются 2 раза в неделю (по понедельникам и четвергам), кроме официальных выходных и праздничных дней РФ.'|t}">
                                    ?
                                </div>
                                {'Заявка создана'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "inprogress"}
                        <td class="pr0">
                            <div class="btn-title btn-title_orange pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Средства возвращены в платежную систему'|t}">
                                    ?
                                </div>
                                {'В процессе'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Платежная система сообщила, что средства возвращены<br>%s в %s'|t:{Helper::date($o[i].date_done, "j F")}:{Helper::date($o[i].date_done, "H:i")}}.">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                {if $o[i].moneyback_cancel_reason}
                                    <div class="tooltipster btn-title_right" data-tooltip-text="{$o[i].moneyback_cancel_reason|nl2br|t}">
                                        ?
                                    </div>
                                {/if}
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {elseif $o[i].type eq 'cancel_bonus'}
                    {if $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Средства списаны с бонусного счёта'|t}">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Операция отменена'|t}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {elseif $o[i].type eq "order_in"}
                    {if $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{if $o[i].is_tips}{'Бонус от покупателя получен'|t}{else}{'Средства переведены на ваш баланс'|t}{/if}">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Операция отменена'|t}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {elseif $o[i].type eq "refund"}
                    {if $o[i].operation_status eq "done"}
                        <td class="pr0">
                            <div class="btn-title btn-title_green pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Ваш баланс пополнен'|t}">
                                    ?
                                </div>
                                {'Выполнено'|t}
                            </div>
                        </td>
                    {elseif $o[i].operation_status eq "cancel"}
                        <td class="pr0">
                            <div class="btn-title btn-title_pink pull-right sm-pull-reset">
                                <div class="tooltipster btn-title_right" data-tooltip-text="{'Операция отменена'|t}">
                                    ?
                                </div>
                                {'Отменено'|t}
                            </div>
                        </td>
                    {/if}
                {/if}
            </tr>
        {/section}

        </tbody>
        {if $showAmount}
            <tfoot class="tfoot-style oper-amount-sum">
            <tr>
                <td></td>
                <td><b>{'ИТОГО'|t}</b> {'по выбранным критериям'|t}:</td>
                <td>{if $totalSum lt 0}- {/if}{include file="utils/currency.tpl" lang=$actor->lang total=abs($totalSum)}</td>
                <td></td>
            </tr>
            </tfoot>
        {/if}
    </table>

    <div class="pagination">
        {if $o|@count GT 0}
            <div class="t-align-c balance-pagination">
                {insert name=paging_block assign=pages value=a data=$pagingdata}
                {$pages}
            </div>
            {if !$userBlocked}
                <div class="button__export">
                    <a href="/balance/export" target="_blank" class="orange-btn export-userop"><i
                                class="fa fa-download"></i> {'Экспорт'|t}</a>
                </div>
            {/if}
            <div class="clear"></div>
        {/if}
    </div>
{/if}