{include file="header.tpl"}

{strip}
	{Helper::printCssFile("/css/dist/conversations.css"|cdnBaseUrl)}
    {Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}

	<script>
        {literal}
		var isChat = true;
		var chatScrolled = 0;
		var chatOnlineUsers = [];
		var offer = {};
		var offerId, offerData, offerLang;
		var controlEnLang = 0, requestKwork = 0, requestProject = 0;
		var turnover, commissionRanges, receiverId, showInboxAllowModal, minPrices, stageMinPrices;
		var minBudget, maxBudget, isPageNeedSmsVerification;
		var conversationActorUserName = "{/literal}{$actor->username}{literal}";
		var conversationActorUserAvatar = "{/literal}{"/medium/{$actor->profilepicture}"|cdnMembersProfilePicUrl}{literal}";
		var conversationUserId = null;
		var conversationImageUrl = "{/literal}{App::config("imageurl")}{literal}";
		var actorIsPayer = {/literal}{if $actorIsPayer}true{else}false{/if}{literal};
		var isOrderButtonEnabled = {/literal}{if App::config("order.propose_inbox_order") && $actor->type == UserManager::TYPE_WORKER}true{else}false{/if}{literal};
        {/literal}
	</script>
	{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/pages/support/support_rating.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/individual-message.js"|cdnBaseUrl)}

    {include file="fox_error7.tpl"}
	<script>
		{* Параметры пользователя *}
        {to_js name="actorId" var=$actorId}
        {to_js name="actorAvatar" var=$actorAvatar}
        {to_js name="actorLogin" var=$actorLogin}
        {to_js name="actorTimezone" var=$actorTimezone}
        {to_js name="actorIsVirtual" var=$actorIsVirtual}
        {to_js name="actorLevel" var=$actorLevel}
        {to_js name="userBlocked" var=$userBlocked}
        {to_js name="fileRetentionPeriodNoticeCount" var=$fileRetentionPeriodNoticeCount}
				{to_js name="isOrderStageTester" var=$isStageTester|intval}
        {to_js name="isActorAvailableAtWeekends" var=$isAvailableAtWeekends}
        {to_js name="isWeekends" var=$isWeekends}

		{* Общие параметры *}
        {to_js name="membersProfilePicUrl" var=$membersProfilePicUrl}
        {to_js name="conversationMessageTypes" var=$conversationMessageTypes}
        {to_js name="langIds" var=$langIds}
        {to_js name="currencyIds" var=$currencyIds}
        {to_js name="maxFileCount" var=$maxFileCount}
        {to_js name="maxKworkCount" var=$maxKworkCount}
        {to_js name="multiKworkRate" var=$multiKworkRate}
        {to_js name="offerMaxStages" var=$offerMaxStages}
        {to_js name="stagesPriceThreshold" var=$stagesPriceThreshold}
        {to_js name="supportUserId" var=$supportUserId}
        {to_js name="individualKworkUrl" var=$individualKworkUrl}
        {to_js name="proposeInboxOrder" var=$proposeInboxOrder}

		{* Параметры чата *}
        {to_js name="chatList" var=$dialogs.rows encodeUnicode=true}
        {to_js name="chatListItemsPerPage" var=$chatListItemsPerPage}
        {to_js name="fileStatusActive" var=$fileStatusActive}
        {to_js name="ruDomain" var=$ruDomain}
        {to_js name="enDomain" var=$enDomain}
        {to_js name="isGuestDialog" var=null}
        {to_js name="conversationPage" var=true}
        {assign var="isConversation" value=true}

		{* Параметры, которые задаются при переходе по прямым ссылкам на диалоги *}
        {to_js name="draftData" var=""}
        {to_js name="defaultMessage" var=$defaultMessage}
		{if $conversationUserId}{literal}
			conversationUserId = {/literal}{$conversationUserId}{literal};
			{/literal}{to_js name="conversationUserId" var=$conversationUserId}
        {/if}
        {if $kworkId}{literal}
			requestKwork = {/literal}{$kworkId}{literal};
        {/literal}{/if}
        {if $offerId}{to_js name="offerId" var=$offerId}{/if}
        {if $offerData}{to_js name="offerData" var=$offerData}{/if}
		{to_js name="maxOptionsSum" var=(float)App::config(\Enum\Config::MAX_TOTAL_EXTRAS_PRICE)}
	</script>

	<div id="currentPage" class="hidden"></div>
	<div class="chat-wrapper">
		<div class="chat{if $conversationUserId} chat_loaded-by-link{/if}">
			{* Список диалогов *}
			<div id="app-chat-list" class="chat__aside">
				<chat-list ref="chatList"></chat-list>
			</div>
			<div class="chat__conversation-wrapper fixed-offer">
				<div id="app">
					{* Просмотрщик изображений *}
					<image-viewer ref="imageViewer"></image-viewer>
                    {* Шапка выбранного диалога *}
					<chat-conversation-header ref="chatConversationHeader"></chat-conversation-header>
                    {* Сообщения выбранного диалога *}
					<chat-messages-list  user-csrf="{${UserManager::SESSION_CSRF_KEY}}"  ref="chatMessagesList"></chat-messages-list>
				</div>
                {* Форма отправки сообщения / индивидуального предложения выбранного ранного диалога *}
				{if UserManager::isActorNotBlocked()}
					<div class="kwork-conversation__footer">
						<a id="bottomFormLink"></a>
						<div class="modal-backdrop fade in hidden"></div>
						{include file="conversation_bit.tpl" isChat=true}
                        {include file="wants/payer/offers/offer_individual_message.tpl"}
					</div>
				{/if}
			</div>
		</div>
	</div>
	<div id="app-files-mobile" class="d-none-important"></div>
	<div class="chat-warning-popup modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="display: none;">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content modal-content_radius-big">
				<div class="modal-body">
					<div class="tooltip-chat">
						<div class="tooltip-chat__title">
							{'Быстрый ответ'|t}
						</div>
						<div class="mt5">
							{'Покупатели надеются на скорейший ответ. Чем <strong>быстрее вы отвечаете</strong>, тем <strong>выше шанс продать</strong> услугу. Если вы несколько раз не отвечаете на новые обращения покупателей, то кворки блокируются на несколько дней.'|t}
						</div>
						<div class="mt8">
							{'Внимание! <strong>Требование обязательного ответа</strong> действует только на <strong>первое личное сообщение</strong> от нового покупателя или того, с кем вы не преписывались более 20 дней.'|t}
						</div>
					</div>
				</div>
				<button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>
	</div>

    {include file="wants/payer/offers/stage-modal.tpl" isChat=true}

	{Helper::printJsFile("/js/dist/conversations.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/commission.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/sendinbox.js"|cdnBaseUrl)}
	<script>
		{if $isPageNeedSmsVerification}{literal}
			jQuery(function () {
				phoneVerifiedOpenModal();
				jQuery('.btn-send-message').click(function (e) {
					e.preventDefault();
					phoneVerifiedOpenModal();
				});
			});
		{/literal}{/if}
	</script>

    {Helper::registerFooterJsFile("/js/libs/withinviewport.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/libs/jquery.withinviewport.min.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/pull-is-type-message.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/conversations.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/caret.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/dist/conversations-bit.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/conversation_bit.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/imagesloaded.pkgd.min.js"|cdnBaseUrl)}

{/strip}
{include file="footer.tpl"}
