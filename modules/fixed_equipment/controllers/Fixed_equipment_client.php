<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a warehouse client.
 */
class fixed_equipment_client extends ClientsController
{

	/**
	 * __construct description
	 */
	public function __construct()
	{
		parent::__construct();
		if(get_option('fe_show_public_page') != 1 && get_option('fe_show_customer_asset') != 1){
			redirect(site_url('authentication/login'));
		}
		$this->load->model('fixed_equipment_model');
		$this->load->model('departments_model');
	}

	/**
	* index 
	* @param  int $page 
	* @param  int $group_id   
	* @param  string $key  
	* @return view       
	*/
	public function index($page='', $group_id = '', $warehouse = '',$key = ''){  
		if($page == ''  || $group_id == ''){
			access_denied('Projects');
		}
		if($warehouse == ''|| !is_numeric($warehouse)){
			$warehouse = 0;
		}
		if($page == '' || !is_numeric($page)){
			$page = 1;
		}
		if($group_id == ''){
			$group_id = 0;
		}
		if($key != ''){
			$key = trim(urldecode($key));
			$data['keyword'] = $key;
		}

		$data['ofset'] = 24;
		$data['title'] = _l('fe_sales');
		$data['group_product'] = [
			['id' => 'asset', 'name' => _l('fe_assets')],
			['id' => 'license', 'name' => _l('fe_licenses')],
			['id' => 'accessory', 'name' => _l('fe_accessories')],
			['id' => 'component', 'name' => _l('fe_components')],
			['id' => 'consumable', 'name' => _l('fe_consumables')]
		];  

		$data['group_id'] = $group_id;
		$data_product = $this->fixed_equipment_model->get_list_product_by_group($group_id, $warehouse, $key, (($page-1) * $data['ofset']), $data['ofset']);
		$data['product'] = [];
		$date = date('Y-m-d');
		foreach ($data_product['list_product'] as $item) {
			$discount_percent = 0;
			array_push($data['product'], array(
				'id' => $item['id'],
				'name' => ($item['series'] != '' ? $item['series'].' ' : "").''.$item['assets_name'],
				'without_checking_warehouse' => 0,
				'price' => $item['selling_price'],
				'rental_price' => $item['rental_price'],
				'for_rent' => $item['for_rent'],
				'for_sell' => $item['for_sell'],
				'renting_period' => $item['renting_period'],
				'renting_unit' => $item['renting_unit'],
				'w_quantity' => $item['max_qty'], 
				'discount_percent' => $discount_percent,
				'has_variation' => ($item['for_sell'] == 1 && $item['for_rent'] != 1 ? 0 : 1),
				'type' => $item['type'],
				'model_id' => $item['model_id'],
				'price_discount' => 0
			));
		}
		$data['title_group'] = _l('fe_all_items');
		$data['page'] = $page;
		$data['ofset_count'] = $data_product['count'];
		$data['total_page'] = ceil($data['ofset_count']/$data['ofset']);
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$this->data($data);
		$this->view('client/sales');
		$this->layout();
	} 

	/**
	 * view_cart
	 * @param  string $id 
	 * @return      
	 */
	public function view_cart($id = ''){
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$data['title'] = _l('cart');
		$data['logged'] = $id;
		$this->data($data);
		$this->view('client/cart/cart');
		$this->layout();
	}

	/**
	 * check out
	 * @param  int $id 
	 * @return redirect 
	 */
	public function check_out($id = 0, $type = 'order')
	{   
		if(is_client_logged_in()) {
			if($id == 0){
				redirect(site_url('fixed_equipment/fixed_equipment_client/view_cart/1'));         
			}
			else{
				redirect(site_url('fixed_equipment/fixed_equipment_client/view_overview/'.$type));        
			}
		}
		else{
			redirect_after_login_to_current_url();
			redirect(site_url('authentication/login'));
		}  
	}

