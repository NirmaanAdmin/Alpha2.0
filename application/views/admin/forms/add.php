<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style type="text/css">
    .daily_report_title, .daily_report_activity {
        font-weight: bold;
        text-align: center;
        background-color: lightgrey;
    }
    .daily_report_title {
        font-size: 17px;
    }
    .daily_report_activity {
        font-size: 16px;
    }
    .daily_report_head {
        font-size: 14px;
    }
    .daily_report_label {
        font-weight: bold;
    }
    .daily_center {
        text-align: center;
    }
    .table-responsive {
        overflow-x: visible !important;
        scrollbar-width: none !important;
    }
    .laber-type .dropdown-menu .open, .agency .dropdown-menu .open {
        width: max-content !important;
    }
    .agency .dropdown-toggle, .laber-type .dropdown-toggle {
        width: 90px !important;
    }
</style>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'new_form_form']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-items-center tw-mb-2">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-mr-4">
                        <?php echo _l('clients_single_form_information_heading'); ?>
                    </h4>
                    <?php if (!isset($project_id) && !isset($contact)) { ?>
                        <a href="#" id="form_no_contact" class="label label-default">
                            <i class="fa-regular fa-envelope tw-mr-1"></i> <?php echo _l('form_create_no_contact'); ?>
                        </a>
                        <a href="#" class="hide label label-default" id="form_to_contact">
                            <i class="fa-regular fa-user tw-mr-1"></i>
                            <?php echo _l('form_create_to_contact'); ?>
                        </a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">

                                <?php echo render_input('subject', 'form_settings_subject', '', 'text', ['required' => 'true']); ?>

                                <div class="form-group projects-wrapper">
                                    <?php
                                    $project_selected = !empty($this->input->get('project_id', TRUE)) ? $this->input->get('project_id', TRUE) : '';
                                    echo render_select('project_id', $projects, array('id','name'), 'project', $project_selected, array('required'=>'true'));
                                    ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_select('department', $departments, ['departmentid', 'name'], 'form_settings_departments', (count($departments) == 1) ? $departments[0]['departmentid'] : ''); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('cc', 'CC'); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                                        $form_type_selected = '';
                                        echo render_select('form_type', get_form_listing(), array('id','name'), 'Form type', $form_type_selected, array('required'=>'true'));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                                        <?php echo _l('tags'); ?></label>
                                        <input type="text" class="tagsinput" id="tags" name="tags" data-role="tagsinput">
                                    </div>

                                    <div class="form-group select-placeholder">
                                        <label for="assigned" class="control-label">
                                            <?php echo _l('form_settings_assign_to'); ?>
                                        </label>
                                        <select name="assigned" id="assigned" class="form-control selectpicker"
                                        data-live-search="true"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        data-width="100%" required="true">
                                        <option value=""><?php echo _l('form_settings_none_assigned'); ?></option>
                                        <?php foreach ($staff as $member) { ?>
                                            <option value="<?php echo e($member['staffid']); ?>" <?php if ($member['staffid'] == get_staff_user_id()) {
                                                echo 'selected';
                                            } ?>>
                                            <?php echo e($member['firstname'] . ' ' . $member['lastname']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php $priorities['callback_translate'] = 'form_priority_translate';
                                    echo render_select('priority', $priorities, ['priorityid', 'name'], 'form_settings_priority', hooks()->apply_filters('new_form_priority_selected', 2), ['required' => 'true']); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php 
                                    $value = '';
                                    echo render_date_input('duedate', 'task_add_edit_due_date', $value, array('required'=>'true'));
                                    ?>
                                </div>

                                <?php if (get_option('services') == 1) { ?>
                                    <div class="col-md-6 hide">
                                        <?php if (is_admin() || get_option('staff_members_create_inline_form_services') == '1') {
                                            echo render_select_with_input_group('service', $services, ['serviceid', 'name'], 'form_settings_service', '', '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_service();return false;"><i class="fa fa-plus"></i></a></div>');
                                        } else {
                                            echo render_select('service', $services, ['serviceid', 'name'], 'form_settings_service');
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php echo render_custom_fields('forms'); ?>
                        </div>

                        <div class="view_form_design"></div>

                        <div class="col-md-12">
                            <hr class="hr-panel-separator" />
                        </div>
                        <div class="col-md-12 tw-mt-3">
                            <h4 class="tw-mt-0 tw-font-semibold tw-text-base tw-text-neutral-700">
                                <?php echo _l('form_add_body'); ?>
                            </h4>
                            <div class="row">
                                <div class="col-md-12 mbot20 before-form-message">
                                    <div class="row">
                                        <div class="col-md-6 hide">
                                            <select id="insert_predefined_reply" data-width="100%"
                                            data-live-search="true" class="selectpicker"
                                            data-title="<?php echo _l('form_single_insert_predefined_reply'); ?>">
                                            <?php foreach ($predefined_replies as $predefined_reply) { ?>
                                                <option value="<?php echo e($predefined_reply['id']); ?>">
                                                    <?php echo e($predefined_reply['name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php if (get_option('use_knowledge_base') == 1) { ?>
                                            <div class="visible-xs">
                                                <div class="mtop15"></div>
                                            </div>
                                            <div class="col-md-6 hide">
                                                <?php $groups = get_all_knowledge_base_articles_grouped(); ?>
                                                <select id="insert_knowledge_base_link" data-width="100%"
                                                class="selectpicker" data-live-search="true"
                                                onchange="insert_form_knowledgebase_link(this);"
                                                data-title="<?php echo _l('form_single_insert_knowledge_base_link'); ?>">
                                                <option value=""></option>
                                                <?php foreach ($groups as $group) { ?>
                                                    <?php if (count($group['articles']) > 0) { ?>
                                                        <optgroup label="<?php echo e($group['name']); ?>">
                                                            <?php foreach ($group['articles'] as $article) { ?>
                                                                <option value="<?php echo e($article['articleid']); ?>">
                                                                    <?php echo e($article['subject']); ?>
                                                                </option>
                                                            <?php } ?>
                                                        </optgroup>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo render_textarea('message', '', '', [], [], '', 'tinymce'); ?>
                        <div class="attachments_area">
                            <div class="row attachments">
                                <div class="attachment">
                                    <div class="col-md-4 col-md-offset-8 mtop10">
                                        <div class="form-group">
                                            <label for="attachment"
                                            class="control-label"><?php echo _l('form_add_attachments'); ?></label>
                                            <div class="input-group">
                                                <input type="file"
                                                extension="<?php echo str_replace(['.', ' '], '', get_option('form_attachments_file_extensions')); ?>"
                                                filesize="<?php echo file_upload_max_size(); ?>"
                                                class="form-control" name="attachments[0]"
                                                accept="<?php echo get_form_form_accepted_mimes(); ?>">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default add_more_attachments"
                                                    data-max="<?php echo get_option('maximum_allowed_form_attachments'); ?>"
                                                    type="button"><i class="fa fa-plus"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="btn-bottom-toolbar text-right">

                    <button type="submit" data-form="#new_form_form" autocomplete="off"
                    data-loading-text="<?php echo _l('wait_text'); ?>"
                    class="btn btn-primary"><?php echo _l('open_form'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
</div>
<div class="tw-py-10"></div>
<?php $this->load->view('admin/forms/services/service'); ?>
<?php init_tail(); ?>
<?php hooks()->do_action('new_form_admin_page_loaded'); ?>
<script>
    $(function(){
        $('#project_id').trigger('change');
        validate_new_form_form();

        $("body").on('change', 'select[name="form_type"]', function () {
            var form_type = $(this).val();
            if(form_type != '') {
                find_form_design(form_type);
            } else {
                $('.view_form_design').html('');
                alert_float('warning', "Please select form type" )
            }
        });

        function find_form_design(form_type) {
            $.post(admin_url + 'forms/find_form_design/'+form_type).done(function(response){
                $('.view_form_design').html('');
                $('.view_form_design').html(response);
                $('.view_project_name').html('');
                var project_name = $('#project_id option:selected').text();
                $('.view_project_name').html(project_name);
                $('.selectpicker').selectpicker('refresh');
            });
        }

        $(document).on('click', '.dpr-add-item-to-table', function(event) {
            "use strict";

            var data = 'undefined';
            data = typeof (data) == 'undefined' || data == 'undefined' ? dpr_get_item_preview_values() : data;
            var table_row = '';
            var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('.dpr-items-table tbody .item').length + 1;
            lastAddedItemKey = item_key;

            dpr_get_item_row_template('newitems[' + item_key + ']', data.location, data.agency, data.type, data.work_execute, data.material_consumption, data.machinery, data.skilled, data.unskilled, data.depart, data.total, data.male, data.female, item_key).done(function(output){
                table_row += output;

                $('.dpr_body').append(table_row);

                init_selectpicker();
                pur_clear_item_preview_values();
                $('body').find('#items-warning').remove();
                $("body").find('.dt-loader').remove();
                $('#item_select').selectpicker('val', '');

                return true;
            });
            return false;
        });

        function dpr_get_item_row_template(name, location, agency, type, work_execute, material_consumption, machinery, skilled, unskilled, depart, total, male, female, item_key)  {
          "use strict";

          jQuery.ajaxSetup({
            async: false
          });

          var d = $.post(admin_url + 'forms/get_dpr_row_template', {
            name: name,
            location : location,
            agency : agency,
            type : type,
            work_execute : work_execute,
            material_consumption : material_consumption,
            machinery : machinery,
            skilled : skilled,
            unskilled : unskilled,
            depart : depart,
            total : total,
            male : male,
            female : female,
            item_key: item_key
          });
          jQuery.ajaxSetup({
            async: true
          });
          return d;
        }

        function dpr_get_item_preview_values() {
          "use strict";

          var response = {};
          response.location = $('.dpr-items-table input[name="location"]').val();
          response.agency = $('.dpr-items-table select[name="agency"]').selectpicker('val');
          response.type = $('.dpr-items-table select[name="type"]').selectpicker('val');
          response.work_execute = $('.dpr-items-table input[name="work_execute"]').val();
          response.material_consumption = $('.dpr-items-table input[name="material_consumption"]').val();
          response.machinery = $('.dpr-items-table input[name="machinery"]').val();
          response.skilled = $('.dpr-items-table input[name="skilled"]').val();
          response.unskilled = $('.dpr-items-table input[name="unskilled"]').val();
          response.depart = $('.dpr-items-table input[name="depart"]').val();
          response.total = $('.dpr-items-table input[name="total"]').val();
          response.male = $('.dpr-items-table input[name="male"]').val();
          response.female = $('.dpr-items-table input[name="female"]').val();

          return response;
        }

        function pur_clear_item_preview_values() {
          "use strict";

          var previewArea = $('.dpr_body .main');
          previewArea.find('input').val('');
          previewArea.find('textarea').val('');
          previewArea.find('select').val('').selectpicker('refresh');
        }

    });
</script>
</body>

</html>