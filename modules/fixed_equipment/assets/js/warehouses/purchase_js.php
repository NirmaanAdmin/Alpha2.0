
<script>
	var purchase;
	var lastAddedItemKey = null;

	(function($) {
		"use strict";
		init_goods_receipt_currency(<?php echo html_entity_decode($base_currency_id) ?>);

		// Maybe items ajax search
		init_ajax_search('items','#item_select.ajax-search', undefined, admin_url+'warehouse/wh_commodity_code_search');

		appValidateForm($('#add_goods_receipt'), {
			date_c: 'required',
			date_add: 'required',
			<?php if($pr_orders_status == true && get_warehouse_option('goods_receipt_required_po') == 1 ){ ?>
				pr_order_id: 'required',

			<?php } ?>
		}); 

		calculate_total();

	})(jQuery);

	function get_tax_name_by_id(tax_id){
		"use strict";
		var taxe_arr = <?php echo json_encode($taxes); ?>;
		var name_of_tax = '';
		$.each(taxe_arr, function(i, val){
			if(val.id == tax_id){
				name_of_tax = val.label;
			}
		});
		return name_of_tax;
	}

	function tax_rate_by_id(tax_id){
		"use strict";
		var taxe_arr = <?php echo json_encode($taxes); ?>;
		var tax_rate = 0;
		$.each(taxe_arr, function(i, val){
			if(val.id == tax_id){
				tax_rate = val.taxrate;
			}
		});
		return tax_rate;
	}

	function numberWithCommas(x) {
		"use strict";
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}


//version2
(function($) {
	"use strict";

// Add item to preview from the dropdown for invoices estimates
$("body").on('change', 'select[name="item_select"]', function () {
	var itemid = $(this).selectpicker('val');
	if (itemid != '') {
		add_item_to_preview(itemid);
	}
	else{
		clear_item_preview();
	}
});

// Recaulciate total on these changes
$("body").on('change', 'select.taxes', function () {
	calculate_total();
});

$("body").on('click', '.add_goods_receipt', function () {
	submit_form(false);
});

$('.add_goods_receipt_send').on('click', function() {
	submit_form(true);
});


$('select[name="pr_order_id"]').on('change', function() {
	"use strict";  

	var pr_order_id = $('select[name="pr_order_id"]').val();
	$.get(admin_url+'warehouse/coppy_pur_request/'+pr_order_id).done(function(response){
		response = JSON.parse(response);

		if(response){
			$('.invoice-item table.invoice-items-table.items tbody').html('');
			$('.invoice-item table.invoice-items-table.items tbody').append(response.list_item);

			setTimeout(function () {
				calculate_total();
			}, 15);

			init_selectpicker();
			init_datepicker();
			wh_reorder_items('.invoice-item');
			wh_clear_item_preview_values('.invoice-item');
			$('body').find('#items-warning').remove();
			$("body").find('.dt-loader').remove();
			$('#item_select').selectpicker('val', '');

		}

	}).fail(function(error) {

	});

	if(pr_order_id != ''){

		$.post(admin_url + 'warehouse/copy_pur_vender/'+pr_order_id).done(function(response){
			var response_vendor = JSON.parse(response);

			$('select[name="supplier_code"]').val(response_vendor.userid).change();
			$('select[name="buyer_id"]').val(response_vendor.buyer).change();

			$('select[name="project"]').val(response_vendor.project).change();
			$('select[name="type"]').val(response_vendor.type).change();
			$('select[name="department"]').val(response_vendor.department).change();
			$('select[name="requester"]').val(response_vendor.requester).change();

		});
	}else{
		$('select[name="supplier_code"]').val('').change();
		$('select[name="buyer_id"]').val('').change();

		$('select[name="project"]').val('').change();
		$('select[name="type"]').val('').change();
		$('select[name="department"]').val('').change();
		$('select[name="requester"]').val('').change();
	}

});

// Recaulciate total on these changes
$("body").on('change', '.sortable .serial_number input, #serial_number', function () {
	var obj = $(this);
	if(obj.attr('readonly') != 'undefined'){
		$('.add_goods_receipt, .main .btn-add').attr('disabled', true);
		requestGetJSON('fixed_equipment/check_exist_serial_inventory/' + obj.val()).done(function (response) {
			if(response != ''){
				alert_float('warning', response);
				obj.attr('data-duplicate', true);
			}
			else{
				obj.removeAttr('data-duplicate');
			}
			check_duplicate_serial_number();
		});
	}
	check_duplicate_serial_number();
});

})(jQuery);

