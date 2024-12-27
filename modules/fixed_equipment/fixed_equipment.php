<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Fixed Equipment Management
Description: This module provides important details such as check-in and checks outs, a location of the assets, depreciation, audit, maintenance schedule, date of return of the assets among other details.
Version: 1.0.9
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('FIXED_EQUIPMENT_MODULE_NAME', 'fixed_equipment');
define('FIXED_EQUIPMENT_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads'));
define('FIXED_EQUIPMENT_PATH', 'modules/fixed_equipment/uploads/');
define('FIXED_EQUIPMENT_IMAGE_UPLOADED_PATH', 'modules/fixed_equipment/uploads/');
define('FIXED_EQUIPMENT_REVISION', 1091);
define('FIXED_EQUIPMENT_PATH_PLUGIN', 'modules/fixed_equipment/assets/plugins');
define('FIXED_EQUIPMENT_LIBRARIES', 'modules/fixed_equipment/libraries');
define('FIXED_EQUIPMENT_IMPORT_ITEM_ERROR', 'modules/fixed_equipment/uploads/import_item_error/');

define('FIXED_EQUIPMENT_STOCK_IMPORT_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/stock_import/'));
define('FIXED_EQUIPMENT_STOCK_EXPORT_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/stock_export/'));
define('FIXED_EQUIPMENT_LOST_ADJUSTMENT_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/lost_adjustment/'));
define('FIXED_EQUIPMENT_INTERNAL_DELIVERY_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/internal_delivery/'));
define('FIXED_EQUIPMENT_PACKING_LIST_MODULE_UPLOAD_FOLDER', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/packing_lists/'));
define('FIXED_EQUIPMENT_SHIPMENT_UPLOAD', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/shipments/'));
define('ISSUE_UPLOAD', module_dir_path(FIXED_EQUIPMENT_MODULE_NAME, 'uploads/issues/'));
define('ISSUE_UPLOAD_PATH', 'modules/fixed_equipment/uploads/issues/');

hooks()->add_action('admin_init', 'fixed_equipment_permissions');
hooks()->add_action('admin_init', 'fixed_equipment_module_init_menu_items');
hooks()->add_action('app_admin_head', 'fixed_equipment_add_head_components');
hooks()->add_action('app_admin_footer', 'fixed_equipment_load_js');
hooks()->add_action('hr_profile_tab_name', 'fixed_equipment_add_tab_name');
hooks()->add_action('hr_profile_tab_content', 'fixed_equipment_add_tab_content');
hooks()->add_action('hr_profile_load_js_file', 'fixed_equipment_hr_profile_load_js_file');
hooks()->add_filter('hr_profile_load_icon', 'fixed_equipment_load_icon', 10, 2);
hooks()->add_action('head_element_client','fixed_equipment_head_element');
hooks()->add_action('client_pt_footer_js','fixed_equipment_client_foot_js');
hooks()->add_action('customers_navigation_end', 'fixed_equipment_module_init_client_menu_items');
hooks()->add_action('after_contact_login','fixed_equipment_redirect_to_pages');

// Task related issue
hooks()->add_action('task_modal_rel_type_select', 'issue_task_modal_rel_type_select'); // new
hooks()->add_filter('relation_values', 'issue_get_relation_values', 10, 2); // new
hooks()->add_filter('get_relation_data', 'issue_get_relation_data', 10, 4); // new
hooks()->add_filter('tasks_table_row_data', 'issue_add_table_row', 10, 3);

/*Attendance export excel path*/
define('FIXED_EQUIPMENT_PATH_EXPORT_FILE', 'modules/fixed_equipment/uploads/attendance/');

// hooks()->add_action('after_custom_fields_select_options','init_fixed_equipment_customfield');
//cronjob
hooks()->add_action('before_cron_run', 'cronjob_run_auto_depreciation');
hooks()->add_action('fixed_equipment_init',FIXED_EQUIPMENT_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', FIXED_EQUIPMENT_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', FIXED_EQUIPMENT_MODULE_NAME.'_predeactivate');
/**
* Register activation module hook
*/
register_activation_hook(FIXED_EQUIPMENT_MODULE_NAME, 'fixed_equipment_module_activation_hook');
/**
 * activation hook
 */
function fixed_equipment_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

register_language_files(FIXED_EQUIPMENT_MODULE_NAME, [FIXED_EQUIPMENT_MODULE_NAME]);

$CI = & get_instance();
$CI->load->helper(FIXED_EQUIPMENT_MODULE_NAME . '/fixed_equipment');

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function fixed_equipment_module_init_menu_items()
{  
	$CI = &get_instance();
	if (has_permission('fixed_equipment_dashboard', '', 'view_own') ||
		has_permission('fixed_equipment_dashboard', '', 'view') ||
		has_permission('fixed_equipment_assets', '', 'view_own') ||
		has_permission('fixed_equipment_assets', '', 'view') ||
		has_permission('fixed_equipment_licenses', '', 'view_own') ||
		has_permission('fixed_equipment_licenses', '', 'view') ||
		has_permission('fixed_equipment_accessories', '', 'view_own') ||
		has_permission('fixed_equipment_accessories', '', 'view') ||
		has_permission('fixed_equipment_consumables', '', 'view_own') ||
		has_permission('fixed_equipment_consumables', '', 'view') ||
		has_permission('fixed_equipment_components', '', 'view_own') ||
		has_permission('fixed_equipment_components', '', 'view') ||
		has_permission('fixed_equipment_predefined_kits', '', 'view_own') ||
		has_permission('fixed_equipment_predefined_kits', '', 'view') ||
		has_permission('fixed_equipment_requested', '', 'view_own') ||
		has_permission('fixed_equipment_requested', '', 'view') ||
		has_permission('fixed_equipment_maintenances', '', 'view_own') ||
		has_permission('fixed_equipment_maintenances', '', 'view') ||
		has_permission('fixed_equipment_audit', '', 'view_own') ||
		has_permission('fixed_equipment_audit', '', 'view') ||
		has_permission('fixed_equipment_report', '', 'view_own') ||
		has_permission('fixed_equipment_report', '', 'view') ||
		has_permission('fixed_equipment_depreciations', '', 'view_own') ||
		has_permission('fixed_equipment_depreciations', '', 'view') ||
		has_permission('fixed_equipment_sign_manager', '', 'view_own') ||
		has_permission('fixed_equipment_sign_manager', '', 'view') ||
		has_permission('fixed_equipment_order_list', '', 'view_own') ||
		has_permission('fixed_equipment_order_list', '', 'view') ||
		has_permission('fixed_equipment_inventory', '', 'view_own') ||
		has_permission('fixed_equipment_inventory', '', 'view') ||
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

		is_admin()) {



		$CI->app_menu->add_sidebar_menu_item('fixed_equipment', [
			'name'     => _l('fe_fixed_equipment'),
			'icon'     => 'fa fa-bullseye',
			'position' => 30,
		]);

		if (has_permission('fixed_equipment_dashboard', '', 'view_own') || has_permission('fixed_equipment_dashboard', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_dashboard',
				'name'     => _l('fe_dashboard'),
				'href'     => admin_url('fixed_equipment/dashboard'),
				'icon'     => 'fa fa-dashboard menu-icon',
				'position' =>0,
			]);
		}  

		if (has_permission('fixed_equipment_assets', '', 'view_own') || has_permission('fixed_equipment_assets', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_assets',
				'name'     => _l('fe_assets'),
				'href'     => admin_url('fixed_equipment/assets'),
				'icon'     => 'fa fa-bath',
				'position' =>1,
			]);
		} 

		if (has_permission('fixed_equipment_licenses', '', 'view_own') || has_permission('fixed_equipment_licenses', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_licenses',
				'name'     => _l('fe_licenses'),
				'href'     => admin_url('fixed_equipment/licenses'),
				'icon'     => 'fa fa-certificate',
				'position' =>2,
			]);
		} 


		if (has_permission('fixed_equipment_accessories', '', 'view_own') || has_permission('fixed_equipment_accessories', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_accessories',
				'name'     => _l('fe_accessories'),
				'href'     => admin_url('fixed_equipment/accessories'),
				'icon'     => 'fa fa-check',
				'position' =>3,
			]);
		} 


		if (has_permission('fixed_equipment_consumables', '', 'view_own') || has_permission('fixed_equipment_consumables', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_consumables',
				'name'     => _l('fe_consumables'),
				'href'     => admin_url('fixed_equipment/consumables'),
				'icon'     => 'fa fa-life-ring',
				'position' =>4,
			]);
		} 


		if (has_permission('fixed_equipment_components', '', 'view_own') || has_permission('fixed_equipment_components', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_components',
				'name'     => _l('fe_components'),
				'href'     => admin_url('fixed_equipment/components'),
				'icon'     => 'fa fa-recycle',
				'position' =>5,
			]);
		} 

		if (has_permission('fixed_equipment_predefined_kits', '', 'view_own') || has_permission('fixed_equipment_predefined_kits', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_predefined_kits',
				'name'     => _l('fe_predefined_kits'),
				'href'     => admin_url('fixed_equipment/predefined_kits'),
				'icon'     => 'fa fa-object-group',
				'position' =>6,
			]);
		} 
		if (has_permission('fixed_equipment_sign_manager', '', 'view_own') || has_permission('fixed_equipment_sign_manager', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_checkout_mgt',
				'name'     => _l('fe_sign_manager'),
				'href'     => admin_url('fixed_equipment/checkout_managements'),
				'icon'     => 'fa fa-handshake',
				'position' =>7,
			]);
		} 
		if (has_permission('fixed_equipment_requested', '', 'view_own') || has_permission('fixed_equipment_requested', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_requested',
				'name'     => _l('fe_requested'),
				'href'     => admin_url('fixed_equipment/requested'),
				'icon'     => 'fa fa-undo',
				'position' =>8,
			]);
		}

		if (has_permission('fixed_equipment_maintenances', '', 'view_own') || has_permission('fixed_equipment_maintenances', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_assets_maintenances',
				'name'     => _l('fe_maintenances'),
				'href'     => admin_url('fixed_equipment/assets_maintenances'),
				'icon'     => 'fa fa-wrench',
				'position' =>9,
			]);
		} 


		if (has_permission('fixed_equipment_audit', '', 'view_own') || has_permission('fixed_equipment_audit', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_bulk_audit',
				'name'     => _l('fe_audit'),
				'href'     => admin_url('fixed_equipment/audit_managements'),
				'icon'     => 'fa fa-file',
				'position' =>10,
			]);
		}  

		if (has_permission('fixed_equipment_depreciations', '', 'view_own') || has_permission('fixed_equipment_depreciations', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_depreciations',
				'name'     => _l('fe_depreciations'),
				'href'     => admin_url('fixed_equipment/depreciations'),
				'icon'     => 'fa fa-usd menu-icon',
				'position' =>11,
			]);
		} 


		if (has_permission('fixed_equipment_locations', '', 'view_own') || has_permission('fixed_equipment_locations', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_locations',
				'name'     => _l('fe_locations'),
				'href'     => admin_url('fixed_equipment/locations'),
				'icon'     => 'fa fa-map menu-icon',
				'position' =>12,
			]);
		} 

		if (has_permission('fixed_equipment_inventory', '', 'view_own') || has_permission('fixed_equipment_inventory', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_inventory',
				'name'     => _l('fe_inventory'),
				'href'     => admin_url('fixed_equipment/inventory?tab=inventory_receiving'),
				'icon'     => 'fa fa-indent menu-icon',
				'position' =>13,
			]);
		} 

		// if (has_permission('fixed_equipment_inventory_receiving', '', 'view_own') || has_permission('fixed_equipment_inventory_receiving', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_inventory_receiving',
		// 		'name'     => _l('fe_inventory_receiving'),
		// 		'href'     => admin_url('fixed_equipment/inventory_receiving'),
		// 		'icon'     => 'fa fa-indent menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_inventory_delivery', '', 'view_own') || has_permission('fixed_equipment_inventory_delivery', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_inventory_delivery',
		// 		'name'     => _l('fe_inventory_delivery'),
		// 		'href'     => admin_url('fixed_equipment/inventory_delivery'),
		// 		'icon'     => 'fa fa-outdent menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_internal_transfer', '', 'view_own') || has_permission('fixed_equipment_internal_transfer', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_internal_transfer',
		// 		'name'     => _l('fe_internal_transfer'),
		// 		'href'     => admin_url('fixed_equipment/internal_transfer'),
		// 		'icon'     => 'fa fa-reply-all menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_lost_adjustment', '', 'view_own') || has_permission('fixed_equipment_lost_adjustment', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_lost_adjustment',
		// 		'name'     => _l('fe_lost_adjustment'),
		// 		'href'     => admin_url('fixed_equipment/lost_adjustment'),
		// 		'icon'     => 'fa fa-industry menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_inventory_history', '', 'view_own') || has_permission('fixed_equipment_inventory_history', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_inventory_history',
		// 		'name'     => _l('fe_inventory_history'),
		// 		'href'     => admin_url('fixed_equipment/inventory_history'),
		// 		'icon'     => 'fa fa-clock-o menu-icon menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_shipments', '', 'view_own') || has_permission('fixed_equipment_shipments', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_shipments',
		// 		'name'     => _l('fe_shipments'),
		// 		'href'     => admin_url('fixed_equipment/shipments'),
		// 		'icon'     => 'fa fa-truck menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_delivery_note', '', 'view_own') || has_permission('fixed_equipment_delivery_note', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_delivery_note',
		// 		'name'     => _l('fe_delivery_note'),
		// 		'href'     => admin_url('fixed_equipment/delivery_note'),
		// 		'icon'     => 'fa fa-pencil-square menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		// if (has_permission('fixed_equipment_packing_list', '', 'view_own') || has_permission('fixed_equipment_packing_list', '', 'view') || is_admin()) {
		// 	$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
		// 		'slug'     => 'fixed_equipment_packing_list',
		// 		'name'     => _l('fe_packing_list'),
		// 		'href'     => admin_url('fixed_equipment/packing_list'),
		// 		'icon'     => 'fa fa-inbox menu-icon',
		// 		'position' =>12,
		// 	]);
		// } 

		if (has_permission('fixed_equipment_order_list', '', 'view_own') || has_permission('fixed_equipment_order_list', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_order_list',
				'name'     => _l('fe_order_list'),
				'href'     => admin_url('fixed_equipment/order_list'),
				'icon'     => 'fa fa-list menu-icon',
				'position' =>14,
			]);
		} 

		if (has_permission('fixed_equipment_report', '', 'view_own') || has_permission('fixed_equipment_report', '', 'view') || is_admin()) {
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_report',
				'name'     => _l('fe_report'),
				'href'     => admin_url('fixed_equipment/report'),
				'icon'     => 'fa fa-area-chart menu-icon menu-icon',
				'position' =>15,
			]);
		} 
		if(get_option('fe_show_public_page') == 1){
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_portal',
				'name'     => _l('fe_portal'),
				'href'     => site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'),
				'icon'     => 'fa fa-bookmark',      
				'position' =>16,
			]);
		}
		if (is_admin() || 
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
		) {
			$tab = '';
			if (!is_admin()) { 
				$permit_arr = [
					['tab' => 'depreciations', 'permit' => 'fixed_equipment_setting_depreciation'],
					['tab' => 'suppliers', 'permit' => 'fixed_equipment_setting_supplier'],
					['tab' => 'asset_manufacturers', 'permit' => 'fixed_equipment_setting_manufacturer'],
					['tab' => 'categories', 'permit' => 'fixed_equipment_setting_category'],
					['tab' => 'models', 'permit' => 'fixed_equipment_setting_model'],
					['tab' => 'status_labels', 'permit' => 'fixed_equipment_setting_status_label'],
					['tab' => 'custom_field', 'permit' => 'fixed_equipment_setting_custom_field']
				];
				foreach($permit_arr as $permit){
					if ($tab == '' && (has_permission($permit['permit'], '', 'view') || has_permission($permit['permit'], '', 'view_own'))) {          
						$tab = $permit['tab'];
						break;
					}
				}

				if($tab == ''){
					$tab = 'depreciations';
				}	       
			}
			else{
				$tab = 'depreciations';
			}
			$CI->app_menu->add_sidebar_children_item('fixed_equipment', [
				'slug'     => 'fixed_equipment_settings',
				'name'     => _l('fe_settings'),
				'href'     => admin_url('fixed_equipment/settings?tab='.$tab),
				'icon'     => 'fa fa-cogs',
				'position' =>17,
			]);
		}  
	}
}
/**
 * load js
 */
