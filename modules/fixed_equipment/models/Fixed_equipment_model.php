<?php
defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Fixed equipment model
 */
class fixed_equipment_model extends app_model
{
	public function __construct()
	{
		parent::__construct();
		if(!class_exists('qrstr')){
			include_once(FIXED_EQUIPMENT_PATH_PLUGIN.'/phpqrcode/qrlib.php');		
		}
	}
	/**
	 * add depreciations
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_depreciations($data){
		$data['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_depreciations', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update depreciations
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_depreciations($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_depreciations', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * delete depreciations
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_depreciations($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_depreciations');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

		/**
	 * get depreciations
	 * @param  integer $id 
	 * @return array or object    
	 */
		public function get_depreciations($id = ''){
			if($id != ''){
				$this->db->where('id', $id);
				return $this->db->get(db_prefix().'fe_depreciations')->row();
			}
			else{
				return $this->db->get(db_prefix().'fe_depreciations')->result_array();
			}
		}

	/**
	 * add locations
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_locations($data){
		$this->db->insert(db_prefix().'fe_locations', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update locations
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_locations($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_locations', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * delete locations
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_locations($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_locations');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * get locations
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_locations($id = '', $where = '', $select = '*'){
		if($select != ''){
			$this->db->select($select);
		}
		if($where != ''){
			$this->db->where($where);
		}
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_locations')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_locations')->result_array();
		}
	}

	 /**
	 * Gets the file.
	 *
	 * @param         $id      The file id
	 * @param      boolean  $rel_id  The relative identifier
	 *
	 * @return     boolean  The file.
	 */
	 public function get_file($id, $rel_id = false)
	 {
	 	$this->db->where('id', $id);
	 	$file = $this->db->get(db_prefix().'files')->row();
	 	if ($file && $rel_id) {
	 		if ($file->rel_id != $rel_id) {
	 			return false;
	 		}
	 	}
	 	return $file;
	 }
 /**
	 * { delete filed item }
	 *
	 * @param        $id     The identifier
	 *
	 * @return     boolean  
	 */
 public function delete_file_item($id,$type)
 {
 	$attachment = $this->get_item_attachments('', $id);
 	$deleted    = false;
 	if ($attachment) {
 		if (empty($attachment->external)) {
 			unlink(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'.$type.'/'. $attachment->rel_id . '/' . $attachment->file_name);
 		}
 		$this->db->where('id', $attachment->id);
 		$this->db->delete('tblfiles');
 		if ($this->db->affected_rows() > 0) {
 			$deleted = true;
 		}

 		if (is_dir(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'.$type.'/'. $attachment->rel_id)) {
				// Check if no attachments left, so we can delete the folder also
 			$other_attachments = list_files(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'.$type.'/'. $attachment->rel_id);
 			if (count($other_attachments) == 0) {
					// okey only index.html so we can delete the folder also
 				delete_dir(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'.$type.'/'. $attachment->rel_id);
 			}
 		}
 	}
 	return $deleted;
 }

	/**
	 * Gets the item attachments.
	 *
	 * @param  $assets  The assets
	 * @param  string  $id The identifier
	 *
	 * @return      The item attachments.
	 */
	public function get_item_attachments($assets, $id = '')
	{
		// If is passed id get return only 1 attachment
		if (is_numeric($id)) {
			$this->db->where('id', $id);
		}
		$result = $this->db->get('tblfiles');
		if (is_numeric($id)) {
			return $result->row();
		}
		return $result->result_array();
	}

	/**
	 * get image items
	 * @param  integer $item_id 
	 * @return integer          
	 */
	public function get_image_items($item_id, $type){
		$file_path  = site_url('modules/fixed_equipment/assets/images/no_image.jpg');
		$data_file = $this->get_image_file_name($item_id, $type);
		if($data_file){
			if($data_file->file_name!=''){
				$file_path  = site_url(FIXED_EQUIPMENT_IMAGE_UPLOADED_PATH.$type.'/'.$item_id.'/'.$data_file->file_name);
			}
		}
		return $file_path;
	}

	 /**
	 * get image file name
	 * @param   int $id 
	 * @param   string $type 
	 * @return  object   
	 */
	 public function get_image_file_name($id, $type){
	 	$this->db->where('rel_id',$id);
	 	$this->db->where('rel_type', $type);
	 	$this->db->select('file_name');
	 	return $this->db->get(db_prefix().'files')->row();
	 }

	/**
	 * add suppliers
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_suppliers($data){
		$data['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_suppliers', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update suppliers
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_suppliers($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_suppliers', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * delete suppliers
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_suppliers($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_suppliers');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * get suppliers
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_suppliers($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_suppliers')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_suppliers')->result_array();
		}
	}
/**
	 * add asset_manufacturers
	 * @param array $data 
	 * @return integer $insert id 
	 */
public function add_asset_manufacturers($data){
	$data['creator_id'] = get_staff_user_id();
	$this->db->insert(db_prefix().'fe_asset_manufacturers', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		return $insert_id;
	}
	return 0;
}
	/**
	 * update asset_manufacturers
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_asset_manufacturers($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_asset_manufacturers', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * delete asset_manufacturers
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_asset_manufacturers($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_asset_manufacturers');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	
	/**
	 * get asset_manufacturers
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_asset_manufacturers($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_asset_manufacturers')->row();
		}
		else{
			if(!is_admin() && has_permission('fixed_equipment_setting_manufacturer', '', 'view_own')){
				$this->db->where('creator_id', get_staff_user_id());
			}
			return $this->db->get(db_prefix().'fe_asset_manufacturers')->result_array();
		}
	}

	/**
	 * add categories
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_categories($data){
		$data['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_categories', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update categories
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_categories($data){
		if(!isset($data['primary_default_eula'])){
			$data['primary_default_eula'] = 0;
		}
		if(!isset($data['confirm_acceptance'])){
			$data['confirm_acceptance'] = 0;
		}
		if(!isset($data['send_mail_to_user'])){
			$data['send_mail_to_user'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_categories', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * delete categories
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_categories($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_categories');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get categories
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_categories($id = '', $type = ''){
		if($type != ''){
			$this->db->where('type', $type);
		}
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_categories')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_categories')->result_array();
		}
	}

	/**
	 * add models
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_models($data){
		if(isset($data['model_name'])){
			$data_add['model_name'] = $data['model_name'];
		}
		if(isset($data['manufacturer'])){
			$data_add['manufacturer'] = $data['manufacturer'];
		}
		if(isset($data['category'])){
			$data_add['category'] = $data['category'];
		}
		if(isset($data['model_no'])){
			$data_add['model_no'] = $data['model_no'];
		}
		if(isset($data['depreciation'])){
			$data_add['depreciation'] = $data['depreciation'];
		}
		if(isset($data['eol'])){
			$data_add['eol'] = $data['eol'];
		}
		if(isset($data['note'])){
			$data_add['note'] = $data['note'];
		}
		if(isset($data['may_request'])){
			$data_add['may_request'] = $data['may_request'];
		}
		if(isset($data['fieldset_id'])){
			$data_add['fieldset_id'] = $data['fieldset_id'];
		}
		$data_add['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_models', $data_add);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			if (isset($data['custom_fields'])) {
				$custom_fields = $data['custom_fields'];
				handle_custom_fields_post($insert_id, $custom_fields);
			}				
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update models
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_models($data){
		if(isset($data['model_name'])){
			$data_update['model_name'] = $data['model_name'];
		}
		if(isset($data['manufacturer'])){
			$data_update['manufacturer'] = $data['manufacturer'];
		}
		if(isset($data['category'])){
			$data_update['category'] = $data['category'];
		}
		if(isset($data['model_no'])){
			$data_update['model_no'] = $data['model_no'];
		}
		if(isset($data['depreciation'])){
			$data_update['depreciation'] = $data['depreciation'];
		}
		if(isset($data['eol'])){
			$data_update['eol'] = $data['eol'];
		}
		if(isset($data['note'])){
			$data_update['note'] = $data['note'];
		}
		if(isset($data['may_request'])){
			$data_update['may_request'] = $data['may_request'];
		}
		else{
			$data_update['may_request'] = 0;
		}
		if(isset($data['fieldset_id'])){
			$data_update['fieldset_id'] = $data['fieldset_id'];
		}
		$affectedRows = 0;
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_models', $data_update);
		if($this->db->affected_rows() > 0) {
			$affectedRows++;
		}
		if (isset($data['custom_fields'])) {
			$custom_fields = $data['custom_fields'];
			if (handle_custom_fields_post($data['id'], $custom_fields)) {
				$affectedRows++;
			}
		}
		if($affectedRows != 0){
			return true;
		}
		return false;
	}

	/**
	 * delete models
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_models($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_models');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get models
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_models($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_models')->row();
		}
		else{
			if(!is_admin() && has_permission('fixed_equipment_setting_model', '', 'view_own')){
				$this->db->where('creator_id', get_staff_user_id());
			}
			$this->db->order_by('model_name', 'ASC');
			return $this->db->get(db_prefix().'fe_models')->result_array();
		}
	}

/**
 * get custom field models
 * @param  integer $model_id 
 * @return array           
 */
public function get_custom_field_models($model_id){
	return $this->db->query('select a.id, fieldid, b.name, b.slug, a.value from '.db_prefix().'customfieldsvalues a left join '.db_prefix().'customfields b on a.fieldid = b.id where relid = '.$model_id.' and a.fieldto = "fixed_equipment" and active = 1')->result_array();
}

	/**
	 * add status_labels
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_status_labels($data){
		$data['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_status_labels', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}

	/**
	 * update status_labels
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_status_labels($data){
		if(!isset($data['default_label'])){
			$data['default_label'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_status_labels', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * delete status_labels
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_status_labels($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_status_labels');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get status_labels
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_status_labels($id = '', $status_type = ''){
		if($status_type != ''){
			$this->db->where('status_type', $status_type);
		}
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_status_labels')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_status_labels')->result_array();
		}
	}
/**
 * add asset
 * @param array $data 
 */
public function add_asset($data){
	$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
	$data['date_buy'] = fe_format_date($data['date_buy']);
	$list_serial = [];
	$list_insert_id = [];
	if(isset($data['serial'])){
		$list_serial = $data['serial'];
		$alocation_name = '--';
		$supplier_name_s = '--';
		$model_name_s = '--';
		$model_no_s = '--';
		if(isset($data['asset_location']) && $data['asset_location'] != ''){
			$data_alocation = $this->get_locations($data['asset_location']);
			if($data_alocation){
				$alocation_name = $data_alocation->location_name;
			}
		}
		if(isset($data['supplier_id']) && $data['supplier_id'] != ''){
			$data_supplier = $this->get_suppliers($data['supplier_id']);
			if($data_supplier){
				$supplier_name_s = $data_supplier->supplier_name;
			}
		}
		if(isset($data['model_id']) && $data['model_id'] != ''){
			$data_model = $this->get_models($data['model_id']);
			if($data_model){
				$model_name_s = $data_model->model_name;
				$model_no_s = $data_model->model_no;
			}
		}
		$asset_name_s = (($data['assets_name'] == '') ? $model_name_s : $data['assets_name']);
		$tempDir = FIXED_EQUIPMENT_PATH.'qrcodes/';
		foreach ($list_serial as $key => $serial) {
			if($serial != ''){
				$qr_code = md5($serial);              
				$html = '';
				$html .= "\n"._l('fe_asset_name').': '.$asset_name_s."\n";
				$html .= _l('fe_asset_tag').': '.$serial."\n";
				$html .= _l('fe_models').': '.$model_name_s."\n";
				$html .= _l('fe_model_no').': '.$model_no_s."\n";
				$html .= 'QR code : '.$qr_code."\n";
				$html .= _l('fe_locations').': '.$alocation_name."\n";
				$html .= _l('fe_purchase_date').': '.$data['date_buy']."\n";
				$html .= _l('fe_purchase_cost').': '.$data['unit_price']."\n";
				$html .= _l('fe_warranty').': '.$data['warranty_period']."\n";
				$html .= _l('fe_supplier').': '.$supplier_name_s."\n";
				$codeContents = $html;
				$fileName = $qr_code;
				$pngAbsoluteFilePath = $tempDir.$fileName;
				$urlRelativeFilePath = $tempDir.$fileName;
				if (!file_exists($pngAbsoluteFilePath)) {
					QRcode::png($codeContents, $pngAbsoluteFilePath.'.png', "L", 4, 4);
				} 

				$data_add['assets_name'] = $asset_name_s;
				$data_add['model_id'] = $data['model_id'];
				$data_add['status'] = $data['status'];
				$data_add['supplier_id'] = $data['supplier_id'];
				$data_add['date_buy'] = $data['date_buy'];
				$data_add['order_number'] = $data['order_number'];
				$data_add['unit_price'] = $data['unit_price'];
				$data_add['asset_location'] = $data['asset_location'];
				$data_add['location_id'] = $data['asset_location'];
				$data_add['warranty_period'] = $data['warranty_period'];
				$data_add['description'] = $data['description'];

				if(isset($data['for_rent']) && $data['for_rent'] == 1){
					$data_add['for_rent'] = 1;
					$data_add['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
					$data_add['renting_period'] = $data['renting_period'];
					$data_add['renting_unit'] = $data['renting_unit'];
				}
				else{
					$data_add['for_rent'] = 0;
					$data_add['rental_price'] = 0;
					$data_add['renting_period'] = '';
					$data_add['renting_unit'] = '';
				}

				if(isset($data['for_sell']) && $data['for_sell'] == 1){
					$data_add['for_sell'] = 1;
					$data_add['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
				}
				else{
					$data_add['for_sell'] = 0;
					$data_add['selling_price'] = 0;
				}
				$data_add['qr_code'] = $qr_code;
				$data_add['series'] = $serial;
				$data_add['requestable'] = isset($data['requestable']) ? $data['requestable'] : 0;

				$this->db->insert(db_prefix() . 'fe_assets',$data_add);
				$insert_id = $this->db->insert_id();
				if($insert_id){
					$this->add_log(get_staff_user_id(), 'create_new', $insert_id, '', '', '', null, '');
					if(isset($data['customfield'])){
						foreach ($data['customfield'] as $customfield_id => $field_value) {
							$value = (is_array($field_value) ? json_encode($field_value) : $field_value);
							$data_customfield = $this->get_custom_fields($customfield_id);
							if($data_customfield){
								$data_insert['title'] = $data_customfield->title;
								$data_insert['type'] = $data_customfield->type;
								$data_insert['option'] = $data_customfield->option;
								$data_insert['required'] = $data_customfield->required;
								$data_insert['value'] = $value;
								$data_insert['fieldset_id'] = $data_customfield->fieldset_id;
								$data_insert['custom_field_id'] = $data_customfield->id;
								$data_insert['asset_id'] = $insert_id;
								$this->db->insert(db_prefix() . 'fe_custom_field_values',$data_insert);
							}
						}
					}
					$list_insert_id[] = $insert_id;

        			hooks()->do_action('after_fe_asset_added', $insert_id);
				}
			}
		}
	}
	return $list_insert_id;
}
/**
 * update asset
 * @param  array $data 
 * @param  integer $id   
 * @return boolean       
 */
public function update_asset($data, $id){
	$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
	$data['date_buy'] = fe_format_date($data['date_buy']);

	$data_asset = $this->get_assets($id);
	if($data_asset){
		if(is_numeric($data_asset->location_id) && (int)$data_asset->location_id == 0){
			$data_add['location_id'] = $data['asset_location'];
		}
	}

	$list_serial = [];
	$list_insert_id = [];
	$affectedRows = 0;
	if(isset($data['serial'])){
		$list_serial = $data['serial'];
		$alocation_name = '--';
		$supplier_name_s = '--';
		$model_name_s = '--';
		$model_no_s = '--';
		if(isset($data['asset_location']) && $data['asset_location'] != ''){
			$data_alocation = $this->get_locations($data['asset_location']);
			if($data_alocation){
				$alocation_name = $data_alocation->location_name;
			}
		}
		if(isset($data['supplier_id']) && $data['supplier_id'] != ''){
			$data_supplier = $this->get_suppliers($data['supplier_id']);
			if($data_supplier){
				$supplier_name_s = $data_supplier->supplier_name;
			}
		}
		if(isset($data['model_id']) && $data['model_id'] != ''){
			$data_model = $this->get_models($data['model_id']);
			if($data_model){
				$model_name_s = $data_model->model_name;
				$model_no_s = $data_model->model_no;
			}
		}
		$asset_name_s = (($data['assets_name'] == '') ? $model_name_s : $data['assets_name']);
		$tempDir = FIXED_EQUIPMENT_PATH.'qrcodes/';
		foreach ($list_serial as $key => $serial) {
			if($serial != ''){
				$qr_code = md5($serial);              
				$html = '';
				$html .= "\n"._l('fe_asset_name').': '.$asset_name_s."\n";
				$html .= _l('fe_asset_tag').': '.$serial."\n";
				$html .= _l('fe_models').': '.$model_name_s."\n";
				$html .= _l('fe_model_no').': '.$model_no_s."\n";
				$html .= 'QR code : '.$qr_code."\n";
				$html .= _l('fe_locations').': '.$alocation_name."\n";
				$html .= _l('fe_purchase_date').': '.$data['date_buy']."\n";
				$html .= _l('fe_purchase_cost').': '.$data['unit_price']."\n";
				$html .= _l('fe_warranty').': '.$data['warranty_period']."\n";
				$html .= _l('fe_supplier').': '.$supplier_name_s."\n";
				$codeContents = $html;
				$fileName = $qr_code;
				$pngAbsoluteFilePath = $tempDir.$fileName;
				$urlRelativeFilePath = $tempDir.$fileName;
				if (!file_exists($pngAbsoluteFilePath)) {
					QRcode::png($codeContents, $pngAbsoluteFilePath.'.png', "L", 4, 4);
				} 
				$data_add['assets_name'] = $asset_name_s;
				$data_add['model_id'] = $data['model_id'];
				$data_add['status'] = $data['status'];
				$data_add['supplier_id'] = $data['supplier_id'];
				$data_add['date_buy'] = $data['date_buy'];
				$data_add['order_number'] = $data['order_number'];
				$data_add['unit_price'] = $data['unit_price'];
				$data_add['asset_location'] = $data['asset_location'];
				$data_add['warranty_period'] = $data['warranty_period'];
				$data_add['description'] = $data['description'];
				$data_add['qr_code'] = $qr_code;
				$data_add['series'] = $serial;
				$data_add['requestable'] = isset($data['requestable']) ? $data['requestable'] : 0;

				if(isset($data['for_rent']) && $data['for_rent'] == 1){
					$data_add['for_rent'] = 1;
					$data_add['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
					$data_add['renting_period'] = $data['renting_period'];
					$data_add['renting_unit'] = $data['renting_unit'];
				}
				else{
					$data_add['for_rent'] = 0;
					$data_add['rental_price'] = 0;
					$data_add['renting_period'] = '';
					$data_add['renting_unit'] = '';
				}


				if(isset($data['for_sell']) && $data['for_sell'] == 1){
					$data_add['for_sell'] = 1;
					$data_add['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
				}
				else{
					$data_add['for_sell'] = 0;
					$data_add['selling_price'] = 0;
				}

				$old_model_id = '';
				$this->db->where('id', $id);
				$data_saved_assets = $this->db->get(db_prefix() . 'fe_assets')->row();
				if($data_saved_assets && $key == 0){
					$old_model_id = $data_saved_assets->model_id;
					$this->db->where('id', $data_saved_assets->id);
					$this->db->update(db_prefix() . 'fe_assets',$data_add);
					if($this->db->affected_rows() > 0) {
						$change = '';
						if($data_add['status'] != $data_saved_assets->status){
							$status_name1 = '';
							$data_status1 = $this->fixed_equipment_model->get_status_labels($data_saved_assets->status);
							if($data_status1){
								$status_name1 = $data_status1->name;
							}
							$status_name2 = '';
							$data_status2 = $this->fixed_equipment_model->get_status_labels($data_add['status']);
							if($data_status2){
								$status_name2 = $data_status2->name;
							}
							if($status_name1 != '' && $status_name2 != ''){
								$change = _l('fe_status').': '.$status_name1.' &#10145; '.$status_name2;
							}
						}
						$this->add_log(get_staff_user_id(), 'update', $data_saved_assets->id, '', $change, '', null, '');
						$affectedRows++;

        				hooks()->do_action('after_fe_asset_updated', $data_saved_assets->id);
					}
					// Custom field
					if($old_model_id != '' && ($data['model_id'] == $old_model_id)){
						foreach ($data['customfield'] as $customfield_id => $field_value) {
							$value = (is_array($field_value) ? json_encode($field_value) : $field_value);
							$this->db->where('asset_id', $id);
							$this->db->where('custom_field_id', $customfield_id);
							$data_customfield = $this->db->get(db_prefix().'fe_custom_field_values')->row();
							if($data_customfield){
								$this->db->where('id', $data_customfield->id);
								$this->db->update(db_prefix() . 'fe_custom_field_values', ['value' => $value]);
								if($this->db->affected_rows() > 0) {
									$affectedRows++;
								}
							}
						}
					}
					else{
						// If change model -> delete old custom field and add new custom field
						$this->db->where('asset_id', $id);
						$this->db->delete(db_prefix().'fe_custom_field_values');
						if($this->db->affected_rows() > 0) {
							if(isset($data['customfield'])){
								foreach ($data['customfield'] as $customfield_id => $field_value) {
									$value = (is_array($field_value) ? json_encode($field_value) : $field_value);
									$data_customfield = $this->get_custom_fields($customfield_id);
									if($data_customfield){
										$data_insert['title'] = $data_customfield->title;
										$data_insert['type'] = $data_customfield->type;
										$data_insert['option'] = $data_customfield->option;
										$data_insert['required'] = $data_customfield->required;
										$data_insert['value'] = $value;
										$data_insert['fieldset_id'] = $data_customfield->fieldset_id;
										$data_insert['custom_field_id'] = $data_customfield->id;
										$data_insert['asset_id'] = $id;
										$this->db->insert(db_prefix() . 'fe_custom_field_values',$data_insert);
										if($this->db->insert_id() > 0) {
											$affectedRows++;
										}
									}
								}
							}
						}
					}
						// Custom field

				}
				else{
					$this->db->insert(db_prefix() . 'fe_assets',$data_add);
					$insert_id = $this->db->insert_id();
					if($insert_id){
						$this->add_log(get_staff_user_id(), 'create_new', $insert_id, '', '', '', null, '');
						if(isset($data['customfield'])){
							foreach ($data['customfield'] as $customfield_id => $field_value) {
								$value = (is_array($field_value) ? json_encode($field_value) : $field_value);
								$data_customfield = $this->get_custom_fields($customfield_id);
								if($data_customfield){
									$data_insert['title'] = $data_customfield->title;
									$data_insert['type'] = $data_customfield->type;
									$data_insert['option'] = $data_customfield->option;
									$data_insert['required'] = $data_customfield->required;
									$data_insert['value'] = $value;
									$data_insert['fieldset_id'] = $data_customfield->fieldset_id;
									$data_insert['custom_field_id'] = $data_customfield->id;
									$data_insert['asset_id'] = $insert_id;
									$this->db->insert(db_prefix() . 'fe_custom_field_values',$data_insert);
									if($this->db->insert_id() > 0) {
										$affectedRows++;
									}
								}
							}
						}
						$affectedRows++;

        				hooks()->do_action('after_fe_asset_updated_v2', $insert_id);
					}
				}
			}
		}
	}

	if($affectedRows != 0){
		return true;				
	}
	else{
		return false;
	}
}
/**
 * delete assets
 * @param  integer $id 
 * @return integer     
 */
public function delete_assets($id){
	// $this->db->where('rel_id', $id);
	// $this->db->where('rel_type', 'assets');
	// $attachments = $this->db->get(db_prefix().'files')->result_array();
	// foreach ($attachments as $attachment) {
	// 	$this->delete_assets_attachment($attachment['id']);
	// }
	$this->db->where('id', $id);
	$this->db->update(db_prefix() . 'fe_assets', ['active' => 0]);
	if ($this->db->affected_rows() > 0) {
		// $this->delete_history_assets($id);
		// $this->delete_checkin_out_assets($id);

        hooks()->do_action('after_fe_asset_deleted', $id);

		return true;
	}

	return false;
}
/**
 * delete assets attachment
 * @param  integer $id 
 * @return integer     
 */
public function delete_assets_attachment($id)
{
	$attachment = $this->get_assets_attachments('assets', $id);
	$deleted    = false;
	if ($attachment) {
		if (empty($attachment->external)) {
			unlink(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'. $attachment->rel_id . '/' . $attachment->file_name);
		}
		$this->db->where('id', $attachment->id);
		$this->db->delete(''.db_prefix().'files');
		if ($this->db->affected_rows() > 0) {
			$deleted = true;
		}

		if (is_dir(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'. $attachment->rel_id)) {
			$other_attachments = list_files(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'. $attachment->rel_id);
			if (count($other_attachments) == 0) {
				delete_dir(FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER .'/'. $attachment->rel_id);
			}
		}
	}
	return $deleted;
}
/**
 * get assets attachments
 * @param  string $type   
 * @param  integer $assets 
 * @param  integer $id     
 */
public function get_assets_attachments($type, $assets, $id = '')
{
	// If is passed id get return only 1 attachment
	if (is_numeric($id)) {
		$this->db->where('id', $id);
	} else {
		$this->db->where('rel_id', $assets);
	}
	$this->db->where('rel_type', $type);
	$result = $this->db->get(''.db_prefix().'files');
	if (is_numeric($id)) {
		return $result->row();
	}
	return $result->result_array();
}
	/**
	 * get assets
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_assets($id = '', $type = '', $checkin = false, $requestable = false, $status = '', $exclude_active = false, $model_id = '', $quantity = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_assets')->row();
		}
		else{
			$this->db->select('*, '.db_prefix().'fe_assets.id as id');
			if($status != ''){
				$this->db->join(db_prefix().'fe_status_labels', db_prefix().'fe_status_labels.id = '.db_prefix().'fe_assets.status', 'left');
				$this->db->where(db_prefix().'fe_status_labels.status_type', $status);
			}
			if($type != ''){
				$this->db->where('type', $type);
			}
			if($checkin == true){
				$this->db->where('checkin_out', 1);
			}
			if($requestable == true){
				$this->db->where('requestable', 1);
			}
			if(!$exclude_active){
				$this->db->where('active', 1);
			}
			if(is_numeric($model_id)){
				$this->db->where('model_id', $model_id);
			}
			if(is_numeric($quantity)){
				$this->db->limit((int)$quantity);
			}
			return $this->db->get(db_prefix().'fe_assets')->result_array();
		}
	}
/**
 * check exist serial
 * @param  string $serial   
 * @param  string $asset_id 
 * @return object           
 */

public function check_exist_serial($serial, $asset_id = '')
{
	$query = '';
	if($asset_id != ''){
		$query = ' and id != '.$asset_id.'';
	}
	return $this->db->query('select * from '.db_prefix().'fe_assets where active = 1 and series = \''.$serial.'\''.$query)->row();
}

/**
 * check in assets
 * @param  array $data 
 * @return array       
 */
public function check_in_assets($data){
	if(isset($data['checkin_date'])){
		$data['checkin_date'] = fe_format_date($data['checkin_date']);
	}

	if(isset($data['expected_checkin_date'])){
		$data['expected_checkin_date'] = fe_format_date($data['expected_checkin_date']);
	}

	if($data['type'] == 'checkin'){
		$data['check_status'] = 1;
	}
	else{
		if($data['checkout_to'] == 'asset'){
			$data['staff_id'] = $this->get_manager_asset($data['asset_id']);
		}
		elseif($data['checkout_to'] == 'location'){
			$data['staff_id'] = $this->get_manager_location($data['location_id']);
		}
	}
	$data['item_type'] = 'asset';
	$this->db->insert(db_prefix().'fe_checkin_assets', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		//Get checkout id to update status after checkin
		$checkin_out_id = '';
		$data_assets = $this->get_assets($data['item_id']);
		if($data_assets){
			$checkin_out_id = $data_assets->checkin_out_id;	
			// If has select location
			if(isset($data['location_id']) && $data['location_id'] != ''){
				$data_asset['location_id'] = $data['location_id'];
			}
			else{
				// Not select location then using default location
				$data_asset['location_id'] = $data_assets->asset_location;	
				// If is check out and check out to asset then get location of this asset	
				if((isset($data['type']) && $data['type'] == 'checkout')){

					if((isset($data['checkout_to']) && $data['checkout_to'] == 'asset') && (isset($data['asset_id']) && is_numeric($data['asset_id']))) {
						$data_asset_checkout = $this->get_assets($data['asset_id']);
						if(is_numeric($data_asset_checkout->location_id) && $data_asset_checkout->location_id > 0){
							$data_asset['location_id'] = $data_asset_checkout->location_id;
							if(!is_numeric($data_asset['location_id']) && ($data_asset['location_id'] == 0 || $data_asset['location_id'] == null || $data_asset['location_id'] == '')){
								if(is_numeric($data_asset_checkout->asset_location) && $data_asset_checkout->asset_location > 0){
									$data_asset['location_id'] = $data_asset_checkout->asset_location;
								}
							}
						}
					}
				}	
			}	
		}

		if(isset($data['asset_name']) && $data['asset_name'] != ''){
			$data_asset['assets_name'] = $data['asset_name'];
		}
		$checkin_out = 1;
		if($data['type'] == 'checkout'){
			$checkin_out = 2;
		}
		$data_asset['checkin_out'] = $checkin_out;
		$data_asset['checkin_out_id'] = $insert_id;
		$data_asset['status'] = $data['status'];
		$this->db->where('id', $data['item_id']);
		$this->db->update(db_prefix().'fe_assets', $data_asset);
		if($data['type'] == 'checkout'){
			$to_id = '';
			switch ($data['checkout_to']) {
				case 'user':
				$to_id = $data['staff_id'];
				break;
				case 'asset':
				$to_id = $data['asset_id'];
				$this->update_location_for_checkout_to_asset($data['item_id'], $data_asset['location_id']);
				break;
				case 'location':
				$to_id = $data['location_id'];
				$this->update_location_for_checkout_to_asset($data['item_id'], $data_asset['location_id']);
				break;
				case 'project':
				$to_id = $data['project_id'];
				break;
			}
			// Add log checkout
			$this->add_log(get_staff_user_id(), $data['type'], $data['item_id'], '', '', $data['checkout_to'], $to_id, $data['notes']);
		}
		elseif($data['type'] == 'checkin'){
			$to_id = '';
			$to = '';

			$data_checkout = '';
			if($checkin_out_id == '' || $checkin_out_id == null){
				$data_checkout = $this->db->query('select * from '.db_prefix().'fe_checkin_assets where item_id = '.$data['item_id'].' and (type="checkout" OR type="request") order by date_creator desc limit 0,1')->row();
			}
			else{
				$data_checkout = $this->get_checkin_out_data($checkin_out_id);
			}

			if($data_checkout != ''){
				// Update status of checkout when checkin
				$this->db->where('id', $data_checkout->id);
				$this->db->update(db_prefix().'fe_checkin_assets', ['check_status' => 1]);
				//
				$to_id = '';
				$to = $data_checkout->checkout_to;
				switch ($to) {
					case 'user':
					$to_id = $data_checkout->staff_id;
					break;
					case 'asset':
					$to_id = $data_checkout->asset_id;
					$this->update_location_for_checkout_to_asset($data_checkout->item_id, $data_asset['location_id']);
					break;
					case 'location':
					$to_id = $data_checkout->location_id;
					$this->update_location_for_checkout_to_asset($data_checkout->item_id, $data_asset['location_id']);
					break;
					case 'project':
					$to_id = $data['project_id'];
					break;
				}
				$this->db->where('id', $insert_id);
				$this->db->update(db_prefix().'fe_checkin_assets', ['staff_id' => $data_checkout->staff_id]);
			}
			// Add log checkin
			$this->add_log(get_staff_user_id(), $data['type'], $data['item_id'], '', '', $to, $to_id, $data['notes']);
		}
		return $insert_id;
	}
	return 0;
}
/**
 * add log
 * @param string $admin_id 
 * @param string $action   
 * @param string $item_id  
 * @param string $target   
 * @param string $changed  
 * @param string $to       
 * @param string $to_id    
 * @param string $notes    
 */
public function add_log($admin_id = '', $action = '', $item_id = '', $target = '', $changed = '', $to = '',$to_id = '',$notes = ''){
	$data['admin_id'] = $admin_id;
	$data['action'] = $action;
	$data['item_id'] = $item_id;
	$data['target'] = $target;
	$data['changed'] = $changed;
	$data['to'] = $to;
	$data['to_id'] = $to_id;
	$data['notes'] = $notes;
	$this->db->insert(db_prefix().'fe_log_assets', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		return $insert_id;
	}
	return 0;
}

/**
 * count log detail
 * @param  integer $item_id 
 * @param  string $action  
 * @param  integer $requestable  
 * @return integer          
 */
public function count_log_detail($item_id = '', $type = '', $requestable = '', $request_status = ''){
	if($item_id != ''){
		$this->db->where('item_id', $item_id);
	}
	if($type != ''){
		$this->db->where('type', $type);
	}
	if(is_numeric($requestable)){
		$this->db->where('requestable', $requestable);
	}
	if(is_numeric($request_status)){
		$this->db->where('request_status', $request_status);
	}
	return $this->db->get(db_prefix().'fe_checkin_assets')->num_rows();
}

/**
 * get last checkin out assets
 * @param  integer $asset_id 
 * @param  string $type     
 * @return object           
 */
public function get_last_checkin_out_assets($asset_id, $type = 'checkin'){
	return $this->db->query('select * from '.db_prefix().'fe_checkin_assets where item_id = '.$asset_id.' and type = "'.$type.'" order by date_creator desc limit 0,1')->row();
}
/**
 * add licenses
 * @param array $data 
 */
public function add_licenses($data){
	$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
	$data['date_buy'] = (isset($data['date_buy']) || $data['date_buy'] != '') ? fe_format_date($data['date_buy']) : null;
	$data['expiration_date'] = (isset($data['expiration_date']) || $data['expiration_date'] != '') ? fe_format_date($data['expiration_date']) : null;
	$data['termination_date'] = (isset($data['termination_date']) || $data['termination_date'] != '') ? fe_format_date($data['termination_date']) : null;

	if(isset($data['for_rent']) && $data['for_rent'] == 1){
		$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
	}
	else{
		$data['for_rent'] = 0;
		$data['rental_price'] = 0;
		$data['renting_period'] = '';
		$data['renting_unit'] = '';
	}

	if(isset($data['for_sell']) && $data['for_sell'] == 1){
		$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
	}
	else{
		$data['for_sell'] = 0;
		$data['selling_price'] = 0;
	}

	$this->db->insert(db_prefix() . 'fe_assets',$data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		for($i = 1; $i <= $data['seats']; $i++){
			$data_seats['seat_name'] = 'Seat '.$i;
			$data_seats['to'] = '';
			$data_seats['to_id'] = '';
			$data_seats['license_id'] = $insert_id;
			$this->db->insert(db_prefix() . 'fe_seats',$data_seats);
		}

        hooks()->do_action('after_fe_license_added', $insert_id);
	}
	return $insert_id;
}
/**
 * update licenses
 * @param  array $data 
 */
public function update_licenses($data){
	$data_all_seat = $this->get_seat_by_parent($data['id']);
	$data_avail_seat = $this->get_seat_by_parent($data['id'], 1);
	$total_all = count($data_all_seat);
	$total_avail = count($data_avail_seat);
	if($data['seats'] > $total_all){
		// Aditional seat
		$identity = $total_all + 1;
		$remain = $data['seats'] - $total_all;
		for($i = 1; $i <= $remain; $i++){
			$data_seats['seat_name'] = 'Seat '.$identity;
			$data_seats['to'] = '';
			$data_seats['to_id'] = '';
			$data_seats['license_id'] = $data['id'];
			$this->db->insert(db_prefix() . 'fe_seats',$data_seats);
			$identity++;
		}
	}
	if($data['seats'] < $total_all){
		// Remove seat
		$remain = $total_all - $data['seats'];
		if($remain > $total_avail){
			return 3;
		}
		else{
			foreach ($data_avail_seat as $key => $value) {
				$this->db->where('id', $value['id']);
				$this->db->delete(db_prefix() . 'fe_seats');
				if ($this->db->affected_rows() > 0) {
					$this->db->where('item_id', $value['id']);
					$this->db->delete(db_prefix().'fe_checkin_assets');
				}
				if(($key+1) == $remain){
					break;
				}
			}
		}
	}
	$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
	$data['date_buy'] = (isset($data['date_buy']) || $data['date_buy'] != '') ? fe_format_date($data['date_buy']) : null;
	$data['expiration_date'] = (isset($data['expiration_date']) || $data['expiration_date'] != '') ? fe_format_date($data['expiration_date']) : null;
	$data['termination_date'] = (isset($data['termination_date']) || $data['termination_date'] != '') ? fe_format_date($data['termination_date']) : null;
	if(isset($data['reassignable'])){
		$data['reassignable'] = $data['reassignable'];
	}
	else{
		$data['reassignable'] = 0;
	}
	if(isset($data['maintained'])){
		$data['maintained'] = $data['maintained'];
	}
	else{
		$data['maintained'] = 0;
	}

	if(isset($data['for_rent']) && $data['for_rent'] == 1){
		$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
	}
	else{
		$data['for_rent'] = 0;
		$data['rental_price'] = 0;
		$data['renting_period'] = '';
		$data['renting_unit'] = '';
	}

	if(isset($data['for_sell']) && $data['for_sell'] == 1){
		$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
	}
	else{
		$data['for_sell'] = 0;
		$data['selling_price'] = 0;
	}

	$this->db->where('id', $data['id']);
	$this->db->update(db_prefix() . 'fe_assets',$data);
	if($this->db->affected_rows() > 0) {
        hooks()->do_action('after_fe_license_updated', $data['id']);

		return 1;
	}
	return 2;
}
/**
 * delete licenses
 * @param  integer $id 
 * @return boolean     
 */
public function delete_licenses($id){
	$this->db->where('id', $id);
	$this->db->delete(db_prefix() . 'fe_assets');
	if ($this->db->affected_rows() > 0) {
		$this->delete_history_assets($id);
		$this->delete_checkin_out_assets($id);
		$this->delete_seats($id);

        hooks()->do_action('after_fe_license_deleted', $id);

		return true;
	}
	return false;
}


/**
 * delete checkin out assets
 * @param  integer $item_id 
 * @return integer          
 */
public function delete_history_assets($item_id){
	$this->db->where('item_id', $item_id);
	$this->db->delete(db_prefix() . 'fe_log_assets');
	if ($this->db->affected_rows() > 0) {
		return true;
	}
	return false;
}

/**
 * delete checkin out assets
 * @param  integer $item_id 
 * @return integer          
 */
public function delete_checkin_out_assets($item_id){
	$this->db->where('item_id', $item_id);
	$this->db->delete(db_prefix() . 'fe_checkin_assets');
	if ($this->db->affected_rows() > 0) {
		return true;
	}
	return false;
}
/**
 * delete seats
 * @param  integer $license_id 
 * @return boolean             
 */
public function delete_seats($license_id){
	$this->db->where('license_id', $license_id);
	$this->db->delete(db_prefix() . 'fe_seats');
	if ($this->db->affected_rows() > 0) {
		return true;
	}
	return false;
}
/**
 * check in licenses
 * @param  array $data 
 * @return array       
 */
public function check_in_licenses($data){
	if(isset($data['checkin_date'])){
		$data['checkin_date'] = fe_format_date($data['checkin_date']);
	}
	$data['item_type'] = 'license';

	if($data['type'] == 'checkout'){
		if($data['checkout_to'] == 'asset'){
			$data['staff_id'] = $this->get_manager_asset($data['asset_id']);
		}
	}
	$this->db->insert(db_prefix().'fe_checkin_assets', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		$to = '';
		$to_id = '';
		if(isset($data['checkout_to'])){
			$to = $data['checkout_to'];
			switch ($to) {
				case 'user':
				$to_id = $data['staff_id'];
				break;
				case 'asset':
				$to_id = $data['asset_id'];
				break;
				case 'customer':
				$to_id = $data['customer_id'];
				break;
			}
		}

		$check_status = 1;
		if($data['type'] == 'checkout'){
			$check_status = 2;
		}
		// --- Upadate status Seat table
		$this->db->where('id', $data['item_id']);
		$this->db->update(db_prefix().'fe_seats', [
			'status' => $check_status,
			'to' => $to,
			'to_id' => $to_id
		]);
		// --- End upadate status Seat table

		// --- Upadate status Assets table if all item in Seat table same status
		$asset_id = '';
		$data_seat = $this->get_seats($data['item_id']);
		if($data_seat){
			$asset_id = $data_seat->license_id;
			$full_status = $this->check_full_checkin_out($asset_id, $check_status);
			if($full_status){
				$this->db->where('id', $asset_id);
				$this->db->update(db_prefix().'fe_assets', [
					'checkin_out' => $check_status
				]);
			}
		}
		// --- End upadate status Assets table

		// ---  Add log
		if($asset_id != ''){
			if($data['type'] == 'checkout'){
				$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);
			}
			elseif($data['type'] == 'checkin'){
				$data_checkout = $this->db->query('select * from '.db_prefix().'fe_log_assets where item_id = '.$asset_id.' and action="checkout" order by date_creator desc limit 0,1')->row();
				if($data_checkout){
					$to_id = $data_checkout->to_id;
					$to = $data_checkout->to;
				}
				$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);

				$data_checkout = $this->db->query('select * from '.db_prefix().'fe_checkin_assets where item_id = '.$data['item_id'].' and (type="checkout" OR type="request") order by date_creator desc limit 0,1')->row();
				if($data_checkout){
					$this->db->where('id', $insert_id);
					$this->db->update(db_prefix().'fe_checkin_assets', ['staff_id' => $data_checkout->staff_id]);
				}
			}
		}
		// ---  End add log
		return $insert_id;
	}
	return 0;
}
/**
 * get seats
 * @param  integer $id 
 * @return integer     
 */
public function get_seats($id){
	if($id != ''){
		$this->db->where('id', $id);
		return $this->db->get(db_prefix().'fe_seats')->row();
	}
	else{
		return $this->db->get(db_prefix().'fe_seats')->result_array();
	}
}
/**
 * check full checkin out
 * @param  integer  $license_id 
 * @param  integer $status     
 * @return boolean              
 */
public function check_full_checkin_out($license_id, $status = 1){
	$this->db->where('license_id', $license_id);
	$data = $this->db->get(db_prefix().'fe_seats')->result_array();
	if($data && is_array($data)){
		$count_total = count($data);
		$count_effect = 0;
		foreach ($data as $key => $value) {
			if($value['status'] == $status){
				$count_effect++;
			}
		}
		return ($count_total == $count_effect);
	}
	return false;
}
/**
 * count total avail seat
 * @param  integer $license_id 
 * @return object             
 */
public function count_total_avail_seat($license_id){
	$obj = new stdClass();
	$obj->total = 0;
	$obj->avail = 0;
	$this->db->where('license_id', $license_id);
	$data = $this->db->get(db_prefix().'fe_seats')->result_array();
	if($data && is_array($data)){
		$count_total = count($data);
		$count_effect = 0;
		foreach ($data as $key => $value) {
			if($value['status'] == 1){
				$count_effect++;
			}
		}
		$obj->total = $count_total;
		$obj->avail = $count_effect;
	}
	return $obj;
}

/**
 * check in license auto
 * @param  array $data 
 * @return integter       
 */
public function check_in_license_auto($data, $warehouse_id = ''){
	$result = 0;
	if(isset($data['id']) && $data['id'] != ''){
		$id = $data['id'];
		unset($data['id']);
		if(is_numeric($warehouse_id) && $warehouse_id > 0){
			$this->db->where('warehouse_id', $warehouse_id);
		}
		if($data['type'] == 'checkin'){
			$this->db->where('status', 2);
			$this->db->order_by('id', 'desc');
			$this->db->where('license_id', $id);
		}
		else{
			$this->db->where('status', 1);
			$this->db->order_by('id', 'desc');
			$this->db->where('license_id', $id);
		}
		$data_seat = $this->db->get(db_prefix().'fe_seats')->row();
		if($data_seat){
			$data['item_id'] = $data_seat->id;
		}
		if(isset($data['item_id']) && $data['item_id'] != ''){
			$result = $this->check_in_licenses($data);
		}
	}
	return $result;
}
/**
 * get seat by parent
 * @param  integer $license_id 
 * @return array object             
 */
public function get_seat_by_parent($license_id, $status = ''){
	$this->db->where('license_id', $license_id);
	if($status != ''){
		$this->db->where('status', $status);		
	}
	$this->db->order_by('id', 'desc');		
	return $this->db->get(db_prefix().'fe_seats')->result_array();
}

	/**
	 * add accessories
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_accessories($data){
		$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
		$data['date_buy'] = fe_format_date($data['date_buy']);
		if(isset($data['for_rent']) && $data['for_rent'] == 1){
			$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
		}
		else{
			$data['for_rent'] = 0;
			$data['rental_price'] = 0;
		}

		if(isset($data['for_sell']) && $data['for_sell'] == 1){
			$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
		}
		else{
			$data['for_sell'] = 0;
			$data['selling_price'] = 0;
		}
		$this->db->insert(db_prefix().'fe_assets', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update accessories
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_accessories($data){
		if(isset($data['quantity'])){
			$total_checkout = $this->count_checkin_asset_by_parents($data['id']);
			$data_assets = $this->get_assets($data['id']);
			if($data_assets){
				if($data['quantity'] == 0){
					// Quantity not valid
					return 1;
				}
				if($data['quantity'] < $data_assets->quantity){
					$delete = $data_assets->quantity - $data['quantity'];
					$remain = $data_assets->quantity - $total_checkout;
					if($delete > $remain){
					// Quantity not valid (Not smaller than valid quantity)
						return 1;
					}
				}
			}
			else{
				// This accessory not exist
				return 2;
			}
		}
		else{
			// Quantity is unknown
			return 3;
		}
		$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
		$data['date_buy'] = fe_format_date($data['date_buy']);
		if(isset($data['for_rent']) && $data['for_rent'] == 1){
			$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
		}
		else{
			$data['for_rent'] = 0;
			$data['rental_price'] = 0;
		}

		if(isset($data['for_sell']) && $data['for_sell'] == 1){
			$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
		}
		else{
			$data['for_sell'] = 0;
			$data['selling_price'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_assets', $data);
		if($this->db->affected_rows() > 0) {
			// Updated successfull
			return 4;
		}
		// Update fail
		return 5;
	}

		/**
	 * add consumables
	 * @param array $data 
	 * @return integer $insert id 
	 */
		public function add_consumables($data){
			$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
			$data['date_buy'] = fe_format_date($data['date_buy']);
			if(isset($data['for_rent']) && $data['for_rent'] == 1){
				$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
			}
			else{
				$data['for_rent'] = 0;
				$data['rental_price'] = 0;
			}

			if(isset($data['for_sell']) && $data['for_sell'] == 1){
				$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
			}
			else{
				$data['for_sell'] = 0;
				$data['selling_price'] = 0;
			}
			$this->db->insert(db_prefix().'fe_assets', $data);
			$insert_id = $this->db->insert_id();
			if($insert_id){
        		hooks()->do_action('after_fe_consumable_added', $insert_id);

				return $insert_id;
			}
			return 0;
		}
	/**
	 * update consumables
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_consumables($data){
		if(isset($data['quantity'])){
			$total_checkout = $this->count_checkin_asset_by_parents($data['id']);
			$data_assets = $this->get_assets($data['id']);
			if($data_assets){
				if($data['quantity'] == 0){
					// Quantity not valid
					return 1;
				}
				if($data['quantity'] < $data_assets->quantity){
					$delete = $data_assets->quantity - $data['quantity'];
					$remain = $data_assets->quantity - $total_checkout;
					if($delete > $remain){
					// Quantity not valid (Not smaller than valid quantity)
						return 1;
					}
				}
			}
			else{
				// This accessory not exist
				return 2;
			}
		}
		else{
			// Quantity is unknown
			return 3;
		}
		$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
		$data['date_buy'] = fe_format_date($data['date_buy']);
		if(isset($data['for_rent']) && $data['for_rent'] == 1){
			$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
		}
		else{
			$data['for_rent'] = 0;
			$data['rental_price'] = 0;
		}

		if(isset($data['for_sell']) && $data['for_sell'] == 1){
			$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
		}
		else{
			$data['for_sell'] = 0;
			$data['selling_price'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_assets', $data);
		if($this->db->affected_rows() > 0) {

        	hooks()->do_action('after_fe_consumable_updated', $data['id']);

			// Updated successfull
			return 4;
		}
		// Update fail
		return 5;
	}
	/**
	 * add components
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_components($data){
		$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
		$data['date_buy'] = fe_format_date($data['date_buy']);
		if(isset($data['for_rent']) && $data['for_rent'] == 1){
			$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
		}
		else{
			$data['for_rent'] = 0;
			$data['rental_price'] = 0;
		}

		if(isset($data['for_sell']) && $data['for_sell'] == 1){
			$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
		}
		else{
			$data['for_sell'] = 0;
			$data['selling_price'] = 0;
		}
		$this->db->insert(db_prefix().'fe_assets', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
        	hooks()->do_action('after_fe_component_added', $insert_id);

			return $insert_id;
		}
		return 0;
	}
	/**
	 * update components
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_components($data){
		$data['unit_price'] = fe_reformat_currency_asset($data['unit_price']);
		$data['date_buy'] = fe_format_date($data['date_buy']);
		if(isset($data['for_rent']) && $data['for_rent'] == 1){
			$data['rental_price'] = fe_reformat_currency_asset($data['rental_price']);
		}
		else{
			$data['for_rent'] = 0;
			$data['rental_price'] = 0;
		}

		if(isset($data['for_sell']) && $data['for_sell'] == 1){
			$data['selling_price'] = fe_reformat_currency_asset($data['selling_price']);
		}
		else{
			$data['for_sell'] = 0;
			$data['selling_price'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_assets', $data);
		if($this->db->affected_rows() > 0) {
        	hooks()->do_action('after_fe_component_updated', $data['id']);

			return true;
		}
		return false;
	}
	/**
	 * add predefined_kits
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_predefined_kits($data){
		$this->db->where('assets_name', $data['assets_name']);
		$this->db->where('type', 'predefined_kit');
		$data_exist = $this->db->get(db_prefix().'fe_assets')->row();
		if(!$data_exist){
			$this->db->insert(db_prefix().'fe_assets', $data);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				return $insert_id;
			}
			return 0;
		}
		else{
			return -1;
		}
	}
	/**
	 * update predefined_kits
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_predefined_kits($data){
		$this->db->where('assets_name', $data['assets_name']);
		$this->db->where('type', 'predefined_kit');
		$data_exist = $this->db->get(db_prefix().'fe_assets')->row();
		if(!$data_exist){
			$this->db->where('id', $data['id']);
			$this->db->update(db_prefix().'fe_assets', $data);
			if($this->db->affected_rows() > 0) {
				return 1;
			}
			return 0;
		}
		return -1;
	}

	/**
	 * add model predefined kits
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_model_predefined_kits($data){
		$this->db->where('model_id',$data['model_id']);
		$this->db->where('parent_id',$data['parent_id']);
		$data_model_p = $this->db->get(db_prefix().'fe_model_predefined_kits')->row();
		if(!$data_model_p){
			$this->db->insert(db_prefix().'fe_model_predefined_kits', $data);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				return $insert_id;
			}
			return 0;
		}
		return '';
	}

	/**
	 * update model predefined kits
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_model_predefined_kits($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_model_predefined_kits', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * delete model predefined kits
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_model_predefined_kits($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_model_predefined_kits');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
 * check in accessories
 * @param  array $data 
 * @return array       
 */
	public function check_in_accessories($data){
		$asset_id = (isset($data['item_id']) ? $data['item_id'] : '');
		$to = (isset($data['checkout_to']) ? $data['checkout_to'] : '');
		$to_id = (isset($data['staff_id']) ? $data['staff_id'] : '');
		if($data['type'] == 'checkin'){
			$this->db->where('id', $data['id']);
			$this->db->update(db_prefix().'fe_checkin_assets',[
				'status' => 1,
				'checkin_date' => fe_format_date($data['checkin_date']),
				'notes' => $data['notes']
			]);

			if($this->db->affected_rows() > 0) {
				//Check in
				$this->db->where('id', $data['id']);
				$data_checkout = $this->db->get(db_prefix().'fe_checkin_assets')->row();
				if($data_checkout){
					unset($data_checkout->id);
					$data_checkout->type = 'checkin';
					$data_checkout->item_type = 'accessory';
					$this->db->insert(db_prefix().'fe_checkin_assets', (array)$data_checkout);
				}
				// ---  Add log
				if($asset_id != ''){
					$data_checkout = $this->db->query('select * from '.db_prefix().'fe_log_assets where item_id = '.$asset_id.' and action="checkout" order by date_creator desc limit 0,1')->row();
					if($data_checkout){
						$to_id = $data_checkout->to_id;
						$to = $data_checkout->to;
					}
					$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);
				}
				// ---  End add log
				return true;
			}
			return false;
		}
		else{
			unset($data['id']);
			$data['item_type'] = 'accessory';
			$this->db->insert(db_prefix().'fe_checkin_assets', $data);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				// ---  Add log
				if($asset_id != ''){
					$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);
				}
				// ---  End add log
				return $insert_id;
			}
			return 0;
		}
	}
/**
 * count checkin asset by parents
 * @param  integer $parent_id 
 * @return integer            
*/
public function count_checkin_asset_by_parents($parent_id){
	$this->db->where('item_id', $parent_id);
	$this->db->where('status', 2);
	return $this->db->get(db_prefix().'fe_checkin_assets')->num_rows();
}

/**
 * check in consumables
 * @param  array $data 
 * @return array       
 */
public function check_in_consumables($data){
	$asset_id = $data['item_id'];
	$to = $data['checkout_to'];
	$to_id = $data['staff_id'];

	if($data['type'] == 'checkin'){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_checkin_assets',[
			'status' => 1,
			'checkin_date' => fe_format_date($data['checkin_date']),
			'notes' => $data['notes']
		]);
		if($this->db->affected_rows() > 0) {
			// --- Check in
			$this->db->where('id', $data['id']);
			$data_checkout = $this->db->get(db_prefix().'fe_checkin_assets')->row();
			if($data_checkout){
				unset($data_checkout->id);
				$data_checkout->type = 'checkin';
				$data_checkout->item_type = 'consumable';
				$this->db->insert(db_prefix().'fe_checkin_assets', (array)$data_checkout);
			}
			// ---  Add log
			if($asset_id != ''){
				$data_checkout = $this->db->query('select * from '.db_prefix().'fe_log_assets where item_id = '.$asset_id.' and action="checkout" order by date_creator desc limit 0,1')->row();
				if($data_checkout){
					$to_id = $data_checkout->to_id;
					$to = $data_checkout->to;
				}
				$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);
			}
				// ---  End add log
			return true;
		}
		return false;
	}
	else{
		unset($data['id']);
		$data['item_type'] = 'consumable';
		$this->db->insert(db_prefix().'fe_checkin_assets', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			// ---  Add log
			if($asset_id != ''){
				$this->add_log(get_staff_user_id(), $data['type'], $asset_id, '', '', $to, $to_id, $data['notes']);
			}
			// ---  End add log
			return $insert_id;
		}
		return 0;
	}
}


/**
 * check in components
 * @param  array $data 
 * @return array       
 */
public function check_in_components($data){
	if($data['type'] == 'checkin'){
		$data_checked_out = $this->get_checkin_out_data($data['id']);
		if($data_checked_out){
			$old_qty = $data_checked_out->quantity;

			// Get old quantity checked out
			// If adjust quantity is greater than the old quantity return error is -1
			// Else if adjust quantity is equal old quantity then change status to 1 (checked in) and update quantity, note
			// Else if adjust quantity is smaller than old quantity then only change quantity of check out

			if($data['quantity'] > $old_qty){
				return false;
			}
			if($data['quantity'] == $old_qty){
				$this->db->where('id', $data['id']);
				$this->db->update(db_prefix().'fe_checkin_assets',[
					'status' => 1,
					'quantity' => 0,
					'notes' => $data['notes']
				]);
				if($this->db->affected_rows() > 0) {
					$this->insert_checkin_component($data['id'], $data['quantity']);
					return true;
				}
			}
			if($data['quantity'] < $old_qty){
				$new_quantity = $old_qty - $data['quantity'];
				$this->db->where('id', $data['id']);
				$this->db->update(db_prefix().'fe_checkin_assets',[
					'quantity' => $new_quantity,
					'notes' => $data['notes']
				]);
				if($this->db->affected_rows() > 0) {
					$this->insert_checkin_component($data['id'], $data['quantity']);
					return true;
				}
			}
		}
		return '';
	}
	else{
		if(!isset($data['checkout_to'])){
			$data['checkout_to'] = 'asset';
		}
		$data['staff_id'] = $this->get_manager_asset($data['asset_id']);
		$data['item_type'] = 'component';
		$data_assets = $this->get_assets($data['item_id']);
		$amount_checked_out = $this->count_checkin_component_by_parents($data['item_id']);
		if($data_assets){
			$total_amount = $data_assets->quantity;
			if(($amount_checked_out + $data['quantity']) <= $total_amount){
				unset($data['id']);
				$this->db->insert(db_prefix().'fe_checkin_assets', $data);
				$insert_id = $this->db->insert_id();
				if($insert_id){
					return $insert_id;
				}
			}
			else{
				return -1;
			}
		}
		return 0;
	}
}

/**
 * check in predefined_kits
 * @param  array $data 
 * @return array       
 */
public function check_in_predefined_kits($data){
	if($data['type'] == 'checkin'){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_checkin_assets',[
			'status' => 1,
			'quantity' => $data['quantity'],
			'notes' => $data['notes']
		]);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	else{
		$robj = new stdClass();
		unset($data['id']);
		if(isset($data['checkin_date'])){
			$data['checkin_date'] = fe_format_date($data['checkin_date']);
		}
		if(isset($data['expected_checkin_date'])){
			$data['expected_checkin_date'] = fe_format_date($data['expected_checkin_date']);
		}
		$select_kit = 0;
		if(isset($data['choose_an_available_kit']) && $data['choose_an_available_kit'] == 1){
			$select_kit = $data['available_kit'];
		}
		$result = $this->list_asset_checkout_predefined_kits($data['item_id'], $select_kit);
		if($result->status == 2){
			$affectedRows = 0;
			$list_asset = $result->list_asset;
			foreach ($list_asset as $asset) {
				$data_checkout['item_id'] = $asset['id'];
				$data_checkout['type'] = 'checkout';
				$data_checkout['model'] = $asset['model'];
				$data_checkout['asset_name'] = $asset['assets_name'];
				$data_checkout['status'] = $asset['status'];
				$data_checkout['location_id'] = '';
				$data_checkout['asset_id'] = $asset['id'];
				$data_checkout['checkout_to'] = $data['checkout_to'];
				$data_checkout['staff_id'] = $data['staff_id'];
				$data_checkout['project_id'] = $data['project_id'];
				$data_checkout['checkin_date'] = $data['checkin_date'];
				$data_checkout['expected_checkin_date'] = $data['expected_checkin_date'];
				$data_checkout['predefined_kit_id'] = $data['item_id'];
				$data_checkout['notes'] = $data['notes'];
				$res = $this->check_in_assets($data_checkout);
				if($res != 0){
					$affectedRows++;
				}
			}
			if($affectedRows != 0){
				if(is_numeric($select_kit) && $select_kit > 0){
					$this->delete_assign_predefined_kits($select_kit);
				}
				$robj->status = 2;
				$robj->msg = _l('fe_checkout_successfully');
				return $robj;
			}
			else{
				$robj->status = 3;
				$robj->msg = _l('fe_checkout_fail');
				return $robj;
			}
		}
		else{
			return $result;
		}
	}
}

/**
 * list asset checkout predefined kits
 * @param  integer $kit_id 
 * @return object         
 */
public function list_asset_checkout_predefined_kits($kit_id, $available_kit = 0){
	$item_id = '';
	if(is_numeric($available_kit) && $available_kit > 0){
		$data_kit = $this->get_assign_asset_predefined_kits($available_kit);
		if($data_kit && $data_kit->assign_data != ''){
			$assign_json = json_decode($data_kit->assign_data);
			if(is_object($assign_json) && $assign_json = (array)$assign_json){
				foreach($assign_json as $assign){
					foreach($assign as $asset_id){
						if(is_numeric($asset_id) && $asset_id > 0){
							$item_id .= $asset_id.',';
						}
					}
				}
			}
		}
	}
	$robj = new stdClass();
	$robj->status = 2;
	$robj->msg = '';
	$list_asset = [];
	$this->db->where('parent_id', $kit_id);
	$list_model = $this->db->get(db_prefix().'fe_model_predefined_kits')->result_array();
	if($list_model){
		$count_affected_model = 1;
		$count_model = 1;
		foreach ($list_model as $model_append) {
			$count_model++;
			$model_id = $model_append['model_id'];
			$model_name = '';
			$models = $this->get_models($model_id);
			if($models){
				$model_name = $models->model_name;
			}
			if($item_id != ''){
				$item_id = rtrim($item_id, ',');
				$this->db->where(db_prefix().'fe_assets.id IN ('.$item_id.')');
			}
			$this->db->where('model_id', $model_id);
			$this->db->where('active', 1);
			$this->db->where('type', 'asset');
			$this->db->where('checkin_out', 1);
			$this->db->order_by('id', 'desc');

			$this->db->select(db_prefix().'fe_assets.id, assets_name, status');

			$this->db->join(db_prefix().'fe_status_labels', db_prefix().'fe_status_labels.id = '.db_prefix().'fe_assets.status', 'left');
			$this->db->where(db_prefix().'fe_status_labels.status_type', 'deployable');
			$list_asset_model = $this->db->get(db_prefix().'fe_assets')->result_array();
			if($list_asset_model){
				// If enough quantity or more -> get id of asset add to array
				$quantity = $model_append['quantity'];
				if(count($list_asset_model) >= (float)$quantity){
					$count_affected_model++;
					foreach ($list_asset_model as $i => $asset) {
						$list_asset[] = array('id' => $asset['id'], 'assets_name' => $asset['assets_name'], 'status' => $asset['status'], 'model' => $model_name);
						if($i == ($quantity - 1)){
							break;
						}
					}
				}
				else{
					// Not enought quantity -> return error
					$robj->status = 1;
					$robj->msg = $model_name.' '._l('fe_not_enough_amount_of_asset_to_checkout');
					return $robj;
				}
			}
			else{
				// Not enought quantity -> return error
				$robj->status = 1;
				$robj->msg = $model_name.' '._l('fe_not_enough_amount_of_asset_to_checkout');
				return $robj;
			}
		}
		if($count_affected_model != $count_model){
			$robj->status = 1;
			$robj->msg = $model_name.' '._l('fe_not_enough_amount_of_asset_to_checkout');
			return $robj;
		}
	}
	else{
		// No model append
		$robj->status = 0;
		$robj->msg = _l('fe_no_model_available');
		return $robj;
	}
	$robj->list_asset = $list_asset;
	return $robj;
}

/**
 * add assets maintenances
 * @param array $data 
 */
public function add_assets_maintenances($data){
	if(isset($data['start_date'])){
		$data['start_date'] = fe_format_date($data['start_date']);
	}
	if(isset($data['completion_date'])){
		$data['completion_date'] = fe_format_date($data['completion_date']);
	}
	$data['cost'] = fe_reformat_currency_asset($data['cost']);
	$this->db->insert(db_prefix().'fe_asset_maintenances', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
        hooks()->do_action('after_fe_maintenance_added', $insert_id);

		return $insert_id;
	}
	return 0;
}
/**
 * update assets maintenances
 * @param array $data 
 */
public function update_assets_maintenances($data){
	if(isset($data['start_date'])){
		$data['start_date'] = fe_format_date($data['start_date']);
	}
	if(isset($data['completion_date'])){
		$data['completion_date'] = fe_format_date($data['completion_date']);
	}
	if(!isset($data['warranty_improvement'])){
		$data['warranty_improvement'] = $data['warranty_improvement'];
	}
	$data['cost'] = fe_reformat_currency_asset($data['cost']);
	$this->db->where('id', $data['id']);
	$this->db->update(db_prefix().'fe_asset_maintenances', $data);
	if($this->db->affected_rows() > 0) {
        hooks()->do_action('after_fe_maintenance_updated', $data['id']);

		return true;
	}
	return false;
}

public function get_asset_name($asset_id){
	$asset_name = '';
	if($asset_id != ''){
		$data_asset = $this->get_assets($asset_id);
		if($data_asset){
			$asset_name = $data_asset->assets_name;
			if($asset_name == ''){
				$data_model = $this->get_models($data_asset->model_id);
				if($data_model){
					$asset_name = $data_model->model_name;
				}
			}
		}
	}
	return $asset_name;
}


/**
 * get asset location checkout
 * @param   $asset_id 
 */
public function get_asset_location_checkout($checkin_out_id, $location_id){
	$current_location = '';
	$checkout_to = '';
	$checkout_type = '';
	$to_id = '';
	$this->db->where('id', $checkin_out_id);
	$this->db->where('type', 'checkout');
	$data_checkout = $this->db->get(db_prefix().'fe_checkin_assets')->row();
	if($data_checkout){
		$to = $data_checkout->checkout_to;
		$checkout_to = '';
		$checkout_type = $to;
		if($to != '' && $to != null){
			switch ($to) {
				case 'user':
				$department_name = '';
				if(is_numeric($data_checkout->staff_id) && $data_checkout->staff_id > 0){
					$data_staff_department = $this->departments_model->get_staff_departments($data_checkout->staff_id);
					if($data_staff_department){
						foreach ($data_staff_department as $key => $staff_department) {
							$department_name .= $staff_department['name'].', ';
						}
						if($department_name != ''){
							$department_name = rtrim($department_name,', ');
						}
					}
				}
				$checkout_to = get_staff_full_name($data_checkout->staff_id);
				$current_location = $department_name;
				$to_id = $data_checkout->staff_id;				
				break;
				case 'customer':
				$checkout_to = fe_get_customer_name($data_checkout->customer_id);
				$current_location = '';
				$to_id = $data_checkout->customer_id;				
				break;
				case 'asset':
				if(is_numeric($location_id) && $location_id > 0){
					$data_assets = $this->get_assets($data_checkout->asset_id);
					if($data_assets){
						$checkout_to = (($data_assets->series != '') ? '('.$data_assets->series.') - ' : '').''.$data_assets->assets_name;

						if(is_numeric($location_id) && $location_id > 0){
							$data_locations = $this->fixed_equipment_model->get_locations($location_id);
							if($data_locations){
								$current_location = $data_locations->location_name;
							}							
						}
						
						$to_id = $data_checkout->asset_id;
					}
				}
				break;
				case 'location':
				if(is_numeric($location_id) && $location_id > 0){
					$data_locations = $this->get_locations($data_checkout->location_id);
					if($data_locations){
						$checkout_to = $data_locations->location_name;						
						$current_location = $data_locations->location_name;
						$to_id = $data_checkout->location_id;
					}
				}
				break;
				case 'project':
				if(is_numeric($data_checkout->project_id) && $data_checkout->project_id > 0){
					$data_project = $this->get_projects($data_checkout->project_id);
					if($data_project){
						$checkout_to = $data_project->name;						
						$current_location = "";
						$to_id = $data_checkout->project_id;
					}
				}
				break;
			}
		}
	}
	$obj = new stdClass();
	$obj->current_location = $current_location;
	$obj->checkout_to = $checkout_to;
	$obj->checkout_type = $checkout_type;
	$obj->to_id = $to_id;
	return $obj;
}
/**
 * get asset location info
 * @param  integer $asset_id 
 * @return integer           
 */
public function get_asset_location_info($asset_id){
	$obj = new stdClass();
	$obj->default_location = '';
	$obj->curent_location = '';
	$obj->checkout_to = '';
	$obj->checkout_type = '';
	$obj->to_id = '';
	$data_assets = $this->get_assets($asset_id);
	if($data_assets && is_object($data_assets)){
		$default_location = '';
		$curent_location = '';
		$checkout_to = '';
		$to_id = '';
		$checkout_type = '';

		if(is_numeric($data_assets->asset_location) && $data_assets->asset_location > 0){
			$data_location = $this->get_locations($data_assets->asset_location);
			if($data_location){
				$default_location = $data_location->location_name;
			}
		}

		if($data_assets->checkin_out == 2){
			$checkout_info = $this->get_asset_location_checkout($data_assets->checkin_out_id, $data_assets->location_id);
			$curent_location = $checkout_info->current_location;
			$checkout_to = $checkout_info->checkout_to;
			$to_id = $checkout_info->to_id;
			$checkout_type = $checkout_info->checkout_type;
		}
		else{
			$location_id = $data_assets->location_id;
			if(is_numeric($location_id) && $location_id > 0){
				$data_location = $this->get_locations($location_id);
				if($data_location){
					$curent_location = $data_location->location_name;
				}
				else{
					$curent_location = $default_location;
				}
			}
			else{
				$curent_location = $default_location;
			}
		}
		$obj->checkout_to = $checkout_to;
		$obj->checkout_type = $checkout_type;
		$obj->default_location = $default_location; 
		$obj->curent_location = $curent_location;  
		$obj->to_id = $to_id;
	}
	return $obj;
}

/**
 * delete asset maintenances
 * @param  integer $id 
 * @return boolean     
 */
public function delete_asset_maintenances($id){
	$this->db->where('id', $id);
	$this->db->delete(db_prefix().'fe_asset_maintenances');
	if($this->db->affected_rows() > 0) {
        hooks()->do_action('after_fe_maintenance_deleted', $id);

		return true;
	}
	return false;
}

/**
 * get asset maintenances
 * @param  integer $id 
 * @return integer     
 */
public function get_asset_maintenances($id){
	if($id != ''){
		$this->db->where('id', $id);
		return $this->db->get(db_prefix().'fe_asset_maintenances')->row();
	}
	else{
		return $this->db->get(db_prefix().'fe_asset_maintenances')->result_array();
	}
}
/**
	 * add approval process
	 * @param array $data 
	 * @return boolean 
	 */
public function add_approval_process($data)
{
	unset($data['approval_setting_id']);


	if(isset($data['staff'])){
		$setting = [];
		foreach ($data['staff'] as $key => $value) {
			$node = [];
			$node['approver'] = 'specific_personnel';
			$node['staff'] = $data['staff'][$key];

			$setting[] = $node;
		}
		unset($data['approver']);
		unset($data['staff']);
	}



	if(!isset($data['choose_when_approving'])){
		$data['choose_when_approving'] = 0;
	}

	if(isset($data['departments'])){
		$data['departments'] = implode(',', $data['departments']);
	}

	if(isset($data['job_positions'])){
		$data['job_positions'] = implode(',', $data['job_positions']);
	}

	$data['setting'] = json_encode($setting);

	if(isset($data['notification_recipient'])){
		$data['notification_recipient'] = implode(",", $data['notification_recipient']);
	}

	$this->db->insert(db_prefix() .'fe_approval_setting', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		return true;
	}
	return false;
}
	/**
	 * update approval process
	 * @param  integer $id   
	 * @param  array $data 
	 * @return boolean       
	 */
	public function update_approval_process($id, $data)
	{
		if(isset($data['staff'])){
			$setting = [];
			foreach ($data['staff'] as $key => $value) {
				$node = [];
				$node['approver'] = 'specific_personnel';
				$node['staff'] = $data['staff'][$key];

				$setting[] = $node;
			}
			unset($data['approver']);
			unset($data['staff']);
		}

		if(!isset($data['choose_when_approving'])){
			$data['choose_when_approving'] = 0;
		}

		$data['setting'] = json_encode($setting);

		if(isset($data['departments'])){
			$data['departments'] = implode(',', $data['departments']);
		}else{
			$data['departments'] = '';
		}

		if(isset($data['job_positions'])){
			$data['job_positions'] = implode(',', $data['job_positions']);
		}else{
			$data['job_positions'] = '';
		}

		if(isset($data['notification_recipient'])){
			$data['notification_recipient'] = implode(",", $data['notification_recipient']);
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix() .'fe_approval_setting', $data);

		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * delete approval setting
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_approve_setting($id)
	{
		if(is_numeric($id)){
			$this->db->where('id', $id);
			$this->db->delete(db_prefix() .'fe_approval_setting');
			if ($this->db->affected_rows() > 0) {
				return true;
			}
		}
		return false;
	}

/**
 * get approval setting
 * @param  integer $id 
 * @return integer     
 */
public function get_approval_setting($id){
	if($id != ''){
		$this->db->where('id',$id);
		return $this->db->get(db_prefix().'fe_approval_setting')->row();
	}else {
		return $this->db->get(db_prefix().'fe_approval_setting')->result_array();
	}
}
/**
 * add new request
 * @param $data 
 */
public function add_new_request($data){
	$this->db->insert(db_prefix().'fe_checkin_assets', $data);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		return $insert_id;
	}
	return 0;
}
/**
 * change request status
 * @param  integer $id     
 * @param  integer $status 
 * @return integer         
 */
public function change_request_status($id, $status){
	if(is_numeric($id)){
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'fe_checkin_assets', ['request_status' => $status]);
		if ($this->db->affected_rows() > 0) {
			return true;
		}
	}
	return false;
}

/**
 * get approve setting
 * @param  string  $type         
 * @param  boolean $only_setting 
 * @return boolean                
 */
public function get_approve_setting($type, $only_setting = true){
	$this->db->select('*');
	$this->db->where('related', $type);
	$approval_setting = $this->db->get(db_prefix().'fe_approval_setting')->row();
	if($approval_setting){
		if($only_setting == false){
			return $approval_setting;
		}else{
			return json_decode($approval_setting->setting);
		}
	}else{
		return false;
	}
}


	/**
	 * send request approve
	 * @param  array $data     
	 * @param  integer $staff_id 
	 * @return bool           
	 */
	public function send_request_approve($rel_id, $rel_type, $staff_id = ''){
		$data_new = $this->get_approve_setting($rel_type, true);
		$data_setting = $this->get_approve_setting($rel_type, false);
		$this->delete_approval_details($rel_id, $rel_type);
		$date_send = date('Y-m-d H:i:s');
		$notification_recipient_list = '';
		foreach ($data_new as $value) {
			$row = [];
			$row['notification_recipient'] = $data_setting->notification_recipient;
			$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));
			$row['staffid'] = $value->staff;
			$row['date_send'] = $date_send;
			$row['rel_id'] = $rel_id;
			$row['rel_type'] = $rel_type;
			$row['sender'] = $staff_id;
			$this->db->insert(db_prefix().'fe_approval_details', $row);
			if($notification_recipient_list == ''){
				$notification_recipient_list = $data_setting->notification_recipient;
			}
		}
		if($notification_recipient_list != ''){
			$data['notification_recipient'] = $notification_recipient_list;
			$data['rel_id'] = $rel_id;
			$data['rel_type'] = $rel_type;
			$this->session->set_userdata(['send_notify' => $data]);
		}
		$this->send_notify_approve($rel_id, $rel_type);
		return true;
	}
	/**
	 * delete approval details
	 * @param  string $rel_id   
	 * @param  string $rel_type 
	 * @return boolean           
	*/
	public function delete_approval_details($rel_id, $rel_type)
	{
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);
		$this->db->delete(db_prefix().'fe_approval_details');
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}
	/**
	 * get checkin out data
	 * @param  integer $id 
	 * @return integer     
	 */
	public function get_checkin_out_data($id){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_checkin_assets')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_checkin_assets')->result_array();
		}
	}
	/**
	 * get approval details
	 * @param  integer $rel_id   
	 * @param  string $rel_type 
	 * @return integer           
	 */
	public function get_approval_details($rel_id,$rel_type){
		if($rel_id != ''){
			$this->db->where('rel_id',$rel_id);
			$this->db->where('rel_type',$rel_type);
			$this->db->order_by('id');
			return $this->db->get(db_prefix().'fe_approval_details')->result_array();
		}else {
			return $this->db->get(db_prefix().'fe_approval_details')->result_array();
		}
	}
/**
 * change approve
 * @param  [type] $data 
 * @return [type]       
 */
public function change_approve($data){
	$this->db->where('rel_id', $data['rel_id']);
	$this->db->where('rel_type', $data['rel_type']);
	$this->db->where('staffid', $data['staffid']);
	$this->db->update(db_prefix() . 'fe_approval_details', $data);
	if ($this->db->affected_rows() > 0) {
		$this->send_notify_approve($data['rel_id'], $data['rel_type'], $data['approve'], $data['staffid']);
		// If has rejected then change status to finish approve
		if($data['approve'] == 2)
		{
			$this->change_request_status($data['rel_id'], 2);
			return true;
		}
		$count_approve_total = $this->count_approve($data['rel_id'],$data['rel_type'])->count;
		$count_approve = $this->count_approve($data['rel_id'],$data['rel_type'],1)->count;
		$count_rejected = $this->count_approve($data['rel_id'],$data['rel_type'],2)->count;
		if(($count_approve + $count_rejected) == $count_approve_total){
			if($count_approve_total == $count_approve){
				$this->change_request_status($data['rel_id'], 1);
				$data_checkout_log = $this->fixed_equipment_model->get_checkin_out_data($data['rel_id']);
				if($data_checkout_log){
					$this->db->where('id', $data_checkout_log->item_id);
					$this->db->update(db_prefix().'fe_assets', ['checkin_out' => 2, 'checkin_out_id' => $data['rel_id']]);
					$this->add_log(get_staff_user_id(), $data['rel_type'], $data_checkout_log->item_id, '', '', 'user', $data_checkout_log->staff_id, $data_checkout_log->notes);
				}
			}
			else{
				$this->change_request_status($data['rel_id'], 2);
			}
		}
		return true;               
	}
	return false;
}
/**
 * send notify approve
 * @param  integer $id           
 * @param  string $request_type 
 * @return boolean               
 */
public function send_notify_approve($id, $request_type, $status = '', $staffid = ''){
	$link = '';
	$obj_name = '';
	$creator_id = 0;
	if($request_type == 'checkout'){
		$this->db->select('creator_id, request_title');
		$this->db->where('id', $id);
		$_data = $this->db->get(db_prefix().'fe_checkin_assets')->row();
		if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
			$creator_id = $_data->creator_id;
			$obj_name = $_data->request_title;
		}
		$link = 'fixed_equipment/detail_request/'.$id;
	}
	elseif($request_type == 'audit'){
		$this->db->select('creator_id, title');
		$this->db->where('id', $id);
		$_data = $this->db->get(db_prefix().'fe_audit_requests')->row();
		if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
			$creator_id = $_data->creator_id;
			$obj_name = $_data->title;
		}
		$link = 'fixed_equipment/view_audit_request/'.$id;		
	}
	elseif($request_type == 'close_audit'){
		$this->db->select('creator_id, title');
		$this->db->where('id', $id);
		$_data = $this->db->get(db_prefix().'fe_audit_requests')->row();
		if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
			$creator_id = $_data->creator_id;
			$obj_name = $_data->title;
		}
		$link = 'fixed_equipment/audit/'.$id;		
	}
	elseif($request_type == 'inventory_receiving'){
		$this->db->select('creator_id, goods_receipt_code');
		$this->db->where('id', $id);
		$_data = $this->db->get(db_prefix().'fe_goods_receipt')->row();
		if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
			$creator_id = $_data->creator_id;
			$obj_name = $_data->goods_receipt_code;
		}
		$link = 'fixed_equipment/inventory?tab=inventory_receiving&id='.$id;		
	}
	elseif($request_type == 'inventory_delivery'){
		$this->db->select('addedfrom, goods_delivery_code');
		$this->db->where('id', $id);
		$_data = $this->db->get(db_prefix().'fe_goods_delivery')->row();
		if($_data && is_numeric($_data->addedfrom) && $_data->addedfrom > 0){
			$creator_id = $_data->addedfrom;
			$obj_name = $_data->goods_delivery_code;
		}
		$link = 'fixed_equipment/inventory?tab=inventory_delivery&id='.$id;		
	}


	$full_path = admin_url($link);
	$type = _l('fe_'.$request_type);
	$data_approve = $this->get_approval_details($id,$request_type);
	if($data_approve){
		$this->load->model('emails_model');
		// Send notify for next approver
		$staff_approver = '';
		$has_reject = false;
		foreach ($data_approve as $key => $approver) {
			if($approver['approve'] == 2 || $approver['approve'] == -1){
				$has_reject = true;
				break;
			}
			if(($approver['approve'] == '' || $approver['approve'] == null) && $staff_approver == '')
			{
				$staff_approver = $approver['staffid'];
			}
		}
		if(is_numeric($staff_approver) && $staff_approver > 0 && !$has_reject){
			$string_sub = _l('fe_sent_you_an_approval_request').' '._l('fe_'.$request_type).''.($obj_name != '' ? ' ('.$obj_name.')' : '');
			$this->notifications($staff_approver, $link, $string_sub);
			$staff_email = fe_get_staff_email($staff_approver);
			if($staff_email != ''){
				$this->emails_model->send_simple_email($staff_email, _l('fe_request_approval'), _l('fe_email_send_request_approve', $type).' '._l('fe_from_staff', get_staff_full_name($creator_id)).'<br>'._l('fe_detail').': <a href="'.$full_path.'">'.$obj_name.'</a> ');
			}
		}

		if(is_numeric($status) && is_numeric($staffid)  && $staffid > 0){
			$message = '';
			if($status == 1){
				$message = ' '._l('fe_just_approved').' '._l('fe_'.$request_type).''.($obj_name != '' ? ' ('.$obj_name.')' : '');
			}
			else{
				$message = ' '._l('fe_just_rejected').' '._l('fe_'.$request_type).''.($obj_name != '' ? ' ('.$obj_name.')' : '');
			}

			// Send notify to receipient
			if(isset($data_approve[0]['notification_recipient']) && $data_approve[0]['notification_recipient'] != ''){
				$data_recipient = explode(',', $data_approve[0]['notification_recipient']);
				foreach($data_recipient as $recipient){
					$this->notifications($recipient, $link, $message);
					$staff_email = fe_get_staff_email($recipient);
					if($staff_email != ''){
						$this->emails_model->send_simple_email($staff_email, _l('fe_approval_notification'), get_staff_full_name($staffid).' '.$message.'<br>'._l('fe_detail').': <a href="'.$full_path.'">'.$obj_name.'</a>');
					}
				}
			}

			// Send notify to creator
			if(is_numeric($creator_id) && $creator_id > 0){
				$this->notifications($creator_id, $link, $message);
				$staff_email = fe_get_staff_email($creator_id);
				if($staff_email != ''){
					$this->emails_model->send_simple_email($staff_email, _l('fe_approval_notification'), get_staff_full_name($staffid).' '.$message.'<br>'._l('fe_detail').': <a href="'.$full_path.'">'.$obj_name.'</a>');
				}
			}
			//
		}
	}
	return false;
}
/**
 * notifications
 * @param  integer $id_staff    
 * @param  integer $link        
 * @param  integer 
 * @return integer              
 */
public function notifications($id_staff, $link, $description){

	$notifiedUsers = [];
	$id_userlogin = get_staff_user_id();
	$notified = add_notification([
		'fromuserid'      => $id_userlogin,
		'description'     => $description,
		'link'            => $link,
		'touserid'        => $id_staff,
		'additional_data' => serialize([
			$description,
		]),
	]);
	if ($notified) {
		array_push($notifiedUsers, $id_staff);
	}
	pusher_trigger_notification($notifiedUsers);
}
/**
 * count approve
 * @param integer $rel_id   
 * @param integer $rel_type 
 * @param  string $approve  
 * @return object        
 */
public function count_approve($rel_id, $rel_type, $approve = ''){
	if($approve == ''){
		return $this->db->query('SELECT count(distinct(staffid)) as count FROM '.db_prefix().'fe_approval_details where rel_id = '.$rel_id.' and rel_type = \''.$rel_type.'\'')->row();
	}
	else{
		return $this->db->query('SELECT count(distinct(staffid)) as count FROM '.db_prefix().'fe_approval_details where rel_id = '.$rel_id.' and rel_type = \''.$rel_type.'\' and approve = '.$approve.'')->row();
	}
}
/**
 * get_last_checkin_out_asset
 * @param  integer $item_id 
 * @return object          
 */
public function get_last_checkin_out_asset($item_id){
	return $this->db->query('select * from '.db_prefix().'fe_checkin_assets where item_id = '.$item_id.' and type="checkout" order by date_creator desc limit 0,1')->row();
}

	/**
	 * add approver choosee when approve
	 * @param  array $data     
	 * @param  integer $staff_id 
	 * @return bool           
	 */
	public function add_approver_choosee_when_approve($rel_id, $rel_type, $staff_id = ''){
		$data_new = $this->get_approve_setting($rel_type, true);
		$data_setting = $this->get_approve_setting($rel_type, false);
		$this->delete_approval_details($rel_id, $rel_type);
		$date_send = date('Y-m-d H:i:s');
		$row = [];
		$row['notification_recipient'] = $data_setting->notification_recipient;
		$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));
		$row['staffid'] = $staff_id;
		$row['date_send'] = $date_send;
		$row['rel_id'] = $rel_id;
		$row['rel_type'] = $rel_type;
		$row['sender'] = $staff_id;
		$this->db->insert(db_prefix().'fe_approval_details', $row);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			$link = 'fixed_equipment/detail_request/'.$rel_id;
			$string_sub = _l('fe_sent_you_an_approval_request').' '._l($rel_type);
			$this->notifications($staff_id, $link, strtolower($string_sub));
			return $insert_id;
		}
		return 0;
	}

	/**
	 * get list checkout assets
	 * @param  integer $asset id 
	 * @return array object           
	 */
	public function get_list_checkout_assets($asset_id){
		return $this->db->query('select * from '.db_prefix().'fe_checkin_assets where checkout_to = "asset" and asset_id = '.$asset_id.' and (type="checkout" OR type="request") and check_status = 2')->result_array();
	}

	/**
	 * add fieldset
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_fieldset($data){
		$data['creator_id'] = get_staff_user_id();
		$this->db->insert(db_prefix().'fe_fieldsets', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update fieldset
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_fieldset($data){
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_fieldsets', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * add custom_field
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_custom_field($data){
		$data['option'] = is_array($data['option']) ? json_encode($data['option']) : null;
		if(!isset($data['required'])){
			$data['required'] = 0;
		}
		$this->db->insert(db_prefix().'fe_custom_fields', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}
	/**
	 * update custom_field
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_custom_field($data){
		$data['option'] = is_array($data['option']) ? json_encode($data['option']) : null;
		if(!isset($data['required'])){
			$data['required'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_custom_fields', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * delete custom_field
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_custom_field($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_custom_fields');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

		/**
	 * get custom_fields
	 * @param  integer $id 
	 * @return array or object    
	 */
		public function get_custom_fields($id = ''){
			if($id != ''){
				$this->db->where('id', $id);
				return $this->db->get(db_prefix().'fe_custom_fields')->row();
			}
			else{
				return $this->db->get(db_prefix().'fe_custom_fields')->result_array();
			}
		}
		/**
		 * get field set
		 * @param  integer $id 
		 * @return array or object    
		 */
		public function get_field_set($id = ''){
			if($id != ''){
				$this->db->where('id', $id);
				return $this->db->get(db_prefix().'fe_fieldsets')->row();
			}
			else{
				return $this->db->get(db_prefix().'fe_fieldsets')->result_array();
			}
		}

		/**
	 * get custom fields by field set
	 * @param  integer $id 
	 * @return array object    
	 */
		public function get_custom_field_by_fieldset($id = ''){
			$this->db->where('fieldset_id', $id);
			return $this->db->get(db_prefix().'fe_custom_fields')->result_array();
		}

	/**
	 * get custom fields value assets
	 * @param  integer $id 
	 * @return array object    
	 */
	public function get_custom_field_value_assets($asset_id = ''){
		$this->db->where('asset_id', $asset_id);
		return $this->db->get(db_prefix().'fe_custom_field_values')->result_array();
	}
/**
 * data query
 * @param  string  $query    
 * @param  boolean $multiple 
 * @return array object or array            
 */
public function data_query($query, $multiple = false){
	if($multiple){
		return $this->db->query($query)->result_array();
	}
	else{
		return $this->db->query($query)->row();			
	}
}
	/**
	 * create audit request
	 * @param  array $data 
	 * @return array       
	 */
	public function create_audit_request($data){
		$data_detail = [];
		if(isset($data['assets_detailt'])){
			$data_detail = json_decode($data['assets_detailt']);
			unset($data['assets_detailt']);
		}
		if(isset($data['asset_id'])){
			$data['asset_id'] = json_encode($data['asset_id']);
		}
		$data['audit_date'] = fe_format_date($data['audit_date']);
		$this->db->insert(db_prefix().'fe_audit_requests', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){

			foreach ($data_detail as $k => $row) {
				if($row[0] != null){
					$data_add['asset_id'] = $row[0];
					$data_add['asset_name'] = $row[1];
					$data_add['type'] = $row[2];
					$data_add['quantity'] = $row[3];
					$data_add['audit_id'] = $insert_id;
					$this->db->insert(db_prefix().'fe_audit_detail_requests', $data_add);
				}
			}
			return $insert_id;
		}
		return 0;
	}

/**
 * get audits
 * @param  integer $id 
 * @return array or object     
 */
public function get_audits($id){
	if($id != ''){
		$this->db->where('id', $id);
		return $this->db->get(db_prefix().'fe_audit_requests')->row();
	}
	else{
		return $this->db->get(db_prefix().'fe_audit_requests')->result_array();
	}
}

	/**
	 * get audit detail by master
	 * @param  integer $id 
	 * @return array     
	*/
	public function get_audit_detail_by_master($id){
		$this->db->where('audit_id', $id);
		return $this->db->get(db_prefix().'fe_audit_detail_requests')->result_array();
	}

/**
 * change approve audit
 * @param  array $data 
 * @return boolean       
 */
public function change_approve_audit($data){
	$this->db->where('rel_id', $data['rel_id']);
	$this->db->where('rel_type', $data['rel_type']);
	$this->db->where('staffid', $data['staffid']);
	$this->db->update(db_prefix() . 'fe_approval_details', $data);
	if ($this->db->affected_rows() > 0) {
		$this->send_notify_approve($data['rel_id'], $data['rel_type'], $data['approve'], $data['staffid']);
		// If has rejected then change status to finish approve
		if($data['approve'] == 2)
		{
			$this->db->where('id', $data['rel_id']);
			$this->db->update(db_prefix().'fe_audit_requests', ['status' => 2]);
			return true;
		}
		$count_approve_total = $this->count_approve($data['rel_id'],$data['rel_type'])->count;
		$count_approve = $this->count_approve($data['rel_id'],$data['rel_type'],1)->count;
		$count_rejected = $this->count_approve($data['rel_id'],$data['rel_type'],2)->count;
		if(($count_approve + $count_rejected) == $count_approve_total){
			if($count_approve_total == $count_approve){
				$this->db->where('id', $data['rel_id']);
				$this->db->update(db_prefix().'fe_audit_requests', ['status' => 1]);
			}
			else{
				$this->db->where('id', $data['rel_id']);
				$this->db->update(db_prefix().'fe_audit_requests', ['status' => 2]);
			}
		}
		return true;               
	}
	return false;
}

/**
	 * add approver choosee when approve audit
	 * @param  array $data     
	 * @param  integer $staff_id 
	 * @return bool           
	 */
public function add_approver_choosee_when_approve_audit($rel_id, $rel_type, $staff_id = ''){
	$data_new = $this->get_approve_setting($rel_type, true);
	$data_setting = $this->get_approve_setting($rel_type, false);
	$this->delete_approval_details($rel_id, $rel_type);
	$date_send = date('Y-m-d H:i:s');
	$row = [];
	$row['notification_recipient'] = $data_setting->notification_recipient;
	$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));
	$row['staffid'] = $staff_id;
	$row['date_send'] = $date_send;
	$row['rel_id'] = $rel_id;
	$row['rel_type'] = $rel_type;
	$row['sender'] = $staff_id;
	$this->db->insert(db_prefix().'fe_approval_details', $row);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		$link = 'fixed_equipment/view_audit_request/'.$rel_id;
		$string_sub = _l('fe_sent_you_an_approval_request').' '._l($rel_type);
		$this->notifications($staff_id, $link, strtolower($string_sub));
		return $insert_id;
	}
	return 0;
}

/**
	 * delete audit request
	 * @param  integer $id 
	 * @return boolean     
	 */
public function delete_audit_request($id){
	$this->db->where('id', $id);
	$this->db->delete(db_prefix().'fe_audit_requests');
	if($this->db->affected_rows() > 0) {
		$this->db->where('audit_id', $id);
		$this->db->delete(db_prefix().'fe_audit_detail_requests');
		return true;
	}
	return false;
}

	/**
	 * send request approve close audit
	 * @param  array $data     
	 * @param  integer $staff_id 
	 * @return bool           
	 */
	public function send_request_approve_close_audit($rel_id, $rel_type, $staff_id = ''){
		$request_type = 'audit';
		$data_new = $this->get_approve_setting($request_type, true);
		$data_setting = $this->get_approve_setting($request_type, false);
		$this->delete_approval_details($rel_id, $rel_type);
		$date_send = date('Y-m-d H:i:s');
		$notification_recipient_list = '';
		foreach ($data_new as $value) {
			$row = [];
			$row['notification_recipient'] = $data_setting->notification_recipient;
			$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));
			$row['staffid'] = $value->staff;
			$row['date_send'] = $date_send;
			$row['rel_id'] = $rel_id;
			$row['rel_type'] = $rel_type;
			$row['sender'] = $staff_id;
			$this->db->insert(db_prefix().'fe_approval_details', $row);
			if($notification_recipient_list == ''){
				$notification_recipient_list = $data_setting->notification_recipient;
			}
		}
		if($notification_recipient_list != ''){
			$data['notification_recipient'] = $notification_recipient_list;
			$data['rel_id'] = $rel_id;
			$data['rel_type'] = $rel_type;
			$this->session->set_userdata(['send_notify' => $data]);
		}

		// Send notify
		$this->send_notify_approve($rel_id, $rel_type);
		// End Send notify
		return true;
	}
	/**
	 * update audit request
	 * @param  array $data 
	 * @return boolean       
	 */
	public function update_audit_request($data){
		$id = $data['id'];
		$affectedRows = 0;
		$list_detail = json_decode($data['assets_detailt']);
		foreach ($list_detail as $key => $row) {
			if($row[0] != null){
				$adjusted = (isset($row[4]) && is_numeric($row[4])) ? $row[4] : null;
				$maintenance = (isset($row[5]) && is_numeric($row[5])) ? $row[5] : 0;
				$accept = (isset($row[6]) && is_numeric($row[6])) ? $row[6] : 0;
				$data_update['adjusted'] = $adjusted;
				$data_update['accept'] = $accept;
				$data_update['maintenance'] = $maintenance;
				$this->db->where('asset_id', $row[0]);
				$this->db->where('audit_id', $id);
				$this->db->update(db_prefix().'fe_audit_detail_requests', $data_update);
				$affectedRows++;
			}
		}
		if($affectedRows != 0){
			return true;
		}
		return false;
	}

/**
 * change approve close audit
 * @param  array $data 
 * @return boolean       
 */
public function change_approve_close_audit($data){
	$data_hanson = (isset($data['data_hanson']) ? json_decode($data['data_hanson']) : []);
	unset($data['data_hanson']);
	$this->update_asset_quantity_close_audit($data_hanson, $data['rel_id']);
	$this->db->where('rel_id', $data['rel_id']);
	$this->db->where('rel_type', $data['rel_type']);
	$this->db->where('staffid', $data['staffid']);
	$this->db->update(db_prefix() . 'fe_approval_details', $data);
	if ($this->db->affected_rows() > 0) {
		$this->send_notify_approve($data['rel_id'], $data['rel_type'], $data['approve'], $data['staffid']);
		// If has rejected then change status to finish approve
		if($data['approve'] == 2)
		{
			$this->db->where('id', $data['rel_id']);
			$this->db->update(db_prefix().'fe_audit_requests', ['closed' => 2]);
			return true;
		}
		$count_approve_total = $this->count_approve($data['rel_id'],$data['rel_type'])->count;
		$count_approve = $this->count_approve($data['rel_id'],$data['rel_type'],1)->count;
		$count_rejected = $this->count_approve($data['rel_id'],$data['rel_type'],2)->count;
		if(($count_approve + $count_rejected) == $count_approve_total){
			if($count_approve_total == $count_approve){
				$this->db->where('id', $data['rel_id']);
				$this->db->update(db_prefix().'fe_audit_requests', ['closed' => 1]);
				// Change status order to approved
				// $data_audit = $this->get_audits($data['rel_id']);
				// if($data_audit && is_numeric($data_audit->from_order) && $data_audit->from_order > 0){
				// 	$this->update_cart($data_audit->from_order, ['approve_status' => 1]);
				// }
			}
			else{
				$this->db->where('id', $data['rel_id']);
				$this->db->update(db_prefix().'fe_audit_requests', ['closed' => 2]);
			}
		}
		return true;               
	}
	return false;
}
/**
 * update asset quantity close audit
 * @param  array $data_hanson 
 * @param  integer $rel_id      
 * @return boolean              
 */
public function update_asset_quantity_close_audit($data_hanson, $rel_id){
	foreach ($data_hanson as $row) {
		if(($row[6] != null && $row[6] != '') && ($row[4] != null && $row[4] != '')){
			switch (strtolower($row[2])) {
				case 'asset':
				if($row[6] == 1 && $row[4] == 0){
					// Deactive 
					$this->db->where('id', $row[0]);
					$this->db->update(db_prefix().'fe_assets', ['active' => 0]);
				}
				elseif($row[6] == 1 && $row[4] != 0){
					// Active
					$this->db->where('id', $row[0]);
					$this->db->update(db_prefix().'fe_assets', ['active' => 1]);	
				}
				break;
				case 'license':
				if(is_numeric($row[4])){
					if(($row[3] > $row[4]) && $row[6] == 1){
						$query = 'select count(1) as count from '.db_prefix().'fe_seats where license_id = '.$row[0];
						$count_avail = $this->data_query($query)->count;
						if($row[4] < $count_avail){
							$balance = $count_avail - $row[4];
							$this->db->query('delete from '.db_prefix().'fe_seats where license_id='.$row[0].' order by status asc limit '.$balance);
						}
						$this->db->where('id', $row[0]);
						$this->db->update(db_prefix().'fe_assets', ['seats' => $row[4]]);
					}					
				}					
				break;
				case 'accessory':
				if(is_numeric($row[4])){
					if(($row[3] > $row[4]) && $row[6] == 1){
						$query = 'select count(1) as count from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2';
						$count_avail = $this->data_query($query)->count;

						if($row[4] < $count_avail){
							$balance = $count_avail - $row[4];
							$this->db->query('delete from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2 order by id asc limit '.$balance);
						}
						$this->db->where('id', $row[0]);
						$this->db->update(db_prefix().'fe_assets', ['quantity' => $row[4]]);
					}
				}
				break;
				case 'consumable':
				if(is_numeric($row[4])){
					if(($row[3] > $row[4]) && $row[6] == 1){
						$query = 'select count(1) as count from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2';
						$count_avail = $this->data_query($query)->count;
						if($row[4] < $count_avail){
							$balance = $count_avail - $row[4];
							$this->db->query('delete from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2 order by id asc limit '.$balance);
						}
						$this->db->where('id', $row[0]);
						$this->db->update(db_prefix().'fe_assets', ['quantity' => $row[4]]);
					}
				}
				break;
				case 'component':
				if(is_numeric($row[4])){
					if(($row[3] > $row[4]) && $row[6] == 1){
						$query = 'select count(1) as count from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2';
						$count_avail = $this->data_query($query)->count;
						if($row[4] < $count_avail){
							$balance = $count_avail - $row[4];
							$this->db->query('delete from '.db_prefix().'fe_checkin_assets where item_id = '.$row[0].' and status = 2 order by id asc limit '.$balance);
						}
						$this->db->where('id', $row[0]);
						$this->db->update(db_prefix().'fe_assets', ['quantity' => $row[4]]);
					}
				}
				break;
			}
			// Update change to audit detail
			$adjusted = (isset($row[4]) && is_numeric($row[4])) ? $row[4] : null;
			$maintenance = (isset($row[5]) && is_numeric($row[5])) ? $row[5] : 0;
			$accept = (isset($row[6]) && is_numeric($row[6])) ? $row[6] : 0;
			$data_update['adjusted'] = $adjusted;
			$data_update['accept'] = $accept;
			$data_update['maintenance'] = $maintenance;
			$this->db->where('asset_id', $row[0]);
			$this->db->where('audit_id', $rel_id);
			$this->db->update(db_prefix().'fe_audit_detail_requests', $data_update);
		}
	}
	return true;
}

/**
	 * add approver choosee when close audit
	 * @param  array $data     
	 * @param  integer $staff_id 
	 * @return bool           
	 */
public function add_approver_choosee_when_close_audit($rel_id, $rel_type, $staff_id = ''){
	$request_type = 'audit';
	$data_new = $this->get_approve_setting($request_type, true);
	$data_setting = $this->get_approve_setting($request_type, false);
	$this->delete_approval_details($rel_id, $rel_type);
	$date_send = date('Y-m-d H:i:s');
	$row = [];
	$row['notification_recipient'] = $data_setting->notification_recipient;
	$row['approval_deadline'] = date('Y-m-d', strtotime(date('Y-m-d').' +'.$data_setting->number_day_approval.' day'));
	$row['staffid'] = $staff_id;
	$row['date_send'] = $date_send;
	$row['rel_id'] = $rel_id;
	$row['rel_type'] = $rel_type;
	$row['sender'] = $staff_id;
	$this->db->insert(db_prefix().'fe_approval_details', $row);
	$insert_id = $this->db->insert_id();
	if($insert_id){
		// Send notify
		$this->send_notify_approve($rel_id, $rel_type);
		// End Send notify
		return $insert_id;
	}
	return 0;
}

/**
 * count asset by_model
 * @param  integer $model_id 
 * @return integer           
 */
public function count_asset_by_model($model_id){
	$count = 0;
	$this->db->where('model_id', $model_id);
	$this->db->where('active', 1);
	$count_row = $this->db->get(db_prefix().'fe_assets')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}

/**
 * count custom field by field set
 * @param  integer $fieldset_id 
 * @return integer              
 */
public function count_custom_field_by_field_set($fieldset_id){
	$count = 0;
	$this->db->where('fieldset_id', $fieldset_id);
	$count_row = $this->db->get(db_prefix().'fe_custom_fields')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}

/**
 * get list model by fieldset
 * @param  integer $fieldset_id 
 * @return array              
 */
public function get_list_model_by_fieldset($fieldset_id){
	$this->db->where('fieldset_id', $fieldset_id);
	return $this->db->get(db_prefix().'fe_models')->result_array();
}

/**
 * from to date report
 * @return object 
 */
public function from_to_date_report(){
	$from_date = '';
	$to_date = '';
	$months_report = $this->input->post('months_report');
	if($months_report == 'this_month'){
		$from_date = date('Y-m-01');
		$to_date   = date('Y-m-t');
	}

	if($months_report == '1'){ 
		$from_date = date('Y-m-01', strtotime('first day of last month'));
		$to_date   = date('Y-m-t', strtotime('last day of last month'));              
	}


	if($months_report == 'this_year'){
		$from_date = date('Y-m-d', strtotime(date('Y-01-01')));
		$to_date = date('Y-m-d', strtotime(date('Y-12-31')));
	}

	if($months_report == 'last_year'){
		$from_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')));
		$to_date = date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31')));               
	}

	if($months_report == '3'){
		$months_report--;
		$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
		$to_date   = date('Y-m-t');
	}

	if($months_report == '6'){
		$months_report--;
		$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
		$to_date   = date('Y-m-t');
	}

	if($months_report == '12'){
		$months_report--;
		$from_date = date('Y-m-01', strtotime("-$months_report MONTH"));
		$to_date   = date('Y-m-t');

	}

	if($months_report == 'custom'){
		$from_date = fe_format_date($this->input->post('report_from'));
		$to_date   = fe_format_date($this->input->post('report_to'));                                      
	}

	$obj = new stdClass();
	$obj->from_date = $from_date;
	$obj->to_date = $to_date;
	return $obj;
}

/**
 * count total assets
 * @param  string $type 
 * @return integer       
 */
public function count_total_assets($type){
	$count = 0;
	$this->db->where('type', $type);
	$this->db->where('active', 1);
	$count_row = $this->db->get(db_prefix().'fe_assets')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}
/**
 * calculate depreciation
 * @param  string $cost               
 * @param  integer $month_depreciation 
 * @param  date $start_using_date   
 * @param  string $currency_name      
 * @return object                     
 */
public function calculate_depreciation($cost, $month_depreciation, $start_using_date){
	$obj = new stdClass();
	$cost_s = 0;
	$year_depreciation_s = 0;
	$monthly_depreciation_s = 0;

	// depreciation by year
	$year_depreciation_s = $cost / ($month_depreciation / 12);

	// depreciation by month
	$monthly_depreciation_s = $year_depreciation_s / 12;

	// Number day using by month
	$number_day_in_month = date('t', strtotime($start_using_date));
	$number_day_using_by_month = ($number_day_in_month - date('d', strtotime($start_using_date)))+1;

	// Depreciation of month
	$monthly_depreciation_s = ($monthly_depreciation_s / $number_day_in_month) * $number_day_using_by_month;

	$obj->cost = $cost_s;
	$obj->year_depreciation = $year_depreciation_s;
	$obj->monthly_depreciation = $monthly_depreciation_s;
	return $obj;
}

	/**
	 * get list month
	 * @param   $from_date 
	 * @param   $to_date             
	 */
	public function get_list_month($from_date, $to_date){
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
 * count asset by location
 * @param  integer $asset_location 
 * @return integer                 
 */
public function count_asset_by_location($location_id){
	$count = 0;
	$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_assets where active=1 and type="asset" and asset_location = '.$location_id.' and checkin_out = 1')->row();
	if($data){
		$count = $data->count;
	}
	return $count;
}
/**
 * count asset assign by location
 * @param  integer $location_id 
 * @return integer              
 */
public function count_asset_assign_by_location($location_id){
	$count = 0;
	$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_assets where active=1 and type="asset" and location_id = '.$location_id.' and checkin_out = 2')->row();
	if($data){
		$count = $data->count;
	}
	return $count;
}

/**
 * count asset by manufacturer
 * @param  integer $location_id 
 * @return integer              
 */
public function count_asset_by_manufacturer($location_id){
	$count = 0;
	$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_checkin_assets where type = "checkout" and location_id = '.$location_id.' and ((requestable = 0 and request_status = 0) OR (requestable = 1 and request_status = 1))')->row();
	if($data){
		$count = $data->count;
	}
	return $count;
}

/**
 * count total assets supplier
 * @param  string $type 
 * @return integer       
 */
public function count_total_asset_supplier($supplier_id, $type = ''){
	$count = 0;
	$this->db->where('supplier_id', $supplier_id);
	if($type != ''){
		$this->db->where('type', $type);
	}
	$this->db->where('active', 1);
	$count_row = $this->db->get(db_prefix().'fe_assets')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}

/**
 * count total assets manufacturer
 * @param  string $type 
 * @return integer       
 */
public function count_total_asset_manufacturer($manufacturer_id, $type = ''){
	$count = 0;
	$this->db->where('manufacturer_id', $manufacturer_id);
	if($type != ''){
		$this->db->where('type', $type);
	}
	$this->db->where('active', 1);
	$count_row = $this->db->get(db_prefix().'fe_assets')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}

/**
 * count asset by manufacturer
 * @param  integer $location_id 
 * @return integer              
 */
public function count_asset_by_manufacturer_only_asset_type($manufacturer_id){
	$count = 0;
	$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_assets a LEFT JOIN '.db_prefix().'fe_models b ON b.id = a.model_id  where b.manufacturer = '.$manufacturer_id.' and a.type = "asset" and a.active = 1')->row();
	if($data){
		$count = $data->count;
	}
	return $count;
}

/**
 * count asset by category
 * @param  integer $cat_id 
 * @param  string $type   
 * @return integer
 */
public function count_asset_by_category($cat_id, $type){
	$count = 0;
	if($type == 'asset'){
		$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_assets a LEFT JOIN '.db_prefix().'fe_models b ON b.id = a.model_id  where b.category = '.$cat_id.' and a.type = "asset" and a.active = 1')->row();
		if($data){
			$count = $data->count;
		}
	}
	else{
		$data = $this->db->query('select count(1) as count from '.db_prefix().'fe_assets  where category_id = '.$cat_id.' and type = "'.$type.'" and active = 1')->row();
		if($data){
			$count = $data->count;
		}
	}
	return $count;
}

/**
 * count asset by status
 * @param  integer $status id 
 * @return integer           
 */
public function count_asset_by_status($status_id){
	$count = 0;
	$this->db->where('status', $status_id);
	$this->db->where('active', 1);
	$count_row = $this->db->get(db_prefix().'fe_assets')->num_rows();
	if(is_numeric($count_row)){
		$count = $count_row;
	}
	return $count;
}

/**
 * get 2 audit info asset
 * @param  integer $asset_id 
 * @return object           
 */
public function get_2_audit_info_asset($asset_id){
	return $this->db->query('select * from '.db_prefix().'fe_audit_detail_requests a LEFT JOIN '.db_prefix().'fe_audit_requests b ON a.audit_id = b.id where a.asset_id = '.$asset_id.' and b.closed = 1 order by a.date_creator desc limit 2')->result_array();
}

/**
 * get cordinate
 * @return json 
 */
public function get_coordinate($address){
	$coordinate = fe_address2geo($address);
	return $coordinate;
}

	/**
	 * delete fieldset
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_fieldset($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_fieldsets');
		if($this->db->affected_rows() > 0) {
			$this->db->where('fieldset_id', $id);
			$this->db->delete(db_prefix().'fe_custom_fields');
			return true;
		}
		return false;
	}

/**
	 * delete request
	 * @param  integer $id 
	 * @return boolean     
	 */
public function delete_request($id){
	$this->db->where('id', $id);
	$this->db->delete(db_prefix().'fe_checkin_assets');
	if($this->db->affected_rows() > 0) {
		return true;
	}
	return false;
}

/**
 * get staff assets
 * @param  integer $staffid 
 * @return array object          
 */
public function get_staff_assets($staffid){
	$staff_query = '';
	if($staffid != ''){
		$staff_query = ' and b.staff_id='.$staffid;
	}
	$query = 'select * from '.db_prefix().'fe_assets a LEFT JOIN '.db_prefix().'fe_checkin_assets b ON a.checkin_out_id = b.id where a.type="asset" and a.checkin_out=2 and a.active=1 and b.type="checkout" and b.checkout_to="user" and ((b.requestable = 0 and b.request_status = 0) or (b.requestable = 1 and b.request_status = 1))'.$staff_query;
	return $this->db->query($query)->result_array();
}

/**
 * get list asset id has depreciations
 * @return array 
 */
public function get_list_asset_id_has_depreciations(){
	$list_id = [];
	$data_asset = $this->db->query('select id, type, model_id, date_buy, unit_price, depreciation from '.db_prefix().'fe_assets where active=1 and (type="asset" OR type="license")')->result_array();
	foreach ($data_asset as $key => $row) {
		if($row['type'] == 'asset'){
			$data_model = $this->fixed_equipment_model->get_models($row['model_id']);
			if($data_model){
				$eol = _d(get_expired_date($row['date_buy'], $data_model->eol));
				if(is_numeric($data_model->depreciation) && $data_model->depreciation > 0){
					$data_depreciation = $this->fixed_equipment_model->get_depreciations($data_model->depreciation);
					if($data_depreciation && $row['unit_price'] != '' && $row['unit_price'] != 0 && $row['unit_price'] != null){
						$list_id[] = $row['id'];
					}
				}
			}
		}

		if($row['type'] == 'license'){
			if(is_numeric($row['depreciation']) && $row['depreciation'] > 0){
				$data_depreciation = $this->fixed_equipment_model->get_depreciations($row['depreciation']);
				if($data_depreciation && $row['unit_price'] != '' && $row['unit_price'] != 0 && $row['unit_price'] != null){
					$list_id[] = $row['id'];
				}
			}
		}
	}
	return $list_id;
}
/**
 * get list checked out predefined kit staff
 * @param  integer $staffid           
 * @param  integer $predefined_kit_id 
 * @return array                    
 */
public function get_list_checked_out_predefined_kit_staff($staffid, $predefined_kit_id){
	$this->db->where('type', 'checkout');
	$this->db->where('checkout_to', 'user');
	$this->db->where('check_status', 2);
	$this->db->where('staff_id', $staffid);
	$this->db->where('predefined_kit_id', $predefined_kit_id);
	return $this->db->get(db_prefix().'fe_checkin_assets')->result_array();
}

/**
 * count checkin asset by parents
 * @param  integer $parent_id 
 * @return integer            
*/
public function count_checkin_component_by_parents($parent_id){
	$sum = 0;
	$this->db->where('item_id', $parent_id);
	$this->db->where('type', 'checkout');
	$this->db->where('status', 2);
	$data = $this->db->get(db_prefix().'fe_checkin_assets')->result_array();
	foreach ($data as $row) {
		$sum += $row['quantity'];
	}
	return $sum;
}
/**
 * update location for checkout to asset
 * @param  integer $asset_id    
 * @param  integer $location_id 
 * @return boolean              
 */
public function update_location_for_checkout_to_asset($asset_id, $location_id){
	$list_id_assigned = $this->db->query('select '.db_prefix().'fe_assets.id from '.db_prefix().'fe_assets 
		left join '.db_prefix().'fe_checkin_assets 
		on '.db_prefix().'fe_checkin_assets.id = '.db_prefix().'fe_assets.checkin_out_id 
		where '.db_prefix().'fe_assets.active = 1 
		and '.db_prefix().'fe_assets.type = "asset" 
		and '.db_prefix().'fe_checkin_assets.checkout_to = "asset" 
		and '.db_prefix().'fe_checkin_assets.asset_id = '.$asset_id)->result_array();
	$affectedRows = 0;
	foreach ($list_id_assigned as $row) {
		$this->db->where('id', $row['id']);
		$this->db->update(db_prefix().'fe_assets', ['location_id' => $location_id]);
		if($this->db->affected_rows() > 0) {
			$affectedRows++;
		}
	}
	if($affectedRows > 0) {
		return true;
	}
	return false;
}

/**
 * get current asset location
 * @param   $asset_id 
 */
public function get_current_asset_location($asset_id){
	$current_location = '';
	$checkout_to = '';
	$query = 'select * from '.db_prefix().'fe_log_assets where item_id = '.$asset_id.' and action="checkout" order by date_creator desc limit 0,1';
	$data_checkout = $this->db->query($query)->row();
	if($data_checkout){
		$to_id = $data_checkout->to_id;
		$to = $data_checkout->to;
		$checkout_to = $to;
		if($to_id != '' && $to != ''){
			switch ($to) {
				case 'user':
				$department_name = '';
				$data_staff_department = $this->departments_model->get_staff_departments($to_id);
				if($data_staff_department){
					foreach ($data_staff_department as $key => $staff_department) {
						$department_name .= $staff_department['name'].', ';
					}
					if($department_name != ''){
						$department_name = '('.rtrim($department_name,', ').') ';
					}
				}
				$current_location = $department_name.''.get_staff_full_name($to_id);
				break;
				case 'asset':
				$data_assets = $this->get_assets($to_id);
				if($data_assets){
					$current_location = '('.$data_assets->qr_code.') '.$data_assets->assets_name;
				}
				break;
				case 'location':
				$data_locations = $this->get_locations($to_id);
				if($data_locations){
					$current_location = $data_locations->location_name;
				}
				break;
			}
		}
	}
	$obj = new stdClass();
	$obj->current_location = $current_location;
	$obj->checkout_to = $checkout_to;
	return $obj;
}

/**
 * straight line depreciation method
 * @param  double $cost
 * @param  double $depreciation_value
 * @param  double $salvage_value
 * @param  date $purchase_date
 * @return object
 */
public function straight_line_depreciation_method($cost, $depreciation_value, $salvage_value, $purchase_date) {
	$obj = new stdClass();
	// Monthly Depreciation Value = (Cost – Salvage value) / Number of months
	$diff = 0;
	$monthly_depreciation = round(($cost - $salvage_value) / $depreciation_value,2);
	$list_date = fe_get_list_month($purchase_date, date('Y-m-d'));
	if (is_array($list_date) && count($list_date) > 0) {
		foreach ($list_date as $date) {
			$diff += $monthly_depreciation;
		}
	}
	$obj->diff = $diff;
	$obj->current_depreciation = $monthly_depreciation;
	return $obj;
}

/**
 * total maintenance asset cost
 * @param  integer $asset_id 
 * @return decimal           
 */
public function total_maintenance_asset_cost($asset_id){
	$total = 0;
	$data = $this->db->query('select sum(cost) as cost from '.db_prefix().'fe_asset_maintenances where asset_id='.$asset_id)->row();
	if($data){
		$total = $data->cost;
	}
	return $total;
}

	/**
	 * delete permission
	 * @param  integer $id
	 * @return boolean
	 */
	public function delete_permission($id) {
		$str_permissions = '';
		foreach (fe_list_permisstion() as $per_key => $per_value) {
			if (strlen($str_permissions) > 0) {
				$str_permissions .= ",'" . $per_value . "'";
			} else {
				$str_permissions .= "'" . $per_value . "'";
			}
		}
		$sql_where = " feature IN (" . $str_permissions . ") ";
		$this->db->where('staff_id', $id);
		$this->db->where($sql_where);
		$this->db->delete(db_prefix() . 'staff_permissions');

		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get check in out list
	 * @param string $list 
	 * @return array       
	 */
	public function get_check_in_out_list($list){
		$this->db->where('id IN ('.$list.')');
		return $this->db->get(db_prefix().'fe_checkin_assets')->result_array();
	}

	/**
	 * get staff check in out
	 * @param  integer $checkin_out_id 
	 * @return integer                 
	*/
	public function get_staff_check_in_out($checkin_out_id, $i = 0){
		$result = '';
		$data_checkin_out = $this->get_checkin_out_data($checkin_out_id);
		if($data_checkin_out){
			if(is_numeric($data_checkin_out->staff_id) && $data_checkin_out->staff_id > 0){
				$result = $data_checkin_out->staff_id;				
			}
			else{
				if($data_checkin_out->checkout_to == 'asset'){
					$this->db->where('id', $data_checkin_out->asset_id);
					$this->db->where('checkin_out', 2);
					$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
					if($data_asset){
						$result = $this->get_staff_check_in_out($data_asset->checkin_out_id, $i++);					
					}
				}
				else if($data_checkin_out->checkout_to == 'location'){
					$this->db->where('id', $data_checkin_out->location_id);
					$data_location = $this->db->get(db_prefix().'fe_locations')->row();
					if($data_location){
						$result = $data_location->manager;					
					}
				}
			}
		}
		if(is_numeric($result)){
			return $result;
		}
		if($i == 100){
			return 0;
		}
	}

	/**
	 * get_sign_documents
	 * @return array or object 
	 */
	public function get_sign_document($id = '', $where = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_sign_documents')->row();
		}
		else{
			if($where != ''){
				$this->db->where($where);
			}
			return $this->db->get(db_prefix().'fe_sign_documents')->result_array();
		}
	}

	/**
	 * get check in out not yet sign
	 * @return array 
	 */
	public function get_check_in_out_not_yet_sign($staff_id = ''){
		$query = 'select * from '.db_prefix() . 'fe_checkin_assets';
		$id_used = $this->db->query('select GROUP_CONCAT(checkin_out_id SEPARATOR \',\') as id FROM '.db_prefix() . 'fe_sign_documents')->row();
		if(isset($id_used->id) && $id_used->id != ''){
			$query = $query.' where NOT find_in_set(id, "'.$id_used->id.'")';			
			if(is_numeric($staff_id)){
				$query = $query.' and staff_id = '.$staff_id;
			}
		}
		else{
			if(is_numeric($staff_id)){
				$query = $query.' where staff_id = '.$staff_id;
			}
		}
		return $this->db->query($query)->result_array();
	}

	public function add_sign_document($data){
		$checkin_out_id = '';
		if(isset($data['check_in_out_id']) && $data['check_in_out_id'] != '' && isset($data['check_in_out_id'][0])){
			$checkin_out_id = implode(',', $data['check_in_out_id']);
			$data_insert['checkin_out_id'] = $checkin_out_id;
			$data_insert['check_to_staff'] = $this->get_staff_check_in_out($data['check_in_out_id'][0]);
			$this->db->insert(db_prefix().'fe_sign_documents', $data_insert);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				$reference = str_pad($insert_id, 5, '0', STR_PAD_LEFT);
				$this->db->where('id', $insert_id);
				$this->db->update(db_prefix().'fe_sign_documents', ['reference' => $reference]);
				$staff_sign_id = [];
				$staff_sign_id[] = get_staff_user_id();
				$staff_sign_id[] = $data_insert['check_to_staff'];
				foreach ($staff_sign_id	 as $key => $value) {
					$data_signer['sign_document_id'] = $insert_id;
					$data_signer['staff_id'] = $value;
					$this->db->insert(db_prefix().'fe_signers', $data_signer);
				}
				return $insert_id;
			}
			return 0;
		}
	}

	/**
	 * get signer
	 * @return array or object 
	 */
	public function get_signer($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_signers')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_signers')->result_array();
		}
	}

	/**
	 * get signer by master
	 * @return array or object 
	 */
	public function get_signer_by_master($id = ''){
		$this->db->where('sign_document_id', $id);
		return $this->db->get(db_prefix().'fe_signers')->result_array();
	}

	/**
	 * get sign document check in out
	 * @return array 
	 */
	public function get_sign_document_check_in_out($check_in_out){
		return $this->db->query('SELECT * FROM '.db_prefix().'fe_sign_documents where find_in_set('.$check_in_out.', checkin_out_id)')->row();
	}

	/**
	 * change sign document status
	 * @param  integer $id     
	 * @param  integer $status 
	 * @return boolean         
	 */
	public function change_sign_document_status($id, $status){
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'fe_sign_documents', ['status' => $status]);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * update signer info
	 * @param  integer $id   
	 * @param  array $data 
	 * @return boolean       
	 */
	public function update_signer_info($id, $data){
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'fe_signers', $data);
		if($this->db->affected_rows() > 0) {
			$this->db->where('id', $id);
			$signer_data = $this->db->get(db_prefix().'fe_signers')->row();
			if($signer_data){
				$document_id = $signer_data->sign_document_id;
				$list_signer_data = $this->get_signer_by_master($document_id);
				$check = 0;
				foreach ($list_signer_data as $key => $value) {
					if($value['date_of_signing'] != null){
						$check++;
					}
				}
				if($check == 1){
					$this->change_sign_document_status($document_id, 2);
				}
				if($check == 2){
					$this->change_sign_document_status($document_id, 3);
				}
			}
			$data_signer = $this->get_signer_by_master();
			return true;
		}
		return false;
	}

	/**
	 * get assets by qrcode
	 * @param  string $qrcode 
	 * @return object    
	 */
	public function get_asset_by_qr_code($qr_code = ''){
		return $this->db->query('select * from '.db_prefix().'fe_assets where qr_code="'.$qr_code.'"')->row();
	}

	/**
    * data xlsx
    * @param  string $tmpFilePath 
    * @param  string $newFilePath 
    * @return string           
    */
    public function data_import_xlsx_item($tmpFilePath, $newFilePath, $type){
        $arr_insert = [];
        $error_filename = '';
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            $rows = [];
            $arr_insert = [];

            $tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

            if (!file_exists(TEMP_FOLDER)) {
                mkdir(TEMP_FOLDER, 0755);
            }

            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0755);
            }

            // Setup our new file path
            $newFilePath = $tmpDir . $newFilePath;

            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                //Reader file
                $xlsx = new XLSXReader_fin($newFilePath);
                $sheetNames = $xlsx->getSheetNames();
                $data = $xlsx->getSheetData($sheetNames[1]);

                //Writer file
                $width = [];
            	$writer_header = [];
                foreach ($data[0] as $key => $value) {
                	$writer_header[$value] = 'string';
                	$width[] = 50;
                }
                $width[] = 100;
                $writer_header[''] = 'string';
                $writer = new XLSXWriter();
                $writer->writeSheetHeader('Sheet1', $writer_header, $col_options = ['widths' => $width]);

                $total_row_success = 0;
                $total_row_error = 0;
                $total_rows = 0;

                for ($row = 1; $row < count($data); $row++) {
                    $total_rows++;
                    $check_result = $this->check_xlsx_row_item($data[$row], $type);
                    if($check_result->not_error == true){
                    	array_push($arr_insert, $check_result->data);
                    	$res = $this->add_item_to_db($check_result->data, $type);
                    	if($res){
                    		$total_row_success++;
                    	}
                    }
                    else{
                    	 // write error file
                    	 $arr_error = [];
                    	 foreach ($check_result->data as $key => $value) {
                    	 	if($key != 'type'){
                    	 		if($key == 'serial'){
                    	 			$arr_error[] = ((isset($value[0])) ? $value[0] : '');                    	 	                   	 		
                    	 		}
                    	 		else{
                    	 			$arr_error[] = $value;                    	 	                    	 		
                    	 		}
                    	 	}
                    	 }
                    	 $arr_error[] = $check_result->string_error;                    	 	
                         $writer->writeSheetRow('Sheet1', $arr_error);
                    	 $total_row_error++;
                    }
                }
                if ($total_row_error != 0) {
                    $error_filename = 'import_item_error_' . get_staff_user_id() . '_' . strtotime(date('Y-m-d H:i:s')) . '.xlsx';
                    $writer->writeToFile(str_replace($error_filename, FIXED_EQUIPMENT_IMPORT_ITEM_ERROR . $error_filename, $error_filename));
                }
            }
            if (file_exists($newFilePath)) {
                @unlink($newFilePath);
            }
        }

        $out_data = new stdClass();
        $out_data->total_row_success = $total_row_success;
        $out_data->total_row_error = $total_row_error;
        $out_data->total_rows = $total_rows;
        $out_data->arr_insert = $arr_insert;
        $out_data->error_filename = $error_filename;
        return $out_data;
    }

    /**
     * check xlsx row item
     * @param   $data 
     * @param   $type 
     * @return        
     */
    public function check_xlsx_row_item($data, $type){
    	switch ($type) {
    		case 'asset':
    		return $this->check_xlsx_asset($data);
    		break;
    		case 'license':
    		return $this->check_xlsx_license($data);
    		break;
    		case 'accessory':
    		return $this->check_xlsx_accessory($data);
    		break;
    		case 'consumable':
    		return $this->check_xlsx_consumable($data);
    		break;
    		case 'component':
    		return $this->check_xlsx_component($data);
    		break;
    	}
    	return false;
    }
    /**
     * check xlsx asset
     * @param  array $data 
     * @return object       
     */
    public function check_xlsx_asset($data){
    	$obj = new stdClass();
    	$not_error = true;
    	$string_error = '';

    	$data_insert["assets_name"] = ((isset($data[0]) && $data[0] != null) ? $data[0] : '');
    	$serial = (isset($data[1]) ? $data[1] : '');
    	$data_insert["serial"] = [];
    	$data_insert["model_id"] = (isset($data[2]) ? $data[2] : '');
    	$data_insert["status"] = (isset($data[3]) ? $data[3] : '');
    	$data_insert["supplier_id"] = (isset($data[4]) ? $data[4] : '');
    	$data_insert["asset_location"] = (isset($data[5]) ? $data[5] : '');
    	$data_insert["date_buy"] = (isset($data[6]) ? $data[6] : '');
    	$data_insert["unit_price"] = (isset($data[7]) ? $data[7] : '');
    	$data_insert["order_number"] = (isset($data[8]) ? $data[8] : '');
    	$data_insert["warranty_period"] = (isset($data[9]) ? $data[9] : '');
    	$data_insert['requestable'] = (isset($data[10]) ? $data[10] : '');
    	$data_insert["description"] = (isset($data[11]) ? $data[11] : '');

         //Required:
         //Asset tag
         //Model
         //Status
    	if($serial == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_asset_tag') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		$data_insert["serial"][] = $serial;    		
    	}


    	if($data_insert["model_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_model_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["model_id"])){
    			$check_model = $this->get_models($data_insert["model_id"]);
    			if(!$check_model){
    				$not_error = false;
    				$string_error .= ', '._l('fe_model_id') .' '. _l('fe_invalid');
    			}
    			else{
    				if($data_insert["assets_name"] == ''){
    					$data_insert["assets_name"] = $check_model->model_name;
    				}
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_model_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["status"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_status_id') .' '. _l('fe_not_yet_entered');
    	}
        else{
    		if(is_numeric($data_insert["status"])){
    			$check = $this->get_status_labels($data_insert["status"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_status_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_status_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	// Check supplier ID, Location ID, Warranty, Purchase cost
    	if($data_insert["supplier_id"] != ''){
    		if(is_numeric($data_insert["supplier_id"])){
    			$check = $this->get_suppliers($data_insert["supplier_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["asset_location"] != ''){
    		if(is_numeric($data_insert["asset_location"])){
    			$check = $this->get_locations($data_insert["asset_location"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_location_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_location_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["warranty_period"] != ''){
    		if(!is_numeric($data_insert["warranty_period"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_warranty') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["requestable"] != ''){
    		if(!is_numeric($data_insert["requestable"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_requestable') .' '. _l('fe_must_be_number');
    		}
    		else{
    			if(!in_array($data_insert["requestable"], [0,1])){
    				$not_error = false;
    				$string_error .= ', '._l('fe_requestable').' '. strtolower(_l('fe_value')) .' '. _l('fe_invalid');
    			}
    		}
    	}

    	if($data_insert["unit_price"] != ''){
    		if(!is_numeric($data_insert["unit_price"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_purchase_cost') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["date_buy"] != ''){
    		$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["date_buy"], $match) != 1) {
    			$string_error .= ', '._l('fe_purchase_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["date_buy"] = date('Y-m-d', strtotime($data_insert["date_buy"]));
    		}
    	}


        $obj->data = $data_insert;
        $obj->not_error = $not_error;
        $obj->string_error = ltrim($string_error, ', ');
    	return $obj;
    }
    /**
     * check xlsx license
     * @param  array $data 
     * @return object       
     */
    public function check_xlsx_license($data){
    	$obj = new stdClass();
    	$not_error = true;
    	$string_error = '';


    	$data_insert["type"] = 'license'; 
    	$data_insert["assets_name"] = ((isset($data[0]) && $data[0] != null) ? $data[0] : ''); 
    	$data_insert["product_key"] = ((isset($data[1]) && $data[1] != null) ? $data[1] : ''); 
    	$data_insert["seats"] = ((isset($data[2]) && $data[2] != null) ? $data[2] : ''); 
    	$data_insert["licensed_to_name"] = ((isset($data[3]) && $data[3] != null) ? $data[3] : ''); 
    	$data_insert["licensed_to_email"] = ((isset($data[4]) && $data[4] != null) ? $data[4] : ''); 
    	$data_insert["reassignable"] = ((isset($data[5]) && $data[5] != null) ? $data[5] : ''); 
    	$data_insert["maintained"] = ((isset($data[6]) && $data[6] != null) ? $data[6] : ''); 
    	$data_insert["order_number"] = ((isset($data[7]) && $data[7] != null) ? $data[7] : ''); 
    	$data_insert["purchase_order_number"] = ((isset($data[8]) && $data[8] != null) ? $data[8] : ''); 
    	$data_insert["unit_price"] = ((isset($data[9]) && $data[9] != null) ? $data[9] : ''); 
    	$data_insert["date_buy"] = ((isset($data[10]) && $data[10] != null) ? $data[10] : ''); 
    	$data_insert["expiration_date"] = ((isset($data[11]) && $data[11] != null) ? $data[11] : ''); 
    	$data_insert["termination_date"] = ((isset($data[12]) && $data[12] != null) ? $data[12] : ''); 
    	$data_insert["category_id"] = ((isset($data[13]) && $data[13] != null) ? $data[13] : ''); 
    	$data_insert["manufacturer_id"] = ((isset($data[14]) && $data[14] != null) ? $data[14] : ''); 
    	$data_insert["supplier_id"] = ((isset($data[15]) && $data[15] != null) ? $data[15] : ''); 
    	$data_insert["depreciation"] = ((isset($data[16]) && $data[16] != null) ? $data[16] : ''); 
    	$data_insert["description"] = ((isset($data[17]) && $data[17] != null) ? $data[17] : ''); 

         //Required:
         //Software name
         //Category ID
         //Seats
         //Manufacturer
    	if($data_insert["assets_name"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_software_name') .' '. _l('fe_not_yet_entered');
    	}

    	if($data_insert["seats"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_seats') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(!is_numeric($data_insert["seats"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_seats') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["reassignable"] != ''){
    		if(!is_numeric($data_insert["reassignable"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_reassignable') .' '. _l('fe_must_be_number');
    		}
    		else{
    			if(!in_array($data_insert["reassignable"], [0,1])){
    				$not_error = false;
    				$string_error .= ', '._l('fe_reassignable').' '. strtolower(_l('fe_value')) .' '. _l('fe_invalid');
    			}
    		}
    	}

    	if($data_insert["maintained"] != ''){
    		if(!is_numeric($data_insert["maintained"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_reassignable') .' '. _l('fe_must_be_number');
    		}
    		else{
    			if(!in_array($data_insert["maintained"], [0,1])){
    				$not_error = false;
    				$string_error .= ', '._l('fe_reassignable').' '. strtolower(_l('fe_value')) .' '. _l('fe_invalid');
    			}
    		}
    	}

    	if($data_insert["unit_price"] != ''){
    		if(!is_numeric($data_insert["unit_price"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_purchase_cost') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["date_buy"] != ''){
    		$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["date_buy"], $match) != 1) {
    			$string_error .= ', '._l('fe_purchase_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["date_buy"] = date('Y-m-d', strtotime($data_insert["date_buy"]));
    		}
    	}

    	if($data_insert["expiration_date"] != ''){
			$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["expiration_date"], $match) != 1) {
    			$string_error .= ', '._l('fe_expiration_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["expiration_date"] = date('Y-m-d', strtotime($data_insert["expiration_date"]));
    		}
    	}
    	if($data_insert["termination_date"] != ''){
			$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["termination_date"], $match) != 1) {
    			$string_error .= ', '._l('fe_termination_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["termination_date"] = date('Y-m-d', strtotime($data_insert["termination_date"]));
    		}
    	}

    	if($data_insert["category_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_category_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["category_id"])){
    			$check = $this->get_categories($data_insert["category_id"], 'license');
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_category_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_category_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["manufacturer_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["manufacturer_id"])){
    			$check = $this->get_asset_manufacturers($data_insert["manufacturer_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["supplier_id"] != ''){
			if(is_numeric($data_insert["supplier_id"])){
    			$check = $this->get_suppliers($data_insert["supplier_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["depreciation"] != ''){
			if(is_numeric($data_insert["depreciation"])){
    			$check = $this->get_depreciations($data_insert["depreciation"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_depreciation_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_depreciation_id') .' '. _l('fe_must_be_number');
    		}
    	}

        $obj->data = $data_insert;
        $obj->not_error = $not_error;
        $obj->string_error = ltrim($string_error, ', ');
    	return $obj;
    }
    /**
     * add item to database
     * @param array $data 
     * @param string $type 
     */
    public function add_item_to_db($data, $type){
    	switch ($type) {
    		case 'asset':
    		$res = $this->add_asset($data);
    		if(count($res) > 0){
    			return true;
    		}
    		break;
    		case 'license':
    		$res = $this->add_licenses($data);
    		if (is_numeric($res) && $res > 0) {
    			return true;
    		}
    		break;
    		case 'accessory':
    		$res = $this->add_accessories($data);
    		if (is_numeric($res) && $res > 0) {
    			return true;
    		}
    		break;
    		case 'consumable':
    		$res = $this->add_consumables($data);
    		if (is_numeric($res) && $res > 0) {
    			return true;
    		}    		break;
    		case 'component':
    		$res = $this->add_components($data);
    		if (is_numeric($res) && $res > 0) {
    			return true;
    		}    		break;
    	}
    	return false;
    }
    /**
     * check xlsx accessory
     * @param  array $data 
     * @return object       
     */
    public function check_xlsx_accessory($data){
    	$obj = new stdClass();
    	$not_error = true;
    	$string_error = '';
    	$data_insert["type"] = 'accessory';
    	$data_insert["assets_name"] = ((isset($data[0]) && $data[0] != null) ? $data[0] : ''); 
    	$data_insert["model_no"] = ((isset($data[1]) && $data[1] != null) ? $data[1] : ''); 
    	$data_insert["order_number"] = ((isset($data[2]) && $data[2] != null) ? $data[2] : ''); 
    	$data_insert["unit_price"] = ((isset($data[3]) && $data[3] != null) ? $data[3] : ''); 
    	$data_insert["date_buy"] = ((isset($data[4]) && $data[4] != null) ? $data[4] : ''); 
    	$data_insert["quantity"] = ((isset($data[5]) && $data[5] != null) ? $data[5] : ''); 
    	$data_insert["min_quantity"] = ((isset($data[6]) && $data[6] != null) ? $data[6] : ''); 
    	$data_insert["category_id"] = ((isset($data[7]) && $data[7] != null) ? $data[7] : ''); 
    	$data_insert["supplier_id"] = ((isset($data[8]) && $data[8] != null) ? $data[8] : ''); 
    	$data_insert["manufacturer_id"] = ((isset($data[9]) && $data[9] != null) ? $data[9] : ''); 
    	$data_insert["asset_location"] = ((isset($data[10]) && $data[10] != null) ? $data[10] : ''); 

		//Required:
		//Accessory name
		//Category ID
		//Quantity
		if($data_insert["assets_name"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_accessory_name') .' '. _l('fe_not_yet_entered');
    	}
    	if($data_insert["category_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_category_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["category_id"])){
    			$check = $this->get_categories($data_insert["category_id"], 'accessory');
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_category_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_category_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["quantity"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_quantity') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(!is_numeric($data_insert["quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_quantity') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["unit_price"] != ''){
    		if(!is_numeric($data_insert["unit_price"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_purchase_cost') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["date_buy"] != ''){
			$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["date_buy"], $match) != 1) {
    			$string_error .= ', '._l('fe_purchase_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["date_buy"] = date('Y-m-d', strtotime($data_insert["date_buy"]));
    		}
    	}
    	if($data_insert["min_quantity"] != ''){
    		if(!is_numeric($data_insert["min_quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_min_quantity') .' '. _l('fe_must_be_number');
    		}
    	}
		// Check supplier ID, Location ID, Warranty, Purchase cost
    	if($data_insert["supplier_id"] != ''){
    		if(is_numeric($data_insert["supplier_id"])){
    			$check = $this->get_suppliers($data_insert["supplier_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_supplier_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["asset_location"] != ''){
    		if(is_numeric($data_insert["asset_location"])){
    			$check = $this->get_locations($data_insert["asset_location"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_location_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_location_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["manufacturer_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["manufacturer_id"])){
    			$check = $this->get_asset_manufacturers($data_insert["manufacturer_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_must_be_number');
    		}
    	}

    	$obj->data = $data_insert;
    	$obj->not_error = $not_error;
    	$obj->string_error = ltrim($string_error, ', ');
    	return $obj;
    }
    /**
     * check xlsx consumable
     * @param  array $data 
     * @return object       
     */
    public function check_xlsx_consumable($data){
    	$obj = new stdClass();
    	$not_error = true;
    	$string_error = '';
    	$data_insert["type"] = 'consumable';
    	$data_insert["assets_name"] = ((isset($data[0]) && $data[0] != null) ? $data[0] : ''); 
    	$data_insert["model_no"] = ((isset($data[1]) && $data[1] != null) ? $data[1] : ''); 
    	$data_insert["item_no"] = ((isset($data[2]) && $data[2] != null) ? $data[2] : ''); 
    	$data_insert["order_number"] = ((isset($data[3]) && $data[3] != null) ? $data[3] : ''); 
    	$data_insert["unit_price"] = ((isset($data[4]) && $data[4] != null) ? $data[4] : ''); 
    	$data_insert["date_buy"] = ((isset($data[5]) && $data[5] != null) ? $data[5] : ''); 
    	$data_insert["quantity"] = ((isset($data[6]) && $data[6] != null) ? $data[6] : ''); 
    	$data_insert["min_quantity"] = ((isset($data[7]) && $data[7] != null) ? $data[7] : ''); 
    	$data_insert["category_id"] = ((isset($data[8]) && $data[8] != null) ? $data[8] : ''); 
    	$data_insert["manufacturer_id"] = ((isset($data[9]) && $data[9] != null) ? $data[9] : ''); 
    	$data_insert["asset_location"] = ((isset($data[10]) && $data[10] != null) ? $data[10] : ''); 

		//Required:
		//Consumables name
		//Category ID
		//Quantity
		
		if($data_insert["assets_name"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_accessory_name') .' '. _l('fe_not_yet_entered');
    	}
    	if($data_insert["category_id"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_category_id') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(is_numeric($data_insert["category_id"])){
    			$check = $this->get_categories($data_insert["category_id"], 'consumable');
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_category_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_category_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["quantity"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_quantity') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(!is_numeric($data_insert["quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_quantity') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["manufacturer_id"] != ''){
    		if(is_numeric($data_insert["manufacturer_id"])){
    			$check = $this->get_asset_manufacturers($data_insert["manufacturer_id"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_manufacturer_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["asset_location"] != ''){
    		if(is_numeric($data_insert["asset_location"])){
    			$check = $this->get_locations($data_insert["asset_location"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_location_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_location_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["unit_price"] != ''){
    		if(!is_numeric($data_insert["unit_price"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_purchase_cost') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["date_buy"] != ''){
			$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["date_buy"], $match) != 1) {
    			$string_error .= ', '._l('fe_purchase_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["date_buy"] = date('Y-m-d', strtotime($data_insert["date_buy"]));
    		}
    	}

    	if($data_insert["min_quantity"] != ''){
    		if(!is_numeric($data_insert["min_quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_min_quantity') .' '. _l('fe_must_be_number');
    		}
    	}

    	$obj->data = $data_insert;
    	$obj->not_error = $not_error;
    	$obj->string_error = ltrim($string_error, ', ');
    	return $obj;
    }
     /**
     * check xlsx component
     * @param  array $data 
     * @return object       
     */
    public function check_xlsx_component($data){
    	$obj = new stdClass();
    	$not_error = true;
    	$string_error = '';
    	$data_insert["type"] = 'component';
    	$data_insert["assets_name"] = ((isset($data[0]) && $data[0] != null) ? $data[0] : ''); 
    	$data_insert["quantity"] = ((isset($data[1]) && $data[1] != null) ? $data[1] : ''); 
    	$data_insert["min_quantity"] = ((isset($data[2]) && $data[2] != null) ? $data[2] : ''); 
    	$data_insert["series"] = ((isset($data[3]) && $data[3] != null) ? $data[3] : ''); 
    	$data_insert["order_number"] = ((isset($data[4]) && $data[4] != null) ? $data[4] : ''); 
    	$data_insert["unit_price"] = ((isset($data[5]) && $data[5] != null) ? $data[5] : ''); 
    	$data_insert["date_buy"] = ((isset($data[6]) && $data[6] != null) ? $data[6] : ''); 
    	$data_insert["category_id"] = ((isset($data[7]) && $data[7] != null) ? $data[7] : ''); 
    	$data_insert["asset_location"] = ((isset($data[8]) && $data[8] != null) ? $data[8] : ''); 
		//Required:
		//Component name
		//Quantity
		if($data_insert["assets_name"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_component_name') .' '. _l('fe_not_yet_entered');
    	}
    	if($data_insert["quantity"] == ''){
    		$not_error = false;
    		$string_error .= ', '._l('fe_quantity') .' '. _l('fe_not_yet_entered');
    	}
    	else{
    		if(!is_numeric($data_insert["quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_quantity') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["min_quantity"] != ''){
    		if(!is_numeric($data_insert["min_quantity"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_min_quantity') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["asset_location"] != ''){
    		if(is_numeric($data_insert["asset_location"])){
    			$check = $this->get_locations($data_insert["asset_location"]);
    			if(!$check){
    				$not_error = false;
    				$string_error .= ', '._l('fe_location_id') .' '. _l('fe_invalid');
    			}
    		}
    		else{
    			$not_error = false;
    			$string_error .= ', '._l('fe_location_id') .' '. _l('fe_must_be_number');
    		}
    	}
    	if($data_insert["unit_price"] != ''){
    		if(!is_numeric($data_insert["unit_price"])){
    			$not_error = false;
    			$string_error .= ', '._l('fe_purchase_cost') .' '. _l('fe_must_be_number');
    		}
    	}

    	if($data_insert["date_buy"] != ''){
    		$reg_day = '~(0[1-9]|1[012])[-/](0[1-9]|[12][0-9]|3[01])[-/](19|20)\d\d~'; /*mm/dd/YYYY*/
    		if (preg_match($reg_day, $data_insert["date_buy"], $match) != 1) {
    			$string_error .= ', '._l('fe_purchase_date') .' '. _l('invalid');
    			$not_error = false;
    		}
    		else{
    			$data_insert["date_buy"] = date('Y-m-d', strtotime($data_insert["date_buy"]));
    		}
    	}
		if(is_numeric($data_insert["category_id"])){
			$check = $this->get_categories($data_insert["category_id"], 'component');
			if(!$check){
				$not_error = false;
				$string_error .= ', '._l('fe_category_id') .' '. _l('fe_invalid');
			}
		}
    	$obj->data = $data_insert;
    	$obj->not_error = $not_error;
    	$obj->string_error = ltrim($string_error, ', ');
    	return $obj;
    }

    /**
    * update audit detail item
    * @param  integer $asset_id 
    * @param  integer $audit_id 
    * @param  array $data     
    */
    public function update_audit_detail_item($asset_id, $audit_id, $data){
		$this->db->where('asset_id', $asset_id);
		$this->db->where('audit_id', $audit_id);
		$this->db->update(db_prefix().'fe_audit_detail_requests', $data);
    }

	/**
	 * calculate depreciation
	 */
	public function auto_calculate_depreciation(){
		$query = '';
		$list_asset_id = $this->fixed_equipment_model->get_list_asset_id_has_depreciations();
		if(count($list_asset_id) > 0){
			$query = ' AND a.id in ('.implode(',', $list_asset_id).')';
		}
		else{
			$query = ' AND a.id = 0';
		}
		$result = $this->db->query('select a.id, type, unit_price, date_buy, a.depreciation, model_id from '.db_prefix().'fe_assets a LEFT JOIN '.db_prefix().'fe_models b ON b.id = a.model_id where date_buy is not null AND unit_price != "" AND unit_price is not null'.$query)->result_array();
		$current_month = date('Y-m-01');
		foreach ($result as $key => $aRow) {
			if($aRow['date_buy'] == ''){
				continue;
			}

			$depreciation_value = '';
			if($aRow['type'] == 'asset'){
				$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
				if($data_model){
					$eol = _d(get_expired_date($aRow['date_buy'], $data_model->eol));
					if(is_numeric($data_model->depreciation) && $data_model->depreciation > 0){
						$data_depreciation = $this->fixed_equipment_model->get_depreciations($data_model->depreciation);
						if($data_depreciation && $aRow['unit_price'] != '' && $aRow['unit_price'] != 0 && $aRow['unit_price'] != null){
							$depreciation_value = $data_depreciation->term;	
						}
					}
				}
			}

			if($aRow['type'] == 'license'){
				if(is_numeric($aRow['depreciation']) && $aRow['depreciation'] > 0){
					$data_depreciation = $this->fixed_equipment_model->get_depreciations($aRow['depreciation']);
					if($data_depreciation && $aRow['unit_price'] != '' && $aRow['unit_price'] != 0 && $aRow['unit_price'] != null){
						$depreciation_value = $data_depreciation->term;	
					}
				}
			}
			$finish_date = $this->get_finish_month_depreciation($aRow['date_buy'], $depreciation_value);					
			$count_total_month = 0;
			$data_total_month = $this->db->query('select count(1) as count from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'])->row();
			if($data_total_month){
				$count_total_month = $data_total_month->count;
			}
			if($count_total_month >= $depreciation_value && (!$current_month == $finish_date)){
				continue;
			}
			$cost = ($aRow['unit_price'] != '' && $aRow['unit_price'] != null) ? $aRow['unit_price'] : 0;
			
			$from_date = $aRow['date_buy'];
			$to_date = date('Y-m-d');
			$data_item = $this->db->query('select date(date) as date from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'].' order by date desc limit 0,1')->row();
			if($data_item){
				$from_date = date('Y-m-d', strtotime($data_item->date));					
			}
			// Monthly Depreciation Value = (Cost – Salvage value) / Number of months
			$salvage_value = 0;
			$remaining_value = 0;
			$maintenance_cost = 0;
			$count_month_dep = 0;
			$list_date = fe_get_list_month($from_date, $to_date);
			if (is_array($list_date) && count($list_date) > 0) {
				foreach ($list_date as $date) {
					$this->db->where('date(date)', $date);
					$this->db->where('item_id', $aRow['id']);
					$old_data = $this->db->get(db_prefix().'fe_depreciation_items')->row();
					if(!$old_data){
						// Straight line depreciation
						$maintenance_cost = $this->get_maintenance_cost_by_date($aRow['id'], $aRow['date_buy'], $date);
						if($maintenance_cost > 0){
							$data_sum_all = $this->db->query('select sum(value) as sum from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'])->row();
							if($data_sum_all){
								$remaining_value = $data_sum_all->sum;
							}
							$data_count_all = $this->db->query('select count(1) as count from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'])->row();
							if($data_count_all){
								$count_month_dep = $data_count_all->count;
							}
						}
						if(($depreciation_value - $count_month_dep) != 0){
							$monthly_depreciation = ($cost - $salvage_value - $remaining_value + $maintenance_cost) / ($depreciation_value - $count_month_dep);
						}else{
							$monthly_depreciation = 0;
						}

						$this->db->insert(db_prefix().'fe_depreciation_items', [
							'item_id' => $aRow['id'],
							'value' => $monthly_depreciation,
							'date' => $date
						]);

						$insert_id = $this->db->insert_id();

						hooks()->do_action('after_fe_depreciation_added', $insert_id);
					}
					else{
						if($current_month == $date){
							$maintenance_cost = $this->get_maintenance_cost_by_date($aRow['id'], $aRow['date_buy'], $date);
							if($maintenance_cost > 0){
								$data_sum_all = $this->db->query('select sum(value) as sum from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'].' and date(date) != \''.$current_month.'\'')->row();
								if($data_sum_all){
									$remaining_value = $data_sum_all->sum;
								}
								$data_count_all = $this->db->query('select count(1) as count from '.db_prefix().'fe_depreciation_items where item_id = '.$aRow['id'].' and date(date) != \''.$current_month.'\'')->row();
								if($data_count_all){
									$count_month_dep = $data_count_all->count;
								}
							}

							if(($depreciation_value - $count_month_dep) != 0){
								$monthly_depreciation = ($cost - $salvage_value - $remaining_value + $maintenance_cost) / ($depreciation_value - $count_month_dep);
							}else{
								$monthly_depreciation = 0;
							}
							$this->db->where('id', $old_data->id);
							$this->db->update(db_prefix().'fe_depreciation_items', [
								'item_id' => $aRow['id'],
								'value' => $monthly_depreciation,
								'date' => $date
							]);
							hooks()->do_action('after_fe_depreciation_added', $old_data->id);
						}
					}
				}
			}
		}
	}
	/**
	 * insert cron log
	 * @param  date  $date      
	 * @param  string  $cron_name 
	 * @param  integer $rel_id    
	 * @param  string  $rel_type  
	 */
	public function insert_cron_log($date, $cron_name, $rel_id = 0, $rel_type = ''){
		$data['date'] = $date;
		$data['cron_name'] = $cron_name;
		$data['rel_id'] = $rel_id;
		$data['rel_type'] = $rel_type;
		$this->db->insert(db_prefix().'fe_cron_log', $data);
	}

	/**
	 * check cron log
	 * @param  date $date      
	 * @param  string $cron_name 
	 * @return boolean            
	 */
	public function check_cron_log($date, $cron_name){
		$this->db->where('date(date)', $date);
		$this->db->where('cron_name', $cron_name);
		$this->db->select('id');
		$result = $this->db->get(db_prefix().'fe_cron_log')->row();
		if($result){
			return true;
		}
		return false;
	}

	/**
	 * maintenance cost by date
	 * @param  integer $item_id 
	 * @param  date $date    
	 * @return decimal          
	 */
	public function get_maintenance_cost_by_date($item_id, $from_date, $to_date, $where = ''){
		$cost = 0;
		$data = $this->db->query('SELECT sum(cost) as cost FROM '.db_prefix().'fe_asset_maintenances where asset_id = '.$item_id.' and (date(start_date) >= \''.date('Y-m-01', strtotime($from_date)).'\' and date(start_date) <= \''.date('Y-m-t', strtotime($to_date)).'\')'.$where)->row();
		if($data){
			$cost = $data->cost;
		}
		return (float)($cost < 0 ? 0 : $cost);
	}
	/**
	 * get depreciation item info
	 * @param  integer $item_id   
	 * @param  date $from_date 
	 * @param  date $to_date   
	 * @return object            
	 */
	public function get_depreciation_item_info($item_id, $from_date, $to_date){
		$obj = new stdClass();
		$obj->diff = 0;
		$obj->current_depreciation = 0;
		$data_sum_all = $this->db->query('select sum(value) as sum from '.db_prefix().'fe_depreciation_items where item_id = '.$item_id.' and date(date) >= \''.date('Y-m-01', strtotime($from_date)).'\' and date(date) <= \''.date('Y-m-01', strtotime($to_date)).'\'')->row();
		if($data_sum_all){
			$obj->diff = $data_sum_all->sum;
		}
		$data_current = $this->db->query('select value from '.db_prefix().'fe_depreciation_items where item_id = '.$item_id.' and (month(date)='.date('m', strtotime($to_date)).' and year(date)='.date('Y', strtotime($to_date)).')')->row();
		if($data_current){
			$obj->current_depreciation = $data_current->value;
		}
		return $obj;
	}

	/**
	 * get finish month depreciation
	 * @param  integer $depreciation_value 
	 * @return string                     
	 */
	public function get_finish_month_depreciation($date_buy, $depreciation_value){
		$depreciation_value -= 1;
		if($depreciation_value < 0){
			$depreciation_value = 0;
		}
		return date('Y-m-01', strtotime($date_buy.' +'.$depreciation_value.' month'));					
	}

	/**
	 * get manager location
	 * @param  integer $location_id 
	 * @return integer              
	 */
	public function get_manager_location($location_id){
		$this->db->where('id', $location_id);
		$this->db->select('manager');
		$data_location = $this->db->get(db_prefix().'fe_locations')->row();
		if($data_location){
			return $data_location->manager;
		}
		return 0;
	}
	/**
	 * get manager asset
	 * @param  integer $item_id 
	 * @return integer          
	 */
	public function get_manager_asset($item_id){
		$manager = 0;
		$this->db->where('id', $item_id);
		$this->db->select('asset_location, checkin_out_id, checkin_out');
		$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
		if($data_asset){
			if($data_asset->checkin_out == 1 && is_numeric($data_asset->asset_location) && $data_asset->asset_location > 0 && $location_id = $data_asset->asset_location){
				$manager_id = $this->get_manager_location($location_id);
				if($manager_id > 0){
					return $manager_id;
				}
			}
			else if($data_asset->checkin_out == 2){
				$manager = $this->get_staff_check_in_out($data_asset->checkin_out_id);
			}
		}
		return $manager;
	}
	/**
	 * insert checkin component
	 * @param  integer $id       
	 * @param  integer $quantity 
	 */
	public function insert_checkin_component($id, $quantity){
		$this->db->where('id', $id);
		$data_checkout = $this->db->get(db_prefix().'fe_checkin_assets')->row();
		if($data_checkout){
			unset($data_checkout->id);
			$data_checkout->type = 'checkin';
			$data_checkout->item_type = 'component';
			$data_checkout->quantity = $quantity;
			$this->db->insert(db_prefix().'fe_checkin_assets', (array)$data_checkout);
		}
	}

	 /**
     * add warehouse
     * @param array $data 
     */
    public function add_warehouse($data) {
    	if (!isset($data['display'])) {
    		$data['display'] = 0;
    	}
    	$this->db->insert(db_prefix() . 'fe_warehouse', $data);
    	$insert_id = $this->db->insert_id();
    	if ($insert_id) {
    		return $insert_id;
    	}
    	return false;
    }

	/**
	 * update warehouse
	 * @param  array $data
	 * @param  integer $id
	 * @return boolean
	 */
	public function update_warehouse($data) {
		if (!isset($data['display'])) {
			$data['display'] = 0;
		}
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix() . 'fe_warehouse', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * delete warehouse
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_warehouse($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_warehouse');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get warehouses
	 * @param  integer $id 
	 * @return array or object     
	 */
	public function get_warehouses($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_warehouse')->row();
		}
		else{
			return $this->db->get(db_prefix().'fe_warehouse')->result_array();
		}
	}

	 /**
     * item to variation
     * @param  [type] $array_value 
     * @return [type]              
     */
    public function item_to_variation($array_value)
    {
    	$new_array=[];
    	foreach ($array_value as $key =>  $values) {
    	    $name = '';
    	    if($values['attributes'] != null && $values['attributes'] != ''){
    	    	$attributes_decode = json_decode($values['attributes']);
    	    	foreach ($attributes_decode as $n_value) {
    	    		if(is_array($n_value)){
    	    			foreach ($n_value as $n_n_value) {
    	    				if(strlen($name) > 0){
    	    					$name .= '#'.$n_n_value->name.' ( '.$n_n_value->option.' ) ';
    	    				}else{
    	    					$name .= ' #'.$n_n_value->name.' ( '.$n_n_value->option.' ) ';
    	    				}
    	    			}
    	    		}else{
						if(isset($n_value->name) && isset($n_value->option)){
							if(strlen($name) > 0){
								$name .= '#'.$n_value->name.' ( '.$n_value->option.' ) ';
							}else{
								$name .= ' #'.$n_value->name.' ( '.$n_value->option.' ) ';
							}
						}
    	    		}
    	    	}
    	    }
	    	array_push($new_array, [
	    		'id' => $values['id'],
	    		'label' => $values['commodity_code'].'_'.$values['description'],
	    	]);
    	}
    	return $new_array;
    }

    /**
	 * get commodity code name
	 * @return array
	 */
	public function get_commodity_code_name() {
		return [];
		$arr_value = $this->db->query('select * from ' . db_prefix() . 'items where active = 1 AND id not in ( SELECT distinct parent_id from '.db_prefix().'items WHERE parent_id is not null AND parent_id != "0" ) order by id asc')->result_array();
		return $this->item_to_variation($arr_value);
	}

	/**
	 * get unit code name
	 * @return array
	 */
	public function get_units_code_name() {
		return $this->db->query('select unit_type_id as id, unit_name as label from ' . db_prefix() . 'ware_unit_type')->result_array();
	}

	/**
	* get warehouse code name
	* @return array
	*/
	public function get_warehouse_code_name() {
		return $this->db->query('select warehouse_id as id, warehouse_name as label from ' . db_prefix() . 'warehouse where display = 1 order by '.db_prefix().'warehouse.order asc')->result_array();
	}

	/**
	 * get commodity
	 * @param  boolean $id
	 * @return array or object
	 */
	public function get_commodity($id = false) {
		return [];
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'items')->row();
		}
		if ($id == false) {
			return $this->db->query('select * from tblitems')->result_array();
		}
	}

	/**
	 * create goods code
	 * @return	string
	 */
	public function create_goods_code() {
		$goods_code = get_option('fe_inventory_receiving_prefix') . get_option('fe_next_inventory_receiving_mumber');
		return $goods_code;
	}

		/**
	 * get staff
	 * @param  string $id
	 * @param  array  $where
	 * @return array or object
	 */
	public function get_staff($id = '', $where = []) {
		$select_str = '*,CONCAT(firstname," ",lastname) as full_name';
		// Used to prevent multiple queries on logged in staff to check the total unread notifications in core/AdminController.php
		if (is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
			$select_str .= ',(SELECT COUNT(*) FROM ' . db_prefix() . 'notifications WHERE touserid=' . get_staff_user_id() . ' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM ' . db_prefix() . 'todos WHERE finished=0 AND staffid=' . get_staff_user_id() . ') as total_unfinished_todos';
		}
		$this->db->select($select_str);
		$this->db->where($where);
		if (is_numeric($id)) {
			$this->db->where('staffid', $id);
			$staff = $this->db->get(db_prefix() . 'staff')->row();
			if ($staff) {
				$staff->permissions = $this->get_staff_permissions($id);
			}
			return $staff;
		}
		$this->db->order_by('firstname', 'desc');
		return $this->db->get(db_prefix() . 'staff')->result_array();
	}

	/**
	 * wh get grouped
	 * @return [type] 
	 */
	public function wh_get_grouped($can_be = 'inventory_receiving')
	{
		$items = [];
		$list_group = ['asset', 'license', 'accessory' , 'consumable' , 'component'];

		if($can_be == 'inventory_receiving'){
			foreach ($list_group as $type) {
				if($type == 'asset'){
					$_items = $this->db->get(db_prefix() . 'fe_models')->result_array();
					if (count($_items) > 0) {
						foreach ($_items as $i) {
							array_push($items, ['id' => $i['id'].'-model', 'name' => $i['model_no'].' '.$i['model_name']]);
						}
					}
				}
				else{
					$this->db->select('id, assets_name, description, unit_price', $type);
					$this->db->where('type', $type);
					$this->db->where('active', 1);
					$this->db->order_by('description', 'asc');
					$_items = $this->db->get(db_prefix() . 'fe_assets')->result_array();
					if (count($_items) > 0) {
						foreach ($_items as $i) {
							array_push($items, ['id' => $i['id'], 'name' => $i['assets_name']]);
						}
					}
				}
			}
		}
		else{
			$data_inventory_receiving = $this->db->query('select distinct(commodity_code) from '.db_prefix().'fe_goods_receipt_detail where concat(\'\', commodity_code * 1) = commodity_code')->result_array();
			$commodity_code = '';
			foreach ($data_inventory_receiving as $key => $value) {
				$commodity_code .= $value['commodity_code'].', ';
			}
			$commodity_code = rtrim($commodity_code, ', ');

			$data_inventory_receiving = $this->db->query('select distinct(serial_number) from '.db_prefix().'fe_goods_receipt_detail where serial_number != \'\' AND serial_number IS NOT NULL')->result_array();
			$serial_number = '';
			foreach ($data_inventory_receiving as $key => $value) {
				$serial_number .= '\''.$value['serial_number'].'\', ';
			}
			$serial_number = rtrim($serial_number, ', ');
			foreach ($list_group as $type) {
				$this->db->select('id, assets_name, description, unit_price, series, type');
				if($serial_number != '' && $commodity_code != ''){
					$this->db->where('(id IN ('.$commodity_code.') OR series IN ('.$serial_number.'))');					
				}
				elseif($serial_number != '' && $commodity_code == ''){
					$this->db->where('series IN ('.$serial_number.')');					
				}
				elseif($serial_number == '' && $commodity_code != ''){
					$this->db->where('id IN ('.$commodity_code.')');					
				}
				else{
					$this->db->where('id IN (0)');					
				}
				$this->db->where('type', $type);
				$this->db->where('active', 1);
				if($type != 'license'){
					$this->db->where('checkin_out', 1);
				}
				$this->db->order_by('description', 'asc');
				$_items = $this->db->get(db_prefix() . 'fe_assets')->result_array();
				if (count($_items) > 0) {
					foreach ($_items as $i) {
						if($i['type'] == 'asset'){
							$data_goods_receipt_detail = $this->db->query('SELECT warehouse_id FROM '.db_prefix().'fe_goods_receipt_detail where serial_number = \''.$i['series'].'\' order by id desc limit 0,1')->result_array();
						}
						else{
							$data_goods_receipt_detail = $this->db->query('SELECT warehouse_id FROM '.db_prefix().'fe_goods_receipt_detail where commodity_code = '.$i['id'].'')->result_array();
						}
						$available = false;
						if(is_array($data_goods_receipt_detail) && count($data_goods_receipt_detail) > 0){
							foreach ($data_goods_receipt_detail as $key => $value) {
								$available_quantity = $this->get_quantity_inventory_item($i['id'], $value['warehouse_id']);
								if($available_quantity > 0){
									$available = true;
									break;
								}
							}
						}
						if($available){
							array_push($items, ['id' => $i['id'], 'name' => ($i['series'] != '' ? $i['series'].' ' : '').''.$i['assets_name']]);
						}
					}
				}


			}
		}
		return $items;
	}

	 /**
     * create goods receipt row template
     * @param  array   $warehouse_data   
     * @param  string  $name             
     * @param  string  $commodity_name   
     * @param  string  $warehouse_id     
     * @param  string  $quantities       
     * @param  string  $unit_name        
     * @param  string  $unit_price       
     * @param  string  $taxname          
     * @param  string  $lot_number       
     * @param  string  $date_manufacture 
     * @param  string  $commodity_code   
     * @param  string  $tax_rate         
     * @param  string  $tax_money        
     * @param  string  $note             
     * @param  string  $item_key         
     * @param  string  $sub_total        
     * @param  string  $tax_name         
     * @param  string  $tax_id           
     * @param  boolean $is_edit          
     * @return [type]                    
     */
    public function create_goods_receipt_row_template($warehouse_data = [], 
    	$name = '', 
    	$commodity_name = '', 
    	$warehouse_id = '', 
    	$quantities = '', 
    	$unit_price = '', 
    	$taxname = '', 
    	$commodity_code = '', 
    	$tax_rate = '', 
    	$tax_money = '', 
    	$serial_number = '',
    	$note = '', 
    	$item_key = '', 
    	$sub_total = '', 
    	$tax_name = '', 
    	$tax_id = '', 
    	$is_edit = false) {


		$this->load->model('invoice_items_model');
		$row = '';

		$name_commodity_code = 'commodity_code';
		$name_commodity_name = 'commodity_name';
		$name_warehouse_id = 'warehouse_id';
		$name_unit_id = 'unit_id';
		$name_unit_name = 'unit_name';
		$name_quantities = 'quantities';
		$name_unit_price = 'unit_price';
		$name_tax_id_select = 'tax_select';
		$name_tax_id = 'tax';
		$name_tax_money = 'tax_money';
		$name_goods_money = 'goods_money';
		$name_note = 'note';
		$name_tax_rate = 'tax_rate';
		$name_tax_name = 'tax_name';
		$array_attr = [];
		$array_attr_payment = ['data-payment' => 'invoice'];
		$name_sub_total = 'sub_total';
		$name_serial_number = 'serial_number';

		$array_qty_attr = [ 'min' => '0.0', 'step' => 'any'];
		$array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
		$str_rate_attr = 'min="0.0" step="any"';
		$array_serial_number_attr = ['placeholder' => _l('fe_serial_number')];

		if(count($warehouse_data) == 0){
			$warehouse_data = $this->get_warehouses();
		}

		if ($name == '') {
			$row .= '<tr class="main">
                  <td></td>';
			$vehicles = [];
			$array_attr = ['placeholder' => _l('unit_price')];

			$manual             = true;
			$invoice_item_taxes = '';
			$amount = '';
			$sub_total = 0;

		} else {
			$row .= '<tr class="sortable item">
					<td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';
			$name_commodity_code = $name . '[commodity_code]';
			$name_commodity_name = $name . '[commodity_name]';
			$name_warehouse_id = $name . '[warehouse_id]';
			$name_unit_id = $name . '[unit_id]';
			$name_quantities = $name . '[quantities]';
			$name_unit_price = $name . '[unit_price]';
			$name_tax_id_select = $name . '[tax_select][]';
			$name_tax_id = $name . '[tax]';
			$name_tax_money = $name . '[tax_money]';
			$name_goods_money = $name . '[goods_money]';
			$name_note = $name . '[note]';
			$name_tax_rate = $name . '[tax_rate]';
			$name_tax_name = $name .'[tax_name]';
			$name_sub_total = $name .'[sub_total]';
			$name_serial_number = $name .'[serial_number]';

			$array_rate_attr = ['onblur' => 'calculate_total();', 'onchange' => 'calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('unit_price')];

			$array_qty_attr = ['onblur' => 'calculate_total();', 'onchange' => 'calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-quantity' => (float)$quantities];

			if($commodity_code != '' && !is_numeric($commodity_code)){
				$array_qty_attr['readonly'] = true;
			}
			else{
				$array_serial_number_attr['readonly'] = true;
			}

			//case for delivery note: only get warehouse available quantity
			$manual             = false;

			$tax_money = 0;
			$tax_rate_value = 0;

			// if($is_edit){
			// 	$invoice_item_taxes = wh_convert_item_taxes($tax_id, $tax_rate, $tax_name);
			// 	$arr_tax_rate = explode('|', $tax_rate);
			// 	foreach ($arr_tax_rate as $key => $value) {
			// 		$tax_rate_value += (float)$value;
			// 	}
			// }else{
				$invoice_item_taxes = $taxname;
				$tax_rate_data = $this->get_tax_rate($taxname);
				$tax_rate_value = $tax_rate_data['tax_rate'];
			// }

			if((float)$tax_rate_value != 0){
				$tax_money = (float)$unit_price * (float)$quantities * (float)$tax_rate_value / 100;
				$amount = (float)$unit_price * (float)$quantities + (float)$tax_money;
			}else{
				$amount = (float)$unit_price * (float)$quantities;
			}

			$sub_total = (float)$unit_price * (float)$quantities;
			$amount = app_format_number($amount);

		}
		$clients_attr = ["onchange" => "get_vehicle('" . $name_commodity_code . "','" . $name_unit_id . "','" . $name_warehouse_id . "');", "data-none-selected-text" => _l('customer_name'), 'data-customer_id' => 'invoice'];

		$row .= '<td class="">' . render_textarea($name_commodity_name, '', $commodity_name, ['rows' => 2, 'placeholder' => _l('item_description_placeholder'), 'readonly' => true] ) . '</td>';
		$row .= '<td class="warehouse_select">' .
		render_select($name_warehouse_id, $warehouse_data,array('id','name'),'',$warehouse_id,[], ["data-none-selected-text" => _l('warehouse_name')], 'no-margin').
		'</td>';
		$row .= '<td class="quantities">' . 
		render_input($name_quantities, '', $quantities, 'number', $array_qty_attr, [], 'no-margin') . 
		'</td>';

		$row .= '<td class="rate">' . render_input($name_unit_price, '', $unit_price, 'number', $array_rate_attr) . '</td>';
		$row .= '<td class="taxrate">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_key, true, $manual) . '</td>';
		$row .= '<td class="amount" align="right">' . $amount . '</td>';

		$row .= '<td class="hide commodity_code">' . render_input($name_commodity_code, '', $commodity_code, 'text', ['placeholder' => _l('commodity_code')]) . '</td>';
		$row .= '<td class="serial_number">' . render_input($name_serial_number, '', $serial_number, 'text', $array_serial_number_attr) . '</td>';

		if(strlen($serial_number) > 0){
			$name_serial_number_tooltip = _l('fe_serial_number').': '.$serial_number;
		}else{
			$name_serial_number_tooltip = _l('fe_view_serial_number');
		}

		if ($name == '') {
			$row .= '<td><button type="button" onclick="wh_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info btn-add"><i class="fa fa-check"></i></button></td>';
		} else {
			$row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="wh_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;" data-toggle="tooltip" data-original-title="'._l('delete').'"><i class="fa fa-trash"></i></a></td>';
		}
		$row .= '</tr>';
		return $row;
	}


	/**
	 * get taxes dropdown template
	 * @param  [type]  $name     
	 * @param  [type]  $taxname  
	 * @param  string  $type     
	 * @param  string  $item_key 
	 * @param  boolean $is_edit  
	 * @param  boolean $manual   
	 * @return [type]            
	 */
	public function get_taxes_dropdown_template($name, $taxname, $type = '', $item_key = '', $is_edit = false, $manual = false)
	{
        // if passed manually - like in proposal convert items or project
		if($taxname != '' && !is_array($taxname)){
			$taxname = explode(',', $taxname);
		}

		if ($manual == true) {
            // + is no longer used and is here for backward compatibilities
			if (is_array($taxname) || strpos($taxname, '+') !== false) {
				if (!is_array($taxname)) {
					$__tax = explode('+', $taxname);
				} else {
					$__tax = $taxname;
				}
                // Multiple taxes found // possible option from default settings when invoicing project
				$taxname = [];
				foreach ($__tax as $t) {
					$tax_array = explode('|', $t);
					if (isset($tax_array[0]) && isset($tax_array[1])) {
						array_push($taxname, $tax_array[0] . '|' . $tax_array[1]);
					}
				}
			} else {
				$tax_array = explode('|', $taxname);
                // isset tax rate
				if (isset($tax_array[0]) && isset($tax_array[1])) {
					$tax = get_tax_by_name($tax_array[0]);
					if ($tax) {
						$taxname = $tax->name . '|' . $tax->taxrate;
					}
				}
			}
		}
        // First get all system taxes
		$this->load->model('taxes_model');
		$taxes = $this->taxes_model->get();
		$i     = 0;
		foreach ($taxes as $tax) {
			unset($taxes[$i]['id']);
			$taxes[$i]['name'] = $tax['name'] . '|' . $tax['taxrate'];
			$i++;
		}
		if ($is_edit == true) {

            // Lets check the items taxes in case of changes.
            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
			$func_taxes = 'get_' . $type . '_item_taxes';
			if (function_exists($func_taxes)) {
				$item_taxes = call_user_func($func_taxes, $item_key);
			}

			foreach ($item_taxes as $item_tax) {
				$new_tax            = [];
				$new_tax['name']    = $item_tax['taxname'];
				$new_tax['taxrate'] = $item_tax['taxrate'];
				$taxes[]            = $new_tax;
			}
		}

        // In case tax is changed and the old tax is still linked to estimate/proposal when converting
        // This will allow the tax that don't exists to be shown on the dropdowns too.
		if (is_array($taxname)) {
			foreach ($taxname as $tax) {
                // Check if tax empty
				if ((!is_array($tax) && $tax == '') || is_array($tax) && $tax['taxname'] == '') {
					continue;
				};
                // Check if really the taxname NAME|RATE don't exists in all taxes
				if (!value_exists_in_array_by_key($taxes, 'name', $tax)) {
					if (!is_array($tax)) {
						$tmp_taxname = $tax;
						$tax_array   = explode('|', $tax);
					} else {
						$tax_array   = explode('|', $tax['taxname']);
						$tmp_taxname = $tax['taxname'];
						if ($tmp_taxname == '') {
							continue;
						}
					}
					$taxes[] = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1]];
				}
			}
		}

        // Clear the duplicates
		$taxes = $this->wh_uniqueByKey($taxes, 'name');

		$select = '<select class="selectpicker display-block taxes" data-width="100%" name="' . $name . '" multiple data-none-selected-text="' . _l('no_tax') . '">';

		foreach ($taxes as $tax) {
			$selected = '';
			if (is_array($taxname)) {
				foreach ($taxname as $_tax) {
					if (is_array($_tax)) {
						if ($_tax['taxname'] == $tax['name']) {
							$selected = 'selected';
						}
					} else {
						if ($_tax == $tax['name']) {
							$selected = 'selected';
						}
					}
				}
			} else {
				if ($taxname == $tax['name']) {
					$selected = 'selected';
				}
			}

			$select .= '<option value="' . $tax['name'] . '" ' . $selected . ' data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
		}
		$select .= '</select>';

		return $select;
	}

		/**
	 * wh uniqueByKey
	 * @param  [type] $array 
	 * @param  [type] $key   
	 * @return [type]        
	 */
	public function wh_uniqueByKey($array, $key)
    {
        $temp_array = [];
        $i          = 0;
        $key_array  = [];

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]  = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }

        return $temp_array;
    }


    /**
	 * wh get tax rate
	 * @param  [type] $taxname 
	 * @return [type]          
	 */
	public function get_tax_rate($taxname)
	{	
		$tax_rate = 0;
		$tax_rate_str = '';
		$tax_id_str = '';
		$tax_name_str = '';
		if(is_array($taxname)){
			foreach ($taxname as $key => $value) {
				$_tax = explode("|", $value);
				if(isset($_tax[1])){
					$tax_rate += (float)$_tax[1];
					if(strlen($tax_rate_str) > 0){
						$tax_rate_str .= '|'.$_tax[1];
					}else{
						$tax_rate_str .= $_tax[1];
					}

					$this->db->where('name', $_tax[0]);
					$taxes = $this->db->get(db_prefix().'taxes')->row();
					if($taxes){
						if(strlen($tax_id_str) > 0){
							$tax_id_str .= '|'.$taxes->id;
						}else{
							$tax_id_str .= $taxes->id;
						}
					}

					if(strlen($tax_name_str) > 0){
						$tax_name_str .= '|'.$_tax[0];
					}else{
						$tax_name_str .= $_tax[0];
					}
				}
			}
		}
		return ['tax_rate' => $tax_rate, 'tax_rate_str' => $tax_rate_str, 'tax_id_str' => $tax_id_str, 'tax_name_str' => $tax_name_str];
	}


	/**
	 * add goods
	 * @param array $data
	 * @param boolean $id
	 * return boolean
	 */
	public function add_goods_receipt($data, $skip_approval = false) {

		$inventory_receipts = [];
		if (isset($data['newitems'])) {
			$inventory_receipts = $data['newitems'];
			unset($data['newitems']);
		}

		unset($data['item_select']);
		unset($data['commodity_name']);
		unset($data['warehouse_id']);
		unset($data['quantities']);
		unset($data['unit_price']);
		unset($data['tax']);
		unset($data['lot_number']);
		unset($data['date_manufacture']);
		unset($data['expiry_date']);
		unset($data['note']);
		unset($data['unit_name']);
		unset($data['sub_total']);
		unset($data['commodity_code']);
		unset($data['unit_id']);
		unset($data['tax_rate']);
		unset($data['tax_name']);
		unset($data['tax_money']);
		unset($data['goods_money']);
		unset($data['serial_number']);


		$default_warehouse_id = 0;
		if(isset($data['warehouse_id_m'])){
			$data['warehouse_id'] = $data['warehouse_id_m'];
			$default_warehouse_id = $data['warehouse_id_m'];
			unset($data['warehouse_id_m']);
		}

		if(isset($data['expiry_date_m'])){
			$data['expiry_date'] = to_sql_date($data['expiry_date_m']);
			unset($data['expiry_date_m']);
		}
		
		if(isset($data['onoffswitch'])){
			if($data['onoffswitch'] == 'on'){
				$switch_barcode_scanners = true;
				unset($data['onoffswitch']);
			}
		}

		$check_appr = $this->get_approve_setting('inventory_receiving');
		$data['approval'] = 0;
		if ($check_appr && $check_appr != false) {
			$data['approval'] = 0;
		} else {
			$data['approval'] = 1;
		}
		if($skip_approval){
			$data['approval'] = 1;
		}

		if(isset($data['project'])){
			unset($data['project']);
		}

		if(isset($data['save_and_send_request']) ){
			$save_and_send_request = $data['save_and_send_request'];
			unset($data['save_and_send_request']);
		}

		$data['goods_receipt_code'] = $this->create_goods_code();
		$data['date_c'] = fe_format_date($data['date_c']);
		$data['date_add'] = fe_format_date($data['date_add']);
		if(isset($data['expiry_date'])){
			$data['expiry_date'] = fe_format_date($data['expiry_date']);			
		}
		$data['addedfrom'] = get_staff_user_id();
		$data['total_tax_money'] = fe_reformat_currency_asset($data['total_tax_money']);
		$data['total_goods_money'] = fe_reformat_currency_asset($data['total_goods_money']);
		$data['value_of_inventory'] = fe_reformat_currency_asset($data['value_of_inventory']);
		$data['total_money'] = fe_reformat_currency_asset($data['total_money']);
		$data['creator_id'] = get_staff_user_id();

		$this->db->insert(db_prefix() . 'fe_goods_receipt', $data);
		$insert_id = $this->db->insert_id();

		/*insert detail*/
		if ($insert_id) {
			foreach ($inventory_receipts as $inventory_receipt) {
				if($inventory_receipt['warehouse_id'] == ''){
					$inventory_receipt['warehouse_id'] = $default_warehouse_id;
				}
				$inventory_receipt['goods_receipt_id'] = $insert_id;
				$tax_money = 0;
				$tax_rate_value = 0;
				$tax_rate = null;
				$tax_id = null;
				$tax_name = null;
				if(isset($inventory_receipt['tax_select'])){
					$tax_rate_data = $this->get_tax_rate($inventory_receipt['tax_select']);
					$tax_rate_value = $tax_rate_data['tax_rate'];
					$tax_rate = $tax_rate_data['tax_rate_str'];
					$tax_id = $tax_rate_data['tax_id_str'];
					$tax_name = $tax_rate_data['tax_name_str'];
				}

				if((float)$tax_rate_value != 0){
					$tax_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] * (float)$tax_rate_value / 100;
					$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
					$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
				} else {
					$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
					$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
				}

				$sub_total = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];

				$inventory_receipt['tax_money'] = $tax_money;
				$inventory_receipt['tax'] = $tax_id;
				$inventory_receipt['goods_money'] = $goods_money;
				$inventory_receipt['tax_rate'] = $tax_rate;
				$inventory_receipt['sub_total'] = $sub_total;
				$inventory_receipt['tax_name'] = $tax_name;
				unset($inventory_receipt['order']);
				unset($inventory_receipt['id']);
				unset($inventory_receipt['tax_select']);
				$this->db->insert(db_prefix() . 'fe_goods_receipt_detail', $inventory_receipt);
				if (!($check_appr && $check_appr != false)) {
					$data_update_item = [];
					$data_update_item['id'] = $inventory_receipt['commodity_code'];
					$data_update_item['quantities'] = $inventory_receipt['quantities'];
					$data_update_item['serial_number'] = $inventory_receipt['serial_number'];
					$data_update_item['unit_price'] = $inventory_receipt['unit_price'];
					$data_update_item['warehouse_id'] = $inventory_receipt['warehouse_id'];
					// $this->update_item_approve($data_update_item);
				}
			}
		}

		if (isset($insert_id)) {
			/*write log*/
			$data_log = [];
			$data_log['rel_id'] = $insert_id;
			$data_log['rel_type'] = 'stock_import';
			$data_log['staffid'] = get_staff_user_id();
			$data_log['date'] = date('Y-m-d H:i:s');
			$data_log['note'] = "stock_import";
			$this->add_activity_log($data_log);
			/*update next number setting*/
			$this->update_inventory_setting(['fe_next_inventory_receiving_mumber' =>  get_option('fe_next_inventory_receiving_mumber')+1]);
			if ($data['approval'] == 1) {
				$this->update_approve_request($insert_id, 'inventory_receiving', 1);
			}
		}
		return $insert_id;
	}


		/**
	 * update inventory setting
	 * @param  array $data 
	 * @return boolean       
	 */
	public function update_inventory_setting($data)
	{
		$affected_rows=0;
		foreach ($data as $key => $value) {
			$this->db->where('name',$key);
			$this->db->update(db_prefix() . 'options', [
				'value' => $value,
			]);
			if ($this->db->affected_rows() > 0) {
				$affected_rows++;
			}
		}
		if($affected_rows > 0){
			return true;
		}else{
			return false;
		}
	}



	/**
	 * get goods receipt
	 * @param  integer $id
	 * @return array or object
	 */
	public function get_goods_receipt($id) {
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'fe_goods_receipt')->row();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_goods_receipt')->result_array();
		}
	}

	/**
	 * get goods receipt detail
	 * @param  integer $id
	 * @return array
	 */
	public function get_goods_receipt_detail($id) {
		if (is_numeric($id)) {
			$this->db->where('goods_receipt_id', $id);
			return $this->db->get(db_prefix() . 'fe_goods_receipt_detail')->result_array();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_goods_receipt_detail')->result_array();
		}
	}

		/**
     * Gets the html tax receip.
     */
    public function get_html_tax_receip($id){
        $html = '';
        $preview_html = '';
        $html_currency = '';
        $pdf_html = '';
        $taxes = [];
        $t_rate = [];
        $tax_val = [];
        $tax_val_rs = [];
        $tax_name = [];
        $rs = [];

        $this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
        
        $this->db->where('goods_receipt_id', $id);
        $details = $this->db->get(db_prefix().'fe_goods_receipt_detail')->result_array();

        foreach($details as $row){
            if($row['tax'] != ''){
                $tax_arr = explode('|', $row['tax']);

                $tax_rate_arr = [];
                if($row['tax_rate'] != ''){
                    $tax_rate_arr = explode('|', $row['tax_rate']);
                }

                foreach($tax_arr as $k => $tax_it){
                    if(!isset($tax_rate_arr[$k]) ){
                        $tax_rate_arr[$k] = $this->tax_rate_by_id($tax_it);
                    }

                    if(!in_array($tax_it, $taxes)){
                        $taxes[$tax_it] = $tax_it;
                        $t_rate[$tax_it] = $tax_rate_arr[$k];
                        $tax_name[$tax_it] = $this->get_tax_name($tax_it).' ('.$tax_rate_arr[$k].'%)';
                    }
                }
            }
        }

        if(count($tax_name) > 0){
            foreach($tax_name as $key => $tn){
                $tax_val[$key] = 0;
                foreach($details as $row_dt){
                    if(!(strpos($row_dt['tax'] ?? '', $taxes[$key]) === false)){
                        $tax_val[$key] += ($row_dt['quantities']*$row_dt['unit_price']*$t_rate[$key]/100);
                    }
                }
                $pdf_html .= '<tr id="subtotal"><td ></td><td></td><td></td><td class="text_left">'.$tn.'</td><td class="text_right">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
                $preview_html .= '<tr id="subtotal"><td>'.$tn.'</td><td>'.app_format_money($tax_val[$key], '').'</td><tr>';
                $html .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], '').'</td></tr>';
                $html_currency .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
                $tax_val_rs[] = $tax_val[$key];
            }
        }
        
        $rs['pdf_html'] = $pdf_html;
        $rs['preview_html'] = $preview_html;
        $rs['html'] = $html;
        $rs['taxes'] = $taxes;
        $rs['taxes_val'] = $tax_val_rs;
        $rs['html_currency'] = $html_currency;
        return $rs;
    }

    /**
     * Gets the tax name.
     *
     * @param        $tax    The tax
     *
     * @return     string  The tax name.
     */
    public function get_tax_name($tax){
        $this->db->where('id', $tax);
        $tax_if = $this->db->get(db_prefix().'taxes')->row();
        if($tax_if){
            return $tax_if->name;
        }
        return '';
    }

    /**
	 * delete goods receipt
	 * @param  [integer] $id
	 * @return [redirect]
	 */
	public function delete_goods_receipt($id) {
		$affected_rows = 0;
		$this->db->where('goods_receipt_id', $id);
		$this->db->delete(db_prefix() . 'fe_goods_receipt_detail');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'fe_goods_receipt');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}
		if ($affected_rows > 0) {
			return true;
		}
		return false;
	}

	/**
	 * update goods receipt
	 * @param  array  $data 
	 * @param  boolean $id   
	 * @return [type]        
	 */
	public function update_goods_receipt($data, $id = false) {
		$inventory_receipts = [];
		$update_inventory_receipts = [];
		$remove_inventory_receipts = [];
		if(isset($data['isedit'])){
			unset($data['isedit']);
		}
		if (isset($data['newitems'])) {
			$inventory_receipts = $data['newitems'];
			unset($data['newitems']);
		}
		if (isset($data['items'])) {
			$update_inventory_receipts = $data['items'];
			unset($data['items']);
		}
		if (isset($data['removed_items'])) {
			$remove_inventory_receipts = $data['removed_items'];
			unset($data['removed_items']);
		}
		unset($data['item_select']);
		unset($data['commodity_name']);
		unset($data['warehouse_id']);
		unset($data['quantities']);
		unset($data['unit_price']);
		unset($data['tax']);
		unset($data['lot_number']);
		unset($data['date_manufacture']);
		unset($data['expiry_date']);
		unset($data['note']);
		unset($data['unit_name']);
		unset($data['sub_total']);
		unset($data['commodity_code']);
		unset($data['unit_id']);
		unset($data['tax_rate']);
		unset($data['tax_name']);
		unset($data['tax_money']);
		unset($data['goods_money']);
		unset($data['serial_number']);

		$default_warehouse_id = 0;
		if(isset($data['warehouse_id_m'])){
			$data['warehouse_id'] = $data['warehouse_id_m'];
			$default_warehouse_id = $data['warehouse_id_m'];
			unset($data['warehouse_id_m']);
		}

		if(isset($data['expiry_date_m'])){
			$data['expiry_date'] = to_sql_date($data['expiry_date_m']);
			unset($data['expiry_date_m']);
		}
		$check_appr = $this->get_approve_setting('1');
		$data['approval'] = 0;
		if ($check_appr && $check_appr != false) {
			$data['approval'] = 0;
		} else {
			$data['approval'] = 1;
		}
		if(isset($data['save_and_send_request'])){
			$save_and_send_request = $data['save_and_send_request'];
			unset($data['save_and_send_request']);
		}
		if (isset($data['hot_purchase'])) {
			$hot_purchase = $data['hot_purchase'];
			unset($data['hot_purchase']);
		}
		$data['date_c'] = fe_format_date($data['date_c']);
		$data['date_add'] = fe_format_date($data['date_add']);
		if(isset($data['expiry_date'])){
			$data['expiry_date'] = fe_format_date($data['expiry_date']);			
		}
		$data['addedfrom'] = get_staff_user_id();
		$data['total_tax_money'] = fe_reformat_currency_asset($data['total_tax_money']);
		$data['total_goods_money'] = fe_reformat_currency_asset($data['total_goods_money']);
		$data['value_of_inventory'] = fe_reformat_currency_asset($data['value_of_inventory']);
		$data['total_money'] = fe_reformat_currency_asset($data['total_money']);

		$goods_receipt_id = $data['id'];
		unset($data['id']);
		$results = 0;
		$this->db->where('id', $goods_receipt_id);
		$this->db->update(db_prefix() . 'fe_goods_receipt', $data);
		if ($this->db->affected_rows() > 0) {
			$results++;
		}

		/*update save note*/
		// update receipt note
		foreach ($update_inventory_receipts as $inventory_receipt) {
			if($inventory_receipt['warehouse_id'] == ''){
				$inventory_receipt['warehouse_id'] = $default_warehouse_id;
			}
			$tax_money = 0;
			$tax_rate_value = 0;
			$tax_rate = null;
			$tax_id = null;
			$tax_name = null;
			if(isset($inventory_receipt['tax_select'])){
				$tax_rate_data = $this->get_tax_rate($inventory_receipt['tax_select']);
				$tax_rate_value = $tax_rate_data['tax_rate'];
				$tax_rate = $tax_rate_data['tax_rate_str'];
				$tax_id = $tax_rate_data['tax_id_str'];
				$tax_name = $tax_rate_data['tax_name_str'];
			}

			if((float)$tax_rate_value != 0){
				$tax_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] * (float)$tax_rate_value / 100;
				$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
				$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
			}else{
				$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
				$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
			}

			$sub_total = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];

			$inventory_receipt['tax_money'] = $tax_money;
			$inventory_receipt['tax'] = $tax_id;
			$inventory_receipt['goods_money'] = $goods_money;
			$inventory_receipt['tax_rate'] = $tax_rate;
			$inventory_receipt['sub_total'] = $sub_total;
			$inventory_receipt['tax_name'] = $tax_name;
			unset($inventory_receipt['order']);
			unset($inventory_receipt['tax_select']);

			$this->db->where('id', $inventory_receipt['id']);
			if ($this->db->update(db_prefix() . 'fe_goods_receipt_detail', $inventory_receipt)) {
				$results++;
			}
		}

		// delete receipt note
		foreach ($remove_inventory_receipts as $receipt_detail_id) {
			$this->db->where('id', $receipt_detail_id);
			if ($this->db->delete(db_prefix() . 'fe_goods_receipt_detail')) {
				$results++;
			}
		}

		// Add receipt note
		foreach ($inventory_receipts as $inventory_receipt) {
			$inventory_receipt['goods_receipt_id'] = $goods_receipt_id;
			if($inventory_receipt['warehouse_id'] == ''){
				$inventory_receipt['warehouse_id'] = $default_warehouse_id;
			}
			$tax_money = 0;
			$tax_rate_value = 0;
			$tax_rate = null;
			$tax_id = null;
			$tax_name = null;
			if(isset($inventory_receipt['tax_select'])){
				$tax_rate_data = $this->get_tax_rate($inventory_receipt['tax_select']);
				$tax_rate_value = $tax_rate_data['tax_rate'];
				$tax_rate = $tax_rate_data['tax_rate_str'];
				$tax_id = $tax_rate_data['tax_id_str'];
				$tax_name = $tax_rate_data['tax_name_str'];
			}

			if((float)$tax_rate_value != 0){
				$tax_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] * (float)$tax_rate_value / 100;
				$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
				$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'] + (float)$tax_money;
			}else{
				$goods_money = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
				$amount = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];
			}

			$sub_total = (float)$inventory_receipt['unit_price'] * (float)$inventory_receipt['quantities'];

			$inventory_receipt['tax_money'] = $tax_money;
			$inventory_receipt['tax'] = $tax_id;
			$inventory_receipt['goods_money'] = $goods_money;
			$inventory_receipt['tax_rate'] = $tax_rate;
			$inventory_receipt['sub_total'] = $sub_total;
			$inventory_receipt['tax_name'] = $tax_name;
			unset($inventory_receipt['order']);
			unset($inventory_receipt['id']);
			unset($inventory_receipt['tax_select']);

			$this->db->insert(db_prefix() . 'fe_goods_receipt_detail', $inventory_receipt);
			if($this->db->insert_id()){
				$results++;
			}
		}
		
		if (isset($goods_receipt_id)) {
	            //send request approval
			if($save_and_send_request == 'true'){
				// $this->send_request_approve(['rel_id' => $goods_receipt_id, 'rel_type' => '1', 'addedfrom' => $data['addedfrom']]);
			}
		}

		//approval if not approval setting
		if (isset($goods_receipt_id)) {
			// if ($data['approval'] == 1) {
			// 	$this->update_approve_request($goods_receipt_id, 1, 1);
			// }
		}

		// hooks()->do_action('after_wh_goods_receipt_updated', $goods_receipt_id);


		return $results > 0 ? $goods_receipt_id : false;

	}

	/**
	 * update item approve
	 * @param  array $data 
	 * @return array       
	 */
	public function update_item_approve($data){
		if(is_numeric($data['id'])){
			// Update for license, accessory, component, consumable
			$this->db->where('id', $data['id']);
			$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
			if($data_asset){
				switch ($data_asset->type) {
					case 'license':
					$data_all_seat = $this->get_seat_by_parent($data['id']);
					$total_all = count($data_all_seat);
					// Aditional seat
					$identity = $total_all + 1;
					// Insert new Seat
					// for($i = 1; $i <= $data['quantities']; $i++){
					// 	$data_seats['seat_name'] = 'Seat '.$identity;
					// 	$data_seats['to'] = '';
					// 	$data_seats['to_id'] = '';
					// 	$data_seats['license_id'] = $data['id'];
					// 	$data_seats['warehouse_id'] = $data['warehouse_id'];
					// 	$this->db->insert(db_prefix() . 'fe_seats', $data_seats);
					// 	$identity++;
					// }
					// Update Seat for license
					// $this->db->where('id', $data['id']);
					// $this->db->update(db_prefix() . 'fe_assets', ['seats' => ($total_all+ $data['quantities'])]);
					// if($this->db->affected_rows() > 0) {
						hooks()->do_action('after_fe_license_updated', $data['id']);
					// 	return true;
					// }
					break;
					case 'accessory':
					// $this->db->where('id', $data['id']);
					// $this->db->update(db_prefix().'fe_assets', ['quantity' => ($data_asset->quantity + $data['quantities'])]);
					// if($this->db->affected_rows() > 0) {
					// 	return true;
					// }
					break;
					case 'component':
					// $this->db->where('id', $data['id']);
					// $this->db->update(db_prefix().'fe_assets', ['quantity' => ($data_asset->quantity + $data['quantities'])]);
					// if($this->db->affected_rows() > 0) {
						hooks()->do_action('after_fe_component_updated', $data['id']);
					// 	return true;
					// }
					break;
					case 'consumable':
					// $this->db->where('id', $data['id']);
					// $this->db->update(db_prefix().'fe_assets', ['quantity' => ($data_asset->quantity + $data['quantities'])]);
					// if($this->db->affected_rows() > 0) {
						hooks()->do_action('after_fe_consumable_updated', $data['id']);
					// 	return true;
					// }
					break;
				}
				$quantity = $this->get_quantity_inventory_item($data['id'], $data['warehouse_id']);
				//Add log
				$data_log = [
					'rel_type' 			=> 'inventory_receiving',
					'rel_id' 			=> $data['rel_id'],
					'item_id' 			=> $data['id'],
					'old_quantity' 		=> ($quantity - $data['quantities']),
					'quantity' 			=> $quantity,
					'from_warehouse_id' => $data['warehouse_id'],
					'date_add' 			=> date('Y-m-d H:i:s'),
    				'added_from_id' 	=> get_staff_user_id()
    			];
    			$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);
			}
		}
		else{
			// Insert for asset
			$id_exp = explode('-', $data['id']);
			if(isset($id_exp[0]) && $data['serial_number'] != ''){
				$model_id = $id_exp[0];
				$this->db->select('id');
				$this->db->where('type', 'asset');
				$this->db->where('series', $data['serial_number']);
				$exist_data = $this->db->get(db_prefix().'fe_assets')->row();
				if($exist_data){
					$this->db->where('id', $exist_data->id);
					$this->db->update(db_prefix().'fe_assets', ['active' => 1, 'warehouse_id' => $data['warehouse_id']]);
					//Add log
					$data_log = [
						'rel_type' 			=> 'inventory_receiving',
						'rel_id' 			=> $data['rel_id'],
						'item_id' 			=> $exist_data->id,
						'old_quantity' 		=> 0,
						'quantity' 			=> 1,
						'from_warehouse_id' => $data['warehouse_id'],
						'date_add' 			=> date('Y-m-d H:i:s'),
						'added_from_id' 	=> get_staff_user_id()
					];
					$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);
					return true;
				}
				else{
					$data_insert = [];
					$data_insert["alow_add_component"] = 0;
					$data_insert["id"] = '';
					$data_insert["assets_name"] = '';
					$data_insert["model_id"] = $model_id;
					$data_insert["status"] = fe_get_default_status();
					$data_insert["supplier_id"] = '';
					$data_insert["date_buy"] = '';
					$data_insert["order_number"] = '';
					$data_insert["unit_price"] = $data['unit_price'];
					$data_insert["warehouse_id"] = $data['warehouse_id'];
					$data_insert["asset_location"] = '';
					$data_insert["warranty_period"] = '';
					$data_insert["selling_price"] = '';
					$data_insert["rental_price"] = '';
					$data_insert["renting_period"] = '';
					$data_insert["renting_unit"] = '';
					$data_insert["description"] = '';
					$data_insert["serial"][] = $data['serial_number'];
					$result = $this->add_asset($data_insert);
					if (count($result) > 0) {
						foreach ($result as $key => $id) {
							//Add log
							$data_log = [
								'rel_type' 			=> 'inventory_receiving',
								'rel_id' 			=> $data['rel_id'],
								'item_id' 			=> $id,
								'old_quantity' 		=> 0,
								'quantity' 			=> 1,
								'from_warehouse_id' => $data['warehouse_id'],
								'date_add' 			=> date('Y-m-d H:i:s'),
								'added_from_id' 	=> get_staff_user_id()
							];
							$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);
						}
						return true;
					}
				}
			}
		}
		return false;
	}

		/**
	 * check approval detail
	 * @param   integer $rel_id
	 * @param   string $rel_type
	 * @return  boolean
	 */
	public function check_approval_details($rel_id, $rel_type) {
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);
		$approve_status = $this->db->get(db_prefix() . 'fe_approval_details')->result_array();

		if (count($approve_status) > 0) {
			foreach ($approve_status as $value) {
				if ($value['approve'] == -1) {
					return 'reject';
				}
				if ($value['approve'] == 0) {
					$value['staffid'] = explode(', ', $value['staffid']);
					return $value;
				}
			}
			return true;
		}
		return false;
	}

	/**
	* get staff sign
	* @param   integer $rel_id
	* @param   string $rel_type
	* @return  array
	*/
	public function get_staff_sign($rel_id, $rel_type) {
		$this->db->select('*');
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);
		$this->db->where('action', 'sign');
		$approve_status = $this->db->get(db_prefix() . 'fe_approval_details')->result_array();
		if (isset($approve_status)) {
			$array_return = [];
			foreach ($approve_status as $key => $value) {
				array_push($array_return, $value['staffid']);
			}
			return $array_return;
		}
		return [];
	}

	/**
	 *  update approval details
	 * @param  integer $id
	 * @param  array $data
	 * @return boolean
	 */
	public function update_approval_details($id, $data) {
		$data['date'] = date('Y-m-d H:i:s');
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'fe_approval_details', $data);
		if ($this->db->affected_rows() > 0) {
			$this->send_notify_approve($data['rel_id'], $data['rel_type'], $data['approve'], $data['staff_approve']);
			return true;
		}
		return false;
	}

	/**
	 * add activity log
	 * @param array $data
	 * return boolean
	 */
	public function add_activity_log($data) {
		$this->db->insert(db_prefix() . 'fe_activity_log', $data);
		return true;
	}


	/**
	 * update approve request
	 * @param  integer $rel_ids
	 * @param  string $rel_type
	 * @param  integer $status
	 * @return boolean
	 */
	public function update_approve_request($rel_id, $rel_type, $status) {
		$data_update = [];
		switch ($rel_type) {
			case 'inventory_receiving':
			$data_update['approval'] = $status;
			$this->db->where('id', $rel_id);
			$this->db->update(db_prefix() . 'fe_goods_receipt', $data_update);
			if((int)$status == 1){
				//update history stock, inventoty manage after staff approved
				
				$goods_receipt = $this->get_goods_receipt($rel_id);
				$goods_receipt_detail = $this->get_goods_receipt_detail($rel_id);

				$order_type = '';
				if(is_numeric($goods_receipt->from_order) && $goods_receipt->from_order > 0){
					$order_data = $this->get_cart($goods_receipt->from_order);
					if($order_data){
						$order_type = $order_data->type;
					}
				}
				
				if($order_type == '' || $order_type == 'order'){
					foreach ($goods_receipt_detail as $goods_receipt_detail_value) {
						$data_update_item = [];
						$data_update_item['rel_id'] = $rel_id;
						$data_update_item['id'] = $goods_receipt_detail_value['commodity_code'];
						$data_update_item['quantities'] = $goods_receipt_detail_value['quantities'];
						$data_update_item['serial_number'] = $goods_receipt_detail_value['serial_number'];
						$data_update_item['unit_price'] = $goods_receipt_detail_value['unit_price'];
						$data_update_item['warehouse_id'] = $goods_receipt_detail_value['warehouse_id'];
						//Update quantity
						$this->update_item_approve($data_update_item);
					}
				}
				else{
					foreach ($goods_receipt_detail as $goods_receipt_detail_value) {
						$data_update_item = [];
						$data_update_item['rel_id'] = $rel_id;
						$data_update_item['id'] = $goods_receipt_detail_value['commodity_code'];
						$data_update_item['quantities'] = $goods_receipt_detail_value['quantities'];
						$data_update_item['serial_number'] = $goods_receipt_detail_value['serial_number'];
						$data_update_item['unit_price'] = $goods_receipt_detail_value['unit_price'];
						$data_update_item['warehouse_id'] = $goods_receipt_detail_value['warehouse_id'];
						//Checkin
						$this->checkin_item_from_good_receipt($data_update_item);
					}
				}
			}
			return true;
			break;
			case 'inventory_delivery':
			$data_update['approval'] = $status;
			$this->db->where('id', $rel_id);
			$this->db->update(db_prefix() . 'fe_goods_delivery', $data_update);
			if((int)$status == 1){
				$this->checkout_item_from_good_delivery($rel_id);
			}
			return true;
			break;
			default:
			return false;
			break;
		}
	}

	/**
	 * get quantity inventory
	 * @param  integer $warehouse_id
	 * @param  integer $commodity_id
	 * @return object
	 */
	public function get_quantity_inventory($warehouse_id, $commodity_id) {
		if(is_numeric($commodity_id)){
			// Update for license, accessory, component, consumable
			$this->db->where('id', $commodity_id);
			$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
			if($data_asset){
				if($data_asset->type == 'license') {
					return $data_asset->seats;
				}
				else{
					return $data_asset->quantity;
				}
			}
		}
		else{
			return 0;
		}
		return 0;
	}

	/**
	 * send mail
	 * @param  array $data
	 * @return
	 */
	public function send_mail($data ,$staffid = ''){
		if($staffid == ''){
			$staff_id = $staffid;
		}else{
			$staff_id = get_staff_user_id();
		}

		$this->load->model('emails_model');
		if (!isset($data['status'])) {
			$data['status'] = '';
		}
		$get_staff_enter_charge_code = '';
		$mes = 'notify_send_request_approve_project';
		$staff_addedfrom = 0;
		$additional_data = $data['rel_type'];
		$object_type = $data['rel_type'];
		switch ($data['rel_type']) {
			case 'inventory_receiving':
			$type = _l('fe_inventory_receiving');
			$staff_addedfrom = $this->get_goods_receipt($data['rel_id'])->addedfrom;
			$list_approve_status = $this->get_approval_details($data['rel_id'], $data['rel_type']);
			$mes = 'notify_send_request_approve_stock_import';
			$mes_approve = 'notify_send_approve_stock_import';
			$mes_reject = 'notify_send_rejected_stock_import';
			$link = 'fixed_equipment/inventory?tab=inventory_receiving#' . $data['rel_id'];
			break;
			default:
			break;
		}

		$check_approve_status = $this->check_approval_details($data['rel_id'], $data['rel_type'], $data['status']);
		if (isset($check_approve_status['staffid'])) {
			$mail_template = 'send-request-approve';
			if (!in_array(get_staff_user_id(), $check_approve_status['staffid'])) {
				foreach ($check_approve_status['staffid'] as $value) {

					if($value != ''){
					$staff = $this->staff_model->get($value);

					if($staff){
						$notified = add_notification([
							'description' => $mes,
							'touserid' => $staff->staffid,
							'link' => $link,
							'additional_data' => serialize([
								$additional_data,
							]),
						]);
						if ($notified) {
							pusher_trigger_notification([$staff->staffid]);
						}

						//send mail
						
						$this->emails_model->send_simple_email($staff->email, _l('fe_request_approval'), _l('fe_email_send_request_approve', $type) .' <a href="'.admin_url($link).'">'.admin_url($link).'</a> '._l('fe_from_staff', get_staff_full_name($staff_addedfrom)));
					}
				}
				}
			}
		}

		if (isset($data['approve'])) {
			if ($data['approve'] == 1) {
				$mes = $mes_approve;
				$mail_template = 'fe_email_send_approve';
			} else {
				$mes = $mes_reject;
				$mail_template = 'fe_email_send_rejected';
			}

			$staff = $this->staff_model->get($staff_addedfrom);
			$notified = add_notification([
				'description' => $mes,
				'touserid' => $staff->staffid,
				'link' => $link,
				'additional_data' => serialize([
					$additional_data,
				]),
			]);
			if ($notified) {
				pusher_trigger_notification([$staff->staffid]);
			}

			//send mail
			
			$this->emails_model->send_simple_email($staff->email, _l('fe_approval_notification'), _l($mail_template, $type.' <a href="'.admin_url($link).'">'.admin_url($link).'</a> ').' '._l('by_staff', get_staff_full_name(get_staff_user_id())));


			foreach ($list_approve_status as $key => $value) {
				$value['staffid'] = explode(', ', $value['staffid']);
				if ($value['approve'] == 1 && !in_array(get_staff_user_id(), $value['staffid'])) {
					foreach ($value['staffid'] as $staffid) {

						$staff = $this->staff_model->get($staffid);
						$notified = add_notification([
							'description' => $mes,
							'touserid' => $staff->staffid,
							'link' => $link,
							'additional_data' => serialize([
								$additional_data,
							]),
						]);
						if ($notified) {
							pusher_trigger_notification([$staff->staffid]);
						}

						//send mail
						$this->emails_model->send_simple_email($staff->email, _l('fe_approval_notification'), _l($mail_template, $type. ' <a href="'.admin_url($link).'">'.admin_url($link).'</a>').' '._l('fe_by_staff', get_staff_full_name($staff_id)));
					}
				}
			}

		}
	}


	 /**
     * create goods delivery row template
     * @param  array   $warehouse_data       
     * @param  string  $name                 
     * @param  string  $commodity_name       
     * @param  string  $warehouse_id         
     * @param  string  $available_quantity   
     * @param  string  $quantities           
     * @param  string  $unit_name            
     * @param  string  $unit_price           
     * @param  string  $taxname              
     * @param  string  $commodity_code       
     * @param  string  $unit_id              
     * @param  string  $tax_rate             
     * @param  string  $total_money          
     * @param  string  $discount             
     * @param  string  $discount_money       
     * @param  string  $total_after_discount 
     * @param  string  $guarantee_period     
     * @param  string  $expiry_date          
     * @param  string  $lot_number           
     * @param  string  $note                 
     * @param  string  $sub_total            
     * @param  string  $tax_name             
     * @param  string  $tax_id               
     * @param  string  $item_key             
     * @param  boolean $is_edit              
     * @return [type]                        
     */
	 public function create_goods_delivery_row_template($warehouse_data = [], 
	 	$name = '', 
	 	$commodity_name = '', 
	 	$warehouse_id = '', 
	 	$available_quantity = '', 
	 	$quantities = '', 
	 	$unit_name = '', 
	 	$unit_price = '', 
	 	$taxname = '', 
	 	$commodity_code = '', 
	 	$unit_id = '', 
	 	$tax_rate = '', 
	 	$total_money = '', 
	 	$discount = '', 
	 	$discount_money = '', 
	 	$total_after_discount = '', 
	 	$guarantee_period = '', 
	 	$expiry_date = '', 
	 	$lot_number = '', 
	 	$note = '', 
	 	$sub_total = '', 
	 	$tax_name = '', 
	 	$tax_id = '', 
	 	$item_key = '',
	 	$is_edit = false, 
	 	$is_purchase_order = false, 
	 	$serial_number = '', 
	 	$without_checking_warehouse = 0) {

	 	$this->load->model('invoice_items_model');
	 	$row = '';
	 	$name_commodity_code = 'commodity_code';
	 	$name_commodity_name = 'commodity_name';
	 	$name_warehouse_id = 'warehouse_id';
	 	$name_unit_id = 'unit_id';
	 	$name_unit_name = 'unit_name';
	 	$name_available_quantity = 'available_quantity';
	 	$name_quantities = 'quantities';
	 	$name_unit_price = 'unit_price';
	 	$name_tax_id_select = 'tax_select';
	 	$name_tax_id = 'tax_id';
	 	$name_total_money = 'total_money';
	 	$name_lot_number = 'lot_number';
	 	$name_expiry_date = 'expiry_date';
	 	$name_note = 'note';
	 	$name_tax_rate = 'tax_rate';
	 	$name_tax_name = 'tax_name';
	 	$array_attr = [];
	 	$array_attr_payment = ['data-payment' => 'invoice'];
	 	$name_sub_total = 'sub_total';
	 	$name_discount = 'discount';
	 	$name_discount_money = 'discount_money';
	 	$name_total_after_discount = 'total_after_discount';
	 	$name_guarantee_period = 'guarantee_period';
	 	$name_serial_number = 'serial_number';
	 	$name_without_checking_warehouse = 'without_checking_warehouse';
	 	$array_available_quantity_attr = [ 'min' => '0.0', 'step' => 'any', 'readonly' => true];
	 	$array_qty_attr = [ 'min' => '0.0', 'step' => 'any'];
	 	$array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
	 	$array_discount_attr = [ 'min' => '0.0', 'step' => 'any'];
	 	$str_rate_attr = 'min="0.0" step="any"';

	 	if(count($warehouse_data) == 0){
	 		$warehouse_data = $this->get_warehouses();
	 	}

	 	if ($name == '') {
	 		$row .= '<tr class="main">
	 		<td></td>';
	 		$vehicles = [];
	 		$array_attr = ['placeholder' => _l('unit_price')];
	 		$warehouse_id_name_attr = [];
	 		$manual             = true;
	 		$invoice_item_taxes = '';
	 		$amount = '';
	 		$sub_total = 0;

	 	} else {
	 		$row .= '<tr class="sortable item">
	 		<td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';
	 		$name_commodity_code = $name . '[commodity_code]';
	 		$name_commodity_name = $name . '[commodity_name]';
	 		$name_warehouse_id = $name . '[warehouse_id]';
	 		$name_unit_id = $name . '[unit_id]';
	 		$name_unit_name = '[unit_name]';
	 		$name_available_quantity = $name . '[available_quantity]';
	 		$name_quantities = $name . '[quantities]';
	 		$name_unit_price = $name . '[unit_price]';
	 		$name_tax_id_select = $name . '[tax_select][]';
	 		$name_tax_id = $name . '[tax_id]';
	 		$name_total_money = $name . '[total_money]';
	 		$name_lot_number = $name . '[lot_number]';
	 		$name_expiry_date = $name . '[expiry_date]';
	 		$name_note = $name . '[note]';
	 		$name_tax_rate = $name . '[tax_rate]';
	 		$name_tax_name = $name .'[tax_name]';
	 		$name_sub_total = $name .'[sub_total]';
	 		$name_discount = $name .'[discount]';
	 		$name_discount_money = $name .'[discount_money]';
	 		$name_total_after_discount = $name .'[total_after_discount]';
	 		$name_guarantee_period = $name .'[guarantee_period]';
	 		$name_serial_number = $name .'[serial_number]';
	 		$name_without_checking_warehouse = $name .'[without_checking_warehouse]';

	 		$warehouse_id_name_attr = ["onchange" => "get_available_quantity('" . $name_commodity_code . "','" . $name_warehouse_id . "','" . $name_available_quantity . "');", "data-none-selected-text" => _l('warehouse_name'), 'data-from_stock_id' => 'invoice'];
	 		$array_available_quantity_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-available_quantity' => (float)$available_quantity, 'readonly' => true];
	 		if($is_purchase_order){
	 			$array_qty_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-quantity' => (float)$quantities, 'readonly' => true];
	 		}elseif(strlen($serial_number) > 0){
	 			$array_qty_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-quantity' => (float)$quantities, 'readonly' => true];
	 		}else{
	 			$array_qty_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-quantity' => (float)$quantities];
	 		}

	 		$array_rate_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('rate')];
	 		$array_discount_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('discount')];


	 		$manual             = false;

	 		$tax_money = 0;
	 		$tax_rate_value = 0;

	 		if($is_edit){
	 			$invoice_item_taxes = fe_convert_item_taxes($tax_id, $tax_rate, $tax_name);
				$arr_tax_rate = [];
				if($tax_rate != null){
					$arr_tax_rate = explode('|', $tax_rate);
				}
	 			foreach ($arr_tax_rate as $key => $value) {
	 				$tax_rate_value += (float)$value;
	 			}
	 		}else{
	 			$invoice_item_taxes = $taxname;
	 			$tax_rate_data = $this->get_tax_rate($taxname);
	 			$tax_rate_value = $tax_rate_data['tax_rate'];
	 		}

	 		if((float)$tax_rate_value != 0){
	 			$tax_money = (float)$unit_price * (float)$quantities * (float)$tax_rate_value / 100;
	 			$goods_money = (float)$unit_price * (float)$quantities + (float)$tax_money;
	 			$amount = (float)$unit_price * (float)$quantities + (float)$tax_money;
	 		}else{
	 			$goods_money = (float)$unit_price * (float)$quantities;
	 			$amount = (float)$unit_price * (float)$quantities;
	 		}

	 		$sub_total = (float)$unit_price * (float)$quantities;
	 		$amount = app_format_number($amount);

	 	}
	 	$clients_attr = ["onchange" => "get_vehicle('" . $name_commodity_code . "','" . $name_unit_id . "','" . $name_warehouse_id . "');", "data-none-selected-text" => _l(''), 'data-customer_id' => 'invoice'];

	 	$row .= '<td class="">' . render_textarea($name_commodity_name, '', $commodity_name, ['rows' => 2, 'placeholder' => _l('item_description_placeholder'), 'readonly' => true] ) . '</td>';


	 	$row .= '<td class="warehouse_select">' .
	 	render_select($name_warehouse_id, $warehouse_data,array('id','name'),'',$warehouse_id, $warehouse_id_name_attr, ["data-none-selected-text" => _l('warehouse_name')], 'no-margin').
	 	'</td>';
	 	$row .= '<td class="available_quantity">' . 
	 	render_input($name_available_quantity, '', $available_quantity, 'number', $array_available_quantity_attr, [], 'no-margin') . 
	 	'</td>';
	 	$row .= '<td class="quantities">' . render_input($name_quantities, '', $quantities, 'number', $array_qty_attr, [], 'no-margin') .
	 	'</td>';

	 	$row .= '<td class="rate">' . render_input($name_unit_price, '', $unit_price, 'number', $array_rate_attr) . '</td>';
	 	$row .= '<td class="taxrate">' . $this->get_taxes_dropdown_template($name_tax_id_select, $invoice_item_taxes, 'invoice', $item_key, true, $manual) . '</td>';
	 	$row .= '<td class="amount" align="right">' . $amount . '</td>';
	 	$row .= '<td class="discount">' . render_input($name_discount, '', $discount, 'number', $array_discount_attr) . '</td>';
	 	$row .= '<td class="label_discount_money" align="right">' . $amount . '</td>';
	 	$row .= '<td class="label_total_after_discount" align="right">' . $amount . '</td>';

	 	$row .= '<td class="hide commodity_code">' . render_input($name_commodity_code, '', $commodity_code, 'text', ['placeholder' => _l('commodity_code')]) . '</td>';
	 	$row .= '<td class="hide unit_id">' . render_input($name_unit_id, '', $unit_id, 'text', ['placeholder' => _l('unit_id')]) . '</td>';
	 	$row .= '<td class="hide discount_money">' . render_input($name_discount_money, '', $discount_money, 'number', []) . '</td>';
	 	$row .= '<td class="hide total_after_discount">' . render_input($name_total_after_discount, '', $total_after_discount, 'number', []) . '</td>';
	 	$row .= '<td class="hide serial_number">' . render_input($name_serial_number, '', $serial_number, 'text', []) . '</td>';
	 	$row .= '<td class="hide without_checking_warehouse">' . render_input($name_without_checking_warehouse, '', $without_checking_warehouse, 'text', []) . '</td>';

	 	if ($name == '') {
	 		$row .= '<td></td>';
	 		$row .= '<td><button type="button" onclick="wh_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
	 	} else {
	 		if(is_numeric($item_key) && strlen($serial_number) > 0 && is_admin() && get_option('wh_products_by_serial')){
	 			$row .= '<td><a href="#" class="btn btn-success pull-right" data-toggle="tooltip" data-original-title="'._l('wh_change_serial_number').'" onclick="wh_change_serial_number(\''. $name_commodity_code .'\',\''.$name_warehouse_id .'\',\''. $name_serial_number .'\',\''. $name_commodity_name .'\'); return false;"><i class="fa fa-refresh"></i></a></td>';
	 		}else{
	 			$row .= '<td></td>';
	 		}
	 		if($is_purchase_order){
	 			$row .= '<td></td>';
	 		}else{
	 			$row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="wh_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
	 		}
	 	}
	 	$row .= '</tr>';
	 	return $row;
	 }

	 /**
     * get invoices
     * @param  boolean $id 
     * @return array      
     */
    public function  get_invoices($id = false)
    {
    	if (is_numeric($id)) {
    		$this->db->where('id', $id);

    		return $this->db->get(db_prefix() . 'invoices')->row();
    	}
    	if ($id == false) {
    		$arr_invoice = $this->get_invoices_goods_delivery('invoice');
    		if(count($arr_invoice) > 0){
    			return $this->db->query('select *, iv.id as id from '.db_prefix().'invoices as iv left join '.db_prefix().'projects as pj on pj.id = iv.project_id left join '.db_prefix().'clients as cl on cl.userid = iv.clientid  where iv.id NOT IN ('.implode(", ", $arr_invoice).') order by iv.id desc')->result_array();
    		}
    		return $this->db->query('select *, iv.id as id from '.db_prefix().'invoices as iv left join '.db_prefix().'projects as pj on pj.id = iv.project_id left join '.db_prefix().'clients as cl on cl.userid = iv.clientid  order by iv.id desc')->result_array();
    	}
    }

    /**
	 * get invoices goods delivery
	 * @return mixed 
	 */
	public function get_invoices_goods_delivery($type)
	{
		$this->db->where('type', $type);
		$goods_delivery_invoices_pr_orders = $this->db->get(db_prefix().'fe_goods_delivery_invoices_pr_orders')->result_array();
		$array_id = [];
		foreach ($goods_delivery_invoices_pr_orders as $value) {
			array_push($array_id, $value['rel_type']);
		}
		return $array_id;
	}

    /**
	 * create goods delivery code
	 * @return string
	 */
	public function create_goods_delivery_code() {
		$goods_code = get_option('fe_inventory_delivery_prefix') . (get_option('fe_next_inventory_delivery_mumber'));
		return $goods_code;
	}

	/**
	 * get warehouse info item
	 * @param  integer $item_id 
	 * @return array          
	 */
	public function get_warehouse_info_item($item_id){
		$result = [];
		$result['warehouse'] = [];
		$result['item_type'] = '';
		$result['item_name'] = '';
		$result['unit_price'] = '';
		$result['selected_warehouse_id'] = '';
		$result['selected_quantities'] = '';
		$this->db->where('id', $item_id);
		$data_asset  = $this->db->get(db_prefix().'fe_assets')->row();
		if($data_asset){
			if($data_asset->type == 'asset'){
				$data_goods_receipt_detail = $this->db->query('SELECT warehouse_id FROM '.db_prefix().'fe_goods_receipt_detail where serial_number = \''.$data_asset->series.'\'')->result_array();
			}
			else{
				$data_goods_receipt_detail = $this->db->query('SELECT warehouse_id FROM '.db_prefix().'fe_goods_receipt_detail where commodity_code = '.$item_id.'')->result_array();
			}
			if(is_array($data_goods_receipt_detail) && count($data_goods_receipt_detail) > 0){
				$warehouse = [];
				$quantity = [];
				$i = 0;
				foreach ($data_goods_receipt_detail as $key => $value) {
					$warehouse_id = $value['warehouse_id'];
					$available_quantity = $this->get_quantity_inventory_item($item_id, $warehouse_id);
					if($available_quantity > 0){
						if($i == 0){
							$result['selected_warehouse_id'] = $warehouse_id;
							$result['selected_quantities'] = $available_quantity;
							$i = 1;
						}
						$warehouse[] = ['id' => $warehouse_id, 'name' => fe_get_warehouse_name($warehouse_id), 'quantity' => $available_quantity];
					}
				}
				$result['item_name'] = $data_asset->assets_name;
				$result['unit_price'] = $data_asset->unit_price;
				$result['warehouse'] = $warehouse;
				$result['item_type'] = $data_asset->type;
			}
		}
		return $result;
	}

	/**
	 * add goods delivery
	 * @param array  $data
	 * @param boolean $id
	 * return boolean
	 */
	public function add_goods_delivery($data, $skip_approval = false) {
		$goods_deliveries = [];
		if (isset($data['newitems'])) {
			$goods_deliveries = $data['newitems'];
			unset($data['newitems']);
		}

		unset($data['item_select']);
		unset($data['commodity_name']);
		unset($data['warehouse_id']);
		unset($data['available_quantity']);
		unset($data['quantities']);
		unset($data['unit_price']);
		unset($data['note']);
		unset($data['unit_name']);
		unset($data['commodity_code']);
		unset($data['unit_id']);
		unset($data['discount']);
		unset($data['guarantee_period']);
		unset($data['tax_rate']);
		unset($data['tax_name']);
		unset($data['discount_money']);
		unset($data['total_after_discount']);
		unset($data['serial_number']);
		unset($data['without_checking_warehouse']);
		if(isset($data['onoffswitch'])){
			if($data['onoffswitch'] == 'on'){
				$switch_barcode_scanners = true;
				unset($data['onoffswitch']);
			}
		}
		$check_appr = $this->get_approve_setting('inventory_delivery');
		$data['approval'] = 0;
		if ($check_appr && $check_appr != false) {
			$data['approval'] = 0;
		} else {
			$data['approval'] = 1;
		}
		if($skip_approval == true){
			$data['approval'] = 1;
		}

		if(isset($data['edit_approval'])){
			unset($data['edit_approval']);
		}

		if(isset($data['save_and_send_request'])){
			$save_and_send_request = $data['save_and_send_request'];
			unset($data['save_and_send_request']);
		}

		if (isset($data['hot_purchase'])) {
			$hot_purchase = $data['hot_purchase'];
			unset($data['hot_purchase']);
		}

		$data['goods_delivery_code'] = $this->create_goods_delivery_code();
		$data['date_c'] = fe_format_date($data['date_c']);
		$data['date_add'] = fe_format_date($data['date_add']);
		$data['total_money'] 	= fe_reformat_currency_asset($data['total_money']);
		$data['total_discount'] = fe_reformat_currency_asset($data['total_discount']);
		$data['after_discount'] = fe_reformat_currency_asset($data['after_discount']);

		$data['addedfrom'] = get_staff_user_id();
		$data['delivery_status'] = null;
		$this->db->insert(db_prefix() . 'fe_goods_delivery', $data);
		$insert_id = $this->db->insert_id();
		/*update save note*/
		if (isset($insert_id)) {
			foreach ($goods_deliveries as $goods_delivery) {
				$goods_delivery['goods_delivery_id'] = $insert_id;
				$goods_delivery['expiry_date'] = null;
				$goods_delivery['lot_number'] = null;
				$tax_money = 0;
				$tax_rate_value = 0;
				$tax_rate = null;
				$tax_id = null;
				$tax_name = null;
				if(isset($goods_delivery['tax_select'])){
					$tax_rate_data = $this->get_tax_rate($goods_delivery['tax_select']);
					$tax_rate_value = $tax_rate_data['tax_rate'];
					$tax_rate = $tax_rate_data['tax_rate_str'];
					$tax_id = $tax_rate_data['tax_id_str'];
					$tax_name = $tax_rate_data['tax_name_str'];
				}
				if((float)$tax_rate_value != 0){
					$tax_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] * (float)$tax_rate_value / 100;
					$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
					$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
				}else{
					$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
					$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
				}
				$sub_total = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
				$goods_delivery['tax_id'] = $tax_id;
				$goods_delivery['total_money'] = $total_money;
				$goods_delivery['tax_rate'] = $tax_rate;
				$goods_delivery['sub_total'] = $sub_total;
				$goods_delivery['tax_name'] = $tax_name;
				unset($goods_delivery['order']);
				unset($goods_delivery['id']);
				unset($goods_delivery['tax_select']);
				unset($goods_delivery['unit_name']);
				if(isset($goods_delivery['without_checking_warehouse'])){
					unset($goods_delivery['without_checking_warehouse']);
				}
				$this->db->insert(db_prefix() . 'fe_goods_delivery_detail', $goods_delivery);
			}

			/*write log*/
			$data_log = [];
			$data_log['rel_id'] = $insert_id;
			$data_log['rel_type'] = 'stock_export';
			$data_log['staffid'] = get_staff_user_id();
			$data_log['date'] = date('Y-m-d H:i:s');
			$data_log['note'] = "stock_export";
			$this->add_activity_log($data_log);
			/*update next number setting*/
			$this->update_inventory_setting(['fe_next_inventory_delivery_mumber' =>  get_option('fe_next_inventory_delivery_mumber')+1]);
			if ($data['approval'] == 1) {
				$this->update_approve_request($insert_id, 'inventory_delivery', 1);
			}
		}
		return $insert_id > 0 ? $insert_id : false;
	}

	/**
	 * get activity log
	 * @param   integer $rel_id
	 * @param   string $rel_type
	 * @return  array
	 */
	public function get_activity_log($rel_id, $rel_type) {
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);
		return $this->db->get(db_prefix() . 'fe_activity_log')->result_array();
	}

	/**
	 * get goods delivery detail
	 * @param  integer $id
	 * @return array
	 */
	public function get_goods_delivery_detail($id) {
		if (is_numeric($id)) {
			$this->db->where('goods_delivery_id', $id);
			return $this->db->get(db_prefix() . 'fe_goods_delivery_detail')->result_array();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_goods_delivery_detail')->result_array();
		}
	}

	/**
	 * get goods delivery
	 * @param  integer $id
	 * @return array or object
	 */
	public function get_goods_delivery($id) {
		if (is_numeric($id)) {
			$this->db->where('id', $id);

			return $this->db->get(db_prefix() . 'fe_goods_delivery')->row();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_goods_delivery order by id desc')->result_array();
		}
	}

	/**
	 * get packing list by deivery note
	 * @param  [type] $delivery_id 
	 * @return [type]              
	 */
	public function get_packing_list_by_deivery_note($delivery_id)
	{
		$this->db->where('delivery_note_id', $delivery_id);
		$this->db->order_by('datecreated', 'asc');
		$packing_lists = $this->db->get(db_prefix().'fe_packing_lists')->result_array();
		return $packing_lists;
	}

	/**
	 * get html tax delivery
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function get_html_tax_delivery($id){
        $html = '';
        $html_currency = '';
        $preview_html = '';
        $pdf_html = '';
        $taxes = [];
        $t_rate = [];
        $tax_val = [];
        $tax_val_rs = [];
        $tax_name = [];
        $rs = [];

        $this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
        
        $this->db->where('goods_delivery_id', $id);
        $details = $this->db->get(db_prefix().'fe_goods_delivery_detail')->result_array();

        foreach($details as $row){
            if($row['tax_id'] != ''){
                $tax_arr = explode('|', $row['tax_id']);

                $tax_rate_arr = [];
                if($row['tax_rate'] != ''){
                    $tax_rate_arr = explode('|', $row['tax_rate']);
                }

                foreach($tax_arr as $k => $tax_it){
                    if(!isset($tax_rate_arr[$k]) ){
                        $tax_rate_arr[$k] = $this->tax_rate_by_id($tax_it);
                    }

                    if(!in_array($tax_it, $taxes)){
                        $taxes[$tax_it] = $tax_it;
                        $t_rate[$tax_it] = $tax_rate_arr[$k];
                        $tax_name[$tax_it] = $this->get_tax_name($tax_it).' ('.$tax_rate_arr[$k].'%)';
                    }
                }
            }
        }

        if(count($tax_name) > 0){
            foreach($tax_name as $key => $tn){
                $tax_val[$key] = 0;
                foreach($details as $row_dt){
                    if(!(strpos($row_dt['tax_id'], $taxes[$key]) === false)){
                        $tax_val[$key] += ($row_dt['quantities']*$row_dt['unit_price']*$t_rate[$key]/100);
                    }
                }
                $pdf_html .= '<tr id="subtotal"><td ></td><td></td><td></td><td class="text_left">'.$tn.'</td><td class="text_right">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
                $preview_html .= '<tr id="subtotal"><td>'.$tn.'</td><td>'.app_format_money($tax_val[$key], '').'</td><tr>';
                $html .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], '').'</td></tr>';
                $html_currency .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
                $tax_val_rs[] = $tax_val[$key];
            }
        }
        
        $rs['pdf_html'] = $pdf_html;
        $rs['preview_html'] = $preview_html;
        $rs['html'] = $html;
        $rs['taxes'] = $taxes;
        $rs['taxes_val'] = $tax_val_rs;
        $rs['html_currency'] = $html_currency;
        return $rs;
    }

    /**
     * { tax rate by id }
     *
     * @param        $tax_id  The tax identifier
     */
    public function tax_rate_by_id($tax_id){
        $this->db->where('id', $tax_id);
        $tax = $this->db->get(db_prefix().'taxes')->row();
        if($tax){
            return $tax->taxrate;
        }
        return 0;
    }

    /**
	 * delete goods delivery
	 * @param  [integer] $id
	 * @return [redirect]
	 */
	public function delete_goods_delivery($id) {
		$affected_rows = 0;
		$this->db->where('goods_delivery_id', $id);
		$this->db->delete(db_prefix() . 'fe_goods_delivery_detail');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'fe_goods_delivery');
		if ($this->db->affected_rows() > 0) {

			$affected_rows++;
		}

		$packing_list_ids = [];
		$packing_lists = $this->get_packing_list_by_deivery_note($id);
		foreach ($packing_lists as $value) {
		    $packing_list_ids[] = $value['id'];
		}

		if(count($packing_list_ids) > 0){
			$this->db->where('packing_list_id IN ('.implode(',', $packing_list_ids).')');
			$this->db->delete(db_prefix() . 'fe_packing_list_details');
			if ($this->db->affected_rows() > 0) {
				$affected_rows++;
			}
		}

		$this->db->where('delivery_note_id', $id);
		$this->db->delete(db_prefix() . 'fe_packing_lists');
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		if ($affected_rows > 0) {
			return true;
		}
		return false;
	}

   /**
     * update goods delivery
     * @param [type]  $data 
     * @param boolean $id   
     */
    public function update_goods_delivery($data, $id = false) {
    	$results=0;

    	$goods_deliveries = [];
		$update_goods_deliveries = [];
		$remove_goods_deliveries = [];
		if(isset($data['isedit'])){
			unset($data['isedit']);
		}

		if (isset($data['newitems'])) {
			$goods_deliveries = $data['newitems'];
			unset($data['newitems']);
		}

		if (isset($data['items'])) {
			$update_goods_deliveries = $data['items'];
			unset($data['items']);
		}
		if (isset($data['removed_items'])) {
			$remove_goods_deliveries = $data['removed_items'];
			unset($data['removed_items']);
		}

		unset($data['item_select']);
		unset($data['commodity_name']);
		unset($data['warehouse_id']);
		unset($data['available_quantity']);
		unset($data['quantities']);
		unset($data['unit_price']);
		unset($data['note']);
		unset($data['unit_name']);
		unset($data['commodity_code']);
		unset($data['unit_id']);
		unset($data['discount']);
		unset($data['guarantee_period']);
		unset($data['tax_rate']);
		unset($data['tax_name']);
		unset($data['discount_money']);
		unset($data['total_after_discount']);
		unset($data['serial_number']);
		unset($data['without_checking_warehouse']);

    	$check_appr = $this->get_approve_setting('inventory_delivery');
    	$data['approval'] = 0;
    	if ($check_appr && $check_appr != false) {
    		$data['approval'] = 0;
    	} else {
    		$data['approval'] = 1;
    	}

    	if (isset($data['hot_purchase'])) {
    		$hot_purchase = $data['hot_purchase'];
    		unset($data['hot_purchase']);
    	}

    	if(isset($data['edit_approval'])){
    		unset($data['edit_approval']);
    	}

    	if(isset($data['save_and_send_request']) ){
	    		$save_and_send_request = $data['save_and_send_request'];
	    		unset($data['save_and_send_request']);
    	}

    			$data['date_c'] = fe_format_date($data['date_c']);
		$data['date_add'] = fe_format_date($data['date_add']);
		$data['total_money'] 	= fe_reformat_currency_asset($data['total_money']);
		$data['total_discount'] = fe_reformat_currency_asset($data['total_discount']);
		$data['after_discount'] = fe_reformat_currency_asset($data['after_discount']);

    	$data['addedfrom'] = get_staff_user_id();

    	$goods_delivery_id = $data['id'];
    	unset($data['id']);

    	$this->db->where('id', $goods_delivery_id);
    	$this->db->update(db_prefix() . 'fe_goods_delivery', $data);
    	if ($this->db->affected_rows() > 0) {
			$results++;
		}

    	/*update googs delivery*/

    	foreach ($update_goods_deliveries as $goods_delivery) {
			$tax_money = 0;
			$tax_rate_value = 0;
			$tax_rate = null;
			$tax_id = null;
			$tax_name = null;
			if(isset($goods_delivery['tax_select'])){
				$tax_rate_data = $this->get_tax_rate($goods_delivery['tax_select']);
				$tax_rate_value = $tax_rate_data['tax_rate'];
				$tax_rate = $tax_rate_data['tax_rate_str'];
				$tax_id = $tax_rate_data['tax_id_str'];
				$tax_name = $tax_rate_data['tax_name_str'];
			}

			if((float)$tax_rate_value != 0){
				$tax_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] * (float)$tax_rate_value / 100;
				$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
				$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
			}else{
				$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
				$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
			}

			$sub_total = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];

			$goods_delivery['tax_id'] = $tax_id;
			$goods_delivery['total_money'] = $total_money;
			$goods_delivery['tax_rate'] = $tax_rate;
			$goods_delivery['sub_total'] = $sub_total;
			$goods_delivery['tax_name'] = $tax_name;

			unset($goods_delivery['order']);
			unset($goods_delivery['tax_select']);
			unset($goods_delivery['unit_name']);
			if(isset($goods_delivery['without_checking_warehouse'])){
				unset($goods_delivery['without_checking_warehouse']);
			}


			$this->db->where('id', $goods_delivery['id']);
			if ($this->db->update(db_prefix() . 'fe_goods_delivery_detail', $goods_delivery)) {
				$results++;
			}
		}

		// delete receipt note
		foreach ($remove_goods_deliveries as $goods_deliver_id) {
			$this->db->where('id', $goods_deliver_id);
			if ($this->db->delete(db_prefix() . 'fe_goods_delivery_detail')) {
				$results++;
			}
		}

		// Add goods deliveries
		foreach ($goods_deliveries as $goods_delivery) {
			$goods_delivery['goods_delivery_id'] = $goods_delivery_id;
			$goods_delivery['expiry_date'] = null;
			$goods_delivery['lot_number'] = null;

			$tax_money = 0;
			$tax_rate_value = 0;
			$tax_rate = null;
			$tax_id = null;
			$tax_name = null;
			if(isset($goods_delivery['tax_select'])){
				$tax_rate_data = $this->get_tax_rate($goods_delivery['tax_select']);
				$tax_rate_value = $tax_rate_data['tax_rate'];
				$tax_rate = $tax_rate_data['tax_rate_str'];
				$tax_id = $tax_rate_data['tax_id_str'];
				$tax_name = $tax_rate_data['tax_name_str'];
			}

			if((float)$tax_rate_value != 0){
				$tax_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] * (float)$tax_rate_value / 100;
				$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
				$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'] + (float)$tax_money;
			}else{
				$total_money = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
				$amount = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];
			}

			$sub_total = (float)$goods_delivery['unit_price'] * (float)$goods_delivery['quantities'];

			$goods_delivery['tax_id'] = $tax_id;
			$goods_delivery['total_money'] = $total_money;
			$goods_delivery['tax_rate'] = $tax_rate;
			$goods_delivery['sub_total'] = $sub_total;
			$goods_delivery['tax_name'] = $tax_name;

			unset($goods_delivery['order']);
			unset($goods_delivery['id']);
			unset($goods_delivery['tax_select']);
			unset($goods_delivery['unit_name']);
			if(isset($goods_delivery['without_checking_warehouse'])){
				unset($goods_delivery['without_checking_warehouse']);
			}

			$this->db->insert(db_prefix() . 'fe_goods_delivery_detail', $goods_delivery);
			if($this->db->insert_id()){
				$results++;
			}
		}


			//send request approval
    	if($save_and_send_request == 'true'){
    		/*check send request with type =2 , inventory delivery voucher*/
    		// $check_r = $this->check_inventory_delivery_voucher(['rel_id' => $goods_delivery_id, 'rel_type' => '2']);

    		// if($check_r['flag_export_warehouse'] == 1){
    		// 	$this->send_request_approve(['rel_id' => $goods_delivery_id, 'rel_type' => 'inventory_delivery', 'addedfrom' => $data['addedfrom']]);

    		// }
    	}
		//approval if not approval setting
    	if (isset($goods_delivery_id)) {
    		if ($data['approval'] == 1) {
    			$this->update_approve_request($goods_delivery_id, 'inventory_delivery', 1);
    		}
    	}
    	return $results > 0 ? true : false;

    }


      /**
     * log wh activity
     * @param  [type] $id              
     * @param  [type] $description     
     * @param  string $additional_data 
     * @return [type]                  
     */
    public function log_inventory_activity($id, $rel_type, $description, $date = '')
    {
    	if(strlen($date) == 0){
    		$date = date('Y-m-d H:i:s');
    	}
        $log = [
            'date'            => $date,
            'description'     => $description,
            'rel_id'          => $id,
            'rel_type'          => $rel_type,
            'staffid'         => get_staff_user_id(),
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];

        $this->db->insert(db_prefix() . 'fe_goods_delivery_activity_log', $log);
        $insert_id = $this->db->insert_id();
        if($insert_id){
        	if($rel_type == 'delivery'){
        		$this->notify_customer_shipment_status($id);
        	}
        	return $insert_id;
        }
        return false;
    }

    /**
	 * notify customer shipment status
	 * @param  [type] $data 
	 * @return [type]       
	 */
    public function notify_customer_shipment_status($delivery_id)
    {	
    	$delivery = $this->get_goods_delivery($delivery_id);
    	if($delivery && is_numeric($delivery->customer_code) && ($delivery->pr_order_id == null || $delivery->pr_order_id == 0)){
			//get primary contact by client id
    		$primary_contact_user_id = get_primary_contact_user_id($delivery->customer_code);
    		$delivery_status = $delivery->delivery_status;
    		$shipment_by_delivery = $this->get_shipment_by_delivery($delivery_id);
    	}
    	if(isset($primary_contact_user_id) && $primary_contact_user_id && isset($shipment_by_delivery)){
    		$contact = $this->clients_model->get_contact($primary_contact_user_id);
    		$companyname = get_company_name($delivery->customer_code);
    		if($contact){
    			$content_html = $this->email_content_from_shipment_status($delivery_status, $companyname, $shipment_by_delivery->shipment_number ,$shipment_by_delivery->shipment_hash);
    			$inbox['body'] = _strip_tags($content_html);
    			$inbox['body'] = nl2br_save_html($inbox['body']);
    			$subject = _l('wh_delivery_status_notification').'['.$shipment_by_delivery->shipment_number.']';
    			$this->load->model('emails_model');
    			$result = $this->emails_model->send_simple_email($contact->email, $subject, $inbox['body'] );
    			if ($result) {
    				return true;
    			}
    			return false;
    			$ci = &get_instance();
    			$ci->email->initialize();
    			$ci->load->library('email');
    			$ci->email->clear(true);
    			if (strlen(get_option('smtp_host_sms_email')) > 0 && strlen(get_option('smtp_password_sms_email')) > 0 && strlen(get_option('smtp_username_sms_email'))) {

    				$ci->email->from(get_option('smtp_email_sms_email'), get_option('companyname'));
    			} else {
    				$ci->email->from(get_option('smtp_email'), get_option('companyname'));
    			}
    			$ci->email->to($data['email']);
    			$ci->email->message(get_option('email_header') . $inbox['body'] . get_option('email_footer'));
    			$ci->email->subject(_strip_tags($subject));
    			if ($ci->email->send(true)) {
    				return true;
    			}
    		}
    	}
    	return false;
    }

    	/**
	 * get shipment by delivery
	 * @param  [type] $delivery_id 
	 * @return [type]              
	 */
	public function get_shipment_by_delivery($delivery_id)
	{
		if (is_numeric($delivery_id)) {
			$this->db->where('goods_delivery_id', $delivery_id);
			return $this->db->get(db_prefix() . 'fe_omni_shipments')->row();
		}
		if ($delivery_id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_omni_shipments')->result_array();
		}
	}

	/**
	 * email content from shipment status
	 * @param  [type] $status        
	 * @param  [type] $companyname   
	 * @param  [type] $shipment_code 
	 * @param  [type] $shipment_id   
	 * @return [type]                
	 */
	public function email_content_from_shipment_status($status, $companyname, $shipment_code, $shipment_id)
	{
		$content_html = '';
		$table_font_size = 'font-size:13px;';
		$status_font_size = 'font-size:20px;';

		switch ($status) {

			case 'ready_for_packing':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname .',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'ready_to_deliver':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'delivery_in_progress':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'delivered':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'received':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'returned':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			case 'not_delivered':
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;

			default:
				$content_html = '';
				$content_html .='<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
				<tbody class="tbody-main" style="'.$table_font_size.'">';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_hello') .' '. $companyname. ',</b></td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$table_font_size.'">';
				$content_html .= '<td align="left" width="100%">' . _l('wh_the_status_of_your_order') .' '.'<a href="' . site_url('fixed_equipment/fixed_equipment_client/shipment_detail_hash/' . $shipment_id ).'" >' . $shipment_code . '</a>'.' '. _l('wh_has_been_change'). '</td>';
				$content_html .= '</tr>';

				$content_html .= '<tr style="'.$status_font_size.'">';
				$content_html .= '<td align="left" width="100%"><b>' . _l('wh_'.$status). '</b></td>';
				$content_html .= '</tr>';
				$content_html.= '</tbody>
				</table>';
			break;
		}
		return $content_html;
	}

    /**
     * delete activitylog
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_activitylog($id)
    {
        $this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'fe_goods_delivery_activity_log');
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
    }


    /**
	 * wh get activity log
	 * @param  [type] $id   
	 * @param  [type] $type 
	 * @return [type]       
	 */
	public function inventory_get_activity_log($id, $rel_type)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', $rel_type);
        $this->db->order_by('date', 'ASC');
        return $this->db->get(db_prefix() . 'fe_goods_delivery_activity_log')->result_array();
    }

    	/**
	 *  get list product by group
	 * @param  int  $id_chanel 
	 * @param  int  $id_group  
	 * @param  string  $key       
	 * @param  integer $limit     
	 * @param  integer $ofset     
	 * @return  array $result              
	 */
	public function get_list_product_by_group($type_item = '', $warehouse_id = '', $keyword = '',$limit = 0, $ofset = 1){
		// Search product
		$search = '';
		if($keyword!=''){
			$search = ' and (assets_name like \'%'.$keyword.'%\' or series like \'%'.$keyword.'%\') ';
		}
		// Product by group
		$type = '';
		if($type_item != '0'){
			$type = ' and type = \''.$type_item.'\'';
		}

		$warehouse = '';
		if($warehouse_id != '0'){
			$warehouse = ' and warehouse_id = '.$warehouse_id.'';
		}


		// $exclude = ' and id NOT IN (select id from '.db_prefix().'fe_assets where checkin_out = 2 and active = 1 and type = \'asset\')';
		$where = 'where active = 1 and (for_rent = 1 or for_sell = 1)'.$type.''.$warehouse.''.$search;

		$count_product = 'select count(id) as count from '.db_prefix().'fe_assets '.$where;
		$select_list_product = 'select id, assets_name, series, warehouse_id, model_id, for_rent, for_sell, renting_period, renting_unit, rental_price, selling_price, renting_period, renting_unit, quantity, type from '.db_prefix().'fe_assets '.$where.' limit '.$limit.','.$ofset;

		$data_list = $this->db->query($select_list_product)->result_array();
		$new_data_list = [];
		foreach ($data_list as $key => $value) {
			$value['max_qty'] = $this->get_stock_quantity_item($value['id'], true);
			$new_data_list[] = $value;
		}
		return [
			'list_product' => $new_data_list,
			'count' => (int)$this->db->query($count_product)->row()->count
		];
	}

	/**
	 * has product group
	 * @param  integer  $type   
	 * @return boolean             
	 */
	public function has_product_group($type){
		$data = $this->db->query('SELECT count(1) as count FROM '.db_prefix().'fe_assets where type = \''.$type.'\' and active = 1 and (for_rent = 1 or for_sell = 1)')->row();
		if($data){
			if((int)$data->count > 0){
				return true;
			}
		}
		return false;
	}

	/**
	 * incrementalHash
	 * @return string hash 
	 */
	public function incrementalHash(){
		$charset = "01FGHIJ23OPQ456TUVWXYZ789ABCDEKLMNRS";
		$base = strlen($charset);
		$result = '';

		$now = explode(' ', microtime())[1];
		while ($now >= $base){
			$i = (int)$now % $base;
			$result = $charset[$i] . $result;
			$now /= $base;
		}
		return substr($result, -5).strtotime(date('Y-m-d H:i:s'));
	}

	/**
	 * check out
	 * @param  array $data 
	 * @return string order_number       
	 */
	public function check_out($data){
		$this->load->model('clients_model');
		$data_client = $this->clients_model->get($data['userid']);
		if($data_client){
			$date = date('Y-m-d');
			$user_id = $data['userid'];
			$order_number = $this->incrementalHash();
			$channel_id = 2;
			$data_cart['userid'] = $user_id;
			$data_cart['order_number'] = $order_number;
			$data_cart['channel_id'] = $channel_id;
			$data_cart['channel'] = 'portal';
			$data_cart['company'] =  $data_client->company;
			$data_cart['phonenumber'] =  $data_client->phonenumber;
			$data_cart['city'] =  $data_client->city;
			$data_cart['state'] =  $data_client->state;
			$data_cart['country'] =  $data_client->country;
			$data_cart['zip'] =  $data_client->zip;
			$data_cart['billing_street'] =  $data_client->billing_street;
			$data_cart['billing_city'] =  $data_client->billing_city;
			$data_cart['billing_state'] =  $data_client->billing_state;
			$data_cart['billing_country'] =  $data_client->billing_country;
			$data_cart['billing_zip'] =  $data_client->billing_zip;
			$data_cart['shipping_street'] =  $data_client->shipping_street;
			$data_cart['shipping_city'] =  $data_client->shipping_city;
			$data_cart['shipping_state'] =  $data_client->shipping_state;
			$data_cart['shipping_country'] =  $data_client->shipping_country;
			$data_cart['shipping_zip'] =  $data_client->shipping_zip;
			$data_cart['total'] =  preg_replace('%,%','',$data['total']);
			$data_cart['sub_total'] =  $data['sub_total'];
			$data_cart['discount'] =  $data['discount'];
			$data_cart['discount_total'] =  $data['discount_total'];
			$data_cart['discount_voucher'] =  $data['discount_total'];
			$data_cart['discount_type'] =  2;
			$data_cart['notes'] =  $data['notes'];
			$data_cart['tax'] =  $data['tax'];
			$data_cart['allowed_payment_modes'] =  $data['payment_methods'];
			$data_cart['shipping'] =  $data['shipping'];
			$data_cart['hash'] = app_generate_hash();
			$data_cart['shipping_form'] =  $data['shipping_form'];
			$data_cart['shipping_value'] =  $data['shipping_value'];
			$data_cart['type'] =  $data['type'];
var_dump($data_cart);die;
			$this->db->insert(db_prefix() . 'fe_cart', $data_cart);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				$productid_list = explode(',',$data['list_id_product']);
				if($data['type'] == 'order'){
					$quantity_list = explode(',',$data['list_qty_product']);
					$this->add_cart_detail_order($insert_id, $productid_list, $quantity_list);
				}
				elseif($data['type'] == 'booking'){
					$rental_time_list = explode(',',$data['list_rental_time']);
					$rental_date_list = explode(',',$data['list_rental_date']);
					$pickup_time_list = explode(',',$data['list_pickup_time']);
					$dropoff_time_list = explode(',',$data['list_dropoff_time']);
					$number_day_list = explode(',',$data['list_number_day']);
					$this->add_cart_detail_booking($insert_id, $productid_list, $rental_time_list, $rental_date_list, $pickup_time_list, $dropoff_time_list, $number_day_list);
				}
				$data_inv = $this->get_cart($insert_id);
				// Remove cookie to clear cart
				if($data['type'] == 'order'){
					if (isset($_COOKIE['fe_cart_id_list'])&&isset($_COOKIE['fe_cart_qty_list'])) {
						unset($_COOKIE['fe_cart_id_list']); 
						unset($_COOKIE['fe_cart_qty_list']); 
						setcookie('fe_cart_id_list', null, -1, '/'); 
						setcookie('fe_cart_qty_list', null, -1, '/'); 
					} 
				} 
				elseif($data['type'] == 'booking'){
					if (isset($_COOKIE['fe_cart_id_list_booking'])) {
						unset($_COOKIE['fe_cart_id_list_booking']); 
						setcookie('fe_cart_id_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_rental_date_list_booking'])) {
						unset($_COOKIE['fe_cart_rental_date_list_booking']); 
						setcookie('fe_cart_rental_date_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_rental_time_list_booking'])) {
						unset($_COOKIE['fe_cart_rental_time_list_booking']); 
						setcookie('fe_cart_rental_time_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_rental_time_list_booking'])) {
						unset($_COOKIE['fe_cart_rental_time_list_booking']); 
						setcookie('fe_cart_rental_time_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_dropoff_time_list_booking'])) {
						unset($_COOKIE['fe_cart_dropoff_time_list_booking']); 
						setcookie('fe_cart_dropoff_time_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_item_type_list_booking'])) {
						unset($_COOKIE['fe_cart_item_type_list_booking']); 
						setcookie('fe_cart_item_type_list_booking', null, -1, '/'); 
					} 
					if (isset($_COOKIE['fe_cart_renting_unit_list_booking'])) {
						unset($_COOKIE['fe_cart_renting_unit_list_booking']); 
						setcookie('fe_cart_renting_unit_list_booking', null, -1, '/'); 
					} 
				}
				return $insert_id;    
			}
			return '';
		}     
	}

	/**
	 * add cart detail order
	 * @param integer $insert_id      
	 * @param array $productid_list 
	 * @param array $quantity_list  
	 */
	public function add_cart_detail_order($insert_id, $productid_list, $quantity_list){
		foreach ($productid_list as $key => $product_id) {
			$data_detailt['product_id'] = $product_id;   
			$item_quantity = $quantity_list[$key];
			$data_detailt['quantity'] = $item_quantity;
			$data_detailt['classify'] = '';
			$data_detailt['cart_id']  = $insert_id;
			$product_name = '';
			$long_description = '';
			$sku = '';
			$prices  = 0;
			$data_products = $this->get_assets($product_id);
			if($data_products){
				$product_name = (($data_products->series != '' ? $data_products->series.' ' : '').''.$data_products->assets_name);
				$long_description = '';
				$sku = '';
				$prices = $data_products->selling_price;
			}
			$data_detailt['product_name'] = $product_name;
			$data_detailt['prices'] = $prices;
			$tax_array = [];
			$data_detailt['tax'] = json_encode($tax_array);
			$data_detailt['sku'] = $sku;
			$data_detailt['long_description'] = $long_description;
			$discount_percent = 0;
			$prices_discount  = 0;
			$data_detailt['percent_discount'] = 0;
			$data_detailt['prices_discount'] = 0;
			$this->db->insert(db_prefix() . 'fe_cart_detailt', $data_detailt);
		} 
	}

	/**
	 * add cart detail booking
	 * @param integer $insert_id         
	 * @param array $productid_list    
	 * @param array $rental_time_list  
	 * @param array $rental_date_list  
	 * @param array $pickup_time_list  
	 * @param array $dropoff_time_list 
	 */
	public function add_cart_detail_booking($insert_id, $productid_list, $rental_time_list, $rental_date_list, $pickup_time_list, $dropoff_time_list, $number_day_list){
		foreach ($productid_list as $key => $product_id) {
			$data_detailt['product_id'] = $product_id;  

			$rental_time = (isset($rental_time_list[$key]) ? $rental_time_list[$key] : '');
			$rental_date = (isset($rental_date_list[$key]) ? $rental_date_list[$key] : '');
			$pickup_time = (isset($pickup_time_list[$key]) ? $pickup_time_list[$key] : '');
			$dropoff_time = (isset($dropoff_time_list[$key]) ? $dropoff_time_list[$key] : '');
			$number_day = (isset($number_day_list[$key]) ? $number_day_list[$key] : 0);

			$data_detailt['quantity'] = 1;
			$data_detailt['classify'] = '';
			$data_detailt['cart_id']  = $insert_id;
			$product_name = '';
			$long_description = '';
			$sku = '';
			$prices  = 0;
			$rental_value  = 0;
			$for_rent  = 0;
			$renting_period  = 0;
			$renting_unit  = 0;
			$data_products = $this->get_assets($product_id);
			if($data_products){
				$product_name = (($data_products->series != '' ? $data_products->series.' ' : '').''.$data_products->assets_name);
				$long_description = '';
				$sku = '';
				$prices = $data_products->rental_price;
				$rental_value = round($number_day * $data_products->rental_price / $data_products->renting_period);
				$for_rent  = $data_products->for_rent;
				$renting_unit  = $data_products->renting_unit;
				$renting_period  = $data_products->renting_period;
			}
			$data_detailt['product_name'] = $product_name;
			$data_detailt['prices'] = $prices;
			$tax_array = [];
			$data_detailt['tax'] = json_encode($tax_array);
			$data_detailt['sku'] = $sku;
			$data_detailt['long_description'] = $long_description;
			$discount_percent = 0;
			$prices_discount  = 0;
			$data_detailt['percent_discount'] = 0;
			$data_detailt['prices_discount'] = 0;

			$data_detailt['pickup_time'] = $pickup_time;
			$data_detailt['dropoff_time'] = $dropoff_time;

			$rental_start_date = '';
			$rental_end_date = '';
			if($for_rent == 1){
				if($renting_unit == 'hour'){
					$rental_start_date = $rental_date;
					$rental_end_date = $rental_date;
				}
				else{
					$exp = explode(' to ', $rental_time);
					$rental_start_date = (isset($exp[0]) ? trim($exp[0]) : '');
					$rental_end_date = (isset($exp[1]) ? trim($exp[1]) : '');
				}
			}
			$data_detailt['rental_start_date'] = $rental_start_date;
			$data_detailt['rental_end_date'] = $rental_end_date;
			$data_detailt['rental_value'] = $rental_value;
			$data_detailt['number_date'] = $number_day;
			$data_detailt['renting_period'] = $renting_period;
			$data_detailt['renting_unit'] = $renting_unit;
			$data_detailt['status'] = 1;
			$this->db->insert(db_prefix() . 'fe_cart_detailt', $data_detailt);
		} 
	}

	/**
	 * get cart
	 * @param  int $id 
	 * @return object or array    
	 */
	public function get_cart($id = '', $where = ''){
		if($id != ''){
			$this->db->where('id',$id);
			return $this->db->get(db_prefix().'fe_cart')->row();
		}
		else{     
			if($where != ''){
				$this->db->where($where);
			}
			return $this->db->get(db_prefix().'fe_cart')->result_array();
		}
	}
	/**
	 * get cart detailt
	 * @param  int $id 
	 * @return  object or array      
	 */
	public function get_cart_detailt($id = ''){
		if($id != ''){
			$this->db->where('id',$id);
			return $this->db->get(db_prefix().'fe_cart_detailt')->row();
		}
		else{     
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
	}

	/**
	 * get list product by group
	 * @param  int  $id_chanel  
	 * @param  int  $id_group   
	 * @param  int  $id_product 
	 * @param  int $limit      
	 * @param  int $ofset      
	 * @return array              
	 */
	public function get_list_product_by_group_s($type, $id_product = '', $limit = 0, $ofset = 1){
		if($type!=''){
			$count_product = 'select count(id) as count from '.db_prefix().'fe_assets where active = 1 and (for_rent = 1 or for_sell = 1) and type = \''.$type.'\' and id != '.$id_product;
			$select_list_product = 'select id, assets_name, series, warehouse_id, model_id, for_rent, for_sell, renting_period, renting_unit, rental_price, selling_price, renting_period, renting_unit, quantity, type from '.db_prefix().'fe_assets where active = 1 and (for_rent = 1 or for_sell = 1) and type = \''.$type.'\' and id != '.$id_product.' limit '.$limit.','.$ofset;
			$result = [
				'list_product' => $this->db->query($select_list_product)->result_array(),
				'count' => (int)$this->db->query($count_product)->row()->count
			];
			return $result;
		}
	}

		/**
	 * get_group_product
	 * @param  int $id_group 
	 * @return array           
	 */
	public function get_group_product_s($id_group = ''){
		$data_group_product = [
			['id' => 'asset', 'name' => _l('fe_assets')],
			['id' => 'license', 'name' => _l('fe_licenses')],
			['id' => 'accessory', 'name' => _l('fe_accessories')],
			['id' => 'component', 'name' => _l('fe_components')],
			['id' => 'consumable', 'name' => _l('fe_consumables')]
		];  
		if($id_group != ''){
			$new_list = [];
			foreach ($data_group_product as $key => $item) {
				if($item['id'] != $id_group){
					$new_list[] = $item;
				}
			}
			$data_group_product = $new_list;
		}
		return $data_group_product;
	}

	/**
	 * get id invoice 
	 * @param  $number
	 * @return   id invoice    
	 */
	public function get_id_invoice($number){
		$this->db->where('number', $number);
		$invoice = $this->db->get(db_prefix().'invoices')->row();

		if($invoice){
			return $invoice->id;
		}

		return '';
	}

	/**
	 * get cart detailt by cart id
	 * @param  int $cart_id 
	 * @return array          
	 */
	public function get_cart_detailt_by_cart_id($cart_id = ''){
		if($cart_id != ''){
			$this->db->where('cart_id',$cart_id);
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
		else{     
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
	}

	/**
	 * get warehouse 
	 * @param  boolean $id
	 * @return array or object
	 */
	public function get_warehouse($id = false) {
		if (is_numeric($id)) {
			$this->db->where('warehouse_id', $id);
			return $this->db->get(db_prefix() . 'fe_warehouse')->row();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_warehouse')->result_array();
		}
	}

	/**
	 * get html tax delivery
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function get_html_tax_manual_order($id){
		$html = '';
		$html_currency = '';
		$preview_html = '';
		$pdf_html = '';
		$taxes = [];
		$t_rate = [];
		$tax_val = [];
		$tax_val_rs = [];
		$tax_name = [];
		$rs = [];

		$this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();

		$this->db->where('id',  $id);
		$cart = $this->db->get(db_prefix().'fe_cart')->row();

		$this->db->where('cart_id', $id);
		$details = $this->db->get(db_prefix().'fe_cart_detailt')->result_array();

		$discount_type = $cart->discount_type_str;
		$discount_total_type = $cart->discount_type;
		if($cart->discount_type == 1){
			// %
			$discount_percent = $cart->add_discount;
			$discount_fixed = 0;

		}else if($cart->discount_type == 2){
			// fixed
			$discount_percent = 0;
			$discount_fixed = $cart->add_discount;
		}

		foreach($details as $row){
			if($row['tax_id'] != ''){
				$tax_arr = explode('|', $row['tax_id']);

				$tax_rate_arr = [];
				if($row['tax_rate'] != ''){
					$tax_rate_arr = explode('|', $row['tax_rate']);
				}

				foreach($tax_arr as $k => $tax_it){
					if(!isset($tax_rate_arr[$k]) ){
						$tax_rate_arr[$k] = $this->tax_rate_by_id($tax_it);
					}

					if(!in_array($tax_it, $taxes)){
						$taxes[$tax_it] = $tax_it;
						$t_rate[$tax_it] = $tax_rate_arr[$k];
						$tax_name[$tax_it] = $this->get_tax_name($tax_it).' ('.$tax_rate_arr[$k].'%)';
					}
				}
			}
		}

		if(count($tax_name) > 0){
			foreach($tax_name as $key => $tn){
				$tax_val[$key] = 0;
				foreach($details as $row_dt){
					if(!(strpos($row_dt['tax_id'], $taxes[$key]) === false)){
						$total_tax = ($row_dt['quantity']*$row_dt['prices']*$t_rate[$key]/100);
						if (($discount_percent !== '' && $discount_percent != 0) && $discount_type == 'before_tax' && $discount_total_type == 1) {
							$total_tax_calculated = ($total_tax * $discount_percent) / 100;
							$total_tax = ($total_tax - $total_tax_calculated);
						} else if (($discount_fixed !== '' && $discount_fixed != 0) && $discount_type == 'before_tax' && $discount_total_type == 2) {
							$t = ($discount_fixed / ($row_dt['quantity']*$row_dt['prices'])) * 100;
							$total_tax = ($total_tax - ($total_tax * $t) / 100);
						}

						$tax_val[$key] += $total_tax;
					}
				}
				$pdf_html .= '<tr id="subtotal"><td ></td><td></td><td></td><td class="text_left">'.$tn.'</td><td class="text_right">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
				$preview_html .= '<tr id="subtotal"><td>'.$tn.'</td><td>'.app_format_money($tax_val[$key], '').'</td><tr>';
				$html .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], '').'</td></tr>';
				$html_currency .= '<tr class="tax-area_pr"><td>'.$tn.'</td><td width="65%">'.app_format_money($tax_val[$key], $base_currency->symbol).'</td></tr>';
				$tax_val_rs[] = $tax_val[$key];
			}
		}

		$rs['pdf_html'] = $pdf_html;
		$rs['preview_html'] = $preview_html;
		$rs['html'] = $html;
		$rs['taxes'] = $taxes;
		$rs['taxes_val'] = $tax_val_rs;
		$rs['html_currency'] = $html_currency;
		return $rs;
	}

	/**
	 * get cart by order number
	 * @param  string $order_number 
	 * @return object or array               
	*/
	public function get_cart_by_order_number($order_number=''){
		if($order_number != ''){
			$this->db->where('order_number',$order_number);
			return $this->db->get(db_prefix().'fe_cart')->row();
		}
		else{     
			return $this->db->get(db_prefix().'fe_cart')->result_array();
		}
	}

	/**
	 * change status order
	 * @param  array  $data         
	 * @param  string  $order_number 
	 * @param  integer $admin_action 
	 * @return bool                
	 */
	public function change_status_order($data, $order_number,$admin_action = 0){
		$this->db->where('order_number',$order_number);
		$data_order = $this->db->get(db_prefix().'fe_cart')->row();
		if($data_order){
			$old_status = $data_order->status;
			$data_update['reason'] = $data['cancelReason'];
			$data_update['status'] = $data['status'];
			$data_update['admin_action'] = $admin_action;
			$this->db->where('id', $data_order->id);
			$this->db->update(db_prefix().'fe_cart',$data_update);
			if ($this->db->affected_rows() > 0) {
				if(in_array($data['status'], [1,2,14,3,4,5])){
					$this->create_shipment_from_order($data_order->id);
				}
				return true;
			}
		}
		return false;
	}

	/**
	 *  get total order
	 * @param  int  $id      
	 * @param  boolean $voucher 
	 * @return array           
	 */
	public function get_total_order($id ='',$voucher = false){        
		$data_detailt = $this->get_cart_detailt_by_master($id);
		$total = 0;
		foreach ($data_detailt as $key => $value) {
			$total += $value['quantity'] * $value['prices'];
		}
		return ['total' => $total,'sub_total' => $total,'discount' => '0'];
	}

	/**
	 * update status order comfirm 
	 * @param  int $order_id 
	 * @return bolean
	 */
	public function update_status_order_comfirm($order_id, $prefix = '' , $_invoice_number = '', $number = '', $status = 2){
		$code_invoice = $prefix . $_invoice_number;
		$this->db->where('id', $order_id);
		$dara = $this->db->update(db_prefix().'fe_cart', ['status' => $status, 'admin_action' => 1, 'invoice' => $code_invoice, 'number_invoice' => $number]);
		if ($this->db->affected_rows() > 0) {
			$this->create_shipment_from_order($order_id);
			return true;
		}
		return false;
	}

	/**
	 * create invoice detail order
	 * @param int $orderid 
	 * @return bolean
	 */
	public function create_invoice_detail_order($orderid, $status = '') {
		$cart = $this->get_cart($orderid);
		if($cart){
			$order_type = $cart->type;
			$this->load->model('invoices_model');
			$this->load->model('credit_notes_model');
			$cart_detailt = $this->get_cart_detailt_by_master($orderid);
			$newitems = [];
			$count = 0;
			foreach ($cart_detailt as $key => $value) {
				$unit_name = '';
				$tax_arr = [];
				$count = $key;

				$rate = 0;
				if($order_type == 'order'){
				// Order type
					$rate = (int)$value['quantity']*$value['prices'];;
				}
				else{
				// Booking type
					$rate = $value['rental_value'];;
				}
				array_push($newitems, array('order' => $key, 'description' => $value['product_name'], 'long_description' => $value['long_description'], 'qty' => $value['quantity'], 'unit' => $unit_name, 'rate'=> $rate, 'taxname' => $tax_arr));
			}   

			$data_total = $this->get_total_order($orderid);
			$total = $data_total['total'];
			$sub_total = $data_total['sub_total'];
			$discount_total = $data_total['discount'];
			$__number = get_option('next_invoice_number');
			$_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
			$this->db->where('isdefault', 1);
			$curreny = $this->db->get(db_prefix().'currencies')->row()->id;

			if($cart){
				if(is_numeric($cart->currency) && $cart->currency > 0){
					$curreny = $cart->currency;
				}
				$data['clientid'] = $cart->userid;
				$data['billing_street'] = $cart->billing_street;
				$data['billing_city'] = $cart->billing_city;
				$data['billing_state'] = $cart->billing_state;
				$data['billing_zip'] = $cart->billing_zip;
				$data['billing_country'] = $cart->billing_country;
				$data['include_shipping'] = 1;
				$data['show_shipping_on_invoice'] = 1;
				$data['shipping_street'] = $cart->shipping_street;
				$data['shipping_city'] = $cart->shipping_city;
				$data['shipping_state'] = $cart->shipping_state;
				$data['shipping_zip'] = $cart->shipping_zip;
				$date_format   = get_option('dateformat');
				$date_format   = explode('|', $date_format);
				$date_format   = $date_format[0];       
				$data['date'] = date($date_format);
				$data['duedate'] = date($date_format);
			//terms_invoice
				$data['terms'] = get_option('predefined_terms_invoice');
				if(isset($cart->shipping) && (float)$cart->shipping > 0){
					array_push($newitems, array('order' => $count+1, 'description' => _l('shipping'), 'long_description' => "", 'qty' => 1, 'unit' => "", 'rate'=> $cart->shipping, 'taxname' => array()));
				}
				$data['currency'] = $curreny;
				$data['newitems'] = $newitems;
				$data['number'] = $_invoice_number;
				$data['total'] = $cart->total;
				$data['subtotal'] = $cart->sub_total;      
				$data['total_tax'] = $cart->tax;
				$data['discount_total'] = $cart->discount_total;
				$data['shipping_fee'] = $cart->shipping;
				$data['discount_total' ] = $cart->discount;
				$data['discount_type'] = $cart->discount_type_str;
				$data['sale_agent'] = is_numeric($cart->seller) ? $cart->seller : '';
				$data['adjustment'] = $cart->adjustment;

				$prefix = get_option('invoice_prefix');

				$data['allowed_payment_modes'] = [ 0 => $cart->allowed_payment_modes ];


				$id = $this->invoices_model->add($data);

				if($id){
					if($status!=''){
						$this->update_status_order_comfirm($orderid, $prefix, $_invoice_number, $__number, $status);
					}
					else{
						$this->update_status_order_comfirm($orderid, $prefix, $_invoice_number, $__number);
					}           
					return true;
				}
			}   
		}   
		return true;
	}

	/**
	 * get cart detailt by master
	 * @param  int $id 
	 * @return array     
	 */
	public function get_cart_detailt_by_master($id = '', $where = ''){
		if($where != ''){
			$this->db->where($where);
		}
		if($id != ''){
			$this->db->where('cart_id',$id);
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
		else{     
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
	}

	 /**
	 * get invoice 
	 * @param  $number
	 * @return   invoice    
	 */
	 public function get_invoice($number){
	 	$this->db->where('number', $number);
	 	return $this->db->get(db_prefix().'invoices')->row();
	 }

	/**
	 * check inventory delivery voucher
	 * @param  array $data 
	 * @return string       
	 */
	public function check_inventory_delivery_voucher($data)
	{
		$flag_export_warehouse = 1;
		$str_error='';
		/*get goods delivery detail*/
		$this->db->where('cart_id', $data['rel_id']);
		$cart_details = $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		if (count($cart_details) > 0) {
			foreach ($cart_details as $delivery_detail_key => $cart_detail) {
				$quantity_inventory = $this->get_avai_quantity_inventory($cart_detail['product_id']);
				if((float)$quantity_inventory < (float)$cart_detail['quantity']){
					$str_error .= _l('fe_item').' '. $cart_detail['product_name'].':  '._l('fe_not_enough_inventory');
					$flag_export_warehouse =  0;
				}
			}
		}
		$result=[];
		$result['str_error'] = $str_error;
		$result['flag_export_warehouse'] = $flag_export_warehouse;
		return $result;
	}

	/**
	 * get quantity inventory
	 * @param  integer $item_id 
	 * @return integer               
	 */
	public function get_avai_quantity_inventory($item_id){
		$quantity = 1;
		$this->db->where('id', $item_id);
		$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
		if($data_asset){

			if($data_asset->type == 'license'){
				$avail = 0;
				$data_total = $this->count_total_avail_seat($item_id);
				if($data_total){
					$avail = $data_total->avail;
				}
				$quantity = $avail;
			}
			elseif($data_asset->type == 'accessory'){
				$quantity = $data_asset->quantity - $this->count_checkin_asset_by_parents($item_id);
			}
			elseif($data_asset->type == 'component'){
				$quantity = $data_asset->quantity - $this->count_checkin_component_by_parents($item_id);
			}
			elseif($data_asset->type == 'consumable'){
				$quantity = $data_asset->quantity - $this->count_checkin_asset_by_parents($item_id);
			}
		}
		return $quantity;	
	}

	/**
	* create export stock
	* @param int $orderid 
	* @param int $status 
	* @return bolean
	*/
	public function create_export_stock($orderid, $status = '') {
		$cart = $this->get_cart($orderid);  
		$cart_detailt = $this->get_cart_detailt_by_master($orderid);
		$data_delivery["edit_approval"]="";
		$data_delivery["save_and_send_request"]=false;
		$data_delivery["additional_discount"]=0;
		$data_delivery["date_c"] = date('Y-m-d');
		$data_delivery["date_add"] = date('Y-m-d');
		$data_delivery["pr_order_id"] = "";
		$data_delivery["invoice_id"] = "";
		$data_delivery["customer_code"]=$cart->userid;
		$data_delivery["to_"]="";

		$address = ($cart->shipping_street != '' ? $cart->shipping_street.', ' : '').
		($cart->shipping_city != '' ? $cart->shipping_city.', ' : '').
		($cart->shipping_state != '' ? $cart->shipping_state.', ' : '').
		($cart->shipping_country != '' ? get_country_short_name($cart->shipping_country).', ' : '').
		($cart->shipping_zip != '' ? $cart->shipping_zip.', ' : '');
		$data_delivery["address"] = rtrim(', ', $address);
		$data_delivery["warehouse_id"]="";
		$data_delivery["staff_id"]="";
		$data_delivery["invoice_no"]="";
		$data_delivery["item_select"]="";
		$data_delivery["commodity_name"]="";
		$data_delivery["available_quantity"]="";
		$data_delivery["quantities"]="";
		$data_delivery["unit_price"]="";
		$data_delivery["discount"]="";
		$data_delivery["commodity_code"]="";
		$data_delivery["unit_id"]="";
		$data_delivery["discount_money"]="";
		$data_delivery["total_after_discount"]="";
		$data_delivery["serial_number"]="";
		$data_delivery["without_checking_warehouse"]="";
		$data_delivery["type"]=$cart->type;
		$total = 0;
		$newitems = [];
		$order = 0;
		foreach ($cart_detailt as $key => $item) {
			$product_id = $item['product_id'];
			$unit_price = ($cart->type == 'booking' ? $item['rental_value'] : $item['prices']);
			$data_warehouse = $this->export_auto($product_id, (int)$item['quantity']);
			foreach ($data_warehouse as $wkey => $value) {
				$order++;
				$w_quantity = $value['quantity'];
				$line_total = $w_quantity * $unit_price;
				$total += $line_total;
				$newitems[] = [
					"order" => $order,
					"id"=>"",
					"commodity_name"=>$item['product_name'],
					"warehouse_id"=>$value['warehouse_id'],
					"available_quantity"=>$value['available_quantity'],
					"quantities"=>$w_quantity,
					"unit_price"=>$unit_price,
					"discount"=>"",
					"commodity_code"=>$product_id,
					"unit_id"=>"",
					"discount_money"=>0,
					"total_after_discount"=>$line_total,
					"serial_number"=>"",
					"without_checking_warehouse"=>0
				];
			}
		}
		$data_delivery["newitems"]=$newitems;
		$data_delivery["sub_total"]=$total;
		$data_delivery["total_money"]=$total;
		$data_delivery["total_discount"]=0;
		$data_delivery["shipping_fee"]=0;
		$data_delivery["after_discount"]=$total;
		$data_delivery["description"]="";
		$id_exp = $this->add_goods_delivery($data_delivery, true);
		if($id_exp){
			$data_update['status'] = $status;
			$data_update['admin_action'] = 1;
			$data_update['stock_export_number'] = $id_exp;
			$this->db->where('id', $orderid);
			$this->db->update(db_prefix().'fe_cart', $data_update);
			if ($this->db->affected_rows() > 0) {
				$shipment = $this->get_shipment_by_order($orderid);
				if($shipment){
					$shipment_log = _l('inventory_delivery_voucher_have_been_created');
					$this->log_inventory_activity($shipment->id, 'shipment', $shipment_log);
					$this->update_shipment_status($shipment->id, ['shipment_status' => 'processing_order']);
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * export auto
	 * @param  integer $product_id 
	 * @return array             
	 */
	public function export_auto($product_id, $need_quantity){
		$result_array = [];
		if($need_quantity > 0){
			$this->db->select('type, series');
			$this->db->where('id', $product_id);
			$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
			if($data_asset){
				$data_receipt = [];
				if($data_asset->type == 'asset'){
					$data_receipt = $this->db->query('select warehouse_id from '.db_prefix().'fe_goods_receipt_detail where serial_number = \''.$data_asset->series.'\'  order by id')->result_array();
				}
				else{
					$data_receipt = $this->db->query('select warehouse_id from '.db_prefix().'fe_goods_receipt_detail where commodity_code = \''.$product_id.'\'  order by id')->result_array();
				}
				if(is_array($data_receipt) && count($data_receipt) > 0){
					$array = [];
					$remain = $need_quantity;
					foreach ($data_receipt as $key => $value) {
						$warehouse_id = $value['warehouse_id'];
						$available_quantity =  $this->get_quantity_inventory_item($product_id, $warehouse_id);
						if($available_quantity > 0){
							if($remain <= $available_quantity){
								$result_array[] = ['warehouse_id' => $warehouse_id, 'quantity' => (int)$remain, 'available_quantity' => $available_quantity];
								break;
							}
							elseif($remain > $available_quantity){
								$result_array[] = ['warehouse_id' => $warehouse_id, 'quantity' => (int)$available_quantity, 'available_quantity' => $available_quantity];
								$remain = $need_quantity - $available_quantity;
							}
						}
					}
				}
			}
		}
		return $result_array;
	}

    /**
     * get shipping address from invoice
     * @param  integer $invoice_id 
     * @return string             
     */
    public function get_shipping_address_from_invoice($invoice_id)
    {	
    	$address='';
    	$this->db->where('id', $invoice_id);
    	$invoice_value = $this->db->get(db_prefix().'invoices')->row();
    	if($invoice_value){
    		$address = $invoice_value->shipping_street;
    	}
    	return $address;
    }

    /**
     * get itemid from name
     * @param  string $name 
     * @return integer       
     */
    public function get_itemid_from_name($name)
    {	
    	$item_id=0;
    	$this->db->where('assets_name', $name);
    	$item_value = $this->db->get(db_prefix().'fe_assets')->row();
    	if($item_value){
    		$item_id = $item_value->id;
    	}
    	return $item_id;
    }

	/**
	 * get tax id from taxname taxrate
	 * @param  [type] $taxname 
	 * @param  [type] $taxrate 
	 * @return [type]          
	 */
	public function get_tax_id_from_taxname_taxrate($taxname, $taxrate)
	{	$tax_id = 0;
		$this->db->where('name', $taxname);
		$this->db->where('taxrate', $taxrate);
		$tax_value = $this->db->get(db_prefix().'taxes')->row();

		if($tax_value){
			$tax_id = $tax_value->id;
		}
		return $tax_id;
	}

	/**
		 * auto_create_goods_delivery_with_invoice
		 * @param  integer $invoice_id 
		 *              
		 */
	public function auto_create_goods_delivery_with_invoice($invoice_id, $invoice_update='')
	{
		$this->db->where('id', $invoice_id);
		$invoice_value = $this->db->get(db_prefix().'invoices')->row();
		if($invoice_value){
			/*get value for goods delivery*/
			$data['goods_delivery_code'] = $this->create_goods_delivery_code();
			$data['date_c'] = fe_format_date($invoice_value->date);
			$data['date_add'] = fe_format_date($invoice_value->date);
			$data['shipping_fee']  = $invoice_value->shipping_fee;
			$data['customer_code']  = $invoice_value->clientid;
			$data['invoice_id']   = $invoice_id;
			$data['addedfrom']  = $invoice_value->addedfrom;
			$data['description']  = $invoice_value->adminnote;
			$data['address']  = $this->get_shipping_address_from_invoice($invoice_id);
			$data['staff_id'] 	= $invoice_value->sale_agent;
    		$data['invoice_no'] 	= format_invoice_number($invoice_value->id);

			$data['total_money']  = (float)$invoice_value->subtotal + (float)$invoice_value->total_tax;
			$data['total_discount'] = $invoice_value->discount_total;
			$data['after_discount'] = $invoice_value->total;
			/*get data for goods delivery detail*/
			/*get item in invoices*/
			$this->db->where('rel_id', $invoice_id);
			$this->db->where('rel_type', 'invoice');
			$arr_itemable = $this->db->get(db_prefix().'itemable')->result_array();

			$arr_item_insert=[];
			$arr_new_item_insert=[];
			$index=0;
			if(count($arr_itemable) > 0){
				foreach ($arr_itemable as $key => $value) {
					$commodity_code = $this->get_itemid_from_name($value['description']);
					//get_unit_id
					$unit_id = 0;
					//get warranty
					$warranty = 0;

					if($commodity_code != 0){

						$tax_rate = '';
    					$tax_name = '';
    					$str_tax_id = '';
    					$total_tax_rate = 0;
    					$commodity_name = '';

						/*get tax item*/
						$this->db->where('itemid', $value['id']);
						$this->db->where('rel_id', $invoice_id);
						$this->db->where('rel_type', "invoice");

						$item_tax = $this->db->get(db_prefix().'item_tax')->result_array();

						if(count($item_tax) > 0){
							foreach ($item_tax as $tax_value) {
								$tax_id = $this->get_tax_id_from_taxname_taxrate($tax_value['taxname'], $tax_value['taxrate']);
								if(strlen($tax_rate) != ''){
    								$tax_rate .= '|'.$tax_value['taxrate'];
    							}else{
    								$tax_rate .= $tax_value['taxrate'];

    							}
    							$total_tax_rate += (float)$tax_value['taxrate'];

    							if(strlen($tax_name) != ''){
    								$tax_name .= '|'.$tax_value['taxname'];
    							}else{
    								$tax_name .= $tax_value['taxname'];

    							}

								if($tax_id != 0){
    								if(strlen($str_tax_id) != ''){
    									$str_tax_id .= '|'.$tax_id;
    								}else{
    									$str_tax_id .= $tax_id;

    								}
    							}
							}
						}

						if((float)$value['qty'] > 0){

							$temporaty_quantity = $value['qty'];
    						$inventory_warehouse_by_commodity = $this->get_inventory_warehouse_by_commodity($commodity_code);

    						//have serial number
    						foreach ($inventory_warehouse_by_commodity as $key => $inventory_warehouse) {
    							if($temporaty_quantity > 0){
    								$available_quantity = (float)$inventory_warehouse['inventory_number'];
    								$warehouse_id = $inventory_warehouse['warehouse_id'];

    								$temporaty_available_quantity = $available_quantity;
    								$list_temporaty_serial_numbers = $this->warehouse_model->get_list_temporaty_serial_numbers($commodity_code, $inventory_warehouse['warehouse_id'], $value['qty']);
    								foreach ($list_temporaty_serial_numbers as $serial_value) {

										if($temporaty_available_quantity > 0){
											$temporaty_commodity_name = $commodity_name.' SN: '.$serial_value['serial_number'];
											$quantities = 1;

											$arr_new_item_insert[$index]['commodity_name'] = $temporaty_commodity_name;
											$arr_new_item_insert[$index]['commodity_code'] = $commodity_code;
											$arr_new_item_insert[$index]['quantities'] = $quantities + 0;
											$arr_new_item_insert[$index]['unit_price'] = $value['rate'] + 0;
											$arr_new_item_insert[$index]['tax_rate'] = $tax_rate;
											$arr_new_item_insert[$index]['tax_name'] = $tax_name;
											$arr_new_item_insert[$index]['tax_id'] = $str_tax_id;
											$arr_new_item_insert[$index]['unit_id'] = $unit_id;
											$arr_new_item_insert[$index]['guarantee_period'] = $warranty;
											$arr_new_item_insert[$index]['serial_number'] = $serial_value['serial_number'];
											$arr_new_item_insert[$index]['warehouse_id'] = $warehouse_id;
											$arr_new_item_insert[$index]['available_quantity'] = $temporaty_available_quantity;

											$arr_new_item_insert[$index]['total_money'] = (float)$quantities*(float)$value['rate'] + ((float)$total_tax_rate/100 * (float)$quantities*(float)$value['rate']);
											$arr_new_item_insert[$index]['total_after_discount'] = (float)$quantities*(float)$value['rate'] + ((float)$total_tax_rate/100 * (float)$quantities*(float)$value['rate']);


											$temporaty_quantity--;
											$temporaty_available_quantity--;
											$index ++;
											$inventory_warehouse_by_commodity[$key]['inventory_number'] = $temporaty_available_quantity;
										}
    								}
    							}
    						}
    						
    						// don't have serial number
    						if($temporaty_quantity > 0){
    							$quantities = $temporaty_quantity;
    							$available_quantity = 0;

    							foreach ($inventory_warehouse_by_commodity as $key => $inventory_warehouse) {
    								if((float)$inventory_warehouse['inventory_number'] > 0 && $temporaty_quantity > 0){

    									$available_quantity = (float)$inventory_warehouse['inventory_number'];
    									$warehouse_id = $inventory_warehouse['warehouse_id'];
    									
    									if ($temporaty_quantity >= $inventory_warehouse['inventory_number']) {
    										$temporaty_quantity = (float) $temporaty_quantity - (float) $inventory_warehouse['inventory_number'];
    										$quantities = (float)$inventory_warehouse['inventory_number'];
    									} else {
    										$quantities = (float)$temporaty_quantity;
    										$temporaty_quantity = 0;
    									}

    									$arr_new_item_insert[$index]['commodity_name'] = $commodity_name;
    									$arr_new_item_insert[$index]['commodity_code'] = $commodity_code;
    									$arr_new_item_insert[$index]['quantities'] = $quantities + 0;
    									$arr_new_item_insert[$index]['unit_price'] = $value['rate'] + 0;
    									$arr_new_item_insert[$index]['tax_rate'] = $tax_rate;
    									$arr_new_item_insert[$index]['tax_name'] = $tax_name;
    									$arr_new_item_insert[$index]['tax_id'] = $str_tax_id;
    									$arr_new_item_insert[$index]['unit_id'] = $unit_id;
    									$arr_new_item_insert[$index]['guarantee_period'] = $warranty;
    									$arr_new_item_insert[$index]['serial_number'] = '';
    									$arr_new_item_insert[$index]['warehouse_id'] = $warehouse_id;
    									$arr_new_item_insert[$index]['available_quantity'] = $available_quantity;

    									$arr_new_item_insert[$index]['total_money'] = (float)$quantities*(float)$value['rate'] + ((float)$total_tax_rate/100 * (float)$quantities*(float)$value['rate']);
    									$arr_new_item_insert[$index]['total_after_discount'] = (float)$quantities*(float)$value['rate'] + ((float)$total_tax_rate/100 * (float)$quantities*(float)$value['rate']);

    									$index ++;
    								}
    							}
    						}
    					}
					}
				}
			}
			$data_insert=[];
			$data_insert['goods_delivery'] = $data;
			$data_insert['goods_delivery_detail'] = $arr_new_item_insert;

			if($invoice_update != ''){
				//case invoice update
				$status = $this->warehouse_model->add_goods_delivery_from_invoice_update($invoice_id, $data_insert);

			}else{
				//case invoice add
				$status = $this->omnisalse_add_goods_delivery_from_invoice($data_insert, $invoice_id);

			}
			if($status){
				return $status;
			}else{
				return $status;
			}
		}
		return false;
	}

	/**
	 * get estimates data
	 * @param  string $estimate_id 
	 * @return [type]              
	 */
	public function get_estimates_data($estimate_id = '')
	{
		if(is_numeric($estimate_id)){
			$sql_where = 'select * from '.db_prefix().'estimates where id NOT IN (SELECT DISTINCT estimate_id from '.db_prefix().'cart where estimate_id is not null) OR '.db_prefix().'estimates.id = '.$estimate_id.' order by id desc';
		}else{
			$sql_where = 'select * from '.db_prefix().'estimates where id NOT IN (SELECT DISTINCT estimate_id from '.db_prefix().'cart where estimate_id is not null) order by id desc';
		}
		$estimates = $this->db->query($sql_where)->result_array();
		return $estimates;
	}

	public function create_order_manual_row_template( $name = '', $product_name = '', $available_quantity = '', $quantity = '', $prices = '', $sku = '', $product_id = '', $item_key = '', $is_edit = false) {
		$this->load->model('invoice_items_model');
		$row = '';
		$name_product_id = 'product_id';
		$name_product_name = 'description';
		$name_available_quantity = 'available_quantity';
		$name_quantity = 'qty';
		$name_prices = 'rate';
		$array_attr = [];
		$array_attr_payment = ['data-payment' => 'invoice'];
		$name_sku = 'sku';

		$array_qty_attr = [ 'min' => '0.0', 'step' => 'any'];
		$array_rate_attr = [ 'min' => '0.0', 'step' => 'any'];
		$str_rate_attr = 'min="0.0" step="any"';

		if ($name == '') {
			$row .= '<tr class="main">
			<td></td>';
			$vehicles = [];
			$array_attr = ['placeholder' => _l('unit_price')];
			$manual             = true;
			$invoice_item_taxes = '';
			$amount = '';
			$sub_total = 0;
			$array_rate_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('rate'), 'readonly' => true];

		} else {
			$row .= '<tr class="sortable item">
			<td class="dragger"><input type="hidden" class="order" name="' . $name . '[order]"><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '"></td>';
			$name_product_id = $name . '[product_id]';
			$name_product_name = $name . '[description]';
			$name_available_quantity = $name . '[available_quantity]';
			$name_quantity = $name . '[qty]';
			$name_prices = $name . '[rate]';
			$name_sku = $name .'[sku]';

			$array_qty_available_quantity = ['min' => '0.0' , 'step' => 'any',  'data-available_quantity' => (float)$available_quantity];
			$array_qty_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any',  'data-quantity' => (float)$quantity];
			
			$array_rate_attr = ['onblur' => 'wh_calculate_total();', 'onchange' => 'wh_calculate_total();', 'min' => '0.0' , 'step' => 'any', 'data-amount' => 'invoice', 'placeholder' => _l('rate'), 'readonly' => true];

			$manual             = false;

			$goods_money = (float)$prices * (float)$quantity;
			$amount = (float)$prices * (float)$quantity;

			$sub_total = (float)$prices * (float)$quantity;
			$amount = app_format_number($amount);
			$sub_total = app_format_number($sub_total);
		}
		if(is_numeric($product_id)){
			$get_asset = $this->get_assets($product_id);
			if($get_asset){
				if($get_asset->type == 'asset'){
					$array_qty_attr['readonly'] = true;
				}
			}
		}


		$row .= '<td class="">' . render_textarea($name_product_name, '', $product_name, ['rows' => 4, 'placeholder' => _l('item_description_placeholder'), 'readonly' => true] ) . '</td>';

		$row .= '<td class="available_quantity"><div class="form-group">
		<div class="available_quantity">
		<input type="number" class="form-control" name="'.$name_available_quantity.'" min="0" value="'.$available_quantity.'" data-available_quantity="'.$available_quantity.'"  readonly="true">
		</div>
		</div></td>';

		$row .= '<td class="quantity"><div class="form-group">
		<div class="quantity">
		'.render_input($name_quantity, '', $quantity, 'number', $array_qty_attr).'
		</div>
		</div></td>';

		$row .= '<td class="rate">' . render_input($name_prices, '', $prices, 'number', $array_rate_attr) . '</td>';

		$row .= '<td class="amount" align="right">' . $sub_total . '</td>';

		$row .= '<td class="hide product_id">' . render_input($name_product_id, '', $product_id, 'text', ['placeholder' => _l('product_id')]) . '</td>';
		$row .= '<td class="hide sku">' . render_input($name_sku, '', $sku, 'text', []) . '</td>';

		if ($name == '') {
			$row .= '<td><button type="button" onclick="wh_add_item_to_table(\'undefined\',\'undefined\'); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button></td>';
		} else {
			$row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="wh_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
		}
		$row .= '</tr>';
		return $row;
	}

	/**
	 * [get taxes dropdown template v2
	 * @param  [type]  $name     
	 * @param  [type]  $taxname  
	 * @param  string  $type     
	 * @param  string  $item_key 
	 * @param  boolean $is_edit  
	 * @param  boolean $manual   
	 * @return [type]            
	 */
	public function get_taxes_dropdown_template_v2($name, $taxname, $type = '', $item_key = '', $is_edit = false, $manual = false)
	{
        // if passed manually - like in proposal convert items or project
		if($taxname != '' && !is_array($taxname)){
			$taxname = explode(',', $taxname);
		}

		if ($manual == true) {
            // + is no longer used and is here for backward compatibilities
			if (is_array($taxname) || strpos($taxname, '+') !== false) {
				if (!is_array($taxname)) {
					$__tax = explode('+', $taxname);
				} else {
					$__tax = $taxname;
				}
                // Multiple taxes found // possible option from default settings when invoicing project
				$taxname = [];
				foreach ($__tax as $t) {
					$tax_array = explode('|', $t);
					if (isset($tax_array[0]) && isset($tax_array[1])) {
						array_push($taxname, $tax_array[0] . '|' . $tax_array[1]);
					}
				}
			} else {
				$tax_array = explode('|', $taxname);
                // isset tax rate
				if (isset($tax_array[0]) && isset($tax_array[1])) {
					$tax = get_tax_by_name($tax_array[0]);
					if ($tax) {
						$taxname = $tax->name . '|' . $tax->taxrate;
					}
				}
			}
		}
        // First get all system taxes
		$this->load->model('taxes_model');
		$taxes = $this->taxes_model->get();
		$i     = 0;
		foreach ($taxes as $tax) {
			unset($taxes[$i]['id']);
			$taxes[$i]['name'] = $tax['name'] . '|' . $tax['taxrate'];
			$i++;
		}
		if ($is_edit == true) {

            // Lets check the items taxes in case of changes.
            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
			$func_taxes = 'get_' . $type . '_item_taxes';
			if (function_exists($func_taxes)) {
				$item_taxes = call_user_func($func_taxes, $item_key);
			}

			foreach ($item_taxes as $item_tax) {
				$new_tax            = [];
				$new_tax['name']    = $item_tax['taxname'];
				$new_tax['taxrate'] = $item_tax['taxrate'];
				$taxes[]            = $new_tax;
			}
		}

        // In case tax is changed and the old tax is still linked to estimate/proposal when converting
        // This will allow the tax that don't exists to be shown on the dropdowns too.
		if (is_array($taxname)) {
			foreach ($taxname as $tax) {
                // Check if tax empty
				if ((!is_array($tax) && $tax == '') || is_array($tax) && $tax['taxname'] == '') {
					continue;
				};
                // Check if really the taxname NAME|RATE don't exists in all taxes
				if (!value_exists_in_array_by_key($taxes, 'name', $tax)) {
					if (!is_array($tax)) {
						$tmp_taxname = $tax;
						$tax_array   = explode('|', $tax);
					} else {
						$tax_array   = explode('|', $tax['taxname']);
						$tmp_taxname = $tax['taxname'];
						if ($tmp_taxname == '') {
							continue;
						}
					}
					$taxes[] = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1]];
				}
			}
		}

        // Clear the duplicates
		$taxes = $this->wh_uniqueByKey($taxes, 'name');

		$select = '<select class="selectpicker display-block taxes" data-width="100%" name="' . $name . '" multiple data-none-selected-text="' . _l('no_tax') . '">';
		foreach ($taxes as $key => $tax) {
			$selected = '';
			if (is_array($taxname)) {

				foreach ($taxname as $_tax) {
					if (is_array($_tax)) {

						if ($_tax['taxname'] == $tax['name']) {
							$selected = 'selected';
						}
					} else {
						if ($_tax == $tax['name']) {
							$selected = 'selected';
						}
					}
				}
			} else {
				if ($taxname == $tax['name']) {
					$selected = 'selected';
				}
			}

			if($selected == ''){
				$selected = 'disabled';
			}
			$select .= '<option value="' . $tax['name'] . '" ' . $selected . ' data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
		}
		$select .= '</select>';

		return $select;
	}
/**
	 * get tax by name
	 * @param  string $name
	 * @return object     
	 */
	public function get_tax_by_name($name, $rate)
	{
		if($name == ''){
			return 0;
		}

		$taxs = $this->db->get(db_prefix().'taxes')->result_array();
		if($taxs){
			foreach ($taxs as $tax) {
				if(strtolower($tax['name']) == strtolower($name)){
					if($tax['taxrate'] != $rate){
						$this->db->where('id', $tax['id']);
						$this->db->update(db_prefix().'taxes', ['taxrate' => $rate]);
					}

					return $tax;
				}
			}
		}

		$this->db->insert(db_prefix().'taxes', ['name' => $name, 'taxrate' => $rate]);
		$insert_id = $this->db->insert_id();
		return ['id' => $insert_id, 'name' => $name, 'taxrate' => $rate];
	}

	/**
	 * checkout item from good delivery
	 * @param  integer $rel_id 
	 */
	public function checkout_item_from_good_delivery($rel_id){
		$goods_delivery = $this->get_goods_delivery($rel_id);
		if($goods_delivery){
			$goods_delivery_detail = $this->get_goods_delivery_detail($rel_id);
			if($goods_delivery->type == 'booking'){
				foreach ($goods_delivery_detail as $key => $value) {
					$item_type = '';
					$this->db->where('id', $value['commodity_code']);
					$data_item = $this->db->get(db_prefix().'fe_assets')->row();
					if($data_item){
						$item_type = $data_item->type;						
						if($item_type == 'asset'){
							$data_checkout["item_id"] = $data_item->id;
							$data_checkout["type"] = "checkout"; 
							$data_checkout["model"] = fe_get_model_name($data_item->model_id); 
							$data_checkout["asset_name"] = $data_item->assets_name; 
							$data_checkout["status"] = fe_get_default_status();
							$data_checkout["checkout_to"] = "customer"; 
							$data_checkout["location_id"] = ""; 
							$data_checkout["asset_id"] = ""; 
							$data_checkout["staff_id"] = "";
							$data_checkout["customer_id"] = $goods_delivery->customer_code;
							$data_checkout["warehouse_id"] = $value['warehouse_id'];
							$data_checkout["checkin_date"] = "";
							$data_checkout["expected_checkin_date"] = "";
							$data_checkout["notes"] = "";
							$this->check_in_assets($data_checkout);
						}
						elseif($item_type == 'license'){
							for ($i = 0; $i < $value['quantities']; $i++) { 
								$data_checkout["id"]=$data_item->id;
								$data_checkout["type"] = "checkout"; 
								$data_checkout["asset_name"] =  $data_item->assets_name;
								$data_checkout["checkout_to"] = "customer"; 
								$data_checkout["asset_id"] = ""; 
								$data_checkout["staff_id"] = ""; 
								$data_checkout["customer_id"] = $goods_delivery->customer_code;
								$data_checkout["warehouse_id"] = $value['warehouse_id'];
								$data_checkout["notes"] = ""; 
								$this->check_in_license_auto($data_checkout, $value['warehouse_id']);
							}
						}
						elseif($item_type == 'accessory'){
							for ($i = 0; $i < $value['quantities']; $i++) { 
								$data_checkout["id"]=""; 
								$data_checkout["item_id"] = $data_item->id; 
								$data_checkout["type"] = "checkout"; 
								$data_checkout["checkout_to"] = "customer"; 
								$data_checkout["status"] = fe_get_default_status(); 
								$data_checkout["asset_name"] = $data_item->assets_name;
								$data_checkout["staff_id"] = ""; 
								$data_checkout["customer_id"] = $goods_delivery->customer_code;
								$data_checkout["warehouse_id"] = $value['warehouse_id'];
								$data_checkout["notes"] = "";
								$this->check_in_accessories($data_checkout);
							}
						}
						elseif($item_type == 'consumable'){
							for ($i = 0; $i < $value['quantities']; $i++) { 
								$data_checkout["id"]=""; 
								$data_checkout["item_id"] = $data_item->id; 
								$data_checkout["type"] = "checkout"; 
								$data_checkout["status"] = fe_get_default_status(); 
								$data_checkout["asset_name"] = $data_item->assets_name;
								$data_checkout["checkout_to"] = "customer"; 
								$data_checkout["staff_id"] = ""; 
								$data_checkout["customer_id"] = $goods_delivery->customer_code;
								$data_checkout["warehouse_id"] = $value['warehouse_id'];
								$data_checkout["notes"] = "";
								$this->check_in_consumables($data_checkout);
							}
						}
						elseif($item_type == 'component'){
							$data_checkout["id"] = ""; 
							$data_checkout["item_id"] = $data_item->id;
							$data_checkout["type"] = "checkout"; 
							$data_checkout["status"] = fe_get_default_status(); 
							$data_checkout["asset_name"] = $data_item->assets_name;
							$data_checkout["quantity"] = $value['quantities'];
							$data_checkout["checkout_to"] = "customer"; 
							$data_checkout["customer_id"] = $goods_delivery->customer_code;
							$data_checkout["asset_id"] = "";
							$data_checkout["warehouse_id"] = $value['warehouse_id'];
							$data_checkout["notes"] = "";
							$this->check_in_components($data_checkout);
						}
					}

					$inv_quantity = $this->get_quantity_inventory_item($value['commodity_code'], $value['warehouse_id']);
					//Add log
					$data_log = [
						'rel_type' 			=> 'inventory_delivery',
						'rel_id' 			=> $rel_id,
						'item_id' 			=> $value['commodity_code'],
						'old_quantity' 		=>  ((float)$inv_quantity + (float)$value['quantities']),
						'quantity' 			=> $inv_quantity,
						'from_warehouse_id' => $value['warehouse_id'],
						'date_add' 			=> date('Y-m-d H:i:s'),
						'added_from_id' 	=> get_staff_user_id()
					];
					$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);


				}
			}
			elseif($goods_delivery->type == 'order' || $goods_delivery->type == ''){
				foreach ($goods_delivery_detail as $key => $value) {
					$item_type = '';
					$item_id = $value['commodity_code'];
					$this->db->where('id', $item_id);
					$data_item = $this->db->get(db_prefix().'fe_assets')->row();
					if($data_item){
						$item_type = $data_item->type;
						if($item_type == 'asset'){
							$this->delete_assets($item_id);
						}
						elseif($item_type == 'license'){
							$this->update_amount_seat($item_id, $value['quantities']);
						}
						elseif($item_type == 'accessory' || $item_type == 'consumable' || $item_type == 'component'){
							$this->db->where('id', $value['commodity_code']);
							$data_item = $this->db->get(db_prefix().'fe_assets')->row();
							if($data_item){
								$new_quantity = $data_item->quantity - (int)$value['quantities'];
								if($new_quantity < 0){
									$new_quantity = 0;
								}
								$this->db->where('id', $value['commodity_code']);
								$this->db->update(db_prefix().'fe_assets', ['quantity' => $new_quantity]);
							}
						}
					}
					$inv_quantity = $this->get_quantity_inventory_item($item_id, $value['warehouse_id']);
					//Add log
					$data_log = [
						'rel_type' 			=> 'inventory_delivery',
						'rel_id' 			=> $rel_id,
						'item_id' 			=> $item_id,
						'old_quantity' 		=> ((float)$inv_quantity + (float)$value['quantities']),
						'quantity' 			=> $inv_quantity,
						'from_warehouse_id' => $value['warehouse_id'],
						'date_add' 			=> date('Y-m-d H:i:s'),
						'added_from_id' 	=> get_staff_user_id()
					];
					$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);
				}
			}


			// Create packing list
			if(is_numeric($goods_delivery->customer_code) && $goods_delivery->customer_code > 0){
				$client                = $this->clients_model->get($goods_delivery->customer_code);
				$data_packing_list['delivery_note_id'] = $goods_delivery->id;
				$data_packing_list['packing_list_number'] = '';
				$data_packing_list['packing_list_name'] = '';
				$data_packing_list['clientid'] = $goods_delivery->customer_code;
				$data_packing_list['subtotal'] = $goods_delivery->sub_total;
				$data_packing_list['total_amount'] = $goods_delivery->total_money;
				$data_packing_list['total_after_discount'] = $goods_delivery->after_discount;

				$data_packing_list['billing_street'] = $client->billing_street;
				$data_packing_list['billing_city'] = $client->billing_city;
				$data_packing_list['billing_state'] = $client->billing_state;
				$data_packing_list['billing_zip'] = $client->billing_zip;
				$data_packing_list['billing_country'] = $client->billing_country;

				$data_packing_list['shipping_street'] = $client->shipping_street;
				$data_packing_list['shipping_city'] = $client->shipping_city;
				$data_packing_list['shipping_state'] = $client->shipping_state;
				$data_packing_list['shipping_zip'] = $client->shipping_zip;
				$data_packing_list['shipping_country'] = $client->shipping_country;

				$data_packing_list['client_note'] = '';
				$data_packing_list['admin_note'] = '';
				$data_packing_list['approval'] = 1;
				$data_packing_list['sales_order_reference'] = $this->incrementalHash();

				$data_packing_list['datecreated'] = date('Y-m-d-H-i-s');
				$data_packing_list['staff_id'] = get_staff_user_id();

				$detail_packing = [];
				foreach ($goods_delivery_detail as $key => $value) {
					$detail_row['packing_list_id'] = '';
					$detail_row['delivery_detail_id'] = $value['id'];
					$detail_row['commodity_code'] = $value['commodity_code'];
					$detail_row['commodity_name'] = $value['commodity_name'];
					$detail_row['quantity'] = $value['quantities'];
					$detail_row['unit_price'] = $value['unit_price'];
					$detail_row['sub_total'] = $value['sub_total'];
					$detail_row['total_amount'] = $value['total_money'];
					$detail_row['discount'] = 0;
					$detail_row['discount_total'] = 0;
					$detail_row['total_after_discount'] = $value['total_after_discount'];
					$detail_packing[] = $detail_row;
				}
				$data_packing_list['detail'] = $detail_packing;
				$this->add_packing_list($data_packing_list);
			}
		}
	}

	/**
	 * update amount seat
	 * @param  integer $id            
	 * @param  integer $seat_quantity 
	 */
	public function update_amount_seat($id, $seat_quantity){
		$data_all_seat = $this->get_seat_by_parent($id);
		$data_avail_seat = $this->get_seat_by_parent($id, 1);
		$total_all = count($data_all_seat);
		$total_avail = count($data_avail_seat);
		if($seat_quantity < $total_all){

			// Remove seat
			// $remain = $total_all - $seat_quantity;
			// if($remain <= $total_avail){
			// 	foreach ($data_avail_seat as $key => $value) {
			// 		$this->db->where('id', $value['id']);
			// 		$this->db->delete(db_prefix() . 'fe_seats');
			// 		if ($this->db->affected_rows() > 0) {
			// 			$this->db->where('item_id', $value['id']);
			// 			$this->db->delete(db_prefix().'fe_checkin_assets');
			// 		}
			// 		if(($key+1) == $remain){
			// 			break;
			// 		}
			// 	}
			// }
		}
		return true;
	}


	/**
	 * get cart of client by status
	 * @param  int  $userid 
	 * @param  int $status 
	 * @return array          
	 */
	public function get_cart_of_client_by_status($userid = '', $status = 0, $channel_id = '', $where = ''){
		if($where != ''){
			$this->db->where($where);
		}
		if($userid != ''){
			if($channel_id != ''){
				if($channel_id == 2){
					$this->db->where('(channel_id = 2 OR channel_id = 4)');   
				}
				else{
					$this->db->where('channel_id', $channel_id);   					
				}
			}
			$this->db->where('userid',$userid);
			$this->db->where('status',$status);
			$this->db->order_by('datecreator', 'DESC');
			return $this->db->get(db_prefix().'fe_cart')->result_array();
		}
		elseif($userid == '' && $status !=''){  
			if($channel_id != ''){
				if($channel_id == 2){
					$this->db->where('(channel_id = 2 OR channel_id = 4)');   
				}
				else{
					$this->db->where('channel_id', $channel_id);   					
				}
			}
			$this->db->where('status',$status);   
			$this->db->where('original_order_id is null');   
			$this->db->order_by('datecreator', 'DESC');
			return $this->db->get(db_prefix().'fe_cart')->result_array();
		}
		else{
			return $this->db->get(db_prefix().'fe_cart')->result_array();
		}
	}

	/**
	 * get shipment by order
	 * @param  [type] $order_id 
	 * @return [type]           
	 */
	public function get_shipment_by_order($order_id)
	{
		if (is_numeric($order_id)) {
			$this->db->where('cart_id', $order_id);
			return $this->db->get(db_prefix() . 'fe_omni_shipments')->row();
		}
		if ($order_id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_omni_shipments')->result_array();
		}
	}

/**
	 * create return request portal
	 * @param  array $data         
	 * @param  string $order_number 
	 * @return integer               
	 */
	public function create_return_request_portal($data, $order_number){
		$order = $this->get_cart_by_order_number($order_number);
		if($order){
			$fee_for_return_order = 0;		
			$subtotal = 0;
			$discount_total = $order->discount;
			$totaltax = 0;
			foreach ($data['item'] as $key => $item) {
				if(isset($item['select'])){
					$this->db->where('id', $item['select']);
					$cart_data = $this->db->get(db_prefix().'fe_cart_detailt')->row();
					if($cart_data){
						if($order->type == 'order'){
							$subtotal += $cart_data->prices * (float)$item['quantity'];
						}
						else{
							$subtotal += $cart_data->rental_value;							
						}
					}
				}
			}
			$total = $subtotal + ($totaltax != '' ? $totaltax : 0)-($discount_total != '' ? $discount_total : 0) + $fee_for_return_order;
			//Change status for ogriginal order
			if($data['return_type'] == 'fully'){
				$this->db->where('id', $order->id);
				$this->db->update(db_prefix().'fe_cart', ['status' => 11]);
			}
			else{
				$this->db->where('id', $order->id);
				$this->db->update(db_prefix().'fe_cart', ['status' => 12]);
			}
			//Create return order
			$order->original_order_id = $order->id;
			unset($order->id);
			$order->status = 0;
			$order->sub_total = $subtotal;
			$order->tax = $totaltax;
			$order->discount = $discount_total;
			$order->shipping = 0;
			$order->total = $total;
			$order->discount_total = '';
			$order->order_number = $this->incrementalHash();
			$order->fee_for_return_order = $fee_for_return_order;
			$order->datecreator = date('Y-m-d H:i:s');
			$order->return_reason = $data['reason'];
			$order->return_type = $data['return_type'];
			$order->return_reason_type = $data['return_reason_type'];
			$order->hash = app_generate_hash();
			$order->invoice = '';
			$order->number_invoice = '';
			$order->stock_import_number = 0;
			$order->stock_export_number = '';
			$this->db->insert(db_prefix().'fe_cart', (array)$order);
			$insert_order_id = $this->db->insert_id();
			if($insert_order_id){
				foreach($data['item'] as $item){
					if(isset($item['select'])){
						$this->db->where('id', $item['select']);
						$data_cart_detail = $this->db->get(db_prefix().'fe_cart_detailt')->row();
						if($data_cart_detail){
							unset($data_cart_detail->id);
							$data_cart_detail->quantity = $item['quantity'];
							$data_cart_detail->cart_id = $insert_order_id;
							$this->db->insert(db_prefix().'fe_cart_detailt', (array)$data_cart_detail);
						}
					}
				}
			}
			/*write log*/
			$data_log = [];
			$data_log['rel_id'] = $insert_order_id;
			$data_log['rel_type'] = 'order_returns';
			$data_log['staffid'] = get_staff_user_id();
			$data_log['date'] = date('Y-m-d H:i:s');
			$data_log['note'] = "order_returns";
			$this->add_activity_log($data_log);
			return $insert_order_id;
		}
	}

	/**
	 * change status return order
	 * @param  array $data 
	 * @return boolean       
	 */
	public function change_status_return_order($data){
	  // Status change to canceled
		$status = 8;
		if($data['status'] == 1){
			// Status change to confirm
			$status = 3;
		}
		$this->db->where('id', $data['order_id']);
		$this->db->update(db_prefix().'fe_cart', ['approve_status' => $data['status'], 'status' => $status, 'reason' => $data['cancel_reason']]);
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get activity log
	 * @param  [type] $id   
	 * @param  [type] $type 
	 * @return [type]       
	 */
	public function wh_get_activity_log($id, $rel_type)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', $rel_type);
        $this->db->order_by('date', 'ASC');
        return $this->db->get(db_prefix() . 'fe_goods_delivery_activity_log')->result_array();
    }


    	/**
	 * create import stock
	 * @param  integer $order_id     
	 * @param  integer $warehouse_id 
	 * @return integer               
	 */
	public function create_import_stock($order_id, $warehouse_id){
		$order = $this->get_cart($order_id);
		if($order){
			$data["save_and_send_request"] = false;
			$data["date_c"] = date('Y-m-d');
			$data["date_add"] = date('Y-m-d');
			$data["pr_order_id"] = "";
			$data["supplier_code"] = "";
			$data["supplier_name"] = "";
			$data["buyer_id"] = "";
			$data["project"] = "";
			$data["deliver_name"] = "";
			$data["warehouse_id_m"] = $warehouse_id;
			$data["invoice_no"] = "";
			$data["item_select"] = "";
			$data["commodity_name"] = "";
			$data["warehouse_id"] = $warehouse_id;
			$data["note"] = "";
			$data["quantities"] = "";
			$data["unit_name"] = "";
			$data["date_manufacture"] = "";
			$data["commodity_code"] = "";
			$data["unit_id"] = "";
			$data["from_order"] = $order_id;


			$sub_total = 0;
			$newitems = [];
			$data_cart_detail = $this->get_cart_detailt_by_master($order_id);
			foreach ($data_cart_detail as $key => $value) {
				$serial_number = '';
				$commodity_code = '';
				$commodity_name = '';
				$this->db->where('id', $value['product_id']);
				$this->db->select('series, type, model_id');
				$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
				if($data_asset){
					$serial_number = $data_asset->series;
					$commodity_code = ($data_asset->type == 'asset' ? fe_get_model_item($value['product_id']).'-model' : $value['product_id']);
					$commodity_name = $value['product_name'];
				}
				$data_item["order"] = $key + 1;
				$data_item["id"] = $value['product_id'];
				$data_item["commodity_name"] = $commodity_name;
				$data_item["warehouse_id"] = $warehouse_id;
				$data_item["note"] = "";
				$data_item["quantities"] = $value['quantity'];
				$data_item["unit_price"] = $value['prices'];
				$data_item["tax_select"] = $value['tax'];
				$data_item["commodity_code"] = $commodity_code;
				$data_item["serial_number"] = $serial_number;
				$data_item["unit_id"] = "";
				$sub_total += (float)$value['quantity'] * (float)$value['prices'];
				$newitems[] = $data_item;
			}
			$data["newitems"] = $newitems;
			$data["total_goods_money"] = $sub_total;
			$data["value_of_inventory"] = $sub_total;
			$data["total_tax_money"] = 0;
			$data["total_money"] = $sub_total;
			$data["description"] = "";
			$insert_id = $this->fixed_equipment_model->add_goods_receipt($data, true);
			if($insert_id){
				$this->db->where('id', $order_id);
				$this->db->update(db_prefix().'fe_cart', ['stock_import_number' => $insert_id]);
				return true;
			}
		}
		return false;
	}

	/**
	 * checkin item from good receipt
	 * @param  array $data 
	 * @return boolean       
	 */
	public function checkin_item_from_good_receipt($data){
		if(!is_numeric($data['id'])){
			// Insert for asset
			$id_exp = explode('-', $data['id']);
			if(isset($id_exp[0]) && $model_id = $id_exp[0]){	
				$item_id = fe_get_item_id_from_serial($data['serial_number']);
				if(is_numeric($item_id) && $item_id > 0){
					$status_id = fe_get_default_status();
					$model_name = fe_get_model_name($model_id);
					$assets_name = '';
					$this->db->select('assets_name');
					$this->db->where('id', $item_id);
					$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
					if($data_asset){
						$assets_name = $data_asset->assets_name;
					}

					$data_insert["item_id"] = $item_id;
					$data_insert["type"] = "checkin" ;
					$data_insert["model"] = $model_name;
					$data_insert["asset_name"] = (($assets_name == '' || $assets_name == null) ? $model_name : $assets_name );
					$data_insert["status"] = "1" ;
					$data_insert["location_id"] = "" ;
					$data_insert["checkin_date"] = date('Y-m-d');
					$data_insert["notes"] = "" ;
					$this->check_in_assets($data_insert);

					//Add log
					$data_log = [
						'rel_type' 			=> 'inventory_receiving',
						'rel_id' 			=> $data['rel_id'],
						'item_id' 			=> $item_id,
						'old_quantity' 		=> 0,
						'quantity' 			=> $data['quantities'],
						'from_warehouse_id' => $data['warehouse_id'],
						'date_add' 			=> date('Y-m-d H:i:s'),
						'added_from_id' 	=> get_staff_user_id()
					];
					$this->db->insert(db_prefix(). 'fe_goods_transaction_details', $data_log);
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * cancel invoice
	 * @param  integer $invoice_id 
	 * @return boolean             
	 */
	public function cancel_invoice($order_id, $invoice_id){
		$this->load->model('invoices_model');
		$success = $this->invoices_model->mark_as_cancelled($invoice_id);
		if($success){
			$this->db->where('id', $order_id);
			$this->db->update(db_prefix().'fe_cart', ['process_invoice' => 'on']);
			return true;
		}
		return false;
	}

	/**
	 * update invoice
	 * @param  integer $invoice_id 
	 * @return boolean             
	 */	
	public function update_invoice($order_id, $invoice_id){
		$this->load->model('invoices_model');
		$cart_data = $this->get_cart($order_id);
		$invoice_data = $this->invoices_model->get($invoice_id);
		if($invoice_data && $cart_data){
			$order_type = $cart_data->type;

			$using_items = [];
			$newitems = [];
			$count = 0;

			$cart_detailt = $this->get_cart_detailt_by_master($order_id);
			$removed_items = [];
			foreach ($cart_detailt as $key => $value) {
				$this->db->select('id');
				$this->db->where('rel_id', $invoice_id);
				$this->db->where('rel_type', 'invoice');
				$this->db->where('description = "'.$value['product_name'].'"');
				$itemable_data = $this->db->get(db_prefix()."itemable")->row();
				if($itemable_data){
					$removed_items[] = $itemable_data->id;
				}
			}  

			$total = 0;
			$old_cart_detailt = $this->get_cart_detailt_by_master($cart_data->original_order_id);
			foreach ($old_cart_detailt as $key => $value) {
				$this->db->select('id');
				$this->db->where('rel_id', $invoice_id);
				$this->db->where('rel_type', 'invoice');
				$this->db->where('description = "'.$value['product_name'].'"');
				$itemable_data = $this->db->get(db_prefix()."itemable")->row();
				if($itemable_data){
					$itemid = $itemable_data->id;
					if(!in_array($itemid, $removed_items)){
						$taxname = '';
						$unit_name = '';
						$rate = 0;
						if($order_type == 'order'){
							// Order type
							$rate = (int)$value['quantity']*$value['prices'];;
						}
						else{
							// Booking type
							$rate = $value['rental_value'];;
						}
						$total += (float)$rate;
						array_push($newitems, array('itemid' => $itemid, 'order' => $key, 'description' => $value['product_name'], 'long_description' => $value['long_description'], 'qty' => $value['quantity'], 'unit' => $unit_name, 'rate'=> $rate, 'taxname' => array($taxname)));

					}
				}
			}
			$invoice_data_update['items'] = $newitems;
			$invoice_data_update['subtotal'] = $total;
			$invoice_data_update['total'] = $total;
			$invoice_data_update['discount_total'] = 0;
			$invoice_data_update['removed_items'] =  $removed_items;
			$success = $this->invoices_model->update($invoice_data_update, $invoice_id);
			if ($success) {
				// Update invoice to return order
				$this->db->where('id', $order_id);
				$this->db->update(db_prefix().'fe_cart', ['process_invoice' => 'on']);
				return true;
			}
		}
		return false;
	} 

	/**
	 * get quantity inventory item
	 * @param  integer $item_id      
	 * @param  integer $warehouse_id 
	 * @return integer               
	 */
	public function get_quantity_inventory_item($item_id, $warehouse_id){
		$total = 0;
		$this->db->select('type, series');
		$this->db->where('id', $item_id);
		$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
		if($data_asset){
			$item_type = $data_asset->type;
			$total_receipt_qty = 0;
			$total_delivery_qty = 0;
			if($item_type == 'asset'){
				$receipt = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_receipt_detail a left join '.db_prefix().'fe_goods_receipt b on a.goods_receipt_id = b.id where serial_number = \''.$data_asset->series.'\' and b.approval = 1 and a.warehouse_id = '.$warehouse_id)->row();
				if($receipt){
					$total_receipt_qty = $receipt->sum;
				}
			}
			else{
				$receipt = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_receipt_detail a left join '.db_prefix().'fe_goods_receipt b on a.goods_receipt_id = b.id where commodity_code = '.$item_id.' and b.approval = 1 and a.warehouse_id = '.$warehouse_id)->row();
				if($receipt){
					$total_receipt_qty = $receipt->sum;
				}
			}
			$delivery = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_delivery_detail a left join '.db_prefix().'fe_goods_delivery b on a.goods_delivery_id = b.id where commodity_code = '.$item_id.' and b.approval = 1 and a.warehouse_id = '.$warehouse_id)->row();
			if($delivery){
				$total_delivery_qty = $delivery->sum;
			}
			$total = $total_receipt_qty - $total_delivery_qty;
			if($total < 0){
				$total = 0;
			}
		}
		return $total;
	}


	/**
	* create shipment from order
	* @param  [type] $order_id 
	* @return [type]           
	*/
	public function create_shipment_from_order($order_id)
	{
		// create shipment
		$cart = $this->get_cart($order_id);
		if($cart){
			$shipment = [];
			$shipment['cart_id'] = $order_id;
			$shipment['shipment_number'] = 'SHIPMENT' . date('YmdHi');
			$shipment['planned_shipping_date'] = null;
			$shipment['shipment_status'] = 'confirmed_order';
			$shipment['datecreated'] = date('Y-m-d H:i:s');
			if(is_numeric($cart->stock_export_number)){
				$shipment['goods_delivery_id'] = $cart->stock_export_number;
			}
			$shipment['shipment_hash'] = app_generate_hash();

			$this->db->insert(db_prefix() . 'fe_omni_shipments', $shipment);
			$insert_id = $this->db->insert_id();
			if($insert_id){
				$shipment_log1 = _l('wh_order_has_been_confirmed');
				$this->log_inventory_activity($insert_id, 'shipment', $shipment_log1);
				$shipment_log2 = _l('wh_shipment_have_been_created');
				$this->log_inventory_activity($insert_id, 'shipment', $shipment_log2);

				return $insert_id;
			}
		}
		return false;
	}


	/**
	 * wh get shipment activity log
	 * @param  [type] $shipment_id 
	 * @return [type]              
	 */
	public function wh_get_shipment_activity_log($shipment_id)
	{
		$cart_id = '';
		$delivery_id = '';
		$packing_list_id = [];
		$arr_activity_log = [];
		$this->db->where('id', $shipment_id);
		$shipment = $this->db->get(db_prefix() . 'fe_omni_shipments')->row();
		if($shipment){
			$cart_id = $shipment->cart_id;
			if(is_numeric($cart_id) && $cart_id != 0){
				$get_cart = $this->get_cart($shipment->cart_id);
				if($get_cart && is_numeric($get_cart->stock_export_number)){
					// get order activity_log
					$delivery_id = $get_cart->stock_export_number;

					$packing_lists = $this->get_packing_list_by_deivery_note($get_cart->stock_export_number);

					if(count($packing_lists) > 0){
						foreach ($packing_lists as $value) {
							$packing_list_id[] = $value['id'];
						}
					}
				}
			}
		}

		$this->db->or_group_start();
		$this->db->where('rel_id', $shipment_id);
		$this->db->where('rel_type', 'shipment');
		$this->db->group_end();
		if(strlen($cart_id) > 0){
			$this->db->or_group_start();
			$this->db->where('rel_id', $cart_id);
			$this->db->where('rel_type', 'fe_order');
			$this->db->group_end();
		}

		if(strlen($delivery_id) > 0){
			$this->db->or_group_start();
			$this->db->where('rel_id', $delivery_id);
			$this->db->where('rel_type', 'delivery');
			$this->db->group_end();
		}

		if(count($packing_list_id) > 0){
			$this->db->or_group_start();
			$this->db->where('rel_id IN ('.implode(',', $packing_list_id).')');
			$this->db->where('rel_type', 'packing_list');
			$this->db->group_end();
		}
		$this->db->order_by('date', 'desc');
		$shipment_activity_log = $this->db->get(db_prefix().'fe_goods_delivery_activity_log')->result_array();
		return $shipment_activity_log;
	}

	/**
	 * update activity log
	 * @param  [type] $id   
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public function update_activity_log($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().'fe_goods_delivery_activity_log', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * wh get activity log by id
	 * @param  integer $id 
	 * @return object     
	 */
	public function wh_get_activity_log_by_id($id)
	{
		$this->db->where('id', $id);
		return $this->db->get(db_prefix() . 'fe_goods_delivery_activity_log')->row();
	}

	/**
	 * get_shipment_log_attachments
	 * @param  integer $log_id 
	 * @return array         
	 */
	public function get_shipment_log_attachments($log_id){
		$this->db->order_by('dateadded', 'desc');
		$this->db->where('rel_id', $log_id);
		$this->db->where('rel_type', 'shipment_image');
		return $this->db->get(db_prefix() . 'files')->result_array();
	}

	/**
	 * delete shipment
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_shipment($id)
	{
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_omni_shipments');
		if($this->db->affected_rows() > 0){
			return true;
		}
		return false;
	}

	/**
	 * update cart
	 * @param  int $id 
	 * @return boolean   
	 */
	public function update_cart($id = '', $data = []){
		if($id != ''){
			$this->db->where('id',$id);
			$this->db->update(db_prefix().'fe_cart', $data);
			if ($this->db->affected_rows() > 0) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * add packing list
	 * @param array $data 
	 */
	public function add_packing_list($data){
		$detail = [];
		if(isset($data['detail'])){
			$detail = $data['detail'];
			unset($data['detail']);
		}
		$data['packing_list_number'] = $this->create_packing_list_code();
		$this->db->insert(db_prefix().'fe_packing_lists', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			$this->update_inventory_setting(['fe_next_packing_list_number' =>  get_option('fe_next_packing_list_number')+1]);
			if(is_array($detail)){
				foreach ($detail as $key => $value) {
					$value['packing_list_id'] = $insert_id;
					$this->db->insert(db_prefix().'fe_packing_list_details', $value);
				}
			}
			return $insert_id;
		}
		return 0;
	}

	/**
	 * create packing_list code
	 * @return	string
	 */
	public function create_packing_list_code() {
		$goods_code = get_option('fe_packing_list_prefix') . get_option('fe_next_packing_list_number');
		return $goods_code;
	}

	/**
	* delete packing list
	* @param  integer $id 
	* @return boolean     
	*/
	public function delete_packing_list($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_packing_lists');
		if($this->db->affected_rows() > 0) {
			$this->db->where('packing_list_id', $id);
			$this->db->delete(db_prefix().'fe_packing_list_details');
			return true;
		}
		return false;
	}

	/**
	 * get packing list
	 * @param  integer $id 
	 * @return object or array    
	 */
	public function get_packing_list($id)
	{
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'fe_packing_lists')->row();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_packing_lists')->result_array();
		}
	}

	/**
	 * get packing list detail
	 * @param  integer $id
	 * @return array
	 */
	public function get_packing_list_detailt_by_master($id) {
		if (is_numeric($id)) {
			$this->db->where('packing_list_id', $id);
			return $this->db->get(db_prefix() . 'fe_packing_list_details')->result_array();
		}
		if ($id == false) {
			return $this->db->query('select * from '.db_prefix().'fe_packing_list_details')->result_array();
		}
	}


/**
	 * delivery status mark as
	 * @param  [type] $status 
	 * @param  [type] $id     
	 * @param  [type] $type   
	 * @return [type]         
	 */
	public function delivery_status_mark_as($status, $id, $type)
	{

		$status_f = false;
		if($type == 'delivery'){
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'fe_goods_delivery', ['delivery_status' => $status]);
			if ($this->db->affected_rows() > 0) {
				$status_f = true;
				//write log
				$this->log_wh_activity($id, 'delivery', _l('fe_'.$status));

				$get_goods_delivery = $this->get_goods_delivery($id);
				if($get_goods_delivery && is_numeric($get_goods_delivery->customer_code)){
					$this->warehouse_check_update_shipment_when_delivery_note_approval($id, $status, 'delivery_status_mark');
				}
				
				$this->check_update_shipment_when_delivery_note_approval($id, $status, 'delivery_status_mark');

			}
		}elseif($type == 'packing_list'){
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'fe_packing_lists', ['delivery_status' => $status]);
			if ($this->db->affected_rows() > 0) {
				$status_f = true;
				//write log for packing list
				$this->log_wh_activity($id, 'packing_list', _l('fe_'.$status));


				//write log for delivery note
				$activity_log = '';
				$delivery_id = '';
				$get_packing_list = $this->get_packing_list($id);
				if($get_packing_list){
					$activity_log .= $get_packing_list->packing_list_number .' - '.$get_packing_list->packing_list_name;
					$delivery_id = $get_packing_list->delivery_note_id;
				}
				$activity_log .= ': '._l('fe_'.$status);
				if(is_numeric($delivery_id)){
					
					$get_goods_delivery = $this->get_goods_delivery($delivery_id);
					if($get_goods_delivery && is_numeric($get_goods_delivery->customer_code)){
						$this->warehouse_check_update_shipment_when_delivery_note_approval($id, $status, 'packing_list_status_mark', $delivery_id);
					}
					
					$this->check_update_shipment_when_delivery_note_approval($id, $status, 'packing_list_status_mark', $delivery_id);


					$delivery_note_log_des = ' <a href="'.admin_url('fixed_equipment/view_packing_list/' . $id).'">'.$activity_log.'</a> ';
					$this->log_wh_activity($delivery_id, 'delivery', $delivery_note_log_des);

				// check update delivery status of delivery note
					$delivery_list_status = fe_delivery_list_status();
					$arr_delivery_list_status_name = [];
					$arr_delivery_list_status_order = [];
					foreach ($delivery_list_status as $value) {
					    $arr_delivery_list_status_name[$value['id']] = $value['order'];
					    $arr_delivery_list_status_order[$value['order']] = $value['id'];
					}

					$get_packing_list_by_deivery_note = $this->get_packing_list_by_deivery_note($delivery_id);
					if(count($get_packing_list_by_deivery_note) > 0){
						$goods_delivery_status = '';
						$goods_delivery_status_order = '';
						$packing_list_order = 0;

						$get_goods_delivery = $this->get_goods_delivery($delivery_id);
						if($get_goods_delivery){
							$goods_delivery_status = $get_goods_delivery->delivery_status;
						}

						if(isset($arr_delivery_list_status_name[$goods_delivery_status])){
							$goods_delivery_status_order = $arr_delivery_list_status_name[$goods_delivery_status];
						}
						
						foreach ($get_packing_list_by_deivery_note as $value) {
						    if(isset($arr_delivery_list_status_name[$value['delivery_status']])){
						    	if((int)$arr_delivery_list_status_name[$value['delivery_status']] >=  $packing_list_order){
						    		$packing_list_order = (int)$arr_delivery_list_status_name[$value['delivery_status']];
						    	}
						    }
						}

						if((int)$packing_list_order > (int)$goods_delivery_status_order){
							if(isset($arr_delivery_list_status_order[$packing_list_order])){
								$this->db->where('id', $delivery_id);
								$this->db->update(db_prefix() . 'fe_goods_delivery', ['delivery_status' => $arr_delivery_list_status_order[$packing_list_order] ]);

								$get_goods_delivery = $this->get_goods_delivery($delivery_id);
								if($get_goods_delivery && is_numeric($get_goods_delivery->customer_code)){
									$this->warehouse_check_update_shipment_when_delivery_note_approval($delivery_id, $arr_delivery_list_status_order[$packing_list_order], 'delivery_status_mark');
								}
								
								$this->check_update_shipment_when_delivery_note_approval($delivery_id, $arr_delivery_list_status_order[$packing_list_order], 'delivery_status_mark');

							}
						}

					}
				}

				
			}
		}
	  return $status_f;
	}
	

	  /**
     * log wh activity
     * @param  [type] $id              
     * @param  [type] $description     
     * @param  string $additional_data 
     * @return [type]                  
     */
    public function log_wh_activity($id, $rel_type, $description, $date = '')
    {
    	if(strlen($date) == 0){
    		$date = date('Y-m-d H:i:s');
    	}
        $log = [
            'date'            => $date,
            'description'     => $description,
            'rel_id'          => $id,
            'rel_type'          => $rel_type,
            'staffid'         => get_staff_user_id(),
            'full_name'       => get_staff_full_name(get_staff_user_id()),
        ];

        $this->db->insert(db_prefix() . 'fe_goods_delivery_activity_log', $log);
        $insert_id = $this->db->insert_id();
        if($insert_id){
        	if($rel_type == 'delivery'){
        		$this->notify_customer_shipment_status($id);
        	}
        	return $insert_id;
        }
        return false;
    }


    /**
	 * warehouse check update shipment when delivery note approval
	 * @param  [type]  $rel_id      
	 * @param  string  $status      
	 * @param  string  $rel_type    
	 * @param  integer $delivery_id 
	 * @return [type]               
	 */
	public function warehouse_check_update_shipment_when_delivery_note_approval($rel_id, $status = 'quality_check', $rel_type = 'delivery_approval', $delivery_id = 0)
	{

		$delivery_list_status = fe_delivery_list_status();
		$arr_delivery_list_status_name = [];
		$arr_delivery_list_status_order = [];
		foreach ($delivery_list_status as $value) {
			$arr_delivery_list_status_name[$value['id']] = $value['order'];
			$arr_delivery_list_status_order[$value['order']] = $value['id'];
		}

		if($status == 'quality_check' && $rel_type == 'delivery_approval'){


			$shipment = $this->get_shipment_by_delivery($rel_id);
			if($shipment){
				$this->update_shipment_status($shipment->id, ['shipment_status' => 'quality_check']);
				return true;
			}
			return false;

		}elseif($rel_type == 'delivery_status_mark'){

			$shipment = $this->get_shipment_by_delivery($rel_id);
			if($shipment){

				if(isset($arr_delivery_list_status_name[$status])){
					if((int)$arr_delivery_list_status_name[$status] >= 4){
							// delivered
						$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_delivered']);
					}elseif((int)$arr_delivery_list_status_name[$status] >= 3){
							// delivery_in_progress
						$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_dispatched']);
					}
				}
			}

		}elseif($rel_type == 'packing_list_status_mark'){

			$shipment = $this->get_shipment_by_delivery($rel_id);
			if($shipment){
				if(isset($arr_delivery_list_status_name[$status])){
					if((int)$arr_delivery_list_status_name[$status] >= 3){
							// delivery_in_progress
						$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_dispatched']);
					}
				}
			}

		}
		return true;
	}


	/**
	 * update shipment status
	 * @param  [type] $id   
	 * @param  array  $data 
	 * @return [type]       
	 */
	public function update_shipment_status($id, $data = [])
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'fe_omni_shipments', $data);
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}


	/**
	 * check update shipment when delivery note approval
	 * @param  [type] $delivery_id 
	 * @return [type]              
	 */
	public function check_update_shipment_when_delivery_note_approval($rel_id, $status = 'quality_check', $rel_type = 'delivery_approval', $delivery_id = 0)
	{
		$delivery_list_status = fe_delivery_list_status();
		$arr_delivery_list_status_name = [];
		$arr_delivery_list_status_order = [];
		foreach ($delivery_list_status as $value) {
			$arr_delivery_list_status_name[$value['id']] = $value['order'];
			$arr_delivery_list_status_order[$value['order']] = $value['id'];
		}
		if($status == 'quality_check' && $rel_type == 'delivery_approval'){
			$this->db->where('stock_export_number',$rel_id);
			$cart = $this->db->get(db_prefix().'fe_cart')->row();
			if($cart){
				$shipment = $this->get_shipment_by_order($cart->id);
				if($shipment){
					$this->update_shipment_status($shipment->id, ['shipment_status' => 'quality_check']);
					return true;
				}
				return false;
			}
			return false;
		}elseif($rel_type == 'delivery_status_mark'){
			$this->db->where('stock_export_number',$rel_id);
			$cart = $this->db->get(db_prefix().'fe_cart')->row();
			if($cart){
				$shipment = $this->get_shipment_by_order($cart->id);
				if($shipment){
					if(isset($arr_delivery_list_status_name[$status])){
						if((int)$arr_delivery_list_status_name[$status] >= 4){
							// delivered
							$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_delivered']);
						}elseif((int)$arr_delivery_list_status_name[$status] >= 3){
							// delivery_in_progress
							$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_dispatched']);
						}
					}
				}
			}
		}elseif($rel_type == 'packing_list_status_mark'){
			$this->db->where('stock_export_number',$delivery_id);
			$cart = $this->db->get(db_prefix().'fe_cart')->row();
			if($cart){
				$shipment = $this->get_shipment_by_order($cart->id);
				if($shipment){
					if(isset($arr_delivery_list_status_name[$status])){
						if((int)$arr_delivery_list_status_name[$status] >= 3){
							// delivery_in_progress
							$this->update_shipment_status($shipment->id, ['shipment_status' => 'product_dispatched']);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * update cart detail
	 * @param  int $id 
	 * @return boolean   
	 */
	public function update_cart_detail($id = '', $data = []){
		if($id != ''){
			$this->db->where('id',$id);
			$this->db->update(db_prefix().'fe_cart_detailt', $data);
			if ($this->db->affected_rows() > 0) {
				return true;
			}
			return false;
		}
		return false;
	}


	/**
	 * get quantity inventory item
	 * @param  integer $item_id      
	 * @param  integer $warehouse_id 
	 * @return integer               
	 */
	public function get_stock_quantity_item($item_id, $exclude_in_cart = false){
		$total = 0;
		$this->db->select('type, series');
		$this->db->where('id', $item_id);
		$data_asset = $this->db->get(db_prefix().'fe_assets')->row();
		if($data_asset){
			$item_type = $data_asset->type;
			$total_receipt_qty = 0;
			$total_delivery_qty = 0;
			if($item_type == 'asset'){
				$receipt = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_receipt_detail a left join '.db_prefix().'fe_goods_receipt b on a.goods_receipt_id = b.id where serial_number = \''.$data_asset->series.'\' and b.approval = 1')->row();
				if($receipt){
					$total_receipt_qty = $receipt->sum;
				}
			}
			else{
				$receipt = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_receipt_detail a left join '.db_prefix().'fe_goods_receipt b on a.goods_receipt_id = b.id where commodity_code = '.$item_id.' and b.approval = 1')->row();
				if($receipt){
					$total_receipt_qty = $receipt->sum;
				}
			}
			$delivery = $this->db->query('select sum(quantities) as sum FROM '.db_prefix().'fe_goods_delivery_detail a left join '.db_prefix().'fe_goods_delivery b on a.goods_delivery_id = b.id where commodity_code = '.$item_id.' and b.approval = 1')->row();
			if($delivery){
				$total_delivery_qty = $delivery->sum;
			}

			$exclude_total = 0;
			if($exclude_in_cart){
				$data_exclude = $this->db->query('select sum(quantity) as quantity FROM '.db_prefix().'fe_cart_detailt a left join '.db_prefix().'fe_cart b on a.cart_id = b.id where product_id = '.$item_id.' and b.status = 0 and b.original_order_id is null')->row();
				if($data_exclude){
					$exclude_total = $data_exclude->quantity;
				}
			}
			$total = $total_receipt_qty - $total_delivery_qty - $exclude_total;
			if($total < 0){
				$total = 0;
			}
		}
		return $total;
	}

	/**
	 * send notify new object
	 */
	public function send_notify_new_object($data){
		$id = $data['rel_id'];
		$request_type = $data['rel_type'];
		$link = '';
		if($request_type == 'checkout'){
			$this->db->select('creator_id, request_title');
			$this->db->where('id', $id);
			$_data = $this->db->get(db_prefix().'fe_checkin_assets')->row();
			if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
				$creator_id = $_data->creator_id;
				$obj_name = $_data->request_title;
			}
			$link = 'fixed_equipment/detail_request/'.$id;
		}
		elseif($request_type == 'audit'){
			$this->db->select('creator_id, title');
			$this->db->where('id', $id);
			$_data = $this->db->get(db_prefix().'fe_audit_requests')->row();
			if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
				$creator_id = $_data->creator_id;
				$obj_name = $_data->title;
			}
			$link = 'fixed_equipment/view_audit_request/'.$id;		
		}
		elseif($request_type == 'close_audit'){
			$this->db->select('creator_id, title');
			$this->db->where('id', $id);
			$_data = $this->db->get(db_prefix().'fe_audit_requests')->row();
			if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
				$creator_id = $_data->creator_id;
				$obj_name = $_data->title;
			}
			$link = 'fixed_equipment/audit/'.$id;		
		}
		elseif($request_type == 'inventory_receiving'){
			$this->db->select('creator_id, goods_receipt_code');
			$this->db->where('id', $id);
			$_data = $this->db->get(db_prefix().'fe_goods_receipt')->row();
			if($_data && is_numeric($_data->creator_id) && $_data->creator_id > 0){
				$creator_id = $_data->creator_id;
				$obj_name = $_data->goods_receipt_code;
			}
			$link = 'fixed_equipment/inventory?tab=inventory_receiving&id='.$id;		
		}
		elseif($request_type == 'inventory_delivery'){
			$this->db->select('addedfrom, goods_delivery_code');
			$this->db->where('id', $id);
			$_data = $this->db->get(db_prefix().'fe_goods_delivery')->row();
			if($_data && is_numeric($_data->addedfrom) && $_data->addedfrom > 0){
				$creator_id = $_data->addedfrom;
				$obj_name = $_data->goods_delivery_code;
			}
			$link = 'fixed_equipment/inventory?tab=inventory_delivery&id='.$id;		
		}

		if($link != ''){
			$this->load->model('emails_model');
			$full_path = admin_url($link);
			$this->db->select('notification_recipient');
			$this->db->where('rel_id',$id);
			$this->db->where('rel_type',$request_type);
			$this->db->limit(1, 0);
			$data_approve = $this->db->get(db_prefix().'fe_approval_details')->row();
			if($data_approve){
				$notification_recipient = ($data_approve->notification_recipient != '' ? explode(',', $data_approve->notification_recipient) : []);
				foreach($notification_recipient as $recipient){
					$message = ' '._l('fe_just_created').' '._l('fe_'.$request_type).''.($obj_name != '' ? ' ('.$obj_name.')' : '');
					$this->notifications($recipient, $link, $message);
					$staff_email = fe_get_staff_email($recipient);
					if($staff_email != ''){
						$this->emails_model->send_simple_email($staff_email, _l('fe_new').' '._l('fe_'.$request_type), get_staff_full_name($creator_id).' '.$message.'<br>'._l('fe_detail').': <a href="'.$full_path.'">'.$obj_name.'</a>');
					}
				}
			}
		}
	}

	/**	
	 * get child location id
	 */
	public function get_child_location_id($parent_id, $result = []){
		$data = $this->get_locations('', 'parent = '.$parent_id, 'id');
		foreach($data as $row){
			$id = $row['id'];
			if(!in_array($id, $result)){
				$result[] = $id;
				$result = $this->get_child_location_id($id, $result);
			}
		}
		return $result;
	}

	/**
	 * get model predefined kits
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function get_model_predefined_kits($id){
		$this->db->select(db_prefix().'fe_models.id as id, quantity, model_name');
		$this->db->where('parent_id', $id);
		$this->db->join(db_prefix().'fe_models', db_prefix().'fe_models.id = '.db_prefix().'fe_model_predefined_kits.model_id', 'left');
		return $this->db->get(db_prefix().'fe_model_predefined_kits')->result_array();
	}

	/**
	 * list asset checkout predefined kits
	 * @param  integer $kit_id 
	 * @return object         
	 */
	public function list_asset_checkout_predefined_kit_by_model($model_id, $quantity)
	{
		$robj = new stdClass();
		$robj->status = 2;
		$robj->msg = '';
		$robj->list_asset = [];

		$list_asset = [];

		$count_affected_model = 1;
		$count_model = 1;

		$count_model++;
		$model_name = '';
		$models = $this->get_models($model_id);
		if ($models) {
			$model_name = $models->model_name;
		}
		$this->db->where('model_id', $model_id);
		$this->db->where('active', 1);
		$this->db->where('type', 'asset');
		$this->db->where('checkin_out', 1);
		$this->db->order_by('id', 'desc');

		$this->db->select(db_prefix() . 'fe_assets.id, assets_name, status, series');

		$this->db->join(db_prefix() . 'fe_status_labels', db_prefix() . 'fe_status_labels.id = ' . db_prefix() . 'fe_assets.status', 'left');
		$this->db->where(db_prefix() . 'fe_status_labels.status_type', 'deployable');
		$list_asset_model = $this->db->get(db_prefix() . 'fe_assets')->result_array();
		if ($list_asset_model) {
			// If enough quantity or more -> get id of asset add to array
			if (count($list_asset_model) >= (float)$quantity) {
				$count_affected_model++;
				foreach ($list_asset_model as $i => $asset) {
					$list_asset[] = array('id' => $asset['id'], 'assets_name' => $asset['assets_name'], 'status' => $asset['status'], 'model' => $model_name, 'series' => $asset['series']);
				}
			} else {
				// Not enought quantity -> return error
				$robj->status = 1;
				$robj->msg = $model_name . ' ' . _l('fe_not_enough_amount_of_asset_to_checkout');
				return $robj;
			}
		} else {
			// Not enought quantity -> return error
			$robj->status = 1;
			$robj->msg = $model_name . ' ' . _l('fe_not_enough_amount_of_asset_to_checkout');
			return $robj;
		}
		$robj->list_asset = $list_asset;
		return $robj;
	}

	/**
	 * add assign asset predefined kits
	 * @param array $data 
	 * @return integer $insert id 
	 */
	public function add_assign_asset_predefined_kits($data){
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['assign_data'] = json_encode($data['assign_data']);
		$this->db->insert(db_prefix().'fe_assign_asset_predefined_kits', $data);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return 0;
	}

	/**
	 * update assign asset predefined kits
	 * @param  array $data 
	 * @return boolean     
	 */
	public function update_assign_asset_predefined_kits($data){
		$data['assign_data'] = json_encode($data['assign_data']);
		$this->db->where('id', $data['id']);
		$this->db->update(db_prefix().'fe_assign_asset_predefined_kits', $data);
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**
	 * get assign asset predefined kits
	 * @param  integer $id 
	 * @return array or object    
	 */
	public function get_assign_asset_predefined_kits($id = '', $where = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix().'fe_assign_asset_predefined_kits')->row();
		}
		else{
			if($where != ''){
				$this->db->where($where);
			}
			return $this->db->get(db_prefix().'fe_assign_asset_predefined_kits')->result_array();
		}
	}

	/**
	 * delete assign predefined kits
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function delete_assign_predefined_kits($id){
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().'fe_assign_asset_predefined_kits');
		if($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	/**	
	 * get projects
	 */
	public function get_projects($id = ''){
		if($id != ''){
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'projects')->row();
		}
		else{
			$this->db->select('*');
			$this->db->order_by('id', 'desc');
			return $this->db->get(db_prefix() . 'projects')->result_array();
		}
	}

	/**
	 * get client asset
	 * @param  int  $userid 
	 * @param  int $status 
	 * @return array          
	 */
	public function get_client_assets($userid = ''){
		return $this->db->query('SELECT a.id, assets_name, series, c.order_number, c.type, c.datecreator  FROM '.db_prefix().'fe_assets a join '.db_prefix().'fe_cart_detailt b on a.id = b.product_id join '.db_prefix().'fe_cart c on c.id = b.cart_id where c.userid = '.$userid.' and original_order_id is null and (channel_id = 2 OR channel_id = 4) and c.status = 5 order by datecreator DESC')->result_array();
	}

	/**
	 * create serial numbers
	 * @param  integer $quantity 
	 * @return [type]            
	 */
	public function create_serial_numbers($quantity = 1, $serial_modal = false, $update_next_number = true, $next_serial_number = true, $serial_number = 0) {
		$arr_serial_numbers = [];
		$quantity = (int)$quantity;
		$serial_number_format = get_option('fe_serial_number_format');
		
		if($next_serial_number){
			$next_number = (int)get_option('fe_next_serial_number');
		}else{
			$next_number = $serial_number;
		}

		$new_next_number = $next_number;

		for ($i=0; $i < $quantity ; $i++) { 

			$str_result ='';
			if($serial_number_format == 1){
				$str_result .= str_pad($next_number,7,'0',STR_PAD_LEFT);
			}elseif($serial_number_format == 2){
				$str_result .= date('Y').date('m').date('d').str_pad($next_number,6,'0',STR_PAD_LEFT);
			}else{
				$str_result .= str_pad($next_number,7,'0',STR_PAD_LEFT);
			}
			
			if($serial_modal){
				$arr_serial_numbers[] = ['serial_number' => $str_result];
			}else{
				$arr_serial_numbers[] = $str_result;
			}
			$next_number++;
			$new_next_number++;
		}
		if($update_next_number){
			/*update next number setting*/
			update_option('fe_next_serial_number', $new_next_number);
		}

		return $arr_serial_numbers;
	}

	/**
	 * list asset by model
	 * @param  [type] $model 
	 * @return [type]        
	 */
	public function list_asset_by_model($model)
	{
		$this->db->where('model_id', $model);
		$assets = $this->db->get(db_prefix().'fe_assets')->result_array();

		$options = '';
		if(count($assets) > 0){
			foreach ($assets as $value) {
				$options .= '<option value="' . $value['id'] . '">' . $value['series'].' '.$value['assets_name'] . '</option>';
			}
		}
		return $options;
	}

	/**
	 * get model grouped
	 * @return [type] 
	 */
	public function get_model_grouped()
    {
        $items = [];
        $this->db->order_by('type', 'asc');
        $this->db->order_by('category_name', 'asc');
        $groups = $this->db->get(db_prefix() . 'fe_categories')->result_array();

        array_unshift($groups, [
            'id'   => 0,
            'category_name' => '',
        ]);

        foreach ($groups as $key => $group) {
        	if(isset($group['type']) && $group['type'] == 'asset') {
        		$this->db->select('*,' . db_prefix() . 'fe_categories.category_name as category_name, CONCAT("model-", ' . db_prefix() . 'fe_models.id) as id,' . db_prefix() . 'fe_models.model_no as item_no, CONCAT(model_name, "(", IFNULL(( SELECT count('.db_prefix().'fe_assets.id)  from '.db_prefix().'fe_assets left join '.db_prefix().'fe_status_labels on '.db_prefix().'fe_status_labels.id = '.db_prefix().'fe_assets.status where '.db_prefix().'fe_models.id = '.db_prefix().'fe_assets.model_id AND active = 1 AND '.db_prefix().'fe_status_labels.status_type = "deployable" group by model_id), 0),")") as item_name');
        		$this->db->where('category', $group['id']);
        		$this->db->join(db_prefix() . 'fe_categories', '' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_models.category', 'left');
        		$this->db->order_by('item_name', 'asc');
        		$_items = $this->db->get(db_prefix() . 'fe_models')->result_array();
        		if (count($_items) > 0) {
        			$items[$group['id']] = [];
        			foreach ($_items as $i) {
        				array_push($items[$group['id']], $i);
        			}
        		}
        		unset($groups[$key]);
        	}
        }

        // accessories
        // comsumable
        // component
        foreach ($groups as $key => $group) {
        	if(isset($group['type']) && $group['type'] != 'license') {
        		$this->db->select('*,' . db_prefix() . 'fe_categories.category_name as category_name,' . db_prefix() . 'fe_assets.id as id,' . db_prefix() . 'fe_assets.assets_code as item_no, CONCAT(assets_name, "(", IFNULL('.db_prefix().'fe_assets.quantity, 0),")") as item_name');

        		$this->db->where('category_id', $group['id']);
        		$this->db->join(db_prefix() . 'fe_categories', '' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_assets.category_id', 'left');
        		$this->db->order_by('item_name', 'asc');
        		$_items = $this->db->get(db_prefix() . 'fe_assets')->result_array();
        		if (count($_items) > 0) {
        			$items[$group['id']] = [];
        			foreach ($_items as $i) {
        				array_push($items[$group['id']], $i);
        			}
        		}
        		unset($groups[$key]);

        	}
        }
       
        // license
        foreach ($groups as $key => $group) {
        	if(isset($group['type']) && $group['type'] == 'license') {
        		$this->db->select('*,' . db_prefix() . 'fe_categories.category_name as category_name,' . db_prefix() . 'fe_assets.id as id,' . db_prefix() . 'fe_assets.assets_code as item_no, CONCAT(assets_name, "(", IFNULL(( SELECT count('.db_prefix().'fe_seats.id)  from '.db_prefix().'fe_seats  where '.db_prefix().'fe_seats.license_id = '.db_prefix().'fe_assets.id AND to_id = 0 ), 0),")") as item_name');

        		$this->db->where('category_id', $group['id']);
        		$this->db->join(db_prefix() . 'fe_categories', '' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_assets.category_id', 'left');
        		$this->db->order_by('item_name', 'asc');
        		$_items = $this->db->get(db_prefix() . 'fe_assets')->result_array();
        		if (count($_items) > 0) {
        			$items[$group['id']] = [];
        			foreach ($_items as $i) {
        				array_push($items[$group['id']], $i);
        			}
        		}
        		unset($groups[$key]);
        		
        	}
        }

        return $items;
    }

    public function add_manual_order($data, $id = false) {
		$newitems = [];
		if (isset($data['newitems'])) {
			$newitems = $data['newitems'];
			unset($data['newitems']);
		}

		if(isset($data['item_select'])){
			unset($data['item_select']);
		}
		if(isset($data['description'])){
			unset($data['description']);
		}
		if(isset($data['available_quantity'])){
			unset($data['available_quantity']);
		}
		if(isset($data['qty'])){
			unset($data['qty']);
		}
		if(isset($data['rate'])){
			unset($data['rate']);
		}
		if(isset($data['product_id'])){
			unset($data['product_id']);
		}
		if(isset($data['sku'])){
			unset($data['sku']);
		}
		if(isset($data['include_shipping'])){
			unset($data['include_shipping']);
		}
		if(isset($data['show_shipping_on_estimate'])){
			unset($data['show_shipping_on_estimate']);
		}
		

		$data_client = $this->clients_model->get($data['userid']);
		$date = date('Y-m-d');
		$user_id = $data['userid'];
		$order_number = $this->incrementalHash();
		$channel_id = 2;
		$data_cart['userid'] = $data['userid'];
		$data_cart['order_number'] = $data['order_number'];
		$data_cart['channel_id'] = 4;
		$data_cart['channel'] = 'manual';
		$data_cart['company'] =  get_company_name($data['userid']);
		$data_cart['phonenumber'] =  $data_client->phonenumber;
		$data_cart['city'] =  $data_client->city;
		$data_cart['state'] =  $data_client->state;
		$data_cart['country'] =  $data_client->country;
		$data_cart['zip'] =  $data_client->zip;
		$data_cart['billing_street'] =  $data_client->billing_street;
		$data_cart['billing_city'] =  $data_client->billing_city;
		$data_cart['billing_state'] =  $data_client->billing_state;
		$data_cart['billing_country'] =  $data_client->billing_country;
		$data_cart['billing_zip'] =  $data_client->billing_zip;
		$data_cart['shipping_street'] =  $data_client->shipping_street;
		$data_cart['shipping_city'] =  $data_client->shipping_city;
		$data_cart['shipping_state'] =  $data_client->shipping_state;
		$data_cart['shipping_country'] =  $data_client->shipping_country;
		$data_cart['shipping_zip'] =  $data_client->shipping_zip;
		$data_cart['total'] =  $data['total'];
		$data_cart['sub_total'] =  $data['sub_total'];
		$data_cart['discount'] =  0;
		$data_cart['discount_total'] =  0;
		$data_cart['discount_voucher'] =  0;
		$data_cart['discount_type'] =  2;
		$data_cart['notes'] =  $data['notes'];
		$data_cart['tax'] =  0;
		$data_cart['allowed_payment_modes'] =  $data['allowed_payment_modes'];
		$data_cart['shipping'] =  0;
		$data_cart['hash'] = app_generate_hash();
		$data_cart['shipping_form'] =  'fixed';
		$data_cart['shipping_value'] =  0;
		$data_cart['type'] =  'order';
		$data_cart['seller'] =  $data['seller'];

		$this->db->insert(db_prefix() . 'fe_cart', $data_cart);
		$insert_id = $this->db->insert_id();
		/*update save note*/
		if (isset($insert_id)) {
			foreach ($newitems as $cart_detailt) {

				$cart_detail_data = [];
				$cart_detail_data['cart_id'] = $insert_id;
				$cart_detail_data['product_id'] = $cart_detailt['product_id'];
				$cart_detail_data['quantity'] = $cart_detailt['qty'];
				$cart_detail_data['classify'] = '';
				$cart_detail_data['product_name'] = $cart_detailt['description'];
				$cart_detail_data['prices'] = (float)$cart_detailt['rate'];
				$cart_detail_data['long_description'] = '';
				$cart_detail_data['sku'] = '';
				$cart_detail_data['percent_discount'] = 0;
				$cart_detail_data['prices_discount'] = 0;
				$cart_detail_data['tax'] = '[]';
				$cart_detail_data['available_quantity'] = $cart_detailt['available_quantity'];
				$this->db->insert(db_prefix() . 'fe_cart_detailt', $cart_detail_data);
			}
			return $insert_id;
		}
		return false;
	}

	public function update_manual_order($data, $id = false) {
    	$results=0;

		$newitems = [];
		if (isset($data['newitems'])) {
			$newitems = $data['newitems'];
			unset($data['newitems']);
		}

		if (isset($data['items'])) {
			$updateitems = $data['items'];
			unset($data['items']);
		}
		if (isset($data['removed_items'])) {
			$removeditems = $data['removed_items'];
			unset($data['removed_items']);
		}

		if(isset($data['item_select'])){
			unset($data['item_select']);
		}
		if(isset($data['description'])){
			unset($data['description']);
		}
		if(isset($data['available_quantity'])){
			unset($data['available_quantity']);
		}
		if(isset($data['qty'])){
			unset($data['qty']);
		}
		if(isset($data['rate'])){
			unset($data['rate']);
		}
		if(isset($data['product_id'])){
			unset($data['product_id']);
		}
		if(isset($data['sku'])){
			unset($data['sku']);
		}
		if(isset($data['include_shipping'])){
			unset($data['include_shipping']);
		}
		if(isset($data['show_shipping_on_estimate'])){
			unset($data['show_shipping_on_estimate']);
		}
		

		$data_client = $this->clients_model->get($data['userid']);
		$date = date('Y-m-d');

		$channel_id = 2;
		$data_cart['userid'] = $data['userid'];
		$data_cart['order_number'] = $data['order_number'];
		$data_cart['company'] =  get_company_name($data['userid']);
		$data_cart['phonenumber'] =  $data_client->phonenumber;
		$data_cart['city'] =  $data_client->city;
		$data_cart['state'] =  $data_client->state;
		$data_cart['country'] =  $data_client->country;
		$data_cart['zip'] =  $data_client->zip;
		$data_cart['billing_street'] =  $data_client->billing_street;
		$data_cart['billing_city'] =  $data_client->billing_city;
		$data_cart['billing_state'] =  $data_client->billing_state;
		$data_cart['billing_country'] =  $data_client->billing_country;
		$data_cart['billing_zip'] =  $data_client->billing_zip;
		$data_cart['shipping_street'] =  $data_client->shipping_street;
		$data_cart['shipping_city'] =  $data_client->shipping_city;
		$data_cart['shipping_state'] =  $data_client->shipping_state;
		$data_cart['shipping_country'] =  $data_client->shipping_country;
		$data_cart['shipping_zip'] =  $data_client->shipping_zip;
		$data_cart['total'] =  $data['total'];
		$data_cart['sub_total'] =  $data['sub_total'];
		$data_cart['discount'] =  0;
		$data_cart['discount_total'] =  0;
		$data_cart['discount_voucher'] =  0;
		$data_cart['discount_type'] =  2;
		$data_cart['notes'] =  $data['notes'];
		$data_cart['tax'] =  0;
		$data_cart['allowed_payment_modes'] =  $data['allowed_payment_modes'];
		$data_cart['shipping'] =  0;
		$data_cart['shipping_form'] =  'fixed';
		$data_cart['shipping_value'] =  0;
		$data_cart['seller'] =  $data['seller'];

		$this->db->where('id', $id);
    	$this->db->update(db_prefix() . 'fe_cart', $data_cart);
    	if ($this->db->affected_rows() > 0) {
			$results++;
		}

		// update
		if(isset($updateitems)){

			foreach ($updateitems as $updateitem) {
				$cart_detail_data = [];
				$cart_detail_data['product_id'] = $updateitem['product_id'];
				$cart_detail_data['quantity'] = $updateitem['qty'];
				$cart_detail_data['classify'] = '';
				$cart_detail_data['product_name'] = $updateitem['description'];
				$cart_detail_data['prices'] = (float)$updateitem['rate'];
				$cart_detail_data['long_description'] = '';
				$cart_detail_data['sku'] = '';
				$cart_detail_data['percent_discount'] = 0;
				$cart_detail_data['prices_discount'] = 0;
				$cart_detail_data['tax'] = '[]';
				$cart_detail_data['available_quantity'] = $updateitem['available_quantity'];

				$this->db->where('id', $updateitem['id']);
				if ($this->db->update(db_prefix() . 'fe_cart_detailt', $cart_detail_data)) {
					$results++;
				}
			}

		}
		
		// delete
		if(isset($removeditems)){
			foreach ($removeditems as $removeditem) {
				$this->db->where('id', $removeditem);
				if ($this->db->delete(db_prefix() . 'fe_cart_detailt')) {
					$results++;
				}
			}
		}
		// add
		if(isset($newitems)){
			foreach ($newitems as $cart_detailt) {
				$cart_detail_data = [];
				$cart_detail_data['cart_id'] = $id;
				$cart_detail_data['product_id'] = $cart_detailt['product_id'];
				$cart_detail_data['quantity'] = $cart_detailt['qty'];
				$cart_detail_data['classify'] = '';
				$cart_detail_data['product_name'] = $cart_detailt['description'];
				$cart_detail_data['prices'] = (float)$cart_detailt['rate'];
				$cart_detail_data['long_description'] = '';
				$cart_detail_data['sku'] = '';
				$cart_detail_data['percent_discount'] = 0;
				$cart_detail_data['prices_discount'] = 0;
				$cart_detail_data['tax'] = '[]';
				$cart_detail_data['available_quantity'] = $cart_detailt['available_quantity'];
				$cart_detailt = $this->db->insert(db_prefix() . 'fe_cart_detailt', $cart_detail_data);
				if($cart_detailt){
					$result++;
				}
			}
		}

		if($results > 0){
			return true;
		}
		return false;
	}

	public function get_client_assets_2($userid = ''){
		$array_asset = [];
		$assets = $this->db->query('SELECT a.id, a.model_id, tblfe_models.model_name, c.id as cart_id, c.order_number, c.type, c.datecreator, assets_name,b.quantity, a.type as asset_type, series FROM tblfe_assets a 
			LEFT JOIN tblfe_cart_detailt b on a.id = b.product_id 
			LEFT JOIN tblfe_cart c on c.id = b.cart_id
			LEFT JOIN tblfe_models on a.model_id = tblfe_models.id
			where c.userid = '.$userid.' and original_order_id is null and (channel_id = 2 OR channel_id = 4) and c.status = 5 order by datecreator DESC')->result_array();
		foreach ($assets as $key => $value) {
			if(is_null($value['model_id'])){

				if(isset($array_asset[$value['id'].'_'.$value['cart_id']])){
					$array_asset[$value['id'].'_'.$value['cart_id']]['assets'][] = $value;
					$array_asset[$value['id'].'_'.$value['cart_id']]['quantity'] += (float)$value['quantity'];
				}else{
					$array_asset[$value['id'].'_'.$value['cart_id']]['assets_name'] = $value['assets_name'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['quantity'] = $value['quantity'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['order_number'] = $value['order_number'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['datecreator'] = $value['datecreator'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['type'] = $value['type'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['cart_id'] = $value['cart_id'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['model_id'] = $value['model_id'];
					$array_asset[$value['id'].'_'.$value['cart_id']]['assets'] = $value;
				}
			}else{
				if(isset($array_asset['model_'.$value['model_id'].'_'.$value['cart_id']])){
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['assets'][] = $value;
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['quantity'] += (float)$value['quantity'];

				}else{
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['assets_name'] = $value['model_name'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['quantity'] = $value['quantity'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['order_number'] = $value['order_number'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['datecreator'] = $value['datecreator'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['type'] = $value['type'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['cart_id'] = $value['cart_id'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['model_id'] = $value['model_id'];
					$array_asset['model_'.$value['model_id'].'_'.$value['cart_id']]['assets'] = $value;

				}
			}

			// code...
		}
		return $array_asset;
	}

	/**
	 * create issue numbers
	 * @return [type] 
	 */
	public function create_issue_numbers() {
		$issue_prefix = get_option('fe_issue_prefix');
		$issue_number_format = get_option('fe_issue_number_format');
		$next_number = (int)get_option('fe_next_issue_number');

		$str_result ='';
		if($issue_number_format == 1){
			$str_result .= $issue_prefix.str_pad($next_number,7,'0',STR_PAD_LEFT);
		}elseif($issue_number_format == 2){
			$str_result .= $issue_prefix.date('Y').date('m').date('d').str_pad($next_number,6,'0',STR_PAD_LEFT);
		}else{
			$str_result .= $issue_prefix.str_pad($next_number,7,'0',STR_PAD_LEFT);
		}

		return $str_result;
	}

	/**
	 * get issue
	 * @param  boolean $id    
	 * @param  string  $where 
	 * @return [type]         
	 */
	public function get_issue($id=false, $where = '')
	{
		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'fe_tickets')->row();
		}

		if ($id == false) {
			if(new_strlen($where) > 0){
				$this->db->where($where);
			}
			$this->db->order_by('datecreated', 'DESC');
			return $this->db->get(db_prefix() . 'fe_tickets')->result_array();
		}
	}

	/**
	 * add_issue
	 * @param [type] $data 
	 */
	public function add_issue($data)
	{
		$order_number = '';
		$cart = $this->get_cart($data['cart_id']);
		if($cart){
			$order_number = $cart->order_number;
		}
		$get_assets = $this->get_assets($data['asset_id']);
		if($get_assets){
			$data['model_id'] = $get_assets->model_id;
		}else{
			$data['model_id'] = null;
		}

		if(!isset($data['status'])){
			$data['status'] = 'open';
		}

		$data['datecreated'] = to_sql_date($data['datecreated'], true);
		$data['staffid'] = 0;

		if($data['created_type'] == 'staff'){
			$data['staffid'] = get_staff_user_id();
		}

		$data['code'] = $this->create_issue_numbers();

		$this->db->insert(db_prefix().'fe_tickets',$data);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			update_option('fe_next_issue_number', get_option('fe_next_issue_number')+1);
			//send mail to staff or client
			if($data['created_type'] == 'staff'){
				$subject = 'New Issue Opened';
				$fromname = get_option('companyname');
				$email = fe_get_contact_email('client', $data['client_id']);

				$merge_fields = [];
				$get_primary_contact_user_id =  get_contact_full_name(get_primary_contact_user_id($data['client_id']));
				$merge_fields['{contact_firstname}'] = $get_primary_contact_user_id;
				$merge_fields['{contact_lastname}'] = '';
				$merge_fields['{Order}'] = $order_number;
				$merge_fields['{asset_name}'] = $this->get_asset_name($data['asset_id']);
				$merge_fields['{ticket_subject}'] = $data['ticket_subject'];
				$merge_fields['{ticket_message}'] = $data['issue_summary'];
				$merge_fields['{Issue_id}'] = $data['code'];
				$merge_fields['{email_signature}'] = get_option('email_signature');
				$merge_fields['{ticket_public_url}'] = site_url('fixed_equipment/fixed_equipment_client/issue_detail/'.$insert_id);
				$issue_send_to_customer_template = $this->issue_send_to_customer_template();

				foreach ($merge_fields as $key => $val) {
					if (stripos($issue_send_to_customer_template, $key) !== false) {
						$issue_send_to_customer_template = str_ireplace($key, $val, $issue_send_to_customer_template);
					} else {
						$issue_send_to_customer_template = str_ireplace($key, '', $issue_send_to_customer_template);
					}
				}

				if(new_strlen($email) > 0){
					$parse_content['fromname'] = $fromname;				
					$parse_content['subject'] = $subject;				
					$parse_content['content'] = $issue_send_to_customer_template;	
								
					$fe_send_email = $this->fe_send_email($email, $parse_content, $insert_id, 'staff_new_issue');
				}

				if(get_staff_user_id() != $data['assigned_id']){
					$subject = 'New Issue Created';
					$fromname = get_option('companyname');
					$email = fe_get_staff_email($data['assigned_id']);

					$merge_fields = [];
					$merge_fields['Order'] = $order_number;
					$merge_fields['asset_name'] = $this->get_asset_name($data['asset_id']);
					$merge_fields['ticket_subject'] = $data['ticket_subject'];
					$merge_fields['ticket_message'] = $data['issue_summary'];
					$merge_fields['email_signature'] = get_option('email_signature');
					$merge_fields['{ticket_public_url}'] = admin_url('fixed_equipment/issue_detail/'.$insert_id);
					$merge_fields['{Issue_id}'] = $data['code'];

					$issue_send_to_staff_template = $this->issue_send_to_staff_template();

					foreach ($merge_fields as $key => $val) {
						if (stripos($issue_send_to_staff_template, $key) !== false) {
							$issue_send_to_staff_template = str_ireplace($key, $val, $issue_send_to_staff_template);
						} else {
							$issue_send_to_staff_template = str_ireplace($key, '', $issue_send_to_staff_template);
						}
					}

					if(new_strlen($email) > 0){
						$parse_content['fromname'] = $fromname;				
						$parse_content['subject'] = $subject;				
						$parse_content['content'] = $issue_send_to_staff_template;				
						$fe_send_email = $this->fe_send_email($email, $parse_content, $insert_id, 'customer_open_issue');
					}
				}

			}else{
				$subject = 'New Issue Created';
				$fromname = get_option('companyname');
				$email = fe_get_staff_email($data['assigned_id']);

				$merge_fields = [];
				$merge_fields['{Order}'] = $order_number;
				$merge_fields['{asset_name}'] = $this->get_asset_name($data['asset_id']);
				$merge_fields['{ticket_subject}'] = $data['ticket_subject'];
				$merge_fields['{ticket_message}'] = $data['issue_summary'];
				$merge_fields['{email_signature}'] = get_option('email_signature');
				$merge_fields['{ticket_public_url}'] = admin_url('fixed_equipment/issue_detail/'.$insert_id);
				$merge_fields['{Issue_id}'] = $data['code'];

				$issue_send_to_staff_template = $this->issue_send_to_staff_template();

				foreach ($merge_fields as $key => $val) {
					if (stripos($issue_send_to_staff_template, $key) !== false) {
						$issue_send_to_staff_template = str_ireplace($key, $val, $issue_send_to_staff_template);
					} else {
						$issue_send_to_staff_template = str_ireplace($key, '', $issue_send_to_staff_template);
					}
				}

				if(new_strlen($email) > 0){
					$parse_content['fromname'] = $fromname;				
					$parse_content['subject'] = $subject;				
					$parse_content['content'] = $issue_send_to_staff_template;				
					$fe_send_email = $this->fe_send_email($email, $parse_content, $insert_id, 'customer_open_issue');
				}
			}
			return $insert_id;
		}
		return false;
	}

	/**
	 * update issue
	 * @param  [type] $data 
	 * @param  [type] $id   
	 * @return [type]       
	 */
	public function update_issue($data, $id)
	{
		$affected_rows=0;
		$get_assets = $this->get_assets($data['asset_id']);
		if($get_assets){
			$data['model_id'] = $get_assets->model_id;
		}else{
			$data['model_id'] = null;
		}
		$data['dateupdated'] =  date('Y-m-d H:i:s');
		$data['last_update_time'] =  date('Y-m-d H:i:s');
		$data['datecreated'] =  to_sql_date($data['datecreated'], true);

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'fe_tickets', $data);
		if ($this->db->affected_rows() > 0) {
			$affected_rows++;
		}

		if($affected_rows > 0){
			return true;
		}
		return false;   
	}

	/**
	 * delete ticket
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_ticket($id)
	{	
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'fe_tickets');
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	public function issue_send_to_customer_template()
	{
	// 		Hi {contact_firstname} {contact_lastname}

	// 		New Issue has been opened related to Order {Order}: {asset_name}

	// 		Subject: {ticket_subject}

	// 		Ticket message:
	// 		{ticket_message}

	// You can view the Issue on the following link: #{Issue_id}

	// Kind Regards,
	// {email_signature}
		// subject: New Issue Opened
		
		$template_html = '<p><span style="font-size: 12pt;">Hi {contact_firstname} {contact_lastname}</span><br /><br /><span style="font-size: 12pt;">New Issue has been opened related to Order {Order}: {asset_name}</span><br /><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {ticket_subject}</span><span style="font-size: 12pt;"><br /></span><br /><span style="font-size: 12pt;"><strong>Issue message:</strong></span><br /><span style="font-size: 12pt;">{ticket_message}</span><br /><br /><span style="font-size: 12pt;">You can view the Issue on the following link: <a href="{ticket_public_url}">#{Issue_id}</a><br /><br />Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span></p>';
		return $template_html;
	}

	/**
	 * issue send to staff template
	 * @return [type] 
	 */
	public function issue_send_to_staff_template()
	{

	// 		New Issue has been opened related to Order {Order}: {asset_name}

	// 		Subject: {ticket_subject}

	// 		Ticket message:
	// 		{ticket_message}

	// You can view the Issue on the following link: #{Issue_id}

	// Kind Regards,
	// {email_signature}
		// subject: New Issue Created
		$template_html = '<p><span style="font-size: 12pt;">New Issue has been opened related to Order {Order}: {asset_name}</span><br /><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {ticket_subject}</span><span style="font-size: 12pt;"><br /></span><br /><span style="font-size: 12pt;"><strong>Issue message:</strong></span><br /><span style="font-size: 12pt;">{ticket_message}</span><br /><br /><span style="font-size: 12pt;">You can view the Issue on the following link: <a href="{ticket_public_url}">#{Issue_id}</a><br /><br />Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span></p>';
		return $template_html;
	}

	/**
	 * issue change status template
	 * @return [type] 
	 */
	public function issue_change_status_template()
	{

	// 	Issue {ticket_subject} has been changed status to {status}.

	// Issue#: {issue_id}

	// Kind Regards,
	// {email_signature}
		// Subject: Issue Change Status
		$template_html = '<p><span style="font-size: 12pt;">Issue {ticket_subject} has been changed status to {status}.</span><br /><br /><span style="font-size: 12pt;"><strong>Issue#</strong>: <a href="{issue_public_url}">{Issue_id}</a></span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span></p>';
		return $template_html;
	}

	/**
	 * fe send email
	 * @param  [type] $email         
	 * @param  [type] $parse_content 
	 * @param  [type] $issue_id      
	 * @param  string $type          
	 * @return [type]                
	 */
	public function fe_send_email($email, $parse_content, $issue_id, $type='', $send_by = '')
	{
		$inbox['body'] = _strip_tags($parse_content['content']);
		$inbox['body'] = nl2br_save_html($inbox['body']);
		
		$this->load->model('emails_model');
		$result = $this->fe_send_simple_email($email, $parse_content['subject'], $inbox['body'], $parse_content['fromname']);

		if ($result) {

			if($type == 'staff_new_issue'){
				//write log
				$description = '';
				$description .= '<strong>'._l('fe_new_issue_opened').'</strong><br>';
				$description .= '<strong>'._l('fe_subject').'</strong>: '.$parse_content['subject'].'<br>';
				$description .= '<strong>'._l('fe_to').'</strong>: '.$email.'<br>';
				$description .= $inbox['body'];

				$this->fe_ticket_log($issue_id, 'fe_issue', $description, '', null, null, 0, 'System');

			}elseif($type == 'customer_open_issue'){
				$description = '';
				$description .= '<strong>'._l('fe_new_issue_created').'</strong><br>';
				$description .= '<strong>'._l('fe_subject').'</strong>: '.$parse_content['subject'].'<br>';
				$description .= '<strong>'._l('fe_to').'</strong>: '.$email.'<br>';
				$description .= $inbox['body'];

				$this->fe_ticket_log($issue_id, 'fe_issue', $description, '', null, null, 0, 'System');

			}elseif($type == 'change_status'){
				$description = '';
				if($send_by == 'staff'){
					$description .= '<strong>'._l('fe_send_an_email_to_client').'</strong><br>';
				}else{
					$description .= '<strong>'._l('fe_send_an_email_to_staff').'</strong><br>';
				}
				$description .= '<strong>'._l('fe_subject').'</strong>: '.$parse_content['subject'].'<br>';
				$description .= '<strong>'._l('fe_to').'</strong>: '.$email.'<br>';
				$description .= $inbox['body'];

				$this->fe_ticket_log($issue_id, 'fe_issue', $description, '', null, null, 0, 'System');
			}

			return true;
		}
		return false;
	}

	public function fe_send_simple_email($email, $subject, $message, $fromname = '')
	{
		$cnf = [
			'from_email' => get_option('smtp_email'),
			'from_name'  => $fromname != '' ? $fromname : get_option('companyname'),
			'email'      => $email,
			'subject'    => $subject,
			'message'    => $message,
		];

        // Simulate fake template to be parsed
		$template           = new StdClass();
		$template->message  = get_option('email_header') . $cnf['message'] . get_option('email_footer');
		$template->fromname = $cnf['from_name'];
		$template->subject  = $cnf['subject'];

		$template = parse_email_template($template);

		$cnf['message']   = $template->message;
		$cnf['from_name'] = $template->fromname;
		$cnf['subject']   = $template->subject;

		$cnf['message'] = check_for_links($cnf['message']);

		$cnf = hooks()->apply_filters('before_send_simple_email', $cnf);

		if (isset($cnf['prevent_sending']) && $cnf['prevent_sending'] == true) {
			$this->clear_attachments();

			return false;
		}
		$this->load->config('email');
		$this->email->clear(true);
		$this->email->set_newline(config_item('newline'));
		$this->email->from($cnf['from_email'], $cnf['from_name']);
		$this->email->to($cnf['email']);

		$bcc = '';
        // Used for action hooks
		if (isset($cnf['bcc'])) {
			$bcc = $cnf['bcc'];
			if (is_array($bcc)) {
				$bcc = implode(', ', $bcc);
			}
		}

		$systemBCC = get_option('bcc_emails');
		if ($systemBCC != '') {
			if ($bcc != '') {
				$bcc .= ', ' . $systemBCC;
			} else {
				$bcc .= $systemBCC;
			}
		}
		if ($bcc != '') {
			$this->email->bcc($bcc);
		}

		if (isset($cnf['cc'])) {
			$this->email->cc($cnf['cc']);
		}

		if (isset($cnf['reply_to'])) {
			$this->email->reply_to($cnf['reply_to']);
		}

		$this->email->subject($cnf['subject']);
		$this->email->message($cnf['message']);

		$this->email->set_alt_message(strip_html_tags($cnf['message'], '<br/>, <br>, <br />'));

		if (isset($this->attachment) && count($this->attachment) > 0) {
			foreach ($this->attachment as $attach) {
				if (!isset($attach['read'])) {
					$this->email->attach($attach['attachment'], 'attachment', $attach['filename'], $attach['type']);
				} else {
					if (!isset($attach['filename']) || (isset($attach['filename']) && empty($attach['filename']))) {
						$attach['filename'] = basename($attach['attachment']);
					}
					$this->email->attach($attach['attachment'], '', $attach['filename']);
				}
			}
		}

		$this->clear_attachments();
		if ($this->email->send()) {
			log_activity('Email sent to: ' . $cnf['email'] . ' Subject: ' . $cnf['subject']);

			return true;
		}

		return false;
	}

	/**
	 * fe ticket log
	 * @param  [type]  $id           
	 * @param  [type]  $rel_type     
	 * @param  [type]  $description  
	 * @param  string  $date         
	 * @param  [type]  $from_date    
	 * @param  [type]  $to_date      
	 * @param  integer $duration     
	 * @param  string  $created_type 
	 * @return [type]                
	 */
	public function fe_ticket_log($id, $rel_type, $description, $date = '', $from_date = null, $to_date = null, $duration = 0, $created_type = '')
	{
		if(new_strlen($date) == 0){
			$date = date('Y-m-d H:i:s');
		}

		if($created_type == 'System'){
			$staffid = 0;
			$full_name = 'System';
			$created_type = 'Auto created by system';
		}else{

			if (is_staff_logged_in()) {
				$staffid = get_staff_user_id();
				$full_name = get_staff_full_name(get_staff_user_id());
				$created_type = 'staff';
			}elseif(is_client_logged_in()){
				$staffid = get_client_user_id();
				$full_name = get_company_name($staffid);
				$created_type = 'client';
			}else{
				$staffid = 0;
				$full_name = 'System';
				$created_type = 'Auto created by system';
			}
		}

		$log = [
			'date'            => $date,
			'description'     => $description,
			'rel_id'          => $id,
			'rel_type'          => $rel_type,
			'staffid'         => $staffid,
			'full_name'       => $full_name,
			'from_date'	=> $from_date,
			'to_date'	=> $to_date,
			'duration'	=> $duration,
			'created_type'	=> $created_type,
		];

		$this->db->insert(db_prefix() . 'fe_ticket_timeline_logs', $log);
		$insert_id = $this->db->insert_id();
		if($insert_id){
			return $insert_id;
		}
		return false;
	}

	/**
	 * clear attachments
	 * @return [type] 
	 */
	private function clear_attachments()
	{
		$this->attachment = [];
	}

	/**
	 * fe_get_attachments_file
	 * @param  [type] $rel_id   
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function fe_get_attachments_file($rel_id, $rel_type)
	{
		$this->db->order_by('dateadded', 'desc');
		$this->db->where('rel_id', $rel_id);
		$this->db->where('rel_type', $rel_type);

		return $this->db->get(db_prefix() . 'files')->result_array();
	}

	/**
	 * delete_issue_pdf_file
	 * @param  [type] $attachment_id 
	 * @param  [type] $folder_name   
	 * @return [type]                
	 */
	public function delete_issue_pdf_file($attachment_id, $folder_name)
	{
		$deleted    = false;
		$attachment = $this->get_issue_delete($attachment_id);
		if ($attachment) {
			if (empty($attachment->external)) {
				unlink($folder_name .$attachment->rel_id.'/'.$attachment->file_name);
			}
			$this->db->where('id', $attachment->id);
			$this->db->delete(db_prefix() . 'files');
			if ($this->db->affected_rows() > 0) {
				$deleted = true;
				log_activity('Movement Attachment Deleted [ID: ' . $attachment->rel_id . ']');
			}

			if (is_dir($folder_name .$attachment->rel_id)) {
				// Check if no attachments left, so we can delete the folder also
				$other_attachments = list_files($folder_name .$attachment->rel_id);
				if (count($other_attachments) == 0) {
					// okey only index.html so we can delete the folder also
					delete_dir($folder_name .$attachment->rel_id);
				}
			}
		}

		return $deleted;
	}

	/**
	 * get_issue_delete
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function get_issue_delete($id) {

		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'files')->row();
		}
	}

	public function get_issue_history($ticket_id, $client = false)
	{
		$histories = [];

		$this->db->where('ticket_id', $ticket_id);
		$action_post_replies = $this->db->get(db_prefix() . 'fe_ticket_action_post_internal_notes')->result_array();
		foreach ($action_post_replies as $value) {
			$value['strdate'] = strtotime($value['datecreated']);
			$histories[strtotime($value['datecreated'])] = $value;
		}

		$this->db->where('rel_id', $ticket_id);
		$this->db->where('rel_type', 'fe_issue');
		$ticket_timeline_logs = $this->db->get(db_prefix() . 'fe_ticket_timeline_logs')->result_array();

		$second = 1;
		foreach ($ticket_timeline_logs as $value) {
			if(isset($histories[strtotime($value['date'])])){
				$value['strdate'] = strtotime($value['date']);

				$histories[strtotime($value['date']."+ ".$second." seconds")] = $value;
				$second ++;
			}else{

				$value['strdate'] = strtotime($value['date']);

				$histories[strtotime($value['date'])] = $value;
			}
		}

		usort($histories, function ($item1, $item2) {
			return $item2['strdate'] <=> $item1['strdate'];
		});

		return $histories;
	}

	public function get_issue_post_internal_history($ticket_id, $client = false)
	{
		$histories = [];

		$this->db->where('ticket_id', $ticket_id);
		$action_post_replies = $this->db->get(db_prefix() . 'fe_ticket_action_post_internal_notes')->result_array();
		foreach ($action_post_replies as $value) {
			$value['strdate'] = strtotime($value['datecreated']);
			$histories[strtotime($value['datecreated'])] = $value;
		}

		usort($histories, function ($item1, $item2) {
			return $item2['strdate'] <=> $item1['strdate'];
		});

		return $histories;
	}

	public function find_similar_content_issue($ticket_id='')
	{
		/*find similar realted: issue_summary, internal_note, resolution*/
		$ticket_related = [];
		$precision_default = 30;
		$ticket_ids = [];

		$main_issue = $this->get_issue($ticket_id);
		if(!$main_issue){
			return [];
		}
		$get_assets = $this->get_assets($main_issue->asset_id);
		if($get_assets && is_numeric($get_assets->model_id) && $get_assets->model_id != 0){
			$this->db->where('model_id', $get_assets->model_id);
		}else{
			$this->db->where('asset_id', $get_assets->id);
		}

		$this->db->where('id !=', $ticket_id);
		$this->db->where('cart_id', $main_issue->cart_id);
		$this->db->where('status', 'closed');
		$this->db->order_by('datecreated', 'DESC');
		$ticket_related = $this->db->get(db_prefix().'fe_tickets')->result_array();

		return $ticket_related;
	}

	public function add_issue_internal_reply($data)
	{
		$data_reply = [];

		$data_reply['ticket_id'] = $data['ticket_id'];
		$data_reply['note_title'] = $data['note_title'];
		$data_reply['note_details'] = $data['note_details'];
		$data_reply['ticket_status'] = $data['fe_ticket_status'];
		$data_reply['resolution'] = isset($data['response']) ? $data['response'] : '';
		if(!isset($data['staffid'])){
			$data_reply['staffid'] = get_staff_user_id();
		}else{
			$data_reply['staffid'] = $data['staffid'];
		}

		if(!isset($data['created_type'])){
			$data_reply['created_type'] = 'staff';
		}else{
			$data_reply['created_type'] = $data['created_type'];
		}

		if(isset($data['fe_ticket_status']) && new_strlen($data['fe_ticket_status']) > 0){
			$original_issue = $this->get_issue($data['ticket_id']);
			$send_mail = false;
			if($original_issue->status != $data['fe_ticket_status']){
				$send_mail = true;
			}

			$data_reply['ticket_status'] = $data['fe_ticket_status'];

			/*update ticket status*/
			$this->db->where('id', $data['ticket_id']);
			$this->db->update(db_prefix().'fe_tickets', ['status' => $data['fe_ticket_status'], 'last_update_time' => date('Y-m-d H:i:s'), 'dateupdated' => date('Y-m-d H:i:s')]);

			if($send_mail){
				if($data_reply['created_type'] == 'staff'){
					$this->change_issue_status($data['ticket_id'], 'staff');
				}else{
					$this->change_issue_status($data['ticket_id'], 'client');
				}
			}
		}
		if(isset($data['internal_resolution'])){
			$data_reply['resolution'] = $data['note_details'];

			/*update ticket resolution*/
			$this->db->where('id', $data['ticket_id']);
			$this->db->update(db_prefix().'fe_tickets', ['resolution' => $data['note_details'], 'last_update_time' => date('Y-m-d H:i:s'), 'dateupdated' => date('Y-m-d H:i:s')]);

		}
		$data_reply['datecreated'] = date('Y-m-d H:i:s');

		$this->db->insert(db_prefix().'fe_ticket_action_post_internal_notes', $data_reply);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			$this->update_issue_first_reply_time($data['ticket_id']);

			/*TODO send mail to client*/
			return $insert_id;
		}
		return false;
	}

	/**
	 * update issue first reply time
	 * @param  [type] $ticket_id 
	 * @return [type]            
	 */
	public function update_issue_first_reply_time($ticket_id)
	{
		$this->db->where('id', $ticket_id);
		$ticket = $this->db->get(db_prefix() . 'fe_tickets');
		if($ticket){
			if($ticket->first_reply_time == null){
				$this->db->where('id', $ticket_id);
				$ticket = $this->db->update(db_prefix() . 'fe_tickets', ['first_reply_time' => date('Y-m-d H:i:s')]);
			}
		}
		return true;
	}

	/**
	 * delete_issue_history
	 * @param  [type] $id   
	 * @param  [type] $type 
	 * @return [type]       
	 */
	public function delete_issue_history($id, $type)
	{
		if($type == 'assign_ticket'){
			$this->db->where('id', $id);
			$this->db->delete(db_prefix() . 'fe_ticket_action_reassign_tickets');
			if ($this->db->affected_rows() > 0) {
				return true;
			}
		}elseif($type == 'post_internal'){
			$this->db->where('id', $id);
			$this->db->delete(db_prefix() . 'fe_ticket_action_post_internal_notes');
			if ($this->db->affected_rows() > 0) {
				return true;
			}	if ($this->db->affected_rows() > 0) {
				return true;
			}
		}elseif($type == 'post_reply'){
			$this->db->where('id', $id);
			$this->db->delete(db_prefix() . 'fe_ticket_action_post_replies');
			if ($this->db->affected_rows() > 0) {
				return true;
			}
		}elseif($type == 'ticket_timeline_log'){
			$this->db->where('id', $id);
			$this->db->delete(db_prefix() . 'fe_ticket_timeline_logs');
			if ($this->db->affected_rows() > 0) {
				return true;
			}
		}
		return false;
	}

	public function change_issue_status($ticket_id, $type = 'staff')
	{
		$data = $this->get_issue($ticket_id);

		$merge_fields = [];
		$subject = 'Issue Change Status';
		$fromname = get_option('companyname');
		if($type == 'staff'){
			$email = fe_get_contact_email('client', $data->client_id);
			$merge_fields['{issue_public_url}'] = site_url('fixed_equipment/fixed_equipment_client/issue_detail/'.$ticket_id);
		}else{
			$email = fe_get_staff_email($data->assigned_id);
			$merge_fields['{issue_public_url}'] = admin_url('fixed_equipment/issue_detail/'.$ticket_id);
		}

		$merge_fields['{ticket_subject}'] = $data->ticket_subject;
		$merge_fields['{status}'] = $data->status;
		$merge_fields['{email_signature}'] = get_option('email_signature');
		$merge_fields['{Issue_id}'] = $data->code;


		$issue_change_status_template = $this->issue_change_status_template();

		foreach ($merge_fields as $key => $val) {
			if (stripos($issue_change_status_template, $key) !== false) {
				$issue_change_status_template = str_ireplace($key, $val, $issue_change_status_template);
			} else {
				$issue_change_status_template = str_ireplace($key, '', $issue_change_status_template);
			}
		}

		if(new_strlen($email) > 0){
			$parse_content['fromname'] = $fromname;				
			$parse_content['subject'] = $subject;				
			$parse_content['content'] = $issue_change_status_template;	

			$fe_send_email = $this->fe_send_email($email, $parse_content, $ticket_id, 'change_status', $type);
		}
	}

	/**
	 * fe issue status mark as
	 * @param  [type] $status 
	 * @param  [type] $id     
	 * @param  string $type   
	 * @return [type]         
	 */
	public function fe_issue_status_mark_as($status, $id, $type = 'staff')
	{
		$status_f = false;
		// ticket_status
		if($status == 'closed'){
			$time_spent = 0;
			$get_ticket = $this->get_issue($id);
			if($get_ticket){
				$datecreated = $get_ticket->datecreated;
				$time_spent = strtotime(date('Y-m-d H:i:s')) - strtotime($datecreated);
				$time_spent = $time_spent/3600;
			}
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'fe_tickets', ['status' => $status, 'time_spent' => $time_spent]);
		}else{
			$this->db->where('id', $id);
			$this->db->update(db_prefix() . 'fe_tickets', ['status' => $status]);
		}

		if ($this->db->affected_rows() > 0) {
			$status_f = true;
			$this->change_issue_status($id, $type);

				//write log
			$this->fe_ticket_log($id, 'fe_issue', _l('fe_issue_status_change_to').': '. _l('fe_'.$status));
		}
		
		return $status_f;
	}

	/**
	 * delete issue
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_issue($id)
	{	
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'fe_tickets');
		if ($this->db->affected_rows() > 0) {
			return true;
		}
		return false;
	}

	public function client_get_cart_detailt_by_cart_id($cart_id = '', $model_id = '', $product_id = ''){

		if($model_id != '' && is_numeric($model_id) && $model_id > 0){

			$this->db->select(db_prefix().'fe_cart_detailt.product_id,'.db_prefix().'fe_cart_detailt.product_name');
			$this->db->join(db_prefix().'fe_assets', db_prefix().'fe_assets.id = '.db_prefix().'fe_cart_detailt.product_id', 'left');
			$this->db->where(db_prefix().'fe_cart_detailt.cart_id', $cart_id);
			$this->db->where(db_prefix().'fe_assets.model_id', $model_id);
			return $cart_detailt =  $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}else{
			$this->db->where('product_id',$product_id);
			$this->db->where('cart_id',$cart_id);
			return $this->db->get(db_prefix().'fe_cart_detailt')->result_array();
		}
		
	}
}
