{strip}
	{*
	 ожидает array $user с полями:
	 USERID, username, fullname, fullnameen, profilepicture, live_date
	 и $portfolio (но необязательно)
	*}
	<div class="profile-info clearfix {if ($user.fullname)}threerows{/if}">
		{insert name=get_member_profilepicture assign=profilepicture value=var USERID=$user.USERID}
		<div class="profile-avatar">
			<a href="{userProfileUrl($user.username)}"{if $portfolio->id} class="js-default-portfolio-url"{/if}>
				{include file="user_avatar.tpl" profilepicture=$user.profilepicture username=$user.username size="medium"}
			</a>
		</div>
		<div class="profile-stats">
			<div class="user-info">
				<a href="{userProfileUrl($user.username)}"{if $portfolio->id} class="js-default-portfolio-url bold"{/if}>{$user.username|stripslashes}</a>
				{if Translations::isDefaultLang()}
					{if ($user.fullname)}
						<a href="{userProfileUrl($user.username)}"{if $portfolio->id} class="js-default-portfolio-url"{/if}>{$user.fullname|stripslashes}</a>
					{/if}
				{else}
					{if ($user.fullnameen)}
						<a href="{userProfileUrl($user.username)}"{if $portfolio->id} class="js-default-portfolio-url"{/if}>{$user.fullnameen|stripslashes}</a>
					{/if}
				{/if}
			</div>
			{insert name=user_level assign=level value=a userid=$user.USERID}
			<div class="user-level fw300">
				{if $level eq 1}
					{'Новичок'|t}
				{/if}
				{if $level eq 2}
					{'Продвинутый'|t}
				{/if}
				{if $level eq 3}
					{'Профессионал'|t}
				{/if}
				{insert name=is_online assign=is_online value=a userid=$user.USERID}
				{if $is_online eq 1}
					<span class="font-OpenSans f14 ml10 fw300"><i class="dot-user-status dot-user-online"></i> {'Онлайн'|t}</span>
				{else}
					{insert name=last_online_ago assign=last_online value=a time=$user.live_date}
					<span class="font-OpenSans f14 ml10 fw300">
					<i class="dot-user-status dot-user-offline"></i> {'Оффлайн'|t}</span>
				{/if}
			</div>
		</div>
		{if $portfolio->id}
			<hr class="gray mb13">
			{if $user.cache_rating > 0}
				<div class="clearfix">
					<div class="pull-right cusongsblock-panel__rating m-pull-reset ">
						<ul class="rating-block cusongsblock-panel__rating-list dib v-align-m">
							{control name=rating_stars rating=$user.cache_rating}
						</ul>
					</div>
					<span class="f14 dib">{'Репутация'|t}</span>
				</div>
			{/if}
			<div class="clearfix mt6">
				<div class="pull-right f14">{$user.order_done_count}</div>
				<div class="f14">{'Выполнено заказов'|t}</div>
			</div>
			<div class="clearfix mt6">
				<div class="pull-right f14">
					<i class="ico-green-circle dib v-align-m mr5"></i>
					<span class="dib v-align-m mr5">{$user.allGoodReviews}</span>
					<i class="ico-red-circle dib v-align-m ml5 mr5"></i>
					<span class="dib v-align-m">{$user.allBadReviews}</span>
				</div>
				{assign var="totalReviews" value=($user.allGoodReviews+$user.allBadReviews)}
				{if $totalReviews > 0}
					{$totalReviews} {declension count=$totalReviews form1="оценка в заказах" form2="оценки в заказах" form5="оценок в заказах"}
				{else}
					{'Нет оценок'|t}
				{/if}
			</div>
		{/if}

		{* Редактировать *}
		{if $actor->USERID == $portfolio->user->USERID}
			<div class="mt10 clearfix js-portfolio-edit-field" style="display: none;">
				<button class="green-btn bnt-portfolio-edit pull-right js-edit-portfolio">
					{'Редактировать'|t}
				</button>
			</div>
		{/if}
	</div>
{/strip}