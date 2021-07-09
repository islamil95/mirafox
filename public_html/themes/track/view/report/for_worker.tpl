{strip}
	<div id="track-id-{$track->MID}"
		 class="{if $config.track.isFocusGroupMember}track--item {/if} tr-track step-block-order_item checkWork-js{if $isUnread} unread{/if}{if !$track->MID} new-report{/if} report-message {if $track->report && $track->report->canEdit()} can-edit{/if} {$direction}"
		 data-track-id="{$track->MID}">
        {if $config.track.isFocusGroupMember}
			<div class="track--item__sidebar">
				<div class="track--item__sidebar-image {$color}">
					<svg width="25" height="25" viewBox="0 0 25 25">
						<use xlink:href="#{$icon}"></use>
					</svg>
				</div>
			</div>
        {else}
			<div class="f14 color-gray mt3 t-align-r">{$date|date}</div>
        {/if}
        {if $config.track.isFocusGroupMember}
		<div class="track--item__main">
            {else}
			<div class="t-align-c">
                {/if}
                {if $config.track.isFocusGroupMember}
					<div class="track--item__title">
						<h3 class="f15 bold">
                            {$title}
						</h3>
						<div class="track--item__date  color-gray">{$date|date:"H:i"}</div>

					</div>
                {else}
					<i class="{$icon}"></i>
					<h3 class="track-{$color} pt10 fw600">
                        {$title}
					</h3>
                {/if}
				{if $config.track.isFocusGroupMember}
				<div class="track--item__content">
                    {/if}
					<div class="f15  {if !$config.track.isFocusGroupMember} mt15 {/if} mb15 breakwords m-ml0">
						<div class="mb15 breakwords m-ml0">
                            {$phases_count = $phases|count}
                            {if $phases_count}
                                {"Если работа по задачам занимает больше 3 дней, то каждые 3 дня желательно публиковать краткий отчет о проделанной работе. Отчеты помогают покупателю понимать, на какой стадии находится работа."|tn:$phases_count}
                            {else}
                                {"Если выполнение заказа занимает более 3 дней, то каждые 3 дня желательно публиковать краткий отчет о своей работе. Отчеты помогают покупателю понимать, на какой стадии находится заказ. Своевременная публикация отчетов увеличивает шанс получить положительный отзыв."|t}
                            {/if}
						</div>
					</div>
					<div class="mb15 breakwords m-ml0">
                        {if $phases}
                            {include file="track/view/report/phases_block.tpl"}
                        {else}
							<div class="progress-text mt10">
								<b>{"Прогресс"|t} <span
											class="js-progress-value">{$track->report->is_executed_on}</span>%&nbsp;</b>
							</div>
							<div class="js-order-progress order-progress-wrapper dib">
                                {include file="track/view/progress_bar.tpl" trackMID=$track->MID progress=$track->report->is_executed_on loop=($track->order->isInWork()) ? 100 : 90}
							</div>
                        {/if}
					</div>
					<div class="{if $track->MID}hide {/if}mb15 breakwords m-ml0">
						<i class="f10">{"Нажмите на линию прогресса, чтобы изменить процент"|t}</i>
					</div>
                    {if !$config.track.isFocusGroupMember}
				</div>
                {/if}
				<div class="exist-form-wrapper">
					<form action="{absolute_url route="track_worker_report_new"}" enctype="multipart/form-data"
                          {if isAllowToUser($track->order->USERID) || isAllowToUser($track->order->worker_id) || $isKworkUser}onsubmit="preAjaxSendTrackMessage($(this)); return false;{/if}"
						  method="POST">
						<input type="hidden" name="track_id" value="{$track->MID}">
						<input type="hidden" name="orderId" value="{$track->order->OID}">
						<input type="hidden" name="isExecutedOn" value="{$track->report->is_executed_on}">
						<input type="hidden" name="action" value="{\Track\Type::WORKER_REPORT_NEW}">
						<input type="hidden" name="stages"
							   value="{if $track->report->phases}{$phases|@json_encode|htmlspecialchars:ENT_QUOTES}{/if}">
						<div class="{if !$config.track.isFocusGroupMember} t-align-c {/if}">
							<div class="f15 mt15 mb15 breakwords m-ml0">
								<div class="{if $track->MID}hide {/if} hide-edit-action">
							<textarea id="kwork_report_message"
									  data-required="true"
									  data-has-tags="true"
									  data-max="350"
									  name="message"
									  required="required"
									  class="js-field-input  js-field-input-description kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_description input"
									  placeholder="{"Комментарий по проделанной работе"|t}"
							>{$track->message}</textarea>
									<div class="js-field-input-hint kwork-save-step__field-hint t-align-r">
                                        {'350 максимум'|t}
									</div>
								</div>
								<div class="report-message-block {if !$track->MID}hide {/if}mb15 breakwords m-ml0 hide-edit-action">
                                    {$track->message}
								</div>
								<div class="js-message-error color-red f12"></div>
								<div class="mt20">
									<button type="submit"
											class="{if $track->MID}hide {/if} hide-edit-action green-btn">
                                        {"Отправить отчет"|t}
									</button>
                                    {if $track->report && $track->report->canEdit() && $phasesCanEdit}
										<div class="{if !$track->MID}hide {/if}edit-link ta-right hide-edit-action">
											<a class="link_local mb5" href="javascript:void(0);"
											   onclick="editReport('{$track->MID}')">
                                                {"Изменить отчет"|t}
											</a><br>
											<i>
										<span class="color-gray f9">
											{"Доступно в течение 10 минут после отправки отчета."|t}
										</span>
											</i>
										</div>
                                    {/if}
								</div>
							</div>
                            {block name="actions"}{/block}
						</div>
					</form>
				</div>
				<div class="append-form-wrapper">
				</div>
                {if $config.track.isFocusGroupMember}
			</div>
		</div>
        {/if}
	</div>
    {Helper::registerFooterJsFile("/js/calc_hint.js"|cdnBaseUrl)}
{/strip}