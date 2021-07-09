{strip}
<div class="js-inprogress-confirm inprogress-confirm modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content_radius-big">
			<div class="modal-body p30">
				<div class="d-flex flex-nowrap">
					<img class="inprogress-confirm__img" src="{"/well_done.png"|cdnImageUrl}" width="205" height="212" alt="Все готово!">
					<div class="inprogress-confirm__content ml20">
						<h1 class="mb20 f30 fw600">{'Работа завершена'|t}</h1>
                        <div class="mb17 f18 fw600">{'Подтверждаю, что:'|t}</div>
						<ul class="kwork-list kwork-list_type-2">
							<li class="kwork-list__item">
								{'Работа выполнена на 100%%'|t}
							</li>
							<li class="kwork-list__item">
								{'Все результаты работы переданы покупателю'|t}
							</li>
						</ul>
						<div class="d-flex justify-content-between flex-nowrap mt22 kwork-buttons">
							<div class="kwork-button kwork-button__lg kwork-button_theme_orange-bordered" data-dismiss="modal">
								{'Отменить'|t}
							</div>
							<div class="kwork-button kwork-button__lg kwork-button_theme_green-filled ml20 js-inprogress-confirm-submit">
								{'Подтвердить'|t}
							</div>
						</div>
					</div>
				</div>
			</div>
			<button type="button" class="modal-close modal-close_lg" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
</div>
{/strip}
