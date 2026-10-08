<?php
defined('BASEPATH') or exit('No direct script access allowed');
$module_name = 'tasks';

$status_filter_name = 'task_status';
$task_priority_name = 'task_priority';
$task_assignees_name = 'task_assignees';

// Get CI instance
$CI = &get_instance();
$CI->load->model('tasks_model');

$hasPermissionEdit = staff_can('edit', 'tasks');
$hasPermissionDelete = staff_can('delete', 'tasks');
$tasksPriorities = get_tasks_priorities();
$task_statuses = $CI->tasks_model->get_statuses();

// Base columns
$aColumns = [
    '1', // bulk actions
    db_prefix() . 'tasks.id as id',
    db_prefix() . 'tasks.name as task_name',
    'status',
    'startdate',
    'duedate',
    get_sql_select_task_asignees_full_names() . ' as assignees',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'tasks.id and rel_type="task" ORDER by tag_order ASC) as tags',
    'priority',
];

// Custom fields
$custom_fields = get_table_custom_fields('tasks');
$customFieldsColumns = [];
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    $customFieldsColumns[] = $selectAs;
    $aColumns[] = '(SELECT value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=' . db_prefix() . 'tasks.id AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $field['id'] . ' AND ' . db_prefix() . 'customfieldsvalues.fieldto="' . $field['fieldto'] . '" LIMIT 1) as ' . $selectAs;
}

$aColumns = hooks()->apply_filters('tasks_table_sql_columns', $aColumns);

// Fix for big queries
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}

// Where conditions
$where = [];
$join = [];

if ($CI->input->post('filters')) {
    $filters = $CI->input->post('filters');
    if (isset($filters['rules']) && is_array($filters['rules'])) {
        $where[] = $this->getWhereFromRules($filters['rules']);
    }
}

if (staff_cant('view', 'tasks')) {
    $where[] = get_tasks_where_string();
}

// Dashboard my tasks table
if ($CI->input->post('my_tasks')) {
    $where[] = 'AND (' . db_prefix() . 'tasks.id IN (SELECT taskid FROM ' . db_prefix() . 'task_assigned WHERE staffid = ' . get_staff_user_id() . ') AND status != ' . Tasks_model::STATUS_COMPLETE . ')';
}