function fixed_equipment_load_js(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=depreciations') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/depreciations.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=locations') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/locations.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=suppliers') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/suppliers.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=asset_manufacturers') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/asset_manufacturers.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=categories') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/categories.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=approval_settings') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/approval_settings.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=models') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/models.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=status_labels') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/status_labels.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=permission') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/permission.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/view_model') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/view_model.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_asset') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_asset.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/licenses') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/licenses.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_licenses') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_licenses.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/accessories') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/accessories.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/consumables') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/consumables.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/components') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/components.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/predefined_kits') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/predefined_kits.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_predefined_kits') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_predefined_kits.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_accessories') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_accessories.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_consumables') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_consumables.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_components') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_components.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/assets_maintenances') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/assets_maintenances.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/requested') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/requested.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_request') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_request.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/settings?tab=custom_field') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/custom_field.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_customfield') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/detail_customfield.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/audit_request') === false) || !(strpos($viewuri, '/admin/fixed_equipment/view_audit_request') === false) || !(strpos($viewuri, '/admin/fixed_equipment/audit') === false)) {
		echo '<link rel="stylesheet prefetch" href="'.base_url('modules/purchase/assets/plugins/handsontable/chosen.css').'">';
		echo '<script src="'.base_url('modules/purchase/assets/plugins/handsontable/chosen.jquery.js').'"></script>';
		echo '<script src="'.base_url('modules/purchase/assets/plugins/handsontable/handsontable-chosen-editor.js').'"></script>' ;
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'third_party/scan_qrcodes/html5-qrcode.min.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/audit_managements') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/audit_managements.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/view_audit_request') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/view_audit_request.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/depreciations') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/depreciations.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/locations') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/locations.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/detail_locations') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/detail_locations.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		$googlemap_api_key = '';
		$api_key = get_option('fe_googlemap_api_key');
		if($api_key){
			$googlemap_api_key = $api_key;
		}	
		echo '<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>';
		echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$googlemap_api_key.'&callback=initMap&libraries=&v=weekly" defer></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/checkout_managements') === false)) {
		echo '<script src="' . site_url('assets/plugins/signature-pad/signature_pad.min.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/checkout_managements.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/inventory?tab=inventory_receiving') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/warehouses/inventory_receiving_management.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	// if (!(strpos($viewuri, '/admin/fixed_equipment/lost_adjustment') === false)) {
	// 	echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/warehouses/lost_adjustment_management.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	// }
	if (!(strpos($viewuri, '/admin/fixed_equipment/order_list') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/orders/order_list_management.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/inventory?tab=warehouse_management') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/settings/warehouse.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/assign_asset_predefined_kit/') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/predefined_kit/assign_asset_predefined_kits.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, '/admin/fixed_equipment/manage_goods_receipt') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
	}
}

