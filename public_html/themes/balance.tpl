{include file="header.tpl"}

{Helper::printCssFile("/css/fontawesome.v5.min.css"|cdnBaseUrl)}

{strip}

    {if $message}
        {include file="fox_error7.tpl"}
    {/if}
    <div class="m-hidden">
        {control name="user_top" USERID=$actor->id uname=$actor->username desc="type" fullname=$actor->fullname live_date=$actor->live_date rating=$actor->cache_rating profilepicture=$actor->profilepicture cover=$actor->cover}
    </div>
    {if $error}
        {control name="event" type="message" data=$error}
    {/if}

    <div class="page-balance">
        <div class="centerwrap clearfix pt20 pb0 block-response">
            <h1 class="f32">{'Баланс'|t}</h1>
            <div class="balance-info-block">
                {if !$userBlocked}
                    {if $billCount > 0 && $actor->lang == Translations::DEFAULT_LANG}
                        <a href="{$baseurl}/bill_list" class="dib pull-right" style="line-height: 0">{'Выставленные счета'|t}</a>
                    {/if}
                {/if}

                <div class="balance-info-block__summ">
                    {include file="utils/currency.tpl" lang=$actor->lang total=$actor->totalFunds}
                </div>
                <div class="balance-info-block__action">
                    <div class="btn-group btn-group-js btn-group_2 dib v-align-m page-balance_btn-group">
                        {if !$userBlocked}
                            <div class="green-btn  balance-refill-btn-js" id="increase-balance" onclick="toggleBalanceRefillPopup();show_balance_popup('', 'balance');">{'Пополнить баланс'|t}</div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        {if $total > 20 && !$userBlocked}
            <div class="centerwrap clearfix pt20 pb0 block-response">
            <div class="filter-balance__block hide">
                <div class="filter-balance__form">
                    <form>
                        <div class="fields__row">
                            <div class="field__inline w100-min">
                                <input type="text" id="from" name="from" max="{$dateLimit.maxDate}" min="{$dateLimit.minDate}" class="input js-input__calendar w110" data-position="bottom left" placeholder="{'От'|t}" data-placeholder="{'От'|t}">
                                <span class="js-input__calendar-clear"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><polygon points="10,0.7 9.3,0 5,4.3 0.7,0 0,0.7 4.3,5 0,9.3 0.7,10 5,5.7 9.3,10 10,9.3 5.7,5"/></svg></span>
                                <i class="far fa-calendar-alt calendar-mini"></i>
                            </div>
                            <div class="field__inline w100-min">
                                <input type="text" id="to" name="to" class="input js-input__calendar w110" data-position="bottom left" placeholder="{'До'|t}" data-placeholder="{'До'|t}">
                                <span class="js-input__calendar-clear"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><polygon points="10,0.7 9.3,0 5,4.3 0.7,0 0,0.7 4.3,5 0,9.3 0.7,10 5,5.7 9.3,10 10,9.3 5.7,5"/></svg></span>
                                <i class="far fa-calendar-alt calendar-mini"></i>
                            </div>
                            <div class="field__inline w358px js-multiselectGroup type-operation-select">
                                <select multiple="multiple" class="js-select__checkbox" name="type-operation">
                                    <option value="all" selected>{'Все операции'|t}</option>
                                    {if $positiveOperations|@count ne "0"}
                                    <optgroup label="{'Пополнение'|t}">
                                        {foreach from=$positiveOperations item=operation}
                                        <option value="{$operation.name}">{$operation.text|t}</option>
                                        {/foreach}
                                    </optgroup>
                                    {/if}
                                    {if $negativeOperations|@count ne "0"}
                                    <optgroup label="{'Списание'|t}">
                                        {foreach from=$negativeOperations item=operation}
                                            <option value="{$operation.name}">{$operation.text|t}</option>
                                        {/foreach}
                                    </optgroup>
                                    {/if}
                                </select>
                            </div>
                            <div class="field__inline w358px select__single">
                                <select name="name-kwork" placeholder="{'Название кворка'|t}" class="js-select__single filter-kwork-name mobile-css-style">
                                    <option selected value="">{'Название кворка'|t}</option>
                                    {foreach from=$ordersList item=kwork}
                                        <option value="{$kwork.kwork_id}">{$kwork.kwork_title}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {/if}
        <div class="centerwrap clearfix mt0 pb20 block-response mt15 m-p0" id="operations-result">
            {if $o|@count eq "0"}
                <div class="mt30 dib">
                    {'У Вас нет ни одного заказа.'|t}
                    {if !$userBlocked}
                        &nbsp;<a href="{$baseurl}/">{'Начать работу!'|t}</a>
                    {/if}
                </div>
            {else}
                {include file="balance_table.tpl"}
            {/if}
        </div>
    </div>

    {Helper::registerFooterJsFile("/js/dist/balance.js"|cdnBaseUrl)}
    {Helper::registerFooterCssFile("/css/dist/balance.css"|cdnBaseUrl)}

    {Helper::printJsFile("/js/datepicker.min.js"|cdnBaseUrl)}
    {Helper::printJsFile("/js/datepicker.en.js"|cdnBaseUrl)}
    {Helper::printCssFile("/css/datepicker.min.css"|cdnBaseUrl)}

    {Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
    {Helper::printJsFile("/js/bootstrap-multiselect.js"|cdnBaseUrl)}
    {Helper::printJsFile("/js/chosen-mobile-true.jquery.min.js"|cdnBaseUrl)}

    {Helper::printCssFile("/css/bootstrap-multiselect.css"|cdnBaseUrl)}
    {literal}
        <script>
            // Необходимо ли указать дополнительные данные
            var isNeedFillForeignSolarData = {/literal}{$user->isNeedFillForeignSolarData|intval}{literal};

            $(function() {
                var typeOptions = [];
                //собираем список опций типа операции для дальнейшего использования
                $("select[name=type-operation] option").each(function(){
                   typeOptions.push($(this).val());
                });

                if($(".js-select__single option").length == 1){
                    $(".select__single").css("visibility", "hidden");
                }

                $('.filter-balance__block').removeClass('hide');
                var selectChackbox = $('.js-select__checkbox');

                selectChackbox.multiselect({
                    enableClickableOptGroups: true,
                    numberDisplayed: 1,
                    selectAllText: 'Все операции',
                    nonSelectedText: 'Все операции',
                    nSelectedText: 'выбрано',
                    allSelectedText : 'Все операции',
                    includeSelectAllOption: false,
                    selectAllNumber: false,
                    maxHeight: 242,
					onChange: function(event) {
                        changeBalanceFilter(true, true);
                    }

                });

                var selectSelectAll = $('.multiselect-container li:first-child'),
                    selectNotSelectAll= $('.multiselect-container li:not(:first-child)');

                selectSelectAll.on('click', function() {
                    if($(this).hasClass('active')){
                        return false
                    }else{
                        selectChackbox.multiselect('deselect',  typeOptions);
                        selectChackbox.multiselect('select', ['all'], true);
                        selectChackbox.find('option[value=all]').prop('selected', true);
                    }

                    selectChackbox.multiselect("refresh");
                });


                selectNotSelectAll.on('click', function() {
                    selectSelectAll.removeClass('active');
                    selectSelectAll.find('input').prop('checked', false);
                    selectChackbox.find('option[value=all]').prop('selected', false);

                });

                selectChackbox.on('change', function() {
                    var countSelect = $('.multiselect-container li').length,
                        countSelectActive = $('.multiselect-container li.active').length;
                    if(countSelect == countSelectActive + 1 || countSelectActive == null){
                        selectChackbox.multiselect('deselect', typeOptions);
                        selectChackbox.multiselect('select', ['all']);
                        selectChackbox.find('option[value=all]').prop('selected', true);
                    }

                    var typesVal = selectChackbox.val();
                    if(!typesVal){
                        selectChackbox.multiselect('select', ['all']);
                    }else{
                        $(".select__single").show();
                        selectChackbox.multiselect("refresh");
                    }
                });

                $('.js-select__single').multiselect({
                    maxHeight: 242
                });
 
                var calendarInput = $('.js-input__calendar'),
                    calendarClear = $('.js-input__calendar-clear');
                var minDate = new Date($("#from").attr("min"));
                var maxDate = new Date($("#from").attr("max"));

                $("#from, #to").on("change", function (e) {
                    var id = "#" + $(this).attr("id");
                    if($(this).val() != ""){
                        var from = $(this).val().split(".");
                        var date = new Date(from[2], from[1] - 1, from[0]);
                        var myDatepicker =  $(id).datepicker().data('datepicker');

                        if(date.getTime() <= myDatepicker.maxDate.getTime() && date.getTime() >= myDatepicker.minDate.getTime()){
                            myDatepicker.selectDate(date);
                        }else{
                            $(id).val("");
                            $(id).next().removeClass("show");
                            $(id).parent().find("i").show();
                        }
                    }else{
                        $(id).next().removeClass("show");
                        $(id).parent().find("i").show();
                    }
                });


                calendarInput.datepicker({
					language: lang,
                    minDate: minDate,
                    maxDate: maxDate,
                    navTitles: {
                        days: 'MM yyyy'
                    },
                    onHide: function(datetime){
                        calendarClose();
                    },
                    onSelect: function(formattedDate, date, inst) {
                        //задаем лимиты для полей "от", "до"
                        if(inst.el.attributes[1].nodeValue == "from"){
                            if(!date){
                                date =  minDate;
                            }
                            $("#to").datepicker({
                                minDate: new Date(date)
                            });

                        }else{
                            if(!date){
                                date =  maxDate;
                            }
                            $("#from").datepicker({
                                maxDate: new Date(date)
                            });
                        }
                        $(inst.el).trigger('change1');

                        if ($("#" + inst.el.attributes[1].nodeValue).val() == "") {
                            $(inst.el).next().removeClass('show');
                            $(inst.el).parent().find("i").show();
                        }else{
                            $(inst.el).next().addClass('show');
                            $(inst.el).parent().find("i").hide();
                        }
                    }
                });
                calendarInput.change(function() {
                    if($(this).val() != "") {
                        $(this).next().addClass('show');
                    }
                    else
                        $(this).next().removeClass('show');
                });

                calendarInput.focus(function() {
                    $(this).attr('placeholder', '');
                });
                calendarInput.blur(function() {
                    var calendarPlaceholder = $(this).attr('data-placeholder');
                    $(this).attr('placeholder', calendarPlaceholder);
                });
                calendarClear.click(function() {

                    $(this).prev().val('');
                    $(this).removeClass('show');

                    var calendarPlaceholder = $(this).prev().attr('data-placeholder');
                    var myDp = $(this).prev().datepicker().data('datepicker');
                    myDp.clear();
                    $(this).prev().attr('placeholder', calendarPlaceholder);
                    changeBalanceFilter(true, true);
                });
                $('.datepicker--nav-title').on("click", function() {
                    return false
                });

                $('.js-multiselectGroup').on("click", function() {
                    $('.chosen-container-single').removeClass('chosen-with-drop');
                });
                $('.select__single').on("click", function() {
                    if($('.btn-group').hasClass('open')){
                        $('.chosen-container-active').addClass('chosen-with-drop');
                    }
                });


            });
        </script>
    {/literal}
    {Helper::printJsFile("/js/pages/balance/balance.js"|cdnBaseUrl)}
{/strip}

{include file="footer.tpl"}