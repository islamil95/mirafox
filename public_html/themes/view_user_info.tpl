{strip}
	<div class="gray-bg-border p15-20 mt20 view-user-info-mobile">
		{include file="view_portfolio_info.tpl" user=$kwork}
		{if $userRating > 0}
			<hr class="gray">
			<div class="clearfix">
				<div class="pull-right cusongsblock-panel__rating m-pull-reset ">
					<ul class="rating-block cusongsblock-panel__rating-list dib v-align-m">
						{control name=rating_stars rating=$userRating}
					</ul>
				</div>
				<span class="f14 dib">{'Репутация'|t}</span>
			</div>
		{/if}

		<hr class="gray">
		<div class="clearfix mt15">
			<div class="pull-right f14">{$kwork.order_done_count}</div>
			<div class="f14">{'Выполнено заказов'|t}</div>
		</div>
		<hr class="gray">
		<div class="clearfix mt15">
			<div class="pull-right f14">
				<i class="ico-green-circle dib v-align-m mr5"></i>
				<span class="dib v-align-m mr5">{$allGoodReviews}</span>
				<i class="ico-red-circle dib v-align-m ml5 mr5"></i>
				<span class="dib v-align-m">{$allBadReviews}</span>
			</div>
			{if $foxtotalvotes > 0}{$foxtotalvotes} {declension count=$foxtotalvotes form1="оценка в заказах" form2="оценки в заказах" form5="оценок в заказах"}{else}{'Нет оценок'|t}{/if}
		</div>
		<hr class="gray">
		<div class="clearfix mt15">
			<div class="pull-right f14">{$kwork.queueCount}</div>
			{'Заказов в очереди'|t}
		</div>
		<hr class="gray">
		<div class="f14">
			{insert name=city_id_to_name value=a assign=userc countryId=$kwork.country_id id=$kwork.city_id}{$userc}
		</div>
		<div class="mt10 f14">
			{'На сайте с'|t}  {$kwork.addtime|date_format:"%e %B %Y":'':'auto'}
		</div>
		{if !$actor}
			<div class="white-btn w30p mt10 f14 signup-js" style='width: 100%; padding: 7px 15px;'>
				<i class="icon ico-mail-green v-align-m mr7"></i>
				<i class="icon ico-mail-white v-align-m mr7"></i>
				<span class="v-align-m">{'Связаться cо мной'|t}</span>
			</div>
		{elseif isNotAllowUser($kwork.USERID)}
			<div class="button-contact-with-me white-btn w30p mt10 f14"
				 style='width: 100%; padding: 7px 15px;'
				 data-kworkId="{$kwork.PID}"
				 data-workername="{$kwork.username}">
				<i class="icon ico-mail-green v-align-m mr7"></i>
				<i class="icon ico-mail-white v-align-m mr7"></i>
				<span class="v-align-m">
						{'Связаться cо мной'|t}
					</span>
			</div>
			<div class="clear"></div>
			{*Контент для попапа*}
			<div class="js-popup-show-tie-warning__container hidden">
				<div class="icon ico-i-small dibi"></div>
				<h1 class="dib ml5 popup__title mb5">{'Рекомендация'|t}</h1>
				<hr class="gray mt5 mb15">
				<div>
					<p>{'Лучше не использовать личные сообщения (ЛС) перед заказом. Почему?'|t}</p>
					<ul class="ml20 mt15 mb15">
						<li class="mb5">{'Продавец может начать взвинчивать цену на нужный вам объем работы.'|t}</li>
						<li>{'Высокая вероятность отказа или игнорирования. ЛС ни к чему не обязывает продавца. А оформленный заказ должен быть выполнен продавцом, иначе его рейтинг снизится.'|t}</li>
					</ul>
					<p class="mt10">{'Кстати, при необходимости заказ легко можно дополнить доп. опциями или кворками уже в процессе работы.'|t}</p>
					<div class="popup__buttons mt10">
						<button class="popup__button popup__button-tie popup-close-js green-btn">{'Закрыть'|t}</button>
						<button class="popup__button popup__button-tie pull-right white-btn" onclick="location.href = '/conversations/{$kwork.username}?kworkId={$kwork.PID}&goToLastUnread=1';">{'Написать продавцу'|t}</button>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}