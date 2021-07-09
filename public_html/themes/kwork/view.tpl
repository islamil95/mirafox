{if $canModer}
	{include file="header_moder.tpl"}
{else}
	{include file="header.tpl"}
{/if}
{strip}
	{* JS *}
	{if !$pageSpeedEnabled}
		{* данные файлы на мобильных подгружаются после загрузки страницы *}
		{Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
		{Helper::printCssFile("/css/dist/kwork-view.css"|cdnBaseUrl)}
	{/if}
	{if $canModer}
		{Helper::registerFooterCssFile("/css/pages/moder_stat.css"|cdnBaseUrl)}
	{/if}
	{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/components/youtube-thumbnail.js"|cdnBaseUrl)}

	{if $canModer}
		{Helper::registerFooterJsFile("/js/pages/moder_stat.js"|cdnBaseUrl)}
	{/if}
	{Helper::registerFooterJsFile("/js/sL-plugin.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/rolldown-text.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/m-swap.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/view.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/slick.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("https://www.youtube.com/iframe_api")}
	{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/portfolio_view_popup.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/kwork-view.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/components/allAttributes.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/components/tooltipster.bundle.min.js"|cdnBaseUrl)}
	{if $canModer}
		{Helper::registerFooterJsFile("/js/kwork_moderate.js"|cdnBaseUrl)}
		{Helper::registerFooterJsFile("/js/pages/save_kwork.js"|cdnBaseUrl)}
	{/if}

	{* комментарии *}
	{if $actor}
		{Helper::printJsFile("/js/caret.js"|cdnBaseUrl)}
		{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/components/review-form.js"|cdnBaseUrl)}
		{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
	{/if}

	{* JS *}
	{* CSS *}
	{if $canModer}
		{assign var=moderStatStyleOuter value=' class="moder-block"'}
		{assign var=moderStatStyleInner value=' style="min-width: 0;"'}
		<style>
			.kwork-card-moder-preview {
				position: absolute;
				top: 60px;
				left: -300px;
				max-width: 260px;
			}

			@media only screen and (max-width: 767px) {
				.kwork-card-moder-preview {
					top: 280px;
				}
			}
		</style>
	{/if}
	{* CSS *}


	{* Ошибки и сообщения *}
	{include file="fox_error7.tpl"}

	{assign var=kworkPortfolioEmpty value=true}
	{if !empty($portfolioList) || !empty($kworkPortfolio)}
		{$kworkPortfolioEmpty = false}
	{/if}

	{assign var="editBlocked" value=($actor && $actor->id == $kwork.USERID && $actor->kwork_allow_status == "deny")}

	{assign var="sliderType" value=($isMobile && !$onlyDesktopVersion) ? 'mobile' : 'desktop'}

	{assign var="showDesktopSlider" value=($showAllData || !$isPostModeration || !$canModer || $patchPhoto || $patchExtPhotos || $patchYoutube)}

	<div id="js-kwork-view" class="page-more-kwork {if $kwork.is_package}page-more-kwork-package{/if}{if $actor} page-more-kwork_actor{/if}{if $canModer} is_moder{/if}">
		<div class="mt20 m-hidden"></div>
		<div{$moderStatStyleOuter}>
			<div class="centerwrap clearfix pb20 m-p0"{$moderStatStyleInner}>
				{if $canModer}
				<div class="moder-stat-block">
					<div class="moder-stat">
						<div class="moder-stat__foot">
							<a class="js-moder-stat-link" href="javascript: void(0);">Статистика модерации</a>
						</div>
					</div>
				</div>
				{/if}

				<div class="kwork_slider_mobile">
					{if $sliderType==='mobile'}
						{include file="kwork_slider.tpl" type="mobile"}
						<div class="kwork_slider_mobile_counter"></div>
					{/if}
				</div>

				{* Блок с разными уведомлениями *}
				{include file="kwork/view_notice_block.tpl"}

				<div class="clear"></div>

				{if $isModeratedKwork && ($canModer || $isModer)}
					{include file="kwork/kwork_reminders.tpl" reminders=$reminders}
				{/if}

				{if showLangSwitchBlock($kwork.USERID)}
					<div class="pull-right mr353 m-mr0">
						{include file='i18n/lang_switch_block.tpl'}
					</div>
				{/if}
				<div class="contentArea mb0" itemscope itemtype="http://schema.org/Product">
                    <span itemprop="aggregateRating"
                          itemscope=""
                          itemtype="http://schema.org/AggregateRating"
                          class="hidden">
                        <meta itemprop="ratingValue" content="{round($userRating/20, 1)}">
                        <meta itemprop="worstRating" content="1">
                        <meta itemprop="bestRating" content="5">
                        <meta itemprop="ratingCount" content="{$badReviews + $goodReviews}">
                        <meta itemprop="reviewCount" content="{$badReviews + $goodReviews}">
                    </span>
					<div class="bgWhite white-bg-border clearfix mb0" style="padding-bottom: 1px;">
						<div class="page-more-kwork_header">
							<div class="p15-20 border-b-none" style="padding-top: 6px;">
								{if !$showAllData && $isPostModeration && $canModer}
									<a id="kwork-status" data-postmoderation="true"></a>
								{/if}
								<h1 id="kwork-title" class="mb8 mt7 kwork-title" itemprop="name">
									{$kwork.gtitle|mb_ucfirst|stripslashes}
								</h1>
								{if !$canModer}
									{* Избранное *}
									<div class="m-hidden">
									{include file="kwork/view_bookmark_block.tpl"}
									</div>
								{/if}
									{* Хлебные крошки *}
								{include file="kwork/view_bread_crumb_block.tpl" nowrap="true"}
								<div class="clear"></div>

								{* Информация о пользователе для мобильных *}
								{include file="kwork/mobile/user_info.tpl"}
							</div>
							<div class="clear"></div>
							<div class="kwork_slider_desktop
							           {if !$kworkPortfolioEmpty && $isShowPortfolio} cursor--zoom-in{/if}
									   {if !$showDesktopSlider} hide-desktop-slider {/if}
							">
								{if $sliderType==='desktop'}
									{include file="kwork_slider.tpl" type="desktop"}
									<div class="kwork_slider_mobile_counter" style="display: none"></div>
								{/if}
							</div>
						</div>
						<div class="view-direction">
							<div class="p15-20 clearfix pt20 pb20">
								<div class="clear"></div>
								<h2 class="kwork-description">{'Об этом кворке'|t}</h2>
								<div class="mt20 m-hidden"></div>
								<div class="mt10 m-visible"></div>
								<div class="b-about-text clearfix">
									<div class="b-about-text_container">
										<div class="b-about-text_container_text">
											<div id="description-text" class="description-text ta-justify breakwords">
												{$kwork.gdesc|stripslashes|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

                    {* Рекомендованные кворки *}
                    {if $recommendedKworks->isNotEmpty()}
						<div {$moderStatStyleOuter}>
							<div class="w660 clearfix block-response mt20 recommend-wrapper{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}" {$moderStatStyleInner}>
								<div class="clear"></div>
								<h2 class="f22 {if !$isMobile && !$isTablet}f20{/if}">{'Рекомендуем также'|t}</h2>
								<div class="cusongslist cusongslist_small_5_column recommend {if !$isMobile && !$isTablet}ext{/if}">
									{include file="fox_bit.tpl" carousel=true posts=$recommendedKworks->toArray()}
								</div>
							</div>
						</div>
                    {/if}

					{if !$canModer}
						<div class="mt20 m-swap tablet-version{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}" data-name="refund-guarantee"></div>
					{/if}


					{if !$canModer}
						<div class="bgWhite clearfix mb0 mt20{if $pageSpeedDesktop} lazy-load_scroll-wrapper{/if}">
							<div class="white-bg-border clearfix user-reviews">
								<div class="clear"></div>
								<br>
								<h2>{'Отзывы по кворку'|t}</h2>
								{include file="reviews.tpl" revs=$reviews count=$count_reviews type=$reviewsType grat=$goodReviews brat=$badReviews reviewsBlock="kwork_reviews"}
								{assign var=isRevList value=1}
							</div>
						</div>
					{/if}
				</div>
				<div class="sidebarArea change-position-mobile m-hidden">
					{if $canModer}
						{include file="view_right_bar_moder.tpl"}
					{else}
						{include file="view_right_bar.tpl"}
					{/if}
				</div>
			</div>
		</div>

		{if $otherUserKworks|count gt 0}
			<div class="bgLightGray{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}" {$moderStatStyleOuter}>
				<div class="centerwrap other-seller-kwork-wrap clearfix block-response pt20" {$moderStatStyleInner}>
					<div class="clear"></div>
					<div class="kwork-detail-carousel-wrapper">
						{if $actor}
						<h2 class="f26">{'Другие кворки продавца'|t}</h2>
						{else}
						<h2 class="f26">{'Другие кворки фрилансера'|t}</h2>
						{/if}
						<div class="cusongslist cusongslist_small_5_column other mt10 kwork-card-data-wrap" data-kwork-load-category="1">
							<div class="kwork-detail-carousel">
								{if $canModer}
								{include file="fox_bit_list_v2.tpl" posts=$otherUserKworks}
								{else}
								{include file="fox_bit.tpl" carousel=true posts=$otherUserKworks}
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}

		{if $sameKworks|count gt 0 && !$canModer}
			<div{$moderStatStyleOuter}>
				<div class="centerwrap clearfix pt20 similar-kwork-wrap block-response kwork-card-data-wrap{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}" data-kwork-load-category="6" {$moderStatStyleInner}>
					<div class="clear"></div>
					<div class="kwork-detail-carousel-wrapper">
						<h2 class="f26">{'Похожие кворки'|t}</h2>
						<div class="cusongslist cusongslist_small_5_column other mt10">
							<div class="kwork-detail-carousel">
								{include file="fox_bit.tpl" carousel=true posts=$sameKworks}
							</div>
						</div>
					</div>
				</div>
				<div class="pt10 pb20 t-align-c m-visible">
					<a href="{$baseurl}/categories/{$kwork.seo|lower|stripslashes}">{'Смотреть все'|t}</a>
				</div>
			</div>
		{/if}

		<div class="m-visible{if $pageSpeedMobile} lazy-load_scroll-wrapper{/if}">
			{include file="view_user_info.tpl" reviewsBlock="kwork_reviews"}
			{include file="view_kwork_share.tpl"}
		</div>

		{if $isModeratedKwork && $canModer}
			<div{$moderStatStyleOuter}>
				<div class="centerwrap clearfix pt20 block-response"{$moderStatStyleInner}>
					{control name="kwork\kwork_notice\kwork_notice_action_moderated" p=$kwork}
				</div>
			</div>
		{/if}
	</div>
{literal}
	<script>
		var baseUrl = '{/literal}{$baseurl}/'{literal};
		var bookmarkUrl = '{/literal}{$baseurl}/bookmark?id='{literal};
		var spellCheck = {/literal}{$spellCheck}{literal};
		var price = {/literal}{$kwork.price|zero:0:false}{literal};
		var post_id = {/literal}{$kwork.PID}{literal};
		var vendor = "{/literal}{$kwork.USERID}{literal}";
		var productUrl = "{/literal}{$baseurl}{$kwork.url}{literal}";
		var pictureUrl = "{/literal}{$purl}/t3/{$kwork.photo}{literal}";
		var productName = "{/literal}{$kwork.gtitle|trim}{literal}";
		var productCategory = "{/literal}{$kwork.category}{literal}";
		var admitadCampainCode = "{/literal}{App::config("admitad.campaign_code")}{literal}";
		var balanceRefillAmount = '{/literal}{$convertedOrderPrice - $actor->totalFunds}{literal}';
		var isRecalculatePrice = {/literal}{if $authOrder && $authOrder.kworkCount gt 0}true{else}false{/if}{literal};
		var showBalancePopup = {/literal}{if $balance_popup eq 1}true{else}false{/if}{literal};
		var isUserKworkAndOnPause = {/literal}{if $kwork.active == KworkManager::STATUS_PAUSE && $kwork.USERID == $actor->id&& !$isSuspended}true{else}false{/if}{literal};
		var isLowPortfolioPauseReason = {/literal}{if $kwork.pause_reason == KworkManager::PAUSE_REASON_LOW_PORTFOLIO}true{else}false{/if}{literal};
 		var noticeMessage = '{/literal}{'Кворк поставлен на паузу, поскольку очередь к нему достигла '|t}{$kwork_pause_on} {declension count=$kwork_pause_on form1="заказа" form2="заказов" form5="заказов"}{'. Кворк будет снят с паузы, когда очередь снизится до '|t}{$kwork_pause_off} {declension count=$kwork_pause_off form1="заказа" form2="заказов" form5="заказов"}{literal}';
		var kworkLang = '{/literal}{$kwork.lang}{literal}' || 'ru';
		var kworkDays = 0;
		var kworkCategory = {/literal}{$kwork.category}{literal};
		var rate = {/literal}{$rate}{literal};
		var isPackage = {/literal}{if $kwork.is_package}true{else}false{/if}{literal};
		var tempSelectedPackageType = ""; {/literal}{* устанавливается при вызове show_signup() *}{literal}
		var jsCategoryName = '{/literal}{$kwork.name|stripslashes}{literal}';
		var jsCategorySeo = '{/literal}{$baseurl}/categories/{$kwork.seo|lower|stripslashes}{literal}';
		var categoryFreePrice = {/literal}{if $category.is_package_free_price}true{else}false{/if}{literal};
		var rejectReasons = {/literal}{if ($isAdmin || $isKworkUser || $isModer)}{$rejectReasons|@json_encode}{else}null{/if}{literal};

        {/literal}{if $config.chat.isFocusGroupMember}{literal}
		var hasConversation = {/literal}{$hasConversation|boolval|@json_encode}{literal};
		var chatRedirectUrl = '/inbox/' + jQuery('.button-contact-with-me').data('workername') + '?kworkId=' + post_id;
		var conversationUserId = {/literal}{$kwork.USERID|intval}{literal};
		var conversationMessage = '';
		if (hasConversation) {
			chatRedirectUrl += '?kworkId=' + post_id;
			conversationMessage = t('Добрый день. Тема: кворк "{{0}}".',['{/literal}{$kwork.gtitle|mb_ucfirst|stripslashes}{literal}']);
		}
        {/literal}{/if}{literal}

		{/literal}{if $actor}{literal}
		$(document).on('click', '.button-contact-with-me', function (e) {
			{/literal}{if $isPageNeedSmsVerification}{literal}
			e.preventDefault();
			$.ajax({
				url: '/check_payer_phone_verification',
				type: 'GET',
				context:this,
				success: function(result) {
					if(!result.success){
						phoneVerifiedOpenModal();
					} else {
						{/literal}{/if}{literal}
						var workername = $('.button-contact-with-me').data('workername');

						{/literal}{if $config.chat.isFocusGroupMember}{literal}
						firstConversationMessage(hasConversation, chatRedirectUrl, conversationUserId, conversationMessage);
						return false;
						{/literal}{/if}{literal}

						location.href = "/conversations/" + workername + "?kworkId=" + post_id + "&goToLastUnread=1";
						{/literal}{if $isPageNeedSmsVerification}{literal}
					}
				}
			});
			{/literal}{/if}{literal}
		});
		{/literal}{/if}{literal}
	</script>
{/literal}

	{*Карусель*}
	{Helper::registerFooterJsFile("/js/jquery.kworkcarousel.min.js"|cdnBaseUrl)}
	{if $isModeratedKwork && ($canModer || $isModer)}
	<script>
		$(document).ready(function () {
			AddEditModerReminderModule.init({
				kworkId: post_id
			});
		});
	</script>
	{/if}

{/strip}
{if $canModer}
	{include file="footer_base.tpl"}
{else}
	{include file="footer.tpl"}
{/if}
