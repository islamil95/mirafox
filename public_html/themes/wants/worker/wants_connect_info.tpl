{if $actor}
	<div class="connects-points{if $have100PercentPenalty} connects-points--two-item{/if}">
		<div class="connects-points__point tooltipster" data-tooltip-content=".connects-tooltip">
			<div class="connects-points__item">
				<div class="connects-points__item-count">{$connect["connect_points"]|intval}</div>
				<div class="connects-points__item-text">{"Доступно коннектов"|t}</div>
				{if $connect["connect_points"] < 6}
					<div class="dib p5 f13 nowrap cur--default tooltipster"
						 style="margin-bottom: -5px;"
						 data-tooltip-text="{"Как повысить количество заказов и получать больше коннектов? Ознакомьтесь со %sстатьей%s."|t:"<a href='/faq?role=5&article={if Translations::isDefaultLang()}454{else}466{/if}'>":"</a>"}">
						{"Не хватает коннектов?"|t}
					</div>
				{/if}
			</div>
			{if !$have100PercentPenalty}
				<div class="connects-points__item">
					<div class="connects-points__item-count">{UserConnectManager::getRefillDateFormat()}</div>
					<div class="connects-points__item-text">{"Дата пополнения"|t}</div>
				</div>
			{/if}
		</div>
		<div class="connects-points__email connects-points__item connects-points__item--vcenter">
			<div class="offer-sprite offer-sprite-mail"></div>
			<a class="js-link-email-notification connects-points__item-link" href="javascript:;">{"Настройка email-уведомлений"|t}</a>
			{if $connect["connect_points"] < 6 && !$have100PercentPenalty}
				<div class="w100p p5 f13 m-hidden">&nbsp;</div>
			{/if}
		</div>
	</div>

	<div class="hidden">
		<div class="connects-tooltip">
			<p>{"Отправка каждого предложения покупателю требует одного коннекта. Чтобы отправить предложения по двум проектам, нужно иметь два коннекта и т.д. Количество коннектов ограничено и зависит от вашего уровня продавца"|t}:</p>
			<p><b>{"Новичок"|t}</b> – {UserConnectManager::CONNECT_FOR_NOVICE} {"коннектов"|t}</p>
			<p><b>{"Продвинутый"|t}</b> – {UserConnectManager::CONNECT_FOR_ADVANCED} {"коннектов"|t}</p>
			<p><b>{"Профессионал"|t}</b> – {UserConnectManager::CONNECT_FOR_PROFESSIONAL} {"коннектов"|t}</p>
			<p>{"Каждые 30 дней коннекты автоматически возобновляются. Старайтесь делать максимально качественные предложения покупателям, чтобы повысить продажи."|t}</p>

			{if !is_null($connect["connect_points_return_date"])}
				<p>
					{'Добавлены коннекты, поскольку увеличился процент успешных заказов %s.'|t:{Helper::dateFormat($connect["connect_points_return_date"], "%e %B")}}

					{if $connect["isPenalised"]}
						{'Оставшиеся штрафы по коннектам перечислены ниже.'|t}
					{/if}
				</p>
			{/if}

			{* Общий заголовок для снижения конектов в текущем месяце и о снижении статистики на 100% в текущем *}
			{if $connect["isPenalised"] || $have100PercentPenalty}
				<p><b style="color: red">{"Снижение лимита коннектов"|t}</b> (<a href="{$baseurl}/faq?role=5&amp;article=442" target="_blank">{"Подробнее..."|t}</a>)</p>
			{/if}

			{if $connect["isPenalised"]}
				<p>{"Ваш лимит коннектов снижен по причинам:"|t}</p>
				{if $connect["connect_penalty_orders_done"]}
					<p>- {"<b>Успешных заказов %d&#37;</b> - минус %d&#37; коннектов"|t:$connect["connect_orders_done"]:$connect["connect_penalty_orders_done"]}</p>
				{/if}
				{if $connect["connect_penalty_spam"]}
					<p>- {"<b>Спам в предложениях</b> - минус %d коннектов"|t:$connect["connect_penalty_spam"]}</p>
				{/if}
				{if $connect["connect_penalty_contacts_exchange"]}
					<p>- {"<b>Обмен контактами</b> - минус %d коннектов"|t:$connect["connect_penalty_contacts_exchange"]}</p>
				{/if}
			{/if}

			{if $have100PercentPenalty && $connect["connect_penalty_orders_done"] != 100}
				<p>{"Ваш лимит коннектов будет снижен %s по причинам:"|t:(UserConnectManager::getRefillDateFormat())}</p>
				<p>- {"<b>Успешных заказов %d&#37;</b> - минус 100&#37; коннектов"|t:$connect["order_done_persent"]}</p>
			{/if}
		</div>
	</div>

	<div class="js-popup-email-notification email-notification popup--fixed hidden">
		<div class="overlay overlay-disabled js-popup-close"></div>

		<div class="popup_content popup-w500">

			<h2 class="popup__title pr20">{'Настройка email-уведомлений'|t}</h2>
			<hr class="gray">
			<div class="js-popup-close pull-right popup-close cur">X</div>
			<div class="email-notification__form">
				<div class="form-group">
					<input {if $notificationChecked != -1}checked {/if} name="email-notification" id="email-notification-yes" type="radio" value="yes" class="js-email-notification-yes styled-radio">
					<label for="email-notification-yes">{'Получать уведомления (<strong>рекомендуем</strong>)'|t}</label>
				</div>

				{foreach from=$notificationSettings item=notification}
					<div class="form-group {if $notificationChecked == -1 && $notification.value != -1}hidden{/if} {if $notification.value != -1} ml20 {/if}">
						<input {if $notification.value == $notificationChecked}checked {/if}
							   name="notification-period"
							   class="styled-radio js-email-notification-period"
							   id="notification-{$notification.value}" type="radio" value="{$notification.value}" >
						<label for="notification-{$notification.value}">{$notification.name2}</label>
						{if $notification.value == -1}
							<div class="form-warning {if $notificationChecked != -1}hidden{/if}">{'Вы лишаете себя возможности быть в курсе актуальных проектов на бирже. Рекомендуем включить уведомления и выбрать удобный график.'|t}</div>
						{/if}
					</div>
				{/foreach}

				<div class="t-align-c">
					<a class="js-save-email-notification-periods form-button green-btn btn--big m-wMax w250" href="javascript:;">{'Готово'|t}</a>
				</div>
			</div>
		</div>
	</div>

	{if ($connect["isPenalised"] || $have100PercentPenalty) && $connect["connect_points"] <= 0 }
		<div class="js-popup-penalty-orders popup-penalty-orders modal fade" tabindex="-1" role="dialog" data-backdrop="static"
			 data-keyboard="false" style="display: none;">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title">
							{'Упс...'|t}
						</h1>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						{if $connect["isPenalised"]}
							<p>{'Ваш лимит коннектов временно снижен, поэтому вы не можете отправить предложение покупателю.'|t}</p>
						{else}
							<p>{'Ваш лимит коннектов временно исчерпан, поэтому вы не можете отправить предложение покупателю.'|t}</p>
						{/if}

						{if $connect["isPenalised"]}
							<p class="mt5">{'Почему лимит снижен:'|t}</p>
							<ul class="ml17">
								{if $connect["connect_penalty_orders_done"]}
									<li class="mt5">{"<b>Успешных заказов %d&#37;</b> - минус %d&#37; коннектов"|t:$connect["connect_orders_done"]:$connect["connect_penalty_orders_done"]}</li>
								{/if}
								{if $connect["connect_penalty_spam"]}
									<li class="mt5">{"<b>Спам в предложениях</b> - минус %d коннектов"|t:$connect["connect_penalty_spam"]}</li>
								{/if}
								{if $connect["connect_penalty_contacts_exchange"]}
									<li class="mt5">{"<b>Обмен контактами</b> - минус %d коннектов"|t:$connect["connect_penalty_contacts_exchange"]}</li>
								{/if}
							</ul>
						{/if}

						{* Если в следующем месяце будет 100% списание коннектов и нет списаний в текущем за плохую статистику *}
						{if $have100PercentPenalty && $connect["connect_penalty_orders_done"] != 100}
							<p class="mt5">{"Важно! Ваш лимит коннектов будет снижен %s по причине:"|t:(UserConnectManager::getRefillDateFormat())}</p>
							<ul class="ml17">
								<li class="mt5">{"<b>Успешных заказов %d&#37;</b> - минус 100&#37; коннектов"|t:$connect["order_done_persent"]}</li>
							</ul>
						{/if}

						<p class="mt5">
							{* Если в следующем месяце будет 100% списание коннектов *}
							{if $have100PercentPenalty && $connect["connect_penalty_orders_done"] != 100}
								{'Коннекты пополнятся %s, если вы устраните причину снижения коннектов.'|t:UserConnectManager::getRefillDateFormat()}

								{* если есть списание по плохой статистике *}
							{elseif $connect["connect_penalty_orders_done"]}
								{'Коннекты пополнятся, как только вы устраните причину снижения коннектов.'|t}

								{* если есть штрафы за спам и обмен контактоам *}
							{elseif $connect["connect_penalty_spam"] || $connect["connect_penalty_contacts_exchange"]}
								{'Коннекты пополнятся %s.'|t:UserConnectManager::getRefillDateFormat()}
							{/if}
							<a href="{$baseurl}/faq?role=5&article=442" target="_blank">{'Узнайте больше...'|t}</a>
						</p>

						<div class="popup__buttons t-align-c">
							<button class="popup__button green-btn" data-dismiss="modal">
								{"ОК"|t}
							</button>
						</div>

					</div>
				</div>
			</div>
		</div>
	{/if}
{/if}
<script>
	var connectPoints = {$connect["connect_points"]|intval};
</script>