// Status filter
if ($CI->input->post('task_status') && count($CI->input->post('task_status')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'tasks.status IN (' . implode(',', $CI->input->post('task_status')) . ')';
}

// Priority filter
if ($CI->input->post('task_priority') && count($CI->input->post('task_priority')) > 0) {
    $where[] = 'AND ' . db_prefix() . 'tasks.priority IN (' . implode(',', $CI->input->post('task_priority')) . ')';
}

// Assignees filter
if ($CI->input->post('task_assignees') && count($CI->input->post('task_assignees')) > 0) {
    $conditions = [];
    foreach ($CI->input->post('task_assignees') as $assignee_id) {
        $conditions[] = 'FIND_IN_SET(' . (int)$assignee_id . ', ' . get_sql_select_task_assignees_ids() . ')';
    }
    $where[] = 'AND (' . implode(' OR ', $conditions) . ')';
}

// Update module filters
$status_filter_name_value = !empty($CI->input->post('task_status')) ? implode(',', $CI->input->post('task_status')) : NULL;
update_module_filter($module_name, $status_filter_name, $status_filter_name_value);

$task_priority_filter_name_value = !empty($CI->input->post('task_priority')) ? implode(',', $CI->input->post('task_priority')) : NULL;
update_module_filter($module_name, $task_priority_name, $task_priority_filter_name_value);

$task_assignees_filter_name_value = !empty($CI->input->post('task_assignees')) ? implode(',', $CI->input->post('task_assignees')) : NULL;
update_module_filter($module_name, $task_assignees_name, $task_assignees_filter_name_value);

// array_push($where, 'AND CASE WHEN rel_type="project" AND rel_id IN (SELECT project_id FROM ' . db_prefix() . 'project_settings WHERE project_id=rel_id AND name="hide_tasks_on_main_tasks_table" AND value=1) THEN rel_type != "project" ELSE 1=1 END');

$result = data_tables_init(
    $aColumns,
    'id',
    db_prefix() . 'tasks',
    $join,
    $where,
    [
        'rel_type',
        'rel_id',
        'recurring',
        tasks_rel_name_select_query() . ' as rel_name',
        'billed',
        '(SELECT staffid FROM ' . db_prefix() . 'task_assigned WHERE taskid=' . db_prefix() . 'tasks.id AND staffid=' . get_staff_user_id() . ') as is_assigned',
        get_sql_select_task_assignees_ids() . ' as assignees_ids',
        '(SELECT MAX(id) FROM ' . db_prefix() . 'taskstimers WHERE task_id=' . db_prefix() . 'tasks.id and staff_id=' . get_staff_user_id() . ' and end_time IS NULL) as not_finished_timer_by_current_staff',
        '(SELECT staffid FROM ' . db_prefix() . 'task_assigned WHERE taskid=' . db_prefix() . 'tasks.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned',
        '(SELECT CASE WHEN addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0 THEN 1 ELSE 0 END) as current_user_is_creator',
    ],
    '',
    [],
    '',
    'tasks'
);

$output  = $result['output'];
$rResult = $result['rResult'];
$sr = 1;

foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    // Task ID
    $row[] = '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" onclick="init_task_modal(' . $aRow['id'] . '); return false;">' . $sr++ . '</a>';

    // Task name and options
    $outputName = '';
    if ($aRow['not_finished_timer_by_current_staff']) {
        $outputName .= '<span class="pull-left text-danger"><i class="fa-regular fa-clock fa-fw tw-mr-1"></i></span>';
    }

    $outputName .= '<a href="' . admin_url('tasks/view/' . $aRow['id']) . '" class="display-block main-tasks-table-href-name' . (!empty($aRow['rel_id']) ? ' mbot5' : '') . '" onclick="init_task_modal(' . $aRow['id'] . '); return false;">' . e($aRow['task_name']) . '</a>';

    if ($aRow['rel_name']) {
        $relName = task_rel_name($aRow['rel_name'], $aRow['rel_id'], $aRow['rel_type']);
        $link = task_rel_link($aRow['rel_id'], $aRow['rel_type']);
        $outputName .= '<span class="hide"> - </span><a class="tw-text-neutral-700 task-table-related tw-text-sm" data-toggle="tooltip" title="' . _l('task_related_to') . '" href="' . $link . '">' . e($relName) . '</a>';
    }

    if ($aRow['recurring'] == 1) {
        $outputName .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('recurring_task') . '</span>';
    }

    $outputName .= '<div class="row-options">';

    $class = 'text-success bold';
    $style = '';
    $tooltip = '';

    if ($aRow['billed'] == 1 || !$aRow['is_assigned'] || $aRow['status'] == Tasks_model::STATUS_COMPLETE) {
        $class = 'text-dark disabled';
        $style = 'style="opacity:0.6;cursor: not-allowed;"';
        if ($aRow['status'] == Tasks_model::STATUS_COMPLETE) {
            $tooltip = ' data-toggle="tooltip" data-title="' . e(format_task_status($aRow['status'], false, true)) . '"';
        } elseif ($aRow['billed'] == 1) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_billed_cant_start_timer') . '"';
        } elseif (!$aRow['is_assigned']) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_start_timer_only_assignee') . '"';
        }
    }

    if ($aRow['not_finished_timer_by_current_staff']) {
        $outputName .= '<a href="#" class="text-danger tasks-table-stop-timer" onclick="timer_action(this,' . $aRow['id'] . ',' . $aRow['not_finished_timer_by_current_staff'] . '); return false;">' . _l('task_stop_timer') . '</a>';
    } else {
        $outputName .= '<span' . $tooltip . ' ' . $style . '>
            <a href="#" class="' . $class . ' tasks-table-start-timer" onclick="timer_action(this,' . $aRow['id'] . '); return false;">' . _l('task_start_timer') . '</a>
        </span>';
    }

    if ($hasPermissionEdit) {
        $outputName .= '<span class="tw-text-neutral-300"> | </span><a href="#" onclick="edit_task(' . $aRow['id'] . '); return false">' . _l('edit') . '</a>';
    }

    if ($hasPermissionDelete) {
        $outputName .= '<span class="tw-text-neutral-300"> | </span><a href="' . admin_url('tasks/delete_task/' . $aRow['id']) . '" class="text-danger _delete task-delete">' . _l('delete') . '</a>';
    }
    $outputName .= '</div>';

    $row[] = $outputName;

    // Status
    $canChangeStatus = ($aRow['current_user_is_creator'] != '0' || $aRow['current_user_is_assigned'] || staff_can('edit', 'tasks'));
    $status = get_task_status_by_id($aRow['status']);
    $outputStatus = '<span class="label" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';" task-status-table="' . e($aRow['status']) . '">';
    $outputStatus .= e($status['name']);

    if ($canChangeStatus) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
        $outputStatus .= '</a>';

        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $aRow['id'] . '">';
        foreach ($task_statuses as $taskChangeStatus) {
            if ($aRow['status'] != $taskChangeStatus['id']) {
                $outputStatus .= '<li>
                    <a href="#" onclick="task_mark_as(' . $taskChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                        ' . e(_l('task_mark_as', $taskChangeStatus['name'])) . '
                    </a>
                </li>';
            }
        }
        $outputStatus .= '</ul>';
        $outputStatus .= '</div>';
    }
    $outputStatus .= '</span>';
    $row[] = $outputStatus;

    // Start date
    $row[] = e(_d($aRow['startdate']));

    // Due date
    $row[] = e(_d($aRow['duedate']));

    // Assignees
    $row[] = format_members_by_ids_and_names($aRow['assignees_ids'], $aRow['assignees']);

    // Tags
    $row[] = render_tags($aRow['tags']);

    // Priority
    $outputPriority = '<span style="color:' . e(task_priority_color($aRow['priority'])) . ';" class="inline-block">' . e(task_priority($aRow['priority']));

    if (staff_can('edit', 'tasks') && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $outputPriority .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputPriority .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableTaskPriority-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputPriority .= '<span data-toggle="tooltip" title="' . _l('task_single_priority') . '"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>';
        $outputPriority .= '</a>';

        $outputPriority .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskPriority-' . $aRow['id'] . '">';
        foreach ($tasksPriorities as $priority) {
            if ($aRow['priority'] != $priority['id']) {
                $outputPriority .= '<li>
                    <a href="#" onclick="task_change_priority(' . $priority['id'] . ',' . $aRow['id'] . '); return false;">
                        ' . e($priority['name']) . '
                    </a>
                </li>';
            }
        }
        $outputPriority .= '</ul>';
        $outputPriority .= '</div>';
    }
    $outputPriority .= '</span>';
    $row[] = $outputPriority;

    // Custom fields
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_')) !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn];
    }

    $row['DT_RowClass'] = 'has-row-options';

    if ((!empty($aRow['duedate']) && $aRow['duedate'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' danger';
    }

    $row = hooks()->apply_filters('tasks_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}

?>