{extends file="toppayer/base.tpl"}
{block name=topPayerContent}
	<h1 class="f32 mb40">{'Спасибо за ответ!'|t}</h1>
	<div class="font-OpenSans f14">
		<form id="top-payer-contact" action="{route route="toppayer_form_handler" params=["token" => $token]}" method="post" class="mb20">
			<span class="mr10">{'Пожалуйста, укажите ваш номер телефона или skype:'|t}</span>
			<input type="text" maxlength="32" class="text font-OpenSans f16 mr10 w160" name="contact" required>
			<input type="submit" class="btn btn_color_green btn_size_m h30" value="{'Отправить'|t}">
		</form>
		{'Руководство свяжется в вами!'|t}
	</div>
{/block}