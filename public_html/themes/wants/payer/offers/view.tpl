{extends file="page_with_user_header.tpl"}

{block name="styles"}
	{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/project.css"|cdnBaseUrl)}
	{Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{/block}

{block name="scripts"}
	{Helper::registerFooterJsFile("https://www.youtube.com/iframe_api")}
	{Helper::registerFooterJsFile("/js/portfolio_view_popup.js"|cdnBaseUrl)}

	{Helper::printJsFile("/js/caret.js"|cdnBaseUrl)}
	{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/project.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/pages/project.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/jquery.reviewWidget.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/rolldown-text.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/wants-portfolio.js"|cdnBaseUrl)}
{/block}

{strip}
	{block name="content"}
		<div class="project clearfix">
			<h1>{'Детали фриланс проекта'|t}</h1>

			<div class="block-response mb25">
				{if $actor}
					<i class="ico-arrow-left dib v-align-m"></i>
					<a class="dib v-align-m f14 color-gray color-text" href="{if $isUserWant}{absolute_url route="manage_projects"}{else}{absolute_url route="projects_worker"}{/if}">{'К списку проектов'|t}</a>
				{/if}
			</div>

			<div id="foxPostForm" class="p0">
				<div class="border-b-none">
					{include file="wants/payer/offers/want.tpl"}

					{if $isUserWant}
						<a id="offers-anchor"></a>
						{if !$offers->count() && count($offersNotActual) == 0 && in_array($want->status, [WantManager::STATUS_CANCEL, WantManager::STATUS_STOP, \Model\Want::STATUS_USER_STOP])}
							<div class="clearfix mb20"></div>
						{else}
							<div class="mt25 clearfix">
								<div style="position:relative;">
									{if $actor && $actor->isVirtual}
										<div class="income-filter-wrapper">
											<h2 class="f26 js-header-list-offers">{'Предложения фрилансеров'|t}</h2>
											<div class="income-filter">
												<span class="income-filter__title">{'Заработок продавца'|t}</span>
												<select class="js-income-filter select-styled select-styled--thin long-touch-js income-filter__select">
													<option value="year" selected>{'за год'|t}</option>
													<option value="all">{'за все время'|t}</option>
												</select>
											</div>
										</div>
									{else}
										<h2 class="mb25 f26 js-header-list-offers">{'Предложения фрилансеров'|t}</h2>
									{/if}

									{if !$offers->count()}
										<div class="info-block info-block_project f14 lh22 mb10">
											<p>{'Пока предложений нет. Сейчас проект размещен на бирже, и фрилансеры изучают его. Скоро появятся первые предложения.'|t}</p>
										</div>
									{/if}

									{if !$actor->order_count}
										{include file="wants/payer/offers/alert.tpl"}
									{/if}
								</div>
							</div>

							{if $offers->count() > 3}
								{include file="wants/payer/offers/sort_block.tpl"}
							{/if}
							<div class="offers js-offer-list">
								<input type="hidden" name="wantLang" value="{$want->lang}">
								{foreach $offers as $offer}
									{include file="wants/payer/offers/payer_offer_item.tpl"}
								{/foreach}
								<a class="js-offer-item-cancel-link offers__cancel-link {if !$hasHidedOffers} hidden{/if}" href="javascript:;"></a>

								{if count($offersNotActual) > 0}
								<div class="offers__archived-link-wrap">
									<a class="js-offer-item-archive-link offers__archived-link" href="javascript:;">{'Показать архивные предложения'|t}</a>
								</div>

								<div class="js-offer-item-archive-block offers__archived">
									{foreach $offersNotActual as $offer}
										{include file="wants/payer/offers/payer_offer_item.tpl"}
									{/foreach}
								</div>
								{/if}
							</div>

							{if $actor->order_count}
								{include file="wants/payer/offers/alert.tpl"}
							{/if}
						{/if}
					{else}
						<div class="clearfix mb20"></div>
					{/if}
				</div>
			</div>
		</div>

		{include file="wants/payer/offers/stage-modal.tpl"}

        {if $config.chat.isFocusGroupMember}
            {include file="popup/individual_message.tpl"}
        {/if}
	{/block}
{/strip}

{literal}
	<script>
{/literal}
		{if $balance_popup == 1}
			show_balance_popup("{$balance_popup_amount}", "project");
		{/if}
{literal}
	</script>
{/literal}
{literal}
	<script>
		var isWorker = 0;
		var controlEnLang = {/literal}{$controlEnLang|intval}{literal};
		var offer = {
			lang: "{/literal}{$offerLang}{literal}",
			isOrderStageTester: {/literal}{$isStageTester|intval}{literal},
			kworkPackages: {/literal}0{literal},
			maxKworkCount: {/literal}0{literal},
			multiKworkRate: {/literal}{OrderManager::getMultiKworkRate($want->category)}{literal},
			customMinPrice: {/literal}{$customMinPrice}{literal},
			customMaxPrice: {/literal}{$customMaxPrice}{literal},
			stageMinPrice: {/literal}{$stageMinPrice}{literal},
			offerMaxStages: {/literal}{$offerMaxStages}{literal},
			stagesPriceThreshold: {/literal}{$stagesPriceThreshold}{literal},
			customPricesOptionsHtml: null
		};
	</script>
{/literal}