var data;
// Add item to preview
function add_item_to_preview(id) {
	"use strict";
	clear_item_preview();
	requestGetJSON('fixed_equipment/get_item_by_id/' + id).done(function (response) {
		data = response;
		$('.main input[name="commodity_code"]').val(id);
		$('.main textarea[name="commodity_name"]').val(response.name);
		$('.main input[name="unit_price"]').val(response.purchase_price);
		$('.main input[name="quantities"]').val(1);
		if(response.is_model == 1){
			$('.main input[name="quantities"]').removeAttr('readonly');
			$('.main input[name="serial_number"]').attr('readonly', true);			
		}
		else{
			$('.main input[name="quantities"]').removeAttr('readonly');			
			$('.main input[name="serial_number"]').attr('readonly', true);
		}
		$(document).trigger({
			type: "item-added-to-preview",
			item: response,
			item_type: 'item',
		});
	});
}

function wh_add_item_to_table() {
	"use strict";
	var previewArea = $('.main');
	if(previewArea.find('input[name="commodity_code"]').val() == ''){
		alert_float('warning', '<?php echo _l('fe_please_select_an_item'); ?>');
		return false;
	}
	if($('#warehouse_id_m').val() == ''){
		if(previewArea.find('select[name="warehouse_id"]').val() == ''){
			alert_float('warning', '<?php echo _l('fe_please_select_a_warehouse'); ?>');
			return false;
		}
	}
	if(previewArea.find('input[name="quantities"]').val() == ''){
		alert_float('warning', '<?php echo _l('fe_please_input_quantity'); ?>');
		return false;
	}
	if(previewArea.find('input[name="unit_price"]').val() == ''){
		alert_float('warning', '<?php echo _l('fe_please_input_unit_price'); ?>');
		return false;
	}
	var attr = previewArea.find('input[name="quantities"]').attr('readonly');
	if((typeof attr !== 'undefined' && attr !== false) && previewArea.find('input[name="serial_number"]').val() == ''){
		alert_float('warning', '<?php echo _l('fe_please_input_serial_number'); ?>');
		return false;
	}

	var response = {};
	response.commodity_name = $('.invoice-item .main textarea[name="commodity_name"]').val();
	response.warehouse_id = $('.invoice-item .main select[name="warehouse_id"]').val();
	response.quantities = $('.invoice-item .main input[name="quantities"]').val();
	response.unit_price = $('.invoice-item .main input[name="unit_price"]').val();
	response.taxname = $('.main select.taxes').selectpicker('val');
	response.commodity_code = $('.invoice-item .main input[name="commodity_code"]').val();
	response.serial_number = $('.invoice-item .main input[name="serial_number"]').val();



	var table_row = '';
	var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('.invoice-items-table tbody .item').length + 1;

	var main_quantities = response.quantities;
	requestGetJSON('fixed_equipment/get_item_by_id/' + previewArea.find('input[name="commodity_code"]').val()).done(function (get_item) {
		if(get_item.is_model == 1){
			// open serial modal
			fill_multiple_serial_number_modal(main_quantities, 'newitems[' + item_key + ']');
			return true;
		}else{

			// lastAddedItemKey = item_key;
			response.item_key = item_key;
			response.name = 'newitems[' + item_key + ']';
			$("body").append('<div class="dt-loader"></div>');
			wh_get_item_row_template(response).done(function(output){
				table_row += output;
				$('.invoice-item table.invoice-items-table.items tbody').append(table_row);

				setTimeout(function () {
					calculate_total();
				}, 15);

				init_selectpicker();
				init_datepicker();
				wh_reorder_items('.invoice-item');
				clear_item_preview();
				$('body').find('#items-warning').remove();
				$("body").find('.dt-loader').remove();
				$('#item_select').selectpicker('val', '');
				return true;
			});
			return false;

		}
	});
}

