<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Render admin forms table
 * @param string  $name        table name
 * @param boolean $bulk_action include checkboxes on the left side for bulk actions
 */
function AdminFormsTableStructure($name = '', $bulk_action = false)
{
    $table = '<table class="table customizable-table number-index-' . ($bulk_action ? '2' : '1') . ' dt-table-loading ' . ($name == '' ? 'forms-table' : $name) . ' table-forms" id="forms" data-last-order-identifier="forms" data-default-order="' . get_table_last_order('forms') . '">';
    $table .= '<thead>';
    $table .= '<tr>';

    $table .= '<th class="' . ($bulk_action == true ? '' : 'not_visible') . '">';
    $table .= '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="forms"><label></label></div>';
    $table .= '</th>';

    $table .= '<th class="toggleable" id="th-number">' . _l('the_number_sign') . '</th>';
    $table .= '<th class="toggleable" id="th-subject">' . _l('form_dt_subject') . '</th>';
    $table .= '<th class="toggleable" id="th-tags">' . _l('tags') . '</th>';
    $table .= '<th class="toggleable" id="th-department">' . _l('form_dt_department') . '</th>';
    $services_th_attrs = '';
    if (get_option('services') == 0) {
        $services_th_attrs = ' class="not_visible"';
    }
    $table .= '<th' . $services_th_attrs . '>' . _l('form_dt_service') . '</th>';
    $table .= '<th class="toggleable" id="th-submitter">' . _l('form_dt_submitter') . '</th>';
    $table .= '<th class="toggleable" id="th-status">' . _l('form_dt_status') . '</th>';
    $table .= '<th class="toggleable" id="th-priority">' . _l('form_dt_priority') . '</th>';
    $table .= '<th class="toggleable" id="th-last-reply">' . _l('form_dt_last_reply') . '</th>';
    $table .= '<th class="toggleable form_created_column" id="th-created">' . _l('form_date_created') . '</th>';

    $custom_fields = get_table_custom_fields('forms');

    foreach ($custom_fields as $field) {
        $table .= '<th>' . $field['name'] . '</th>';
    }

    $table .= '</tr>';
    $table .= '</thead>';
    $table .= '<tbody></tbody>';
    $table .= '</table>';

    $table .= '<script id="hidden-columns-table-forms" type="text/json">';
    $table .= get_staff_meta(get_staff_user_id(), 'hidden-columns-table-forms');
    $table .= '</script>';

    return $table;
}

/**
 * Function to translate form status
 * The app offers ability to translate form status no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function form_status_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('form_status_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI = & get_instance();
        $CI->db->where('formstatusid', $id);
        $status = $CI->db->get(db_prefix() . 'forms_status')->row();

        return !$status ? '' : $status->name;
    }

    return $line;
}

/**
 * Function to translate form priority
 * The apps offers ability to translate form priority no matter if they are stored in database
 * @param  mixed $id
 * @return string
 */
function form_priority_translate($id)
{
    if ($id == '' || is_null($id)) {
        return '';
    }

    $line = _l('form_priority_db_' . $id, '', false);

    if ($line == 'db_translate_not_found') {
        $CI = & get_instance();
        $CI->db->where('priorityid', $id);
        $priority = $CI->db->get(db_prefix() . 'forms_priorities')->row();

        return !$priority ? '' : $priority->name;
    }

    return $line;
}

/**
 * When form will be opened automatically set to open
 * @param integer  $current Current status
 * @param integer  $id      formid
 * @param boolean $admin   Admin opened or client opened
 */
function set_form_open($current, $id, $admin = true)
{
    if ($current == 1) {
        return;
    }

    $field = ($admin == false ? 'clientread' : 'adminread');

    $CI = & get_instance();
    $CI->db->where('formid', $id);

    $CI->db->update(db_prefix() . 'forms', [
        $field => 1,
    ]);
}

/**
 * Check whether to show form submitter on clients area table based on applied settings and contact
 * @since  2.3.2
 * @return boolean
 */
function show_form_submitter_on_clients_area_table()
{
    $show_submitter_on_table = true;
    if (!can_logged_in_contact_view_all_forms()) {
        $show_submitter_on_table = false;
    }

    return hooks()->apply_filters('show_form_submitter_on_clients_area_table', $show_submitter_on_table);
}

/**
 * Check whether the logged in contact can view all forms in customers area
 * @since  2.3.2
 * @return boolean
 */
function can_logged_in_contact_view_all_forms()
{
    return !(!is_primary_contact() && get_option('only_show_contact_forms') == 1);
}

/**
 * Get clients area form summary statuses data
 * @since  2.3.2
 * @param  array $statuses  current statuses
 * @return array
 */
function get_clients_area_forms_summary($statuses)
{
    foreach ($statuses as $key => $status) {
        $where = ['userid' => get_client_user_id(), 'status' => $status['formstatusid']];
        if (!can_logged_in_contact_view_all_forms()) {
            $where[db_prefix() . 'forms.contactid'] = get_contact_user_id();
        }
        $statuses[$key]['total_forms']   = total_rows(db_prefix() . 'forms', $where);
        $statuses[$key]['translated_name'] = form_status_translate($status['formstatusid']);
        $statuses[$key]['url']             = site_url('clients/forms/' . $status['formstatusid']);
    }

    return hooks()->apply_filters('clients_area_forms_summary', $statuses);
}

/**
 * Check whether contact can change the form status (single form) in clients area
 * @since  2.3.2
 * @param  mixed $status  the status id, if not passed, will first check from settings
 * @return boolean
 */
