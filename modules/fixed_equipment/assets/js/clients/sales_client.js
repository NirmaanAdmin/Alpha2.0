(function(){
  "use strict";
  $(document).on("click",".add_cart, .added",function() {
    var popup_obj = $('#select_variation');
    var ids = $(this).attr('data-id');
    var parent = $(this).parent();
    var qty_obj = parent.find('.qty');
    var qtys = qty_obj.val();
    var w_qtys = qty_obj.data('w_quantity');
    var renting_unit = parent.find('input[name="renting_unit"]').val();
    var item_type = parent.find('input[name="item_type"]').val();

    // For variation 
    var has_variation = parent.find('input[name="has_variation"]').val();
    if(has_variation == 1){
      var image = $(this).parents('.product-cell').find('.product-image img').attr('src');
      var name = $(this).parents('.product-cell').find('.product-content').html();
      popup_obj.find('.content').html('');
      popup_obj.find('.image img').attr('src',image);
      popup_obj.find('.modal-title').html(name);
      var sub_prices = '<span class="price sub hide"></span>';
      popup_obj.find('.prices').html(sub_prices);

      popup_obj.find('input[name="parent_id"]').val(ids);
      popup_obj.modal('show');

      if(parent.find('input[name="for_sell"]').val() == 1){
        popup_obj.find('.buy_item_frame').removeClass('hide');
      }
      else{
        popup_obj.find('.buy_item_frame').addClass('hide');
      }

      if(parent.find('input[name="renting_unit"]').val() == 'hour'){
        popup_obj.find('.rent_by_hour').removeClass('hide');
        popup_obj.find('.rent_by_day').addClass('hide');
      }
      else{
        popup_obj.find('.rent_by_hour').addClass('hide');
        popup_obj.find('.rent_by_day').removeClass('hide');
      }
      popup_obj.find('input[name="quantity_available"]').val(w_qtys);

      popup_obj.find('input[name="item_type"]').val(item_type);
      popup_obj.find('input[name="renting_unit"]').val(renting_unit);

      popup_obj.find('.buy_item_frame input[name="qty"]').attr('data-w_quantity', w_qtys);
      popup_obj.find('.buy_item_frame .add_to_cart').attr('data-id', ids);
      change_ui(3);
      $('input[name="rental_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minDate: new Date(),
      });
      $('input[name="rental_time"]').daterangepicker({"minDate": new Date(),"startDate": new Date(),
        "endDate": new Date(),});
      popup_obj.find('select[name="pickup_time"]').val('').change();
      popup_obj.find('select[name="dropoff_time"]').val('').change();

      if($(this).attr('data-buyed') == 1){
        change_ui(1);
      }
      if($(this).attr('data-rented') == 1){
        var unit_type = get_cookie_value_booking(ids, 'fe_cart_renting_unit_list_booking');
        if(unit_type == 'hour'){
          var rental_date = get_cookie_value_booking(ids, 'fe_cart_rental_date_list_booking');
          var pickup_time = get_cookie_value_booking(ids, 'fe_cart_pickup_time_list_booking');
          var dropoff_time = get_cookie_value_booking(ids, 'fe_cart_dropoff_time_list_booking');
          popup_obj.find('input[name="rental_date"]').val(rental_date);
          popup_obj.find('select[name="pickup_time"]').val(pickup_time).change();
          popup_obj.find('select[name="dropoff_time"]').val(dropoff_time).change();
        }
        else{
          var rental_time = get_cookie_value_booking(ids, 'fe_cart_rental_time_list_booking');
          popup_obj.find('input[name="rental_time"]').val(rental_time);
        }
        change_ui(2);
      }
      return false;
    }
    else{
       // For variation 

      var w_quantity = qty_obj.attr('data-w_quantity');
      if(w_quantity == 0){
        alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
        return false;
      }
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
              if(parseInt(qtys) > parseInt(w_quantity)){
                alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
              }
              else{
                id_list.push(ids);
                qty_list.push(qtys);
                add_to_cart(id_list,qty_list);
                $(this).addClass('hide');
                parent.find('button.add_to_cart_1').removeClass('hide').html('<i class="fa fa-check"></i>'+$('input[name="added_text"]').val());
                alert_float('success', $('input[name="added_to_cart"]').val());
              }
            }
          }
          else{
            var new_list_qty = [];
            var enoungh = 1;
            $.each(qty_list, function( key, value ) {
              if(index_id == key){
                var temp_qty = 0;
                if(qtys != ''){
                  temp_qty = parseInt(value)+parseInt(qtys);
                  if(temp_qty > w_quantity){
                    enoungh = 0;
                    temp_qty = w_quantity;                           
                  }
                  new_list_qty.push(temp_qty);                        
                }
                else{
                  temp_qty = parseInt(value)+1;   
                  if(temp_qty > w_quantity){
                    enoungh = 0;
                    temp_qty = w_quantity;                         
                  }                     
                  new_list_qty.push(temp_qty);
                }  
              }
              else{
                new_list_qty.push(value);
              }
            });
            if(enoungh == 0){
              alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
            }
            else{
              add_to_cart(id_list,new_list_qty);
              $(this).addClass('hide');
              parent.find('button.add_to_cart_1').removeClass('hide').html('<i class="fa fa-check"></i>'+$('input[name="added_text"]').val());
              alert_float('success', $('input[name="added_to_cart"]').val());
              $('button[data-id="'+ids+'"]').attr('data-buyed', '1').attr('data-rented', '');
            }
          }
        }
        else{
          if(parseInt(qtys) > parseInt(w_quantity)){
            alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
          }
          else{
            var id_list = [ids];
            var qtys_list = [qtys];
            add_to_cart(id_list,qtys_list);
            $(this).addClass('hide');
            parent.find('button.add_to_cart_1').removeClass('hide').html('<i class="fa fa-check"></i>'+$('input[name="added_text"]').val());
            alert_float('success', $('input[name="added_to_cart"]').val());
            $('button[data-id="'+ids+'"]').attr('data-buyed', '1').attr('data-rented', '');
          }
        }
      }
      count_product_cart();
    }
    popup_obj.modal('hide');
  });


  // For booking
  $('.rental_item').click(function(){
    var popup_obj = $('#select_variation');
    var amount_in_stock = popup_obj.find('input[name="quantity_available"]').val();
    var rental_time = popup_obj.find('input[name="rental_time"]').val(), 
    rental_date = popup_obj.find('input[name="rental_date"]').val(), 
    item_type = popup_obj.find('input[name="item_type"]').val(), 
    renting_unit = popup_obj.find('input[name="renting_unit"]').val(), 
    qty = 1,
    pickup_time = popup_obj.find('select[name="pickup_time"]').val(), 
    dropoff_time = popup_obj.find('select[name="dropoff_time"]').val(), 
    ids = popup_obj.find('input[name="parent_id"]').val(), qtys = 0;

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
      $('#alert_add').modal('show');
      $('.add_success').addClass('hide');
      $('.add_error').removeClass('hide');
      setTimeout(function(){ $('#alert_add').modal('hide'); },1000);
      return false;
    }
    var valid = add_item_booking(ids, renting_unit, rental_time, rental_date, pickup_time, dropoff_time, item_type);
    if(valid == 0){
     alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
     return false;
   }
   else{
    if(valid == 1){
     alert_float('success', $('input[name="added_to_cart"]').val());
     change_ui(2);
     $('button[data-id="'+ids+'"]').attr('data-buyed', '').attr('data-rented', '1');
     $('.add_to_cart_1[data-id="'+ids+'"]').removeClass('hide').find('.booked_btn_text').removeClass('hide');
     $('.add-cart button.add_cart[data-id="'+ids+'"]').addClass('hide');
   }
   else{
    if(valid == 2){
     alert_float('success', $('input[name="updated_rental_information"]').val());
   }
 }
}


count_product_cart();
popup_obj.modal('hide');
});


  $(document).on("click",".add_to_cart",function() {
    var popup_obj = $('#select_variation');
    var ids = $(this).attr('data-id');
    var parent = $(this).parent();

    var qty_obj = parent.find('.qty');
    var qtys = qty_obj.val();
    var w_quantity = qty_obj.attr('data-w_quantity');
    if(w_quantity == 0){
      alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
      return false;
    }
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
            if(parseInt(qtys) > parseInt(w_quantity)){
              alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
            }
            else{
              id_list.push(ids);
              qty_list.push(qtys);
              add_to_cart(id_list,qty_list);
              alert_float('success', $('input[name="added_to_cart"]').val());
              change_ui(1);
              $('button[data-id="'+ids+'"]').attr('data-buyed', '1').attr('data-rented', '');
              $('.add_to_cart_1[data-id="'+ids+'"]').removeClass('hide').find('.added_btn_text').removeClass('hide');
              $('.add-cart button.add_cart[data-id="'+ids+'"]').addClass('hide');
            }
          }
        }
        else{
          var new_list_qty = [];
          var enoungh = 1;
          $.each(qty_list, function( key, value ) {
            if(index_id == key){
              var temp_qty = 0;
              if(qtys != ''){
                temp_qty = parseInt(value)+parseInt(qtys);
                if(temp_qty > w_quantity){
                  enoungh = 0;
                  temp_qty = w_quantity;                           
                }
                new_list_qty.push(temp_qty);                        
              }
              else{
                temp_qty = parseInt(value)+1;   
                if(temp_qty > w_quantity){
                  enoungh = 0;
                  temp_qty = w_quantity;                         
                }                     
                new_list_qty.push(temp_qty);
              }  
            }
            else{
              new_list_qty.push(value);
            }
          });
          if(enoungh == 0){
            alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
          }
          else{
            add_to_cart(id_list,new_list_qty);
            alert_float('success', $('input[name="added_to_cart"]').val());
            change_ui(1);
            $('button[data-id="'+ids+'"]').attr('data-buyed', '1').attr('data-rented', '');
            $('.add_to_cart_1[data-id="'+ids+'"]').removeClass('hide').find('.added_btn_text').removeClass('hide');
            $('.add-cart button.add_cart[data-id="'+ids+'"]').addClass('hide');
          }
        }
      }
      else{
        if(parseInt(qtys) > parseInt(w_quantity)){
          alert_float('warning', $('input[name="sorry_the_number_of_current_products_is_not_enough"]').val());
        }
        else{
          var id_list = [ids];
          var qtys_list = [qtys];
          add_to_cart(id_list,qtys_list);
          alert_float('success', $('input[name="added_to_cart"]').val());
          change_ui(1);
          $('button[data-id="'+ids+'"]').attr('data-buyed', '1').attr('data-rented', '');
          $('.add_to_cart_1[data-id="'+ids+'"]').removeClass('hide').find('.added_btn_text').removeClass('hide');
          $('.add-cart button.add_cart[data-id="'+ids+'"]').addClass('hide');
        }
      }
    }
    count_product_cart();
    popup_obj.modal('hide');
  });


  $(window).on('load', function() {  
    count_product_cart();
  });
  $('.btn_page').click(function(){
    $('.btn_page').removeClass('active');
    $(this).addClass('active');
    $('.product_list').html(''); 
    var page = $(this).data('page');
    var group_id = $('input[name="group_id"]').val();
    var keyword = $('input[name="keyword"]').val();
    if(keyword != ''){
      keyword = '/'+keyword;
    }
    ChangeUrlWithIndex(page,group_id);
    if(page!=''){
      $.post(site_url+'fixed_equipment/fixed_equipment_client/get_product_by_group/'+page+'/'+group_id+'/0'+keyword).done(function(response){
        response = JSON.parse(response);
        $('.product_list').html(response.data);
      });   
    }
  });

  $('input[name="rental_date"]').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    minDate: new Date(),
  });

  $('input[name="rental_time"]').daterangepicker({"minDate": new Date(),"startDate": new Date(),
    "endDate": new Date(),});

})(jQuery);
function ChangeUrlWithIndex(page, group_id) {
  "use strict";
  var url = window.location.href, url = url.split("/");
  var keyword = $('input[name="keyword"]').val();
  if(keyword != ''){
    keyword = '/'+keyword;
  }
  url = url[0]+'/'+url[1]+'/'+url[2]+'/'+url[3]+'/'+url[4]+'/'+url[5]+'/'+page+'/'+group_id+'/0'+keyword;
  window.history.pushState({}, document.title, url);
}
function add_to_cart(cart_id_list,cart_qty_list){
  "use strict";
  add_cookie('fe_cart_id_list',cart_id_list,30);
  add_cookie('fe_cart_qty_list',cart_qty_list,30);
}



