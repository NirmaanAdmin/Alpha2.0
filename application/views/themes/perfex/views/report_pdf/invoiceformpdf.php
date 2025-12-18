<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Get company logo
$logo = '';
$company_logo = get_option('company_logo_dark');
if (!empty($company_logo)) {
    $logo = '<img src="' . base_url('uploads/company/' . $company_logo) . '" width="230" height="100">';
}

// Calculate totals
$totals = [
    'subtotal' => 0,
    'total' => 0,
    'total_tax' => 0,
    'discount_total' => 0,
    'adjustment' => 0,
    'credits_applied' => 0,
    'amount_open' => 0,
];

// Count statuses
$status_counts = [
    'Unpaid' => 0,
    'Paid' => 0,
    'Partially Paid' => 0,
    'Overdue' => 0,
    'Cancelled' => 0,
    'Draft' => 0,
];

// Get tax column names dynamically
$tax_columns = [];
$tax_column_count = 0;
if (!empty($invoice_data)) {
    $first_row = $invoice_data[0];
    foreach ($first_row as $key => $value) {
        if (strpos($key, 'total_tax_single_') === 0) {
            $tax_columns[$key] = $key;
            $totals[$key] = 0;
            $tax_column_count++;
        }
    }
}

// Calculate totals and status counts
foreach ($invoice_data as $invoice) {
    $totals['subtotal'] += floatval($invoice['subtotal']);
    $totals['total'] += floatval($invoice['total']);
    $totals['total_tax'] += floatval($invoice['total_tax']);
    $totals['discount_total'] += floatval($invoice['discount_total']);
    $totals['adjustment'] += floatval($invoice['adjustment']);
    $totals['credits_applied'] += floatval($invoice['credits_applied']);
    $totals['amount_open'] += floatval($invoice['amount_open']);
    
    // Add tax column totals
    foreach ($tax_columns as $tax_column) {
        if (isset($invoice[$tax_column])) {
            $totals[$tax_column] += floatval($invoice[$tax_column]);
        }
    }
    
    // Count status
    switch ($invoice['status']) {
        case 1:
            $status_counts['Unpaid']++;
            break;
        case 2:
            $status_counts['Paid']++;
            break;
        case 3:
            $status_counts['Partially Paid']++;
            break;
        case 4:
            $status_counts['Overdue']++;
            break;
        case 5:
            $status_counts['Cancelled']++;
            break;
        case 6:
            $status_counts['Draft']++;
            break;
    }
}

// Organization Info
$organization_info = '';
$organization_info .= '<div style="color:#424242;">';
$organization_info .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
$organization_info .= '<tr>';
$organization_info .= '<td width="60%" style="vertical-align:top;">';
$organization_info .= format_organization_info();
$organization_info .= '</td>';
$organization_info .= '<td width="40%" style="text-align:center; vertical-align:top;">';
$organization_info .= $logo;
$organization_info .= '</td>';
$organization_info .= '</tr>';
$organization_info .= '</table>';
$organization_info .= '</div>';

$pdf->writeHTML($organization_info, true, false, true, false, '');

// Report Header
$report_header = '';
$report_header .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">';
$report_header .= '<tbody>';
$report_header .= '<tr style="font-size:20px;">';
$report_header .= '<td align="center" colspan="2"><b>INVOICE REPORT</b></td>';
$report_header .= '</tr>';
$report_header .= '<tr style="font-size:13px;">';
$report_header .= '<td width="50%" align="left"><b>Generated Date:</b></td>';
$report_header .= '<td width="50%" align="left">' . date('d M, Y H:i:s') . '</td>';
$report_header .= '</tr>';
$report_header .= '<tr style="font-size:13px;">';
$report_header .= '<td width="50%" align="left"><b>Total Invoices:</b></td>';
$report_header .= '<td width="50%" align="left">' . count($invoice_data) . '</td>';
$report_header .= '</tr>';
$report_header .= '</tbody>';
$report_header .= '</table>';

$pdf->writeHTML($report_header, true, false, true, false, '');

// Invoice Data Table
$invoice_table = '';
$invoice_table .= '<br><table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$invoice_table .= '<thead>';
$invoice_table .= '<tr style="font-size:11px; background-color:#f5f5f5;">';
$invoice_table .= '<th width="10%" align="left"><b>Invoice #</b></th>';
$invoice_table .= '<th width="10%" align="left"><b>Client</b></th>';
$invoice_table .= '<th width="8%" align="left"><b>Date</b></th>';
$invoice_table .= '<th width="8%" align="left"><b>Due Date</b></th>';
$invoice_table .= '<th width="7%" align="left"><b>Subtotal</b></th>';
$invoice_table .= '<th width="7%" align="left"><b>Total</b></th>';
$invoice_table .= '<th width="7%" align="left"><b>Total Tax</b></th>';

// Add tax columns
foreach ($tax_columns as $tax_column) {
    $tax_display_name = str_replace('total_tax_single_', 'Tax ', $tax_column);
    $tax_display_name = str_replace('_', ' ', $tax_display_name);
    $invoice_table .= '<th width="6%" align="left"><b>' . ucwords($tax_display_name) . '</b></th>';
}