/**
 * fixed equipment add head components
 */
function fixed_equipment_add_head_components(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/style.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	if (!(strpos($viewuri, '/admin/fixed_equipment/assets') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/asset_management.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/audit_request') === false) || !(strpos($viewuri, '/admin/fixed_equipment/view_audit_request') === false) || !(strpos($viewuri, '/admin/fixed_equipment/audit') === false)) {
		echo '<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>';
		echo '<link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">';
	}

	if (!(strpos($viewuri, '/admin/fixed_equipment/report') === false) || !(strpos($viewuri, '/admin/fixed_equipment/dashboard') === false)) {
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '?v=' . FIXED_EQUIPMENT_REVISION . '"></script>'; 
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/predefined_kits') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/predefined_kits.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	// if (!(strpos($viewuri, '/admin/fixed_equipment/checkout_managements') === false)) {
	// 	echo '<link href="' . site_url('assets/themes/perfex/css/style.min.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	// }
	if (!(strpos($viewuri, '/admin/fixed_equipment/audit') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/audit.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/view_order_detailt/') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/orders/view_order_detailt.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/fixed_equipment/shipment_detail/') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/shipments/shipment.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}

	if (!(strpos($viewuri, '/fixed_equipment/manage_goods_receipt') === false)) {  
        echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/handsontable/chosen.css') . '"  rel="stylesheet" type="text/css" />';
        echo '<script src="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.js') . '"></script>';
    }
}

/**
 * fixed equipment permissions
 */
function fixed_equipment_permissions()
{
	$capabilities = [];

	// dashboard
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
	];
	register_staff_capabilities('fixed_equipment_dashboard', $capabilities, _l('fe_fixed_equipment_dashboard'));

		// asset
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_assets', $capabilities, _l('fe_fixed_equipment_assets'));

		// licenses
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_licenses', $capabilities, _l('fe_fixed_equipment_licenses'));

		// accessories
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_accessories', $capabilities, _l('fe_fixed_equipment_accessories'));

		// consumables
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_consumables', $capabilities, _l('fe_fixed_equipment_consumables'));

		// components
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_components', $capabilities, _l('fe_fixed_equipment_components'));

		// predefined_kits
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_predefined_kits', $capabilities, _l('fe_fixed_equipment_predefined_kits'));

		// requested
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_requested', $capabilities, _l('fe_fixed_equipment_requested'));

		// maintenances
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_maintenances', $capabilities, _l('fe_fixed_equipment_maintenances'));

		// audit
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_audit', $capabilities, _l('fe_fixed_equipment_audit'));

	// locations
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_locations', $capabilities, _l('fe_fixed_equipment_locations'));

	// Inventory
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_inventory', $capabilities, _l('fe_fixed_equipment_inventory'));

	// Order List
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_order_list', $capabilities, _l('fe_fixed_equipment_order_list'));

	// report
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
	];
	register_staff_capabilities('fixed_equipment_report', $capabilities, _l('fe_fixed_equipment_report'));

	// sign manager
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create')
	];
	register_staff_capabilities('fixed_equipment_sign_manager', $capabilities, _l('fe_fixed_equipment_sign_manager'));

	// depreciations
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
	];
	register_staff_capabilities('fixed_equipment_depreciations', $capabilities, _l('fe_fixed_equipment_depreciations'));

	// setting - model
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_setting_model', $capabilities, _l('fe_fixed_equipment_setting_models'));

	// setting - manufacturer
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_setting_manufacturer', $capabilities, _l('fe_fixed_equipment_setting_manufacturers'));

		// setting - depreciation
		$capabilities['capabilities'] = [
			'view_own' => _l('permission_view'),
			'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
			'create' => _l('permission_create'),
			'edit' => _l('edit'),
			'delete' => _l('delete')
		];
		register_staff_capabilities('fixed_equipment_setting_depreciation', $capabilities, _l('fe_fixed_equipment_setting_depreciations'));

			// setting - category
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_setting_category', $capabilities, _l('fe_fixed_equipment_setting_categories'));

		// setting - status_label
		$capabilities['capabilities'] = [
			'view_own' => _l('permission_view'),
			'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
			'create' => _l('permission_create'),
			'edit' => _l('edit'),
			'delete' => _l('delete')
		];
		register_staff_capabilities('fixed_equipment_setting_status_label', $capabilities, _l('fe_fixed_equipment_setting_status_labels'));

			// setting - custom_field
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('fixed_equipment_setting_custom_field', $capabilities, _l('fe_fixed_equipment_setting_custom_fields'));

		// setting - supplier
		$capabilities['capabilities'] = [
			'view_own' => _l('permission_view'),
			'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
			'create' => _l('permission_create'),
			'edit' => _l('edit'),
			'delete' => _l('delete')
		];
		register_staff_capabilities('fixed_equipment_setting_supplier', $capabilities, _l('fe_fixed_equipment_setting_suppliers'));

}
/**
 * Initializes the fixed_equipment customfield.
 * @param string  $custom_field  The custom field
 */
