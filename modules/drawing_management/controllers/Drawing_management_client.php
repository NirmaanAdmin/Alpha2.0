<?php
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

/**
* Document management client
*/
class drawing_management_client extends ClientsController
{

	/**
	* __construct description
	*/
	public function __construct()
	{
		parent::__construct();
		if(get_option('dmg_allows_customers_to_manage_documents') != 1){
			redirect(site_url('authentication/login'));
		}
		$this->load->model('drawing_management_model');
	}

	/**
	* index 
	* @param  int $page 
	* @param  int $group_id   
	* @param  string $key  
	* @return view       
	*/
	public function index(){  
		$data['title']                 = _l('dmg_file_management');
		init_drawing_fist_item('customer');
		if(!is_client_logged_in()){
			redirect(site_url('authentication/login'));
		}
		$user_id = get_client_user_id();
		$master_parent_id = '';
		$id = $this->input->get('id');
		$edit = $this->input->get('edit');
		$share_to_me = $this->input->get('share_to_me');
		$data['share_to_me'] = ($share_to_me == null ? 0 : $share_to_me);
		$data['edit'] = ($edit == null ? 0 : $edit);
		$data_root_folder = $this->drawing_management_model->get_item('','parent_id = 0 and ((creator_id = '.$user_id.' and creator_type = "customer") or (creator_id = 0 and creator_type = "public"))','name, id, creator_id, is_primary, filetype');
		if($id == null){
			$id = '';
			foreach ($data_root_folder as $key => $value) {
				if($id == ''){
					if($key == 0){
						$id = $value['id'];
						$master_parent_id = $id;
					}
				}
			}
		}
		else{
			$master_parent_id = $this->drawing_management_model->get_master_id($id);
		}
		$file_locked = false;
		$data['root_folder'] = $data_root_folder;
		$data['parent_id'] = $id;
		$data['master_parent_id'] = $master_parent_id;
		if(is_numeric($id) && $id > 0){
			$file_locked = drawing_check_file_locked($id);
			if($data['edit'] == 1 && $file_locked){
				access_denied('drawing_management');
			}
			$data['item'] = $this->drawing_management_model->get_item($id);
			if($data['item'] == null){
				redirect(site_url('drawing_management/drawing_management_client'));            
			}
		}
		$data['share_id'] = $this->drawing_management_model->get_item_share_to_me(true, 'customer');
		$data['file_locked'] = $file_locked;
		$data['staffs'] = $this->staff_model->get();
		$this->load->model('clients_model');
		$data['customers'] = $this->clients_model->get();
		$this->load->model('client_groups_model');
		$data['customer_groups'] = $this->client_groups_model->get_groups();
		$this->data($data);
		$this->view('clients/file_managements/file_management.php', $data);
		$this->layout();
	} 

	/**
	* create remider
	* @param  integer $id 
	* @param  integer $file_id 
	*/
	public function delete_remider($id, $file_id){
		$result =  $this->drawing_management_model->delete_remider($id);
		if($result){
			set_alert('success', _l('dmg_deleted_successfully'));
		}
		else{
			set_alert('danger', _l('dmg_deleted_fail'));					
		}
		echo html_entity_decode($result); 
	}


