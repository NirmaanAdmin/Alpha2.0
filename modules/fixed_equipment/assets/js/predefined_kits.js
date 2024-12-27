(function(){
	"use strict";
	var fnServerParams = {
	}
	initDataTable('.table-predefined_kits', admin_url + 'fixed_equipment/predefined_kits_table', false, false, fnServerParams, [0, 'desc']);

	appValidateForm($('#predefined_kits-form'), {
		'assets_name': 'required'
	})

	appValidateForm($('#check_out_predefined_kits-form'), {
		'staff_id': 'required'
	})

	$("input[data-type='currency']").on({
		keyup: function() {        
			formatCurrency($(this));
		},
		blur: function() { 
			formatCurrency($(this), "blur");
		}
	});

	$("#check_out select[name='staff_id']" ).change(function() {
		if(first_open == true){
			$('#check_out .asset_list').html('');
			var staff_id = $(this).val();
			var id = $('#check_out input[name="item_id"]').val();
			var requestURL = 'fixed_equipment/get_asset_staff_predefined_kit/'+id+'/'+staff_id;
			requestGetJSON(requestURL).done(function(response) {
				$('#check_out .asset_list').html(response);
			}).fail(function(data) {
				alert_float('danger', 'Error');
			});	
		}
		first_open = true;
	});

	$('#choose_an_available_kit').on('click', function(){
		var val = $(this).is(':checked');
		var validate_data = {};
		if($('input[name="checkout_to"]:checked').val() == 'user'){
			if(val){
				$('#available_kit').removeClass('hide');
				validate_data = {
					'staff_id': 'required',
					'available_kit': 'required'
				};
			}
			else{
				$('#available_kit').addClass('hide');
				validate_data = {
					'staff_id': 'required'
				}
			}
		}
		else{
			if(val){
				$('#available_kit').removeClass('hide');
				validate_data = {
					'project_id': 'required',
					'available_kit': 'required'
				};				
			}
			else{
				$('#available_kit').addClass('hide');
				validate_data = {
					'project_id': 'required'
				};
			}
		}
		appValidateForm($('#check_out_predefined_kits-form'), validate_data);	
	});

	$('input[name="checkout_to"]').click(function(){
		$('.checkout_to_fr').addClass('hide');
		var val = $(this).val();
		var data = {};
		if($('#choose_an_available_kit').is(':checked')){
			switch(val){
				case 'user':
				$('.checkout_to_staff_fr').removeClass('hide');
				data = {
					'staff_id': 'required',
					'available_kit': 'required'
				};
				break;
				case 'project':
				$('.checkout_to_project_fr').removeClass('hide');
				data = {
					'project_id': 'required',
					'available_kit': 'required'
				};
				break;
			}
		}
		else{
			switch(val){
				case 'user':
				$('.checkout_to_staff_fr').removeClass('hide');
				data = {
					'staff_id': 'required'
				};
				break;
				case 'project':
				$('.checkout_to_project_fr').removeClass('hide');
				data = {
					'project_id': 'required'
				};
				break;
			}
		}
		appValidateForm($('#check_out_predefined_kits-form'), data);
	});


})(jQuery);

function get_warning_available_kit(el){
	var val = $(el).val();
	if(val != ''){
		var requestURL = 'fixed_equipment/get_warning_available_kit/'+$('#check_out input[name="item_id"]').val()+'/'+val;
		requestGetJSON(requestURL).done(function(response) {
			console.log(response.error);
			if(response.error == true || response.error == 1 || response.error == '1' || response.error == 'true'){
				$('#available_kit_warning').html(response.data).removeClass('hide');
			}
			else{
				$('#available_kit_warning').html('').addClass('hide');
			}
		}).fail(function(data) {
			alert_float('danger', 'Error');
		});	
	}
	else{
		$('#available_kit_warning').html('').addClass('hide');
	}
}

function add(){
	"use strict";
	$('#add_new_predefined_kits').modal('show');
	$('#add_new_predefined_kits .add-title').removeClass('hide');
	$('#add_new_predefined_kits .edit-title').addClass('hide');
	$('#add_new_predefined_kits input[name="id"]').val('');
	$('#add_new_predefined_kits input[type="text"]').val('');
	$('#add_new_predefined_kits input[type="number"]').val('');
	$('#add_new_predefined_kits select').val('').change();
	$('#add_new_predefined_kits textarea').val('');
	$('#add_new_predefined_kits input[type="checkbox"]').prop('checked', false);
	$('#ic_pv_file').remove();
}

function edit(el,id){
	"use strict";
	$('#add_new_predefined_kits').modal('show');
	$('#add_new_predefined_kits .add-title').addClass('hide');
	$('#add_new_predefined_kits .edit-title').removeClass('hide');
	$('#add_new_predefined_kits input[name="id"]').val(id);
	$('#add_new_predefined_kits input[name="assets_name"]').val($(el).data('assets_name'));
}

function check_in(el, id){
	"use strict";
	var asset_name = $(el).data('asset_name');
	$('#check_in').modal('show');
	$('#check_in .modal-header .add-title').text(asset_name);
	$('#check_in input[name="item_id"]').val(id);
	$('#check_in input[name="asset_name"]').val(asset_name);
}
var first_open = false;
function check_out(el, id){
	"use strict";
	first_open = false;
	var asset_name = $(el).data('asset_name');
	$('#check_out .asset_list').html('');
	$('#check_out').modal('show');
	$('#check_out input[name="item_id"]').val(id);
	$('#check_out input[name="asset_name"]').val(asset_name);
	$("#check_out select[name='staff_id']" ).val('').change();
	$('#choose_an_available_kit').prop('checked', false);
	$('#available_kit').addClass('hide');
	$('#available_kit_warning').html('').addClass('hide');
	appValidateForm($('#check_out_predefined_kits-form'), {
		'staff_id': 'required'
	})
	var management_obj = $('#check_out .go_to_management');
	var href = management_obj.data('href')+id;
	management_obj.attr('href', href);
	var requestURL = admin_url+'fixed_equipment/get_available_kit/'+id;
	requestGetJSON(requestURL).done(function(response) {
		$('#available_kit .select-content').html(response.data);
		init_selectpicker();		
	}).fail(function(data) {
		alert_float('danger', 'Error');
	});
}

