{strip}
    <style>
        a.hide-notify{
            display: inline-block;
            text-align: center;
            text-decoration: none;
            border-bottom: 1px dashed;
            color: #ff7676;
            font-weight: 800;
        }
        a.show-lang-info{
            color: #87b948;
        }
        .info-icon{
            color: #ffb63c;
            width: 70px;
            height: 70px;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
        }
        .separator {
            border-top: 1px solid #e1e1e1;
            width: 90%;
            display: block;
            margin: 0 auto;
        }
    </style>
    <div class="gray-bg-border clearfix mb20 notify-header">
        <div class="contentArea mb0 w100p pb0">
            <div class="p15-20 sm-text-center">
                <div class="info-icon pull-left">
                    <i class="fa fa-5x fa-briefcase v-align-m"></i>
                </div>
                <div class="f16 font-OpenSans dib v-align-m mw80p ml15 mt10 sm-margin-reset pull-left requiredInfo-block">
                    <div class="w100p">
                        {"Создайте Кворки на английском языке, чтобы выйти на западный рынок и <strong>увеличить свои продажи!</strong> Общаться с покупателями потребуется на английском."|t}
                    </div>
                    <div class="mb10 w100p">
                        <div class="w50p pull-left" style="text-align: center;">
                            <a class="hide-notify" href="#">{'Нет, спасибо'|t}</a>
                        </div>
                        <div class="w50p pull-left" style="text-align: center;">
                            <a class="hide-notify show-lang-info" href="#">{'Да! Как?'|t}</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="mt10 w100p hidden notify-message">
                        <div>
                            <div class="separator"></div>
                        </div>
                        <div class="mt10 mb35 f13">
                            {'Откройте созданный Кворк на русском или Создайте Новый, нажмите на кнопку "EN (бета-версия)". Заполните все поля на английском языке. После модерации ваш Кворк будет показан для англоязычных пользователей на kwork.com. Вы сможете получить больше заказов.'|t}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('.hide-notify').on('click', function(){
                if($(this).hasClass('show-lang-info')){
                    $('.notify-message').removeClass('hidden');
                }else{
                    $('.notify-header').hide();
                }
                $.ajax({
                        type: "POST",
                        url:'{$baseurl}/manage_kworks',
                        data:{
                            'hideLangNotify':1
                        },
                        dataType: 'json',
                        success: function(response) {
                        }
                });
                return false; 
            });
        });
    </script>
{/strip}