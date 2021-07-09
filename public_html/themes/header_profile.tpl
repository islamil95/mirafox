{strip}
	<a href="{$baseurl}/user/{$actor->username|lower}" class="dib v-align-m w60">
		{include file="user_avatar.tpl" profilepicture=$actor->profilepicture username=$actor->username size="big" class="s60"}
	</a>
	<div class="dib v-align-m pull-reset ml10 w172 lh20 mt-7">
		<div><a href="{$baseurl}/user/{$actor->username|lower}"
			 class="color-text font-OpenSans dropdownbox_username">{$actor->username}</a></div>
		<div><span id="usertypelabel2" class="f14 color-lightGray dib">
		{if $actor->type eq 'payer'}
			{'Покупатель'|t}
		{elseif $actor->type eq 'worker'}
			{'Продавец'|t}
		{/if}
		</span></div>
		{if $actor->type eq 'worker' && $actor->worker_status && $actor->worker_status != \UserManager::WORKER_STATUS_NONE}
		<div class="position-r"><a href="javascript:;" class="dib underline-hover worker-status-{$actor->worker_status} js-worker-status js-worker-status-switch js-worker-status-switch-container" data-status="{$actor->worker_status}" data-check-offers="true" title="{'Изменить статус'|t}">
				{if $actor->worker_status == \UserManager::WORKER_STATUS_FREE}{'Принимаю заказы'|t}{/if}
				{if $actor->worker_status == \UserManager::WORKER_STATUS_BUSY}{'Занят'|t}{/if}
			</a>
			<span class="kwork-icon icon-custom-help tooltipster ml5 {if $actor->worker_status == \UserManager::WORKER_STATUS_FREE}hidden{/if}"
				data-tooltip-side="bottom"
				data-tooltip-theme="light"
				data-tooltip-interactive="false"
				data-tooltip-text="{'<p>Кворки остановлены, заказы не поступают.</p><p><b>Внимание!</b><br /> Когда будете готовы выполнять услуги, включите статус «Принимаю заказы», и активные кворки снова станут доступны в каталоге.</p>'|t}"></span>
			<span class="kwork-icon icon-custom-help tooltipster ml5 {if $actor->worker_status == \UserManager::WORKER_STATUS_BUSY}hidden{/if}"
				data-tooltip-side="bottom"
				data-tooltip-theme="light"
				data-tooltip-interactive="false"
				data-tooltip-text="{'<p>Есть активные кворки, заказы принимаются.</p><p><b>Внимание!</b><br /> Отказ от заказа из-за занятости снижает рейтинг продавца. Если вы не готовы принимать новые заказы, установите статус «Занят».</p>'|t}"></span>
		</div>
		{/if}
	</div>
	<div class="select-user-type select-user-type_small mt15 mb10">
		<input name="userType" onclick="changeUserType(1, true);" type="radio" id="1" value="1"
				{if $actor->type eq 'payer'}checked{/if}><label for="1"
				class="select-user-type_customer select-user-type-js">{'Покупатель'|t}</label>
		<input name="userType" onclick="changeUserType(2, true);" type="radio" id="2" value="2"
				{if $actor->type eq 'worker'}checked{/if}><label for="2"
				class="select-user-type_performer select-user-type-js">{'Продавец'|t}</label>
	</div>

	<ul class="user-menu-payer" {if $actor->type eq 'worker'}style="display:none"{/if}>
		<li><a href="{$baseurl}/orders" class="has-orders-count-as-payer">{'Мои заказы'|t}</a></li>
		<li><a href="{$baseurl}/manage_projects" class="js-has-wants-count-as-payer">{'Биржа'|t}</a></li>
		{if false && PromoBlackfridayManager::isPageShow() && Translations::getLang() == Translations::DEFAULT_LANG}
			<li><a href="{$baseurl}/blackfriday">{'Акция "Черная пятница"'|t}</a></li>
		{/if}
        {if $show2019CaseContestLink}
			{include file="contest/contest_2019_case_menu_link.tpl"}
        {/if}
		{if $actor && $actor->show_poll_notify && $canonicalUrl != $baseurl|cat:'/poll'}
			<li class="user-menu-payer__poll"><i class="icon ico-warningSmall poll-ico-warning"></i><a class="poll-warning-menu-link"
					href="{$baseurl}/poll?hash={$actor->pollHash}&type=menu">{'Оставьте мнение о Kwork'|t}</a>
			</li>
		{/if}
		{if false}
			<li><a href="{$baseurl}/prize_exchange"><b>{'Выиграй iPhone'|t}</b></a></li>
		{/if}
	</ul>

	<ul class="user-menu-worker" {if $actor->type eq 'payer'}style="display:none"{/if}>
		<li><a href="{$baseurl}/manage_kworks">{'Мои кворки'|t}</a></li>
		<li><a href="{$baseurl}/manage_orders" class="has-orders-count-as-worker">{'Заказы'|t}</a></li>
		<li><a href="{$baseurl}/projects">{'Биржа'|t}</a> <span class="italic f12 color-link-gray">{'+%s проектов за сутки'|tn:{WantManager::getNewWantsCount()}:{WantManager::getNewWantsCount()}}</span></li>
		{* #5627 contest *}
		{if false && \UserManager::isLanguageTester($actor->USERID) && Translations::getLang() == Translations::DEFAULT_LANG}
			<li><a href="{$baseurl}/prize">
					Розыгрыш iPhone 8
					{if \Contest\Contests\Contest2018PrizeManager::userSeePrizePage() != true}
					<div class="round-sign">!</div>
					{/if}
				</a>
			</li>
		{/if}
		{* end 5527 *}
        {if $actor->analytics_enable}
			<li><a href="{$baseurl}/analytics" class="active">{'Аналитика продаж'|t}</a></li>
        {/if}
        {if $show2019CaseContestLink}
            {include file="contest/contest_2019_case_menu_link.tpl"}
        {/if}
		{if false}
			<li><a href="{$baseurl}/prize_exchange"><b>{'Выиграй iPhone'|t}</b></a></li>
		{/if}
	</ul>

	<ul>
		{if $actor && ($actor->role == "moder" || $actor->role == "admin")}
			{assign var=countModerKwork value=ModerManager::getRedisKworkCountOnModer()}
			<li><a class="{if $countModerKwork > 100}color-red{/if}"
					href="{$baseurl}/moder_kwork">{'Модерация'|t} {if $countModerKwork}({$countModerKwork}){/if}</a>
			</li>
		{/if}
		
		<li class="divider"></li>
		{* @TODO: Перевести на роутинг *}
		<li><a href="/logout">{'Выход'|t}</a></li>
	</ul>
{/strip}