{if $isNeedShowSumsubVerification}
    <div class="js-tab-content tab-content {if $tab == 'verification'}active{/if}" data-tab-content="verification">
        <div class="p15-20 white-bg-border w700">
            {if !$applicant->is_green}
                <div class="verification-info verification-iframe-preview">
                    <div class="verification-error"></div>
                    <p>Заказчики на Workbay <strong>больше доверяют</strong> и <strong>чаще делают заказы</strong> у верифицированных исполнителей. Мы рекомендуем пройти процедуру подтверждения личности по паспорту.</p>
                    <h3 class="mb10 fs20 fw600 lh38">Безопасность паспортных данных</h3>
                    <p>Workbay не получает и не хранит данные у себя. Для проверки паспортных данных используется надежный и сертифицированный сервис <a href="https://sumsub.com/" target="_blank" rel="nofollow noopener">Sum&Substance, ООО «Технологии Цифровой Безопасности»</a>. Передача, обработка и хранение информации происходит в зашифрованном виде и соответствует требованиям высоких стандартов по безопасности.</p>
                </div>
                <div class="verification-iframe-holder block-state-active mb20" style="background:none">
                    <div id="idensic"></div>
                    <div class="block-state-active_tooltip block-help-image-js" style="right: -275px; top: -45px;">
                        <p>{'Загрузите скан паспорта, сделайте селфи. Система автоматически распознает и проверит данные паспорта. Если все в порядке - вы пройдете верификацию и получите значок "Проверенный исполнитель".'|t}
                    </div>

                </div>
                <div class="verification-info">
                    <h3 class="mb15 fs20 fw600 lh38">Частые вопросы</h3>
                    <div class="support-tree mx-auto">
                        <collapse-list :items="collapseArr"></collapse-list>
                    </div>
                </div>
            {else}
                <div class="mt20 mb20"><h3 class="mb5 text-success fs20 fw600 lh38">Верификация пройдена!</h3><div>Поздравляем! Заказчики больше доверяют верифицированным исполнителям.</div></div>
            {/if}
        </div>
    </div>
{/if}