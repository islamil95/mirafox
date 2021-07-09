{include file="header.tpl"}
{strip}
	{Helper::printJsFile("/js/mainfox.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/dist/bookmarks.js"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/bookmarks.css"|cdnBaseUrl)}
	{include file="fox_error7.tpl"}

	<div class="bodybg">
		<div class="gray lg-centerwrap centerwrap m-m0">

			<div class="categoriesHeader relative t-align-l">
				<h1 class="f32 dib mb0">{'Скрытые кворки и продавцы'|t}</h1>
				{* Вкладки *}
				<div class="group-controls btn-group dib hidden-page-control">
					<a href="{route route="bookmarks" params=["status" => "active"]}" class="white-btn {if $status eq 'active'}green-btn{/if}">
						{'Активные'|t}
					</a>
					{if $countStopped > 0}
						<a href="{route route="bookmarks" params=["status" => "stop"]}" class="white-btn {if $status eq 'stop'}green-btn{/if}">
							{'Остановленные'|t}
						</a>
					{/if}
					{if $countHiddens > 0}
						<a href="/hidden" class="white-btn green-btn">{'Скрытые'|t}</a>
					{/if}
				</div>
			</div>


			<div class="f15 mb15">
				<p>
					{'На данной странице отображаются кворки, которые вы скрыли, а также продавцы, автоматически попавшие в Скрытые. На Кворке не место безответственным продавцам, поэтому они попадают в Скрытые в случае срыва вашего заказа.'|t}
				</p>
				<p class="mt5">
					{'Скрытые кворки и кворки скрытых продавцов не будут видны вам в Поиске, не придут в рассылках или рекомендациях.'|t}
				</p>
			</div>

			{* Скрытые кворки *}
			{if $hiddenItems['kworks']}
				<div class="clearfix mb20">
					<h1 class="t-align-c m-text-left m-f18">{'Скрытые кворки'|t}</h1>
					<div class="cusongslist cusongslist_4_column c4c kwork-card-data-wrap" data-kwork-load-category="6">
						{foreach from=$hiddenItems['kworks'] item=kwork}
							{$kwork.isHidden = true} {* TODO Если в карточках приходит isHidden, то эту строку удалить *}
							{control name="_blocks/kwork/kwork_card" kwork=$kwork user_kwork_marks=$user_kwork_marks}
						{/foreach}
					</div>
				</div>
			{/if}
			{if $countHiddens['kworks'] > \HiddenManager::HIDDEN_LIMIT}
				<div class="ta-center">
					<a href="jacascript:void(0);" class="showMoreBtn loadKworks" data-type="kwork" data-append="cusongsblock">{'Показать все'|t}</a>
				</div>
			{/if}

			{* Скрытые продавцы *}
			{if $hiddenItems['users']}
				<div class="clearfix">
					<h1 class="t-align-c m-text-left m-f18">{'Скрытые продавцы'|t}</h1>
					<div id="hiddenUsers" class="cusongslist cusongslist_3_column c3c">
						{$users = $hiddenItems['users']}
						{include file="fox_user_bit.tpl"}
					</div>
				</div>
			{/if}
			{if $countHiddens['users'] > \HiddenManager::HIDDEN_LIMIT}
				<div class="ta-center">
					<a href="jacascript:void(0);" class="showMoreBtn loadKworks" data-type="user" data-append="cusongsblock-user">{'Показать все'|t}</a>
				</div>
			{/if}

		</div>
	</div>
{/strip}

<script>
	{literal}
		window.isHiddenPage = true;
		
		var _tplHiddenUsers = ''
			+ '<div class="user-control">'
				+ '<span class="js-user-hidden kwork-icon icon-eye-slash tooltipster hidden f16" data-action="add" data-tooltip-theme="dark-minimal" data-tooltip-text="{/literal}{'Скрыть продавца'|t}{literal}"></span>'
				+ '<span class="js-user-hidden kwork-icon icon-eye-slash tooltipster active f16" data-action="del" data-tooltip-theme="dark-minimal" data-tooltip-text="{/literal}{'Вернуть из скрытых'|t}{literal}"></span>'
			+ '</div>';

		jQuery(function() {
			jQuery('#hiddenUsers .cusongsblock-user').each(function() {
				var $userCard = jQuery(this);
				var userId = $userCard.data('id');

				$userCard.append(_tplHiddenUsers);
			});

			jQuery('body').on('click', '.js-user-hidden', function() {
				var $currentElement = jQuery(this);
				var $userCard = $currentElement.closest('.cusongsblock-user');
				var userId = $userCard.data('id');
				var action = $currentElement.data('action');

				$userCard.find('.js-user-hidden').toggleClass('hidden');
				customHide(userId, 'user', action);
			});
			jQuery('.showMoreBtn').on('click',function(){
				var type = $(this).data('type');
				var appendBlck = $(this).data('append');
				var that = $(this);
				$.ajax({
					type: "POST",
					url: '/api/hidden/gethiddenitems',
					async: false,
					data: {
						'type': type,
						'limitFrom': 0
					},
					dataType: "json",
					success: function(response) {
						$("."+appendBlck).last().after(response.html);
						that.hide();
					}
				}, 'json');
			});
		});
	{/literal}
</script>
{include file="footer.tpl"}