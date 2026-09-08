<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="vueApp">
    <?php include_once(APPPATH . 'views/admin/invoices/invoices_top_stats.php'); ?>
    <div class="panel_s">
        <div class="panel-body">
            <div class="project_invoices">
                <?php include_once(APPPATH.'views/admin/invoices/filter_params.php'); ?>
                <?php $this->load->view('admin/invoices/list_template', [
                    'table'=>$invoices_table,
                    'table_id'=> $invoices_table->id(),
                    'show_filters' => false
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php hooks()->add_action('app_admin_footer', function () { ?>
<script>
$(function() {
    var table_invoices;
    table_invoices = $("table.table-invoices");
    if (table_invoices.length > 0) {
        var Sales_table_ServerParams = {};
        var Sales_table_Filter = $("._hidden_inputs._filters input");
        $.each(Sales_table_Filter, function () {
          Sales_table_ServerParams[$(this).attr("name")] =
            '[name="' + $(this).attr("name") + '"]';
        });
        if (table_invoices.length) {
          initDataTable(
            table_invoices,
            admin_url +
              "invoices/table_new" +
              ($("body").hasClass("recurring") ? "?recurring=1" : ""),
            "undefined",
            "undefined",
            Sales_table_ServerParams,
            !$("body").hasClass("recurring")
              ? [
                  [3, "desc"],
                  [0, "desc"],
                ]
              : [table_invoices.find("th.next-recurring-date").index(), "asc"]
          );
        }
    }
})
</script>
<?php }) ?>