{strip}
	<div class="js-popup-delete-rating__container hidden">
		<form method="post"
			  action="{route route="review_remove" params=["orderId" => $order->OID]}">
			<h2>{'Удаление отзыва'|t}</h2>
			<input type="hidden" name="action" value="payer_remove_rating">
			<input type="hidden" name="orderId" value="{$order->OID}">
			<input type="hidden" name="ratingId" value="{$order->review->RID}">
			<hr class="gray">
			{if $order->review->answer && !$order->hasAutoRating()}
				<p>{'Вы хотите удалить отзыв?'|t}
				<p class="mb10">{'Ответ на отзыв также будет удалён.'|t}
			{else}
				<p class="mb10">{'Вы хотите удалить отзыв?'|t}
			{/if}
			<div class="popup__buttons">
				<button type="button" class="popup__button white-btn popup-close-js">{'Отменить'|t}</button>
				<button type="submit" class="popup__button green-btn pull-right">{'Удалить'|t}</button>
				<div class="clearfix"></div>
			</div>
		</form>
	</div>
{/strip}