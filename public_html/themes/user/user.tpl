{include file="header.tpl"}
{*
	@TODO: В шаблоне используется один и тот же html, должен быть один, без дублирования
	@TODO: Упорядочить Javascript
*}
{strip}
	{Helper::registerFooterJsFile("/js/slick.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("https://www.youtube.com/iframe_api")}
	{Helper::registerFooterJsFile("/js/portfolio_view_popup.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/kwork_new_edit.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/add-files.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/components/profile-photo-upload.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/jquery.imgareaselect.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/pages/user.js"|cdnBaseUrl)}
	{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/dist/profile.js"|cdnBaseUrl)}

	{Helper::registerFooterCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/components/comment-form.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/caret.js"|cdnBaseUrl)}

	{Helper::printJsFile("/js/components/review-form.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/components/jquery.lineProgressbar.js"|cdnBaseUrl)}

	{Helper::printCssFile("/css/components/jquery.lineProgressbar.css"|cdnBaseUrl)}
	{Helper::printCssFile("/css/dist/profile.css"|cdnBaseUrl)}
	{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}

	<style type="text/css">
		@media only screen and (max-width: 767px) {
			.all_page {
				padding-top: 94px;
			}
		}
	</style>

	<div class="userbanner user-userbanner" style="background: url({$userProfile->getCoverUrl()|cdnCoverUrl}) no-repeat center center; background-size: cover;">
	</div>

	{include file="user/head.tpl"}
	{* END Шапка пользователя (подключаемая) *}

	{* Мобильное меню *}
	<div class="bgWhite">
		<div class="profile-tab_block clearfix fixed m-visible">
			<a href="#about" class="active">{'Обо мне'|t}</a>
			<a href="#kwork">{'Кворки'|t}</a>
			<a href="#reviews">{'Отзывы'|t}</a>
		</div>
	</div>
	{* END Мобильное меню *}

	<div id="app" class="lg-interview">
		{if $interviewData}
			<interview interview-data-json="{base64_encode(json_encode($interviewData))}"></interview>
		{/if}
	</div>

	{* Кворки пользователя *}
	{if $showKworks}
		{include file="user/kworks.tpl"}
	{/if}
	{* END Кворки пользователя *}

	{* Отзывы о пользователе *}
	{include file="user/reviews.tpl"}
	{* END Отзывы о пользователе *}

	{literal}
	<script>
        var profile_user_id = "{/literal}{$userProfile->USERID}{literal}";
        {/literal}{if $isWorker}{literal}
        $(window).load(function () {
            changeProfileAvatar();
        });
        {/literal}{/if}{literal}
        {/literal}{if $config.chat.isFocusGroupMember}{literal}
		var hasConversation = {/literal}{$hasConversation|boolval|@json_encode}{literal};
		var chatRedirectUrl = '/inbox/{/literal}{$userProfile->username}{literal}';
        {/literal}{/if}{literal}
	</script>
	{/literal}

	{Helper::printJsFiles()}
{/strip}
{include file="footer.tpl"}
