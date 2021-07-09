{* разрешил ли покупатель составлять портфолио либо старый заказ, по которому покупатель не принимал решение, можно ли публиковать в портфолио *}
{if !$canWriteMessage}
	<div class="btn-group track-page-group-btn">
		<div class="orange-btn js-toggle-message-button sx-wMax mr30 pull-left inactive hide-track-button" data-form="send-portfolio-js"></div>
	</div>
{/if}
{*
* необходимо также подключать (на странице трека они уже подключены в track.tpl):
* {Helper::printJsFile("/js/resemble.js"|cdnBaseUrl)}
*}

{* блок с загрузкой портфолио *}
<div class="js-show-track-portfolio t-align-c clearfix"{if $order->portfolio}style="display: none;"{/if}>
	<i class="fa fa-3x fa-picture-o track-portfolio-icon mt25"></i>
	<h3 class="pt10 font-OpenSansSemi track-green">{'Загрузите результат работы в кворк'|t}</h3>
	<p class="f15 mt15 pl15 pr15">
		<b>{'Покупатель согласился на размещение работы в вашем портфолио!'|t}</b>
	</p>
	<p class="f15 pl15 pr15">
		{'Загрузите изображения, анимацию или видео, показывающие работу с разных сторон, в нескольких цветах, стилях или размерах.'|t}
	</p>
	<script>
		$(function() {
			var $isOrderDoneBlock = $('.js-order-done');
			if ($isOrderDoneBlock.length > 0) {
				var $parentOrderDoneBlock = $isOrderDoneBlock.closest('.step-block-order_item');
				if ($parentOrderDoneBlock.hasClass('unread')) {
					$('.js-show-track-portfolio').closest('.js-comment-box').addClass('unread');
				}
			}
		});

		window.portfolios = [];
		window.portfolioType = "{$kworkPortfolioType}";
		window.ordersPortfolioCoversHashes = {$ordersPortfolioCoversHashes|@json_encode};
		window.ordersPortfolioImagesHashes = {$ordersPortfolioImagesHashes|@json_encode};
		window.ordersPortfolioVideos = {$ordersPortfolioVideos|@json_encode};
	</script>
	{if $portfolioJson}
		<script>
			window.portfolios = [{$portfolioJson|@json_encode}];
		</script>
	{/if}
	{include file="portfolio/upload/card-list.tpl" type="portfolios" maxCount="{$maxPhotos}" sortable="unsortable"}
	<script>
		var portfolioReady = window.portfolioListIsReady || false;
		if (portfolioReady) {
			window.initPortfolioList();
		}
	</script>
</div>