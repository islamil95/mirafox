{include file="header.tpl"}
{strip}
	{Helper::registerFooterJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/copyright.js"|cdnBaseUrl)}
	{include file="fox_error7.tpl"}
	<div class="static-page__block">
		<div class="white-bg-block centerwrap ta-justify">
			<div class="pt20 m-visible"></div>
			{include file="copyright/about.tpl"}
			{include file="copyright/whattodo.tpl"}
			<div id="foxForm" class="ml0-750 mw50p m-wMax">
				<form action="{absolute_url route="copyright_form_handler"}" method="POST" name="copyright" class="send_contact_message-js">
					<input type="hidden" name="action" id="send" value="send">
					<div class="form-entry relative input_field-js input_name-js">
						<label class="label" for="username">
							{'Имя'|t}
						</label>
						<input class="text font-OpenSans f16 h40 lh40"
							   id="username" maxlength="50" name="username"
							   size="16" tabindex="1" type="text"
							   value="{$username}">
						<div class="input-error" {if hasError($errors, "username")}style="display: block"{/if}>
							{getError($errors, "username")}
						</div>
					</div>
					{if isNotAuth()}
						<div class="form-entry relative input_field-js input_email-js">
							<label class="label" for="email">
								Email
							</label>
							<input class="text font-OpenSans f16 h40 lh40"
								   id="email" maxlength="50" name="email"
								   size="16" tabindex="2" type="text"
								   value="{$email}">
							<div class="input-error" {if hasError($errors, "email")}style="display: block"{/if}>
								{getError($errors, "email")}
							</div>
						</div>
					{/if}
					<div class="form-entry relative input_field-js input_message-js">
						<label class="label" for="supportMessage">
							{'Сообщение'|t}
						</label>
						<textarea class="text mh100"
								  id="supportMessage"
								  tabindex="3"
								  name="supportMessage"
								  rows="5"
						>{$supportMessage}</textarea>
						<div class="input-error" {if hasError($errors, "supportMessage")}style="display: block"{/if}>
							{getError($errors, "supportMessage")}
						</div>
					</div>
					<div class="dialog__files">
						<div id="load-files-copyright" class="add-files" data-input-name="support"></div>
					</div>
					<input type="submit"
						   id="submit-copyright-message"
						   value="{'Отправить'|t}"
						   class="mt10 hugeGreenBtn GreenBtnStyle h40 lh40 mw150px pull-reset m-wMax js-uploader-button-disable"/>
				</form>
			</div>
			<div class="clear"></div>
			<a href="{route route="terms_of_service"}" class="f15 db mt15">{'Правила и условия использования'|t}</a>
		</div>
	</div>
{/strip}
{include file="footer.tpl"}