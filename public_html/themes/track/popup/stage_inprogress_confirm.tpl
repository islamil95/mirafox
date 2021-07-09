{if $order->has_stages}
<div class="{$jsClass} stage-inprogress-confirm modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title">
					<span class="js-stage-confirm-text-plural hidden">{'Подтверждение выполнения задач'|t}</span>
					<span class="js-stage-confirm-text-singular hidden">{'Подтверждение выполнения задачи'|t}</span>
					<span class="js-stage-confirm-text-reject hidden">{'Отправить на доработку'|t}</span>
				</h1>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				{* action проставляется в js *}
				<form method="post"
					  class="js-stage-inprogress-confirm-form"
					  data-action-approve="{absolute_url route="approve_stages" params=["orderId"=> $order->OID]}"
					  data-action-reject="{absolute_url route="reject_stages" params=["orderId"=> $order->OID]}"
				>
					<input type="hidden" name="orderId" value="{$order->OID}">
					<input type="hidden" name="stageIds" value="">
					<input type="hidden" name="message" value="">
					<p class="mb5">
						<span class="js-stage-confirm-text-reject hidden">{'Выберите задачу или задачи, которые нужно вернуть продавцу в работу. В отдельном сообщении опишите ему, что нужно доработать или исправить.'|t}</span>
					</p>
					<div class="js-stage-confirm-text-plural js-stage-confirm-text-singular hidden popup-msg">
						<div class="icon ico-i-small"></div>
						<div class="popup-text">
							<span class="js-stage-confirm-text-plural hidden">{'Подтверждаю, что задачи выполнены, претензий к продавцу не имею.'|t}</span>
							<span class="js-stage-confirm-text-singular hidden">{'Подтверждаю, что задача выполнена, претензий к продавцу не имею.'|t}</span>
						</div>
					</div>
					<div class="track-stages">
						{$checkStages = $order->getCheckStages()}
						{if $checkStages->count() > 0}
							<div class="js-stage-confirm-text-approve-bottom hidden stage-inprogress-confirm__group-title">{'На проверке'|t}</div>
							{foreach $checkStages as $stage}

								{include file="track/stages/stage.tpl" stage=$stage hideButtonsStage=true}

							{/foreach}
						{/if}

						{$reservedNotCheckStages = $order->getReservedNotCheckStages()}
						{if $reservedNotCheckStages->count() > 0}
							<div class="js-stage-confirm-text-reject-hide">
							<div class="js-stage-confirm-text-approve-bottom hidden stage-inprogress-confirm__group-title">{'Активные задачи'|tn:$reservedNotCheckStages->count()}</div>
							{foreach $reservedNotCheckStages as $stage}

								{include file="track/stages/stage.tpl" stage=$stage hideButtonsStage=true}

							{/foreach}
							</div>
						{/if}
					</div>
					<div class="js-stage-inprogress-confirm-total stage-inprogress-confirm__total hidden">
						<span>{'Итого'|t}:</span>
						<span class="js-stage-inprogress-confirm-total-value"></span>
					</div>
					{if $reservedNotCheckStages->count() > 0}
						<p class="js-stage-confirm-text-not-check hidden mb5">
							{'Обратите внимание! Продавец пока не запрашивал оплату. Но вы можете перевести ее, если довольны результатом и считаете, что работа по задаче выполнена.'|t}
						</p>
					{/if}
					<p>
						<span class="js-stage-confirm-text-plural hidden">{"После подтверждения оплата переводится продавцу. Обратиться в арбитраж по завершенным задачам нельзя."|t}</span>
						<span class="js-stage-confirm-text-singular hidden">{'После подтверждения оплата переводится продавцу. Обратиться в арбитраж по завершенной задаче нельзя.'|t}</span>
					</p>
					<div class="popup__buttons">
						<button type="button" class="popup__button white-btn" data-dismiss="modal">{"Отмена"|t}</button>
						<button class="js-stage-inprogress-reject-submit hidden popup__button orange-btn pull-right">{'Отправить на доработку'|t}</button>
						<button class="js-stage-inprogress-approve-submit hidden popup__button green-btn pull-right">
							{"Оплатить"|t}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
{/if}
