{strip}
	<div class="modal modal-kwork-change-name fade" tabindex="-1" role="dialog" style="display: none;">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header mb15 pb0">
					<div class="fw600 f24 pr30">{"Изменить название"|t}</div>
					<button type="button" class="modal-close modal-close_lg" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="js-popup-individual-message__container">
						<div class="popup-individual-message">
							<input type="hidden" class="kwork-id">
							<div>
								<div class="mt5">
									{'Новое название услуги'|t}
								</div>
								<div class="mt5">
									<textarea class="input input_size_s wMax kwork-name" maxlength="80"></textarea>
								</div>
							</div>
							<div class="mt10 form-entry-error color-red ta-center hidden"></div>
							<div class="popup__buttons individual-message-buttons">
								<button type="button" class="white-part-btn white-btn_hover-white btn--big18 p0 hoverMe h50 w100p js-kwork-change-name-cancel">{"Отменить"|t}</button>
								<button type="submit" class="green-btn w100p btn--big18 p0 hoverMe btn-disable-toggle pull-reset js-uploader-button-disable save-new-name">{"Сохранить"|t}</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}