{strip}
{section name=i loop=$revs}
    {if $isTinyView && $count == 3 && $smarty.section.i.index == 3}
        {break}
	{/if}
    {if ($type eq 'positive' AND ($revs[i].good eq 1 OR !$revs[i].comment)) OR ($type eq 'negative' AND $revs[i].bad eq 1) OR ($type eq 'all')}
        <li class="clearfix">
            {if $isRevList ne 1}
                <div itemprop="review" itemscope itemtype="http://schema.org/Review" style="display: none;">
                    <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                        <meta itemprop="bestRating" content="5" >
                        <meta itemprop="worstRating" content="1" >
                        <meta itemprop="ratingValue" content="{if $revs[i].good eq 1}5{else}1{/if}" >
                    </span>
                    <span itemprop="name">{$revs[i].gtitle|stripslashes}</span>
                    <span itemprop="author" itemscope itemtype="http://schema.org/Person">
                    <span itemprop="name">{$revs[i].username|stripslashes}</span></span>
                    <meta itemprop="datePublished" content="{$revs[i].time_added|timestamp_to_date:2}">
                    <div itemprop="reviewBody">{RatingManager::formatText($revs[i].comment_source|stripslashes, $revs[i].auto_mode, $revs[i].hasPaidStages)}</div>
                </div>
            {/if}
            {if $revs[i].auto_mode}
                <span class="review-avatar"><img src="{"/auto-rating.jpg"|cdnImageUrl}" width="50" class="rounded" alt="{$revs[i].username|stripslashes}"></span>
            {else}
                {include file="review_avatar.tpl" reviewId=$revs[i].RID badgeId=$revs[i].badge.id badgeName=$revs[i].badge.name badgeSuper=$revs[i].super userAvatar=$revs[i].profilepicture userName=$revs[i].username}
            {/if}
            <div class="comment{if $withPortfolio && $revs[i].portfolio} comment_with-portfolio{/if}" data-id="{$revs[i].RID}">
                {if $revs[i].auto_mode}
                    <span class="comment-meta"><span class="auto-review">{'Автоматический отзыв сервиса Kwork'|t}</span></span>
                {else}
                    <span class="comment-meta"><a href="{$baseurl}/{insert name=get_seo_profile value=a username=$revs[i].username|stripslashes}">{$revs[i].username|stripslashes}</a></span>
                {/if}
                {assign var="reviewComment" value=RatingManager::formatText($revs[i].comment, $revs[i].auto_mode, $revs[i].hasPaidStages)}
                {if $reviewComment}
                    <span class="f13 color-gray db">
                        {if $revs[i].good eq 1}
                            <i class="ico-green-circle smile dib v-align-m"></i>
                        {elseif $revs[i].bad eq 1}
                            <i class="ico-red-circle smile dib v-align-m"></i>
                        {/if}
                        <span class="dib v-align-m ml5">{insert name=time_ago assign=cntd value=a days=0 time=$revs[i].time_added}{$cntd} {'назад'|t}</span>
                        {if $revs[i].kwork neq null}
                            <span class="v-align-m review__kwork-link db">
                                {'Кворк'|t}: {if StatusManager::kworkCheckListEnable($revs[i].kwork)}<a href="{$baseurl}{$revs[i].kwork->url}">{$revs[i].kwork->gtitle|mb_ucfirst}</a>{else}{$revs[i].kwork->gtitle|mb_ucfirst}{/if}
                            </span>
                        {/if}
                    </span>
                    <div class="comment-comment">
                        {$reviewComment|nl2br}
                    </div>
                    <div class="comment__wrap-answer-{$revs[i].RID}">
                        {if $revs[i].answer && $isTinyView == 0}
							{include file="reviews_answer.tpl" review=$revs[i]}							
                        {elseif ($isWorker)}
                            <a onclick='$(this).parent().find(".answerForm").toggle();' class="mb10" style='cursor:pointer; display:block;'>{'Ответить'|t}</a>
							<form action='/send_review_comment'
								  method='post'
								  style='display:none;'
								  class='answerForm send-review-js mb20'
								  data-action='/send_review_comment' data-append-comment=".comment__wrap-answer-{$revs[i].RID}">
								<input type="hidden" name="review_id" value="{$revs[i].RID}">

								<div class="review-row">
									<div class="js-message-body review-text server-sided"
										 contenteditable="true"
										 spellcheck="false"
										 placeholder=""
										 name="comment"
										 data-max-count="{\RatingManager::COMMENT_MAX_SYMBOLS_COUNT}"></div>
									<div class="field-error"></div>
								</div>
								<div class="clear"></div>
								<div class="submit-row mt20">
									<div>
										<input type="submit" class="hugeGreenBtn hoverMe GreenBtnStyle h50 w210 pull-reset review-submit" value="{'Отправить'|t}">
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
                    </div>
                {else}
                    <span class="f13 color-gray db">
                        <span class="dib v-align-m ml10">{insert name=time_ago assign=cntd value=a days=0 time=$revs[i].time_added}{$cntd} {'назад'|t}</span>
                        {if $revs[i].kwork neq null}
                            <span class="v-align-m review__kwork-link db">
                                {'Кворк'|t}: {if StatusManager::kworkCheckListEnable($revs[i].kwork)}<a href="{$baseurl}{$revs[i].kwork->url}">{$revs[i].kwork->gtitle|mb_ucfirst}</a>{else}{$revs[i].kwork->gtitle|mb_ucfirst}{/if}
                            </span>
                        {/if}
                    </span>
                    <span class="comment-comment"><span class="color-gray">{'Нет отзыва'|t}</span></span>
                {/if}
            </div>
            {if $withPortfolio}
                {if $revs[i].portfolio->photo}
                    <div class="js-review-portfolio review-portfolio"
                         data-src="{"/t3/{$revs[i].portfolio->photo}"|cdnPortfolioUrl}"
                         data-title="{$revs[i].kwork->gtitle|mb_ucfirst}"
                         data-username="{$revs[i].username|stripslashes}"
                         data-review="{$revs[i].comment|stripslashes}"
                    >
                        <img class="review__portfolio-thumb" width="172" height="115" alt="{"Портфолио %s"|t:$revs[i].worker_username|stripslashes}"
                             src="{"/t2/{$revs[i].portfolio->photo}"|cdnPortfolioUrl}">
                    </div>
                {elseif $revs[i].portfolio->video}
                    {insert name=youtube_key value=a assign=youtubeId yt=$revs[i].portfolio->video}
                    <div class="js-review-portfolio js-review-portfolio_video review-portfolio_video review-portfolio"
                         data-src="//img.youtube.com/vi/{$youtubeId}/sddefault.jpg"
                         data-youtube-id="{$youtubeId}"
                         data-title="{$revs[i].kwork->gtitle|mb_ucfirst}"
                         data-username="{$revs[i].username|stripslashes}"
                         data-review="{$revs[i].comment|stripslashes}"
                    >
                        <i class="review-portfolio__video-icon"></i>
                        <img class="review__portfolio-thumb" width="172" height="115" alt="{"Портфолио %s"|t:$revs[i].worker_username|stripslashes}"
                             src="//img.youtube.com/vi/{$youtubeId}/mqdefault.jpg">
                    </div>
                {/if}
            {/if}
        </li>
    {/if}
{/section}
{/strip}