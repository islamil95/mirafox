{strip}
	{if isset($portfolioItems[$offer->user_id]) && count($portfolioItems[$offer->user_id])}
		<div class="wants-portfolio pt20 clearfix" id="portfolio-{$offer->user_id}">
			<h2 class="offer-item__title mb20">{'Портфолио'|t}</h2>
			{if isset($usersPortfolioCategories[$offer->user_id]) && count($usersPortfolioCategories[$offer->user_id]) >= 3}
				<div class="user-portfolio__panel mb20 clearfix">
					<div class="offer-item__wants-portfolio__wrapper-style-select wrapper-style-select w210">
						<select class="offer-item__wants-portfolio__filter" name="portfolio-filter[{$offer->user_id}]" onchange="WantsPortfolio.changeCat({$offer->user_id})">
							<option value="0">{'Все рубрики'|t}</option>
							{foreach $usersPortfolioCategories[$offer->user_id] as $catId}
								<option value="{$catId}" {if $catId == $portfolioSelectedCategory[$offer->user_id]}selected{/if}>{$categoryNames[$catId]}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/if}
			<div class="portfolio-list-collage-wrapper portfolio-list-collage-wrapper_sm">
				<div class="portfolio-list-collage offer-item__wants-portfolio__portfolio-list-collage" data-user="{$offer->user_id}">
					{* Сюда добавляются элементы портфолио методом WantsPortfolio.load *}
					{include file="wants/common/offer_portfolio_list.tpl" items=$portfolioItems[$offer->user_id]}
				</div>
				<input type="hidden" name="curPage[{$offer->user_id}]" value="1">
				<div class="offer-item__wants-portfolio__button-panel clearfix ta-center {if !$haveNext[$offer->user_id]}hide{/if}">
					<button class="btn_show-more {if $haveNext[$offer->user_id]}show{/if}">{'Показать еще'|t}</button>
					<div class="wrap-btn_collapse">
						<span class="btn_collapse link link_local link_arrow link_arrow_blue link_arrow_up">{'Свернуть'|t}</span>
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}
