<?php defined('BASEPATH') or exit('No direct script access allowed');
class Cash_flow_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_expenses_total($data)
    {
        $this->load->model('currencies_model');
        $base_currency = $this->currencies_model->get_base_currency()->id;
        $currency_switcher = false;
        $spent_query = $this->db
            ->select('SUM(amount) AS spent_amount')
            ->where([
                'operation' => 'Cash-out',
                'buisness_id' => $data['buisness_id']
            ])
            ->get(db_prefix() . 'cf_expenses');
        $spent_result = $spent_query->row();
        $spent_total = $spent_result ? $spent_result->spent_amount : 0;
        $received_query = $this->db
            ->select('SUM(amount) AS received_amount')
            ->where(['operation' => 'Cash-in', 'buisness_id' => $data['buisness_id']])
            ->get(db_prefix() . 'cf_expenses');
        $received_result = $received_query->row();
        $received_total = $received_result ? $received_result->received_amount : 0;
        $revenue_total = $received_total - $spent_total;
        $currencyid = $base_currency;
        $currency = get_currency($currencyid);
        $_result = [
            'spent' => app_format_money($spent_total, $currency),
            'received' => app_format_money($received_total, $currency),
            'revenue' => app_format_money($revenue_total, $currency),
        ];

        $_result['currency_switcher'] = $currency_switcher;
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    function get_buisness_type()
    {
        if (!is_admin()) {
            $this->db->select('cf_buisness_types.*');
            $this->db->from(db_prefix() . 'cf_buisness_types');
            $this->db->join(db_prefix() . 'cf_buisness_assignee', 'cf_buisness_types.id = cf_buisness_assignee.buisness_id');
            $this->db->where('cf_buisness_assignee.assignee_id', get_staff_user_id());
            return $this->db->get()->result_array();
        } else {
            return $this->db->get(db_prefix() . 'cf_buisness_types')->result_array();
        }
    }

    public function add_buisness_type($data)
    {
        $this->db->insert(db_prefix() . 'cf_buisness_types', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Buisness Type Added [' . $data['buisness_name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Edit contract type
     * @param mixed $data All $_POST data
     * @param mixed $id Contract type id
     */
    public function update_buisness_type($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'cf_buisness_types', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Buisness Type Updated [' . $data['buisness_name'] . ', ID:' . $id . ']');

            return true;
        }

        return false;
    }



    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'cf_expenses.id as id,' . db_prefix() . 'expenses_categories.name as category_name,' . db_prefix() . 'payment_modes.name as payment_mode_name,' . db_prefix() . 'taxes.name as tax_name, ' . db_prefix() . 'taxes.taxrate as taxrate,' . db_prefix() . 'taxes_2.name as tax_name2, ' . db_prefix() . 'taxes_2.taxrate as taxrate2, ' . db_prefix() . 'cf_expenses.id as expenseid,' . db_prefix() . 'cf_expenses.addedfrom as addedfrom, recurring_from');
        $this->db->from(db_prefix() . 'cf_expenses');
        $this->db->join(db_prefix() . 'clients', '' . db_prefix() . 'clients.userid = ' . db_prefix() . 'cf_expenses.clientid', 'left');
        $this->db->join(db_prefix() . 'payment_modes', '' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'cf_expenses.paymentmode', 'left');
        $this->db->join(db_prefix() . 'taxes', '' . db_prefix() . 'taxes.id = ' . db_prefix() . 'cf_expenses.tax', 'left');
        $this->db->join('' . db_prefix() . 'taxes as ' . db_prefix() . 'taxes_2', '' . db_prefix() . 'taxes_2.id = ' . db_prefix() . 'cf_expenses.tax2', 'left');
        $this->db->join(db_prefix() . 'expenses_categories', '' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'cf_expenses.category');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'cf_expenses.id', $id);
            $expense = $this->db->get()->row();
            if ($expense) {
                $expense->attachment = '';
                $expense->filetype = '';
                $expense->attachment_added_from = 0;

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'cashflow_expense');
                $file = $this->db->get(db_prefix() . 'files')->row();

                if ($file) {
                    $expense->attachment = $file->file_name;
                    $expense->filetype = $file->filetype;
                    $expense->attachment_added_from = $file->staffid;
                }

                $this->load->model('projects_model');
                $expense->currency_data = get_currency($expense->currency);
                if ($expense->project_id) {
                    $expense->project_data = $this->projects_model->get($expense->project_id);
                }

                if (is_null($expense->payment_mode_name)) {
                    // is online payment mode
                    $this->load->model('payment_modes_model');
                    $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
                    foreach ($payment_gateways as $gateway) {
                        if ($expense->paymentmode == $gateway['id']) {
                            $expense->payment_mode_name = $gateway['name'];
                        }
                    }
                }
            }

            return $expense;
        }
        $this->db->order_by('date', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Add new expense
     * @param mixed $data All $_POST data
     * @return  mixed
     */
    public function add($data)
    {

        $buisness_id = $data['buisness_id'];
        // print_r( $buisness_id); die;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }
        if (isset($data['create_invoice_billable'])) {
            $data['create_invoice_billable'] = 1;
        } else {
            $data['create_invoice_billable'] = 0;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['send_invoice_to_customer'])) {
            $data['send_invoice_to_customer'] = 1;
        } else {
            $data['send_invoice_to_customer'] = 0;
        }

        if (isset($data['repeat_every']) && $data['repeat_every'] != '') {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every'] = $data['repeat_every_custom'];
                $data['recurring_type'] = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                $_temp = explode('-', $data['repeat_every']);
                $data['recurring_type'] = $_temp[1];
                $data['repeat_every'] = $_temp[0];
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring'] = 0;
        }
        unset($data['repeat_type_custom']);
        unset($data['repeat_every_custom']);

        if ((isset($data['project_id']) && $data['project_id'] == '') || !isset($data['project_id'])) {
            $data['project_id'] = 0;
        }
        $data['addedfrom'] = get_staff_user_id();
        $data['dateadded'] = date('Y-m-d H:i:s');

        $data = hooks()->apply_filters('before_expense_added', $data);

        $this->db->insert(db_prefix() . 'cf_expenses', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }
            if (isset($data['project_id']) && !empty($data['project_id'])) {
                $this->load->model('projects_model');
                $project_settings = $this->projects_model->get_project_settings($data['project_id']);
                $visible_activity = 0;
                foreach ($project_settings as $s) {
                    if ($s['name'] == 'view_finance_overview') {
                        if ($s['value'] == 1) {
                            $visible_activity = 1;

                            break;
                        }
                    }
                }
                $expense = $this->get($insert_id);
                $activity_additional_data = $expense->name;
                $this->projects_model->log_activity($data['project_id'], 'project_activity_recorded_expense', $activity_additional_data, $visible_activity);
            }
            $received_amount = $this->db->select_sum('amount')->where(['operation' => 'cash-in', 'buisness_id' => $buisness_id])->get(db_prefix() . 'cf_expenses')->row()->amount ?? 0;
            $spent_amount = $this->db->select_sum('amount')->where(['operation' => 'cash-out', 'buisness_id' => $buisness_id])->get(db_prefix() . 'cf_expenses')->row()->amount ?? 0;

            $balance = $received_amount - $spent_amount;

            // Update the balance for this expense
            $this->db->where('id', $insert_id);
            $this->db->update(db_prefix() . 'cf_expenses', ['balance' => $balance]);

            log_activity('New Expense Added [' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function get_child_expenses($id)
    {
        $this->db->select('id');
        $this->db->where('recurring_from', $id);
        $expenses = $this->db->get(db_prefix() . 'cf_expenses')->result_array();

        $_expenses = [];
        foreach ($expenses as $expense) {
            $_expenses[] = $this->get($expense['id']);
        }

        return $_expenses;
    }
    public function update($data, $id)
    {
        $this->db->trans_begin();
        $original_expense = $this->get($id);
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        $buisness_id = $data['buisness_id'];
        if (!empty($original_expense->repeat_every) && empty($data['repeat_every'])) {
            $data['cycles'] = 0;
            $data['total_cycles'] = 0;
            $data['last_recurring_date'] = null;
        }
        if (!empty($data['repeat_every'])) {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every'] = $data['repeat_every_custom'];
                $data['recurring_type'] = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                list($data['repeat_every'], $data['recurring_type']) = explode('-', $data['repeat_every']);
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring'] = 0;
        }
        $data['cycles'] = $data['recurring'] == 0 ? 0 : ($data['cycles'] ?? 0);
        unset($data['repeat_type_custom'], $data['repeat_every_custom']);
        $data['create_invoice_billable'] = !empty($data['create_invoice_billable']) ? 1 : 0;
        $data['billable'] = !empty($data['billable']) ? 1 : 0;
        $data['send_invoice_to_customer'] = !empty($data['send_invoice_to_customer']) ? 1 : 0;
        $data['project_id'] = $data['project_id'] ?? 0;
        $data = hooks()->apply_filters('before_expense_updated', $data, $id);
        $custom_fields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);
        $updated = handle_custom_fields_post($id, $custom_fields);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'cf_expenses', $data);
        $received_amount = $this->db->select_sum('amount')->where(['operation' => 'cash-in', 'buisness_id' => $buisness_id])->get(db_prefix() . 'cf_expenses')->row()->amount ?? 0;
        $spent_amount = $this->db->select_sum('amount')->where(['operation' => 'cash-out', 'buisness_id' => $buisness_id])->get(db_prefix() . 'cf_expenses')->row()->amount ?? 0;

        $balance = $received_amount - $spent_amount;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'cf_expenses', ['balance' => $balance]);
        if ($this->db->affected_rows() > 0) {
            $updated = true;
        }
        do_action_deprecated('after_expense_updated', [$id], '2.9.4', 'expense_updated');
        hooks()->do_action('expense_updated', [
            'id' => $id,
            'data' => $data,
            'custom_fields' => $custom_fields,
            'updated' => &$updated,
        ]);

        if ($updated) {
            log_activity('Cash Flow Updated [' . $id . ']');
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return $updated;
        }
    }

    public function delete($id, $simpleDelete = false)
    {
        $_expense = $this->get($id);

        if ($_expense->invoiceid !== null && $simpleDelete == false) {
            return [
                'invoiced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'cf_expenses');

        if ($this->db->affected_rows() > 0) {
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'expenses');
            $this->db->delete(db_prefix() . 'customfieldsvalues');
            $this->db->where('rel_type', 'cf_expense');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }

            $this->delete_expense_attachment($id);

            $this->db->where('recurring_from', $id);
            $this->db->update(db_prefix() . 'cf_expenses', ['recurring_from' => null]);

            $this->db->where('rel_type', 'cashflow_expense');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'cashflow_expense');
            $this->db->delete(db_prefix() . 'related_items');

            log_activity('Cash Flow Expense Deleted [' . $id . ']');


            return true;
        }

        return false;
    }
    public function convert_to_invoice($id, $draft_invoice = false, $params = [])
    {
        $expense = $this->get($id);
        $new_invoice_data = [];
        $client = $this->clients_model->get($expense->clientid);

        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }
        $new_invoice_data['clientid'] = $expense->clientid;
        $new_invoice_data['number'] = get_option('next_invoice_number');
        $invoice_date = (isset($params['invoice_date']) ? $params['invoice_date'] : date('Y-m-d'));
        $new_invoice_data['date'] = _d($invoice_date);

        if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime($invoice_date))));
        }

        $new_invoice_data['show_quantity_as'] = 1;
        $new_invoice_data['terms'] = get_option('predefined_terms_invoice');
        $new_invoice_data['clientnote'] = get_option('predefined_clientnote_invoice');
        $new_invoice_data['discount_total'] = 0;
        $new_invoice_data['sale_agent'] = 0;
        $new_invoice_data['adjustment'] = 0;
        $new_invoice_data['project_id'] = $expense->project_id;

        $new_invoice_data['subtotal'] = $expense->amount;
        $total = $expense->amount;

        if ($expense->tax != 0) {
            $total += ($expense->amount / 100 * $expense->taxrate);
        }
        if ($expense->tax2 != 0) {
            $total += ($expense->amount / 100 * $expense->taxrate2);
        }

        $new_invoice_data['total'] = $total;
        $new_invoice_data['currency'] = $expense->currency;
        $new_invoice_data['status'] = 1;
        $new_invoice_data['adminnote'] = '';
        $new_invoice_data['billing_street'] = clear_textarea_breaks($client->billing_street);
        $new_invoice_data['billing_city'] = $client->billing_city;
        $new_invoice_data['billing_state'] = $client->billing_state;
        $new_invoice_data['billing_zip'] = $client->billing_zip;
        $new_invoice_data['billing_country'] = $client->billing_country;
        if (!empty($client->shipping_street)) {
            $new_invoice_data['shipping_street'] = clear_textarea_breaks($client->shipping_street);
            $new_invoice_data['shipping_city'] = $client->shipping_city;
            $new_invoice_data['shipping_state'] = $client->shipping_state;
            $new_invoice_data['shipping_zip'] = $client->shipping_zip;
            $new_invoice_data['shipping_country'] = $client->shipping_country;
            $new_invoice_data['include_shipping'] = 1;
            $new_invoice_data['show_shipping_on_invoice'] = 1;
        } else {
            $new_invoice_data['include_shipping'] = 0;
            $new_invoice_data['show_shipping_on_invoice'] = 1;
        }

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }

        $new_invoice_data['billed_expenses'][1] = [
            $expense->expenseid,
        ];
        $new_invoice_data['allowed_payment_modes'] = $temp_modes;
        $new_invoice_data['newitems'][1]['description'] = _l('item_as_expense') . ' ' . $expense->name;
        $new_invoice_data['newitems'][1]['long_description'] = $expense->description;

        if (isset($params['include_note']) && $params['include_note'] == true && !empty($expense->note)) {
            $new_invoice_data['newitems'][1]['long_description'] .= PHP_EOL . $expense->note;
        }
        if (isset($params['include_name']) && $params['include_name'] == true && !empty($expense->expense_name)) {
            $new_invoice_data['newitems'][1]['long_description'] .= PHP_EOL . $expense->expense_name;
        }

        $new_invoice_data['newitems'][1]['unit'] = '';
        $new_invoice_data['newitems'][1]['qty'] = 1;
        $new_invoice_data['newitems'][1]['taxname'] = [];
        if ($expense->tax != 0) {
            $tax_data = get_tax_by_id($expense->tax);
            array_push($new_invoice_data['newitems'][1]['taxname'], $tax_data->name . '|' . $tax_data->taxrate);
        }
        if ($expense->tax2 != 0) {
            $tax_data = get_tax_by_id($expense->tax2);
            array_push($new_invoice_data['newitems'][1]['taxname'], $tax_data->name . '|' . $tax_data->taxrate);
        }

        $new_invoice_data['newitems'][1]['rate'] = $expense->amount;
        $new_invoice_data['newitems'][1]['order'] = 1;
        $this->load->model('invoices_model');

        $invoiceid = $this->invoices_model->add($new_invoice_data, true);
        if ($invoiceid) {
            $this->db->where('id', $expense->expenseid);
            $this->db->update(db_prefix() . 'expenses', [
                'invoiceid' => $invoiceid,
            ]);

            if (is_custom_fields_smart_transfer_enabled()) {
                $this->db->where('fieldto', 'expenses');
                $this->db->where('active', 1);
                $cfExpenses = $this->db->get(db_prefix() . 'customfields')->result_array();
                foreach ($cfExpenses as $field) {
                    $tmpSlug = explode('_', $field['slug'], 2);
                    if (isset($tmpSlug[1])) {
                        $this->db->where('fieldto', 'invoice');
                        $this->db->group_start();
                        $this->db->like('slug', 'invoice_' . $tmpSlug[1], 'after');
                        $this->db->where('type', $field['type']);
                        $this->db->where('options', $field['options']);
                        $this->db->where('active', 1);
                        $this->db->group_end();
                        $cfTransfer = $this->db->get(db_prefix() . 'customfields')->result_array();
                        if (count($cfTransfer) == 1 && ((similarity($field['name'], $cfTransfer[0]['name']) * 100) >= CUSTOM_FIELD_TRANSFER_SIMILARITY)) {
                            $value = get_custom_field_value($id, $field['id'], 'expenses', false);
                            if ($value == '') {
                                continue;
                            }
                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid' => $invoiceid,
                                'fieldid' => $cfTransfer[0]['id'],
                                'fieldto' => 'invoice',
                                'value' => $value,
                            ]);
                        }
                    }
                }
            }

            log_activity('Expense Converted To Invoice [ExpenseID: ' . $expense->expenseid . ', InvoiceID: ' . $invoiceid . ']');

            hooks()->do_action('expense_converted_to_invoice', ['expense_id' => $expense->expenseid, 'invoice_id' => $invoiceid]);

            return $invoiceid;
        }

        return false;
    }
    public function copy($id)
    {
        $expense_fields = $this->db->list_fields(db_prefix() . 'cf_expenses');
        $expense = $this->get($id);
        $new_expense_data = [];
        foreach ($expense_fields as $field) {
            if (isset($expense->$field)) {
                if ($field != 'invoiceid' && $field != 'id' && $field != 'recurring_from') {
                    $new_expense_data[$field] = $expense->$field;
                }
            }
        }
        $new_expense_data['addedfrom'] = get_staff_user_id();
        $new_expense_data['dateadded'] = date('Y-m-d H:i:s');
        $new_expense_data['last_recurring_date'] = null;
        $new_expense_data['total_cycles'] = 0;

        $this->db->insert(db_prefix() . 'cf_expenses', $new_expense_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $custom_fields = get_custom_fields('expenses');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($id, $field['id'], 'expenses', false);
                if ($value == '') {
                    continue;
                }
                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid' => $insert_id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'expenses',
                    'value' => $value,
                ]);
            }
            log_activity('Cash Flow Expense Copied [ExpenseID' . $id . ', NewExpenseID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }
    public function delete_expense_attachment($id)
    {
        if (is_dir(get_upload_path_by_type('expense') . $id)) {
            if (delete_dir(get_upload_path_by_type('expense') . $id)) {
                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'cashflow_expense');
                $this->db->delete(db_prefix() . 'files');
                log_activity(' Cash Flow Expense Receipt Deleted [ExpenseID: ' . $id . ']');

                return true;
            }
        }

        return false;
    }

    public function get_category($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'expenses_categories')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'expenses_categories')->result_array();
    }
    public function add_category($data)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->insert(db_prefix() . 'expenses_categories', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Expense Category Added [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }
    public function update_category($data, $id)
    {
        $data['description'] = nl2br($data['description']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'expenses_categories', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Category Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete_category($id)
    {
        if (is_reference_in_table('category', db_prefix() . 'cf_expenses', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expenses_categories');
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Category Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_expenses_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'expenses ORDER by year DESC')->result_array();
    }

    public function delete_bsns_type($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'cf_buisness_types');
        if ($this->db->affected_rows() > 0) {
            log_activity('Buisness Deleted [' . $id . ']');
            return true;
        }

        return false;
    }

    public function add_buisness_assignee($data)
    {
        $query = $this->db->insert(db_prefix() . 'cf_buisness_assignee', $data);
        if ($query) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }
    public function delete_buisness_assignees($deleted_id)
    {
        $this->db->where('buisness_id	', $deleted_id);
        return $this->db->delete(db_prefix() . 'cf_buisness_assignee');
    }
}