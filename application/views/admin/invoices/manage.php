<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div id="vueApp">
            <div class="row">
                <?php include_once(APPPATH . 'views/admin/invoices/filter_params.php'); ?>
                <?php $this->load->view('admin/invoices/list_template'); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<div id="modal-wrapper"></div>
<script>
    var hidden_columns = [2, 6, 7, 8];
</script>
<?php init_tail(); ?>
<script>
    $(function() {
        init_invoice();
    });
    $(function() {
        init_estimate();
    });
    var table_rec_task;
    var report_from_choose;
    var report_from = $('input[name="report-from"]');
    var report_to = $('input[name="report-to"]');
    var date_range = $('#date-range');
    $(function() {
        table_rec_task = $('.table-invoices');
        report_from_choose = $('#report-time');

        var Params = {
            "client": "[name='client[]']",
            "project": "[name='project[]']",
            "status": "[name='status[]']",
            "report_months": '[name="months-report"]',
            "report_from": '[name="report-from"]',
            "report_to": '[name="report-to"]',
            "year_requisition": "[name='year_requisition']",
        };
        initDataTable('.table-invoices', admin_url + 'invoices/table_new', [], [], Params,
            [0, 'desc']);
        $.each(Params, function(i, obj) {
            $('select' + obj).on('change', function() {
                table_rec_task.DataTable().ajax.reload();
            });
        });

        $('select[name="months-report"]').on('change', function() {
            if ($(this).val() != 'custom') {
                table_rec_task.DataTable().ajax.reload();
            }
        });

        $('select[name="year_requisition"]').on('change', function() {
            table_rec_task.DataTable().ajax.reload();
        });

        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    table_rec_task.DataTable().ajax.reload();
                }
            } else {
                report_to.attr('disabled', true);
            }
        });

        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                table_rec_task.DataTable().ajax.reload();
            }
        });

        $('select[name="months-report"]').on('change', function() {
            var val = $(this).val();
            report_to.attr('disabled', true);
            report_to.val('');
            report_from.val('');
            if (val == 'custom') {
                date_range.addClass('fadeIn').removeClass('hide');
                return;
            } else {
                if (!date_range.hasClass('hide')) {
                    date_range.removeClass('fadeIn').addClass('hide');
                }
            }
            table_rec_task.DataTable().ajax.reload();
        });

        $(document).on('click', '.reset_all_ot_filters', function() {
            var filterArea = $('.all_ot_filters');
            filterArea.find('input').val("");
            filterArea.find('select').selectpicker("val", "");
            table_rec_task.DataTable().ajax.reload().columns.adjust().responsive.recalc();
        });
        $(document).on('change', 'select[name="client[]"]', function() {
            $('select[name="client[]"]').selectpicker('refresh');
        });

        $(document).on('change', 'select[name="status[]"]', function() {
            $('select[name="status[]"]').selectpicker('refresh');
        });

        $(document).on('change', 'select[name="project[]"]', function() {
            $('select[name="project[]"]').selectpicker('refresh');
        });

        get_client_invoices_dashboard();

        function get_client_invoices_dashboard() {
            "use strict";
            var data = {}

            $.post(admin_url + 'invoices/get_client_invoices_dashboard', data).done(function(response) {
                response = JSON.parse(response);

                // Update value summaries
                $('.total_invoices_raised').text(response.total_invoices_raised);
                $('.total_invoiced_amount').text(response.total_invoiced_amount);
                $('.average_invoice_value').text(response.average_invoice_value);
                var lineChartOverTime = null;
                // LINE CHART - Certified Value Over Time
                var lineCtx = document.getElementById('lineChartOverTime').getContext('2d');

                if (lineChartOverTime) {
                    lineChartOverTime.data.labels = response.line_order_date;
                    lineChartOverTime.data.datasets[0].data = response.line_order_total;
                    lineChartOverTime.update();
                } else {
                    lineChartOverTime = new Chart(lineCtx, {
                        type: 'line',
                        data: {
                            labels: response.line_order_date,
                            datasets: [{
                                label: 'Certified Value',
                                data: response.line_order_total,
                                fill: false,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Month'
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Certified Value'
                                    }
                                }
                            }
                        }
                    });
                }

                // BAR CHART - Top 10 Vendors by Amount
                var vendorBarCtx = document.getElementById('barChartTopVendors').getContext('2d');
                var vendorLabels = response.bar_top_vendor_name;
                var vendorData = response.bar_top_vendor_value;

                if (window.barTopVendorsChart) {
                    barTopVendorsChart.data.labels = vendorLabels;
                    barTopVendorsChart.data.datasets[0].data = vendorData;
                    barTopVendorsChart.update();
                } else {
                    window.barTopVendorsChart = new Chart(vendorBarCtx, {
                        type: 'bar',
                        data: {
                            labels: vendorLabels,
                            datasets: [{
                                label: 'Amount',
                                data: vendorData,
                                backgroundColor: '#1E90FF',
                                borderColor: '#1E90FF',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Amount'
                                    }
                                },
                                y: {
                                    ticks: {
                                        autoSkip: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Vendors'
                                    }
                                }
                            }
                        }
                    });
                }

                // PIE CHART - Pie Chart for Invoice per Status
                var statusPieCtx = document.getElementById('pieChartForStatus').getContext('2d');
                var statusData = response.pie_status_value;
                var statusLabels = response.pie_status_name;

                if (window.poByStatusChart) {
                    poByStatusChart.data.labels = statusLabels;
                    poByStatusChart.data.datasets[0].data = statusData;
                    poByStatusChart.update();
                } else {
                    window.poByStatusChart = new Chart(statusPieCtx, {
                        type: 'pie',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusData,
                                backgroundColor: statusLabels.map((_, i) => `hsl(${i * 35 % 360}, 70%, 60%)`),
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.formattedValue;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

            });
        }
    });
</script>
</body>

</html>