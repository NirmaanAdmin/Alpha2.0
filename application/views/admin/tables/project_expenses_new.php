<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('payment_modes_model');
// Get custom fields
$custom_fields = get_table_custom_fields('expenses');

// Base columns
$aColumns = [
    db_prefix() . 'expenses.id as id',
    db_prefix() . 'expenses_categories.name as category_name',
    db_prefix() . 'expenses.vendor as vendor',
    'amount',
    'expense_name',
    'file_name',
    'date',
    'invoiceid',
    'reference_no',
    'paymentmode',
];

// Joins
$join = [
    'JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category',
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

array_push($where, 'AND project_id=' . $this->ci->db->escape_str($project_id));

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

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

    $row[] = $aRow['id'];

    $categoryOutput = '';
    $categoryOutput = '<a href="' . admin_url('expenses/list_expenses/' . $aRow[db_prefix() . 'expenses.id']) . '" target="_blank">' . e($aRow['category_name']) . '</a>';
    if ($aRow['billable'] == 1) {
        if ($aRow['invoiceid'] == null) {
            $categoryOutput .= '<p class="text-danger">' . _l('expense_list_unbilled') . '</p>';
        } else {
            if (total_rows(db_prefix() . 'invoices', [
                'id' => $aRow['invoiceid'],
                'status' => 2,
            ]) > 0) {
                $categoryOutput .= '<br /><p class="text-success">' . _l('expense_list_billed') . '</p>';
            } else {
                $categoryOutput .= '<p class="text-success">' . _l('expense_list_invoice') . '</p>';
            }
        }
    }
    $row[] = $categoryOutput;

    $row[] = '<a href="' . admin_url('purchase/vendor/' . $aRow['vendor']) . '">' . e($aRow['vendor_name']) . '</a>';

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

    $row[] = '<a href="' . admin_url('expenses/list_expenses/' . $aRow['id']) . '" onclick="init_expense(' . $aRow['id'] . ');return false;">' . e($aRow['expense_name']) . '</a>';

    $outputReceipt = '';
    if (!empty($aRow['file_name'])) {
        $outputReceipt = '<a href="' . site_url('download/file/expense/' . $aRow['id']) . '">' . e($aRow['file_name']) . '</a>';
    }
    $row[] = $outputReceipt;

    $row[] = date('d M, Y', strtotime($aRow['date']));

    if ($aRow['invoiceid']) {
        $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoiceid']) . '">' . e(format_invoice_number($aRow['invoiceid'])) . '</a>';
    } else {
        $row[] = '';
    }

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

?>