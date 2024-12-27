(function(){
  "use strict";
  $('.add_to_cart,.added_to_cart').click(function(){
    var has_classify = $('input[name="has_classify"]').val();
    if(has_classify == 1){
      var msg = $('input[name="msg_classify"]').val();
      var row_variation = $('.variation-row');
      let count_row = row_variation.length;
      let count_effect = 0;
      for(let i=0;i<count_row;i++){
        var row = row_variation.eq(i);
        var find_selected = row.find('.selected');
        row.find('.variation-items .alert-variation').remove();
        var variation_name = row.find('.variation-items label').text();
        if(find_selected.length == 1){
          count_effect++;
        }
        else{
          row.find('.variation-items').append('<div class="alert-variation mtop5"><span class="text-danger">'+msg+' '+variation_name+'</span></div>');
        }
      }
      if(count_effect != count_row){
        return false;
      }
    }

    var amount_in_stock = $('input[name="quantity_available"]').val();
    var qtys = $('#quantity').val(), ids = $('input[name="id"]').val();
    if(parseInt(amount_in_stock) < parseInt(qtys)){
     alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
      return false;
    }
    var valid = 1;
    var cart_id_list = getCookie('fe_cart_id_list'), cart_qty_list;
    if(typeof cart_id_list != ""){
      if(cart_id_list.trim()){
        var id_list = JSON.parse('['+cart_id_list+']');
        cart_qty_list = getCookie('fe_cart_qty_list');
        var qty_list = JSON.parse('['+cart_qty_list+']');
        var index_id = -1;
        $.each(id_list, function( key, value ) {
          if(value == ids){
            index_id = key;
          }
        }); 
        if(index_id == -1){
          if(ids != '' &&qtys != ''){
            id_list.push(ids);
            qty_list.push(qtys);
            add_to_cart(id_list,qty_list);
          }
        }
        else{
          var new_list_qty = [];
          $.each(qty_list, function( key, value ) {
            if(index_id == key){
              var checks_qtys = 0; 
              if(qtys != ''){
                if(qtys >= 0){  
                  checks_qtys = parseInt(value)+parseInt(qtys);                     
                  new_list_qty.push(checks_qtys);                            
                }
                else{
                  checks_qtys = parseInt(value)+1;                     
                  new_list_qty.push(checks_qtys);                           
                }
              }
              else{
                checks_qtys = parseInt(value)+1;                     
                new_list_qty.push(parseInt(value)+1);                          
              }
              if(parseInt(checks_qtys) > parseInt(amount_in_stock)){
                valid = 0;                      
              }
            }
            else{
              new_list_qty.push(value);
            }                    
          });

          if(valid == 1){
            add_to_cart(id_list,new_list_qty);
          }
        }
      }
      else{
        var id_list = [ids];
        var qtys_list = [qtys];
        add_to_cart(id_list,qtys_list);
        change_ui(1);
        alert_float('success', $('input[name="added_to_cart"]').val());
        valid = 1;
      }
    }   
    count_product_cart();
    if(valid == 0){
      alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
      return false;
    }
    else{
      change_ui(1);
      alert_float('success', $('input[name="added_to_cart"]').val());
    }
  });  

  // For booking
  $('.rental_item').click(function(){
    var amount_in_stock = $('input[name="quantity_available"]').val();
    var rental_time = $('input[name="rental_time"]').val(), 
    rental_date = $('input[name="rental_date"]').val(), 
    item_type = $('input[name="item_type"]').val(), 
    renting_unit = $('input[name="renting_unit"]').val(), 
    qty = 1,
    pickup_time = $('select[name="pickup_time"]').val(), 
    dropoff_time = $('select[name="dropoff_time"]').val(), 
    ids = $('input[name="id"]').val(), qtys = 0;
    if(renting_unit == 'hour'){
      var valid_rental_info = true;
      if(pickup_time == ''){
        valid_rental_info = false;
      }
      if(dropoff_time == ''){
        valid_rental_info = false;
      }
      if(rental_date == ''){
        valid_rental_info = false;
      }
      if(!valid_rental_info){
        $('.enter_full_info_alert').removeClass('hide').addClass('show-shake');
        setTimeout(function(){
          $('.enter_full_info_alert').removeClass('show-shake');          
        }, 1000);
        return false;
      }
      else{
        $('.enter_full_info_alert').addClass('hide');          
      }
    }
    else{
      var valid_rental_info = true;
      if(rental_time == ''){
        valid_rental_info = false;
      }
      if(!valid_rental_info){
        $('.enter_full_info_alert').removeClass('hide').addClass('show-shake');
        setTimeout(function(){
          $('.enter_full_info_alert').removeClass('show-shake');          
        }, 1000);
        return false;
      }
      else{
        $('.enter_full_info_alert').addClass('hide');          
      }
    }

    if(parseInt(amount_in_stock) < parseInt(qtys)){
     alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
      return false;
    }
    var valid = add_item_booking(ids, renting_unit, rental_time, rental_date, pickup_time, dropoff_time, item_type);
    count_product_cart();
    if(valid == 0){
      alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
      return false;
    }
    else{
      if(valid == 1){
       change_ui(2);
       alert_float('success', $('input[name="added_to_cart"]').val());
      }
      else{
        if(valid == 2){
         alert_float('success', $('input[name="updated_rental_information"]').val());
        }
      }
    }
  });

  $(window).on('load', function() {  
    count_product_cart();
  });
  $('.variation-items .product-variation').click(function(){
    var this_obj = $(this);
    if(!this_obj.hasClass('selected')){
      this_obj.parent().find('.product-variation').removeClass('selected');
      this_obj.addClass('selected');
    }else{
      this_obj.removeClass('selected');
    }
    $('input[name="quantity_available"]').val('0');
    $('input[name="id"]').val('0');
    $('#amount_available').text('0');
    var option_list = [];
    var row_variation = $('.variation-row');
    let count_row = row_variation.length;
    let count_effect = 0;
    for(let i=0;i<count_row;i++){
      var row = row_variation.eq(i);
      var find_selected = row.find('.selected');
      if(find_selected.length == 1){
       var variation_item = {};
       variation_item.variation_name = row.find('label').text();
       variation_item.variation_option = find_selected.text().trim();
       option_list.push(variation_item);
       count_effect++;
     }
   }
   if(count_effect == count_row){
    get_data_variation_product($('input[name="parent_id"]').val(), option_list);
  }
});

  $('input[name="rental_date"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    minDate: new Date(),
  });



  var rental_time_obj = $('input[name="rental_time"]');
  if(rental_time_obj.val() == ''){
    rental_time_obj.daterangepicker({ "minDate": $('input[name="start_date"]').val(), "startDate": $('input[name="start_date"]').val(), 
      "endDate": $('input[name="start_date"]').val() });
  }
  else{
    rental_time_obj.daterangepicker();    
  }


})(jQuery);

