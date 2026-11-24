<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
echo form_hidden('project', $project->id);
echo '<div class="clearfix"></div>';
?>
<div class="panel_s panel-table-full project_dpr">
    <div class="panel-body">
        <?php echo AdminReportsTableStructure('', true); ?>
    </div>
</div>
<?php hooks()->add_action('app_admin_footer', function () { ?>
<script>
$(function() {
    var Params = {
        "project[]": "[name='project']",
    };
    initDataTable('.preports-table', admin_url + 'forms/table_drp_details', [], [], Params, [6, 'desc']);
})
</script>
<?php }) ?>