function numberWithCommas(x) {
  "use strict";
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function add_to_cart_booking(cart_id_list,cart_rental_time_list, cart_rental_date_list, cart_pickup_time_list, cart_dropoff_time_list, cart_product_type_list, cart_renting_unit_list, cart_qty_list){
  "use strict";
  add_cookie('fe_cart_id_list_booking',cart_id_list,30);
  add_cookie('fe_cart_rental_time_list_booking',cart_rental_time_list,30);
  add_cookie('fe_cart_rental_date_list_booking',cart_rental_date_list,30);
  add_cookie('fe_cart_pickup_time_list_booking',cart_pickup_time_list,30);
  add_cookie('fe_cart_dropoff_time_list_booking',cart_dropoff_time_list,30);
  add_cookie('fe_cart_product_type_list_booking',cart_product_type_list,30);
  add_cookie('fe_cart_renting_unit_list_booking',cart_renting_unit_list,30);
  add_cookie('fe_cart_qty_list_booking',cart_qty_list,30);
}

function change_ui(type){
  "use strict";
  var modal_obj = $('#select_variation');
  if(type == 1){
    modal_obj.find('.rental_item_frame').addClass('lock');
    modal_obj.find('.add_to_cart_2').addClass('hide');
    modal_obj.find('.add_to_cart_1').removeClass('hide');

    modal_obj.find('.buy_item_frame').removeClass('lock');
    modal_obj.find('.rental_item_2').removeClass('hide');
    modal_obj.find('.rental_item_1').addClass('hide');

  }
  if(type == 2){
    modal_obj.find('.buy_item_frame').addClass('lock');
    modal_obj.find('.rental_item_2').addClass('hide');
    modal_obj.find('.rental_item_1').removeClass('hide');

    modal_obj.find('.rental_item_frame').removeClass('lock');
    modal_obj.find('.add_to_cart_2').removeClass('hide');
    modal_obj.find('.add_to_cart_1').addClass('hide');
  }

  if(type == 3){
    modal_obj.find('.buy_item_frame').removeClass('lock');
    modal_obj.find('.rental_item_2').removeClass('hide');
    modal_obj.find('.rental_item_1').addClass('hide');

    modal_obj.find('.rental_item_frame').removeClass('lock');
    modal_obj.find('.add_to_cart_2').removeClass('hide');
    modal_obj.find('.add_to_cart_1').addClass('hide');
  }
}