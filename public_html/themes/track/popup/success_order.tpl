{strip}
{$isNotAvatar = $actor->profilepicture === 'noprofilepicture.gif'}
<div class="modal popup-success-order fade{if $isNotAvatar} is-not-avatar{/if}" tabindex="-1" role="dialog" id="success_order_popup_content" style="display: none;">
	<div class="modal-dialog modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">				
			<div class="modal-body modal-body">		
				<h1 class="popup-success-order__title f30 fw600 t-align-c">{'Заказ выполнен!'|t}</h1>
				{if !$isNotAvatar}
					<img class="popup-success-order__avatar" src="{"/large/{$actor->profilepicture}"|cdnMembersProfilePicUrl}" alt="">
				{/if}
				<ul class="popup-success-order__list">
					<li>
						<img src="{"/stopwatch.svg"|cdnImageUrl}" alt="">
						<p>{'Время выполнения заказа'|t}</p>
						<p class="popup-success-order__list__title js-popup-success-order__work-time"></p>
					</li>
					<li>
						<img src="{"/to-do-list.svg"|cdnImageUrl}" alt="">
						<p>{'Вы завершили'|t}</p>
						<p class="popup-success-order__list__title js-popup-success-order__count-orders"></p>
					</li>
					<li>
						<img class="js-popup-success-order__user-badge" src="" width="38" height="38" alt="">
						<p>{'Ваш уровень'|t}</p>
						<p class="popup-success-order__list__title js-popup-success-order__level"></p>
						<p class="js-popup-success-order__next-level-text"></p>
					</li>
				</ul>
				<div class="f15 t-align-c pb15">{'Не забудьте оценить продавца'|t}</div>
				<button type="button" class="kwork-button kwork-button__lg kwork-button_theme_green-filled w100p" data-dismiss="modal">{'Готово!'|t}</button>
			</div>
		</div>
	</div>
</div>
{/strip}