function init_fixed_equipment_customfield($custom_field = ''){
	$select = '';
	if($custom_field != ''){
		if($custom_field->fieldto == 'fixed_equipment_name'){
			$select = 'selected';
		}
	}
	$html = '<option value="fixed_equipment_name" '.$select.' >'. _l('fe_fixed_equipment').'</option>';
	echo html_entity_decode($html);
}

/**
 * fixed equipment add tab name
 * @param  string $tab_names 
 * @return string            
 */
function fixed_equipment_add_tab_name($tab_names)
{
	$tab_names[] = 'fe_asset';
	return $tab_names;
}

/**
 * fixed equipment add tab content
 * @param  string $tab_content_link 
 * @return  string                  
 */
function fixed_equipment_add_tab_content($tab_content_link)
{
	if(!(strpos($tab_content_link, 'hr_record/includes/fe_asset') === false)){
		$tab_content_link = FIXED_EQUIPMENT_MODULE_NAME.'/employee_asset/asset_list_content';
	}
	return $tab_content_link;
}

/**
 * fixed equipment hr profile load js file
 * @param  string $group_name 
 */
function fixed_equipment_hr_profile_load_js_file($group_name)
{
	if($group_name == 'fe_asset'){
		echo require('modules/fixed_equipment/assets/js/staff_asset_js.php');		
	}
}
/**
 * fixed equipment load icon
 * @param  string $icon  
 * @param  string $group 
 * @return string        
 */
