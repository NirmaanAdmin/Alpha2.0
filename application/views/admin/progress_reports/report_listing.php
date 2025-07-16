<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'dpr_module';
?>

<div id="wrapper">
    <div class="content">
        <div id="vueApp">
            <div class="row">
                <div class="col-md-12">
                    <div class="_buttons tw-mb-2 sm:tw-mb-4">
                        <div class="col-md-4">
                            <a href="<?php echo admin_url('forms/add_dpr'); ?>"
                                class="btn btn-primary pull-left display-block mright5">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?php
                                if ($module == "dpr") {
                                    echo _l('daily_progress_report');
                                } else if ($module == "wpr") {
                                    echo _l('weekly_progress_report');
                                } else if ($module == "mpr") {
                                    echo _l('monthly_progress_report');
                                } else {
                                    echo _l('new_form');
                                }
                                ?>
                            </a>

                            <?php if ($module == "dpr") { ?>
                                <a href="<?php echo admin_url('forms/dpr_dashboard'); ?>" class="btn btn-primary">
                                    <?php echo _l('dashboard_string'); ?>
                                </a>
                            <?php }

                            ?>


                        </div>

                        <div class="row all_ot_filters">
                            <div class="col-md-1 form-group pull-right">
                                <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
                                    <?php echo _l('reset_filter'); ?>
                                </a>
                            </div>
                            <div class="col-md-2 form-group pull-right">
                                <?php
                                $project_type_filter = get_module_filter($module_name, 'project_id');
                                $project_type_filter_val = !empty($project_type_filter) ? explode(",", $project_type_filter->filter_value) : '';
                                echo render_select('project[]', $projects, array('id', 'name'), '', $project_type_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('projects'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                ?>
                            </div>
                        </div>
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="weekly-form-opening no-shadow tw-mb-10" style="display:none;">
                                    <h4 class="tw-font-semibold tw-mb-8 tw-flex tw-items-center tw-text-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor"
                                            class="tw-w-5 tw-h-5 tw-mr-1.5 tw-text-neutral-500">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>

                                        <?php echo _l('home_weekend_form_opening_statistics'); ?>
                                    </h4>
                                    <div class="relative" style="max-height:350px;">
                                        <canvas class="chart" id="weekly-form-openings-chart" height="350"></canvas>
                                    </div>
                                </div>

                                <?php hooks()->do_action('before_render_forms_list_table'); ?>
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg><span> Form Summary </span></h4>
                                <hr class="hr-panel-separator" />
                                <a href="#" data-toggle="modal" data-target="#forms_bulk_actions"
                                    class="bulk-actions-btn table-btn hide"
                                    data-table=".table-forms"><?php echo _l('bulk_actions'); ?></a>
                                <div class="clearfix"></div>
                                <div class="panel-table-full">
                                    <?php echo AdminReportsTableStructure('', true); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade bulk_actions" id="forms_bulk_actions" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                </div>
                <div class="modal-body">
                    <!-- <div class="checkbox checkbox-primary merge_forms_checkbox">
                    <input type="checkbox" name="merge_forms" id="merge_forms">
                    <label for="merge_forms"><?php echo _l('merge_forms'); ?></label>
                </div> -->
                    <?php if (can_staff_delete_form()) { ?>
                        <div class="checkbox checkbox-danger mass_delete_checkbox">
                            <input type="checkbox" name="mass_delete" id="mass_delete">
                            <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                        </div>
                        <hr class="mass_delete_separator" />
                    <?php } ?>
                    <div id="bulk_change">
                        <!-- <?php echo render_select('move_to_status_forms_bulk', $statuses, ['formstatusid', 'name'], 'form_single_change_status'); ?> -->
                        <?php echo render_select('move_to_department_forms_bulk', $departments, ['departmentid', 'name'], 'department'); ?>
                        <?php echo render_select('move_to_priority_forms_bulk', $priorities, ['priorityid', 'name'], 'form_priority'); ?>
                        <div class="form-group">
                            <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                            <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value=""
                                data-role="tagsinput">
                        </div>
                        <?php if (get_option('services') == 1) { ?>
                            <!-- <?php echo render_select('move_to_service_forms_bulk', $services, ['serviceid', 'name'], 'service'); ?> -->
                        <?php } ?>
                    </div>
                    <div id="merge_forms_wrapper">
                        <div class="form-group">
                            <label for="primary_form_id">
                                <span class="text-danger">*</span> <?php echo _l('primary_form'); ?>
                            </label>
                            <select id="primary_form_id" class="selectpicker" name="primary_form_id" data-width="100%"
                                data-live-search="true"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" required>
                                <option></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="primary_form_status">
                                <span class="text-danger">*</span> <?php echo _l('primary_form_status'); ?>
                            </label>
                            <select id="primary_form_status" class="selectpicker" name="primary_form_status"
                                data-width="100%" data-live-search="true"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" required>
                                <?php foreach ($statuses as $status) { ?>
                                    <option value="<?php echo e($status['formstatusid']); ?>"><?php echo e($status['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <a href="#" class="btn btn-primary"
                        onclick="forms_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php init_tail(); ?>
    <script>
        var table_rec_prereport;
        (function($) {
            table_rec_prereport = $('.preports-table');

            var Params = {
                "project_id": "[name='project[]']",
            };

            initDataTable('.preports-table', admin_url + 'forms/table_drp_details', [], [], Params, [6, 'desc']);


            $.each(Params, function(i, obj) {
                $('select' + obj).on('change', function() {
                    table_rec_prereport.DataTable().ajax.reload()
                        .columns.adjust()
                        .responsive.recalc();
                });
            });

            $(document).on('click', '.reset_all_ot_filters', function() {
                var filterArea = $('.all_ot_filters');
                filterArea.find('input').val("");
                filterArea.find('select').selectpicker("val", "");
                table_rec_prereport.DataTable().ajax.reload().columns.adjust().responsive.recalc();
            });

        })(jQuery);
    </script>
    <script>
        var chart;
        var chart_data = <?php echo $weekly_forms_opening_statistics; ?>;

        function init_forms_weekly_chart() {
            if (typeof(chart) !== 'undefined') {
                chart.destroy();
            }
            // Weekly form openings statistics
            chart = new Chart($('#weekly-form-openings-chart'), {
                type: 'line',
                data: chart_data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                            }
                        }]
                    }
                }
            });
        }
    </script>
    </body>

    </html>