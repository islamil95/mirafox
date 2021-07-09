{strip}
	{if ($want->category_id == 59 && $want->price_limit == 750) || $want->price_limit == 500}
		{$priceName = 'Цена:'|t}
	{else}
		{$priceName = 'Цена до:'|t}
	{/if}

	<div class="card want-card js-card-{$want->id} js-want-container{if $date_view} js-viewed{/if}" data-id="{$want->id}">
		<div class="card__content pb5">
			<div class="mb15">
				<div class="wants-card__header">
					<div class="wants-card__header-title first-letter breakwords">
						<a href="{absolute_url route="view_offers_all" params=["id" => $want->id]}">{$want->name|stripslashes}</a>
					</div>
					<div class="wants-card__header-right-block">
						<div class="wants-card__header-controls">
							{if $want->price_limit > 0}
								<div class="wants-card__header-price wants-card__price m-hidden">
									<span class="fs12">{$priceName}</span> {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
								</div>
							{/if}
						</div>
					</div>
				</div>
				{if \Translations::isDefaultLang() && $want->lang == \Translations::EN_LANG}
					<div style="float: right; background-color: #ffb63c; color: #ffffff; padding: 4px; border-radius: 5px;" title="Покупатель общается на английском. Напишите свое предложение на английском.">EN</div>
				{/if}
				<div class="mt10 br-with-lh">
					{if $want->desc|mb_strlen < 245}
						<div class="breakwords first-letter f14 lh22">
							{$want->desc|replace_full_urls|stripslashes|strip_nl|nl2br}
						</div>
					{else}
						<div class="breakwords first-letter f14 js-want-block-toggle lh22">
							{$want->desc|stripslashes|strip_nl|truncate:245|replace_full_urls|nl2br}&nbsp;
							<a href="javascript:void(0);" class="js-want-link-toggle-desc link_local">{"Показать полностью"|t}</a>
						</div>
						<div class="breakwords first-letter f14 js-want-block-toggle lh22 hidden">
							{$want->desc|replace_full_urls|stripslashes|strip_nl|nl2br}&nbsp;
							<a href="javascript:void(0);" class="js-want-link-toggle-desc link_local">{"Скрыть"|t}</a>
						</div>
					{/if}
				</div>
				{if $want->price_limit > 0}
					<div class="wants-card__header-price wants-card__price mt10 m-visible">
						<span class="fs12">{$priceName}</span> {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
					</div>
				{/if}
			</div>
			{include file="wants/common/want_payer_statistic.tpl" user=$want->user}
			<div class="ta-right">
					<div class="query-item__info mb10 ta-left" style="padding-top: 8px;">
					{insert name=countdown_short value=a assign=timeLeft time=(strtotime($want->date_expire)) type="deadline"}
					{if $timeLeft}{'Осталось:'|t} {$timeLeft} &nbsp;&nbsp;&nbsp;{/if}{'Предложений:'|t} {$want->kwork_count}
				</div>
				<div class="query-seen_block dib {if is_null($date_view) || !$date_view}hide{/if} mr10">
					<img src="{"/ico-galka-green.png"|cdnImageUrl}" alt="" />
					<span>{'ПРОСМОТРЕНО'|t}</span>
				</div>
                {if $actor}
					<a class="m-wMax m-h50 {if $canAddOfferStatus !== true}js-link-popup-warning-profile{elseif $ifPenaltyOrders}js-link-penalty-orders{/if} green-btn projects-offer-btn {if $actor->kwork_allow_status == "deny" || $waitPenaltyMessage}denied{/if}"
					   href="/new_offer?project={$want->id}">
                        {'Предложить услугу'|t}
					</a>
                {else}
					<a class="m-wMax m-h50 green-btn projects-offer-btn offer-signup-js" href="javascript: void(0);">
                        {'Предложить услугу'|t}
					</a>
                {/if}
			</div>
			{if $hasCardFooter}
				<div class="clear"></div>
				<div class="query-card__footer mb8 clearfix">
					<div class="query-card__breadcrumbs block-response">
						<span class="query-card__breadcrumb-item">{$want->category->parentCategory->name}</span>
						<span class="query-card__breadcrumb-item">{$want->category->name}</span>
					</div>
					<div class="clear m-visible mt10"></div>
					<div class="color-gray query-card__date pull-right m-pull-reset">
						{if $want->status eq 'new'}
							{'создан'|t} {$want->date_create|date}
						{else}
							{'активен до'|t} {$want->date_expire|date}
						{/if}
					</div>
				</div>
			{/if}
		</div>
	</div>
{/strip}