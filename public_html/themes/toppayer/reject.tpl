{extends file="toppayer/base.tpl"}
{block name=topPayerContent}
	<h1 class="f32 mb40">{'Извините!'|t}</h1>
	<div class="font-OpenSans f14">
		{'Администрация Кворк не побеспокоит вас больше.'|t}
	</div>
	<br>
	<a href="{route route="toppayer_form" params=["token" => $token]}">
		{'Я передумал и готов участвовать!'|t}
	</a>
{/block}