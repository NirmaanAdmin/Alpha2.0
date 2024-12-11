<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Cash Flow 
Description: Cash flow Module for individual business
Version: 1.0.0
Requires at least: 2.3.*
Author: Zonvoir Technologies Pvt. Ltd.
*/
$CI =& get_instance();
if (!defined('MODULE_CASH_FLOW')) {
    define('MODULE_CASH_FLOW', basename(__DIR__));
}
define('CASH_FLOW_MODULE_NAME', 'cash_flow');

if (!defined('CASHFLOW_EXPENSE_ATTACHMENTS_FOLDER')) {
define('CASHFLOW_EXPENSE_ATTACHMENTS_FOLDER', FCPATH . 'uploads/expenses'. '/');
}

hooks()->add_action('admin_init', 'cash_flow_module_init_menu_items');
hooks()->add_action('admin_init', 'cash_flow_permissions');
hooks()->add_filter('customers_table_sql_where', 'cash_flow_filters');
hooks()->add_filter('download_file_path', 'download_attachment', 10, 3);
register_uninstall_hook(MODULE_CASH_FLOW, 'cash_flow_module_uninstall_hook');
hooks()->add_action('before_cashflow_form_name', 'add_form_field', 10, 1);

function add_form_field($expense)
{
    $selected = (isset($expense) ? $expense->operation : '');
    echo render_select('operation', [
        ['id' => 'cash-in', 'name' => 'cash-in'],
        ['id' => 'cash-out', 'name' => 'cash-out']
    ], ['id', 'name'], 'operation_type', $selected, ['required' => 'required']);

}

function cash_flow_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

$CI->load->helper(MODULE_CASH_FLOW . '/cash_flow');

register_language_files(MODULE_CASH_FLOW, [MODULE_CASH_FLOW]);


function cash_flow_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view_own'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities(MODULE_CASH_FLOW, $capabilities, _l('cash_flow'));
    }
}

register_activation_hook(MODULE_CASH_FLOW, 'cash_flow_module_activation_hook');

function cash_flow_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}
register_language_files(MODULE_CASH_FLOW, [MODULE_CASH_FLOW]);

function cash_flow_module_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('CASH_FLOW', [
        'name'     => _l('cash_flow_menu_name'),
        'slug'     => 'cash-flow',
        'href'     => admin_url('cash_flow'),
        'icon'     => 'fa fa-cash-register',
        'position' => 1,
    ]);
    $CI->app_scripts->add(MODULE_CASH_FLOW . '-js', base_url('modules/' . MODULE_CASH_FLOW . '/assets/js/' . MODULE_CASH_FLOW . '.js?v=' . time()));
}
function cash_flow_attachment_folder_path($type)
{
    
    if($type=='expense'){
        return $path = CASHFLOW_EXPENSE_ATTACHMENTS_FOLDER;
    }
  
}
function download_attachment($path, $args) {
    $CI = &get_instance();
    $folder_indicator = isset($args['folder']) ? $args['folder'] : '';
    $attachmentid = isset($args['attachmentid']) ? $args['attachmentid'] : '';

    if ($folder_indicator == 'expenses') {
        if (!is_staff_logged_in()) {
            show_404();
        }

        $CI->db->where('rel_id', $attachmentid);
        $CI->db->where('rel_type', 'cashflow_expense');
        $file = $CI->db->get(db_prefix() . 'files')->row();

        if ($file) {
            $path = get_upload_path_by_type('expense') . $file->rel_id . '/' . $file->file_name;
        }
    }
    return $path;
}
hooks()->add_action('app_admin_assets', function () {
	$CI = get_instance();
	$CI->app_css->add(MODULE_CASH_FLOW . '-css', base_url('modules/' . MODULE_CASH_FLOW . '/assets/css/' . MODULE_CASH_FLOW . '.css?v=' . time()));
});