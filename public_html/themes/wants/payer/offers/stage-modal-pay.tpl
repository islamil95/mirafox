{strip}
	<div class="offer-modal__item-label">{'Порядок оплаты'|t}
		<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".offer-modal__item-tooltip"></span>
	</div>
	<div class="offer-modal__radio">
		<div class="js-offer-radio offer-modal__radio-item" data-type="all">
			<div class="offer-modal__radio-img offer-modal__radio-img_all">
				<img src="{"/payment-icon-all.svg"|cdnImageUrl}" width="50" height="50" alt="">
			</div>
			<div>
				<div class="offer-modal__radio-title">
                    {'Целиком, когда заказ выполнен'|t}
				</div>
				<p>
                    {'Оплата берется сразу и поступает продавцу после того, как вы проверите и одобрите заказ.'|t}
				</p>
			</div>
		</div>
		<div class="js-offer-radio offer-modal__radio-item" data-type="stages">
			<div class="offer-modal__radio-img offer-modal__radio-img_stages">
				<img src="{"/payment-icon-stages.svg"|cdnImageUrl}" width="60" height="40" alt="">
			</div>
			<div>
				<div class="offer-modal__radio-title">
                    {'По мере выполнения задач'|t}
				</div>
				<p>{'Оплата берется частями и поступает продавцу после того, как вы проверите и одобрите каждую задачу.'|t}</p>
				<p class="js-offer-hidden-text hidden mt5">{'Для этого разделите заказ на задачи.'|t}</p>
			</div>
		</div>
	</div>
	<div class="hidden">
		<div class="offer-modal__item-tooltip">
			<p class="fw700">{'Когда выбирать оплату целиком?'|t}</p>
			<p class="mb10">{'Если проект не сильно большой, и вы не хотите разбивать оплату на несколько задач. Фрилансер не получит оплату, пока работа не будет выполнена на 100%%.'|t}</p>

			<p class="fw700">{'Когда выбирать оплату по задачам?'|t}</p>
			<ol>
				<li>{'Если проект большой, и вам удобнее оплачивать его постепенно по мере готовности.'|t}</li>
				<li>{'Если вы хотите убедиться, что исполнитель потянет проект. В любой момент вы сможете остановить работу, оплатив только выполненные задачи.'|t}</li>
			</ol>

		</div>
	</div>
{/strip}