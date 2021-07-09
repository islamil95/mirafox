{include file="header.tpl"}
{strip}
	{Helper::registerFooterJsFile("/js/slick.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/sL-plugin.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/libs/withinviewport.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/libs/jquery.withinviewport.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/readTrackService.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/jquery.kworkpopup.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/caret.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/commission.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/calc_hint.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/dist/components/file-uploader.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/pull-is-type-message.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/edit-message-track.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/track.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/jquery.imgareaselect.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/components/review-form.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/dist/track.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/twemoji.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/trumbowyg/plugins/linebreak/linebreak.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/pages/phases.js"|cdnBaseUrl)}
	{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/jquery.kworkpopup.css"|cdnBaseUrl)}
	{Helper::printCssFile("/css/fontawesome.v5.min.css"|cdnBaseUrl)}
	<script>
		var Track = {$Track};
		window.track = Track;
		var kworkId = {$order->kwork->PID};

		{if $config.chat.isFocusGroupMember}{literal}
		var hasConversation = {/literal}{$hasConversation|boolval|@json_encode}{literal};
		var chatRedirectUrl = '/inbox/{/literal}{if isAllowToUser($order->USERID)}{$order->worker->username}{else}{$order->payer->username}{/if}{literal}';
        {/literal}{/if}

	</script>
	<script>
		{include file="actor-js-data.tpl"}
	</script>

	{Helper::printCssFile("/css/dist/track.css"|cdnBaseUrl)}

	{* lineProgressbar *}
	{Helper::printCssFile("/css/components/jquery.lineProgressbar.css"|cdnBaseUrl)}
	{Helper::printJsFile("/js/components/jquery.lineProgressbar.js"|cdnBaseUrl)}

	{Helper::printJsFile("/js/resemble.js"|cdnBaseUrl)}

	<div id="app" class="track-page">
		<image-viewer ref="imageViewer"></image-viewer>
	</div>
	{include file="track/popup/remove_message_check.tpl"}
	{include file="track/view/edit_message_form_template.tpl"}
	<div class="centerwrap clearfix pb20 mt20 track-page new-version{if $config.track.isFocusGroupMember} track--compact{/if}">
		<div class="contentArea pb0 bgWhite clearfix mb0">
			<div id="new-messages-circle">
				<new-messages-circle class="desktop"></new-messages-circle>
			</div>

			{* шапка *}
			<div class="track-page__header track-head">
				<div class="track-page__header-title">
					<div class="track-page__header-image">
						<div style="background-image:url({if $order->kwork->isCustom()}{$order->worker->getMediumProfilePictureUrl()}{else}{getImageT3Url($order->kwork->photo)}{/if});"></div>
					</div>
					<div class="js-multi-elipsis track-page__header-h1 break-word">
						<div>
						{if $order->kwork->isCustom()}
							{$order->kwork_title|mb_ucfirst}
						{else}
							<a class="color-text break-word js-multi-elipsis-text" href="{$order->kwork->getKworkUrl()}">
								{$order->getOrderTitleForUser($actor->id)|mb_ucfirst}
							</a>
						{/if}
						{if $order->data->bonus_text}
							<div class="order_promo_header">+ {$order->data->bonus_text}</div>
						{/if}
						</div>
					</div>
				</div>

				<div class="pull-left m-pull-reset bread-crump block-response w100p mr-150 mb25 mt5">
					<span class="select-none">
						{if isAllowToUser($order->USERID)}
							<a href="{getAbsoluteURL("/orders")}">{'Мои заказы'|t}</a>
						{else}
							<a href="{getAbsoluteURL("/manage_orders")}">{'Управление заказами'|t}</a>
						{/if}
					<img class="ml10 mr10" style="width:5px" src="{"/arrow_right_gray.png"|cdnImageUrl}" alt="">
					</span>
					{'Заказ'|t} №{$order->OID}&nbsp;
				</div>
				<div class="pull-right m-pull-reset color-gray mt5">{$order->getCreateDate()|date_format:"%e %B, %H:%M"}</div>
				<div class="clear"></div>
				<div id="order-data" class='info mt10'>
					{include file="track/order.tpl" lang=getUserLang()}
					{include file="track/order_upgrade.tpl"}
					{*TODO на время тестирования. После - удалить проверку *}
					{if $isStageTester}
						{include file="track/stages/add-panel.tpl"}
					{/if}
				</div>
				{include file="track/view/modal_alert_update_order.tpl"}

				<div class="clear"></div>
			</div>
			<div class="waveborder bottom"></div>
			<div class="track-spacer"></div>

			<div style="height:15px;background-color:#F6F6F6"></div>
			<div class="waveborder top"></div>
			<div id="step-block-order" class="step-block-order white-bg-border noBorderBottom noBorderTop">
                {if !$config.track.isFocusGroupMember}
					{if $shownThenHided}
						<div class="t-align-c pt20 pb20 show-track hide" id="show-more-track">
							{"Показаны %s последних сообщений."|t:$shownThenHided}
							&nbsp;<a href="#" class="show-track-action link">{"Показать все"|t}</a>
							<div class="loader hide"></div>
						</div>
					{/if}
				{/if}
				<div id="tracks-wrapper">
					<div id="tracks-ajax-loader"></div>
					{$tracksCount = (count($order->tracks) - 1)}
					<script>
						{assign var="trackList" value=[]}
						{for $i = $hiddenCount to $tracksCount}
							{assign var="trackItem" value=$order->tracks[$i]->getFrontendData($order)}
							{if $trackItem}
								{append var="trackList" value=$trackItem}
							{/if}
						{/for}
						{to_js name="trackList" var=$trackList}
						{to_js name="hasHidableConversation" var=$hasHidableConversation}
					</script>
					<div id="app-tracks">
						<track-list ref="trackList" />
					</div>
					<div id="scripts-container"></div>

					{* Промежуточный отчет *}
					{Track\Factory\ReportFactory::getInstance()->getView($order)->render()}

					{* Рендерим отзыв, если он есть *}
					<div class="wrap-answer-{$order->OID}">
						{Track\Factory\ReviewToTrackViewFactory::getInstance()->getView($order)->render()}
					</div>
				</div>
				{* Блок с бонусом продавцу *}
				{if $tips->isAvailable()}
					{include file="track/tips.tpl"}
				{/if}
				{$tipsHtml}
			</div>

			{if $isShowWorkTimeWarning}
				<div class="js-work-time-warning bgLightGray p15-20">
					<div class="message--warning">{$workTimeWarning}</div>
					{include file="track/view/loyality/loyality_late.tpl" loyalityVisible=false}
				</div>
			{/if}

			<div id="track-form" class="mf-form attached-images-area" data-submit-button="#track-send-message-button">
				{assign var="formView" value=Track\Factory\FormViewFactory::getInstance()->getView($order)}
				{$formView->render()}
			</div>
            {include file="track/icons.tpl"}

			{literal}
				<script>
					var trackFormHash = '{/literal}{$formView->getFormMD5Hash()}{literal}';
				</script>
			{/literal}
		</div>

		{* правый блок *}
		<div class="sidebarArea">
			{include file="track/sidebar.tpl"}
			<div class="mobile-form-offset"></div>
		</div>
	</div>

	{if $admitadThankYou}
	{literal}
		<script>
				window.ad_order = '{/literal}{$order->OID}{literal}';		// orders.oid
				window.ad_amount = '{/literal}{$order->price}{literal}';	// orders.price
			window.ad_products = [{
					'id': '{/literal}{$order->OID}{literal}',				// orders.oid
				'number': '{/literal}{$order->count}{literal}'     // orders.count
				}];
		</script>
	{/literal}
	{/if}

{literal}
	<script>
		var comission = {/literal}{intval(App::config("commission_percent"))}{literal};

		var turnover = {/literal}{if !is_null($turnover)}{$turnover}{else}{literal}null{/literal}{/if}{literal};
		var commissionRanges = null;
		var orderPrice = {/literal}{$orderPrice}{literal};

		var formData = new FormData;
		var haveFiles = false;
		var isNewCalc = {/literal}{if $isNewCalc}true{else}false{/if}{literal};{/literal}
		{to_js name="actorId" var=$actor->USERID}
		{to_js name="draftData" var=$draftData}

		{if $pageMsg && isset($pageMsg.type)}
			show_message('{$pageMsg.type}', '{$pageMsg.content}', '{$pageMsg.name}');
		{/if}
		{literal}
		$(window).load(function () {
			if ($('.request-not-correspond_double_false').length) {
				if ($('.js-request-type-text_first').length) {
					$('.js-request-type-worker_inwork').hide();
					$('.js-request-type-text_first').show();
				} else {
					$('.js-request-type-text_first').hide();
					$('.js-request-type-worker_inwork').show();
				}
			}

			$('#cancel-order').on('click', function () {
				if (typeof(yaCounter32983614) !== 'undefined') {
					if ($('.cancel-order-js input:checked').val() == 'worker_inprogress_cancel_request' || $('.cancel-order-js input:checked').val() == 'payer_inprogress_cancel_request') {
						yaCounter32983614.reachGoal('START-CANCEL-ORDER-TWO');
					}
					else if ($('.cancel-order-js input:checked').val() == 'payer_inprogress_cancel' ||
						$('.cancel-order-js input:checked').val() == 'worker_inprogress_cancel') {
						yaCounter32983614.reachGoal('CANCEL-ORDER');
					}
				}
			});
			{/literal}

			{if $balance_popup}
				show_balance_popup('', 'kwork');
			{/if}
			{literal}
		});

		$(document).ready(function() {
			$(".chosen_select").chosen({
				width: "108px",
				disable_search: true,
			});

			$(".recommend-block__header").on("click", function() {
				var value = true,
					icon = $("#recommend-blocks .recommend-block__header .fa"),
					container = $(".recommend-block-track-page__container");

				container.toggleClass("hidden");
				icon.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");

				if(container.hasClass("hidden")) {
					value = false;
				}

				$.post("/api/user/showrecommendationsintrackpage",
					{ value: value },
					function () {},
					"json");
			});
		});
	</script>
{/literal}
{/strip}

{$customMinPrice = \KworkManager::getCustomMinPrice($order->getLang())}
{$customMaxPrice = \KworkManager::getCustomMaxPrice($order->getLang())}
{include file="popup/individual_message.tpl"}
{include file="track/popup/success_order.tpl"}
{include 'order-more-modal.tpl'}

{include file="footer.tpl"}