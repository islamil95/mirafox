{strip}
	<span class="db mt30">
		<span class="review-avatar t-align-r kwork-review-avatar">
			{include file="user_avatar.tpl" profilepicture=$review.answer.worker_profilepicture username=$review.answer.worker_username size="medium"}
		</span>
		<span class="f14 comment">
			<span class="comment-meta">
				<a href="{$baseurl}/{insert name=get_seo_profile value=a username=$review.answer.worker_username|stripslashes}" class="fs14">{$review.answer.worker_username|stripslashes}</a>
				<span class="ml8 f13 m-db m-m0">{insert name=time_ago assign=cntd value=a days=0 time=$review.answer.time_added}{$cntd} {'назад'|t}</span>
			</span>
			<span class="comment-comment lh22 f13">
				{Helper::formatText($review.answer.message|stripslashes)}
			</span>
			{if $review.answer.canEdit}
				<a onclick="$(this).parent().find('.editForm').toggle();" class="mb10" style="cursor:pointer; display:block;">{'Редактировать'|t}</a>
				<form action='/edit_review_comment'
					  method='post'
					  style='display:none;'
					  class='editForm send-review-js'
					  data-action='/edit_review_comment'
					  data-append-comment=".comment__wrap-answer-{$review.RID}">
					<input type="hidden" name="review_id" value="{$review.RID}">
					<div class="review-row">
						<div class="js-message-body review-text server-sided"
							 contenteditable="true"
							 spellcheck="false"
							 placeholder=""
							 name="comment"
							 data-max-count="{\RatingManager::COMMENT_MAX_SYMBOLS_COUNT}">{\Helper::nl2p($review.answer.message|stripslashes)}</div>
						<div class="field-error"></div>
					</div>
					<div class="clear"></div>
					<div class="submit-row mt20">
						<div>
							<input type="submit" class="hugeGreenBtn hoverMe GreenBtnStyle h50 w210 pull-reset review-submit" value="{'Сохранить'|t}">
						</div>
						<div class="variants">
							<div class="write-row">
								<div>{'Вы написали:'|t} <span class="counter"><span class="count">0</span> / {\RatingManager::COMMENT_MAX_SYMBOLS_COUNT}</span></div>
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
			{/if}
		</span>
	</span>
{/strip}