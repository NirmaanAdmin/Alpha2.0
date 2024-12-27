(function(){
	"use strict";
	var fnServerParams = {
		"id": "[name='parent_id']"
	}
	initDataTable('.table-model_predefined_kits', admin_url + 'fixed_equipment/assign_asset_predefined_kit_table', false, false, fnServerParams, [0, 'desc']);

    appValidateForm($('#model_predefined_kits-form'), {
        'name': 'required'
    })

})(jQuery);

function add(){
	"use strict";
	$('#assign_asset').modal('show');
	$('#assign_asset input[name="id"]').val('');
    $('#assign_asset .add-title').removeClass('hide');
    $('#assign_asset .edit-title').addClass('hide');
	$('#assign_asset input[name="name"]').val('');    
    var parent_id = $('input[name="id"]').val();
    var requestURL = admin_url+'fixed_equipment/get_modal_content_assign_asset/' + parent_id;
	requestGetJSON(requestURL).done(function(response) {
		$('#assign-content').html(response.data);
		init_selectpicker();		
	}).fail(function(data) {
		alert_float('danger', 'Error');
	});
}

function edit(el){
	"use strict";
    var id = $(el).data('id');
    var parent_id = $(el).data('parent_id');
	$('#assign_asset').modal('show');
	$('#assign_asset input[name="id"]').val(id);
	$('#assign_asset input[name="name"]').val($(el).data('name'));    
	$('#assign-content').html('');
    $('#assign_asset .add-title').addClass('hide');
    $('#assign_asset .edit-title').removeClass('hide');
    var requestURL = admin_url+'fixed_equipment/get_modal_content_assign_asset/' + parent_id + '/'+ id;
	requestGetJSON(requestURL).done(function(response) {
		$('#assign-content').html(response.data);
		init_selectpicker();		
	}).fail(function(data) {
		alert_float('danger', 'Error');
	});
}
