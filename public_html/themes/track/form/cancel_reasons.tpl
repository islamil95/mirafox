<div class="js-cancel-order-main-popup-template hidden{if isAllowToUser($order->USERID)} isPayer{/if}">
    <form action="{absolute_url route="track_cancel_order"}" method="post">
        <h1 class="popup__title">
            <span class="js-inprogress-cancel-default">{'Отмена заказа'|t}</span>
            {if isAllowToUser($order->USERID)}
                <span class="js-inprogress-cancel-reject hidden">{'Отправить заказ в Арбитраж?'|t}</span>
            {/if}
        </h1>
        <input type="hidden" name="orderId" value="{$order->OID}">
        <hr class="gray mt20" style="margin-bottom:20px;">
        <div class="js-inprogress-cancel-default">
            {if isAllowToUser($order->worker_id)}
                <div class="message--warning">
                    {'В случае неуважительной причины отмены заказа рейтинг ВСЕХ кворков снижается. Настоятельно рекомендуем выполнить данный заказ.'|t}
                </div>
            {/if}
            {if $cancelReasons|@count == 1}
                {foreach from=$cancelReasons item=reason key=id}
                    <input name="reason" type="hidden" value="{$id}" >
                {/foreach}
            {else}
                <div class="order-cancel-form__reason">
							<span class="dib mb10">
								{if isAllowToUser($order->worker_id)}
                                    {'Если вы все же намерены отменить заказ, выберите причину:'|t}
                                {else}
                                    {'Выберите причину отмены:'|t}
                                {/if}
							</span>
                    <div class="popup-reasons f14 mt8 js-fixed-tooltip" style="padding-left:5px;">
                        {foreach from=$cancelReasons item=reason key=id}
                            {assign var="boldItem" value=true}
                            {if $reason.track_status=="payer_inprogress_cancel"}
                                {include file="./reason_item.tpl"}
                            {/if}
                        {/foreach}
                        <div class="mt5"></div>
                        {foreach from=$cancelReasons item=reason key=id}
                            {assign var="boldItem" value=false}
                            {if $reason.track_status!="payer_inprogress_cancel"}
                                {include file="./reason_item.tpl"}
                            {/if}
                            {if $id=="worker_payer_is_dissatisfied" || $id=="worker_force_cancel"}
                                <div class="request-not-correspond request-not-correspond_theme_popup" style="display: none">
                                    <div class="request-not-correspond__title bold">
                                        {'Проект покупателя не соответствует кворку?'|t}
                                        <i class="ico-arrow-down request-not-correspond__title-icon"></i>
                                    </div>
                                    <div class="request-not-correspond__more-text">
                                        <p>{'Kwork лучше ранжирует и чаще показывает покупателям кворки, по которым совершено больше покупок. <span class="request-not-correspond__warning">Отказываясь от заказа, вы теряете возможность повысить конверсию и уступаете место другим продавцам.</span>'|t}</p>
                                        <p><b>{'Что делать?'|t}</b></p>
                                        <ol class="request-not-correspond__list">
                                            <li class="request-not-correspond__list-item">{'Создайте новые опции в своем кворке и предложите их покупателю. Покупатели приветствуют принцип «любой каприз за ваши деньги».'|t}</li>
                                            <li class="request-not-correspond__list-item">{'Создайте новый кворк, который покрывает задачу покупателя. Возможно и другие покупатели в будущем закажут его.'|t}</li>
                                            {if $actor->level > 1}
                                                <li class="request-not-correspond__list-item">{'Предложите <a href="/faq#question-%s">индивидуальный кворк</a> под задачу покупателя.'|t:App::config("faq.individual_offer_{Translations::getLang()}")}</li>
                                            {/if}
                                        </ol>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            {/if}
            <div class="mt20 order-cancel-form__comment"
                 {if $cancelReasons|@count > 1}style="display:none;"{/if}>
                <label>
                    {if isAllowToUser($order->USERID)}
                        {'Комментарий для продавца'|t}
                    {else}
                        {'Напишите, почему Вы хотите отменить заказ:'|t}
                    {/if}
                    <textarea id="message_body3" name="message" rows="5" class="{if isAllowToUser($order->worker_id)}js-required{/if} control-en textarea-styled wMax f14 mt8" style="padding-left:5px; padding-right:5px;"></textarea>
                </label>
            </div>
            <hr class="gray mt20">
            {* Чекбокс скрытия продавца при отмене *}
            {if $order->USERID == $actor->id}
                <div class="popup-hide-kworks clearfix mt20 js-fixed-tooltip">
                    <input name="hide_all_user_kworks" type="checkbox" class="styled-checkbox" id="hideAllUserKworks" value="{$order->worker_id}">
                    <label for="hideAllUserKworks">
                        {'Скрывать кворки продавца'|t}
                        &nbsp;<span class="tooltip-wrapper-mobile ml5">
									<span class="tooltip_circle dib tooltipster tooltip_circle--hover tooltip_circle--light" data-tooltip-text="{'Скрыть все кворки продавца из поиска и каталога.'|t}" data-tooltip-side="right" data-tooltip-theme="dark">?</span>
								</span>
                    </label>
                </div>
            {/if}
        </div>
        {if isAllowToUser($order->USERID)}
            <div class="js-inprogress-cancel-reject hidden">
                {if $order->inprogressCancelRejectCounter() == 2}
                    {'Вы уже третий раз пытаетесь отменить этот заказ.'|t}
                {else}
                    {'Вы уже более трех раз пытаетесь отменить этот заказ.'|t}
                {/if} {'Возможно, вам требуется помощь третьей стороны, - Арбитра Kwork?'|t}
            </div>
        {/if}

        <div class="popup__buttons mt20">
            <button type="button"
                    class="popup__button js-cancel-order-submit red-btn">
                <span class="js-inprogress-cancel-default">{'Отменить заказ'|t}</span>
                <span class="js-inprogress-cancel-reject hidden">{'Нет, не требуется'|t}</span>
            </button>
            <button type="reset"
                    class="popup__button pull-right popup-close-js green-btn">{'Пока не отменять'|t}</button>
            <a href="/arbitrage?track_type={if $order->isCheck()}payer_check_arbitrage{else}payer_inprogress_arbitrage{/if}&order_id={$order->OID}" class="popup__button pull-right green-btn js-inprogress-cancel-reject hidden lh40" style="width: {if Translations::getLang() == Translations::isDefaultLang()}165px{else}185px{/if}">{'Да, в Арбитраж'|t}</a>
            <div class="clearfix"></div>
        </div>
    </form>