	/**
	* create remider
	* @param  integer $file_id 
	*/
	public function create_remider($file_id){
		$redirect = '';
		if($this->input->post()){
			$data = $this->input->post();
			if(isset($data['redirect'])){
				$redirect = $data['redirect'];
				unset($data['redirect']);
			}

			if($data['id'] == ''){
				$result =  $this->drawing_management_model->create_remider($data);
				if($result > 0){
					set_alert('success', _l('dmg_created_successfully'));
				}
				else{
					set_alert('danger', _l('dmg_create_failure'));					
				}
			}
			else{
				$result =  $this->drawing_management_model->update_remider($data);
				if($result){
					set_alert('success', _l('dmg_updated_successfully'));
				}
				else{
					set_alert('danger', _l('dmg_update_failure'));
				}
			}
		}
		if($redirect == 'share_to_me'){
			redirect(site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$file_id));     			
		}
		else{
			redirect(site_url('drawing_management/drawing_management_client?id='.$file_id));     			
		}
	}

	/**
	* share document
	*/
	public function share_document(){
		if($this->input->post()){
			$data = $this->input->post();
			$parent_id = $data['parent_id'];
			unset($data['parent_id']);

			$redirect = $data['redirect'];
			unset($data['redirect']);

			if($data['id'] == ''){
				unset($data['id']);
				$result =  $this->drawing_management_model->add_share_document($data);
				if(is_numeric($result)){
					set_alert('success', _l('dmg_shared_successfully'));
				}
				else{
					set_alert('danger', _l('dmg_share_fail'));					
				}
			}
			else{
				$result =  $this->drawing_management_model->update_share_document($data);
				if($result){
					set_alert('success', _l('dmg_updated_successfully'));
				}
				else{
					set_alert('danger', _l('dmg_update_failure'));
				}
			}
			if($redirect == 'share_to_me'){
				redirect(site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$parent_id));     			
			}
			else{
				redirect(site_url('drawing_management/drawing_management_client?id='.$parent_id));     			
			}
		}
		redirect(site_url('drawing_management/drawing_management_client?id='));
	}

	/**
	* create share
	* @param  integer $id 
	* @param  integer $file_id 
	*/
	public function delete_share($id, $file_id){
		$result =  $this->drawing_management_model->delete_share($id);
		if($result){
			set_alert('success', _l('dmg_deleted_successfully'));
		}
		else{
			set_alert('danger', _l('dmg_deleted_fail'));					
		}
		echo html_entity_decode($result);
	}

	/**
	* upload version file
	* @param  integer $id 
	*/
	public function upload_version_file($id){
		$result =  $this->drawing_management_model->upload_version_file($id);
		if($result){
			set_alert('success', _l('dmg_uploaded_successfully'));
		}
		else{
			set_alert('danger', _l('dmg_upload_failed'));					
		}
		if($this->input->post('redirect') == 'share_to_me'){
			redirect(site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$id));     			
		}
		else{
			redirect(site_url('drawing_management/drawing_management_client?id='.$id));     			
		}
	}

	/**
	* delete log
	* @param  integer $id 
	*/
	public function delete_log($id, $parent_id = ''){
		$result = false;
		if($id != ''){
			$result =  $this->drawing_management_model->delete_log_version($id);
			if($result){
				set_alert('success', _l('dmg_deleted_successfully'));
			}
			else{
				set_alert('danger', _l('dmg_deleted_fail'));					
			}
		}
		echo html_entity_decode($result);
	}

	/**
	* restore item
	* @return boolean 
	*/
	public function restore_item($id){
		$success = $this->drawing_management_model->restore_item($id);
		if($success){
			set_alert('success', _l('dmg_successfully_restored'));
		}
		else{
			set_alert('danger', _l('dmg_restore_failed'));					
		}
		echo html_entity_decode($success);
		die;
	}

	/**
	* bulk duplicate item
	* @return string 
	*/
	public function bulk_duplicate_item(){
		$selected_folder = $this->input->get('selected_folder');
		$selected_item = $this->input->get('selected_item');
		$selected_array = explode(',', $selected_item);
		$success = false;
		$affectedRows = 0;
		foreach ($selected_array as $key => $item_id) {
			$res = $this->drawing_management_model->duplicate_item($selected_folder, $item_id);
			if($res){
				$affectedRows++;
			}
		}
		if($affectedRows > 0){
			$success = true;
		}
		if($success){
			set_alert('success', _l('dmg_successfully_duplicated'));
		}
		else{
			set_alert('danger', _l('dmg_duplicate_failure'));					
		}
		echo html_entity_decode($success);
		die;
	}

	/**
	* bulk move item
	* @return string 
	*/
	public function bulk_move_item(){
		$selected_folder = $this->input->get('selected_folder');
		$selected_item = $this->input->get('selected_item');
		$selected_array = explode(',', $selected_item);
		$success = false;
		$affectedRows = 0;
		foreach ($selected_array as $key => $item_id) {
			$res = $this->drawing_management_model->move_item($selected_folder, $item_id);
			if($res){
				$affectedRows++;
			}
		}
		if($affectedRows > 0){
			$success = true;
		}
		if($success){
			set_alert('success', _l('dmg_successfully_moved'));
		}
		else{
			set_alert('danger', _l('dmg_move_failure'));					
		}
		echo html_entity_decode($success);
		die;
	}

    /* preview */
    public function editdocument2()
    {
    	$id = $this->input->get('id');
    	if (!(has_permission('drawing_management_file_management', '', 'edit')) && drawing_check_file_locked($id)) {
    		access_denied('drawing_management');
    	}
    	$data_item = $this->drawing_management_model->get_item($id);
    	if($data_item && is_object($data_item)){
    		$data['file'] = $data_item;
    		$data['title'] = $data_item->name;
    		$data['id'] = $id;
    		$this->load->view('clients/file_managements/edit_file.php', $data);
    	}
    	else{
			redirect(site_url('drawing_management/drawing_management_client'));     
    	}
    }

	/* preview */
	public function editdocument()
	{
		$id = $this->input->get('id');
		if (!(has_permission('drawing_management_file_management', '', 'edit')) && drawing_check_file_locked($id)) {
			access_denied('drawing_management');
		}
		require_once(module_dir_path(DRAWING_MANAGEMENT_MODULE_NAME).'/third_party/vendor/autoload.php');    

		$data_item = $this->drawing_management_model->get_item($id);
		if($data_item && is_object($data_item)){
			$data['file'] = $data_item;
			$data['title'] = $data_item->name;

			$path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER . '/files/' . $data_item->parent_id . '/'.$data_item->name;
			//Load docx file
			$phpWord = IOFactory::load($path);
			$save_path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER."/html_files/".$id;
			drawing_dmg_create_folder($save_path);
			$file_path = $save_path.'/index.html';

			// Convert to html file
			$objWriter = IOFactory::createWriter($phpWord, 'HTML');
			$objWriter->save($file_path);

			$data_html = file_get_contents($file_path);

			$doc = new DOMDocument();
			$doc->loadHTML($data_html);
			$xpath = new DOMXPath($doc);

			foreach($xpath->evaluate("//head") as $node) {
				$node->parentNode->removeChild($node);
			}
			$output = $doc->saveHTML();
			$data['html'] = $output;
			$data['id'] = $id;
			$this->load->view('file_managements/edit_file.php', $data);
		}
		else{
			redirect(admin_url('drawing_management'));     
		}
	}


	/**
	* lock unlock item
	* @param  integer $id   
	* @param  string $type 
	*/
	public function lock_unlock_item($id, $type){
		$data['id'] = $id;
		$data['locked'] = ($type == 'lock' ? 1 : 0);
		$data['lock_user'] = get_staff_user_id();
		$success = $this->drawing_management_model->update_item($data);
		if($type == 'lock'){
			if($success){
				set_alert('success', _l('dmg_locked_successfully'));
			}
			else{
				set_alert('danger', _l('dmg_lock_failure'));					
			}
		}
		if($type == 'unlock'){
			if($success){
				set_alert('success', _l('dmg_unlocked_successfully'));
			}
			else{
				set_alert('danger', _l('dmg_unlock_failure'));					
			}
		}
		echo html_entity_decode($success);
		die;
	}

	/* preview */
	public function preview()
	{
		$data['title']                 = _l('dmg_file_management');
		$master_parent_id = '';
		$id = $this->input->get('id');
		$data['file'] = $this->drawing_management_model->get_item($id);
		$this->load->view('file_managements/preview_file.php', $data);
	}

	/**
	* get custom field data
	* @param  integer $id 
	* @return integer     
	*/
	public function get_custom_field_data($id){
		$data = $this->drawing_management_model->get_custom_fields($id);
		echo json_encode($data);
		die;
	}

	/**
	* get custom field
	* @param  integer $id 
	* @return integer     
	*/
	public function get_custom_field($id = ''){
		if($id == ''){
			echo json_encode('');
			die;
		}
		$required = 1;
		$html = '';
		$customfield = $this->drawing_management_model->get_custom_fields($id);
		if($customfield){
			switch ($customfield->type) {
				case 'select':
				$data['option'] = $customfield->option;
				$data['title'] = $customfield->title;
				$data['id'] = $customfield->id;
				$data['required'] = $required;
				$data['select'] = '';
				$html .= $this->load->view('includes/controls/select', $data, true);
				break;
				case 'multi_select':
				$data['option'] = $customfield->option;
				$data['title'] = $customfield->title;
				$data['id'] = $customfield->id;
				$data['required'] = $required;
				$data['select'] = '';
				$html .= $this->load->view('includes/controls/multi_select', $data, true);
				break;
				case 'checkbox':
				$data['option'] = $customfield->option;
				$data['title'] = $customfield->title;
				$data['id'] = $customfield->id;
				$data['required'] = $required;
				$data['select'] = '';
				$html .= $this->load->view('includes/controls/checkbox', $data, true);
				break;
				case 'radio_button':
				$data['option'] = $customfield->option;
				$data['title'] = $customfield->title;
				$data['id'] = $customfield->id;
				$data['required'] = $required;
				$data['select'] = '';
				$html .= $this->load->view('includes/controls/radio_button', $data, true);
				break;
				case 'textarea':
				$data['id'] = $customfield->id;
				$data['title'] = $customfield->title;
				$data['required'] = $required;
				$data['value'] = '';
				$html .= $this->load->view('includes/controls/textarea', $data, true);
				break;
				case 'numberfield':
				$data['id'] = $customfield->id;
				$data['title'] = $customfield->title;
				$data['required'] = $required;
				$data['value'] = '';
				$html .= $this->load->view('includes/controls/numberfield', $data, true);
				break;
				case 'textfield':
				$data['id'] = $customfield->id;
				$data['title'] = $customfield->title;
				$data['required'] = $required;
				$data['value'] = '';
				$html .= $this->load->view('includes/controls/textfield', $data, true);
				break;
			}
		}
		echo json_encode($html);
		die;
	}

	/**
	* download folder
	* @param  integer $id 
	*/
	public function download_folder($id)
	{
		$data_item = $this->drawing_management_model->get_item($id, '', 'name, filetype');
		if($data_item && $data_item->filetype == 'folder'){
			// Delete folder with old file
			$delete_old_path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER.'/temps/'.$id;
			if(file_exists($delete_old_path)){
				delete_files($delete_old_path, true);    			
			}
			// Create folder and download
			$root_folder_name = $data_item->name;
			$this->drawing_management_model->create_folder($id);
			$this->load->library('zip');
			$path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER.'/temps/'.$id.'/'.$root_folder_name.'/';
			$this->zip->read_dir($path, false);
			$this->zip->download($root_folder_name.'.zip'); 
			$this->zip->clear_data();    		
		}
	}

	/**
	 * bulk delete item
	 */
	public function bulk_delete_item(){
		$id = $this->input->get('id');
		$success = false;
		if($id != ''){
			$affectedRows = 0;
			$id_list = explode(',', $id);
			foreach ($id_list as $key => $_id) {
				if($_id){
					$result =  $this->drawing_management_model->delete_item($_id);
					if($result){
						$affectedRows++;
					}
				}
			}
			if($affectedRows > 0){
				$success = true;
			}
		}

		if($success){
			set_alert('success', _l('dmg_deleted_successfully'));
		}
		else{
			set_alert('danger', _l('dmg_deleted_fail'));					
		}
		echo html_entity_decode($success);
		die;
	}

	/**
	 * bulk download item
	 */
	public function bulk_download_item(){
		$parent_id = $this->input->get('parent_id');
		$id = $this->input->get('id');
		$success = false;
		if($id != ''){
			$root_path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER.'/temps/bulk_downloads/';


			$current_timest = strtotime(date('Y-m-d H:i:s'));


			// Create folder and download
			$folder_name = 'Document-Management-'.$current_timest;
			$save_path = $root_path.$folder_name.'/';
			$this->drawing_management_model->create_folder_bulk_download($id, $folder_name);
			$this->load->library('zip');
			$this->zip->read_dir($save_path, false);
			$this->zip->download($folder_name.'.zip'); 
			$this->zip->clear_data();  
		}
	}

