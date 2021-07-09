{* если есть отзыв *}
{strip}
	<div id="track-id-{$order->review->RID}"
		 class="tr-track step-block-order_item"
		 data-track-id="{$order->review->RID}">
		<div class="f14 color-gray mt3 t-align-r">{$order->review->time_added|date}</div>
		<div class="t-align-c">
			<i class="ico-reating-{if $order->review->good == 1}good{else}bad{/if}"></i>
			<h3 class="pt10 font-OpenSansSemi {if $order->review->good == 1}track-green{else}track-red{/if}">
				{if $order->review->auto_mode}
					{'Автоматический отзыв'|t}
				{else}
					{'Отзыв по заказу'|t}
				{/if}
			</h3>
			<div class="f15 mt15 m-pt10">
				{if isAllowToUser($order->worker_id)}
					{if $order->review->good == 1}
						{'%s оставил положительный отзыв по этому заказу'|t:$order->payer->username}
					{else}
						{'%s оставил отрицательный отзыв по этому заказу'|t:$order->payer->username}
					{/if}
				{else}
					{if $order->review->good == 1}
						{'Вы оставили положительный отзыв по этому заказу'|t}
					{else}
						{'Вы оставили отрицательный отзыв по этому заказу'|t}
					{/if}
				{/if}
				<br>
				{if $order->review->auto_mode}
					{RatingManager::formatText({$order->review->commentHtml()|t}, $order->review->auto_mode, $order->hasPaidStages())}
				{else}
					{$order->review->commentHtml()|nl2br}
				{/if}
			</div>
			{if ( isAllowToUser($order->USERID) || UserManager::isModer()) && RatingManager::inEditTime($order->review->RID) }
				<i class="js-kwork-comment-remove__link icon ico-trash-18 mb-5 mr5"></i>
				<a class="js-kwork-comment-remove__link kwork-comment-remove__link"
				   href="javascript:void(0);">
					{'Удалить отзыв'|t}
				</a>
				<i class="js-kwork-comment-edit__link icon ico-edit mb-5 mr5 ml15"></i>
				<a class="f14 dib mt10 kwork-comment-edit__link {if UserManager::isModer()}mr10{/if}"
				   href="javascript:void(0);">
					{'Редактировать отзыв'|t}
				</a>
				<div class="bgLightGray p15-20 mt10" style="display: none">
					{include file='./review_form.tpl' method="editReview" allowedTypes=$editTypeReview reviewType=$order->review->good reviewText=$order->review->comment}
				</div>
				{include file='track/popup/remove_rating.tpl'}
			{/if}
			{if !$order->review->answer && isAllowToUser($order->worker_id)}
				<a class="f14 dib mt10"
				   style="cursor:pointer;"
				   onclick="$('.rating_comment').toggle();">
					{'Ответить на отзыв'|t}
				</a>
			{/if}
		</div>
	</div>
	{if !$order->review->answer}
		{if isAllowToUser($order->worker_id)}
			{* Оставить ответ на отзыв *}
			<div class="bgLightGray p15-20 rating_comment" style='display:none;'>
				<form method="post"
					  class="send-review-js"
					  action="{route route="review_create_comment" params=["orderId" => $order->OID]}"
					  data-action="{route route="review_create_comment" params=["orderId" => $order->OID]}"
					  data-append-comment=".wrap-answer-{$order->OID}">
					<h2 class="pt10 f18">
						{if $order->review->auto_mode}
							{'Вы можете оставить ответ на автоматический отзыв'|t}
						{else}
							{'Вы можете оставить ответ на отзыв покупателя'|t}
						{/if}
					</h2>
					<input type="hidden" name="review_id" value="{$order->review->RID}">

					<div class="review-row">
						<div class="js-message-body review-text server-sided"
							 contenteditable="true"
							 spellcheck="false"
							 placeholder="{'Текст отзыва'|t}"
							 name="comment"
							 data-max-count="300"></div>
						<div class="field-error"></div>
					</div>
					<div class="clear"></div>
					<div class="submit-row mt20">
						<div>
							<input type="submit"
								   class="hugeGreenBtn hoverMe GreenBtnStyle h50 w210 pull-reset review-submit"
								   onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('NEW-REVIEW'); return true; }"
								   value="{'Отправить'|t}">
						</div>
						<div class="variants">
							<div class="write-row">
								<div>{'Вы написали:'|t} <span class="counter"><span class="count">0</span> / 300</span></div>
								<ul class="loadbar">
									{section name=percent start=0 loop=100 step=10}
										<li>
											<div class="progress-bar"></div>
										</li>
									{/section}
								</ul>
							</div>
						</div>
					</div>
				</form>
			</div>
		{/if}
	{elseif $order->review->answer->showToCurrentUser()}
		{* если есть ответ на отзыв(редаккирование ответа на отзыв) *}
		<div id="track-id-{$order->review->RID}" class="tr-track step-block-order_item"
			 data-track-id="{$order->review->RID}">
			<div class="f14 color-gray mt3 t-align-r">{$order->review->answer->time_added|date}</div>
			<div class="t-align-c">
				<i class="ico-reating-good"></i>
				<h3 class="pt10 font-OpenSansSemi track-green">{'Ответ на отзыв'|t}</h3>
				<div class="f15 mt15">
					{if isAllowToUser($order->worker_id)}
						{'Вы оставили ответ на отзыв по этому заказу'|t}
					{else}
						{'Продавец ответил на ваш отзыв'|t}
					{/if}
					<br>
					<i>{$order->review->answer->message|stripslashes|nl2br}</i>
					{if $order->review->answer->status == Model\RatingComment::STATUS_NEW}
						<br/>
						<h5 class="track-green"><i>Ответ направлен на модерацию.</i></h5>
                    {elseif $order->review->answer->status == Model\RatingComment::STATUS_REJECT}
						<br/>
						<h5 class="track-red"><i>Ответ отклонен модерацией.</i></h5>
                    {/if}
				</div>
				{if isAllowToUser($order->worker_id) && $order->review->answer->time_added > time() - 2 * Helper::ONE_WEEK}
					<a class="f14 dib mt10 kwork-rating-comment-edit__link"
					   href="javascript:void(0);">{'Редактировать ответ'|t}</a>
					<div class="bgLightGray" style='display: none'>
						<form method="post"
							  action="{route route="review_update_comment" params=["orderId" => $order->OID]}"
							  class="rating_comment send-review-js"
							  data-action="{route route="review_update_comment" params=["orderId" => $order->OID]}"
							  data-append-comment=".wrap-answer-{$order->OID}">
							<div class="review-row">
								<div class="js-message-body review-text server-sided"
									 contenteditable="true"
									 spellcheck="false"
									 placeholder="{'Текст отзыва'|t}"
									 name="comment"
									 data-max-count="300">{\Helper::nl2p($order->review->answer->message)}</div>
								<div class="field-error"></div>
							</div>
							<div class="clear"></div>
							<div class="submit-row mt20">
								<div>
									<input type="submit"
										   class="hugeGreenBtn hoverMe GreenBtnStyle h50 w210 pull-reset review-submit"
										   value="{'Редактировать'|t}">
								</div>
								<div class="variants">
									<div class="write-row">
										<div>{'Вы написали:'|t} <span class="counter"><span class="count">0</span> / 300</span></div>
										<ul class="loadbar">
											{section name=percent start=0 loop=100 step=10}
												<li>
													<div class="progress-bar"></div>
												</li>
											{/section}
										</ul>
									</div>
								</div>
							</div>
						</form>
					</div>
				{/if}
			</div>
		</div>
	{/if}
{/strip}