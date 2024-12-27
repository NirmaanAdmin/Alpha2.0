<script>
	(function(){
		"use strict";
		var fnServerParams = {

		}
		initDataTable('.table-table_packing_list', admin_url + 'fixed_equipment/packing_list_managements_table', '', '', fnServerParams, [0, 'desc']);
	})(jQuery);

	function delivery_status_mark_as(status, task_id, type) {
		url = 'fixed_equipment/delivery_status_mark_as/' + status + '/' + task_id + '/' + type;
		var taskModalVisible = $('#task-modal').is(':visible');
		url += '?single_task=' + taskModalVisible;
		$("body").append('<div class="dt-loader"></div>');

		requestGetJSON(url).done(function (response) {
			$("body").find('.dt-loader').remove();
			if (response.success === true || response.success == 'true') {

				var av_tasks_tables = ['.table-table_packing_list', '.table-table_manage_packing_list'];
				$.each(av_tasks_tables, function (i, selector) {
					if ($.fn.DataTable.isDataTable(selector)) {
						$(selector).DataTable().ajax.reload(null, false);
					}
				});
				alert_float('success', response.message);
			}
		});
	}
</script>