function fixed_equipment_load_icon($icon, $group)
{
    if($group == 'fe_asset'){
        $icon = '<span class="fa fa-bath fa-fw fa-lg"></span>';
    }
    return $icon;
}

/**
 * cronjob run auto depreciation
 */
function cronjob_run_auto_depreciation(){
	$CI = &get_instance();
	$CI->load->model('fixed_equipment/fixed_equipment_model');
	if(!$CI->fixed_equipment_model->check_cron_log(date('Y-m-d'), 'auto_depreciation')){
		$CI->fixed_equipment_model->auto_calculate_depreciation();
		$CI->fixed_equipment_model->insert_cron_log(date('Y-m-d'), 'auto_depreciation');
	}
}

function fixed_equipment_head_element(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/style.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/view_cart') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/cart.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/view_overview') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/view_overview_cart.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/detailt/') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/daterangepicker/css/daterangepicker.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/detail_product.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/index/') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/daterangepicker/css/daterangepicker.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/order_list') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/order_list.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/fixed_equipment/fixed_equipment_client/view_order_detail/') === false)) {
		echo '<link href="' . module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/css/clients/order_detail.css') . '?v=' . FIXED_EQUIPMENT_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
}
function fixed_equipment_client_foot_js(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/main.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/index/') === false)) {
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/advance-elements/moment-with-locales.min.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/daterangepicker/daterangepicker.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
        echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/sales_client.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    }
    if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/view_cart') === false)) {
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/cart.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    }
    if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/detailt/') === false)) {
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/advance-elements/moment-with-locales.min.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/plugins/daterangepicker/daterangepicker.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/detail_product.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    }
    if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/order_list') === false)) {
        echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/order_list.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    }
    if (!(strpos($viewuri,'/fixed_equipment/fixed_equipment_client/view_order_detail/') === false)) {
    	echo '<script src="'.module_dir_url(FIXED_EQUIPMENT_MODULE_NAME, 'assets/js/clients/order_detail.js').'?v='.FIXED_EQUIPMENT_REVISION.'"></script>';
    }
}

