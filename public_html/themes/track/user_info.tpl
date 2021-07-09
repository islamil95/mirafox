{strip}
	<div class="track--info">
        {include file="track/sidebar_order_state.tpl"}
	</div>
	<div class="track--info__user  p20-15">
        {if isAllowToUser($order->USERID)}
            {$orderUser = $order->worker}
        {else}
            {$orderUser = $order->payer}
        {/if}
		<div class="track--info__user-details">
			<div class="track--info__user-title d-flex">
                        <span class="mr-auto">
							{if isAllowToUser($order->USERID)}
                                {'Продавец'|t}
                            {else}
                                {'Покупатель'|t}
                            {/if}
						</span>
				<a class="track--info__user-link fadeble"
				   href="{absolute_url route="profile_view" params=["username" => $orderUser->username|lower]}">
                    {$orderUser->username}
				</a>
			</div>
            {if $orderUser->getTranslatedFullname()}
				<div class="track--info__user-name fadeble">
                    {$orderUser->getTranslatedFullname()|stripslashes}
				</div>
            {/if}
            {insert name=is_online assign=is_online value=a userid=$orderUser->USERID}
			<div class="nowrap color-gray">
				<span class="js-user-online-block online-status align-items-center d-flex justify-content-end"
					  data-user-id="{$orderUser->USERID}" data-with-text="true">
						   {if $is_online == 1}
							   <i class="dot-user-status dot-user-online"></i>
                               {'Онлайн'|t}
							{else}

							   <i class="dot-user-status dot-user-offline"></i>
								    {'Оффлайн'|t}
							   <span class="f13 user-offline-time">&nbsp;({$orderUser->getLastOnline()|timeLeft:false:''})</span>
                           {/if}
					</span>
			</div>
		</div>
		<div class="track--info__user-avatar">
			<a href="{absolute_url route="profile_view" params=["username" => $orderUser->username|lower]}">
                {include
                file="user_avatar.tpl"
                profilepicture={$orderUser->profilepicture}
                username=$orderUser->username
                size="big"
                class="s60"
                }
			</a>
		</div>


	</div>
{/strip}