function add_to_cart(cart_id_list,cart_qty_list){
  "use strict";
  add_cookie('fe_cart_id_list',cart_id_list,30);
  add_cookie('fe_cart_qty_list',cart_qty_list,30);
}

function scroll_slide(val){
  "use strict";
  var offset_l = $('#frameslide').get(0).scrollLeft;
  var width = $('#frameslide').width();
  var index_scroll = offset_l + (val*(width-80));
  if(index_scroll<0){
    index_scroll = 0;
  }
  $('#frameslide').animate({ scrollLeft: index_scroll }, 1000);
  if(index_scroll>offset_l){
    index_scroll = offset_l;
  }
}
function change_qty(val){
  "use strict";
  var qty = $('#quantity').val();
  var newQty = parseInt(qty)+parseInt(val);
  if(newQty<1){
    newQty = 1;
  }
  $('#quantity').val(newQty);
}

function get_data_variation_product(product_id, option_list){
  var token_hash = $('input[name="token_hash"]').val();
  $.ajax({
   url: site_url+"omni_sales/omni_sales_client/get_product_variation",
   type: "post",
   data: {'csrf_token_name':token_hash,'product_id':product_id,'option_list':option_list},
   success: function(){

   },
   error:function(){

   }
 }).done(function(response) {
   response = JSON.parse(response);
   if(response.product_id == ''){
     $('.product-title').removeClass('hide');
     $('.product-title.sub').addClass('hide');

     $('.product-description').removeClass('hide');
     $('.product-description.sub').addClass('hide');

     $('.new-price').removeClass('hide');
     $('.new-price.sub').addClass('hide');

     $('.long_descriptions').removeClass('hide');
     $('.long_descriptions.sub').addClass('hide');

     $('input[name="quantity_available"]').val('0');
     $('input[name="id"]').val('0');
     $('#amount_available').text('0');
   }
   else{
     $('.product-title').addClass('hide');
     $('.product-title.sub').removeClass('hide').text(response.product_name);

     $('.product-description').addClass('hide');
     $('.product-description.sub').removeClass('hide').html(response.description);

     $('.new-price').addClass('hide');
     $('.new-price.sub').removeClass('hide').text(response.rate);

     $('.long_descriptions').addClass('hide');
     $('.long_descriptions.sub').removeClass('hide').html(response.long_description);

     $('input[name="quantity_available"]').val(response.w_quantity);
     $('input[name="id"]').val(response.product_id);

     $('.amount_available').removeClass('hide');
     $('#amount_available').text(response.w_quantity);

     $('.preview .contain_image img').attr('src',response.image_url);
   }
 }); 
}
function change_ui(type){
    "use strict";
    if(type == 1){
        $('.rental_item_frame').addClass('lock');
        $('.add_to_cart_2').addClass('hide');
        $('.add_to_cart_1').removeClass('hide');

    }
    if(type == 2){
        $('.buy_item_frame').addClass('lock');
        $('.rental_item_2').addClass('hide');
        $('.rental_item_1').removeClass('hide');
    }
}