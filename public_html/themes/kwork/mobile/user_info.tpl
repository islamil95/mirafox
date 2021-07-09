{strip}
<div class="m-visible mt5 fs15">
	<div class="page-more-kwork_info d-flex justify-content-between mt30 mb30 fs15">
		<div class="page-more-kwork_col">
			<div class="page-more-kwork_label">{'Выполнение'|t}</div>
			<div class="page-more-kwork_value fw700">
				{$kwork.days|stripslashes} {declension count=$kwork.days form1="день" form2="дня" form5="дней"}
			</div>
		</div>
		<div class="page-more-kwork_col">
			<div class="page-more-kwork_label">{'В очереди'|t}</div>
			<div class="page-more-kwork_value fw700">{$kwork.queueCount}</div>
		</div>
		<div class="page-more-kwork_col">
			<div class="page-more-kwork_label">{'В Избранном'|t}</div>
			<div class="page-more-kwork_value fw700 js-fav-count">{$kwork.bookmark_count}</div>
		</div>
		<div class="page-more-kwork_col">
			<div class="page-more-kwork_bookmark mt8">
				{if !$canModer}
					{* Избранное *}
					{include file="kwork/view_bookmark_block.tpl"}
				{/if}
			</div>
		</div>
	</div>

	<div class="clearfix hidden">
		<div class="pull-right font-OpenSans f14">
			<i class="ico-green-circle dib v-align-m mr5"></i>
			<span class="dib v-align-m mr5">{$goodReviews}</span>
			<i class="ico-red-circle dib v-align-m ml5 mr5"></i>
			<span class="dib v-align-m">{$badReviews}</span>
		</div>
		{if $foxtotalvotes > 0}{$foxtotalvotes} {declension count=$foxtotalvotes form1="оценка в заказах" form2="оценки в заказах" form5="оценок в заказах"}{else}{'Нет оценок'|t}{/if}
	</div>

	<div class="page-more-kwork_user d-flex justify-content-between">
		<a class="page-more-kwork_user-block" href="{userProfileUrl($kwork.username)}">
			{insert name=get_member_profilepicture assign=profilepicture value=var USERID=$kwork.USERID}
			<div class="page-more-kwork_user-picture pull-left mr15">
				{include file="user_avatar.tpl" profilepicture=$profilepicture username=$kwork.username size="medium"}
			</div>
			<div class="page-more-kwork_user-name ovf-h">
				<i class="fa fa-circle {if $is_online}color-green{else}color-gray{/if} fs20 mr10"></i>
				{$kwork.username}
			</div>
		</a>
		<div class="page-more-kwork_user-rating mr10 ml10">
			<ul>
				{control name=rating_stars rating=$userRating}
				<li class="fs14 color-gray">({$count_reviews})</li>
			</ul>
		</div>
		{if $config.chat.isFocusGroupMember}
			<a href="javascript:void(0)" class="page-more-kwork_user-letter"
			   onclick="firstConversationMessage(hasConversation, chatRedirectUrl, conversationUserId, conversationMessage)">
				<i class="fa fa-envelope color-green mr10"></i>
				<i class="fa fa-angle-right color-gray"></i>
			</a>
		{else}
			<a href="{getConversationUrl($kwork.username)}&kworkId={$kwork.PID}" class="page-more-kwork_user-letter">
				<i class="fa fa-envelope color-green mr10"></i>
				<i class="fa fa-angle-right color-gray"></i>
			</a>
		{/if}
	</div>

</div>
{/strip}