/**
	 * edit file
	 * @return [type] 
	 */
	public function edit_file(){
		if($this->input->post()){
			$data = $this->input->post();
			$parent_id = '';
			if(isset($data['all_custom_field'])){
				unset($data['all_custom_field']);
			}
			if(isset($data['all_file'])){
				unset($data['all_file']);
			}
			if(isset($data['field_id'])){
				unset($data['field_id']);
			}
			if(isset($data['default_parent_id'])){
				$parent_id = $data['default_parent_id'];
				unset($data['default_parent_id']);
			}
			if($data['id'] != ''){
				$redirect_type = '';
				if(isset($data['redirect_type'])){
					$redirect_type = $data['redirect_type'];
					unset($data['redirect_type']);
				}
				$custom_field = '';
				$related_file = '';
				if(isset($data['related_file'])){
					$related_file = implode(',', $data['related_file']);
				}
				$data['custom_field'] = $custom_field;
				$data['related_file'] = $related_file;
				$res = $this->drawing_management_model->update_item($data);
				if($res){
					set_alert('success', _l('dmg_updated_successfully'));
				}
				else{
					set_alert('danger', _l('dmg_update_failure'));
				}
				if($redirect_type == 'share_to_me'){
					redirect(site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$parent_id));            					
				}
				else{
					redirect(site_url('drawing_management/drawing_management_client?id='.$parent_id));            					
				}
			}
		}
		redirect(site_url('drawing_management/drawing_management_client'));     
	}


	/**
	* create new section
	*/
	public function create_new_section(){
		if($this->input->post()){
			$data = $this->input->post();
			$parent_id = '';
			if(isset($data['default_parent_id'])){
				$parent_id = $data['default_parent_id'];
				unset($data['default_parent_id']);
			}
			$check_result = $this->drawing_management_model->check_duplicate_name($data['parent_id'], $data['name'], $data['id'], 'folder');        
			if($check_result){
				if($data['parent_id'] == '0'){
					// Section
					set_alert('warning', _l('dmg_section_name_has_duplicated'));
				}
				else{
					//Folder
					set_alert('warning', _l('dmg_folder_name_has_duplicated'));
				}
			}
			else{
				if($data['id'] == ''){		
					$res = $this->drawing_management_model->create_item($data);
					if($res){
						set_alert('success', _l('dmg_created_successfully'));
						// Section
						if($data['parent_id'] == '0'){
							redirect(site_url('drawing_management/drawing_management_client?id='.$res));            
						}
						else{
						//Folder
							redirect(site_url('drawing_management/drawing_management_client?id='.$data['parent_id']));            
						}
					}
					else{
						set_alert('danger', _l('dmg_create_failure'));
					}
				}
				else{
					$id = $data['id'];
					$res = $this->drawing_management_model->update_item($data);
					if($res){
						set_alert('success', _l('dmg_updated_successfully'));
					}
					else{
						set_alert('danger', _l('dmg_update_failure'));
					}
					redirect(site_url('drawing_management/drawing_management_client?id='.$parent_id));            
				}
			}
		}
		redirect(site_url('drawing_management/drawing_management_client'));            
	}

	/**
	 * delete section
	 * @param  integer $id 
	 */
	public function delete_section($id, $parent_id = ''){
		$result = false;
		if($id != ''){
			$result =  $this->drawing_management_model->delete_item($id);
			if($result){
				set_alert('success', _l('dmg_deleted_successfully'));
			}
			else{
				set_alert('danger', _l('dmg_deleted_fail'));					
			}
		}
		echo html_entity_decode($result);
	}

	/**
	 * upload file
	 */
	public function upload_file($id, $redirect_type = ''){
		$result =  $this->drawing_management_model->upload_file($id, 'files');
		if($result){
			set_alert('success', _l('dmg_uploaded_successfully'));
		}
		else{
			set_alert('danger', _l('dmg_upload_failed'));					
		}
		if($redirect_type == 'share_to_me'){
			redirect(site_url('drawing_management/drawing_management_client?share_to_me=1&id='.$id));            					
		}
		else{
			redirect(site_url('drawing_management/drawing_management_client?id='.$id));            						
		}
	}

	 /**
     * get folder list
     * @return string 
     */
    public function get_folder_list(){
    	$parent = $this->input->get('parent');
    	$selected_folder = $this->input->get('selected_folder');
    	$selected_item = $this->input->get('selected_item');

    	$data['main_tree'] = 1;
    	$data['parent_id'] = $parent;
    	$data['selected_folder'] = $selected_folder;
    	$data['selected_item'] = explode(',', $selected_item);
    	$data['creator_type'] = 'customer';	
    	$data['user_id'] = get_client_user_id();
    	$html = '<div class="row"><div class="col-md-12 overflow-x-auto"><div class="filetree">';
    	$html .= $this->load->view('includes/modal_contents/item_list.php', $data, true);		
    	$html .= '</div></div></div>';
    	echo html_entity_decode($html);
    	die;
    }

	/**
	* create audit request
	*/
	public function send_request_approve($id){
		if($this->input->post()){
			$data =  $this->input->post();
			if(isset($data['select_folder'])){
				$data['folder_after_approval'] = $data['select_folder'];
				unset($data['select_folder']);
			}

			$approve_type = 'normal';
			if(isset($data['approve_type'])){
				$approve_type = $data['approve_type'];
				unset($data['approve_type']);
			}
			if($approve_type == 'normal'){
				$data['approve'] = -1;
				$success = $this->drawing_management_model->update_item($data);
				if($success){
					// Approve
					$staff_id = get_staff_user_id();
					$rel_type = 'document';
					$check_proccess = $this->drawing_management_model->get_approve_setting($rel_type, false);
					$process = '';
					if($check_proccess){
						$this->drawing_management_model->send_request_approve($id, $rel_type, $staff_id);
						set_alert('success', _l('dmg_successful_submission_of_approval_request'));
					} else {
						// Auto checkout if not approve process
						// Change status
						$this->db->where('id', $id);
						$this->db->update(db_prefix().'dms_items', ['approve' => 1]);
						set_alert('success', _l('dmg_approved'));
					}
				}
				else{
					set_alert('danger', _l('dmg_request_failed'));			
				}
			}
			else{
				$data['sign_approve'] = -1;
				$success = $this->drawing_management_model->update_item($data);
				if($success){
					// Approve
					$staff_id = get_staff_user_id();
					$rel_type = 'document';
					$check_proccess = $this->drawing_management_model->get_approve_setting($rel_type, false);
					$process = '';
					if($check_proccess){
						$this->drawing_management_model->send_request_approve_eid($id, $rel_type, $staff_id);
						set_alert('success', _l('dmg_successful_submission_of_approval_request'));
					} else {
						// Auto checkout if not approve process
						// Change status
						$this->db->where('id', $id);
						$this->db->update(db_prefix().'dms_items', ['approve' => 1]);
						set_alert('success', _l('dmg_approved'));
					}
				}
				else{
					set_alert('danger', _l('dmg_request_failed'));			
				}
			}

		}
		redirect(site_url('drawing_management/drawing_management_client?id='.$id));            						
	}


	/**
	 * detail approve
	 * @param  string $hash 
	 */
	public function detail_sign_approve($hash){
		$data_item = $this->drawing_management_model->get_item_from_hash($hash);
		if($data_item){
			$id = $data_item->id;
			$data['id'] = $id;
			$data['item'] = $data_item;
			$data['title'] = $data_item->name;

			$rel_type = 'document';
			$data['data_approve'] = $this->drawing_management_model->get_approval_detail_eids($id, $rel_type);
			$process = '';
			$check_proccess = $this->drawing_management_model->get_approve_setting($rel_type, false);
			if($check_proccess){
				if($check_proccess->choose_when_approving == 0){
					$process = 'not_choose';
				}else{
					$process = 'choose';
				}
			}else{
				$process = 'no_proccess';
			}
			$data['process'] = $process;

			$this->data($data);
			$this->view('clients/file_managements/includes/detail_sign_request.php', $data);
			$this->layout();
		}
		else{
			redirect(site_url('drawing_management/drawing_management_client'));            						
		}
	}

		/**
	 * detail approve
	 * @param  string $hash 
	 */
	public function detail_approve($hash){
		$data_item = $this->drawing_management_model->get_item_from_hash($hash);
		if($data_item){
			$id = $data_item->id;
			$data['id'] = $id;
			$data['item'] = $data_item;
			$data['title'] = $data_item->name;

			$rel_type = 'document';
			$data['data_approve'] = $this->drawing_management_model->get_approval_details($id, $rel_type);
			$process = '';
			$check_proccess = $this->drawing_management_model->get_approve_setting($rel_type, false);
			if($check_proccess){
				if($check_proccess->choose_when_approving == 0){
					$process = 'not_choose';
				}else{
					$process = 'choose';
				}
			}else{
				$process = 'no_proccess';
			}
			$data['process'] = $process;

			$this->data($data);
			$this->view('clients/file_managements/includes/detail_request.php', $data);
			$this->layout();
		}
		else{
			redirect(site_url('drawing_management/drawing_management_client'));            						
		}
	}

    /**
     * save document
     * @return string 
     */
    public function save_document(){
    	$id = $this->input->post('id');
    	$data_item = $this->drawing_management_model->get_item($id);
    	if($data_item && is_object($data_item)){
    		$html = urldecode($this->input->post('html_content', false));
    		$html = '<!DOCTYPE html>
    		<html>
    		<head>
    		<meta charset="UTF-8" />
    		<title></title>
    		</head>
    		<body>
    		'.$html.'
    		</body>
    		</html>';

    		$path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER . '/files/' . $data_item->parent_id . '/'.$data_item->name;

    		$save_path = DRAWING_MANAGEMENT_MODULE_UPLOAD_FOLDER."/html_files/".$id;
    		drawing_dmg_create_folder($save_path);
    		$file_path = $save_path.'/index.html';

    		$myfile = fopen($file_path, "w") or die("Unable to open file!");
    		fwrite($myfile, $html);
    		$this->drawing_management_model->convert_html_file_to_word_api($file_path, $path);
    		echo 'true';
    		die;
    	}
    }

}