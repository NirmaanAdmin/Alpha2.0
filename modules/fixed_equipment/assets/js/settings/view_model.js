(function(){
	"use strict";
	var fnServerParams = {
		"model_id": "[name='id']"
	}
	initDataTable('.table-view_model', admin_url + 'fixed_equipment/view_model_table', false, false, fnServerParams, [0, 'desc']);
	
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

		if($('#for_sell').is(':checked')){
			data_validate.selling_price = 'required';
		}
		if($('#for_rent').is(':checked')){
			data_validate.rental_price = 'required';
			data_validate.renting_period = 'required';
			data_validate.renting_unit = 'required';
		}

		appValidateForm($('#assets-form'), data_validate)
	});
})(jQuery);
/**
	 * edit asset
	 */
	 function edit(id){
	 	"use strict";
	 	$('#add_new_assets .modal-body').html('');
	 	$('#add_new_assets').modal('show');
	 	$('#add_new_assets .add-title').addClass('hide');
	 	$('#add_new_assets .edit-title').removeClass('hide');
	 	$('#add_new_assets input[name="id"]').val(id);
	 	var requestURL = (typeof(url) != 'undefined' ? url : 'fixed_equipment/get_modal_content_assets/') + (typeof(id) != 'undefined' ? id : '');
	 	requestGetJSON(requestURL).done(function(response) {
	 		$('#add_new_assets .modal-body').html(response.data);
	 		init_selectpicker();	
	 		init_datepicker();

	 		var data_validate = {};
	 		data_validate.model_id = 'required';
	 		data_validate.status = 'required';

	 		if($('#for_sell').is(':checked')){
	 			data_validate.selling_price = 'required';
	 		}
	 		if($('#for_rent').is(':checked')){
	 			data_validate.rental_price = 'required';
	 			data_validate.renting_period = 'required';
	 			data_validate.renting_unit = 'required';
	 		}

	 		appValidateForm($('#assets-form'), data_validate);
	 		$("input[data-type='currency']").on({
	 			keyup: function() {        
	 				formatCurrency($(this));
	 			},
	 			blur: function() { 
	 				formatCurrency($(this), "blur");
	 			}
	 		});
	 	}).fail(function(data) {
	 		alert_float('danger', 'Error');
	 	});
	 }
