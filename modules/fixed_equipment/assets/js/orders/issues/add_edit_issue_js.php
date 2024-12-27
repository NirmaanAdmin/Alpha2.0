<script type="text/javascript">
	$(function(){
		'use strict';

		appValidateForm($("body").find('#add_ticket'), {
			'client_id': 'required',
			'asset_id': 'required',
			'issue_summary': 'required',
			'assigned_id': 'required',
			'ticket_subject': 'required',
			'datecreated': 'required',
		}, productSubmitHandler); 


	});
	Dropzone.options.expenseForm = false;
	Dropzone.autoDiscover = false;
	var issueDropzone;

	if ($('#dropzoneDragArea').length > 0) {
		issueDropzone = new Dropzone("#add_ticket", appCreateDropzoneOptions({
			autoProcessQueue: false,
			clickable: '#dropzoneDragArea',
			previewsContainer: '.dropzone-previews',
			addRemoveLinks: true,
			maxFiles: 10,
			success: function(file, response) {
				response = JSON.parse(response);
				if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length ===
					0) {
					window.location.assign(response.url);
			}else{
				issueDropzone.processQueue();
			}
		},
	}));
	}

	function productSubmitHandler(form) {
		'use strict';	
		var data={};
		data.formdata = $( form ).serializeArray();

		$('#box-loading').show();
		$('.submit_button').attr( "disabled", "disabled" );

		$.post(form.action, data).done(function(response1) {
			var response = JSON.parse(response1);
			if (response.issueid) {
				if(typeof(issueDropzone) !== 'undefined' ){
					if (issueDropzone.getQueuedFiles().length > 0 ) {

						issueDropzone.options.url = admin_url + 'fixed_equipment/add_issue_attachment/' + response.issueid;

						issueDropzone.processQueue();

					} else {
						window.location.assign(response.url);
					}
				} else {
					window.location.assign(response.url);
				}
			} else {
				window.location.assign(response.url);
			}
		});
		return false;
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