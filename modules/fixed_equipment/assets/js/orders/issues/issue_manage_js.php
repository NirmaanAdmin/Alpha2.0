
<script>

	"use strict";

	var InvoiceServerParams={
		"client_filter": "[name='client_filter[]']",
		"category_filter": "[name='category_filter[]']",
		"priority_filter": "[name='priority_filter[]']",
		"ticket_status_filter": "[name='ticket_status_filter[]']",

	};
	var ticket_table = $('.table-ticket_table');
	initDataTable(ticket_table, admin_url+'customer_service/ticket_table',[0],[0], InvoiceServerParams, [0 ,'desc']);

	$.each(InvoiceServerParams, function(i, obj) {
		$('select' + obj).on('change', function() {  
			ticket_table.DataTable().ajax.reload();
		});
	});

	$('#date_add').on('change', function() {
		ticket_table.DataTable().ajax.reload().columns.adjust().responsive.recalc();
	});

	var hidden_columns = [0];
	$('.table-ticket_table').DataTable().columns(hidden_columns).visible(false, false);

</script>