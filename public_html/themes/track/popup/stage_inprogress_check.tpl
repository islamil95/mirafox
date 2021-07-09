{if $order->has_stages}
	<div class="modal fade stage-inprogress-check js-stage-inprogress-check-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="js-stage-inprogress-check-close close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h1 class="modal-title">{'Какую задачу вы хотите сдать на проверку?'|t}</h1>
				</div>
				<div class="modal-body js-tooltip-update">
					{foreach  $order->getReservedNotCheckStages() as $stage}
						{include file="track/stages/stage.tpl" checkboxInprogressCheck=true}
					{/foreach}
				</div>
				<div class="modal-footer">
					<button type="button" class="js-stage-inprogress-check-close white-btn white-btn_no-hover btn-hover-red popup__button pull-left">{'Отмена'|t}</button>
					<button type="button" class="js-stage-inprogress-check-submit green-btn popup__button pull-right disabled" disabled="disabled">{'Готово'|t}</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade stage-inprogress-send js-stage-inprogress-send-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h1 class="modal-title">{'Вы подтверждаете, что:'|t}</h1>
				</div>
				<div class="modal-body">
					<p class="js-change-text">1. {'Работа над задачами соответствует заданию покупателя и выполнена в полном объеме.'|t}</p>
					<p>2. {'Вы собираетесь отправить работу на проверку покупателю. Покупатель может вернуть задачу на доработку, если выявит какие-то проблемы.'|t}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="white-btn white-btn_no-hover btn-hover-red popup__button pull-left" data-dismiss="modal">{'Отмена'|t}</button>
					<button type="button" class="js-stage-inprogress-send-submit green-btn popup__button pull-right">{'Подтвердить'|t}</button>
				</div>
			</div>
		</div>
	</div>
{/if}
