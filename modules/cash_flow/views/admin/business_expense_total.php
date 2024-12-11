<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'buisness_name',
    'amount',
    'operation',
    'balance',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'cf_expenses';
$where = [];
$join = [
    'LEFT JOIN ' . db_prefix() . 'cf_buisness_types ON ' . db_prefix() . 'cf_buisness_types.id = ' . db_prefix() . 'cf_expenses.buisness_id',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'cf_expenses.currency',
    'LEFT JOIN (SELECT buisness_id, SUM(amount) AS total_cash_in FROM ' . db_prefix() . 'cf_expenses WHERE operation = "cash-in" GROUP BY buisness_id) AS cin ON ' . db_prefix() . 'cf_expenses.buisness_id = cin.buisness_id',
    'LEFT JOIN (SELECT buisness_id, SUM(amount) AS total_cash_out FROM ' . db_prefix() . 'cf_expenses WHERE operation = "cash-out" GROUP BY buisness_id) AS cout ON ' . db_prefix() . 'cf_expenses.buisness_id = cout.buisness_id',
    'INNER JOIN (SELECT buisness_id, MAX(dateadded) AS MaxDate FROM ' . db_prefix() . 'cf_expenses GROUP BY buisness_id) tm ON ' . db_prefix() . 'cf_expenses.buisness_id = tm.buisness_id AND ' . db_prefix() . 'cf_expenses.dateadded = tm.MaxDate'
];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'currencies.name as currency_name',
    'cin.total_cash_in AS totcin',
    'cout.total_cash_out AS totcout',
    db_prefix() . 'cf_buisness_types.id as bid'
]);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = '<a href="'.admin_url('cash_flow/list_cf_expenses/'.$aRow['bid']).'">'.$aRow['buisness_name'].'</a>';
    $row[] = app_format_money($aRow['totcin'], $aRow['currency_name']);
    $row[] = app_format_money($aRow['totcout'], $aRow['currency_name']);
    $row[] = isset($aRow['balance']) ? app_format_money($aRow['balance'], $aRow['currency_name']) : 0;
    $output['aaData'][] = $row;
}
