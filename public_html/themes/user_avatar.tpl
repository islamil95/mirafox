{strip}
	{$letter = $username|mb_substr:0:1}
	{$background = UserManager::getAvatarColor($letter)}
	<span class="user-avatar{if $class != ''} {$class}{/if} js-user-avatar_block">
		{if $profilepicture === 'noprofilepicture.gif' && ($letter|is_numeric || $letter == '_' || $letter == '-')}
			<img class="user-avatar__picture rounded" src="{"/avatar/{$size}/noprofilepicture.png"|cdnImageUrl}" style="background: {$background};" alt="{$letter}">
		{elseif $profilepicture === 'noprofilepicture.gif'}
			<span class="user-avatar__default" style="background: {$background};">{$letter}</span>
		{elseif $pageSpeedDesktop && $pageName == "view" && $reviewsBlock == "kwork_reviews"}
			<img class="user-avatar__picture rounded lazy-load_scroll" src="{"/blank.png"|cdnImageUrl}" data-src="{"/{$size}/{$profilepicture}"|cdnMembersProfilePicUrl}" {if $size == 'medium'}data-{userMediumPictureSrcset($profilepicture)}{/if} alt="">
		{else}
			<img class="user-avatar__picture js-user-avatar__picture rounded" src="{"/{$size}/{$profilepicture}"|cdnMembersProfilePicUrl}" alt="">
		{/if}
	</span>
{/strip}