/**
 *  add menu item and js file to client
*/
function fixed_equipment_module_init_client_menu_items()
{
	if(get_option('fe_show_public_page') == 1){
		$add_tooltip = '';
		if(fe_get_status_modules('omni_sales')){
			$add_tooltip = 'data-placement="bottom" data-toggle="tooltip" data-title="'._l('fe_fixed_equipment').'"';                
		}
		echo '<li class="customers-nav-item-Insurances-plan">
		<a href="'.site_url('fixed_equipment/fixed_equipment_client/view_cart').'" '.$add_tooltip.'>
		<i class="fa fa-shopping-cart"></i>
		<span class="text-white fe_qty_total"></span>
		</a>
		</li>';

		echo '<li class="customers-nav-item-Insurances-plan">
		<a href="'.site_url('fixed_equipment/fixed_equipment_client/index/1/0/0').'" '.$add_tooltip.'>
		<i class="fa fa-tags"></i>
		</a>
		</li>'; 

		if(is_client_logged_in()){
			echo '<li class="customers-nav-item-Insurances-plan">
			<a href="'.site_url('fixed_equipment/fixed_equipment_client/order_list').'" '.$add_tooltip.'>'._l('order_list').'
			</a>
			</li>';
			echo '<li class="customers-nav-item-Insurances-plan">
			<a href="'.site_url('fixed_equipment/fixed_equipment_client/client_assets').'" '.$add_tooltip.'>'._l('fe_assets').'
			</a>
			</li>';
		} 
	}elseif(get_option('fe_show_customer_asset') == 1){
		if(is_client_logged_in()){
			$add_tooltip = 'data-placement="bottom" data-toggle="tooltip" data-title="'._l('fe_assets').'"';                
			
			echo '<li class="customers-nav-item-Insurances-plan">
			<a href="'.site_url('fixed_equipment/fixed_equipment_client/client_assets').'" '.$add_tooltip.'>'._l('fe_assets').'
			</a>
			</li>';
		}
	}
}


