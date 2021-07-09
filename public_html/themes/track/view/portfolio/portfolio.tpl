{strip}
	{if isAllowToUser($track->order->worker_id) && $track->isNotClose() && $portfolioItem}
		{*загружен ли файл для портфолио*}
		<div id="track-id-{$track->MID}" class="tr-track step-block-order_item step-block-order_item-portfolio" data-track-id="{$track->MID}">
			<div class="f14 color-gray mt3 t-align-r">{$date|date}</div>
			<div class="t-align-c">
				<i class="ico-check"></i>
				<h3 class="pt10 font-OpenSansSemi track-green">
					{'Портфолио добавлено'|t}
				</h3>
				<div class="f15 {if !$config.track.isFocusGroupMember}mt15{/if}">
					{if $track->isDone()}
						{'Результат работы добавлен в портфолио'|t}
					{elseif $track->isNew()}
						{'Мы запросили у покупателя разрешение на публикацию результатов в портфолио. Если покупатель согласится на публикацию или не примет решение в течение недели, ваша работа автоматически появится в портфолио.'|t}
					{/if}
				</div>
				{include file="portfolio/upload/card-list.tpl" type="portfolios" maxCount="1" sortable="unsortable" uneditable=!$canSendPortfolio}
			</div>
		</div>
	{/if}
{/strip}
