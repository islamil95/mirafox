{extends file="wants/common/offer_item.tpl"}
{block name="bottom"}
	<div class="offer-item__bottom offer-item__bottom--wide">
		{include file="wants/worker/offers/offer_status.tpl"}
		<div>
		{if $offer->isCanEdit()}
			<a class="js-query-item__edit mr10" href="/edit_offer?project={$offer->want_id}">
				<i class="icon ico-edit"></i>
			</a>
		{/if}
		<a class="js-query-item__delete" data-id="{$offer->id}">
			<i class="icon ico-trash-18"></i>
		</a>
		</div>
	</div>
{/block}