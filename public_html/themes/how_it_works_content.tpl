{strip}
<div class="how-it-works-content clearfix">
	<div class="icons t-align-c">
		{* step *}
		<div class="step outline-none">
			<div class="about-index_item_image">
				{if $pageSpeedMobile || ($pageSpeedDesktop && $pageName=="index")}
					<img src="{"/blank.png"|cdnImageUrl}" class="lazy-load_scroll" data-src="{"/howitworks_1.png"|cdnImageUrl}" width="100" height="100" alt="">
				{else}
					<img src="{"/howitworks_1.png"|cdnImageUrl}" width="100" height="100" alt="">
				{/if}
			</div>
			<h3 class="fontf-pnb mb10 ta-left fs18">{"Выберите услугу"|t}</h3>
			<p class="m-hidden">
				{"Десятки тысяч услуг в каталоге и&nbsp;отличные предложения на бирже"|t}
				<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover ml5"
					data-tooltip-text="{"Тысячи фрилансеров размещают свои услуги на сайте Kwork. Для поиска услуг используйте каталог, а также поисковую строку. Или разместите свой проект на бирже Kwork и получите множество предложений от профи."|t}"
					data-tooltip-theme="dark">?</span>
			</p>
			<p class="m-visible">
				{"Среди десятка тысяч услуг"|t}
			</p>
		</div>

		{* step *}
		<div class="step outline-none">
			<div class="about-index_item_image">
				{if $pageSpeedMobile || ($pageSpeedDesktop && $pageName=="index")}
					<img src="{"/blank.png"|cdnImageUrl}" class="lazy-load_scroll" data-src="{"/howitworks_2.png"|cdnImageUrl}" width="100" height="100" alt="">
				{else}
					<img src="{"/howitworks_2.png"|cdnImageUrl}" width="100" height="100" alt="">
				{/if}
			</div>
			<h3 class="fontf-pnb mb10 ta-left fs18">{"Оплатите"|t}</h3>
			<p>
				{"Один клик, и услуга<br>заказана"|t}
				<span class="m-hidden tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover ml5"
					data-tooltip-text="{"Нажмите кнопку «Купить» на странице услуги. Деньги будут перечислены продавцу после того, как он выполнит работу, и вы её одобрите."|t}"
					data-tooltip-theme="dark">?</span>
			</p>

			<i class="icon icon_info-ispolnitel ico-arrow-1 sm-hidden"></i>
			<i class="icon icon_info-ispolnitel ico-arrow-2 sm-hidden"></i>
		</div>

		{* step *}
		<div class="step outline-none">
			<div class="about-index_item_image">
				{if $pageSpeedMobile || ($pageSpeedDesktop && $pageName=="index")}
					<img src="{"/blank.png"|cdnImageUrl}" class="lazy-load_scroll" data-src="{"/howitworks_3.png"|cdnImageUrl}" width="100" height="100" alt="">
				{else}
					<img src="{"/howitworks_3.png"|cdnImageUrl}" width="100" height="100" alt="">
				{/if}
			</div>
			<h3 class="fontf-pnb mb10 ta-left fs18">{"Получите результат"|t}</h3>
			<p class="m-hidden">
				{"Качественный&nbsp;результат&nbsp;в&nbsp;срок и&nbsp;гарантия&nbsp;возврата&nbsp;средств"|t}
				<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover ml5"
					data-tooltip-text="{"Исполнители стараются работать быстро и качественно – от этого зависит их рейтинг и доходы. Если работа не выполнена в срок, вы можете отменить заказ в один клик."|t}"
					data-tooltip-theme="dark">?</span>
			</p>
			<p class="m-visible">
				{"Качественно и в срок"|t}
			</p>
		</div>
	</div>
</div>
{/strip}