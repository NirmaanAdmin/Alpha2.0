(function($) {
	$('input[name="return_type"]').on('change', function(){
		var val = $(this).val();
		$('.refund_quantity_reason').val('');
		if(val == 'fully'){
			var quantity_obj = $('.refund_quantity_item');
			$.each(quantity_obj, function( key, value ) {
				$(this).attr('readonly', true);
				$(this).val($(this).attr('max'));
			}); 
			$('.select-item-checkbox').prop('checked', true).closest('td,th').addClass('hide');
		}
		else{
			$('.refund_quantity_item').removeAttr('readonly');
			$('.select-item-checkbox').prop('checked', true).closest('td,th').removeClass('hide');
		}
		change_checkbox_row_reason_type();
	});

	$('#return_order_send_rq_btn').on('click', function(){
		var select_item_checkbox = $('.select-item-checkbox');
		var check_selected_item = false;
		select_item_checkbox.each(function(i, e){
			if($(this).is(':checked')){
				check_selected_item = true;
			}
		});
		if(!check_selected_item){
			alert_float('danger', $('input[name="please_select_item"]').val());
			return false;
		}
		
		$('#return_order_submit_btn').click();
	});

	$('input[name="return_reason_type"]').on('change', function(){
		var value = $(this).val();
		if(value == 'return_and_get_money_back'){
			$('.get_money_back_reason').removeClass('hide').find('textarea').attr('required', true);
		}
		else{
			$('.get_money_back_reason').addClass('hide').find('textarea').removeAttr('required');
		}
		change_checkbox_row_reason_type();
	});
})(jQuery);

function change_checkbox_row_reason_type(){
	var item_checkbox = $('#refund_modal tbody tr.main');
	var value = $('input[name="return_reason_type"]:checked').val();
	if(value == 'return_for_maintenance_repair'){
		item_checkbox.each(function(i, e){
			var this_obj = $(this); 
			if(this_obj.data('type') != 'asset'){
				this_obj.addClass('disabled').find('input[type="checkbox"]').prop('checked', false);
			}
		});
	}
	else{
		item_checkbox.removeClass('disabled');
	}
}
function open_modal_chosse(){
	"use strict";
	$('#chosse').modal();
}
function open_refund_modal(){
	"use strict";
	$('#refund_modal').modal();
}
function select_all_item(el){
	var check = $(el).is(':checked');
	if(check == true){
		$('td .select-item-checkbox').prop('checked', true);
	}
	else{
		$('td .select-item-checkbox').prop('checked', false);
	}
}
function select_return_item(el){
	var check = $(el).is(':checked');
	if(check == false){
		$('th .select-item-checkbox').prop('checked', false);
	}
}