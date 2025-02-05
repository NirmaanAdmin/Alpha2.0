<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('forms_model');
$statuses = $this->ci->forms_model->get_form_status();
$this->ci->load->model('departments_model');

$rules = [
    App_table_filter::new('subject', 'TextRule')->label(_l('form_dt_subject')),
    App_table_filter::new('department', 'SelectRule')->label(_l('form_dt_department'))->options(function ($ci) {
        return collect($ci->departments_model->get())->map(fn ($dep) => [
            'value' => $dep['departmentid'],
            'label' => $dep['name']
        ])->all();
    })->isVisible(fn () => is_admin()),
    App_table_filter::new('status', 'MultiSelectRule')->label(_l('form_dt_status'))->options(function ($ci) use ($statuses) {
        return collect($statuses)->map(fn ($status) => [
            'value' => $status['formstatusid'],
            'label' => form_status_translate($status['formstatusid'])
        ])->all();
    }),
    App_table_filter::new('priority', 'SelectRule')->label(_l('form_dt_priority'))->options(function ($ci) {
        return collect($ci->forms_model->get_priority())->map(fn ($priority) => [
            'value' => $priority['priorityid'],
            'label' => form_priority_translate($priority['priorityid'])
        ])->all();
    }),
    App_table_filter::new('service', 'SelectRule')->label(_l('form_dt_service'))->options(function ($ci) use ($statuses) {
        return collect($ci->forms_model->get_service())->map(fn ($service) => [
            'value' => $service['serviceid'],
            'label' => $service['name']
        ])->all();
    }),
    App_table_filter::new('merged', 'BooleanRule')->label(_l('merged'))->raw(function ($value) {
        return $value == "1" ? 'merged_form_id IS NOT NULL' : 'merged_form_id IS NULL';
    }),
    App_table_filter::new('my_forms', 'BooleanRule')->label(_l('my_forms_assigned'))->raw(function ($value) {
        return $value == "1" ? 'assigned = ' . get_staff_user_id() : 'assigned != ' . get_staff_user_id();
    }),
];

