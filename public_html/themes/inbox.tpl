{strip}
{Helper::printJsFile("/js/mainfox.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/pages/inbox/inbox_list.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/pages/inbox/inbox_search.js"|cdnBaseUrl)}
<style type="text/css">
	<style type="text/css">
	@media only screen and (max-width: 767px){
		.all_page {
			padding-top: 94px;
		}
	}
	@media only screen and (max-width: 767px){
		.all_page {
			padding-top: 94px;
		}
	}
</style>
{include file="fox_error7.tpl"}

<div class="m-visible profile-tab inbox-button-block">
	<div class="profile-tab_block clearfix profile-tab_block_message {if $archiveDialogCount == 0} profile-tab_block_message-no_archive {/if}">
		<a href="{$baseurl}/inbox?o={$o}&a={$a}" class="{if $s eq 'all' OR $s eq ''}active{/if} ">{'Все'|t}</a>
		<a href="{$baseurl}/inbox?s=unread&o={$o}&a={$a}" class="{if $s eq 'unread'}active{/if} ">{'Непрочитанное'|t}</a>
		<a href="{$baseurl}/inbox?s=archived&o={$o}&a={$a}" class="{if $s eq 'archived'}active{/if} ">{'Архив'|t}</a>
	</div>
</div>

<div class="centerwrap pt20 clearfix inbox_top">
	
	<h1 class="f32 m-hidden">{'Сообщения'|t}</h1>

	<div class="clearfix">
		{if $m|@count GT 10}
			<div class="inbox-search">
				<form class="inbox-search-form" action="#" method="post">
					<input type="hidden" name="search" value="1" />
					<input class="inbox-search-input styled-input" type="text" name="query" value="" placeholder="{'Поиск по сообщениям'|t}" />
					<button class="inbox-search-reset" type="reset" title="Очистить"></button>
					<button class="inbox-search-submit" type="submit" title="Искать"></button>
				</form>
			</div>
		{/if}
		<div class="btn-group btn-group_4 m-hidden">
			<a href="{$baseurl}/inbox?o={$o}&a={$a}" class=" white-btn {if $s eq 'all' OR $s eq ''} green-btn{/if}">{'Все'|t}</a>
			<a href="{$baseurl}/inbox?s=unread&o={$o}&a={$a}" class=" white-btn {if $s eq 'unread'} green-btn{/if}">{'Непрочитанное'|t}</a>

			{if $archiveDialogCount != 0}<a href="{$baseurl}/inbox?s=archived&o={$o}&a={$a}" class=" white-btn {if $s eq 'archived'} green-btn{/if}">{'Архив'|t}</a>{/if}
		</div>
	</div>

	{if $m|@count == 0}
		<div class="not_messages-text mt25 font-OpenSans">
			{if $s eq 'all'}
				{'Здесь будет отображаться Ваша переписка с другими пользователями.'|t}<br>{'Для того, чтобы начать беседу, перейдите на профиль пользователя и нажмите кнопку "Написать сообщение"'|t}
			{elseif $s eq 'unread'}
				{'Здесь будут последние непрочитанные сообщения Вам от других пользователей.'|t}
			{elseif $s eq 'archived'}
				{'Здесь будут отображаться Ваши переписки с другими пользователями, которые вы отправили в архив.'|t}
			{/if}
		</div>
	{/if}
	{if !$isMobile}
	<!-- Включение/Выключение звуковых уведомлений -->
	<div class="message_sound_block m-hidden">
		<i class="tooltipster js-message-sound-ico icon ico-bell {if $actor->message_sound}hidden{/if}"  onclick="setMessageSound($(this));"
			data-tooltip-side="left"
			data-tooltip-text="{'Звуковые уведомления отключены. Включить'|t}">
		</i>
		<i class="tooltipster js-message-sound-ico icon ico-bell_active {if !$actor->message_sound}hidden{/if}" onclick="setMessageSound($(this));"
			data-tooltip-side="left"
			data-tooltip-text="{'Звуковые уведомления включены. Отключить'|t}">
		</i>
	</div>
	{/if}
</div>

	<div class="message_list-block">
		{if $m|@count > 0}
				{control name="inbox\list" s=$s a=$a o=$o m=$m pagingData=$pagingData}
		{/if}
	</div>
{/strip}