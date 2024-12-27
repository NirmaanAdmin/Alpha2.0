<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_edit_permission = false;
if (is_admin() || has_permission('fixed_equipment_inventory', '', 'edit')) {
	$has_edit_permission = true;
}

$has_delete_permission = false;
if (is_admin() || has_permission('fixed_equipment_inventory', '', 'delete')) {
	$has_delete_permission = true;
}


$aColumns = [
	'code',
	'name',
	'address',
	db_prefix() . 'fe_warehouse.order as wh_order',
	'display',
	'note',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'fe_warehouse';
$where = [];
$join= [];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id','code','name','address','city', 'state', 'zip_code', 'country']);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
	$row = [];
	for ($i = 0; $i < count($aColumns); $i++) {
		if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
			$_data = $aRow[strafter($aColumns[$i], 'as ')];
		} 
		if ($aColumns[$i] == 'code') {
			$code = $aRow['code'];
			$code .= '<div class="row-options">';
			if ($has_edit_permission) {
				$code .= '<a href="#" onclick="edit('.$aRow['id'] .'); return false;" class="text-danger" data-commodity_id="' . $aRow['id'] . '"  >' . _l('edit') . '</a>';
			}
			if ($has_edit_permission && $has_delete_permission) {
				$code .= ' | ';
			}
			if ($has_delete_permission) {
				$code .= '<a href="' . admin_url('fixed_equipment/delete_warehouse/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
			}
			$code .= '</div>';
			$_data = $code;
		} elseif ($aColumns[$i] == 'name') {
			$_data = $aRow['name'];
		}elseif($aColumns[$i] == 'address'){
			$address='';
			$address_array = [];
			$address_array[0] =  $aRow['address'];
			$address_array[1] =  $aRow['city'];
			$address_array[2] =  $aRow['state'];
			$address_array[3] =  $aRow['country'];
			$address_array[4] =  $aRow['zip_code'];
			foreach ($address_array as $key => $add_value) {
				if(isset($add_value) && $add_value != ''){
					switch ($key) {
						case 0:
						$address .= $add_value.'<br>';
						break;
						case 1:
						$address .= $add_value;
						break;
						case 2:
						$address .= ', '.$add_value.'<br>';
						break;
						case 3:
						$address .= get_country_name($add_value);
						break;
						case 4:
						$address .= ', '.$add_value;
						break;
					}
				}
			}
			$_data = '<div class="text-nowrap">'.$address.'</div>';
		} elseif ($aColumns[$i] == 'wh_order') {
			$_data = $aRow['wh_order'];

		} elseif ($aColumns[$i] == 'display') {

			if($aRow['display'] == 0){
				$_data =  '<i class="fa fa-times text-danger"></i>'; 
			}else{
				$_data = '<i class="fa fa-check text-success"></i>';
			}

		} elseif ($aColumns[$i] == 'note') {

			$_data = $aRow['note'];
		}

		$row[] = $_data;
	}
	$output['aaData'][] = $row;
}

