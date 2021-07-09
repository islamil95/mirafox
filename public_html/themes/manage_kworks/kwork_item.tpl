{strip}
	<div class="w780 card manage-kworks-item sm-margin-reset kwork-wrap js-kwork-analytics js-kwork-analytics-{$post.PID}" data-kwork-id="{$post.PID}">
		<div class="d-flex flex-nowrap newfoxwrapper manage-kworks-item__inner">
			<div class="newfoximg responsivefoximages manage-kworks-item__left-part">
				<a href="{if $post.active == KworkManager::STATUS_DRAFT}{$baseurl}/new?draft_id={$post.PID}{else}{$baseurl}{$post.url}{/if}" class="ispinner-container">
					{include file="_blocks/thumbnail_img_load.tpl" spinnerMode="lite"}
					{if $post.photo}
						{assign var="imgClass" value=""}
						{if $post.is_resizing == 0}
							{assign var="imageSize" value="t2"}
						{else}
							{assign var="imageSize" value="t0"}
							{$sizeImage = \CImage::getSizeImage("{$purl}/{$imageSize}/{$post.photo}")}
							{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
								{assign var="imgClass" value="isHorizontalImg"}
							{/if}
						{/if}
						<img src="{$purl}/{$imageSize}/{$post.photo}" {photoSrcset($imageSize, $post.photo)} alt="" class="db {$imgClass}" onload="removeISpinner(event)">
					{else}
						<img src="{"/collage/640x357/collage_default_category.jpg"|cdnImageUrl}" alt="" class="db centered-image" onload="removeISpinner(event)">
					{/if}
				</a>
			</div>
			<div class="newfoxdetails pli0 ml15 m-m0 relative manage-kworks-item__right-part {if $post.active != KworkManager::STATUS_DRAFT && $post.is_need_update_price || $post.is_need_update_packages || $post.is_need_update_package_prices || $post.is_need_update || $post.is_need_update_volume || $post.is_need_update_translates || $post.is_need_add_portfolio || $post.outsider_reason_hint }manage-kworks-item-left-bottom-line{/if}">
				<div class="manage-kworks-item__top-part">
					<h3 class="manage-kworks-item__title">
						<a id="kwork_name_{$post.PID}" href="{if $post.active == KworkManager::STATUS_DRAFT}{$baseurl}/new?draft_id={$post.PID}{if $post.lang == Translations::EN_LANG}&lang={Translations::EN_LANG}{/if}{else}{$baseurl}{$post.url}{/if}" {if strlen($post.gtitle) > 85}title="{$post.gtitle|stripslashes|upstring}"{/if}>
							<span class="dib">{$post.gtitle|ucfirst|stripslashes|mb_truncate:85:"...":'UTF-8'}</span>
						</a>
						<i class="change-kwork-name-js fa fa-pencil tooltipster" data-tooltip-text="{'Изменить название услуги'|t}" rel="{$post.PID}"></i>
					</h3>
					<div class="manage-kworks-item__top-right-part" >
						<div class="fw600 color-green_2 nowrap fs20 mt-4 manage-kworks-item__price">
							{include file="utils/currency.tpl" lang=$post.lang total=$post.price - $post.ctp}
						</div>
						<div class="manage-kworks-item__controls">
							{if !$isSuspend}
								<div class="hidden">
									{if $post.active == KworkManager::FEAT_ACTIVE}
										<input class="checkbox approved styled-checkbox {if !empty($post.has_offers)}has-offers{/if}" id="gig_{$post.PID}" name="gig[]" type="checkbox" value="{$post.PID}" />
									{else}
										<input class="checkbox inactive styled-checkbox" id="gig_{$post.PID}" name="gig[]" type="checkbox" value="{$post.PID}" />
									{/if}
									<label for="gig_{$post.PID}" class="pl20">&nbsp;</label>
								</div>
								<div class="newfoxdetails-controls mt-1">
									<a href="#delete" {if $post.active == KworkManager::STATUS_DRAFT}onclick="delete_draft_confirm({$post.PID}); return false;"{/if} class="dib {if $post.active != KworkManager::STATUS_DRAFT}btn-delete-gigs{else}btn-delete-draft{/if} autocheck tooltipster" data-twin="{$post.twin_id}" data-tooltip-text="{'Удалить'|t}" data-tooltip-theme="dark">
										<i class="kwork-icon icon-bin"></i>
									</a>
								</div>
								<div class="newfoxdetails-controls mt-1">
									{if $post.active != KworkManager::STATUS_DRAFT}
										<a href="{$baseurl}/edit?id={$post.PID}" class="btn-edit btn-edit-js dib tooltipster" data-tooltip-text="{'Изменить'|t}" data-tooltip-theme="dark">
											<i class="kwork-icon icon-pencil"></i>
										</a>
									{else}
										<a href="{$baseurl}/new?draft_id={$post.PID}{if $post.lang == Translations::EN_LANG}&lang={Translations::EN_LANG}{/if}" class="btn-edit btn-edit-js dib tooltipster" data-tooltip-text="{'Изменить'|t}" data-tooltip-theme="dark">
											<i class="kwork-icon icon-pencil"></i>
										</a>
									{/if}
								</div>
							{/if}
						</div>
					</div>
				</div>
				<div class="mh92 m-mh-initial">
					{if $post.active != KworkManager::STATUS_DRAFT}
					<div class="d-flex justify-content-between">
						<div class="m-mb10 m-mr10" style="flex-basis: 50%">
							<div class="f13 m-f11 bold mb5">
								{'Активность'|t}
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5">
								<span data-tooltip-text="{'Общая информация, сколько раз кворк был просмотрен, куплен и на какую сумму.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
								</span>
							</div>
							<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Просмотры:'|t}
								{if $post.viewcount > 0}
									<span>&nbsp;{$post.viewcount|stripslashes|zero}</span>
								{else}
									<span> 0</span>
								{/if}
							</div>

							<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Продажи:'|t}
								{if $post.done_order_count > 0}
									<span>&nbsp;{$post.done_order_count|zero}</span>
								{else}
									<span> 0</span>
								{/if}
							</div>

							<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Заработано:'|t}
								{if $post.rev > 0}
									<span class="nowrap">&nbsp;{include file="utils/currency.tpl" lang=$actor->lang total=$post.rev}</span>
								{else}
									<span>&nbsp;0</span>
								{/if}
							</div>
						</div>
						{if $post.statistics.done_orders_percent.done_orders_percent || $post.statistics.review_rating.level || ($post.rotation_conversion && $post.statistics.conversion.level && $post.done_order_count >= UserStatisticManager::USER_PERCENTS_ORDER_DONE_THRESHOLD)}
						<div style="flex-basis: 50%">
							<div class="f13 m-f11 bold mb5">
								{'Рейтинг кворка'|t}
								<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5">
										<span data-tooltip-text="{'Чем выше показатели рейтинга, тем лучше ваш кворк ранжируется в каталоге и больше покупателей видят и заказывают его.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle tooltip_circle--scale-16 dib tooltipster tooltip_circle--light tooltipstered">?</span>
									</span>
							</div>
							{if $post.statistics.done_orders_percent.done_orders_percent}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Ответственность'|t}
									<div class="dib tooltipster nowrap js-kwork-analytics-tooltip"
										 data-tooltip-content="#done-orders-percent-{$post.PID}"
										 data-tooltip-side="bottom"
										 data-tooltip-delay="300"
										 data-kwork-id="{$post.PID}"
										 data-tooltip-width="auto"
										 data-metric="{UserStatisticManager::METRIC_DONE_ORDERS_PERCENT}">
										<div style="display: none;">
											<div id="done-orders-percent-{$post.PID}" style="max-width: 660px; padding: 10px 5px;">
												{include file="manage_kworks/tooltip_done_orders_percent.tpl"}
											</div>
										</div>
										{if $post.statistics.done_orders_percent.level}
											<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
												<span class="tooltip_circle tooltip_circle--scale-16 tooltip_circle--light ">?</span>
											</span>
										{else}
											<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
												<span data-tooltip-text="{'Не хватает данных. Показатель будет рассчитан после нескольких заказов.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
											</span>													
										{/if}
									</div>
									{': '}
									<div class="dib fw600 js-analytics__level" data-metric="{UserStatisticManager::METRIC_DONE_ORDERS_PERCENT}">
										{if $post.statistics.done_orders_percent.level}
											<span class="analytics-value--{$post.statistics.done_orders_percent.level}">
												{$post.statistics.done_orders_percent.user_percent.good}%
												{' '}
												{include file="manage_kworks/level_colored.tpl" level=$post.statistics.done_orders_percent.level}
											</span>
										{else}
											{'Н/Д'|t}
										{/if}
									</div>
								</div>
							{else}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Ответственность'|t}
									<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5">
										<span data-tooltip-text="{'Не хватает данных. Показатель будет рассчитан после нескольких заказов.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
									</span>
									{': '}
									<span class="fw600">{'Н/Д'|t}</span>
								</div>
							{/if}
							{if $post.statistics.review_rating.level}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Отзывы'|t}
									<div class="dib tooltipster nowrap js-kwork-analytics-tooltip"
										 data-tooltip-content="#review-rating-{$post.PID}"
										 data-tooltip-side="bottom"
										 data-tooltip-delay="300"
										 data-kwork-id="{$post.PID}"
										 data-tooltip-width="auto"
										 data-metric="{UserStatisticManager::METRIC_REVIEW_RATING}">
										<div style="display: none;">
											<div id="review-rating-{$post.PID}" style="max-width: 660px; padding: 10px 5px;">
												{include file="manage_kworks/tooltip_review_rating.tpl"}
											</div>
										</div>
										<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
											<span class="tooltip_circle tooltip_circle--scale-16 tooltip_circle--light ml5">?</span>
										</span>
									</div>
									{': '}
									<div class="dib fw600 js-analytics__level analytics-value--{$post.statistics.review_rating.level}" data-metric="{UserStatisticManager::METRIC_REVIEW_RATING}">
										{$post.statistics.review_rating.user_percent.good}%
										{' '}
										{include file="manage_kworks/level_colored.tpl" level=$post.statistics.review_rating.level}
									</div>
								</div>
							{else}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Отзывы'|t}
									<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
										<span data-tooltip-text="{'Не хватает данных. Показатель будет рассчитан после нескольких заказов.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
									</span>
									{': '}
									<span class="fw600">{'Н/Д'|t}</span>
								</div>
							{/if}
							{if $post.rotation_conversion && $post.statistics.conversion.level && $post.done_order_count >= UserStatisticManager::USER_PERCENTS_ORDER_DONE_THRESHOLD}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Конверсия'|t}
									<div class="dib tooltipster nowrap js-kwork-analytics-tooltip"
										 data-tooltip-side="bottom"
										 data-tooltip-width="auto"
										 data-tooltip-delay="300"
										 data-tooltip-content="#conversion-{$post.PID}">
										<div style="display: none;">
											<div id="conversion-{$post.PID}" style="max-width: 660px; padding: 10px 5px;">
												{include file="manage_kworks/tooltip_conversion.tpl"}
											</div>
										</div>
										{if $post.rotation_conversion}
											<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
												<span class="tooltip_circle tooltip_circle--scale-16 tooltip_circle--light">?</span>
											</span>
										{else}
										<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5">
											<span data-tooltip-text="{'Не хватает данных. Показатель будет рассчитан после нескольких заказов.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
										</span>
										{/if}
									</div>
									{': '}
									<div class="dib fw600 js-analytics__level" data-metric="conversion">
										{if $post.rotation_conversion}             
											<span class="analytics-value--{$post.statistics.conversion.level}">
											{$post.statistics.conversion.user_percent.good}%
											{' '}
											{include file="manage_kworks/level_colored.tpl" level=$post.statistics.conversion.level}
											</span>
										{else}
											{'Н/Д'|t}
										{/if}
									</div>
								</div>
							{else}
								<div class="f13 m-f11 mt3 manage-kworks-item__index">{'Конверсия'|t}
									<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16 ml5 mr5">
										<span data-tooltip-text="{'Не хватает данных. Показатель будет рассчитан после нескольких заказов.'|t}" data-tooltip-side="right" data-tooltip-interactive="true" class="tooltip_circle dib tooltipster tooltip_circle--scale-16 tooltip_circle--light tooltipstered">?</span>
									</span>
									{': '}
									<span class="fw600">{'Н/Д'|t}</span>
								</div>
							{/if}
						</div>
						{/if}
					</div>
				{/if}
				</div>
				{if $post.active != KworkManager::STATUS_DRAFT && ($post.is_need_update_price || $post.is_need_update_packages || $post.is_need_update_package_prices || $post.is_need_update || $post.is_need_update_volume || $post.is_need_update_translates || $post.is_need_add_portfolio || $post.outsider_reason_hint) }
					<div class="manage-kworks-item_underline"></div>
				{/if}
			</div>
		</div>

		{if $post.active != KworkManager::STATUS_DRAFT && ($post.is_need_update_price || $post.is_need_update_packages || $post.is_need_update_package_prices || $post.is_need_update || $post.is_need_update_volume || $post.is_need_update_translates || $post.is_need_add_portfolio || $post.outsider_reason_hint) }
			<div class="mb15">
				{if $post.active != KworkManager::STATUS_DRAFT}
					{if $post.is_need_update_price}
						{include file="manage_kworks/tooltip_price.tpl" assign="tooltip"}
						<div class="mb10 color-red f12 f-normal">
							{'Пересмотрите объем услуг и цену в кворке.'|t}&nbsp;
							<span style="border-bottom:1px dashed;cursor:pointer;" class="tooltipster"
								  data-tooltip-text="{$tooltip|replace:'"':'&quot;'}">{'Подробнее'|t}</span>
						</div>
					{elseif $post.is_need_update_packages}
						{include file="manage_kworks/tooltip_packages.tpl" assign="tooltip"}
						<div class="mb10 color-red f12 f-normal">
							{'Отредактируйте кворк, иначе его не увидят покупатели.'|t}&nbsp;
							<span style="border-bottom:1px dashed;cursor:pointer;" class="tooltipster"
								  data-tooltip-text="{$tooltip|replace:'"':'&quot;'}">{'Подробнее'|t}</span>
						</div>
					{elseif $post.is_need_update_package_prices}
						{include file="manage_kworks/tooltip_package_prices.tpl" kworkLang="{$post.lang}" assign="tooltip"}
						<div class="mb10 color-red f12 f-normal">
							{'Отредактируйте стоимость пакетов Эконом и Бизнес.'|t}&nbsp;
							<span style="border-bottom:1px dashed;cursor:pointer;" class="tooltipster"
								  data-tooltip-text="{$tooltip|replace:'"':'&quot;'}">{'Подробнее'|t}</span>
						</div>
					{else}
						{if $post.is_need_update || $post.is_need_update_volume || $post.is_need_update_translates || $post.is_need_add_portfolio}
							{if $post.is_need_update_volume}
								{include file="manage_kworks/tooltip_volume.tpl" assign="tooltip"}
							{elseif $post.is_need_update_translates}
								{include file="manage_kworks/tooltip_need_update_translate.tpl" assign="tooltip"}
							{elseif $post.is_need_fill_attribute}
								{include file="manage_kworks/tooltip_fill_attributes.tpl" assign="tooltip"}
							{elseif $post.is_need_add_portfolio}
								{assign var=customUpdateNotice value=Translations::t("Добавьте больше работ в портфолио.")}
								{include file="manage_kworks/tooltip_fill_portfolio.tpl" assign="tooltip"}
							{else}
								{include file="manage_kworks/tooltip_need_update.tpl" assign="tooltip"}
							{/if}
							<div class="mb10 color-red f12 f-normal">
								{$customUpdateNotice|default:(Translations::t("Требуется срочно обновить описание."))}&nbsp;
								<span style="border-bottom:1px dashed;cursor:pointer;" class="tooltipster"
									  data-tooltip-text="{$tooltip|replace:'"':'&quot;'}">{"Почему?"|t}</span>
							</div>
						{/if}
					{/if}
				{/if}

				{if $post.outsider_reason_hint}
					<div class="kwork-outsider-reason-hint color-green_2 f12 f-normal">
						{'Этот кворк будет отображаться намного выше в каталоге, если'|t} {$post.outsider_reason_hint}
					</div>
				{/if}
			</div>
		{/if}	
	</div>
{/strip}