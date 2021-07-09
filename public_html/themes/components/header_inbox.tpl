<div class="js-notice-inbox massage todo ml10" data-active="{$unreadDialogCount > 0}">
	{if $actor->is_available_at_weekends == 1 || ($actor->is_available_at_weekends == 0 && !Helper::isWeekends())}
		{assign var="warn" value="message_counter_warning"}
	{else}
		{assign var="warn" value=""}
	{/if}
	<a href="{$baseurl}/inbox" class="js-notice__link">
		{if $unreadDialogCount > 9}
			<div class="massage_counter {if $warningDialogCount > 0 && App::config("module.inbox_abuse.enable")}{$warn}{/if}" style="font-size: 10px;">+</div>
		{elseif $unreadDialogCount < 1}
			<div class="massage_counter {if $warningDialogCount > 0 && App::config("module.inbox_abuse.enable")}{$warn}{/if}" style="display: none"></div>
		{else}
			<div class="massage_counter {if $warningDialogCount > 0 && App::config('module.inbox_abuse.enable')}{$warn}{/if}">{$unreadDialogCount}</div>
		{/if}
	</a>
	{if $actor}
		<div class="block-popup {if $unreadDialogCount > 0}wating{/if}">
			<div class="notifications-content-block">
				{if $unreadDialogCount == 0}
					<span class="inbox-message-link empty-inbox-data nowrap">
						{'У Вас пока нет новых сообщений.'|t}
					</span>
				{else}
					&nbsp;
				{/if}
			</div>
		</div>
	{/if}
</div>