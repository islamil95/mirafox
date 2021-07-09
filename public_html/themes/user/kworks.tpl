{strip}
	<div class="bgLightGray pb35">
		<div class="centerwrap lg-centerwrap clearfix block-response">
			<a name="#kwork" class="profile-tab-link"></a>
			<h2 class="f26">{'Кворки пользователя'|t}</h2>
			{if $totalKworks == 0}
				<p class="font-OpenSans f16 lh24 mt20">
					{'У пользователя <span class="break-all-word">%s</span> нет активных кворков'|t:($userProfile->username|stripslashes)}
				</p>
			{else}
				<div class="user-kwork-list-container user-kwork-list cusongslist mt10 t-align-l {if $isCustomRequest && (!$actor || $actor->id != $userProfile->USERID) && $privateMessageStatus === true}show-last{/if}">
					{include file="fox_bit.tpl" posts=$userKworks isUserProfile=true}
					<div class="clear"></div>
				</div>
				{if $totalKworks > 10 || ($totalKworks > 9 && $isCustomRequest && (!$actor || $actor->id != $userProfile->USERID) && $privateMessageStatus === true)}
					<div class="mt10 m-text-center">
						<a href="#show_all" class="kworks-show-all fs16 fw600" onclick="return false;">{'Показать все кворки продавца'|t}</a>
						<a href="#hide_all" class="kworks-hide-all fs16 fw600 hidden" onclick="return false;">{'Скрыть кворки продавца'|t}</a>
					</div>
				{/if}
			{/if}
		</div>
	</div>
{/strip}