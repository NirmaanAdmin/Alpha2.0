<script type="text/javascript">
	function fixed_equipment_order_list_status_mark_as(status, task_id, type) {
		"use strict"; 
		
		var url = 'fixed_equipment/fixed_equipment_order_list_status_mark_as/' + status + '/' + task_id + '/' + type;
		var taskModalVisible = $('#task-modal').is(':visible');
		$("body").append('<div class="dt-loader"></div>');

		requestGetJSON(url).done(function (response) {
			$("body").find('.dt-loader').remove();
			if (response.success === true || response.success == 'true') {

				if(type == 'stage_status' || type == 'ticket_status'){
					location.reload();
				}
				var av_tasks_tables = ['.table-ticket_table'];
				$.each(av_tasks_tables, function (i, selector) {
					if ($.fn.DataTable.isDataTable(selector)) {
						$(selector).DataTable().ajax.reload(null, false);
					}
				});
				alert_float('success', response.message);
			}
		});
	}
	
	function delete_issue_pdf_file(wrapper, attachment_id) {
		"use strict";  
		if (confirm_delete()) {
			$.get(admin_url + 'fixed_equipment/delete_issue_pdf_file/' +attachment_id, function (response) {
				if (response.success == true) {
					$(wrapper).parents('.contract-attachment-wrapper').remove();

					var totalAttachmentsIndicator = $('.attachments-indicator');
					var totalAttachments = totalAttachmentsIndicator.text().trim();

					if(totalAttachments == 1) {
						totalAttachmentsIndicator.remove();
					} else {
						totalAttachmentsIndicator.text(totalAttachments-1);
					}
					alert_float('success', "<?php echo _l('deleted_attach_file_successfully') ?>");

				} else {
					alert_float('danger', "<?php echo _l('deleting_attach_file_failed') ?>");
				}
			}, 'json');
		}
		return false;
	}

	function preview_file(invoker){
		'use strict';

		var id = $(invoker).attr('id');
		var rel_id = $(invoker).attr('rel_id');
		view_file(id, rel_id);
	}

	function view_file(id, rel_id) {   
		'use strict';

		$('#pdf_file_data').empty();
		$("#pdf_file_data").load(admin_url + 'fixed_equipment/view_pdf_file/' + id + '/' + rel_id , function(response, status, xhr) {
			if (status == "error") {
				alert_float('danger', xhr.statusText);
			}
		});
	}
</script>