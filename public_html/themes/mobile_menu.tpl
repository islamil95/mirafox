{strip}
	<div class="mobile-menu-hide" onclick="mobile_menu_hide();"><i class="fa fa-arrow-left"></i></div>
	<div class="fox-dotcom-mobile-dropdown" id="dropdown-menu">
		{if $actor}
			<span class="fox-dotcom-mobile-dropdown_profile">
			<a href="{$baseurl}/user/{$actor->username|lower}" class="dib v-align-m">
				{include file="user_avatar.tpl" profilepicture=$actor->profilepicture username=$actor->username size="big" class="s60"}
			</a>
      <span class="dib v-align-m ml20 fox-dotcom-mobile-dropdown_profile_text">
      <a href="{$baseurl}/user/{$actor->username|lower}" class="fox-dotcom-mobile-dropdown_profile_text_username">{$actor->username}</a><br>
      <a href="{$baseurl}/balance" class="fox-dotcom-mobile-dropdown_profile_text_balance">
      	{include file="utils/currency.tpl" lang=$actor->lang total=$actor->totalFunds|floor}
      </a>
      </span>
			</span>
		{/if}
		{if !$actor}
			{if Translations::getLang() != Translations::EN_LANG}
				<div class="ml10 mr10 mb20">
					<a class="hugeGreenBtn GreenBtnStyle h40 pull-reset wMax signup-js">{'Регистрация'|t}</a>
				</div>
			{/if}
			<a class="foxkworkitem login-js" href="javascript:;"><i class="fa fa-sign-in"></i>{'Вход'|t}</a>
		{/if}
		<a class="foxkworkitem" href="{$baseurl}/"><i class="fa fa-home" aria-hidden="true"></i>{'На главную'|t}</a>

		{if $actor}
			<a class="foxkworkitem" href="{$baseurl}/categories"><i class="fa fa-th-large" aria-hidden="true"></i>{'Категории'|t}</a>
			<a class="foxkworkitem foxkworkitem_top-border js-notice-mobile" href="{$baseurl}/notifications">
				<i class="fa fa-bell" aria-hidden="true"></i>{'Уведомления'|t}
				<span class="notify-number-block js-notice-mobile__other-counter{if $actor->notify_unread_count == 0} hidden{/if}{if $actor->red_notify && App::config('module.inbox_abuse.enable')} notify-number-block_warning{/if}">{$actor->notify_unread_count}</span>
			</a>
			
			{$warn = ""}
			{if 
				($warningDialogCount > 0 && App::config("module.inbox_abuse.enable")) &&
				($actor->is_available_at_weekends == 1 || ($actor->is_available_at_weekends == 0 && !Helper::isWeekends()))
			}
				{$warn = "notify-number-block_warning"}
			{/if}
			<a class="foxkworkitem" href="{$baseurl}/inbox"><i class="fa fa-envelope-o" aria-hidden="true"></i>{'Сообщения'|t}
				<span class="notify-number-block js-notice-mobile__message-counter {$warn}" {if $unreadDialogCount == 0} style="display: none;"{/if}>
						{$unreadDialogCount}
				</span>
			</a>
			
			{if $basketEnable}
				<a class="foxkworkitem" href="{$baseurl}/basket">
					<i class="fa fa-shopping-cart" aria-hidden="true"></i>{'Корзина'|t}
					{if $cart|count gt 0}
						<span class="notify-number-block">{$cart|count}</span>
					{/if}
				</a>
			{/if}
			<a class="foxkworkitem" href="{$baseurl}/bookmarks">
				<i class="fa fa-heart" aria-hidden="true"></i>{'Избранное'|t}
			</a>
            {if $show2019CaseContestLink}
                {include file="contest/contest_2019_case_mobile_menu_link.tpl"}
            {/if}
			<a class="foxkworkitem foxkworkitem_type">{'Покупатель'|t}</a>
			<a class="foxkworkitem" href="{$baseurl}/orders" onclick="changeUserType(1)">
				<i class="fa fa-list" aria-hidden="true"></i>
				<span class="has-orders-count-as-payer">{'Мои заказы'|t}</span>
			</a>
			<a class="foxkworkitem" href="{$baseurl}/manage_projects" onclick="changeUserType(1)">
				<i class="fa fa-list-alt" aria-hidden="true"></i>{'Биржа'|t}
			</a>
			<a class="foxkworkitem foxkworkitem_type">{'Продавец'|t}</a>
			<a class="foxkworkitem js-blocked-kworks" href="{$baseurl}/new" onclick="changeUserType(2)">
				<i class="fa fa-plus-circle" aria-hidden="true"></i>{'Создать кворк'|t}
			</a>
			<a class="foxkworkitem" href="{$baseurl}/manage_kworks" onclick="changeUserType(2)">
				<i class="fa fa-th" aria-hidden="true"></i>{'Мои кворки'|t}
			</a>
			<a class="foxkworkitem" href="{$baseurl}/projects" onclick="changeUserType(2)">
				<i class="fa fa-exchange" aria-hidden="true"></i>{'Биржа'|t} <span class="italic f12">{'+%s проектов за сутки'|tn:{WantManager::getNewWantsCount()}:{WantManager::getNewWantsCount()}}</span>
			</a>
			{* #5627 contest *}
			{if false && \UserManager::isLanguageTester($actor->USERID) &&  Translations::getLang() == Translations::DEFAULT_LANG}
				<a class="foxkworkitem" href="{$baseurl}/prize" onclick="changeUserType(2)">
					<i class="fa fa-gift" aria-hidden="true"></i>Розыгрыш iPhone 8
					<div class="round-sign">!</div>
				</a>
			{/if}
			{* end 5627 *}
			<a class="foxkworkitem" href="{$baseurl}/manage_orders" onclick="changeUserType(2)">
				<i class="fa fa-cog" aria-hidden="true"></i>
				<span class="has-orders-count-as-worker">{'Заказы'|t}</span>
			</a>
			{if $actor->analytics_enable}
				<a href="{$baseurl}/analytics" class="foxkworkitem" onclick="changeUserType(2)">
					<i class="fa fa-bullseye" aria-hidden="true"></i>{'Аналитика продаж'|t}
				</a>
			{/if}

			{if $actor && ($actor->role == "moder" || $actor->role == "admin")}
				{assign var=countModerKwork value=ModerManager::getRedisKworkCountOnModer()}
				<a class="foxkworkitem {if $countModerKwork > 100}color-red{/if}" href="{$baseurl}/moder_kwork">
					<i class="fa fa-user-secret" aria-hidden="true"></i>{'Модерация'|t}
					{if $countModerKwork} ({$countModerKwork}){/if}
				</a>
			{/if}
		{/if}
		{if !$actor}

			{if $isTopFreelancerApp}
				<a class="foxkworkitem" href="{$baseurl}/manage_projects"><i class="fa fa-list-alt" aria-hidden="true"></i>{'Биржа'|t}</a>
			{else}
				<a class="foxkworkitem" href="{$baseurl}/categories"><i class="fa fa-th-large" aria-hidden="true"></i>{'Категории'|t}</a>
			{/if}
			<a class="foxkworkitem" href="{$baseurl}/terms_of_service"><i class="fa fa-list-ul"></i>{'Правила сервиса'|t}</a>
			<a class="foxkworkitem" href="/faq">
				<i class="fa fa-question" aria-hidden="true"></i>{'Вопрос - Ответ'|t}
			</a>
		{/if}
		{if $actor}
			<a class="foxkworkitem foxkworkitem_type">{'ОБЩЕЕ'|t}</a>
			{* @TODO: Перевести на роутинг *}
			<a class="foxkworkitem" href="/faq">
				<i class="fa fa-question" aria-hidden="true"></i>{'Вопрос - Ответ'|t}
			</a>
			{if Translations::isDefaultLang()}
				<a class="foxkworkitem" href="{$baseurl}/kwork_book">
					<i class="fa fa-info" aria-hidden="true"></i>Как зарабатывать на Kwork
				</a>
			{/if}
			<a class="foxkworkitem" href="/logout">
				<i class="fa fa-sign-out" aria-hidden="true"></i>{'Выход'|t}
			</a>
		{/if}
		<a class="foxkworkitem render_desktop-js"><i class="fa fa-desktop" aria-hidden="true"></i>{'Перейти на полную версию сайта'|t}</a>
	</div>
{/strip}