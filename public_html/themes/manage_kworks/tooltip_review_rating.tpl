<div class="kwork-tooltip {if !$post.statistics.review_rating.level} without-donut {/if}">
	<div class="kwork-tooltip__head">
		{'Отзывы'|t}
		<span> {'(не более %s последних заказов)'|t:UserStatisticManager::LIMIT_REVIEW_ORDERS_COUNT}</span>
	</div>

	<div class="kwork-tooltip__body">
		<div class="analytics__donut-block-values">
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_REVIEW_GOOD stat="review_rating"}
				<div class="donut-block__title">{'Положительные'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_REVIEW_BAD stat="review_rating"}
				<div class="donut-block__title">{'Отрицательные'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_REVIEW_EMPTY stat="review_rating"}
				<div class="donut-block__title">{'Без отзыва'|t}</div>
			</div>
			<div class="donut-block-values__item">
				{include file="manage_kworks/tooltip_metric_value.tpl" metric=UserStatisticManager::METRIC_REPEAT_SALES_WO_REVIEWS stat="review_rating"}
				<div class="donut-block__title">{'Без отзыва, повторные или с бонусами'|t}</div>
			</div>
		</div>

		<div class="analytics__donut">
			<div class="js-analytics__donut--reviews"></div>
			<div class="analytics__caption">
				{'Отзывы'|t}
				<br>
				<div class="analytics-value__level">
                    {include file="manage_kworks/level_colored.tpl" level=$post.statistics.review_rating.level}
				</div>
			</div>
		</div>

		<div class="analytics-tooltip__text">
			<p>{'От качества отзывов зависит, насколько высоко будут отображаться ваши кворки покупателям, а следовательно, и количество заказов.'|t}</p>
			<div class="analytics-tooltip__title">{'Что повышает?'|t}</div>
			<ul class="pl20">
				<li>{'Положительные отзывы.'|t}</li>
				<li>{'Без отзыва, повторные заказы или с бонусами (указывают на доверие покупателей).'|t}</li>
			</ul>
			<div class="analytics-tooltip__title">{'Что снижает?'|t}</div>
			<ul class="pl20 {if $post.statistics.review_rating.level}mb5{/if}">
				<li>{'Отрицательные отзывы. К ним же относятся отмены заказов после просрочек и отмены покупателем по причине "Продавец не может корректно выполнить заказ" (вносят большой негативный вклад в рейтинг).'|t}</li>
			</ul>
			<p>				
				{capture assign=coloredPercent}
					<span class="bold analytics-value--{$post.statistics.review_rating.level}">
						{$post.statistics.review_rating.user_percent.good}%
					</span>
				{/capture}
				{assign var=goodBad value=Translations::t("лучше")}
				{if $post.statistics.review_rating.user_percent.good == 100}
					{assign var=goodBad value=Translations::t("равны или лучше")}
				{/if}
				{'Отзывы на этот кворк %s, чем у %s других кворков в рубрике "%s"'|t:$goodBad:$coloredPercent:$post.category_name}
			</p>
		</div>
	</div>
</div>
