$(window).load(function(){
  $('.pay-order .pay-order_select_item').eq(0).addClass('active');
  $('.pay-order_block .pay-order_block_form').eq(0).show();
  $('.pay-order .pay-order_select_item').bind('click',function(){
    $('.pay-order .pay-order_select_item').removeClass('active');
    $(this).addClass('active');
    $('.pay-order_block .pay-order_block_form').hide();
    $('.pay-order_block .pay-order_block_form.pay-order_'+$(this).attr('data-tab')).show();
  })
});

