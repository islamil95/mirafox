{strip}
	<div class="modal modal-individual-message modal-radius unshowed fade" tabindex="-1" role="dialog" id="chat-individual-message-modal" style="display: none;">
		<div class="modal-dialog modal-dialog modal-dialog-centered w600" role="document">
			<div class="modal-content">
				<div class="modal-header mb15 pb0">
                    <div class="fw600 f24 pr30">{'Запрос индивидуального кворка'|t}</div>
					<button type="button" class="modal-close modal-close_lg" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="js-popup-individual-message__container">
						<div class="popup-individual-message">
							<form class="popup-individual-message-form js-individual-message-form ajax-disabling" enctype="multipart/form-data" method="post">
								<input type="hidden" name="msgto" class="js-msgto">
								<input type="hidden" name="message_type" class="js-message-type">
								<input type="hidden" name="submg" value="1">
								<div>
									<div class="mt5">
										{'Вы можете отправить индивидуальный заказ продавцу. Опишите, что именно вам нужно, укажите желаемый бюджет и срок выполнения'|t}.
									</div>
									<div class="textarea mt20 position-r">
										<textarea id="message_body_offer" name="message_body" class="js-required textarea-styled wMax f14 mt8 js-stopwords-check" title="{'Ваше сообщение'|t}"></textarea>
									</div>
									<div class="js-orders-between-users-block orders-between-user-block mt10 hidden">
										<div class="fs18 fw600">{"Заказы между вами"|t}</div>
										<div class="js-chat-orders-list"></div>
									</div>
								</div>
								<div class="mt15">
									<div class="label fs18 fw600">
										{'Бюджет'|t}
									</div>
									<div class="budget mt15 nowrap pull-left">
										<input type="text" id="budget" name="budget" class="styled-input fs14 w100" placeholder="">
										<span class="fs18 fw600 ml10 rouble">Р</span>
									</div>
									<div class="days mt15 nowrap pull-right">
										<div class="m-wMax m-pull-reset chosen14">
											{include file="html_select_duration.tpl" id="request-kwork-duration" class="input input_size_s styled-select" name="kwork_duration"
												durations=$durations.available}
										</div>
									</div>
									<div class="clear"></div>
								</div>
								<div class="mt15">
									<div class="dialog__files"><div id="load-files-conversations" class="add-files"></div></div>
								</div>
								<div class="mt10 js-individual-message-error color-red ta-center hidden"></div>
								<div class="popup__buttons mt20 d-flex justify-content-center">
									<button type="submit" class="green-btn w50p btn--big18 p0 hoverMe btn-disable-toggle pull-reset js-uploader-button-disable">{'Отправить'|t}</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}