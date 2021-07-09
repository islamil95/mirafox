<div class="mb10 want-payer-statistic">
	{include file="user_avatar.tpl" profilepicture=$user->profilepicture username=$user->username size="big" class="user-avatar-image s65 user-avatar-square"} <div class="dib v-align-t ml10">
		<div>
			<div class="dib">
				{'Покупатель'|t}: <a class="v-align-t" href="{$baseurl}/user/{$user->username}">{$user->username}</a>&nbsp;
			</div>
			{if $want->alreadyWork || $offer->alreadyWork}
				<span class="kwork-icon icon-cart icon-cart-green tooltipster color-green fs20" data-tooltip-text="{"Ранее вы сотрудничали с данным пользователем. Последний заказ был"|t} {if $want->alreadyWork == OrderManager::STATUS_DONE || $offer->alreadyWork == OrderManager::STATUS_DONE}{"успешно выполнен"|t}{else}{"сорван"|t}{/if}"></span>
			{/if}
		</div>
	</div>
</div>
