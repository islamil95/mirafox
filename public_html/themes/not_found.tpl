{include file="header_not_found.tpl"}
{Helper::registerFooterJsFile("https://yastatic.net/es5-shims/0.0.2/es5-shims.min.js")}
{Helper::registerFooterJsFile("https://yastatic.net/share2/share.js")}
{Helper::registerFooterJsFile("/js/js.cookie.js"|cdnBaseUrl)}

{strip}
	<div class="page-404">
		<div class="page-404_logo">{'СТРАНИЦА НЕ НАЙДЕНА'|t}</div>
		<div class="page-404_img1{if !Translations::isDefaultLang()} page-404_img1_en{/if}">
			<div></div>
		</div>
		<div class="page-404_img2{if !Translations::isDefaultLang()} page-404_img2_en{/if}">
			<a href="/faq"></a>
		</div>
		<div class="page-404_img3{if !Translations::isDefaultLang()} page-404_img3_en{/if}">
			<a href="/"></a>
		</div>
	</div>
	<div class="js-ya-share2 hidden ">
		<div class="t-align-c">
			<i class="icon ico-invite-friends"></i>
			<h3 class="mb6 fw600">Пригласи друзей и заработай</h3>
			<div class="m-center-block fw300">Расскажи друзьям о Kwork и получай вознаграждение по партнерской
				программе
			</div>
			<div class="ta-center">
				<div class="mt10 ya-share2"
					 data-services="facebook,vkontakte,twitter,odnoklassniki,gplus"
					 data-bare="true"
					 data-url="{$baseurl}{if $actor}?ref={$actor->id}{/if}"
				></div>
                {if $actor}<input class="wMax styled-input mt10 ta-center" onclick="$(this).select();" readonly=""
								  value="{$baseurl}?ref={$actor->id}">{/if}
			</div>
		</div>
	</div>
{/strip}
{literal}
	<script>
		$(document).on('click', '.page-404_img1>*', function () {
			var content = $('.js-ya-share2').clone().removeClass('hidden').html();
			show_popup(content, '', undefined, 'd-flex align-items-center');
		});
	</script>
{/literal}
{include file="footer.tpl"}