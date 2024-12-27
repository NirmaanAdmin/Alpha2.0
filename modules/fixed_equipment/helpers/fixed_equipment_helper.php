<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Gets the item file attachment.
 *
 * @param        $id     The identifier
 *
 * @return       The item tp attachment.
 */
function fe_get_item_file_attachment($id, $type = 'locations') {
	$CI = &get_instance();
	$CI->db->where('rel_id', $id);
	$CI->db->where('rel_type', $type);
	$attachments = $CI->db->get(db_prefix() . 'files')->result_array();
	return $attachments;
}
/**
 * { handle item file }
 *
 * @param      string   $id     The identifier
 *
 * @return     boolean
 */
function fe_handle_item_file($id, $type, $head_file_name = '') {
	$path = FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER . '/'.$type.'/' . $id . '/';
	$CI = &get_instance();
	$totalUploaded = 0;

	if (isset($_FILES['attachments']['name'])
		&& ($_FILES['attachments']['name'] != '' || is_array($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > 0)) {
		if (!is_array($_FILES['attachments']['name'])) {
			$_FILES['attachments']['name'] = [$_FILES['attachments']['name']];
			$_FILES['attachments']['type'] = [$_FILES['attachments']['type']];
			$_FILES['attachments']['tmp_name'] = [$_FILES['attachments']['tmp_name']];
			$_FILES['attachments']['error'] = [$_FILES['attachments']['error']];
			$_FILES['attachments']['size'] = [$_FILES['attachments']['size']];
		}
		_file_attachments_index_fix('attachments');
		for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {

			// Get the temp file path
			$tmpFilePath = $_FILES['attachments']['tmp_name'][$i];
			// Make sure we have a filepath
			if (!empty($tmpFilePath) && $tmpFilePath != '') {
				if (_perfex_upload_error($_FILES['attachments']['error'][$i])
					|| !_upload_extension_allowed($_FILES['attachments']['name'][$i])) {
					continue;
			}

			_maybe_create_upload_path($path);
			$filename = $head_file_name.unique_filename($path, $_FILES['attachments']['name'][$i]);
			$newFilePath = $path.$filename;
				// Upload the file into the temp dir
			if (move_uploaded_file($tmpFilePath, $newFilePath)) {
				$attachment = [];
				$attachment[] = [
					'file_name' => $filename,
					'filetype' => $_FILES['attachments']['type'][$i],
				];
				$CI->misc_model->add_attachment_to_database($id, $type, $attachment);
				$totalUploaded++;
			}
		}
	}
}
return (bool) $totalUploaded;
}

	/**
	 * reformat currency asset
	 * @param  string $str 
	 * @return string        
	 */
	function fe_reformat_currency_asset($str)
	{
		$f_dot =  str_replace(',','', $str);
	  return ((float)$f_dot + 0);
	}

  /**
     * check format date ymd
     * @param  date $date 
     * @return boolean       
     */
  function fe_check_format_date_ymd($date) {
  	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
  		return true;
  	} else {
  		return false;
  	}
  }
    /**
     * check format date
     * @param  date $date 
     * @return boolean 
     */
    function fe_check_format_date($date){
    	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s(0|[0-1][0-9]|2[0-4]):?((0|[0-5][0-9]):?(0|[0-5][0-9])|6000|60:00)$/",$date)) {
    		return true;
    	} else {
    		return false;
    	}
    }
/**
* format date
* @param  date $date     
* @return date           
*/
function fe_format_date($date){
	if(!fe_check_format_date_ymd($date)){
		$date = to_sql_date($date);
	}
	return $date;
}            

/**
 * format date time
 * @param  date $date     
 * @return date           
 */
function fe_format_date_time($date){
	if(!fe_check_format_date($date)){
		$date = to_sql_date($date, true);
	}
	return $date;
}

/**
 * get image qrcode
 * @param  integer $asset_id 
 * @return string $url            
 */
function fe_get_image_qrcode($asset_id){
	$url =  site_url('modules/fixed_equipment/assets/images/no_image.jpg');
	$CI = &get_instance();
	$CI->db->where('id', $asset_id);
	$data_assets = $CI->db->get(db_prefix() . 'fe_assets')->row();
	if($data_assets){
		if($data_assets->qr_code != ''){
			$url = base_url(FIXED_EQUIPMENT_PATH.'qrcodes/'.$data_assets->qr_code.'.png');	
		}
	}
	return $url;
}

/**
 * get expired date
 * @param  date $start_date   
 * @param  integer $number_month 
 * @return date               
 */
function get_expired_date($start_date, $number_month){
	$time_st = strtotime($start_date . " +".$number_month." month");
	return date('Y-m-d', $time_st);
}

/**
 * crawl get
 * @param  string &$curl  
 * @param  string $link   
 * @param  string $header 
 * @return string         
 */
function fe_crawl_get(&$curl, $link, $header = null) {
	$cookie_file = dirname(__FILE__) . '/' . 'cookie.txt';      
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
	curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
	curl_setopt($curl, CURLOPT_COOKIESESSION, true);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 120);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
	if (isset($header)) {
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	}
	return curl_exec($curl);
}
/**
 * address2geo
 * @param  string 
 * @return json
 */
function fe_address2geo($address){
	$googlemap_api_key = '';
	$api_key = get_option('fe_googlemap_api_key');
	if($api_key){
		$googlemap_api_key = $api_key;
	}
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".rawurlencode($address)."&key=".$googlemap_api_key;
	$curl = curl_init();
	$curlData = fe_crawl_get($curl,$url);  
	$geo = json_decode($curlData);   
	if(isset($geo) && isset($geo->results[0])){
		return json_encode($geo->results[0]->geometry->location);
	}
	return '';
}

