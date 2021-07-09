{if $actor->id ne $userProfile->USERID and $privateMessageStatus === true}
	{*Внимание работать будет только на странице профиля пользователя т.к. зависит от попапа в шаблоне individual_message.tpl*}
	<div class="cusongsblock user-custom-request">
		<div class="request_kwork_block">
			{include file="user_avatar.tpl" profilepicture=$userProfile->profilepicture username=$userProfile->username size="big" class="request_kwork_image s80"}
			<span class="f16 semibold">{'Нужно что-то еще?'|t}</span><br>
			<span class="request_kwork_text">{'Вы можете заказать у меня индивидуальный кворк'|t}</span>
			<div class="m-mb15 green-btn mt15 wMax {if $isUserProfile && $actor && $privateMessageStatus === true}js-individual-message__popup-link{else}signup-js{/if}"
				 data-type="{InboxManager::CUSTOM_REQUEST_TYPE}" onclick="" touch-action="manipulation">
				{'Заказать кворк'|t}
			</div>
		</div>
	</div>
{/if}