</div>
<div class="js-cancel-order-subtype-popup-template hidden">
    <form action="{absolute_url route="track_cancel_order"}" method="post">
        <h1 class="popup__title">{'Уточнение причины отказа'|t}</h1>
        <input type="hidden" name="orderId" value="{$order->OID}">
        <hr class="gray mt20" style="margin-bottom:20px;">
        {foreach from=$cancelReasons item=reason key=id}
            {if isset($reason["subtypes"])}
                <input name="parentReason" type="hidden"
                       class="hidden js-cancel-order-parent-reason-input">
                <div data-parent-reason="{$id}" class="js-subreasons">
                    {foreach from=$reason["subtypes"] item=subreason key=subid}
                        <div>
                            <input name="reason"
                                   class="styled-radio js-required js-popup-cancel-form__reason js-cancel-order-reason-input"
                                   id="reason-{$subid}-placeholder" type="radio" value="{$subid}"
                                   data-is-payer="{isAllowToUser($order->worker_id)}"/>
                            <label for="reason-{$subid}">{$subreason.name}</label>
                        </div>
                    {/foreach}
                    <div class="">
                        {foreach from=$reason["subtypes"] item=subreason key=subid}
                            <div class="js-subreason-help hidden" data-subreason-help="{$subid}">
                                <hr class="gray mt20">
                                {foreach from=$subreason["help"] item=helpItem}
                                    {if $helpItem["type"] eq "header"}
                                        <span class="bold db mt10">{$helpItem["content"]}</span>
                                    {elseif $helpItem["type"] eq "paragraph"}
                                        <p>{$helpItem["content"]}</p>
                                    {/if}
                                {/foreach}
                            </div>
                        {/foreach}
                    </div>
                    <div class="mt20 js-cancel-order-comment">
                        <label>
                            {'Ваш комментарий:'|t}
                            <textarea id="message_body4" name="message" rows="5"
                                      class="{if isAllowToUser($order->worker_id)}js-required{/if} control-en textarea-styled wMax f14 mt8"
                                      style="padding-left:5px; padding-right:5px;"></textarea>
                        </label>
                    </div>
                </div>
            {/if}
        {/foreach}
        <div class="js-cancel-order-additional mt20" style="line-height:10px;">
            <i class="f12 color-gray"></i>
        </div>
        <hr class="gray mt20">
        <div class="popup__buttons mt20">
            <button type="submit" class="popup__button js-cancel-order-submit red-btn disabled mt0"
                    disabled>{'Отменить заказ'|t}</button>
            <button type="button" style="height:40px;font-size:16px;display:none;"
                    class="js-cancel-order-back red-btn mt0">{'Отменить по другой причине'|t}</button>
            <button type="reset"
                    class="popup__button popup-close-js green-btn pull-right mt0">{'Вернуться к заказу'|t}</button>
            <div class="clearfix"></div>
        </div>
    </form>
</div>