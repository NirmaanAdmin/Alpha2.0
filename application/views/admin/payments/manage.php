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
    });
</script>
</body>

</html>