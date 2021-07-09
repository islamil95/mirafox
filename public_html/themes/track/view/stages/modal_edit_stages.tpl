<div class="modal fade js-track-stage-edit-modal track-stage-edit" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div>
					<h1 class="js-track-stage-edit-title modal-title"></h1>
					<div class="f13 d-flex align-items-center mt3"><div class="mr10"><div class="block-circle block-circle-24 block-circle_orange bold fs16 lh24 white">!</div></div>Если вы работаете над большим проектом или повторяющимися задачами, добавьте в заказ новые задачи. Каждая задача включает в себя задание, которое выполняется и оплачивается отдельно.</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="js-offer-stages" data-order-id="">
					{include file="wants/common/stages.tpl" actorType=UserManager::TYPE_PAYER page='track'}
				</div>
			</div>
			<div class="modal-footer pt0">
				<form class="js-edit-stages-form wMax">
					<div class="popup__buttons">
						<button type="button"
								class="popup__button white-btn pull-left"
								data-dismiss="modal"
						>
                            {"Отменить"|t}
						</button>
						<button type="submit"
								class="js-track-stage-edit-confirm popup__button green-btn pull-right disabled"
								disabled
						>
							{'Сохранить'|t}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
