<script type="text/javascript">
	(function($) {
		"use strict";  

		init_goods_delivery_currency(<?php echo new_html_entity_decode($base_currency_id) ?>);
		appValidateForm($('#add_order_manual'), {
			userid: 'required',
			order_number: 'required',
			sale_agent: 'required',
		});

		wh_calculate_total(); 
		

		// Add item to preview from the dropdown for invoices estimates
		$("body").on('change', 'select[name="item_select"]', function () {
			var itemid = $(this).selectpicker('val');
			if (itemid != '') {
				wh_add_item_to_preview(itemid);
			}
		});

		$("body").on("change", "#userid", function () {
			var val = $(this).val();

			clear_billing_and_shipping_details();
			if (!val) {
				return false;
			}
			var currentInvoiceID = $("body")
			.find('input[name="merge_current_invoice"]')
			.val();
			currentInvoiceID =
			typeof currentInvoiceID == "undefined" ? "" : currentInvoiceID;

			requestGetJSON(
				"fixed_equipment/client_change_data/" + val + "/" + currentInvoiceID
				).done(function (response) {
					$("#merge").html(response.merge_info);

					for (var f in billingAndShippingFields) {
						if (billingAndShippingFields[f].indexOf("billing") > -1) {
							if (billingAndShippingFields[f].indexOf("country") > -1) {
								$(
									'select[name="' + billingAndShippingFields[f] + '"]'
									).selectpicker(
									"val",
									response["billing_shipping"][0][billingAndShippingFields[f]]
									);
								} else {
									if (billingAndShippingFields[f].indexOf("billing_street") > -1) {
										$('textarea[name="' + billingAndShippingFields[f] + '"]').val(
											response["billing_shipping"][0][billingAndShippingFields[f]]
											);
									} else {
										$('input[name="' + billingAndShippingFields[f] + '"]').val(
											response["billing_shipping"][0][billingAndShippingFields[f]]
											);
									}
								}
							}
						}

						if (!empty(response["billing_shipping"][0]["shipping_street"])) {
							$('input[name="include_shipping"]').prop("checked", true).change();
						}

						for (var fsd in billingAndShippingFields) {
							if (billingAndShippingFields[fsd].indexOf("shipping") > -1) {
								if (billingAndShippingFields[fsd].indexOf("country") > -1) {
									$(
										'select[name="' + billingAndShippingFields[fsd] + '"]'
										).selectpicker(
										"val",
										response["billing_shipping"][0][billingAndShippingFields[fsd]]
										);
									} else {
										if (billingAndShippingFields[fsd].indexOf("shipping_street") > -1) {
											$('textarea[name="' + billingAndShippingFields[fsd] + '"]').val(
												response["billing_shipping"][0][billingAndShippingFields[fsd]]
												);
										} else {
											$('input[name="' + billingAndShippingFields[fsd] + '"]').val(
												response["billing_shipping"][0][billingAndShippingFields[fsd]]
												);
										}
									}
								}
							}

							init_billing_and_shipping_details();

						});
			});

		$("body").on('change', 'input[name="qty"]', function () {

			"use strict"; 
			var available_quantity = $('.main input[name="available_quantity"]').val();
			var quantities = $('.main input[name="qty"]').val();
			if(parseFloat(available_quantity) < parseFloat(quantities)){
				alert_float('warning', '<?php echo _l('inventory_quantity_is_not_enough') ?>');
				$('.main input[name="qty"]').val(available_quantity);
			}
		});

	})(jQuery);

	function wh_add_item_to_table(data, itemid) {
		"use strict";
		data = typeof (data) == 'undefined' || data == 'undefined' ? wh_get_item_preview_values() : data;

		if ((data.available_quantity == "" || data.quantities == "" || data.product_id == "" )) {
			
			if(parseFloat(data.available_quantity) < parseFloat(data.quantities)){
      //check_available_quantity
				alert_float('warning', '<?php echo _l('Inventory quantity is not enough') ?>');
			}
			return;
		}

		if(parseFloat(data.available_quantity) < parseFloat(data.quantities)){
      //check_available_quantity
			alert_float('warning', '<?php echo _l('Inventory quantity is not enough') ?>');
			return;
		}
		var data_post = {};
		data_post.product_id = data.product_id;
		data_post.quantity = data.quantities;
		data_post.description = data.description;

		if($.isNumeric(data.product_id)){
			after_wh_add_item_to_table('undefined', 'undefined', '');
    
		}else{
			//get serial number
			$.post(admin_url + 'fixed_equipment/get_serial_number', data_post).done(function(response){
				response = JSON.parse(response);
				if(response.status == true || response.status == 'true'){
					fill_multiple_serial_number_modal(response.table_serial_number);
				}else{
					after_wh_add_item_to_table('undefined', 'undefined', '');
				}

			});
		}

	}

	function fill_multiple_serial_number_modal(table_serial_number) {
		"use strict";

		$("#modal_wrapper").load("<?php echo admin_url('fixed_equipment/fixed_equipment/load_serial_number_modal'); ?>", {
			table_serial_number: table_serial_number,
		}, function() {
			$("body").find('#serialNumberModal').modal({ show: true, backdrop: 'static' });
		});

	}

	function wh_get_item_preview_values() {
		"use strict";

		var response = {};

		response.description = $('.invoice-item .main textarea[name="description"]').val();
		response.product_id = $('.invoice-item .main input[name="product_id"]').val();
		response.quantities = $('.invoice-item .main input[name="qty"]').val();
		response.available_quantity = $('.invoice-item .main input[name="available_quantity"]').val();
		response.rate = $('.invoice-item .main input[name="sku"]').val();
		response.sku = $('.invoice-item .main input[name="sku"]').val();
		return response;
	}

	// Add item to preview
	function wh_add_item_to_preview(id) {
		"use strict";

		requestGetJSON('fixed_equipment/get_model_by_id/' + id +'/'+true).done(function (response) {
			clear_item_preview_values();

			$('.main textarea[name="description"]').val(response.name);
			$('.main input[name="product_id"]').val(response.model_id);
			$('.main input[name="available_quantity"]').val(response.total_available_qty);
			$('.main input[name="qty"]').val(1);
			$('.main input[name="rate"]').val(response.rate);
			

			$('.selectpicker').selectpicker('refresh');

			var $currency = $("body").find('.accounting-template select[name="currency"]');
			var baseCurency = $currency.attr('data-base');
			var selectedCurrency = $currency.find('option:selected').val();
			var $rateInputPreview = $('.main input[name="rate"]');

			if (baseCurency == selectedCurrency) {
				$rateInputPreview.val(response.rate);
			} else {
				var itemCurrencyRate = response['rate_currency_' + selectedCurrency];
				if (!itemCurrencyRate || parseFloat(itemCurrencyRate) === 0) {
					$rateInputPreview.val(response.rate);
				} else {
					$rateInputPreview.val(itemCurrencyRate);
				}
			}

			$(document).trigger({
				type: "item-added-to-preview",
				item: response,
				item_type: 'item',
			});
		});
	}

	function after_wh_add_item_to_table(data, itemid, formdata) {
		"use strict";

		data = typeof (data) == 'undefined' || data == 'undefined' ? wh_get_item_preview_values() : data;

		if ((data.available_quantity == "" || data.quantities == "" || data.product_id == "" )) {
			
			if(parseFloat(data.available_quantity) < parseFloat(data.quantities)){
      //check_available_quantity
				alert_float('warning', '<?php echo _l('Inventory quantity is not enough') ?>');
			}

			return;
		}
		if(parseFloat(data.available_quantity) < parseFloat(data.quantities)){
      //check_available_quantity
			alert_float('warning', '<?php echo _l('Inventory quantity is not enough') ?>');
			return;
		}

		var table_row = '';
		var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('.invoice-items-table tbody .item').length + 1;
		lastAddedItemKey = item_key;
		$("body").append('<div class="dt-loader"></div>');
		wh_get_item_row_template('newitems[' + item_key + ']', data.description, data.product_id, data.quantities, data.available_quantity, data.rate, data.sku, itemid, item_key, formdata).done(function(output){
			table_row += output;

			lastAddedItemKey = parseInt(lastAddedItemKey) + parseInt(data.quantities);
			$('.invoice-item table.invoice-items-table.items tbody').append(table_row);

			setTimeout(function () {
				wh_calculate_total();
			}, 15);
			init_selectpicker();
			init_datepicker();
			wh_reorder_items('.invoice-item');
			wh_clear_item_preview_values('.invoice-item');
			$('body').find('#items-warning').remove();
			$("body").find('.dt-loader').remove();
			$('#item_select').selectpicker('val', '');

			return true;
		});
		return false;
	}

	function wh_delete_item(row, itemid,parent) {
		"use strict";

		$(row).parents('tr').addClass('animated fadeOut', function () {
			setTimeout(function () {
				$(row).parents('tr').remove();
				wh_calculate_total();
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

	function wh_clear_item_preview_values(parent) {
		"use strict";

		var previewArea = $(parent + ' .main');
		previewArea.find('input').val('');
		previewArea.find('textarea').val('');
		previewArea.find('select').val('').selectpicker('refresh');
	}

	function wh_calculate_total(){
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
		total_money = 0,
		total_tax_money = 0,
		quantity = 1,
		total_discount_calculated = 0,
		item_discount_percent = 0,
		item_discount = 0,
		item_total_payment,
		rows = $('.table.has-calculations tbody tr.item'),
		subtotal_area = $('#subtotal'),
		discount_area = $('#discount_area'),
		adjustment = $('input[name="adjustment"]').val(),
    // discount_percent = $('input[name="discount_percent"]').val(),
		discount_percent = 'before_tax',
		discount_fixed = $('input[name="discount_total"]').val(),
		discount_total_type = $('.discount-total-type.selected'),
		discount_type = $('select[name="discount_type"]').val(),
		additional_discount = $('input[name="additional_discount"]').val(),
		shipping_fee = $('input[name="shipping_fee"]').val();


		$('.wh-tax-area').remove();

		$.each(rows, function () {

			var item_tax = 0,
			item_amount  = 0;

			quantity = $(this).find('[data-quantity]').val();
			if (quantity === '') {
				quantity = 1;
				$(this).find('[data-quantity]').val(1);
			}
			item_discount_percent = $(this).find('td.discount input').val();

			if (isNaN(item_discount_percent) || item_discount_percent == '') {
				item_discount_percent = 0;
			}

			_amount = accounting.toFixed($(this).find('td.rate input').val() * quantity, app.options.decimal_places);
			item_amount = _amount;
			_amount = parseFloat(_amount);

			$(this).find('td.amount').html(format_money(_amount));

			subtotal += _amount;
			row = $(this);
		});

		total = (total + subtotal);
		total_money = total;

		total = total;

		$('.subtotal').html(format_money(subtotal) + hidden_input('sub_total', accounting.toFixed(subtotal, app.options.decimal_places)) + hidden_input('sub_total', accounting.toFixed(total_money, app.options.decimal_places)));
		$('.total').html(format_money(total) + hidden_input('total', accounting.toFixed(total, app.options.decimal_places)));

		$(document).trigger('wh-manual-order-total-calculated');

	}


	function wh_get_item_row_template(name, description, product_id, quantities, available_quantity, rate, sku, item_key, item_index, formdata)  {
		"use strict";

		jQuery.ajaxSetup({
			async: false
		});

		var d = $.post(admin_url + 'fixed_equipment/get_manual_order_row_template', {
			name: name,
			description: description,
			product_id: product_id,
			quantities: quantities,
			available_quantity: available_quantity,
			rate: rate,
			sku: sku,
			item_key : item_key,
			item_index : item_index,
			formdata : formdata,
		});
		jQuery.ajaxSetup({
			async: true
		});
		return d;
	}

	// Set the currency for accounting
	function init_goods_delivery_currency(id, callback) {
		var $accountingTemplate = $("body").find('.accounting-template');

		if ($accountingTemplate.length || id) {
			var selectedCurrencyId = !id ? $accountingTemplate.find('select[name="currency"]').val() : id;

			requestGetJSON(admin_url + 'misc/get_currency/' + selectedCurrencyId)
			.done(function (currency) {
                // Used for formatting money
				accounting.settings.currency.decimal = currency.decimal_separator;
				accounting.settings.currency.thousand = currency.thousand_separator;
				accounting.settings.currency.symbol = currency.symbol;
				accounting.settings.currency.format = currency.placement == 'after' ? '%v %s' : '%s%v';

				wh_calculate_total();

				if(callback) {
					callback();
				}
			});
		}
	}
</script>