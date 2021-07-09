<div class="kwork-tooltip">
	<div class="kwork-tooltip__body">
		<div class="analytics-tooltip__text">
			<div class="analytics-tooltip__title">{'Конверсия продаж'|t}</div>
			<p>{'Это отношение суммарной стоимости заказов кворка к числу зарегистрированных покупателей, просмотревших этот кворк. Чем чаще и на большие суммы покупатели заказывают ваш кворк после его просмотра, тем выше конверсия.'|t}</p>
			<div class="analytics-tooltip__title">{'На что влияет конверсия?'|t}</div>
			<p>{'На ваши продажи и доход. Кворк с хорошей конверсией отображается выше в каталоге, а значит, больше новых покупателей увидят и закажут его.'|t}</p>
			<div class="analytics-tooltip__title">{'Как повысить конверсию?'|t}</div>
			<p>{'Продавайте чаще, зарабатывайте больше:'|t}</p>
			<ol>
				<li style="margin-left: -10px;">{'Убедите покупателя, что ваш кворк – лучший выбор. Обеспечьте броский заголовок, продающую обложку кворка, хорошее описание, портфолио.'|t}</li>
				<li style="margin-left: -10px;">{'Оказывайте больше услуг за более высокую стоимость: предлагайте доп.опции, не отказывайтесь от дополнительной работы за дополнительную плату.'|t}</li>
			</ol>
			{if $post.statistics.conversion}
			<div>
				<p>
					{capture assign=coloredPercent}
						<span class="bold analytics-value--{$post.statistics.conversion.level}">
						{$post.statistics.conversion.user_percent.good}%
					</span>
					{/capture}
					{assign var=goodBad value=Translations::t("лучше")}
					{if $post.statistics.conversion.user_percent.good == 100}
						{assign var=goodBad value=Translations::t("равна или лучше")}
					{/if}
					{'Ваша конверсия %s, чем у %s продавцов в рубрике "%s".'|t:$goodBad:$coloredPercent:$post.category_name}
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>