function remove_row(el) {
	"use strict";
	$(el).closest('.row_item').remove();
}

function clear_item_preview() {
	"use strict";
	var previewArea = $('.main');
	previewArea.find('input').val('');
	previewArea.find('textarea').val('');
	previewArea.find('select').val('').selectpicker('refresh');
	previewArea.find('input[name="quantities"]').removeAttr('disabled');	
	$('#serial_number').removeAttr('data-duplicate');
	check_duplicate_serial_number();
}

function wh_get_item_row_template(response) {
	"use strict";
	jQuery.ajaxSetup({
		async: false
	});
	var d = $.post(admin_url + 'fixed_equipment/get_good_receipt_row_template', response);
	jQuery.ajaxSetup({
		async: true
	});
	return d;
}

function wh_delete_item(row, itemid,parent) {
	"use strict";

	$(row).parents('tr').addClass('animated fadeOut', function () {
		setTimeout(function () {
			$(row).parents('tr').remove();
			calculate_total();
		}, 50);
	});
	if (itemid && $('input[name="isedit"]').length > 0) {
		$(parent+' #removed-items').append(hidden_input('removed_items[]', itemid));
	}
}

function wh_reorder_items(parent) {
	"use strict";

	var rows = $(parent + ' .table.has-calculations tbody tr.item');
	var i = 1;
	$.each(rows, function () {
		$(this).find('input.order').val(i);
		i++;
	});
}

