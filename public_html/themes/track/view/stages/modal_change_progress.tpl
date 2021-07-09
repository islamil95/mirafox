{Helper::registerFooterCssFile("/css/pages/phases.css"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/pages/phases.js"|cdnBaseUrl)}
<div class="modal fade stage-progress-modal js-stage-progress-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h1 class="modal-title">{'Прогресс по задаче'|t}</h1>
			</div>
			<div class="modal-body">
				<div class="mb15 breakwords">
					<div class="js-stage-progress-title fw700"></div>
					<div class="js-phases-item fw700 mt10">
						{'Прогресс'|t} <span class="js-progress-value"></span>%
						<div class="phases__progress js-phases-progress">
							{include file="track/view/progress_bar.tpl"}
						</div>
					</div>
				</div>
				<div class="mb15 breakwords">
					<i class="f10">{"Нажмите на линию прогресса, чтобы изменить процент"|t}</i>
				</div>
				<div class="f15 mt15">
					<textarea id="kwork_report_message"
							  data-required="true"
							  data-has-tags="true"
							  data-max="350"
							  name="message"
							  required="required"
							  class="js-field-input input"
							  placeholder="{"Комментарий по проделанной работе"|t}"
					></textarea>
					<div class="js-field-input-hint stage-progress-modal__message-hint">
						{'350 максимум'|t}
					</div>
				</div>
				<div class="js-stage-progress-error stage-progress-modal__error"></div>

			</div>
			<div class="modal-footer pt0">
				<div class="t-align-c m-wMax">
					<button class="js-stage-progress-send btn btn_color_green btn_size_l m-wMax">
						{'Отправить'|t}
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
