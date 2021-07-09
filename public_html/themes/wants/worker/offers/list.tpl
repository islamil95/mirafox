{Helper::printCssFile("/css/dist/offers.css"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/offers.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/dist/offers.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/pages/project.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/jquery.reviewWidget.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/urlparams.js"|cdnBaseUrl)}

<div class="wants-filter-content">
	<div>
		<div class="wants-filter">
			<a href="{absolute_url route="projects_worker"}" class="wants-filter__link">
				{'Проекты'|t}
			</a>
			<a href="{absolute_url route="offers"}" class="wants-filter__link active">
				{'Мои предложения'|t}
			</a>
		</div>
	</div>
	<div class="wants-content">
		<div>
			{foreach from=$offers item=offer}
				<div class="offer-want-container js-offer-want-container mb20">
					{include file="wants/worker/wants_list_item_offers.tpl" want=$offer->want}
					<div class="js-offer-container" style="display: none;">
						{include file="wants/worker/offers/worker_offer_item.tpl" }
					</div>
				</div>
			{/foreach}
		</div>
		<div style="text-align:center;" class="mb10">
			{$offers->links()}
		</div>
	</div>
</div>