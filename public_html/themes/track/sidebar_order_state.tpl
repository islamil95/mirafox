{strip}
	<div class="track--info__row">
		<div class="align-items-center d-flex track--info__row-title">
            {'Статус заказа'|t}
		</div>
		<div class="track--info__row-value align-items-center d-flex">
					<span class="badge color-white {if $order->isCancelRequest()} badge--gray {else} {if in_array($order->status,[2,4,6,8])} badge--orange {elseif $order->status == 1} badge--blue {else}{if $order->isDone()} badge--green {else} badge--red {/if}{/if}
					{/if}
					 js-order-status tooltipster"
                      {if $statusDesc}
					  data-tooltip-side="top"
						data-tooltip-text="{$statusDesc}"
						{/if}>
					<span>
                    {$statusTitle }
						</span>
				</span>
		</div>
	</div>

	<div class="track--info__row">
		<div class="track--info__row-title">
            {'Цена заказа'|t}
		</div>
		<div class="track--info__row-value">
            {if $order->currency_id == \Model\CurrencyModel::USD}
				<span class="usd">$</span>
            {/if}
            {if isAllowToUser($order->USERID) || $priceFor == "payer"}
                {$order->price|zero}
			{else}
                {$order->crt|zero}
            {/if}
            {if $order->currency_id == \Model\CurrencyModel::RUB}
				&nbsp;
				<span class="rouble">Р</span>
            {/if}
		</div>
	</div>
    {if $order->isNew() || ($order->isInProgress() && !$isCancelRequest) ||$order->isCheck()}
		<div class="track--info__row">
			<div class="align-items-center d-flex track--info__row-title">
                {if $order->isCheck()}
                    {'До автопринятия'|t}
                {elseif $order->isNew() || ($order->isInProgress() && !$isCancelRequest)}
                    {if !$order->in_work && $actor->USERID == $order->worker_id}
                        {'До автоотмены'|t}
                    {else}
                        {'Срок выполнения'|t}
                    {/if}
                {else}
                    {'Срок выполнения'|t}
                {/if}
			</div>
			<div class="track--info__row-value align-items-center d-flex">
                {if $order->isNew() || ($order->isInProgress() && !$isCancelRequest)}
                    {if !$order->in_work && $actor->USERID == $order->worker_id}
                        {assign var=hoursToInwork value=$order->hoursToGetInwork()}
						<span class="tooltipster {if $hoursToInwork > 0}color-light-green{else}color-red{/if}"
							  data-tooltip-side="right"
							  data-tooltip-text="{'На взятие заказа в работу'|t}">
							{if $hoursToInwork > 0}{$hoursToInwork}{declension count=$hoursToInwork form1=' ч.' form2='&nbsp;ч.' form5='&nbsp;ч.'}{else}{'Менее 1 ч.'|t}{/if}
							</span>
                    {elseif $timeLeftStr}
						<span class="tooltipster"
							  data-tooltip-side="right"
							  data-tooltip-text="{'Оставшееся время выполнения заказа'|t}">
							{$timeLeftStr}
						</span>
                    {else}
						<div class="d-none">
							<div class="track-timeout-tooltip">
                                {if $order->isWorker($actor->USERID)}
                                    {$workTimeWarning}
                                {else}
									<p>{'Время на выполнение заказа вышло. По правилам продавец должен сдать заказ в указанный срок, иначе у вас появляется возможность моментально отменить заказ с возвратом средств. При этом рейтинг продавца будет снижен.'|t}</p>
									<h4 class="pt10 pb10">{'Сейчас вы можете:'|t}</h4>
									<ul>
										<li>{'Уточнить прогресс выполнения заказа и дождаться его выполнения несмотря на просрочку.'|t}</li>
									</ul>
									<h4 class="pt10 pb10">{'ИЛИ'|t}</h4>
									<ul>
										<li>{'Отменить заказ и выбрать другого исполнителя. Для мгновенного возврата средств укажите причину "Не выполнено вовремя" при отмене заказа.'|t}</li>
									</ul>
									<p class="pt10">{'В обоих случаях вы можете оставить свой отзыв о продавце.'|t}</p>
                                {/if}
							</div>
						</div>
						<span class="tooltipster"
							  data-tooltip-side="right"
							  data-tooltip-content=".track-timeout-tooltip">
							{'Время вышло'|t}
						</span>
                    {/if}
                {elseif $order->isCheck()}
					<span class="tooltipster tooltipstered" data-tooltip-side="right"
						  data-tooltip-text="{'Оставшееся время до автопринятия кворка'|t}">
						{$order->timeUntilAutoaccept()}</span>
					</span>
                {/if}
			</div>
		</div>
    {/if}
{/strip}
