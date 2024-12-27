<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'goods_receipt_code',
    'supplier_name',
    'buyer_id',
    'date_add',
    'total_tax_money', 
    'total_goods_money',
    'value_of_inventory',
    'total_money',
    'approval',
];
$sIndexColumn = 'id';
$sTable       = db_prefix().'fe_goods_receipt';
$join         = [ ];
$where = [];

if($this->ci->input->post('day_vouchers')){
    $day_vouchers = to_sql_date($this->ci->input->post('day_vouchers'));
}

if (isset($day_vouchers)) {
    $where[] = 'AND date(date_add) = "' . $day_vouchers . '"';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id','date_add','date_c','goods_receipt_code', 'supplier_code']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    for ($i = 0; $i < count($aColumns); $i++) {

        $_data = $aRow[$aColumns[$i]];
        if($aColumns[$i] == 'supplier_name'){
                $_data = $aRow['supplier_name'];
        }elseif($aColumns[$i] == 'buyer_id'){

            $_data = '<div class="text-nowrap"><a href="' . admin_url('staff/profile/' . $aRow['buyer_id']) . '">' . staff_profile_image($aRow['buyer_id'], [
                'staff-profile-image-small',
            ]) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/profile/' . $aRow['buyer_id']) . '">' . get_staff_full_name($aRow['buyer_id']) . '</a></div>';
        }elseif($aColumns[$i] == 'date_add'){
            $_data = _d($aRow['date_add']);
        }elseif ($aColumns[$i] == 'total_tax_money') {
            $_data = app_format_money((float)$aRow['total_tax_money'],'');
        }elseif($aColumns[$i] == 'goods_receipt_code'){
            $name =  '<a href="' . admin_url('fixed_equipment/view_purchase/' . $aRow['id'] ).'" onclick="init_goods_receipt('.$aRow['id'].'); return false;">' . $aRow['goods_receipt_code'] . '</a>';

            $name .= '<div class="row-options text-nowrap">';

            $name .= '<a href="' . admin_url('fixed_equipment/view_purchase/' . $aRow['id'] ).'" onclick="init_goods_receipt('.$aRow['id'].'); return false;">' . _l('view') . '</a>';

            if((has_permission('fixed_equipment_inventory', '', 'edit') || is_admin()) && ($aRow['approval'] == 0)){
                $name .= ' | <a href="' . admin_url('fixed_equipment/manage_goods_receipt/' . $aRow['id'] ).'" >' . _l('edit') . '</a>';
            }

            if ((has_permission('fixed_equipment_inventory', '', 'delete') || is_admin()) && ($aRow['approval'] == 0)) {
                $name .= ' | <a href="' . admin_url('fixed_equipment/delete_goods_receipt/' . $aRow['id'] ).'" class="text-danger _delete" >' . _l('delete') . '</a>';
            }
            $name .= '</div>';
            $_data = $name;
        }elseif ($aColumns[$i] == 'total_goods_money') {
            $_data = app_format_money((float)$aRow['total_goods_money'],'');
        }elseif ($aColumns[$i] == 'total_money') {
            $_data = app_format_money((float)$aRow['total_money'],'');
        }elseif($aColumns[$i] == 'value_of_inventory') {
            $_data = app_format_money((float)$aRow['value_of_inventory'],'');
        }elseif($aColumns[$i] == 'approval') {
         if($aRow['approval'] == 1){
            $_data = '<span class="label label-success tag-id-1 label-tab1"><span class="tag">'._l('approved').'</span><span class="hide">, </span></span>&nbsp';
        }elseif($aRow['approval'] == 0){
            $_data = '<span class="label label-warning tag-id-1 label-tab2"><span class="tag">'._l('not_yet_approve').'</span><span class="hide">, </span></span>&nbsp';
        }elseif($aRow['approval'] == -1){
            $_data = '<span class="label label-danger tag-id-1 label-tab3"><span class="tag">'._l('reject').'</span><span class="hide">, </span></span>&nbsp';
        }
    }
    


    $row[] = $_data;
}
$output['aaData'][] = $row;

}
