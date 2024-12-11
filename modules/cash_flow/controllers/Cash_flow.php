<?php defined('BASEPATH') or exit('No direct script access allowed');
use app\services\utilities\Arr;
class Cash_flow extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cash_flow_model');
    }

    public function index()
    {
        $data['title'] = _l('buisness_types');
        $data['companies'] = $this->cash_flow_model->get_buisness_type();

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('cash_flow', 'admin/business_expense_total'));
        }
        $data['staffs'] = $this->staff_model->get();
        $this->load->view('admin/cf_buisnesses', $data);

    }

    public function buisness_type($id = '')
    {
        if ($this->input->post()) {

            $postData=$this->input->post();

            if (!$this->input->post('id')) {

                $buisness_data = [
                    'buisness_name' => $postData['buisness_name'],
                    'buisness_color' => $postData['buisness_color'],
                    'added_at' => date("d-m-y h:i:s"),
                    'updated_at' => date("d-m-y h:i:s")
                ];

                $id = $this->cash_flow_model->add_buisness_type($buisness_data);
                if ($id) {
                    foreach ($postData['assignees'] as $assignee) {
                        $assignee_data = [
                            'assignee_id' => $assignee,
                            'buisness_id' => $id,
                        ];
                        $this->cash_flow_model->add_buisness_assignee($assignee_data);
                    }
                    $success = true;
                    $message = _l('added_successfully', _l('buisness_type'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id' => $id,
                    'name' => $this->input->post('buisness_name'),
                ]);
            } else {
                $buisness_data = [
                    'buisness_name' => $postData['buisness_name'],
                    'buisness_color' => $postData['buisness_color'],
                    'added_at' => date("d-m-y h:i:s"),
                    'updated_at' => date("d-m-y h:i:s")
                ];
                $id = $postData['id'];
                unset($postData['id']);
                $success = $this->cash_flow_model->update_buisness_type($buisness_data, $id);
                $message = '';
                if ($success) {
                    $this->cash_flow_model->delete_buisness_assignees($id);
                    foreach ($postData['assignees'] as $assignee) {
                        $assignee_data = [
                            'assignee_id' => $assignee,
                            'buisness_id' => $id,
                        ];
                        $this->cash_flow_model->add_buisness_assignee($assignee_data);
                    }
                    $message = _l('updated_successfully', _l('buisness_type'));

                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }
    public function delete_buisness($id)
    {

        if (!$id) {
            redirect(admin_url('cash_flow'));
        }

        $response = $this->cash_flow_model->delete_bsns_type($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('contract_type_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('contract_type')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('contract_type_lowercase')));
        }
        redirect(admin_url('cash_flow'));
    }

    public function get_expenses_total()
    {
        $data = $this->input->post();

        if ($this->input->post()) {
            $data['totals'] = $this->cash_flow_model->get_expenses_total($this->input->post());
            if ($data['totals']['currency_switcher'] == true) {
                $this->load->model('currencies_model');
                $data['currencies'] = $this->currencies_model->get();
            }
            $data['_currency'] = $data['totals']['currencyid'];
            $this->load->view('cash_flow_total_template', $data);
        }
    }

    public function list_cf_expenses($buisness_id, $id = '')
    {

        close_setup_menu();
        $data['buisness_id'] = $buisness_id;
        $this->load->model('payment_modes_model');

        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

        $data['expenseid'] = $id;
        $data['categories'] = $this->cash_flow_model->get_category();
        $data['years'] = $this->cash_flow_model->get_expenses_years();
        $data['title'] = _l('expenses');

        $this->load->view('cash_flow/admin/expenses/manage', $data);
    }


    public function table($buisness_id, $clientid = '')
    {


        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);
        $data['buisness_id'] = $buisness_id;

        $this->app->get_table_data(module_views_path('cash_flow', 'admin/cf_expenses'), [
            'clientid' => $clientid,
            'data' => $data,

        ]);

    }

    public function expense($buisness_id, $id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!staff_can('create', 'cash_flow')) {
                    set_alert('danger', _l('access_denied'));
                    echo json_encode([
                        'url' => admin_url('cash_flow/expense'),
                    ]);
                    die;
                }
                $id = $this->cash_flow_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('expense')));
                    echo json_encode([
                        'url' => admin_url('cash_flow/list_cf_expenses/' . $buisness_id),
                        'expenseid' => $id,
                    ]);
                    die;
                }
                echo json_encode([
                    'url' => admin_url('cash_flow/expense'),
                ]);
                die;
            }
            if (!staff_can('edit', 'cash_fllow')) {
                set_alert('danger', _l('access_denied'));
                echo json_encode([
                    'url' => admin_url('cash_flow/expense/' . $buisness_id),
                ]);
                die;
            }
            $success = $this->cash_flow_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('expense')));
            }
            echo json_encode([
                'url' => admin_url('cash_flow/list_cf_expenses/' . $buisness_id),
                'expenseid' => $id,
            ]);
            die;
        }
        if ($id == '') {

            $title =  _l('add_cf_expenses');
        } else {


            $data['expense'] = $this->cash_flow_model->get($id);

            if (!$data['expense'] || (!has_permission('expenses', '', 'view') && $data['expense']->addedfrom != get_staff_user_id())) {
                blank_page(_l('expense_not_found'));
            }

            $title = _l('edit_cf_expenses');
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('taxes_model');
        $this->load->model('payment_modes_model');
        $this->load->model('currencies_model');

        $data['taxes'] = $this->taxes_model->get();
        $data['categories'] = $this->cash_flow_model->get_category();
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'invoices_only !=' => 1,
        ]);
        $data['bodyclass'] = 'expense';
        $data['currencies'] = $this->currencies_model->get();
        $data['title'] = $title;
        $data['buisness_id'] = $buisness_id;
        $this->load->view('cash_flow/admin/expenses/expense', $data);
    }

    public function import()
    {
        if (!staff_can('create', 'expenses')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_expenses', [], 'import');

        $this->import->setDatabaseFields($this->db->list_fields(db_prefix() . 'expenses'))
            ->setCustomFields(get_custom_fields('expenses'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['title'] = _l('import');
        $this->load->view('admin/expenses/import', $data);
    }

    public function bulk_action()
    {
        $total_deleted = 0;
        $total_updated = 0;

        if ($this->input->post()) {
            $ids = $this->input->post('ids');
            $amount = $this->input->post('amount');
            $date = $this->input->post('date');
            $category = $this->input->post('category');
            $paymentmode = $this->input->post('paymentmode');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if (staff_can('delete', 'expenses')) {
                            if ($this->cash_flow_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if (staff_can('edit', 'expenses')) {
                            $this->db->where('id', $id);
                            $this->db->update('expenses', array_filter([
                                'paymentmode' => $paymentmode ?: null,
                                'category' => $category ?: null,
                                'date' => $date ? to_sql_date($date) : null,
                                'amount' => $amount ?: null,
                            ]));

                            if ($this->db->affected_rows() > 0) {
                                $total_updated++;
                            }
                        }
                    }
                }
            }

            if ($total_updated > 0) {
                set_alert('success', _l('updated_successfully', _l('expenses')));
            } elseif ($this->input->post('mass_delete')) {
                set_alert('success', _l('total_expenses_deleted', $total_deleted));
            }
        }
    }

    public function pdf($id)
    {
        $expense = $this->cash_flow_model->get($id);
        if (!staff_can('view', 'cash_flow') && $expense->addedfrom != get_staff_user_id()) {
            access_denied();
        }
        $pdf = app_pdf('expense', LIBSPATH . 'pdf/Expense_pdf', $expense);
        $pdf->output('#' . slug_it($expense->category_name) . '_' . _d($expense->date) . '.pdf', 'I');
    }

    public function delete($buisness_id, $id)
    {

        if (!staff_can('delete', 'cash_flow')) {
            access_denied('cash_flow');
        }
        if (!$id) {
            redirect(admin_url('cash_flow/list_cf_expenses'));
        }
        $response = $this->cash_flow_model->delete($id);
        if ($response === true) {
            set_alert('success', _l('deleted', _l('expense')));
        } else {

            set_alert('warning', _l('problem_deleting', _l('expense_lowercase')));
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'expenses/') !== false) {
            redirect(admin_url('cash_flow/list_cf_expenses/' . $buisness_id));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function copy($buisness_id, $id)
    {
        if (!staff_can('create', '', 'cash_flow')) {
            access_denied('cash_flow');
        }
        $new_expense_id = $this->cash_flow_model->copy($id);
        if ($new_expense_id) {
            set_alert('success', _l('expense_copy_success'));
            redirect(admin_url('cash_flow/expense/' . $buisness_id . '/' . $new_expense_id));
        } else {
            set_alert('warning', _l('expense_copy_fail'));
        }
        redirect(admin_url('cash_flow/list_cf_expenses/' . $buisness_id . '/' . $id));
    }

    public function convert_to_invoice($id)
    {
        if (staff_can('create', '', 'cash_flow')) {
            access_denied('Convert Expense to Invoice');
        }
        if (!$id) {
            redirect(admin_url('cash_flow/list_cf_expenses'));
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }

        $params = [];
        if ($this->input->get('include_note') == 'true') {
            $params['include_note'] = true;
        }

        if ($this->input->get('include_name') == 'true') {
            $params['include_name'] = true;
        }

        $invoiceid = $this->cash_flow_model->convert_to_invoice($id, $draft_invoice, $params);
        if ($invoiceid) {
            set_alert('success', _l('expense_converted_to_invoice'));
            redirect(admin_url('invoices/invoice/' . $invoiceid));
        } else {
            set_alert('warning', _l('expense_converted_to_invoice_fail'));
        }
        redirect(admin_url('cash_flow/list_cf_expenses' . $id));
    }

    public function get_expense_data_ajax($id, $buisness_id = '')
    {

        if (!staff_can('view', 'cash_flow') && !staff_can('view_own', 'cash_flow')) {
            echo _l('access_denied');
            die;
        }
        $expense = $this->cash_flow_model->get($id);
        if (!$expense || (!staff_can('view', 'cash_flow') && $expense->addedfrom != get_staff_user_id())) {
            echo _l('expense_not_found');
            die;
        }
        $data['expense'] = $expense;
        $data['buisness_id'] = $buisness_id;
        if ($expense->billable == 1) {
            if ($expense->invoiceid !== null) {
                $this->load->model('invoices_model');
                $data['invoice'] = $this->invoices_model->get($expense->invoiceid);
            }
        }
        $data['child_expenses'] = $this->cash_flow_model->get_child_expenses($id);
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('cash_flow/admin/expenses/expense_preview_template', $data);
    }

    public function get_customer_change_data($customer_id = '')
    {
        echo json_encode([
            'customer_has_projects' => customer_has_projects($customer_id),
            'client_currency' => $this->clients_model->get_customer_default_currency($customer_id),
        ]);
    }

    public function categories()
    {
        if (!is_admin()) {
            access_denied('expenses');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('expenses_categories');
        }
        $data['title'] = _l('expense_categories');
        $this->load->view('admin/expenses/manage_categories', $data);
    }

    public function category()
    {
        if (!is_admin() && get_option('staff_members_create_inline_expense_categories') == '0') {
            access_denied('expenses');
        }
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->cash_flow_model->add_category($this->input->post());
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $id ? _l('added_successfully', _l('expense_category')) : '',
                    'id' => $id,
                    'name' => $this->input->post('name'),
                ]);
            } else {
                $data = $this->input->post();
                $id = $data['id'];
                unset($data['id']);
                $success = $this->cash_flow_model->update_category($data, $id);
                $message = _l('updated_successfully', _l('expense_category'));
                echo json_encode(['success' => $success, 'message' => $message]);
            }
        }
    }

    public function delete_category($id)
    {
        if (!is_admin()) {
            access_denied('expenses');
        }
        if (!$id) {
            redirect(admin_url('expenses/categories'));
        }
        $response = $this->cash_flow_model->delete_category($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('expense_category_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('expense_category')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('expense_category_lowercase')));
        }
        redirect(admin_url('expenses/categories'));
    }

    public function add_expense_attachment($id, $buisness_id)
    {
        handle_cf_expense_attachments($id);
        echo json_encode([
            'url' => admin_url('cash_flow/list_cf_expenses/' . $buisness_id . '/' . $id),
        ]);
    }

    public function delete_expense_attachment($buisness_id, $id, $preview = '')
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'cashflow_expense');
        $file = $this->db->get(db_prefix() . 'files')->row();
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            $success = $this->cash_flow_model->delete_expense_attachment($id);
            if ($success) {
                set_alert('success', _l('deleted', _l('expense_receipt')));
            } else {
                set_alert('warning', _l('problem_deleting', _l('expense_receipt_lowercase')));
            }
            if ($preview == '') {
                redirect(admin_url('cash_flow/expense/' . $buisness_id . '/' . $id));
            } else {
                redirect(admin_url('cash_flow/list_cf_expenses/' . $buisness_id . '/' . $id));
            }
        } else {
            access_denied('cash_flow');
        }
    }

}