function calculate_total(){
	"use strict";
	if ($('body').hasClass('no-calculate-total')) {
		return false;
	}

	var calculated_tax,
	taxrate,
	item_taxes,
	row,
	_amount,
	_tax_name,
	taxes = {},
	taxes_rows = [],
	subtotal = 0,
	total = 0,
	total_tax_money = 0,
	quantity = 1,
	total_discount_calculated = 0,
	rows = $('.table.has-calculations tbody tr.item'),
	subtotal_area = $('#subtotal'),
	discount_area = $('#discount_area'),
	adjustment = $('input[name="adjustment"]').val(),
		// discount_percent = $('input[name="discount_percent"]').val(),
		discount_percent = 'before_tax',
		discount_fixed = $('input[name="discount_total"]').val(),
		discount_total_type = $('.discount-total-type.selected'),
		discount_type = $('select[name="discount_type"]').val();

		$('.wh-tax-area').remove();

		$.each(rows, function () {

			quantity = $(this).find('[data-quantity]').val();
			if (quantity === '') {
				quantity = 1;
				$(this).find('[data-quantity]').val(1);
			}

			_amount = accounting.toFixed($(this).find('td.rate input').val() * quantity, app.options.decimal_places);
			_amount = parseFloat(_amount);

			$(this).find('td.amount').html(format_money(_amount, true));

			subtotal += _amount;
			row = $(this);
			item_taxes = $(this).find('select.taxes').val();

			if (item_taxes) {
				$.each(item_taxes, function (i, taxname) {
					taxrate = row.find('select.taxes [value="' + taxname + '"]').data('taxrate');
					calculated_tax = (_amount / 100 * taxrate);
					if (!taxes.hasOwnProperty(taxname)) {
						if (taxrate != 0) {
							_tax_name = taxname.split('|');
							var tax_row = '<tr class="wh-tax-area"><td>' + _tax_name[0] + '(' + taxrate + '%)</td><td id="tax_id_' + slugify(taxname) + '"></td></tr>';
							$(subtotal_area).after(tax_row);
							taxes[taxname] = calculated_tax;
						}
					} else {
                    // Increment total from this tax
                    taxes[taxname] = taxes[taxname] += calculated_tax;
                }
            });
			}
		});

	// Discount by percent
	if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
		total_discount_calculated = (subtotal * discount_percent) / 100;
	} else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
		total_discount_calculated = discount_fixed;
	}

	$.each(taxes, function (taxname, total_tax) {
		if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
			total_tax_calculated = (total_tax * discount_percent) / 100;
			total_tax = (total_tax - total_tax_calculated);
		} else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
			var t = (discount_fixed / subtotal) * 100;
			total_tax = (total_tax - (total_tax * t) / 100);
		}

		total += total_tax;
		total_tax_money += total_tax;
		total_tax = format_money(total_tax);
		$('#tax_id_' + slugify(taxname)).html(total_tax);
	});

	total = (total + subtotal);

	// Discount by percent
	if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-percent')) {
		total_discount_calculated = (total * discount_percent) / 100;
	} else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-fixed')) {
		total_discount_calculated = discount_fixed;
	}

	total = total - total_discount_calculated;
	adjustment = parseFloat(adjustment);

	// Check if adjustment not empty
	if (!isNaN(adjustment)) {
		total = total + adjustment;
	}

	var discount_html = '-' + format_money(total_discount_calculated);
	$('input[name="discount_total"]').val(accounting.toFixed(total_discount_calculated, app.options.decimal_places));

	// Append, format to html and display
	$('.discount-total').html(discount_html);
	$('.adjustment').html(format_money(adjustment));

	console.log(subtotal);

	$('.wh-subtotal').html(format_money(subtotal) + hidden_input('total_goods_money', accounting.toFixed(subtotal, app.options.decimal_places)) + hidden_input('value_of_inventory', accounting.toFixed(subtotal, app.options.decimal_places)));

	$('.inventory_value').remove();
	var total_inventory_value = '<tr class="inventory_value"><td><span class="bold"><?php echo _l('value_of_inventory'); ?> :</span></td><td class="">'+format_money(subtotal)+'</td></tr>';
	$('#subtotal').after(total_inventory_value);

	$('.total_tax_value').remove();
	var total_tax_value = '<tr class="total_tax_value"><td><span class="bold"><?php echo _l('total_tax_money'); ?> :</span></td><td class="">'+format_money(total_tax_money)+'</td></tr>';
	$('#totalmoney').before(total_tax_value);

	$('.wh-total').html(format_money(total) + hidden_input('total_tax_money', accounting.toFixed(total_tax_money, app.options.decimal_places)) + hidden_input('total_money', accounting.toFixed(total, app.options.decimal_places)));
	$(document).trigger('wh-receipt-note-total-calculated');
}


function submit_form(save_and_send_request) {
	"use strict";
	calculate_total();

	$('input[name="save_and_send_request"]').val(save_and_send_request);

	var rows = $('.table.has-calculations tbody tr.item');
	if(rows.length == 0){
		alert_float('warning', '<?php echo _l('fe_please_select_at_least_one_item'); ?>', 3000);
		return false;
	}



	var check_select_warehouse2 = true;
	if($('#warehouse_id_m').val() == ''){
		check_select_warehouse2 = false;
	}

	var check_select_warehouse1 = true;
	if(!check_select_warehouse2){
		$.each(rows, function () {
			var warehouse_id = $(this).find('td.warehouse_select select').val();
			if(warehouse_id == '' || warehouse_id == undefined){
				alert_float('warning', '<?php echo _l('fe_please_select_a_warehouse'); ?>', 3000);
				check_select_warehouse1 = false;
				return false;
			}
		})
	}

	if(check_select_warehouse1 == false){
		return false;
	}
	// Add disabled to submit buttons
	$(this).find('.add_goods_receipt_send').prop('disabled', true);
	$(this).find('.add_goods_receipt').prop('disabled', true);
	$('#add_goods_receipt').submit();
	return true;
}

