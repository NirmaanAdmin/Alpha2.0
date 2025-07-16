<?php
defined('BASEPATH') or exit('No direct script access allowed');
$module_name = 'dpr_module';

$project_filter_name = 'project_id';

// Get CI instance
$CI = &get_instance();
$CI->load->model('forms_model');
$CI->load->model('departments_model');
$CI->load->model('staff_model');

$statuses = $CI->forms_model->get_form_status();
$custom_fields = get_table_custom_fields('forms');

// Base columns
$aColumns = [
    0, // checkbox placeholder
    db_prefix() . 'forms.formid as formid',
    db_prefix() . 'forms.subject as subject',
    db_prefix() . 'forms.project_id as project_id',
    db_prefix() . 'departments.name as department_name',
    db_prefix() . 'forms.priority as priority',
    db_prefix() . 'forms.date as date',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'forms.formid and rel_type="form" ORDER by tag_order ASC) as tags',
    '2' // PDF actions placeholder
];

// Joins
$join = [
    'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid',
    'LEFT JOIN ' . db_prefix() . 'services ON ' . db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service',
    'LEFT JOIN ' . db_prefix() . 'departments ON ' . db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department',
    'LEFT JOIN ' . db_prefix() . 'forms_status ON ' . db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid',
    'LEFT JOIN ' . db_prefix() . 'forms_priorities ON ' . db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority',
];

$where = [];

// -- Filters --
if ($CI->input->post('project_id') && count($CI->input->post('project_id')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'forms.project_id IN (' . implode(',', $CI->input->post('project_id')) . ')';
}


// Module filter
if (isset($module)) {
    $where[] = 'AND form_type = "' . $CI->db->escape_str($module) . '"';
}

// Save filters
$project_filter_name_value = !empty($CI->input->post('project_id')) ? implode(',', $CI->input->post('project_id')) : NULL;
update_module_filter($module_name, $project_filter_name, $project_filter_name_value);



// Custom fields
$cfIndex = 0;
foreach ($custom_fields as $field) {
    $alias = is_cf_date($field) ? 'date_picker_cvalue_' . $cfIndex : 'cvalue_' . $cfIndex;
    $aColumns[] = 'ctable_' . $cfIndex . '.value as ' . $alias;
    $join[] = 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' .
        $cfIndex . ' ON ' . db_prefix() . 'forms.formid = ctable_' .
        $cfIndex . '.relid
             AND ctable_' . $cfIndex . '.fieldto = "' . $field['fieldto'] . '"
             AND ctable_' . $cfIndex . '.fieldid  = ' . $field['id'];
    $cfIndex++;
}

// Additional select fields
$additionalSelect = [
    'adminread',
    'formkey',
    db_prefix() . 'forms.userid',
    'statuscolor',
    db_prefix() . 'forms.name as form_opened_by_name',
    db_prefix() . 'forms.email',
    'assigned',
    db_prefix() . 'clients.company',
    'department',
];

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init(
    $aColumns,
    'formid',
    db_prefix() . 'forms',
    $join,
    $where,
    $additionalSelect
);

$output  = $result['output'];
$rResult = $result['rResult'];
$srNo    = (int)$CI->input->post('start') + 1;

foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="'
        . $aRow['formid'] . '" data-name="' . $aRow['subject'] . '" data-status="' . $aRow['status'] . '"><label></label></div>';

    // Form ID
    $row[] = $srNo;

    // Subject
    $subject = e($aRow['subject']);
    $url = admin_url('forms/view_edit_dpr/' . $aRow['formid']);
    $subjectRow = '<a href="' . $url . '?tab=settings" class="valign">' . $subject . '</a>';
    
    // Add assigned staff image if exists
    if ($aRow['assigned'] != 0) {
        $subjectRow .= '<a href="' . admin_url('profile/' . $aRow['assigned']) . '" data-toggle="tooltip" title="' . e(get_staff_full_name($aRow['assigned'])) . '" class="pull-left mright5">' . staff_profile_image($aRow['assigned'], [
            'staff-profile-image-xs',
        ]) . '</a>';
    }
    
    // Row options
    $subjectRow .= '<div class="row-options">';
    $subjectRow .= '<a href="' . $url . '?tab=settings">' . _l('view') . '</a>';
    $subjectRow .= ' | <a href="' . $url . '?tab=settings">' . _l('edit') . '</a>';
    if (can_staff_delete_form()) {
        $subjectRow .= ' | <a href="' . admin_url('forms/delete/' . $aRow['formid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $subjectRow .= '</div>';
    
    $row[] = $subjectRow;

    // Project
    $row[] = get_project_name_by_id($aRow['project_id']);

    // Department
    $row[] = e($aRow['department_name']);

    // Priority
    $row[] = e(form_priority_translate($aRow['priority']));

    // Date
    $row[] = date('d M, Y H:i A', strtotime($aRow['date']));

    // Tags
    $row[] = render_tags($aRow['tags']);

    // PDF actions
    $form_pdf = '';
    $form_pdf .= '<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><span class="caret"></span></a>';
    $form_pdf .= '<ul class="dropdown-menu dropdown-menu-right">';
    $form_pdf .= '<li class="hidden-xs"><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid'] . '?output_type=I&staff_dpr=true') . '" target="_blank">' . _l('Staff DPR') . '</a></li>';
    $form_pdf .= '<li class="hidden-xs"><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid'] . '?output_type=I&staff_dpr=false') . '" target="_blank">' . _l('Client DPR') . '</a></li>';
    $form_pdf .= '</ul>';
    $row[] = $form_pdf;

    // Custom fields
    $cfIdx = 0;
    foreach ($custom_fields as $field) {
        $alias = is_cf_date($field)
            ? 'date_picker_cvalue_' . $cfIdx
            : 'cvalue_' . $cfIdx;
        $row[] = strpos($alias, 'date_picker_') !== false
            ? _d($aRow[$alias])
            : $aRow[$alias];
        $cfIdx++;
    }

    $row['DT_RowId'] = 'form_' . $aRow['formid'];

    if ($aRow['adminread'] == 0) {
        $row['DT_RowClass'] = 'text-danger';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $output['aaData'][] = $row;
    $srNo++;
}