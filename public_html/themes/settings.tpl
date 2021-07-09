{strip}
{Helper::printCssFile("/css/dist/profile-settings.css"|cdnBaseUrl, "all")}
{Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{Helper::printCssFile("/css/setting_foreign_card.css"|cdnBaseUrl)}
{* lineProgressbar *}
{Helper::printCssFile("/css/components/jquery.lineProgressbar.css"|cdnBaseUrl)}
{Helper::printJsFile("/js/components/jquery.lineProgressbar.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/resemble.js"|cdnBaseUrl)}
<style>
	.thumb {
		height: 135px;
	}
	.popup .popup_content.popup-card-confirm {
		max-width: 600px;
	}
	.popup .popup_content.popup-card-confirm #foxForm .form-entry span {
		float: none;
		margin-right: 25px;
	}
	.popup .popup_content.popup-card-confirm #popup_phone {
		height: auto;
		line-height: 30px;
		display: inline-block;
		margin-left: 5px;
		width: 140px;
	}
	.popup .popup_content.popup-card-confirm .phone-verify-error {
		font-style: italic;
	}
	.phone-verify-error-timer, .phone-verify-redirect-timer {
		float: none;
		margin-right: 0 !important;
	}
	.popup .popup_content.popup-card-confirm #popup_confirm_code {
		display: inline-block;
		height: auto;
		line-height: 30px;
		width: 130px;
	}
	.popup .popup_content.popup-card-confirm .phone-verify-autoredirect-message {
		font-style: italic;
	}
	.popup .popup_content.popup-card-confirm .phone-verify-error-block {
		font-style: italic;
	}
	.popup .chosen-container-single .chosen-single span {
		display: inline-block;
		margin-top: -1px;
		font-size: 15px;
	}
	.popup .phone-verify-link {
		line-height: 13px;
		display: inline-block;
	}
	.card-verify-sms {
		display: flex;
		align-items: center;
		max-width: 90px;
		height: 40px;
		margin-left: 5px
	}
	.card-verify-sms .phone-verify-link {
		margin-top: 0 !important;
	}

	.tab-content:not(.active) {
		display: none;
	}


    .settings_tabs .settings_tabs-item {
        display: inline-block;
        font-size: 18px;
        padding: 0 20px;
        color: #a0a0a0;
        position: relative;
        transition: color 0.25s ease-in-out;
        line-height: 52px;
        white-space: nowrap;
        border-bottom: 1px solid #ccc;
        float: left;
        margin-left: 5px;
    }

    .settings_tabs .settings_tabs-item:first-child {
        margin-left: 0;
    }

    .settings_tabs .settings_tabs-item > a {
        color: #a0a0a0 !important;
    }

    .settings_tabs .settings_tabs-item:hover,
    .settings_tabs .settings_tabs-item:hover > a,
    .settings_tabs .settings_tabs-item.active {
        color: #000 !important;
        cursor: pointer;
    }

    .settings_tabs .settings_tabs-item:before {
        content: '';
        position: absolute;
        right: 0;
        bottom: -1px;
        left: 0;
        background: #87b948;
        height: 2px;
        opacity: 0;
        transition: opacity 0.25s ease-in-out;
    }

    .settings_tabs .settings_tabs-item:hover:before,
    .settings_tabs .settings_tabs-item.active:before {
        opacity: 1;
    }

	.input-password-trigger {
		width: 16px;
		height: 16px;
		position: absolute;
		padding: 25px 0;
		top: 0;
		right: 10px;
		z-index: 10;
		cursor: pointer;
		background: url('/images/eye-sprite.png') 0 18px no-repeat;
	}
	.input-password-trigger:hover {
		background: url('/images/eye-sprite.png') 100% 18px no-repeat;
	}

    @media only screen and (max-width: 550px) {

        .settings_tabs .settings_tabs-item {
            display: flex;
            width: 48.5%;
            flex-wrap: nowrap;
            justify-content: center;
            padding: 0;
        }
		.settings_tabs .settings_tabs-item:nth-child(3) {
			margin-left:0
		}
    }

