{strip}
	{foreach $users as $user}
		{if !is_array($user)}
			{assign var='user' value=get_object_vars($user)}
		{/if}
		{assign var="hasNoPicture" value=(($user['username']|substr:0:1 eq '-' || $user['username']|substr:0:1 eq '_') && $user['profilepicture']|lower eq 'noprofilepicture.gif')}
		<div class="cusongsblock-user user-search_block" data-id="{$user['USERID']}">
			<div class="cusongsblock-user_logo user-search_logo">
				<a
					href="{$baseurl}/user/{$user['username']|lower}"
					{if ($user['profilepicture']|lower eq 'noprofilepicture.gif' && $user['avatarColor'])}
					style="background-color: {$user['avatarColor']}"
					{/if}
				>
					{if ($user['profilepicture']|lower eq 'noprofilepicture.gif') && !$hasNoPicture}
						{$user['username']|substr:0:1}
					{else}
						{if $user['profilepicture']|lower eq 'noprofilepicture.gif'}
							<img alt="{$user['username']}" class="rounded" src="{"/avatar/big/noprofilepicture.png"|cdnImageUrl}">
						{else}
							<img alt="{$user['username']}" class="rounded" src="{"/big/{$user['profilepicture']}"|cdnMembersProfilePicUrl}">
						{/if}
					{/if}
				</a>
			</div>
			<div class="cusongsblock-user_name">
				<a href="{$baseurl}/user/{$user['username']|lower}">{$user['username']}</a>
			</div>
			<div class="cusongsblock-user_country">
				{insert name=city_id_to_name value=a assign=usercc id=$user['city_id'] countryId=$user['country_id']}
				{$usercc}
			</div>
			<div class="cusongsblock-user_rating" style="text-align:center;">
				<ul class="rating-block cusongsblock-panel__rating-list user-search_rating">
				{if $user['rating'] > 0}
					{control name=rating_stars rating=$user['rating']}
				{else}
					<li class='rating-block__rating-item--new'>{'Новый'|t}</li>
				{/if}
				</ul>
			</div>
		</div>
	{/foreach}
{/strip}