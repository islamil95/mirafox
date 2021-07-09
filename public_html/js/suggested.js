$(window).load(function(){
    $('#current-count-character').text($('.textarea-count-js').val().length);
    $('.textarea-count-js').bind('change click keypress paste keydown',function(e){
            var self = this;
            var key_code = e.keyCode
            setTimeout(function(e) {
                if($(self).val().length>=400 && key_code!=8){
                        $(self).val($(self).val().substr(0,400))
                        $('#current-count-character').text($(self).val().length);

                    return false;
                }
                $('#current-count-character').text($(self).val().length);
            }, 0);
    })
    $('#foxPostForm').bind('submit',function(){
        if($('.textarea-count-js').val().length>400){
            return false;
        }

    })
})
