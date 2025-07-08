<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('forms_model');
$statuses = $this->ci->forms_model->get_form_status();
$this->ci->load->model('departments_model');
$this->ci->load->model('staff_model'); // Added missing staff_model load

$rules = [
    App_table_filter::new('subject', 'TextRule')->label(_l('form_dt_subject')),
    App_table_filter::new('department', 'SelectRule')->label(_l('form_dt_department'))->options(function ($ci) {
        return collect($ci->departments_model->get())->map(fn($dep) => [
            'value' => $dep['departmentid'],
            'label' => $dep['name']
        ])->all();
    })->isVisible(fn() => is_admin()),
    App_table_filter::new('my_forms', 'BooleanRule')->label(_l('my_forms_assigned'))->raw(function ($value) {
        return $value == "1" ? 'assigned = ' . get_staff_user_id() : 'assigned != ' . get_staff_user_id();
    }),
];

$rules[] = App_table_filter::new('assigned', 'SelectRule')->label(_l('form_assigned'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->isVisible(fn() => is_admin())
    ->options(function ($ci) {
        $staff = $ci->staff_model->get('', ['active' => 1]);
        return collect($staff)->map(function ($staff) {
            return [
                'value' => $staff['staffid'],
                'label' => $staff['firstname'] . ' ' . $staff['lastname']
            ];
        })->all();
    });

$rules[] = App_table_filter::new('project_id', 'SelectRule')->label(_l('projects'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->options(function ($ci) {
        $project = get_projects();
        return collect($project)->map(function ($project) {
            return [
                'value' => $project['id'],
                'label' => $project['name']
            ];
        })->all();
    });

return App_table::find('preports')
    ->outputUsing(function ($params) use ($statuses) {
        extract($params);

        $aColumns = [
            '1', // bulk actions
            'formid',
            'subject',
            'project_id',
            db_prefix() . 'departments.name as department_name',
            'priority',
            db_prefix() . 'forms.date as date',
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'forms.formid and rel_type="form" ORDER by tag_order ASC) as tags',
            '2'
        ];

        $additionalSelect = [
            'adminread',
            'formkey',
            db_prefix() . 'forms.userid',
            'statuscolor',
            db_prefix() . 'forms.name as form_opened_by_name',
            db_prefix() . 'forms.email',
            db_prefix() . 'forms.userid',
            'assigned',
            db_prefix() . 'clients.company',
            'department', // Added department to additionalSelect
        ];

        $join = [
            'LEFT JOIN ' . db_prefix() . 'contacts ON ' . db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid',
            'LEFT JOIN ' . db_prefix() . 'services ON ' . db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service',
            'LEFT JOIN ' . db_prefix() . 'departments ON ' . db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department',
            'LEFT JOIN ' . db_prefix() . 'forms_status ON ' . db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status',
            'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid',
            'LEFT JOIN ' . db_prefix() . 'forms_priorities ON ' . db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority',
        ];

        $where = [];

        // Apply filters from rules
        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }
        if ($project_id = $this->ci->input->post('project_id')) {
            array_push($where, 'AND '. db_prefix() . 'forms.project_id = ' . $this->ci->db->escape_str($project_id));
        }
        // Department access for non-admins
        if (!is_admin() && get_option('staff_access_only_assigned_departments') == 1) {
            $staff_departments_ids = $this->ci->departments_model->get_staff_departments(get_staff_user_id(), true);
            $departments_ids = [];

            if (count($staff_departments_ids) == 0) {
                $departments = $this->ci->departments_model->get();
                foreach ($departments as $department) {
                    array_push($departments_ids, $department['departmentid']);
                }
            } else {
                $departments_ids = $staff_departments_ids;
            }

            if (count($departments_ids) > 0) {
                $where[] = 'AND department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
            }
        }

        // Module filter
        if (isset($module)) {
            $where[] = 'AND form_type = "' . $this->ci->db->escape_str($module) . '"';
        }

        // Custom fields
        $custom_fields = get_table_custom_fields('forms');
        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'forms.formid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $sIndexColumn = 'formid';
        $sTable = db_prefix() . 'forms';

        // Fix for big queries
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

        $output = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $pkey => $aRow) {
            $row = [];
            for ($i = 0; $i < count($aColumns); $i++) {
                if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                    $_data = $aRow[strafter($aColumns[$i], 'as ')];
                } else {
                    $_data = $aRow[$aColumns[$i]];
                }

                if ($aColumns[$i] == '1') {
                    $_data = '<div class="checkbox"><input type="checkbox" value="' . $aRow['formid'] . '" data-name="' . $aRow['subject'] . '" data-status="' . $aRow['status'] . '"><label></label></div>';
                } elseif ($aColumns[$i] == 'project_id') {
                    $_data = get_project_name_by_id($aRow['project_id']);
                } elseif ($aColumns[$i] == 'subject' || $aColumns[$i] == 'formid') {
                    if ($aRow['assigned'] != 0) {
                        if ($aColumns[$i] != 'formid') {
                            $_data .= '<a href="' . admin_url('profile/' . $aRow['assigned']) . '" data-toggle="tooltip" title="' . e(get_staff_full_name($aRow['assigned'])) . '" class="pull-left mright5">' . staff_profile_image($aRow['assigned'], [
                                'staff-profile-image-xs',
                            ]) . '</a>';
                        } else {
                            $_data = $pkey + 1;
                        }
                    } else {
                        $_data = e($_data);
                    }

                    $url = admin_url('forms/view_edit_dpr/' . $aRow['formid']);
                    $_data = '<a href="' . $url . '?tab=settings" class="valign">' . $_data . '</a>';
                    if ($aColumns[$i] == 'subject') {
                        $_data .= '<div class="row-options">';
                        $_data .= '<a href="' . $url . '?tab=settings">' . _l('view') . '</a>';
                        $_data .= ' | <a href="' . $url . '?tab=settings">' . _l('edit') . '</a>';
                        if (can_staff_delete_form()) {
                            $_data .= ' | <a href="' . admin_url('forms/delete/' . $aRow['formid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                        }
                        $_data .= '</div>';
                    }
                } elseif ($aColumns[$i] == 'tags') {
                    $_data = render_tags($_data);
                } elseif (strpos($aColumns[$i], 'forms.date as date') !== false) {
                    $_data = date('d M, Y H:i A', strtotime($aRow['date']));
                } elseif ($aColumns[$i] == 'priority') {
                    $_data = e(form_priority_translate($aRow['priority']));
                } elseif ($aColumns[$i] == '2') {
                    $form_pdf = '';
                    $form_pdf .= '<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><span class="caret"></span></a>';
                    $form_pdf .= '<ul class="dropdown-menu dropdown-menu-right">';
                    $form_pdf .= '<li class="hidden-xs"><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid'] . '?output_type=I') . '">' . _l('view_pdf') . '</a></li>';
                    $form_pdf .= '<li class="hidden-xs"><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid'] . '?output_type=I') . '" target="_blank">' . _l('view_pdf_in_new_window') . '</a></li>';
                    $form_pdf .= '<li><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid']) . '">' . _l('download') . '</a></li>';
                    $form_pdf .= '<li><a href="' . admin_url('forms/form_dpr_pdf/' . $aRow['formid'] . '?print=true') . '" target="_blank">' . _l('print') . '</a></li>';
                    $form_pdf .= '</ul>';
                    $_data = $form_pdf;
                } elseif (strpos($aColumns[$i], 'date_picker_') !== false) {
                    $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
                }

                $row[] = $_data;

                if ($aRow['adminread'] == 0) {
                    $row['DT_RowClass'] = 'text-danger';
                }
            }

            $row['DT_RowClass'] = isset($row['DT_RowClass']) ? $row['DT_RowClass'] . ' has-row-options' : 'has-row-options';
            $row = hooks()->apply_filters('admin_forms_table_row_data', $row, $aRow);
            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules($rules);
