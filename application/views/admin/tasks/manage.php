<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'tasks'; ?>
<div id="wrapper">
    <div class="content">
        <div class="row _buttons tw-mb-2 sm:tw-mb-4">
            <div class="col-md-8">
                <?php if (staff_can('create',  'tasks')) { ?>
                <a href="#" onclick="new_task(<?php if ($this->input->get('project_id')) {
    echo "'" . admin_url('tasks/task?rel_id=' . $this->input->get('project_id') . '&rel_type=project') . "'";
} ?>); return false;" class="btn btn-primary pull-left new">
                    <i class="fa-regular fa-plus tw-mr-1"></i>
                    <?php echo _l('new_task'); ?>
                </a>
                <?php } ?>
                <a 
                    href="<?php echo admin_url(!$this->input->get('project_id') ? ('tasks/switch_kanban/' . $switch_kanban) : ('projects/view/' . $this->input->get('project_id') . '?group=project_tasks')); ?>" class="btn btn-default mleft10 pull-left hidden-xs" data-toggle="tooltip" 
                    data-placement="top"
                    data-title="<?php echo $switch_kanban == 1 ? _l('switch_to_list_view') : _l('leads_switch_to_kanban'); ?>"
                >
                    <?php if ($switch_kanban == 1) { ?>
                    <i class="fa-solid fa-table-list"></i>
                    <?php } else { ?>
                    <i class="fa-solid fa-grip-vertical"></i>
                    <?php }; ?>
                </a>
            </div>
            <div class="col-md-4">
                <?php if ($this->session->has_userdata('tasks_kanban_view') && $this->session->userdata('tasks_kanban_view') == 'true') { ?>
                <div data-toggle="tooltip" data-placement="top" data-title="<?php echo _l('search_by_tags'); ?>">
                    <?php echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'tasks_kanban();', 'placeholder' => _l('search_tasks')], [], 'no-margin') ?>
                </div>
                <?php } else { ?>
                <a href="<?php echo admin_url('tasks/detailed_overview'); ?>"
                    class="btn btn-success pull-right mright5"><?php echo _l('detailed_overview'); ?></a>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                  if ($this->session->has_userdata('tasks_kanban_view') && $this->session->userdata('tasks_kanban_view') == 'true') { ?>
                <div class="kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                    <div class="row">
                        <div id="kanban-params">
                            <?php echo form_hidden('project_id', $this->input->get('project_id')); ?>
                        </div>
                        <div class="container-fluid">
                            <div id="kan-ban"></div>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                <div class="row all_filters">
                    <div class="col-md-2 form-group">
                        <?php
                        $task_status_type_filter = get_module_filter($module_name, 'task_status');
                        $task_status_type_filter_val = !empty($task_status_type_filter) ? explode(",", $task_status_type_filter->filter_value) : '';
                        echo render_select('task_status[]', $task_statuses, array('id', 'name'), '', $task_status_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task status'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                        ?>
                    </div>
                    <div class="col-md-2 form-group">
                        <?php
                        $task_assignees_type_filter = get_module_filter($module_name, 'task_assignees');
                        $task_assignees_type_filter_val = !empty($task_assignees_type_filter) ? explode(",", $task_assignees_type_filter->filter_value) : '';
                        echo render_select('task_assignees[]', $staff, array('staffid', ['firstname','lastname']), '', $task_assignees_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task Assignees'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                        ?>
                    </div>
                    <div class="col-md-2 form-group">
                        <?php
                        $task_priority_type_filter = get_module_filter($module_name, 'task_priority');
                        $task_priority_type_filter_val = !empty($task_priority_type_filter) ? explode(",", $task_priority_type_filter->filter_value) : '';
                        echo render_select('task_priority[]', get_tasks_priorities(), array('id', 'name'), '', $task_priority_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('Task Priority'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                        ?>
                    </div>
                    <div class="col-md-1 form-group">
                        <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_filters">
                            <?php echo _l('reset_filter'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $this->load->view('admin/tasks/_summary', ['table' => '.table-tasks']); ?>
                        <a href="#" data-toggle="modal" data-target="#tasks_bulk_actions"
                            class="hide bulk-actions-btn table-btn"
                            data-table=".table-tasks"><?php echo _l('bulk_actions'); ?></a>
                        <div class="panel-table-full show_filters">
                            <?php $this->load->view('admin/tasks/_table', ['bulk_actions' => true]); ?>
                        </div>
                        <?php $this->load->view('admin/tasks/_bulk_actions'); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table_rec_task;
    (function($) {
        table_rec_task = $('.table-tasks');
        var Params = {
            "task_status": "[name='task_status[]']",
            "task_assignees": "[name='task_assignees[]']",
            "task_priority": "[name='task_priority[]']",
        };
        initDataTable('.table-tasks', admin_url + 'tasks/table_tasks_details', [], [], Params, [5, 'asc']);
        $.each(Params, function(i, obj) {
            $('select' + obj).on('change', function() {
                table_rec_task.DataTable().ajax.reload();
            });
        });
        $(document).on('click', '.reset_all_filters', function() {
            var filterArea = $('.all_filters');
            filterArea.find('input').val("");
            filterArea.find('select').selectpicker("val", "");
            table_rec_task.DataTable().ajax.reload();
        });
    })(jQuery);
</script>
<script>
taskid = '<?php echo e($taskid); ?>';
$(function() {
    tasks_kanban();
});
</script>
</body>

</html>