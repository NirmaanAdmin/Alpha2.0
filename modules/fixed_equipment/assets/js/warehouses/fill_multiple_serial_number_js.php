<script>
	
	var transactions, dataObject_pu;

	(function($) {
		"use strict";  

		<?php if(isset($edit_serial_number_data)){ ?>
			dataObject_pu = <?php echo json_encode($edit_serial_number_data) ; ?>;
		<?php }else{?>
			dataObject_pu = [];
		<?php } ?>

		setTimeout(function(){

			var hotElement1 = document.getElementById('fill_multiple_serial_number_hs');

			transactions = new Handsontable(hotElement1, {

				contextMenu: false,
				manualRowMove: true,
				manualColumnMove: true,
				stretchH: 'all',
				autoWrapRow: true,
				rowHeights: 30,
				defaultRowHeight: 100,
				minRows: <?php echo new_html_entity_decode($min_row); ?>,
				maxsRows: <?php echo new_html_entity_decode($max_row); ?>,
				width: '100%',
				height: '350px',
				licenseKey: 'non-commercial-and-evaluation',
				rowHeaders: true,
				autoColumncommodity_group: {
					samplingRatio: 23
				},
				
				filters: true,
				manualRowRecommodity_group: true,
				manualColumnRecommodity_group: true,
				allowInsertRow: false,
				allowRemoveRow: false,
				columnHeaderHeight: 40,
				colWidths: [40, 50, 100,150, 150, 150,150,150,50,150,100,100,150,150,200,200,150,150],
				rowHeights: 30,
				
				rowHeaderWidth: [44],
				hiddenColumns: {
					columns: [],
					indicators: true
				},


				columns: [
				{
					type: 'text',
					data: 'serial_number',
					readOnly: false,
				},

				],

				colHeaders: [
				"<?php echo _l('fe_serial_number') ?>",
				],

				data: dataObject_pu,
			});

		},300);

	})(jQuery);


	$('.btn_submit_multiple_serial_number').on('click', function() {
		'use strict';

		var valid_edit_multiple_transaction = $('#fill_multiple_serial_number_hs').find('.htInvalid').html();

		if(valid_edit_multiple_transaction){
			alert_float('danger', "<?php echo _l('data_must_number') ; ?>");
		}else{
			var str_serial_number = '';
			var prefix_name = $('input[name="prefix_name"]').val();
			var arr_serial_number = transactions.getData();
			var serial_number_mandatory = false;

			$.each(arr_serial_number, function(i, val){
				if(val[0] != null && val[0] != ''){
					if(str_serial_number == ''){
						str_serial_number += val[0];
					}else{
						str_serial_number += ','+val[0];
					}
				}else{
					serial_number_mandatory = true;
				}
			});
			if(serial_number_mandatory){
				alert_float('danger', "<?php echo _l('fe_serial_number_as_mandatory') ; ?>");
				return;
			}

			var response = {};
			response.commodity_name = $('.invoice-item .main textarea[name="commodity_name"]').val();
			response.warehouse_id = $('.invoice-item .main select[name="warehouse_id"]').val();
			response.quantities = $('.invoice-item .main input[name="quantities"]').val();
			response.unit_price = $('.invoice-item .main input[name="unit_price"]').val();
			response.taxname = $('.main select.taxes').selectpicker('val');
			response.commodity_code = $('.invoice-item .main input[name="commodity_code"]').val();
			response.serial_number = str_serial_number;

			var table_row = '';
			var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('.invoice-items-table tbody .item').length + 1;
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
				$('#serialNumberModal').modal('hide');
				return true;
			});
			$('#serialNumberModal').modal('hide');
			return false;
			
		}
	});

	$('.btn_auto_generate_serial_number').on('click', function() {
		'use strict';

		var serial_number_quantity = $('input[name="serial_number_quantity"]').val();
		$.get(admin_url+'fixed_equipment/generate_serial_number/' + serial_number_quantity).done(function(response){
			response = JSON.parse(response);
			if(response){
				dataObject_pu = response.serial_numbers;
			    transactions.updateSettings({
			       data: dataObject_pu,

			    })
			}

		}).fail(function(error) {

		});

	});

</script>