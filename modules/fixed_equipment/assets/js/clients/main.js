(function(){
  "use strict";
  $(window).on('load', function() {  
    count_product_cart();
  });
})(jQuery);

function getCookie(cname) {
  "use strict";
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function add_cookie(cname, cvalue, exdays) {
  "use strict";
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function count_product_cart(){
  "use strict";
  var cart_qty_list = getCookie('fe_cart_qty_list'),count = 0;
  if(cart_qty_list.trim()){
    var qty_list = JSON.parse('['+cart_qty_list+']');
    $.each(qty_list, function( key, value ) {
      count+=value;
    });   
  }

  var cart_qty_list_booking = getCookie('fe_cart_id_list_booking');
  if(cart_qty_list_booking.trim()){
    var qty_list = JSON.parse('['+cart_qty_list_booking+']');
    $.each(qty_list, function( key, value ) {
      count+=1;
    });   
  }

  if(count > 0){
    $('.fe_qty_total').text(count).fadeIn(500);
  }
  else{
    $('.fe_qty_total').text('').fadeOut(500);
  }
}


function add_item_booking(
  ids,
  renting_unit,
  rental_time,
  rental_date, 
  pickup_time,
  dropoff_time,
  item_type,
  ){
  "use strict";
  var valid = 0;
  var cart_id_list = getCookie('fe_cart_id_list_booking'), 
  cart_rental_date_list, 
  cart_item_type_list, 
  cart_renting_unit_list, 
  cart_pickup_time_list, 
  cart_dropoff_time_list, 
  cart_rental_time_list;
  if(typeof cart_id_list != ""){
    if(cart_id_list.trim()){
      var id_list = JSON.parse('['+cart_id_list+']');
      cart_rental_time_list = getCookie('fe_cart_rental_time_list_booking');
      cart_rental_date_list = getCookie('fe_cart_rental_date_list_booking');
      cart_item_type_list = getCookie('fe_cart_item_type_list_booking');
      cart_renting_unit_list = getCookie('fe_cart_renting_unit_list_booking');
      cart_pickup_time_list = getCookie('fe_cart_pickup_time_list_booking');
      cart_dropoff_time_list = getCookie('fe_cart_dropoff_time_list_booking');
      var rental_time_list = [];
      $.each(cart_rental_time_list.split(','), function( key, value ) {
        rental_time_list.push(value);
      });

      var rental_date_list = [];
      $.each(cart_rental_date_list.split(','), function( key, value ) {
        rental_date_list.push(value);
      });

      var pickup_time_list = [];
      $.each(cart_pickup_time_list.split(','), function( key, value ) {
        pickup_time_list.push(value);
      });

      var dropoff_time_list = [];
      $.each(cart_dropoff_time_list.split(','), function( key, value ) {
        dropoff_time_list.push(value);
      });

      var dropoff_time_list = [];
      $.each(cart_dropoff_time_list.split(','), function( key, value ) {
        dropoff_time_list.push(value);
      });

      var item_type_list = [];
      $.each(cart_item_type_list.split(','), function( key, value ) {
        item_type_list.push(value);
      });

      var renting_unit_list = [];
      $.each(cart_renting_unit_list.split(','), function( key, value ) {
        renting_unit_list.push(value);
      });

      let index = get_index_id_booking(ids);
      if(index == -1){
       id_list.push(ids);
       rental_time_list.push(rental_time);
       rental_date_list.push(rental_date);
       pickup_time_list.push(pickup_time);
       dropoff_time_list.push(dropoff_time);
       item_type_list.push(item_type);
       renting_unit_list.push(renting_unit);
       add_to_cart_booking(id_list, rental_time_list, rental_date_list, pickup_time_list, dropoff_time_list, item_type_list, renting_unit_list);
       valid = 1;
     }
     else{
      var new_list_rental_time = update_value_by_index(index, rental_time, rental_time_list);
      var new_list_rental_date = update_value_by_index(index, rental_date, rental_date_list);
      var new_list_pickup_time = update_value_by_index(index, pickup_time, pickup_time_list);
      var new_list_dropoff_time = update_value_by_index(index, dropoff_time, dropoff_time_list);
      var new_list_item_type = update_value_by_index(index, item_type, item_type_list);
      var new_list_renting_unit = update_value_by_index(index, renting_unit, renting_unit_list);
      add_to_cart_booking(id_list, new_list_rental_time, new_list_rental_date, new_list_pickup_time, new_list_dropoff_time, new_list_item_type, new_list_renting_unit);
      valid = 2;
    }
  }
  else{
    var id_list = [ids];
    var rental_time_list = [rental_time];
    var rental_date_list = [rental_date];
    var item_type_list = [item_type];
    var renting_unit_list = [renting_unit];
    var pickup_time_list = [pickup_time];
    var dropoff_time_list = [dropoff_time];
    add_to_cart_booking(id_list, rental_time_list, rental_date_list, pickup_time_list, dropoff_time_list, item_type_list, renting_unit_list);
    valid = 1;
  }
}   
return valid;
}

function add_to_cart_booking(cart_id_list,cart_rental_time_list, cart_rental_date_list, cart_pickup_time_list, cart_dropoff_time_list, cart_item_type_list, cart_renting_unit_list){
  "use strict";
  add_cookie('fe_cart_id_list_booking',cart_id_list,30);
  add_cookie('fe_cart_rental_time_list_booking',cart_rental_time_list,30);
  add_cookie('fe_cart_rental_date_list_booking',cart_rental_date_list,30);
  add_cookie('fe_cart_pickup_time_list_booking',cart_pickup_time_list,30);
  add_cookie('fe_cart_dropoff_time_list_booking',cart_dropoff_time_list,30);
  add_cookie('fe_cart_item_type_list_booking',cart_item_type_list,30);
  add_cookie('fe_cart_renting_unit_list_booking',cart_renting_unit_list,30);
}


function get_index_id_booking(id){
  "use strict";
  let index = -1;
  var cart_id_list = getCookie('fe_cart_id_list_booking');
  if(cart_id_list != ''){
    $.each(cart_id_list.split(','), function( key, value ) {
      if(value == id){
        index = key;
        return false;
      }
    });
  }
  return index;
}

function update_value_by_index(index, update_value, list){
  "use strict";
  var result_list = [];
  $.each(list, function( key, old_value ) {
    if(index == key){
      if(update_value != ''){
        result_list.push(update_value);                            
      }else{
        result_list.push(old_value);       
      }
    }
    else{
      result_list.push(old_value);
    }                    
  });
  return result_list;
}

function get_cookie_value_booking(id, find_cookie_name){
  "use strict";
  var result = '';
  var index = get_index_id_booking(id);
  if(index != -1){
    var list = getCookie(find_cookie_name);
    if(list != ''){
      $.each(list.split(','), function( key, value) {
        if(index == key){
          result = value;
          return false;
        }
      });
    }
  }
  return result;
}