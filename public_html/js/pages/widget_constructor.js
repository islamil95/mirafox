$(window).load(function(){ 
    $('#parents').on('change', function(){
        $('.childs').addClass('hidden');
        $('.childs').prop('selectedIndex',0);
        $('.childs').removeAttr('required');
        var id = $(this).val();
        $('#'+ id).removeClass('hidden').attr('required', '');
        $('[name=gcat]').attr('name', '');
        $(this).attr('name', 'gcat');
        
    });
    $('.childs').on('change', function(){        
        $('[name=gcat]').attr('name', '');
        if($(this).val() != 'all'){
            $(this).attr('name', 'gcat');
        }else{
            $('#parents').attr('name', 'gcat');
        }
    });
    
    $('input.js-widget-type').change(function(){
        $('input[name=widget-type]').parent().find('.js-value-inputs').addClass('hide');
        $(this).parent().find('.js-value-inputs').removeClass('hide');
    });
    
    function reLoadExemple(){
        $('.widget-constructor_code-area').hide();
        
        var $container = $('.widget-constructor_example');
        var $codeContainer = $('.widget-constructor_code textarea');
        $container.empty();
        $container.preloader('show');
        
        // Получение параметров
        var width = $('input[type=text][name=widget-width]').val();
        
        var requestRow = '&width=' + width;
                
        // Категория/подкатегория
        if($('#cat_widget-type_id:checked').length > 0){
            requestRow += '&cat_type=' + $('[name=gcat]').val();            
        }else{
            requestRow += '&cat_type=no';
        }
        
        if($('#bookmarks_widget-type_id:checked').length > 0){
            requestRow += '&bookmarks_type=1';
        }
        
        if($('#my_kworks_widget-type_id:checked').length > 0){
            requestRow += '&my_kworks_type=1';
        }
        
        if($('#kwork_list_widget-type_id:checked').length > 0){
            requestRow += '&kwork_list_type=' + $('input[name=kwork_list]').val();
        }
        
        if($('#popular_widget-type_id:checked').length > 0){
            requestRow += '&popular_type=1';
        }
        
        requestRow += '&count=' + $('input[name=widget-kwork_count]').val();
        
        $.get('/api/widget/getinitcode', 'json=no' + requestRow,
        function (html) {            
            $container.html(html);  
            $codeContainer.text(html);
        });
    }
    
    $('.widget-constructor_settings input').change(function(){
        reLoadExemple();
    });  
    $('.widget-constructor_settings select').change(function(){
        reLoadExemple();
    });
    
    $('.js-get-widget-code').on('click', function(){        
        $('.js-copy-widget-code').text(t('Скопировать код'));
        $('.widget-constructor_code-area').show();
    });
    
     $('.js-copy-widget-code').on('click', function(){
        $('.widget-constructor_code-area textarea').select();
        document.execCommand('copy');
        $(this).text(t("Скопировано в буфер обмена"));
    });
});