</style>
{Helper::printJsFile("/js/jquery-ui.min.autocomplete.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/kwork_new_edit.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/jquery.imgareaselect.min.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
{include file="fox_error7.tpl"}
{literal}
	<script>
		var countryAutocomplete = (function () {
			var _countriesWithCity = {};
			var _cityCache = {};
			var $_countryFields = {};
			var $_cityFields = {};

			var _getCitySource = function (request, response) {
				var countryId = $_countryFields['id'].val() ^ 0;
				$.get('/city_autocomplete', {term: request.term, 'country_id': countryId}, function (result) {
					var responseData = [];
					for (var i = 0; i < result.length; i++) {
						_cityCache[result[i].id] = result[i];

						responseData.push({
							'id': result[i].id,
							'value': result[i].value,
							'label': result[i].label
						});

						if (result[i].value.toLowerCase() == $_cityFields['input'].val().toLowerCase()) {
							_setCity(result[0].id);
						}

						if(responseData.length == 0) {
						    _setCity(0);
                                                    _cleanCity();
                                                }
					}
					response(responseData);
				}, 'json');
			};
			var _getCountrySource = function (request, response) {
				$.get('/country_autocomplete', {term: request.term}, function (result) {
					var responseData = [];
					for (var i = 0; i < result.length; i++) {
						if (!_countriesWithCity.hasOwnProperty(result[i].id)) {
							_countriesWithCity[result[i].id] = result[i].hasCities;
						}

						responseData.push({
							'id': result[i].id,
							'value': result[i].value
						});

						if (result[i].value.toLowerCase() == $_countryFields['input'].val().toLowerCase()) {
							_setCountry(result[0].id);
						}
					}
					if(responseData.length == 0) {
					    _setCountry(0);
                                            _cleanCountry();
                                            _setCity(0);
                                            _cleanCity();
                                        }
					response(responseData);
				}, 'json');
			};

			var _setCountry = function (countryId) {
				$_countryFields['id'].val(countryId).trigger('change');
			};
                        var _cleanCountry = function () {
                                $_countryFields['input'].val('');
                        };
			var _setCity = function (cityId) {
				$_cityFields['id'].val(cityId).trigger('change');
			};
                        var _cleanCity = function () {
                                $_cityFields['input'].val('');
                        };

			var _showCity = function (countryId) {
				countryId = countryId ^ 0;
				if (!countryId || _countriesWithCity[countryId]) {
					$('.fcity-block').show();
				} else {
					$('.fcity-block').hide();
				}
			};

			var _onChangeCity = function (cityId) {
				var city = _cityCache[cityId];
				if (typeof city == 'object') {
					$_countryFields['input'].val(city.country_name);
					$_countryFields['id'].val(city.country_id);
				}
			};

			return {
				init: function () {
					$_countryFields['input'] = $("#fcountry");
					$_countryFields['id'] = $("#fcountry_id");
					$_cityFields['input'] = $("#fcity");
					$_cityFields['id'] = $("#fcity_id");
					$_cityFields['input'].autocomplete({
						source: _getCitySource,
						minLength: 2,
						select: function (event, ui) {
							_setCity(ui.item.id);
						}
					});

					$_countryFields['input'].autocomplete({
						source: _getCountrySource,
						minLength: 2,
						select: function (event, ui) {
							_setCountry(ui.item.id);
                                                        _setCity(0);
                                                        _cleanCity();
						}
					});

					$_countryFields['input'].on('input', function () {
						if ($(this).val() == '') {
							_setCountry(0);
                                                        _cleanCountry();
                                                        _setCity(0);
                                                        _cleanCity();
						}
					});

					$_countryFields['id'].change(function () {
						_showCity($(this).val());
					});

					$_cityFields['id'].change(function () {
						_onChangeCity($(this).val());
					});
				}
			}
		})();

		$(window).load(function () {
			countryAutocomplete.init();
		});
	</script>
{/literal}
{literal}
	<script>
        var headerCover = new KworkPhotoModule();
        var settingsCover = new KworkPhotoModule();
        var avatarPhoto = new KworkPhotoModule();
        $(window).load(function () {
            headerCover.init({
                selectorBlock: '.profile-cover-change-block',
                maxCount: 1,
                fileWrapperClass: 'file-wrapper-block-profile-cover',
                fileInputName: 'cover-photo',
                widthRatio: 1366,
                heightRatio: 205,
                minWidth: 1366,
                minHeight: 206,
                photos: {/literal}{$coverPhotosJson}{literal}
            });
            settingsCover.init({
                selectorBlock: '.settings-cover-block',
                maxCount: 1,
                iasParent: '.settings-cover-block_wrapper',
                fileWrapperClass: 'file-wrapper-block-settings-cover',
                fileInputName: 'cover-photo',
                widthRatio: 1366,
                heightRatio: 205,
                minWidth: 1366,
                minHeight: 206,
                photos: {/literal}{$coverPhotosJson}{literal}
            });
            avatarPhoto.init({
                selectorBlock: '.settings-avatar-block',
                maxCount: 1,
                iasParent: '.settings-avatar-block_wrapper',
                fileWrapperClass: 'file-wrapper-block-settings-avatar',
                fileInputName: 'avatar-photo',
                widthRatio: 1,
                heightRatio: 1,
                minWidth: 200,
                minHeight: 200,
                photos: {/literal}{$avatarPhotosJson}{literal}
            });
        });
        function showChangeProfileCover() {
            $(".profile-cover-change-block").slideToggle("fast");
            headerCover.cancelModule();
        }
	</script>
{/literal}
<div id="app">
	{control name="user_top" USERID=$actor->id uname=$actor->username desc=$actor_desc fullname=$actor->fullname live_date=$actor->live_date rating=$actor->cache_rating profilepicture=$actor->profilepicture cover=$actor->cover contentType="viewProfile"}
	<!--Всплывающий блок с загрузкой картинки в шапку профиля-->
	<div class="profile-cover-change-block centerwrap">
		<form id="profile-cover-change-form" action="{$baseurl}/settings?action=change_cover" enctype="multipart/form-data" method="post">
			<div class="js-add-photo">
				<div class="js-template-photo-block" style="display:none;">
					<div class="add-photo__file-wrapper file-wrapper long-touch-js">
						<div class="upload-file-place js-file-wrapper-block-container">
							<div class="file-wrapper-block-profile-cover js-file-add-button"></div>
							<input type="file"
								   class="js-file-input"
								   accept=".jpeg, .jpg, .gif, .png"
								   data-pre-upload="true"
								   data-pre-upload-type="with-progressbar">
						</div>
						<div style="text-align: center; height: 25px; padding-top: 5px;">
							<span class="js-file-add-button button f14 link-color dibi mt6i ml7 mr7"
								  data-init-text="{'Выбрать'|t}" data-edit-text="{'Изменить'|t}">{'Выбрать'|t}
							</span>
							<div class="dib js-delete hidden ml7 mr7">
								<span class="f14 link-color">{'Удалить'|t}</span>
							</div>
						</div>
						<input class="js-photo-size" type="hidden" value="">
						<input class="js-delete-photo" type="hidden" value="0">
					</div>
				</div>
				<div class="js-photo__block" style="text-align: center;"></div>
				<div class="js-photo-resize__block mt10">
					<img src="{"/empty.png"|cdnImageUrl}"
						 class="js-photo-resize__image"
						 id="resize-img-top"
						 style="max-width: 100%; height: auto;display:none;" alt="" >
				</div>
				<div class="add-photo_error js-add-photo_error"></div>
			</div>
			<div class="profile_error_container"></div>
			<div class="profile-cover-change-block_controls">
				<button type="button" class="hoverMe red-btn w135 mt20 f16" style="height:40px;" onclick="showChangeProfileCover();">{'Отменить'|t}</button>
				<button class="hoverMe green-btn w135 mt20 f16 ml30" style="height:40px;">{'Сохранить'|t}</button>
			</div>
		</form>
	</div>
	<!--END Всплывающий блок с загрузкой картинки в шапку профиля-->
	<div class="centerwrap clearfix pt20 pb20">
		<h1 class="f32">{'Настройки'|t}</h1>

		<div class="user-portfolio__panel mb20 clearfix">
			<div class="">
				<div id="settings_tabs" class="settings_tabs js-tabs">
					<a href="#general" class="settings_tabs-item js-tab {if !$tab || $tab == 'general'}active{/if}" data-tab-id="general">{'Общие'|t}</a>
					<a href="#profile" class="settings_tabs-item js-tab {if $tab == 'profile'}active{/if}" data-tab-id="profile">{'Профиль'|t}</a>
					<a href="#finances"  class="settings_tabs-item js-tab {if $tab == 'finances'}active{/if}" data-tab-id="finances">{'Финансы'|t}</a>
					{if $isNeedShowSumsubVerification}
						<a href="#verification" class="settings_tabs-item js-tab {if $tab == "verification"}active{/if}" data-tab-id="verification">{'Верификация'|t}</a>
					{/if}
				</div>
			</div>
		</div>
		<div class="kwork-settings-wrapper d-flex justify-content-between">

				<div class="kwork-settings contentArea mb0 w700">
					<div id="foxPostForm" class="p0">
						<div data-id="settings_tabs" class="js-tab-content-wrapper">
							{include file="settings/general.tpl"}
							{include file="settings/profile.tpl"}
							{include file="settings/finances.tpl"}
							{include file="settings/verification.tpl"}
						</div>
					</div>
				</div>

			<div class="kwork-settings-sidebar sidebarArea change-position-mobile w268">
				{if $showInterviewRequest}
					<interview-request interview-profiles-json='{base64_encode(json_encode($interviewProfiles))}' interview-settings = '{$interviewSettings}'>
					</interview-request>
				{/if}
			</div>
		</div>
	</div>
</div>

{include file="setting_foreign_card.tpl"}

{literal}
	<script>
		var jcrop_api = 'start';
		var userLoginLength = {/literal}{$userLoginLength}{literal};

		function updateThinkCharsCount(idElement, idUpdate) {
			var used = $('#' + idElement).val().length;
			$('#' + idUpdate).html(used);
		}

		$(window).load(function () {

			updateGigDescCharsCount();

			$('#wm_purse').prop('placeholder', t('Введите R или P-кошелек. Например: R000000000000 или P000000000000'));

			$('#qiwi_purse').prop('placeholder', t('Введите номер телефона. Например: 79510000071'));

			$('#card_purse').prop('placeholder', t('Введите номер карты. Например: 1234000000005678'));

		});

		//редактирование кошельков
		function editSolar(target) {
			var parent = $(target).closest('.settings-solar-card-info');

			parent.toggleClass('active');
			parent.find('.color-red').text('');
		}

		//удаление кошельков
		function removeSolar(type) {
			var html = '' +
				'<div>' +
				'<h1 class="popup__title">' + t('Удаление кошелька') + '</h1>' +
				'<hr class="gray" style="margin-bottom:32px;">' +
				'<div style="display:inline-block;width:100%;">' +
				'<p class="f15 pb50 ml10">' + t('В целях безопасности средств вывод на новый кошелек будет доступен через 1 неделю после изменения реквизитов.<br>Удалить кошелек?') + '</p>' +
				'<button class="popup__button red-btn" onclick="$(\'#' + type + '_purse\').val(\'\'); $(\'#edit-purse__form\').submit();">' + t('Удалить') + '</button>' +
				'<button class="popup__button white-btn pull-right popup-close-js" onclick="return false;">{/literal}{'Отменить'|t}{literal}</button></div>' +
				'</div>' +
				'</div>';
			show_popup(html);
		}

		function showConfirmPurseBlock(text) {
			var html = '' +
				'<div>' +
				'<h1 class="popup__title">' + t('Требуется подтверждение') + '</h1>' +
				'<hr class="gray" style="margin-bottom:32px;">' +
				'<div style="display:inline-block;width:100%;">' +
				'<p class="f15 pb50 ml10">' + text + '</p>' +
				'<button class="popup__button green-btn pull-right popup-close-js" onclick="return false;">' + t('Ок') + '</button></div>' +
				'</div>' +
				'</div>';
			show_popup(html, 'popup-confirm-purse-block', '', 'popup-confirm-purse-block', true);
		}

		function removeCardConfirmation() {
			var html = '' +
					'<div>' +
					'<form method="post" action="/settings_card_remove">' +
					'<input type="hidden" name="action" value="card_remove" />' +
					'<h1 class="popup__title">' + t('Удаление карты') + '</h1>' +
					'<hr class="gray" style="margin-bottom:32px;">' +
					'<div style="display:inline-block;width:100%;">' +
	{/literal}
	{if time() > strtotime($p.next_card_link_available)}
			'<p class="f15 pb50 ml10">' + t('Удалить прикрепленную карту?') + '</p>' +
	{else}
			'<p class="f15 pb50 ml10">' + t('В целях безопасности средств привязать новую карту вы сможете не ранее {literal}{{0}}{/literal}.<br/>Удалить карту?', ['{$p.next_card_link_available|date}']) + '</p>' +
	{/if}
	{literal}
			'<button class="popup__button red-btn" onclick="$(this).closest(\'form\').submit();">' + t('Удалить') + '</button>' +
					'<button class="popup__button white-btn pull-right popup-close-js" onclick="return false;">{/literal}{'Отменить'|t}{literal}</button></div>' +
					'</div>' +
					'</form>' +
					'</div>';
			show_popup(html);
		}
		$('#settings_form #email').change(function () {
			$('#settings_form .email-entry-error').text('');
            var badMailHost = checkBadEmailDomains($(this).val());
            if (badMailHost !== false && badMailHost.length > 1) {
                $('#settings_form .email-entry-error').text(t('{{0}} не принимает сообщения от Kwork. Используйте другой email, пожалуйста', [upstring(badMailHost)]));
            }
        });

        function deselect() {
            $("#hideEnKwork").prop("checked", false);
        }

        function updateGigDescCharsCount() {
            var used = $("#details").val().length;
            $(".js-detailsused-title").show();
            $(".js-detailsused").html(used);
        }

        $(document).ready(function () {
            var scrollTo = "{/literal}{$scrollTo|htmlspecialchars}{literal}";
            if (scrollTo === "worker_options") {
                $('html, body').animate({
                    scrollTop: $("#workerOptions").offset().top
                }, 200);
            }

            $("#details").keyup(function () {
                updateGigDescCharsCount();
            });

			$('.js-change-password').on('click', function(){
				$('#password-block').fadeToggle();
			});

			$('.js-restore-changepassword').on('click', function(){
				$('.settings-block__inner').fadeToggle();
				$('[type="password"]').val('');
			});

			$('.js-password-view').hover(function(){
				var currentInput = $(this).parent().find('input');
				if (currentInput.attr('type') == 'password') {
					currentInput.attr('type', 'text');
				} else {
					currentInput.attr('type', 'password');
				}
			});

			$('.js-password-view').blur(function(){
				$('.js-password-view').parent().find('input').attr('type', 'password');
			});

            {/literal}{if $enOrderInWork > 0}{literal}
            $("#hideEnKwork").on("change", function () {
                if ($("#hideEnKwork").prop("checked")) {
                    var gigsLangForm = declension({/literal}{$enOrderInWork}{literal}, t("ваш англоязычный кворк будeт остановлен."), t("ваших англоязычных кворка будут остановлены."), t("ваших англоязычных кворков будут остановлены."));
                    var html = '' +
                        '<div>' +
                        '<h1 class="popup__title">' + t('Подтверждение') + '</h1>' +
                        '<hr class="gray" style="margin-bottom:32px;">' +
                        '<div style="display:inline-block;width:100%;">' +
                        '<p class="f15 pb50 ml10">' + t('Вы уверены, что хотите отключить английскую версию? В этом случае {{0}} {{1}}', [{/literal}{$enOrderInWork}{literal}, gigsLangForm]) + '</p>' +
                        '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left popup-close-js f16" style="height:40px;">' + t('Да') + '</button>' +
                        '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" onClick="deselect();" style="height:40px;">' + t('Нет') + '</button></div>' +
                        '</div>' +
                        '</div>';
                    show_popup(html);
                }
            });
            {/literal}{/if}{literal}

            {/literal}
            {if $needConfirmPurseBlock}
			{literal}
				showConfirmPurseBlock("{/literal}{$confirmPurseBlockText}{literal}");
            {/literal}
            {/if}
            {literal}
        });
    </script>
{/literal}

	{* bootstrap modal *}
	{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
	{Helper::printJsFile("/js/setting_foreign_card.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/dist/profile-settings.js"|cdnBaseUrl)}
	{Helper::printJsFiles()}
	{Helper::printJsFile("/js/settings.js"|cdnBaseUrl)}
	{if $isNeedShowSumsubVerification}
		{*Тут запрашивается внешний скрипт для prod/dev разные хосты*}
		{Helper::printJsFile("{$sumSubHost}/idensic/static/sumsub-kyc.js")}
	{/if}
{/strip}
