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

function get_form_listing()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Report',
            'options' => [
                [
                    'id' => 'dpr',
                    'name' => 'Daily Progress Report',
                ]
            ],
        ],
        [
            'id' => 2,
            'name' => 'Checklist',
            'options' => [
                [
                    'id' => 'apc',
                    'name' => 'Air Pollution Control',
                ],
                [
                    'id' => 'wpc',
                    'name' => 'Water Pollution Control',
                ],
                [
                    'id' => 'mfa',
                    'name' => 'Monthly First Aid Box Inspection',
                ],
                [
                    'id' => 'mlg',
                    'name' => 'Monthly Lifting Gear and Appliance Inspection',
                ],
                [
                    'id' => 'msh',
                    'name' => 'Monthly Safety Harness Inspection',
                ],
                [
                    'id' => 'sca',
                    'name' => 'Scaffolds Dismantling Checklist',
                ],
                [
                    'id' => 'esc',
                    'name' => 'Electrical Safety Checklist',
                ],
            ]
        ]
    ];
    return $result;
}

function get_weather_listing()
{
    $result = array();
    $result = [
        [
            'id' => 'Clear',
            'name' => 'Clear',
        ],
        [
            'id' => 'Cloudy',
            'name' => 'Cloudy',
        ],
        [
            'id' => 'Rain',
            'name' => 'Rain',
        ],
    ];
    return $result;
}

function get_work_stop_listing()
{
    $result = array();
    $result = [
        [
            'id' => 'Y',
            'name' => 'Y',
        ],
        [
            'id' => 'N',
            'name' => 'N',
        ],
    ];
    return $result;
}

function get_laber_type_listing($name_type, $type)
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Departmental - labor',
        ],
        [
            'id' => 2,
            'name' => 'Departmental- janitorial',
        ],
        [
            'id' => 3,
            'name' => 'Reinforcement - Skilled - bar benders',
        ],
        [
            'id' => 4,
            'name' => 'Reinforcement - semi- Skilled',
        ],
        [
            'id' => 5,
            'name' => 'Reinforcement - unskilled ',
        ],
        [
            'id' => 6,
            'name' => 'Shuttering - skilled carpenter',
        ],
        [
            'id' => 7,
            'name' => 'Shuttering - unskilled',
        ],
        [
            'id' => 8,
            'name' => 'Masonary - skilled',
        ],
        [
            'id' => 9,
            'name' => 'Masonary - unskilled -masonry helper',
        ],
        [
            'id' => 10,
            'name' => 'Fabrication - welders',
        ],
        [
            'id' => 11,
            'name' => 'Fabrication - skilled',
        ],
        [
            'id' => 12,
            'name' => 'Fabrication - helpers',
        ],
        [
            'id' => 13,
            'name' => 'Furniture - Carpenters',
        ],
        [
            'id' => 14,
            'name' => 'Plumbing - skilled',
        ],
        [
            'id' => 15,
            'name' => 'Plumbing - unskilled',
        ],
        [
            'id' => 16,
            'name' => 'Color - skilled',
        ],
        [
            'id' => 17,
            'name' => 'Security',
        ],
        [
            'id' => 18,
            'name' => 'Concrete pump labor',
        ],
        [
            'id' => 19,
            'name' => 'Electrician - skilled',
        ],
        [
            'id' => 20,
            'name' => 'Electrician - helper',
        ],
    ];
    return render_select($name_type, $result, array('id', 'name'), '', $type);
}

