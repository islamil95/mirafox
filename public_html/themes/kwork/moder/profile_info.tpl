{strip}
<div class="profile-info clearfix {if $kwork.fullname}threerows{/if}">
	{insert name=get_member_profilepicture assign=profilepicture value=var USERID=$kwork.USERID}
	<div class="profile-avatar">
		<a href="{userProfileUrl($kwork.username)}">
			{include file="user_avatar.tpl" profilepicture=$profilepicture username=$kwork.username size="medium"}
		</a>
	</div>
	<div class="pull-left">
		<div class="user-info">
			<a href="{userProfileUrl($kwork.username)}">
				{$kwork.username|stripslashes}
			</a>
			{if Translations::isDefaultLang()}
				{if ($kwork.fullname)}
					<a href="{userProfileUrl($kwork.username)}">
						{$kwork.fullname|stripslashes}
					</a>
				{/if}
			{else}
				{if ($kwork.fullnameen)}
					<a href="{userProfileUrl($kwork.username)}">
						{$kwork.fullnameen|stripslashes}
					</a>
				{/if}
			{/if}
		</div>
	</div>
</div>
{/strip}