{strip}
	<div class="popup-user" style="display:none;" id="js-popup-individual-message__container">
	<div class="overlay overlay-disabled"></div>
	<div class="popup_centered">
	<div class="popup_content popup-individual-message">
	    <div class="popup_content_inner">
		<div class="pull-right popup-close cur" style="width:30px; height:20px; text-align:center; padding:10px 10px 10px;">X</div>
        <form class="popup-individual-message-form js-individual-message-form ajax-disabling" enctype="multipart/form-data" method="post">
			<a href="javascript:;" class="kwork-icon icon-close popup__close_theme_mobile popup-close-js"></a>
            <h1 class="pr20 popup__title">{'Отправить сообщение'|t}{if $allowCustomRequest} ({'заказ'|t}){/if}</h1>
            <input type="hidden" name="msgto" class="js-msgto" value="{$userProfile->USERID}">
            <input type="hidden" name="message_type" value="individual_message">
            <input type="hidden" name="submg" value="1">
			<div class="js-chat-redirect-url d-none-important"></div>
            <div class="border-top">
                <div class="label fs18 fw600">
                    <span class="icon-edit m-visible"></span>
                    {'Ваше сообщение'|t}
                </div>
                <div class="textarea mt20 position-r">
                    <textarea id="message_body" name="message_body" class="{if $userProfile->lang != getUserLang() && getUserLang() == \Translations::DEFAULT_LANG}control-en {/if}js-required textarea-styled wMax f14 mt8 js-stopwords-check"
			title="{'Ваше сообщение'|t}" placeholder="{if $controlEnLang}{'Внимание! Пользователь общается только на английском языке.'|t}{/if}">
		    </textarea>
                </div>
				{if $ordersBetweenUsers}
					<div class="js-orders-between-users-block orders-between-user-block mt10">
						<div class="fs18 fw600">{"Заказы между вами"|t}</div>
						{foreach from=$ordersBetweenUsers item=order}
							<div class="break-word mt5">
								<input type="radio"
									   name="orderId"
									   id="orderId{$order["id"]}"
									   value="{$order["id"]}"
									   class="js-send-order-message-radio styled-radio">
								<label for="orderId{$order["id"]}">
									{$order["kwork_title"]}
								</label>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>
			{if $isCustomRequest}
				<div class="mt15 fs15">
					{'Вы можете отправить индивидуальный заказ продавцу. Опишите, что именно вам нужно, укажите желаемый бюджет и срок выполнения'|t}
				</div>
				<div class="mt15">
					<div class="label fs18 fw600">
						{'Бюджет'|t} <span class="fs15 fw400">({'указывается по желанию'|t})</span>
					</div>
					<div class="budget mt15 nowrap pull-left">
						{if $offerLang == Translations::EN_LANG}
							<span class="fs18 fw600 mr5">$</span>
						{/if}
						<input type="text" id="budget" name="budget" class="styled-input fs14 w100" placeholder="{$customMinPrice|zero} - {$customMaxPrice|zero}" />
						{if $offerLang == Translations::DEFAULT_LANG}
							<span class="fs18 fw600 ml10 rouble">Р</span>
						{/if}
					</div>
					<div class="days mt15 nowrap pull-right">
						<div class="m-wMax m-pull-reset chosen14">
							{include file="html_select_duration.tpl" id="request-kwork-duration" class="input input_size_s styled-select" name="kwork_duration"
								durations=$durations.available}
						</div>
					</div>
					<div class="clear"></div>
				</div>
			{/if}
			<div class="mt15">
				<div class="dialog__files"><div id="load-files-conversations" class="add-files"></div></div>
			</div>
			<div class="mt10 js-individual-message-error color-red ta-center hidden"></div>
			<div class="popup__buttons mt20">
				<button class="btn--big green-btn w100p btn-disable-toggle js-uploader-button-disable">{'Отправить'|t}</button>
			</div>
			{if $hasConversation}
				<div class="mt10 t-align-c">
					<a href="{getConversationUrl($userProfile->username)}" class="link">{'Перейти к диалогу с пользователем'|t}</a>
				</div>
			{/if}
		</form>
	</div>
	</div>
	</div>
	</div>
	{literal}
		<script>
			var minBudget = {/literal}{$customMinPrice}{literal};
			var maxBudget = {/literal}{$customMaxPrice}{literal};
			var offerLang = {/literal}"{$offerLang}"{literal};
			var showInboxAllowModal = {/literal}{$showInboxAllowModal|boolval|@json_encode}{literal};
			var receiverId = {/literal}{$userProfile->USERID|intval}{literal};
			var isPageNeedSmsVerification = {/literal}{$isPageNeedSmsVerification|intval}{literal};
		</script>
	{/literal}
	{Helper::printJsFile("/js/popupAllowConversations.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/individual-message.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
{/strip}