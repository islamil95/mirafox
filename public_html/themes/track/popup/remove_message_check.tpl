{strip}
	<div class="modal fade js-remove-message-check-modal" tabindex="-1" role="dialog" data-keyboard="false" style="display: none;">		
		<div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title">{'Подтверждение удаления'|t}</h1>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action = "/track/action/remove" class="js-remove-message-form" method="post">
						<input type="hidden" name="itemId" class="js-form-field-id" value="" />
						<input type="hidden" name="orderId" class="js-form-field-id" value="" />
						<p class="f15 pb10 ml10">{'Вы действительно хотите удалить сообщение?'|t}</p>
						<div class="popup__buttons">						
							<button class="popup__button green-btn popup-close-js" onclick="return false;" data-dismiss="modal">Не удалять</button>	
							<button class="popup__button white-btn pull-right">Удалить</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
{/strip}