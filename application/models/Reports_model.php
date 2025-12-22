<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  Leads conversions monthly report
     * @param   mixed $month  which month / chart
     * @return  array          chart data
     */
    public function leads_monthly_report($month)
    {
        $result      = $this->db->query('select last_status_change from ' . db_prefix() . 'leads where MONTH(last_status_change) = ' . $month . ' AND status = 1 and lost = 0')->result_array();
        $month_dates = [];
        $data        = [];
        for ($d = 1; $d <= 31; $d++) {
            $time = mktime(12, 0, 0, $month, $d, date('Y'));
            if (date('m', $time) == $month) {
                $month_dates[] = _d(date('Y-m-d', $time));
                $data[]        = 0;
            }
        }
        $chart = [
            'labels'   => $month_dates,
            'datasets' => [
                [
                    'label'           => _l('leads'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.5)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $data,
                ],
            ],
        ];
        foreach ($result as $lead) {
            $i = 0;
            foreach ($chart['labels'] as $date) {
                if (_d(date('Y-m-d', strtotime($lead['last_status_change']))) == $date) {
                    $chart['datasets'][0]['data'][$i]++;
                }
                $i++;
            }
        }

        return $chart;
    }

    public function get_stats_chart_data($label, $where, $dataset_options, $year)
    {
        $chart = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'       => $label,
                    'borderWidth' => 1,
                    'tension'     => false,
                    'data'        => [],
                ],
            ],
        ];

        foreach ($dataset_options as $key => $val) {
            $chart['datasets'][0][$key] = $val;
        }
        $this->load->model('expenses_model');
        $categories = $this->expenses_model->get_category();
        foreach ($categories as $category) {
            $_where['category']   = $category['id'];
            $_where['YEAR(date)'] = $year;
            if (count($where) > 0) {
                foreach ($where as $key => $val) {
                    $_where[$key] = $this->db->escape_str($val);
                }
            }
            array_push($chart['labels'], $category['name']);
            array_push($chart['datasets'][0]['data'], total_rows(db_prefix() . 'expenses', $_where));
        }

        return $chart;
    }

    public function get_expenses_vs_income_report($year = '')
    {
        $this->load->model('expenses_model');

        $months_labels  = [];
        $total_expenses = [];
        $total_income   = [];
        $i              = 0;
        if (!is_numeric($year)) {
            $year = date('Y');
        }
        for ($m = 1; $m <= 12; $m++) {
            array_push($months_labels, _l(date('F', mktime(0, 0, 0, $m, 1))));
            $this->db->select('id')->from(db_prefix() . 'expenses')->where('MONTH(date)', $m)->where('YEAR(date)', $year);
            $expenses = $this->db->get()->result_array();
            if (!isset($total_expenses[$i])) {
                $total_expenses[$i] = [];
            }
            if (count($expenses) > 0) {
                foreach ($expenses as $expense) {
                    $expense = $this->expenses_model->get($expense['id']);
                    $total   = $expense->amount;
                    // Check if tax is applied
                    if ($expense->tax != 0) {
                        $total += ($total / 100 * $expense->taxrate);
                    }
                    if ($expense->tax2 != 0) {
                        $total += ($expense->amount / 100 * $expense->taxrate2);
                    }
                    $total_expenses[$i][] = $total;
                }
            } else {
                $total_expenses[$i][] = 0;
            }
            $total_expenses[$i] = array_sum($total_expenses[$i]);
            // Calculate the income
            $this->db->select('amount');
            $this->db->from(db_prefix() . 'invoicepaymentrecords');
            $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
            $this->db->where('MONTH(' . db_prefix() . 'invoicepaymentrecords.date)', $m);
            $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
            $payments = $this->db->get()->result_array();
            if (!isset($total_income[$m])) {
                $total_income[$i] = [];
            }
            if (count($payments) > 0) {
                foreach ($payments as $payment) {
                    $total_income[$i][] = $payment['amount'];
                }
            } else {
                $total_income[$i][] = 0;
            }
            $total_income[$i] = array_sum($total_income[$i]);
            $i++;
        }
        $chart = [
            'labels'   => $months_labels,
            'datasets' => [
                [
                    'label'           => _l('report_sales_type_income'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_income,
                ],
                [
                    'label'           => _l('expenses'),
                    'backgroundColor' => 'rgba(252,45,66,0.4)',
                    'borderColor'     => '#fc2d42',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $total_expenses,
                ],
            ],
        ];

        return $chart;
    }

    /**
     * Chart leads weeekly report
     * @return array  chart data
     */
    public function leads_this_week_report()
    {
        $this->db->where('CAST(last_status_change as DATE) >= "' . date('Y-m-d', strtotime('monday this week')) . '" AND CAST(last_status_change as DATE) <= "' . date('Y-m-d', strtotime('sunday this week')) . '" AND status = 1 and lost = 0');
        $weekly = $this->db->get(db_prefix() . 'leads')->result_array();
        $colors = get_system_favourite_colors();
        $chart  = [
            'labels' => [
                _l('wd_monday'),
                _l('wd_tuesday'),
                _l('wd_wednesday'),
                _l('wd_thursday'),
                _l('wd_friday'),
                _l('wd_saturday'),
                _l('wd_sunday'),
            ],
            'datasets' => [
                [
                    'data' => [
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                    ],
                    'backgroundColor' => [
                        $colors[0],
                        $colors[1],
                        $colors[2],
                        $colors[3],
                        $colors[4],
                        $colors[5],
                        $colors[6],
                    ],
                    'hoverBackgroundColor' => [
                        adjust_color_brightness($colors[0], -20),
                        adjust_color_brightness($colors[1], -20),
                        adjust_color_brightness($colors[2], -20),
                        adjust_color_brightness($colors[3], -20),
                        adjust_color_brightness($colors[4], -20),
                        adjust_color_brightness($colors[5], -20),
                        adjust_color_brightness($colors[6], -20),
                    ],
                ],
            ],
        ];
        foreach ($weekly as $weekly) {
            $lead_status_day = _l(mb_strtolower('wd_' . date('l', strtotime($weekly['last_status_change']))));
            $i               = 0;
            foreach ($chart['labels'] as $dat) {
                if ($lead_status_day == $dat) {
                    $chart['datasets'][0]['data'][$i]++;
                }
                $i++;
            }
        }

        return $chart;
    }

    public function leads_staff_report()
    {
        $this->load->model('staff_model');
        $staff = $this->staff_model->get();
        if ($this->input->post()) {
            $from_date = to_sql_date($this->input->post('staff_report_from_date'));
            $to_date   = to_sql_date($this->input->post('staff_report_to_date'));
        }
        $chart = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => _l('leads_staff_report_created'),
                    'backgroundColor' => 'rgba(3,169,244,0.2)',
                    'borderColor'     => '#03a9f4',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
                [
                    'label'           => _l('leads_staff_report_lost'),
                    'backgroundColor' => 'rgba(252,45,66,0.4)',
                    'borderColor'     => '#fc2d42',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
                [
                    'label'           => _l('leads_staff_report_converted'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => [],
                ],
            ],
        ];

        foreach ($staff as $member) {
            array_push($chart['labels'], $member['firstname'] . ' ' . $member['lastname']);

            if (!isset($to_date) && !isset($from_date)) {
                $this->db->where('CASE WHEN assigned=0 THEN addedfrom=' . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END
                    AND status=1', '', false);
                $total_rows_converted = $this->db->count_all_results(db_prefix() . 'leads');

                $total_rows_created = total_rows(db_prefix() . 'leads', [
                    'addedfrom' => $member['staffid'],
                ]);

                $this->db->where('CASE WHEN assigned=0 THEN addedfrom=' . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END
                    AND lost=1', '', false);
                $total_rows_lost = $this->db->count_all_results(db_prefix() . 'leads');
            } else {
                $sql                  = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(last_status_change) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND status = 1 AND CASE WHEN assigned=0 THEN addedfrom=" . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END';
                $total_rows_converted = $this->db->query($sql)->row()->total;

                $sql                = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(dateadded) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND addedfrom=" . $member['staffid'] . '';
                $total_rows_created = $this->db->query($sql)->row()->total;

                $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(last_status_change) BETWEEN '" . $this->db->escape_str($from_date) . "' AND '" . $this->db->escape_str($to_date) . "' AND lost = 1 AND CASE WHEN assigned=0 THEN addedfrom=" . $member['staffid'] . ' ELSE assigned=' . $member['staffid'] . ' END';

                $total_rows_lost = $this->db->query($sql)->row()->total;
            }

            array_push($chart['datasets'][0]['data'], $total_rows_created);
            array_push($chart['datasets'][1]['data'], $total_rows_lost);
            array_push($chart['datasets'][2]['data'], $total_rows_converted);
        }

        return $chart;
    }

    /**
     * Lead conversion by sources report / chart
     * @return arrray chart data
     */
    public function leads_sources_report()
    {
        $this->load->model('leads_model');
        $sources = $this->leads_model->get_source();
        $chart   = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => _l('report_leads_sources_conversions'),
                    'backgroundColor' => 'rgba(124, 179, 66, 0.5)',
                    'borderColor'     => '#7cb342',
                    'data'            => [],
                ],
            ],
        ];
        foreach ($sources as $source) {
            array_push($chart['labels'], $source['name']);
            array_push($chart['datasets'][0]['data'], total_rows(db_prefix() . 'leads', [
                'source' => $source['id'],
                'status' => 1,
                'lost'   => 0,
            ]));
        }

        return $chart;
    }

    public function report_by_customer_groups()
    {
        $months_report = $this->input->post('months_report');
        $groups        = $this->clients_model->get_groups();
        if ($months_report != '') {
            $custom_date_select = '';
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
                    date('Y-m-d', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = db_prefix() . 'invoicepaymentrecords.date ="' . $from_date . '"';
                } else {
                    $custom_date_select = '(' . db_prefix() . 'invoicepaymentrecords.date BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
            $this->db->where($custom_date_select);
        }
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date,' . db_prefix() . 'invoices.clientid,(SELECT GROUP_CONCAT(name) FROM ' . db_prefix() . 'customers_groups LEFT JOIN ' . db_prefix() . 'customer_groups ON ' . db_prefix() . 'customer_groups.groupid = ' . db_prefix() . 'customers_groups.id WHERE customer_id = ' . db_prefix() . 'invoices.clientid) as customerGroups');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->join(db_prefix() . 'invoices', db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $this->db->where(db_prefix() . 'invoices.clientid IN (select customer_id FROM ' . db_prefix() . 'customer_groups)');
        $this->db->where(db_prefix() . 'invoices.status !=', 5);
        $by_currency = $this->input->post('report_currency');
        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }
        $payments       = $this->db->get()->result_array();
        $data           = [];
        $data['temp']   = [];
        $data['total']  = [];
        $data['labels'] = [];
        foreach ($groups as $group) {
            if (!isset($data['groups'][$group['name']])) {
                $data['groups'][$group['name']] = $group['name'];
            }
        }

        // If any groups found
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                foreach ($payments as $payment) {
                    $p_groups = explode(',', $payment['customerGroups']);
                    foreach ($p_groups as $p_group) {
                        if ($p_group == $group) {
                            $data['temp'][$group][] = $payment['amount'];
                        }
                    }
                }
                array_push($data['labels'], $group);
                if (isset($data['temp'][$group])) {
                    $data['total'][] = array_sum($data['temp'][$group]);
                } else {
                    $data['total'][] = 0;
                }
            }
        }

        $chart = [
            'labels'   => $data['labels'],
            'datasets' => [
                [
                    'label'           => _l('total_amount'),
                    'backgroundColor' => 'rgba(197, 61, 169, 0.2)',
                    'borderColor'     => '#c53da9',
                    'borderWidth'     => 1,
                    'tension'         => false,
                    'data'            => $data['total'],
                ],
            ],
        ];

        return $chart;
    }

    public function report_by_payment_modes()
    {
        $this->load->model('payment_modes_model');
        $modes  = $this->payment_modes_model->get('', [], true, true);
        $year   = $this->input->post('year');
        $colors = get_system_favourite_colors();
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $by_currency = $this->input->post('report_currency');
        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }
        $all_payments = $this->db->get()->result_array();
        $chart        = [
            'labels'   => [],
            'datasets' => [],
        ];
        $data           = [];
        $data['months'] = [];
        foreach ($all_payments as $payment) {
            $month   = date('m', strtotime($payment['date']));
            $dateObj = DateTime::createFromFormat('!m', $month);
            $month   = $dateObj->format('F');
            if (!isset($data['months'][$month])) {
                $data['months'][$month] = $month;
            }
        }
        usort($data['months'], function ($a, $b) {
            $month1 = date_parse($a);
            $month2 = date_parse($b);

            return $month1['month'] - $month2['month'];
        });

        foreach ($data['months'] as $month) {
            array_push($chart['labels'], _l($month) . ' - ' . $year);
        }
        $i = 0;
        foreach ($modes as $mode) {
            if (total_rows(db_prefix() . 'invoicepaymentrecords', [
                'paymentmode' => $mode['id'],
            ]) == 0) {
                continue;
            }
            $color = '#4B5158';
            if (isset($colors[$i])) {
                $color = $colors[$i];
            }
            $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
            $this->db->from(db_prefix() . 'invoicepaymentrecords');
            $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
            $this->db->where(db_prefix() . 'invoicepaymentrecords.paymentmode', $mode['id']);
            $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
            $by_currency = $this->input->post('report_currency');
            if ($by_currency) {
                $this->db->where('currency', $by_currency);
            }
            $payments = $this->db->get()->result_array();

            $datasets_data          = [];
            $datasets_data['total'] = [];
            foreach ($data['months'] as $month) {
                $total_payments = [];
                if (!isset($datasets_data['temp'][$month])) {
                    $datasets_data['temp'][$month] = [];
                }
                foreach ($payments as $payment) {
                    $_month  = date('m', strtotime($payment['date']));
                    $dateObj = DateTime::createFromFormat('!m', $_month);
                    $_month  = $dateObj->format('F');
                    if ($month == $_month) {
                        $total_payments[] = $payment['amount'];
                    }
                }
                $datasets_data['total'][] = array_sum($total_payments);
            }
            $chart['datasets'][] = [
                'label'           => $mode['name'],
                'backgroundColor' => $color,
                'borderColor'     => adjust_color_brightness($color, -20),
                'tension'         => false,
                'borderWidth'     => 1,
                'data'            => $datasets_data['total'],
            ];
            $i++;
        }

        return $chart;
    }

    /**
     * Total income report / chart
     * @return array chart data
     */
    public function total_income_report()
    {
        $year = $this->input->post('year');
        $this->db->select('amount,' . db_prefix() . 'invoicepaymentrecords.date');
        $this->db->from(db_prefix() . 'invoicepaymentrecords');
        $this->db->where('YEAR(' . db_prefix() . 'invoicepaymentrecords.date)', $year);
        $this->db->join(db_prefix() . 'invoices', '' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid');
        $by_currency = $this->input->post('report_currency');

        if ($by_currency) {
            $this->db->where('currency', $by_currency);
        }

        $payments       = $this->db->get()->result_array();
        $data           = [];
        $data['months'] = [];
        $data['temp']   = [];
        $data['total']  = [];
        $data['labels'] = [];

        foreach ($payments as $payment) {
            $month   = date('m', strtotime($payment['date']));
            $dateObj = DateTime::createFromFormat('!m', $month);
            $month   = $dateObj->format('F');
            if (!isset($data['months'][$month])) {
                $data['months'][$month] = $month;
            }
        }

        usort($data['months'], function ($a, $b) {
            $month1 = date_parse($a);
            $month2 = date_parse($b);

            return $month1['month'] - $month2['month'];
        });

        foreach ($data['months'] as $month) {
            foreach ($payments as $payment) {
                $monthNumber = date('m', strtotime($payment['date']));
                $dateObj     = DateTime::createFromFormat('!m', $monthNumber);
                $_month      = $dateObj->format('F');
                if ($month == $_month) {
                    $data['temp'][$month][] = $payment['amount'];
                }
            }

            array_push($data['labels'], _l($month) . ' - ' . $year);

            $data['total'][] = array_sum($data['temp'][$month]) - $this->calculate_refunded_amount($year, $monthNumber, $by_currency);
        }

        $chart = [
            'labels'   => $data['labels'],
            'datasets' => [
                [
                    'label'           => _l('report_sales_type_income'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'tension'         => false,
                    'borderWidth'     => 1,
                    'data'            => $data['total'],
                ],
            ],
        ];

        return $chart;
    }

    public function get_distinct_payments_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'invoicepaymentrecords')->result_array();
    }

    public function get_distinct_customer_invoices_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'invoices WHERE clientid=' . get_client_user_id())->result_array();
    }

    protected function calculate_refunded_amount($year, $month, $currency)
    {
        $sql = 'SELECT
        SUM(' . db_prefix() . 'creditnote_refunds.amount) as refunds_amount
        FROM ' . db_prefix() . 'creditnote_refunds
        WHERE YEAR(refunded_on) = ' . $year . ' AND MONTH(refunded_on) = ' . $month;

        if ($currency) {
            $sql .= ' AND credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes WHERE currency=' . $currency . ')';
        }

        $refunds_amount = $this->db->query($sql)->row()->refunds_amount;

        if ($refunds_amount === null) {
            $refunds_amount = 0;
        }

        return $refunds_amount;
    }
    private function distinct_taxes($rel_type)
    {
        return $this->db->query('SELECT DISTINCT taxname,taxrate FROM ' . db_prefix() . "item_tax WHERE rel_type='" . $rel_type . "' ORDER BY taxname ASC")->result_array();
    }
    // public function get_invoice_data($filters = [])
    // {
    //     $this->load->model('currencies_model');
    //     $this->load->model('invoices_model');

    //     $invoice_taxes = $this->distinct_taxes('invoice');
    //     $totalTaxesColumns = count($invoice_taxes);

    //     $select = [
    //         db_prefix() . 'invoices.id',
    //         'number',
    //         get_sql_select_client_company(),
    //         'YEAR(date) as year',
    //         'date',
    //         'duedate',
    //         'subtotal',
    //         'total',
    //         'total_tax',
    //         'discount_total',
    //         'adjustment',
    //         '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id) as credits_applied',
    //         '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id)) as amount_open',
    //         'status',
    //         'userid',
    //         'clientid',
    //         'discount_percent',
    //         'deleted_customer_name',
    //         'currency',
    //     ];

    //     // Add tax columns
    //     $invoiceTaxesSelect = array_reverse($invoice_taxes);
    //     foreach ($invoiceTaxesSelect as $key => $tax) {
    //         array_splice($select, 8, 0, '(
    //     SELECT CASE
    //     WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
    //     WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
    //     ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
    //     END
    //     FROM ' . db_prefix() . 'itemable
    //     INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
    //     WHERE ' . db_prefix() . 'itemable.rel_type="invoice" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'invoices.id) as total_tax_single_' . $key);
    //     }

    //     // Build the query
    //     $this->db->select(implode(', ', $select));
    //     $this->db->from(db_prefix() . 'invoices');
    //     $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

    //     // Start with base where condition (without AND)
    //     $this->db->where(db_prefix() . 'invoices.status !=', 5);
    //     // Apply filters
    //     $months_report = $filters['month'];
    //     $field = 'date';
    //     $custom_date_select = '';
    //     if ($months_report != '') {
    //         if (is_numeric($months_report)) {
    //             // Last month
    //             if ($months_report == '1') {
    //                 $beginMonth = date('Y-m-01', strtotime('first day of last month'));
    //                 $endMonth   = date('Y-m-t', strtotime('last day of last month'));
    //             } else {
    //                 $months_report = (int) $months_report;
    //                 $months_report--;
    //                 $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
    //                 $endMonth   = date('Y-m-t');
    //             }

    //             $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
    //         } elseif ($months_report == 'this_month') {
    //             $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
    //         } elseif ($months_report == 'this_year') {
    //             $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
    //                 date('Y-m-d', strtotime(date('Y-01-01'))) .
    //                 '" AND "' .
    //                 date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
    //         } elseif ($months_report == 'last_year') {
    //             $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
    //                 date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
    //                 '" AND "' .
    //                 date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
    //         } elseif ($months_report == 'custom') {
    //             $from_date = to_sql_date($this->input->post('report_from'));
    //             $to_date   = to_sql_date($this->input->post('report_to'));
    //             if ($from_date == $to_date) {
    //                 $custom_date_select = 'AND ' . $field . ' = "' . $this->db->escape_str($from_date) . '"';
    //             } else {
    //                 $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $this->db->escape_str($from_date) . '" AND "' . $this->db->escape_str($to_date) . '")';
    //             }
    //         }
    //     }

    //     if (isset($filters['sale_agent_invoices']) && !empty($filters['sale_agent_invoices'])) {
    //         $agents = $filters['sale_agent_invoices'];
    //         if (is_array($agents)) {
    //             $this->db->where_in('sale_agent', $agents);
    //         } else {
    //             $this->db->where('sale_agent', $agents);
    //         }
    //     }

    //     if (isset($filters['report_currency']) && $filters['report_currency']) {
    //         $by_currency = $filters['report_currency'];
    //         // Update the amount_open calculation for specific currency
    //         $amount_open_query = '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND currency = ' . $by_currency . ') - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id AND currency = ' . $by_currency . '))';

    //         // Find and replace the amount_open in select
    //         foreach ($select as $key => $value) {
    //             if (strpos($value, 'as amount_open') !== false) {
    //                 $select[$key] = $amount_open_query . ' as amount_open';
    //                 break;
    //             }
    //         }
    //         $this->db->select(implode(', ', $select)); // Re-select with updated amount_open
    //         $this->db->where('currency', $by_currency);
    //     }

    //     if (isset($filters['invoice_status']) && !empty($filters['invoice_status'])) {
    //         $statuses = $filters['invoice_status'];
    //         if (is_array($statuses)) {
    //             $this->db->where_in('status', $statuses);
    //         } else {
    //             $this->db->where('status', $statuses);
    //         }
    //     }

    //     // Get results
    //     $query = $this->db->get();

    //     // For debugging, you can see the query:
    //     // echo $this->db->last_query(); die();

    //     return $query->result_array();
    // }
    public function get_invoice_data($filters = [])
    {
        $this->load->model('currencies_model');
        $this->load->model('invoices_model');

        $invoice_taxes = $this->distinct_taxes('invoice');
        $totalTaxesColumns = count($invoice_taxes);

        $select = [
            db_prefix() . 'invoices.id',
            'number',
            get_sql_select_client_company(),
            'YEAR(date) as year',
            'date',
            'duedate',
            'subtotal',
            'total',
            'total_tax',
            'discount_total',
            'adjustment',
            '(SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id) as credits_applied',
            '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id) - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id)) as amount_open',
            'status',
            'userid',
            'clientid',
            'discount_percent',
            'deleted_customer_name',
            'currency',
        ];

        // Add tax columns
        $invoiceTaxesSelect = array_reverse($invoice_taxes);
        foreach ($invoiceTaxesSelect as $key => $tax) {
            array_splice($select, 8, 0, '(
    SELECT CASE
    WHEN discount_percent != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * discount_percent/100)),' . get_decimal_places() . ')
    WHEN discount_total != 0 AND discount_type = "before_tax" THEN ROUND(SUM((qty*rate/100*' . db_prefix() . 'item_tax.taxrate) - (qty*rate/100*' . db_prefix() . 'item_tax.taxrate * (discount_total/subtotal*100) / 100)),' . get_decimal_places() . ')
    ELSE ROUND(SUM(qty*rate/100*' . db_prefix() . 'item_tax.taxrate),' . get_decimal_places() . ')
    END
    FROM ' . db_prefix() . 'itemable
    INNER JOIN ' . db_prefix() . 'item_tax ON ' . db_prefix() . 'item_tax.itemid=' . db_prefix() . 'itemable.id
    WHERE ' . db_prefix() . 'itemable.rel_type="invoice" AND taxname="' . $tax['taxname'] . '" AND taxrate="' . $tax['taxrate'] . '" AND ' . db_prefix() . 'itemable.rel_id=' . db_prefix() . 'invoices.id) as total_tax_single_' . $key);
        }

        // Build the query
        $this->db->select(implode(', ', $select));
        $this->db->from(db_prefix() . 'invoices');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid', 'left');

        // Start with base where condition
        $this->db->where(db_prefix() . 'invoices.status !=', 5);

        // Apply filters
        if (isset($filters['month']) && $filters['month'] != '') {
            $months_report = $filters['month'];
            $field = 'date';

            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $this->db->where($field . ' >=', $beginMonth);
                $this->db->where($field . ' <=', $endMonth);
            } elseif ($months_report == 'this_month') {
                $this->db->where($field . ' >=', date('Y-m-01'));
                $this->db->where($field . ' <=', date('Y-m-t'));
            } elseif ($months_report == 'this_year') {
                $this->db->where($field . ' >=', date('Y-01-01'));
                $this->db->where($field . ' <=', date('Y-12-31'));
            } elseif ($months_report == 'last_year') {
                $lastYear = date('Y', strtotime('last year'));
                $this->db->where($field . ' >=', $lastYear . '-01-01');
                $this->db->where($field . ' <=', $lastYear . '-12-31');
            } elseif ($months_report == 'custom') {
                // For custom date range, you need to pass from_date and to_date in filters
                if (isset($filters['from_date']) && isset($filters['to_date'])) {
                    $from_date = to_sql_date($filters['from_date']);
                    $to_date = to_sql_date($filters['to_date']);

                    if ($from_date == $to_date) {
                        $this->db->where($field, $from_date);
                    } else {
                        $this->db->where($field . ' >=', $from_date);
                        $this->db->where($field . ' <=', $to_date);
                    }
                }
            }
        }

        if (isset($filters['sale_agent']) && !empty($filters['sale_agent'])) {
            $agents = $filters['sale_agent'];
            if (is_array($agents)) {
                $this->db->where_in('sale_agent', $agents);
            } else {
                $this->db->where('sale_agent', $agents);
            }
        }

        if (isset($filters['report_currency']) && $filters['report_currency']) {
            $by_currency = $filters['report_currency'];
            // Update the amount_open calculation for specific currency
            $amount_open_query = '(SELECT total - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'invoicepaymentrecords WHERE invoiceid = ' . db_prefix() . 'invoices.id AND currency = ' . $by_currency . ') - (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'credits WHERE ' . db_prefix() . 'credits.invoice_id=' . db_prefix() . 'invoices.id AND currency = ' . $by_currency . '))';

            // Find and replace the amount_open in select
            foreach ($select as $key => $value) {
                if (strpos($value, 'as amount_open') !== false) {
                    $select[$key] = $amount_open_query . ' as amount_open';
                    break;
                }
            }
            $this->db->select(implode(', ', $select)); // Re-select with updated amount_open
            $this->db->where('currency', $by_currency);
        }

        if (isset($filters['invoice_status']) && !empty($filters['invoice_status'])) {
            $statuses = $filters['invoice_status'];
            if (is_array($statuses)) {
                $this->db->where_in('status', $statuses);
            } else {
                $this->db->where('status', $statuses);
            }
        }

        // Get results
        $query = $this->db->get();

        // For debugging, you can see the query:
        // echo $this->db->last_query(); die();

        return $query->result_array();
    }
}
