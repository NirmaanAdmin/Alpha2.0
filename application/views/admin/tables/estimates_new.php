<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('estimates_model');
$CI->load->model('purchase/purchase_model');
$module_name = 'estimates';
$client_name = 'client';
$project_name = 'project';
$status_name = 'status';
// Get custom fields
$custom_fields = get_table_custom_fields('estimate');
$customFieldsColumns = [];

// Base columns
$aColumns = [
    db_prefix() . 'estimates.id as id',
    'number',
    'total',
    'total_tax',
    'YEAR(date) as year',
    get_sql_select_client_company(),
    db_prefix() . 'projects.name as project_name',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables 
      JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id 
      WHERE rel_id = ' . db_prefix() . 'estimates.id and rel_type="estimate" 
      ORDER by tag_order ASC) as tags',
    'date',
    'expirydate',
    'reference_no',
    db_prefix() . 'estimates.status',
];

// Joins
$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'estimates.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'estimates.currency',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'estimates.project_id',
];

// Custom fields
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key .
        ' ON ' . db_prefix() . 'estimates.id = ctable_' . $key . '.relid 
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

if ($this->ci->input->post('client') && count($this->ci->input->post('client')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'estimates.clientid IN (' . implode(',', $this->ci->input->post('client')) . ')');
}

if ($this->ci->input->post('project') && count($this->ci->input->post('project')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'estimates.project_id IN (' . implode(',', $this->ci->input->post('project')) . ')');
}

if ($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'estimates.status IN (' . implode(',', $this->ci->input->post('status')) . ')');
}

$custom_date_select = $this->ci->purchase_model->get_where_report_period('' . db_prefix() . 'estimates.date');
if ($custom_date_select != '') {
    $custom_date_select = trim($custom_date_select);
    if (!startsWith($custom_date_select, 'AND')) {
        $custom_date_select = 'AND ' . $custom_date_select;
    }
    array_push($where, $custom_date_select);
}

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

$client_name_value = !empty($this->ci->input->post('client')) ? implode(',', $this->ci->input->post('client')) : NULL;
update_module_filter($module_name, $client_name, $client_name_value);

$project_name_value = !empty($this->ci->input->post('project')) ? implode(',', $this->ci->input->post('project')) : NULL;
update_module_filter($module_name, $project_name, $project_name_value);

$status_name_value = !empty($this->ci->input->post('status')) ? implode(',', $this->ci->input->post('status')) : NULL;
update_module_filter($module_name, $status_name, $status_name_value);

// Additional data to select
$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'estimates',
    $join,
    $where,
    [
        db_prefix() . 'estimates.id',
        db_prefix() . 'estimates.clientid',
        db_prefix() . 'estimates.invoiceid',
        db_prefix() . 'currencies.name as currency_name',
        'project_id',
        'deleted_customer_name',
        'hash',
    ]
);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];



    // Estimate Number with actions
    $numberOutput = '';
    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" target="_blank">' .
            e(format_estimate_number($aRow['id'])) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('estimates/list_estimates/' . $aRow['id']) . '" 
                         onclick="init_estimate(' . $aRow['id'] . '); return false;">' .
            e(format_estimate_number($aRow['id'])) . '</a>';
    }

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . site_url('estimate/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';

    if (staff_can('edit', 'estimates')) {
        $numberOutput .= ' | <a href="' . admin_url('estimates/estimate/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if (staff_can('delete', 'estimates')) {
        $numberOutput .= ' | <a href="' . admin_url('estimates/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    // Total Amount
    $amount = e(app_format_money($aRow['total'], $aRow['currency_name']));
    if ($aRow['invoiceid']) {
        $amount .= '<br /><span class="text-success tw-text-sm">' . _l('estimate_invoiced') . '</span>';
    }
    $row[] = $amount;

    // Total Tax
    $row[] = e(app_format_money($aRow['total_tax'], $aRow['currency_name']));

    // Year
    $row[] = $aRow['year'];

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

    // Date
    $row[] = e(_d($aRow['date']));

    // Expiry Date
    $row[] = e(_d($aRow['expirydate']));

    // Reference No
    $row[] = e($aRow['reference_no']);

    // Status
    $row[] = format_estimate_status($aRow[db_prefix() . 'estimates.status']);

    // Custom Fields
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';
    $row = hooks()->apply_filters('estimates_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
