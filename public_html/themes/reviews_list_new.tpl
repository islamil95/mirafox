{strip}
{section name=i loop=$revs}
    {if ($type eq 'positive' AND ($revs[i].good eq 1 OR !$revs[i].comment)) OR ($type eq 'negative' AND $revs[i].bad eq 1) OR ($type eq 'all')}
        <li class="clearfix mb25 pb20" id="RID{$revs[i].RID}">
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
                    <div itemprop="reviewBody">{RatingManager::formatText($revs[i].comment_source, $revs[i].auto_mode, $revs[i].hasPaidStages)}</div>
                </div>
            {/if}
            {if $withPortfolio}
                {if $revs[i].portfolio->video}
                    {insert name=youtube_key value=a assign=youtubeId yt=$revs[i].portfolio->video}
                    <div class="js-review-portfolio review-portfolio_video review-portfolio"
                         data-src="//img.youtube.com/vi/{$youtubeId}/sddefault.jpg"
                         data-youtube-id="{$youtubeId}"
                         data-title="{$revs[i].kwork->gtitle|mb_ucfirst}"
                         data-username="{$revs[i].username|stripslashes}"
                         data-review="{$revs[i].comment}"
                    >
					
					{assign var="imgClass" value=""}
					{if $revs[i].portfolio->is_resizing == 0}
						{assign var="imageSize" value="t2"}
					{else}
						{assign var="imageSize" value="t0"}
						{$sizeImage = \CImage::getSizeImage("{"/{$imageSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl}")}
						{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
							{assign var="imgClass" value="isHorizontalImg"}
						{/if}
					{/if}
					
						<i class="review-portfolio__ico-play-css ico-play-css ico-play-css_sm" style="cursor:pointer"></i>
						<div class="review__portfolio-thumb">
							<img class="{$imgClass}" 
								 width="180" height="120" 
								 alt="{"Портфолио %s"|t:$revs[i].worker_username|stripslashes}"
								 src="{"/{$imageSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl}"
								 srcset="{"/{$imageRetinaSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl} 2x"
							>
						</div>
                    </div>
                {elseif $revs[i].portfolio->photo}
					{assign var="imgClass" value=""}
					{if $revs[i].portfolio->is_resizing == 0}
						{assign var="imageSize" value="t2"}
						{assign var="imageRetinaSize" value="t2_r"}
						{assign var="imageSizeBig" value="t3"}
					{else}
						{assign var="imageSize" value="t0"}
						{assign var="imageRetinaSize" value="t0"}
						{assign var="imageSizeBig" value="t0"}
						{$sizeImage = \CImage::getSizeImage("{"/{$imageSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl}")}
						{if $sizeImage.orientation == 'landscape' && $sizeImage.height / $sizeImage.width < 0.665}
							{assign var="imgClass" value="isHorizontalImg"}
						{/if}
					{/if}
                    <div class="js-review-portfolio review-portfolio"
                         data-src="{"/{$imageSizeBig}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl}"
                         data-title="{$revs[i].kwork->gtitle|mb_ucfirst}"
                         data-username="{$revs[i].username|stripslashes}"
                         data-review="{$revs[i].comment}"
                    >
						<div class="review__portfolio-thumb">
							<img class="{$imgClass}" 
								 width="180" height="120" 
								 alt="{"Портфолио %s"|t:$revs[i].worker_username|stripslashes}"
								 src="{"/{$imageSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl}"
								 srcset="{"/{$imageRetinaSize}/{$revs[i].portfolio->cover}"|cdnPortfolioUrl} 2x"
							>
						</div>
                    </div>
                {/if}
            {/if}
            <div class="fs16 mb10">
                {if StatusManager::kworkCheckListEnable($revs[i].kwork)}<a href="{$baseurl}{$revs[i].kwork->url}">{$revs[i].kwork->gtitle|mb_ucfirst}</a>{else}{$revs[i].kwork->gtitle|mb_ucfirst}{/if}
            </div>
            {if $revs[i].auto_mode}
                <span class="review-avatar"><img src="{"/auto-rating.jpg"|cdnImageUrl}" width="65" class="mw65px rounded" alt="{$revs[i].username|stripslashes}"></span>
            {else}
                {include file="review_avatar.tpl" reviewId=$revs[i].RID badgeId=$revs[i].badge.id badgeName=$revs[i].badge.name badgeSuper=$revs[i].super userAvatar=$revs[i].profilepicture userName=$revs[i].username}
            {/if}
            <span class="comment {if $withPortfolio && $revs[i].portfolio}comment_with-portfolio{/if}">
                <span class="comment-meta">
                    {if $revs[i].auto_mode}
                        <span class="auto-review fs16">{'Автоматический отзыв сервиса Kwork'|t}</span>
                    {else}
                        <a href="{$baseurl}/{insert name=get_seo_profile value=a username=$revs[i].username|stripslashes}" class="fs16">{$revs[i].username|stripslashes}</a>
                    {/if}
                    {if $revs[i].good eq 1}
                        <i class="ico-green-circle smile dib"></i>
                    {elseif $revs[i].bad eq 1}
                        <i class="ico-red-circle smile dib"></i>
                    {/if}
                    <span class="ml8 fs13 m-db m-m0">{insert name=time_ago assign=cntd value=a days=0 time=$revs[i].time_added}{$cntd} {'назад'|t}</span>
                </span>
                {assign var="reviewComment" value=RatingManager::formatText($revs[i].comment, $revs[i].auto_mode, $revs[i].hasPaidStages)}
                {if $reviewComment}
                    <span class="comment-comment lh22 fs14">
                        {$reviewComment|nl2br}
                    </span>
                    <span class="comment__wrap-answer-{$revs[i].RID}">
                        {if $revs[i].answer}
							{include file="reviews_answer_new.tpl" review=$revs[i]}
                        {elseif ($isWorker)}
                            <a onclick='$(this).parent().find(".answerForm").toggle();' class="mb10" style='cursor:pointer; display:block;'>{'Ответить'|t}</a>
                            <form action='/send_review_comment'
								  method='post'
								  style='display:none;'
								  class='answerForm send-review-js'
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
                    </span>
                {else}
                    <span class="comment-comment fs14 color-gray">{'Нет отзыва'|t}</span>
                {/if}
            </span>
        </li>
    {/if}
{/section}
{/strip}