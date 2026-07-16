<?php

defined('BASEPATH') or exit('No direct script access allowed');
$module_name = 'internal_delivery_note';

$staff_id_name = 'staff_id';
$project_name = 'project';
$status_name = 'status';
$aColumns = [

    'internal_delivery_code',
    'staff_id',
    'addedfrom',
    'datecreated',
    // 'total_amount',
    'approval',
    ];
$sIndexColumn = 'id';
$sTable       = db_prefix().'internal_delivery_note';
$join         = [ ];

$where = [];

if($this->ci->input->post('day_vouchers')){
    $day_vouchers = to_sql_date($this->ci->input->post('day_vouchers'));
}

if (isset($day_vouchers)) {

    $where[] = ' AND tblgoods_delivery.date_add <= "' . $day_vouchers . '"';
    
}

if($this->ci->input->post('staff_id') && count($this->ci->input->post('staff_id')) > 0){
    $staff_id = $this->ci->input->post('staff_id');
    if($staff_id != ''){
        $where[] = ' AND tblinternal_delivery_note.staff_id IN (' . implode(', ', $staff_id) . ')';
    }
}

if($this->ci->input->post('project') && count($this->ci->input->post('project')) > 0){
    $project = $this->ci->input->post('project');
    if($project != ''){
        $where[] = ' AND tblinternal_delivery_note.project IN (' . implode(', ', $project) . ')';
    }
}

if($this->ci->input->post('status') && count($this->ci->input->post('status')) > 0){
    $status = $this->ci->input->post('status');
    if($status != ''){
        $where[] = ' AND tblinternal_delivery_note.approval IN (' . implode(', ', $status) . ')';
    }
}

$staff_id_value = !empty($this->ci->input->post('staff_id')) ? implode(',', $this->ci->input->post('staff_id')) : NULL;
update_module_filter($module_name, $staff_id_name, $staff_id_value);

$project_value = !empty($this->ci->input->post('project')) ? implode(',', $this->ci->input->post('project')) : NULL;
update_module_filter($module_name, $project_name, $project_value);

$status_value = !empty($this->ci->input->post('status')) ? implode(',', $this->ci->input->post('status')) : NULL;
update_module_filter($module_name, $status_name, $status_value);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id','date_add','internal_delivery_name','internal_delivery_code','description','date_c','date_add','datecreated']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];


   for ($i = 0; $i < count($aColumns); $i++) {
    $CI           = & get_instance();

        if($aColumns[$i] == 'internal_delivery_code'){

            $name = '<a href="' . admin_url('warehouse/view_internal_delivery/' . $aRow['id'] ).'" onclick="init_internal_delivery('.$aRow['id'].'); return false;">' . $aRow['internal_delivery_code'] .' - '.$aRow['internal_delivery_name']. '</a>';


            $name .= '<div class="row-options">';

            $name .= '<a href="' . admin_url('warehouse/view_internal_delivery/' . $aRow['id'] ).'" onclick="init_internal_delivery('.$aRow['id'].'); return false;">' . _l('view') . '</a>';
            
            if((has_permission('warehouse', '', 'edit') || is_admin()) && ($aRow['approval'] == 0)){
                $name .= ' | <a href="' . admin_url('warehouse/add_update_internal_delivery/' . $aRow['id'] ).'" >' . _l('edit') . '</a>';
            }

            if ((has_permission('warehouse', '', 'delete') || is_admin()) && ($aRow['approval'] == 0)) {
                $name .= ' | <a href="' . admin_url('warehouse/delete_internal_delivery/' . $aRow['id'] ).'" class="text-danger _delete" >' . _l('delete') . '</a>';
            }
            

            $name .= '</div>';

            $_data = $name;
 
        }elseif($aColumns[$i] == 'date_add'){

            $_data = _d($aRow['date_add']);

        }elseif($aColumns[$i] == 'staff_id'){
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staff_id']) . '">' . staff_profile_image($aRow['staff_id'], [
                'staff-profile-image-small',
                ]) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/profile/' . $aRow['staff_id']) . '">' . get_staff_full_name($aRow['staff_id']) . '</a>';

        }elseif($aColumns[$i] == 'addedfrom'){
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['addedfrom']) . '">' . staff_profile_image($aRow['addedfrom'], [
                'staff-profile-image-small',
                ]) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/profile/' . $aRow['addedfrom']) . '">' . get_staff_full_name($aRow['addedfrom']) . '</a>';
        }elseif($aColumns[$i] == 'datecreated'){
            $_data = _d($aRow['datecreated']);

        }elseif($aColumns[$i] == 'total_amount'){
            $_data = app_format_money((float)$aRow['total_amount'],'');
            
        }elseif($aColumns[$i] == 'approval') {
             
             if($aRow['approval'] == 1){
                $_data = '<span class="label label-tag tag-id-1 label-tab1"><span class="tag">'._l('approved').'</span><span class="hide">, </span></span>&nbsp';
             }elseif($aRow['approval'] == 0){
                $_data = '<span class="label label-tag tag-id-1 label-tab2"><span class="tag">'._l('not_yet_approve').'</span><span class="hide">, </span></span>&nbsp';
             }elseif($aRow['approval'] == -1){
                $_data = '<span class="label label-tag tag-id-1 label-tab3"><span class="tag">'._l('reject').'</span><span class="hide">, </span></span>&nbsp';
             }
        }
   


        $row[] = $_data;
    }
    $output['aaData'][] = $row;

}
