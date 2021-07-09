{strip}
	{if $actor}
		<div class="block-popup">
			{if $notificationData.last_tracks|@count eq "0"}
				<div id="foxNotifBox_none" class="nowrap">
					{'У Вас пока нет уведомлений.'|t}
				</div>
			{else}
				<div class="foxNotifBox_other">
					{foreach item=notifyTypeItem from=$notificationData.last_tracks key=notificationTypeName name=list}
						{if !$smarty.foreach.list.first}
							<hr class="gray"/>
						{/if}
						{if $notifyTypeItem->getNotificationCount() > 1}
							<a href="{$notifyTypeItem->getLink()}" class="bold fs13 db pt15 pb15 nowrap"
							   data-type="{$notifyTypeItem->getEntityType()}">
								{$notifyTypeItem->getDescription()|t} ({$notifyTypeItem->getNotificationCount()})
							</a>
						{else}
							<a href="{$notifyTypeItem->getFirstNotify()->getLink()}"
							   class="bold fs13 db pt15 pb15 nowrap" data-type="{$notifyTypeItem->getEntityType()}">
								{$notifyTypeItem->getDescription()|t}
							</a>
						{/if}
					{/foreach}
				</div>
			{/if}
		</div>
	{else}
		<script>window.location.href = '/';</script>
	{/if}
{/strip}