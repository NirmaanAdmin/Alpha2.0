(function($) {
  "use strict";

  	var _project_id = $('input[name="_project_id"]').val();
	initDataTable('.table-table_co_request', admin_url+'changee/table_project_co_request/'+_project_id);

})(jQuery);