		/**
		 * { view overview }
		 *
		 * @param      string  $id     The identifier
		 * @return  redirect
		 */
		public function view_overview($type = 'order'){
			if($this->input->post()){
				$data = $this->input->post();
				$res = $this->fixed_equipment_model->check_out($data);                                    
				if(is_numeric($res) && $res > 0){
					if($type == 'order'){
						set_alert('success', _l('fe_ordered_successfully', _l('client')));
					}
					else{
						set_alert('success', _l('fe_booked_successfully', _l('client')));						
					}
					redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
				}               
			}
			if(is_client_logged_in()){
				$data_userid = get_client_user_id();
				$data_profile = $this->clients_model->get($data_userid);
				if($data_profile){
					if($data_profile->shipping_street!='' && $data_profile->shipping_city!='' && $data_profile->shipping_street!='' && $data_profile->shipping_state!=''){
						$cookie_name = 'fe_cart_id_list';
						if($type == 'booking'){
							$cookie_name = 'fe_cart_id_list_booking';
						}

						if(isset($_COOKIE[$cookie_name])){
							$list_id = $_COOKIE[$cookie_name];
							$array_id = explode(',', $list_id);
							$list_group = [];
							$list_prices = [];
							$list_tax = [];
							foreach ($array_id as $key => $id) {
								$data_group = $this->fixed_equipment_model->get_assets($id);
								if($data_group){
									$list_group[] = 0;
									$list_prices[] = $data_group->selling_price;
									$list_tax[] = 0;
								}
							}

							$data['type'] = $type;
							$data['list_group'] = implode(',', $list_group);
							$data['list_prices'] = implode(',', $list_prices);
							$data['list_tax'] = implode(',', $list_tax);
							$data['tax'] = [];
							$this->load->model('payment_modes_model');
							$this->load->model('payments_model');
							$data['payment_modes'] = $this->payment_modes_model->get('', [
								'expenses_only !=' => 1,
							]);
							$this->load->model('currencies_model');
							$data['base_currency'] = $this->currencies_model->get_base_currency();
							$data['title'] = _l('cart');
							$this->data($data);
							$this->view('client/cart/overview_cart');
							$this->layout();
						}
						else{
							redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
						}
					}
					else{
						redirect(site_url('fixed_equipment/fixed_equipment_client/client/'.$data_userid));
					}
				}
				else{
					redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
				}
			}
			else{
				redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
			}
		}