/**
	 * get list month
	 * @param   $from_date 
	 * @param   $to_date             
	 */
function fe_get_list_month($from_date, $to_date){
	$start    = new DateTime($from_date);
	$start->modify('first day of this month');
	$end      = new DateTime($to_date);
	$end->modify('first day of next month');
	$interval = DateInterval::createFromDateString('1 month');
	$period   = new DatePeriod($start, $interval, $end);
	$result = [];
	foreach ($period as $dt) {
		$result[] = $dt->format("Y-m-01");
	}
	return $result;
}

/**
 * permisstion list
 * @return array
 */
function fe_list_permisstion() {
	$permission = [];
	$permission[] = 'fixed_equipment_dashboard';
	$permission[] = 'fixed_equipment_assets';
	$permission[] = 'fixed_equipment_licenses';
	$permission[] = 'fixed_equipment_accessories';
	$permission[] = 'fixed_equipment_consumables';
	$permission[] = 'fixed_equipment_components';
	$permission[] = 'fixed_equipment_predefined_kits';
	$permission[] = 'fixed_equipment_requested';
	$permission[] = 'fixed_equipment_maintenances';
	$permission[] = 'fixed_equipment_audit';
	$permission[] = 'fixed_equipment_locations';
	$permission[] = 'fixed_equipment_inventory';
	$permission[] = 'fixed_equipment_order_list';
	$permission[] = 'fixed_equipment_report';
	$permission[] = 'fixed_equipment_depreciations';
	$permission[] = 'fixed_equipment_sign_manager';
	$permission[] = 'fixed_equipment_setting_model';
	$permission[] = 'fixed_equipment_setting_manufacturer';
	$permission[] = 'fixed_equipment_setting_depreciation';
	$permission[] = 'fixed_equipment_setting_category';
	$permission[] = 'fixed_equipment_setting_status_label';
	$permission[] = 'fixed_equipment_setting_custom_field';
	$permission[] = 'fixed_equipment_setting_supplier';
	return $permission;
}

/**
 * get staff id permissions
 * @return array
 */
function fe_get_staff_id_permissions() {
	$CI = &get_instance();
	$array_staff_id = [];
	$index = 0;

	$str_permissions = '';
	foreach (fe_list_permisstion() as $per_key => $per_value) {
		if (strlen($str_permissions) > 0) {
			$str_permissions .= ",'" . $per_value . "'";
		} else {
			$str_permissions .= "'" . $per_value . "'";
		}
	}

	$sql_where = "SELECT distinct staff_id FROM " . db_prefix() . "staff_permissions
        where feature IN (" . $str_permissions . ")
        ";
	$staffs = $CI->db->query($sql_where)->result_array();

	if (count($staffs) > 0) {
		foreach ($staffs as $key => $value) {
			$array_staff_id[$index] = $value['staff_id'];
			$index++;
		}
	}
	return $array_staff_id;
}

/**
 * get staff id not permissions
 * @return array
 */
function fe_get_staff_id_not_permissions() {
	$CI = &get_instance();
	$CI->db->where('admin != ', 1);
	if (count(fe_get_staff_id_permissions()) > 0) {
		$CI->db->where_not_in('staffid', fe_get_staff_id_permissions());
	}
	return $CI->db->get(db_prefix() . 'staff')->result_array();

}
/**
 * get client IP
 * @return string
 */
