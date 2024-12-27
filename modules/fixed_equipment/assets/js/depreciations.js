   (function(){
    "use strict";
    var fnServerParams = {
      "asset": "[name='asset_id[]']",
      "month": "[name='month_filter']",
      "status": "[name='status_filter']"
    }
    initDataTable('.table-assets_management', admin_url + 'fixed_equipment/depreciation_table', false, false, fnServerParams, [0, 'desc']);
    $( "select[name='asset_id[]'], select[name='status_filter'], input[name='month_filter']" ).change(function() {
      $('.table-assets_management').DataTable().ajax.reload()
      .columns.adjust();
    });
  })(jQuery);

