<div class="js-tab-content tab-content {if !$tab || $tab == 'general'}active{/if}" data-tab-content="general">
    <form id="settings_form"
            action="{$baseurl}/settings#general"
            enctype="multipart/form-data"
            method="post"
            class="js-main-settings-form"
            autocomplete="off">
        <div class="settings-block">
            
            <div class="f15">
                <div>
                    <div class="mr5 d-inline">
                    {'Адрес публичной страницы вашего профиля:'|t}
                    </div>
                    <a class="break-all-word"
                        target="_blank"
                        href="{$baseurl}/{insert name=get_seo_profile value=a username=$actor->username|stripslashes}">{$baseurl}
                        /{insert name=get_seo_profile value=a username=$actor->username|stripslashes}</a>
                </div>
                {if App::config("module.lang.en_site_enable") && $actor->lang == Translations::DEFAULT_LANG && !$actor->disableEn && (App::config("module.lang.enable") || $actor->isLanguageTester) && ($p.fullnameen == '' && $p.descriptionen == '')}
                    <div class="mt10 d-flex align-items-center">
                        <div>
                            <div class="block-circle block-circle-60 block-circle_orange dib v-align-m">
                                    <i class="ico-info"></i>
                            </div>
                        </div>
                        <p class=" dib v-align-m ml15">
                            {'Чтобы продавать свои услуги англоязычным покупателям, заполните Имя и Информацию о себе на английском.'|t}
                        </p>
                    </div>
                {/if}
            </div>
            <div class="clear"></div>
            <div class="mt30">
                <div class="block-state-active"
                        style="background: none;">
                    <label for="uname">{'Логин'|t}</label>
                    <input maxlength="{$userLoginLength}"
                            class="js-username-input text f16 {if $isLoginChange}read-o{/if}"
                            {if $isLoginChange}readonly{/if}
                            id="uname"
                            name="uname"
                            type="text"
                            value="{$p.username|stripslashes}"
                            autocomplete="new-password"/>
                    <span id="js-username-error"
                            class="js-username-error-field hidden color-red"></span>
                    {if $isLoginChange}
                        <div class="block-state-active_tooltip block-help-image-js"
                                style="right: -275px; top: 10px;">
                            <p class="color-red">{'Сменить логин можно всего один раз, вы уже изменяли свой логин'|t}
                        </div>
                    {else}
                        <div class="block-state-active_tooltip block-help-image-js"
                                style="right: -275px; top: -45px;">
                            <p class="bold">{'Внимание!'|t}</p>
                            <p>{'При изменении логина вы не сможете изменить платежные реквизиты в течение 1 недели.'|t}
                            <p>{'Сменить логин вы можете всего один раз. Убедитесь, что логин верный. Он будет доступен всем пользователям.'|t}
                        </div>
                    {/if}
                </div>
            </div>

            <div class="mt20 block-state-active"
                    style="background: none;">
                <label class="editgigformtitle"
                        for="email">{'Email'}</label>
                <input class="text f16 js-email-input"
                        id="email"
                        name="email"
                        type="email"
                        value="{$p.email|stripslashes}"/>
                <span id="js-email-error"
                        class="hidden color-red js-email-error-field"></span>
                <div class="block-state-active_tooltip block-help-image-js"
                        style="right: -275px; top: -10px;">
                    <p class="bold">{'Внимание!'|t}</p>
                    <p>{'В целях безопасности при изменении Email, вы не сможете изменить платежные реквизиты в течение 1 недели.'|t}
                </div>
                <div class="color-orange f14 email-entry-error mt10"></div>
                {if !$actor->verified}
                    <div style="clear:both"></div>
                    <label>&nbsp;</label>
                    <a style="color: #45b5dc;"
                        href="{absolute_url route="email_confirmation_send"}">{'Повторно подтвердить электронную почту'|t}</a>
                {/if}
            </div>

            {if App::config("module.timezone.enable")}
                <div class="mt20 block-state-active"
                        style="background: none;">
                    <label class="editgigformtitle"
                            for="ftimezone_id">{'Часовой пояс'|t}</label>
                    <select class="text f16"
                            id="ftimezone_id"
                            name="ftimezone_id">
                        {foreach $timezones as $timezone}
                            <option value="{$timezone["id"]}"
                                    {if $timezone["id"] == $p.timezone_id}selected{/if}>
                                ({Timezone::formatString($timezone["utc_offset"])}
                                ) {$timezone["name"]|t}</option>
                        {/foreach}
                    </select>
                    <div class="block-state-active_tooltip block-help-image-js"
                            style="right: -275px; top: 15px;">{'Укажите правильно, чтобы отображалось корректное время в чатах и заказах.'|t}</div>
                </div>
            {/if}

            <input  type="hidden"
                    name="subpass"
                    value="1"/>

            <input  type="hidden"
                    name="foxtoken"
                    value="{$foxtoken}"/>
            <input type="hidden" name="{UserManager::SESSION_CSRF_KEY}" value="{${UserManager::SESSION_CSRF_KEY}}">

            <div class="settings-block__footer">
                <input type="submit"
                        value="{'Сохранить'|t}"
                        class="settings-block__button green-btn"/>

                {if $actor->verified}
                    <div class="delete-account">
                        <a href="javascript:;" class="js-delete-account-link delete-account-link">{'Удалить аккаунт'|t}</a>
                        <delete-account :is-worker-type-actor="{($actor->type === UserManager::TYPE_WORKER)|var_export}" :phone="'{$actor->phone}'"></delete-account>
                    </div>
                {/if}
            </div>

            <div class="row align-items-end">
                <div class="col-6">
                    <div class="mb5"><strong>Пароль</strong></div>
                    <img src="{"/lock.png"|cdnImageUrl}" class="position-r" style="top:5px" alt="Lock">
                </div>
                <div class="col-6 text-right">
                    <button type="button" class="btn btn_color_white btn_size_m js-change-password"><strong>Изменить</strong></button>
                </div>
            </div>
        </div>

        <div class="settings-block mt20" id="password-block" style="display:none">
            <div class="settings-block__inner" id="change-password-block">
                <h3 class="mb5 fs20 fw700 lh38">Изменение пароля</h3>
                <div class="mr5">Заполните данные ниже и нажмите на кнопку.</div>
                <div class="mr5">Вам придет email, в котором потребуется подтвердить смену пароля.</div>
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-12">
                        <div class="mt20 block-state-active no-active" style="background: none;">
                            <span class="js-password-view input-password-trigger"></span>
                            <input class="text f16 pr-4 js-password-old-input"
                                    id="pass_old"
                                    name="pass_old"
                                    size="30"
                                    type="password" 
                                    placeholder="Старый пароль" 
                                    autocomplete="off"/>
                            <small class="lh16 mt8 d-block hidden color-red js-password-old-error-field"></small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-12">
                        <div class="mt20 block-state-active ml-0 mr-0 p-0" style="background: none;">
                            <span class="js-password-view input-password-trigger"></span>
                            <input class="text f16 js-password-input pr-4"
                                    id="pass"
                                    name="pass"
                                    size="30"
                                    type="password" 
                                    placeholder="Новый пароль" 
                                    autocomplete="off"/>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -298px; top: -50px;">
                                <p class="bold">{'Внимание!'|t}</p>
                                <p>{'При изменении пароля, вы не сможете изменить платежные реквизиты в течение 1 недели.'|t}
                            </div>
                            <small class="lh16 mt8 d-block js-password-error-field hidden color-red"></small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-12">
                        <div class="mt20 block-state-active ml-0 mr-0 p-0" style="background: none;">
                            <span class="js-password-view input-password-trigger"></span>
                            <input class="text f16 js-password-confirm-input pr-4"
                                    id="pass2"
                                    name="pass2"
                                    size="30"
                                    type="password" 
                                    placeholder="Новый пароль (еще раз)" 
                                    autocomplete="off"/>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -298px; top: -50px;">
                                <p class="bold">{'Внимание!'|t}</p>
                                <p>{'При изменении пароля, вы не сможете изменить платежные реквизиты в течение 1 недели.'|t}
                            </div>
                            <small class="lh16 mt8 d-block js-password-confirm-error-field hidden color-red"></small>
                        </div>
                    </div>
                </div>
                
                <div class="mt25"><strong>Установите надежный пароль, который:</strong></div>
                <ul class="text-green pl-3 mt5 fs13">
                    <li><span class="text-body">ранее не использовался вами на Kwork</span></li>
                    <li><span class="text-body">состоит не менее чем из 8 символов (букв и цифр)</span></li>
                    <li><span class="text-body">не содержит простых слов, ваше имя, дату рождения, email, номер телефона и другую личную информацию</span></li>
                    <li><span class="text-body">отличается от паролей, которые вы используете на других сайтах</span></li>
                </ul>

                <button type="submit" class="btn--big18 green-btn mt10 pb-3 pt-3 pr-4 pl-4 lh18 wMax-500">Сохранить<br><span class="fs12">и получить email для смены пароля</span></button>
            </div>
            <div class="settings-block__inner" id="change-pass-success" style="display:none">
                <h3 class="mb5 fs20 fw700 lh38">Проверьте email</h3>
                <div class="mr5">В целях безопасности пароль будет изменен только после подтверждения по email. </div>
                <div class="mr5">Вам отправлено письмо. <strong>Перейти по ссылке в письме</strong>, чтобы изменение пароля вступило в силу.</div>
                <button type="button" class="btn--big18 green-btn mt20 js-restore-changepassword">Ок</button>
            </div>
        </div>
    </form>

    {if $actor->lang == Translations::DEFAULT_LANG && App::config("module.lang.en_site_enable")}
        <div class="settings-block mt20">
            <form action="{$baseurl}/settings" class="reset-form" method="post">
                <h1 class="mt10">{'Язык'|t}</h1>
                <div>
                    <input class="styled-checkbox" type="checkbox" name="hideEnKwork" id="hideEnKwork" {if $actor->disable_en_kworks}disabled="disabled" checked="checked"{elseif $actor->hide_en_kworks}checked="checked"{/if}>
                    <label class="editgigformtitle" for="hideEnKwork">{'Скрыть переключение на английскую версию сервиса. Я не работаю с EN покупателями.'|t}</label>
                </div>
                <input type="hidden" name="action" value="language_settings" >
                <input type="hidden" name="foxtoken" value="{$foxtoken}"/>

                <div class="settings-block__footer">
                    <input {if $actor->disable_en_kworks}disabled="disabled"{/if} type="submit" value="{'Сохранить'|t}" class="settings-block__button green-btn" >
                </div>
            </form>
        </div>
    {/if}
    {* @TODO 6699 через месяц после введения для всех выпилить *}
    {*
    {if !\Order\Stages\OrderStageOfferManager::isTester()}
        <div class="p15-20 mt20 white-bg-border w700" >
            <form action="{$baseurl}/settings" class="reset-form" method="post">
                <h1 class="mt10">{'Опции покупателя'|t}</h1>
                <div>
                    <input class="styled-checkbox" type="checkbox" name="disable_report_notification" id="disable_report_notification" {if !$userData->disable_report_notification}checked{/if}>
                    <label class="editgigformtitle" for="disable_report_notification">{'Получать промежуточные отчеты от продавцов'|t}
                        <span class="tooltip_circle dib tooltipster tooltip_circle--hover tooltip_circle--light ml5"
                                data-tooltip-text="{'Если выполнение заказа занимает более 3 дней, то продавцы каждые 2-3 дня высылают промежуточные отчеты о том, как продвигается работа по заказу.'|t}"
                                data-tooltip-side="right">?</span>
                    </label>
                </div>
                <input type="submit" value="{'Сохранить'|t}" class="foxbluebutton hugeGreenBtn GreenBtnStyle h50 pull-reset mt30 mb15 wMax-500" >
                <input type="hidden" name="action" value="payeer_settings" >
                <input type="hidden" name="foxtoken" value="{$foxtoken}"/>
            </form>
        </div>
    {/if} *}
</div>