<div class="kwork-tooltip {if !$post.statistics.done_orders_percent.level} without-donut {/if}">
	<div class="kwork-tooltip__head">
		{'Ответственность'|t}
		<span>{'(не более %s последних заказов)'|t:UserStatisticManager::LIMIT_RESPONSIBILITY_ORDERS_COUNT}</span>
	</div>

	<div class="kwork-tooltip__body">
		<div class="analytics__donut-block-values">
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_DONE_ORDERS stat="done_orders_percent"}
				<div class="donut-block__title">{'Выполненные'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_CANCELLATION stat="done_orders_percent"}
				<div class="donut-block__title">{'Отказы'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_AUTO_CANCEL stat="done_orders_percent"}
				<div class="donut-block__title">{'Автоотмена'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_EXPIRATION_CANCEL stat="done_orders_percent"}
				<div class="donut-block__title">{'Просрочка с отменой'|t}</div>
			</div>
		</div>

		<div class="analytics__donut">
			<div class="js-analytics__donut--cancellation"></div>
			<div class="analytics__caption">
				{'ответственность'|t}
				<br>
				<div class="analytics-value__level">
                    {include file="manage_kworks/level_colored.tpl" level=$post.statistics.done_orders_percent.level}
				</div>
			</div>
		</div>

		<div class="analytics-tooltip__text">
			<p>{'Чем выше ответственность, тем выше отображаются ваши кворки покупателям, и тем лучше идут продажи.'|t}</p>
			<div class="analytics-tooltip__title">{'Что повышает ответственность?'|t}</div>
			<ul class="pl20">
				<li>{'Выполненные заказы.'|t}</li>
			</ul>
			<div class="analytics-tooltip__title">{'Что снижает?'|t}</div>
			<ul class="pl20 {if $post.statistics.done_orders_percent.user_percent}mb5{/if}">
				<li>{'Отказы от выполнения заказов без уважительной причины.'|t}</li>
				<li>{'Автоотмена заказа, если работа над заказом не была начата в течение %s.'|t:Helper::autoCancelString(Helper::AUTOCANCEL_MODE_TEXT_IN)}</li>
				<li>{'Решение арбитража в пользу покупателя.'|t}</li>
				<li>{'Отмена заказа по причине просрочки.'|t}</li>
			</ul>
			<p>
				{capture assign=coloredPercent}
					<span class="bold analytics-value--{$post.statistics.done_orders_percent.level}">
						{$post.statistics.done_orders_percent.user_percent.good}%
					</span>
				{/capture}
				{assign var=goodBad value=Translations::t("лучше")}
				{if $post.statistics.done_orders_percent.user_percent.good == 100}
					{assign var=goodBad value=Translations::t("равна или лучше")}
				{/if}
				{'Ваша ответственность %s, чем у %s продавцов в рубрике "%s".'|t:$goodBad:$coloredPercent:$post.category_name}
			</p>
		</div>
	</div>
</div>