		/**
		 * detailt 
		 * @param  int  $id 
		 * @return    view  
		 */
		public function detailt($id){
			$this->load->model('currencies_model');
			$data['base_currency'] = $this->currencies_model->get_base_currency();          
			$date = date('Y-m-d');
			$data_detailt_product = $this->fixed_equipment_model->get_assets($id);


			$data['detailt_product'] = $data_detailt_product;

			$group_id = $data_detailt_product->type;
			$group_name = _l('fe_'.$data_detailt_product->type.'');
			$data['group_id'] = $group_id;

			$max_product = 15;
			$count_product = 0;
			$data_product  = $this->fixed_equipment_model->get_list_product_by_group_s($group_id,$id,0,$max_product);
			$data['group'] = $group_name;
			$data['product'] = [];
			$data['price']  = $data_detailt_product->selling_price;
			$data['discount_percent'] = 0;
			$data['price_discount'] = 0;
			$data['amount_in_stock'] = $this->fixed_equipment_model->get_stock_quantity_item($id, true);
			$date = date('Y-m-d');
			if($data_product){

				foreach ($data_product['list_product'] as $item) {
					$discount_percent = 0;
					$quantity = $this->fixed_equipment_model->get_stock_quantity_item($item['id'], true);

					array_push($data['product'], array(
						'id' => $item['id'],
						'name' => ($item['series'] != '' ? $item['series'].' ' : "").''.$item['assets_name'],
						'without_checking_warehouse' => 0,
						'price' => $item['selling_price'],
						'rental_price' => $item['rental_price'],
						'for_rent' => $item['for_rent'],
						'for_sell' => $item['for_sell'],
						'renting_period' => $item['renting_period'],
						'renting_unit' => $item['renting_unit'],
						'w_quantity' => $quantity, 
						'discount_percent' => $discount_percent,
						'has_variation' => ($item['for_sell'] == 1 && $item['for_rent'] != 1 ? 0 : 1),
						'type' => $item['type'],
						'model_id' => $item['model_id'],
						'price_discount' => 0
					));
				}


				$count_product = $data_product['count'];

				if($count_product<$max_product){
					$data_group = $this->fixed_equipment_model->get_group_product_s($group_id);
					foreach ($data_group as $key => $group) {
						$data_product  = $this->fixed_equipment_model->get_list_product_by_group_s($group['id'], $id, 0, $max_product);
						foreach ($data_product['list_product'] as $item) {
							$discount_percent = 0;
							$quantity = $this->fixed_equipment_model->get_stock_quantity_item($item['id'], true);
							array_push($data['product'], array(
								'id' => $item['id'],
								'name' => ($item['series'] != '' ? $item['series'].' ' : "").''.$item['assets_name'],
								'without_checking_warehouse' => 0,
								'price' => $item['selling_price'],
								'rental_price' => $item['rental_price'],
								'for_rent' => $item['for_rent'],
								'for_sell' => $item['for_sell'],
								'renting_period' => $item['renting_period'],
								'renting_unit' => $item['renting_unit'],
								'w_quantity' => $quantity, 
								'discount_percent' => $discount_percent,
								'has_variation' => ($item['for_sell'] == 1 && $item['for_rent'] != 1 ? 0 : 1),
								'type' => $item['type'],
								'model_id' => $item['model_id'],
								'price_discount' => 0
							));
						}
						$count_product += $data_product['count'];
						if($count_product > $max_product){
							break;
						}
					}
				}          
			}
			$this->data($data);
			$this->view('client/detailt_product');
			$this->layout();
		}


	/**
	* search product 
	* @param  int  $group_id 
	* @return            
	*/
	public function search_product($group_id){
		if($this->input->post()){
			$data = $this->input->post();
			redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/'.$group_id.'/0/'.$data['keyword']));                    
		}
	}