/*scanner barcode*/
$(document).ready(function() {
	var pressed = false;
	var chars = [];
	$(window).keypress(function(e) {
		if (e.key == '%') {
			pressed = true;
		}
		chars.push(String.fromCharCode(e.which));
		if (pressed == false) {
			setTimeout(function() {
				if (chars.length >= 8) {
					var barcode = chars.join('');
					requestGetJSON('warehouse/wh_get_item_by_barcode/' + barcode).done(function (response) {
						if(response.status == true || response.status == 'true'){
							wh_add_item_to_preview(response.id);
							alert_float('success', response.message);
						}else{
							alert_float('warning', '<?php echo _l('no_matching_products_found') ?>');
						}
					});

				}
				chars = [];
				pressed = false;
			}, 200);
		}
		pressed = true;
	});
});


function wh_view_serial_number(name_quantities, serial_input, prefix_name){
	"use strict";

	var serial_input_value = $('input[name="'+serial_input+'"]').val();
	if(serial_input_value == ''){
		var quantity = $('input[name="'+name_quantities+'"]').val();
		fill_multiple_serial_number_modal(quantity, prefix_name);
	}else{

		$("#modal_wrapper").load("<?php echo admin_url('warehouse/warehouse/fill_multiple_serial_number_modal'); ?>", {
			slug: 'edit',
			serial_input_value:serial_input_value,
			prefix_name:prefix_name,

		}, function() {
			$("body").find('#serialNumberModal').modal({ show: true, backdrop: 'static' });
		});
	}

}

// Set the currency for accounting
function init_goods_receipt_currency(id, callback) {
	var $accountingTemplate = $("body").find('.accounting-template');

	if ($accountingTemplate.length || id) {
		var selectedCurrencyId = !id ? $accountingTemplate.find('select[name="currency"]').val() : id;

		requestGetJSON('misc/get_currency/' + selectedCurrencyId)
		.done(function (currency) {
                // Used for formatting money
                accounting.settings.currency.decimal = currency.decimal_separator;
                accounting.settings.currency.thousand = currency.thousand_separator;
                accounting.settings.currency.symbol = currency.symbol;
                accounting.settings.currency.format = currency.placement == 'after' ? '%v %s' : '%s%v';

                calculate_total();

                if(callback) {
                	callback();
                }
            });
	}
}


function check_duplicate_serial_number(){
	"use strict";
	var list = $('.sortable .serial_number input, #serial_number');
	var has_duplicate = false;
	let i = 0;
	for (i = 0; i < list.length; i++) {
		list.eq(i).removeAttr('style');
	}
	i = 0;
	for (i = 0; i < list.length; i++) {
		var this_obj = list.eq(i);
		if(typeof this_obj.attr('data-duplicate') != 'undefined'){
			this_obj.attr('style', 'border-color: red !important');
			has_duplicate = true;
		}
		let j = 0;
		for (j = 0; j < list.length; j++) {
			if((i != j) && (this_obj.val() != '' && list.eq(j).val() != '' && this_obj.val() == list.eq(j).val())){
				this_obj.attr('style', 'border-color: red !important');
				has_duplicate = true;
			}
		}
	}
	if(has_duplicate == true){
		$('.add_goods_receipt, .main .btn-add').attr('disabled', true);
	}
	else{
		$('.add_goods_receipt, .main .btn-add').removeAttr('disabled');
	}
}

function fill_multiple_serial_number_modal(quantity, prefix_name) {
	"use strict";

	if( quantity > 0){
		$("#modal_wrapper").load("<?php echo admin_url('fixed_equipment/fixed_equipment/fill_multiple_serial_number_modal'); ?>", {
			slug: 'add',
			quantity:quantity,
			prefix_name:prefix_name,
		}, function() {
			$("body").find('#serialNumberModal').modal({ show: true, backdrop: 'static' });
		});
	}else{
		alert_float('warning', "<?php echo _l('please_choose_quantity_more_than_0') ?>");
	}

	init_selectpicker();
	$(".selectpicker").selectpicker('refresh');
}


</script>
