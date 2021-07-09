<div class="track__wrap-review-form">
	<h2 class="pt10 pb15">{if $order->isCancel()}{'Заказ отменён, вы можете оставить отзыв'|t}{else}{'Заказ выполнен, вы можете оставить отзыв'|t}{/if}</h2>
	{include file='../view/review/review_form.tpl' method="addReview" allowedTypes=$canWriteReview}
</div>
<script>
	$(function () {
		var button = $(".btn-disable-toggle");
		$("#message_body").on("input", function () {
			if (StopwordsModule._testContacts($(this).val()).length == 0) {
					button.removeAttr("disabled").removeClass("disabled");
			} else {
					button.prop("disabled", true).addClass("disabled");
			}
		});
	})
</script>