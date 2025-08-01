<?php

defined('BASEPATH') or exit('No direct script access allowed');
$logo = '';
$company_logo = get_option('company_logo_dark');
if (!empty($company_logo)) {
    $logo = '<img src="' . base_url('uploads/company/' . $company_logo) . '" width="230" height="100">';
}
$organization_info = '';
$organization_info = '<div style="color:#424242;">';
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

$pdf->writeHTML($organization_info, true, false, false, false, '');

$formbasicinfo = '';
$formbasicinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$formbasicinfo .= '<tbody>';
$formbasicinfo .= '
<tr style="font-size:20px;" colspan="4">
    <td align="center"><b>DAILY PROGRESS REPORT</b></td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('form_settings_subject') . '</b></td>
    <td width="40%;" align="left">' . $form_data->subject . '</td>
    <td width="20%;" align="left"><b>' . _l('form_settings_assign_to') . '</b></td>
    <td width="20%;" align="left">' . get_staff_full_name($form_data->assigned) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('project') . '</b></td>
    <td width="40%;" align="left">' . get_project_name_by_id($form_data->project_id) . '</td>
    <td width="20%;" align="left"><b>DPR Date</b></td>
    <td width="20%;" align="left">' . date('d M, Y', strtotime($form_data->duedate)) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>' . _l('department') . '</b></td>
    <td width="40%;" align="left">' . get_staff_department_name($form_data->department) . '</td>
    <td width="20%;" align="left"><b>Priority</b></td>
    <td width="20%;" align="left">' . get_priority_name($form_data->priority) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Submission Date</b></td>
    <td width="40%;" align="left">' . date('d M, Y', strtotime($form_data->date)) . '</td>
    <td width="20%;" align="left"><b>Client</b></td>
    <td width="20%;" align="left">' . get_company_name($form_basic_info->client_id) . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Consultant</b></td>
    <td width="40%;" align="left">' . $form_basic_info->consultant . '</td>
    <td width="20%;" align="left"><b>PMC</b></td>
    <td width="20%;" align="left">' . $form_basic_info->pmc . '</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Weather</b></td>
    <td width="10%;" align="left">' . $form_basic_info->weather . '</td>
    <td width="20%;" align="left"><b>Work Stop?</b></td>
    <td width="10%;" align="left">' . $form_basic_info->work_stop . '</td>
    <td width="20%;" align="left"><b>Contractor</b></td>
    <td width="20%;" align="left">' . $form_basic_info->contractor . '</td>
</tr>
';
$formbasicinfo .= '</tbody>';
$formbasicinfo .= '</table>';

$pdf->writeHTML($formbasicinfo, true, false, false, false, '');
$widthloc = '';
if ($form_data->staff_dpr == 'true') {
    $widthloc = '20%';
} else {
    $widthloc = '31%';
}
$formrowsinfo = '';
$formrowsinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$formrowsinfo .= '<tbody>';
$formrowsinfo .= '
<tr style="font-size:20px;">
    <td colspan="11" align="center"><b>ACTIVITY WITH LOCATION & OUTPUT</b></td>
</tr>
<tr style="font-size:11px;">
    <td rowspan="2" width="' . $widthloc . '" align="center"><b>Location</b></td>';
if ($form_data->staff_dpr == 'true') {
    $formrowsinfo .= '<td rowspan="2" width="11%;" align="center"><b>Agency</b></td>';
}

$formrowsinfo .= '<td rowspan="2" width="10%;" align="center"><b>Type</b></td>
    <td rowspan="2" width="8%;" align="center"><b>Sub Type</b></td> <!-- Sub Type column -->
    <td colspan="2" width="20%;" align="center"><b>Work Progress</b></td>
    <td colspan="3" width="15%;" align="center"><b>Manpower</b></td>
    <td rowspan="2" width="8%;" align="center"><b>Machinery</b></td>
    <td rowspan="2" width="8%;" align="center"><b>Total Machinery</b></td> <!-- Total Machinery column -->
</tr>
<tr style="font-size:11px;">
    <td width="10%;" align="center"><b>Work Execute (smt/Rmt/Cmt)</b></td>
    <td width="10%;" align="center"><b>Material Consumption</b></td>
    <td width="5%;" align="center"><b>Male</b></td>
    <td width="5%;" align="center"><b>Female</b></td>
    <td width="5%;" align="center"><b>Total</b></td>
</tr>
';

if (!empty($form_rows_info)) {
    foreach ($form_rows_info as $key => $value) {
        $formrowsinfo .= '
            <tr style="font-size:11px;">
                <td align="left">' . $value['location'] . '</td>';
        if ($form_data->staff_dpr == 'true') {
            $formrowsinfo .= '<td align="left">' . get_vendor_company_name($value['agency']) . '</td>';
        }
        $formrowsinfo .= '<td align="left">' . get_progress_report_type_name($value['type']) . '</td>
                <td align="left">' . get_progress_report_sub_type_name($value['sub_type']) . '</td> 
                <td align="left">' . $value['work_execute'] . '</td>
                <td align="left">' . $value['material_consumption'] . '</td>
                <td align="right">' . $value['male'] . '</td>
                <td align="right">' . $value['female'] . '</td>
                <td align="right">' . $value['total'] . '</td>
                <td align="left">' . get_progress_report_machinary_name($value['machinery']) . '</td>
                <td align="left">' . $value['total_machinery'] . '</td> 
            </tr>';
    }
}

$formrowsinfo .= '</tbody>';
$formrowsinfo .= '</table>';

if ($form_data->message != '' && $form_data->staff_dpr == 'true') {
    //add note to pdf
    $formrowsinfo .= '<br/><br/>';
    $formrowsinfo .= '<h2>Note:</h2>';
    $formrowsinfo .= '<p>' . $form_data->message . '</p>';
}


$pdf->writeHTML($formrowsinfo, true, false, false, false, '');