function can_change_form_status_in_clients_area($status = null)
{
    $option = get_option('allow_customer_to_change_form_status');

    if (is_null($status)) {
        return $option == 1;
    }

    /*
    *   For all cases check the option too again, because if the option is set to No, no status changes on any status is allowed
     */
    if ($option == 0) {
        return false;
    }

    $forbidden = hooks()->apply_filters('forbidden_form_statuses_to_change_in_clients_area', [3, 4]);

    if (in_array($status, $forbidden)) {
        return false;
    }

    return true;
}

/**
 * For html5 form accepted attributes
 * This function is used for the forms form attachments
 * @return string
 */
function get_form_form_accepted_mimes()
{
    $form_allowed_extensions = get_option('form_attachments_file_extensions');

    $_form_allowed_extensions = array_map(function ($ext) {
        return trim($ext);
    }, explode(',', $form_allowed_extensions));

    $all_form_ext = str_replace([' '], '', $form_allowed_extensions);

    if (is_array($_form_allowed_extensions)) {
        foreach ($_form_allowed_extensions as $ext) {
            $all_form_ext .= ',' . get_mime_by_extension($ext);
        }
    }

    return $all_form_ext;
}

function form_message_save_as_predefined_reply_javascript()
{
    if (!is_admin() && get_option('staff_members_save_forms_predefined_replies') == '0') {
        return false;
    } ?>
<div class="modal fade" id="savePredefinedReplyFromMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('predefined_replies_dt_name'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('name', 'predefined_reply_add_edit_name'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary"
                    id="saveFormMessagePredefinedReply"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
$(function() {
    var editorMessage = tinymce.get('message');
    if (typeof(editorMessage) != 'undefined') {
        editorMessage.on('change', function() {
            if (editorMessage.getContent().trim() != '') {
                if ($('#savePredefinedReplyFromMessage').length == 0) {
                    $('[app-field-wrapper="message"] [role="menubar"]:first')
                        .append(
                            "<button id=\"savePredefinedReplyFromMessage\" data-toggle=\"modal\" type=\"button\" data-target=\"#savePredefinedReplyFromMessageModal\" class=\"tox-mbtn save_predefined_reply_from_message pointer\" href=\"#\"></button>"
                        );
                }
                // For open is handled on contact select
                if ($('#single-form-form').length > 0) {
                    var contactIDSelect = $('#contactid');
                    if (contactIDSelect.data('no-contact') == undefined && contactIDSelect.data(
                            'form-emails') == '0') {
                        show_form_no_contact_email_warning($('input[name="userid"]').val(),
                            contactIDSelect.val());
                    } else {
                        clear_form_no_contact_email_warning();
                    }
                }
            } else {
                $('#savePredefinedReplyFromMessage').remove();
                clear_form_no_contact_email_warning();
            }
        });

        if(editorMessage.getContent().trim() == '') {
            $('button[data-form=#single-form-form]').attr('disabled', true);
        } else {
            $('button[data-form=#single-form-form]').attr('disabled', false);
        }
        editorMessage.on('keyup',function(){
            if(editorMessage.getContent().trim() == '') {
                $('button[data-form=#single-form-form]').attr('disabled', true);
            } else {
                $('button[data-form=#single-form-form]').attr('disabled', false);
            }
        });
    }
    $('body').on('click', '#saveFormMessagePredefinedReply', function(e) {
        e.preventDefault();
        var data = {}
        data.message = editorMessage.getContent();
        data.name = $('#savePredefinedReplyFromMessageModal #name').val();
        data.form_area = true;
        $.post(admin_url + 'forms/predefined_reply', data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var predefined_reply_select = $('#insert_predefined_reply');
                predefined_reply_select.find('option:first').after('<option value="' + response
                    .id + '">' + data.name + '</option>');
                predefined_reply_select.selectpicker('refresh');
            }
            $('#savePredefinedReplyFromMessageModal').modal('hide');
        });
    });
});
</script>
<?php
}

function get_form_public_url($form)
{
    if (is_array($form)) {
        $form = array_to_object($form);
    }

    $CI = &get_instance();

    if (!$form->formkey) {
        $CI->db->where('formid', $form->formid);
        $CI->db->update('forms', ['formkey' => $key = app_generate_hash()]);
    } else {
        $key = $form->formkey;
    }

    return site_url('forms/forms/' . $key);
}

function can_staff_delete_form_reply()
{
    return can_staff_delete_form();
}

function can_staff_delete_form()
{
    if(is_admin()) {
        return true;
    }

    if(!is_staff_member() && get_option('access_forms_to_none_staff_members') == '0') {
        return false;
    }

    return get_option('allow_non_admin_members_to_delete_forms_and_replies') == '1';
}

function can_staff_edit_form_message()
{
    if(is_admin()) {
        return true;
    }

    if(!is_staff_member() && get_option('access_forms_to_none_staff_members') == '0') {
        return false;
    }

    return get_option('allow_non_admin_members_to_edit_form_messages') == '1';
}

function form_public_form_customers_footer()
{
    // Create new listeners for the public_form
    // removes the one from clients.js (if loaded) and using new ones
    ?>
<style>
.single-form-project-area {
    display: none !important;
}
</style>
<script>
$(function() {
    setTimeout(function() {
        $('#form-reply').appFormValidator();

        $('.toggle-change-form-status').off('click');
        $('.toggle-change-form-status').on('click', function() {
            $('.form-status,.form-status-inline').toggleClass('hide');
        });

        $('#form_status_single').off('change');
        $('#form_status_single').on('change', function() {
            data = {};
            data.status_id = $(this).val();
            data.form_id = $('input[name="form_id"]').val();
            $.post(site_url + 'clients/change_form_status/', data).done(function() {
                window.location.reload();
            });
        });
    }, 2000)
})
</script>
<?php
}