{strip}
	<script>
		function defer(method) {
			if (window.jQuery) {
				method();
			} else {
				setTimeout(function () {
					defer(method)
				}, 50);
			}
		}
	</script>

	{if $pullModuleEnable}
		{Helper::printJsFile("/js/libs/pushstream.min.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/modules/pull.js"|cdnBaseUrl)}
		{Helper::printJsFile("/js/libs/ion.sound.min.js"|cdnBaseUrl)}
	{/if}

	{Helper::printJsFiles()}

	{if $pullModuleEnable}
	{literal}
		<script>
			PullModule.start({
				host: '{/literal}{App::config('pushserver')}{literal}',
				channelId: {/literal}'{$userPullChannel}'{literal},
				userId: {/literal}{intval($actor->id)}{literal}
			});
		</script>
	{/literal}
	{/if}

	{if $appMode == "stage"}
		{if Translations::isDefaultLang()}
			<!-- LiveInternet counter -->
		{literal}
			<script><!--
					new Image().src = "//counter.yadro.ru/hit?r" +
						escape(document.referrer) + ((typeof (screen) == "undefined") ? "" :
							";s" + screen.width + "*" + screen.height + "*" + (screen.colorDepth ?
							screen.colorDepth : screen.pixelDepth)) + ";u" + escape(document.URL) +
						";" + Math.random(); //-->
			</script>
		{/literal}
			<!-- /LiveInternet counter -->
		{/if}

		{if $appMode == "stage" && !App::isMirror() && App::config("qiberty.enable") && Translations::isDefaultLang()}
			{QibertyManager::getHeadCode()}
		{/if}
	{/if}

{literal}
	<script>
		var base_url = '{/literal}{$baseurl}{literal}';
	</script>
{/literal}

	{if $pageName == "index" && false}
		<meta name="webmoney" content="C9455300-89B3-4C00-B332-42DBE9A8279A"/>
	{/if}

	{if $metricEnable}
		<script defer>
			defer(function () {
				{literal}
				var userMetricIsChecked = {/literal}{$userMetricIsChecked}{literal};
				var userMetricId = '{/literal}{$userMetricId}{literal}';
				{/literal}
				{if $pageSpeedDesktop}
				{literal}
				window.addEventListener('DOMContentLoaded', function () {
					kMetric.init(userMetricIsChecked, userMetricId);
				});
				{/literal}
				{else}
				{literal}
				kMetric.init(userMetricIsChecked, userMetricId);
				{/literal}
				{/if}
			});
		</script>
	{/if}

	{if $appMode == "stage"}
		{if $track_client_id}
		{literal}
			<script>
				var dataLayer = window.dataLayer = window.dataLayer || [];
				dataLayer.push({'cid': '{/literal}{$track_client_id}{literal}'});
			</script>
		{/literal}
		{/if}
		{if $login_user_id}
		{literal}
			<script>
				var dataLayer = window.dataLayer = window.dataLayer || [];
				dataLayer.push({'userId': '{/literal}{$login_user_id}{literal}'});
			</script>
		{/literal}
		{/if}
		{if $is_track_page && $needDataLayerOrderId}
		{literal}
			<script>
				var dataLayer = window.dataLayer = window.dataLayer || [];
				dataLayer.push({
					'orderId': '{/literal}{$order->id}{literal}',
					'event': 'orderplaced'
				});
			</script>
		{/literal}
		{/if}
	{/if}
		{assign var=dataLayer value=GTM::getDataLayer(true)}
		{if $dataLayer}
		{literal}
			<script>
				var dataLayer = window.dataLayer = window.dataLayer || [];
				{/literal}
				{foreach from=$dataLayer item=dataLayerItem}
				{literal}
				dataLayer.push({/literal}{$dataLayerItem}{literal});
				{/literal}
				{/foreach}
				{literal}
			</script>
		{/literal}
		{/if}


	{if $appMode == "stage" && Translations::isDefaultLang() && App::config(\Support\SupportManager::SHOW_JIVOSITE_CONFIG) && (!$actor || $actor->type == UserManager::TYPE_PAYER && UserManager::getData($actor->id)->reg_type == UserManager::TYPE_PAYER)}
		<!-- Chat -->
	{literal}
		<script defer>
			defer(function () {
				if (isNotMobile()) {
					(function () {
						var widget_id = 'NYxPRzDrFh';
						var d = document;
						var w = window;

						function l() {
							var s = document.createElement('script');
							s.async = true;
							s.src = '//code.jivosite.com/script/widget/' + widget_id;
							var ss = document.getElementsByTagName('script')[0];
							ss.parentNode.insertBefore(s, ss);
						}

						if (d.readyState == 'complete') {
							l();
						} else {
							if (w.attachEvent) {
								w.attachEvent('onload', l);
							} else {
								w.addEventListener('load', l, false);
							}
						}
					})();
				}
			});

		</script>
	{/literal}
		<!-- /Chat -->
	{/if}

	{if App::isShowAuthCaptcha() ||  $alwaysShowCaptcha}
		{reCAPTCHA::getJS()}
		<script>
			var isNeedShowRecaptcha = true;
		</script>
	{/if}

	{if App::isMirror() || !$disallow_mirror}
		{if !Marketing::isGetCode()}
			{Marketing::getDefaultCode()}
		{else}
			{$googleAdServicesCode}
		{/if}
	{/if}

	{if $appMode == "stage" && !App::isMirror() && App::config("qiberty.enable") && QibertyManager::isTargets()}
		{QibertyManager::getTargets()}
	{/if}

	{if Translations::getLang() === Translations::EN_LANG && App::config("module.en_bugreport.enable")}
		{Helper::printJsFile("/js/bug-report.js"|cdnBaseUrl, $pageSpeedDesktop)}
	{/if}

	{if App::config("sourcebuster.enable")}
		{Helper::printJsFile("/js/libs/sourcebuster.min.js"|cdnBaseUrl, $pageSpeedDesktop)}
		<script defer>
			defer(function () {
				{if $pageSpeedDesktop}
				{literal}
				window.addEventListener('DOMContentLoaded', function () {
					sbjs.init({
						domain: location.host
					});
				});
				{/literal}
				{else}
				{literal}
				sbjs.init({
					domain: location.host
				});
				{/literal}
				{/if}
			});
		</script>
	{/if}
{/strip}