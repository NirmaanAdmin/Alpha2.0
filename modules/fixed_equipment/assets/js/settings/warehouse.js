(function(){
	"use strict";
	var fnServerParams = {};
	initDataTable('.table-warehouses', admin_url + 'fixed_equipment/warehouses_table', false, false, fnServerParams, [0, 'desc']);
	appValidateForm($('#form_add_warehouse'), {
		'code': 'required',
		'name': 'required',
		'order': 'required'
	})
})(jQuery);

/**
 * add model
 */
 function add(){
 	"use strict";
 	$('#add').modal('show');
 	$('#add .add-title').removeClass('hide');
 	$('#add .edit-title').addClass('hide');
 	$('#add input[name="id"]').val('');
 	$('#add input[type="text"], #add input[type="number"]').val('');
 	$('#add select').val('').change();
 	$('#add textarea').val('');
 }

/**
 * edit model
 */
 function edit(id){
 	"use strict";
 	$('#add').modal('show');
 	$('#add .add-title').addClass('hide');
 	$('#add .edit-title').removeClass('hide');
 	$('#add input[name="id"]').val(id);
 	var requestURL = admin_url+'fixed_equipment/get_modal_content_warehouses/' + (typeof(id) != 'undefined' ? id : '');
 	requestGetJSON(requestURL).done(function(response) {
 		$('#add .modal-body').html('');
 		$('#add .modal-body').html(response.data);
 		init_selectpicker();		
 		appValidateForm($('#form_add_warehouse'), {
 			'code': 'required',
 			'name': 'required',
 			'order': 'required'
 		})
 	}).fail(function(data) {
 		alert_float('danger', 'Error');
 	});
 }
