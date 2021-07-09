{strip}
	<div class="mobile-menu-hide" onclick="mobile_menu_hide();"><i class="fa fa-arrow-left"></i></div>
	<div class="fox-dotcom-mobile-dropdown" id="dropdown-menu">
			<span class="fox-dotcom-mobile-dropdown_profile">
				<span class="dib v-align-m">
					{include file="user_avatar.tpl" profilepicture=$actor->profilepicture username=$actor->username size="big" class="s60"}
				</span>
				<span class="dib v-align-m ml20 fox-dotcom-mobile-dropdown_profile_text">
					<span class="fox-dotcom-mobile-dropdown_profile_text_username">
						{$actor->username}
					</span>
					<br>
					{if $canUserWithdraw}
						<a href="{$baseurl}/balance" class="fox-dotcom-mobile-dropdown_profile_text_balance">
							{include file="utils/currency.tpl" lang=$actor->lang total=$actor->totalFunds|floor}
						</a>
					{/if}
				</span>
			</span>

		<a class="foxkworkitem" href="{$baseurl}/">
			<i class="fa fa-home" aria-hidden="true"></i>{'На главную'|t}
		</a>

		<a class="foxkworkitem" href="/logout">
			<i class="fa fa-sign-out" aria-hidden="true"></i>{'Выход'|t}
		</a>
	</div>
{/strip}