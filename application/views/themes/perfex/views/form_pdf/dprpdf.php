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

?>