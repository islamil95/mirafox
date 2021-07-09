{strip}
    {include file="fox_error7.tpl"}
    <div class="centerwrap">
<div class="w780 mAuto clearfix">
    <div class="mt20 m-hidden"></div>
    <div class="mt10 m-visible"></div>
    <h1 class="font-OpenSansBold f32  m-text-center">{'Вход'|t}</h1>
    <hr class="gray mb30 mt20 m-hidden">
    <hr class="gray mbi5 m-visible">

    <div class="m-w470 w240 pull-right m-hidden">
        
            <div class="s-btn social-login">
                {if Translations::isDefaultLang()}
                <a href="/login_soc?type=vk" class="vk" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); } return true;"><span>{'ВКонтакте'|t}</span></a>
                {/if}
                <a href="/login_soc?type=fb" class="fb" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); } return true;"><span>Facebook</span></a>
            </div>

    </div>
 
    <div id="foxForm">
        <form action="{$canonicalOriginUrl}" method="post" class="m-text-center">
            <div class="w470 dib v-align-m">
				<div class="form-entry">
					<input class="text styled-input wMax f15" placeholder="{'Электронная почта или логин'|t}" id="l_username" name="l_username"  type="email" value="{$user_username}" required />
				</div>
				<div class="form-entry">
					<input class="text styled-input wMax f15" id="l_password" placeholder="{'Пароль'|t}" name="l_password" size="30" type="password" required />
				</div>
				{if App::isShowAuthCaptcha()}
					<div class="form-entry">{reCAPTCHA::getFormField()}</div>
				{/if}
				<div class="row form-entry">
					<input type="submit" value="{"Войти"|t}" class="hugeGreenBtn GreenBtnStyle h50 pull-reset w240 dib-imp v-align-m m-wMax" formnovalidate />
					<div class=" color-orange font-OpenSans f14 form-entry-error w200 dib v-align-m ml10 m-block">
                        <div class="color-orange mt5"><p>{$error2}</p></div>
                    </div>
					<input type="hidden" name="jlog" id="jlog" value="1" />
				</div>
            </div>
            <div class="dib v-align-m form-entry-middle font-OpenSans h85 m-hidden hide-before {if !Translations::isDefaultLang()}form-entry-middle-one-btn-mt{/if}">{'или войти через'|t}</div>
            <hr class="gray mb30 mt20 clear m-hidden">
			{if Translations::isDefaultLang()}
                <div class="t-align-c font-OpenSans f14 mt5 pull-right m-hidden">
                    <span class=" color-gray">{'Не зарегистрированы?'|t} </span>
                    <a class="color-text underline f14" href="{$baseurl}/signup">{'Зарегистрироваться'|t}</a>
                </div>
            {/if}
             <div class="m-visible clearfix w470 mAuto ">
                 <a href="{$baseurl}/forgotpassword" class="pull-right f14 font-OpenSans color-text underline ">{'Забыли пароль?'|t}</a>
                <div class="pull-left options">
                    <input class="checkbox dib" id="l_remember_me_page_mobile" name="l_remember_me" type="checkbox" value="1" checked />
                    <label class="f14 font-OpenSans mb0 label" for="l_remember_me_page_mobile">{'Запомнить меня'|t}</label>
                </div>
               
            </div>
            <div class="pull-left m-hidden">
                <div class="dib v-align-m options">
                    <input class="checkbox dib" id="l_remember_me_page_desktop" name="l_remember_me" type="checkbox" value="1" checked />
                    <label class="f14 font-OpenSans mb0 label" for="l_remember_me_page_desktop">{'Запомнить меня'|t}</label>
                </div>
                <a href="{$baseurl}/forgotpassword" class="dib ml20 v-align-m f14 font-OpenSans color-text underline ">{'Забыли пароль?'|t}</a>
            </div>
        </form>
    </div>
    <div class="form-entry-middle_mobile m-visible t-align-c mt10">
        <span class="dib">{'или войти через'|t}</span>
        <hr class="gray ">
    </div>
    
    
    <div class="s-btn social-login clearfix mt15 m-visible w470 mAuto">
        {if Translations::isDefaultLang()}
            <a href="/login_soc?type=vk" class="vk mb10" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); } return true;"><span>{'ВКонтакте'|t}</span></a>
        {/if}
        <a href="/login_soc?type=fb" class="fb mb10" onclick="if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); } return true;"><span>{'Facebook'|t}</span></a>
    </div>
     <hr class="gray m-visible mti5 mbi5">
	{if Translations::isDefaultLang()}
      <div class="t-align-c font-OpenSans f13   m-visible  ">
          <span class=" color-gray div v-align-m">{'Не зарегистрированы?'|t} </span>
          <a class="color-text underline  div v-align-m" href="{$baseurl}/signup">{'Зарегистрироваться'|t}</a>
      </div>
    {/if}
</div>
   </div>
<br><br>
{literal}
    <script>
        $(window).load(function(){
            var trackClientId = getTrackClientId();
            $("#foxForm form").append('<input type="hidden" name="track_client_id" value="' + trackClientId + '" />');
        });
    </script>
{/literal}
{/strip}