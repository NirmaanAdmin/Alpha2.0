<script type="text/javascript">
	var timer = null;


	$(function(){
		'use strict';

		appValidateForm($("body").find('.post_internal_reply'), {
			'note_title' : 'required',
			'note_details' : 'required',
		});

		appValidateForm($("body").find('.department_transfer'), {
			'comment' : 'required',
			'department_id' : 'required',
		});
		appValidateForm($("body").find('.reassign_ticket'), {
			'comment' : 'required',
			'department_id' : 'required',
		});


	});

	function delete_issue_history(wrapper, id, type) {
		"use strict"; 
		if (confirm_delete()) {
			requestGetJSON('fixed_equipment/delete_issue_history/' + id +'/'+type).done(function(response) {
				if (response.success === true || response.success == 'true') { $(wrapper).parents('.feed-item').remove(); }
			}).fail(function(data) {
				alert_float('danger', data.responseText);
			});
		}
	}

</script>