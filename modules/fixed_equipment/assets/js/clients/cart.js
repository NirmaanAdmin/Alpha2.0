(function(){
    "use strict";
  $(window).on('load', function() {  
    count_product_cart();
  });
})(jQuery);
function change_cart_qty(el){
	"use strict";
	var new_qty = $(el).val();
	var index = $(el).data('key'); 
	var price = $(el).data('price');   

    var cart_qty_list = getCookie('fe_cart_qty_list');
    if(typeof cart_qty_list != ""){
          if(cart_qty_list.trim()){
            	var qty_list = JSON.parse('['+cart_qty_list+']');
                var new_list_qty = [];
                $.each(qty_list, function( key, value ) {
                    if(key == index){
                      new_list_qty.push(parseInt(new_qty));
                    }
                    else{
                      new_list_qty.push(value);
                    }
                });
                add_cookie('fe_cart_qty_list',new_list_qty,30);
                $('.line_total_order').eq(index).text(numberWithCommas(parseFloat(new_qty*price).toFixed(2)));
          }
    }
    count_subtotal();
    count_product_cart();
}
function numberWithCommas(x) {
  "use strict";
   return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function delete_item(el){
  "use strict";
  var id_product = $(el).data('id');  
   var cart_id_list = getCookie('fe_cart_id_list'), cart_qty_list;
  if(typeof cart_id_list != ""){
    if(cart_id_list.trim()){
      var id_list = JSON.parse('['+cart_id_list+']');
      cart_qty_list = getCookie('fe_cart_qty_list');
      var qty_list = JSON.parse('['+cart_qty_list+']');
      var empty = 0;
      var new_list_id = []; 
      var index = -1;
      $.each(id_list, function( key, value ) {
         if(id_product != value){
            new_list_id.push(value);
            empty++;
          }
          else{
            index = key;
          }
      });  

      var new_list_qty = [];
      $.each(qty_list, function( key, value ) {
          if(key != index){
            new_list_qty.push(value);
          }
      });
      add_to_cart(new_list_id,new_list_qty);

      $(el).closest('.order-list .main').remove().fadeOut(800);
      if(empty == 0){
        $('.order-list .fr1').addClass('hide');
        $('.order-list .fr2').removeClass('hide');
      }
    }
  }
  count_subtotal();
  count_product_cart(); 
}

function delete_cookie(name) {
  document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function add_to_cart(cart_id_list,cart_qty_list){
  "use strict";
  add_cookie('fe_cart_id_list',cart_id_list,30);
  add_cookie('fe_cart_qty_list',cart_qty_list,30);
}

function count_subtotal(){
  "use strict";
    var list_obj = $('.order-list .line_data'), count_line, sub_total = 0;
    count_line = list_obj.length;

    for(var i = 0; i < count_line; i++){
      sub_total += list_obj.eq(i).data('price') * list_obj.eq(i).val();
    }
    $('.order-list .subtotal').text(numberWithCommas(parseFloat(sub_total).toFixed(2)));

}

function delete_item_booking(el){
  "use strict";
  var id_product = $(el).data('id');  
  var cart_id_list = getCookie('fe_cart_id_list_booking');
  if(typeof cart_id_list != ""){
    if(cart_id_list.trim()){
      var id_list = JSON.parse('['+cart_id_list+']');
      var empty = 0;
      var new_list_id = []; 
      //Get index and exclude id in list
      var index = -1;
      $.each(id_list, function( key, value ) {
         if(id_product != value){
            new_list_id.push(value);
            empty++;
          }
          else{
            index = key;
          }
      });  

      // Exclude rental time
      var cart_rental_time_list = getCookie('fe_cart_rental_time_list_booking');
      var new_list_rental_time = [];
      $.each(cart_rental_time_list.split(','), function( key, value ) {
        if(key != index){
          new_list_rental_time.push(value);
        }
      });


      // Exclude rental date
      var cart_rental_date_list = getCookie('fe_cart_rental_date_list_booking');
      var new_list_rental_date = [];
      $.each(cart_rental_date_list.split(','), function( key, value ) {
        if(key != index){
          new_list_rental_date.push(value);
        }
      });


      // Exclude pickup time
      var cart_pickup_time_list = getCookie('fe_cart_pickup_time_list_booking');
      var new_list_pickup_time = [];
      $.each(cart_pickup_time_list.split(','), function( key, value ) {
        if(key != index){
          new_list_pickup_time.push(value);
        }
      });

      // Exclude drop off time
      var cart_dropoff_time_list = getCookie('fe_cart_dropoff_time_list_booking');
      var new_list_dropoff_time = [];
      $.each(cart_dropoff_time_list.split(','), function( key, value ) {
        if(key != index){
          new_list_dropoff_time.push(value);
        }
      });


      // Exclude item type
      var cart_item_type_list = getCookie('fe_cart_item_type_list_booking');
      var new_list_item_type = [];
      $.each(cart_item_type_list.split(','), function( key, value ) {
        if(key != index){
          new_list_item_type.push(value);
        }
      });


      // Exclude renting unit
      var cart_renting_unit_list = getCookie('fe_cart_renting_unit_list_booking');
      var new_list_renting_unit = [];
      $.each(cart_renting_unit_list.split(','), function( key, value ) {
        if(key != index){
          new_list_renting_unit.push(value);
        }
      });
      add_to_cart_booking(new_list_id, new_list_rental_time, new_list_rental_date, new_list_pickup_time, new_list_dropoff_time, new_list_item_type, new_list_renting_unit);
      $(el).closest('.booking-list .main').remove().fadeOut(800);
      if(empty == 0){
        $('.booking-list .fr1').addClass('hide');
        $('.booking-list .fr2').removeClass('hide');
      }
    }
  }
  count_subtotal_booking();
  count_product_cart(); 
}

function count_subtotal_booking(){
  "use strict";
    var list_obj = $('.booking-list .line_data'), count_line, sub_total = 0;
    count_line = list_obj.length;

    for(var i = 0; i < count_line; i++){
      sub_total += list_obj.eq(i).data('price') * list_obj.eq(i).data('number_day');
    }
    $('.booking-list .subtotal').text(numberWithCommas(parseFloat(sub_total).toFixed(2)));

}