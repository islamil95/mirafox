<div class="js-tab-content tab-content {if $tab == 'finances'}active{/if}" data-tab-content="finances">
    <div class="settings-block"
            id="withdraw-block">
        <form action="{$baseurl}/settigns_finances"
                class="reset-form js-payments-settings-form"
                id="edit-purse__form"
                method="post">
            {$withdrawSystem = User\UserWithdrawSystemManager::getUserWithdrawSystem()}
            {if $actor->lang == Translations::DEFAULT_LANG}
                {include
                    file="settings/purse.tpl"
                    type="webmoney"
                    purseName=Translations::t("Кошелек WebMoney")
                    number=$p.webmoneyId
                    secureNumber=$p.webmoneyIdSecure
                    iconClass="icon-webmoney"
                    hint=Translations::t("Введите R или P-кошелек. Например: R000000000000 или P000000000000")
                }
                {include
                    file="settings/purse.tpl"
                    type="qiwi"
                    purseName=Translations::t("Кошелек Qiwi")
                    number=$p.qiwiId
                    secureNumber=$p.qiwiIdSecure
                    iconClass="icon-qiwi"
                    onlyNumberClass="js-input-number" 
                    hint=Translations::t("Введите номер телефона. Например: 79510000071")
                }
            {/if}
            {if $withdrawSystem != User\UserWithdrawSystemManager::SOLAR}
                {include
                    file="settings/purse.tpl"
                    type="yandex"
                    purseName=Translations::t("Кошелек Яндекс.Деньги")
                    number=$p.yandexId
                    secureNumber=$p.yandexIdSecure
                    iconClass="icon-yandex"
                    onlyNumberClass="js-input-number"
                    hint=Translations::t("Введите номер счета")
                }
            {/if}
            {if $withdrawSystem == User\UserWithdrawSystemManager::PAYMORE}
                {include
                    file="settings/purse.tpl"
                    type="card"
                    purseName=Translations::t("Банковская карта")
                    number=$p.cardId
                    secureNumber=$p.cardIdSecure
                    iconClass="ico-mastercardVisa"
                    hint=Translations::t("Введите номер карты. Например: 1234000000005678")
                }
            {else}
                {include file="settings/solar_card.tpl"}
            {/if}

            <input type="submit"
                    value="{'Сохранить кошельки'|t}"
                    class="js-payments-settings-submit green-btn disabled h50 fs20 pull-reset mt30 mb15 wMax-500" disabled/>
            <input type="hidden"
                    name="sub_purse"
                    value="1"/>
            <input type="hidden"
                    name="foxtoken"
                    value="{$foxtoken}"/>
            <input type="hidden" name="{UserManager::SESSION_CSRF_KEY}" value="{${UserManager::SESSION_CSRF_KEY}}">
        </form>
        <form class="solar-card-reverify-form"
                method="post">
            <input name="action"
                    value="card_reverify"
                    type="hidden"/>
        </form>
        <div class="solar-card-verify-popup-content"
                style="display: none;">
            <h1 class="popup__title">{'Привязать карту'|t}</h1>
            <hr class="gray mt20 balance-popup__line">
            <div class="pb10">{'Чтобы воспользоваться возможностью вывода на карту, необходимо выполнить одноразовую привязку карты к аккаунту. Средства на карту выводятся в рублях. Если валюта карты отличается от рубля, будет произведена конвертация по курсу на дату платежа. Введите ваши настоящие имя, фамилию и подтвердите номер телефона.'|t}</div>
            <div id="foxForm">
                <form id="form-card-verify"
                        method="post"
                        onkeypress="return event.keyCode != 13;">
                    <input class="card-verify__currency"
                            name="currency"
                            value="RUB"
                            type="hidden"/>
                    <input name="action"
                            value="card_verify"
                            type="hidden"/>
                    <div>
                        <label for="popup_fname" class="db"><b>{'Укажите актуальные данные'|t}</b></label>
                        <div class="form-entry mt10 dib"
                                style="width: 48%;">
                            <input class="text styled-input h40 lh40 f15 noInvalidOutline"
                                    placeholder="{'Имя'|t}"
                                    value="{$p.first_name|stripslashes}"
                                    id="popup_fname"
                                    name="popup_fname"
                                    tabindex="1"
                                    type="text">
                        </div>
                        <div class="form-entry pull-right mt10 dib"
                                style="width: 48%;">
                            <input class="text styled-input h40 lh40 f15 noInvalidOutline"
                                    placeholder="{'Фамилия'|t}"
                                    value="{$p.last_name|stripslashes}"
                                    id="popup_lname"
                                    name="popup_lname"
                                    tabindex="2"
                                    type="text">
                        </div>
                        <div>
                            <label for="solar_card_full_number">
                                <strong>{'Номер карты'|t}</strong>
                            </label>
                            <div class="form-entry mt10">
                                <input name="solar_card_full_number"
                                        type="text"
                                        id="solar_card_full_number"
                                        value=""
                                        class="text styled-input h40 lh40 f15 noInvalidOutline"
                                        placeholder="{'Укажите полный номер карты, от 16 до 20 цифр'|t}">
                            </div>
                        </div>
                        <div class="color-red f12 error-block hidden mb10"></div>
                        <div class="color-red f12 fio-error-block hidden mb10"></div>
                        <div class="form-entry phone-verify-block1 {if $actor->phone_verified}hidden{/if}">
                            <label for="popup_phone"><b>{'Подтвердите мобильный телефон'|t}</b></label>
                            <div class="mt5">{'На ваш телефон будет выслано SMS-сообщение с кодом активации. Введите код активации и подтвердите номер телефона. Плата за SMS не взимается.'|t}</div>
                            <div class="mt15 settings-phone-row">
                                <div class="settings-phone-title">
                                    <span class="dib">{'Номер телефона'|t}</span>
                                </div>
                                <div class="settings-phone-code">
                                    <div class="dib">
                                        <select name="phone_country_code"
                                                id="popup_phone_country_code">
                                            {foreach $countryList as $country}
                                            <option value="{$country.phone_code}">{$country.name}
                                                (+{$country.phone_code}
                                                )
                                            </option>
                                            {/foreach}
                                        </select>
                                        <div style="text-align: center;">
                                            <i class="f10"
                                                style="vertical-align: top;">{'код страны'|t}</i>
                                        </div>
                                    </div>
                                </div>
                                <div class="settings-phone-number">
                                    <div class="dib"
                                            style="text-align: center;">
                                        <input class="text styled-input h40 lh40 f15 noInvalidOutline"
                                                value="{$p.phone|stripslashes}"
                                                id="popup_phone"
                                                name="popup_phone"
                                                tabindex="3"
                                                type="text"
                                                required><br/>
                                        <i class="f10"
                                            style="vertical-align: top;">{'номер телефона'|t}</i>
                                    </div>
                                </div>
                                <div class="settings-phone-sms">
                                    <div class="dib card-verify-sms">
                                        <a href="javascript:void(0);"
                                            class="js-phone-verify phone-verify-link">{'Выслать sms'|t}</a>
                                        <div class="phone-verify-error color-red f12 mt10"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-entry phone-verify-code-block mt10 hidden">
                                <div class="phone-verify-code-block__input">
                                    <span class="dib"
                                            style="float: none; vertical-align: top; margin-top: 5px; width: 105px;">{'Код из sms'|t}</span>
                                    <input class="text styled-input f15 noInvalidOutline phone-verify-code"
                                            placeholder="{'Введите код'|t}"
                                            value=""
                                            id="popup_confirm_code"
                                            name="confirm_code"
                                            tabindex="4"
                                            type="text">
                                    <a href="javascript:void(0);"
                                        class="js-phone-code-check dib"
                                        style="margin-left: 5px;">{'Подтвердить'|t}</a>
                                </div>
                                <span class="color-red f12 hidden phone-verify-code-error mt10"
                                        style="display: block;font-style:italic;"></span>
                            </div>
                            <div class="form-entry phone-verify-error-block color-red f12"></div>
                        </div>
                        <div class="form-entry phone-verify-block2 {if !$actor->phone_verified}hidden{/if}"
                                style="line-height: 27px;">
                            <label for="popup_phone">
                                {$tPhone = "<span class=\"color-green phone-verify-number mr0 float-initial\">{(UserManager::getMaskedPhone($p.phone, true)|stripslashes)}</span>"}
                                <b>{'Номер телефона %s успешно подтвержден!'|t:$tPhone}</b>
                            </label>
                            <div>{'Нажмите «Продолжить» для перехода в систему Solar Staff и привязки карты'|t}</div>
                            <div class="phone-verify-autoredirect-message hidden f12">
                                {$tTime = '<span class="phone-verify-redirect-timer">5</span>'}
                                {'Через %s сек. вы будете перемещены автоматически.'|t:$tTime}
                            </div>
                            <div class="row form-entry">
                                <button id="js-solar-button-continue"
                                        class="green-btn mt10 h45 f16 w200 m-wMax"
                                        formnovalidate>{'Продолжить'|t}</button>
                                <br>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>