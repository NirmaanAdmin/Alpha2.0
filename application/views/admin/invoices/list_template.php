<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-12">
    <div class="tw-mb-2 sm:tw-mb-4">
        <div class="_buttons">
            <?php $this->load->view('admin/invoices/invoices_top_stats'); ?>
            <?php if (staff_can('create',  'invoices')) { ?>
                <a href="<?php echo admin_url('invoices/invoice'); ?>"
                    class="btn btn-primary pull-left new new-invoice-list mright5">
                    <i class="fa-regular fa-plus tw-mr-1"></i>
                    <?php echo _l('create_new_invoice'); ?>
                </a>
            <?php } ?>
            <?php if (!isset($project) && !isset($customer) && staff_can('create', 'payments')) { ?>
                <button id="add-batch-payment" onclick="add_batch_payment()" class="btn btn-primary pull-left">
                    <i class="fa-solid fa-file-invoice tw-mr-1"></i>
                    <?php echo _l('batch_payments'); ?>
                </button>
            <?php } ?>
            <?php if (!isset($project)) { ?>
                <a href="<?php echo admin_url('invoices/recurring'); ?>" class="btn btn-default pull-left mleft5">
                    <i class="fa-solid fa-repeat tw-mr-1"></i>
                    <?php echo _l('invoices_list_recurring'); ?>
                </a>
            <?php } ?>
            <div class="display-block pull-right tw-space-x-0 sm:tw-space-x-1.5">
                <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs"
                    onclick="toggle_small_view('.table-invoices','#invoice'); return false;" data-toggle="tooltip"
                    title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i
                        class="fa fa-angle-double-left"></i>
                </a>
                <a href="#" class="btn btn-default btn-with-tooltip invoices-total"
                    onclick="slideToggle('#stats-top'); init_invoices_total(true); return false;" data-toggle="tooltip"
                    title="<?php echo _l('view_stats_tooltip'); ?>">
                    <i class="fa fa-bar-chart"></i>
                </a>

            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12" id="small-table">
            <div class="panel_s">
                <div class="panel-body panel-table-full">
                    <div class="row all_ot_filters">
                        <hr style="margin-top: 0px !important;">
                        <?php
                        $module_name = 'invoices';
                        $invoice_clients_filter = get_module_filter($module_name, 'client');
                        $invoice_clients_filter_val = !empty($invoice_clients_filter) ? explode(",", $invoice_clients_filter->filter_value) : [];
                        ?>
                        <div class="col-md-3 form-group">
                            <label for="client"><?php echo _l('client'); ?></label>
                            <select name="client[]" id="client" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
                                <?php foreach ($clients as $client) { ?>
                                    <option value="<?php echo pur_html_entity_decode($client['userid']); ?>"
                                        <?php if (in_array($client['userid'], $invoice_clients_filter_val)) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo pur_html_entity_decode($client['company']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php
                        $project_filter = get_module_filter($module_name, 'project');
                        $project_filter_val = !empty($project_filter) ? explode(",", $project_filter->filter_value) : [];
                        ?>

                        <div class="col-md-3 form-group">
                            <label for="project"><?php echo _l('project'); ?></label>
                            <select name="project[]" id="project" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
                                <?php foreach ($projects as $project) { ?>
                                    <option value="<?php echo pur_html_entity_decode($project['id']); ?>"
                                        <?php if (in_array($project['id'], $project_filter_val)) {
                                            echo 'selected';
                                        } ?>>
                                        <?php echo pur_html_entity_decode($project['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php
                        $vendor_filter = get_module_filter($module_name, 'status');
                        $vendor_filter_val = !empty($vendor_filter) ? explode(",", $vendor_filter->filter_value) : [];
                        ?>
                        <div class="col-md-3 form-group">
                            <label for="vendor"><?php echo _l('Status'); ?></label>

                            <select name="status[]" id="status" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">

                                <?php foreach (invoice_statuses() as $key => $value) { ?>
                                    <option value="<?php echo $key; ?>" <?php if (in_array($key, $vendor_filter_val)) {
                                                                            echo 'selected';
                                                                        } ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 form-group" id="report-time">
                            <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                            <select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                                <option value="this_month"><?php echo _l('this_month'); ?></option>
                                <option value="1"><?php echo _l('last_month'); ?></option>
                                <option value="this_year"><?php echo _l('this_year'); ?></option>
                                <option value="last_year"><?php echo _l('last_year'); ?></option>
                                <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                                <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                                <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                                <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                            </select>
                        </div>
                        <div id="date-range" class="hide mbot15">
                            <div class="col-md-2 form-group">
                                <label for="report-from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                                <div class="input-group date">
                                    <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar calendar-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="report-to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                                <div class="input-group date">
                                    <input type="text" class="form-control datepicker" disabled="disabled" id="report-to" name="report-to">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar calendar-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $current_year = date('Y');
                        $y0 = (int)$current_year;
                        $y1 = (int)$current_year - 1;
                        $y2 = (int)$current_year - 2;
                        $y3 = (int)$current_year - 3;
                        ?>
                        <div class="form-group hide" id="year_requisition">
                            <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                            <select name="year_requisition" id="year_requisition" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('filter_by') . ' ' . _l('year'); ?>">
                                <option value="<?php echo pur_html_entity_decode($y0); ?>" <?php echo 'selected' ?>><?php echo _l('year') . ' ' . pur_html_entity_decode($y0); ?></option>
                                <option value="<?php echo pur_html_entity_decode($y1); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y1); ?></option>
                                <option value="<?php echo pur_html_entity_decode($y2); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y2); ?></option>
                                <option value="<?php echo pur_html_entity_decode($y3); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y3); ?></option>
                            </select>
                        </div>


                        <div class="col-md-1 form-group pull-right">
                            <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
                                <?php echo _l('reset_filter'); ?>
                            </a>
                        </div>
                    </div>
                    <!-- if invoiceid found in url -->
                    <?php echo form_hidden('invoiceid', $invoiceid); ?>
                    <?php $this->load->view('admin/invoices/table_html'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-7 small-table-right-col">
            <div id="invoice" class="hide"></div>
        </div>
    </div>
</div>