<div class="js-tab-content tab-content {if $tab == 'profile'}active{/if}" data-tab-content="profile">
    <div class="settings-block">
        <form id="profile_form"
            action="{$baseurl}/settings#profile"
            enctype="multipart/form-data"
            method="post"
            class="js-profile-settings-form">

            {if Translations::isDefaultLang()}
                <div style="display:flex; justify-content: space-between; align-items: flex-end">
                    <div style="width:49%" >
                        <div class="block-state-active"
                                style="background: none;">
                            <label for="fname">{'Имя'|t}</label>
                            <input class="text js-settings__fname f16 js-name-input js-stopwords-check"
                                    id="fname"
                                    name="fname"
                                    type="text"
                                    value="{$p.fullname|stripslashes}"/>
                            <div class="fname-message color-gray f12 mt10 hidden js-name-error-field">{'Возможен ввод только русских букв'|t}</div>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -611px; top: 15px;">{'Будет видно пользователям, повышает доверие к вам.'|t}</div>
                        </div>
                    </div>
                    {* @Выпилено, но возможно надо будет вернуть *}
                    {*
                    <div style="width:49%">
                        <div class="block-state-active"
                                style="background: none;">
                            <label for="first_letter_from_last_name"><span class="m-hidden">{'Фамилия (достаточно первой буквы)'|t}</span><span class="m-visible">{'Фамилия (первая буква)'|t}</span></label>
                            <input class="text f16 js-lastname-input"
                                    id="first_letter_from_last_name"
                                    name="first_letter_from_last_name"
                                    type="text"
                                    maxlength="1"
                                    value="{$p.first_letter_from_last_name|stripslashes}"/>
                            <div class="first_letter_from_last_name-message color-gray f12 mt10 hidden js-lastname-error-field">{'Возможен ввод только русских букв'|t}</div>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -275px; top: 15px;">{'Нужно, чтобы покупатели не путались с одинаковыми именами.'|t}</div>
                        </div>
                    </div>
                    *}
                </div>
            {else}
                <div class="row">
                    <div class="col-sm-6 mb20">
                        <div class="block-state-active"
                                style="background: none;">
                            <label for="fnameen">{'Имя'|t}</label>
                            <input class="text js-settings__fname f16 js-nameen-input js-stopwords-check"
                                    id="fnameen"
                                    name="fnameen"
                                    type="text"
                                    value="{$p.fullnameen|stripslashes}"/>
                            <div class="fnameen-message color-gray f12 mt10 hidden js-nameen-error-field">{'Возможен ввод только русских букв'|t}</div>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -275px; top: 15px;">{'Будет видно пользователям, повышает доверие к вам.'|t}</div>
                        </div>
                    </div>
                    {* @Выпилено, но возможно надо будет вернуть *}
                    {*
                    <div class="col-sm-6 mb20">
                        <div class="block-state-active"
                                style="background: none;">
                            <label for="first_letter_from_last_name_en">{'Фамилия (достаточно первой буквы)'|t}</label>
                            <input class="text f16 js-lastnameen-input js-stopwords-check"
                                    id="first_letter_from_last_name_en"
                                    name="first_letter_from_last_name_en"
                                    type="text"
                                    maxlength="1"
                                    value="{$p.first_letter_from_last_name_en|stripslashes}"/>
                            <div class="first_letter_from_last_name-message color-gray f12 mt10 hidden js-lastnameen-error-field">{'Возможен ввод только русских букв'|t}</div>
                            <div class="block-state-active_tooltip block-help-image-js"
                                    style="right: -275px; top: 15px;">{'Нужно, чтобы покупатели не путались с одинаковыми именами.'|t}</div>
                        </div>
                    </div>
                    *}
                </div>
            {/if}

            <div class="settings-avatar-block_wrapper mt20">
                <div class="block-state-active no-bg">
                <div class="settings-avatar-block">
                    <label class="editgigformtitle">{'Фото'|t}</label>
                    <div class="clearfix"></div>
                    <div style="display: inline-block">
                        <div class="js-add-photo">
                            <div class="js-template-photo-block" style="display:none;">
                                <div class="add-photo__file-wrapper file-wrapper long-touch-js m0">
                                    <div class="upload-file-place js-file-wrapper-block-container add-files_icon">
                                        <div class="file-wrapper-block-settings-avatar js-file-add-button"></div>
                                        <input type="file"
                                                class="js-file-input"
                                                accept=".jpeg, .jpg .png"
                                                data-pre-upload="true"
                                                data-pre-upload-type="with-progressbar">
                                    </div>
                                    <div style="text-align: center; height: 25px; padding-top: 5px;">
                                    <span class="js-file-add-button button f14 link-color dibi mt6i ml7 mr7"
                                            data-init-text="{'Выбрать'|t}" data-edit-text="{'Изменить'|t}">{'Выбрать'|t}
                                    </span>
                                        <div class="dib ml5 js-delete hidden ml7 mr7">
                                            <span class="f14 link-color">{'Удалить'|t}</span>
                                        </div>
                                    </div>
                                    <input class="js-photo-size" type="hidden" value="">
                                    <input class="js-delete-photo" type="hidden" value="0">
                                </div>
                            </div>
                            <div class="js-photo__block"></div>
                            <div class="js-photo-resize__block mt10">
                                <img src="{"/empty.png"|cdnImageUrl}"
                                        class="js-photo-resize__image"
                                        style="max-width: 100%; height: auto;display:none;"
                                        alt="">
                            </div>
                            <div class="add-photo_error js-add-photo_error"></div>
                        </div>
                    </div>
                    <div class="profile_error_container"></div>
                </div>
                <div class="block-state-active_tooltip block-help-image-js"
                        style="right: -275px; top: 15px;">
                    <p>{'Личное фото хорошего качества укрепляет доверие клиентов. Но в целях анонимности вы можете загрузить любой аватар.'|t}</p>
                    <p>{'Форматы: jpg, jpeg, png. Максимальный размер не ограничен, но вес не более 10Мб. Минимальный размер 200*200px.'|t}</p>
                </div>
                </div>
            </div>
            <div class="mt20 block-state-active"
                    style="background: none;">
                <label for="profession">{'Вы по специальности'|t}</label>
                <input class="text f16"
                        id="profession"
                        name="profession"
                        type="text"
                        maxlength="50"
                        value="{$p.profession|stripslashes}"/>
                <div class="block-state-active_tooltip block-help-image-js"
                        style="right: -275px; top: -15px;">{'Одной короткой фразой опишите, кто вы по специальности на Kwork, например, «Бэкенд программист PHP + MySQL» (50 символов ограничение).'|t}</div>
            </div>

            {if $actor->lang == Translations::DEFAULT_LANG && $showRuDescription}
                <div class="mt20 block-state-active"
                        style="background: none;">
                    <label class="editgigformtitle"
                            for="details">
                        {"Информация о вас"|t}
                    </label>
                    <textarea
                            class="textarea-styled-no-height wMax js-stopwords-check js-details-default-input"
                            id="details"
                            name="details"
                            rows="11"
                            data-min="{UserManager::MIN_USER_DESCRIPTION_LENGTH}"
                            data-max="{UserManager::MAX_USER_DESCRIPTION_LENGTH}"
                            maxlength="{UserManager::MAX_USER_DESCRIPTION_LENGTH}"
                            placeholder="{'Навыки, опыт, специализация...'|t}">{$p.description|stripslashes}</textarea>
                    <span class="js-details-default-error-field color-red hidden"></span>
                    <div class="color-gray f12 pt10">
                        {'Максимальная длина - %s символов.'|t:1200}
                        <span class="js-detailsused-title ml5 mr5" style="display: none;">{"Сейчас:"|t}</span>
                        <span class="js-detailsused"></span>
                    </div>
                    <div class="pull-right mt10 color-gray f12 italic ml10 js-details-limit-field"></div>
                    <div class="pull-left mt10 color-red f12 hidden js-details-default-error-field"></div>
                    <div class="clear"></div>
                    <div class="block-state-active_tooltip block-help-image-js"
                            style="right: -275px; top: 15px;">{'Расскажите клиентам о вашем опыте и навыках. Покажите им, что вы – тот, кого они ищут.'|t}
                        <ul class="mt15 pl15">
                            <li class="mb5">{'Опишите свои сильные стороны и навыки'|t}</li>
                            <li class="mb5">{'Расскажите о важных проектах и опыте'|t}</li>
                            <li>{'Пишите кратко и без ошибок'|t}</li>
                        </ul>
                    </div>

                </div>
            {else}
                <div class="mt20 en_desc">
                    <label class="editgigformtitle"
                            for="detailsen">
                        {"Информация о вас"|t}
                    </label>
                    <textarea
                            class="textarea-styled-no-height wMax js-detailsen-input js-stopwords-check"
                            id="detailsen"
                            name="detailsen"
                            rows="11"
                            data-min="{UserManager::MIN_USER_DESCRIPTION_LENGTH}"
                            placeholder="{'Навыки, опыт, специализация...'|t}">{$p.descriptionen|stripslashes}</textarea>
                    <div class="pull-right mt10 color-gray f12 italic ml10 js-details-limit-field"></div>
                    <div class="pull-left color-red f12 mt10 hidden js-detailsen-error-field"></div>
                    <div class="clear"></div>
                </div>
            {/if}
            <div class="js-stopwords-warning-container"></div>

            <div class="mt20" style="display: flex; align-items: flex-end; justify-content: space-between">
                <div style="width:49%">
                    <label class="editgigformtitle"
                            for="fcountry">{'Страна'|t}</label>
                    <input class="text f16"
                            id="fcountry"
                            name="fcountry"
                            size="30"
                            type="text"
                            {if Translations::isDefaultLang()}
                                value="{$p.countryname|stripslashes}"
                            {else}
                                value="{$p.countrynameen|stripslashes}"
                            {/if}
                    />
                    <input id="fcountry_id"
                            name="fcountry_id"
                            type="hidden"
                            value="{$p.countryId}"/>
                </div>
                <div class="fcity-block"
                        style="{if !$showCityField }display:none;{/if}width:49%">
                    <label class="editgigformtitle"
                            for="fcity">{'Город (по желанию)'|t}</label>
                    <input class="text f16"
                            id="fcity"
                            name="fcity"
                            size="30"
                            type="text"
                            {if Translations::isDefaultLang()}
                                value="{$p.cityname|t|stripslashes}"
                            {else}
                                value="{$p.citynameen|stripslashes}"
                            {/if}
                    />
                    <input id="fcity_id"
                            name="fcity_id"
                            type="hidden"
                            value="{$p.cityid}"/>
                </div>
            </div>

            <div class="settings-cover-block_wrapper">
                <div class="settings-cover-block mt20 block-state-active"
                        style="background: none;">
                    <label class="editgigformtitle">{'Шапка профиля'|t}</label>
                    <div class="js-add-photo">
                        <div class="js-template-photo-block"
                                style="display:none;">
                            <div class="add-photo__file-wrapper file-wrapper long-touch-js"
                                    style="margin: 0;">
                                <div class="upload-file-place js-file-wrapper-block-container add-files_icon">
                                    <div class="file-wrapper-block-settings-cover js-file-add-button"></div>
                                    <input type="file"
                                            class="js-file-input"
                                            accept=".jpeg, .jpg, .png"
                                            data-pre-upload="true"
                                            data-pre-upload-type="with-progressbar">
                                </div>
                                <div style="text-align: center; height: 25px; padding-top: 5px;">
                                    <span class="js-file-add-button button f14 link-color dibi mt6i ml7 mr7"
                                            data-init-text="{'Выбрать'|t}" data-edit-text="{'Изменить'|t}">{'Выбрать'|t}</span>
                                    <div class="dib js-delete hidden ml7 mr7">
                                        <span class="f14 link-color">{'Удалить'|t}</span>
                                    </div>
                                </div>
                                <input class="js-photo-size"
                                        type="hidden"
                                        value="">
                                <input class="js-delete-photo"
                                        type="hidden"
                                        value="0">
                            </div>
                        </div>
                        <div class="js-photo__block"
                                style="text-align: center;">
                        </div>
                        <div class="js-photo-resize__block mt10">
                            <img src="{"/empty.png"|cdnImageUrl}"
                                    class="js-photo-resize__image"
                                    style="max-width: 100%; height: auto;display:none;"
                                    alt="">
                        </div>
                        <div class="add-photo_error js-add-photo_error"></div>
                    </div>
                    <div class="profile_error_container"></div>
                    <div class="block-state-active_tooltip block-help-image-js"
                            style="right: -275px; top: 15px;">
                        <p>{'Изображение вверху страницы вашего публичного профиля. Помогает выделиться на фоне конкурентов, сформировать свой бренд.'|t}</p>
                        <p>{'Форматы: jpg, jpeg, png. Максимальный размер не ограничен, но вес не более 10Мб. Минимальный размер 1366*206px.'|t}</p>
                    </div>
                </div>
            </div>

            <input type="submit"
                    value="{'Сохранить'|t}"
                    class="foxbluebutton hugeGreenBtn GreenBtnStyle h50 pull-reset mt30 mb15 wMax-500 btn-disable-toggle"/>
            <input type="hidden"
                    name="subform"
                    value="1"/>
            <input type="hidden"
                        name="foxtoken"
                        value="{$foxtoken}"/>
            <input type="hidden" name="{UserManager::SESSION_CSRF_KEY}" value="{${UserManager::SESSION_CSRF_KEY}}">
        </form>
    </div>
</div>