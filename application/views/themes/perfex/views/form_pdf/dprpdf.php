<?php

defined('BASEPATH') or exit('No direct script access allowed');

$organization_info = '';
$organization_info = '<div style="color:#424242;">';
$organization_info .= format_organization_info();
$organization_info .= '</div><br/><br/>';
$pdf->writeHTML($organization_info, true, false, false, false, '');

$formbasicinfo = '';
$formbasicinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$formbasicinfo .= '<tbody>';
$formbasicinfo .= '
<tr style="font-size:20px;" colspan="4">
    <td align="center"><b>'.strtoupper($form_data->name).'</b></td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>'._l('form_settings_subject').'</b></td>
    <td width="40%;" align="left">'.$form_data->subject.'</td>
    <td width="20%;" align="left"><b>'._l('form_settings_assign_to').'</b></td>
    <td width="20%;" align="left">'.get_staff_full_name($form_data->assigned).'</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>'._l('project').'</b></td>
    <td width="40%;" align="left">'.get_project_name_by_id($form_data->project_id).'</td>
    <td width="20%;" align="left"><b>'._l('form_pipe_date').'</b></td>
    <td width="20%;" align="left">'.date('d-m-Y', strtotime($form_data->date)).'</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>'._l('department').'</b></td>
    <td width="40%;" align="left">'.get_staff_department_name($form_data->department).'</td>
    <td width="20%;" align="left"><b>Client</b></td>
    <td width="20%;" align="left">'.get_company_name($form_basic_info->client_id).'</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Consultant</b></td>
    <td width="40%;" align="left">'.$form_basic_info->consultant.'</td>
    <td width="20%;" align="left"><b>PMC</b></td>
    <td width="20%;" align="left">'.$form_basic_info->pmc.'</td>
</tr>
<tr style="font-size:13px;">
    <td width="20%;" align="left"><b>Weather</b></td>
    <td width="40%;" align="left">'.$form_basic_info->weather.'</td>
    <td width="20%;" align="left"><b>Work Stop?</b></td>
    <td width="20%;" align="left">'.$form_basic_info->work_stop.'</td>
</tr>
';
$formbasicinfo .= '</tbody>';
$formbasicinfo .= '</table>';

$pdf->writeHTML($formbasicinfo, true, false, false, false, '');

$formrowsinfo = '';
$formrowsinfo .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">';
$formrowsinfo .= '<tbody>';
$formrowsinfo .= '
<tr style="font-size:20px;">
    <td colspan="12" align="center"><b>ACTIVITY WITH LOCATION & OUTPUT</b></td>
</tr>
<tr style="font-size:11px;">
    <td rowspan="2" width="10%;" align="center"><b>Location</b></td>
    <td rowspan="2" width="10%;" align="center"><b>Agency</b></td>
    <td rowspan="2" width="10%;" align="center"><b>Type</b></td>
    <td colspan="2" width="20%;" align="center"><b>Work Progress</b></td>
    <td rowspan="2" width="8%;" align="center"><b>Machinery</b></td>
    <td colspan="6" width="42%;" align="center"><b>Manpower</b></td>
</tr>
<tr style="font-size:11px;">
    <td width="10%;" align="center"><b>Work Execute (smt/Rmt/Cmt)</b></td>
    <td width="10%;" align="center"><b>Material Consumption</b></td>
    <td width="7%;" align="center"><b>Skilled</b></td>
    <td width="7%;" align="center"><b>Unskilled</b></td>
    <td width="7%;" align="center"><b>Depart</b></td>
    <td width="7%;" align="center"><b>Total</b></td>
    <td width="7%;" align="center"><b>Male</b></td>
    <td width="7%;" align="center"><b>Female</b></td>
</tr>
';
if(!empty($form_rows_info)) {
    foreach ($form_rows_info as $key => $value) {
        $type = $value['type'];
        $laber_type_listing = get_laber_type_listing(array(), '', true);
        $type_value = '';
        if(!empty($type)) {
            $filteredArray = array_filter($laber_type_listing, function ($item) use ($type) {
                return $item['id'] == $type;
            });
            $filteredArray = !empty($filteredArray) ? array_values($filteredArray) : array();
            $type_value = !empty($filteredArray) ? $filteredArray[0]['name'] : '';
        } 
        $formrowsinfo .= '
            <tr style="font-size:11px;">
                <td align="left">'.$value['location'].'</td>
                <td align="left">'.get_vendor_company_name($value['agency']).'</td>
                <td align="left">'.$type_value.'</td>
                <td align="left">'.$value['work_execute'].' '.get_pur_unit($value['work_execute_unit']).'</td>
                <td align="left">'.$value['material_consumption'].' '.get_pur_unit($value['material_consumption_unit']).'</td>
                <td align="left">'.$value['machinery'].'</td>
                <td align="right">'.$value['skilled'].'</td>
                <td align="right">'.$value['unskilled'].'</td>
                <td align="right">'.$value['depart'].'</td>
                <td align="right">'.$value['total'].'</td>
                <td align="right">'.$value['male'].'</td>
                <td align="right">'.$value['female'].'</td>
            </tr>';
    }
}
$formrowsinfo .= '</tbody>';
$formrowsinfo .= '</table>';

$pdf->writeHTML($formrowsinfo, true, false, false, false, '');

?>