$invoice_table .= '<th width="6%" align="left"><b>Discount</b></th>';
$invoice_table .= '<th width="6%" align="left"><b>Adjustment</b></th>';
$invoice_table .= '<th width="6%" align="left"><b>Credits</b></th>';
$invoice_table .= '<th width="7%" align="left"><b>Amount Open</b></th>';
$invoice_table .= '<th width="6%" align="left"><b>Status</b></th>';
$invoice_table .= '</tr>';
$invoice_table .= '</thead>';
$invoice_table .= '<tbody>';

if (!empty($invoice_data)) {
    foreach ($invoice_data as $invoice) {
        // Get status text and style
        $status_text = '';
        $status_style = '';
        switch ($invoice['status']) {
            case 1:
                $status_text = 'Unpaid';
                $status_style = 'color:#a94442; background-color:#f2dede; padding:2px 5px; border-radius:3px;';
                break;
            case 2:
                $status_text = 'Paid';
                $status_style = 'color:#3c763d; background-color:#dff0d8; padding:2px 5px; border-radius:3px;';
                break;
            case 3:
                $status_text = 'Partial';
                $status_style = 'color:#8a6d3b; background-color:#fcf8e3; padding:2px 5px; border-radius:3px;';
                break;
            case 4:
                $status_text = 'Overdue';
                $status_style = 'color:#a94442; background-color:#f2dede; padding:2px 5px; border-radius:3px;';
                break;
            case 5:
                $status_text = 'Cancelled';
                $status_style = 'color:#666; background-color:#d9d9d9; padding:2px 5px; border-radius:3px;';
                break;
            case 6:
                $status_text = 'Draft';
                $status_style = 'color:#31708f; background-color:#d9edf7; padding:2px 5px; border-radius:3px;';
                break;
            default:
                $status_text = 'Unknown';
                $status_style = 'padding:2px 5px;';
        }
        
        $invoice_table .= '<tr style="font-size:10px;">';
        $invoice_table .= '<td width="10%" align="left">' . format_invoice_number($invoice['id']) . '</td>';
        $invoice_table .= '<td width="10%" align="left">';
        if (!empty($invoice['deleted_customer_name'])) {
            $invoice_table .= e($invoice['deleted_customer_name']);
        } else {
            $invoice_table .= e($invoice['company'] ?? 'N/A');
        }
        $invoice_table .= '</td>';
        $invoice_table .= '<td width="8%" align="left">' . _d($invoice['date']) . '</td>';
        $invoice_table .= '<td width="8%" align="left">' . _d($invoice['duedate']) . '</td>';
        $invoice_table .= '<td width="7%" align="right">' . app_format_money($invoice['subtotal'], '') . '</td>';
        $invoice_table .= '<td width="7%" align="right">' . app_format_money($invoice['total'], '') . '</td>';
        $invoice_table .= '<td width="7%" align="right">' . app_format_money($invoice['total_tax'], '') . '</td>';
        
        // Add tax columns
        foreach ($tax_columns as $tax_column) {
            $tax_value = isset($invoice[$tax_column]) ? $invoice[$tax_column] : 0;
            $invoice_table .= '<td width="6%" align="right">' . app_format_money($tax_value, '') . '</td>';
        }
        
        $invoice_table .= '<td width="6%" align="right">' . app_format_money($invoice['discount_total'], '') . '</td>';
        $invoice_table .= '<td width="6%" align="right">' . app_format_money($invoice['adjustment'], '') . '</td>';
        $invoice_table .= '<td width="6%" align="right">' . app_format_money($invoice['credits_applied'], '') . '</td>';
        $invoice_table .= '<td width="7%" align="right">' . app_format_money($invoice['amount_open'], '') . '</td>';
        $invoice_table .= '<td width="6%" align="center"><span style="' . $status_style . '">' . e($status_text) . '</span></td>';
        $invoice_table .= '</tr>';
    }
    
    // Totals Row
    $invoice_table .= '<tr style="font-size:11px; background-color:#f9f9f9; font-weight:bold;">';
    $invoice_table .= '<td colspan="4" align="right"><b>TOTALS:</b></td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['subtotal'], '') . '</td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['total'], '') . '</td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['total_tax'], '') . '</td>';
    
    // Add tax column totals
    foreach ($tax_columns as $tax_column) {
        $tax_total = isset($totals[$tax_column]) ? $totals[$tax_column] : 0;
        $invoice_table .= '<td align="right">' . app_format_money($tax_total, '') . '</td>';
    }
    
    $invoice_table .= '<td align="right">' . app_format_money($totals['discount_total'], '') . '</td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['adjustment'], '') . '</td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['credits_applied'], '') . '</td>';
    $invoice_table .= '<td align="right">' . app_format_money($totals['amount_open'], '') . '</td>';
    $invoice_table .= '<td align="center"></td>';
    $invoice_table .= '</tr>';
} else {
    $invoice_table .= '<tr>';
    $colspan = 14 + $tax_column_count;
    $invoice_table .= '<td colspan="' . $colspan . '" align="center" style="font-size:12px; padding:20px;">No invoices found</td>';
    $invoice_table .= '</tr>';
}

$invoice_table .= '</tbody>';
$invoice_table .= '</table>';

$pdf->writeHTML($invoice_table, true, false, true, false, '');

?>