
  $(window).load(function(){
    $("#toggle-additional-visuals").click(function(){
      $("#additional-visuals").toggle('slow');
      $('html, body').animate({
        scrollTop: $("#additional-visuals").offset().top
      }, 200);
    });

    $('#gig_description').trumbowyg({
      fullscreenable: false,
      closable: false,
      btns: ['bold', '|', 'italic', '|', 'orderedList'],
      removeformatPasted: true
    });
  });


  /*максимальная длина дополнительных опций*/
$(window).load(function(){
  if($('.extra-block-js input.kwork_extra_title').length>0){
    $('.extra-block-js input.kwork_extra_title').each(function(){
      $(this).trigger('blur');
    })
  }
})
  /*максимальная длина дополнительных опций*/
$(document).on('keyup paste blur','.extra-block-js input.kwork_extra_title',function(){
  if($(this).val().length>50){
    $(this).val($(this).val().substr(0, 50))
  }
  $(this).parents('.extra-block-js').find('.extrasping-settings_length-js').text($(this).val().length);
})
var touch_timer;
$(document).on('touchstart','.long-touch-js',function () {
  var text = $(this).parents('.block-state-active').find('.block-state-active_tooltip').text()
  touch_timer = setTimeout(function () {
     alert(text)
  },500)
 
})
$(document).on('touchend','.long-touch-js',function () {
  clearTimeout(touch_timer)
})


function foxtoggle(obj, obbox) {
    if (obbox == 'extrasme') {
        if ($('#extrasme').is(":checked")) {
            $('#extrasmore').show();
        } else {
            $('#extrasmore').hide();
        }
    } else if (obbox == 'favouriteme'){
        if ($('#favouriteme').is(":checked")) {
            $('#favouritemore').show();
        } else {
            $('#favouritemore').hide();
        }
    }
}