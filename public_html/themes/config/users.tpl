{if !$config["users"]}
	{append var="config" value=[
		"profilePicUrl" => App::config("membersprofilepicurl")
	] index="users" scope=root}
{/if}
<script>
	{to_js name="config.users" var=$config.users}
	{if $userAvatarColors}{to_js name="userAvatarColors" var=$userAvatarColors}{/if}
</script>