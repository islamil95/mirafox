<form action="{absolute_url route="track_text"}" name="textmessage" enctype="multipart/form-data" id="form_pass_work" class="track-form track-form-js send-message-js mt10 ajax-disabling {if ($isMissingData)}hidden{/if}" {if $order->isDone()}style="display:none"{/if} method="post" {if isAllowToUser($order->USERID) || isAllowToUser($order->worker_id) || $isKworkUser}onsubmit="TrackUtils.defSubmitPassWorkForm(); return false;"{/if}>
    {* Главная часть формы, в мобильной адаптации уходит вниз *}
	<div class="mf-general-part">
		<div class="safe-container" data-name="app-files-mobile">
			<div id="app-files-mobile">
				<new-messages-circle class="mobile"></new-messages-circle>
				<file-uploader ref="fileUploader" v-model="files" @change="onChange($event)" name="mobile" :link-uploader="desktopUploader" :nobutton="true" :makethumbs="true" :second-user-id="secondUserId"></file-uploader>
			</div>
		</div>
		{include file="message-form.tpl" mode="track" otherUserName=$otherUserName withCalc=true}
	</div>

	{* Блок для вывода ошибок *}
	<div class="js-message-error track-error hidden"></div>

	{if $admincsrftoken}
		<input type="hidden" name="admincsrftoken" value="{$admincsrftoken}" />
	{/if}
	{if isAllowToUser($order->USERID) || isAllowToUser($order->worker_id) || $isKworkUser}
		<input type="hidden" name="message_send_ajax" id="message_send_ajax" value="1" />
	{/if}
	{if $order->has_stages}
		<input type="hidden" name="stageIds" value="">
	{/if}
	{if $extrasPanelVisible}
		<div id="suggestExtras">
			{include './suggest_buttons_row.tpl'}
			<div class="suggest-blocks d-none">	
				{if $order->getBetterPackages()}
					{include './suggest_package_level.tpl'}
				{/if}
				{include './suggest_options.tpl'}
			</div>
		</div>
	{/if}
	<input type="hidden" class="js-send-message-action" name="action" id="fm_pass_action" value="text" />
	<input type="hidden" name="orderId" value="{$order->OID}" id="fm_pass_orderId"/>

	<div class="clear"></div>
	<div class="block-state-active">
		{if !empty($similarOrderData)}
			<div class="similar_files_data mt10 instruction-js"></div>
		{/if}
		<progress max="100" value="0" class="hidden" style="width:100%;"></progress>
		<div class="safe-container" data-name="app-files">
			<div id="app-files" class="track-app-files">
				<file-uploader ref="fileUploader" v-model="files" @change="onChange($event)" :limit-count="trackMaxFilesCount" :limit-size="trackMaxFilesSize" name="desktop" :nobutton="true" :makethumbs="true" :second-user-id="secondUserId" :drag-n-drop="desktopDragNDropEnable" name="desktop"></file-uploader>
			</div>
		</div>

		<div class="block-state-active_tooltip block-help-image-js" style="right:-280px; top: -60px;">
			{'На сайте есть ограничение на размер каждого файла: %sМб. Всё, что больше, можно заливать в любое облако и добавлять ссылку в сообщение.'|t:$config.files.maxSize}
		</div>
	</div>
	<div class="clear"></div>
	<div class="js-file-error track-error hidden">{'Файл превышает допустимый объем и не может быть отправлен. Рекомендуем использовать сервис для обмена файлами, например, <a href="https://disk.yandex.ru/" target="_blank">Яндекс.Диск</a>'|t}</div>

	<div class="pt10 message-edit-panel-controls hidden">
		<a href="javascript:void(0);" class="message-cancel-edit btn-track white-btn">Отмена правки</a>
		<a href="javascript:void(0);" class="message-confirm-edit btn-track green-btn pull-right" style="display: none">Отправить</a>
	</div>

	<div class="pt10 track-send-control">
		<input id="track-send-message-button" type="button" style="display:none;" class="message-submit-button btn-track green-btn js-uploader-button-disable" value="{'Отправить сообщение'|t}">

		{*
		показываем кнопки быстрого подтверждения заказа, если:
		- от покупателя требуется подтвердить заказ: 4 / OrderManager::STATUS_CHECK
		- интерфейсное уведомление о сдаче заказа в работу не последнее (есть 1+ сообщение после него): $lastTrackType != worker_inprogress_check
		*}
		{if $lastTrackType != 'worker_inprogress_check' && isAllowToUser($order->USERID) && $order->isCheck() && $order->data_provided}
			<div class="track-additional-buttons">
				<div class="already-checked-hint">Уже проверили заказ?</div>
				<div class="buttons-wrapper">
					<a href="javascript:;" class="green-btn green-btn--rubber js-uploader-button-disable js-track-form__popup-confirm-link" data-is-multiple="0" data-first-stage="0">
						{'Подтвердить выполнение'|t}<span>{' заказа'|t}</span>
					</a>
					<div class="or-text">или</div>
					<a href="javascript:;" class="js-track-reject js-uploader-button-disable orange-btn" data-is-multiple="0" data-first-stage="0">{'Отправить на доработку'|t}</a>
				</div>
			</div>
		{/if}

		{if ($type_form == "pass-work") }
			<div class="block-state-active">
				<input type="button" class="{if $order->has_stages}js-track-inprogress-check-button{else}js-track-pass-work-button{/if} track-pass-work-button btn-track orange-btn js-uploader-button-disable" style="display: none;" value="{'Сдать выполненную работу'|t}" />
				<div class="block-state-active_tooltip">
					{'Используйте этот функционал только для сдачи окончательной работы'|t}
				</div>
			</div>
		{/if}
	</div>	
	<div class="clear"></div>
	<div class="js-stopwords-error-wrapper"></div>
</form>