function get_vendor($name_agency, $agency)
{
    $id = '';
    $where = [];
    $CI = &get_instance();

    $CI->db->select(implode(',', prefixed_table_fields_array(db_prefix() . 'pur_vendor')) . ',' . get_sql_select_vendor_company());

    if (is_numeric($id)) {

        $CI->db->join(db_prefix() . 'countries', '' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'pur_vendor.country', 'left');
        $CI->db->join(db_prefix() . 'pur_contacts', '' . db_prefix() . 'pur_contacts.userid = ' . db_prefix() . 'pur_vendor.userid AND is_primary = 1', 'left');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $CI->db->where($where);
        }

        $CI->db->where(db_prefix().'pur_vendor.userid', $id);
        $vendor = $CI->db->get(db_prefix() . 'pur_vendor')->row();

        if ($vendor && get_option('company_requires_vat_number_field') == 0) {
            $vendor->vat = null;
        }

        return $vendor;

    } else {

        if(!has_permission('purchase_vendors', '', 'view') && is_staff_logged_in()){

            $CI->db->join(db_prefix() . 'countries', '' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'pur_vendor.country', 'left');
            $CI->db->join(db_prefix() . 'pur_contacts', '' . db_prefix() . 'pur_contacts.userid = ' . db_prefix() . 'pur_vendor.userid AND is_primary = 1', 'left');

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $CI->db->where($where);
            }

            $CI->db->where(db_prefix().'pur_vendor.userid IN (SELECT vendor_id FROM '.db_prefix().'pur_vendor_admin WHERE staff_id=' . get_staff_user_id() . ')');
        }else{
            $CI->db->join(db_prefix() . 'countries', '' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'pur_vendor.country', 'left');
            $CI->db->join(db_prefix() . 'pur_contacts', '' . db_prefix() . 'pur_contacts.userid = ' . db_prefix() . 'pur_vendor.userid AND is_primary = 1', 'left');

            if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
                $CI->db->where($where);
            }
        }
    }

    $CI->db->order_by('company', 'asc');

    $result = $CI->db->get(db_prefix() . 'pur_vendor')->result_array();

    return render_select($name_agency, $result, array('userid', 'company'), '', $agency);
}

function get_client_listing()
{
    $CI = &get_instance();
    $CI->db->select('userid,company');
    return $CI->db->get(db_prefix().'clients')->result_array();
}

