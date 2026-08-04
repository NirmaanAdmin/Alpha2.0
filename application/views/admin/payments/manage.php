<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <div class="panel-table-full">
                    <?php $this->load->view('admin/payments/table_html'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table_rec_task;
    var report_from_choose;
    var report_from = $('input[name="report-from"]');
    var report_to = $('input[name="report-to"]');
    var date_range = $('#date-range');
    $(function() {
        table_rec_task = $('.table-payments');
        report_from_choose = $('#report-time');

        var Params = {
            "payment_mode": "[name='payment_mode[]']",
            "client": "[name='client[]']",
            "report_months": '[name="months-report"]',
            "report_from": '[name="report-from"]',
            "report_to": '[name="report-to"]',
            "year_requisition": "[name='year_requisition']",
        };
        initDataTable('.table-payments', admin_url + 'payments/table', undefined, undefined, Params,
            <?php echo hooks()->apply_filters('payments_table_default_order', json_encode([0, 'desc'])); ?>);

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
            table_rec_task.DataTable().ajax.reload();
        });
        $(document).on('change', 'select[name="payment_mode[]"]', function() {
            $('select[name="payment_mode[]"]').selectpicker('refresh');
        });

        $(document).on('change', 'select[name="payment_mode[]"]', function() {
            $('select[name="payment_mode[]"]').selectpicker('refresh');
        });

        $(document).on('change', 'select[name="client[]"]', function() {
            $('select[name="client[]"]').selectpicker('refresh');
        });
        get_client_invoices_payment_dashboard();
    });

    function get_client_invoices_payment_dashboard() {
        "use strict";

        var data = {};

        $.post(admin_url + 'payments/get_client_invoices_payment_dashboard', data)
            .done(function(response) {

                response = JSON.parse(response);

                /*
                 * =====================================================
                 * CLIENT PAYMENT PIE CHART
                 * =====================================================
                 */

                var clientLabels = response.client_name || [];
                var clientData = response.client_value || [];

                var clientCanvas = document.getElementById('pieChartForStatus');

                if (clientCanvas) {

                    var clientPieCtx = clientCanvas.getContext('2d');

                    if (window.clientPaymentChart) {

                        window.clientPaymentChart.data.labels = clientLabels;

                        window.clientPaymentChart.data.datasets[0].data = clientData;

                        window.clientPaymentChart.data.datasets[0].backgroundColor =
                            clientLabels.map(function(_, i) {
                                return `hsl(${(i * 35) % 360}, 70%, 60%)`;
                            });

                        window.clientPaymentChart.update();

                    } else {

                        window.clientPaymentChart = new Chart(clientPieCtx, {
                            type: 'pie',

                            data: {
                                labels: clientLabels,

                                datasets: [{
                                    data: clientData,

                                    backgroundColor: clientLabels.map(function(_, i) {
                                        return `hsl(${(i * 35) % 360}, 70%, 60%)`;
                                    }),

                                    borderColor: '#fff',
                                    borderWidth: 1
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {

                                    legend: {
                                        position: 'bottom'
                                    },

                                    tooltip: {
                                        callbacks: {

                                            label: function(context) {

                                                var value = context.raw || 0;

                                                return context.label + ': ' +
                                                    Number(value).toLocaleString('en-IN', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    });
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }


                /*
                 * =====================================================
                 * PAYMENT MODE PIE CHART
                 * =====================================================
                 */

                var paymentModeLabels = response.payment_mode_name || [];
                var paymentModeData = response.payment_mode_value || [];

                var paymentModeCanvas = document.getElementById('pieChartForPaymentMode');

                if (paymentModeCanvas) {

                    var paymentModePieCtx = paymentModeCanvas.getContext('2d');

                    if (window.paymentModeChart) {

                        window.paymentModeChart.data.labels = paymentModeLabels;

                        window.paymentModeChart.data.datasets[0].data = paymentModeData;

                        window.paymentModeChart.data.datasets[0].backgroundColor =
                            paymentModeLabels.map(function(_, i) {
                                return `hsl(${(i * 35) % 360}, 70%, 60%)`;
                            });

                        window.paymentModeChart.update();

                    } else {

                        window.paymentModeChart = new Chart(paymentModePieCtx, {
                            type: 'pie',

                            data: {
                                labels: paymentModeLabels,

                                datasets: [{
                                    data: paymentModeData,

                                    backgroundColor: paymentModeLabels.map(function(_, i) {
                                        return `hsl(${(i * 35) % 360}, 70%, 60%)`;
                                    }),

                                    borderColor: '#fff',
                                    borderWidth: 1
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {

                                    legend: {
                                        position: 'bottom'
                                    },

                                    tooltip: {
                                        callbacks: {

                                            label: function(context) {

                                                var value = context.raw || 0;

                                                return context.label + ': ' +
                                                    Number(value).toLocaleString('en-IN', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    });
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

            });
    }
</script>
</body>

</html>

<script src="<?php echo module_dir_url(PURCHASE_MODULE_NAME, 'assets/plugins/charts/chart.js'); ?>?v=<?php echo PURCHASE_REVISION; ?>"></script>