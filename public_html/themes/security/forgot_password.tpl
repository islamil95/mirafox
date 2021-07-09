{include file="header.tpl"}
{strip}
	{include file="fox_error7.tpl"}
	<div class="popup_has-mobile-version">
		<div class="forgot-form">
			<div class="overlay"></div>
			<div class="popup_content">
				<div class="flex-column">
					<div class="flex-column__content">
						<div class="flex-column__content-mobile">
							<a href="javascript:;" onclick="mobile_menu_toggle();" class="popup__menu_theme_mobile">
								<i class="fa fa-bars"></i>
							</a>
							<div class="popup__title_theme_mobile">{'Восстановление пароля'|t}</div>
							<div class="popup__logo"></div>
						</div>
						<h1 class="t-align-c font-OpenSans f32 mt20 forgot-title">{'Забыли пароль?'|t}</h1>
						<div class="bgWhite p20 mAuto w480 centerwraps forgot-content">
							<div id="foxForm">
								<form action="{absolute_url route="forgot_password_form_handler"}" method="post">
									<div class="form-entry">
										<input class="text styled-input wMax f15"
											   id="email"
											   placeholder="{'Электронная почта'|t}"
											   name="email"
											   tabindex="1"
											   type="email"
											   value="" required/>
									</div>
									<div class="form-entry form-entry-recaptcha">
										{if App::isShowAuthCaptcha()}
											{reCAPTCHA::getFormField()}
										{/if}
									</div>
									<div class="row">
										<input type="submit"
											   value="{'Отправить'|t}"
											   class="hugeGreenBtn GreenBtnStyle h50 pull-reset wMax popup__button_theme_orange">
									</div>
									{if $errorMessage}
										<div class="form-entry-error">
											<div class="color-orange mt5">
												<p>{$errorMessage}</p>
											</div>
										</div>
									{/if}
									<input type="hidden" name="fpsub" id="fpsub" value="1"/>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{literal}
	<script>
		function resizeCaptcha() {
			if (isMobile()) {
				var captchaWidth = 302;
				var captchaHeight = 76;
				var captchaWrapper = $('.form-entry-recaptcha');
				var captchaElements = $('#rc-imageselect, .g-recaptcha');
				var scale = 1;
				scale = (1 - (captchaWidth - captchaWrapper.width()) * (0.05 / 15));
				captchaElements.css('transform', 'scale(' + scale + ')');
				captchaWrapper.height(captchaHeight * scale);

				$('body').addClass('compensate-for-scrollbar');
			}
		}
		$(function(){
			resizeCaptcha();
			$(window).on('resize', function() {
				resizeCaptcha();
			});
		});
	</script>
	{/literal}
{/strip}
{include file="footer.tpl"}