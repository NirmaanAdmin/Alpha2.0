<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * fixed_equipment
 */
class fixed_equipment extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('fixed_equipment_model');
		$this->load->model('departments_model');
		hooks()->do_action('fixed_equipment_init');
	}

	/* index */
	public function index()
	{
		if (!has_permission('fixed_equipment_dashboard', '', 'view')) {
			access_denied('fixed_equipment');
		}
		$data['title'] = _l('fe_fixed_equipment');
		$this->load->view('dashboard', $data);
	}

	/**
	 * settings
	 * @return view 
	 */
	public function settings()
	{
		if (!(is_admin() ||
			has_permission('fixed_equipment_setting_model', '', 'view') ||
			has_permission('fixed_equipment_setting_model', '', 'view_own') ||

			has_permission('fixed_equipment_setting_manufacturer', '', 'view') ||
			has_permission('fixed_equipment_setting_manufacturer', '', 'view_own') ||

			has_permission('fixed_equipment_setting_depreciation', '', 'view') ||
			has_permission('fixed_equipment_setting_depreciation', '', 'view_own') ||

			has_permission('fixed_equipment_setting_category', '', 'view') ||
			has_permission('fixed_equipment_setting_category', '', 'view_own') ||

			has_permission('fixed_equipment_setting_status_label', '', 'view') ||
			has_permission('fixed_equipment_setting_status_label', '', 'view_own') ||

			has_permission('fixed_equipment_setting_custom_field', '', 'view') ||
			has_permission('fixed_equipment_setting_custom_field', '', 'view_own') ||

			has_permission('fixed_equipment_setting_supplier', '', 'view') ||
			has_permission('fixed_equipment_setting_supplier', '', 'view_own') ||

			has_permission('fixed_equipment_assets', '', 'view_own') ||
			has_permission('fixed_equipment_assets', '', 'view') ||
			has_permission('fixed_equipment_licenses', '', 'view_own') ||
			has_permission('fixed_equipment_licenses', '', 'view') ||
			has_permission('fixed_equipment_accessories', '', 'view_own') ||
			has_permission('fixed_equipment_accessories', '', 'view') ||
			has_permission('fixed_equipment_consumables', '', 'view_own') ||
			has_permission('fixed_equipment_consumables', '', 'view')

		)) {
			access_denied('fe_fixed_equipment');
		}
		$data['title']                 = _l('fe_fixed_equipment');
		$data['tab'] = $this->input->get('tab');
		if ($data['tab'] == 'suppliers') {
			$this->load->model('staff_model');
			$data['locations'] = $this->fixed_equipment_model->get_locations();
			$data['staffs'] = $this->staff_model->get();
			$this->load->model('currencies_model');
			$data['currencies'] = $this->currencies_model->get();
			$data['base_currency'] = $this->currencies_model->get_base_currency();
		}
		if ($data['tab'] == 'models') {
			$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
			$data['categories'] = $this->fixed_equipment_model->get_categories('', 'asset');
			$data['depreciations'] = $this->fixed_equipment_model->get_depreciations();
			$data['custom_field_lists'] = get_custom_fields('fixed_equipment');
			$data['field_sets'] = $this->fixed_equipment_model->get_field_set();
			$data['models'] = $this->fixed_equipment_model->get_models('');
			$this->load->model('currencies_model');
			$base_currency = $this->currencies_model->get_base_currency();
			$data['currency_name'] = '';
			if (isset($base_currency)) {
				$data['currency_name'] = $base_currency->name;
			}
		}
		if ($data['tab'] == 'approval_settings') {
			$this->load->model('staff_model');
			$data['staffs'] = $this->staff_model->get();
		}
		$this->load->view('manage_setting', $data);
	}

	/**
	 * depreciations table
	 * @return json 
	 */
	public function depreciations_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'name',
					'term'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_depreciations';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_depreciation', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_depreciation', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}

				$has_delete_pemit = false;
				if (is_admin() || 
					has_permission('fixed_equipment_setting_depreciation', '', 'delete') ||
					has_permission('fixed_equipment_assets', '', 'delete') ||
					has_permission('fixed_equipment_licenses', '', 'delete') ||
					has_permission('fixed_equipment_accessories', '', 'delete') ||
					has_permission('fixed_equipment_consumables', '', 'delete')
				) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'name',
					'term'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="' . $aRow['name'] . '" data-term="' . $aRow['term'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_edit_pemit && $has_delete_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_depreciations/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['name'] . $_data;
					$row[] = $aRow['term'] . ' ' . _l('fe_months');

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add depreciations
	 */
	public function add_depreciations()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$result =  $this->fixed_equipment_model->add_depreciations($data);
				if (is_numeric($result)) {
					set_alert('success', _l('fe_added_successfully', _l('fe_depreciations')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_depreciations')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_depreciations($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_depreciations')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_depreciations')));
				}
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=depreciations'));
	}
	/**
	 * delete depreciations
	 * @param  integer $id 
	 */
	public function delete_depreciations($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_depreciations($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_depreciations')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=depreciations'));
	}

	/**
	 * locations table
	 * @return json 
	 */
	public function locations_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$select = [
					'id',
					'location_name',
					'id',
					'parent',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_locations';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'location_name',
					'parent',
					'manager',
					'location_currency',
					'address',
					'city',
					'state',
					'zip',
					'country',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_locations/' . $aRow['id']) . '" >' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_locations', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_locations', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_locations/' . $aRow['id']) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['location_name'] . $_data;

					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'locations') . '">';

					$parent_name = '';
					if (is_numeric($aRow['parent'])) {
						$data_location = $this->fixed_equipment_model->get_locations($aRow['parent']);
						if ($data_location) {
							$parent_name =  $data_location->location_name;
						}
					}
					$row[] = $parent_name;

					$row[] = $this->fixed_equipment_model->count_asset_by_location($aRow['id']);

					$row[] = $this->fixed_equipment_model->count_asset_assign_by_location($aRow['id']);

					$currentcy_name = '';
					if (is_numeric($aRow['location_currency'])) {
						$data_currencies = $this->currencies_model->get($aRow['location_currency']);
						if ($data_currencies) {
							$currentcy_name = $data_currencies->name;
						}
					}

					$row[] = $currentcy_name;
					$row[] = $aRow['address'];
					$row[] = $aRow['city'];
					$row[] = $aRow['state'];
					$row[] = $aRow['zip'];

					$country_name = '';
					if (is_numeric($aRow['country'])) {
						$data_country = get_country($aRow['country']);
						if ($data_country) {
							$country_name = $data_country->short_name;
						}
					}
					$row[] = $country_name;


					$manager_name = '';
					if (is_numeric($aRow['manager'])) {
						$manager_name =  get_staff_full_name($aRow['manager']);
					}
					$row[] = $manager_name;

					$row[] = _dt($aRow['date_creator']);


					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add locations
	 */
	public function add_locations()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id =  $this->fixed_equipment_model->add_locations($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'locations');
					set_alert('success', _l('fe_added_successfully', _l('fe_locations')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_locations')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_locations($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_locations')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_locations')));
				}
				fe_handle_item_file($data['id'], 'locations');
			}
		}
		redirect(admin_url('fixed_equipment/locations'));
	}
	/**
	 * delete locations
	 * @param  integer $id 
	 */
	public function delete_locations($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_locations($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_locations')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_locations')));
			}
		}
		redirect(admin_url('fixed_equipment/locations'));
	}
	/**
	 * get modal content locations
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_locations($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$data['location'] = $this->fixed_equipment_model->get_locations($id);
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['staffs'] = $this->staff_model->get();
		$data['currencies'] = $this->currencies_model->get();
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/locations_modal_content', $data, true),
			'success' => true
		]);
	}
	/**
	 * { file item }
	 *
	 * @param        $id      The identifier
	 * @param        $rel_id  The relative identifier
	 */
	public function file_item($id, $rel_id, $type)
	{
		$data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
		$data['current_user_is_admin']             = is_admin();
		$data['file'] = $this->fixed_equipment_model->get_file($id, $rel_id);
		$data['types'] = $type;
		if (!$data['file']) {
			header('HTTP/1.0 404 Not Found');
			die;
		}
		$this->load->view('settings/includes/_file', $data);
	}
	/**
	 * { delete file attachment }
	 *
	 * @param  $id     The identifier
	 */
	public function delete_file_item($id, $type)
	{
		$this->load->model('misc_model');
		$file = $this->misc_model->get_file($id);
		if ($file->staffid == get_staff_user_id() || is_admin()) {
			echo html_entity_decode($this->fixed_equipment_model->delete_file_item($id, $type));
		} else {
			header('HTTP/1.0 400 Bad error');
			echo _l('access_denied');
			die;
		}
	}

	/**
	 * suppliers table
	 * @return json 
	 */
	public function suppliers_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_suppliers';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_supplier', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_supplier', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}
				$has_delete_pemit = false;
				if (is_admin() ||
				 has_permission('fixed_equipment_setting_supplier', '', 'delete') ||
				 has_permission('fixed_equipment_setting_supplier', '', 'delete') ||
				 has_permission('fixed_equipment_assets', '', 'delete') ||
				 has_permission('fixed_equipment_licenses', '', 'delete') ||
				 has_permission('fixed_equipment_accessories', '', 'delete') ||
				 has_permission('fixed_equipment_consumables', '', 'delete')
				 ) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'supplier_name',
					'address',
					'city',
					'state',
					'country',
					'zip',
					'contact_name',
					'phone',
					'fax',
					'email',
					'url',
					'note',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_edit_pemit && $has_delete_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_suppliers/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['supplier_name'] . '</span>' . $_data;
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'suppliers') . '">';
					$row[] = '<span class="text-nowrap">' . $aRow['address'] . '</span>';
					$row[] = '<span class="text-nowrap">' . $aRow['contact_name'] . '</span>';
					$row[] = $aRow['email'];
					$row[] = $aRow['phone'];
					$row[] = $aRow['fax'];
					$row[] = '<span class="text-nowrap">' . $aRow['url'] . '</span>';
					$row[] = $this->fixed_equipment_model->count_total_asset_supplier($aRow['id'], 'asset');
					$row[] = $this->fixed_equipment_model->count_total_asset_supplier($aRow['id'], 'accessory');
					$row[] = $this->fixed_equipment_model->count_total_asset_supplier($aRow['id'], 'license');

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add suppliers
	 */
	public function add_suppliers()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id =  $this->fixed_equipment_model->add_suppliers($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'suppliers');
					set_alert('success', _l('fe_added_successfully', _l('fe_suppliers')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_suppliers')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_suppliers($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_suppliers')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_suppliers')));
				}
				fe_handle_item_file($data['id'], 'suppliers');
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=suppliers'));
	}
	/**
	 * delete suppliers
	 * @param  integer $id 
	 */
	public function delete_suppliers($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_suppliers($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_suppliers')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_suppliers')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=suppliers'));
	}

	/**
	 * get modal content suppliers
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_suppliers($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$data['supplier'] = $this->fixed_equipment_model->get_suppliers($id);
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['staffs'] = $this->staff_model->get();
		$data['currencies'] = $this->currencies_model->get();
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/suppliers_modal_content', $data, true),
			'success' => true
		]);
	}
	/**
	 * asset_manufacturers table
	 * @return json 
	 */
	public function asset_manufacturers_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_asset_manufacturers';
				$join         = [];
				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_manufacturer', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'name',
					'url',
					'support_url',
					'support_phone',
					'support_email',
					'date_creator'
				]);

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_manufacturer', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}
				$has_delete_pemit = false;
				if (is_admin() ||
				 has_permission('fixed_equipment_setting_manufacturer', '', 'delete') ||
				 has_permission('fixed_equipment_assets', '', 'delete') ||
				 has_permission('fixed_equipment_licenses', '', 'delete') ||
				 has_permission('fixed_equipment_accessories', '', 'delete') ||
				 has_permission('fixed_equipment_consumables', '', 'delete')
				 ) {
					$has_delete_pemit = true;
				}

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_delete_pemit && $has_edit_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_asset_manufacturers/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['name'] . '</span>' . $_data;
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'asset_manufacturers') . '">';
					$row[] = '<span class="text-nowrap">' . $aRow['url'] . '</span>';
					$row[] = '<span class="text-nowrap">' . $aRow['support_url'] . '</span>';
					$row[] = '<span class="text-nowrap">' . $aRow['support_phone'] . '</span>';
					$row[] = '<span class="text-nowrap">' . $aRow['support_email'] . '</span>';
					$total1 = 0;
					$row[] = $this->fixed_equipment_model->count_asset_by_manufacturer_only_asset_type($aRow['id']);
					$row[] = $this->fixed_equipment_model->count_total_asset_manufacturer($aRow['id'], 'license');
					$row[] = $this->fixed_equipment_model->count_total_asset_manufacturer($aRow['id'], 'consumable');
					$row[] = $this->fixed_equipment_model->count_total_asset_manufacturer($aRow['id'], 'accessory');
					$row[] = _dt($aRow['date_creator']);
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add asset_manufacturers
	 */
	public function add_asset_manufacturers()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id =  $this->fixed_equipment_model->add_asset_manufacturers($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'asset_manufacturers');
					set_alert('success', _l('fe_added_successfully', _l('fe_asset_manufacturers')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_asset_manufacturers')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_asset_manufacturers($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_asset_manufacturers')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_asset_manufacturers')));
				}
				fe_handle_item_file($data['id'], 'asset_manufacturers');
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=asset_manufacturers'));
	}
	/**
	 * delete asset_manufacturers
	 * @param  integer $id 
	 */
	public function delete_asset_manufacturers($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_asset_manufacturers($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_asset_manufacturers')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_asset_manufacturers')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=asset_manufacturers'));
	}

	/**
	 * get modal content asset_manufacturers
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_asset_manufacturers($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$data['asset_manufacturer'] = $this->fixed_equipment_model->get_asset_manufacturers($id);
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/asset_manufacturers_modal_content', $data, true),
			'success' => true
		]);
	}

	/**
	 * categories table
	 * @return json 
	 */
	public function categories_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_categories';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_category', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_category', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}

				$has_delete_pemit = false;
				if (is_admin() || 
				has_permission('fixed_equipment_setting_category', '', 'delete') ||
				has_permission('fixed_equipment_assets', '', 'delete') ||
				has_permission('fixed_equipment_licenses', '', 'delete') ||
				has_permission('fixed_equipment_accessories', '', 'delete') ||
				has_permission('fixed_equipment_consumables', '', 'delete')
				) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'category_name',
					'type',
					'primary_default_eula',
					'confirm_acceptance',
					'send_mail_to_user',
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_edit_pemit && $has_delete_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_categories/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['category_name'] . $_data;
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'categories') . '">';
					$row[] = _l('fe_' . $aRow['type']);
					$qty = 0;
					$row[] = $this->fixed_equipment_model->count_asset_by_category($aRow['id'], $aRow['type']);

					$eula = '<i class="fa fa-times text-danger"></i>';
					if ($aRow['primary_default_eula'] == 1) {
						$eula = '<i class="fa fa-check text-success"></i>';
					}
					$row[] = $eula;

					$mail = '<i class="fa fa-times text-danger"></i>';
					if ($aRow['send_mail_to_user'] == 1) {
						$mail = '<i class="fa fa-check text-success"></i>';
					}
					$row[] = $mail;

					$acceptance = '<i class="fa fa-times text-danger"></i>';
					if ($aRow['confirm_acceptance'] == 1) {
						$acceptance = '<i class="fa fa-check text-success"></i>';
					}
					$row[] = $acceptance;




					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add categories
	 */
	public function add_categories()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id =  $this->fixed_equipment_model->add_categories($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'categories');
					set_alert('success', _l('fe_added_successfully', _l('fe_categories')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_categories')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_categories($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_categories')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_categories')));
				}
				fe_handle_item_file($data['id'], 'categories');
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=categories'));
	}
	/**
	 * delete categories
	 * @param  integer $id 
	 */
	public function delete_categories($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_categories($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_categories')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_categories')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=categories'));
	}

	/**
	 * get modal content categories
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_categories($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$data['category'] = $this->fixed_equipment_model->get_categories($id);
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/categories_modal_content', $data, true),
			'success' => true
		]);
	}

	/**
	 * models table
	 * @return json 
	 */
	public function models_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_models';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_model', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_permission = false;
				if (is_admin() ||
				has_permission('fixed_equipment_setting_model', '', 'edit') ||
				has_permission('fixed_equipment_assets', '', 'edit') ||
				has_permission('fixed_equipment_licenses', '', 'edit') ||
				has_permission('fixed_equipment_accessories', '', 'edit') ||
				has_permission('fixed_equipment_consumables', '', 'edit')) {
					$has_edit_permission = true;
				}

				$has_delete_permission = false;
				if (is_admin() ||
				has_permission('fixed_equipment_setting_model', '', 'delete') ||
				has_permission('fixed_equipment_assets', '', 'delete') ||
				has_permission('fixed_equipment_licenses', '', 'delete') ||
				has_permission('fixed_equipment_accessories', '', 'delete') ||
				has_permission('fixed_equipment_consumables', '', 'delete')) {
					$has_delete_permission = true;
				}

				$manufacturer = $this->input->post("manufacturer");
				$category = $this->input->post("category");
				$depreciation = $this->input->post("depreciation");
				if ($manufacturer != '') {
					$list = implode(',', $manufacturer);
					array_push($where, 'AND manufacturer in (' . $list . ')');
				}

				if ($category != '') {
					$list = implode(',', $category);
					array_push($where, 'AND category in (' . $list . ')');
				}

				if ($depreciation != '') {
					$list = implode(',', $depreciation);
					array_push($where, 'AND depreciation in (' . $list . ')');
				}


				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'model_name',
					'manufacturer',
					'category',
					'model_no',
					'depreciation',
					'eol',
					'note',
					'custom_field',
					'may_request',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/view_model/' . $aRow['id'] . '') . '">' . _l('fe_view') . '</a>';
					if ($has_edit_permission) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_delete_permission) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_models/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<a href="' . admin_url('fixed_equipment/view_model/' . $aRow['id'] . '') . '"><span class="text-nowrap">' . $aRow['model_name'] . '</span></a>' . $_data;
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'models') . '">';

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer'])) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $manufacturer_name . '</span>';
					$row[] = $aRow['model_no'];

					$row[] = $this->fixed_equipment_model->count_asset_by_model($aRow['id']);

					$depreciation_name = '';
					if (is_numeric($aRow['depreciation'])) {
						$data_depreciation = $this->fixed_equipment_model->get_depreciations($aRow['depreciation']);
						if ($data_depreciation) {
							$depreciation_name = $data_depreciation->name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $depreciation_name . '</span>';

					$category_name = '';
					if (is_numeric($aRow['category'])) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category']);
						if ($data_category) {
							$category_name = $data_category->category_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $category_name . '</span>';
					$row[] = (is_numeric($aRow['eol']) ? '<span class="text-nowrap">' . $aRow['eol'] . ' ' . _l('months') . '</span>' : '');

					$row[] = '<span class="text-nowrap">' . $aRow['note'] . '</span>';
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add models
	 */
	public function add_models()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id =  $this->fixed_equipment_model->add_models($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'models');
					set_alert('success', _l('fe_added_successfully', _l('fe_models')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_models')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_models($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_models')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_models')));
				}
				fe_handle_item_file($data['id'], 'models');
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=models'));
	}
	/**
	 * delete models
	 * @param  integer $id 
	 */
	public function delete_models($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_models($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_models')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_models')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=models'));
	}

	/**
	 * get modal content models
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_models($id)
	{
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'asset');
		$data['depreciations'] = $this->fixed_equipment_model->get_depreciations();
		$data['custom_field_lists'] = get_custom_fields('fixed_equipment');
		$data['model'] = $this->fixed_equipment_model->get_models($id);
		$data['field_sets'] = $this->fixed_equipment_model->get_field_set();

		$custom_field_id_list = [];
		$data_custom = $this->fixed_equipment_model->get_custom_field_models($id);
		if ($data_custom) {
			foreach ($data_custom as $fields) {
				$custom_field_id_list[] = $fields['fieldid'];
			}
		}
		$data['model']->custom_field = $custom_field_id_list;
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/models_modal_content', $data, true),
			'success' => true
		]);
	}
	/**
	 * status_labels table
	 * @return json 
	 */
	public function status_labels_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_status_labels';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_status_label', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_status_label', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}

				$has_delete_pemit = false;
				if (is_admin() || 
				has_permission('fixed_equipment_setting_status_label', '', 'delete') ||
				has_permission('fixed_equipment_assets', '', 'delete') ||
				has_permission('fixed_equipment_licenses', '', 'delete') ||
				has_permission('fixed_equipment_accessories', '', 'delete') ||
				has_permission('fixed_equipment_consumables', '', 'delete')
				) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'name',
					'status_type',
					'chart_color',
					'note',
					'show_in_side_nav',
					'default_label'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-name="' . $aRow['name'] . '" data-status_type="' . $aRow['status_type'] . '" data-chart_color="' . $aRow['chart_color'] . '" data-note="' . $aRow['note'] . '" data-default_label="' . $aRow['default_label'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_edit_pemit && $has_delete_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_status_labels/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';
					$row[] = $aRow['name'] . $_data;
					$row[] = _l('fe_' . $aRow['status_type']);
					$row[] = $this->fixed_equipment_model->count_asset_by_status($aRow['id']);
					$row[] = '<div><button class="btn" style="background-color: ' . $aRow['chart_color'] . '"></button> <strong><small>' . $aRow['chart_color'] . '</small></strong></div>';
					$default_label = '<i class="fa fa-times"></i>';
					if ($aRow['default_label'] == 1) {
						$default_label = '<i class="fa fa-check"></i>';
					}
					$row[] = $default_label;

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add status_labels
	 */
	public function add_status_labels()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$result =  $this->fixed_equipment_model->add_status_labels($data);
				if (is_numeric($result)) {
					set_alert('success', _l('fe_added_successfully', _l('fe_status_labels')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_status_labels')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_status_labels($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_status_labels')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_status_labels')));
				}
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=status_labels'));
	}

	/**
	 * delete status_labels
	 * @param  integer $id 
	 */
	public function delete_status_labels($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_status_labels($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_status_labels')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_status_labels')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=status_labels'));
	}

	/**
	 * view model
	 * @param  integer $id 
	 */
	public function view_model($id)
	{
		if (!is_admin() && !(has_permission('fixed_equipment_setting_model', '', 'view') ||
			has_permission('fixed_equipment_setting_model', '', 'view_own') ||
			has_permission('fixed_equipment_assets', '', 'view_own') ||
			has_permission('fixed_equipment_assets', '', 'view') ||
			has_permission('fixed_equipment_licenses', '', 'view_own') ||
			has_permission('fixed_equipment_licenses', '', 'view') ||
			has_permission('fixed_equipment_accessories', '', 'view_own') ||
			has_permission('fixed_equipment_accessories', '', 'view') ||
			has_permission('fixed_equipment_consumables', '', 'view_own') ||
			has_permission('fixed_equipment_consumables', '', 'view')
		)) {
			access_denied('fe_fixed_equipment');
		}
		$data['model'] = $this->fixed_equipment_model->get_models($id);
		$title = '';
		if ($data['model']) {
			$title = $data['model']->model_name;
		}
		$data['title']                 = $title;
		$this->load->view('settings/view_model', $data);
	}

	/**
	 * view model table
	 */
	public function view_model_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$model_id = $this->input->post('model_id');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];

				$custom_fields = get_custom_fields('fixed_equipment');
				foreach ($custom_fields as $field) {
					array_push($select, 'id');
				}

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [];
				array_push($where, 'AND type = "asset" AND active = 1 AND model_id = ' . $model_id);

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					'depreciation',
					'supplier_id',
					'order_number',
					'description',
					'requestable',
					'qr_code',
					'date_creator',
					'updated_at',
					'checkin_out',
					'checkin_out_id',
					'status'
				]);
				$can_edit = false;
				if (is_admin() || has_permission('fixed_equipment_assets', '', 'edit')) {
					$can_edit = true;
				}

				$can_delete = false;
				if (is_admin() || has_permission('fixed_equipment_assets', '', 'delete')) {
					$can_delete = true;
				}

				$can_view = false;
				if (is_admin() || has_permission('fixed_equipment_assets', '', 'view') || has_permission('fixed_equipment_assets', '', 'view_own')) {
					$can_view = true;
				}

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options text-nowrap">';
					$arr_row_option = [];

					if ($can_view) {
						$arr_row_option[] = '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow['id'] . '?tab=details') . '">' . _l('fe_view') . '</a>';
					}
					if ($can_edit) {
						$arr_row_option[] = '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($can_delete) {
						$arr_row_option[] = '<a href="' . admin_url('fixed_equipment/delete_assets/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= implode(' | ', $arr_row_option);
					$_data .= '</div>';

					$row[] = '<div class="text-nowrap">' . $aRow['assets_name'] . '</div>' . $_data;

					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';

					$row[] = $aRow['series'];

					$model_name = '';
					$model_no = '';
					$category_id = 0;
					$manufacturer_id = 0;
					if (is_numeric($aRow['model_id']) > 0) {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$model_name = $data_model->model_name;
							$model_no = $data_model->model_no;
							$category_id = $data_model->category;
							$manufacturer_id = $data_model->manufacturer;
						}
					}
					$row[] = '<span class="text-nowrap">' . $model_name . '</span>';
					$row[] = '<span class="text-nowrap">' . $model_no . '</span>';

					$category_name = '';
					if (is_numeric($category_id) && $category_id > 0) {
						$data_cat = $this->fixed_equipment_model->get_categories($category_id);
						if ($data_cat) {
							$category_name = $data_cat->category_name;
						}
					}
					$row[] = $category_name;

					$status_name = '';
					if (is_numeric($aRow['status']) && $aRow['status'] > 0) {
						$data_status = $this->fixed_equipment_model->get_status_labels($aRow['status']);
						if ($data_status) {
							$status_name = $data_status->name;
						}
					}
					$row[] = $status_name;

					$data_location_info = $this->fixed_equipment_model->get_asset_location_info($aRow['id']);
					$checkout_to = '';
					$current_location = '';

					if ($data_location_info->checkout_to != '') {
						$icon_checkout_to = '';
						if ($data_location_info->checkout_type == 'location') {
							$icon_checkout_to = '<i class="fa fa-map-marker"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_locations/' . $data_location_info->to_id) . '?re=assets" class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'user') {
							$icon_checkout_to = '<i class="fa fa-user"></i>';
							$checkout_to = '<span class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</span>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'asset') {
							$icon_checkout_to = '<i class="fa fa-barcode"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_asset/' . $data_location_info->to_id . '?tab=details') . '" class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						}
					}
					$row[] = $checkout_to;
					$row[] = '<span class="text-nowrap">' . $data_location_info->curent_location . '</span>';
					$row[] = '<span class="text-nowrap">' . $data_location_info->default_location . '</span>';

					$manufacturer_name = '';
					if (is_numeric($manufacturer_id) && $manufacturer_id > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($manufacturer_id);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;

					$supplier_name = '';
					if (is_numeric($aRow['supplier_id'])) {
						$data_supplier = $this->fixed_equipment_model->get_suppliers($aRow['supplier_id']);
						if ($data_supplier) {
							$supplier_name = $data_supplier->supplier_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $supplier_name . '</span>';

					$row[] = $aRow['date_buy'] != '' ? _d($aRow['date_buy']) : '';
					$row[] = $aRow['unit_price'] != '' ? app_format_money($aRow['unit_price'], $currency_name) : '';
					$row[] = $aRow['order_number'];
					$row[] = $aRow['warranty_period'] != '' ? $aRow['warranty_period'] . ' ' . _l('months') : '';
					$row[] = '';
					$row[] = '<div class="text-nowrap">' . $aRow['description'] . '</div>';
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow['id'], 'checkout');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow['id'], 'checkin');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow['id'], 'checkout', 1);
					$row[] = '<span class="text-nowrap">' . _dt($aRow['date_creator']) . '</span>';
					$row[] = '<span class="text-nowrap">' . _dt($aRow['updated_at']) . '</span>';

					$checkout_date = '';
					$expected_checkin_date = '';
					if ($aRow['checkin_out'] == 2) {
						if (is_numeric($aRow['checkin_out_id']) && $aRow['checkin_out_id'] > 0) {
							$data_checkout = $this->fixed_equipment_model->get_checkin_out_data($aRow['checkin_out_id']);
							if ($data_checkout) {
								$expected_checkin_date = (($data_checkout->expected_checkin_date != '' || $data_checkout->expected_checkin_date != null) ? _d($data_checkout->expected_checkin_date) : '');
								$checkout_date = (($data_checkout->checkin_date != '' || $data_checkout->checkin_date != null) ? _d($data_checkout->checkin_date) : _d(date('Y-m-d'), $data_checkout->date_creator));
							}
						}
					}

					$row[] = $checkout_date;
					$row[] = $expected_checkin_date;

					$last_audit = '';
					$next_audit = '';
					$data_audit = $this->fixed_equipment_model->get_2_audit_info_asset($aRow['id']);
					if ($data_audit) {
						if (isset($data_audit[0]) && isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
							$last_audit = _d(date('Y-m-d', strtotime($data_audit[1]['audit_date'])));
						}
						if (isset($data_audit[0]) && !isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
						}
					}
					$row[] = '<span class="text-nowrap">' . $last_audit . '</span>';
					$row[] = '<span class="text-nowrap">' . $next_audit . '</span>';

					foreach ($custom_fields as $field) {
						$value = get_custom_field_value($aRow['id'], $field['id'], 'fixed_equipment');
						$row[] = $value;
					}



					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * assets
	 */
	public function assets()
	{
		if (!(has_permission('fixed_equipment_assets', '', 'view_own') || has_permission('fixed_equipment_assets', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$data['title']    = _l('fe_asset_management');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$this->load->model('clients_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}

        $data['projects'] = $this->fixed_equipment_model->get_projects();
		$data['models'] = $this->fixed_equipment_model->get_models();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['status_labels'] = $this->fixed_equipment_model->get_status_labels();
		$data['status_label_checkout'] = $this->fixed_equipment_model->get_status_labels('', 'deployable');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$data['staffs'] = $this->staff_model->get();
		$data['customers'] = $this->clients_model->get();
		$this->load->view('asset_management', $data);
	}

	/**
	 * add assets 
	 */
	public function add_assets()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			if (!$this->input->post('id')) {
				$res = $this->fixed_equipment_model->add_asset($data);
				if (count($res) > 0) {
					$message = _l('fe_added_successfully', _l('fe_asset'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_added_fail', _l('fe_asset'));
					set_alert('danger', $message);
				}
				redirect(admin_url('fixed_equipment/assets'));
			} else {
				$id = $data['id'];
				unset($data['id']);
				$success = $this->fixed_equipment_model->update_asset($data, $id);
				if ($success) {
					$message = _l('fe_updated_successfully', _l('fe_asset'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_updated_fail', _l('fe_asset'));
					set_alert('danger', $message);
				}
				redirect(admin_url('fixed_equipment/assets'));
			}
			die;
		}
		$data['title']    = _l('fe_add_asets');
		$this->load->view('add_assets', $data);
	}

	/**
	 * status_labels table
	 * @return json 
	 */
	public function all_asset_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$model = $this->input->post("model");
				$status = $this->input->post("status");
				$supplier = $this->input->post("supplier");
				$location = $this->input->post("location");

				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					'assets_name',
					db_prefix() . 'fe_assets.id',
					'series',
					'model_name',
					db_prefix() . 'fe_assets.model_no',
					db_prefix() . 'fe_categories.category_name',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_asset_manufacturers.name',

					db_prefix() . 'fe_suppliers.supplier_name',

					'date_buy',
					'unit_price',
					'order_number',
					'warranty_period',
					db_prefix() . 'fe_assets.id',
					'description',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',

					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_models ON ' . db_prefix() . 'fe_models.id = ' . db_prefix() . 'fe_assets.model_id',
					'LEFT JOIN ' . db_prefix() . 'fe_categories ON ' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_models.category',
					'LEFT JOIN ' . db_prefix() . 'fe_asset_manufacturers ON ' . db_prefix() . 'fe_asset_manufacturers.id = ' . db_prefix() . 'fe_models.manufacturer',
					'LEFT JOIN ' . db_prefix() . 'fe_suppliers ON ' . db_prefix() . 'fe_suppliers.id = ' . db_prefix() . 'fe_assets.supplier_id'
				];
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.type = "asset"');
				array_push($where, 'AND active = 1');

				if ($model != '') {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.model_id = ' . $model);
				}
				if ($status != '') {
					array_push($where, 'AND status = ' . $status);
				}
				if ($supplier != '') {
					array_push($where, 'AND supplier_id = ' . $supplier);
				}
				if ($location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}

				if (!is_admin() && has_permission('fixed_equipment_assets', '', 'view_own')) {
					array_push($where, 'AND requestable = 1');
				}
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'for_sell',
					'for_rent',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					db_prefix() . 'fe_categories.category_name',
					'supplier_id',
					'order_number',
					'description',
					'requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					'checkin_out_id',
					db_prefix() . 'fe_models.model_name',
					db_prefix() . 'fe_models.model_no',
					'status'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options text-nowrap">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow[db_prefix() . 'fe_assets.id'] . '?tab=details') . '">' . _l('fe_view') . '</a>';
					$_data .= ' | <a target="_blank" href="' . admin_url('fixed_equipment/print_qrcode_pdf/' . $aRow[db_prefix() . 'fe_assets.id'] . '?output_type=I') . '" class="text-warning">' . _l('fe_print_qrcode') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_assets', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow[db_prefix() . 'fe_assets.id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_assets', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets/' . $aRow[db_prefix() . 'fe_assets.id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['assets_name'] . '</span>' . $_data;

					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';

					$row[] = $aRow['series'];

					$category_id = 0;
					$manufacturer_id = 0;
					if (is_numeric($aRow['model_id']) > 0) {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$category_id = $data_model->category;
							$manufacturer_id = $data_model->manufacturer;
						}
					}
					$row[] = '<span class="text-nowrap">' . $aRow['model_name'] . '</span>';
					$row[] = $aRow['model_no'];

					$category_name = '';
					if (is_numeric($category_id) && $category_id > 0) {
						$data_cat = $this->fixed_equipment_model->get_categories($category_id);
						if ($data_cat) {
							$category_name = '<span class="text-nowrap">' . $data_cat->category_name . '</span>';
						}
					}
					$row[] = $category_name;

					$status = '';
					$status_name = '';
					if (is_numeric($aRow['status']) && $aRow['status'] > 0) {
						$data_status = $this->fixed_equipment_model->get_status_labels($aRow['status']);
						if ($data_status) {
							$status = $data_status->status_type;
							if ($aRow['checkin_out'] == 2 && $status == 'deployable') {
								$status = 'deployed';
							}
							$status_name = '<div class="row text-nowrap mleft5 mright5"><span style="color:' . $data_status->chart_color . '">' . $data_status->name . '</span><span class="mleft10 label label-primary">' . _l('fe_' . $status) . '</span></div>';
						}
					}
					$row[] = $status_name;



					$data_location_info = $this->fixed_equipment_model->get_asset_location_info($aRow[db_prefix() . 'fe_assets.id']);
					$checkout_to = '';
					$current_location = '';

					if ($data_location_info->checkout_to != '') {
						$icon_checkout_to = '';
						if ($data_location_info->checkout_type == 'location') {
							$icon_checkout_to = '<i class="fa fa-map-marker"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_locations/' . $data_location_info->to_id) . '?re=assets" class="text-nowrap">' . $icon_checkout_to . ' ' . _l('fe_location') . ': ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'user') {
							$head = '';
							$tail = '';
							if (fe_get_status_modules('hr_profile')) {
								$head = '<a href="' . admin_url('hr_profile/member/' . $data_location_info->to_id . '/profile') . '" target="_blank">';
								$tail = '</a>';
							}
							$icon_checkout_to = '<i class="fa fa-user"></i>';
							$checkout_to = $head . '<span class="text-nowrap">' . $icon_checkout_to . ' ' . _l('fe_staff') . ': ' . $data_location_info->checkout_to . '</span>' . $tail;
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'customer') {
							$icon_checkout_to = '<i class="fa fa-user"></i>';
							$checkout_to = '<a href="' . admin_url('clients/client/' . $data_location_info->to_id) . '" class="text-nowrap">' . $icon_checkout_to . ' ' . _l('fe_customer') . ': ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'asset') {
							$icon_checkout_to = '<i class="fa fa-barcode"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_asset/' . $data_location_info->to_id . '?tab=details') . '" class="text-nowrap">' . $icon_checkout_to . ' ' . _l('fe_asset') . ': ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						}
						elseif ($data_location_info->checkout_type == 'project') {							
							$icon_checkout_to = '<i class="fa-solid fa-chart-gantt"></i>';
							$checkout_to = '<a href="' . admin_url('projects/view/' . $data_location_info->to_id) . '" class="text-nowrap">' . $icon_checkout_to . ' ' . _l('fe_project') . ': ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						}
					}
					$row[] = $checkout_to;
					$row[] = '<span class="text-nowrap">' . $data_location_info->curent_location . '</span>';
					$row[] = '<span class="text-nowrap">' . $data_location_info->default_location . '</span>';
					$manufacturer_name = '';
					if (is_numeric($manufacturer_id) && $manufacturer_id > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($manufacturer_id);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;

					$supplier_name = '';
					if (is_numeric($aRow['supplier_id'])) {
						$data_supplier = $this->fixed_equipment_model->get_suppliers($aRow['supplier_id']);
						if ($data_supplier) {
							$supplier_name = '<span class="text-nowrap">' . $data_supplier->supplier_name . '</span>';
						}
					}
					$row[] = $supplier_name;

					$row[] = $aRow['date_buy'] != '' ? _d($aRow['date_buy']) : '';
					$row[] = $aRow['unit_price'] != '' ? app_format_money($aRow['unit_price'], $currency_name) : '';
					$row[] = $aRow['order_number'];
					$row[] = (($aRow['warranty_period'] != '' && $aRow['warranty_period'] != 0) ? $aRow['warranty_period'] . ' ' . _l('months') : '');
					$row[] = (($aRow['warranty_period'] != '' && $aRow['warranty_period'] != 0) ? _d(get_expired_date($aRow['date_buy'], $aRow['warranty_period'])) : '');
					$row[] = '<span class="text-nowrap">' . $aRow['description'] . '</span>';
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout', 0);
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkin');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout', 1, 1);
					$row[] = '<span class="text-nowrap">' . _dt($aRow['date_creator']) . '</span>';
					$row[] = '<span class="text-nowrap">' . _dt($aRow['updated_at']) . '</span>';
					$checkout_date = '';
					$expected_checkin_date = '';
					if ($aRow['checkin_out'] == 2) {
						if (is_numeric($aRow['checkin_out_id']) && $aRow['checkin_out_id'] > 0) {
							$data_checkout = $this->fixed_equipment_model->get_checkin_out_data($aRow['checkin_out_id']);
							if ($data_checkout) {
								$expected_checkin_date = (($data_checkout->expected_checkin_date != '' || $data_checkout->expected_checkin_date != null) ? _d($data_checkout->expected_checkin_date) : '');
								$checkout_date = (($data_checkout->checkin_date != '' || $data_checkout->checkin_date != null) ? _d($data_checkout->checkin_date) : _d(date('Y-m-d'), $data_checkout->date_creator));
							}
						}
					}

					$row[] = '<span class="text-nowrap">' . $checkout_date . '</span>';
					$row[] = '<span class="text-nowrap">' . $expected_checkin_date . '</span>';
					$last_audit = '';
					$next_audit = '';
					$data_audit = $this->fixed_equipment_model->get_2_audit_info_asset($aRow['id']);
					if ($data_audit) {
						if (isset($data_audit[0]) && isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
							$last_audit = _d(date('Y-m-d', strtotime($data_audit[1]['audit_date'])));
						}
						if (isset($data_audit[0]) && !isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
						}
					}
					$row[] = '<span class="text-nowrap">' . $last_audit . '</span>';
					$row[] = '<span class="text-nowrap">' . $next_audit . '</span>';

					$button = '';
					if (is_admin() || has_permission('fixed_equipment_assets', '', 'create')) {
						if ($aRow['for_sell'] == 0 && $aRow['for_rent'] == 0) {
							if ($aRow['checkin_out'] == 2) {
								$button = '<a class="btn btn-primary" data-asset_name="' . $aRow['assets_name'] . '" data-serial="' . $aRow['series'] . '" data-model="' . $aRow['model_name'] . '" onclick="check_in(this, ' . $aRow[db_prefix() . 'fe_assets.id'] . ')" >' . _l('fe_checkin') . '</a>';
							} else {
								if ($status == 'deployable') {
									$button = '<a class="btn btn-danger" data-asset_name="' . $aRow['assets_name'] . '" data-serial="' . $aRow['series'] . '" data-model="' . $aRow['model_name'] . '" onclick="check_out(this, ' . $aRow[db_prefix() . 'fe_assets.id'] . ')" >' . _l('fe_checkout') . '</a>';
								}
							}
						} else {
							$button = fe_get_html_option_button($aRow['for_sell'], $aRow['for_rent']);
						}
					}
					$row[] = $button;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * delete assets
	 */

	public function delete_assets($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_assets')));
			}
		}
		redirect(admin_url('fixed_equipment/assets'));
	}
	/**
	 * get modal content assets
	 * @param  integer $id 
	 * @return json     
	 */
	public function get_modal_content_assets($id)
	{
		$this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$data['models'] = $this->fixed_equipment_model->get_models();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['status_labels'] = $this->fixed_equipment_model->get_status_labels();

		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['asset'] = $this->fixed_equipment_model->get_assets($id);
		echo json_encode([
			'data' =>  $this->load->view('includes/new_asset_modal', $data, true),
			'success' => true
		]);
	}
	/**
	 * check exist serial
	 * @param  string $serial   
	 * @param  integer $asset_id 
	 * @return string           
	 */
	public function check_exist_serial($serial, $asset_id)
	{
		$message = '';
		if ($asset_id == 0) {
			$asset_id = '';
		}
		$data = $this->fixed_equipment_model->check_exist_serial($serial, $asset_id);
		if ($data) {
			$message = _l('fe_this_serial_number_exists_in_the_system');
		}
		echo json_encode($message);
	}
	/**
	 * check in assets
	 * @return  
	 */
	public  function check_in_assets()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$result = $this->fixed_equipment_model->check_in_assets($data);
			if ($result > 0) {
				if ($data['type'] == 'checkout') {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_assets')));
				} else {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_assets')));
				}
			} else {
				if ($data['type'] == 'checkout') {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_assets')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_assets')));
				}
			}
			redirect(admin_url('fixed_equipment/assets'));
		}
	}


	/**
	 * check out assets
	 * @return  
	 */
	public  function check_out_assets()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$result = $this->fixed_equipment_model->check_out_assets($data);
			if ($result > 0) {
				set_alert('success', _l('fe_checkout_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_checkout_fail', _l('fe_assets')));
			}
			redirect(admin_url('fixed_equipment/assets'));
		}
	}

	/**
	 * detail asset
	 * @param  integer $id 
	 */
	public function detail_asset($id)
	{
		$data['redirect'] = $this->input->get('re');
		$data['asset'] = $this->fixed_equipment_model->get_assets($id);
		if ($data['asset']) {
			if ($data['asset']->active == 0) {
				set_alert('danger', _l('fe_this_asset_not_exist'));
				redirect(admin_url('fixed_equipment/assets'));
			}
		} else {
			set_alert('danger', _l('fe_this_asset_not_exist'));
			redirect(admin_url('fixed_equipment/assets'));
		}
		$title = '';
		if ($data['asset']) {
			$data_model = $this->fixed_equipment_model->get_models($data['asset']->model_id);
			if ($data_model) {
				$title = $data_model->model_name;
				$data['model'] = $data_model;
			}
		}
		$data['title'] = $title;
		$data['id'] = $id;
		$data['tab'] = $this->input->get('tab');
		if ($data['tab'] == 'maintenances') {
			$this->load->model('currencies_model');
			$this->load->model('staff_model');
			$base_currency = $this->currencies_model->get_base_currency();
			$data['currency_name'] = '';
			if (isset($base_currency)) {
				$data['currency_name'] = $base_currency->name;
			}
			$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
			$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		}

		if ($data['tab'] == 'assets') {
			$data['models'] = $this->fixed_equipment_model->get_models();
			$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
			$data['status_labels'] = $this->fixed_equipment_model->get_status_labels();
			$data['status_label_checkout'] = $this->fixed_equipment_model->get_status_labels('', 'deployable');
			$data['locations'] = $this->fixed_equipment_model->get_locations();
			$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
			$data['staffs'] = $this->staff_model->get();
		}
		$this->load->view('view_detail_assets', $data);
	}

	/**
	 * models table
	 * @return json 
	 */
	public function asets_history_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('staff_model');
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];

				array_push($where, 'where item_id = ' . $id);

				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_log_assets';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'admin_id',
					'action',
					'target',
					'changed',
					db_prefix() . 'fe_log_assets.to',
					'to_id',
					'notes',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = _dt($aRow['date_creator']);
					$row[] = get_staff_full_name($aRow['admin_id']);
					$row[] = _l('fe_' . $aRow['action']);

					$target = '';
					switch ($aRow['to']) {
						case 'user':
							$department_name = '';
							$data_staff_department = $this->departments_model->get_staff_departments($aRow['to_id']);
							if ($data_staff_department) {
								foreach ($data_staff_department as $key => $staff_department) {
									$department_name .= $staff_department['name'] . ', ';
								}
								if ($department_name != '') {
									$department_name = '(' . rtrim($department_name, ', ') . ') ';
								}
							}
							$target = '<i class="fa fa-user"></i> ' . $department_name . '' . get_staff_full_name($aRow['to_id']);
							break;
						case 'asset':
							$data_assets = $this->fixed_equipment_model->get_assets($aRow['to_id']);
							if ($data_assets) {
								$target = '<i class="fa fa-barcode"></i> (' . $data_assets->qr_code . ') ' . $data_assets->assets_name;
							}
							break;
						case 'location':
							$data_locations = $this->fixed_equipment_model->get_locations($aRow['to_id']);
							if ($data_locations) {
								$target = '<i class="fa fa-map-marker"></i> ' . $data_locations->location_name;
							}
							break;
					}

					$row[] = $target;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * licenses
	 */
	public function licenses()
	{
		if (!(has_permission('fixed_equipment_licenses', '', 'view_own') || has_permission('fixed_equipment_licenses', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('currencies_model');
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$res = $this->fixed_equipment_model->add_licenses($data);
				if (is_numeric($res)) {
					$message = _l('fe_added_successfully', _l('fe_licenses'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_added_fail', _l('fe_licenses'));
					set_alert('danger', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_licenses($data);
				if ($success == 1) {
					$message = _l('fe_updated_successfully', _l('fe_licenses'));
					set_alert('success', $message);
				} elseif ($success == 2) {
					$message = _l('fe_updated_fail', _l('fe_licenses'));
					set_alert('danger', $message);
				} else {
					$message = _l('this_seat_is_currently_checked_out_to_a_user', _l('fe_licenses'));
					set_alert('danger', $message);
				}
			}
			redirect(admin_url('fixed_equipment/licenses'));
			die;
		}
		$this->load->model('staff_model');
		$this->load->model('clients_model');
		$data['customers'] = $this->clients_model->get();
		$data['title']  = _l('fe_licenses_management');
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$data['staffs'] = $this->staff_model->get();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'license');
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['depreciations'] = $this->fixed_equipment_model->get_depreciations();
		$base_currency = $this->currencies_model->get_base_currency();

		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('licenses_management', $data);
	}

	/**
	 * licenses table
	 * @return json 
	 */
	public function licenses_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					'assets_name',
					'product_key',
					'expiration_date',
					'licensed_to_email',
					'licensed_to_name',
					db_prefix() . 'fe_asset_manufacturers.name',
					db_prefix() . 'fe_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_asset_manufacturers ON ' . db_prefix() . 'fe_asset_manufacturers.id = ' . db_prefix() . 'fe_assets.manufacturer_id'];
				$manufacturer = $this->input->post('manufacturer');

				if (isset($manufacturer) && $manufacturer != '') {
					array_push($where, 'AND manufacturer_id = ' . $manufacturer);
				}
				array_push($where, 'AND type = "license"');
				array_push($where, 'AND active = 1');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_name',
					'date_buy',
					'depreciation',
					'supplier_id',
					'order_number',
					'description',
					'category_id',
					'product_key',
					'seats',
					'model_no',
					'location_id',
					'manufacturer_id',
					'licensed_to_name',
					'licensed_to_email',
					'reassignable',
					'termination_date',
					'expiration_date',
					'purchase_order_number',
					'maintained',
					'manufacturer_id',
					'checkin_out',
					'status'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_licenses/' . $aRow['id'] . '?tab=details') . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_licenses', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_licenses', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_license/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['assets_name'] . $_data;

					$row[] = $aRow['product_key'];

					$row[] = _d($aRow['expiration_date']);

					$row[] = $aRow['licensed_to_email'];

					$row[] = $aRow['licensed_to_name'];

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer_id']) && $aRow['manufacturer_id'] > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer_id']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;
					$total = 0;
					$avail = 0;
					$inventory_qty = 0;
					$data_total = $this->fixed_equipment_model->count_total_avail_seat($aRow['id']);
					if ($data_total) {
						$total = $data_total->total;
						$avail = $data_total->avail;
					}

					$row[] = $total;
					$row[] = $avail;

					$inventory_data = $this->fixed_equipment_model->get_warehouse_info_item($aRow['id']);
					if(count($inventory_data['warehouse']) > 0 ){
						foreach ($inventory_data['warehouse'] as $value) {
						    $inventory_qty += (float)$value['quantity'];
						}
					}
					$row[] = $inventory_qty;

					if (is_admin() || has_permission('fixed_equipment_licenses', '', 'create')) {
						if ($aRow['checkin_out'] == 2) {
							$row[] = '<a class="btn btn-primary" data-asset_name="' . $aRow['assets_name'] . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						} else {
							$row[] = '<a class="btn btn-danger" data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out(this, ' . $aRow['id'] . ')" >' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * delete license
	 */
	public function delete_license($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_licenses($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_licenses')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_licenses')));
			}
		}
		redirect(admin_url('fixed_equipment/licenses'));
	}

	/**
	 * get data licenses
	 * @param  integer $id 
	 * @return integer     
	 */
	public function get_data_licenses($id)
	{
		$data =  $this->fixed_equipment_model->get_assets($id);
		$data->unit_price = app_format_money($data->unit_price, '');
		$data->rental_price = app_format_money($data->rental_price, '');
		$data->selling_price = app_format_money($data->selling_price, '');
		echo json_encode($data);
	}

	/**
	 * check in assets
	 * @return  
	 */
	public  function check_in_license()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['item_id'] != '') {
				$id = $data['id'];
				unset($data['id']);
				$result = $this->fixed_equipment_model->check_in_licenses($data);
				if ($result > 0) {
					if ($data['type'] == 'checkout') {
						set_alert('success', _l('fe_checkout_successfully', _l('fe_licenses')));
					} else {
						set_alert('success', _l('fe_checkin_successfully', _l('fe_licenses')));
					}
				} else {
					if ($data['type'] == 'checkout') {
						set_alert('danger', _l('fe_checkout_fail', _l('fe_licenses')));
					} else {
						set_alert('danger', _l('fe_checkin_fail', _l('fe_licenses')));
					}
				}
				redirect(admin_url('fixed_equipment/detail_licenses/' . $id . '?tab=seat'));
			} else {
				$result = $this->fixed_equipment_model->check_in_license_auto($data);
				if ($result > 0) {
					if ($data['type'] == 'checkout') {
						set_alert('success', _l('fe_checkout_successfully', _l('fe_licenses')));
					} else {
						set_alert('success', _l('fe_checkin_successfully', _l('fe_licenses')));
					}
				} else {
					if ($data['type'] == 'checkout') {
						set_alert('danger', _l('fe_checkout_fail', _l('fe_licenses')));
					} else {
						set_alert('danger', _l('fe_checkin_fail', _l('fe_licenses')));
					}
				}
				redirect(admin_url('fixed_equipment/licenses'));
			}
		}
	}
	/**
	 * detail licenses
	 * @param  inter $id 
	 */
	public function detail_licenses($id)
	{
		$data['asset'] = $this->fixed_equipment_model->get_assets($id);
		$title = '';
		if ($data['asset']) {
			$title = $data['asset']->assets_name;
		}
		$data['title'] = $title;
		$data['id'] = $id;
		$data['tab'] = $this->input->get('tab');
		if ($data['tab'] == 'seat') {
			$this->load->model('staff_model');
			$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
			$data['staffs'] = $this->staff_model->get();
		}
		$this->load->view('view_detail_licenses', $data);
	}

	/**
	 * status_labels table
	 * @return json 
	 */
	public function license_seat_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id'
				];
				if (is_admin() || has_permission('fixed_equipment_licenses', '', 'view')) {
					array_push($select, 'id');
				}


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_seats';
				$join         = [];
				array_push($where, 'AND license_id = ' . $id);
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'seat_name',
					db_prefix() . 'fe_seats.to',
					'to_id',
					'license_id',
					'status',
					'date_creator'
				]);

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $key => $aRow) {
					$row = [];
					$_data = '';

					$row[] = $aRow['seat_name'];

					$checkout_to_name = '';

					$to_asset = "";
					$location = "";

					if ($aRow['to'] != "") {
						switch ($aRow['to']) {
							case 'user':
								$checkout_to_name = _l('fe_staff') . ': ' . get_staff_full_name($aRow['to_id']);
								$department_name = '';
								$data_staff_department = $this->departments_model->get_staff_departments($aRow['to_id']);
								if ($data_staff_department) {
									foreach ($data_staff_department as $key => $staff_department) {
										$department_name .= $staff_department['name'] . ', ';
									}
									if ($department_name != '') {
										$location = rtrim($department_name, ', ');
									}
								}
								break;
							case 'asset':
								$asset_data = $this->fixed_equipment_model->get_assets($aRow['to_id']);
								if ($asset_data) {
									$checkout_to_name = '';
									if ($asset_data->series != '' && $asset_data->assets_name != '') {
										$checkout_to_name = '(' . $asset_data->series . ') ' . $asset_data->assets_name;
									}
									if ($asset_data->series == '' && $asset_data->assets_name != '') {
										$checkout_to_name = $asset_data->assets_name;
									}
									if ($asset_data->series != '' && $asset_data->assets_name == '') {
										$checkout_to_name = $asset_data->series;
									}
									$checkout_to_name = _l('fe_asset') . ': <a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow['to_id'] . '?tab=details') . '">' . $checkout_to_name . '</a>';
									$location = $this->fixed_equipment_model->get_asset_location_info($aRow['to_id'])->curent_location;
								}
								break;
							case 'customer':
								$checkout_to_name = _l('fe_customer') . ': ' . fe_get_customer_name($aRow['to_id']);
								break;
						}
					}
					$license_name = "";
					$data_asset = $this->fixed_equipment_model->get_assets($aRow['license_id']);
					if ($data_asset) {
						$license_name = $data_asset->assets_name;
					}

					$row[] = $checkout_to_name;
					$row[] = $location;
					if (is_admin() || has_permission('fixed_equipment_licenses', '', 'create')) {
						if ($aRow['status'] == 2) {
							$row[] = '<a class="btn btn-primary" data-license_name="' . $license_name . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						} else {
							$row[] = '<a class="btn btn-danger" data-license_name="' . $license_name . '" onclick="check_out(this, ' . $aRow['id'] . ')" >' . _l('fe_checkout') . '</a>';
						}
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * get location name
	 * @param  integer $asset_id 
	 * @return integer           
	 */
	public function get_location_name($asset_id)
	{
		$obj = new stdClass();
		$obj->current_location = "";
		$obj->default_location = "";

		$data_asset = $this->fixed_equipment_model->get_assets($asset_id);
		if ($data_asset) {
			$location_name = '';
			if (is_numeric($data_asset->asset_location) && $data_asset->asset_location > 0) {
				$data_location = $this->fixed_equipment_model->get_locations($data_asset->asset_location);
				if ($data_location) {
					$location_name = $data_location->location_name;
				}
			}
			$curent_location = '';
			if ($data_asset->checkin_out == 2) {
				$curent_location = $this->fixed_equipment_model->get_current_asset_location($data_asset->id);
			} else {
				$data_checkin_out = $this->fixed_equipment_model->get_last_checkin_out_assets($data_asset->id, 'checkin');
				if ($data_checkin_out) {
					$location_id = $data_checkin_out->location_id;
					if (!is_numeric($location_id) || $location_id == 0) {
						$location_id = $data_asset->asset_location;
					}
					$data_location = $this->fixed_equipment_model->get_locations($location_id);
					if ($data_location) {
						$curent_location = $data_location->location_name;
					}
				} else {
					$curent_location = $location_name;
				}
			}
			$obj->current_location = $curent_location;
			$obj->default_location = $location_name;
		}
		return $obj;
	}
	/**
	 * license history table
	 * @return json 
	 */
	public function license_history_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('staff_model');
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];

				array_push($where, 'where item_id = ' . $id);

				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_log_assets';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'admin_id',
					'action',
					'target',
					'changed',
					db_prefix() . 'fe_log_assets.to',
					'to_id',
					'notes',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = _dt($aRow['date_creator']);
					$row[] = get_staff_full_name($aRow['admin_id']);
					$row[] = _l('fe_' . $aRow['action']);

					$target = '';
					switch ($aRow['to']) {
						case 'user':
							$department_name = '';
							$data_staff_department = $this->departments_model->get_staff_departments($aRow['to_id']);
							if ($data_staff_department) {
								foreach ($data_staff_department as $key => $staff_department) {
									$department_name .= $staff_department['name'] . ', ';
								}
								if ($department_name != '') {
									$department_name = '(' . rtrim($department_name, ', ') . ') ';
								}
							}
							$target = '<i class="fa fa-user"></i> ' . $department_name . '' . get_staff_full_name($aRow['to_id']);
							break;
						case 'asset':
							$data_assets = $this->fixed_equipment_model->get_assets($aRow['to_id']);
							if ($data_assets) {
								$target = '<i class="fa fa-barcode"></i> (' . $data_assets->qr_code . ') ' . $data_assets->assets_name;
							}
							break;
						case 'location':
							$data_locations = $this->fixed_equipment_model->get_locations($aRow['to_id']);
							if ($data_locations) {
								$target = '<i class="fa fa-map-marker"></i> ' . $data_locations->location_name;
							}
							break;
					}

					$row[] = $target;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * accessories
	 */
	public function accessories()
	{
		if (!(has_permission('fixed_equipment_accessories', '', 'view_own') || has_permission('fixed_equipment_accessories', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('currencies_model');
		$this->load->model('clients_model');
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_accessories($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'accessory');
					$message = _l('fe_added_successfully', _l('fe_accessories'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_added_fail', _l('fe_accessories'));
					set_alert('danger', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_accessories($data);
				if ($success == 1) {
					$message = _l('fe_quantity_not_valid', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 2) {
					$message = _l('fe_this_accessory_not_exist', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 3) {
					$message = _l('fe_quantity_is_unknown', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 4) {
					$message = _l('fe_updated_successfully', _l('fe_accessories'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_accessories'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'accessory');
			}
			redirect(admin_url('fixed_equipment/accessories'));
			die;
		}
		$this->load->model('staff_model');
		$data['title']  = _l('fe_accessories_management');
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['staffs'] = $this->staff_model->get();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'accessory');
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['customers'] = $this->clients_model->get();
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('accessories_management', $data);
	}

	/**
	 * accessories table
	 * @return json 
	 */
	public function accessories_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					'assets_name',
					db_prefix() . 'fe_categories.category_name',
					'model_no',
					db_prefix() . 'fe_asset_manufacturers.name',
					db_prefix() . 'fe_locations.location_name',
					db_prefix() . 'fe_assets.quantity',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.unit_price'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_asset_manufacturers ON ' . db_prefix() . 'fe_asset_manufacturers.id = ' . db_prefix() . 'fe_assets.manufacturer_id',
					'LEFT JOIN ' . db_prefix() . 'fe_locations ON ' . db_prefix() . 'fe_locations.id = ' . db_prefix() . 'fe_assets.asset_location',
					'LEFT JOIN ' . db_prefix() . 'fe_categories ON ' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_assets.category_id'
				];
				$manufacturer = $this->input->post('manufacturer');
				$category = $this->input->post('category');
				$location = $this->input->post('location');

				if (isset($manufacturer) && $manufacturer != '') {
					array_push($where, 'AND manufacturer_id = ' . $manufacturer);
				}
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}
				array_push($where, 'AND active = 1');
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.type = "accessory"');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.type',
					'assets_name',
					'category_id',
					'model_no',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];



					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'accessory') . '">';
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_accessories/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_accessories/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$min_quantity = $aRow['min_quantity'];
					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($aRow['id']);
					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name = $data_category->category_name;
						}
					}
					$row[] = $category_name;

					$row[] = $aRow['model_no'];

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer_id']) && $aRow['manufacturer_id'] > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer_id']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;

					$location_name = '';
					if (is_numeric($aRow['asset_location']) && $aRow['asset_location'] > 0) {
						$data_location = $this->fixed_equipment_model->get_locations($aRow['asset_location']);
						if ($data_location) {
							$location_name = $data_location->location_name;
						}
					}
					$row[] = $location_name;
					$row[] = $aRow['quantity'];
					$row[] = $min_quantity;
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = app_format_money($aRow['unit_price'], $currency_name);

					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'create')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * get modal content accessories
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_data_accessories_modal($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$data['accessory'] = $this->fixed_equipment_model->get_assets($id);
		$data['title']  = _l('fe_accessories_management');
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['staffs'] = $this->staff_model->get();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'accessory');
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();

		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		echo json_encode($this->load->view('includes/new_accessories_modal', $data, true));
	}

	/**
	 * delete accessories
	 * @param  integer $id 
	 */
	public function delete_accessories($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_accessories')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_accessories')));
			}
		}
		redirect(admin_url('fixed_equipment/accessories'));
	}

	/**
	 * consumables
	 */
	public function consumables()
	{
		if (!(has_permission('fixed_equipment_consumables', '', 'view_own') || has_permission('fixed_equipment_consumables', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('currencies_model');
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_consumables($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'consumable');
					$message = _l('fe_added_successfully', _l('fe_consumables'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_added_fail', _l('fe_consumables'));
					set_alert('danger', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_consumables($data);
				if ($success == 1) {
					$message = _l('fe_quantity_not_valid', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 2) {
					$message = _l('fe_this_consumables_not_exist', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 3) {
					$message = _l('fe_quantity_is_unknown', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 4) {
					$message = _l('fe_updated_successfully', _l('fe_accessories'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_accessories'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'consumable');
			}
			redirect(admin_url('fixed_equipment/consumables'));
			die;
		}
		$this->load->model('staff_model');
		$this->load->model('clients_model');
		$data['title']  = _l('fe_consumables_management');
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['staffs'] = $this->staff_model->get();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'consumable');
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['customers'] = $this->clients_model->get();
		$base_currency = $this->currencies_model->get_base_currency();

		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('consumables_management', $data);
	}

	/**
	 * consumables table
	 * @return json 
	 */
	public function consumables_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					'assets_name',
					db_prefix() . 'fe_categories.category_name',
					'model_no',
					db_prefix() . 'fe_asset_manufacturers.name',
					db_prefix() . 'fe_locations.location_name',
					'quantity',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_categories ON ' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_assets.category_id',
					'LEFT JOIN ' . db_prefix() . 'fe_locations ON ' . db_prefix() . 'fe_locations.id = ' . db_prefix() . 'fe_assets.asset_location',
					'LEFT JOIN ' . db_prefix() . 'fe_asset_manufacturers ON ' . db_prefix() . 'fe_asset_manufacturers.id = ' . db_prefix() . 'fe_assets.manufacturer_id'
				];

				$manufacturer = $this->input->post('manufacturer');
				$category = $this->input->post('category');
				$location = $this->input->post('location');

				if (isset($manufacturer) && $manufacturer != '') {
					array_push($where, 'AND manufacturer_id = ' . $manufacturer);
				}
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.type = "consumable"');
				array_push($where, 'AND active = 1');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_name',
					'category_id',
					'model_no',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];


					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'consumable') . '">';
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_consumables/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_consumables/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$min_quantity = $aRow['min_quantity'];
					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($aRow['id']);
					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name = $data_category->category_name;
						}
					}
					$row[] = $category_name;

					$row[] = $aRow['model_no'];

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer_id']) && $aRow['manufacturer_id'] > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer_id']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;

					$location_name = '';
					if (is_numeric($aRow['asset_location']) && $aRow['asset_location'] > 0) {
						$data_location = $this->fixed_equipment_model->get_locations($aRow['asset_location']);
						if ($data_location) {
							$location_name = $data_location->location_name;
						}
					}
					$row[] = $location_name;
					$row[] = $aRow['quantity'];
					$row[] = $min_quantity;
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = app_format_money($aRow['unit_price'], $currency_name);

					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'create')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * get modal content consumables
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_data_consumables_modal($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$data['consumable'] = $this->fixed_equipment_model->get_assets($id);
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['staffs'] = $this->staff_model->get();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'consumable');
		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		echo json_encode($this->load->view('includes/new_consumables_modal', $data, true));
	}
	/**
	 * delete consumables
	 * @param  integer $id 
	 */
	public function delete_consumables($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_consumables')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_consumables')));
			}
		}
		redirect(admin_url('fixed_equipment/consumables'));
	}
	/**
	 * components
	 */
	public function components()
	{
		if (!(has_permission('fixed_equipment_components', '', 'view_own') || has_permission('fixed_equipment_components', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('currencies_model');
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_components($data);
				if (is_numeric($insert_id)) {
					fe_handle_item_file($insert_id, 'component');
					$message = _l('fe_added_successfully', _l('fe_components'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_added_fail', _l('fe_components'));
					set_alert('danger', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_components($data);
				if ($success == 1) {
					$message = _l('fe_updated_successfully', _l('fe_components'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_components'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'component');
			}
			redirect(admin_url('fixed_equipment/components'));
			die;
		}
		$this->load->model('staff_model');
		$this->load->model('clients_model');
		$data['title']  = _l('fe_components_management');
		$data['customers'] = $this->clients_model->get();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'component');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();

		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('components_management', $data);
	}

	/**
	 * components table
	 * @return json 
	 */
	public function components_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					'assets_name',
					'series',
					db_prefix() . 'fe_categories.category_name',
					'quantity',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_locations.location_name',
					'order_number',
					'date_buy',
					'unit_price'
				];
		
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_locations ON ' . db_prefix() . 'fe_locations.id = ' . db_prefix() . 'fe_assets.asset_location',
					'LEFT JOIN ' . db_prefix() . 'fe_categories ON ' . db_prefix() . 'fe_categories.id = ' . db_prefix() . 'fe_assets.category_id'
				];

				$category = $this->input->post('category');
				$location = $this->input->post('location');
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}

				array_push($where, 'AND ' . db_prefix() . 'fe_assets.type = "component"');
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.active = 1');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_name',
					'category_id',
					'series',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'order_number',
					'date_buy',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_components/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';

					if (is_admin() || has_permission('fixed_equipment_components', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_components', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_components/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_component_by_parents($aRow['id']);
					$min_quantity = $aRow['min_quantity'];

					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;

					$row[] = $aRow['series'];

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name = $data_category->category_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $category_name . '</span>';
					$remain = 0;
					$row[] = $aRow['quantity'];
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = $min_quantity;



					$location_name = '';
					if (is_numeric($aRow['asset_location']) && $aRow['asset_location'] > 0) {
						$data_location = $this->fixed_equipment_model->get_locations($aRow['asset_location']);
						if ($data_location) {
							$location_name = $data_location->location_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $location_name . '</span>';
					$row[] = $aRow['order_number'];
					$row[] = _d($aRow['date_buy']);
					$row[] = app_format_money($aRow['unit_price'], $currency_name);
					if (is_admin() || has_permission('fixed_equipment_components', '', 'create')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * get modal content components
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_data_components_modal($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$data['component'] = $this->fixed_equipment_model->get_assets($id);
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'component');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		echo json_encode($this->load->view('includes/new_components_modal', $data, true));
	}
	/**
	 * delete components
	 * @param  integer $id 
	 */
	public function delete_components($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_components')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_components')));
			}
		}
		redirect(admin_url('fixed_equipment/components'));
	}

	/**
	 * predefined_kits
	 */
	public function predefined_kits()
	{
		if (!(has_permission('fixed_equipment_predefined_kits', '', 'view_own') || has_permission('fixed_equipment_predefined_kits', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('currencies_model');
		if ($this->input->post()) {
			$data             = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_predefined_kits($data);
				if ($insert_id == 0) {
					$message = _l('fe_added_fail', _l('fe_predefined_kits'));
					set_alert('danger', $message);
				} elseif ($insert_id == -1) {
					$message = _l('fe_the_name_has_already_been_taken', _l('fe_predefined_kits'));
					set_alert('danger', $message);
				} else {
					$message = _l('fe_added_successfully', _l('fe_predefined_kits'));
					set_alert('success', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_predefined_kits($data);
				if ($success == 0) {
					$message = _l('fe_no_data_changes', _l('fe_predefined_kits'));
					set_alert('warning', $message);
				} elseif ($success == -1) {
					$message = _l('fe_the_name_has_already_been_taken', _l('fe_predefined_kits'));
					set_alert('danger', $message);
				} else {
					$message = _l('fe_updated_successfully', _l('fe_predefined_kits'));
					set_alert('success', $message);
				}
			}
			redirect(admin_url('fixed_equipment/predefined_kits'));
			die;
		}
		$this->load->model('staff_model');
		$data['title']  = _l('fe_predefined_kits_management');
        $data['projects'] = $this->fixed_equipment_model->get_projects();
		$data['staffs'] = $this->staff_model->get();
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'component');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();

		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('predefined_kits_management', $data);
	}

	/**
	 * predefined_kits table
	 * @return json 
	 */
	public function predefined_kits_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					'id',
					'id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [];
				array_push($where, 'AND type = "predefined_kit"');
				array_push($where, 'AND active = 1');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'assets_name',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_predefined_kits/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'edit')) {
						$_data .= ' | <a href="javascript:void(0)" data-assets_name="' . $aRow['assets_name'] . '" onclick="edit(this,' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_predefined_kits/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['assets_name'] . $_data;
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'create')) {
						if ($aRow['checkin_out'] == 2) {
							$row[] = '<a class="btn btn-primary" data-asset_name="' . $aRow['assets_name'] . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						} else {
							$row[] = '<a class="btn btn-danger" data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out(this, ' . $aRow['id'] . ')" >' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * get modal content predefined_kits
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_data_predefined_kits_modal($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$data['component'] = $this->fixed_equipment_model->get_assets($id);
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['categories'] = $this->fixed_equipment_model->get_categories('', 'component');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		echo json_encode($this->load->view('includes/new_predefined_kits_modal', $data, true));
	}
	/**
	 * delete predefined_kits
	 * @param  integer $id 
	 */
	public function delete_predefined_kits($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_predefined_kits')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_predefined_kits')));
			}
		}
		redirect(admin_url('fixed_equipment/predefined_kits'));
	}

	/**
	 * detail predefined_kits
	 */
	public function detail_predefined_kits($id)
	{
		$this->load->model('currencies_model');
		$data['id'] = $id;
		$data['models'] = $this->fixed_equipment_model->get_models();
		$data['assets'] = $this->fixed_equipment_model->get_assets($id);
		if ($data['assets']) {
			$data['title']  = $data['assets']->assets_name;
		}
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('detail_predefined_kits', $data);
	}
	/**
	 * detail predefined_kits
	 */
	public function add_model_predefined_kits()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['parent_id'];
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_model_predefined_kits($data);
				if (is_numeric($insert_id) && $insert_id > 0) {
					$message = _l('fe_added_successfully', _l('fe_models'));
					set_alert('success', $message);
				} elseif (is_numeric($insert_id) && $insert_id == 0) {
					$message = _l('fe_added_fail', _l('fe_models'));
					set_alert('danger', $message);
				} else {
					$message = _l('fe_the_model_has_already_been_taken', _l('fe_models'));
					set_alert('danger', $message);
				}
			} else {
				$success = $this->fixed_equipment_model->update_model_predefined_kits($data);
				if ($success == true) {
					$message = _l('fe_updated_successfully', _l('fe_predefined_kits'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_predefined_kits'));
					set_alert('warning', $message);
				}
			}
			redirect(admin_url('fixed_equipment/detail_predefined_kits/' . $id));
			die;
		}
	}
	/**
	 * model predefined kits table
	 */
	public function model_predefined_kits_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$parent_id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_model_predefined_kits';
				$join         = [];
				array_push($where, 'AND parent_id = ' . $parent_id);

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'model_id',
					'quantity'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'edit')) {
						$_data .= '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" data-model_id="' . $aRow['model_id'] . '" data-quantity="' . $aRow['quantity'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || (has_permission('fixed_equipment_predefined_kits', '', 'edit') && has_permission('fixed_equipment_predefined_kits', '', 'delete'))) {
						$_data .= ' | ';
					}
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'delete')) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_model_predefined_kits/' . $parent_id . '/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';
					$model_name = '';
					if (is_numeric($aRow['model_id']) && $aRow['model_id'] > 0) {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$model_name = $data_model->model_name;
						}
					}
					$row[] = $model_name . $_data;
					$row[] = $aRow['quantity'];

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * delete model predefined_kits
	 * @param  integer $id 
	 */
	public function delete_model_predefined_kits($parent_id, $id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_model_predefined_kits($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully'));
			} else {
				set_alert('danger', _l('fe_deleted_fail'));
			}
		}
		redirect(admin_url('fixed_equipment/detail_predefined_kits/' . $parent_id));
	}

	/**
	 * check in accessories
	 * @return  
	 */
	public  function check_in_accessories()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['id'];
			$redirect_detailt_page = 0;
			if (isset($data['detailt_page'])) {
				$redirect_detailt_page = $data['detailt_page'];
				unset($data['detailt_page']);
			}
			$result = $this->fixed_equipment_model->check_in_accessories($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_this_accessory_has_been_checkout_for_this_user', _l('fe_accessories')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_accessories')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_accessories')));
				}
				if ($redirect_detailt_page == 0) {
					redirect(admin_url('fixed_equipment/accessories'));
				} else {
					redirect(admin_url('fixed_equipment/detail_accessories/' . $data['item_id']));
				}
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_accessories')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_accessories')));
				}
				redirect(admin_url('fixed_equipment/detail_accessories/' . $data['item_id']));
			}
		}
	}
	/**
	 * detail accessories
	 * @param  integer $id 
	 * @return integer     
	 */
	public function detail_accessories($id)
	{
		$data['redirect'] = $this->input->get('re');
		$data['title']  = '';
		$data_asset = $this->fixed_equipment_model->get_assets($id);
		if ($data_asset) {
			$data['title'] = $data_asset->assets_name . '' . ($data_asset->model_no != '' ? ' (' . $data_asset->model_no . ')' : '');
			$data['asset_name'] = $data_asset->assets_name;
			$quantity = $data_asset->quantity;
			$total_checkout = $this->fixed_equipment_model->count_checkin_asset_by_parents($id);
			$data['allow_checkout'] = (($quantity - $total_checkout) > 0);
			$data['id'] = $id;
			$data['staffs'] = $this->staff_model->get();
		} else {
			redirect(admin_url('fixed_equipment/accessories'));
		}
		$this->load->view('detail_accessories', $data);
	}
	/**
	 * detail accessories table
	 */
	public function detail_accessories_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$item_id =  $this->input->post('parent_id');
				$select = [
					'id',
					'id',
					'id',
					'id'
				];
				if (is_admin() || has_permission('fixed_equipment_accessories', '', 'view')) {
					array_push($select, 'id');
				}
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = [];
				array_push($where, 'AND item_id = ' . $item_id . ' AND status = 2');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'staff_id',
					'date_creator',
					'checkout_to',
					'customer_id',
					'status',
					'notes'
				]);

				$assets_name = '';
				$data_assets = $this->fixed_equipment_model->get_assets($item_id);
				if ($data_assets) {
					$assets_name = $data_assets->assets_name;
				}

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$checkout_to_name = '';
					if ($aRow['checkout_to'] == 'user') {
						$checkout_to_name = _l('fe_staff') . ': ' . get_staff_full_name($aRow['staff_id']);
					} else {
						$checkout_to_name = _l('fe_customer') . ': ' . fe_get_customer_name($aRow['customer_id']);
					}

					$row[] = $checkout_to_name;

					$row[] = $aRow['notes'];
					$row[] = _d($aRow['date_creator']);
					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'create')) {
						$button = '';
						if ($aRow['status'] == 2) {
							$button = '<a class="btn btn-primary" data-asset_name="' . $assets_name . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						}
						$row[] = $button;
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * check in consumables
	 * @return  
	 */
	public  function check_in_consumables()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['id'];
			$redirect_detailt_page = 0;
			if (isset($data['detailt_page'])) {
				$redirect_detailt_page = $data['detailt_page'];
				unset($data['detailt_page']);
			}
			$result = $this->fixed_equipment_model->check_in_consumables($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_this_consumables_has_been_checkout_for_this_user', _l('fe_consumables')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_consumables')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_consumables')));
				}
				if ($redirect_detailt_page == 0) {
					redirect(admin_url('fixed_equipment/consumables'));
				} else {
					redirect(admin_url('fixed_equipment/detail_consumables/' . $data['item_id']));
				}
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_consumables')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_consumables')));
				}
				redirect(admin_url('fixed_equipment/detail_consumables/' . $data['item_id']));
			}
		}
	}
	/**
	 * detail consumables
	 * @param  integer $id 
	 * @return integer     
	 */
	public function detail_consumables($id)
	{
		$data['redirect'] = $this->input->get('re');
		$data['title']  = '';
		$data_asset = $this->fixed_equipment_model->get_assets($id);
		if ($data_asset) {
			$data['title'] = $data_asset->assets_name . '' . ($data_asset->model_no != '' ? ' (' . $data_asset->model_no . ')' : '');
			$data['asset_name'] = $data_asset->assets_name;
			$quantity = $data_asset->quantity;
			$total_checkout = $this->fixed_equipment_model->count_checkin_asset_by_parents($id);
			$data['allow_checkout'] = (($quantity - $total_checkout) > 0);
			$data['id'] = $id;
			$data['staffs'] = $this->staff_model->get();
		} else {
			redirect(admin_url('fixed_equipment/consumables'));
		}
		$this->load->view('detail_consumables', $data);
	}
	/**
	 * detail consumables table
	 */
	public function detail_consumables_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$item_id =  $this->input->post('parent_id');
				$select = [
					'id',
					'id',
					'id',
					'id'
				];

				if (is_admin() || has_permission('fixed_equipment_consumables', '', 'view')) {
					array_push($select, 'id');
				}

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'fe_checkin_assets.staff_id'];
				array_push($where, 'AND item_id = ' . $item_id . ' AND status = 2');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.staff_id',
					db_prefix() . 'fe_checkin_assets.date_creator',
					db_prefix() . 'fe_checkin_assets.status',
					db_prefix() . 'fe_checkin_assets.checkout_to',
					db_prefix() . 'fe_checkin_assets.customer_id',
					db_prefix() . 'staff.lastname',
					db_prefix() . 'staff.firstname',
					'notes'
				]);

				$assets_name = '';
				$data_assets = $this->fixed_equipment_model->get_assets($item_id);
				if ($data_assets) {
					$assets_name = $data_assets->assets_name;
				}

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$checkout_to_name = '';
					if ($aRow['checkout_to'] == 'user') {
						$checkout_to_name = _l('fe_staff') . ': ' . $aRow['firstname'] . ' ' . $aRow['lastname'];
					} else {
						$checkout_to_name = _l('fe_customer') . ': ' . fe_get_customer_name($aRow['customer_id']);
					}
					$row[] = $checkout_to_name;


					$row[] = $aRow['notes'];
					$row[] = _d($aRow['date_creator']);
					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'create')) {
						$button = '';
						if ($aRow['status'] == 2) {
							$button = '<a class="btn btn-primary" data-asset_name="' . $assets_name . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						}
						$row[] = $button;
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * check in components
	 * @return  
	 */
	public  function check_in_components()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['id'];
			$redirect_detailt_page = 0;
			if (isset($data['detailt_page'])) {
				$redirect_detailt_page = $data['detailt_page'];
				unset($data['detailt_page']);
			}
			$result = $this->fixed_equipment_model->check_in_components($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_the_current_quantity_is_not_enough_for_checkout', _l('fe_components')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_components')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_components')));
				}
				if ($redirect_detailt_page == 0) {
					redirect(admin_url('fixed_equipment/components'));
				} else {
					redirect(admin_url('fixed_equipment/detail_components/' . $data['item_id']));
				}
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_components')));
				} elseif ($result == false) {
					set_alert('danger', _l('fe_quantity_not_valid', _l('fe_components')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_components')));
				}
				redirect(admin_url('fixed_equipment/detail_components/' . $data['item_id']));
			}
		}
	}
	/**
	 * detail components
	 * @param  integer $id 
	 * @return integer     
	 */
	public function detail_components($id)
	{
		$data['redirect'] = $this->input->get('re');
		$data['title']  = '';
		$data_asset = $this->fixed_equipment_model->get_assets($id);
		if ($data_asset) {
			$data['title'] = $data_asset->assets_name . '' . ($data_asset->model_no != '' ? ' (' . $data_asset->model_no . ')' : '');
			$data['asset_name'] = $data_asset->assets_name;
			$quantity = $data_asset->quantity;
			$total_checkout = $this->fixed_equipment_model->count_checkin_component_by_parents($id);
			$data['allow_checkout'] = (($quantity - $total_checkout) > 0);
			$data['id'] = $id;
			$data['staffs'] = $this->staff_model->get();
			$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		} else {
			redirect(admin_url('fixed_equipment/components'));
		}
		$this->load->view('detail_components', $data);
	}
	/**
	 * detail components table
	 */
	public function detail_components_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$item_id =  $this->input->post('parent_id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				if (is_admin() || has_permission('fixed_equipment_components', '', 'view')) {
					array_push($select, 'id');
				}
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = [];
				array_push($where, 'AND item_id = ' . $item_id . ' AND status = 2 and type="checkout"');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'asset_id',
					'quantity',
					'customer_id',
					'date_creator',
					'checkout_to',
					'status',
					'notes'
				]);

				$assets_name = '';
				$data_assets = $this->fixed_equipment_model->get_assets($item_id);
				if ($data_assets) {
					$assets_name = $data_assets->assets_name;
				}

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$check_to_name = '';
					if ($aRow['checkout_to'] == 'asset') {
						$data_asset = $this->fixed_equipment_model->get_assets($aRow['asset_id']);
						if ($data_asset && is_object($data_asset)) {
							if ($data_asset->assets_name != '' && $data_asset->series != '') {
								$check_to_name =  _l('fe_asset') . ': ' . $data_asset->assets_name . ' (' . $data_asset->series . ')';
							}
							if ($data_asset->assets_name == '' && $data_asset->series != '') {
								$check_to_name =  _l('fe_asset') . ': ' . $data_asset->series;
							}
						}
					} else {
						$check_to_name = _l('fe_customer') . ': ' . fe_get_customer_name($aRow['customer_id']);
					}

					$row[] = $check_to_name;
					$row[] = $aRow['quantity'];
					$row[] = $aRow['notes'];
					$row[] = _d($aRow['date_creator']);
					if (is_admin() || has_permission('fixed_equipment_components', '', 'create')) {
						$button = '';
						if ($aRow['status'] == 2) {
							$button = '<a class="btn btn-primary" data-asset_name="' . $assets_name . '" onclick="check_in(this, ' . $aRow['id'] . ')" >' . _l('fe_checkin') . '</a>';
						}
						$row[] = $button;
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * check in predefined_kits
	 * @return  
	 */
	public  function check_in_predefined_kits()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['id'];
			$redirect_detailt_page = 0;
			if (isset($data['detailt_page'])) {
				$redirect_detailt_page = $data['detailt_page'];
				unset($data['detailt_page']);
			}
			$result = $this->fixed_equipment_model->check_in_predefined_kits($data);
			if (is_object($result)) {
				$status = 'success';
				if ($result->status == 0 || $result->status == 1 || $result->status == 3) {
					$status = 'danger';
				}
				set_alert($status, $result->msg);
				if ($redirect_detailt_page == 0) {
					redirect(admin_url('fixed_equipment/predefined_kits'));
				} else {
					redirect(admin_url('fixed_equipment/detail_predefined_kits/' . $data['item_id']));
				}
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_predefined_kits')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_predefined_kits')));
				}
				redirect(admin_url('fixed_equipment/detail_predefined_kits/' . $data['item_id']));
			}
		}
	}

	/**
	 * table asset licenses table
	 */
	public function table_asset_licenses_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$id = $this->input->post('id');
				$select = [
					'license_id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_seats';
				$join         = [];
				array_push($where, 'AND status = 2 AND ' . db_prefix() . 'fe_seats.to = "asset" AND to_id = ' . $id);

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'license_id',
					'status',
					'id'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$license_name = '';
					$product_key = '';
					$data_licenses = $this->fixed_equipment_model->get_assets($aRow['license_id']);
					if ($data_licenses) {
						$license_name = $data_licenses->assets_name;
						$product_key = $data_licenses->product_key;
					}
					$row[] = $license_name;
					$row[] = $product_key;
					if (is_admin() || has_permission('fixed_equipment_licenses', '', 'create')) {
						$row[] = '<a class="btn btn-primary" data-license_name="' . $license_name . '" onclick="check_in(this, ' . $aRow['id'] . ')">' . _l('fe_checkin') . '</a>';
					} else {
						$row[] = '';
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * check in assets
	 * @return  
	 */
	public  function check_in_license_detail_asset()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['id'];
			unset($data['id']);
			$result = $this->fixed_equipment_model->check_in_licenses($data);
			if ($result > 0) {
				set_alert('success', _l('fe_checkin_successfully', _l('fe_licenses')));
			} else {
				set_alert('danger', _l('fe_checkin_fail', _l('fe_licenses')));
			}
			redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=licenses'));
		}
	}

	/**
	 * table asset component table
	 * @return json 
	 */
	public function table_asset_component_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$id = $this->input->post('id');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					'id',
					'id',
					'id'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = [];
				array_push($where, 'AND asset_id = ' . $id . ' AND status = 2');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'item_id',
					'quantity'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$assets_name = '';
					$purchase_cost = 0;
					$data_assets = $this->fixed_equipment_model->get_assets($aRow['item_id']);
					if ($data_assets) {
						$assets_name = $data_assets->assets_name;
						$purchase_cost = $data_assets->unit_price;
					}
					$row[] = $assets_name;
					$row[] = $aRow['quantity'];
					$row[] = app_format_money($purchase_cost, $currency_name);


					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * upload asset file
	 */
	public function upload_asset_file()
	{
		if ($this->input->post()) {
			$id = $this->input->post('id');
			$result = fe_handle_item_file($id, 'asset_files', strtotime(date('y-m-d')) . '-');
			if ($result > 0) {
				set_alert('success', _l('fe_uploaded_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_upload_fail', _l('fe_assets')));
			}
			redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=files'));
		}
	}

	/**
	 * asset file table
	 * @return json 
	 */
	public function asset_files_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'files';
				$join         = [];
				array_push($where, 'AND rel_id = ' . $id . ' AND rel_type = "asset_files"');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'file_name',
					'dateadded',
					'filetype'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$image = '';
					if ($aRow['filetype'] != '') {
						$type_split = explode('/', $aRow['filetype']);
						if (isset($type_split[0])) {
							if ($type_split[0] == 'image') {
								$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . site_url(FIXED_EQUIPMENT_IMAGE_UPLOADED_PATH . 'asset_files/' . $id . '/' . $aRow['file_name']) . '">';
							}
						}
					}
					$row[] = $image;

					$file_name = '<a href="' . site_url(FIXED_EQUIPMENT_PATH . 'asset_files/' . $id . '/' . $aRow['file_name']) . '" download>' . $aRow['file_name'] . '</a>';
					$row[] = $file_name;
					$row[] = $aRow['filetype'];
					$row[] = _dt($aRow['dateadded']);

					$action = '';
					$action .= '<a data-placement="top" data-toggle="tooltip" data-title="' . _l('fe_delete') . '" href="' . admin_url('fixed_equipment/delete_asset_file_item/' . $id . '/' . $aRow['id'] . '/asset_files') . '" class="btn btn-danger btn-icon _delete" data-original-title="" title="' . _l('fe_delete') . '"><i class="fa fa-remove"></i></a>';

					$action .= '<a data-placement="top" data-toggle="tooltip" data-title="' . _l('fe_download') . '" href="' . site_url(FIXED_EQUIPMENT_PATH . 'asset_files/' . $id . '/' . $aRow['file_name']) . '" class="btn btn-default btn-icon mleft10" data-original-title="" title="' . _l('fe_download') . '" download>
				<i class="fa fa-download"></i>
				</a>';

					$row[] = $action;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * { delete file attachment }
	 *
	 * @param  $id     The identifier
	 */
	public function delete_asset_file_item($id, $file_id, $type)
	{
		$this->load->model('misc_model');
		$file = $this->misc_model->get_file($file_id);
		$result = false;
		if ($file->staffid == get_staff_user_id() || is_admin()) {
			$result = html_entity_decode($this->fixed_equipment_model->delete_file_item($file_id, $type));
		}
		if ($result == true) {
			set_alert('success', _l('fe_deleted_successfully'));
		} else {
			set_alert('danger', _l('fe_deleted_fail'));
		}
		redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=files'));
	}

	/**
	 * upload license file
	 */
	public function upload_license_file()
	{
		if ($this->input->post()) {
			$id = $this->input->post('id');
			$result = fe_handle_item_file($id, 'license_files', strtotime(date('y-m-d')) . '-');
			if ($result > 0) {
				set_alert('success', _l('fe_uploaded_successfully', _l('fe_licenses')));
			} else {
				set_alert('danger', _l('fe_upload_fail', _l('fe_licenses')));
			}
			redirect(admin_url('fixed_equipment/detail_licenses/' . $id . '?tab=files'));
		}
	}
	/**
	 * license file table
	 * @return json 
	 */
	public function license_files_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'files';
				$join         = [];
				array_push($where, 'AND rel_id = ' . $id . ' AND rel_type = "license_files"');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'file_name',
					'dateadded',
					'filetype'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$image = '';
					if ($aRow['filetype'] != '') {
						$type_split = explode('/', $aRow['filetype']);
						if (isset($type_split[0])) {
							if ($type_split[0] == 'image') {
								$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . site_url(FIXED_EQUIPMENT_IMAGE_UPLOADED_PATH . 'license_files/' . $id . '/' . $aRow['file_name']) . '">';
							}
						}
					}
					$row[] = $image;

					$file_name = '<a href="' . site_url(FIXED_EQUIPMENT_PATH . 'license_files/' . $id . '/' . $aRow['file_name']) . '" download>' . $aRow['file_name'] . '</a>';
					$row[] = $file_name;
					$row[] = $aRow['filetype'];
					$row[] = _dt($aRow['dateadded']);

					$action = '';
					$action .= '<a data-placement="top" data-toggle="tooltip" data-title="' . _l('fe_delete') . '" href="' . admin_url('fixed_equipment/delete_license_file_item/' . $id . '/' . $aRow['id'] . '/license_files') . '" class="btn btn-danger btn-icon _delete" data-original-title="" title="' . _l('fe_delete') . '"><i class="fa fa-remove"></i></a>';

					$action .= '<a data-placement="top" data-toggle="tooltip" data-title="' . _l('fe_download') . '" href="' . site_url(FIXED_EQUIPMENT_PATH . 'license_files/' . $id . '/' . $aRow['file_name']) . '" class="btn btn-default btn-icon mleft10" data-original-title="" title="' . _l('fe_download') . '" download>
				<i class="fa fa-download"></i>
				</a>';

					$row[] = $action;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * { delete file attachment }
	 *
	 * @param  $id     The identifier
	 */
	public function delete_license_file_item($id, $file_id, $type)
	{
		$this->load->model('misc_model');
		$file = $this->misc_model->get_file($file_id);
		$result = false;
		if ($file->staffid == get_staff_user_id() || is_admin()) {
			$result = html_entity_decode($this->fixed_equipment_model->delete_file_item($file_id, $type));
		}
		if ($result == true) {
			set_alert('success', _l('fe_deleted_successfully'));
		} else {
			set_alert('danger', _l('fe_deleted_fail'));
		}
		redirect(admin_url('fixed_equipment/detail_licenses/' . $id . '?tab=files'));
	}

	/**
	 * assets mantanances
	 */
	public function assets_maintenances()
	{
		if (!(has_permission('fixed_equipment_maintenances', '', 'view_own') || has_permission('fixed_equipment_maintenances', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		if ($this->input->post()) {
			$data  = $this->input->post();
			$insert_id = 0;
			if ($data['id'] == '') {
				unset($data['id']);
				$insert_id = $this->fixed_equipment_model->add_assets_maintenances($data);
				if ($insert_id > 0) {
					set_alert('success', _l('fe_added_successfully', _l('fe_assets_maintenances')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_assets_maintenances')));
				}
			} else {
				$result = $this->fixed_equipment_model->update_assets_maintenances($data);
				if ($result == true) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_assets_maintenances')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_assets_maintenances')));
				}
			}
			$redirect = $this->input->get('redirect');
			if ($redirect != '') {
				$rel_type = $this->input->get('rel_type');
				$rel_id = $this->input->get('rel_id');
				if ($rel_type != '' && is_numeric($rel_id)) {
					if ($rel_type == 'audit') {
						$this->fixed_equipment_model->update_audit_detail_item($data['asset_id'], $rel_id, ['maintenance_id' => $insert_id]);
					}
					if ($rel_type == 'cart_detailt') {
						$this->fixed_equipment_model->update_cart_detail($rel_id, ['maintenance_id' => $insert_id]);
					}
				}
				redirect(admin_url($redirect));
			} else {
				redirect(admin_url('fixed_equipment/assets_maintenances'));
			}
		}

		$data['title']    = _l('fe_assets_maintenances');
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$this->load->view('assets_maintenance_management', $data);
	}


	/**
	 * assets maintenances table
	 * @return json 
	 */
	public function assets_maintenances_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_asset_maintenances';
				$join         = [];

				$maintenance_type = $this->input->post("maintenance_type");
				$from_date = $this->input->post("from_date");
				$to_date = $this->input->post("to_date");

				if ($maintenance_type != '') {
					array_push($where, ' AND maintenance_type = "' . $maintenance_type . '"');
				}
				if ($from_date != '' && $to_date == '') {
					$from_date = fe_format_date($from_date);
					array_push($where, ' AND date(start_date)="' . $from_date . '"');
				}
				if ($from_date != '' && $to_date != '') {
					$from_date = fe_format_date($from_date);
					$to_date = fe_format_date($to_date);
					array_push($where, ' AND date(start_date) between "' . $from_date . '" AND "' . $to_date . '"');
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'asset_id',
					'supplier_id',
					'maintenance_type',
					'title',
					'start_date',
					'completion_date',
					'cost',
					'notes',
					'date_creator',
					'warranty_improvement'
				]);

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';

					$serial = '';
					$data_asset = $this->fixed_equipment_model->get_assets($aRow['asset_id']);
					if ($data_asset && is_object($data_asset)) {
						$serial = $data_asset->series;
					}

					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options">';
					if (is_admin() || has_permission('fixed_equipment_maintenances', '', 'edit')) {
						$_data .= ' <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || (has_permission('fixed_equipment_maintenances', '', 'edit') && has_permission('fixed_equipment_maintenances', '', 'delete'))) {
						$_data .= ' | ';
					}
					if (is_admin() || has_permission('fixed_equipment_maintenances', '', 'delete')) {
						$_data .= ' <a href="' . admin_url('fixed_equipment/delete_asset_maintenances/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';
					$row[] = '<span class="text-nowrap">' . $this->fixed_equipment_model->get_asset_name($aRow['asset_id']) . '</span>' . $_data;
					$row[] = '<span class="text-nowrap">' . $serial . '</span>';
					$data_location_asset = $this->fixed_equipment_model->get_asset_location_info($aRow['asset_id']);
					$row[] = '<span class="text-nowrap">' . $data_location_asset->curent_location . '</span>';
					$row[] = _l('fe_' . $aRow['maintenance_type']);
					$row[] = '<span class="text-nowrap">' . $aRow['title'] . '</span>';
					$row[] = '<span class="text-nowrap">' . _d($aRow['start_date']) . '</span>';
					$row[] = '<span class="text-nowrap">' . _d($aRow['completion_date']) . '</span>';
					$row[] = $aRow['notes'];
					$warranty = '';
					$row[] = $warranty;
					$row[] = app_format_money($aRow['cost'], $currency_name);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * delete asset maintenances
	 * @param  integer $id 
	 */
	public function delete_asset_maintenances($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_asset_maintenances($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_depreciations')));
			}
		}
		redirect(admin_url('fixed_equipment/assets_maintenances'));
	}
	/**
	 * get data assets maintenances
	 * @param  integer $id 
	 */
	public function get_data_assets_maintenances($id)
	{
		$data_assets = $this->fixed_equipment_model->get_asset_maintenances($id);
		if ($data_assets) {
			$data_assets->completion_date = _d($data_assets->completion_date);
			$data_assets->start_date = _d($data_assets->start_date);
			$data_assets->cost = app_format_money($data_assets->cost, '');
		}
		echo json_encode($data_assets);
	}

	/**
	 * detail assets table
	 * @return json 
	 */
	public function detail_assets_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_asset_maintenances';
				$join         = [];

				array_push($where, ' AND asset_id=' . $id);

				$maintenance_type = $this->input->post("maintenance_type");
				$from_date = $this->input->post("from_date");
				$to_date = $this->input->post("to_date");

				if ($maintenance_type != '') {
					array_push($where, ' AND maintenance_type = "' . $maintenance_type . '"');
				}
				if ($from_date != '' && $to_date == '') {
					$from_date = fe_format_date($from_date);
					array_push($where, ' AND date(start_date)="' . $from_date . '"');
				}
				if ($from_date != '' && $to_date != '') {
					$from_date = fe_format_date($from_date);
					$to_date = fe_format_date($to_date);
					array_push($where, ' AND date(start_date) between "' . $from_date . '" AND "' . $to_date . '"');
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'asset_id',
					'supplier_id',
					'maintenance_type',
					'title',
					'start_date',
					'completion_date',
					'cost',
					'notes',
					'date_creator',
					'warranty_improvement'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];


					$serial = '';
					$data_asset = $this->fixed_equipment_model->get_assets($aRow['asset_id']);
					if ($data_asset) {
						$serial = $data_asset->series;
					}

					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options">';
					if (has_permission('fixed_equipment_maintenances', '', 'edit') || is_admin()) {
						$_data .= '<a href="javascript:void(0)" onclick="edit_maintenance(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ((has_permission('fixed_equipment_maintenances', '', 'edit') && has_permission('fixed_equipment_maintenances', '', 'delete')) || is_admin()) {
						$_data .= ' | ';
					}
					if (has_permission('fixed_equipment_maintenances', '', 'delete') || is_admin()) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_asset_maintenance_detail/' . $id . '/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $this->fixed_equipment_model->get_asset_name($aRow['asset_id']) . $_data;
					$row[] = $serial;
					$data_location_asset = $this->fixed_equipment_model->get_asset_location_info($aRow['asset_id']);
					$row[] = $data_location_asset->curent_location;
					$row[] = _l('fe_' . $aRow['maintenance_type']);
					$row[] = $aRow['title'];
					$row[] = _d($aRow['start_date']);
					$row[] = _d($aRow['completion_date']);
					$row[] = $aRow['notes'];
					$warranty = '';
					$row[] = $warranty;
					$row[] = app_format_money($aRow['cost'], $currency_name);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * approval setting
	 * @param  string $id 
	 * @return redirect
	 */
	public function approver_setting($id = '')
	{
		if ($this->input->post()) {
			$data                = $this->input->post();
			$id = $data['approval_setting_id'];
			unset($data['approval_setting_id']);
			if ($id == '') {
				$id = $this->fixed_equipment_model->add_approval_process($data);
				if ($id > 0) {
					set_alert('success', _l('fe_added_successfully', _l('fe_approval_process')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_approval_process')));
				}
			} else {
				$success = $this->fixed_equipment_model->update_approval_process($id, $data);
				if ($success) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_approval_process')));
				} else {
					set_alert('danger', _l('fe_updated_fail', _l('fe_approval_process')));
				}
			}
			redirect(admin_url('fixed_equipment/settings?tab=approval_settings'));
		}
	}
	/**
	 * approve setting table
	 */
	public function approve_setting_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'name',
					'related'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_approval_setting';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'name',
					'related'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_approve_setting/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					$_data .= '</div>';

					$row[] = $aRow['name'] . $_data;
					$row[] = _l('fe_' . $aRow['related']);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * delete approve setting
	 * @param  integer $id 
	 * @return integer     
	 */
	public function delete_approve_setting($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_approve_setting($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_approval_process')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_approval_process')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=approval_settings'));
	}
	/**
	 * get approve setting
	 * @param  integer $id 
	 * @return json     
	 */
	public function get_approve_setting($id)
	{
		$data_setting = $this->fixed_equipment_model->get_approval_setting($id);
		$data_setting->notification_recipient = array_map('intval', explode(',', $data_setting->notification_recipient));
		echo json_encode([
			'success' => true,
			'data_setting' => $data_setting
		]);
		die();
	}
	/**
	 * requested 
	 */
	public function requested()
	{
		if (!(has_permission('fixed_equipment_requested', '', 'view_own') || has_permission('fixed_equipment_requested', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$data['title']    = _l('fe_request_management');
		$this->load->model('staff_model');
		$data['staffs'] = $this->staff_model->get();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset', true, true, 'deployable');
		$this->load->view('requested_management', $data);
	}

	/**
	 * request table
	 * @return json 
	 */
	public function request_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_assets ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_checkin_assets.item_id'];

				$checkout_for = $this->input->post("checkout_for");
				$status = $this->input->post("status");
				$create_from_date = $this->input->post("create_from_date");
				$create_to_date = $this->input->post("create_to_date");
				if (has_permission('fixed_equipment_requested', '', 'view') || is_admin()) {
					if (isset($checkout_for) && $checkout_for != '') {
						$list_checkout_for = (is_array($checkout_for) ? implode(',', $checkout_for) : '');
						if ($list_checkout_for != '') {
							array_push($where, 'AND staff_id IN (' . $list_checkout_for . ')');
						}
					}
				} else {
					array_push($where, 'AND staff_id = ' . get_staff_user_id() . '');
				}

				if ($status != '') {
					if ($status == 3) {
						$status = 0;
					}
					array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.request_status = ' . $status);
				}

				if ($create_from_date != '' && $create_to_date != '') {
					$from_date = fe_format_date($create_from_date);
					$to_date = fe_format_date($create_to_date);
					array_push($where, 'AND (date(' . db_prefix() . 'fe_checkin_assets.date_creator) between "' . $from_date . '" AND "' . $to_date . '")');
				}

				if ($create_from_date == '' && $create_to_date != '') {
					$to_date = fe_format_date($create_to_date);
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $to_date . '"');
				}

				if ($create_from_date != '' && $create_to_date == '') {
					$from_date = fe_format_date($create_from_date);
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $from_date . '"');
				}
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.type = "checkout"');
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.requestable = 1');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_assets.assets_name',
					db_prefix() . 'fe_assets.series',
					db_prefix() . 'fe_assets.model_id',
					'request_title',
					'request_status',
					'staff_id',
					'checkout_to',
					'notes',
					db_prefix() . 'fe_checkin_assets.date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_request/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_requested', '', 'delete')) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_request/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['request_title'] . '</span>' . $_data;
					$row[] = '<span class="text-nowrap">' . $aRow['assets_name'] . '</span>';
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';
					$row[] = $aRow['series'];
					$row[] = '<span class="text-nowrap">' . get_staff_full_name($aRow['staff_id']) . '</span>';
					$row[] = $aRow['notes'];
					$row[] = '<span class="text-nowrap">' . _dt($aRow['date_creator']) . '</span>';
					$status = '';
					if ($aRow['request_status'] == 0) {
						$status = '<span class="label label-primary">' . _l('fe_new') . '</span>';
					} elseif ($aRow['request_status'] == 1) {
						$status = '<span class="label label-success">' . _l('fe_approved') . '</span>';
					} else {
						$status = '<span class="label label-danger">' . _l('fe_rejected') . '</span>';
					}
					$row[] = $status;


					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * add new request 
	 */
	public function add_new_request()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$data['creator_id'] = get_staff_user_id();
			$insert_id = $this->fixed_equipment_model->add_new_request($data);
			if ($insert_id) {
				$staff_id = get_staff_user_id();
				$rel_type = 'checkout';
				$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
				$process = '';
				if ($check_proccess) {
					if ($check_proccess->choose_when_approving == 0) {
						$this->fixed_equipment_model->send_request_approve($insert_id, $rel_type, $staff_id);
						$process = 'not_choose';
						set_alert('success', _l('fe_successful_submission_of_approval_request'));
					} else {
						$process = 'choose';
						set_alert('success', _l('fe_created_successfully'));
					}
				} else {
					// Auto checkout if not approve process
					$this->fixed_equipment_model->change_request_status($insert_id, 1);
					$data_checkout_log = $this->fixed_equipment_model->get_checkin_out_data($insert_id);
					if ($data_checkout_log) {
						// Change status to checkout and save request id
						$this->db->where('id', $data_checkout_log->item_id);
						$this->db->update(db_prefix() . 'fe_assets', ['checkin_out' => 2, 'checkin_out_id' => $insert_id]);
						$this->fixed_equipment_model->add_log($staff_id, $rel_type, $data_checkout_log->item_id, '', '', 'user', $data_checkout_log->staff_id, $data_checkout_log->notes);
					}
					$process = 'no_proccess';
					set_alert('success', _l('fe_checkout_successfully'));
				}
				redirect(admin_url('fixed_equipment/detail_request/' . $insert_id . '?process=' . $process));
			} else {
				set_alert('danger', _l('fe_request_failed'));
			}
		}
		redirect(admin_url('fixed_equipment/requested'));
	}
	/**
	 * detail request
	 * @param  integer $id 
	 */
	public function detail_request($id)
	{
		$this->load->model('staff_model');
		$send_notify = $this->session->userdata("send_notify");
		$data['send_notify'] = 0;
		if ((isset($send_notify)) && $send_notify != '') {
			$data['send_notify'] = $send_notify;
			// $this->session->unset_userdata("send_notify");
		}

		$data_checkout_log = $this->fixed_equipment_model->get_checkin_out_data($id);
		if ($data_checkout_log) {
			$item_id = $data_checkout_log->item_id;
			$data['asset'] = $this->fixed_equipment_model->get_assets($item_id);
			$title = '';
			if ($data['asset']) {
				$data['model'] = $this->fixed_equipment_model->get_models($data['asset']->model_id);
				$title = $data['model']->model_name;
			}
			$data['staffs'] = $this->staff_model->get();
			$data['data_approve'] = $this->fixed_equipment_model->get_approval_details($id, 'checkout');
			$data['title'] = $title;
			$data['id'] = $id;
			$data['tab'] = $this->input->get('tab');


			$rel_type = 'checkout';
			$process = '';
			$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
			if ($check_proccess) {
				if ($check_proccess->choose_when_approving == 0) {
					$process = 'not_choose';
				} else {
					$process = 'choose';
				}
			} else {
				$process = 'no_proccess';
			}
			$data['process'] = $process;
			$this->load->view('detail_request', $data);
		} else {
			redirect(admin_url('fixed_equipment/add_new_request'));
		}
	}
	/**
	 * delete request
	 * @param  integer $id 
	 */
	public function delete_request($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_request($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_request')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_request')));
			}
		}
		redirect(admin_url('fixed_equipment/requested'));
	}

	/**
	 * approve request form
	 * @return json 
	 */
	public function approve_request_form()
	{
		$data = $this->input->post();
		$data['date'] = date('Y-m-d H:i:s');
		$data['staffid'] = get_staff_user_id();
		$success = $this->fixed_equipment_model->change_approve($data);
		$message = '';
		if ($success == true) {
			if ($data['approve'] == 1) {
				$message = _l('fe_approved');
			} else {
				$message = _l('fe_rejected');
			}
		} else {
			$message = _l('fe_approve_fail');
		}
		echo json_encode([
			'success' => $success,
			'message' => $message,
		]);
		die();
	}

	/**
	 * choose approver request
	 * @return json 
	 */
	public function choose_approver_request()
	{
		$data = $this->input->post();
		$success = false;
		$message = '';
		if ($data['id']) {
			$insert_id = $this->fixed_equipment_model->add_approver_choosee_when_approve($data['id'], 'checkout', $data['approver']);
			if (is_numeric($insert_id) && $insert_id > 0) {
				$success = true;
				$message = _l('fe_successful_submission_of_approval_request');
			} else {
				$success = false;
				$message = _l('fe_submit_approval_request_failed');
			}
		}
		echo json_encode([
			'success' => $success,
			'message' => $message
		]);
	}

	/**
	 * assets detail mantanances
	 */
	public function assets_detail_maintenances()
	{
		if ($this->input->post()) {
			$data  = $this->input->post();
			$id = $data['id'];
			if ($data['maintenance_id'] == '') {
				unset($data['maintenance_id']);
				unset($data['id']);
				$result = $this->fixed_equipment_model->add_assets_maintenances($data);
				if ($result > 0) {
					set_alert('success', _l('fe_added_successfully', _l('fe_assets_maintenances')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_assets_maintenances')));
				}
			} else {
				$data['id'] = $data['maintenance_id'];
				unset($data['maintenance_id']);
				$result = $this->fixed_equipment_model->update_assets_maintenances($data);
				if ($result == true) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_assets_maintenances')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_assets_maintenances')));
				}
			}
			redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=maintenances'));
		}
	}


	/**
	 * asset checkout table
	 * @return json 
	 */
	public function asset_checkout_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$id = $this->input->post("id");
				$model = $this->input->post("model");
				$status = $this->input->post("status");
				$supplier = $this->input->post("supplier");
				$location = $this->input->post("location");



				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description',
					'requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					'status',

					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator'

				];


				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_models ON ' . db_prefix() . 'fe_models.id = ' . db_prefix() . 'fe_assets.model_id'];

				$list_id = '';
				$list_checkout_assets = $this->fixed_equipment_model->get_list_checkout_assets($id);
				foreach ($list_checkout_assets as $value) {
					$list_id .= $value['item_id'] . ',';
				}
				if ($list_id != '') {
					$list_id = rtrim($list_id, ',');
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.id in (' . $list_id . ')');
				} else {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.id = 0');
				}


				array_push($where, 'AND type = "asset"');

				if ($model != '') {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.model_id = ' . $model);
				}
				if ($status != '') {
					array_push($where, 'AND status = ' . $status);
				}
				if ($supplier != '') {
					array_push($where, 'AND supplier_id = ' . $supplier);
				}
				if ($location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description',
					'requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					db_prefix() . 'fe_models.model_name',
					db_prefix() . 'fe_models.model_no',
					'status'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow[db_prefix() . 'fe_assets.id'] . '?tab=details') . '">' . _l('fe_view') . '</a>';
					if (has_permission('fixed_equipment_assets', '', 'edit') || is_admin()) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow[db_prefix() . 'fe_assets.id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (has_permission('fixed_equipment_assets', '', 'delete') || is_admin()) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets/' . $aRow[db_prefix() . 'fe_assets.id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['assets_name'] . '</span>' . $_data;

					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';

					$row[] = $aRow['series'];

					$category_id = 0;
					$manufacturer_id = 0;
					if (is_numeric($aRow['model_id']) > 0) {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$category_id = $data_model->category;
							$manufacturer_id = $data_model->manufacturer;
						}
					}
					$row[] = '<span class="text-nowrap">' . $aRow['model_name'] . '</span>';
					$row[] = $aRow['model_no'];

					$category_name = '';
					if (is_numeric($category_id) && $category_id > 0) {
						$data_cat = $this->fixed_equipment_model->get_categories($category_id);
						if ($data_cat) {
							$category_name = $data_cat->category_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $category_name . '</span>';

					$status_name = '';
					if (is_numeric($aRow['status']) && $aRow['status'] > 0) {
						$data_status = $this->fixed_equipment_model->get_status_labels($aRow['status']);
						if ($data_status) {
							$status = $data_status->status_type;
							if ($aRow['checkin_out'] == 2 && $status == 'deployable') {
								$status = 'deployed';
							}
							$status_name = '<div class="row text-nowrap mleft5 mright5"><span style="color:' . $data_status->chart_color . '">' . $data_status->name . '</span><span class="mleft10 label label-primary">' . _l('fe_' . $status) . '</span></div>';
						}
					}
					$row[] = $status_name;


					$data_location_info = $this->fixed_equipment_model->get_asset_location_info($aRow[db_prefix() . 'fe_assets.id']);
					$row[] = ($data_location_info->checkout_to != '' ? _l('fe_' . $data_location_info->checkout_to) : '');
					$row[] = '<span class="text-nowrap">' . $data_location_info->curent_location . '</span>';
					$row[] = '<span class="text-nowrap">' . $data_location_info->default_location . '</span>';


					$manufacturer_name = '';
					if (is_numeric($manufacturer_id) && $manufacturer_id > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($manufacturer_id);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $manufacturer_name . '</span>';

					$supplier_name = '';
					if (is_numeric($aRow['supplier_id'])) {
						$data_supplier = $this->fixed_equipment_model->get_suppliers($aRow['supplier_id']);
						if ($data_supplier) {
							$supplier_name = $data_supplier->supplier_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $supplier_name . '</span>';

					$row[] = $aRow['date_buy'] != '' ? _d($aRow['date_buy']) : '';
					$row[] = $aRow['unit_price'] != '' ? app_format_money($aRow['unit_price'], $currency_name) : '';
					$row[] = $aRow['order_number'];
					$row[] = $aRow['warranty_period'] != '' ? $aRow['warranty_period'] . ' ' . _l('months') : '';
					$row[] = '';
					$row[] = '<span class="text-nowrap">' . $aRow['description'] . '</span>';

					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkin');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout', 1);

					$row[] = '<span class="text-nowrap">' . _dt($aRow['date_creator']) . '</span>';
					$row[] = '<span class="text-nowrap">' . _dt($aRow['updated_at']) . '</span>';
					$checkout_date = '';
					$row[] = '<span class="text-nowrap">' . $checkout_date . '</span>';
					$expected_checkin_date = '';
					$row[] = '<span class="text-nowrap">' . $expected_checkin_date . '</span>';
					$last_audit = '';
					$row[] = '<span class="text-nowrap">' . $last_audit . '</span>';
					$next_audit = '';
					$row[] = '<span class="text-nowrap">' . $next_audit . '</span>';

					$button = '';

					if ($aRow['checkin_out'] == 2 && (has_permission('fixed_equipment_assets', '', 'create') || is_admin())) {
						$button = '<a class="btn btn-primary" data-asset_name="' . $aRow['assets_name'] . '" data-serial="' . $aRow['series'] . '" data-model="' . $aRow['model_name'] . '" onclick="detal_asset_check_in(this, ' . $aRow[db_prefix() . 'fe_assets.id'] . ')" >' . _l('fe_checkin') . '</a>';
					}
					$row[] = $button;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * check in detail assets
	 * @return  
	 */
	public  function check_in_detail_assets()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$id = $data['parent_id'];
			unset($data['parent_id']);
			$result = $this->fixed_equipment_model->check_in_assets($data);
			if ($result > 0) {
				set_alert('success', _l('fe_checkin_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_checkin_fail', _l('fe_assets')));
			}
			redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=assets'));
		}
	}

	/**
	 * add fieldset
	 */
	public function add_fieldset()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$result =  $this->fixed_equipment_model->add_fieldset($data);
				if (is_numeric($result)) {
					set_alert('success', _l('fe_added_successfully', _l('fe_fieldset')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_fieldset')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_fieldset($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_fieldset')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_fieldset')));
				}
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=custom_field'));
	}

	/**
	 * customfield table
	 * @return json 
	 */
	public function customfield_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					'id',
					'id',
					'id',
					'id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_fieldsets';
				$join         = [];

				if (
					!is_admin() &&
					(has_permission('fixed_equipment_setting_custom_field', '', 'view_own') ||
						has_permission('fixed_equipment_assets', '', 'view_own') ||
						has_permission('fixed_equipment_licenses', '', 'view_own') ||
						has_permission('fixed_equipment_accessories', '', 'view_own') ||
						has_permission('fixed_equipment_consumables', '', 'view_own')
					)
				) {
					$where[] = ' AND creator_id = ' . get_staff_user_id();
				}

				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_custom_field', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}
				$has_delete_pemit = false;
				if (is_admin() || 
					has_permission('fixed_equipment_setting_custom_field', '', 'delete') ||
					has_permission('fixed_equipment_assets', '', 'delete') ||
					has_permission('fixed_equipment_licenses', '', 'delete') ||
					has_permission('fixed_equipment_accessories', '', 'delete') ||
					has_permission('fixed_equipment_consumables', '', 'delete')
				) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'name',
					'notes'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_customfield/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					if ($has_edit_pemit) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . ', this); return false;" data-name="' . $aRow['name'] . '" data-notes="' . $aRow['notes'] . '" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_delete_pemit) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_fieldset/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $aRow['name'] . $_data;
					$row[] = $this->fixed_equipment_model->count_custom_field_by_field_set($aRow['id']);
					$used = '';

					$list_model = $this->fixed_equipment_model->get_list_model_by_fieldset($aRow['id']);
					if ($list_model) {
						foreach ($list_model as $model) {
							$used .= '<span class="label label-success mright5">' . $model['model_name'] . '</span>';
						}
					}
					$row[] = $used;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * detail customfield
	 * @return  
	 */
	public function detail_customfield($id)
	{
		if (!(is_admin() ||
			has_permission('fixed_equipment_setting_custom_field', '', 'view') ||
			has_permission('fixed_equipment_setting_custom_field', '', 'view_own') ||
			has_permission('fixed_equipment_assets', '', 'view_own') ||
			has_permission('fixed_equipment_assets', '', 'view') ||
			has_permission('fixed_equipment_licenses', '', 'view_own') ||
			has_permission('fixed_equipment_licenses', '', 'view') ||
			has_permission('fixed_equipment_accessories', '', 'view_own') ||
			has_permission('fixed_equipment_accessories', '', 'view') ||
			has_permission('fixed_equipment_consumables', '', 'view_own') ||
			has_permission('fixed_equipment_consumables', '', 'view')
		)) {
			access_denied('fe_fixed_equipment');
		}
		$data['title'] = _l('fe_detail_fieldset');
		$data['id'] = $id;
		$this->load->view('settings/detail_customfield', $data);
	}
	/**
	 * add custom field 
	 */
	public function add_custom_field()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$result =  $this->fixed_equipment_model->add_custom_field($data);
				if (is_numeric($result)) {
					set_alert('success', _l('fe_added_successfully', _l('fe_custom_field')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_custom_field')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_custom_field($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_custom_field')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_custom_field')));
				}
			}
		}
		redirect(admin_url('fixed_equipment/detail_customfield/' . $data['fieldset_id']));
	}
	/**
	 * custom_field_table
	 * @return json 
	 */
	public function custom_field_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$id = $this->input->post('id');
				$select = [
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_custom_fields';
				$join         = [];
				array_push($where, ' AND fieldset_id = ' . $id);
				$has_edit_pemit = false;
				if (
					is_admin() ||
					has_permission('fixed_equipment_setting_custom_field', '', 'edit') ||
					has_permission('fixed_equipment_assets', '', 'edit') ||
					has_permission('fixed_equipment_licenses', '', 'edit') ||
					has_permission('fixed_equipment_accessories', '', 'edit') ||
					has_permission('fixed_equipment_consumables', '', 'edit')
				) {
					$has_edit_pemit = true;
				}
				$has_delete_pemit = false;
				if (is_admin() || has_permission('fixed_equipment_setting_custom_field', '', 'delete')) {
					$has_delete_pemit = true;
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'title',
					'type',
					db_prefix() . 'fe_custom_fields.option',
					'required',
					'default_value',
					'fieldset_id'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$_data = '';
					if ($has_edit_pemit) {
						$name_s = '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . ');">' . $aRow['title'] . '</a>';
					} else {
						$name_s = $aRow['title'];
					}
					$_data .= '<div class="row-options">';
					if ($has_edit_pemit) {
						$_data .= '<a href="javascript:void(0)" onclick="edit(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if ($has_edit_pemit && $has_delete_pemit) {
						$_data .= ' | ';
					}
					if ($has_delete_pemit) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_custom_field/' . $id . '/' . $aRow['id']) . '" data-id="' . $aRow['id'] . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = $name_s . $_data;
					$row[] = _l('fe_' . $aRow['type']);
					$required = '';
					if ($aRow['required'] == 1) {
						$required = '<i class="fa fa-check"></i>';
					} else {
						$required = '<i class="fa fa-times"></i>';
					}
					$row[] = $required;
					$option_list = '';
					if ($aRow['option'] != '' && $aRow['option'] != null) {
						$decode_option = json_decode($aRow['option']);
						if (is_array($decode_option)) {
							foreach ($decode_option as $option) {
								$option_list .= '<span class="label label-success mright5">' . $option . '</span>';
							}
						}
					}
					$row[] = $option_list;

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * delete custom field
	 * @param  integer $fieldset_id 
	 * @param  integer $id          
	 */
	public function delete_custom_field($fieldset_id, $id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_custom_field($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_custom_field')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_custom_field')));
			}
		}
		redirect(admin_url('fixed_equipment/detail_customfield/' . $fieldset_id));
	}
	/**
	 * get custom field data
	 * @param  integer $id 
	 * @return integer     
	 */
	public function get_custom_field_data($id)
	{
		$data = $this->fixed_equipment_model->get_custom_fields($id);
		echo json_encode($data);
		die;
	}
	/**
	 * delete fieldset
	 * @param  integer $fieldset_id 
	 * @param  integer $id          
	 */
	public function delete_fieldset($fieldset_id)
	{
		if ($fieldset_id != '') {
			$result =  $this->fixed_equipment_model->delete_fieldset($fieldset_id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_fieldset')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_fieldset')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=custom_field'));
	}


	/**
	 * get_custom_field_model
	 * @param  integer $id 
	 * @return integer     
	 */
	public function get_custom_field_model($id = '')
	{
		if ($id == '') {
			echo json_encode('');
			die;
		}
		$data_models = $this->fixed_equipment_model->get_models($id);
		$html = '';
		if ($data_models) {
			$fieldset_id = $data_models->fieldset_id;
			if ($fieldset_id && $fieldset_id != '' && $fieldset_id != null) {

				$data_list_custom_field = $this->fixed_equipment_model->get_custom_field_by_fieldset($fieldset_id);
				if ($data_list_custom_field) {
					foreach ($data_list_custom_field as $key => $customfield) {

						switch ($customfield['type']) {
							case 'select':
								$data['option'] = $customfield['option'];
								$data['title'] = $customfield['title'];
								$data['id'] = $customfield['id'];
								$data['required'] = $customfield['required'];
								$data['select'] = '';
								$html .= $this->load->view('includes/controls/select', $data, true);
								break;
							case 'multi_select':
								$data['option'] = $customfield['option'];
								$data['title'] = $customfield['title'];
								$data['id'] = $customfield['id'];
								$data['required'] = $customfield['required'];
								$data['select'] = '';
								$html .= $this->load->view('includes/controls/multi_select', $data, true);
								break;
							case 'checkbox':
								$data['option'] = $customfield['option'];
								$data['title'] = $customfield['title'];
								$data['id'] = $customfield['id'];
								$data['required'] = $customfield['required'];
								$data['select'] = '';
								$html .= $this->load->view('includes/controls/checkbox', $data, true);
								break;
							case 'radio_button':
								$data['option'] = $customfield['option'];
								$data['title'] = $customfield['title'];
								$data['id'] = $customfield['id'];
								$data['required'] = $customfield['required'];
								$data['select'] = '';
								$html .= $this->load->view('includes/controls/radio_button', $data, true);
								break;
							case 'textarea':
								$data['id'] = $customfield['id'];
								$data['title'] = $customfield['title'];
								$data['required'] = $customfield['required'];
								$data['value'] = '';
								$html .= $this->load->view('includes/controls/textarea', $data, true);
								break;
							case 'numberfield':
								$data['id'] = $customfield['id'];
								$data['title'] = $customfield['title'];
								$data['required'] = $customfield['required'];
								$data['value'] = '';
								$html .= $this->load->view('includes/controls/numberfield', $data, true);
								break;
							case 'textfield':
								$data['id'] = $customfield['id'];
								$data['title'] = $customfield['title'];
								$data['required'] = $customfield['required'];
								$data['value'] = '';
								$html .= $this->load->view('includes/controls/textfield', $data, true);
								break;
						}
					}
				}
			}
		}
		echo json_encode($html);
		die;
	}

	/**
	 * audit
	 * @return  
	 */

	public function audit_managements()
	{
		if (!(has_permission('fixed_equipment_audit', '', 'view_own') || has_permission('fixed_equipment_audit', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$data['title'] = _l('fe_audit_management');
		$this->load->model('staff_model');
		$data['staffs'] = $this->staff_model->get();
		$this->load->view('audit_management', $data);
	}

	/**
	 * audit request
	 * @return  
	 */
	public function audit_request()
	{
		$data['title'] = _l('fe_audit');
		$data['models'] = $this->fixed_equipment_model->get_models();
		$query = 'select id, assets_name, series from ' . db_prefix() . 'fe_assets where type != "predefined_kit" and active = 1';
		$data['assets'] = $this->fixed_equipment_model->data_query($query, true);
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$this->load->model('staff_model');
		$data['staffs'] = $this->staff_model->get();
		$this->load->view('audits', $data);
	}
	/**
	 * get data hanson audit
	 * @return json 
	 */
	public function get_data_hanson_audit()
	{
		$data_hanson = json_encode(['id' => '', 'assets' => '', 'quantity' => '']);
		$data = $this->input->post();
		if ($data['asset_location'] != '' || $data['model_id'] != '' || (isset($data['asset_id']) && $data['asset_id'] != '') || $data['checkin_checkout_status'] != '') {
			$query = '';
			$query .= 'select id, assets_name, series, type from ' . db_prefix() . 'fe_assets';

			$list_query = [];
			if ($data['asset_location'] != '') {
				$list_query[] = ' ((asset_location = ' . $data['asset_location'] . ' AND checkin_out = 1) OR (location_id = ' . $data['asset_location'] . ' AND checkin_out = 2))';
			}

			if ($data['model_id'] != '') {
				$list_query[] = 'model_id = ' . $data['model_id'];
			}

			if (isset($data['asset_id']) && $data['asset_id'] != '') {
				$list_id_asset = (is_array($data['asset_id']) ? implode(',', $data['asset_id']) : $data['asset_id']);
				$list_query[] = 'id in (' . $list_id_asset . ')';
			}

			if ($data['checkin_checkout_status'] != '') {
				$list_query[] = 'checkin_out = ' . $data['checkin_checkout_status'];
			}
			$list_query[] = 'type != "predefined_kit" and active = 1';
			$count = count($list_query);
			if ($count > 0) {
				$query .= ' where';
				foreach ($list_query as $key => $q) {
					$query .= ' ' . $q;
					if (($key + 1) < $count) {
						$query .= ' AND';
					}
				}
			}
			$data_asset = $this->fixed_equipment_model->data_query($query, true);
			$new_detailt = [];
			foreach ($data_asset as $key => $item) {
				$quantity = $this->get_quantity_asset_by_type($item['id'], $item['type']);
				$assets_name = '';
				if ($item['series'] != '' && $item['assets_name'] != '') {
					$assets_name = $item['series'] . ' - ' . $item['assets_name'];
				} elseif ($item['series'] == '' && $item['assets_name'] != '') {
					$assets_name = $item['assets_name'];
				} elseif ($item['series'] != '' && $item['assets_name'] == '') {
					$assets_name = $item['series'];
				}
				array_push($new_detailt, array(
					'id' => $item['id'],
					'item' => $assets_name,
					'type' => ucfirst($item['type']),
					'quantity' => $quantity
				));
			}
			$data_hanson = json_encode($new_detailt);
		}
		echo json_encode([
			'data_hanson' => $data_hanson,
			'success' => true
		]);
	}

	/**
	 * get quantity asset by type
	 * @return integer $quantity 
	 */
	public function get_quantity_asset_by_type($asset_id, $type)
	{
		$quantity = 0;
		switch ($type) {
			case 'accessory':
				$query = 'select quantity from ' . db_prefix() . 'fe_assets where id = ' . $asset_id;
				$data_asset = $this->fixed_equipment_model->data_query($query);
				if ($data_asset) {
					$quantity = $data_asset->quantity;
				}
				break;
			case 'consumable':
				$query = 'select quantity from ' . db_prefix() . 'fe_assets where id = ' . $asset_id;
				$data_asset = $this->fixed_equipment_model->data_query($query);
				if ($data_asset) {
					$quantity = $data_asset->quantity;
				}
				break;
			case 'component':
				$query = 'select quantity from ' . db_prefix() . 'fe_assets where id = ' . $asset_id;
				$data_asset = $this->fixed_equipment_model->data_query($query);
				if ($data_asset) {
					$quantity = $data_asset->quantity;
				}
				break;
			case 'license':
				$query = 'select seats from ' . db_prefix() . 'fe_assets where id = ' . $asset_id;
				$data_asset = $this->fixed_equipment_model->data_query($query);
				if ($data_asset) {
					$quantity = $data_asset->seats;
				}
				break;
			default:
				$quantity = 1;
				break;
		}
		return $quantity;
	}

	/**
	 * create audit request
	 */
	public function create_audit_request()
	{
		if ($this->input->post()) {
			$data =  $this->input->post();
			$staff_id = get_staff_user_id();
			$data['creator_id'] = $staff_id;
			$insert_id = $this->fixed_equipment_model->create_audit_request($data);
			if (is_numeric($insert_id)) {
				// Approve
				$rel_type = 'audit';
				$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
				$process = '';
				if ($check_proccess) {
					if ($check_proccess->choose_when_approving == 0) {
						$this->fixed_equipment_model->send_request_approve($insert_id, $rel_type, $staff_id);
						$process = 'not_choose';						
						set_alert('success', _l('fe_successful_submission_of_approval_request'));
					} else {
						$process = 'choose';
						set_alert('success', _l('fe_created_successfully'));
					}
				} else {
					// Auto checkout if not approve process
					// Change status
					$this->db->where('id', $insert_id);
					$this->db->update(db_prefix() . 'fe_audit_requests', ['status' => 1]);
					$process = 'no_proccess';
					set_alert('success', _l('fe_approved'));
				}
				// End Approve
				redirect(admin_url('fixed_equipment/view_audit_request/' . $insert_id . '?process=' . $process));
			} else {
				set_alert('danger', _l('fe_request_failed'));
			}
		}
		redirect(admin_url('fixed_equipment/audit_managements'));
	}

	/**
	 * create audit request
	 */
	public function view_audit_request($id)
	{
		$this->load->model('staff_model');
		$send_notify = $this->session->userdata("send_notify");
		$data['send_notify'] = 0;
		if ((isset($send_notify)) && $send_notify != '') {
			$data['send_notify'] = $send_notify;
			$this->session->unset_userdata("send_notify");
		}

		$title = '';
		$data['audit'] = $this->fixed_equipment_model->get_audits($id);
		if ($data['audit']) {
			$title = $data['audit']->title;
		}
		$data['title'] = $title;

		$audit_detail = $this->fixed_equipment_model->get_audit_detail_by_master($id);

		$new_detailt = [];
		foreach ($audit_detail as $key => $item) {
			array_push($new_detailt, array(
				'id' => $item['asset_id'],
				'item' => $item['asset_name'],
				'type' => $item['type'],
				'quantity' => $item['quantity'],
				'maintenance' => $item['maintenance']
			));
		}
		$data['data_hanson'] = $new_detailt;
		$data['staffs'] = $this->staff_model->get();
		$data['data_approve'] = $this->fixed_equipment_model->get_approval_details($id, 'audit');

		$data['id'] = $id;

		$rel_type = 'audit';
		$process = '';
		$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
		if ($check_proccess) {
			if ($check_proccess->choose_when_approving == 0) {
				$process = 'not_choose';
			} else {
				$process = 'choose';
			}
		} else {
			$process = 'no_proccess';
		}
		$data['process'] = $process;
		$this->load->model('currencies_model');
		$this->load->model('staff_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$this->load->view('view_audit_managements', $data);
	}
	/**
	 * audit managements table
	 */
	public function audit_managements_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$current_user = get_staff_user_id();
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_audit_requests';
				$join         = [];

				$auditor = $this->input->post("auditor");
				$status = $this->input->post("status");
				$audit_from_date = $this->input->post("audit_from_date");
				$audit_to_date = $this->input->post("audit_to_date");

				if (has_permission('fixed_equipment_audit', '', 'view') || is_admin()) {
					if (isset($auditor) && $auditor != '') {
						$list_auditor = (is_array($auditor) ? implode(',', $auditor) : '');
						if ($list_auditor != '') {
							array_push($where, 'AND auditor IN (' . $list_auditor . ')');
						}
					}
				} else {
					array_push($where, 'AND auditor = ' . $current_user);
				}

				if ($status != '') {
					if ($status == 3) {
						$status = 0;
					}
					array_push($where, 'AND status = ' . $status);
				}

				if ($audit_from_date != '' && $audit_to_date != '') {
					$from_date = fe_format_date($audit_from_date);
					$to_date = fe_format_date($audit_to_date);
					array_push($where, 'AND (date(audit_date) between "' . $from_date . '" AND "' . $to_date . '")');
				}

				if ($audit_from_date == '' && $audit_to_date != '') {
					$to_date = fe_format_date($audit_to_date);
					array_push($where, 'AND date(audit_date) = "' . $to_date . '"');
				}

				if ($audit_from_date != '' && $audit_to_date == '') {
					$from_date = fe_format_date($audit_from_date);
					array_push($where, 'AND date(audit_date) = "' . $from_date . '"');
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'title',
					'audit_date',
					'auditor',
					'asset_location',
					'model_id',
					'asset_id',
					'checkin_checkout_status',
					'status',
					'date_creator'
				]);

				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$_data = '';
					$name_s = '<a href="' . admin_url('fixed_equipment/audit/' . $aRow['id']) . '" >' . $aRow['title'] . '</a>';



					$status_text = '';
					$status = $aRow['status'];
					switch ($status) {
						case 0:
							$status_text = '<span class="label label-primary">' . _l('fe_new') . '</span>';
							break;
						case 1:
							$status_text = '<span class="label label-success">' . _l('fe_approved') . '</span>';
							break;
						case 2:
							$status_text = '<span class="label label-danger">' . _l('fe_rejected') . '</span>';
							break;
					}


					$_data .= '<div class="row-options">';
					if ($status == 1) {
						$_data .= ' <a href="' . admin_url('fixed_equipment/audit/' . $aRow['id']) . '" class="text-success">' . _l('fe_detail') . '</a>';
					} else {
						$_data .= ' <a href="' . admin_url('fixed_equipment/view_audit_request/' . $aRow['id']) . '" class="text-primary">' . _l('fe_detail') . '</a>';
					}

					if (($status == 0 || $status == 2) && (is_admin() || has_permission('fixed_equipment_audit', '', 'delete'))) {
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_audit_request/' . $aRow['id']) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}



					$_data .= '</div>';

					$row[] = $name_s . $_data;
					$row[] = get_staff_full_name($aRow['auditor']);
					$row[] = _d($aRow['audit_date']);



					$row[] = $status_text;

					$row[] = _d($aRow['date_creator']);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * approve request form audit
	 * @return json 
	 */
	public function approve_request_form_audit()
	{
		$data = $this->input->post();
		$data['date'] = date('Y-m-d H:i:s');
		$data['staffid'] = get_staff_user_id();
		$success = $this->fixed_equipment_model->change_approve_audit($data);
		$message = '';
		if ($success == true) {
			if ($data['approve'] == 1) {
				$message = _l('fe_approved');
			} else {
				$message = _l('fe_rejected');
			}
		} else {
			$message = _l('fe_approve_fail');
		}
		echo json_encode([
			'success' => $success,
			'message' => $message,
		]);
		die();
	}

	/**
	 * choose approver request audit
	 * @return json 
	 */
	public function choose_approver_request_audit()
	{
		$data = $this->input->post();
		$success = false;
		$message = '';
		if ($data['id']) {
			$insert_id = $this->fixed_equipment_model->add_approver_choosee_when_approve_audit($data['id'], 'audit', $data['approver']);
			if (is_numeric($insert_id) && $insert_id > 0) {
				$success = true;
				$message = _l('fe_successful_submission_of_approval_request');
			} else {
				$success = false;
				$message = _l('fe_submit_approval_request_failed');
			}
		}
		echo json_encode([
			'success' => $success,
			'message' => $message
		]);
	}


	/**
	 * delete audit request
	 * @param  integer $id 
	 */
	public function delete_audit_request($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_audit_request($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_audit_request')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_audit_request')));
			}
		}
		redirect(admin_url('fixed_equipment/audit_managements'));
	}

	/**
	 * audit
	 */
	public function audit($id)
	{
		$this->load->model('staff_model');
		$title = '';
		$data['audit'] = $this->fixed_equipment_model->get_audits($id);
		if ($data['audit']) {
			if ($data['audit']->status != 1) {
				redirect(admin_url('fixed_equipment/audit_managements'));
			}
			$title = $data['audit']->title;
		} else {
			redirect(admin_url('fixed_equipment/audit_managements'));
		}
		$data['title'] = $title;

		$audit_detail = $this->fixed_equipment_model->get_audit_detail_by_master($id);

		$new_detailt = [];
		foreach ($audit_detail as $key => $item) {
			array_push($new_detailt, array(
				'id' => $item['asset_id'],
				'item' => $item['asset_name'],
				'type' => $item['type'],
				'quantity' => $item['quantity'],
				'adjust' => $item['adjusted'],
				'maintenance' => $item['maintenance'],
				'accept' => (int)$item['accept']
			));
		}
		$data['data_hanson'] = $new_detailt;
		$data['staffs'] = $this->staff_model->get();

		$approve_audit = $this->fixed_equipment_model->get_approval_details($id, 'audit');

		$data['data_approve'] = $this->fixed_equipment_model->get_approval_details($id, 'close_audit');
		$current_user_id = get_staff_user_id();

		$data['is_approver'] = false;
		foreach ($data['data_approve'] as $key => $staff) {
			if ($current_user_id == $staff['staffid']) {
				$data['is_approver'] = true;
				break;
			}
		}
		$data['is_auditor'] = false;
		if ($data['audit']->auditor == $current_user_id) {
			$data['is_auditor'] = true;
		}

		$rel_type = 'audit';
		$process = '';
		$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
		if ($check_proccess) {
			if ($check_proccess->choose_when_approving == 0) {
				$process = 'not_choose';
			} else {
				$process = 'choose';
			}
		} else {
			$data['is_approver'] = true;
			$process = 'no_proccess';
		}
		$data['process'] = $process;
		$data['id'] = $id;
		$this->load->view('audit', $data);
	}

	/**
	 * create audit request
	 */
	public function close_audit_request()
	{
		if ($this->input->post()) {
			$data =  $this->input->post();
			$rel_id = $data['id'];

			$res = $this->fixed_equipment_model->update_audit_request($data);
			if ($res) {
				// Approve
				$staff_id = get_staff_user_id();
				$rel_type = 'audit';
				$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
				$process = '';
				if ($check_proccess) {
					if ($check_proccess->choose_when_approving == 0) {
						$this->fixed_equipment_model->send_request_approve_close_audit($rel_id, 'close_audit', $staff_id);
						// Update status to waiting approve
						$this->db->where('id', $rel_id);
						$this->db->update(db_prefix() . 'fe_audit_requests', ['closed' => 4]);
						// End update status to waiting approve
						$process = 'not_choose';
						set_alert('success', _l('fe_successful_submission_of_approval_request'));
					} else {
						$process = 'choose';
						set_alert('success', _l('fe_created_successfully'));
					}
				} else {
					// Auto checkout if not approve process
					// Change status
					$this->db->where('id', $rel_id);
					$this->db->update(db_prefix() . 'fe_audit_requests', ['closed' => 1]);
					$data_hanson = (isset($data['assets_detailt']) ? json_decode($data['assets_detailt']) : []);
					// Change asset quantity after close audit 
					$this->fixed_equipment_model->update_asset_quantity_close_audit($data_hanson, $rel_id);
					$process = 'no_proccess';
					set_alert('success', _l('fe_approved'));
				}
				// End Approve
				redirect(admin_url('fixed_equipment/audit/' . $rel_id . '?process=' . $process));
			} else {
				set_alert('danger', _l('fe_request_failed'));
			}
		}
		redirect(admin_url('fixed_equipment/audit_managements'));
	}

	/**
	 * approve request close audit
	 * @return json 
	 */
	public function approve_request_close_audit()
	{
		$data = $this->input->post();
		$data['date'] = date('Y-m-d H:i:s');
		$data['staffid'] = get_staff_user_id();
		$success = $this->fixed_equipment_model->change_approve_close_audit($data);
		$message = '';
		if ($success == true) {
			if ($data['approve'] == 1) {
				$message = _l('fe_approved');
			} else {
				$message = _l('fe_rejected');
			}
		} else {
			$message = _l('fe_approve_fail');
		}
		echo json_encode([
			'success' => $success,
			'message' => $message,
		]);
		die();
	}

	/**
	 * choose approver request close audit
	 * @return json 
	 */
	public function choose_approver_request_close_audit()
	{
		$data = $this->input->post();
		$success = false;
		$message = '';
		if ($data['id']) {
			$insert_id = $this->fixed_equipment_model->add_approver_choosee_when_close_audit($data['id'], 'close_audit', $data['approver']);
			if (is_numeric($insert_id) && $insert_id > 0) {
				// Update status to waiting approve
				$this->db->where('id', $data['id']);
				$this->db->update(db_prefix() . 'fe_audit_requests', ['closed' => 4]);
				// End update status to waiting approve
				$success = true;
				$message = _l('fe_successful_submission_of_approval_request');
			} else {
				$success = false;
				$message = _l('fe_submit_approval_request_failed');
			}
		}
		echo json_encode([
			'success' => $success,
			'message' => $message
		]);
	}

	/**
	 * report
	 * @return  
	 */
	public function report()
	{
		if (!(has_permission('fixed_equipment_report', '', 'view_own') || has_permission('fixed_equipment_report', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->load->model('staff_model');
		$data['title'] = _l('fe_report');
		$data['staffs'] = $this->staff_model->get();
		$this->load->view('report', $data);
	}
	/**
	 * table activity dashboard
	 */
	public function table_activity_dashboard()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('staff_model');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];

				if (!(has_permission('fixed_equipment_dashboard', '', 'view') || is_admin())) {
					array_push($where, 'AND admin_id = ' . get_staff_user_id());
				}


				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_log_assets';
				$join         = [];


				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'admin_id',
					'action',
					'target',
					'changed',
					db_prefix() . 'fe_log_assets.to',
					'to_id',
					'notes',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = _dt($aRow['date_creator']);
					$row[] = get_staff_full_name($aRow['admin_id']);
					$row[] = _l('fe_' . $aRow['action']);

					$target = '';
					switch ($aRow['to']) {
						case 'user':
							$department_name = '';
							$data_staff_department = $this->departments_model->get_staff_departments($aRow['to_id']);
							if ($data_staff_department) {
								foreach ($data_staff_department as $key => $staff_department) {
									$department_name .= $staff_department['name'] . ', ';
								}
								if ($department_name != '') {
									$department_name = '(' . rtrim($department_name, ', ') . ') ';
								}
							}
							$head = '';
							$tail = '';
							if (fe_get_status_modules('hr_profile')) {
								$head = '<a href="' . admin_url('hr_profile/member/' . $aRow['to_id'] . '/profile') . '" target="_blank">';
								$tail = '</a>';
							}
							$target = $head . '<i class="fa fa-user"></i> ' . $department_name . '' . get_staff_full_name($aRow['to_id']) . $tail;
							break;
						case 'asset':
							$data_assets = $this->fixed_equipment_model->get_assets($aRow['to_id']);
							if ($data_assets) {
								$target = '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow['to_id'] . '?tab=details') . '" target="_blank"><i class="fa fa-barcode"></i> ' . $data_assets->series . ' ' . $data_assets->assets_name . '</a>';
							}
							break;
						case 'location':
							$data_locations = $this->fixed_equipment_model->get_locations($aRow['to_id']);
							if ($data_locations) {
								$target = '<a href="' . admin_url('fixed_equipment/detail_locations/' . $aRow['to_id']) . '" target="_blank"><i class="fa fa-map-marker"></i> ' . $data_locations->location_name . '</a>';
							}
							break;
					}

					$row[] = $target;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * table activity report
	 */
	public function table_activity_report()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('staff_model');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];


				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_log_assets';
				$join         = [];

				$filter_date = $this->fixed_equipment_model->from_to_date_report();
				if ($filter_date->from_date != '' && $filter_date->to_date != '') {
					array_push($where, 'AND (date(date_creator) between "' . $filter_date->from_date . '" AND "' . $filter_date->to_date . '")');
				}

				if ($filter_date->from_date == '' && $filter_date->to_date != '') {
					array_push($where, 'AND date(date_creator) = "' . $filter_date->to_date . '"');
				}

				if ($filter_date->from_date != '' && $filter_date->to_date == '') {
					array_push($where, 'AND date(date_creator) = "' . $filter_date->from_date . '"');
				}

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'admin_id',
					'action',
					'target',
					'changed',
					db_prefix() . 'fe_log_assets.to',
					'to_id',
					'notes',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = _dt($aRow['date_creator']);
					$row[] = get_staff_full_name($aRow['admin_id']);
					$row[] = _l('fe_' . $aRow['action']);

					$target = '';
					switch ($aRow['to']) {
						case 'user':
							$department_name = '';
							$data_staff_department = $this->departments_model->get_staff_departments($aRow['to_id']);
							if ($data_staff_department) {
								foreach ($data_staff_department as $key => $staff_department) {
									$department_name .= $staff_department['name'] . ', ';
								}
								if ($department_name != '') {
									$department_name = '(' . rtrim($department_name, ', ') . ') ';
								}
							}
							$target = '<i class="fa fa-user"></i> ' . $department_name . '' . get_staff_full_name($aRow['to_id']);
							break;
						case 'asset':
							$data_assets = $this->fixed_equipment_model->get_assets($aRow['to_id']);
							if ($data_assets) {
								$target = '<i class="fa fa-barcode"></i> (' . $data_assets->qr_code . ') ' . $data_assets->assets_name;
							}
							break;
						case 'location':
							$data_locations = $this->fixed_equipment_model->get_locations($aRow['to_id']);
							if ($data_locations) {
								$target = '<i class="fa fa-map-marker"></i> ' . $data_locations->location_name;
							}
							break;
						case 'project':
							$data_projects = $this->fixed_equipment_model->get_projects($aRow['to_id']);
							if ($data_projects) {
								$target = '<i class="fa-solid fa-chart-gantt"></i> ' . $data_projects->name;
							}
							break;
					}

					$row[] = $target;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * table unaccepted assets report
	 * @return json 
	 */
	public function table_unaccepted_assets_report()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_assets ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_checkin_assets.item_id'];

				$checkout_for = $this->input->post("checkout_for");

				if (has_permission('fixed_equipment_report', '', 'view') || is_admin()) {
					if (isset($checkout_for) && $checkout_for != '') {
						$list_checkout_for = (is_array($checkout_for) ? implode(',', $checkout_for) : '');
						if ($list_checkout_for != '') {
							array_push($where, 'AND staff_id IN (' . $list_checkout_for . ')');
						}
					}
				} else {
					array_push($where, 'AND staff_id = ' . get_staff_user_id() . '');
				}


				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.request_status = 2');

				$filter_date = $this->fixed_equipment_model->from_to_date_report();
				if ($filter_date->from_date != '' && $filter_date->to_date != '') {
					array_push($where, 'AND (date(' . db_prefix() . 'fe_checkin_assets.date_creator) between "' . $filter_date->from_date . '" AND "' . $filter_date->to_date . '")');
				}

				if ($filter_date->from_date == '' && $filter_date->to_date != '') {
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $filter_date->to_date . '"');
				}

				if ($filter_date->from_date != '' && $filter_date->to_date == '') {
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $filter_date->from_date . '"');
				}

				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.type = "checkout"');
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.requestable = 1');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_assets.assets_name',
					db_prefix() . 'fe_assets.series',
					db_prefix() . 'fe_assets.model_id',
					'request_title',
					'request_status',
					'staff_id',
					'checkout_to',
					'notes',
					db_prefix() . 'fe_checkin_assets.date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$_data = '';


					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_request/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					$_data .= '</div>';

					$row[] = $aRow['request_title'] . $_data;
					$row[] = $aRow['assets_name'];
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';
					$row[] = $aRow['series'];
					$row[] = get_staff_full_name($aRow['staff_id']);
					$row[] = $aRow['notes'];
					$row[] = _dt($aRow['date_creator']);
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * table inventory report report
	 * @return json 
	 */
	public function table_inventory_report_report()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_assets ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_checkin_assets.item_id'];

				$checkout_for = $this->input->post("checkout_for");



				if (isset($checkout_for) && $checkout_for != '') {
					$list_checkout_for = (is_array($checkout_for) ? implode(',', $checkout_for) : '');
					if ($list_checkout_for != '') {
						array_push($where, 'AND staff_id IN (' . $list_checkout_for . ')');
					}
				}
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.request_status = 2');

				$filter_date = $this->fixed_equipment_model->from_to_date_report();
				if ($filter_date->from_date != '' && $filter_date->to_date != '') {
					array_push($where, 'AND (date(' . db_prefix() . 'fe_checkin_assets.date_creator) between "' . $filter_date->from_date . '" AND "' . $filter_date->to_date . '")');
				}

				if ($filter_date->from_date == '' && $filter_date->to_date != '') {
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $filter_date->to_date . '"');
				}

				if ($filter_date->from_date != '' && $filter_date->to_date == '') {
					array_push($where, 'AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $filter_date->from_date . '"');
				}
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.type = "checkout"');
				array_push($where, 'AND ' . db_prefix() . 'fe_checkin_assets.requestable = 1');

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_assets.assets_name',
					db_prefix() . 'fe_assets.series',
					db_prefix() . 'fe_assets.model_id',
					'request_title',
					'request_status',
					'staff_id',
					'checkout_to',
					'notes',
					db_prefix() . 'fe_checkin_assets.date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$_data = '';


					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_request/' . $aRow['id']) . '">' . _l('fe_view') . '</a>';
					$_data .= '</div>';

					$row[] = $aRow['request_title'] . $_data;
					$row[] = $aRow['assets_name'];
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';
					$row[] = $aRow['series'];
					$row[] = get_staff_full_name($aRow['staff_id']);
					$row[] = $aRow['notes'];
					$row[] = _dt($aRow['date_creator']);
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * dashboard
	 */
	public function dashboard()
	{
		if (!(has_permission('fixed_equipment_dashboard', '', 'view_own') || has_permission('fixed_equipment_dashboard', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$data_asset_by_status = [];
		$data_status = $this->fixed_equipment_model->get_status_labels();
		$count_total_asset = $this->fixed_equipment_model->count_total_assets('asset');
		if ($count_total_asset > 0) {
			foreach ($data_status as $status) {
				$count_result = 0;
				$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets where status = ' . $status['id'] . ' and type = "asset" and active = 1';
				$data_query = $this->fixed_equipment_model->data_query($query);
				if ($data_query) {
					$count_result = $data_query->count;
				}
				$ratio = ($count_result * 100) / $count_total_asset;
				$data_asset_by_status[] = ['name' => $status['name'], 'y' => round($ratio, 2), 'drilldown' => $status['name'], 'color' => $status['chart_color']];
			}
		}
		$data['asset_by_status'] = json_encode($data_asset_by_status);
		$data['title'] = _l('fe_fixed_equipment');
		$this->load->view('dashboard', $data);
	}

	/**
	 * table asset categories dashboard
	 */
	public function table_asset_categories_dashboard()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];


				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_categories';
				$join         = [];



				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'category_name',
					'type'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];

					$row[] = $aRow['category_name'];
					$row[] = _l('fe_' . $aRow['type']);

					$assets = 0;
					$license = 0;
					$accessory = 0;
					$consumable = 0;
					$component = 0;
					switch ($aRow['type']) {
						case 'asset':
							$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets left join ' . db_prefix() . 'fe_models on ' . db_prefix() . 'fe_models.id = ' . db_prefix() . 'fe_assets.model_id where ' . db_prefix() . 'fe_models.category = ' . $aRow['id'] . ' and active = 1';
							$result_query = $this->fixed_equipment_model->data_query($query);
							if ($result_query) {
								$assets = $result_query->count;
							}
							break;
						case 'license':
							$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets where category_id = ' . $aRow['id'] . ' and active = 1';
							$result_query = $this->fixed_equipment_model->data_query($query);
							if ($result_query) {
								$license = $result_query->count;
							}
							break;
						case 'accessory':
							$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets where category_id = ' . $aRow['id'] . ' and active = 1';
							$result_query = $this->fixed_equipment_model->data_query($query);
							if ($result_query) {
								$accessory = $result_query->count;
							}
							break;
						case 'consumable':
							$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets where category_id = ' . $aRow['id'] . ' and active = 1';
							$result_query = $this->fixed_equipment_model->data_query($query);
							if ($result_query) {
								$consumable = $result_query->count;
							}
							break;
						case 'component':
							$query = 'select count(1) as count from ' . db_prefix() . 'fe_assets where category_id = ' . $aRow['id'] . ' and active = 1';
							$result_query = $this->fixed_equipment_model->data_query($query);
							if ($result_query) {
								$component = $result_query->count;
							}
							break;
					}


					$row[] = $assets;
					$row[] = $license;
					$row[] = $accessory;
					$row[] = $consumable;
					$row[] = $component;

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * depreciations
	 * @return  
	 */
	public function depreciations()
	{
		if (!(has_permission('fixed_equipment_depreciations', '', 'view_own') || has_permission('fixed_equipment_depreciations', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$this->fixed_equipment_model->auto_calculate_depreciation();
		$this->load->model('staff_model');
		$data['title'] = _l('fe_depreciations');
		$data['staffs'] = $this->staff_model->get();
		$data['status_labels'] = $this->fixed_equipment_model->get_status_labels();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$this->load->view('depreciations_management', $data);
	}

	/**
	 * depreciation table
	 * @return json 
	 */
	public function depreciation_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$asset = $this->input->post("asset");
				$status = $this->input->post("status");
				$month = $this->input->post("month");
				$current_date = $month . '-01';
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name_s = '';
				if (isset($base_currency)) {
					$currency_name_s = $base_currency->name;
				}

				$select = [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description'
				];



				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = ['LEFT JOIN ' . db_prefix() . 'fe_models ON ' . db_prefix() . 'fe_models.id = ' . db_prefix() . 'fe_assets.model_id'];

				$list_asset_id = $this->fixed_equipment_model->get_list_asset_id_has_depreciations();
				if (count($list_asset_id) > 0) {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.id in (' . implode(',', $list_asset_id) . ')');
				} else {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.id = 0');
				}

				if ($asset != '') {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.id in (' . implode(',', $asset) . ')');
				}
				if ($status != '') {
					array_push($where, 'AND status = ' . $status);
				}

				if (!is_admin()) {
					array_push($where, 'AND requestable = 1');
				}
				array_push($where, 'AND date_buy != \'\' AND date_buy is not null');
				array_push($where, 'AND unit_price != \'\' AND unit_price is not null');


				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description',
					'requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					'status',
					db_prefix() . 'fe_models.model_name',
					db_prefix() . 'fe_models.model_no',
					'type'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$allow_add = false;

					$eol = 0;
					$depreciation_name = '';
					$depreciation_value = '';
					if ($aRow['type'] == 'asset') {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$eol = _d(get_expired_date($aRow['date_buy'], $data_model->eol));
							if (is_numeric($data_model->depreciation) && $data_model->depreciation > 0) {
								$data_depreciation = $this->fixed_equipment_model->get_depreciations($data_model->depreciation);
								if ($data_depreciation && $aRow['unit_price'] != '' && $aRow['unit_price'] != 0 && $aRow['unit_price'] != null) {
									$allow_add = true;
									$depreciation_name = $data_depreciation->name;
									$depreciation_value = $data_depreciation->term;
								}
							}
						}
					}

					if ($aRow['type'] == 'license') {
						if (is_numeric($aRow['depreciation']) && $aRow['depreciation'] > 0) {
							$data_depreciation = $this->fixed_equipment_model->get_depreciations($aRow['depreciation']);
							if ($data_depreciation && $aRow['unit_price'] != '' && $aRow['unit_price'] != 0 && $aRow['unit_price'] != null) {
								$allow_add = true;
								$depreciation_name = $data_depreciation->name;
								$depreciation_value = $data_depreciation->term;
							}
						}
					}

					$monthly_depreciation = 0;
					$diff = 0;

					if ($aRow['date_buy'] != '' && $aRow['date_buy'] != null) {
						$depreciation_result = $this->fixed_equipment_model->get_depreciation_item_info($aRow['id'], $aRow['date_buy'], $current_date);
						if ($depreciation_result) {
							$monthly_depreciation = $depreciation_result->current_depreciation;
							$diff = $depreciation_result->diff;
						}
					}

					if ($allow_add && $monthly_depreciation > 0) {

						$row[] = $aRow['id'];

						$_data = '';

						$_data .= '<div class="row-options">';

						$_data .= '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow[db_prefix() . 'fe_assets.id'] . '?tab=details&re=depreciations') . '">' . _l('fe_view') . '</a>';

						$_data .= '</div>';

						$row[] = '<span class="text-nowrap">' . $aRow['assets_name'] . '</span>' . $_data;

						$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';

						$row[] = $aRow['series'];

						$row[] = '<span class="text-nowrap">' . $depreciation_name . '</span>';

						$row[] = $depreciation_value;

						$status = '';

						$status_name = '';

						if (is_numeric($aRow['status']) && $aRow['status'] > 0) {
							$data_status = $this->fixed_equipment_model->get_status_labels($aRow['status']);
							if ($data_status) {
								$status = $data_status->status_type;
								if ($aRow['checkin_out'] == 2 && $status == 'deployable') {
									$status = 'deployed';
								}
								$status_name = '<div class="row text-nowrap pleft15 pright15"><span style="color:' . $data_status->chart_color . '">' . $data_status->name . '</span><span class="mleft10 label label-primary">' . _l('fe_' . $status) . '</span></div>';
							}
						}

						$row[] = $status_name;

						$data_location_info = $this->fixed_equipment_model->get_asset_location_info($aRow[db_prefix() . 'fe_assets.id']);
						$row[] = '<span class="text-nowrap">' . $data_location_info->curent_location . '</span>';
						$row[] = $aRow['date_buy'] != '' ? '<span class="text-nowrap">' . _d($aRow['date_buy']) . '</span>' : '';

						$row[] = '<span class="text-nowrap">' . $eol . '</span>';

						$cost = ($aRow['unit_price'] != '' && $aRow['unit_price'] != null) ? $aRow['unit_price'] : 0;

						$row[] = '<span class="text-primary">' . ($aRow['unit_price'] != '' ? '<span class="text-nowrap">' . app_format_money($cost, $currency_name_s) . '</span>' : '') . '</span>';

						$maintenance_cost = 0;

						$maintenance_cost = $this->fixed_equipment_model->get_maintenance_cost_by_date($aRow['id'], $aRow['date_buy'], $current_date);

						$currency_val = $cost + $maintenance_cost - $diff;

						$row[] = '<span class="text-info">' . app_format_money(round($maintenance_cost, 2), $currency_name_s) . '</span>';

						$row[] = '<span class="text-success">' . app_format_money(round($currency_val, 2), $currency_name_s) . '</span>';

						$row[] = '<span class="text-danger">' . app_format_money(round($monthly_depreciation, 2), $currency_name_s) . '</span>';

						$row[] = '<span class="text-warning">' . app_format_money(round($diff, 2), $currency_name_s) . '</span>';

						$output['aaData'][] = $row;
					}
				}
				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * location
	 */
	public function locations()
	{
		if (!(has_permission('fixed_equipment_locations', '', 'view_own') || has_permission('fixed_equipment_locations', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		$data['title']    = _l('fe_location_management');
		$this->load->model('staff_model');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['staffs'] = $this->staff_model->get();
		$this->load->model('currencies_model');
		$data['currencies'] = $this->currencies_model->get();
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$this->load->view('location_management', $data);
	}
	/**
	 * detail locations
	 * @param  integer $id
	 */
	public function detail_locations($id)
	{
		if (!(has_permission('fixed_equipment_locations', '', 'view_own') || has_permission('fixed_equipment_locations', '', 'view') || is_admin())) {
			access_denied('fe_fixed_equipment');
		}
		if (!isset($id) || $id == '') {
			redirect(admin_url('fixed_equipment/dashboard'));
		}
		$data['redirect'] = $this->input->get('re');
		$data['title']    = '';
		$this->load->model('staff_model');
		$data['location'] = $this->fixed_equipment_model->get_locations($id);
		if ($data['location']) {
			$data['title'] = $data['location']->location_name;
		}
		$data['id'] = $id;
		$this->load->model('currencies_model');
		$data['currencies'] = $this->currencies_model->get();
		$data['base_currency'] = $this->currencies_model->get_base_currency();


		$data['models'] = $this->fixed_equipment_model->get_models();
		$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
		$data['status_labels'] = $this->fixed_equipment_model->get_status_labels();
		$data['status_label_checkout'] = $this->fixed_equipment_model->get_status_labels('', 'deployable');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset');
		$data['staffs'] = $this->staff_model->get();

		$data['accessories_categories'] = $this->fixed_equipment_model->get_categories('', 'accessory');
		$data['consumable_categories'] = $this->fixed_equipment_model->get_categories('', 'consumable');
		$data['component_categories'] = $this->fixed_equipment_model->get_categories('', 'component');

		$data['manufacturers'] = $this->fixed_equipment_model->get_asset_manufacturers();

		$this->load->view('detail_locations', $data);
	}

	/**
	 * asset location table
	 * @return json 
	 */
	public function asset_location_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {

				$id = $this->input->post("id");
				$model = $this->input->post("model");
				$status = $this->input->post("status");
				$supplier = $this->input->post("supplier");
				$location = $this->input->post("location");

				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',

					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description',
					db_prefix() . 'fe_assets.requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					db_prefix() . 'fe_assets.status',

					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
					db_prefix() . 'fe_assets.date_creator',
				];



				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_models ON ' . db_prefix() . 'fe_models.id = ' . db_prefix() . 'fe_assets.model_id',
					'LEFT JOIN ' . db_prefix() . 'fe_checkin_assets ON ' . db_prefix() . 'fe_checkin_assets.id = ' . db_prefix() . 'fe_assets.checkin_out_id',
				];
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.type = "asset"');
				array_push($where, 'AND active = 1');

				if ($model != '') {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.model_id = ' . $model);
				}
				if ($status != '') {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.status = ' . $status);
				}
				if ($supplier != '') {
					array_push($where, 'AND supplier_id = ' . $supplier);
				}
				if ($id != '') {
					$child_location_data = $this->fixed_equipment_model->get_child_location_id($id);
					$child_location_id = ((count($child_location_data) > 0) ? implode(',',$child_location_data) : 0);
					array_push($where, 'AND ((' . db_prefix() . 'fe_assets.asset_location = ' . $id . ' AND ' . db_prefix() . 'fe_assets.checkin_out = 1) OR (' . db_prefix() . 'fe_assets.location_id = ' . $id . ' AND ' . db_prefix() . 'fe_assets.checkin_out = 2) OR (' . db_prefix() . 'fe_assets.location_id IN (' . $child_location_id . ')))');
					// array_push($where, 'AND ((' . db_prefix() . 'fe_assets.asset_location = ' . $id . ' AND ' . db_prefix() . 'fe_assets.checkin_out = 1) OR (' . db_prefix() . 'fe_assets.location_id = ' . $id . ' AND ' . db_prefix() . 'fe_assets.checkin_out = 2))');
				}

				if (!is_admin() || !has_permission('fixed_equipment_assets', '', 'view')) {
					array_push($where, 'AND ' . db_prefix() . 'fe_assets.requestable = 1');
				}
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					'assets_code',
					'assets_name',
					'series',
					'asset_group',
					'asset_location',
					'model_id',
					'date_buy',
					'warranty_period',
					'unit_price',
					db_prefix() . 'fe_assets.depreciation',
					'supplier_id',
					'order_number',
					'description',
					db_prefix() . 'fe_assets.requestable',
					'qr_code',
					db_prefix() . 'fe_assets.date_creator',
					'updated_at',
					'checkin_out',
					'checkin_out_id',
					db_prefix() . 'fe_models.model_name',
					db_prefix() . 'fe_models.model_no',
					db_prefix() . 'fe_assets.status'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];

					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_asset/' . $aRow[db_prefix() . 'fe_assets.id'] . '?tab=details&re=detail_locations/' . $id) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_assets', '', 'view')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit_assets_location(' . $aRow[db_prefix() . 'fe_assets.id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets_location/' . $aRow[db_prefix() . 'fe_assets.id'] . '/' . $id) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$row[] = '<span class="text-nowrap">' . $aRow['assets_name'] . '</span>' . $_data;

					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';

					$row[] = '<span class="text-nowrap">' . $aRow['series'] . '</span>';

					$category_id = 0;
					$manufacturer_id = 0;
					if (is_numeric($aRow['model_id']) > 0) {
						$data_model = $this->fixed_equipment_model->get_models($aRow['model_id']);
						if ($data_model) {
							$category_id = $data_model->category;
							$manufacturer_id = $data_model->manufacturer;
						}
					}
					$row[] = '<span class="text-nowrap">' . $aRow['model_name'] . '</span>';
					$row[] = $aRow['model_no'];

					$category_name = '';
					if (is_numeric($category_id) && $category_id > 0) {
						$data_cat = $this->fixed_equipment_model->get_categories($category_id);
						if ($data_cat) {
							$category_name = $data_cat->category_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $category_name . '</span>';

					$status = '';
					$status_name = '';
					if (is_numeric($aRow['status']) && $aRow['status'] > 0) {
						$data_status = $this->fixed_equipment_model->get_status_labels($aRow['status']);
						if ($data_status) {
							$status = $data_status->status_type;
							if ($aRow['checkin_out'] == 2 && $status == 'deployable') {
								$status = 'deployed';
							}
							$status_name = '<div class="row text-nowrap mleft5 mright5"><span style="color:' . $data_status->chart_color . '">' . $data_status->name . '</span><span class="mleft10 label label-primary">' . _l('fe_' . $status) . '</span></div>';
						}
					}
					$row[] = $status_name;





					$data_location_info = $this->fixed_equipment_model->get_asset_location_info($aRow[db_prefix() . 'fe_assets.id']);
					$checkout_to = '';
					$current_location = '';

					if ($data_location_info->checkout_to != '') {
						$icon_checkout_to = '';
						if ($data_location_info->checkout_type == 'location') {
							$icon_checkout_to = '<i class="fa fa-map-marker"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_locations/' . $data_location_info->to_id) . '?re=assets" class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'user') {
							$icon_checkout_to = '<i class="fa fa-user"></i>';
							$checkout_to = '<span class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</span>';
							$current_location = '';
						} elseif ($data_location_info->checkout_type == 'asset') {
							$icon_checkout_to = '<i class="fa fa-barcode"></i>';
							$checkout_to = '<a href="' . admin_url('fixed_equipment/detail_asset/' . $data_location_info->to_id . '?tab=details') . '" class="text-nowrap">' . $icon_checkout_to . ' ' . $data_location_info->checkout_to . '</a>';
							$current_location = '';
						}
					}
					$row[] = $checkout_to;
					$row[] = '<span class="text-nowrap">' . $data_location_info->curent_location . '</span>';
					$row[] = '<span class="text-nowrap">' . $data_location_info->default_location . '</span>';


					$manufacturer_name = '';
					if (is_numeric($manufacturer_id) && $manufacturer_id > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($manufacturer_id);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $manufacturer_name . '</span>';

					$supplier_name = '';
					if (is_numeric($aRow['supplier_id'])) {
						$data_supplier = $this->fixed_equipment_model->get_suppliers($aRow['supplier_id']);
						if ($data_supplier) {
							$supplier_name = $data_supplier->supplier_name;
						}
					}
					$row[] = '<span class="text-nowrap">' . $supplier_name . '</span>';

					$row[] = $aRow['date_buy'] != '' ? _d($aRow['date_buy']) : '';
					$row[] = $aRow['unit_price'] != '' ? app_format_money($aRow['unit_price'], $currency_name) : '';
					$row[] = $aRow['order_number'];
					$row[] = (($aRow['warranty_period'] != '' && $aRow['warranty_period'] != 0) ? '<span class="text-nowrap">' . $aRow['warranty_period'] . ' ' . _l('months') . '</span>' : '');
					$row[] = (($aRow['warranty_period'] != '' && $aRow['warranty_period'] != 0) ? _d(get_expired_date($aRow['date_buy'], $aRow['warranty_period'])) : '');
					$row[] = '<span class="text-nowrap">' . $aRow['description'] . '</span>';
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout', 0);
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkin');
					$row[] = $this->fixed_equipment_model->count_log_detail($aRow[db_prefix() . 'fe_assets.id'], 'checkout', 1, 1);
					$row[] = '<span class="text-nowrap">' . _dt($aRow['date_creator']) . '</span>';
					$row[] = '<span class="text-nowrap">' . _dt($aRow['updated_at']) . '</span>';
					$checkout_date = '';
					$expected_checkin_date = '';
					if ($aRow['checkin_out'] == 2) {
						if (is_numeric($aRow['checkin_out_id']) && $aRow['checkin_out_id'] > 0) {
							$data_checkout = $this->fixed_equipment_model->get_checkin_out_data($aRow['checkin_out_id']);
							if ($data_checkout) {
								$expected_checkin_date = (($data_checkout->expected_checkin_date != '' || $data_checkout->expected_checkin_date != null) ? _d($data_checkout->expected_checkin_date) : '');
								$checkout_date = (($data_checkout->checkin_date != '' || $data_checkout->checkin_date != null) ? _d($data_checkout->checkin_date) : _d(date('Y-m-d'), $data_checkout->date_creator));
							}
						}
					}

					$row[] = '<span class="text-nowrap">' . $checkout_date . '</span>';
					$row[] = '<span class="text-nowrap">' . $expected_checkin_date . '</span>';
					$last_audit = '';
					$next_audit = '';
					$data_audit = $this->fixed_equipment_model->get_2_audit_info_asset($aRow['id']);
					if ($data_audit) {
						if (isset($data_audit[0]) && isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
							$last_audit = _d(date('Y-m-d', strtotime($data_audit[1]['audit_date'])));
						}
						if (isset($data_audit[0]) && !isset($data_audit[1])) {
							$next_audit = _d(date('Y-m-d', strtotime($data_audit[0]['audit_date'])));
						}
					}
					$row[] = '<span class="text-nowrap">' . $last_audit . '</span>';
					$row[] = '<span class="text-nowrap">' . $next_audit . '</span>';

					$button = '';
					if (is_admin() || has_permission('fixed_equipment_assets', '', 'view')) {
						if ($aRow['checkin_out'] == 2) {
							$button = '<a class="btn btn-primary" data-asset_name="' . $aRow['assets_name'] . '" data-serial="' . $aRow['series'] . '" data-model="' . $aRow['model_name'] . '" onclick="check_in_asset(this, ' . $aRow[db_prefix() . 'fe_assets.id'] . ')" >' . _l('fe_checkin') . '</a>';
						} else {
							if ($status == 'deployable') {
								$button = '<a class="btn btn-danger" data-asset_name="' . $aRow['assets_name'] . '" data-serial="' . $aRow['series'] . '" data-model="' . $aRow['model_name'] . '" onclick="check_out_asset(this, ' . $aRow[db_prefix() . 'fe_assets.id'] . ')" >' . _l('fe_checkout') . '</a>';
							}
						}
					}

					$row[] = $button;
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * accessories location table
	 * @return json 
	 */
	public function accessories_location_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];

				if (is_admin() || has_permission('fixed_equipment_accessories', '', 'view')) {
					array_push($select, 'id');
				}

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [];
				$manufacturer = $this->input->post('manufacturer');
				$category = $this->input->post('category');
				$location = $this->input->post('location');

				if (isset($manufacturer) && $manufacturer != '') {
					array_push($where, 'AND manufacturer_id = ' . $manufacturer);
				}
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}
				array_push($where, 'AND type = "accessory"');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'assets_name',
					'category_id',
					'model_no',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'accessory') . '">';
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_accessories/' . $aRow['id'] . '?re=detail_locations/' . $location) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'view')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit_accessories_location(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets_location/' . $aRow['id'] . '/' . $location) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$min_quantity = $aRow['min_quantity'];
					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($aRow['id']);
					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name =  '<span class="text-nowrap">' . $data_category->category_name . '</span>';
						}
					}
					$row[] = $category_name;

					$row[] = $aRow['model_no'];

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer_id']) && $aRow['manufacturer_id'] > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer_id']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;
					$row[] = $aRow['quantity'];
					$row[] = $min_quantity;
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = app_format_money($aRow['unit_price'], $currency_name);

					if (is_admin() || has_permission('fixed_equipment_accessories', '', 'view')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out_accessory(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * consumables location table
	 * @return json 
	 */
	public function consumables_location_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}

				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];

				if (is_admin() || has_permission('fixed_equipment_consumables', '', 'view')) {
					array_push($select, 'id');
				}

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [];

				$manufacturer = $this->input->post('manufacturer');
				$category = $this->input->post('category');
				$location = $this->input->post('location');

				if (isset($manufacturer) && $manufacturer != '') {
					array_push($where, 'AND manufacturer_id = ' . $manufacturer);
				}
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}
				array_push($where, 'AND type = "consumable"');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'assets_name',
					'category_id',
					'model_no',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$row[] = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'consumable') . '">';
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_consumables/' . $aRow['id'] . '?re=detail_locations/' . $location) . '">' . _l('fe_view') . '</a>';
					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'view')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit_consumables_location(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets_location/' . $aRow['id'] . '/' . $location) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';

					$min_quantity = $aRow['min_quantity'];
					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($aRow['id']);
					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name = '<span class="text-nowrap">' . $data_category->category_name . '</span>';
						}
					}
					$row[] = $category_name;

					$row[] = $aRow['model_no'];

					$manufacturer_name = '';
					if (is_numeric($aRow['manufacturer_id']) && $aRow['manufacturer_id'] > 0) {
						$data_manufacturer = $this->fixed_equipment_model->get_asset_manufacturers($aRow['manufacturer_id']);
						if ($data_manufacturer) {
							$manufacturer_name = $data_manufacturer->name;
						}
					}
					$row[] = $manufacturer_name;

					$row[] = $aRow['quantity'];
					$row[] = $min_quantity;
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = app_format_money($aRow['unit_price'], $currency_name);

					if (is_admin() || has_permission('fixed_equipment_consumables', '', 'view')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out_consumable(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * components location table
	 * @return json 
	 */
	public function components_location_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				if (is_admin() || has_permission('fixed_equipment_components', '', 'view')) {
					array_push($select, 'id');
				}
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [];

				$category = $this->input->post('category');
				$location = $this->input->post('location');
				if (isset($category) && $category != '') {
					array_push($where, 'AND category_id = ' . $category);
				}
				if (isset($location) && $location != '') {
					array_push($where, 'AND asset_location = ' . $location);
				}

				array_push($where, 'AND type = "component"');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'assets_name',
					'category_id',
					'series',
					'manufacturer_id',
					'asset_location',
					'quantity',
					'min_quantity',
					'unit_price',
					'order_number',
					'date_buy',
					'checkin_out'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					$_data .= '<a href="' . admin_url('fixed_equipment/detail_components/' . $aRow['id'] . '?re=detail_locations/' . $location) . '">' . _l('fe_view') . '</a>';

					if (is_admin() || has_permission('fixed_equipment_components', '', 'view')) {
						$_data .= ' | <a href="javascript:void(0)" onclick="edit_component_location(' . $aRow['id'] . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
						$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_assets_location/' . $aRow['id'] . '/' . $location) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}

					$_data .= '</div>';
					$avail = $aRow['quantity'] - $this->fixed_equipment_model->count_checkin_component_by_parents($aRow['id']);
					$min_quantity = $aRow['min_quantity'];

					$warning_class = '';
					$warning_attribute = '';
					if ($avail < $min_quantity) {
						$warning_class = 'text-danger bold';
						$warning_attribute = 'data-toggle="tooltip" data-placement="top" data-original-title="' . _l('fe_the_quantity_has_reached_the_warning_level') . '"';
					}
					$row[] = '<span class="text-nowrap ' . $warning_class . '" ' . $warning_attribute . '>' . $aRow['assets_name'] . '</span>' . $_data;
					$row[] = $aRow['series'];

					$category_name = '';
					if (is_numeric($aRow['category_id']) && $aRow['category_id'] > 0) {
						$data_category = $this->fixed_equipment_model->get_categories($aRow['category_id']);
						if ($data_category) {
							$category_name = '<span class="text-nowrap">' . $data_category->category_name . '</span>';
						}
					}
					$row[] = $category_name;
					$remain = 0;
					$row[] = $aRow['quantity'];
					$row[] = '<span class="' . $warning_class . '" ' . $warning_attribute . '>' . $avail . '</span>';
					$row[] = $min_quantity;
					$row[] = $aRow['order_number'];
					$row[] = _d($aRow['date_buy']);
					$row[] = app_format_money($aRow['unit_price'], $currency_name);
					if (is_admin() || has_permission('fixed_equipment_components', '', 'view')) {
						if ($aRow['checkin_out'] == 1) {
							$event_add = ' disabled';
							if ($avail > 0) {
								$event_add = ' data-asset_name="' . $aRow['assets_name'] . '" onclick="check_out_component(this, ' . $aRow['id'] . ')"';
							}
							$row[] = '<a class="btn btn-danger"' . $event_add . '>' . _l('fe_checkout') . '</a>';
						}
					}
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * check in assets
	 * @return  
	 */
	public  function check_in_assets_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			$result = $this->fixed_equipment_model->check_in_assets($data);
			if ($result > 0) {
				if ($data['type'] == 'checkout') {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_assets')));
				} else {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_assets')));
				}
			} else {
				if ($data['type'] == 'checkout') {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_assets')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_assets')));
				}
			}
			redirect(admin_url('fixed_equipment/detail_locations/' . $location));
		}
	}


	/**
	 * check in accessories
	 * @return  
	 */
	public  function check_in_accessories_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			$result = $this->fixed_equipment_model->check_in_accessories($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_this_accessory_has_been_checkout_for_this_user', _l('fe_accessories')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_accessories')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_accessories')));
				}
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			} else {
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			}
		}
	}

	/**
	 * check in consumables
	 * @return  
	 */
	public  function check_in_consumables_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			$result = $this->fixed_equipment_model->check_in_consumables($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_this_consumables_has_been_checkout_for_this_user', _l('fe_consumables')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_consumables')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_consumables')));
				}
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_consumables')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_consumables')));
				}
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			}
		}
	}

	/**
	 * check in components
	 * @return  
	 */
	public  function check_in_components_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			$result = $this->fixed_equipment_model->check_in_components($data);
			if (is_numeric($result)) {
				if ($result == -1) {
					set_alert('danger', _l('fe_this_component_has_been_checkout_for_this_asset', _l('fe_components')));
				} elseif ($result == 0) {
					set_alert('danger', _l('fe_checkout_fail', _l('fe_components')));
				} else {
					set_alert('success', _l('fe_checkout_successfully', _l('fe_components')));
				}
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			} else {
				if ($result == true) {
					set_alert('success', _l('fe_checkin_successfully', _l('fe_components')));
				} else {
					set_alert('danger', _l('fe_checkin_fail', _l('fe_components')));
				}
				redirect(admin_url('fixed_equipment/detail_locations/' . $location));
			}
		}
	}

	/**
	 * other setting
	 */
	public function other_setting()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$affected_row = 0;
			if (isset($data['fe_googlemap_api_key'])) {
				$res = update_option('fe_googlemap_api_key', $data['fe_googlemap_api_key']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_show_public_page'])) {
				$res = update_option('fe_show_public_page', $data['fe_show_public_page']);
				if ($res) {
					$affected_row++;
				}
			} else {
				$res = update_option('fe_show_public_page', 0);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_show_customer_asset'])) {
				$res = update_option('fe_show_customer_asset', $data['fe_show_customer_asset']);
				if ($res) {
					$affected_row++;
				}
			} else {
				$res = update_option('fe_show_customer_asset', 0);
				if ($res) {
					$affected_row++;
				}
			}

			if ($affected_row > 0) {
				set_alert('success', _l('fe_saved_successfully', _l('fe_settings')));
			} else {
				set_alert('danger', _l('fe_save_fail', _l('fe_settings')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=other_setting'));
	}

	/**
	 * delete assets location
	 */
	public function delete_assets_location($id, $location_id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assets($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_assets')));
			}
		}
		redirect(admin_url('fixed_equipment/detail_locations/' . $location_id));
	}

	/**
	 * update asset location
	 */
	public function update_asset_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			if ($this->input->post('id')) {
				$id = $data['id'];
				unset($data['id']);
				$success = $this->fixed_equipment_model->update_asset($data, $id);
				if ($success) {
					$message = _l('fe_updated_successfully', _l('fe_asset'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_updated_fail', _l('fe_asset'));
					set_alert('danger', $message);
				}
			}
			redirect(admin_url('fixed_equipment/detail_locations/' . $location));
		}
	}

	/**
	 * update accessories location
	 */
	public function update_accessories_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			if ($this->input->post('id')) {
				$success = $this->fixed_equipment_model->update_accessories($data);
				if ($success == 1) {
					$message = _l('fe_quantity_not_valid', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 2) {
					$message = _l('fe_this_accessory_not_exist', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 3) {
					$message = _l('fe_quantity_is_unknown', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 4) {
					$message = _l('fe_updated_successfully', _l('fe_accessories'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_accessories'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'accessory');
			}
			redirect(admin_url('fixed_equipment/detail_locations/' . $location));
		}
	}

	/**
	 * update consumables location
	 */
	public function update_consumables_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			if ($this->input->post('id')) {
				$success = $this->fixed_equipment_model->update_consumables($data);
				if ($success == 1) {
					$message = _l('fe_quantity_not_valid', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 2) {
					$message = _l('fe_this_consumables_not_exist', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 3) {
					$message = _l('fe_quantity_is_unknown', _l('fe_accessories'));
					set_alert('danger', $message);
				} elseif ($success == 4) {
					$message = _l('fe_updated_successfully', _l('fe_accessories'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_accessories'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'consumable');
			}
			redirect(admin_url('fixed_equipment/detail_locations/' . $location));
		}
	}

	/**
	 * update components location
	 */
	public function update_components_location()
	{
		if ($this->input->post()) {
			$data             = $this->input->post();
			$location = '';
			if (isset($data['location'])) {
				$location = $data['location'];
				unset($data['location']);
			} else {
				redirect(admin_url('fixed_equipment/dashboard'));
			}
			if ($this->input->post('id')) {
				$success = $this->fixed_equipment_model->update_components($data);
				if ($success == 1) {
					$message = _l('fe_updated_successfully', _l('fe_components'));
					set_alert('success', $message);
				} else {
					$message = _l('fe_no_data_changes', _l('fe_components'));
					set_alert('warning', $message);
				}
				fe_handle_item_file($data['id'], 'component');
			}
			redirect(admin_url('fixed_equipment/detail_locations/' . $location));
		}
	}

	/**
	 * staff asset table
	 * @return json 
	 */
	public function staff_asset_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assets';
				$join         = [
					'LEFT JOIN ' . db_prefix() . 'fe_checkin_assets ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_checkin_assets.item_id',
					'LEFT JOIN ' . db_prefix() . 'fe_seats ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_seats.license_id'
				];
				array_push($where, 'AND ' . db_prefix() . 'fe_assets.active=1');
				$staffid = $this->input->post('staffid');
				if (isset($staffid) && $staffid != '') {
					$query = 'AND ((' . db_prefix() . 'fe_assets.type="asset" and ' . db_prefix() . 'fe_assets.checkin_out = 2 and ' . db_prefix() . 'fe_assets.checkin_out_id = ' . db_prefix() . 'fe_checkin_assets.id and ' . db_prefix() . 'fe_checkin_assets.type="checkout" and ' . db_prefix() . 'fe_checkin_assets.checkout_to="user" and ((' . db_prefix() . 'fe_checkin_assets.requestable = 0 and ' . db_prefix() . 'fe_checkin_assets.request_status = 0) or (' . db_prefix() . 'fe_checkin_assets.requestable = 1 and ' . db_prefix() . 'fe_checkin_assets.request_status = 1)) and ' . db_prefix() . 'fe_checkin_assets.staff_id=' . $staffid . ') OR
			(' . db_prefix() . 'fe_assets.type="license" and ' . db_prefix() . 'fe_seats.to = "user" and ' . db_prefix() . 'fe_seats.to_id=' . $staffid . ') OR
			(' . db_prefix() . 'fe_assets.type="accessory" and ' . db_prefix() . 'fe_checkin_assets.type="checkout" and ' . db_prefix() . 'fe_checkin_assets.status=2 and ' . db_prefix() . 'fe_checkin_assets.checkout_to="user" and ((' . db_prefix() . 'fe_checkin_assets.requestable = 0 and ' . db_prefix() . 'fe_checkin_assets.request_status = 0) or (' . db_prefix() . 'fe_checkin_assets.requestable = 1 and ' . db_prefix() . 'fe_checkin_assets.request_status = 1)) and ' . db_prefix() . 'fe_checkin_assets.staff_id=' . $staffid . ') OR
			(' . db_prefix() . 'fe_assets.type="consumable" and ' . db_prefix() . 'fe_checkin_assets.type="checkout" and ' . db_prefix() . 'fe_checkin_assets.status=2 and ' . db_prefix() . 'fe_checkin_assets.checkout_to="user" and ((' . db_prefix() . 'fe_checkin_assets.requestable = 0 and ' . db_prefix() . 'fe_checkin_assets.request_status = 0) or (' . db_prefix() . 'fe_checkin_assets.requestable = 1 and ' . db_prefix() . 'fe_checkin_assets.request_status = 1)) and ' . db_prefix() . 'fe_checkin_assets.staff_id=' . $staffid . '))';
					array_push($where, $query);
				}
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_assets.id',
					db_prefix() . 'fe_assets.assets_name',
					db_prefix() . 'fe_assets.model_id',
					db_prefix() . 'fe_assets.series',
					db_prefix() . 'fe_assets.type',
					db_prefix() . 'fe_checkin_assets.date_creator as checkout_date1',
					db_prefix() . 'fe_assets.checkin_out_id as checkin_out_id',
					db_prefix() . 'fe_seats.date_creator as checkout_date2'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$image = '';
					$checkout_date = _dt($aRow['checkout_date1']);
					if ($aRow['type'] == 'asset') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';
					}
					if ($aRow['type'] == 'consumable') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'consumable') . '">';
					}
					if ($aRow['type'] == 'accessory') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'accessory') . '">';
					}
					if ($aRow['type'] == 'license') {
						$checkout_date = _dt($aRow['checkout_date2']);
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['id'], 'license') . '">';
					}
					$row[] = $aRow['assets_name'];
					$row[] = $image;
					$row[] = $aRow['series'];
					$row[] = _l('fe_' . $aRow['type']);

					$row[] = $checkout_date;
					$sign_document = '';
					$sign_document_status = '';
					if (is_numeric($aRow['checkin_out_id'])) {
						$data_document = $this->fixed_equipment_model->get_sign_document_check_in_out($aRow['checkin_out_id']);
						if ($data_document) {
							$sign_document = '<a href="' . admin_url('fixed_equipment/checkout_managements#' . $data_document->id) . '" >#' . $data_document->reference . '</a>';
							$status = $data_document->status;
							if ($status == 1) {
								$sign_document_status = '<span class="label label-danger">' . _l('fe_not_yet_sign') . '</span>';
							}
							if ($status == 2) {
								$sign_document_status = '<span class="label label-warning">' . _l('fe_signing') . '</span>';
							}
							if ($status == 3) {
								$sign_document_status = '<span class="label label-success">' . _l('fe_signed') . '</span>';
							}
						}
					}
					$row[] = $sign_document_status;
					$row[] = $sign_document;


					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}
	/**
	 * get asset staff predefined kit
	 * @return  
	 */
	public function get_asset_staff_predefined_kit($id, $staffid)
	{

		$html = '';
		$data = $this->fixed_equipment_model->get_list_checked_out_predefined_kit_staff($staffid, $id);
		if ($data) {
			foreach ($data as $row) {
				$model_id = '';
				$serial = '';
				$data_asset = $this->fixed_equipment_model->get_assets($row['item_id']);
				if ($data_asset) {
					$model_id = $data_asset->model_id;
					$serial = $data_asset->series;
				}
				$asset_name = '';
				if ($row['asset_name'] != '' && $serial != '') {
					$asset_name = $row['asset_name'] . ' - ' . $serial;
				}
				if ($row['asset_name'] != '' && $serial == '') {
					$asset_name = $row['asset_name'];
				}
				if ($row['asset_name'] == '' && $serial != '') {
					$asset_name = $serial;
				}
				$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($model_id, 'models') . '">';
				$html .= '<div class="alert alert-info mbot0">' . $image . ' ' . $asset_name . '</div>';
			}
		}
		if ($html != '') {
			$html = '<div class="row"><div class="col-md-12 text-left mtop15 mbot5"><strong>' . _l('fe_assets_currently_checked_out_to_this_user') . '</strong><hr></div></div>' . $html;
		}
		echo json_encode($html);
	}

	/**
	 * asset staff history table
	 * @return json 
	 */
	public function asset_staff_history_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('staff_model');
				$staffid = $this->input->post('staffid');
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];

				array_push($where, 'AND ' . db_prefix() . 'fe_log_assets.to = "user" AND (action = "checkout" OR action = "checkin") AND to_id = ' . $staffid);

				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_log_assets';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'admin_id',
					'action',
					'target',
					'item_id',
					'changed',
					db_prefix() . 'fe_log_assets.to',
					'to_id',
					'notes',
					'date_creator'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = _dt($aRow['date_creator']);
					$row[] = get_staff_full_name($aRow['admin_id']);
					$row[] = _l('fe_' . $aRow['action']);

					$asset = '';
					$data_asset = $this->fixed_equipment_model->get_assets($aRow['item_id']);
					if ($data_asset) {
						if ($data_asset->assets_name != '' && $data_asset->series != '') {
							$asset = $data_asset->assets_name . ' ' . $data_asset->series;
						}
						if ($data_asset->assets_name != '' && $data_asset->series == '') {
							$asset = $data_asset->assets_name;
						}
						if ($data_asset->assets_name == '' && $data_asset->series != '') {
							$asset = $data_asset->series;
						}
					}

					$row[] = $asset;
					$row[] = $aRow['notes'];
					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * delete asset maintenances detail 
	 * @param  integer $id 
	 */
	public function delete_asset_maintenance_detail($id, $maintenance_id)
	{
		if ($maintenance_id != '') {
			$result =  $this->fixed_equipment_model->delete_asset_maintenances($maintenance_id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_depreciations')));
			}
		}
		redirect(admin_url('fixed_equipment/detail_asset/' . $id . '?tab=maintenances'));
	}


	/**
	 * permission table
	 */
	public function permission_table()
	{
		if ($this->input->is_ajax_request()) {

			$select = [
				'staffid',
				'CONCAT(firstname," ",lastname) as full_name',
				'firstname', //for role name
				'email',
				'phonenumber',
			];
			$where = [];
			$where[] = 'AND ' . db_prefix() . 'staff.admin != 1';

			$arr_staff_id = fe_get_staff_id_permissions();

			if (count($arr_staff_id) > 0) {
				$where[] = 'AND ' . db_prefix() . 'staff.staffid IN (' . implode(', ', $arr_staff_id) . ')';
			} else {
				$where[] = 'AND ' . db_prefix() . 'staff.staffid IN ("")';
			}

			$aColumns = $select;
			$sIndexColumn = 'staffid';
			$sTable = db_prefix() . 'staff';
			$join = ['LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role'];

			$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'roles.name as role_name', db_prefix() . 'staff.role']);

			$output = $result['output'];
			$rResult = $result['rResult'];

			$not_hide = '';

			foreach ($rResult as $aRow) {
				$row = [];

				$_data = '';
				$_data .= '<div class="row-options">';
				$_data .= '<a href="javascript:void(0)" onclick="permissions_update(' . $aRow['staffid'] . ', ' . $aRow['role'] . ', ' . $not_hide . '); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
				$_data .= ' | <a href="' . admin_url('fixed_equipment/delete_permission/' . $aRow['staffid']) . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
				$_data .= '</div>';

				$row[] = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['full_name'] . '</a>' . $_data;

				$row[] = $aRow['role_name'];
				$row[] = $aRow['email'];
				$row[] = $aRow['phonenumber'];

				$options = '';



				$row[] = $options;

				$output['aaData'][] = $row;
			}

			echo json_encode($output);
			die();
		}
	}

	/**
	 * permission modal
	 */
	public function permission_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$this->load->model('staff_model');

		if ($this->input->post('slug') === 'update') {
			$staff_id = $this->input->post('staff_id');
			$role_id = $this->input->post('role_id');

			$data = ['funcData' => ['staff_id' => isset($staff_id) ? $staff_id : null]];

			if (isset($staff_id)) {
				$data['member'] = $this->staff_model->get($staff_id);
			}

			$data['roles_value'] = $this->roles_model->get();
			$data['staffs'] = fe_get_staff_id_not_permissions();
			$add_new = $this->input->post('add_new');

			if ($add_new == ' hide') {
				$data['add_new'] = ' hide';
				$data['display_staff'] = '';
			} else {
				$data['add_new'] = '';
				$data['display_staff'] = ' hide';
			}

			$this->load->view('settings/includes/permission_modal', $data);
		}
	}

	/**
	 * staff id changed
	 * @param  integer $staff_id
	 * @return json
	 */
	public function staff_id_changed($staff_id)
	{
		$role_id = '';
		$status = 'false';

		$staff = $this->staff_model->get($staff_id);
		if ($staff) {
			$role_id = $staff->role;
			$status = 'true';
		}

		echo json_encode([
			'role_id' => $role_id,
			'status' => $status,
		]);
		die;
	}

	/**
	 * hr profile update permissions
	 * @param  string $id
	 */
	public function update_permissions($id = '')
	{
		if (!is_admin()) {
			access_denied('fixed_equipment');
		}
		$data = $this->input->post();

		if (!isset($id) || $id == '') {
			$id = $data['staff_id'];
		}

		if (isset($id) && $id != '') {
			if (is_admin()) {
				if (isset($data['administrator'])) {
					$data['admin'] = 1;
					unset($data['administrator']);
				} else {
					if ($id != get_staff_user_id()) {
						if ($id == 1) {
							return [
								'cant_remove_main_admin' => true,
							];
						}
					} else {
						return [
							'cant_remove_yourself_from_admin' => true,
						];
					}
					$data['admin'] = 0;
				}
			}

			$this->db->where('staffid', $id);
			$this->db->update(db_prefix() . 'staff', [
				'role' => $data['role'],
			]);

			$response = $this->staff_model->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $data['permissions']), $id);
		} else {
			$this->load->model('roles_model');
			$role_id = $data['role'];
			unset($data['role']);
			unset($data['staff_id']);
			$data['update_staff_permissions'] = true;
			$response = $this->roles_model->update($data, $role_id);
		}

		if (is_array($response)) {
			if (isset($response['cant_remove_main_admin'])) {
				set_alert('warning', _l('staff_cant_remove_main_admin'));
			} elseif (isset($response['cant_remove_yourself_from_admin'])) {
				set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
			}
		} elseif ($response == true) {
			set_alert('success', _l('fe_updated_successfully', _l('staff_member')));
		}
		redirect(admin_url('fixed_equipment/settings?tab=permission'));
	}

	/**
	 * delete permission
	 * @param  integer $id
	 */
	public function delete_permission($id)
	{
		if (!is_admin()) {
			access_denied('fixed_equipment');
		}
		$response = $this->fixed_equipment_model->delete_permission($id);
		if (is_array($response) && isset($response['referenced'])) {
			set_alert('warning', _l('hr_is_referenced', _l('department_lowercase')));
		} elseif ($response == true) {
			set_alert('success', _l('deleted'));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(admin_url('fixed_equipment/settings?tab=permission'));
	}


	/**
	 * checkout management table
	 * @return json 
	 */
	public function checkout_management_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('currencies_model');
				$base_currency = $this->currencies_model->get_base_currency();
				$currency_name = '';
				if (isset($base_currency)) {
					$currency_name = $base_currency->name;
				}
				$select = [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.id'
				];

				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_checkin_assets';
				$join         = [
				'LEFT JOIN ' . db_prefix() . 'fe_assets ON ' . db_prefix() . 'fe_assets.id = ' . db_prefix() . 'fe_checkin_assets.item_id', 
				'left join ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'fe_checkin_assets.staff_id', 
				'left join ' . db_prefix() . 'fe_locations on ' . db_prefix() . 'fe_locations.id = ' . db_prefix() . 'fe_checkin_assets.location_id', 
				'LEFT JOIN ' . db_prefix() . 'fe_sign_documents ON FIND_IN_SET(' . db_prefix() . 'fe_checkin_assets.id, ' . db_prefix() . 'fe_sign_documents.checkin_out_id)'];

				$location_id = $this->input->post('location_id');
				if ($location_id != '') {
					array_push($where, ' AND ' . db_prefix() . 'fe_checkin_assets.location_id = ' . $location_id . '');
				}

				$asset_id = $this->input->post('asset_id');
				if ($asset_id != '') {
					array_push($where, ' AND IF(item_type = "license", (select license_id from ' . db_prefix() . 'fe_seats where ' . db_prefix() . 'fe_seats.id = ' . db_prefix() . 'fe_checkin_assets.item_id), ' . db_prefix() . 'fe_checkin_assets.item_id) = ' . $asset_id . '');
				}

				$staff_id = $this->input->post('staff_id');
				if ($staff_id != '') {
					array_push($where, ' AND ' . db_prefix() . 'fe_checkin_assets.staff_id = ' . $staff_id . '');
				}

				$date_creator = $this->input->post('date');
				if ($date_creator != '') {
					array_push($where, ' AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) = "' . $date_creator . '"');
				}

				$check_type = $this->input->post('check_type');
				if ($check_type != '') {
					array_push($where, ' AND ' . db_prefix() . 'fe_checkin_assets.type = "' . $check_type . '"');
				}

				$from_date = $this->input->post('from_date');
				$to_date = $this->input->post('to_date');
				if ($from_date != '' && $to_date != '') {
					array_push($where, ' AND date(' . db_prefix() . 'fe_checkin_assets.date_creator) between \'' . fe_format_date($from_date) . '\' and \'' . fe_format_date($to_date) . '\'');
				}

				$sign_document = $this->input->post('sign_document');
				if ($sign_document != '') {
					array_push($where, ' AND ' . db_prefix() . 'fe_sign_documents.id = "' . $sign_document . '"');
				}
				array_push($where, ' AND ((' . db_prefix() . 'fe_checkin_assets.request_status = 1 AND ' . db_prefix() . 'fe_checkin_assets.requestable = 1) OR ' . db_prefix() . 'fe_checkin_assets.requestable = 0)');
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					db_prefix() . 'fe_checkin_assets.id',
					db_prefix() . 'fe_checkin_assets.staff_id',
					'assets_name',
					'item_id',
					db_prefix() . 'fe_assets.model_id',
					db_prefix() . 'fe_assets.series',
					db_prefix() . 'fe_checkin_assets.type as check_type',
					db_prefix() . 'fe_checkin_assets.item_type',
					db_prefix() . 'fe_sign_documents.id as sign_document_id',
					db_prefix() . 'fe_sign_documents.reference as sign_document_reference',
					db_prefix() . 'fe_checkin_assets.date_creator as checkout_date'
				]);
				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = '<input type="checkbox" class="individual" data-id="' . $aRow['id'] . '" onchange="checked_add(this); return false;"/>';
					$row[] = $aRow['id'];
					$image = '';
					$url = '';
					$item_id = $aRow['item_id'];
					$assets_name =  fe_item_name($item_id, true);
					$assets_tag = $aRow['series'];
					$checkout_date = _dt($aRow['checkout_date']);
					if ($aRow['item_type'] == 'asset') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($aRow['model_id'], 'models') . '">';
						$url = admin_url('fixed_equipment/detail_asset/' . $item_id . '?tab=details');
					}
					if ($aRow['item_type'] == 'consumable') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($item_id, 'consumable') . '">';
						$url = admin_url('fixed_equipment/detail_consumables/' . $item_id);
					}
					if ($aRow['item_type'] == 'component') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($item_id, 'component') . '">';
						$url = admin_url('fixed_equipment/detail_components/' . $item_id);
					}
					if ($aRow['item_type'] == 'accessory') {
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($item_id, 'accessory') . '">';
						$url = admin_url('fixed_equipment/detail_accessories/' . $item_id);
					}
					if ($aRow['item_type'] == 'license') {
						$license_id = '';
						$data_seats = $this->fixed_equipment_model->get_seats($aRow['item_id']);
						if ($data_seats) {
							$license_id = $data_seats->license_id;
							$data_licenses = $this->fixed_equipment_model->get_assets($license_id);
							if ($data_licenses) {
								$assets_name = $data_licenses->assets_name;
								$assets_tag = $data_licenses->series;
							}
						}
						$item_id = $license_id;
						$image = '<img class="img img-responsive staff-profile-image-small pull-left" src="' . $this->fixed_equipment_model->get_image_items($license_id, 'license') . '">';
						$url = admin_url('fixed_equipment/detail_licenses/' . $item_id . '?tab=details');
					}

					$row[] = '<a href="' . $url . '">' . $assets_name . '</a>';
					$row[] = $image;
					$row[] = $assets_tag;
					$row[] = ($aRow['item_type'] != null ? _l('fe_' . $aRow['item_type']) : '');
					$row[] = ((is_numeric($aRow['staff_id']) && $aRow['staff_id'] > 0) ? get_staff_full_name($aRow['staff_id']) : '');
					$check_type = '';
					if ($aRow['check_type'] == 'checkout') {
						$check_type = '<span class="label label-warning">' . _l('fe_' . $aRow['check_type']) . '</span>';
					} else {
						$check_type = '<span class="label label-success">' . _l('fe_' . $aRow['check_type']) . '</span>';
					}
					$row[] = $check_type;

					$row[] = $checkout_date;

					$sign_doc = '';
					if ($aRow['sign_document_id'] != '' && $aRow['sign_document_reference'] != '') {
						$sign_doc = '<a href="javascript:void(0)" onclick="detail_sign_document(' . $aRow['sign_document_id'] . ')">#' . $aRow['sign_document_reference'] . '</a>';
					}
					$row[] = $sign_doc;
					$output['aaData'][] = $row;
				}
				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * checkout managements
	 */
	public function checkout_managements()
	{
		if (!(has_permission('fixed_equipment_sign_manager', '', 'view_own') || has_permission('fixed_equipment_sign_manager', '', 'view') || is_admin())) {
			access_denied('fixed_equipment');
		}
		$data['title']    = _l('fe_sign_manager');
		$data['locations'] = $this->fixed_equipment_model->get_locations();
		$data['assets'] = $this->fixed_equipment_model->get_assets();
		$data['staffs'] = $this->staff_model->get();
		$data['sign_documents'] = $this->fixed_equipment_model->get_sign_document();
		$data['check_in_out_not_sign'] = $this->fixed_equipment_model->get_check_in_out_not_yet_sign();
		$this->load->view('checkout_management', $data);
	}

	/**
	 * detail checkout
	 */
	public function detail_checkout()
	{
		if (!(has_permission('fixed_equipment_sign_manager', '', 'view_own') || has_permission('fixed_equipment_sign_manager', '', 'view') || is_admin())) {
			access_denied('fixed_equipment');
		}
		$data['title']    = _l('fe_detail_checkout');

		$this->load->view('detail_checkout', $data);
	}

	/**
	 * get sign modal	
	 * @return string
	 */
	public function get_sign_modal()
	{
		$html = '';
		$list = $this->input->post('id_list');
		$data['list_id'] = $list;
		$checkout_to_staff = 0;
		$data['check_in_out'] = $this->fixed_equipment_model->get_check_in_out_list($list);
		if (count($data['check_in_out']) > 0 && isset($data['check_in_out'][0])) {
			$checkout_to_staff = $this->fixed_equipment_model->get_staff_check_in_out($data['check_in_out'][0]['id']);
		}
		$html = $this->load->view('includes/sign_modal', $data, true);
		echo $html;
		die;
	}

	/**
	 * 	
	 * @param  integer $staffid 
	 * @return integer          
	 */
	public function get_check_in_out_staff_option($staffid)
	{
		$check_in_out_not_sign = $this->fixed_equipment_model->get_check_in_out_not_yet_sign($staffid);
		$html = '';
		foreach ($check_in_out_not_sign as $key => $value) {
			$html .= '<option value="' . $value['id'] . '">#' . $value['id'] . ' ' . $value['asset_name'] . '</option>';
		}
		echo $html;
		die;
	}

	public function add_sign_document()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$res = $this->fixed_equipment_model->add_sign_document($data);
			if ($res) {
				set_alert('success', _l('fe_created_successfully'));
				redirect(admin_url('fixed_equipment/checkout_managements#' . $res));
			} else {
				set_alert('danger', _l('fe_create_failed'));
			}
		}
		redirect(admin_url('fixed_equipment/checkout_managements'));
	}

	/**
	 * get sign document detail
	 * @param  integer $id 
	 * @return string     
	 */
	public function get_sign_document_detail($id)
	{
		$html = '';
		$checkout_to_staff = 0;
		$data['id'] = $id;
		$data['sign_documents'] = $this->fixed_equipment_model->get_sign_document($id);
		$data['signers'] = $this->fixed_equipment_model->get_signer_by_master($id);
		$html = $this->load->view('includes/sign_detail', $data, true);
		echo $html;
		die;
	}

	/**
	 * change sign document status
	 * @param  integer $id     
	 * @param  integer $status 
	 * @return json         
	 */
	public function change_sign_document_status($id, $status)
	{
		$message = '';
		$success = $this->fixed_equipment_model->change_sign_document_status($id, $status);
		if ($success) {
			$message = _l('fe_changed_status_successfully');
		} else {
			$message = _l('fe_change_status_failed');
		}
		echo json_encode([
			'success' => $success,
			'message' => $message
		]);
	}

	/**
	 * staff sign document
	 */
	public function staff_sign_document()
	{
		if ($this->input->post()) {
			$id = $this->input->post('id');
			$document_id = $this->input->post('document_id');
			process_digital_signature_image($this->input->post('signature', false), FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER . '/sign_document/' . $id);
			$data_update['firstname'] = $this->input->post('firstname');
			$data_update['lastname'] = $this->input->post('lastname');
			$data_update['email'] = $this->input->post('email');
			$data_update['ip_address'] = fe_get_client_ip();
			$data_update['date_of_signing'] = date('Y-m-d H:i:s');

			$result = $this->fixed_equipment_model->update_signer_info($id, $data_update);
			if ($result) {
				set_alert('success', _l('fe_signed_successfully'));
			} else {
				set_alert('danger', _l('fe_sign_failed'));
			}
		}
		if (is_numeric($document_id)) {
			redirect(admin_url('fixed_equipment/checkout_managements#' . $document_id));
		} else {
			redirect(admin_url('fixed_equipment/checkout_managements'));
		}
	}
	/**
	 * [sign_detail_pdf
	 * @param  integer $id 
	 */
	public function sign_detail_pdf($id)
	{
		if (!$id) {
			redirect(admin_url('fixed_equipment/checkout_managements'));
		}
		$type = 'D';
		if ($this->input->get('output_type')) {
			$type = $this->input->get('output_type');
		}

		if ($this->input->get('print')) {
			$type = 'I';
		}
		$data['title'] = _l('fe_sign_documents');
		$data['type'] = $type;
		$data['id'] = $id;
		$data['sign_documents'] = $this->fixed_equipment_model->get_sign_document($id);
		$data['signers'] = $this->fixed_equipment_model->get_signer_by_master($id);

		$html = $this->load->view('sign_document/sign_document_html_view', $data, true);
		$html .= '<link href="' . FCPATH . 'modules/fixed_equipment/assets/css/sign_document_pdf.css"  rel="stylesheet" type="text/css" />';
		$data['html'] = $html;
		$this->load->view('sign_document/preview_pdf', $data);
	}

	/**
	 * get asset info from qr code
	 * @return json 
	 */
	public  function get_asset_info_from_qr_code()
	{
		$data = $this->input->post();
		$success = false;
		$id = '';
		$html = '';
		$asset_data = $this->fixed_equipment_model->get_asset_by_qr_code($data['qrcode']);
		if ($asset_data) {
			$success = true;
			$model_name_s = '';
			$data_model = $this->fixed_equipment_model->get_models($asset_data->model_id);
			if ($data_model) {
				$model_name_s = $data_model->model_name;
			}

			$location_name = '';
			if (is_numeric($asset_data->location_id)) {
				$data_alocation = $this->fixed_equipment_model->get_locations($asset_data->location_id);
				if ($data_alocation) {
					$location_name = $data_alocation->location_name;
				}
			}
			$supplier_name_s = '';
			if (is_numeric($asset_data->supplier_id)) {
				$data_supplier = $this->fixed_equipment_model->get_suppliers($asset_data->supplier_id);
				if ($data_supplier) {
					$supplier_name_s = $data_supplier->supplier_name;
				}
			}
			$id = $asset_data->id;
			$html .= '<div class="row"><div class="col-md-3"><img class="img img-responsive pull-left mtop10 mright10 mbot10" src="' . $this->fixed_equipment_model->get_image_items($asset_data->model_id, 'models') . '"/></div>';
			$html .= '<div class="col-md-9"><a target="_blank" href="' . admin_url('fixed_equipment/detail_asset/' . $id . '?tab=details') . '"><h4 class="bold">' . $asset_data->assets_name . '</h4></a>';
			$html .= (($asset_data->series != null && $asset_data->series != '') ? _l('fe_asset_tag') . ': ' . $asset_data->series . '<br>' : '');
			$html .= (($model_name_s != '') ? _l('fe_models') . ': ' . $model_name_s . '<br>' : '');
			$html .= (($location_name != '') ? _l('fe_locations') . ': ' . $location_name . '<br>' : '');
			$html .= (($asset_data->date_buy != null && $asset_data->date_buy != '') ? _l('fe_purchase_date') . ': ' . $asset_data->date_buy . '<br>' : '');
			$html .= (($asset_data->unit_price != null && $asset_data->unit_price != '') ? _l('fe_purchase_cost') . ': ' . app_format_money($asset_data->unit_price, '') . '<br>' : '');
			$html .= (($asset_data->warranty_period != null && $asset_data->warranty_period != '' && $asset_data->warranty_period != 0) ? _l('fe_warranty') . ': ' . $asset_data->warranty_period . '<br>' : '');
			$html .= (($supplier_name_s != '') ? _l('fe_supplier') . ': ' . $supplier_name_s . '<br>' : '');
			$html .= '</div>';
			$html .= '</div>';
		}
		echo json_encode([
			'id' => $id,
			'success' => $success,
			'html' => $html
		]);
	}



	/**
	 * print qr PDF
	 * @param  integer $id 
	 */
	public function print_qrcode_pdf($id_s)
	{
		$type = 'D';
		if ($this->input->get('output_type')) {
			$type = $this->input->get('output_type');
		}

		if ($this->input->get('print')) {
			$type = 'I';
		}

		$data['title'] = _l('fe_print_qrcode');
		$data['type'] = $type;
		$data['list_id'] = explode(',', urldecode($id_s));
		$html = $this->load->view('asset_managerments/print_qrcode_html_view', $data, true);
		$html .= '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/sign_document_pdf.css') . '"  rel="stylesheet" type="text/css" />';
		$data['html'] = $html;
		$this->load->view('asset_managerments/preview_pdf', $data);
	}
	/**
	 * bulk upload
	 * @param  string $type 
	 */
	public function bulk_upload($type)
	{
		$data['title'] = _l('fe_bulk_upload');
		$data['type'] = $type;
		$this->load->model('staff_model');
		$data_staff = $this->staff_model->get(get_staff_user_id());

		/*get language active*/
		if ($data_staff) {
			if ($data_staff->default_language != '') {
				$data['active_language'] = $data_staff->default_language;
			} else {
				$data['active_language'] = get_option('active_language');
			}
		} else {
			$data['active_language'] = get_option('active_language');
		}
		$this->load->view('asset_managerments/bulk_upload', $data);
	}
	/**
	 * import xlsx item
	 * @param  string $type 
	 */
	public function import_xlsx_item($type)
	{
		if (!class_exists('XLSXReader_fin')) {
			require_once module_dir_path(FIXED_EQUIPMENT_MODULE_NAME) . 'assets/plugins/XLSXReader/XLSXReader.php';
		}
		require_once module_dir_path(FIXED_EQUIPMENT_MODULE_NAME) . 'assets/plugins/XLSXWriter/xlsxwriter.class.php';

		$total_row_success = 0;
		$total_row_false = 0;
		$total_rows = 0;
		$string_error = '';
		$error_filename = '';
		$file_type = '';
		$result = new stdClass();
		if ($this->input->post()) {
			$data = $this->input->post();
			if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
				$file_type = substr($_FILES["file_csv"]["name"], strrpos($_FILES["file_csv"]["name"], "."), (strlen($_FILES["file_csv"]["name"]) - strrpos($_FILES["file_csv"]["name"], ".")));
				$this->delete_error_file_day_before(1, FIXED_EQUIPMENT_IMPORT_ITEM_ERROR);
				$result = $this->fixed_equipment_model->data_import_xlsx_item($_FILES['file_csv']['tmp_name'], $_FILES['file_csv']['name'], $type);
				$error_filename = $result->error_filename;
			}
		}
		$data = [
			'total_row_success' => $result->total_row_success,
			'total_row_error' => $result->total_row_error,
			'total_rows' => $result->total_rows,
			'arr_insert' => json_encode($result->arr_insert),
			'file_type' => $file_type,
			'site_url' => site_url(),
			'staff_id' => get_staff_user_id(),
			'error_filename' => FIXED_EQUIPMENT_IMPORT_ITEM_ERROR . $error_filename,
		];
		echo json_encode($data);
		die;
	}

	/**
	 * delete error file day before
	 * @param  string $before_day
	 * @param  string $folder_name
	 * @return boolean
	 */
	public function delete_error_file_day_before($before_day = '', $folder_name = '')
	{
		if ($before_day != '') {
			$day = $before_day;
		} else {
			$day = '7';
		}

		if ($folder_name != '') {
			$folder = $folder_name;
		} else {
			$folder = FIXED_EQUIPMENT_IMPORT_ITEM_ERROR;
		}

		//Delete old file before 7 day
		$date = date_create(date('Y-m-d H:i:s'));
		date_sub($date, date_interval_create_from_date_string($day . " days"));
		$before_7_day = strtotime(date_format($date, "Y-m-d H:i:s"));

		foreach (glob($folder . '*') as $file) {

			$file_arr = explode("/", $file);
			$filename = array_pop($file_arr);

			if (file_exists($file)) {
				//don't delete index.html file
				if ($filename != 'index.html') {
					$file_name_arr = explode("_", $filename);
					$date_create_file = array_pop($file_name_arr);
					$date_create_file = str_replace('.xlsx', '', $date_create_file);

					if ((float) $date_create_file <= (float) $before_7_day) {
						unlink($folder . $filename);
					}
				}
			}
		}
		return true;
	}

	/**
	 * inventory receiving
	 * @param  string $id 
	 */
	public function inventory_receiving($id = '')
	{
		$data['title'] = _l('fe_inventory_receiving');
		$data['purchase_id'] = $id;
		$this->load->view('warehouses/inventory_receiving_management', $data);
	}

	/**
	 * inventory receiving
	 * @param  string $id 
	 */
	public function inventory_delivery($id = '')
	{

		$this->load->view('warehouses/inventory_delivery_management', $data);
	}

	/**
	 * internal transfer
	 * @param  string $id 
	 */
	public function internal_transfer($id = '')
	{
		$data['internal_id'] = $id;
		$data['title'] = _l('fe_internal_transfer');
		$this->load->view('warehouses/internal_transfer_management', $data);
	}

	/**
	 * lost adjustment
	 */
	public function lost_adjustment()
	{
		$data['title'] = _l('fe_lost_adjustment');
		$this->load->view('warehouses/lost_adjustment_management', $data);
	}

	public function order_list()
	{
		if (!has_permission('fixed_equipment_order_list', '', 'view') && !has_permission('fixed_equipment_order_list', '', 'view_own') && !is_admin()) {
			access_denied('fixed_equipment_order_list');
		}
		$this->load->model('clients_model');
		$this->load->model('invoices_model');
		$this->load->model('staff_model');
		$data['customers'] = $this->clients_model->get();
		$data['invoices'] = $this->invoices_model->get();
		$data['prefix'] = get_option('invoice_prefix');
		$data['title'] = _l('fe_order_list');
		$data['staff'] = $this->staff_model->get();
		$this->load->view('orders/order_list_management', $data);
	}

	/**
	 * table warehouse
	 * @return array
	 */
	public function warehouses_table()
	{
		$this->app->get_table_data(module_views_path('fixed_equipment', 'warehouses/tables/warehouses_table'));
	}

	/**
	 * add warehouse 
	 */
	public function add_warehouse()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);
				$result =  $this->fixed_equipment_model->add_warehouse($data);
				if (is_numeric($result)) {
					set_alert('success', _l('fe_added_successfully', _l('fe_warehouse')));
				} else {
					set_alert('danger', _l('fe_added_fail', _l('fe_warehouse')));
				}
			} else {
				$result =  $this->fixed_equipment_model->update_warehouse($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully', _l('fe_warehouse')));
				} else {
					set_alert('danger', _l('fe_no_data_changes', _l('fe_warehouse')));
				}
			}
		}
		redirect(admin_url('fixed_equipment/inventory?tab=warehouse_management'));
	}

	/**
	 * delete warehouse
	 * @param  integer $id 
	 */
	public function delete_warehouse($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_warehouse($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_warehouse')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_warehouse')));
			}
		}
		redirect(admin_url('fixed_equipment/inventory?tab=warehouse_management'));
	}

	/**
	 * get modal content warehouses
	 * @param  integer $id
	 * @return integer     
	 */
	public function get_modal_content_warehouses($id)
	{
		$this->load->model('staff_model');
		$this->load->model('currencies_model');
		$data['warehouse'] = $this->fixed_equipment_model->get_warehouses($id);
		echo json_encode([
			'data' =>  $this->load->view('settings/includes/warehouse_modal_content', $data, true),
			'success' => true
		]);
	}

	/**
	 * manage goods receipt
	 * @param  integer $id
	 * @return view
	 */
	public function manage_goods_receipt($id = '')
	{
		$this->load->model('clients_model');
		$this->load->model('taxes_model');
		if ($this->input->post()) {
			$message = '';
			$data = $this->input->post();
			if (!$this->input->post('id')) {
				$insert_id = $this->fixed_equipment_model->add_goods_receipt($data);
				if ($insert_id) {
					// Approve
					$staff_id = get_staff_user_id();
					$rel_type = 'inventory_receiving';
					$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
					$process = '';
					if ($check_proccess) {
						if ($check_proccess->choose_when_approving == 0) {
							$this->fixed_equipment_model->send_request_approve($insert_id, $rel_type, $staff_id);
							// End update status to waiting approve
							set_alert('success', _l('fe_successful_submission_of_approval_request'));
						} else {
							set_alert('success', _l('fe_created_successfully'));
						}
					}
				} else {
					set_alert('warning', _l('fe_create_failed'));
				}
				redirect(admin_url('fixed_equipment/inventory?tab=inventory_receiving#' . $insert_id));
			} else {
				$id = $this->input->post('id');
				$mess = $this->fixed_equipment_model->update_goods_receipt($data);
				if ($mess) {
					set_alert('success', _l('fe_updated_successfully'));
				} else {
					set_alert('warning', _l('fe_no_data_changes'));
				}
				redirect(admin_url('fixed_equipment/inventory?tab=inventory_receiving#' . $id));
			}
		}
		$data['title'] = _l('goods_receipt');
		$data['warehouses'] = $this->fixed_equipment_model->get_warehouses();
		$data['pr_orders'] = [];
		$data['pr_orders_status'] = false;
		$data['goods_code'] = $this->fixed_equipment_model->create_goods_code();
		$data['staff'] = $this->fixed_equipment_model->get_staff();
		$data['current_day'] = (date('Y-m-d'));
		$data['taxes'] = $this->taxes_model->get();
		$data['ajaxItems'] = false;
		$data['items'] = $this->fixed_equipment_model->wh_get_grouped('inventory_receiving');
		$warehouse_data = $this->fixed_equipment_model->get_warehouses();
		//sample
		$goods_receipt_row_template = $this->fixed_equipment_model->create_goods_receipt_row_template();

		//check status module purchase
		if ($id != '') {
			$goods_receipt = $this->fixed_equipment_model->get_goods_receipt($id);
			if (!$goods_receipt) {
				blank_page('Stock received Not Found', 'danger');
			}
			$data['goods_receipt_detail'] = $this->fixed_equipment_model->get_goods_receipt_detail($id);
			$data['goods_receipt'] = $goods_receipt;
			$data['tax_data'] = $this->fixed_equipment_model->get_html_tax_receip($id);
			$data['total_item'] = count($data['goods_receipt_detail']);
			if (count($data['goods_receipt_detail']) > 0) {
				$index_receipt = 0;
				foreach ($data['goods_receipt_detail'] as $receipt_detail) {
					$index_receipt++;
					$unit_name = '';
					$tax_name_array = [];
					$tax_rate_array = [];
					if($receipt_detail['tax_name'] != '' && $receipt_detail['tax_name'] != null){
						$tax_name_array = explode('|', $receipt_detail['tax_name']);
					}
					if($receipt_detail['tax_rate'] != '' && $receipt_detail['tax_rate'] != null){
						$tax_rate_array = explode('|', $receipt_detail['tax_rate']);
					}
					$tax_array = [];
					$taxname = '';
					foreach ($tax_name_array as $tax_key => $tax_name_item) {
						if (isset($tax_rate_array[$tax_key]) && $tax_rate = $tax_rate_array[$tax_key]) {
							$tax_array[] = $tax_name_item . '|' . $tax_rate;
						}
					}

					$taxname = (count($tax_array) > 0 ? $tax_array : '');
					$commodity_name = $receipt_detail['commodity_name'];

					if (strlen($commodity_name) == 0) {
						$commodity_name = wh_get_item_variatiom($receipt_detail['commodity_code']);
					}

					$goods_receipt_row_template .= $this->fixed_equipment_model->create_goods_receipt_row_template(
						$warehouse_data,
						'items[' . $index_receipt . ']',
						$commodity_name,
						$receipt_detail['warehouse_id'],
						$receipt_detail['quantities'],
						$receipt_detail['unit_price'],
						$taxname,
						$receipt_detail['commodity_code'],
						$receipt_detail['tax_rate'],
						$receipt_detail['tax_money'],
						$receipt_detail['serial_number'],
						$receipt_detail['note'],
						$receipt_detail['id']
					);
				}
			}

			$data['goods_receipt_detail'] = json_encode($this->fixed_equipment_model->get_goods_receipt_detail($id));
		}

		$data['goods_receipt_row_template'] = $goods_receipt_row_template;
		$get_base_currency =  get_base_currency();
		if ($get_base_currency) {
			$data['base_currency_id'] = $get_base_currency->id;
		} else {
			$data['base_currency_id'] = 0;
		}

		$this->load->view('warehouses/goods_receipts/purchase', $data);
	}

	/**
	 * inventory
	 */
	public function inventory()
	{
		if (!(has_permission('fixed_equipment_inventory', '', 'view') || has_permission('fixed_equipment_inventory', '', 'view_own')) && !is_admin()) {
			access_denied('fe_fixed_equipment');
		}
		$send_notify = $this->session->userdata("send_notify");
		$data['send_notify'] = 0;
		if ((isset($send_notify)) && $send_notify != '') {
			$data['send_notify'] = $send_notify;
			$this->session->unset_userdata("send_notify");
		}
		
		$data['title']                 = _l('fe_fixed_equipment');
		$data['tab'] = $this->input->get('tab');
		$id = $this->input->get('id');
		if ($data['tab'] == null) {
			$data['tab'] = 'inventory_receiving';
		}
		if ($data['tab'] == 'inventory_receiving') {
			$data['title'] = _l('fe_inventory_receiving');
			$data['purchase_id'] = $id;
		}
		if ($data['tab'] == 'inventory_delivery') {
			$data['title'] = _l('fe_inventory_delivery');
			$data['delivery_id'] = $id;
		}
		if ($data['tab'] == 'inventory_history') {
			$data['title'] = _l('fe_inventory_history');
		}
		if ($data['tab'] == 'shipments') {
			$data['title'] = _l('fe_shipment');
		}
		if ($data['tab'] == 'packing_list') {
			$data['title'] = _l('fe_packing_list');
		}
		$this->load->view('warehouses/inventory_management', $data);
	}

	/**
	 * prefix setting
	 * @return [type] 
	 */
	public function prefix_setting()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$affected_row = 0;
			if (isset($data['fe_inventory_receiving_prefix'])) {
				$res = update_option('fe_inventory_receiving_prefix', $data['fe_inventory_receiving_prefix']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_next_inventory_receiving_mumber'])) {
				$res = update_option('fe_next_inventory_receiving_mumber', $data['fe_next_inventory_receiving_mumber']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_inventory_delivery_prefix'])) {
				$res = update_option('fe_inventory_delivery_prefix', $data['fe_inventory_delivery_prefix']);
				if ($res) {
					$affected_row++;
				}
			}


			if (isset($data['fe_next_inventory_delivery_mumber'])) {
				$res = update_option('fe_next_inventory_delivery_mumber', $data['fe_next_inventory_delivery_mumber']);
				if ($res) {
					$affected_row++;
				}
			}


			if (isset($data['fe_packing_list_prefix'])) {
				$res = update_option('fe_packing_list_prefix', $data['fe_packing_list_prefix']);
				if ($res) {
					$affected_row++;
				}
			}


			if (isset($data['fe_next_packing_list_number'])) {
				$res = update_option('fe_next_packing_list_number', $data['fe_next_packing_list_number']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_next_serial_number'])) {
				$res = update_option('fe_next_serial_number', $data['fe_next_serial_number']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_serial_number_format'])) {
				$res = update_option('fe_serial_number_format', $data['fe_serial_number_format']);
				if ($res) {
					$affected_row++;
				}
			}

			if (isset($data['fe_issue_prefix'])) {
				$res = update_option('fe_issue_prefix', $data['fe_issue_prefix']);
				if ($res) {
					$affected_row++;
				}
			}
			if (isset($data['fe_next_issue_number'])) {
				$res = update_option('fe_next_issue_number', $data['fe_next_issue_number']);
				if ($res) {
					$affected_row++;
				}
			}
			if (isset($data['fe_issue_number_format'])) {
				$res = update_option('fe_issue_number_format', $data['fe_issue_number_format']);
				if ($res) {
					$affected_row++;
				}
			}

			if ($affected_row > 0) {
				set_alert('success', _l('fe_saved_successfully', _l('fe_prefix_setting')));
			} else {
				set_alert('danger', _l('fe_save_fail', _l('fe_prefix_setting')));
			}
		}
		redirect(admin_url('fixed_equipment/settings?tab=prefix_setting'));
	}

	/* Get item by id / ajax */
	public function get_item_by_id($id)
	{
		if ($this->input->is_ajax_request()) {
			$item = [];
			if (is_numeric($id)) {
				$data_asset = $this->fixed_equipment_model->get_assets($id);
				if ($data_asset) {
					$item['is_model'] = 0;
					$item['name'] = $data_asset->assets_name;
					$item['purchase_price'] = $data_asset->unit_price;
				}
			} else {
				$exp = explode('-', $id);
				if (isset($exp['0']) && $id = $exp['0']) {
					$data_model = $this->fixed_equipment_model->get_models($id);
					if ($data_model) {
						$item['is_model'] = 1;
						$item['name'] = $data_model->model_no . ' ' . $data_model->model_name;
						$item['purchase_price'] = '';
					}
				}
			}
			echo json_encode($item);
		}
	}

	/**
	 * get receipt note row template
	 * @return [type] 
	 */
	public function get_good_receipt_row_template()
	{
		$name = $this->input->post('name');
		$commodity_name = $this->input->post('commodity_name');
		$warehouse_id = $this->input->post('warehouse_id');
		$quantities = $this->input->post('quantities');
		$unit_price = $this->input->post('unit_price');
		$taxname = $this->input->post('taxname');
		$commodity_code = $this->input->post('commodity_code');
		$serial_number = $this->input->post('serial_number');
		$item_key = $this->input->post('item_key');
		$arr_serial_number = explode(',', $serial_number);
		$goods_receipt_row_template = '';
		
		if(!is_null($serial_number) && $serial_number != ''){
			foreach ($arr_serial_number as $key => $serial_number) {
				$quantities = 1;
				$goods_receipt_row_template .= $this->fixed_equipment_model->create_goods_receipt_row_template(
					[],
					'newitems['.$item_key.']',
					$commodity_name,
					$warehouse_id,
					$quantities,
					$unit_price,
					$taxname,
					$commodity_code,
					'',
					'',
					$serial_number,
					'',
					$item_key
				);
				$item_key++;
			}
		}else{
			$goods_receipt_row_template .= $this->fixed_equipment_model->create_goods_receipt_row_template(
				[],
				$name,
				$commodity_name,
				$warehouse_id,
				$quantities,
				$unit_price,
				$taxname,
				$commodity_code,
				'',
				'',
				$serial_number,
				'',
				$item_key
			);
		}
		echo $goods_receipt_row_template;

	}


	/**
	 * table manage goods receipt
	 * @param  integer $id
	 * @return array
	 */
	public function table_manage_goods_receipt()
	{
		$this->app->get_table_data(module_views_path('fixed_equipment', 'warehouses/tables/goods_receipt_table'));
	}

	/**
	 * delete goods receipt
	 * @param  [integer] $id
	 * @return redirect
	 */
	public function delete_goods_receipt($id)
	{
		if (!has_permission('fixed_equipment_inventory', '', 'delete')  &&  !is_admin()) {
			access_denied('inventory');
		}
		$response = $this->fixed_equipment_model->delete_goods_receipt($id);
		if ($response) {
			set_alert('success', _l('fe_deleted_successfully'));
		} else {
			set_alert('danger', _l('fe_deleted_fail'));
		}
		redirect(admin_url('fixed_equipment/inventory?tab=inventory_receiving'));
	}


	/**
	 * view purchase
	 * @param  integer $id
	 * @return view
	 */
	public function view_purchase($id)
	{
		//approval
		// $send_mail_approve = $this->session->userdata("send_mail_approve");
		// if ((isset($send_mail_approve)) && $send_mail_approve != '') {
		// 	$data['send_mail_approve'] = $send_mail_approve;
		// 	$this->session->unset_userdata("send_mail_approve");
		// }
		$request_type = 'inventory_receiving';
		$data['get_staff_sign'] = $this->fixed_equipment_model->get_staff_sign($id, $request_type);
		$data['check_approve_status'] = $this->fixed_equipment_model->check_approval_details($id, $request_type);
		$data['list_approve_status'] = $this->fixed_equipment_model->get_approval_details($id, $request_type);

		// $data['payslip_log'] = $this->fixed_equipment_model->get_activity_log($id, 1);

		// $data['commodity_code_name'] = $this->fixed_equipment_model->get_commodity_code_name();
		// $data['units_code_name'] = $this->fixed_equipment_model->get_units_code_name();
		// $data['units_warehouse_name'] = $this->fixed_equipment_model->get_warehouse_code_name();

		$data['goods_receipt_detail'] = $this->fixed_equipment_model->get_goods_receipt_detail($id);
		$data['goods_receipt'] = $this->fixed_equipment_model->get_goods_receipt($id);

		$data['tax_data'] = $this->fixed_equipment_model->get_html_tax_receip($id);

		$data['title'] = _l('stock_received_info');
		$check_appr = $this->fixed_equipment_model->get_approve_setting('1');
		$data['check_appr'] = $check_appr;
		$this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['base_currency'] = $base_currency;
		$this->load->view('warehouses/goods_receipts/view_purchase', $data);
	}

	/**
	 * approve request
	 * @param  integer $id
	 * @return json
	 */
	public function approve_request()
	{
		$data = $this->input->post();

		$data['staff_approve'] = get_staff_user_id();
		$success = false;
		$code = '';
		$signature = '';
		$open_warehouse_modal = false;
		$receipt_delivery_type = 'inventory_receipt_voucher_returned_goods';
		if (isset($data['signature'])) {
			$signature = $data['signature'];
			unset($data['signature']);
		}
		$status_string = 'status_' . $data['approve'];
		$check_approve_status = $this->fixed_equipment_model->check_approval_details($data['rel_id'], $data['rel_type']);
		if ($check_approve_status && isset($data['approve']) && in_array(get_staff_user_id(), $check_approve_status['staffid'])) {
			$success = $this->fixed_equipment_model->update_approval_details($check_approve_status['id'], $data);
			$message = _l('approved_successfully');
			if ($success) {
				if ($data['approve'] == 1) {
					$message = _l('approved_successfully');
					$data_log = [];

					if ($signature != '') {
						$data_log['note'] = "signed_request";
					} else {
						$data_log['note'] = "approve_request";
					}
					if ($signature != '') {
						switch ($data['rel_type']) {
							case 1:
								$path = FIXED_EQUIPMENT_STOCK_IMPORT_MODULE_UPLOAD_FOLDER . $data['rel_id'];
								break;
							case 2:
								$path = FIXED_EQUIPMENT_STOCK_EXPORT_MODULE_UPLOAD_FOLDER . $data['rel_id'];
								break;
							case 3:
								$path = FIXED_EQUIPMENT_LOST_ADJUSTMENT_MODULE_UPLOAD_FOLDER . $data['rel_id'];
								break;
							case 4:
								$path = FIXED_EQUIPMENT_INTERNAL_DELIVERY_MODULE_UPLOAD_FOLDER . $data['rel_id'];
								break;
							case 5:
								$path = FIXED_EQUIPMENT_PACKING_LIST_MODULE_UPLOAD_FOLDER . $data['rel_id'];
								break;
						}
						fe_process_digital_signature_image($signature, $path, 'signature_' . $check_approve_status['id']);
						$message = _l('sign_successfully');
					}
					$data_log['rel_id'] = $data['rel_id'];
					$data_log['rel_type'] = $data['rel_type'];
					$data_log['staffid'] = get_staff_user_id();
					$data_log['date'] = date('Y-m-d H:i:s');
					$this->fixed_equipment_model->add_activity_log($data_log);
					$check_approve_status = $this->fixed_equipment_model->check_approval_details($data['rel_id'], $data['rel_type']);
					if ($check_approve_status === true) {
						$this->fixed_equipment_model->update_approve_request($data['rel_id'], $data['rel_type'], 1);
						$open_warehouse_modal = true;
					}
				} else {
					$message = _l('rejected_successfully');
					$data_log = [];
					$data_log['rel_id'] = $data['rel_id'];
					$data_log['rel_type'] = $data['rel_type'];
					$data_log['staffid'] = get_staff_user_id();
					$data_log['date'] = date('Y-m-d H:i:s');
					$data_log['note'] = "rejected_request";
					$this->fixed_equipment_model->add_activity_log($data_log);
					$this->fixed_equipment_model->update_approve_request($data['rel_id'], $data['rel_type'], '-1');
				}
			}
		}

		$data_new = [];
		$data_new['send_mail_approve'] = $data;
		$this->session->set_userdata($data_new);
		echo json_encode([
			'success' => $success,
			'message' => $message,
			'open_warehouse_modal' => $open_warehouse_modal,
			'receipt_delivery_type' => $receipt_delivery_type,
		]);
		die();
	}


	/**
	 * send mail
	 * @param  integer $id
	 * @return json
	 */
	public function send_mail()
	{
		if ($this->input->is_ajax_request()) {
			// $data = $this->input->post();
			$data = $this->input->get();
			if ((isset($data)) && $data != '') {
				$this->fixed_equipment_model->send_mail($data);
				$success = 'success';
				echo json_encode([
					'success' => $success,
				]);
			}
		}
	}

	/**
	 * goods delivery
	 * @return view
	 */
	public function goods_delivery($id = '', $edit_approval = false)
	{

		$this->load->model('clients_model');
		$this->load->model('taxes_model');
		if ($this->input->post()) {
			$message = '';
			$data = $this->input->post();
			if (!$this->input->post('id')) {
				$mess = $this->fixed_equipment_model->add_goods_delivery($data);
				if ($mess) {
					// Approve
					$staff_id = get_staff_user_id();
					$rel_type = 'inventory_delivery';
					$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
					$process = '';
					if ($check_proccess) {
						if ($check_proccess->choose_when_approving == 0) {
							$this->fixed_equipment_model->send_request_approve($mess, $rel_type, $staff_id);
							// End update status to waiting approve
							set_alert('success', _l('fe_successful_submission_of_approval_request'));
						} else {
							set_alert('success', _l('fe_created_successfully'));
						}
					}
				} else {
					set_alert('warning', _l('fe_create_failed'));
				}
				redirect(admin_url('fixed_equipment/inventory?tab=inventory_delivery#' . $mess));
			} else {
				$id = $this->input->post('id');
				$mess = $this->fixed_equipment_model->update_goods_delivery($data);

				// if($data['save_and_send_request'] == 'true'){
				// 	$this->save_and_send_request_send_mail(['rel_id' => $id, 'rel_type' => '2', 'addedfrom' => get_staff_user_id()]);
				// }

				if ($mess) {
					set_alert('success', _l('fe_updated_successfully'));
				}
				redirect(admin_url('fixed_equipment/inventory?tab=inventory_delivery#' . $id));
			}
		}
		//get vaule render dropdown select
		$data['commodity_code_name'] = $this->fixed_equipment_model->get_commodity_code_name();
		$data['units_code_name'] = $this->fixed_equipment_model->get_units_code_name();
		$data['units_warehouse_name'] = $this->fixed_equipment_model->get_warehouse_code_name();
		$data['title'] = _l('goods_delivery');

		$data['commodity_codes'] = $this->fixed_equipment_model->get_commodity();
		$warehouse_data = $this->fixed_equipment_model->get_warehouses();
		$data['warehouses'] = $warehouse_data;

		$data['taxes'] = $this->taxes_model->get();
		$data['ajaxItems'] = false;
		$data['items'] = $this->fixed_equipment_model->wh_get_grouped('inventory_delivery');
		//sample
		$goods_delivery_row_template = '';
		if (is_numeric($id)) {
			$goods_delivery = $this->fixed_equipment_model->get_goods_delivery($id);
			if ($goods_delivery->approval == 0) {
				$goods_delivery_row_template = $this->fixed_equipment_model->create_goods_delivery_row_template();
			}
		} else {
			$goods_delivery_row_template = $this->fixed_equipment_model->create_goods_delivery_row_template();
		}

		$data['pr_orders'] = [];
		$data['pr_orders_status'] = false;

		$data['customer_code'] = $this->clients_model->get();
		if ($edit_approval) {
			$invoices_data = $this->db->query('select *, iv.id as id from ' . db_prefix() . 'invoices as iv left join ' . db_prefix() . 'projects as pj on pj.id = iv.project_id left join ' . db_prefix() . 'clients as cl on cl.userid = iv.clientid  order by iv.id desc')->result_array();
			$data['invoices'] = $invoices_data;
		} else {
			$data['invoices'] = $this->fixed_equipment_model->get_invoices();
		}
		$data['goods_code'] = $this->fixed_equipment_model->create_goods_delivery_code();
		$data['staff'] = $this->fixed_equipment_model->get_staff();
		$data['current_day'] = date('Y-m-d');

		if ($id != '') {
			$is_purchase_order = false;
			$goods_delivery = $this->fixed_equipment_model->get_goods_delivery($id);
			if (!$goods_delivery) {
				blank_page('Stock export Not Found', 'danger');
			}
			$data['goods_delivery_detail'] = $this->fixed_equipment_model->get_goods_delivery_detail($id);
			$data['goods_delivery'] = $goods_delivery;

			if (isset($goods_delivery->pr_order_id) && (float)$goods_delivery->pr_order_id > 0) {
				$is_purchase_order = true;
			}

			if (count($data['goods_delivery_detail']) > 0) {
				$index_receipt = 0;
				foreach ($data['goods_delivery_detail'] as $delivery_detail) {
					if ($delivery_detail['commodity_code'] != null && is_numeric($delivery_detail['commodity_code'])) {
						$index_receipt++;
						$unit_name = '';
						$taxname = '';
						$expiry_date = null;
						$lot_number = null;
						$commodity_name = $delivery_detail['commodity_name'];
						$without_checking_warehouse = 0;

						if (strlen($commodity_name) == 0) {
							$commodity_name = wh_get_item_variatiom($delivery_detail['commodity_code']);
						}

						$get_commodity = $this->fixed_equipment_model->get_commodity($delivery_detail['commodity_code']);
						if ($get_commodity) {
							$without_checking_warehouse = $get_commodity->without_checking_warehouse;
						}

						$goods_delivery_row_template .= $this->fixed_equipment_model->create_goods_delivery_row_template($warehouse_data, 'items[' . $index_receipt . ']', $commodity_name, $delivery_detail['warehouse_id'], $delivery_detail['available_quantity'], $delivery_detail['quantities'], $unit_name, $delivery_detail['unit_price'], $taxname, $delivery_detail['commodity_code'], $delivery_detail['unit_id'], $delivery_detail['tax_rate'], $delivery_detail['total_money'], $delivery_detail['discount'], $delivery_detail['discount_money'], $delivery_detail['total_after_discount'], $delivery_detail['guarantee_period'], $expiry_date, $lot_number, $delivery_detail['note'], $delivery_detail['sub_total'], $delivery_detail['tax_name'], $delivery_detail['tax_id'], $delivery_detail['id'], true, $is_purchase_order, $delivery_detail['serial_number'], $without_checking_warehouse);
					}
				}
			}
		}

		//edit note after approval
		$data['edit_approval'] = $edit_approval;
		$data['goods_delivery_row_template'] = $goods_delivery_row_template;
		$get_base_currency =  get_base_currency();
		if ($get_base_currency) {
			$data['base_currency_id'] = $get_base_currency->id;
		} else {
			$data['base_currency_id'] = 0;
		}
		$this->load->view('warehouses/goods_deliverys/create_goods_delivery', $data);
	}

	/* Get item by id / ajax */
	public function get_item_by_id_inventory_delivery($id)
	{
		if ($this->input->is_ajax_request()) {
			echo json_encode($this->fixed_equipment_model->get_warehouse_info_item($id));
		}
	}


	/**
	 * get good delivery row template
	 * @return  
	 */
	public function get_good_delivery_row_template()
	{
		$name = $this->input->post('name');
		$commodity_name = $this->input->post('commodity_name');
		$warehouse_id = $this->input->post('warehouse_id');
		$available_quantity = $this->input->post('available_quantity');
		$quantities = $this->input->post('quantities');
		$unit_name = $this->input->post('unit_name');
		$unit_price = $this->input->post('unit_price');
		$taxname = $this->input->post('taxname');
		$lot_number = $this->input->post('lot_number');
		$expiry_date = $this->input->post('expiry_date');
		$commodity_code = $this->input->post('commodity_code');
		$unit_id = $this->input->post('unit_id');
		$tax_rate = $this->input->post('tax_rate');
		$discount = $this->input->post('discount');
		$note = $this->input->post('note');
		$guarantee_period = $this->input->post('guarantee_period');
		$item_key = $this->input->post('item_key');
		$item_index = $this->input->post('item_index');
		$formdata = $this->input->post('formdata');
		$without_checking_warehouse = $this->input->post('without_checking_warehouse');
		$goods_delivery_row_template = $this->fixed_equipment_model->create_goods_delivery_row_template(
			[],
			$name,
			$commodity_name,
			$warehouse_id,
			$available_quantity,
			$quantities,
			$unit_name,
			$unit_price,
			$taxname,
			$commodity_code,
			$unit_id,
			$tax_rate,
			'',
			$discount,
			'',
			'',
			$guarantee_period,
			$expiry_date,
			$lot_number,
			$note,
			'',
			'',
			'',
			$item_key,
			false,
			false,
			'',
			$without_checking_warehouse
		);
		echo $goods_delivery_row_template;
	}

	/**
	 * table manage delivery
	 * @return array
	 */
	public function table_manage_delivery()
	{
		$this->app->get_table_data(module_views_path('fixed_equipment', 'warehouses/tables/table_manage_delivery.php'));
	}


	/**
	 * view delivery
	 * @param  integer $id
	 * @return view
	 */
	public function view_delivery($id)
	{
		$request_type = 'inventory_delivery';
		$data['get_staff_sign'] = $this->fixed_equipment_model->get_staff_sign($id, $request_type);
		$data['check_approve_status'] = $this->fixed_equipment_model->check_approval_details($id, $request_type);
		$data['list_approve_status'] = $this->fixed_equipment_model->get_approval_details($id, $request_type);
		$data['payslip_log'] = $this->fixed_equipment_model->get_activity_log($id, $request_type);
		//get vaule render dropdown select
		$data['commodity_code_name'] = $this->fixed_equipment_model->get_commodity_code_name();
		$data['units_code_name'] = $this->fixed_equipment_model->get_units_code_name();
		$data['units_warehouse_name'] = $this->fixed_equipment_model->get_warehouse_code_name();

		$data['goods_delivery_detail'] = $this->fixed_equipment_model->get_goods_delivery_detail($id);

		$data['goods_delivery'] = $this->fixed_equipment_model->get_goods_delivery($id);
		$data['activity_log'] = $this->fixed_equipment_model->inventory_get_activity_log($id, 'delivery');

		$data['packing_lists'] = $this->fixed_equipment_model->get_packing_list_by_deivery_note($id);

		$data['title'] = _l('stock_export_info');
		$check_appr = $this->fixed_equipment_model->get_approve_setting('2');
		$data['check_appr'] = $check_appr;
		$data['tax_data'] = $this->fixed_equipment_model->get_html_tax_delivery($id);
		$this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['base_currency'] = $base_currency;
		$this->load->view('warehouses/goods_deliverys/view_delivery', $data);
	}


	/**
	 * delete_goods_delivery
	 * @param  [integer] $id
	 * @return [redirect]
	 */
	public function delete_goods_delivery($id)
	{
		if (!has_permission('fixed_equipment_inventory', '', 'delete')  &&  !is_admin()) {
			access_denied('inventory');
		}
		$response = $this->fixed_equipment_model->delete_goods_delivery($id);
		if ($response) {
			set_alert('success', _l('fe_deleted_successfully'));
		} else {
			set_alert('danger', _l('fe_deleted_fail'));
		}
		redirect(admin_url('fixed_equipment/inventory?tab=inventory_delivery'));
	}

	/**
	 * add activity
	 */
	public function add_activity()
	{
		$goods_delivery_id = $this->input->post('goods_delivery_id');
		if (!has_permission('fixed_equipment_inventory', '', 'edit') && !is_admin() && !has_permission('fixed_equipment_inventory', '', 'create')) {
			access_denied('fixed_equipment');
		}
		if ($this->input->post()) {
			$description = $this->input->post('activity');
			$rel_type = $this->input->post('rel_type');
			$aId     = $this->fixed_equipment_model->log_inventory_activity($goods_delivery_id, $rel_type, $description);
			if ($aId) {
				$status = true;
				$message = _l('fe_added_successfully');
			} else {
				$status = false;
				$message = _l('fe_added_fail');
			}
			echo json_encode([
				'status' => $status,
				'message' => $message,
			]);
		}
	}

	/**
	 * delete activitylog
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_activitylog($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$delete = $this->fixed_equipment_model->delete_activitylog($id);
		if ($delete) {
			$status = true;
		} else {
			$status = false;
		}
		echo json_encode([
			'success' => $status,
		]);
	}

	/**
	 * delete all item
	 * @param  string $id 
	 */
	public function delete_all_item($id_s)
	{
		$type = 'asset';
		if ($this->input->get('type')) {
			$type = $this->input->get('type');
		}
		$data_list_id = explode(',', urldecode($id_s));
		if (count($data_list_id) > 0) {
			$result =  0;
			foreach ($data_list_id as $key => $id) {
				$res =  $this->fixed_equipment_model->delete_assets($id);
				if ($res) {
					$result++;
				}
			}
			if ($result > 0) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_assets')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_assets')));
			}
		}
		if ($type == 'asset') {
			redirect(admin_url('fixed_equipment/assets'));
		}
		if ($type == 'license') {
			redirect(admin_url('fixed_equipment/licenses'));
		}
		if ($type == 'accessory') {
			redirect(admin_url('fixed_equipment/accessories'));
		}
		if ($type == 'component') {
			redirect(admin_url('fixed_equipment/components'));
		}
		if ($type == 'consumable') {
			redirect(admin_url('fixed_equipment/consumables'));
		}
	}

	/**
	 * delete all item
	 * @param  string $id 
	 */
	public function delete_all_request($id_s)
	{
		$type = 'asset';
		if ($this->input->get('type')) {
			$type = $this->input->get('type');
		}
		$data_list_id = explode(',', urldecode($id_s));
		if (count($data_list_id) > 0) {
			$result =  0;
			foreach ($data_list_id as $key => $id) {
				$res =  $this->fixed_equipment_model->delete_request($id);
				if ($res) {
					$result++;
				}
			}
			if ($result > 0) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_request')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_request')));
			}
		}
		redirect(admin_url('fixed_equipment/requested'));
	}


	/**
	 * delete all maintenances
	 * @param  string $id 
	 */
	public function delete_all_maintenance($id_s)
	{
		$data_list_id = explode(',', urldecode($id_s));
		if (count($data_list_id) > 0) {
			$result =  0;
			foreach ($data_list_id as $key => $id) {
				$res =  $this->fixed_equipment_model->delete_asset_maintenances($id);
				if ($res) {
					$result++;
				}
			}
			if ($result > 0) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_depreciations')));
			}
		}
		redirect(admin_url('fixed_equipment/assets_maintenances'));
	}

	/**
	 * delete all audit
	 * @param  string $id 
	 */
	public function delete_all_audit($id_s)
	{
		$data_list_id = explode(',', urldecode($id_s));
		if (count($data_list_id) > 0) {
			$result =  0;
			foreach ($data_list_id as $key => $id) {
				$res =  $this->fixed_equipment_model->delete_audit_request($id);
				if ($res) {
					$result++;
				}
			}
			if ($result > 0) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_audit_request')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_audit_request')));
			}
		}
		redirect(admin_url('fixed_equipment/audit_managements'));
	}

	/**
	 * order list table
	 * @return table
	 */
	public function order_list_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$this->load->model('payment_modes_model');
				$product_filter = $this->input->post('product_filter');
				$channel = $this->input->post('channel');
				$customers = $this->input->post('customers');
				$invoices = $this->input->post('invoices');
				$status = $this->input->post('status');
				$order_type = $this->input->post('order_type');

				$end_date = $this->input->post('end_date');
				$start_date = $this->input->post('start_date');
				$seller = $this->input->post('seller');

				$query = '';

				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where              = [(($query != '') ? $query : '')];
				if (isset($channel) && $channel != '') {
					if ($channel == 2 || $channel == 4) {
						array_push($where, ' where channel_id = ' . $channel);
					} else {
						array_push($where, ' where channel_id not in (2,4)');
					}
				}
				if (isset($order_type) && $order_type != '') {
					if (count($where) > 1) {
						if ($order_type == 'order') {
							array_push($where, ' and type = "order"');
						} elseif ($order_type == 'booking') {
							array_push($where, ' and type = "booking"');
						} elseif ($order_type == 'return') {
							array_push($where, ' and original_order_id is not null');
						}
					} else {
						if ($order_type == 'order') {
							array_push($where, 'where type = "order"');
						} elseif ($order_type == 'booking') {
							array_push($where, 'where type = "booking"');
						} elseif ($order_type == 'return') {
							array_push($where, 'where original_order_id is not null');
						}
					}
				}
				if (isset($customers) && $customers != '') {
					if (count($where) > 1) {
						array_push($where, ' and userid = ' . $customers);
					} else {
						array_push($where, ' where userid = ' . $customers);
					}
				}

				if (isset($invoices) && $invoices != '') {
					if (count($where) > 1) {
						array_push($where, ' and number_invoice = ' . $this->fixed_equipment_model->get_number_invoice($invoices));
					} else {
						array_push($where, ' where number_invoice = ' . $this->fixed_equipment_model->get_number_invoice($invoices));
					}
				}

				if (isset($status) && $status != '') {
					if (count($where) > 1) {
						array_push($where, ' and status = ' . $status);
					} else {
						array_push($where, ' where status = ' . $status);
					}
				}
				if (is_admin() || has_permission('fixed_equipment_order_list', '', 'view')) {
					if (isset($seller) && $seller != '') {
						if (count($where) > 1) {
							array_push($where, ' and seller = ' . $seller);
						} else {
							array_push($where, ' where seller = ' . $seller);
						}
					}
				} else {
					if (count($where) > 1) {
						array_push($where, ' and seller = ' . get_staff_user_id());
					} else {
						array_push($where, ' where seller = ' . get_staff_user_id());
					}
				}


				if ($end_date != '' && $start_date != '') {
					if (!$this->fixed_equipment_model->check_format_date($start_date)) {
						$start_date = to_sql_date($start_date);
					} else {
						$start_date = $start_date;
					}

					if (!$this->fixed_equipment_model->check_format_date($end_date)) {
						$end_date = to_sql_date($end_date);
					} else {
						$end_date = $end_date;
					}

					if (count($where) > 1) {
						array_push($where, ' and date(datecreator) between \'' . $start_date . '\' and \'' . $end_date . '\'');
					} else {
						array_push($where, ' where date(datecreator) between \'' . $start_date . '\' and \'' . $end_date . '\'');
					}
				}

				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_cart';
				$join         = [];
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'name',
					'address',
					'phone_number',
					'voucher',
					'status',
					'datecreator',
					'channel',
					'channel_id',
					'company',
					'number_invoice',
					'invoice',
					'userid',
					'allowed_payment_modes',
					'payment_method_title',
					'original_order_id',
					'type',
					'order_number'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					if ($aRow['number_invoice'] != '') {
						$id = $this->fixed_equipment_model->get_id_invoice($aRow['number_invoice']);
					}
					$row = [];
					$row[] = $aRow['id'];
					$row[] = $aRow['order_number'];
					$row[] = $aRow['datecreator'];
					$row[] = $aRow['company'];
					$row[] = (is_numeric($aRow['userid']) ? fe_get_user_group_name($aRow['userid']) : '');

					$order_type = '';
					if (is_numeric($aRow['original_order_id'])) {
						$order_type = '<span class="label label-danger">' . _l('fe_return_order') . '</span>';
					} else {
						if ($aRow['type'] == 'order') {
							$order_type = '<span class="label label-primary">' . _l('fe_sale_order') . '</span>';
						}
						if ($aRow['type'] == 'booking') {
							$order_type = '<span class="label label-success">' . _l('fe_booking') . '</span>';
						}
					}
					$row[] = $order_type;


					$channel = strtoupper(_l('fe_' . $aRow['channel']));
					$payment_mode = '';
					$data_payment = $this->payment_modes_model->get($aRow['allowed_payment_modes']);
					if ($data_payment) {
						$name = isset($data_payment->name) ? $data_payment->name : '';
						if ($name != '') {
							$payment_mode = '<span class="label label-primary">' . $name . '</span>&nbsp;';
						}
					}

					$row[] = $payment_mode;
					$row[] = $channel;
					$status = fe_get_status_by_index($aRow['status']);

					$row[] = '<span class="label label-success">' . $status . '</span>';

					$row[] = ($aRow['invoice'] != '' ? '<a href="' . admin_url('invoices#' . $id) . '" >' . $aRow['invoice'] . '</a>' : '');

					$option = '';

					if (has_permission('fixed_equipment_order_list', '', 'view') || has_permission('fixed_equipment_order_list', '', 'view_own') || is_admin()) {
						$option .= '<a href="' . admin_url('fixed_equipment/view_order_detailt/' . $aRow['id']) . '" data-toggle="tooltip" data-placement="top" data-title="' . _l('view') . '" class="btn btn-default btn-icon" data-id="' . $aRow['id'] . '" >';
						$option .= '<i class="fa fa-eye"></i>';
						$option .= '</a>';
					}

					if (is_admin() || has_permission('fixed_equipment_order_list', '', 'edit')) {

						if ($aRow['status'] == 0 && $aRow['channel_id'] == 4) {
							$option .= '<a href="' . admin_url('fixed_equipment/order_manual/' . $aRow['id']) . '" data-toggle="tooltip" data-placement="top" data-title="' . _l('edit') . '" class="btn btn-default btn-icon" data-id="' . $aRow['id'] . '" >';
							$option .= '<i class="fa fa-pencil"></i>';
							$option .= '</a>';
						}
					}
					if (is_admin() || has_permission('fixed_equipment_order_list', '', 'delete')) {
						$option .= '<a href="' . admin_url('fixed_equipment/delete_order/' . $aRow['id']) . '" data-toggle="tooltip" data-placement="top" data-title="' . _l('delete') . '" class="btn btn-danger btn-icon _delete">';
						$option .= '<i class="fa fa-remove"></i>';
						$option .= '</a>';
					}
					$row[] = $option;
					$output['aaData'][] = $row;
				}
				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * view order detailt
	 * @param  int $id
	 * @return view
	 */
	public function view_order_detailt($id)
	{
		$data_cart = $this->fixed_equipment_model->get_cart($id);
		if ($data_cart) {
			if (is_admin() || $data_cart->seller == get_staff_user_id() || has_permission('fixed_equipment_order_list', '', 'view') || has_permission('fixed_equipment_order_list', '', 'view_own')) {
				$data['id'] = $id;
				$this->load->model('currencies_model');
				$data['base_currency'] = $this->currencies_model->get_base_currency();
				if (is_numeric($data_cart->currency) && $data_cart->currency > 0) {
					$data['base_currency'] = $this->currencies_model->get($data_cart->currency);
				}
				$data['order'] = $data_cart;
				$data['order_detait'] = $this->fixed_equipment_model->get_cart_detailt_by_cart_id($id);
				if ($data['order']->number_invoice != '') {
					$data['invoice'] = $this->fixed_equipment_model->get_invoice($data['order']->number_invoice);
				}
				$this->load->model('staff_model');
				$data['staffs'] = $this->staff_model->get('staff');
				$data['title'] = $data_cart->name;
				$data['activity_log'] = [];
				if ($this->db->table_exists(db_prefix() . 'wh_goods_delivery_activity_log')) {
					$data['activity_log'] = $this->fixed_equipment_model->wh_get_activity_log($data['order']->stock_export_number, 'fixed_equipment_order');
				}
				$data['warehouses'] = $this->fixed_equipment_model->get_warehouse();
				$data['tax_data'] = $this->fixed_equipment_model->get_html_tax_manual_order($id);
				//check delivery note exist
				$goods_delivery_exist = false;
				if (is_numeric($data['order']->stock_export_number)) {
					$get_goods_delivery = $this->fixed_equipment_model->get_goods_delivery($data['order']->stock_export_number);
					if ($get_goods_delivery) {
						$goods_delivery_exist = true;
					}
				}
				$data['is_return_order'] = false;
				if (is_numeric($data_cart->original_order_id)) {
					$data['is_return_order'] = true;
				}
				$data['currency_name'] = $data['base_currency']->name;
				$data['suppliers'] = $this->fixed_equipment_model->get_suppliers();
				$data['assets'] = $this->fixed_equipment_model->get_assets('', 'asset', false, false, '', true);
				$data['goods_delivery_exist'] = $goods_delivery_exist;
				$data['issue_open'] = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$id.' AND status != "closed"');
				$data['order_issues_closed'] = $this->fixed_equipment_model->get_issue(false, 'cart_id = '.$id);
				$this->load->view('orders/cart_detailt', $data);
			} else {
				access_denied('order');
			}
		}
	}


	/**
	 * { admin change status }
	 *
	 * @param  $order_number  The order number
	 * @return json
	 */
	public function admin_change_status($order_number)
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$message = '';
			$insert_id = $this->fixed_equipment_model->change_status_order($data, $order_number, 1);
			if ($insert_id) {
				echo json_encode([
					'message' => $message,
					'success' => true
				]);
				die;
			}
		}
	}

	/**
	 * create invoice detail order
	 * @param  integer $orderid 
	 */
	public function create_invoice_detail_order($orderid)
	{
		$success = $this->fixed_equipment_model->create_invoice_detail_order($orderid);
		if ($success) {
			$message = _l('fe_created_successfully');
			set_alert('success', $message);
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $orderid));
	}

	/**
	 * check approval sign
	 * @return json 
	 */
	public function check_create_delivery_note()
	{
		$data = $this->input->post();
		$success = true;
		$message = '';
		/*check send request with type =2 , inventory delivery voucher*/
		$check_r = $this->fixed_equipment_model->check_inventory_delivery_voucher($data);
		if ($check_r['flag_export_warehouse'] == 1) {
			$message = 'approval success';
		} else {
			$message = $check_r['str_error'];
			$success = false;
		}
		echo json_encode([
			'success' => $success,
			'message' => $message,
		]);
		die;
	}

	/**
	 * create export stock ajax
	 * @return [type] 
	 */
	public function create_export_stock_ajax()
	{
		$orderid = $this->input->post('orderid');
		$success = $this->fixed_equipment_model->create_export_stock($orderid, 2);
		$status = false;
		$message = '';
		if ($success) {
			$message = _l('create_successfully');
			$status = true;
		}
		echo json_encode([
			'status' => $status,
			'message' => $message,
		]);
		die;
	}

	/**
	 * order manual
	 * @param  string $order_id 
	 */
	public function order_manual($order_id = '')
	{
		$this->load->model('taxes_model');
		$this->load->model('clients_model');
		$this->load->model('invoice_items_model');
		$this->load->model('payment_modes_model');
		if (!has_permission('fixed_equipment_order_list', '', 'view') && !has_permission('fixed_equipment_order_list', '', 'view_own') && !is_admin()) {
			access_denied('order_list');
		}

		if ($this->input->post()) {
			$data = $this->input->post();
			if ($data['id'] == '') {
				unset($data['id']);

				if (!has_permission('fixed_equipment_order_list', '', 'create') && !is_admin()) {
					access_denied('order_list');
				}

				$res = $this->fixed_equipment_model->add_manual_order($data);
				if ($res) {
					$message = _l('added_successfully');
					set_alert('success', $message);
				} else {
					$message = _l('added_fail');
					set_alert('danger', $message);
				}
				redirect(admin_url('fixed_equipment/view_order_detailt/'.$res));
			} else {

				if (!has_permission('fixed_equipment_order_list', '', 'edit') && !is_admin()) {
					access_denied('fixed_equipment_order_list');
				}

				$res = $this->fixed_equipment_model->update_manual_order($data, $order_id);
				if ($res) {
					$message = _l('updated_successfully');
					set_alert('success', $message);
				} else {
					$message = _l('update_fail');
					set_alert('danger', $message);
				}
				redirect(admin_url('fixed_equipment/view_order_detailt/'.$order_id));
			}
		}
		$data['payment_modes'] = $this->payment_modes_model->get('', [
			'expenses_only !=' => 1,
		]);
		$data['id'] = $order_id;
		$data['taxes'] = $this->taxes_model->get();
		$data['customers'] = $this->clients_model->get();
		$data['items'] = $this->fixed_equipment_model->get_model_grouped();
		$data['order_number_code'] = $this->fixed_equipment_model->incrementalHash();
		$order_manual_row_template = $this->fixed_equipment_model->create_order_manual_row_template();

		if ($order_id == '') {
			$data['title'] = _l('fe_create_manual_orders');
		} else {
			$data['title'] = _l('fe_edit_manual_orders');
			$data['order'] = $this->fixed_equipment_model->get_cart($order_id);
			$data['add_items'] = $this->fixed_equipment_model->get_cart_detailt_by_cart_id($order_id);
			if (isset($data['add_items'])) {
				$index_cart_detail = 0;
				foreach ($data['add_items'] as $cart_detail) {
					$index_cart_detail++;
					$unit_name = $cart_detail['unit_name'];
					$commodity_name = $cart_detail['product_name'];

					$order_manual_row_template .= $this->fixed_equipment_model->create_order_manual_row_template('items[' . $index_cart_detail . ']', $cart_detail['product_name'], $cart_detail['available_quantity'], $cart_detail['quantity'], $cart_detail['prices'], $cart_detail['sku'], $cart_detail['product_id'], $cart_detail['id'], true);
				}
			}
		}

		$data['staff']     = $this->staff_model->get('', ['active' => 1]);
		$this->load->model('currencies_model');
		$data['currencies'] = $this->currencies_model->get();
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		$data['base_currency_id'] = 0;
		if (isset($data['base_currency'])) {
			$data['currency_name'] = $data['base_currency']->name;
			$data['base_currency_id'] = $data['base_currency']->id;
		}

		$data['order_manual_row_template'] = $order_manual_row_template;
		$this->load->view('orders/manual_orders', $data);
	}

	/**
	 * change status return order
	 * @return json 
	 */
	public function change_status_return_order()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			$success = false;
			$message = '';

			$success = $this->fixed_equipment_model->change_status_return_order($data);
			if ($success) {
				if ($data['status'] == 1) {
					$message = _l('fe_order_accepted');
				} else {
					$message = _l('fe_order_has_been_rejected');
				}
			} else {
				$message = _l('fe_order_approval_failed');
			}

			echo json_encode([
				'success' => $success,
				'message' => $message
			]);
		}
	}


	public function create_import_stock($order_id, $warehouse_id)
	{
		$response = $this->fixed_equipment_model->create_import_stock($order_id, $warehouse_id);
		if ($response == true) {
			set_alert('success', _l('created_successfully'));
		} else {
			set_alert('warning', _l('create_failed'));
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $order_id));
	}

	/**
	 * cancel invoice
	 * @param  integer $order_id          
	 * @param  integer $original_order_id 
	 */
	public function cancel_invoice($order_id, $original_order_id)
	{
		$order_data = $this->fixed_equipment_model->get_cart($original_order_id);
		if ($order_data) {
			$data_invoice = $this->fixed_equipment_model->get_invoice($order_data->number_invoice);
			if ($data_invoice) {
				$response = $this->fixed_equipment_model->cancel_invoice($order_id, $data_invoice->id);
				if ($response) {
					set_alert('success', _l('fe_canceled_successfully'));
				} else {
					set_alert('warning', _l('fe_cancel_failed'));
				}
			}
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $order_id));
	}

	/**
	 * update invoice
	 * @param  integer $order_id          
	 * @param  integer $original_order_id 
	 */
	public function update_invoice($order_id, $original_order_id)
	{
		$order_data = $this->fixed_equipment_model->get_cart($original_order_id);
		if ($order_data && $order_data->number_invoice) {
			$response = $this->fixed_equipment_model->update_invoice($order_id, $order_data->number_invoice);
			if ($response == true) {
				set_alert('success', _l('fe_updated_successfully'));
			} else {
				set_alert('warning', _l('fe_update_failed'));
			}
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $order_id));
	}

	/**
	 * shipment detail
	 * @param  string $id 
	 * @return [type]     
	 */
	public function shipment_detail($id = '')
	{
		$cart = $this->fixed_equipment_model->get_cart($id);
		$cart_detailts = $this->fixed_equipment_model->get_cart_detailt_by_master($id);
		if (!$cart) {
			blank_page(_l('shipment_not_found'));
		}
		$shipment = $this->fixed_equipment_model->get_shipment_by_order($id);
		if (!$shipment) {
			blank_page(_l('shipment_not_found'));
		}
		$data = [];
		$data['cart'] = $cart;
		$data['cart_detailts'] = $cart_detailts;
		$data['title']          = $data['cart']->order_number;
		$data['shipment']          = $shipment;
		$data['order_id']          = $id;

		if ($data['cart']->number_invoice != '') {
			$data['invoice'] = $this->fixed_equipment_model->get_invoice($data['cart']->number_invoice);
		}

		//get activity log
		$data['arr_activity_logs'] = $this->fixed_equipment_model->wh_get_shipment_activity_log($shipment->id);
		$wh_shipment_status = fe_shipment_status();
		$shipment_staus_order = '';
		foreach ($wh_shipment_status as $shipment_status) {
			if ($shipment_status['name'] ==  $data['shipment']->shipment_status) {
				$shipment_staus_order = $shipment_status['order'];
			}
		}

		foreach ($wh_shipment_status as $shipment_status) {
			if ((int)$shipment_status['order'] <= (int)$shipment_staus_order) {
				$data[$shipment_status['name']] = ' completed';
			} else {
				$data[$shipment_status['name']] = '';
			}
		}
		$data['shipment_staus_order'] = $shipment_staus_order;

		//get delivery note
		if (is_numeric($data['cart']->stock_export_number)) {
			$this->db->where('id', $data['cart']->stock_export_number);
			$data['goods_delivery'] = $this->db->get(db_prefix() . 'fe_goods_delivery')->result_array();
			$data['packing_lists'] = $this->fixed_equipment_model->get_packing_list_by_deivery_note($data['cart']->stock_export_number);

			//update goods delivery id
			$this->db->where('cart_id', $data['cart']->id);
			$this->db->update(db_prefix() . 'fe_omni_shipments', ['goods_delivery_id' => $data['cart']->stock_export_number]);
		}

		$this->load->view('shipments/shipment_detail', $data);
	}

	/**
	 * shipment activity log modal
	 * @return [type] 
	 */
	public function shipment_activity_log_modal()
	{
		if ($this->input->is_ajax_request()) {
			$request_data = $this->input->get();

			$data = [];
			$data['shipment_id'] = $request_data['shipment_id'];
			$data['id'] = $request_data['id'];
			$data['cart_id'] = $request_data['cart_id'];
			$allow_attachment = false;

			$get_shipment_by_order = $this->fixed_equipment_model->get_shipment_by_order($request_data['cart_id']);
			if ($get_shipment_by_order && $get_shipment_by_order->shipment_status == 'product_dispatched') {
				$allow_attachment = true;
			}
			if ($request_data['id'] != '') {

				$data['activity_log'] = $this->fixed_equipment_model->wh_get_activity_log_by_id($request_data['id']);

				$arr_commodity_file = $this->fixed_equipment_model->get_shipment_log_attachments($request_data['id']);
				/*get images old*/
				$images_old_value = '';

				if (count($arr_commodity_file) > 0) {
					foreach ($arr_commodity_file as $key => $value) {
						$images_old_value .= '<div class="dz-preview dz-image-preview image_old' . $value["id"] . '">';
						$rel_type = 'shipment_image';

						$images_old_value .= '<div class="dz-image">';
						if (file_exists(FIXED_EQUIPMENT_SHIPMENT_UPLOAD . $value["rel_id"] . '/' . $value["file_name"])) {
							$images_old_value .= '<a  class="images_w_table" target="blank_page" href="' . site_url('modules/fixed_equipment/uploads/shipments/' . $value["rel_id"] . '/' . $value["file_name"]) . '"><img class="image-w-h" data-dz-thumbnail alt="' . $value["file_name"] . '" src="' . site_url('modules/fixed_equipment/uploads/shipments/' . $value["rel_id"] . '/' . $value["file_name"]) . '"></a>';
						}

						if ($rel_type != '') {
							$images_old_value .= '</div>';

							$images_old_value .= '<div class="dz-error-mark">';
							$images_old_value .= '<a class="dz-remove" data-dz-remove>Remove file';
							$images_old_value .= '</a>';
							$images_old_value .= '</div>';

							if (get_staff_user_id() == $value['staffid'] || is_admin()) {
								$images_old_value .= '<div class="remove_file">';
								$images_old_value .= '<a href="#" class="text-danger" onclick="delete_product_attachment(this,' . $value["id"] . ',' . '\'' . $rel_type . '\'); return false;"><i class="fa fa fa-times"></i></a>';
								$images_old_value .= '</div>';
							}

							$images_old_value .= '</div>';
						}
					}
				}

				$data['images_old_value'] = $images_old_value;
			}
			$data['allow_attachment'] = $allow_attachment;

			$response = $this->load->view('shipments/modals/add_edit_activity_log_modal', $data, true);
			echo json_encode([
				'data' => $response,
			]);
		}
	}

	/**
	 * shipment add edit activity log
	 * @return [type] 
	 */
	public function shipment_add_edit_activity_log()
	{
		if ($this->input->post()) {
			$data = $this->input->post();
			if (!has_permission('fixed_equipment_inventory', '', 'edit') && !is_admin() && !has_permission('fixed_equipment_inventory', '', 'create')) {
				access_denied('fixed_equipment');
			}

			$cart_id = '';
			if ($data['id'] == '') {
				unset($data['id']);
				$cart_id = $data['cart_id'];
				unset($data['cart_id']);
				$date = to_sql_date($data['date'], true);
				$result =  $this->fixed_equipment_model->log_inventory_activity($data['rel_id'], 'shipment', $data['description'], $date);

				if ($result) {
					echo json_encode([
						'url'       => admin_url('fixed_equipment/shipment_detail/' . $cart_id),
						'shipment_log_id' => $result,
						'cart_id' => $cart_id,
					]);
					die;
				}

				echo json_encode([
					'url' => admin_url('fixed_equipment/shipment_detail/' . $cart_id),
				]);
				die;
			} else {
				$cart_id = $data['cart_id'];
				unset($data['cart_id']);
				$data['date'] = to_sql_date($data['date'], true);
				$result =  $this->fixed_equipment_model->update_activity_log($data['id'], $data);

				echo json_encode([
					'url'       => admin_url('fixed_equipment/shipment_detail/' . $cart_id),
					'shipment_log_id' => $data['id'],
					'cart_id' => $cart_id,
				]);
				die;

				if ($result) {
					set_alert('success', _l('fe_updated_successfully'));
				}
				redirect(admin_url('fixed_equipment/shipment_detail/' . $cart_id));
			}
		}
	}

	/**
	 * shipment managements table
	 * @return json 
	 */
	public function shipment_managements_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_omni_shipments';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'cart_id',
					'shipment_hash',
					'shipment_number',
					'planned_shipping_date',
					'shipment_status',
					'datecreated'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					// $_data .= '<a href="'.admin_url('fixed_equipment/delete_depreciations/'.$aRow['id'].'').'" class="text-primary">' . _l('fe_view') . '</a>';
					// $_data .= ' | <a href="javascript:void(0)" data-id="' . $aRow['id'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					$_data .= '<a href="' . admin_url('fixed_equipment/delete_shipment/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					$_data .= '</div>';
					$row[] = $aRow['shipment_number'] . $_data;
					$row[] = '<a href="' . admin_url('fixed_equipment/view_order_detailt/' . $aRow['cart_id'] . '') . '" >' . fe_get_order_name($aRow['cart_id'], true) . '</a>';
					$row[] = _l($aRow['shipment_status']);
					$row[] = _dt($aRow['datecreated']);


					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}


	/**
	 * delete shipment
	 * @param  integer $id       
	 * @param  string $rel_type 
	 * @return [type]           
	 */
	public function delete_shipment($id)
	{
		$company_id = get_company_user_id();
		if (!cpn_has_permission('shipments', $company_id, '', 'delete')) {
			asm_access_denied('company');
		}
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_shipment($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_shipment')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_shipment')));
			}
		}
		redirect(admin_url('fixed_equipment/inventory?tab=shipments'));
	}

	/**
	 * inventory history managements table
	 * @return json 
	 */
	public function inventory_history_managements_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_goods_transaction_details';
				$join         = [];
				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'rel_type',
					'rel_id',
					'rel_id_detail',
					'item_id',
					'old_quantity',
					'quantity',
					'rate',
					'expiry_date',
					'lot_number',
					'from_warehouse_id',
					'to_warehouse_id',
					'date_add',
					'added_from_id',
					'added_from_type'
				]);
				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$row[] = _l('fe_' . $aRow['rel_type']);
					$transaction_id = '';
					if ($aRow['rel_type'] == 'inventory_receiving') {
						$transaction_id = '<a href="' . admin_url('fixed_equipment/inventory?tab=inventory_receiving#' . $aRow['rel_id']) . '">' . fe_get_inventory_receiving_code($aRow['rel_id']) . '</a>';
					}
					if ($aRow['rel_type'] == 'inventory_delivery') {
						$transaction_id = '<a href="' . admin_url('fixed_equipment/inventory?tab=inventory_delivery#' . $aRow['rel_id']) . '">' . fe_get_inventory_delivery_code($aRow['rel_id']) . '</a>';
					}
					$row[] = $transaction_id;
					$row[] = fe_item_name($aRow['item_id'], true);
					$row[] = $aRow['old_quantity'];
					$row[] = $aRow['quantity'];
					$row[] = fe_get_warehouse_name($aRow['from_warehouse_id']);
					$row[] = _dt($aRow['date_add']);
					$output['aaData'][] = $row;
				}
				echo json_encode($output);
				die();
			}
		}
	}

	public function create_audit_order($id)
	{
		$data =  [];
		$cart_data = $this->fixed_equipment_model->get_cart($id);
		if ($cart_data) {
			$cart_detail_data = $this->fixed_equipment_model->get_cart_detailt_by_master($id);
			$data["title"] = _l('fe_audit_from_order') . ' ' . $cart_data->order_number;
			$data["audit_date"] = date('Y-m-d');
			$data["auditor"] = get_staff_user_id();
			$data["asset_location"] = "";
			$data["model_id"] = "";
			$data["checkin_checkout_status"] = "";

			$asset_id = [];
			$assets_detailt = [];
			foreach ($cart_detail_data as $key => $item) {
				$item_id = $item['product_id'];
				$data_item = $this->fixed_equipment_model->get_assets($item_id);
				if ($data_item) {
					$asset_id[] = $item_id;
					$quantity = $this->get_quantity_asset_by_type($item_id, $data_item->type);
					$assets_name = '';
					if ($data_item->series != '' && $data_item->assets_name != '') {
						$assets_name = $data_item->series . ' - ' . $data_item->assets_name;
					} elseif ($data_item->series == '' && $data_item->assets_name != '') {
						$assets_name = $data_item->assets_name;
					} elseif ($data_item->series != '' && $data_item->assets_name == '') {
						$assets_name = $data_item->series;
					}
					array_push($assets_detailt, [
						$item_id,
						$assets_name,
						ucfirst($data_item->type),
						$quantity
					]);
				}
			}
			$data["asset_id"] = $asset_id;
			$data["assets_detailt"] = json_encode($assets_detailt);
			$data['from_order'] = $id;
			$insert_id = $this->fixed_equipment_model->create_audit_request($data);
			if (is_numeric($insert_id)) {
				$this->fixed_equipment_model->update_cart($id, ['audit_id' => $insert_id]);
				// Approve
				$staff_id = get_staff_user_id();
				$rel_type = 'audit';
				$check_proccess = $this->fixed_equipment_model->get_approve_setting($rel_type, false);
				$process = '';
				if ($check_proccess) {
					if ($check_proccess->choose_when_approving == 0) {
						$this->fixed_equipment_model->send_request_approve($insert_id, $rel_type, $staff_id);
						$process = 'not_choose';
						set_alert('success', _l('fe_successful_submission_of_approval_request'));
					} else {
						$process = 'choose';
						set_alert('success', _l('fe_created_successfully'));
					}
				} else {
					// Auto checkout if not approve process
					// Change status
					$this->db->where('id', $insert_id);
					$this->db->update(db_prefix() . 'fe_audit_requests', ['status' => 1]);
					$process = 'no_proccess';
					set_alert('success', _l('fe_approved'));
				}
				// End Approve
				redirect(admin_url('fixed_equipment/view_audit_request/' . $insert_id . '?process=' . $process));
			} else {
				set_alert('danger', _l('fe_request_failed'));
				redirect(admin_url('fixed_equipment/view_order_detailt/' . $id));
			}
		}
		redirect(admin_url('fixed_equipment/audit_managements'));
	}

	/**
	 * shipment managements table
	 * @return json 
	 */
	public function packing_list_managements_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$select = [
					'id',
					'id',
					'id',
					'id',
					'id',
					'id'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_packing_lists';
				$join         = [];

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'delivery_note_id',
					'packing_list_number',
					'packing_list_name',
					'sales_order_reference',
					'clientid',
					'subtotal',
					'total_amount',
					'discount_total',
					'additional_discount',
					'total_after_discount',
					'billing_street',
					'billing_city',
					'billing_state',
					'billing_zip',
					'billing_country',
					'shipping_street',
					'shipping_city',
					'shipping_state',
					'shipping_zip',
					'shipping_country',
					'client_note',
					'admin_note',
					'approval',
					'datecreated',
					'delivery_status',
					'staff_id'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options text-nowrap">';
					// $_data .= '<a href="'.admin_url('fixed_equipment/view_packing_list/'.$aRow['id'].'').'" class="text-primary">' . _l('fe_view') . '</a>';
					// $_data .= ' | <a href="javascript:void(0)" data-id="' . $aRow['id'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					if ((has_permission('fixed_equipment_inventory', '', 'delete') || is_admin())) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_packing_list/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';
					$row[] = $aRow['packing_list_number'] . $_data;
					$row[] = $aRow['sales_order_reference'];
					$row[] = '<div class="text-nowrap">' . fe_get_customer_name($aRow['clientid']) . '</div>';
					$arr_ship_from = [];
					$arr_ship_to = [];
					if ($aRow['billing_street'] != '') {
						$arr_ship_from[] = $aRow['billing_street'];
					}
					if ($aRow['billing_city'] != '') {
						$arr_ship_from[] = $aRow['billing_city'];
					}
					if ($aRow['billing_state'] != '') {
						$arr_ship_from[] = $aRow['billing_state'];
					}
					if ($aRow['billing_zip'] != '') {
						$arr_ship_from[] = $aRow['billing_zip'];
					}
					if ($aRow['billing_country'] != '') {
						$arr_ship_from[] = get_country_short_name($aRow['billing_country']);
					}

					if ($aRow['shipping_street'] != '') {
						$arr_ship_to[] = $aRow['shipping_street'];
					}
					if ($aRow['shipping_city'] != '') {
						$arr_ship_to[] = $aRow['shipping_city'];
					}
					if ($aRow['shipping_state'] != '') {
						$arr_ship_to[] = $aRow['shipping_state'];
					}
					if ($aRow['shipping_zip'] != '') {
						$arr_ship_to[] = $aRow['shipping_zip'];
					}
					if ($aRow['shipping_country'] != '') {
						$arr_ship_to[] = get_country_short_name($aRow['shipping_country']);
					}

					$row[] = ((count($arr_ship_from) > 0) ? implode(', ', $arr_ship_from) : '');
					$row[] = ((count($arr_ship_to) > 0) ? implode(', ', $arr_ship_to) : '');

					$row[] = _dt($aRow['datecreated']);

					$row[] = fe_render_delivery_status_html($aRow['id'], 'packing_list', $aRow['delivery_status']);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	/**
	 * delete packing list
	 * @param  integer $id 
	 * @return integer     
	 */
	public function delete_packing_list($id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_packing_list($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully', _l('fe_packing_list')));
			} else {
				set_alert('danger', _l('fe_deleted_fail', _l('fe_packing_list')));
			}
		}
		redirect(admin_url('fixed_equipment/inventory?tab=packing_list'));
	}

	/**
	 * view packing list
	 * @param  integer $id 
	 */
	public function view_packing_list($id)
	{
		if (!has_permission('fixed_equipment_inventory', '', 'edit') && !is_admin() && !has_permission('fixed_equipment_inventory', '', 'create')) {
			access_denied('fixed_equipment');
		}
		$packing_list = $this->fixed_equipment_model->get_packing_list($id);
		if (!$packing_list) {
			blank_page(_l('fe_packing_list_not_found'));
		}
		$packing_list_detailts = $this->fixed_equipment_model->get_packing_list_detailt_by_master($id);
		$data = [];
		$data['title']          = _l('fe_detail_packing_list');
		$data['packing_list'] = $packing_list;
		$data['packing_list_detailts'] = $packing_list_detailts;
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$this->load->view('packing_lists/packing_list_detail', $data);
	}

	/**
	 * add edit packing list
	 */
	public function add_edit_packing_list($id = '')
	{
		$data['title']          = _l('fe_add_packing_list');
		if ($id != '') {
			$data['title']          = _l('fe_edit_packing_list');
		}
		$this->load->view('packing_lists/add_packing_list', $data);
	}


	/**
	 * delivery status mark as
	 * @param  integer $status 
	 * @param  integer $id     
	 * @param  string $type   
	 * @return json         
	 */
	public function delivery_status_mark_as($status, $id, $type)
	{
		$success = $this->fixed_equipment_model->delivery_status_mark_as($status, $id, $type);
		$message = '';

		if ($success) {
			$message = _l('fe_change_delivery_status_successfully');
		}
		echo json_encode([
			'success'  => $success,
			'message'  => $message
		]);
	}

	/**
	 * create credit note order
	 * @param  integer $id 
	 */
	public function create_credit_note_order($id)
	{
		if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own') && !has_permission('credit_notes', '', 'create')) {
			access_denied('credit_notes');
		}

		$data =  [];
		$cart_data = $this->fixed_equipment_model->get_cart($id);
		if ($cart_data) {
			$cart_detail_data = $this->fixed_equipment_model->get_cart_detailt_by_master($id);
			$subtotal = 0;
			$newitems = [];
			foreach ($cart_detail_data as $key => $item) {
				$item_id = $item['product_id'];
				$data_item = $this->fixed_equipment_model->get_assets($item_id);
				if ($data_item) {
					$asset_id[] = $item_id;
					$quantity = 0;
					$prices = 0;
					$assets_name = '';
					if ($data_item->series != '' && $data_item->assets_name != '') {
						$assets_name = $data_item->series . ' - ' . $data_item->assets_name;
					} elseif ($data_item->series == '' && $data_item->assets_name != '') {
						$assets_name = $data_item->assets_name;
					} elseif ($data_item->series != '' && $data_item->assets_name == '') {
						$assets_name = $data_item->series;
					}

					if ($cart_data->type == 'order') {
						$quantity = $item['quantity'];
						$prices = $item['prices'] * (float)$quantity;
						$subtotal += $prices;
					} else {
						$quantity = $item['number_date'];
						$prices = $item['rental_value'] / $quantity;
						$subtotal += $prices * $quantity;
					}
					array_push($newitems, [
						"order" => ($key + 1),
						"description" => $assets_name,
						"long_description" => "",
						"qty" => $quantity,
						"unit" => "",
						"rate" => $prices
					]);
				}
			}
			$default_currency = 0;
			$this->load->model('currencies_model');
			$data_currencies = $this->currencies_model->get();
			foreach ($data_currencies as $currency) {
				if ($currency['isdefault'] == 1) {
					$default_currency = $currency['id'];
				}
			}


			$next_credit_note_number = get_option('next_credit_note_number');
			$_credit_note_number = str_pad($next_credit_note_number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
			$data["newitems"] = $newitems;
			$data["clientid"] = $cart_data->userid;
			$data["project_id"] = "";
			$data["billing_street"] = $cart_data->billing_street;
			$data["billing_city"] = $cart_data->billing_city;
			$data["billing_state"] = $cart_data->billing_state;
			$data["billing_zip"] = $cart_data->billing_zip;
			$data["billing_country"] = $cart_data->billing_country;
			$data["include_shipping"] = "on";
			$data["show_shipping_on_credit_note"] = "on";
			$data["shipping_street"] = $cart_data->shipping_street;
			$data["shipping_city"] = $cart_data->shipping_city;
			$data["shipping_state"] = $cart_data->shipping_state;
			$data["shipping_zip"] = $cart_data->shipping_zip;
			$data["shipping_country"] = $cart_data->shipping_country;
			$data["date"] = date('Y-m-d');
			$data["number"] = $_credit_note_number;
			$data["currency"] = $default_currency;
			$data["discount_type"] = "";
			$data["reference_no"] = "";
			$data["adminnote"] = "";
			$data["item_select"] = "";
			$data["show_quantity_as"] = 1;
			$data["description"] = "";
			$data["long_description"] = "";
			$data["quantity"] = 1;
			$data["unit"] = "";
			$data["rate"] = "";
			$data["subtotal"] = $subtotal;
			$data["discount_percent"] = 0;
			$data["discount_total"] = 0;
			$data["adjustment"] = 0;
			$data["total"] = $subtotal;
			$data["clientnote"] = "";
			$data["terms"] = "";
			$data["save_and_send"] = true;
			$this->load->model('credit_notes_model');
			$insert_id = $this->credit_notes_model->add($data);
			if (is_numeric($insert_id)) {
				$this->fixed_equipment_model->update_cart($id, ['credit_note_id' => $insert_id]);
				set_alert('success', _l('added_successfully', _l('credit_note')));
			} else {
				set_alert('success', _l('added_failed', _l('credit_note')));
			}
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $id));
	}

	/**
	 * create estimate order
	 * @param  integer $order_id        
	 * @return integer                  
	 */
	public function create_estimate_order($id)
	{
		if (!has_permission('estimates', '', 'create')) {
			access_denied('estimates');
		}
		$data =  [];
		$cart_data = $this->fixed_equipment_model->get_cart($id);
		if ($cart_data) {
			$cart_detail_data = $this->fixed_equipment_model->get_cart_detailt_by_master($id);
			$subtotal = 0;
			$newitems = [];
			foreach ($cart_detail_data as $key => $item) {
				$item_id = $item['product_id'];
				if (is_numeric($item['maintenance_id']) && $item['maintenance_id'] > 0) {
					$data_audit = $this->fixed_equipment_model->get_asset_maintenances($item['maintenance_id']);
					if ($data_audit && $data_audit->cost > 0) {
						$asset_id[] = $item_id;
						$quantity = 0;
						$prices = 0;
						$assets_name = fe_item_name($item_id, true);
						if ($cart_data->type == 'order') {
							$quantity = $item['quantity'];
							$prices = $data_audit->cost * (float)$quantity;
							$subtotal += $prices;
						} else {
							$quantity = 1;
							$prices = $data_audit->cost;
							$subtotal += $prices;
						}
						array_push($newitems, [
							"order" => ($key + 1),
							"description" => $assets_name,
							"long_description" => "",
							"qty" => $quantity,
							"unit" => "",
							"rate" => $prices
						]);
					}
				}
			}
			$default_currency = 0;
			$this->load->model('currencies_model');
			$data_currencies = $this->currencies_model->get();
			foreach ($data_currencies as $currency) {
				if ($currency['isdefault'] == 1) {
					$default_currency = $currency['id'];
				}
			}
			$next_estimate_number = get_option('next_estimate_number');
			$_estimate_number = str_pad($next_estimate_number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
			$data["newitems"] = $newitems;
			$data["clientid"] = $cart_data->userid;
			$data["project_id"] = "";
			$data["billing_street"] = $cart_data->billing_street;
			$data["billing_city"] = $cart_data->billing_city;
			$data["billing_state"] = $cart_data->billing_state;
			$data["billing_zip"] = $cart_data->billing_zip;
			$data["billing_country"] = $cart_data->billing_country;
			$data["include_shipping"] = "on";
			$data["shipping_street"] = $cart_data->shipping_street;
			$data["shipping_city"] = $cart_data->shipping_city;
			$data["shipping_state"] = $cart_data->shipping_state;
			$data["shipping_zip"] = $cart_data->shipping_zip;
			$data["shipping_country"] = $cart_data->shipping_country;
			$data["date"] = date('Y-m-d');
			$data["number"] = $_estimate_number;
			$data["currency"] = $default_currency;
			$data["discount_type"] = "";
			$data["reference_no"] = "";
			$data["adminnote"] = "";
			$data["item_select"] = "";
			$data["show_quantity_as"] = 1;
			$data["description"] = "";
			$data["long_description"] = "";
			$data["quantity"] = 1;
			$data["unit"] = "";
			$data["rate"] = "";
			$data["subtotal"] = $subtotal;
			$data["discount_percent"] = 0;
			$data["discount_total"] = 0;
			$data["adjustment"] = 0;
			$data["total"] = $subtotal;
			$data["clientnote"] = "";
			$data["terms"] = "";
			$data["save_and_send"] = true;
			$data["status"] = 1;
			$this->load->model('estimates_model');
			$insert_id = $this->estimates_model->add($data);
			if (is_numeric($insert_id)) {
				$this->fixed_equipment_model->update_cart($id, ['estimate_id' => $insert_id]);
				set_alert('success', _l('added_successfully', _l('estimate')));
			} else {
				set_alert('success', _l('added_failed', _l('estimate')));
			}
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/' . $id));
	}

	/**
	 * check exist serial
	 * @param  string $serial   
	 * @param  integer $asset_id 
	 * @return string           
	 */
	public function check_exist_serial_inventory($serial)
	{
		$message = '';
		$serial = trim(urldecode($serial));
		$data = $this->fixed_equipment_model->check_exist_serial($serial, '');
		if ($data) {
			$message = _l('fe_this_serial_number_exists_in_the_system');
		}
		echo json_encode($message);
	}

	/**	
	 * send notify
	 */
	public function send_notify(){
		$data = $this->input->post();
		$this->fixed_equipment_model->send_notify_new_object($data);
		echo true;
	}

	/**
	 * detail predefined_kits
	 */
	public function assign_asset_predefined_kit($id)
	{
		$this->load->model('currencies_model');
		$data['id'] = $id;
		$data['assets'] = $this->fixed_equipment_model->get_assets($id);
		if ($data['assets']) {
			$data['title']  = $data['assets']->assets_name;
		}
		$base_currency = $this->currencies_model->get_base_currency();
		$data['currency_name'] = '';
		if (isset($base_currency)) {
			$data['currency_name'] = $base_currency->name;
		}
		$this->load->view('predefined_kits/assign_asset_predefined_kit', $data);
	}

	/**
	 * assign asset predefined kit table
	 */
	public function assign_asset_predefined_kit_table()
	{
		if ($this->input->is_ajax_request()) {
			if ($this->input->post()) {
				$parent_id = $this->input->post('id');
				$select = [
					'id',
					'name',
					'assign_data',
					'datecreated'
				];
				$where        = [];
				$aColumns     = $select;
				$sIndexColumn = 'id';
				$sTable       = db_prefix() . 'fe_assign_asset_predefined_kits';
				$join         = [];
				array_push($where, 'AND parent_id = ' . $parent_id);

				$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
					'id',
					'name',
					'assign_data',
					'datecreated',
					'parent_id'
				]);


				$output  = $result['output'];
				$rResult = $result['rResult'];
				foreach ($rResult as $aRow) {
					$row = [];
					$row[] = $aRow['id'];
					$_data = '';
					$_data .= '<div class="row-options">';
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'edit')) {
						$_data .= '<a href="javascript:void(0)" data-parent_id="' . $aRow['parent_id'] . '" data-id="' . $aRow['id'] . '" data-name="' . $aRow['name'] . '" onclick="edit(this); return false;" class="text-danger">' . _l('fe_edit') . '</a>';
					}
					if (is_admin() || (has_permission('fixed_equipment_predefined_kits', '', 'edit') && has_permission('fixed_equipment_predefined_kits', '', 'delete'))) {
						$_data .= ' | ';
					}
					if (is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'delete')) {
						$_data .= '<a href="' . admin_url('fixed_equipment/delete_assign_predefined_kits/' . $parent_id . '/' . $aRow['id'] . '') . '" class="text-danger _delete">' . _l('fe_delete') . '</a>';
					}
					$_data .= '</div>';
					
					$row[] = $aRow['name'] . $_data;

					$assign_data = '';
					if($aRow['assign_data'] != ''){
						$assign_json = json_decode($aRow['assign_data']);
						if(is_object($assign_json) && $assign_json = (array)$assign_json){
							foreach($assign_json as $assign){
								foreach($assign as $asset_id){
									$assign_data .= '<a class="mleft10 label label-primary" target="_blank" href="' . admin_url('fixed_equipment/detail_asset/' . $asset_id . '?tab=details') . '">' . fe_item_name($asset_id, true) . '</a>';
								}
							}
						}
					}
					$row[] = $assign_data;
					$row[] = _dt($aRow['datecreated']);

					$output['aaData'][] = $row;
				}

				echo json_encode($output);
				die();
			}
		}
	}

	public function assign_asset_predefined_kits(){
		if (!has_permission('estimates', '', 'create')) {
			access_denied('estimates');
		}
		if($this->input->post()){
			$data = $this->input->post();
			if($data['id'] == ''){
				$insert_id = $this->fixed_equipment_model->add_assign_asset_predefined_kits($data);
				if (is_numeric($insert_id)) {
					set_alert('success', _l('added_successfully'));
				} else {
					set_alert('success', _l('added_failed'));
				}
			}
			else{
				$result = $this->fixed_equipment_model->update_assign_asset_predefined_kits($data);
				if ($result) {
					set_alert('success', _l('fe_updated_successfully'));
				} else {
					set_alert('danger', _l('fe_no_data_changes'));
				}
			}
			redirect(admin_url('fixed_equipment/assign_asset_predefined_kit/' . $data['parent_id']));
		}
		redirect(admin_url('fixed_equipment/dashboard'));
	}

	/**
	 * delete model predefined_kits
	 * @param  integer $id 
	 */
	public function delete_assign_predefined_kits($parent_id, $id)
	{
		if ($id != '') {
			$result =  $this->fixed_equipment_model->delete_assign_predefined_kits($id);
			if ($result) {
				set_alert('success', _l('fe_deleted_successfully'));
			} else {
				set_alert('danger', _l('fe_deleted_fail'));
			}
		}
		redirect(admin_url('fixed_equipment/assign_asset_predefined_kit/' . $parent_id));
	}

	/**	
	 * get modal content assign asset
	 */
	public function get_modal_content_assign_asset($parent_id, $id = ''){
		$assign_data = '';
		if(is_numeric($id) && $id > 0){
			$data_assign = $this->fixed_equipment_model->get_assign_asset_predefined_kits($id);
			if($data_assign){
				$assign_data = $data_assign->assign_data;
			}
		}
		$html = $this->load->view('predefined_kits/includes/assign_asset_modal_content.php', ['id' => $parent_id, 'assign_data' => $assign_data], true);
		echo json_encode([
			'data' => $html
		]);
		die;
	}

	/**	
	 * get available kit
	 */
	public function get_available_kit($id){
		$data_available_kit = $this->fixed_equipment_model->get_assign_asset_predefined_kits('', 'parent_id = '.$id);

		$html = render_select('available_kit', $data_available_kit, array('id', 'name'), 'fe_please_select_a_kit', '', ['onchange' => 'get_warning_available_kit(this)']);

		echo json_encode([
			'data' => $html
		]);
		die;
	}

	/**	
	 * get warning available kit
	 */
	public function get_warning_available_kit($id, $kit_id = ''){
		$error = false;
		$html = '';
		$_model_lists = $this->fixed_equipment_model->get_model_predefined_kits($id);
		if ($_model_lists) { 
			foreach ($_model_lists as $model) {
				$model_id = $model['id'];
				$quantity = $model['quantity'];
				$asset_list = $this->fixed_equipment_model->list_asset_checkout_predefined_kit_by_model($model_id, $quantity); 
				if ($asset_list->msg != '') { 
					$error = true;
					$html .= '<div class="alert alert-danger">'.$asset_list->msg.'</div>';
				} 
			} 
		} 
		echo json_encode([
			'error' => $error,
			'data' => $html
		]);
		die;
	}
	
	/**
	 * generate serial number
	 * @param  integer $serial_number_quantity 
	 * @return [type]                          
	 */
	public function generate_serial_number($serial_number_quantity = 1) {
		if ($this->input->is_ajax_request()) {
			$serial_numbers = $this->fixed_equipment_model->create_serial_numbers($serial_number_quantity, true);
			echo json_encode([
				'serial_numbers' => $serial_numbers,
			]);
		}
	}

	/**
	 * fill multiple serial number modal
	 * @return [type] 
	 */
	public function fill_multiple_serial_number_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$data = [];
		$data['title'] = _l('fe_enter_the_serial_number');
		$slug = $this->input->post('slug');

		if($slug == 'add'){
			$quantity = $this->input->post('quantity');
			$prefix_name = $this->input->post('prefix_name');

		}else{
			$actual_serial_number = 0;
			$quantity = $this->input->post('quantity');
			$serial_data = [];
			$serial_input_value = $this->input->post('serial_input_value');
			$serial_input_value = new_explode(',', $serial_input_value);

			if(count($serial_input_value) > 0){
				foreach ($serial_input_value as $value) {
					if($actual_serial_number < $quantity){

						if($value != 'null'){
							$serial_data[] = ['serial_number' => $value];
						}else{
							$serial_data[] = ['serial_number' => ''];
						}
					}
					$actual_serial_number++;
				}
			}
			$prefix_name = $this->input->post('prefix_name');
			$data['edit_serial_number_data'] = $serial_data;
		}


		$data['min_row'] = $quantity;
		$data['max_row'] = $quantity;
		$data['prefix_name'] = $prefix_name;
		$data['serial_number_quantity'] = $quantity;

		$this->load->view('warehouses/goods_receipts/serial_modal', $data);
	}

	/**
	 * get asset fill data
	 * @return [type] 
	 */
	public function get_asset_data()
    {
    	$data = $this->input->post();
    	$assets = $this->fixed_equipment_model->list_asset_by_model($data['model_id']);
    	echo json_encode([
    		'assets' => $assets
    	]);
    }

    /**
     * model update batch rate
     * @return [type] 
     */
    public function model_update_batch_rate()
	{
		$total_updated = 0;
		$data = $this->input->post();
		if($data){
			$data_update = [];
			if(isset($data['requestable']) && $data['requestable'] == 1){
				$data_update['requestable'] = 1;
			}else{
				$data_update['requestable'] = 0;
			}

			if(isset($data['for_sell']) && $data['for_sell'] == 1){
				$data_update['for_sell'] = 1;
				$data_update['selling_price'] = (float)$data['selling_price'];

			}else{
				$data_update['for_sell'] = 0;
			}

			if(isset($data['for_rent']) && $data['for_rent'] == 1){
				$data_update['for_rent'] = 1;
				$data_update['rental_price'] = (float)$data['rental_price'];
				$data_update['renting_period'] = $data['renting_period'];
				$data_update['renting_unit'] = $data['renting_unit'];
			}else{
				$data_update['for_rent'] = 0;
			}

			if($data['select_item'] == 0){
				// update by model_id
				$this->db->where('model_id', $data['model']);
				$this->db->update(db_prefix().'fe_assets', $data_update);
				$total_updated = $this->db->affected_rows();
				
			}else{
				// update by asset_id
				$this->db->where('model_id', $data['model']);
				if(isset($data['asset_id']) && is_array($data['asset_id'])){
					$this->db->where('id IN ('.implode(',', $data['asset_id']).')');
				}
				$this->db->update(db_prefix().'fe_assets', $data_update);
				$total_updated = $this->db->affected_rows();
			}
			set_alert('success', _l('fe_updated_successfully').': '.$total_updated.' '. _l('fe_assets'));
			redirect(admin_url('fixed_equipment/settings?tab=models'));
		}
		redirect(admin_url('fixed_equipment/settings?tab=models'));
	}

	/**
	 * client change data
	 * @param  [type] $customer_id     
	 * @param  string $current_invoice 
	 * @return [type]                  
	 */
	public function client_change_data($customer_id, $current_invoice = '')
    {
        if ($this->input->is_ajax_request()) {
            $data                     = [];
            $data['billing_shipping'] = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);

            echo json_encode($data);
        }
    }

    /**
     * get model by id
     * @param  [type] $id 
     * @return [type]     
     */
    public function get_model_by_id($id)
    {
    	if ($this->input->is_ajax_request()) {
    		$item = [];
    		if (is_numeric($id)) {
    			$total_available_qty = 0;
    			$rate = 0;
    			$asset = $this->fixed_equipment_model->get_assets($id);
    			switch ($asset->type) {
    				case 'accessory':
    				$query = 'select quantity, selling_price from ' . db_prefix() . 'fe_assets where id = ' . $id;
    				$data_asset = $this->fixed_equipment_model->data_query($query);
    				if ($data_asset) {
    					$total_available_qty = $data_asset->quantity;
    					$rate = (float)$data_asset->selling_price;
    				}
    				break;
    				case 'consumable':
    				$query = 'select quantity, selling_price from ' . db_prefix() . 'fe_assets where id = ' . $id;
    				$data_asset = $this->fixed_equipment_model->data_query($query);
    				if ($data_asset) {
    					$total_available_qty = $data_asset->quantity;
    					$rate = (float)$data_asset->selling_price;

    				}
    				break;
    				case 'component':
    				$query = 'select quantity, selling_price from ' . db_prefix() . 'fe_assets where id = ' . $id;
    				$data_asset = $this->fixed_equipment_model->data_query($query);
    				if ($data_asset) {
    					$total_available_qty = $data_asset->quantity;
    					$rate = (float)$data_asset->selling_price;

    				}
    				break;
    				case 'license':
    				$query = 'select selling_price from ' . db_prefix() . 'fe_assets where id = ' . $id;
    				$data_license = $this->fixed_equipment_model->data_query($query);
    				if ($data_license) {
    					$rate = (float)$data_license->selling_price;
    				}
    				$query = 'select count(id) as total_seat from ' . db_prefix() . 'fe_seats where license_id = ' . $id .' AND to_id = 0';
    				$data_asset = $this->fixed_equipment_model->data_query($query);
    				if ($data_asset) {
    					$total_available_qty = $data_asset->total_seat;
    				}
    				break;
    				default:
    				$total_available_qty = 1;
    				break;
    			}

    			$item['model_id'] = $id;
    			$item['name'] = $asset->assets_name;
    			$item['rate'] = $rate;
    			$item['total_available_qty'] = $total_available_qty;

    		}else{
    			$model_id = str_replace('model-', '', $id);
    			$total_available_qty = 0;
    			$data_model = $this->fixed_equipment_model->get_models($model_id);
    			$total_available_qty = count($this->fixed_equipment_model->get_assets('', '', false, false, 'deployable', false, $model_id));

    			if ($data_model) {
    				$item['model_id'] = $id;
    				$item['name'] = $data_model->model_no . ' ' . $data_model->model_name;
    				$item['rate'] = '';
    				$item['total_available_qty'] = $total_available_qty;
    			}
    		}
    		echo json_encode($item);
    	}
    }

    /**
     * get serial number
     * @return [type] 
     */
    public function get_serial_number()
	{

		if ($this->input->is_ajax_request()) {
			$table_serial_number = '';
			$data = $this->input->post();
			$model_id = str_replace('model-', '', $data['product_id']);
			$quantity = $data['quantity'];
			$commodity_name = $data['description'];


			$arr_serial_numbers = [];
			$arr_list_temporaty_serial_number = [];

			$total_available_qty = $this->fixed_equipment_model->get_assets('', '', false, false, 'deployable', false, $model_id);
			$list_serial_numbers = $this->fixed_equipment_model->get_assets('', '', false, false, 'deployable', false, $model_id);
			$list_temporaty_serial_numbers = $this->fixed_equipment_model->get_assets('', '', false, false, 'deployable', false, $model_id, $quantity);

			foreach ($list_temporaty_serial_numbers as $list_temporaty_serial_number) {
			    $arr_list_temporaty_serial_number[$list_temporaty_serial_number['series']] = $list_temporaty_serial_number['series'];
			}

			foreach ($list_serial_numbers as $list_serial_number) {
				if(!isset($arr_list_temporaty_serial_number[$list_serial_number['series']])){
					$arr_serial_numbers[$list_serial_number['series']] = [
						'name' => $list_serial_number['series'],
					];
				}
			}

			foreach ($list_temporaty_serial_numbers as $index => $serial_number) {

				$arr_serial_numbers = array_merge(array($serial_number['series'] => array('name' => $serial_number['series']) ), $arr_serial_numbers);

				$table_serial_number .= '<tr class="sortable serial_number_item"><div class="row">';
				$table_serial_number .= '<div class="col-md-6"><td class="">' . $serial_number['series'].' '.$serial_number['assets_name'] . '</td></div>';
				$table_serial_number .= '<div class="col-md-6"><td class="serial_number">' . render_select('serial_number['.$index.']', $arr_serial_numbers,array('name','name'),'',$serial_number['series'],[], ["data-none-selected-text" => _l('fe_serial_number')], 'no-margin', '', false) . '</td><input name="product_id['.$index.']" type="hidden" value="'.$serial_number['id'].'"></input><input name="product_name['.$index.']" type="hidden" value="'.$serial_number['assets_name'].'"></input><input name="product_rate['.$index.']" type="hidden" value="'.$serial_number['selling_price'].'"></input></div>';
				$table_serial_number .= '</div></tr>';

				if(isset($arr_serial_numbers[$serial_number['series']])){
					unset($arr_serial_numbers[$serial_number['series']]);
				}
			}

			echo json_encode([
				'table_serial_number' => $table_serial_number,
				'status' => new_strlen($table_serial_number) > 0 ? true : false,
			]);
		}
	}

	/**
	 * get manual order row template
	 * @return [type] 
	 */
	public function get_manual_order_row_template()
	{
		$name = $this->input->post('name');
		$description = $this->input->post('description');
		$product_id = $this->input->post('product_id');
		$quantities = $this->input->post('quantities');
		$available_quantity = $this->input->post('available_quantity');
		$rate = $this->input->post('rate');
		$sku = $this->input->post('sku');
		$item_key = $this->input->post('item_key');
		$item_index = $this->input->post('item_index');
		$formdata = $this->input->post('formdata');

		$manual_order_row_template = '';
		$temporaty_quantity = $quantities;
		$temporaty_available_quantity = $available_quantity;
		$list_temporaty_serial_numbers = [];
		$list_temporaty_product_ids = [];
		$list_temporaty_product_name = [];
		$list_temporaty_product_rate = [];

		if(is_array($formdata) && count($formdata) > 1){

			foreach ( $formdata as $key => $form_value) {
				if($form_value['name'] != 'csrf_token_name'){
					if(preg_match('/^serial_number/', $form_value['name'])){

						$list_temporaty_serial_numbers[] = [
							'serial_number' => $form_value['value'],
						];
					}elseif(preg_match('/^product_id/', $form_value['name'])){
						$list_temporaty_product_ids[] = [
							'product_id' => $form_value['value'],
						];
					}elseif(preg_match('/^product_name/', $form_value['name'])){
						$list_temporaty_product_name[] = [
							'product_name' => $form_value['value'],
						];
					}elseif(preg_match('/^product_rate/', $form_value['name'])){
						$list_temporaty_product_rate[] = [
							'product_rate' => $form_value['value'],
						];
					}
				}
			}
		}else{
			// $list_temporaty_serial_numbers = $this->warehouse_model->get_list_temporaty_serial_numbers($commodity_code, $warehouse_id, $quantities);
		}

		foreach ($list_temporaty_serial_numbers as $key => $value) {
			$description = $value['serial_number'];
			$description .= isset($list_temporaty_product_name[$key]) ? ' '.$list_temporaty_product_name[$key]['product_name'] : '';
			$rate = isset($list_temporaty_product_rate[$key]) ? (float)$list_temporaty_product_rate[$key]['product_rate'] : 0;
			$product_id = isset($list_temporaty_product_ids[$key]) ? (float)$list_temporaty_product_ids[$key]['product_id'] : 0;
			$sku = '';

			// check change serial Number manual
			$asset = $this->fixed_equipment_model->get_assets($product_id);
			if($asset->series != $value['serial_number']){

				$this->db->where('series', $value['serial_number']);
				$this->db->where('model_id', $asset->model_id);
				$new_asset = $this->db->get(db_prefix().'fe_assets')->row();
				if($new_asset){
					$description = $value['serial_number'];
					$description .= $new_asset->assets_name;
					$rate = $new_asset->selling_price;
					$product_id = $new_asset->id;
				}
			}

			$quantities = 1;
			$name = 'newitems['.$item_index.']';

			$manual_order_row_template .= $this->fixed_equipment_model->create_order_manual_row_template($name, $description, $temporaty_available_quantity, $quantities, $rate, $sku, $product_id, $item_key, false);

			$temporaty_quantity--;
			$temporaty_available_quantity--;
			$item_index ++;
		}

		if($temporaty_quantity > 0){
			$rate = 0;
			if (is_numeric($product_id)) {
				$total_available_qty = 0;
				$asset = $this->fixed_equipment_model->get_assets($product_id);
				if($asset){
					$rate = (float)$asset->selling_price;
				}
			}

			$quantities = $temporaty_quantity;
			$available_quantity = $temporaty_available_quantity;
			$name = 'newitems['.$item_index.']';

			$manual_order_row_template .= $this->fixed_equipment_model->create_order_manual_row_template($name, $description, $available_quantity, $quantities, $rate, $sku, $product_id, $item_key, false);
			$item_index ++;
		}


		echo $manual_order_row_template;
	}

	/**
	 * load serial number modal
	 * @return [type] 
	 */
	public function load_serial_number_modal()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		$table_serial_number = $this->input->post('table_serial_number');
		$data = [];
		$data['title'] = _l('fe_select_the_serial_number');
		$data['table_serial_number'] = $table_serial_number;

		$this->load->view('orders/serial_modal', $data);
	}

	/**
	 * add edit issue
	 * @param string $id 
	 */
	public function add_edit_issue($id='', $orderid = '')
	{
		if (!has_permission('fixed_equipment_order_list', '', 'view')  && !is_admin()) {
			access_denied('fixed_equipment_order_list');
		}
		
		if ($this->input->post()) {
			$data = $this->input->post();
			$data = issue_data_processing($data);
			if(isset($data['csrf_token_name'])){
				unset($data['csrf_token_name']);
			}
			if ($id == '' || $id == 0) {
				if (!has_permission('fixed_equipment_order_list', '', 'create') && !is_admin()) {
					access_denied('fixed_equipment_order_list');
				}

				$id = $this->fixed_equipment_model->add_issue($data);
				if ($id) {

					$url = admin_url('fixed_equipment/issue_detail/'.$id);
					if ($id) {
						set_alert('success', _l('fe_added_successfully'));
						/*upload multifile*/
						echo json_encode([
							'url' => $url,
							'issueid' => $id,
							'add_or_update' => 'add',
						]);
						die;
					}
					set_alert('success', _l('fe_added_successfully'));
				}

			} else {
				if (!has_permission('fixed_equipment_order_list', '', 'edit') && !is_admin()) {
					access_denied('fixed_equipment_order_list');
				}
				if(isset($data['id'])){
					unset($data['id']);
				}
				$response = $this->fixed_equipment_model->update_issue($data, $id);

				$url = admin_url('fixed_equipment/issue_detail/'.$id);
				if ($response) {
					set_alert('success', _l('updated_successfully'));
				}
				/*upload multifile*/
				echo json_encode([
					'url' => $url,
					'issueid' => $id,
					'add_or_update' => 'update',
				]);
				die;
			}
		}

		if(is_numeric($id) && $id != 0){
			$data['is_edit'] = false;
			$data['title'] = _l('fe_edit_issue');
			$data['ticket'] = $this->fixed_equipment_model->get_issue($id);
			$orderid = $data['ticket']->cart_id;
			$data['issue_attachments'] = $this->fixed_equipment_model->fe_get_attachments_file($id, 'fixe_issue');


		}else{
			$data['is_edit'] = true;
			$data['title'] = _l('fe_new_issue');
		}
		$this->load->model('staff_model');
		$data['clients'] = $this->clients_model->get();
		$data['ticket_code'] = $this->fixed_equipment_model->create_issue_numbers();
		$data['orderid'] = $orderid;
		$data['order'] = $this->fixed_equipment_model->get_cart($orderid);
		$data['order_details'] = $this->fixed_equipment_model->get_cart_detailt_by_cart_id($orderid);
		$data['staffs'] = $this->staff_model->get('', ['active' => 1]);
		$data['id'] = $id;

		$this->load->view('orders/issues/add_edit_issue', $data);
	}

	/**
	 * add_issue_attachment
	 * @param [type] $id 
	 */
	public function add_issue_attachment($id)
	{
		issue_handle_movement_attachments($id);

		// $url = admin_url('fixed_equipment/order_list/'.$id);
		$url = admin_url('fixed_equipment/issue_detail/'.$id);
		echo json_encode([
			'url' => $url,
			'id' => $id,
		]);
	}

	/**
	 * delete_issue_pdf_file
	 * @param  [type] $attachment_id 
	 * @return [type]                
	 */
	public function delete_issue_pdf_file($attachment_id)
	{
		if (!has_permission('fixed_equipment_order_list', '', 'delete') && !is_admin()) {
			access_denied('fixed_equipment_order_list');
		}

		$folder_name = ISSUE_UPLOAD;
		echo json_encode([
			'success' => $this->fixed_equipment_model->delete_issue_pdf_file($attachment_id, $folder_name),
		]);
	}

	/**
	 * view_pdf_file
	 * @param  [type] $id       
	 * @param  [type] $rel_id   
	 * @param  [type] $rel_type 
	 * @return [type]           
	 */
	public function view_pdf_file($id, $rel_id)
	{
		$data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
		$data['current_user_is_admin'] = is_admin();
		$data['file'] = $this->fixed_equipment_model->get_file($id, $rel_id);
		$dir_path = ISSUE_UPLOAD;
		$path = ISSUE_UPLOAD_PATH;

		$data['dir_path'] = $dir_path;
		$data['upload_path'] = $path;

		if (!$data['file']) {
			header('HTTP/1.0 404 Not Found');
			die;
		}
		$this->load->view('orders/issues/preview_pdf_file', $data);
	}

	public function issue_detail($id = '')
	{

		$ticket = $this->fixed_equipment_model->get_issue($id);
		if (!$ticket) {
			blank_page(_l('issue_not_found'));
		}
		
		$data = [];

		$data['ticket'] = $ticket;
		$data['title']          = $data['ticket']->code;
		$data['staffs'] = $this->staff_model->get('', ['active' => 1]);
		$data['ticket_histories'] = $this->fixed_equipment_model->get_issue_history($id);
		$data['ticket_post_internal_histories'] = $this->fixed_equipment_model->get_issue_post_internal_history($id, true);
		$data['issue_the_sames'] = $this->fixed_equipment_model->find_similar_content_issue($id);

		// invoices
		$client_id = $data['ticket']->client_id;
		$data['issue_attachments'] = $this->fixed_equipment_model->fe_get_attachments_file($id, 'fixe_issue');

		$this->load->view('orders/issues/issue_detail', $data);
	}

	/**
	 * issue_post_internal_reply
	 * @param  string $id 
	 * @return [type]     
	 */
	public function issue_post_internal_reply($id = '')
	{
		$data = $this->input->post();
		if ($data) {
			if (!isset($data['id'])) {

				$id = $this->fixed_equipment_model->add_issue_internal_reply($data);
				if ($id) {
					set_alert('success', _l('fe_added_successfully'));
				}
				redirect(admin_url('fixed_equipment/issue_detail/'.$data['ticket_id']));
			}
		}
	}

	/**
	 * delete_issue_history
	 * @param  [type] $id   
	 * @param  [type] $type 
	 * @return [type]       
	 */
	public function delete_issue_history($id, $type)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$delete = $this->fixed_equipment_model->delete_issue_history($id, $type);
		if($delete){
			$status = true;
		}else{
			$status = false;
		}

		echo json_encode([
			'success' => $status,
		]);
	}

	/**
	 * fixed equipment order list status mark_as
	 * @param  [type] $status 
	 * @param  [type] $id     
	 * @param  [type] $type   
	 * @return [type]         
	 */
	public function fixed_equipment_order_list_status_mark_as($status, $id, $type)
	{
		$success = $this->fixed_equipment_model->fe_issue_status_mark_as($status, $id, $type);
		$message = '';

		if ($success) {
			$message = _l('fe_change_status_successfully');
		}
		echo json_encode([
			'success'  => $success,
			'message'  => $message
		]);
	}

	/**
	 * delete issue
	 * @param  [type] $id 
	 * @return [type]     
	 */
	public function delete_issue($id)
	{
		if (!has_permission('fixed_equipment_order_list', '', 'delete')  && !is_admin()) {
			access_denied('fixed_equipment_order_list');
		}
		$issue = $this->fixed_equipment_model->get_issue($id);
		$success = $this->fixed_equipment_model->delete_issue($id);
		if ($success) {
			set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(admin_url('fixed_equipment/view_order_detailt/'.$issue->cart_id));
	}

}