function fe_get_client_ip() {
	//whether ip is from the share internet
	$ip = '';
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * get staff email
 * @param  integer $staffid
 * @return string $email
 */
function fe_get_staff_email($staffid) {
	$CI = &get_instance();
	$CI->db->where('staffid', $staffid);
	$CI->db->select('email');
	$email = '';
	$data = $CI->db->get(db_prefix() . 'staff')->row();
	if ($data) {
		$email = $data->email;
	}
	return $email;
}

/**
 * get sign image
 * @param  integer $id     
 * @param  string $folder 
 * @return string         
 */
function fe_get_sign_image($id, $folder){
		$file_path  = FCPATH. 'modules/fixed_equipment/uploads/sign_document/'.$id.'/signature.png';
		return $file_path;
}
/**
 * get image qrcode
 * @param  integer $asset_id 
 * @return string $url            
 */
function fe_get_image_qrcode_pdf($asset_id){
	$url =  site_url('modules/fixed_equipment/assets/images/no_image.jpg');
	$CI = &get_instance();
	$CI->db->where('id', $asset_id);
	$data_assets = $CI->db->get(db_prefix() . 'fe_assets')->row();
	if($data_assets){
		if($data_assets->qr_code != ''){
			$url  = FCPATH. 'modules/fixed_equipment/uploads/qrcodes/'.$data_assets->qr_code.'.png';
		}
	}
	return $url;
}

/**
 * get status modules wh
 * @param  string $module_name 
 * @return boolean             
 */
function fe_get_status_modules($module_name){
	$CI             = &get_instance();

	$sql = 'select * from '.db_prefix().'modules where module_name = "'.$module_name.'" AND active =1 ';
	$module = $CI->db->query($sql)->row();
	if($module){
		return true;
	}else{
		return false;
	}
}

/**
 * process digital signature image
 * @param  string $partBase64
 * @param  string $path
 * @param  string $image_name
 * @return boolean
 */
function fe_process_digital_signature_image($partBase64, $path, $image_name)
{
    if (empty($partBase64)) {
        return false;
    }

    _maybe_create_upload_path($path);
    $filename = unique_filename($path, $image_name.'.png');

    $decoded_image = base64_decode($partBase64);

    $retval = false;

    $path = rtrim($path, '/') . '/' . $filename;

    $fp = fopen($path, 'w+');

    if (fwrite($fp, $decoded_image)) {
        $retval                                 = true;
        $GLOBALS['processed_digital_signature'] = $filename;
    }

    fclose($fp);

    return $retval;
}


function fe_get_warehouse_name($id, $include_code = false){
	$CI             = &get_instance();
	$CI->db->select('name, code');
	$CI->db->where('id', $id);
	$data = $CI->db->get(db_prefix().'fe_warehouse')->row();
	if($data){
		return ($include_code == true ? $data->code.' ' : '').''.$data->name;
	}
	return '';
}

/**
 * packing list status
 * @param  string $status 
 * @return [type]         
 */
function fe_delivery_list_status($status='')
{
    $statuses = [
        [
            'id'             => 'ready_for_packing',
            'color'          => '#28b8daed',
            'name'           => _l('fe_ready_for_packing'),
            'order'          => 1,
            'filter_default' => true,
        ],
        [
            'id'             => 'ready_to_deliver',
            'color'          => '#03A9F4',
            'name'           => _l('fe_ready_to_deliver'),
            'order'          => 2,
            'filter_default' => true,
        ],
        [
            'id'             => 'delivery_in_progress',
            'color'          => '#2196f3',
            'name'           => _l('fe_delivery_in_progress'),
            'order'          => 3,
            'filter_default' => true,
        ],
        [
            'id'             => 'delivered',
            'color'          => '#3db8da',
            'name'           => _l('fe_delivered'),
            'order'          => 4,
            'filter_default' => true,
        ],
        [
            'id'             => 'received',
            'color'          => '#84c529',
            'name'           => _l('fe_received'),
            'order'          => 5,
            'filter_default' => false,
        ],
        [
            'id'             => 'returned',
            'color'          => '#d71a1a',
            'name'           => _l('fe_returned'),
            'order'          => 6,
            'filter_default' => false,
        ],
        [
            'id'             => 'not_delivered',
            'color'          => '#ffa500',
            'name'           => _l('fe_not_delivered'),
            'order'          => 7,
            'filter_default' => false,
        ],
    ];
    usort($statuses, function ($a, $b) {
        return $a['order'] - $b['order'];
    });
    return $statuses;
}

/**
 * get delivery status by id
 * @param  [type] $id 
 * @return [type]     
 */
function fe_get_delivery_status_by_id($id, $type)
{
    $CI       = &get_instance();
    $statuses = fe_delivery_list_status();
    if($type == 'delivery'){
        $status = [
            'id'         => 0,
            'color'   => '#989898',
            'color' => '#989898',
            'name'       => _l('fe_ready_for_packing'),
            'order'      => 1,
        ];
    }else{
        $status = [
            'id'         => 0,
            'color'   => '#989898',
            'color' => '#989898',
            'name'       => _l('fe_ready_to_deliver'),
            'order'      => 1,
        ];
    }
    foreach ($statuses as $s) {
        if ($s['id'] == $id) {
            $status = $s;
            break;
        }
    }
    return $status;
}

/**
 * render delivery status html
 * @param  string $status 
 * @return [type]         
 */
function fe_render_delivery_status_html($id, $type, $status_value = '', $ChangeStatus = true)
{
    $status          = fe_get_delivery_status_by_id($status_value, $type);

    if($type == 'delivery'){
        $task_statuses = fe_delivery_list_status();
    }else{
        $task_statuses = fe_packing_list_status();
    }
    $outputStatus    = '';

    $outputStatus .= '<span class="inline-block label" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '" task-status-table="' . $status_value . '">';
    $outputStatus .= $status['name'];
    $canChangeStatus = (has_permission('fixed_equipment_inventory', '', 'edit') || is_admin());

    if ($canChangeStatus && $ChangeStatus) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskStatus-' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
        $outputStatus .= '</a>';

        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $id . '">';
        foreach ($task_statuses as $taskChangeStatus) {
            if ($status_value != $taskChangeStatus['id']) {
                $outputStatus .= '<li>
                <a href="#" onclick="delivery_status_mark_as(\'' . $taskChangeStatus['id'] . '\',' . $id . ',\'' . $type . '\'); return false;">
                ' . _l('task_mark_as', $taskChangeStatus['name']) . '
                </a>
                </li>';
            }
        }
        $outputStatus .= '</ul>';
        $outputStatus .= '</div>';
    }

    $outputStatus .= '</span>';

    return $outputStatus;
}

/**
 * packing list status
 * @param  string $status 
 * @return [type]         
 */
