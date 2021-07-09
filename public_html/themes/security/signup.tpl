{include file="header.tpl"}
{strip}
	{Helper::registerFooterJsFile("/js/signup.js"|cdnBaseUrl)}
	{if !App::isShowAuthCaptcha() && $signipCaptchaEnable}
		{reCAPTCHA::getJS()}
	{/if}
	<div class="w780 mAuto clearfix centerwrap">
		<form id="fox_signup_form" action="{absolute_original_url route="registration_form_handler"}" method="post">
			<div class="w480 m-center-block m-text-center">
				<div class="mt20 m-hidden"></div>
				<div class="mt10 m-visible"></div>
				<h1 class="font-OpenSansBold f36">{'Регистрация'|t}</h1>
				<p class="font-OpenSans f16 lh20">
					{'На %s каждый пользователь одновременно и покупатель, и продавец. Переключить интерфейс можно в любое время.'|t:$currentAppDescription}
				</p>
			</div>
			<div class="w480 select-user-type mt10 m-center-block m-text-center">
				<div class="dib">
					<input name="userType"
						   type="radio"
						   onclick="changeSocLink(1);"
						   id="signup1"
						   value="1"
						   class="payer" checked/>
					<label for="signup1"> {'Покупатель'|t}</label>
				</div>
				<div class="dib ml20">
					<input name="userType"
						   type="radio"
						   onclick="changeSocLink(2);"
						   id="signup2"
						   value="2"
						   class="worker"/>
					<label for="signup2"> {'Продавец'|t}</label>
				</div>
			</div>
			<hr class="gray mb30 mt20 m-hidden">
			<hr class="gray mbi5 mti5 m-visible">

			<div class="w240 pull-right m-hidden">
				<div class="s-btn social-login">
					{if Translations::isDefaultLang()}
						<a href="{getAbsoluteOriginalURL("/login_soc?type=vk&usertype=1")}"
						   class="vk wMax"
						   onclick="if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('SIGNUP');} return true;">
							<span>{'ВКонтакте'|t}</span>
						</a>
					{/if}
					<a href="{getAbsoluteOriginalURL("/login_soc?type=fb&usertype=1")}"
					   class="fb wMax"
					   onclick="if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('SIGNUP');} return true;">
						<span>{'Facebook'|t}</span>
					</a>
				</div>
			</div>
			<div class="m-text-center">
				<div class="w470 dib v-align-m">
					<div id="foxForm">
						<div class="form-entry">
							<input class="text styled-input wMax f15"
								   id="user_email"
								   placeholder="{'Электронная почта'|t}"
								   name="user_email"
								   size="30"
								   type="text" value="{$userEmail|stripslashes}" required />
 							<div class="color-orange font-OpenSans f14 email-entry-error mt10"></div>
						</div>
						<div class="form-entry">
							<input class="text styled-input wMax f15"
								   placeholder="{'Ваш логин (будет виден всем)'|t}"
								   maxlength="{$userLoginLength}"
								   name="user_username"
								   size="15"
								   type="text"
								   value="{$userUsername|stripslashes}" required />
						</div>
						<div class="form-entry">
							<input class="text styled-input wMax f15"
								   placeholder="{'Пароль'|t}"
								   id="user_password"
								   name="user_password"
								   size="30"
								   type="password"
								   value="{$userPassword|stripslashes}"
								   autocomplete="off" required />
						</div>
						{if Translations::getLang() == Translations::EN_LANG}
							<div class="signup-country-block" style="display:none;">
								<div class="form-entry signup-country-field">
									<div class="signup-country-field-row">
										<div>Choose your country</div>
										<select class="input input_size_s" name="country" data-placeholder=" ">
											<option value=""></option>
											{* countryList задается в config.php*}
											{foreach from=$countryList item=country}
												<option value="{$country.id}">{$country.name_en}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-entry signup-country-warning" style="display:none;">
									<i class="icon ico-warningSmall"></i> You can start working right now. But withdrawal to your country will be available in 2 months. Before this earned money will be saved on your account.
								</div>
							</div>
						{/if}
						<div class="form-entry signup-promo-field" {if $userPromo == ''}style="display:none;"{/if}>
							<input class="text styled-input wMax f15"
								   placeholder="{'Введите промокод'|t}"
								   id="user_promo"
								   name="user_promo"
								   size="30"
								   type="text"
								   autocomplete="off"
								   value="{$userPromo}"/>
						</div>
						{if $signipCaptchaEnable}
							<div class="form-entry">
								{reCAPTCHA::getFormField()}
							</div>
						{/if}
						<div class="form-entry">
							{if $userPromo == ''}
								<a class="mt0 signup-promo-placeholder signup-promo-placeholder-js">
									{'У меня есть промокод'|t}
								</a>
							{/if}
							<input type="submit"
								   value="{'Зарегистрироваться'|t}"
								   class="hugeGreenBtn GreenBtnStyle h50 w240 pull-reset v-align-m m-wMax" />
							<div class="dib mt5 v-align-m color-orange font-OpenSans f14 form-entry-error w200 ml10 m-block">
								{include file="fox_error7.tpl"}
							</div>
							<input type="hidden"
								   name="jsub"
								   id="jsub"
								   value="1" />
						</div>
					</div>

				</div>
				<div class="dib v-align-m form-entry-middle font-OpenSans lh247 m-hidden hide-before {if !Translations::isDefaultLang()}form-entry-middle-one-btn-mt{/if}">{'или войти через'|t}</div>
			</div>

			<hr class="gray mb20 mt20 clear m-hidden">

			<div class="t-align-c font-OpenSans f14 pull-right m-pull-reset">
				<span class=" color-gray">{'Уже зарегистрированы?'|t} </span>
				<a class="color-text underline"
				   href="/login">
					{"Войти в %s"|t:$currentAppDescription}
				</a>
			</div>
			<div class="form-entry-middle_mobile m-visible t-align-c mt10">
				<span class="dib">{'или войти через'|t}</span>
				<hr class="gray">
			</div>
			<div class="s-btn social-login clearfix mt15 m-visible mAuto w470">
				{if Translations::isDefaultLang()}
					<a href="{getAbsoluteOriginalURL("/login_soc?type=vk&usertype=1")}"
					   class="vk mb10"
					   onclick="if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('SIGNUP');} return true;">
						<span>{'ВКонтакте'|t}</span>
					</a>
				{/if}
				<a href="{getAbsoluteOriginalURL("/login_soc?type=fb&usertype=1")}"
				   class="fb mb10"
				   onclick="if (typeof (yaCounter32983614) !== 'undefined') { yaCounter32983614.reachGoal('SIGNUP');} return true;">
					<span>{'Facebook'|t}</span>
				</a>
			</div>
			<hr class="gray m-visible mti5 mbi5">
			<div class="font-OpenSans f14 pull-left m-pull-reset m-text-center signup-rules">
				{'Регистрируясь, вы принимаете %sПользовательское соглашение%s и соглашаетесь на email-рассылки'|t:'<a href="/terms" target="_blank" class="color-text underline">':'</a>'}
			</div>
		</form>
	</div>

	<div class="clear mt30"></div>

	<br><br>
{/strip}
{include file="footer.tpl"}