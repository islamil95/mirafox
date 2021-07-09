<div class="js-notice-other massage ml10" data-active="{$actor->notify_unread_count > 0}">
	<a href="#" class="js-notice__link">
		{if $actor->notify_unread_count > 0}
			{if $actor->notify_unread_count > 9 }
				<div class="massage_counter f10 {if $actor->red_notify && App::config('module.inbox_abuse.enable')}message_counter_warning{/if}">+</div>
			{else}
				<div class="massage_counter {if $actor->red_notify && App::config('module.inbox_abuse.enable')}message_counter_warning{/if}">{$actor->notify_unread_count}</div>
			{/if}
		{else}
			<div class="massage_counter {if $actor->red_notify && App::config('module.inbox_abuse.enable')}message_counter_warning{/if}" style="display: none"></div>
		{/if}
	</a>
	{if $actor}
		<div class="block-popup {if $actor->notify_unread_count > 0}wating{/if}">
			{if $actor->notify_unread_count == 0}
				<div id="foxNotifBox_none" class="nowrap">
					{'У Вас пока нет новых уведомлений.'|t}
				</div>
				<script>
					notifyIsLoad = true;
				</script>
			{else}
				&nbsp;
			{/if}
		</div>
	{/if}
</div>