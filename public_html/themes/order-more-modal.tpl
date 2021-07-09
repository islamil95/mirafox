<div class="modal fade order-more-modal js-order-more-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h1 class="modal-title">{'Вы хотите'|t}</h1>
			</div>
			<div class="modal-body">
				<div class="order-more-modal__radio">
					<input type="radio"
							name="orderMore"
							id="order-more-modal-current"
							value=""
							class="js-order-more-radio js-order-more-radio-current styled-radio"
							data-type="current">
					<label for="order-more-modal-current">
						{'Продолжить работу в этом заказе'|t}
						<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover" data-tooltip-text="{'Подходит для доработок по текущему заказу, а также для длительных и повторяющихся работ.'|t}">?</span>
					</label>
				</div>
				<div class="order-more-modal__radio">
					<input type="radio"
							name="orderMore"
							id="order-more-modal-new"
							value=""
							class="js-order-more-radio js-order-more-radio-new styled-radio"
							data-type="new">
					<label for="order-more-modal-new">
						{'Сделать повторный заказ'|t}
						<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover" data-tooltip-text="{'Подходит для создания похожего нового заказа с тем же исполнителем.'|t}">?</span>
					</label>
				</div>

				<div class="popup__buttons">
					<button type="button" class="white-btn white-btn_no-hover btn-hover-red popup__button pull-left" data-dismiss="modal">{'Отмена'|t}</button>
					<button type="button" class="js-order-more-submit green-btn disabled popup__button pull-right" disabled>{'Подтвердить'|t}</button>
				</div>
			</div>
		</div>
	</div>
</div>
