<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('expenses_model');
$CI->load->model('payment_modes_model');
$CI->load->model('purchase/purchase_model');
$module_name = 'expenses';

$expense_category_name = 'expense_category';
$payment_mode_name = 'payment_mode';
$vendor_name = 'vendor';
// Get custom fields
$custom_fields = get_table_custom_fields('expenses');

// Base columns
$aColumns = [
    '1', // bulk actions
    db_prefix() . 'expenses.id as id',
    db_prefix() . 'expenses_categories.name as category_name',
    db_prefix() . 'expenses.vendor as vendor',
    'amount',
    'expense_name',
    'file_name',
    'date',
    db_prefix() . 'projects.name as project_name',
    get_sql_select_client_company(),
    'invoiceid',
    'reference_no',
    'paymentmode',
];

// Joins
$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'expenses.clientid',
    'JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'expenses.project_id',
    'LEFT JOIN ' . db_prefix() . 'files ON ' . db_prefix() . 'files.rel_id = ' . db_prefix() . 'expenses.id AND rel_type="expense"',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'expenses.currency',
    'LEFT JOIN ' . db_prefix() . 'pur_vendor ON ' . db_prefix() . 'pur_vendor.userid = ' . db_prefix() . 'expenses.vendor',
];

// Custom fields
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'expenses.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];

// -- Filters --
if ($CI->input->post('filters')) {
    $filters = $CI->input->post('filters');
    if (isset($filters['rules']) && is_array($filters['rules'])) {
        $where[] = $this->getWhereFromRules($filters['rules']);
    }
}


if ($this->ci->input->post('expense_category') && count($this->ci->input->post('expense_category')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'expenses.category IN (' . implode(',', $this->ci->input->post('expense_category')) . ')');
}

if ($this->ci->input->post('payment_mode') && count($this->ci->input->post('payment_mode')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'expenses.paymentmode IN (' . implode(',', $this->ci->input->post('payment_mode')) . ')');
}

if ($this->ci->input->post('vendor') && count($this->ci->input->post('vendor')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'expenses.vendor IN (' . implode(',', $this->ci->input->post('vendor')) . ')');
}

$custom_date_select = $this->ci->purchase_model->get_where_report_period('' . db_prefix() . 'expenses.date');
if ($custom_date_select != '') {
    $custom_date_select = trim($custom_date_select);
    if (!startsWith($custom_date_select, 'AND')) {
        $custom_date_select = 'AND ' . $custom_date_select;
    }
    array_push($where, $custom_date_select);
}
// Staff permissions
if (staff_cant('view', 'expenses')) {
    $where[] = 'AND ' . db_prefix() . 'expenses.addedfrom = ' . get_staff_user_id();
}

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}
$expense_category_name_value = !empty($this->ci->input->post('expense_category')) ? implode(',', $this->ci->input->post('expense_category')) : NULL;
update_module_filter($module_name, $expense_category_name, $expense_category_name_value);

$payment_mode_name_value = !empty($this->ci->input->post('payment_mode')) ? implode(',', $this->ci->input->post('payment_mode')) : NULL;
update_module_filter($module_name, $payment_mode_name, $payment_mode_name_value);

$vendor_name_value = !empty($this->ci->input->post('vendor')) ? implode(',', $this->ci->input->post('vendor')) : NULL;
update_module_filter($module_name, $vendor_name, $vendor_name_value);
$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'expenses',
    $join,
    $where,
    [
        'billable',
        db_prefix() . 'currencies.name as currency_name',
        db_prefix() . 'expenses.clientid',
        'tax',
        'tax2',
        'project_id',
        'recurring',
        db_prefix() . 'pur_vendor.company as vendor_name',
    ]
);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    // ID
    $row[] = $aRow['id'];

    // Category with row options
    $categoryOutput = '';

    if (is_numeric($CI->input->post('clientid'))) {
        $categoryOutput = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '">' . e($aRow['category_name']) . '</a>';
    } else {
        $categoryOutput = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" onclick="init_expense(' . $aRow['id'] . ');return false;">' . e($aRow['category_name']) . '</a>';
    }

    if ($aRow['billable'] == 1) {
        if ($aRow['invoiceid'] == null) {
            $categoryOutput .= ' <p class="text-danger tw-text-sm tw-mb-1">' . _l('expense_list_unbilled') . '</p>';
        } else {
            if (total_rows(db_prefix() . 'invoices', [
                'id' => $aRow['invoiceid'],
                'status' => 2,
            ]) > 0) {
                $categoryOutput .= ' <p class="text-success tw-text-sm tw-mb-1">' . _l('expense_list_billed') . '</p>';
            } else {
                $categoryOutput .= ' <p class="text-success tw-text-sm tw-mb-1">' . _l('expense_list_invoice') . '</p>';
            }
        }
    }

    if ($aRow['recurring'] == 1) {
        $categoryOutput .= '<span class="label label-primary"> ' . _l('expense_recurring_indicator') . '</span>';
    }

    $categoryOutput .= '<div class="row-options">';
    $categoryOutput .= '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" onclick="init_expense(' . $aRow['id'] . ');return false;">' . _l('view') . '</a>';

    if (staff_can('edit', 'expenses')) {
        $categoryOutput .= ' | <a href="' . admin_url('expenses/expense/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    if (staff_can('delete', 'expenses')) {
        $categoryOutput .= ' | <a href="' . admin_url('expenses/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $categoryOutput .= '</div>';
    $row[] = $categoryOutput;
    $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '">' . e($aRow['vendor_name']) . '</a>';
    // Amount with tax calculation
    $total = $aRow['amount'];
    $tmpTotal = $total;

    if ($aRow['tax'] != 0) {
        $tax = get_tax_by_id($aRow['tax']);
        $total += ($total / 100 * $tax->taxrate);
    }
    if ($aRow['tax2'] != 0) {
        $tax = get_tax_by_id($aRow['tax2']);
        $total += ($tmpTotal / 100 * $tax->taxrate);
    }

    $row[] = e(app_format_money($total, $aRow['currency_name']));

    // Expense name
    $row[] = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" onclick="init_expense(' . $aRow['id'] . ');return false;">' . e($aRow['expense_name']) . '</a>';

    // File name / receipt
    $outputReceipt = '';
    if (!empty($aRow['file_name'])) {
        $outputReceipt = '<a href="' . site_url('download/file/expense/' . $aRow['id']) . '">' . e($aRow['file_name']) . '</a>';
    }
    $row[] = $outputReceipt;

    // Date
    $row[] = date('d M, Y', strtotime($aRow['date']));

    // Project
    $row[] = '<a href="' . admin_url('projects/view/' . $aRow['project_id']) . '">' . e($aRow['project_name']) . '</a>';

    // Client company
    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';

    // Invoice
    if ($aRow['invoiceid']) {
        $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . e(format_invoice_number($aRow['invoiceid'])) . '</a>';
    } else {
        $row[] = '';
    }

    // Reference no
    $row[] = e($aRow['reference_no']);

    // Payment mode
    $paymentModeOutput = '';
    if ($aRow['paymentmode'] != '0' && !empty($aRow['paymentmode'])) {
        $payment_mode = $CI->payment_modes_model->get($aRow['paymentmode'], [], false, true);
        if ($payment_mode) {
            $paymentModeOutput = e($payment_mode->name);
        }
    }
    $row[] = $paymentModeOutput;

    // Custom fields
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('expenses_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