function get_items_listing_for_apc()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Are solvent, paint and fuel containers tightly capped and stored in safe places with only authorised access? ',
        ],
        [
            'id' => 2,
            'name' => 'Are access roads properly paved with tarmac?',
        ],
        [
            'id' => 3,
            'name' => 'Are locations where fuels, paints and solvents used or stored, well ventilated?',
        ],
        [
            'id' => 4,
            'name' => 'Are mechanical plant and vehicles regularly maintained?',
        ],
        [
            'id' => 5,
            'name' => 'Are wastes cement packaging disposed in proper waste receptacles?',
        ],
        [
            'id' => 6,
            'name' => 'Is illegal open burning prohibited and restricted?',
        ],
        [
            'id' => 7,
            'name' => 'Are sand heaps constantly wetted or covered to prevent and segregated from general waste?',
        ],
        [
            'id' => 8,
            'name' => 'Are discarded solvent and paint cans properly lidded',
        ],
        [
            'id' => 9,
            'name' => 'Are combustible materials stored away from volatile substances?',
        ],
        [
            'id' => 10,
            'name' => 'Is the cooking area for canteens well ventilated to disperse cooking vapours and fumes to reduce local nuisance?',
        ]
    ];
    return $result;
}
function get_items_listing_for_wpc()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Are solvent, paint and fuel containers tightly lidded and stored on raised platforms and accessed by authorised persons only?',
        ],
        [
            'id' => 2,
            'name' => 'Is  the  access  road  into  the site having properly  designed vehicle washing devices?',
        ],
        [
            'id' => 3,
            'name' => 'Are the locations for the use and storage of chemical containers bounded with sump facilities?',
        ],
        [
            'id' => 4,
            'name' => 'Are the mechanical plants and vehicles maintained regularly to prevent oil leakage? ',
        ],
        [
            'id' => 5,
            'name' => 'Are general wastes such as food wastes properly bagged and disposed in designated waste bins? ',
        ],
        [
            'id' => 6,
            'name' => 'Are toilet facilities regularly maintained in working order and connected to treatment facility?',
        ],
        [
            'id' => 7,
            'name' => 'Are sand heaps not located near drains and bounded to prevent sand from being washed away?',
        ],
        [
            'id' => 8,
            'name' => 'Are waste containers not overfilled and disposed promptly?',
        ],
        [
            'id' => 9,
            'name' => 'Is toxic wastes segregated from general wastes for removal by licensed contractors?',
        ],
        [
            'id' => 10,
            'name' => 'Does the site canteen have washing facilities connected to proper treatment and disposal plants?',
        ],
        [
            'id' => 11,
            'name' => 'Are there oil / diesel / paint stains on the ground?',
        ],
        [
            'id' => 12,
            'name' => 'Any provision of secondary containment for oil / diesel / paint storage area & fuel filling points?',
        ]
    ];
    return $result;
}
function get_items_listing_for_mfa()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Sterilized Bandage (small, Medium & Large)',
        ],
        [
            'id' => 2,
            'name' => 'Sterilized Cotton Wool',
        ],
        [
            'id' => 3,
            'name' => 'Roller Bandage (10cm & 5cm)',
        ],
        [
            'id' => 4,
            'name' => 'Absorbent Gauze (Packet of 10 pieces)',
        ],
        [
            'id' => 5,
            'name' => 'Antiseptic Cream',
        ],
        [
            'id' => 6,
            'name' => 'Triangular Bandages',
        ],
        [
            'id' => 7,
            'name' => 'Scissors',
        ],
        [
            'id' => 8,
            'name' => 'Safety Pin',
        ],
        [
            'id' => 9,
            'name' => 'Disposable Gloves (Pairs)',
        ],
        [
            'id' => 10,
            'name' => 'Eye drop/ ear drop',
        ],
        [
            'id' => 11,
            'name' => 'Eye pad',
        ],
        [
            'id' => 12,
            'name' => 'burn dressings',
        ],
        [
            'id' => 13,
            'name' => 'Snake bit Lancet',
        ],
        [
            'id' => 14,
            'name' => 'Sterile water or saline in 100ml disposable container (only where tap water is not available)',
        ],
        [
            'id' => 15,
            'name' => 'Torchlight',
        ]
    ];
    return $result;
}
function get_items_required_amount_mfa(){
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => '24/12/12',
        ],
        [
            'id' => 2,
            'name' => '15',
        ],
        [
            'id' => 3,
            'name' => '12/12',
        ],
        [
            'id' => 4,
            'name' => '15',
        ],
        [
            'id' => 5,
            'name' => '1',
        ],
        [
            'id' => 6,
            'name' => '10',
        ],
        [
            'id' => 7,
            'name' => '1',
        ],
        [
            'id' => 8,
            'name' => '3 Packets',
        ],
        [
            'id' => 9,
            'name' => '2 Packets',
        ],
        [
            'id' => 10,
            'name' => '1',
        ],
        [
            'id' => 11,
            'name' => '12',
        ],
        [
            'id' => 12,
            'name' => '12',
        ],
        [
            'id' => 13,
            'name' => '2',
        ],
        [
            'id' => 14,
            'name' => '1 bottle',
        ],
        [
            'id' => 15,
            'name' => '1',
        ]
    ];
    return $result;
}
function get_items_listing_for_mlg()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Kinking/ Wear & Tear Condition ',
        ],
        [
            'id' => 2,
            'name' => 'Hook',
        ],
        [
            'id' => 3,
            'name' => 'Safety Latch',
        ],
        [
            'id' => 4,
            'name' => 'Handle C/W Pin',
        ],
        [
            'id' => 5,
            'name' => 'Colour Code',
        ],
        [
            'id' => 6,
            'name' => 'Overall condition',
        ],
        
    ];
    return $result;
}
function get_items_listing_for_msh()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Lanyard',
        ],
        [
            'id' => 2,
            'name' => 'Eye Loop',
        ],
        [
            'id' => 3,
            'name' => 'Snap Hook',
        ],
        [
            'id' => 4,
            'name' => 'Karabiner',
        ],
        [
            'id' => 5,
            'name' => 'Shock Absorber',
        ],
        [
            'id' => 6,
            'name' => 'Dorsal Anchorage',
        ],
        [
            'id' => 7,
            'name' => 'Strap',
        ],
        [
            'id' => 8,
            'name' => 'Buckler',
        ],  
        [
            'id' => 9,
            'name' => 'Colour Code',
        ],
        
    ];
    return $result;
}
function get_items_listing_for_sca()
{
    $result = array();
    $result = [
        [
            'id' => 1,
            'name' => 'Have warning notices stating “CAUTION, DO NOT USE THE SCAFFOLD” been fixed in prominent positions before dismantling the scaffold?',
        ],
        [
            'id' => 2,
            'name' => 'Is the scaffold safe to dismantle?',
        ],
        [
            'id' => 3,
            'name' => 'Have all the scaffolders been warned that if they deliberately or negligently drop any materials, they are liable to instant dismissal and/or followed by legal action?',
        ],
        [
            'id' => 4,
            'name' => 'Have scaffolders been warned not to release any tie-ins in advance of scaffold dismantling  or to do anything that will make the scaffold unstable during dismantling?',
        ],
        [
            'id' => 5,
            'name' => 'Have barricades been erected to restrict casual access to the scaffold?',
        ],
        [
            'id' => 6,
            'name' => 'Are  all  personnel,  not  involved  in  the  actual  dismantling, removed from the site of the scaffold? ',
        ],
        [
            'id' => 7,
            'name' => 'Are the scaffolders wearing safety helmets which will not slip?',
        ],
        [
            'id' => 8,
            'name' => 'Are the scaffolders supplied with personal safety protection devices like safety harness, life line, etc., and do hey use these devices?',
        ],  
        [
            'id' => 9,
            'name' => 'Are all local hazards such as lighting poles, telephone wires, etc., being recognised and allowed for? ',
        ],
        [
            'id' => 10,
            'name' => 'Is   there a qualified   scaffold   supervisor to supervise dismantling operation?',
        ],
        [
            'id' => 11,
            'name' => 'Have  ropes  been  provided  for  lowering  down  tubes  and boards, and baskets or buckets for fittings?',
        ],
        [
            'id' => 12,
            'name' => 'Have any adjacent temporary structures which may be affected by the removal of the scaffold been checked for safety? A Professional Engineer should always be asked to advise?',
        ],
        [
            'id' => 13,
            'name' => 'Have you inspected the site of the dismantled scaffold and taken note of the effects which may affect the other scaffolds in the vicinity? ',
        ],
        
    ];
    return $result;
}
function get_item_status_listing()
{
    $result = array();
    $result = [
        [
            'id' => '1',
            'name' => 'Yes',
        ],
        [
            'id' => '2',
            'name' => 'No',
        ],
        [
            'id' => '3',
            'name' => 'May Be',
        ]
    ];
    return $result;
}
function get_staff_list($where = '')
{
    $CI = &get_instance();
    $CI->db->select('staffid,concat(firstname," ",lastname) as name');
    if ($where != '') {
        $CI->db->where($where);
    }
    return $CI->db->get(db_prefix().'staff')->result_array();
}
function get_items_listing_for_esc()
{
    $result = array();

    $result = [
        ['id' => 1, 'name' => 'Is the SHOCK TREATMENT CHART displayed at prime locations?'],
        ['id' => 2, 'name' => 'Are SAFETY HELMETS, SAFETY SHOES compulsorily while working at site?'],
        ['id' => 3, 'name' => 'Are wires, cables, and switches of approved quality and with ISI mark?'],
        ['id' => 4, 'name' => 'Are all switch boards, extension boards etc. properly anchored/fixed?'],
        ['id' => 5, 'name' => 'Are all switch boards, extension boards etc. protected from rain and water?'],
        ['id' => 6, 'name' => 'Are ELCB/RCCB installed at all main boards & extension boards to avoid fatal shock?'],
        ['id' => 7, 'name' => 'Are appropriately rated fuses provided always?'],
        ['id' => 8, 'name' => 'Are industrial safety pin & plugs used for temporary connections?'],
        ['id' => 9, 'name' => 'Is it ensured that cables for all temporary connections are double insulated type and without joints/damages?'],
        ['id' => 10, 'name' => 'Are insulated and safely secured electrical conduits used for transmission?'],
        ['id' => 11, 'name' => 'Is the usage of rubber mat and electrical rubber gloves insisted while handling electrical equipment?'],
        ['id' => 12, 'name' => 'Are all circuits treated as ‘LIVE’ unless ensured otherwise?'],
        ['id' => 13, 'name' => 'Are warning signs of high voltage such as “Danger 440 V” posted where unauthorized employees might come in contact with live parts?'],
        ['id' => 14, 'name' => 'Is the grounding requirement for the systems, circuits, and equipment as per the specification?'],
        ['id' => 15, 'name' => 'Are all noncurrent-carrying metal parts of portable and fixed equipment grounded?'],
        ['id' => 16, 'name' => 'Are routine checks of all temporary power connections carried out and records maintained?'],
        ['id' => 17, 'name' => 'Is periodic check carried out for defective cables, cracked insulation, loose joints, and faulty sockets?'],
        ['id' => 18, 'name' => 'Is it ensured that kinking, twisting, binding, or crushing of cables is avoided at all times?'],
        ['id' => 19, 'name' => 'Is it ensured that the Electrical system is not overloaded?'],
        ['id' => 20, 'name' => 'Are cords around structural steel and sheet metal protected from mechanical damage?'],
        ['id' => 21, 'name' => 'Is safe overhead distance of HT cables as per Indian Electricity Rules maintained?'],
        ['id' => 22, 'name' => 'Is routing of temporary cables through floors avoided?'],
        ['id' => 23, 'name' => 'Is it ensured that routing of temporary cables does not cause hindrance to working persons?'],
        ['id' => 24, 'name' => 'Is it ensured that temporary cables are fixed firmly to poles/structures/walls?'],
        ['id' => 25, 'name' => 'Are separate and distinct neutral wire and body earth provided?'],
        ['id' => 26, 'name' => 'Is it ensured that no electrical work is carried out on wet surfaces or in the open while it is raining?'],
        ['id' => 27, 'name' => 'Is it ensured that electric equipment/switches are not operated with wet hands?'],
        ['id' => 28, 'name' => 'Is it ensured that electrical switchboards, portable tools, and equipment don’t get wet during usage? If it happens, is the main supply stopped, the tools dried, and then only used?'],
        ['id' => 29, 'name' => 'Is it ensured that extension cords are not run from one floor to another on multi-level sites?'],
        ['id' => 30, 'name' => 'Is it ensured that temporary power connections are not tampered with and no unauthorized connections are made?'],
        ['id' => 31, 'name' => 'Is it ensured that any electric leads, cables, welding transformers, DBs, switch boards, or any equipment connected to the main supply are not left unattended?'],
        ['id' => 32, 'name' => 'Is it ensured that no unauthorized person enters electrical equipment rooms or interferes with electrical apparatus?'],
        ['id' => 33, 'name' => 'Is it ensured that no earthing wire is connected to the pipelines/structures?'],
        ['id' => 34, 'name' => 'Is it ensured that water/water-type fire extinguishers are not used to put off electrical circuit fires?'],
    ];

    return $result;
}
