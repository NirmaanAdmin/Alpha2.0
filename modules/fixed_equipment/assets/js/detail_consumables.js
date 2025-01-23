(function(){
	"use strict";
	var fnServerParams = {
		"id": "[name='parent_id']",
	}
	initDataTable('.table-detail_consumables', admin_url + 'fixed_equipment/detail_consumables_table', false, false, fnServerParams, [0, 'desc']);


})(jQuery);