	/**
	 * get product by group 
	 * @param  int $page 
	 * @param  int $id   
	 * @return    json    
	 */
	public function get_product_by_group($page = '',$group_id = '',$warehouse = '',$key = ''){  
		$data['ofset'] = 24;    

		$data_product = $this->fixed_equipment_model->get_list_product_by_group($group_id, $warehouse, $key, (($page-1) * $data['ofset']), $data['ofset']);
		$data['product'] = [];
		$date = date('Y-m-d');
		foreach ($data_product['list_product'] as $item) {
			$discount_percent = 0;
			$quantity = 1;
			if($item['type'] == 'license'){
				$avail = 0;
				$data_total = $this->fixed_equipment_model->count_total_avail_seat($item['id']);
				if($data_total){
					$avail = $data_total->avail;
				}
				$quantity = $avail;
			}
			elseif($item['type'] == 'accessory'){
				$quantity = $item['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($item['id']);
			}
			elseif($item['type'] == 'component'){
				$quantity = $item['quantity'] - $this->fixed_equipment_model->count_checkin_component_by_parents($item['id']);
			}
			elseif($item['type'] == 'consumable'){
				$quantity = $item['quantity'] - $this->fixed_equipment_model->count_checkin_asset_by_parents($item['id']);
			}

			array_push($data['product'], array(
				'id' => $item['id'],
				'name' => ($item['series'] != '' ? $item['series'].' ' : "").''.$item['assets_name'],
				'without_checking_warehouse' => 0,
				'price' => $item['selling_price'],
				'rental_price' => $item['rental_price'],
				'for_rent' => $item['for_rent'],
				'for_sell' => $item['for_sell'],
				'renting_period' => $item['renting_period'],
				'renting_unit' => $item['renting_unit'],
				'w_quantity' => $quantity, 
				'discount_percent' => $discount_percent,
				'has_variation' => ($item['for_sell'] == 1 && $item['for_rent'] != 1 ? 0 : 1),
				'type' => $item['type'],
				'model_id' => $item['model_id'],
				'price_discount' => 0
			));
		}
		$data['title_group'] = '';
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$html = $this->load->view('client/list_product/list_product_partial',$data,true);
		echo json_encode([
			'data'=>$html
		]);
		die;
	} 


	/**
	* order list
	* @param  int $tab 
	* @return   view    
	*/
	public function order_list($tab = ''){
		$data['title'] = _l('fe_order_list');
		if($tab == ''){
			$data['tab'] = 0;
		}
		else{
			$data['tab'] = $tab;
		}
		$data['status'] = $tab;        
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$data['cart_list'] = [];
		$userid = get_client_user_id();
		if(is_numeric($userid)){
			$data['cart_list'] = $this->fixed_equipment_model->get_cart_of_client_by_status($userid,$tab,'', '(channel_id = 2 OR channel_id = 6  OR channel_id = 4) and original_order_id is null');
		}
		$this->data($data);
		$this->view('client/cart/order_list');
		$this->layout();
	}

	/**
	 * view order detail
	 * @param  int $order_number 
	 * @return  view             
	 */
	public function view_order_detail($order_number){
		$this->load->model('currencies_model');
		$data['order'] = $this->fixed_equipment_model->get_cart_by_order_number($order_number);
		if($data['order']){
			$data['base_currency'] = $this->currencies_model->get_base_currency();
			if(is_numeric($data['order']->currency) && $data['order']->currency > 0){
				$data['base_currency'] = $this->currencies_model->get($data['order']->currency);
			}
			$order_id = $data['order']->id;
			$data['order_detait'] = $this->fixed_equipment_model->get_cart_detailt_by_cart_id($order_id);
			$shipment = $this->fixed_equipment_model->get_shipment_by_order($order_id);	
			if($shipment){
				$data['cart'] = $data['order'];
				$data['title']          = $data['cart']->order_number;
				$data['shipment']          = $shipment;
				$data['order_id']          = $order_id;
				if($data['cart']->number_invoice != ''){
					$data['invoice'] = $this->fixed_equipment_model->get_invoice($data['cart']->number_invoice);
				}
					//get activity log
				$data['arr_activity_logs'] = $this->fixed_equipment_model->wh_get_shipment_activity_log($shipment->id);
				$new_activity_log = [];
				foreach($data['arr_activity_logs'] as $key => $value){
					if($value['rel_type'] == 'delivery'){
						$value['description'] = preg_replace("/<a[^>]+\>[a-z]+/i", "", $value['description']);
					}
					$new_activity_log[] = $value;					
				}
				$data['arr_activity_logs'] = $new_activity_log;
				$wh_shipment_status = fe_shipment_status();
				$shipment_staus_order='';
				foreach ($wh_shipment_status as $shipment_status) {
					if($shipment_status['name'] ==  $data['shipment']->shipment_status){
						$shipment_staus_order = $shipment_status['order'];
					}
				}

				foreach ($wh_shipment_status as $shipment_status) {
					if((int)$shipment_status['order'] <= (int)$shipment_staus_order){
						$data[$shipment_status['name']] = ' completed';
					}else{
						$data[$shipment_status['name']] = '';
					}
				}
				$data['shipment_staus_order'] = $shipment_staus_order;
					//get delivery note
				if(is_numeric($data['cart']->stock_export_number)){
					$this->db->where('id', $data['cart']->stock_export_number);
					$data['goods_delivery'] = $this->db->get(db_prefix() . 'fe_goods_delivery')->result_array();
					$data['packing_lists'] = $this->fixed_equipment_model->get_packing_list_by_deivery_note($data['cart']->stock_export_number);
				}
			}			

			$data['title'] = _l('fe_order_detail');
			$this->data($data);
			$this->view('client/cart/order_detailt');
			$this->layout();
		}
		else{
			redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));         
		}
	}

	/**
	* change status order
	* @param  int $order_number 
	* @return   redirect             
	*/
	public function change_status_order($order_number){
		if($this->input->post()){
			$data = $this->input->post();
			$insert_id = $this->fixed_equipment_model->change_status_order($data,$order_number);
			if ($insert_id) {
				redirect(site_url('fixed_equipment/fixed_equipment_client/view_order_detail/'.$order_number));         
			}               
		}
	}


	/**
	* edit client info
	* @param int $id
	* @return view
	*/
	public function client($id = '')
	{
		if ($this->input->post() && !$this->input->is_ajax_request()) {
			if ($id == '') {
				redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
			} else {
				$success = $this->clients_model->update($this->input->post(), $id);
				if ($success == true) {
					set_alert('success', _l('updated_successfully', _l('client')));
				}
				redirect(site_url('fixed_equipment/fixed_equipment_client/view_overview'));
			}
		}

		$group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
		$data['group'] = $group;

		if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
			redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
		}

		$data['groups'] = $this->clients_model->get_groups();

		if ($id == '') {
			$title = _l('add_new', _l('client_lowercase'));
		} else {
			$client                = $this->clients_model->get($id);
			$data['customer_tabs'] = get_customer_profile_tabs();

			if (!$client) {
				show_404();
			}

			$data['contacts'] = $this->clients_model->get_contacts($id);
			$data['tab']      = isset($data['customer_tabs'][$group]) ? $data['customer_tabs'][$group] : null;



			if ($group == 'profile') {
				$data['customer_groups'] = $this->clients_model->get_customer_groups($id);
				$data['customer_admins'] = $this->clients_model->get_admins($id);

			} elseif ($group == 'attachments') {
				$data['attachments'] = get_all_customer_attachments($id);
			} elseif ($group == 'vault') {


				$data['vault_entries'] = hooks()->apply_filters('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));

				if ($data['vault_entries'] === -1) {
					$data['vault_entries'] = [];
				}
			} elseif ($group == 'estimates') {
				$this->load->model('estimates_model');
				$data['estimate_statuses'] = $this->estimates_model->get_statuses();
			} elseif ($group == 'invoices') {
				$this->load->model('invoices_model');
				$data['invoice_statuses'] = $this->invoices_model->get_statuses();
			} elseif ($group == 'credit_notes') {
				$this->load->model('credit_notes_model');
				$data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
				$data['credits_available']     = $this->credit_notes_model->total_remaining_credits_by_customer($id);
			} elseif ($group == 'payments') {
				$this->load->model('payment_modes_model');
				$data['payment_modes'] = $this->payment_modes_model->get();
			} elseif ($group == 'notes') {
				$data['user_notes'] = $this->misc_model->get_notes($id, 'customer');
			} elseif ($group == 'projects') {
				$this->load->model('projects_model');
				$data['project_statuses'] = $this->projects_model->get_project_statuses();
			} elseif ($group == 'statement') {
				if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
					set_alert('danger', _l('access_denied'));
					redirect(admin_url('clients/client/' . $id));
				}

				$data = array_merge($data, prepare_mail_preview_data('customer_statement', $id));
			} elseif ($group == 'map') {
				if (get_option('google_api_key') != '' && !empty($client->latitude) && !empty($client->longitude)) {

					$this->app_scripts->add('map-js', base_url($this->app_scripts->core_file('assets/js', 'map.js')) . '?v=' . $this->app_css->core_version());

					$this->app_scripts->add('google-maps-api-js', [
						'path'       => 'https://maps.googleapis.com/maps/api/js?key=' . get_option('google_api_key') . '&callback=initMap',
						'attributes' => [
							'async',
							'defer',
							'latitude'       => "$client->latitude",
							'longitude'      => "$client->longitude",
							'mapMarkerTitle' => "$client->company",
						],
					]);
				}
			}
			$data['staff'] = $this->staff_model->get('', ['active' => 1]);
			$data['client'] = $client;
			$title          = $client->company;
			$data['members'] = $data['staff'];
			if (!empty($data['client']->company)) {
				if (is_empty_customer_company($data['client']->userid)) {
					$data['client']->company = '';
				}
			}
		}

		$this->load->model('currencies_model');
		$data['currencies'] = $this->currencies_model->get();

		if ($id != '') {

			$customer_currency = $data['client']->default_currency;

			foreach ($data['currencies'] as $currency) {
				if ($customer_currency != 0) {
					if ($currency['id'] == $customer_currency) {
						$customer_currency = $currency;

						break;
					}
				} else {
					if ($currency['isdefault'] == 1) {
						$customer_currency = $currency;

						break;
					}
				}
			}

			if (is_array($customer_currency)) {
				$customer_currency = (object) $customer_currency;
			}

			$data['customer_currency'] = $customer_currency;

			$slug_zip_folder = (
				$client->company != ''
				? $client->company
				: get_contact_full_name(get_primary_contact_user_id($client->userid))
			);

			$data['zip_in_folder'] = slug_it($slug_zip_folder);
		}

		$data['bodyclass'] = 'customer-profile dynamic-create-groups';
		$data['title']     = $title;
		$this->data($data);
		$this->view('client/cart/client_info');
		$this->layout();
	}

	/**
	 * create return request
	 * @param  string $order_number 
	 */
	public function create_return_request($order_number){
		if($this->input->post()){
			$data = $this->input->post();
			$insert_id = $this->fixed_equipment_model->create_return_request_portal($data, $order_number);
			if ($insert_id) {
				set_alert('success', _l('created_successfully'));
			}  
			else{
				set_alert('danger', _l('create_failed'));
			}             
			redirect(site_url('fixed_equipment/fixed_equipment_client/view_order_detail/'.$order_number));         
		}
	}

	/**
	 * view delivery voucher
	 * @param  string $hash 
	 */
	public function view_delivery_voucher($hash){
		$hash_expl = explode('_', $hash);
		$id = $hash_expl[1];
		$this->load->model('currencies_model');

		$data['check_approve_status'] = $this->fixed_equipment_model->check_approval_details($id, 'inventory_delivery');
		$data['list_approve_status'] = $this->fixed_equipment_model->get_approval_details($id, 'inventory_delivery');

		//get vaule render dropdown select
		$data['commodity_code_name'] = $this->fixed_equipment_model->get_commodity_code_name();
		$data['units_code_name'] = $this->fixed_equipment_model->get_units_code_name();
		$data['units_warehouse_name'] = $this->fixed_equipment_model->get_warehouse_code_name();

		$data['goods_delivery_detail'] = $this->fixed_equipment_model->get_goods_delivery_detail($id);

		$data['goods_delivery'] = $this->fixed_equipment_model->get_goods_delivery($id);
		$hash = '';
		$data_invoice = $this->fixed_equipment_model->get_invoices(($data['goods_delivery']->invoice_id == '' ? 0 : $data['goods_delivery']->invoice_id));
		if($data_invoice){
			$hash = $data_invoice->hash;
		}
		$data['goods_delivery']->hash = $hash;
		$data['activity_log'] = $this->fixed_equipment_model->wh_get_activity_log($id,'delivery');
		$data['packing_lists'] = $this->fixed_equipment_model->get_packing_list_by_deivery_note($id);

		$data['title'] = _l('stock_export_info');
		$check_appr = $this->fixed_equipment_model->get_approve_setting('2');
		$data['check_appr'] = $check_appr;
		$data['tax_data'] = $this->fixed_equipment_model->get_html_tax_delivery($id);
		$this->load->model('currencies_model');
		$base_currency = $this->currencies_model->get_base_currency();
		$data['base_currency'] = $base_currency;
		$data['title'] = _l('omni_orders_detail');
		$this->data($data);
		$this->view('view_delivery/index');
		no_index_customers_area();
		$this->layout();
	}
	/**
	 * client assets
	 */
	public function client_assets(){
		$data['title'] = _l('fe_assets');
		$this->load->model('currencies_model');
		$data['base_currency'] = $this->currencies_model->get_base_currency();
		$data['asset_list'] = [];
		$userid = get_client_user_id();
		if(is_numeric($userid)){
			$data['asset_list'] = $this->fixed_equipment_model->get_client_assets_2($userid);
		}
		$this->data($data);
		$this->view('client/assets/asset_management');
		$this->layout();
	}

	/**
	 * add edit issue
	 * @param string $id 
	 */
	public function add_edit_issue($id='', $orderid = '', $model_id = '', $product_id = '')
	{
		
		if ($this->input->post()) {
			$data = $this->input->post();
		
			if ($id == '' || $id == 0) {

				$id = $this->fixed_equipment_model->add_issue($data);
				if ($id) {

					if ($id) {
						$uploadedFiles = handle_issue_attachments_array($id);
						if ($uploadedFiles && is_array($uploadedFiles)) {
							foreach ($uploadedFiles as $file) {
								$file['contact_id'] = get_contact_user_id();
								$this->misc_model->add_attachment_to_database($id, 'fixe_issue', [$file]);
							}
						}
					}
					set_alert('success', _l('fe_added_successfully'));
					redirect(site_url('fixed_equipment/fixed_equipment_client/issue_detail/' . $id));
				}

			} else {
				
				if(isset($data['id'])){
					unset($data['id']);
				}
				$response = $this->fixed_equipment_model->update_issue($data, $id);

				$uploadedFiles = handle_issue_attachments_array($id);
				if ($uploadedFiles && is_array($uploadedFiles)) {
					foreach ($uploadedFiles as $file) {
						$file['contact_id'] = get_contact_user_id();
						$this->misc_model->add_attachment_to_database($id, 'fixe_issue', [$file]);
					}
				}
				if ($response) {
					set_alert('success', _l('updated_successfully'));
				}
				/*upload multifile*/
				redirect(site_url('fixed_equipment/fixed_equipment_client/issue_detail/' . $id));
			}
		}

		if(is_numeric($id) && $id != 0){
			$data['is_edit'] = false;
			$data['title'] = _l('fe_edit_issue');
			$data['ticket'] = $this->fixed_equipment_model->get_issue($id);
			$orderid = $data['ticket']->cart_id;
			$model_id = $data['ticket']->model_id;
			$product_id = $data['ticket']->asset_id;
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
		$data['fe_ticket_status'] = fe_ticket_status();
	
		$data['order_details'] = $this->fixed_equipment_model->client_get_cart_detailt_by_cart_id($orderid, $model_id, $product_id);
		$data['staffs'] = $this->staff_model->get('', ['active' => 1]);
		$data['id'] = $id;

		$this->data($data);
		$this->view('client/assets/issues/add_edit_issue');
		$this->layout();
	}

	/**
	 * delete_issue_pdf_file
	 * @param  [type] $attachment_id 
	 * @return [type]                
	 */
	public function delete_issue_pdf_file($attachment_id)
	{

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
		$data['fe_ticket_status'] = fe_ticket_status();
		$this->data($data);
		$this->view('client/assets/issues/issue_detail');
		$this->layout();
	}

	public function delete_issue($id)
	{
		$issue = $this->fixed_equipment_model->get_issue($id);
		$success = $this->fixed_equipment_model->delete_issue($id);
		if ($success) {
			set_alert('success', _l('fe_deleted_successfully', _l('fe_depreciations')));
		} else {
			set_alert('warning', _l('problem_deleting'));
		}
		redirect(site_url('fixed_equipment/fixed_equipment_client/client_assets'));
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
				redirect(site_url('fixed_equipment/fixed_equipment_client/issue_detail/'.$data['ticket_id']));
			}
		}
	}

}