<div class="modal fade offer-modal js-offer-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="modal-close modal-close_retina" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h1 class="modal-title">{'Настройка заказа'|t}</h1>
			</div>
			<div class="modal-body">
				<div class="offer-modal__item">
					<div class="offer-modal__item-label">{'Стоимость'|t}</div>
					<div class="js-offer-price offer-modal__item-price"></div>
				</div>

				<div class="offer-modal__item">
					{include file="wants/payer/offers/stage-modal-pay.tpl"}
				</div>

				<div class="js-offer-stages" data-order-id="" style="display: none;">
					{include file="wants/common/stages.tpl" actorType=UserManager::TYPE_PAYER}
				</div>
			</div>
			<div class="modal-footer pt0">
				<div class="t-align-c m-wMax">
					<button class="js-offer-continue btn btn_color_green btn_size_l m-wMax">
						{'Далее'|t}
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
