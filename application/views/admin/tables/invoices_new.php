<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('invoices_model');
$CI->load->model('purchase/purchase_model');

$module_name = 'invoices';
$client_name = 'client';
$project_name = 'project';
$status_name = 'status';

// Get custom fields
$custom_fields = get_table_custom_fields('invoice');
$customFieldsColumns = [];

// Base columns
$aColumns = [
    db_prefix() . 'invoices.id as id',
    'number',
    'total',
    'total_left_to_pay',
    'YEAR(date) as year',
    get_sql_select_client_company(),
    db_prefix() . 'projects.name as project_name',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables
      JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id
      WHERE rel_id = ' . db_prefix() . 'invoices.id and rel_type="invoice"
      ORDER by tag_order ASC) as tags',
    'date',
    'duedate',
    db_prefix() . 'invoices.status',
];

// Joins
$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'invoices.project_id',
];

// Custom fields
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key .
        ' ON ' . db_prefix() . 'invoices.id = ctable_' . $key . '.relid
        AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '"
        AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];

// -- Filters --
if ($CI->input->post('filters')) {
    $filters = $CI->input->post('filters');
    if (isset($filters['rules']) && is_array($filters['rules'])) {
        $where[] = $this->getWhereFromRules($filters['rules']);
    }
}

if ($CI->input->post('client') && count($CI->input->post('client')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid IN (' . implode(',', $CI->input->post('client')) . ')');
}

if ($CI->input->post('project') && count($CI->input->post('project')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.project_id IN (' . implode(',', $CI->input->post('project')) . ')');
}

if ($CI->input->post('status') && count($CI->input->post('status')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(',', $CI->input->post('status')) . ')');
}

// Date range filter
$custom_date_select = $CI->purchase_model->get_where_report_period('' . db_prefix() . 'invoices.date');
if ($custom_date_select != '') {
    $custom_date_select = trim($custom_date_select);
    if (!startsWith($custom_date_select, 'AND')) {
        $custom_date_select = 'AND ' . $custom_date_select;
    }
    array_push($where, $custom_date_select);
}

// Staff permission
if (staff_cant('view', 'invoices')) {
    $userWhere = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

// Update module filters
$client_name_value = !empty($CI->input->post('client')) ? implode(',', $CI->input->post('client')) : NULL;
update_module_filter($module_name, $client_name, $client_name_value);

$project_name_value = !empty($CI->input->post('project')) ? implode(',', $CI->input->post('project')) : NULL;
update_module_filter($module_name, $project_name, $project_name_value);

$status_name_value = !empty($CI->input->post('status')) ? implode(',', $CI->input->post('status')) : NULL;
update_module_filter($module_name, $status_name, $status_name_value);

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'invoices',
    $join,
    $where,
    [
        db_prefix() . 'invoices.id',
        db_prefix() . 'invoices.clientid',
        db_prefix() . 'currencies.name as currency_name',
        'project_id',
        'hash',
        'recurring',
        'deleted_customer_name',
        '(
            SELECT ROUND(
                inv.total
                - IFNULL((SELECT SUM(p.amount) FROM ' . db_prefix() . 'invoicepaymentrecords p WHERE p.invoiceid = inv.id), 0)
                - IFNULL((SELECT SUM(c.amount) FROM ' . db_prefix() . 'credits c WHERE c.invoice_id = inv.id), 0),
            2)
            FROM ' . db_prefix() . 'invoices inv
            WHERE inv.id = ' . db_prefix() . 'invoices.id
        ) AS total_left_to_pay',
    ]
);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Invoice Number with actions
    $numberOutput = '';
    $project_id = $CI->input->post('project_id'); // for condition

    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' .
            e(format_invoice_number($aRow['id'])) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '"
                         onclick="init_invoice(' . $aRow['id'] . '); return false;">' .
            e(format_invoice_number($aRow['id'])) . '</a>';
    }

    if ($aRow['recurring'] > 0) {
        $numberOutput .= '<br /><span class="label label-primary inline-block tw-mt-1"> ' . _l('invoice_recurring_indicator') . '</span>';
    }

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (staff_can('edit', 'invoices')) {
        $numberOutput .= ' | <a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if (staff_can('delete', 'invoices')) {
        $numberOutput .= ' | <a href="' . admin_url('invoices/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    // Total Amount
    $row[] = e(app_format_money($aRow['total'], $aRow['currency_name']));

    // Total Left to Pay
    $total_left_to_pay = get_invoice_total_left_to_pay($aRow['id'], $aRow['total']);
    $row[] = e(app_format_money($total_left_to_pay, $aRow['currency_name']));

    

    // Year
    $row[] = $aRow['year'];
    // Date
    $row[] = e(_d($aRow['date']));

    // Client
    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';
    } else {
        $row[] = e($aRow['deleted_customer_name']);
    }

    // Project
    $row[] = '<a href="' . admin_url('projects/view/' . $aRow['project_id']) . '">' . e($aRow['project_name']) . '</a>';

    // Tags
    $row[] = render_tags($aRow['tags']);

    

    // Due Date
    $row[] = e(_d($aRow['duedate']));

    // Status
    $row[] = format_invoice_status($aRow[db_prefix() . 'invoices.status']);

    // Custom Fields
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';
    $row = hooks()->apply_filters('invoices_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}