{assign var=badReviewsCount value=$usersReviewsCounts[$offer->user_id]["bad"]}
{assign var=goodReviewsCount value=$usersReviewsCounts[$offer->user_id]["good"]}
{strip}
<div class="card offer-item js-offer-item {block name="offer_item_class"}{/block}"
	 data-id="{$offer->kwork_id}"
	 data-offer-id="{$offer->id}"
	 data-is-archived="{($offer->isNotActual() && $want->status !== 'archived')|intval}"
	 data-order-id="{$offer->order_id}"
	 data-project-id="{$offer->want_id}"
	 data-worker-id="{$offer->user_id}">
	<div class="card__content">
		<div class="card-teaser clearfix">
			<div class="offer-item__image-block newfoximg m-pull-reset m-center-block">
				{include file="user_avatar.tpl" profilepicture=$offer->user->profilepicture username=$offer->user->username size="big" class="user-avatar-image s100 user-avatar-square"}

				<div class="mb10">
					{insert name=is_online assign=is_online value=a userid=$offer->user_id}
					{if $is_online}
						<span class="dot-user-status dot-user-online"></span> {'Онлайн'|t}
					{else}
						<span class="dot-user-status dot-user-offline"></span> {'Оффлайн'|t}<br>
						<span class="fs13 dib ml16">
							({$offer->user->getLastOnline()|timeLeft:false:false})
						</span>
					{/if}
				</div>
			</div>
			<div class="offer-item__detail-block m-wMax">
				<div class="d-flex justify-content-between{if $offer->user->sumAmountOrderInOnYear} offer-item__income-wrapper{/if}">
					<a class="offer-item__title first-letter" href="{userProfileUrl($offer->user->username)}">
						{$offer->user->username|stripslashes|truncate:40:"...":true}
					</a>
					{if $offer->user->sumAmountOrderInOnYear}
						<div class="offer-item__income">
							<span class="js-payer-income fw700">{OfferManager::roundPayerIncome($offer->user->sumAmountOrderInOnYear)} {'руб'|t}</span>
							<span class="js-payer-income hidden fw700">{OfferManager::roundPayerIncome($offer->user->sumAmountOrderIn)} {'руб'|t}</span>
							&nbsp;<span class="color-gray">{'заработано'|t}</span>
						</div>
					{/if}
					<div class="offer-item__price">
						<div class="js-order-duration dib mr30">
							{insert name=declension value=a assign=days count=$offer->order->duration/Helper::ONE_DAY form1="день" form2="дня" form3="дней"}
							{$offer->order->duration/Helper::ONE_DAY}&nbsp;{$days}
						</div>
						<span class="js-order-total-price">
						{if $offer->order->currency_id == \Model\CurrencyModel::USD}
							<span class="usd">$</span>
						{/if}

						{if isAllowToUser($offer->order->USERID)}
							{$offer->order->price|zero}
						{else}
							{$offer->order->crt|zero}
						{/if}
						{if $offer->order->currency_id == \Model\CurrencyModel::RUB}
							&nbsp;<span class="rouble">Р</span>
						{/if}
						</span>
					</div>
				</div>
				<div class="offer-item__seller-info">
					<div class="offer-item__seller-count-orders">
						<b>{$offer->user->order_done_count}</b>&nbsp;
						<span>
							{declension count=$offer->user->order_done_count form1="{'заказ'|t}" form2="{'заказа'|t}" form5="{'заказов'|t}"}
						</span>
						{if $offer->alreadyWork && !$isMyOfferPage}
							<span class="kwork-icon icon-cart icon-cart-green tooltipster color-green ml5 fs20" data-tooltip-text="{"Ранее вы сотрудничали с данным пользователем. Последний заказ был"|t} {if $offer->alreadyWork == OrderManager::STATUS_DONE}{"успешно выполнен"|t}{else}{"сорван"|t}{/if}"></span>
						{/if}
					</div>
					{if $offer->user->order_done_count}
						<div class="percent-bars">
							<div class="percent-bar">
								<div class="percent-bar-wrap">
									{$offer->user->data->order_done_persent|round}%&nbsp;{'успешно сдано'|t}
									<div class="percent-bar__container" title="{$offer->user->data->order_done_persent|round}%">
										<div class="percent-bar__line" style="width: {$offer->user->data->order_done_persent|round}%"></div>
									</div>
								</div>&nbsp;
								<span 
									class="tooltipster kwork-icon icon-custom-help"
									data-tooltip-text="{'Процент повышается, когда продавец успешно выполняет заказы. Понижается, когда он отказывается от заказов по неуважительным причинам, или получает отрицательный отзыв.'|t}"
									data-tooltip-theme="dark"
								>
								</span>
							</div>
							<div class="percent-bar">
								{$offer->user->data->order_done_intime_persent|round}%&nbsp;{'сдано вовремя'|t}
								<div class="percent-bar__container" title="{$offer->user->data->order_done_intime_persent|round}%">
									<div class="percent-bar__line" style="width: {$offer->user->data->order_done_intime_persent|round}%"></div>
								</div>
							</div>
						</div>
					{/if}
					<div class="seller-reviews">
						<div class="seller-reviews__title">
							{'Отзывы:'|t}
						</div>
						<div class="seller-reviews__count">
							<div class="seller-reviews__count-item tooltipster"
								 data-tooltip-text="{'Положительных отзывов'|t}">
								<i class="ico-green-circle"></i>
								<span>{$goodReviewsCount}</span>
							</div>
							<div class="seller-reviews__count-item tooltipster"
								 data-tooltip-text="{'Отрицательных отзывов'|t}">
								<i class="ico-red-circle"></i>
								<span>{$badReviewsCount}</span>
							</div>
						</div>
						{if $offer->user->cache_rating}
						<div class="offer-item__seller-rating otherdetails tooltipster"
							 data-tooltip-text="{if $offer->user->cache_rating == 0}{'У продавца нет отзывов'|t}{else}{'Продавец имеет %s%% положительных отзывов'|t:$offer->user->cache_rating}{/if}">
							<span class="usercount"></span>
							<ul style="float: none;">
								{include file="rating_stars.tpl" rating=$offer->user->cache_rating}
							</ul>
						</div>
						{/if}
					</div>
				</div>
				<div class="clearfix">
					<div class="offer-item__info kworkDetails" data-loaded="0">
						<div class="js-offer-comment offer-item__description br-with-lh word-break-word cur" onclick="loadKworkDetails($(this));">
							{$offer->comment|stripslashes|html_entity_decode|strip_tags:$smarty.const.ENT_QUOTES:'utf-8'|replace_full_urls|strip_nl|nl2br}
							<span class="js-dots">...</span>
						</div>
						{block name="thumb_buttons"}{/block}
						<div style="display:none" class="preloader--project-container"
							 data-preloader-class="preloader__ico--project"></div>
						<div class="offer-item__more more" style="display:none;">
							{if !$offer->comment_doubles_description}
							<div class="js-more-description">
								<div class="offer-item__more-title">
									{if $offer->kwork->active == KworkManager::STATUS_CUSTOM}
										{'Описание предложения'|t}
									{else}
										{'Описание кворка'|t}
									{/if}
								</div>
	
								<div class="mt20 m-hidden"></div>
								<div class="mt10 m-visible"></div>
	
								<div class="offer-item__more-description js-description"></div>
							</div>
							{/if}
							<div class="mt10 m-hidden"></div>
							<div class="mt0 m-visible"></div>
							{if $offer->kwork->active != KworkManager::STATUS_CUSTOM}
								<div class="mb5">
									<strong>{'Что понадобится продавцу'|t}</strong>
								</div>
								<div class="instructions"></div>

								<p class="mt15">
									<b>{'Объем услуги при заказе одного кворка:'|t}</b> <span class="js-gwork"></span>
								</p>
								<div class="offer-item__more-files" style="display:none;">{'Файлы'|t}</div>
								<div class="files mt15"></div>
							{/if}
							<div class="offer-item__more-title">{'Что входит в предложение'|t}</div>
							<div class="track-order mt15"></div>
							{if $offer->kwork->good_comments_count || $offer->kwork->good_comments_count}
								<div class="offer-item__more-title">{'Отзывы'|t}</div>
								<div class="reviews js-reviews pb15 reviews_title_left"
									 data-more-btn-text="{'Еще отзывы'|t}">
								</div>
							{/if}
						</div>
					</div>
					<span class="js-description-button-hide link link_local link_arrow link_arrow_blue link_arrow_up dib mb10"
						  style="display:none;" onclick="hideKworkDetails($(this));">
							{'Свернуть описание'|t}
					</span>
					{include file="wants/common/offer_portfolio.tpl"}
				</div>
			</div>
		</div>
		{block name="bottom"}{/block}
	</div>
	<div class="clear"></div>
</div>
{/strip}

{include file="track/stages/stages_tooltip.tpl" isAllowToUser=isAllowToUser($offer->order->USERID) tooltipForOffers=true}