<?php

use app\services\MergeForms;

defined('BASEPATH') or exit('No direct script access allowed');

class Forms_model extends App_Model
{
    private $piping = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function form_count($status = null)
    {
        $where = 'AND merged_form_id is NULL';
        if (!is_admin()) {
            $this->load->model('departments_model');
            $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $departments_ids = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'AND department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }
        }
        $_where = '';
        if (!is_null($status)) {
            if ($where == '') {
                $_where = 'status=' . $status;
            } else {
                $_where = 'status=' . $status . ' ' . $where;
            }
        }

        return total_rows(db_prefix() . 'forms', $_where);
    }

    public function insert_piped_form($data)
    {
        $data = hooks()->apply_filters('piped_form_data', $data);

        $this->piping = true;
        $attachments  = $data['attachments'];
        $subject      = $data['subject'];
        // Prevent insert form to database if mail delivery error happen
        // This will stop createing a thousand forms
        $system_blocked_subjects = [
            'Mail delivery failed',
            'failure notice',
            'Returned mail: see transcript for details',
            'Undelivered Mail Returned to Sender',
        ];

        $subject_blocked = false;

        foreach ($system_blocked_subjects as $sb) {
            if (strpos('x' . $subject, $sb) !== false) {
                $subject_blocked = true;

                break;
            }
        }

        if ($subject_blocked == true) {
            return;
        }

        $message = $data['body'];
        $name    = $data['fromname'];

        $email   = $data['email'];
        $to      = $data['to'];
        $cc      = $data['cc'] ?? [];
        $subject = $subject;
        $message = $message;

        $this->load->model('spam_filters_model');
        $mailstatus = $this->spam_filters_model->check($email, $subject, $message, 'forms');

        // No spam found
        if (!$mailstatus) {
            $pos = strpos($subject, '[Form ID: ');
            if ($pos === false) {
            } else {
                $tid = substr($subject, $pos + 12);
                $tid = substr($tid, 0, strpos($tid, ']'));
                $this->db->where('formid', $tid);
                $data = $this->db->get(db_prefix() . 'forms')->row();
                $tid  = $data->formid;
            }
            $to            = trim($to);
            $toemails      = explode(',', $to);
            $department_id = false;
            $userid        = false;
            foreach ($toemails as $toemail) {
                if (!$department_id) {
                    $this->db->where('email', trim($toemail));
                    $data = $this->db->get(db_prefix() . 'departments')->row();
                    if ($data) {
                        $department_id = $data->departmentid;
                        $to            = $data->email;
                    }
                }
            }
            if (!$department_id) {
                $mailstatus = 'Department Not Found';
            } else {
                if ($to == $email) {
                    $mailstatus = 'Blocked Potential Email Loop';
                } else {
                    $message = trim($message);
                    $this->db->where('active', 1);
                    $this->db->where('email', $email);
                    $result = $this->db->get(db_prefix() . 'staff')->row();
                    if ($result) {
                        if ($tid) {
                            $data            = [];
                            $data['message'] = $message;
                            $data['status']  = get_option('default_form_reply_status');

                            if (!$data['status']) {
                                $data['status'] = 3; // Answered
                            }

                            if ($userid == false) {
                                $data['name']  = $name;
                                $data['email'] = $email;
                            }

                            if (count($cc) > 0) {
                                $data['cc'] = $cc;
                            }

                            $reply_id = $this->add_reply($data, $tid, $result->staffid, $attachments);
                            if ($reply_id) {
                                $mailstatus = 'Form Reply Imported Successfully';
                            }
                        } else {
                            $mailstatus = 'Form ID Not Found';
                        }
                    } else {
                        $this->db->where('email', $email);
                        $result = $this->db->get(db_prefix() . 'contacts')->row();
                        if ($result) {
                            $userid    = $result->userid;
                            $contactid = $result->id;
                        }
                        if ($userid == false && get_option('email_piping_only_registered') == '1') {
                            $mailstatus = 'Unregistered Email Address';
                        } else {
                            $filterdate = date('Y-m-d H:i:s', strtotime('-15 minutes'));
                            $query      = 'SELECT count(*) as total FROM ' . db_prefix() . 'forms WHERE date > "' . $filterdate . '" AND (email="' . $this->db->escape($email) . '"';
                            if ($userid) {
                                $query .= ' OR userid=' . (int) $userid;
                            }
                            $query .= ')';
                            $result = $this->db->query($query)->row();
                            if (10 < $result->total) {
                                $mailstatus = 'Exceeded Limit of 10 Forms within 15 Minutes';
                            } else {
                                if (isset($tid)) {
                                    $data            = [];
                                    $data['message'] = $message;
                                    $data['status']  = 1;
                                    if ($userid == false) {
                                        $data['name']  = $name;
                                        $data['email'] = $email;
                                    } else {
                                        $data['userid']    = $userid;
                                        $data['contactid'] = $contactid;

                                        $this->db->where('formid', $tid);
                                        $this->db->group_start();
                                        $this->db->where('userid', $userid);

                                        // Allow CC'ed user to reply to the form
                                        $this->db->or_like('cc', $email);
                                        $this->db->group_end();
                                        $t = $this->db->get(db_prefix() . 'forms')->row();
                                        if (!$t) {
                                            $abuse = true;
                                        }
                                    }
                                    if (!isset($abuse)) {
                                        if (count($cc) > 0) {
                                            $data['cc'] = $cc;
                                        }
                                        $reply_id = $this->add_reply($data, $tid, null, $attachments);
                                        if ($reply_id) {
                                            // Dont change this line
                                            $mailstatus = 'Form Reply Imported Successfully';
                                        }
                                    } else {
                                        $mailstatus = 'Form ID Not Found For User';
                                    }
                                } else {
                                    if (get_option('email_piping_only_registered') == 1 && !$userid) {
                                        $mailstatus = 'Blocked Form Opening from Unregistered User';
                                    } else {
                                        if (get_option('email_piping_only_replies') == '1') {
                                            $mailstatus = 'Only Replies Allowed by Email';
                                        } else {
                                            $data               = [];
                                            $data['department'] = $department_id;
                                            $data['subject']    = $subject;
                                            $data['message']    = $message;
                                            $data['contactid']  = $contactid;
                                            $data['priority']   = get_option('email_piping_default_priority');
                                            if ($userid == false) {
                                                $data['name']  = $name;
                                                $data['email'] = $email;
                                            } else {
                                                $data['userid'] = $userid;
                                            }
                                            $tid = $this->add($data, null, $attachments);
                                            if ($tid && count($cc) > 0) {
                                                // A customer opens a form by mail to "support@example".com, with one or many 'Cc'
                                                // Remember those 'Cc'.
                                                $this->db->where('formid', $tid);
                                                $this->db->update('forms', ['cc' => implode(',', $cc)]);
                                            }
                                            // Dont change this line
                                            $mailstatus = 'Form Imported Successfully';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($mailstatus == '') {
            $mailstatus = 'Form Import Failed';
        }
        $this->db->insert(db_prefix() . 'forms_pipe_log', [
            'date'     => date('Y-m-d H:i:s'),
            'email_to' => $to,
            'name'     => $name ?: 'Unknown',
            'email'    => $email ?: 'N/A',
            'subject'  => $subject ?: 'N/A',
            'message'  => $message,
            'status'   => $mailstatus,
        ]);

        return $mailstatus;
    }

    private function process_pipe_attachments($attachments, $form_id, $reply_id = '')
    {
        if (!empty($attachments)) {
            $form_attachments = [];
            $allowed_extensions = array_map(function ($ext) {
                return strtolower(trim($ext));
            }, explode(',', get_option('form_attachments_file_extensions')));

            $path = FCPATH . 'uploads/form_attachments' . '/' . $form_id . '/';

            foreach ($attachments as $attachment) {
                $filename      = $attachment['filename'];
                $filenameparts = explode('.', $filename);
                $extension     = end($filenameparts);
                $extension     = strtolower($extension);
                if (in_array('.' . $extension, $allowed_extensions)) {
                    $filename = implode(array_slice($filenameparts, 0, 0 - 1));
                    $filename = trim(preg_replace('/[^a-zA-Z0-9-_ ]/', '', $filename));

                    if (!$filename) {
                        $filename = 'attachment';
                    }

                    if (!file_exists($path)) {
                        mkdir($path, 0755);
                        $fp = fopen($path . 'index.html', 'w');
                        fclose($fp);
                    }

                    $filename = unique_filename($path, $filename . '.' . $extension);
                    file_put_contents($path . $filename, $attachment['data']);

                    array_push($form_attachments, [
                        'file_name' => $filename,
                        'filetype'  => get_mime_by_extension($filename),
                    ]);
                }
            }

            $this->insert_form_attachments_to_database($form_attachments, $form_id, $reply_id);
        }
    }

    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'forms.userid,' . db_prefix() . 'forms.name as from_name,' . db_prefix() . 'forms.email as form_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'forms_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'forms.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'forms.formid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,lastreply,message,' . db_prefix() . 'forms.status,subject,department,priority,' . db_prefix() . 'contacts.email,adminread,clientread,date');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->join(db_prefix() . 'forms_priorities', db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'forms.formid', $id);

            return $this->db->get(db_prefix() . 'forms')->row();
        }
        $this->db->order_by('lastreply', 'asc');

        if (is_client_logged_in()) {
            $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);
        }

        return $this->db->get(db_prefix() . 'forms')->result_array();
    }

    /**
     * Get form by id and all data
     * @param  mixed  $id     form id
     * @param  mixed $userid Optional - Forms from USER ID
     * @return object
     */
    public function get_form_by_id($id, $userid = '')
    {
        $this->db->select('*, ' . db_prefix() . 'forms.userid, ' . db_prefix() . 'forms.name as from_name, ' . db_prefix() . 'forms.email as form_email, ' . db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'forms_priorities.name as priority_name, statuscolor, ' . db_prefix() . 'forms.admin, ' . db_prefix() . 'services.name as service_name, service, ' . db_prefix() . 'forms_status.name as status_name, ' . db_prefix() . 'forms.formid, ' . db_prefix() . 'contacts.firstname as user_firstname, ' . db_prefix() . 'contacts.lastname as user_lastname, ' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname, lastreply, message, ' . db_prefix() . 'forms.status, subject, department, priority, ' . db_prefix() . 'contacts.email, adminread, clientread, date');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'forms.contactid', 'left');
        $this->db->join(db_prefix() . 'forms_priorities', db_prefix() . 'forms_priorities.priorityid = ' . db_prefix() . 'forms.priority', 'left');

        if (strlen($id) === 32) {
            $this->db->where(db_prefix() . 'forms.formkey', $id);
        } else {
            $this->db->where(db_prefix() . 'forms.formid', $id);
        }

        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'forms.userid', $userid);
        }

        $form = $this->db->get()->row();
        if ($form) {
            $form->submitter = $form->contactid != 0 ?
                ($form->user_firstname . ' ' . $form->user_lastname) :
                $form->from_name;

            if (!($form->admin == null || $form->admin == 0)) {
                $form->opened_by = $form->staff_firstname . ' ' . $form->staff_lastname;
            }

            $form->attachments = $this->get_form_attachments($form->formid);
        }


        return $form;
    }

    /**
     * Insert form attachments to database
     * @param  array  $attachments array of attachment
     * @param  mixed  $formid
     * @param  boolean $replyid If is from reply
     */
    public function insert_form_attachments_to_database($attachments, $formid, $replyid = false)
    {
        foreach ($attachments as $attachment) {
            $attachment['formid']  = $formid;
            $attachment['dateadded'] = date('Y-m-d H:i:s');
            if ($replyid !== false && is_int($replyid)) {
                $attachment['replyid'] = $replyid;
            }
            $this->db->insert(db_prefix() . 'form_attachments', $attachment);
        }
    }

    /**
     * Get form attachments from database
     * @param  mixed $id      form id
     * @param  mixed $replyid Optional - reply id if is from from reply
     * @return array
     */
    public function get_form_attachments($id, $replyid = '')
    {
        $this->db->where('formid', $id);
        $this->db->where('replyid', is_numeric($replyid) ? $replyid : null);

        return $this->db->get('form_attachments')->result_array();
    }

    /**
     * Add new reply to form
     * @param mixed $data  reply $_POST data
     * @param mixed $id    form id
     * @param boolean $admin staff id if is staff making reply
     */
    public function add_reply($data, $id, $admin = null, $pipe_attachments = false)
    {
        if (isset($data['assign_to_current_user'])) {
            $assigned = get_staff_user_id();
            unset($data['assign_to_current_user']);
        }

        $unsetters = [
            'note_description',
            'department',
            'priority',
            'subject',
            'assigned',
            'project_id',
            'service',
            'status_top',
            'attachments',
            'DataTables_Table_0_length',
            'DataTables_Table_1_length',
            'custom_fields',
        ];

        foreach ($unsetters as $unset) {
            if (isset($data[$unset])) {
                unset($data[$unset]);
            }
        }

        if ($admin !== null) {
            $data['admin'] = $admin;
            $status        = $data['status'];
        } else {
            $status = 1;
        }

        if (isset($data['status'])) {
            unset($data['status']);
        }

        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        // if form is merged
        $form           = $this->get($id);
        $data['formid'] = ($form && $form->merged_form_id != null) ? $form->merged_form_id : $id;
        $data['date']     = date('Y-m-d H:i:s');
        $data['message']  = trim($data['message']);

        if ($this->piping == true) {
            // $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        }

        $is_html_stripped = $this->piping === true;

        // admin can have html
        if (
            !$is_html_stripped &&
            $admin == null &&
            hooks()->apply_filters('form_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        // $data['message'] = remove_emojis($data['message']);
        $data            = hooks()->apply_filters('before_form_reply_add', $data, $id, $admin);

        $this->db->insert(db_prefix() . 'form_replies', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            /**
             * When a form is in status "In progress" and the customer reply to the form
             * it changes the status to "Open" which is not normal.
             *
             * The form should keep the status "In progress"
             */
            $this->db->select('status');
            $this->db->where('formid', $id);
            $old_form_status = $this->db->get(db_prefix() . 'forms')->row()->status;

            $newStatus = hooks()->apply_filters(
                'form_reply_status',
                ($old_form_status == 2 && $admin == null ? $old_form_status : $status),
                ['form_id' => $id, 'reply_id' => $insert_id, 'admin' => $admin, 'old_status' => $old_form_status]
            );

            if (isset($assigned)) {
                $this->db->where('formid', $id);
                $this->db->update(db_prefix() . 'forms', [
                    'assigned' => $assigned,
                ]);
            }

            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $id, $insert_id);
            } else {
                $attachments = handle_form_attachments($id);
                if ($attachments) {
                    $this->forms_model->insert_form_attachments_to_database($attachments, $id, $insert_id);
                }
            }

            $_attachments = $this->get_form_attachments($id, $insert_id);

            log_activity('New Form Reply [ReplyID: ' . $insert_id . ']');

            $this->db->where('formid', $id);
            $this->db->update(db_prefix() . 'forms', [
                'lastreply'  => date('Y-m-d H:i:s'),
                'status'     => $newStatus,
                'adminread'  => 0,
                'clientread' => 0,
            ]);

            if ($old_form_status != $newStatus) {
                hooks()->do_action('after_form_status_changed', [
                    'id'     => $id,
                    'status' => $newStatus,
                ]);
            }

            $form    = $this->get_form_by_id($id);
            $userid    = $form->userid;
            $isContact = false;
            if ($form->userid != 0 && $form->contactid != 0) {
                $email     = $this->clients_model->get_contact($form->contactid)->email;
                $isContact = true;
            } else {
                $email = $form->form_email;
            }
            if ($admin == null) {
                $this->load->model('departments_model');
                $this->load->model('staff_model');

                $notifiedUsers = [];
                $staff         = $this->getStaffMembersForFormNotification($form->department, $form->assigned);
                foreach ($staff as $staff_key => $member) {
                    // send_mail_template('form_new_reply_to_staff', $form, $member, $_attachments);
                    if (get_option('receive_notification_on_new_form_replies') == 1) {
                        $notified = add_notification([
                            'description'     => 'not_new_form_reply',
                            'touserid'        => $member['staffid'],
                            'fromcompany'     => 1,
                            'fromuserid'      => 0,
                            'link'            => 'forms/form/' . $id,
                            'additional_data' => serialize([
                                $form->subject,
                            ]),
                        ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                $this->update_staff_replying($id);

                $total_staff_replies = total_rows(db_prefix() . 'form_replies', ['admin is NOT NULL', 'formid' => $form->formid]);
                if (
                    $form->assigned == 0 &&
                    get_option('automatically_assign_form_to_first_staff_responding') == '1' &&
                    $total_staff_replies == 1
                ) {
                    $this->db->where('formid', $id);
                    $this->db->update(db_prefix() . 'forms', ['assigned' => $admin]);
                }

                $sendEmail = true;
                if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $form->contactid]) == 0) {
                    $sendEmail = false;
                }
                if ($sendEmail) {
                    // send_mail_template('form_new_reply_to_customer', $form, $email, $_attachments, $cc);
                }
            }

            if ($cc) {
                // imported reply
                if (is_array($cc)) {
                    if ($form->cc) {
                        $currentCC = explode(',', $form->cc);
                        $cc        = array_unique([$cc, $currentCC]);
                    }
                    $cc = implode(',', $cc);
                }
                $this->db->where('formid', $id);
                $this->db->update('forms', ['cc' => $cc]);
            }
            hooks()->do_action('after_form_reply_added', [
                'data'    => $data,
                'id'      => $id,
                'admin'   => $admin,
                'replyid' => $insert_id,
            ]);

            return $insert_id;
        }

        return false;
    }

    /**
     *  Delete form reply
     * @param   mixed $form_id    form id
     * @param   mixed $reply_id     reply id
     * @return  boolean
     */
    public function delete_form_reply($form_id, $reply_id)
    {
        hooks()->do_action('before_delete_form_reply', ['form_id' => $form_id, 'reply_id' => $reply_id]);

        $this->db->where('id', $reply_id);
        $this->db->delete(db_prefix() . 'form_replies');

        if ($this->db->affected_rows() > 0) {
            // Get the reply attachments by passing the reply_id to get_form_attachments method
            $attachments = $this->get_form_attachments($form_id, $reply_id);
            if (count($attachments) > 0) {
                foreach ($attachments as $attachment) {
                    $this->delete_form_attachment($attachment['id']);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Remove form attachment by id
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_form_attachment($id)
    {
        $deleted = false;
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'form_attachments')->row();
        if ($attachment) {
            if (unlink(get_upload_path_by_type('form') . $attachment->formid . '/' . $attachment->file_name)) {
                $this->db->where('id', $attachment->id);
                $this->db->delete(db_prefix() . 'form_attachments');
                $deleted = true;
            }
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files(get_upload_path_by_type('form') . $attachment->formid);
            if (count($other_attachments) == 0) {
                delete_dir(get_upload_path_by_type('form') . $attachment->formid);
            }
        }

        return $deleted;
    }

    /**
     * Get form attachment by id
     * @param  mixed $id attachment id
     * @return mixed
     */
    public function get_form_attachment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'form_attachments')->row();
    }

    /**
     * This functions is used when staff open client form
     * @param  mixed $userid client id
     * @param  mixed $id     formid
     * @return array
     */
    public function get_user_other_forms($userid, $id)
    {
        $this->db->select(db_prefix() . 'departments.name as department_name, ' . db_prefix() . 'services.name as service_name,' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'clients.lastname as staff_lastname,formid,subject,firstname,lastname,lastreply');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'departments', db_prefix() . 'departments.departmentid = ' . db_prefix() . 'forms.department', 'left');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        $this->db->join(db_prefix() . 'services', db_prefix() . 'services.serviceid = ' . db_prefix() . 'forms.service', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'forms.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'forms.admin', 'left');
        $this->db->where(db_prefix() . 'forms.userid', $userid);
        $this->db->where(db_prefix() . 'forms.formid !=', $id);
        $forms = $this->db->get()->result_array();
        $i       = 0;
        foreach ($forms as $form) {
            $forms[$i]['submitter'] = $form['firstname'] . ' ' . $form['lastname'];
            unset($form['firstname']);
            unset($form['lastname']);
            $i++;
        }

        return $forms;
    }

    /**
     * Get all form replies
     * @param  mixed  $id     formid
     * @param  mixed $userid specific client id
     * @return array
     */
    public function get_form_replies($id)
    {
        $form_replies_order = get_option('form_replies_order');
        // backward compatibility for the action hook
        $form_replies_order = hooks()->apply_filters('form_replies_order', $form_replies_order);

        $this->db->select(db_prefix() . 'form_replies.id,' . db_prefix() . 'form_replies.name as from_name,' . db_prefix() . 'form_replies.email as reply_email, ' . db_prefix() . 'form_replies.admin, ' . db_prefix() . 'form_replies.userid,' . db_prefix() . 'staff.firstname as staff_firstname, ' . db_prefix() . 'staff.lastname as staff_lastname,' . db_prefix() . 'contacts.firstname as user_firstname,' . db_prefix() . 'contacts.lastname as user_lastname,message,date,contactid');
        $this->db->from(db_prefix() . 'form_replies');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'form_replies.userid', 'left');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'form_replies.admin', 'left');
        $this->db->join(db_prefix() . 'contacts', db_prefix() . 'contacts.id = ' . db_prefix() . 'form_replies.contactid', 'left');
        $this->db->where('formid', $id);
        $this->db->order_by('date', $form_replies_order);
        $replies = $this->db->get()->result_array();
        $i       = 0;
        foreach ($replies as $reply) {
            if ($reply['admin'] !== null || $reply['admin'] != 0) {
                // staff reply
                $replies[$i]['submitter'] = $reply['staff_firstname'] . ' ' . $reply['staff_lastname'];
            } else {
                if ($reply['contactid'] != 0) {
                    $replies[$i]['submitter'] = $reply['user_firstname'] . ' ' . $reply['user_lastname'];
                } else {
                    $replies[$i]['submitter'] = $reply['from_name'];
                }
            }
            unset($replies[$i]['staff_firstname']);
            unset($replies[$i]['staff_lastname']);
            unset($replies[$i]['user_firstname']);
            unset($replies[$i]['user_lastname']);
            $replies[$i]['attachments'] = $this->get_form_attachments($id, $reply['id']);
            $i++;
        }

        return $replies;
    }

    /**
     * Add new form to database
     * @param mixed $data  form $_POST data
     * @param mixed $admin If admin adding the form passed staff id
     */
    public function add($data, $admin = null, $pipe_attachments = false)
    {
        if ($admin !== null) {
            $data['admin'] = $admin;
            unset($data['form_client_search']);
        }

        if (isset($data['assigned']) && $data['assigned'] == '') {
            $data['assigned'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if ($admin == null) {
            if (isset($data['email'])) {
                $data['userid']    = 0;
                $data['contactid'] = 0;
            } else {
                // Opened from customer portal otherwise is passed from pipe or admin area
                if (!isset($data['userid']) && !isset($data['contactid'])) {
                    $data['userid']    = get_client_user_id();
                    $data['contactid'] = get_contact_user_id();
                }
            }
            $data['status'] = 1;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        // CC is only from admin area
        $cc = '';
        if (isset($data['cc'])) {
            $cc = $data['cc'];
            unset($data['cc']);
        }

        $data['date']      = date('Y-m-d H:i:s');
        $data['formkey'] = app_generate_hash();
        $data['status']    = 1;
        $data['message']   = trim($data['message']);
        $data['subject']   = trim($data['subject']);
        // if ($this->piping == true) {
        //     $data['message'] = preg_replace('/\v+/u', '<br>', $data['message']);
        // }

        $is_html_stripped = $this->piping === true;

        // Admin can have html
        if (
            !$is_html_stripped &&
            $admin == null &&
            hooks()->apply_filters('form_message_without_html_for_non_admin', true)
        ) {
            $data['message'] = _strip_tags($data['message']);
            $data['subject'] = _strip_tags($data['subject']);
            $data['message'] = nl2br_save_html($data['message']);
        }

        if (!isset($data['userid'])) {
            $data['userid'] = 0;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (isset($data['form_type'])) {
            if ($data['form_type'] == "dpr") {
                $dpr_form = array();
                $dpr_form['client_id'] = $data['client_id'];
                $dpr_form['pmc'] = $data['pmc'];
                $dpr_form['weather'] = $data['weather'];
                $dpr_form['consultant'] = $data['consultant'];
                $dpr_form['contractor'] = $data['contractor'];
                $dpr_form['work_stop'] = $data['work_stop'];
                unset($data['client_id']);
                unset($data['pmc']);
                unset($data['weather']);
                unset($data['consultant']);
                unset($data['contractor']);
                unset($data['work_stop']);
                unset($data['location']);
                unset($data['agency']);
                unset($data['type']);
                unset($data['work_execute']);
                unset($data['material_consumption']);
                unset($data['machinery']);
                unset($data['skilled']);
                unset($data['unskilled']);
                unset($data['depart']);
                unset($data['total']);
                unset($data['male']);
                unset($data['female']);
                $new_order = [];
                if (isset($data['newitems'])) {
                    $new_order = $data['newitems'];
                    unset($data['newitems']);
                }
            } elseif ($data['form_type'] == "apc") {
                $apc_form = [];
                $apc_form['date'] = $data['date'];
                $apc_form['location'] = $data['location'];
                $apc_form['inspected_by'] = $data['inspected_by'];
                unset($data['date']);
                unset($data['location']);
                unset($data['inspected_by']);
                unset($data['action']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "wpc") {
                $wpc_form = [];
                $wpc_form['date'] = $data['date'];
                $wpc_form['location'] = $data['location'];
                $wpc_form['inspected_by'] = $data['inspected_by'];
                unset($data['date']);
                unset($data['location']);
                unset($data['inspected_by']);
                unset($data['action']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "mfa") {
                $mfa_form = [];
                $mfa_form['checked_by'] = $data['checked_by'];
                $mfa_form['designation'] = $data['designation'];
                $mfa_form['location'] = $data['location'];
                $mfa_form['date'] = $data['date'];
                unset($data['checked_by']);
                unset($data['designation']);
                unset($data['location']);
                unset($data['date']);
                unset($data['action']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "mlg") {
                $mlg_form = [];
                $mlg_form['trade_of_work'] = $data['trade_of_work'];
                $mlg_form['name'] = $data['name'];
                $mlg_form['type'] = $data['type'];
                $mlg_form['lgno'] = $data['lgno'];
                $mlg_form['expiry_date'] = $data['expiry_date'];
                $mlg_form['swl'] = $data['swl'];
                $mlg_form['remarks'] = $data['remarks'];
                $mlg_form['date'] = $data['date'];
                unset($data['trade_of_work']);
                unset($data['name']);
                unset($data['type']);
                unset($data['lgno']);
                unset($data['expiry_date']);
                unset($data['swl']);
                unset($data['remarks']);
                unset($data['date']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "msh") {
                $msh_form = [];
                $msh_form['trade_of_work'] = $data['trade_of_work'];
                $msh_form['inspected_by'] = $data['inspected_by'];
                $msh_form['shi'] = $data['shi'];
                $msh_form['remarks'] = $data['remarks'];
                $msh_form['date'] = $data['date'];
                unset($data['trade_of_work']);
                unset($data['inspected_by']);
                unset($data['shi']);
                unset($data['remarks']);
                unset($data['date']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "sca") {
                $sca_form = [];
                $sca_form['area_of_work'] = $data['area_of_work'];
                $sca_form['scaffold_supervisor'] = $data['scaffold_supervisor'];
                $sca_form['date'] = $data['date'];
                unset($data['area_of_work']);
                unset($data['scaffold_supervisor']);
                unset($data['date']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            } elseif ($data['form_type'] == "esc") {
                $esc_form = [];
                $esc_form['date'] = $data['date'];
                $esc_form['location'] = $data['location'];
                $esc_form['inspected_by'] = $data['inspected_by'];
                unset($data['date']);
                unset($data['location']);
                unset($data['inspected_by']);
                unset($data['action']);
                $new_order = [];
                if (isset($data['items'])) {
                    $new_order = $data['items'];
                    unset($data['items']);
                }
            }
        }

        // $data['message'] = remove_emojis($data['message']);
        $data = hooks()->apply_filters('before_form_created', $data, $admin);
        // echo '<pre>';
        // print_r($data);
        // die;
        $this->db->insert(db_prefix() . 'forms', $data);
        $formid = $this->db->insert_id();
        if ($formid) {
            if ($data['form_type'] == "dpr") {
                if (isset($dpr_form)) {
                    if (!empty($dpr_form)) {
                        $dpr_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $dpr_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['location'] = $value['location'];
                            $dt_data['agency'] = $value['agency'];
                            $dt_data['type'] = $value['type'];
                            $dt_data['work_execute'] = $value['work_execute'];
                            $dt_data['material_consumption'] = $value['material_consumption'];
                            $dt_data['machinery'] = $value['machinery'];
                            $dt_data['skilled'] = $value['skilled'];
                            $dt_data['unskilled'] = $value['unskilled'];
                            $dt_data['depart'] = $value['depart'];
                            $dt_data['total'] = $value['total'];
                            $dt_data['male'] = $value['male'];
                            $dt_data['female'] = $value['female'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                        }
                    }
                }
            } elseif ($data['form_type'] == "apc") {
                if (isset($apc_form)) {
                    if (!empty($apc_form)) {
                        $apc_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $apc_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['items'] = $sr;
                            $dt_data['status'] = $value['status'];
                            $dt_data['remarks'] = $value['remarks'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('apc_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'apcattachments', $idata);
                                    }
                                }
                            }
                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "wpc") {
                if (isset($wpc_form)) {
                    if (!empty($wpc_form)) {
                        $wpc_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $wpc_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['items'] = $sr;
                            $dt_data['status'] = $value['status'];
                            $dt_data['remarks'] = $value['remarks'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('wpc_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'wpcattachments', $idata);
                                    }
                                }
                            }
                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "mfa") {
                if (isset($mfa_form)) {
                    if (!empty($mfa_form)) {
                        $mfa_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $mfa_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['contents'] = $sr;
                            $dt_data['available_amount'] = $value['available_amount'] ?? null;
                            $dt_data['remarks'] = $value['remarks'] ?? null;
                            $dt_data['small'] =  $value['small'] ?? null;
                            $dt_data['medium'] = $value['medium'] ?? null;
                            $dt_data['large'] = $value['lagar'] ?? null;
                            $dt_data['10cm'] = $value['10cm'] ?? null;
                            $dt_data['5cm'] = $value['5cm'] ?? null;


                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "mlg") {
                if (isset($mlg_form)) {
                    if (!empty($mlg_form)) {
                        $mlg_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $mlg_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['description'] = $sr;
                            $dt_data['checks'] = $value['checks'] ?? null;
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('mlg_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'mlgattachments', $idata);
                                    }
                                }
                            }

                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "msh") {
                if (isset($msh_form)) {
                    if (!empty($msh_form)) {
                        $msh_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $msh_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['description'] = $sr;
                            $dt_data['checks'] = $value['checks'] ?? null;
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('msh_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'mshattachments', $idata);
                                    }
                                }
                            }

                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "sca") {
                if (isset($sca_form)) {
                    if (!empty($sca_form)) {
                        $sca_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $sca_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['description'] = $sr;
                            $dt_data['checks'] = $value['checks'] ?? null;
                            $dt_data['comments'] = $value['comments'] ?? null;
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('sca_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'scaattachments', $idata);
                                    }
                                }
                            }

                            $sr++;
                        }
                    }
                }
            } elseif ($data['form_type'] == "esc") {
                if (isset($esc_form)) {
                    if (!empty($esc_form)) {
                        $esc_form['form_id'] = $formid;
                        $this->db->insert(db_prefix() . $data['form_type'] . '_form', $esc_form);
                    }
                }
                if (isset($new_order)) {
                    if (!empty($new_order)) {
                        $sr = 1;
                        foreach ($new_order as $key => $value) {
                            $dt_data = [];
                            $dt_data['form_id'] = $formid;
                            $dt_data['items'] = $sr;
                            $dt_data['status'] = $value['status'];
                            $dt_data['remarks'] = $value['remarks'];
                            $this->db->insert(db_prefix() . $data['form_type'] . '_form_detail', $dt_data);
                            $insert_id = $this->db->insert_id();
                            // Handle file attachments dynamically for items and attachments_new
                            $iuploadedFiles = handle_ckecklist_item_attachment_array('esc_checklist', $formid, $insert_id, 'items', $sr);

                            if ($iuploadedFiles && is_array($iuploadedFiles)) {
                                if (!empty($iuploadedFiles)) {
                                    foreach ($iuploadedFiles as $file) {
                                        $idata = [
                                            'form_id' =>  $formid,
                                            'form_detail_id' =>  $file['item_id'],
                                            'file_name' => $file['file_name'],
                                            'filetype' => $file['filetype'],
                                        ];
                                        $this->db->insert(db_prefix() . 'escattachments', $idata);
                                    }
                                }
                            }
                            $sr++;
                        }
                    }
                }
            }
            handle_tags_save($tags, $formid, 'form');

            if (isset($custom_fields)) {
                handle_custom_fields_post($formid, $custom_fields);
            }

            if (isset($data['assigned']) && $data['assigned'] != 0) {
                if ($data['assigned'] != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_form_assigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'forms/form/' . $formid,
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);

                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }

                    // send_mail_template('form_assigned_to_staff', get_staff($data['assigned'])->email, $data['assigned'], $formid, $data['userid'], $data['contactid']);
                }
            }
            if ($pipe_attachments != false) {
                $this->process_pipe_attachments($pipe_attachments, $formid);
            } else {
                $attachments = handle_form_attachments($formid);
                if ($attachments) {
                    $this->insert_form_attachments_to_database($attachments, $formid);
                }
            }

            $_attachments = $this->get_form_attachments($formid);


            $isContact = false;
            if (isset($data['userid']) && $data['userid'] != false) {
                $email     = $this->clients_model->get_contact($data['contactid'])->email;
                $isContact = true;
            } else {
                $email = $data['email'];
            }

            $template = 'form_created_to_customer';
            if ($admin == null) {
                // $template      = 'form_autoresponse';
                // $notifiedUsers = [];
                // $staffToNotify = $this->getStaffMembersForFormNotification($data['department'], $data['assigned'] ?? 0);
                // foreach ($staffToNotify as $member) {
                //     send_mail_template('form_created_to_staff', $formid, $data['userid'], $data['contactid'], $member, $_attachments);
                //     if (get_option('receive_notification_on_new_form') == 1) {
                //         $notified = add_notification([
                //             'description'     => 'not_new_form_created',
                //             'touserid'        => $member['staffid'],
                //             'fromcompany'     => 1,
                //             'fromuserid'      => 0,
                //             'link'            => 'forms/form/' . $formid,
                //             'additional_data' => serialize([
                //                 $data['subject'],
                //             ]),
                //         ]);
                //         if ($notified) {
                //             $notifiedUsers[] = $member['staffid'];
                //         }
                //     }
                // }
                pusher_trigger_notification($notifiedUsers);
            } else {
                if ($cc) {
                    $this->db->where('formid', $formid);
                    $this->db->update('forms', ['cc' => is_array($cc) ? implode(',', $cc) : $cc]);
                }
            }

            $sendEmail = true;

            if ($isContact && total_rows(db_prefix() . 'contacts', ['ticket_emails' => 1, 'id' => $data['contactid']]) == 0) {
                $sendEmail = false;
            }

            if ($sendEmail) {
                $form = $this->get_form_by_id($formid);
                // $admin == null ? [] : $_attachments - Admin opened form from admin area add the attachments to the email
                // send_mail_template($template, $form, $email, $admin == null ? [] : $_attachments, $cc);
            }

            hooks()->do_action('form_created', $formid);
            log_activity('New Form Created [ID: ' . $formid . ']');

            return $formid;
        }

        return false;
    }

    /**
     * Get latest 5 client forms
     * @param  integer $limit  Optional limit forms
     * @param  mixed $userid client id
     * @return array
     */
    public function get_client_latests_form($limit = 5, $userid = '')
    {
        $this->db->select(db_prefix() . 'forms.userid, formstatusid, statuscolor, ' . db_prefix() . 'forms_status.name as status_name,' . db_prefix() . 'forms.formid, subject, date');
        $this->db->from(db_prefix() . 'forms');
        $this->db->join(db_prefix() . 'forms_status', db_prefix() . 'forms_status.formstatusid = ' . db_prefix() . 'forms.status', 'left');
        if (is_numeric($userid)) {
            $this->db->where(db_prefix() . 'forms.userid', $userid);
        } else {
            $this->db->where(db_prefix() . 'forms.userid', get_client_user_id());
        }
        $this->db->limit($limit);
        $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);

        return $this->db->get()->result_array();
    }

    /**
     * Delete form from database and all connections
     * @param  mixed $formid formid
     * @return boolean
     */
    public function delete($formid)
    {
        $affectedRows = 0;
        hooks()->do_action('before_form_deleted', $formid);
        // final delete form
        $this->db->where('formid', $formid);
        $this->db->delete(db_prefix() . 'forms');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;

            $this->db->where('merged_form_id', $formid);
            $this->db->set('merged_form_id', null);
            $this->db->update(db_prefix() . 'forms');

            $this->db->where('formid', $formid);
            $attachments = $this->db->get(db_prefix() . 'form_attachments')->result_array();
            if (count($attachments) > 0) {
                if (is_dir(get_upload_path_by_type('form') . $formid)) {
                    if (delete_dir(get_upload_path_by_type('form') . $formid)) {
                        foreach ($attachments as $attachment) {
                            $this->db->where('id', $attachment['id']);
                            $this->db->delete(db_prefix() . 'form_attachments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }

            $this->db->where('relid', $formid);
            $this->db->where('fieldto', 'forms');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            // Delete replies
            $this->db->where('formid', $formid);
            $this->db->delete(db_prefix() . 'form_replies');

            $this->db->where('rel_id', $formid);
            $this->db->where('rel_type', 'form');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_id', $formid);
            $this->db->where('rel_type', 'form');
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'form');
            $this->db->where('rel_id', $formid);
            $this->db->delete(db_prefix() . 'reminders');

            // Get related tasks
            $this->db->where('rel_type', 'form');
            $this->db->where('rel_id', $formid);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
        }
        if ($affectedRows > 0) {
            log_activity('Form Deleted [ID: ' . $formid . ']');

            hooks()->do_action('after_form_deleted', $formid);

            return true;
        }

        return false;
    }

    /**
     * Update form data / admin use
     * @param  mixed $data form $_POST data
     * @return boolean
     */
    public function update_single_form_settings($data)
    {
        $affectedRows = 0;
        $data         = hooks()->apply_filters('before_form_settings_updated', $data);

        $formBeforeUpdate = $this->get_form_by_id($data['formid']);

        if (isset($data['merge_form_ids'])) {
            $forms = explode(',', $data['merge_form_ids']);
            if ($this->merge($data['formid'], $formBeforeUpdate->status, $forms)) {
                $affectedRows++;
            }
            unset($data['merge_form_ids']);
        }

        if (isset($data['custom_fields']) && count($data['custom_fields']) > 0) {
            if (handle_custom_fields_post($data['formid'], $data['custom_fields'])) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (handle_tags_save($tags, $data['formid'], 'form')) {
            $affectedRows++;
        }

        if (isset($data['priority']) && $data['priority'] == '' || !isset($data['priority'])) {
            $data['priority'] = 0;
        }

        if ($data['assigned'] == '') {
            $data['assigned'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '') {
            $data['project_id'] = 0;
        }

        if (isset($data['contactid']) && $data['contactid'] != '') {
            $data['name']  = null;
            $data['email'] = null;
        }

        if (empty($data['department'])) {
            $data['department'] = 0;
        }

        if (isset($data['contact_db_id'])) {
            unset($data['contact_db_id']);
        }

        if ($formBeforeUpdate->form_type == "dpr") {
            $dpr_form = array();
            $dpr_form['client_id'] = $data['client_id'];
            $dpr_form['pmc'] = $data['pmc'];
            $dpr_form['weather'] = $data['weather'];
            $dpr_form['consultant'] = $data['consultant'];
            $dpr_form['contractor'] = $data['contractor'];
            $dpr_form['work_stop'] = $data['work_stop'];
            unset($data['client_id']);
            unset($data['pmc']);
            unset($data['weather']);
            unset($data['consultant']);
            unset($data['contractor']);
            unset($data['work_stop']);
            unset($data['location']);
            unset($data['agency']);
            unset($data['type']);
            unset($data['work_execute']);
            unset($data['material_consumption']);
            unset($data['machinery']);
            unset($data['skilled']);
            unset($data['unskilled']);
            unset($data['depart']);
            unset($data['total']);
            unset($data['male']);
            unset($data['female']);
            $new_order = [];
            if (isset($data['newitems'])) {

                $new_order = $data['newitems'];
                unset($data['newitems']);
            }

            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }

            $remove_order = [];
            if (isset($data['removed_items'])) {
                $remove_order = $data['removed_items'];
                unset($data['removed_items']);
            }
        } elseif ($formBeforeUpdate->form_type == "apc") {
            $apc_form = [];
            $apc_form['date'] = $data['date'];
            $apc_form['location'] = $data['location'];
            $apc_form['inspected_by'] = $data['inspected_by'];
            unset($data['date']);
            unset($data['location']);
            unset($data['inspected_by']);
            unset($data['action']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "wpc") {
            $wpc_form = [];
            $wpc_form['date'] = $data['date'];
            $wpc_form['location'] = $data['location'];
            $wpc_form['inspected_by'] = $data['inspected_by'];
            unset($data['date']);
            unset($data['location']);
            unset($data['inspected_by']);
            unset($data['action']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "mfa") {
            $mfa_form = [];
            $mfa_form['checked_by'] = $data['checked_by'];
            $mfa_form['designation'] = $data['designation'];
            $mfa_form['location'] = $data['location'];
            $mfa_form['date'] = $data['date'];
            unset($data['checked_by']);
            unset($data['designation']);
            unset($data['location']);
            unset($data['date']);
            unset($data['action']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "mlg") {
            $mlg_form = [];
            $mlg_form['trade_of_work'] = $data['trade_of_work'];
            $mlg_form['name'] = $data['name'];
            $mlg_form['type'] = $data['type'];
            $mlg_form['lgno'] = $data['lgno'];
            $mlg_form['expiry_date'] = $data['expiry_date'];
            $mlg_form['swl'] = $data['swl'];
            $mlg_form['remarks'] = $data['remarks'];
            $mlg_form['date'] = $data['date'];
            unset($data['trade_of_work']);
            unset($data['name']);
            unset($data['type']);
            unset($data['lgno']);
            unset($data['expiry_date']);
            unset($data['swl']);
            unset($data['remarks']);
            unset($data['date']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "msh") {
            $msh_form = [];
            $msh_form['trade_of_work'] = $data['trade_of_work'];
            $msh_form['inspected_by'] = $data['inspected_by'];
            $msh_form['shi'] = $data['shi'];
            $msh_form['remarks'] = $data['remarks'];
            $msh_form['date'] = $data['date'];
            unset($data['trade_of_work']);
            unset($data['inspected_by']);
            unset($data['shi']);
            unset($data['remarks']);
            unset($data['date']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "sca") {
            $sca_form = [];
            $sca_form['area_of_work'] = $data['area_of_work'];
            $sca_form['scaffold_supervisor'] = $data['scaffold_supervisor'];
            $sca_form['date'] = $data['date'];
            unset($data['area_of_work']);
            unset($data['scaffold_supervisor']);
            unset($data['date']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        } elseif ($formBeforeUpdate->form_type == "esc") {
            $esc_form = [];
            $esc_form['date'] = $data['date'];
            $esc_form['location'] = $data['location'];
            $esc_form['inspected_by'] = $data['inspected_by'];
            unset($data['date']);
            unset($data['location']);
            unset($data['inspected_by']);
            unset($data['action']);
            $update_order = [];
            if (isset($data['items'])) {
                $update_order = $data['items'];
                unset($data['items']);
            }
        }
        $this->db->where('formid', $data['formid']);
        $this->db->update(db_prefix() . 'forms', $data);
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action(
                'form_settings_updated',
                [
                    'form_id'       => $data['formid'],
                    'original_form' => $formBeforeUpdate,
                    'data'            => $data,
                ]
            );
            $affectedRows++;
        }

        if ($formBeforeUpdate->form_type == "dpr") {
            if (isset($dpr_form)) {
                if (!empty($dpr_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $dpr_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($new_order)) {
                if (!empty($new_order)) {
                    foreach ($new_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['location'] = $value['location'];
                        $dt_data['agency'] = $value['agency'];
                        $dt_data['type'] = $value['type'];
                        $dt_data['work_execute'] = $value['work_execute'];
                        $dt_data['material_consumption'] = $value['material_consumption'];
                        $dt_data['machinery'] = $value['machinery'];
                        $dt_data['skilled'] = $value['skilled'];
                        $dt_data['unskilled'] = $value['unskilled'];
                        $dt_data['depart'] = $value['depart'];
                        $dt_data['total'] = $value['total'];
                        $dt_data['male'] = $value['male'];
                        $dt_data['female'] = $value['female'];
                        $this->db->insert(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        $new_insert_id = $this->db->insert_id();
                        if ($new_insert_id) {
                            $affectedRows++;
                        }
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['location'] = $value['location'];
                        $dt_data['agency'] = $value['agency'];
                        $dt_data['type'] = $value['type'];
                        $dt_data['work_execute'] = $value['work_execute'];
                        $dt_data['material_consumption'] = $value['material_consumption'];
                        $dt_data['machinery'] = $value['machinery'];
                        $dt_data['skilled'] = $value['skilled'];
                        $dt_data['unskilled'] = $value['unskilled'];
                        $dt_data['depart'] = $value['depart'];
                        $dt_data['total'] = $value['total'];
                        $dt_data['male'] = $value['male'];
                        $dt_data['female'] = $value['female'];
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }

            if (isset($remove_order)) {
                if (!empty($remove_order)) {
                    foreach ($remove_order as $key => $value) {
                        $this->db->where('id', $value);
                        if ($this->db->delete(db_prefix() . $formBeforeUpdate->form_type . '_form_detail')) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "apc") {
            if (isset($apc_form)) {
                if (!empty($apc_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $apc_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }


            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['items'] = $sr;
                        $dt_data['status'] = $value['status'];
                        $dt_data['remarks'] = $value['remarks'];
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() .  $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        // $insert_id = $this->db->insert_id();
                        // Handle file attachments dynamically for items and attachments_new

                        $iuploadedFiles = handle_ckecklist_item_attachment_array('apc_checklist', $data['formid'], $value['id'], 'items', $sr);
                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'apcattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }

                        $sr++;
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "wpc") {
            if (isset($wpc_form)) {
                if (!empty($wpc_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $wpc_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['items'] = $sr;
                        $dt_data['status'] = $value['status'];
                        $dt_data['remarks'] = $value['remarks'];
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() .  $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }

                        // Handle file attachments dynamically for items and attachments_new
                        $iuploadedFiles = handle_ckecklist_item_attachment_array('wpc_checklist', $data['formid'], $value['id'], 'items', $sr);

                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'wpcattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }
                        $sr++;
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "mfa") {
            if (isset($mfa_form)) {
                if (!empty($mfa_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $mfa_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['contents'] = $sr;
                        $dt_data['available_amount'] = $value['available_amount'] ?? null;
                        $dt_data['remarks'] = $value['remarks'] ?? null;
                        $dt_data['small'] =  $value['small'] ?? null;
                        $dt_data['medium'] = $value['medium'] ?? null;
                        $dt_data['large'] = $value['lagar'] ?? null;
                        $dt_data['10cm'] = $value['10cm'] ?? null;
                        $dt_data['5cm'] = $value['5cm'] ?? null;
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        $sr++;
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "mlg") {
            if (isset($mlg_form)) {
                if (!empty($mlg_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $mlg_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['description'] = $sr;
                        $dt_data['checks'] = $value['checks'] ?? null;
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        // Handle file attachments dynamically for items and attachments_new
                        $iuploadedFiles = handle_ckecklist_item_attachment_array('mlg_checklist', $data['formid'], $value['id'], 'items', $sr);

                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'mlgattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }
                        $sr++;
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "msh") {

            if (isset($msh_form)) {
                if (!empty($msh_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $msh_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['description'] = $sr;
                        $dt_data['checks'] = $value['checks'] ?? null;
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        // Handle file attachments dynamically for items and attachments_new
                        $iuploadedFiles = handle_ckecklist_item_attachment_array('msh_checklist', $data['formid'], $value['id'], 'items', $sr);

                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'mshattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }
                        $sr++;
                    }
                }
            }
        } elseif ($formBeforeUpdate->form_type == "sca") {

            if (isset($sca_form)) {
                if (!empty($sca_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $sca_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }

            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['description'] = $sr;
                        $dt_data['checks'] = $value['checks'] ?? null;
                        $dt_data['comments'] = $value['comments'] ?? null;
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        // Handle file attachments dynamically for items and attachments_new
                        $iuploadedFiles = handle_ckecklist_item_attachment_array('sca_checklist', $data['formid'], $value['id'], 'items', $sr);

                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'scaattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }
                        $sr++;
                    }
                }
            }
        }elseif ($formBeforeUpdate->form_type == "esc") {
            if (isset($esc_form)) {
                if (!empty($esc_form)) {
                    $this->db->where('form_id', $data['formid']);
                    $this->db->update(db_prefix() . $formBeforeUpdate->form_type . '_form', $esc_form);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }


            if (isset($update_order)) {
                if (!empty($update_order)) {
                    $sr = 1;
                    foreach ($update_order as $key => $value) {
                        $dt_data = [];
                        $dt_data['form_id'] = $data['formid'];
                        $dt_data['items'] = $sr;
                        $dt_data['status'] = $value['status'];
                        $dt_data['remarks'] = $value['remarks'];
                        $this->db->where('id', $value['id']);
                        $this->db->update(db_prefix() .  $formBeforeUpdate->form_type . '_form_detail', $dt_data);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                        // $insert_id = $this->db->insert_id();
                        // Handle file attachments dynamically for items and attachments_new

                        $iuploadedFiles = handle_ckecklist_item_attachment_array('esc_checklist', $data['formid'], $value['id'], 'items', $sr);
                        if ($iuploadedFiles && is_array($iuploadedFiles)) {
                            if (!empty($iuploadedFiles)) {
                                foreach ($iuploadedFiles as $file) {
                                    $idata = [
                                        'form_id' =>  $data['formid'],
                                        'form_detail_id' =>  $file['item_id'],
                                        'file_name' => $file['file_name'],
                                        'filetype' => $file['filetype'],
                                    ];
                                    $this->db->insert(db_prefix() . 'escattachments', $idata);
                                    $last_insert_id = $this->db->insert_id();

                                    if ($last_insert_id) {
                                        $affectedRows++;
                                    }
                                }
                            }
                        }

                        $sr++;
                    }
                }
            }
        }

        $sendAssignedEmail = false;

        $current_assigned = $formBeforeUpdate->assigned;
        if ($current_assigned != 0) {
            if ($current_assigned != $data['assigned']) {
                if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                    $sendAssignedEmail = true;
                    $notified          = add_notification([
                        'description'     => 'not_form_reassigned_to_you',
                        'touserid'        => $data['assigned'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'forms/form/' . $data['formid'],
                        'additional_data' => serialize([
                            $data['subject'],
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$data['assigned']]);
                    }
                }
            }
        } else {
            if ($data['assigned'] != 0 && $data['assigned'] != get_staff_user_id()) {
                $sendAssignedEmail = true;
                $notified          = add_notification([
                    'description'     => 'not_form_assigned_to_you',
                    'touserid'        => $data['assigned'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'forms/form/' . $data['formid'],
                    'additional_data' => serialize([
                        $data['subject'],
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$data['assigned']]);
                }
            }
        }
        if ($sendAssignedEmail === true) {
            $this->db->where('staffid', $data['assigned']);
            $assignedEmail = $this->db->get(db_prefix() . 'staff')->row()->email;

            // send_mail_template('form_assigned_to_staff', $assignedEmail, $data['assigned'], $data['formid'], $data['userid'], $data['contactid']);
        }
        if ($affectedRows > 0) {
            log_activity('Form Updated [ID: ' . $data['formid'] . ']');

            return true;
        }

        return false;
    }

    /**
     * C<ha></ha>nge form status
     * @param  mixed $id     formid
     * @param  mixed $status status id
     * @return array
     */
    public function change_form_status($id, $status)
    {
        $this->db->where('formid', $id);
        $this->db->update(db_prefix() . 'forms', [
            'status' => $status,
        ]);
        $alert   = 'warning';
        $message = _l('form_status_changed_fail');
        if ($this->db->affected_rows() > 0) {
            $alert   = 'success';
            $message = _l('form_status_changed_successfully');
            hooks()->do_action('after_form_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);
        }

        return [
            'alert'   => $alert,
            'message' => $message,
        ];
    }

    // Priorities

    /**
     * Get form priority by id
     * @param  mixed $id priority id
     * @return mixed     if id passed return object else array
     */
    public function get_priority($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('priorityid', $id);

            return $this->db->get(db_prefix() . 'forms_priorities')->row();
        }

        return $this->db->get(db_prefix() . 'forms_priorities')->result_array();
    }

    /**
     * Add new form priority
     * @param array $data form priority data
     */
    public function add_priority($data)
    {
        $this->db->insert(db_prefix() . 'forms_priorities', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Priority Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update form priority
     * @param  array $data form priority $_POST data
     * @param  mixed $id   form priority id
     * @return boolean
     */
    public function update_priority($data, $id)
    {
        $this->db->where('priorityid', $id);
        $this->db->update(db_prefix() . 'forms_priorities', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Priority Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete form priorit
     * @param  mixed $id form priority id
     * @return mixed
     */
    public function delete_priority($id)
    {
        $current = $this->get($id);
        // Check if the priority id is used in forms table
        if (is_reference_in_table('priority', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('priorityid', $id);
        $this->db->delete(db_prefix() . 'forms_priorities');
        if ($this->db->affected_rows() > 0) {
            if (get_option('email_piping_default_priority') == $id) {
                update_option('email_piping_default_priority', '');
            }
            log_activity('Form Priority Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Predefined replies

    /**
     * Get predefined reply  by id
     * @param  mixed $id predefined reply id
     * @return mixed if id passed return object else array
     */
    public function get_predefined_reply($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'forms_predefined_replies')->row();
        }

        return $this->db->get(db_prefix() . 'forms_predefined_replies')->result_array();
    }

    /**
     * Add new predefined reply
     * @param array $data predefined reply $_POST data
     */
    public function add_predefined_reply($data)
    {
        $this->db->insert(db_prefix() . 'forms_predefined_replies', $data);
        $insertid = $this->db->insert_id();
        log_activity('New Predefined Reply Added [ID: ' . $insertid . ', ' . $data['name'] . ']');

        return $insertid;
    }

    /**
     * Update predefined reply
     * @param  array $data predefined $_POST data
     * @param  mixed $id   predefined reply id
     * @return boolean
     */
    public function update_predefined_reply($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'forms_predefined_replies', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Updated [ID: ' . $id . ', ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete predefined reply
     * @param  mixed $id predefined reply id
     * @return boolean
     */
    public function delete_predefined_reply($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'forms_predefined_replies');
        if ($this->db->affected_rows() > 0) {
            log_activity('Predefined Reply Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    // Form statuses

    /**
     * Get form status by id
     * @param  mixed $id status id
     * @return mixed     if id passed return object else array
     */
    public function get_form_status($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('formstatusid', $id);

            return $this->db->get(db_prefix() . 'forms_status')->row();
        }
        $this->db->order_by('statusorder', 'asc');

        return $this->db->get(db_prefix() . 'forms_status')->result_array();
    }

    /**
     * Add new form status
     * @param array form status $_POST data
     * @return mixed
     */
    public function add_form_status($data)
    {
        $this->db->insert(db_prefix() . 'forms_status', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Status Added [ID: ' . $insert_id . ', ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update form status
     * @param  array $data form status $_POST data
     * @param  mixed $id   form status id
     * @return boolean
     */
    public function update_form_status($data, $id)
    {
        $this->db->where('formstatusid', $id);
        $this->db->update(db_prefix() . 'forms_status', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Status Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete form status
     * @param  mixed $id form status id
     * @return mixed
     */
    public function delete_form_status($id)
    {
        $current = $this->get_form_status($id);
        // Default statuses cant be deleted
        if ($current->isdefault == 1) {
            return [
                'default' => true,
            ];
            // Not default check if if used in table
        } elseif (is_reference_in_table('status', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('formstatusid', $id);
        $this->db->delete(db_prefix() . 'forms_status');
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Status Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    // Form services
    public function get_service($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('serviceid', $id);

            return $this->db->get(db_prefix() . 'services')->row();
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'services')->result_array();
    }

    public function add_service($data)
    {
        $this->db->insert(db_prefix() . 'services', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Form Service Added [ID: ' . $insert_id . '.' . $data['name'] . ']');
        }

        return $insert_id;
    }

    public function update_service($data, $id)
    {
        $this->db->where('serviceid', $id);
        $this->db->update(db_prefix() . 'services', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Service Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_service($id)
    {
        if (is_reference_in_table('service', db_prefix() . 'forms', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('serviceid', $id);
        $this->db->delete(db_prefix() . 'services');
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Service Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @return array
     * Used in home dashboard page
     * Displays weekly form openings statistics (chart)
     */
    public function get_weekly_forms_opening_statistics()
    {
        $departments_ids = [];
        if (!is_admin()) {
            if (get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
            }
        }

        $chart = [
            'labels'   => get_weekdays(),
            'datasets' => [
                [
                    'label'           => _l('home_weekend_form_opening_statistics'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                ],
            ],
        ];

        $monday = new DateTime(date('Y-m-d', strtotime('monday this week')));
        $sunday = new DateTime(date('Y-m-d', strtotime('sunday this week')));

        $thisWeekDays = get_weekdays_between_dates($monday, $sunday);

        $byDepartments = count($departments_ids) > 0;
        if (isset($thisWeekDays[1])) {
            $i = 0;
            foreach ($thisWeekDays[1] as $weekDate) {
                $this->db->like('DATE(date)', $weekDate, 'after');
                $this->db->where(db_prefix() . 'forms.merged_form_id IS NULL', null, false);
                if ($byDepartments) {
                    $this->db->where('department IN (SELECT departmentid FROM ' . db_prefix() . 'staff_departments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")');
                }
                $chart['datasets'][0]['data'][$i] = $this->db->count_all_results(db_prefix() . 'forms');

                $i++;
            }
        }

        return $chart;
    }

    public function get_forms_assignes_disctinct()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as assigned FROM ' . db_prefix() . 'forms WHERE assigned != 0 AND merged_form_id IS NULL')->result_array();
    }

    /**
     * Check for previous forms opened by this email/contact and link to the contact
     * @param  string $email      email to check for
     * @param  mixed $contact_id the contact id to transfer the forms
     * @return boolean
     */
    public function transfer_email_forms_to_contact($email, $contact_id)
    {
        // Some users don't want to fill the email
        if (empty($email)) {
            return false;
        }

        $customer_id = get_user_id_by_contact_id($contact_id);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'forms', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        $this->db->where('userid', 0)
            ->where('contactid', 0)
            ->where('admin IS NULL')
            ->where('email', $email);

        $this->db->update(db_prefix() . 'form_replies', [
            'email'     => null,
            'name'      => null,
            'userid'    => $customer_id,
            'contactid' => $contact_id,
        ]);

        return true;
    }

    /**
     * Check whether the given formid is already merged into another primary form
     *
     * @param  int  $id
     *
     * @return boolean
     */
    public function is_merged($id)
    {
        return total_rows('forms', "formid={$id} and merged_form_id IS NOT NULL") > 0;
    }

    /**
     * @param $primary_form_id
     * @param $status
     * @param  array  $ids
     *
     * @return bool
     */
    public function merge($primary_form_id, $status, array $ids)
    {
        if ($this->is_merged($primary_form_id)) {
            return false;
        }

        if (($index = array_search($primary_form_id, $ids)) !== false) {
            unset($ids[$index]);
        }

        if (count($ids) == 0) {
            return false;
        }

        return (new MergeForms($primary_form_id, $ids))
            ->markPrimaryFormAs($status)
            ->merge();
    }

    /**
     * @param array $forms id's of forms to check
     * @return array
     */
    public function get_already_merged_forms($forms)
    {
        if (count($forms) === 0) {
            return [];
        }

        $alreadyMerged = [];
        foreach ($forms as $formId) {
            if ($this->is_merged((int) $formId)) {
                $alreadyMerged[] = $formId;
            }
        }

        return $alreadyMerged;
    }

    /**
     * @param $primaryFormId
     * @return array
     */
    public function get_merged_forms_by_primary_id($primaryFormId)
    {
        return $this->db->where('merged_form_id', $primaryFormId)->get(db_prefix() . 'forms')->result_array();
    }

    public function update_staff_replying($formId, $userId = '')
    {
        $form = $this->get($formId);

        if ($userId === '') {
            return $this->db->where('formid', $formId)
                ->set('staff_id_replying', null)
                ->update(db_prefix() . 'forms');
        }

        if ($form->staff_id_replying !== $userId && !is_null($form->staff_id_replying)) {
            return false;
        }

        if ($form->staff_id_replying === $userId) {
            return true;
        }

        return $this->db->where('formid', $formId)
            ->set('staff_id_replying', $userId)
            ->update(db_prefix() . 'forms');
    }

    public function get_staff_replying($formId)
    {
        $this->db->select('formid,staff_id_replying');
        $this->db->where('formid', $formId);

        return $this->db->get(db_prefix() . 'forms')->row();
    }

    private function getStaffMembersForFormNotification($department, $assignedStaff = 0)
    {
        $this->load->model('departments_model');
        $this->load->model('staff_model');

        $staffToNotify = [];
        if ($assignedStaff != 0 && get_option('staff_related_form_notification_to_assignee_only') == 1) {
            $member = $this->staff_model->get($assignedStaff, ['active' => 1]);
            if ($member) {
                $staffToNotify[] = (array) $member;
            }
        } else {
            $staff = $this->staff_model->get('', ['active' => 1]);
            foreach ($staff as $member) {
                if (get_option('access_forms_to_none_staff_members') == 0 && !is_staff_member($member['staffid'])) {
                    continue;
                }
                $staff_departments = $this->departments_model->get_staff_departments($member['staffid'], true);
                if (in_array($department, $staff_departments)) {
                    $staffToNotify[] = $member;
                }
            }
        }

        return $staffToNotify;
    }

    public function find_project_contact($project_id)
    {
        $this->db->select(db_prefix() . 'contacts.id as id, ' . db_prefix() . 'contacts.userid as userid, CONCAT(firstname," ",lastname) AS full_name', FALSE);
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.clientid = ' . db_prefix() . 'contacts.userid', 'left');
        $this->db->where(db_prefix() . 'projects.id', $project_id);
        $contacts = $this->db->get(db_prefix() . 'contacts')->result_array();
        return $contacts;
    }

    /**
     * Creates a Daily Progress Report row template.
     *
     * @param      array   $unit_data  The unit data
     * @param      string  $name       The name
     */
    public function create_dpr_row_template($name = '', $location = '', $agency = '', $type = '', $work_execute = '', $material_consumption = '', $machinery = '', $skilled = '', $unskilled = '', $depart = '', $total = '', $male = '', $female = '', $is_edit = false, $item_key = '')
    {
        $row = '';

        $name_location = 'location';
        $name_agency = 'agency';
        $name_type = 'type';
        $name_work_execute = 'work_execute';
        $name_material_consumption = 'material_consumption';
        $name_machinery = 'machinery';
        $name_skilled = 'skilled';
        $name_unskilled = 'unskilled';
        $name_depart = 'depart';
        $name_total = 'total';
        $name_male = 'male';
        $name_female = 'female';

        if ($name == '') {
            $row .= '<tr class="main">';
            $manual = true;
        } else {
            $manual = false;
            $row .= '<tr><input type="hidden" class="ids" name="' . $name . '[id]" value="' . $item_key . '">';
            $name_location = $name . '[location]';
            $name_agency = $name . '[agency]';
            $name_type = $name . '[type]';
            $name_work_execute = $name . '[work_execute]';
            $name_material_consumption = $name . '[material_consumption]';
            $name_machinery = $name . '[machinery]';
            $name_skilled = $name . '[skilled]';
            $name_unskilled = $name . '[unskilled]';
            $name_depart = $name . '[depart]';
            $name_total = $name . '[total]';
            $name_male = $name . '[male]';
            $name_female = $name . '[female]';
        }

        $skilled = !empty($skilled) ? $skilled : 0;
        $unskilled = !empty($unskilled) ? $unskilled : 0;
        $depart = !empty($depart) ? $depart : 0;
        $total = !empty($total) ? $total : 0;
        $male = !empty($male) ? $male : 0;
        $female = !empty($female) ? $female : 0;

        $row .= '<td class="location">' . render_input($name_location, '', $location) . '</td>';
        $row .= '<td class="agency">' . get_vendor($name_agency, $agency) . '</td>';
        $row .= '<td class="laber-type">' . get_laber_type_listing($name_type, $type) . '</td>';
        $row .= '<td class="work_execute">' . render_input($name_work_execute, '', $work_execute) . '</td>';
        $row .= '<td class="material_consumption">' . render_input($name_material_consumption, '', $material_consumption) . '</td>';
        $row .= '<td class="machinery">' . render_input($name_machinery, '', $machinery) . '</td>';
        $row .= '<td class="skilled">' . render_input($name_skilled, '', $skilled, 'nubmer') . '</td>';
        $row .= '<td class="unskilled">' . render_input($name_unskilled, '', $unskilled, 'nubmer') . '</td>';
        $row .= '<td class="depart">' . render_input($name_depart, '', $depart, 'nubmer') . '</td>';
        $row .= '<td class="total">' . render_input($name_total, '', $total, 'nubmer') . '</td>';
        $row .= '<td class="male">' . render_input($name_male, '', $male, 'nubmer') . '</td>';
        $row .= '<td class="female">' . render_input($name_female, '', $female, 'nubmer') . '</td>';

        if ($name == '') {
            $row .= '<td><button type="button" class="btn pull-right btn-info dpr-add-item-to-table"><i class="fa fa-check"></i></button></td>';
        } else {
            $row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="dpr_delete_item(this,' . $item_key . ',\'.invoice-item\'); return false;"><i class="fa fa-trash"></i></a></td>';
        }

        $row .= '</tr>';
        return $row;
    }

    public function get_dpr_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_form')->row();
    }

    public function get_dpr_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'dpr_form_detail')->result_array();
    }


    public function get_apc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'apc_form')->row();
    }

    public function get_apc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'apc_form_detail')->result_array();
    }
    public function get_wpc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'wpc_form')->row();
    }

    public function get_wpc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'wpc_form_detail')->result_array();
    }
    public function get_mfa_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mfa_form')->row();
    }
    public function get_mfa_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mfa_form_detail')->result_array();
    }
    public function get_mlg_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mlg_form')->row();
    }

    public function get_mlg_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'mlg_form_detail')->result_array();
    }
    public function get_apc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'apcattachments')->result_array();
    }
    public function get_esc_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'esc_form')->row();
    }
    public function get_esc_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'esc_form_detail')->result_array();
    }
    public function get_esc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'escattachments')->result_array();
    }
    public function delete_apc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'apcattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'apc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'apcattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function get_msh_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'msh_form')->row();
    }
    public function get_msh_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'msh_form_detail')->result_array();
    }
    public function get_msh_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'mshattachments')->result_array();
    }
    public function get_sca_form($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'sca_form')->row();
    }
    public function get_sca_form_detail($form_id)
    {
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'sca_form_detail')->result_array();
    }
    public function get_sca_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'scaattachments')->result_array();
    }
    public function get_mlg_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'mlgattachments')->result_array();
    }

    public function get_wpc_form_attachments($id)
    {
        $this->db->where('form_id', $id);
        return $this->db->get(db_prefix() . 'wpcattachments')->result_array();
    }
    public function delete_wpc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'wpcattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'wpc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'wpcattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_msh_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'mshattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'msh_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'mshattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_sca_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'scaattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'sca_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'scaattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_mlg_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'mlgattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'mlg_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'mlgattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function delete_esc_attachment($id)
    {
        // Fetch the file details from the database
        $this->db->where('id', $id);
        $attachment = $this->db->get(db_prefix() . 'escattachments')->row();

        if ($attachment) {
            // Construct the file path
            $file_path = get_upload_path_by_type('form') . 'esc_checklist/' . $attachment->form_id . '/' . $attachment->form_detail_id . '/' . $attachment->file_name;

            // Check if the file exists and unlink it
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete the attachment record from the database
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'escattachments');

            if ($this->db->affected_rows() > 0) {
                set_alert('success', 'Attachment deleted successfully.');
            } else {
                set_alert('warning', 'Attachment could not be deleted.');
            }
        } else {
            set_alert('warning', 'Attachment not found.');
        }

        // Redirect back to the previous page or list
        redirect($_SERVER['HTTP_REFERER']);
    }
}
