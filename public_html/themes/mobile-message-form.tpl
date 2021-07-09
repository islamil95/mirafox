<div id="block_message_new" class="block-message{if $ordersBetweenUsers && $ordersBetweenUsers|count > 0} hidden{/if}">
	<div id="app-mobile">
		<new-messages-circle class="mobile"></new-messages-circle>
	</div>
	<div class="page-conversation__info-type-message info-type-message">{$u.username} {'печатает'|t}</div>
	<div class="message-form-control__error"></div>
	<form action="{$baseurl}/sendmessage" class="nonajaxy form-message" id="mobile_message_form" enctype="multipart/form-data" method="post">

		<input type="hidden" name="submg" value="1" />
		<input type="hidden" name="msgto" value="{$u.USERID}" />
		<input type="hidden" name="orderId" value="" />

		<div class="js-message-quote-wrap"></div>
		<div class="block-message--top">
			{* files *}
			<div class="box-file-list clearfix attached-images-area">
				<div id="app-files-mobile">
					<file-uploader ref="fileUploader" v-model="files" @change="onChange($event)" name="mobile" :link-uploader="desktopUploader" :nobutton="true" :makethumbs="true" :second-user-id="secondUserId"></file-uploader>
				</div>
				<label class="file-uploader__add"></label>
			</div>
		</div>

		<div class="block-message--bottom pl40 d-flex justify-content-between">
			{* custom/individual kwork button *}
			{if $mode != "track" && App::config("order.propose_inbox_order") && $actor->type == UserManager::TYPE_WORKER && !$isSupportDialog && ($kworks || $actor->level > UserLevelManager::LEVEL_NOVICE)}
				<div class="box-kwork mr10">
					<button type="button" class="box-kwork-toggle {if $actor->level == UserLevelManager::LEVEL_NOVICE}box-kwork-add-kwork{/if}">+</button>
				</div>
			{/if}

			{* input text *}
			<div class="box-input ovf-h mr10">
				<div class="message_body__wrapper">
					<div class="track-page__wrap-message_body">
						<textarea name="" id="mobile_message" cols="30" rows="1" class="js-message-input-focus message_body mobile_message_body{if $controlEnLang} control-en-alt{/if} {if $config.track.isFocusGroupMember}track-page__message_body_blank{/if}" placeholder="{if $controlEnLang}{'Введите сообщение на англ. языке'|t}{else}{'Введите сообщение'|t}{/if}"></textarea>
					</div>
				</div>
			</div>

			{* submit arrow *}
			{include file="./submit-arrow.tpl" oldMode=true}
		</div>
		<div class="track-page__wrap-message_body__emoji-btn">
			<div id="app-emoji-btn-mobile" class="js-track-page__wrap-message_body__emoji-btn">
				<emoji-btn parent-class="form" @change="onChange"></emoji-btn>
			</div>
		</div>
	</form>
</div>
