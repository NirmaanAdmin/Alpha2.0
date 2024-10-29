<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Document Management
Description: This module helps you manage files and folders in the cloud easily, you can upload files, manage documents, edit documents online, approve & sign documents, OCR,....
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('DOCUMENT_MANAGEMENT_MODULE_NAME', 'document_management');
define('DOCUMENT_MANAGEMENT_MODULE_UPLOAD_FOLDER', module_dir_path(DOCUMENT_MANAGEMENT_MODULE_NAME, 'uploads'));
define('DOCUMENT_MANAGEMENT_PATH', 'modules/document_management/uploads/');
define('DOCUMENT_MANAGEMENT_IMAGE_UPLOADED_PATH', 'modules/document_management/uploads/');
define('DOCUMENT_MANAGEMENT_REVISION', 100);
define('DOCUMENT_MANAGEMENT_PATH_PLUGIN', 'modules/document_management/assets/plugins');
define('DOCUMENT_MANAGEMENT_LIBRARIES', 'modules/document_management/libraries');
define('DOCUMENT_MANAGEMENT_IMPORT_ITEM_ERROR', 'modules/document_management/uploads/import_item_error/');


hooks()->add_action('admin_init', 'document_management_permissions');
hooks()->add_action('admin_init', 'document_management_module_init_menu_items');
hooks()->add_action('app_admin_head', 'document_management_add_head_components');
hooks()->add_action('app_admin_footer', 'document_management_load_js');
hooks()->add_action('before_cron_run', 'auto_remider');
hooks()->add_action('head_element_public_document','head_element_document');
hooks()->add_action('footer_element_public_document','footer_element_document');
hooks()->add_action('customers_navigation_end', 'document_module_init_client_menu_items');
hooks()->add_action('document_management_init',DOCUMENT_MANAGEMENT_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', DOCUMENT_MANAGEMENT_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', DOCUMENT_MANAGEMENT_MODULE_NAME.'_predeactivate');
hooks()->add_action('head_element_client','document_management_add_head_component_client');
hooks()->add_action('client_pt_footer_js','document_management_load_js_client');
register_merge_fields('document_management/merge_fields/reminder_merge_fields');




/*Attendance export excel path*/
define('DOCUMENT_MANAGEMENT_PATH_EXPORT_FILE', 'modules/document_management/uploads/attendance/');

register_language_files(DOCUMENT_MANAGEMENT_MODULE_NAME, [DOCUMENT_MANAGEMENT_MODULE_NAME]);
/**
* Register activation module hook
*/
register_activation_hook(DOCUMENT_MANAGEMENT_MODULE_NAME, 'document_management_module_activation_hook');
/**
 * activation hook
 */
function document_management_module_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}


$CI = & get_instance();
$CI->load->helper(DOCUMENT_MANAGEMENT_MODULE_NAME . '/document_management');

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function document_management_module_init_menu_items()
{
	$CI = &get_instance();
	if (has_permission('document_management_file_management', '', 'view_own') ||
		has_permission('document_management_file_management', '', 'view') ||
		is_admin()) {
		$CI->app_menu->add_sidebar_menu_item('document_management', [
			'name'     => _l('dmg_documents'),
			'icon'     => 'fa fa-file',
			'position' => 30,
		]);

	if (has_permission('document_management_file_management', '', 'view_own') || has_permission('document_management_file_management', '', 'view') || is_admin()) {
		$CI->app_menu->add_sidebar_children_item('document_management', [
			'slug'     => 'document_management_file_management',
			'name'     => _l('dmg_file_management'),
			'href'     => admin_url('document_management'),
			'icon'     => 'fa fa-file menu-icon',
			'position' =>0,
		]);
	}

	if (is_admin()) {
		$CI->app_menu->add_sidebar_children_item('document_management', [
			'slug'     => 'document_management_settings',
			'name'     => _l('dmg_settings'),
			'href'     => admin_url('document_management/settings?tab=custom_field'),
			'icon'     => 'fa fa-cogs menu-icon',
			'position' =>0,
		]);
	}
  }
}
/**
 * load js
 */
function document_management_load_js(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/main.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.all.min.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script type="text/javascript">
	$(document).on("click","._swaldelete",function(e) {
	event.preventDefault();
	var  link = $(this).attr("href");
	Swal.fire({
		title: \''._l('dmg_are_you_sure').'?\',
		text: \''._l('dmg_do_you_really_want_to_delete_these_items_this_process_cannot_be_undone').'\',
		icon: \'warning\',
		showCancelButton: true,
		confirmButtonColor: \'#3085d6\',
		cancelButtonColor: \'#d33\',
		confirmButtonText: \''._l('dmg_yes_delete_it').'\',
		cancelButtonText: \''._l('dmg_cancel').'\',
		}).then((result) => {
			if (result.isConfirmed) {
				show_processing(\''._l('dmg_deleting').'\');
				requestGet(link).done(function(success) {
					location.reload();
				}).fail(function(error) {

				});
		}
	})
	});

	function show_processing(title){
		Swal.fire({
			title: title,
			html: \''._l('dmg_the_system_is_processing').'\',
			timerProgressBar: true,
			didOpen: () => {
					Swal.showLoading()
				},
				willClose: () => {

				}
				}).then((result) => {

					})
				}

	</script>';
	if (!(strpos($viewuri, '/admin/document_management/settings?tab=custom_field') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/settings/custom_field.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/admin/document_management/settings?tab=approval_setting') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/settings/approval_setting.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/admin/document_management') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/filetree/js/filetree.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/admin/document_management/detail_approve/') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/file_managements/detail_request.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/admin/document_management/detail_sign_approve/') === false)) {
		echo '<script src="' . site_url('assets/plugins/signature-pad/signature_pad.min.js') . '"></script>';
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/file_managements/detail_sign_request.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
}
/**
* add head components
*/
function document_management_add_head_components(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/css/style.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.min.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	if (!(strpos($viewuri, '/admin/document_management') === false)) {
		echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/filetree/css/filetree.css') . '"  rel="stylesheet" type="text/css" />';
	}
	if (!(strpos($viewuri, '/admin/editdocument/') === false)) {
		echo '<script src="https://cdn.ckeditor.com/ckeditor5/35.2.1/decoupled-document/ckeditor.js"></script>';
	}
}
/**
* fixed equipment permissions
*/
function document_management_permissions()
{
	$capabilities = [];
	// file_management
	$capabilities['capabilities'] = [
		'view_own' => _l('permission_view'),
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('edit'),
		'delete' => _l('delete')
	];
	register_staff_capabilities('document_management_file_management', $capabilities, _l('dmg_document_management_file_management'));
}

