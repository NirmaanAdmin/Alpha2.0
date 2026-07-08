<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row all_ot_filters">

    <?php
    $module_name = 'inv_payments';
    $estimate_clients_filter = get_module_filter($module_name, 'client');
    $estimate_clients_filter_val = !empty($estimate_clients_filter) ? explode(",", $estimate_clients_filter->filter_value) : [];
    ?>
    <div class="col-md-3 form-group">
        <label for="client"><?php echo _l('client'); ?></label>
        <select name="client[]" id="client" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
            <?php foreach ($inv_clients as $clients) { ?>
                <option value="<?php echo pur_html_entity_decode($clients['userid']); ?>"
                    <?php if (in_array($clients['userid'], $estimate_clients_filter_val)) {
                        echo 'selected';
                    } ?>>
                    <?php echo pur_html_entity_decode($clients['company']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <?php
    $payment_mode_filter = get_module_filter($module_name, 'payment_mode');
    $payment_mode_filter_val = !empty($payment_mode_filter) ? explode(",", $payment_mode_filter->filter_value) : [];
    ?>

    <div class="col-md-3 form-group">
        <label for="payment_mode"><?php echo _l('payment_mode'); ?></label>
        <select name="payment_mode[]" id="payment_mode" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
            <?php foreach ($payment_modes as $mode) { ?>
                <option value="<?php echo pur_html_entity_decode($mode['id']); ?>"
                    <?php if (in_array($mode['id'], $payment_mode_filter_val)) {
                        echo 'selected';
                    } ?>>
                    <?php echo pur_html_entity_decode($mode['name']); ?>
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


    <div class="col-md-1 form-group" style="margin-top: 23px;">
        <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
            <?php echo _l('reset_filter'); ?>
        </a>
    </div>
</div>
<?php render_datatable([
    _l('payments_table_number_heading'),
    _l('payments_table_invoicenumber_heading'),
    _l('payments_table_mode_heading'),
    _l('payment_transaction_id'),
    [
        'name'     => _l('payments_table_client_heading'),
        'th_attrs' => ['class' => (isset($client) ? 'not_visible' : '')],
    ],
    _l('payments_table_amount_heading'),
    _l('payments_table_date_heading'),
], (isset($class) ? $class : 'payments'), [], [
    'data-last-order-identifier' => 'payments',
    'data-default-order'         => get_table_last_order('payments'),
]);
