{strip}
	{if $order.is_online}
		<i class="dot-user-status dot-user-online"></i>
	{else}
		<i class="dot-user-status dot-user-offline"></i>
	{/if}
	<a class="user-name ml5"
	   href="{absolute_url route="profile_view" params=["username" => $order.username|lower]}">
        {$order.username}
	</a>
{/strip}