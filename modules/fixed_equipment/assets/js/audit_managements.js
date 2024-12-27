(function(){
	"use strict";
	var fnServerParams = {
		"auditor": "[name='auditor_filter[]']",
		"status": "[name='status_filter']",
		"audit_from_date": "[name='audit_from_date_filter']",
		"audit_to_date": "[name='audit_to_date_filter']"
	}
	initDataTable('.table-audit_management', admin_url + 'fixed_equipment/audit_managements_table', '', '', fnServerParams, [1, 'desc']);

	$('select[name="auditor_filter[]"], select[name="status_filter"], input[name="audit_from_date_filter"], input[name="audit_to_date_filter"]').change(function(){
		$('.table-audit_management').DataTable().ajax.reload()
		.columns.adjust();
	});

	$(document).on("click","#mass_select_all",function() {
		var favorite = [];
		if($(this).is(':checked')){
			$('.individual').prop('checked', true);
			$.each($(".individual"), function(){ 
				favorite.push($(this).data('id'));
			});
		}else{
			$('.individual').prop('checked', false);
			favorite = [];
		}

		$("input[name='check']").val(favorite);
	});
})(jQuery);

function bulk_delete(){
	"use strict";
	var print_id = $('input[name="check"]').val();
	if(print_id != ''){
		if(confirm($('input[name="are_you_sure_you_want_to_delete_these_items"]').val()) == true){
			window.location.href = admin_url+"fixed_equipment/delete_all_audit/"+encodeURIComponent(print_id);
		}
	}
	else{
		alert_float('danger', $('input[name="please_select_at_least_one_item_from_the_list"]').val());
	}
}

function checked_add(el){
	"use strict";
	var id = $(el).data("id");
	var id_product = $(el).data("product");
	if ($(".individual").length == $(".individual:checked").length) {
		$("#mass_select_all").attr("checked", "checked");
		var value = $("input[name='check']").val();
		if(value != ''){
			value = value + ',' + id;
		}else{
			value = id;
		}
	} else {
		$("#mass_select_all").removeAttr("checked");
		var value = $("input[name='check']").val();
		var arr_val = value.split(',');
		if(arr_val.length > 0){
			$.each( arr_val, function( key, value ) {
				if(value == id){
					arr_val.splice(key, 1);
					value = arr_val.toString();
					$("input[name='check']").val(value);
				}
			});
		}
	}
	if($(el).is(':checked')){
		var value = $("input[name='check']").val();
		if(value != ''){
			value = value + ',' + id;
		}else{
			value = id;
		}
		$("input[name='check']").val(value);
	}else{
		var value = $("input[name='check']").val();
		var arr_val = value.split(',');
		if(arr_val.length > 0){
			$.each( arr_val, function( key, value ) {
				if(value == id){
					arr_val.splice(key, 1);
					value = arr_val.toString();
					$("input[name='check']").val(value);
				}
			});
		}
	}
}
