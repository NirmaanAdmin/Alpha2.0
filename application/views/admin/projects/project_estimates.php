<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="vueApp">
    <?php include_once(APPPATH . 'views/admin/estimates/estimates_top_stats.php'); ?>
    <div class="panel_s panel-table-full ">
        <div class="panel-body">
            <div class="project_estimates">
                <?php $this->load->view('admin/estimates/list_template', [
                    'table'=>$estimates_table,
                    'table_id'=> $estimates_table->id(),
                    'show_filters' => false
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php hooks()->add_action('app_admin_footer', function () { ?>
<script>
$(function() {
    var table_estimates;
    table_estimates = $("table.table-estimates");
    if (table_estimates.length > 0) {
        var Sales_table_ServerParams = {};
        var Sales_table_Filter = $("._hidden_inputs._filters input");
        $.each(Sales_table_Filter, function () {
          Sales_table_ServerParams[$(this).attr("name")] =
            '[name="' + $(this).attr("name") + '"]';
        });
        if (table_estimates.length) {
          initDataTable(
            table_estimates,
            admin_url + "estimates/table_new",
            "undefined",
            "undefined",
            Sales_table_ServerParams,
            [
              [3, "desc"],
              [0, "desc"],
            ]
          );
        }
    }
})
</script>
<?php }) ?>