/**
 * auto remider
 */
function auto_remider(){
	$CI = &get_instance();
	$CI->load->model('document_management/document_management_model');
	$CI->document_management_model->auto_remider();
}

/**
 * add head_element
 */
function head_element_document(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/main.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.all.min.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script type="text/javascript">
	function show_processing(title){
		Swal.fire({
			title: title,
			html: \''._l('dmg_the_system_is_processing').'\',
			timerProgressBar: true,
			didOpen: () => {
					Swal.showLoading()
				},
				willClose: () => {

				}
				}).then((result) => {

					})
				}

	</script>';
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/css/style.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.min.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	if (!(strpos($viewuri,'/document_management/editdocument') === false) || !(strpos($viewuri,'/document_management/document_management_client/editdocument') === false)){
		echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/css/edit_office.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
		echo '<script src="https://cdn.ckeditor.com/ckeditor5/35.2.1/decoupled-document/ckeditor.js"></script>';
	}
}


/**
 * add footer element
 */
function footer_element_document(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri,'/document_management/editdocument') === false) || !(strpos($viewuri,'/document_management/document_management_client/editdocument') === false)){
				echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
	}
}

/**
 *  add menu item and js file to client
*/
function document_module_init_client_menu_items()
{
	if(get_option('dmg_allows_customers_to_manage_documents') == 1){
		if(is_client_logged_in()){
			echo '<li class="customers-nav-item-Insurances-plan">
			<a href="'.site_url('document_management/document_management_client').'" >'._l('dmg_file_management').'</a>
			</li>';
		}
	}
}
/**
* add head components
*/
function document_management_add_head_component_client(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/css/clients/style.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.min.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/tags/bootstrap-tagsinput.css').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"  rel="stylesheet" type="text/css" />';
	if (!(strpos($viewuri, '/document_management/document_management_client') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/tags/bootstrap-tagsinput.min.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
		echo '<link href="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/filetree/css/filetree.css') . '"  rel="stylesheet" type="text/css" />';
	}



}
/**
 * load js
 */
function document_management_load_js_client(){
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
    // Javascript
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/main.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/sweetalerts/sweetalert2.all.min.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	echo '<script type="text/javascript">
	$(document).on("click","._swaldelete",function(e) {
	event.preventDefault();
	var  link = $(this).attr("href");
	Swal.fire({
		title: \''._l('dmg_are_you_sure').'?\',
		text: \''._l('dmg_do_you_really_want_to_delete_these_items_this_process_cannot_be_undone').'\',
		icon: \'warning\',
		showCancelButton: true,
		confirmButtonColor: \'#3085d6\',
		cancelButtonColor: \'#d33\',
		confirmButtonText: \''._l('dmg_yes_delete_it').'\',
		cancelButtonText: \''._l('dmg_cancel').'\',
		}).then((result) => {
			if (result.isConfirmed) {
				show_processing(\''._l('dmg_deleting').'\');
				ajaxGet(link).done(function(success) {
					location.reload();
				}).fail(function(error) {

				});
		}
	})
	});

	function show_processing(title){
		Swal.fire({
			title: title,
			html: \''._l('dmg_the_system_is_processing').'\',
			timerProgressBar: true,
			didOpen: () => {
					Swal.showLoading()
				},
				willClose: () => {

				}
				}).then((result) => {

					})
				}

	</script>';

	if (!(strpos($viewuri, '/document_management/document_management_client') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/plugins/filetree/js/filetree.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/document_management/document_management_client/detail_approve/') === false)) {
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/clients/file_managements/detail_request.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
	if (!(strpos($viewuri, '/document_management/document_management_client/detail_sign_approve/') === false)) {
		echo '<script src="' . site_url('assets/plugins/signature-pad/signature_pad.min.js') . '"></script>';
		echo '<script src="' . module_dir_url(DOCUMENT_MANAGEMENT_MODULE_NAME, 'assets/js/clients/file_managements/detail_sign_request.js').'?v='.DOCUMENT_MANAGEMENT_REVISION.'"></script>';
	}
}
function document_management_appint(){
   
}

function document_management_preactivate($module_name){
    if ($module_name['system_name'] == DOCUMENT_MANAGEMENT_MODULE_NAME) {

    }
}

function document_management_predeactivate($module_name){
    if ($module_name['system_name'] == DOCUMENT_MANAGEMENT_MODULE_NAME) {

    }
}
