{strip}
	<div class="bodybg">
		<div class="centerwrap lg-centerwrap clearfix block-response">
			<a name="#reviews" class="profile-tab-link"></a>
			<h2 class="f26" id="reviews">
				{'Отзывы о'|t} <span class="break-all-word">{$userProfile->username|stripslashes}</span>
			</h2>
			<div class="user-review-list" itemscope itemtype="http://schema.org/User">
				{include file="reviews_new.tpl" revs=$reviews count=$reviewsOnPage type=$reviewsType grat=$goodReviewsCount brat=$badReviewsCount}
			</div>
		</div>
	</div>
{/strip}
