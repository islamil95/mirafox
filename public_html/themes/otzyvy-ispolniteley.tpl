{strip}
<div class="static-page__block">
	<div class="white-bg-block centerwrap">
		<div class="pt20 m-visible"></div>
		<h2 class="fontf-pnb fs34 mb10 t-align-c">{'Отзывы продавцов'|t}</h2>
		<div class="fontf-pnl fs18 t-align-c">{'Фрилансеры ценят Kwork за то, что...'|t}</div>
		<div class="comments-panel ">
			{foreach $reviews as $review name=reviewsList}
				{if ($smarty.foreach.reviewsList.index + 1) % 2 != 0}
					<div>
				{/if}
				<div class="comments-panel_item">
					<div>
						<img src="{"/medium/{$review->profilepicture}"|cdnMembersProfilePicUrl}"
							{userMediumPictureSrcset($review->profilepicture)}
							width="60" height="60" alt="{$review->username}" class="comments-panel_item_img" />
						<div class="comments-panel_item_text">
							<a href="/user/{$review->username|lower}" class=" bold fs18 fontf-pnb">{$review->username}</a>
							<ul class="rating-block">
								{control name=rating_stars rating=$review->cache_rating}
							</ul>
							<div class="fontf-pnb">{$review->fullname}</div>
						</div>
					</div>
					<p>{$review->text|stripslashes|html_entity_decode|nl2br}</p>
				</div>
				{if ($smarty.foreach.reviewsList.index + 1) % 2 == 0}
					</div>
				{/if}
			{/foreach}
			{if $actor}
				<div class="t-align-c">
					<div class="GreenBtnStyle big left-panel-button nowrap mw370 comments-panel_btn h50 comments-panel_btn-js mt0">{'Разместить свой отзыв'|t}</div>
				</div>
				<form class="executor-form mw50p" style="display: none;">
					<div class="fs16 bold">{'Напишите свой отзыв'|t}</div>
					<textarea  name="review_text" class="styled-input db wMax f14 mh145 mt15" ></textarea>
					<div class="executor-form_message  mt5"></div>
					<input type="submit" class="hugeGreenBtn GreenBtnStyle h40 lh40 mw150px mt20 pull-reset" value="{'Отправить'|t}">
				</form>
			{/if}
		</div>
		<div class="clear"></div>
	</div>
</div>
{literal}
<script>
	$(document).on('click','.comments-panel_btn-js',function (event) {
		$('.executor-form').slideToggle();
		$('html, body').animate({
			scrollTop: $(".executor-form").offset().top
		}, 800);
	})

	$(document).on('submit','.executor-form',function (event) {
		event.preventDefault()
		if($(this).find('textarea').val().length===0){
			$(this).find('.executor-form_message').addClass('color-red').html(t('Напишите свой отзыв'));
			return false;
		}
		else{
			$(this).find('.executor-form_message').removeClass('color-red').html('');
		}

		$.ajax( {
			type: "POST",
			url: '/api/user/addreview',
			data: $('.executor-form').serialize(),
			dataType:'json',
			success: function(response) {
				if (response.result) {
					$('.executor-form').find('textarea').val('');
					$('.executor-form').find('.executor-form_message').removeClass('color-red').html(t('Спасибо! Ваш отзыв принят и будет опубликован на сайте после проверки.'));
				}else{
					$('.executor-form').find('.executor-form_message').addClass('color-red').html(t('Возникла ошибка, попробуйте позже.'));
				}
			},
			error:function(){
				$('.executor-form').find('.executor-form_message').addClass('color-red').html(t('Возникла ошибка, попробуйте позже.'));
			}
		} );
	})
</script>
{/literal}
{/strip}