function fe_packing_list_status($status='')
{

    $statuses = [

        [
            'id'             => 'ready_to_deliver',
            'color'          => '#03A9F4',
            'name'           => _l('fe_ready_to_deliver'),
            'order'          => 2,
            'filter_default' => true,
        ],
        [
            'id'             => 'delivery_in_progress',
            'color'          => '#2196f3',
            'name'           => _l('fe_delivery_in_progress'),
            'order'          => 3,
            'filter_default' => true,
        ],
        [
            'id'             => 'delivered',
            'color'          => '#3db8da',
            'name'           => _l('fe_delivered'),
            'order'          => 4,
            'filter_default' => true,
        ],
        [
            'id'             => 'received',
            'color'          => '#84c529',
            'name'           => _l('fe_received'),
            'order'          => 5,
            'filter_default' => false,
        ],
        [
            'id'             => 'returned',
            'color'          => '#d71a1a',
            'name'           => _l('fe_returned'),
            'order'          => 6,
            'filter_default' => false,
        ],
        [
            'id'             => 'not_delivered',
            'color'          => '#ffa500',
            'name'           => _l('fe_not_delivered'),
            'order'          => 7,
            'filter_default' => false,
        ],
    ];

    usort($statuses, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $statuses;

    return $status;
}

/**
 * get invoice company projecy
 * @param  [type] $invoice_id 
 * @return [type]             
 */
function fe_get_invoice_company_projecy($invoice_id)
{
    $CI           = & get_instance();
    $invoice_info = '';
    if (is_numeric($invoice_id)) {
        $invoices = $CI->db->query('select *, iv.id as id from '.db_prefix().'invoices as iv left join '.db_prefix().'projects as pj on pj.id = iv.project_id left join '.db_prefix().'clients as cl on cl.userid = iv.clientid  where iv.id ='.$invoice_id)->row();
        if($invoices){
            $invoice_info .= ' - '.$invoices->company.' - '.$invoices->name;
        }
    }
    return $invoice_info;
}

function fe_convert_item_taxes($tax, $tax_rate, $tax_name)
{
    /*taxrate taxname
    5.00    TAX5
    id      rate        name
    2|1 ; 6.00|10.00 ; TAX5|TAX10%*/
    $CI           = & get_instance();
    $taxes = [];
    if($tax != null && strlen($tax) > 0){
        $arr_tax_id = explode('|', $tax);
        if($tax_name != null && strlen($tax_name) > 0){
            $arr_tax_name = explode('|', $tax_name);
            $arr_tax_rate = explode('|', $tax_rate);
            foreach ($arr_tax_name as $key => $value) {
                $taxes[]['taxname'] = $value . '|' .  $arr_tax_rate[$key];
            }
        }elseif($tax_rate != null && strlen($tax_rate) > 0){
            $CI->load->model('fixed_equipment/fixed_equipment_model');
            $arr_tax_id = explode('|', $tax);
            $arr_tax_rate = explode('|', $tax_rate);
            foreach ($arr_tax_id as $key => $value) {
                $_tax_name = $CI->fixed_equipment_model->get_tax_name($value);
                if(isset($arr_tax_rate[$key])){
                    $taxes[]['taxname'] = $_tax_name . '|' .  $arr_tax_rate[$key];
                }else{
                    $taxes[]['taxname'] = $_tax_name . '|' .  $CI->fixed_equipment_model->tax_rate_by_id($value);

                }
            }
        }else{
            $CI->load->model('fixed_equipment/fixed_equipment_model');
            $arr_tax_id = explode('|', $tax);
            $arr_tax_rate = explode('|', $tax_rate);
            foreach ($arr_tax_id as $key => $value) {
                $_tax_name = $CI->fixed_equipment_model->get_tax_name($value);
                $_tax_rate = $CI->fixed_equipment_model->tax_rate_by_id($value);
                $taxes[]['taxname'] = $_tax_name . '|' .  $_tax_rate;
            } 
        }

    }
    return $taxes;
}
/**
 * get cookie value from id
 * @param  integer $id               
 * @param  string $id_cookie_name   
 * @param  string $find_cookie_name 
 * @return string                   
 */
function fe_get_cookie_value_from_id($id, $id_cookie_name, $find_cookie_name){
	$array_list_id_booking = [];
	if(isset($_COOKIE[$id_cookie_name]) && $list_id = $_COOKIE[$id_cookie_name]){
		if($list_id){
			$array_list_id_booking = explode(',',$list_id);
			if(is_array($array_list_id_booking)){
				$index = array_search($id, $array_list_id_booking);
				if($index >= 0 && isset($_COOKIE[$find_cookie_name]) && $list_find = $_COOKIE[$find_cookie_name]){
					$list_find = $_COOKIE[$find_cookie_name];
					if($list_find){
						$array_list = explode(',', $list_find);
						return (isset($array_list) ? $array_list[$index] : '');
					}
				}
			}
		}
	}
	else{
		return '';
	}
}

/**
 * count refund
 * @param  integer $order_id 
 * @return integer           
 */
function fe_count_refund($order_id){
	$CI = &get_instance();
	$CI->db->where('order_id', $order_id);
	return $CI->db->get(db_prefix().'fe_refunds')->num_rows();
}

/**
 * get order number
 * @param  integer $order_id 
 * @return string           
 */
function fe_get_order_number($order_id){
    $CI = &get_instance();
	$CI->db->select('order_number');
	$CI->db->where('id', $order_id);
	$data = $CI->db->get(db_prefix().'fe_cart')->row();
	if($data){
		return $data->order_number;
	}
	return '';
}

/**
 * get status by index
 * @param  integer $index 
 * @return string        
 */
function fe_get_status_by_index($index, $return_obj = false){
	$status = '';
	$slug = '';
	switch ($index) {
		case 0:
		$status = _l('fe_draft');
		$slug = 'draft';
		break;  
		case 1:
		$status = _l('fe_processing');
		$slug = 'processing';
		break;      
		case 2:
		$status = _l('fe_pending_payment');
		$slug = 'pending_payment';
		break;
		case 3:
		$status = _l('fe_confirm');
		$slug = 'confirm';
		break;
		case 4:
		$status = _l('fe_shipping');
		$slug = 'shipping';
		break;
		case 5:
		$status = _l('fe_finish');
		$slug = 'finish';
		break;
		case 6:
		$status = _l('fe_refund');
		$slug = 'refund';
		break;
		case 7:
		$status = _l('fe_return');
		$slug = 'return';
		break; 
		case 8:
		$status = _l('fe_cancelled');
		$slug = 'cancelled';
		break;  
		case 9:
		$status = _l('fe_on_hold');
		$slug = 'on-hold';
		break;  
		case 10:
		$status = _l('fe_failed');
		$slug = 'failed';
		break; 
		case 11:
		$status = _l('fe_return');
		$slug = 'return';
		break; 
		case 12:
		$status = _l('fe_partial_return');
		$slug = 'partial_return';
		break; 
		case 13:
		$status = _l('fe_partial_refund');
		$slug = 'partial_refund';
		break; 
		case 14:
		$status = _l('fe_paid');
		$slug = 'paid';
		break; 
	}
	if($return_obj){
		$obj = new stdClass();
		$obj->status = $status;
		$obj->slug = $slug;
		return $obj;
	}
	return $status;
}

/**
 * omni status list
 * @return array 
 */
function fe_status_list($is_return_order = false){
	if(!$is_return_order){
		return [
			['id' => 0, 'label' => _l('fe_draft'), 'key' => 'draft'],
			['id' => 1, 'label' => _l('fe_processing'), 'key' => 'processing'],
			['id' => 2, 'label' => _l('fe_pending_payment'), 'key' => 'pending_payment'],
			['id' => 14, 'label' => _l('fe_paid'), 'key' => 'paid'],
			['id' => 3, 'label' => _l('fe_confirm'), 'key' => 'confirm'],
			['id' => 4, 'label' => _l('fe_shipping'), 'key' => 'shipping'],
			['id' => 5, 'label' => _l('fe_finish'), 'key' => 'finish'],
			['id' => 10, 'label' => _l('fe_failed'), 'key' =>'failed'],
			['id' => 11, 'label' => _l('fe_return'), 'key' =>'return'],
			['id' => 6, 'label' => _l('fe_refund'), 'key' => 'refund'],
			['id' => 12, 'label' => _l('fe_partial_return'), 'key' => 'partial_return'],
			['id' => 13, 'label' => _l('fe_partial_refund'), 'key' => 'partial_refund'],
			['id' => 8, 'label' => _l('fe_canceled'), 'key' => 'canceled'],
			['id' => 9, 'label' => _l('fe_on_hold'), 'key' => 'on_hold']
		];
	}
	else{
		return [
			['id' => 0, 'label' => _l('fe_draft'), 'key' => 'draft'],
			['id' => 1, 'label' => _l('fe_processing'), 'key' => 'processing'],
			['id' => 2, 'label' => _l('fe_pending_payment'), 'key' => 'pending_payment'],
			['id' => 3, 'label' => _l('fe_confirm'), 'key' => 'confirm'],
			['id' => 4, 'label' => _l('fe_shipping'), 'key' => 'shipping'],
			['id' => 5, 'label' => _l('fe_finish'), 'key' => 'finish'],
			['id' => 10, 'label' => _l('fe_failed'), 'key' =>'failed'],
			['id' => 8, 'label' => _l('fe_canceled'), 'key' => 'canceled'],
			['id' => 9, 'label' => _l('fe_on_hold'), 'key' => 'on_hold']
		];
	}
	
}

/**
 * fe get user group name
 * @return  
 */
function fe_get_user_group_name($user_id){
	$CI = & get_instance(); 
	$data = $CI->db->query('select name from '.db_prefix().'customer_groups a left join '.db_prefix().'customers_groups b on a.groupid = b.id where customer_id = '.$user_id)->result_array();
	$result = '';
	foreach ($data as $item) {
		$result .= $item['name'].', ';
	}
	if($result != ''){
		$result = rtrim($result, ', ');
	}
	return $result;
}

/**
 * get model item
 * @param integer $item_id 
 * @return string          
 */
function fe_get_model_item($item_id){
	$CI = &get_instance();
	$CI->db->select('model_id');
	$CI->db->where('id', $item_id);
	$data = $CI->db->get(db_prefix().'fe_assets')->row();
	if($data){
		return $data->model_id;
	}
	return '';
}

/**
 * get type item
 * @param integer $item_id 
 * @return string          
 */
function fe_get_type_item($item_id){
	$CI = &get_instance();
	$CI->db->select('type');
	$CI->db->where('id', $item_id);
	$data = $CI->db->get(db_prefix().'fe_assets')->row();
	if($data){
		return $data->type;
	}
	return '';
}

/**
 * fe get total refund
 * @param  integer $order_id 
 * @return integer           
 */
function fe_get_total_refund($order_id){
	$CI = &get_instance();
	$CI->db->select('original_order_id');
	$CI->db->where('id', $order_id);
	$data = $CI->db->get(db_prefix().'fe_cart')->row();
	// Caculate total refund only for return order
	if($data && is_numeric($data->original_order_id)){
		$total = 0;
		$CI->db->select('amount');
		$CI->db->where('order_id', $order_id);
		$data_refund = $CI->db->get(db_prefix().'fe_refunds')->result_array();
		if($data_refund){
			foreach ($data_refund as $key => $value) {
				$total += $value['amount'];
			}

		}
		return $total;
	}
	return 0;
}

/**
 * get model name
 * @param integer $model id 
 * @return string          
 */
function fe_get_model_name($model_id){
	$CI = &get_instance();
	$CI->db->select('model_name');
	$CI->db->where('id', $model_id);
	$data = $CI->db->get(db_prefix().'fe_models')->row();
	if($data){
		return $data->model_name;
	}
	return '';
}

/**
 * Get customer name.
 */
function fe_get_customer_name($client_id){
    $CI   = & get_instance();
    $CI->db->select('company');
    $CI->db->where('userid', $client_id);
    $customer = $CI->db->get(db_prefix().'clients')->row();
    if($customer){
        return $customer->company;
    }
    return '';
}

/**
 * count portal order
 * @param  integer $status 
 * @return integer          
 */
function fe_count_portal_order($userid, $status = 0, $channel_id = '', $where = ''){
	if(is_numeric($userid)){
		$CI           = & get_instance();
		$CI->db->select('id');
		if(is_numeric($channel_id)){
			$CI->db->where('channel_id', $channel_id);   
		}
		if($where != ''){
			$CI->db->where($where);   
		}
		$CI->db->where('userid', $userid);
		$CI->db->where('status', $status);
		return $CI->db->get(db_prefix().'fe_cart')->num_rows();
	}
	return 0;
}


/**
 * can refund order
 * @param  integer $order_id 
 * @return boolean           
 */
function fe_can_refund_order($order_id){
	$CI = & get_instance();
	$check_valid = false;
	$cart_data = $CI->fixed_equipment_model->get_cart($order_id);
	if($cart_data){
		if($cart_data->status == 5){
			$check_valid = true;    					
		}
	}
	return $check_valid;
}

/**
 * item name
 * @param  integer  $id           
 * @param  boolean $include_code 
 * @return string                
 */
function fe_item_name($id, $include_code = false){
	$CI             = &get_instance();
	$CI->db->select('assets_name, series');
	$CI->db->where('id', $id);
	$data = $CI->db->get(db_prefix().'fe_assets')->row();
	if($data && is_object($data)){
		return ($include_code == true ? $data->series.' ' : '').''.$data->assets_name;
	}
	return '';
}

/**
 * item name
 * @param  integer  $id           
 * @param  boolean $include_code 
 * @return string                
 */
function fe_item_name_from_serial($series, $include_code = false){
	$CI             = &get_instance();
	$CI->db->select('assets_name, series');
	$CI->db->where('series', $series);
	$data = $CI->db->get(db_prefix().'fe_assets')->row();
	if($data && is_object($data)){
		return ($include_code == true ? $data->series.' ' : '').''.$data->assets_name;
	}
	return '';
}

/**
 * get number day
 * @param  string $rental_unit  
 * @param  string $rental_time  
 * @param  string $dropoff_time 
 * @param  string $pickup_time  
 * @return integer               
 */
function fe_get_number_day($rental_unit, $rental_time = '', $dropoff_time = '', $pickup_time = ''){
	if($rental_unit == 'hour'){
		if($dropoff_time != '' && $pickup_time != ''){
			return $dropoff_time - $pickup_time;
		}
	} 
	else{
		if($rental_time != ''){
			$rental_time = explode(' to ', $rental_time);
			$start_time = $rental_time[0];
			$end_time = $rental_time[1];
			$startTimeStamp = strtotime($start_time);
			$endTimeStamp = strtotime($end_time);
			switch ($rental_unit) {
				case 'day':
				$timeDiff = abs($endTimeStamp - $startTimeStamp);
				// 86400 seconds in one day
				$numberDays = $timeDiff/86400;  
				$numberDays = intval($numberDays) + 1;
				return $numberDays;
				case 'week':
				//Create a DateTime object for the first date.
				$firstDate = new DateTime($start_time);
    			//Create a DateTime object for the second date.
				$secondDate = new DateTime($end_time);
    			//Get the difference between the two dates in days.
				$differenceInDays = $firstDate->diff($secondDate)->days;
    			//Divide the days by 7
				$differenceInWeeks = $differenceInDays / 7;
    			//Round down with floor and return the difference in weeks.
				return floor($differenceInWeeks) + 1;
				case 'month':
				$start = new DateTime($start_time.' 00:00:00');
				$end = new DateTime($end_time.' 00:00:00');
				$diff = $start->diff($end);
				$yearsInMonths = $diff->format('%r%y') * 12;
				$months = $diff->format('%r%m');
				$totalMonths = $yearsInMonths + $months;
				return $totalMonths + 1;
				case 'year':
				$diff = abs($startTimeStamp - $endTimeStamp);
				$years = floor($diff / (365*60*60*24));
				return $years + 1;
			}
		}
	}
	return 0;
}

/**
 * line total booking
 * @param  string $rental_value  
 * @param  string $rental_unit   
 * @param  string $rental_period 
 * @param  string $rental_time   
 * @param  string $dropoff_time  
 * @param  string $pickup_time   
 * @return string                
 */
function fe_line_total_booking($rental_value, $rental_unit, $rental_period, $rental_time = '', $dropoff_time = '', $pickup_time = ''){
	$number_day = fe_get_number_day($rental_unit, $rental_time, $dropoff_time, $pickup_time);
	$obj = new stdClass();
	$obj->number_day = $number_day;
	$obj->line_total = round($number_day * $rental_value / $rental_period);
	return $obj;
}


/**
 * [wh shipment status
 * @return [type] 
 */
function fe_shipment_status()
{
    $status=[];
    $status[]=[
        'name' => 'confirmed_order',
        'label' => 'confirmed_order',
        'order' => 1,
    ];
    $status[]=[
        'name' => 'processing_order',
        'label' => 'processing_order',
        'order' => 2,

    ];
    $status[]=[
        'name' => 'quality_check',
        'label' => 'quality_check',
        'order' => 3,

    ];
    $status[]=[
        'name' => 'product_dispatched',
        'label' => 'product_dispatched',
        'order' => 4,

    ];
    $status[]=[
        'name' => 'product_delivered',
        'label' => 'product_delivered',
        'order' => 5,
    ];
    return $status;
}

/**
 * get model name
 * @param integer $model id 
 * @return string          
 */
function fe_get_order_name($id, $more_info = false){
	$CI = &get_instance();
	$CI->db->select('order_number, company, phonenumber');
	$CI->db->where('id', $id);
	$data = $CI->db->get(db_prefix().'fe_cart')->row();
	if($data){
		if($more_info){
			return $data->order_number.' ('.$data->company.' - '.$data->phonenumber.')';			
		}
		else{
			return $data->order_number;			
		}
	}
	return '';
}

/**
 * get inventory receiving code
 * @param  integer $id 
 * @return string     
 */
function fe_get_inventory_receiving_code($id){
	$CI             = &get_instance();
	$CI->db->select('goods_receipt_code');
	$CI->db->where('id', $id);
	$data = $CI->db->get(db_prefix().'fe_goods_receipt')->row();
	if($data && is_object($data)){
		return $data->goods_receipt_code;
	}
	return '';
}

/**
 * get inventory delivery code
 * @param  integer $id 
 * @return string     
 */
function fe_get_inventory_delivery_code($id){
	$CI             = &get_instance();
	$CI->db->select('goods_delivery_code');
	$CI->db->where('id', $id);
	$data = $CI->db->get(db_prefix().'fe_goods_delivery')->row();
	if($data && is_object($data)){
		return $data->goods_delivery_code;
	}
	return '';
}

/**
 * get item id from serial
 * @param integer $item_id 
 * @return string          
 */
function fe_get_item_id_from_serial($serial){
	$CI = &get_instance();
	$CI->db->select('id');
	$CI->db->where('series', $serial);
	$data = $CI->db->get(db_prefix().'fe_assets')->row();
	if($data){
		return $data->id;
	}
	return 0;
}


	/**
	 * check estimate order
	 * @param  integer $order_id 
	 * @return array           
	 */
	function fe_check_estimate_order($order_id){
		$CI = &get_instance();
		$CI->load->model('fixed_equipment/fixed_equipment_model');
		$result = [];
		$cart_data = $CI->fixed_equipment_model->get_cart($order_id);
		if($cart_data){		
			$cart_detail_data = $CI->fixed_equipment_model->get_cart_detailt_by_master($order_id);
			foreach ($cart_detail_data as $key => $item) {
				$item_id = $item['product_id'];
				if(is_numeric($item['maintenance_id']) && $item['maintenance_id'] > 0){
					$data_audit = $CI->fixed_equipment_model->get_asset_maintenances($item['maintenance_id']);
					if($data_audit && $data_audit->cost > 0){
						$quantity = 0;
						$prices = 0;
						if($cart_data->type == 'order'){
							$quantity = $item['quantity'];
							$prices = $data_audit->cost * (float)$quantity;
						}
						else{
							$quantity = 1;
							$prices = $data_audit->cost;
						}
						array_push($result, [
							'id' => $item['id'],
							'item_id' => $item_id,
							'item_name' => fe_item_name($item_id),
							'cost' => $prices,
							'quantity' => $quantity
						]);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * get default status
	 * @return integer 
	 */
	function fe_get_default_status(){
		$CI = &get_instance();
		$status_id = 0;
		$data_status_label = $CI->db->query('select id from '.db_prefix().'fe_status_labels where status_type = \'deployable\' limit 0,1')->row();	
		if($data_status_label){
			$status_id = $data_status_label->id;
		}
		return $status_id;
	}
	
	/**
	* get html option button
	* @param  ineteger $for_sell 
	* @param  ineteger $for_rent 
	* @return string           
	*/
	function fe_get_html_option_button($for_sell, $for_rent){
		if($for_sell == 1 && $for_rent == 1){
			return '<a class="label label-warning">' . _l('fe_item_for_rent_or_sale_only') . '</a>';
		}
		if($for_sell == 1 && $for_rent == 0){
			return '<a class="label label-warning">' . _l('fe_item_for_sale_only') . '</a>';
		}
		if($for_sell == 0 && $for_rent == 1){
			return '<a class="label label-warning">' . _l('fe_item_for_rent_only') . '</a>';
		}
	}

	function fe_htmldecode($str){
		return html_entity_decode($str ?? '');
	}


	/**
 * AES_256 Encrypt
 * @param string $str
 * @return string
 */
function fe_aes_256_encrypt($str) {
	$key = 'g8934fuw9843hwe8rf9*5bhv';
	$method = 'aes-256-cbc';
	$key = substr(hash('sha256', $key, true), 0, 32);
	$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
	return base64_encode(openssl_encrypt($str, $method, $key, OPENSSL_RAW_DATA, $iv));
}

/**
 * get return order of parent
 * @param  integer $order_id 
 * @return object           
 */
function fe_get_return_order_of_parent($order_id){
	$CI = &get_instance();
	$CI->db->where('original_order_id', $order_id);
	return $CI->db->get(db_prefix().'fe_cart')->row();
}

/**
 * fe get contact email
 * @param  [type] $type 
 * @param  [type] $id   
 * @return [type]       
 */
function fe_get_contact_email($type, $id){
	$email = '';
	$CI           = & get_instance();

	if($type == 'contact'){
		$CI->db->where('id', $id);
		$primary = $CI->db->get(db_prefix() . 'contacts')->row();
		if($primary ){
			$email = $primary->email;
		}

	}else{
		// type client
		$CI->db->where('userid', $id)
		->where('is_primary', 1);

		$primary = $CI->db->get(db_prefix() . 'contacts')->row();
		if($primary ){
			$email = $primary->email;
		}
	}

	return $email;
}

/**
 * issue_handle_movement_attachments
 * @param  [type] $id        
 * @param  string $file_path 
 * @param  string $rel_type  
 * @return [type]            
 */
function issue_handle_movement_attachments($id, $file_path = '', $rel_type = '')
{

	if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
		header('HTTP/1.0 400 Bad error');
		echo _perfex_upload_error($_FILES['file']['error']);
		die;
	}
	if($file_path == ''){
		$path = ISSUE_UPLOAD . $id . '/';
	}else{
		$path = $file_path . $id . '/';
	}

	if($rel_type == ''){
		$rel_type = 'fixe_issue';
	}

	$CI   = & get_instance();

	if (isset($_FILES['file']['name'])) {

        // Get the temp file path
		$tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
		if (!empty($tmpFilePath) && $tmpFilePath != '') {

			_maybe_create_upload_path($path);
			$filename    = $_FILES['file']['name'];
			$newFilePath = $path . $filename;
            // Upload the file into the temp dir
			if (move_uploaded_file($tmpFilePath, $newFilePath)) {

				$CI           = & get_instance();
				$attachment   = [];
				$attachment[] = [
					'file_name' => $filename,
					'filetype'  => $_FILES['file']['type'],
				];
				$CI->misc_model->add_attachment_to_database($id, $rel_type, $attachment);

			}
		}
	}

}

function handle_issue_attachments_array($issueid, $index_name = 'attachments')
{
    $uploaded_files = [];
    $path = ISSUE_UPLOAD . $issueid . '/';
    $CI             = &get_instance();

    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    array_push($uploaded_files, [
                        'file_name' => $filename,
                        'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]);

                    if (is_image($newFilePath)) {
                        create_img_thumb($path, $filename);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

/**
 * issue_data_processing
 * @param  [type] $formdata 
 * @return [type]           
 */
function issue_data_processing($formdata)
{
	$data = [];

	foreach ($formdata['formdata'] as $key => $value) {
		if(isset($data[$value['name']])){
			$data[$value['name']] .= ','.$value['value'];
		}else{
			$data[$value['name']] = $value['value'];
		}
	}
	return $data;
}

/**
 * fe_ticket_status
 * @return [type] 
 */
function fe_ticket_status()
{
	$ticket_status = [

		[
			'id'             => 'open',
			'color'          => '#9E9E9E',
			'name'           => _l('fe_open'),
			'order'          => 1,
			'filter_default' => true,
		],
		[
			'id'             => 'high',
			'color'          => '#F44336',
			'name'           => _l('fe_high'),
			'order'          => 1,
			'filter_default' => true,
		],
		[
			'id'             => 'medium',
			'color'          => '#FF9800',
			'name'           => _l('fe_medium'),
			'order'          => 2,
			'filter_default' => true,
		],
		[
			'id'             => 'low',
			'color'          => '#FFEB3B',
			'name'           => _l('fe_low'),
			'order'          => 3,
			'filter_default' => true,
		],
		[
			'id'             => 'normal',
			'color'          => '#4CAF50',
			'name'           => _l('fe_normal'),
			'order'          => 4,
			'filter_default' => false,
		],
		[
			'id'             => 'closed',
			'color'          => '#2196F3',
			'name'           => _l('fe_closed'),
			'order'          => 5,
			'filter_default' => false,
		],
	];

	usort($ticket_status, function ($a, $b) {
		return $a['order'] - $b['order'];
	});

	return $ticket_status;
}

/**
 * get fe issue status by_id
 * @param  [type] $id   
 * @param  [type] $type 
 * @return [type]       
 */
function get_fe_issue_status_by_id($id, $type)
{
	$CI       = &get_instance();

		// ticket_status
	$statuses = fe_ticket_status();
	$status = [
		'id'             => 'open',
		'color'          => '#9e9e9e',
		'name'           => _l('cs_open'),
		'order'          => 1,
		'filter_default' => true,
	];

	foreach ($statuses as $s) {
		if ($s['id'] == $id) {
			$status = $s;

			break;
		}
	}

	return $status;
}


/**
 * render fe issue status html
 * @param  [type]  $id           
 * @param  [type]  $type         
 * @param  string  $status_value 
 * @param  boolean $ChangeStatus 
 * @return [type]                
 */
function render_fe_issue_status_html($id, $type, $status_value = '', $ChangeStatus = true)
{
	$status          = get_fe_issue_status_by_id($status_value, $type);

	$task_statuses = fe_ticket_status();

	$outputStatus    = '';

	$outputStatus .= '<span class="inline-block label" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '" task-status-table="' . $status_value . '">';
	$outputStatus .= $status['name'];
	$canChangeStatus = (has_permission('fixed_equipment_order_list', '', 'edit') || is_admin());

	if ($canChangeStatus && $ChangeStatus) {
		$outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
		$outputStatus .= '<a href="#" class="dropdown-toggle text-dark dropdown-font-size" id="tableTaskStatus-' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
		$outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
		$outputStatus .= '</a>';

		$outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $id . '">';
		foreach ($task_statuses as $taskChangeStatus) {
			if ($status_value != $taskChangeStatus['id']) {
				$outputStatus .= '<li>
				<a href="#" onclick="fixed_equipment_order_list_status_mark_as(\'' . $taskChangeStatus['id'] . '\',' . $id . ',\'' . $type . '\'); return false;">
				' . _l('task_mark_as', $taskChangeStatus['name']) . '
				</a>
				</li>';
			}
		}
		$outputStatus .= '</ul>';
		$outputStatus .= '</div>';
	}

	$outputStatus .= '</span>';

	return $outputStatus;
}