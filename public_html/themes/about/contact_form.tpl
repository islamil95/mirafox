{if App::allowUseGoogle()}
	{reCAPTCHA::getJS()}
{/if}
{Helper::printJsFile("/js/pages/form/contact.js"|cdnBaseUrl)}
{strip}
	<div class="form-messages"></div>
	{include file="fox_error7.tpl"}
	<div class="static-page__block">
		<div class="white-bg-block centerwrap">
			<div class="pt20 m-visible"></div>
			<h1 class="f32">{$formName}</h1>
			<p class="mb20">
				{'Если у Вас есть вопросы, пожелания или предложения по работе с сервисом, Вы можете связаться с нами по электронной почте:'|t} {if isUserOrContextRu()}
					<a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a>.
				{else}
					<a href="mailto:info@kwork.com" target="_blank">info@kwork.com</a>.
				{/if} {'Или отправить нам сообщение с помощью формы обратной связи:'|t}
			</p>
			<div id="foxForm" class="mw50p m-wMax help-form-container">
				<form action="{route route="contact_form_handler"}" method="post" name="contact" class="send_contact_message-js">
					<div class="form-entry relative input_field-js input_name-js">
						<label class="label" for="username">{'Имя'|t}</label>
						<input class="text f16 h40 lh40"
							   id="username"
							   maxlength="50"
							   name="username"
							   size="16"
							   tabindex="1"
							   type="text"
							   value="{$username}"/>
						<div class="input-error" {if hasError($errors, "username")}style="display: block"{/if}>
							{getError($errors, "username")}
						</div>
					</div>
					{if isNotAuth()}
						<div class="form-entry relative input_field-js input_email-js">
							<label class="label" for="email">Email</label>
							<input class="text f16 h40 lh40"
								   id="email"
								   maxlength="50"
								   name="email"
								   size="16"
								   tabindex="2"
								   type="text"
								   value="{$email}"/>
							<div class="input-error" {if hasError($errors, "email")}style="display: block"{/if}>
								{getError($errors, "email")}
							</div>
						</div>
					{/if}
					<div class="form-entry relative input_field-js input_message-js">
						<label class="label" for="supportMessage">{'Сообщение'|t}</label>
						<textarea class="text mh100"
								  id="supportMessage"
								  tabindex="3"
								  name="supportMessage"
								  rows="5">{$supportMessage}</textarea>
						<div class="input-error" {if hasError($errors, "supportMessage")}style="display: block"{/if}>
							{getError($errors, "supportMessage")}
						</div>
					</div>
					<div class="row m-text-center">
						<div class="captcha-container" style="display: none;">
							{if App::allowUseGoogle()}
								<div class="form-entry input_captcha-js">
									{reCAPTCHA::getFormField()}
									<div class="input-error" style="position: relative"></div>
								</div>
							{/if}
						</div>
						<input type="submit"
							   value="{'Отправить'|t}"
							   class="hugeGreenBtn GreenBtnStyle h40 lh40 pull-reset mw150px dibi form-send-message" />
						<input type="hidden"
							   name="jlog"
							   id="jlog"
							   value="1" />
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="centerwrap">
		{if isUserOrContextRu()}
			<p>
				email: <a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a><br>
				skype: <a href="skype:komanda.kwork?chat">komanda.kwork</a><br>
			</p>
			<br>
		{else}
			<p>
				<b>Kwork Technologies OÜ</b><br>
				Registry code: 14531126<br>
				Harju maakond, Таllinn, Kesklinna linnaosa, Nаrvа mnt 7-Narva mnt 7,-634<br>
				email: <a href="mailto:info@kwork.com" target="_blank">info@kwork.com</a>
			</p>
			<br>
		{/if}
	</div>
	<script>
		var inputValidator = new ValidInputsModule();
		inputValidator.init();

		var form = new ContactForm("{route route="contact_form_handler"}");
		form.init();

	</script>
{/strip}