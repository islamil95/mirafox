{strip}
  <div class="offer-payment-type js-offer-payment-type{if ($customMinPrice < $stagesPriceThreshold) && !($offer && ($offer->order->price >= $stagesPriceThreshold))} hidden{/if}">
    <div class="offer-payment-type__label">{'Желаемый порядок оплаты'|t}
		<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".offer-payment-type__tooltip"></span>
	</div>
    <div class="offer-payment-type__radio">
      <div class="js-new-offer-radio offer-payment-type__radio-item{if $offer->order && (!$offer->order->stages || !$offer->order->stages|count)} offer-payment-type__radio-item--active{/if}" data-type="all">
		<div class="offer-payment-type__radio-title">
			{'Целиком, когда заказ выполнен'|t}
		</div>
		<div class="offer-payment-type__radio-body">
			<div class="offer-payment-type__radio-icon offer-payment-type__radio-icon-all">
			  <img src="{"/payment-icon-all.svg"|cdnImageUrl}" width="50" height="50" alt="">
			</div>
			<div>
				{'Оплата поступит после успешного завершения всего заказа.'|t}
			</div>
		</div>
	</div>
  </div>
  </div>

	<div class="hidden">
		<div class="offer-payment-type__tooltip">
			<p class="fw700">{'Когда выбирать оплату целиком?'|t}</p>
			<p>{'Если проект не сильно большой и при этом:'|t}</p>
			<ol>
				<li>{'Вы не хотите утруждать заказчика проверкой промежуточных результатов.'|t}</li>
				<li>{'Вы не хотите создавать риски отмены заказа на одной из задач работы. В случае оплаты целиком вся сумма заказа резервируется сразу же. А в случае с задачами – каждая задача по очереди, и в любой момент заказчик имеет право отказаться от продолжения работы, оплатив выполненные задачи.'|t}</li>
			</ol>

			<p class="fw700">{'Когда выбирать оплату по задачам?'|t}</p>
			<p>{'Если заказ объемный и длительный, и вам, как и заказчику удобнее будет разбить его на задачи.'|t}</p>
			<p>{'Задачи позволяют получать оплату по частям, не дожидаясь окончания заказа.'|t}</p>
		</div>
	</div>
{/strip}