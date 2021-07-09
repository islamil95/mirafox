{if !isset($reviewType)}
	{assign var=reviewType value=1}
{/if}
<form method="post"
	  action="{route route="review_create" params=["orderId" => $order->OID]}"
	  class="review-form__form send-review-js ajax-disabling"
	  data-form="send-review-js"
	  data-method="{$method}"
	  data-action="{$order->OID}/{$method}"
	  data-append-comment=".wrap-answer-{$order->OID}"
	  data-remove-selectors=".track__wrap-review-form">
	<div class="rating-row">
		<div class="rating-block-review">
			{if $allowedTypes == RatingManager::CAN_ADD_REVIEW_ALL}
				<div class="rating-block-review_good{if $reviewType == 1} active{/if}"></div>
				<div class="rating-block-review_bad{if $reviewType != 1} active{/if}"></div>
			{elseif $allowedTypes == RatingManager::CAN_ADD_REVIEW_BAD}
				<div class="rating-block-review_bad active"></div>
			{/if}
		</div>
		<div class="rating-block-tooltip">
			{'Напишите развернутый отзыв. Такие отзывы помогают вам и другим покупателям выбирать лучших продавцов.'|t}
		</div>
	</div>
	{if $admincsrftoken}
		<input type="hidden" name="admincsrftoken" value="{$admincsrftoken}" />
	{/if}
	{if $allowedTypes == RatingManager::CAN_ADD_REVIEW_ALL}
		<input type="radio" class="hide" name="vote" id="rating-block-review_good-js" value="1"{if $reviewType == 1} checked{/if}>
		<input type="radio" class="hide" name="vote" id="rating-block-review_bad-js" value="0"{if $reviewType != 1} checked{/if}>
	{elseif $allowedTypes == RatingManager::CAN_ADD_REVIEW_BAD}
		<input type="radio" class="hide" name="vote" id="rating-block-review_bad-js" value="0" checked>
	{/if}
	<div class="review-row">
		<div class="js-message-body review-text server-sided"
			 contenteditable="true"
			 spellcheck="false"
			 placeholder="{'Отзыв:'|t}{"\n"}{'1. Какая у вас была задача или проблема?'|t}{"\n"}{'2. Как вы оцениваете результат?'|t}{"\n"}{'3. Понравилась ли вам работа исполнителя?'|t}"
			 name="comment"
			 data-max-count="{\RatingManager::COMMENT_MAX_CHARACTERS_COUNT}">{if $reviewText}{\Helper::nl2p($reviewText)}{/if}</div>
		<div class="field-error"></div>
	</div>
	<div class="clear"></div>
	<div class="submit-row mt20">
		<div>
			<input type="submit" class="hugeGreenBtn hoverMe GreenBtnStyle h50 w210 pull-reset review-submit" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('NEW-REVIEW'); return true; }" value="{'Отправить'|t}">
		</div>
		<div class="variants">
			<div class="write-row">
				<div>{'Вы написали:'|t} <span class="counter"><span class="count">0</span> / {\RatingManager::COMMENT_MAX_CHARACTERS_COUNT}</span></div>
				<ul class="loadbar">
					{section name=percent start=0 loop=100 step=10}
						<li>
							<div class="progress-bar"></div>
						</li>
					{/section}
				</ul>
			</div>
			<div class="review-tooltip">
				<div class="variant bad">{'Отзыв будет ценнее,<br />если вы добавите подробностей'|t}</div>
				<div class="variant normal">{'Хорошее начало! Ваша оценка скорости и качества работы точно пригодится'|t}</div>
				<div class="variant good">{'Отлично! Такие развернутые отзывы помогают выбирать лучшие кворки!'|t}</div>
			</div>
		</div>
	</div>
</form>