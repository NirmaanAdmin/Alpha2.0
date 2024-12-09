<script>
	(function(){
		"use strict";
		$('#add_related_file').on('click', function(){
			var file_list_obj = $('select[name="all_file"]');
			var file_id = file_list_obj.val();
			if(file_id != ''){
				var text = $('select[name="all_file"] option:selected').text();
				var html = '<li class="list-group-item list-group-item-action display-flex">';
				html += '<div class="name mtop7 w100">'+text+'</div>';
				html += '<input type="hidden" name="related_file[]" value="'+file_id+'">';
				html += '<button class="btn btn-sm btn-link remove-attachment" onclick="remove_attachment(this,\'file\')" type="button">';
				html += '<i class="fa fa-times"></i>';
				html += '</button>';
				html += '</li>';

				var list_selected = $('input[name="related_file[]"]');
				let i = 0;
				for(i = 0; i < list_selected.length; i++){
					if(list_selected.eq(i).val() == file_id){
						alert_float('warning', '<?php echo _l('dmg_this_file_has_been_selected'); ?>');
						return true;
					}
				}
				var list_obj = $('#related_file_list');
				list_obj.prepend(html);
				list_obj.removeClass('hide');
				file_list_obj.val('').change();
			}
			else{
				alert_float('warning', '<?php echo _l('dmg_please_select_a_file'); ?>');
			}
		});

		$('#add_custom_field').on('click', function(){
			var file_list_obj = $('select[name="all_custom_field"]');
			var field_id = file_list_obj.val();
			if(field_id != ''){
				var item = 'field-item-'+field_id;
				var html = '<li class="list-group-item list-group-item-action display-flex '+item+'">';
				html += '<div class="control w100"></div>';
				html += '<input type="hidden" name="field_id[]" value="'+field_id+'">';
				html += '<button class="btn btn-sm btn-link remove-attachment" onclick="remove_attachment(this,\'customfield\')" type="button">';
				html += '<i class="fa fa-times"></i>';
				html += '</button>';
				html += '</li>';

				var list_selected = $('input[name="field_id[]"]');
				let i = 0;
				for(i = 0; i < list_selected.length; i++){
					if(list_selected.eq(i).val() == field_id){
						alert_float('warning', '<?php echo _l('dmg_this_field_has_been_selected'); ?>');
						return true;
					}
				}

				var list_obj = $('#custom_field_list');
				list_obj.prepend(html);
				list_obj.removeClass('hide');
				file_list_obj.val('').change();
				// Get control custom field ajax
				var requestURL = (typeof(url) != 'undefined' ? url : 'drawing_management/drawing_management_client/get_custom_field/') + (typeof(field_id) != 'undefined' ? field_id : '');
				ajaxGetJSON(requestURL).done(function(response) {
					$('.'+item+' .control').html(response);
					init_selectpicker();
				}).fail(function(data) {
					alert_float('danger', '<?php echo _l('dmg_an_error_has_occurred'); ?>');
				});	
			}
			else{
				alert_float('warning', '<?php echo _l('dmg_please_select_a_field'); ?>');
			}
		});
	})(jQuery);

	function remove_attachment(el, type){
		if(type == 'file'){
			$(el).closest('li').remove();
			var list_obj = $('#related_file_list');
			if(list_obj.find('li').length == 0){
				list_obj.addClass('hide');
			}
		}
		if(type == 'customfield'){
			$(el).closest('li').remove();
			var list_obj = $('#custom_field_list');
			if(list_obj.find('li').length == 0){
				list_obj.addClass('hide');
			}
		}
	}

</script>