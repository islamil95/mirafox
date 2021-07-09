{*
	Поскольку параметры конфигурации могут использоваться как в шаблонах smarty,
	так и в публичном js, далее инициализируются обе переменные. Если нет необходимости
	использовать какой-то параметр до полной загрузки страницы (например, при построении DOM
	компонентов vue), достаточно заполнить ее в smarty.

	Итоговый проброс параметров из smarty в js выполняется в шаблоне
	config/footer.tpl, при этом выполняется объединение (merge) заполненного js-объекта
 	с массивом из smarty.
*}

{* Объявление js-переменной *}
{literal}{/literal}
	<script>var config = {
		cdn : {
			"baseUrl"  : "{App::config("cdn.base_url")}",
			"adminUrl" : "{App::config("cdn.admin_url")}",
			"imageUrl" : "{App::config("imageurl")}"
		}
	};</script>

{* Объявление smarty-переменной *}
{assign var="config" value=[] scope=root}

{*
 	Далее выполняется подключение шаблонов с различными параметрами и
 	настройками приложения
*}

{* Пользователи *}
{include file="config/users.tpl"}

{* Параметры загрузки файлов *}
{include file="config/files.tpl"}

{* Параметры трека *}
{include file="config/track.tpl"}

{* Параметры чата *}
{include file="config/chat.tpl"}