/**
 * redirect to pages 
 */
function fixed_equipment_redirect_to_pages(){
	maybe_redirect_to_previous_url();
	if(get_option('fe_show_public_page') == 1){
		redirect(site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'));
	}else{
		redirect(site_url('fixed_equipment/fixed_equipment_client/client_assets'));
	}
}

/**
 * issue task modal rel type select
 * @param  [type] $value 
 * @return [type]        
 */
function issue_task_modal_rel_type_select($value) {
    $selected = '';
    if (isset($value) && isset($value['rel_type']) && $value['rel_type'] == 'fe_issue') {
        $selected = 'selected';
    }
    echo "<option value='fe_issue' " . $selected . ">" .
    _l('fe_issue') . "
                           </option>";

}

/**
 * issue get relation values
 * @param  [type] $values   
 * @param  [type] $relation 
 * @return [type]           
 */
function issue_get_relation_values($values, $relation = null) {
    if ($values['type'] == 'fe_issue') {
    	
        if (is_array($relation)) {
            $values['id'] = $relation['id'];
            $values['name'] = $relation['code'].' '.$relation['ticket_subject'];
        } else {
            $values['id'] = $relation->id;
            $values['name'] = $relation->code.' '.$relation->ticket_subject;
        }

        $CI = &get_instance();
        $CI->load->model('fixed_equipment/fixed_equipment_model');
        $fe_issue = $CI->fixed_equipment_model->get_issue($values['id']);
        if($fe_issue){
        	$values['link'] = admin_url('fixed_equipment/issue_detail/'. $fe_issue->id);
        }else{
        	$values['link'] = '';
        }

    }

    return $values;
}

/**
 * issue get relation data
 * @param  [type] $data 
 * @param  [type] $obj  
 * @return [type]       
 */
function issue_get_relation_data($data, $obj) {
    $type = $obj['type'];
    $rel_id = $obj['rel_id'];
    $CI = &get_instance();
    $CI->load->model('fixed_equipment/fixed_equipment_model');

    if ($type == 'fe_issue') {
        if ($rel_id != '') {
            $data = $CI->fixed_equipment_model->get_issue($rel_id);
        } else {
            $data = [];
        }
    }

    return $data;
}

/**
 * issue add table row
 * @param  [type] $row  
 * @param  [type] $aRow 
 * @return [type]       
 */
function issue_add_table_row($row ,$aRow)
{

    $CI = &get_instance();
    $CI->load->model('fixed_equipment/fixed_equipment_model');

    if($aRow['rel_type'] == 'fe_issue'){
        $fe_issue = $CI->fixed_equipment_model->get_issue($aRow['rel_id']);

           if ($fe_issue) {

                $str = '<span class="hide"> - </span><a class="text-muted task-table-related" data-toggle="tooltip" title="' . _l('task_related_to') . '" href="' . admin_url('fixed_equipment/issue_detail/'.$fe_issue->id) . '">' . $fe_issue->code .' '. $fe_issue->ticket_subject . '</a><br /><div class="row-options">';

                $row[2] =  new_str_replace('<div class="row-options">', $str, $row[2]);
            }

    }

    return $row;
}
function fixed_equipment_appint(){
    $CI = & get_instance();    
    require_once 'libraries/gtsslib.php';
    $fixed_equipment_api = new FixEquipmentLic();
    $fixed_equipment_gtssres = $fixed_equipment_api->verify_license(true);    
    if(!$fixed_equipment_gtssres || ($fixed_equipment_gtssres && isset($fixed_equipment_gtssres['status']) && !$fixed_equipment_gtssres['status'])){
         $CI->app_modules->deactivate(FIXED_EQUIPMENT_MODULE_NAME);
        set_alert('danger', "One of your modules failed its verification and got deactivated. Please reactivate or contact support.");
        redirect(admin_url('modules'));
    }    
}

function fixed_equipment_preactivate($module_name){
    if ($module_name['system_name'] == FIXED_EQUIPMENT_MODULE_NAME) {             
        require_once 'libraries/gtsslib.php';
        $fixed_equipment_api = new FixEquipmentLic();
        $fixed_equipment_gtssres = $fixed_equipment_api->verify_license();          
        if(!$fixed_equipment_gtssres || ($fixed_equipment_gtssres && isset($fixed_equipment_gtssres['status']) && !$fixed_equipment_gtssres['status'])){
             $CI = & get_instance();
            $data['submit_url'] = $module_name['system_name'].'/gtsverify/activate'; 
            $data['original_url'] = admin_url('modules/activate/'.FIXED_EQUIPMENT_MODULE_NAME); 
            $data['module_name'] = FIXED_EQUIPMENT_MODULE_NAME; 
            $data['title'] = "Module License Activation"; 
            echo $CI->load->view($module_name['system_name'].'/activate', $data, true);
            exit();
        }        
    }
}

function fixed_equipment_predeactivate($module_name){
    if ($module_name['system_name'] == FIXED_EQUIPMENT_MODULE_NAME) {
        require_once 'libraries/gtsslib.php';
        $fixed_equipment_api = new FixEquipmentLic();
        $fixed_equipment_api->deactivate_license();
    }
}