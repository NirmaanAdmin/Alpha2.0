<?php defined('BASEPATH') or exit('No direct script access allowed');
$categories  = $this->ci->cash_flow_model->get_category();
$_categories = [];
$_operations = [
    ['id' => 'cash-in', 'name' => 'cashin'],
    ['id' => 'cash-out', 'name' => 'cashout']
];
$aColumns = [
    '1',
    'buisness_id',
    db_prefix() . 'cf_expenses.id as id',
    'operation',
    'date',
    'expense_name',
    'amount',
    get_sql_select_client_company(),
    db_prefix() . 'cf_expenses.balance as balance',
    'reference_no',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'cf_expenses.clientid',
    'JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'cf_expenses.category',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'cf_expenses.project_id',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'cf_expenses.currency',
];



$custom_fields = get_table_custom_fields('cf_expenses');

$customFieldsColumns = [];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    $customFieldsColumns[] = $selectAs;
    $aColumns[] = 'ctable_' . $key . '.value as ' . $selectAs;
    $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'cf_expenses.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id'];
}

$where = [];
$filter = [];
foreach ($categories as $c) {
    if ($this->ci->input->post('expenses_by_category_' . $c['id'])) {
        array_push($_categories, $c['id']);
    }
}
if (count($_categories) > 0) {
    array_push($filter, 'AND category IN (' . implode(', ', $_categories) . ')');
}

$_months = [];
for ($m = 1; $m <= 12; $m++) {
    if ($this->ci->input->post('expenses_by_month_' . $m)) {
        array_push($_months, $m);
    }
}
if (count($_months) > 0) {
    array_push($filter, 'AND MONTH(date) IN (' . implode(', ', $_months) . ')');
}
$years  = $this->ci->cash_flow_model->get_expenses_years();
$_years = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($_years, $year['year']);
    }
}
if (count($_years) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $_years) . ')');
}
foreach ($_operations as $operation) {
    if ($this->ci->input->post('expenses_by_operation_' . $operation['name'])) {
        array_push($filter, 'AND operation = "'.$operation['id'].'"');
    }
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}


if (!empty($data['buisness_id'])) {
    $where[] = 'AND ' . db_prefix() . 'cf_expenses.buisness_id=' . $this->ci->db->escape_str($data['buisness_id']);
}

if (!empty($clientid)) {
    $where[] = 'AND ' . db_prefix() . 'cf_expenses.clientid=' . $this->ci->db->escape_str($clientid);
}

if (!staff_can('view', 'cash_flow')) {
    $where[] = 'AND ' . db_prefix() . 'cf_expenses.addedfrom=' . get_staff_user_id();
}
$sIndexColumn = 'id';
$sTable = db_prefix() . 'cf_expenses';
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'billable',
    db_prefix() . 'currencies.name as currency_name',
    db_prefix() . 'cf_expenses.clientid',
    'tax',
    'tax2',
    'project_id',
    'recurring',
]);

$output = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->model('payment_modes_model');
foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];
    $categoryOutput = '<a href="' . admin_url('cash_flow/list_cf_expenses/' . $aRow['id']) . '" onclick="init_cf_expense(' . $aRow['id'] . ');return false;">' . $aRow['operation'] . '</a>';
    if ($aRow['recurring'] == 1) {
        $categoryOutput .= '<span class="label label-primary"> ' . _l('expense_recurring_indicator') . '</span>';
    }
    $categoryOutput .= '<div class="row-options">';
    $categoryOutput .= '<a href="' . admin_url('cash_flow/list_cf_expenses/' . $aRow['buisness_id'] . '/' . $aRow['id']) . '" onclick="init_cf_expense(' . $aRow['id'] . ', ' . $aRow['buisness_id'] . ');return false;">' . _l('view') . '</a>';
    if (staff_can('edit', 'cash_flow')) {
        $categoryOutput .= ' | <a href="' . admin_url('cash_flow/expense/' . $aRow['buisness_id'] . '/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    if (staff_can('delete', 'cash_flow')) {
        $categoryOutput .= ' | <a href="' . admin_url('cash_flow/delete/' . $aRow['buisness_id'] . '/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $categoryOutput .= '</div>';
    $row[] = $categoryOutput;
    $row[] = '<a href="' . admin_url('cash_flow/list_cf_expenses/' . $aRow['id']) . '" onclick="init_cf_expense(' . $aRow['id'] . ');return false;">' . ($aRow['expense_name']) . '</a>';
    $row[] = _d($aRow['date']);
    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    $row[] = '#REF0' . $aRow['id'];

    $row[] = $aRow['operation'] == 'cash-in' ? app_format_money($aRow['amount'], $aRow['currency_name']) : 0;
    $row[] = $aRow['operation'] == 'cash-out' ? app_format_money($aRow['amount'], $aRow['currency_name']) : 0;
    $row[] = isset($aRow['balance']) ? app_format_money($aRow['balance'], $aRow['currency_name']) : 0;
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn];
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
