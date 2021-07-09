{strip}
<div class="modal unshowed popup-confirm-order fade{if $order->kwork->getPortfolioType() == "none"} is-allow-portfolio{/if}" tabindex="-1" role="dialog" id="confirm_inprogress_done_popup_content" style="display: none;">
	<div class="modal-dialog modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">				
			<div class="modal-body">
                {if $order->status == OrderManager::STATUS_INPROGRESS || $order->status == OrderManager::STATUS_ARBITRAGE}
					<form action="{absolute_url route="track_payer_inprogress_done"}" method="post" class="js-track-form__popup-confirm-form">
                {else}
					<form action="{absolute_url route="track_payer_check_done"}" method="post" class="js-track-form__popup-confirm-form">
				{/if}
					<input type="hidden" name="orderId" value="{$order->OID}">
					<input type="hidden" name="stageIds" value="">
					<input type="hidden" name="message" value="">
					<div class="d-flex flex-nowrap align-items-center">
						<div class="popup-confirm-order__wrap-img">
							<img class="popup-confirm-order__img" src="{"/confirm_order.png"|cdnImageUrl}" srcset="{"/confirm_order@2x.png"|cdnImageUrl} 2x" alt="Заказ подтвержден!" />
						</div>
						<div class="popup-confirm-order__content">
							<h1 class="mb20 f30 fw600">{'Принять и оплатить заказ'|t}</h1>
							<div class="mb17 f18 fw600">{'Подтверждаю, что:'|t}</div>
							<ul class="popup-confirm-order__kwork-list kwork-list kwork-list_type-2">
								<li class="kwork-list__item">
									{'Заказ выполнен в полном объеме'|t}
								</li>
								<li class="kwork-list__item">
									{'К результату заказа претензий нет'|t}
								</li>
								<li class="kwork-list__item">
									{'Понимаю, что после оплаты арбитраж невозможен'|t}
								</li>
							</ul>
							{if $order->kwork->getPortfolioType() != "none"}
								{*Разрешено ли составлять портфолио в категории для данного заказа*}
								<div>
									<input id="allow_portfolio_item" name="allow_portfolio_item" type="checkbox" value="1" class="styled-checkbox" checked>
									<label for="allow_portfolio_item">{"Разрешить публикацию работы в портфолио продавца"|t}</label>
								</div>
							{/if}
							<div class="d-flex justify-content-between flex-wrap mt22 kwork-buttons popup-confirm-order__kwork-buttons">
								<button type="button" class="kwork-button kwork-button__lg kwork-button_theme_orange-bordered" data-dismiss="modal">{'Отменить'|t}</button>
								<button type="button" class="kwork-button kwork-button__lg kwork-button_theme_green-filled js-track-form__popup-confirm-submit">{'Оплатить'|t}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			<button type="button" class="modal-close modal-close_lg" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
</div>
{/strip}