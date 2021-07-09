<div class="mf-message-row">
	{if $withCalc}<div class="mf-length-calc"><span>0</span> / 4000</div>{/if}
	<div class="js-message-quote-wrap mf-message-quote"></div>
	<div class="mf-message-input">
		{if $config.track.isFocusGroupMember}
			{if $mode == 'track'}
				<div class="mf-plus safe-container">
					<div id="app-emoji-btn" class="mf-plus__app-emoji-btn js-mf-plus__app-emoji-btn">
						<emoji-btn parent-class="form" @change="onChange"></emoji-btn>
					</div>
				</div>
			{/if}
		{/if}
        {if $isChat}
            {* Кнопка инд. предложения / кворка в чате *}
			<div class="mf-plus chat__button_offer {if $actor->type == UserManager::TYPE_PAYER}js-individual-message__popup-link{/if}">
				<div class="chat__button_offer-icon mf-plus__icon">
					<i class="fl-rouble"></i>
				</div>
			</div>
        {/if}
		<div class="mf-message{if $config.track.isFocusGroupMember && $mode == 'track'} mf-message_track{/if}">
			<div id="track-message-body" class="message-body-sizer {if $config.track.isFocusGroupMember && $mode == 'track'}hidden{/if}"></div>
			{if $mode == 'track'}
				<div class="track-page__wrap-message_body">
					<textarea id="message_body1" name="message" class="control-en js-alt-send styled-input db wMax f14 mh145 mt15 noBottomLeftRadius noBottomRightRadius js-stopwords-check js-stopwords-check-warning js-message-input js-message-input-focus {if $config.track.isFocusGroupMember}js-message-input_hidden{/if}" autocomplete="off" placeholder="" maxlength="4000">{if $draftData}{$draftData.message}{/if}</textarea>
				</div>
			{else}
				<textarea maxlength="4000" class="js-stopwords-check no-min-len styled-input f14 db wMax mh145 js-message-input js-message-input-focus message_body{if $controlEnLang} control-en-alt{/if}" cols="75" rows="3" id="message_body" name="message_body" style="overflow:auto; margin-bottom: -1px;" placeholder="{if $controlEnLang}{'Внимание! Пользователь общается только на английском языке.'|t}{/if}" autocomplete="off">{$currentMessageBody}</textarea>
			{/if}
		</div>
	</div>
	{include file="submit-arrow.tpl" buttonId="new-desktop-submit" hasTooltip="true"}
</div>
<div id="info-type-message-bottom" class="info-type-message"><b>{$otherUserName}</b> {'печатает'|t}</div>
