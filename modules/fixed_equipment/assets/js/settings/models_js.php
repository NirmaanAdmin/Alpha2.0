 <script type="text/javascript">
  appValidateForm($("body").find('#model_update_batch_rate'), {
    'model': 'required',
  });

  function bulk_actions(){
    "use strict";
    $('.display-select-item').addClass('hide');
    $("#y_opt_1_").prop("checked", true);

    $("#table_models_bulk_action option:selected").prop("selected", false).change()
    $("#table_models_bulk_action select[id='item_select_print_barcode']").selectpicker('refresh');

    $('#table_models_bulk_action').modal('show');
  }

  function print_barcode_option(invoker) {
    "use strict";
    var data={};
    data.profit_rate_by_purchase_price_sale = invoker.value;

    if(invoker.value == 1){
      $('.display-select-item').removeClass('hide');
      get_asset_data();
    }else if(invoker.value == 0){
      $('.display-select-item').addClass('hide');
      $("select[name='asset_id[]']").html('');
      $("select[name='asset_id[]']").selectpicker('refresh');

    }
  }

  $('select[name="model"]').on('change',function(){
    get_asset_data();
  });

  function get_asset_data(model_id) {
    var data_select = {};
      data_select.model_id = $('select[name="model"]').val();

      $.post(admin_url + 'fixed_equipment/get_asset_data',data_select).done(function(response){
       response = JSON.parse(response);
       $("select[name='asset_id[]']").html('');

       $("select[name='asset_id[]']").append(response.assets);
       $("select[name='asset_id[]']").selectpicker('refresh');

     });
  }

  $(document).on("change","#for_sell, #for_rent",function() {
        var obj = $(this);
        if(obj.is(':checked')){
          if(obj.attr('name') == 'for_sell'){
            $('.for_sell_fr').removeClass('hide');  
          }
          else{
            $('.for_rent_fr').removeClass('hide');  
          }
        }else{
          if(obj.attr('name') == 'for_sell'){
            $('.for_sell_fr').addClass('hide');
          }
          else{
            $('.for_rent_fr').addClass('hide'); 
          }
        }
        var data_validate = {};
        data_validate.model_id = 'required';
        data_validate.status = 'required';
        data_validate.model = 'required';
        

        if($('#for_sell').is(':checked')){
          data_validate.selling_price = 'required';
        }
        if($('#for_rent').is(':checked')){
          data_validate.rental_price = 'required';
          data_validate.renting_period = 'required';
          data_validate.renting_unit = 'required';
        }

        appValidateForm($('#model_update_batch_rate'), data_validate)
      });
</script>