$rules[] = App_table_filter::new('assigned', 'SelectRule')->label(_l('form_assigned'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->isVisible(fn () => is_admin())
    ->options(function ($ci) {
        $staff = $ci->staff_model->get('', ['active' => 1]);

        return collect($staff)->map(function ($staff) {
            return [
                'value' => $staff['staffid'],
                'label' => $staff['firstname'] . ' ' . $staff['lastname']
            ];
        })->all();
    });

return App_table::find('forms')
    ->outputUsing(function ($params) use ($statuses) {
        extract($params);

        $aColumns = [
            '1',
            'formid',
            'subject',
            db_prefix() . 'departments.name as department_name',
            db_prefix() . 'projects.name as project_name',
            'form_type',
            'CONCAT(' . db_prefix() . 'staff.firstname, \' \', ' . db_prefix() . 'staff.lastname) as assigned_full_name',
            db_prefix() . 'forms.status',
            'lastreply',
            db_prefix() . 'forms.date',
            '2',
        ];

        $contactColumn = 6;
        $tagsColumns   = 3;

        $additionalSelect = [
            'adminread',
            'formkey',
            db_prefix() . 'forms.userid',
            'statuscolor',
            db_prefix() . 'forms.status',
            db_prefix() . 'forms.name as form_opened_by_name',
            db_prefix() . 'forms.email',
            db_prefix() . 'forms.userid',
            'assigned',
        ];

        $join = [
            'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'forms.project_id',
            'LEFT JOIN ' . db_prefix() . 'departments ON ' . db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department',
            'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.assigned',
            'LEFT JOIN ' . db_prefix() . 'forms_status ON ' . db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status',
        ];

        $custom_fields = get_table_custom_fields('forms');
        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'forms.formid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where  = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        if (isset($userid) && $userid != '') {
            array_push($where, 'AND ' . db_prefix() . 'forms.userid = ' . $this->ci->db->escape_str($userid));
        } elseif (isset($by_email)) {
            array_push($where, 'AND ' . db_prefix() . 'forms.email = "' . $this->ci->db->escape_str($by_email) . '"');
        }

        if (isset($via_form)) {
            array_push($where, 'AND ' . db_prefix() . 'forms.formid != ' . $this->ci->db->escape_str($via_form));
        }

        if ($project_id = $this->ci->input->post('project_id')) {
            array_push($where, 'AND project_id = ' . $this->ci->db->escape_str($project_id));
        }

        // If userid is set, the the view is in client profile, should be shown all forms
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $staff_deparments_ids = $this->ci->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->ci->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    array_push($where, 'AND department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                }
            }
        }

        $sIndexColumn = 'formid';
        $sTable       = db_prefix() . 'forms';

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];
            for ($i = 0; $i < count($aColumns); $i++) {
                if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
                    $_data = $aRow[strafter($aColumns[$i], 'as ')];
                } else {
                    $_data = $aRow[$aColumns[$i]];
                }

                if ($aColumns[$i] == '1') {
                    $_data = '<div class="checkbox"><input type="checkbox" value="' . $aRow['formid'] . '" data-name="' . $aRow['subject'] . '" data-status="' . $aRow['status'] . '"><label></label></div>';
                } elseif ($aColumns[$i] == 'lastreply') {
                    if ($aRow[$aColumns[$i]] == null) {
                        $_data = _l('form_no_reply_yet');
                    } else {
                        $_data = e(_dt($aRow[$aColumns[$i]]));
                    }
                } elseif ($aColumns[$i] == 'subject' || $aColumns[$i] == 'formid') {
                    if ($aRow['assigned'] != 0) {
                        if ($aColumns[$i] != 'formid') {
                            $_data .= '<a href="' . admin_url('profile/' . $aRow['assigned']) . '" data-toggle="tooltip" title="' . e(get_staff_full_name($aRow['assigned'])) . '" class="pull-left mright5">' . staff_profile_image($aRow['assigned'], [
                                'staff-profile-image-xs',
                            ]) . '</a>';
                        } else {
                            $_data = e($_data);
                        }
                    } else {
                        $_data = e($_data);
                    }

                    $url   = admin_url('forms/form/' . $aRow['formid']);
                    $_data = '<a href="' . $url . '" class="valign">' . $_data . '</a>';
                    if ($aColumns[$i] == 'subject') {
                        $_data .= '<div class="row-options">';
                        $_data .= '<a href="' . $url . '">' . _l('view') . '</a>';
                        $_data .= ' | <a href="' . $url . '?tab=settings">' . _l('edit') . '</a>';
                        if (can_staff_delete_form()) {
                            $_data .= ' | <a href="' . admin_url('forms/delete/' . $aRow['formid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                        }
                        $_data .= '</div>';
                    }
                } elseif ($aColumns[$i] == db_prefix() . 'forms.status') {
                    $_data = '<span class="label form-status-' . $aRow['status'] . '" style="border:1px solid black; color: black ;background: white;">' . e(form_status_translate($aRow['status'])) . '</span>';
                } elseif ($aColumns[$i] == 'form_type') {
                    $form_type = get_form_name($aRow['form_type']);
                    $_data = !empty($form_type) ? $form_type->name : '';
                } elseif($aColumns[$i] == '2') {
                    $form_pdf = '';
                    $form_pdf .= '<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><span class="caret"></span></a>';
                    $form_pdf .= '<ul class="dropdown-menu dropdown-menu-right">';
                    $form_pdf .= '<li class="hidden-xs"><a href="'.admin_url('forms/form_pdf/' . $aRow['formid'] . '?output_type=I').'">'._l('view_pdf').'</a></li>';
                    $form_pdf .= '<li class="hidden-xs"><a href="'.admin_url('forms/form_pdf/' . $aRow['formid'] . '?output_type=I').'" target="_blank">'._l('view_pdf_in_new_window').'</a></li>';
                    $form_pdf .= '<li><a href="'.admin_url('forms/form_pdf/' . $aRow['formid']).'">'._l('download').'</a></li>';
                    $form_pdf .= '<li><a href="'.admin_url('forms/form_pdf/' . $aRow['formid'] . '?print=true').'" target="_blank">'._l('print').'</a></li>';
                    $form_pdf .= '</ul>';
                    $_data = $form_pdf;
                } else {
                    if (strpos($aColumns[$i], 'date_picker_') !== false) {
                        $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
                    }
                }

                $row[] = $_data;

                if ($aRow['adminread'] == 0) {
                    $row['DT_RowClass'] = 'text-danger';
                }
            }

            if (isset($row['DT_RowClass'])) {
                $row['DT_RowClass'] .= ' has-row-options';
            } else {
                $row['DT_RowClass'] = 'has-row-options';
            }

            $row = hooks()->apply_filters('admin_forms_table_row_data', $row, $aRow);
            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules($rules);
