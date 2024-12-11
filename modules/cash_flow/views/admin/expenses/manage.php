<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">

                    <div class="_buttons">
                        <?php if (staff_can('create',  'cash_flow')) { ?>
                            <a href="<?php echo admin_url('cash_flow/expense/' . $buisness_id); ?>" class="btn btn-primary">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?php echo _l('cf_new_expense'); ?>
                            </a>
                        <?php } ?>
                        <a href="<?php echo admin_url('cash_flow'); ?>" class="btn btn-danger mleft5">
                            <i class="fa-solid fa-home tw-mr-1"></i>
                            <?php echo _l('Back'); ?>
                        </a>
                        <?php $this->load->view('cash_flow/admin/expenses/filter_by_template'); ?>
                        <a href="#" onclick="slideToggle('#stats-top'); return false;"
                            class="pull-right btn btn-default mleft5 btn-with-tooltip" data-toggle="tooltip"
                            title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
                        <a href="#" class="btn btn-default pull-right btn-with-tooltip toggle-small-view hidden-xs"
                            onclick="toggle_small_view('.table-expenses','#expense'); return false;"
                            data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i
                                class="fa fa-angle-double-left"></i></a>
                        <div id="stats-top" class="hide">
                            <hr />
                            <div id="expenses_total"></div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12" id="small-table">
                        <div class="panel_s">
                            <div class="panel-body">
                                <div class="clearfix"></div>
                                <!-- if expenseid found in url -->
                                <?php echo form_hidden('expenseid', $expenseid); ?>
                                <div class="panel-table-full">
                                    <?php
                                    $hasPermission = staff_can('edit', 'cash_flow') || staff_can('edit', 'cash_flow');
                                    $withBulkActions = false;
                                    ?>
                                    <?php
                                    $table_data = [
                                       
                                        _l('the_number_sign'),
                                        _l('operation'),
                                        _l('expense_name'),
                                        _l('expense_dt_table_heading_date'),

                                    ];

                                    if (!isset($project)) {
                                        array_push($table_data, [
                                            'name'     => _l('expense_dt_table_heading_customer'),
                                            'th_attrs' => ['class' => (isset($client) ? 'not_visible' : '')],
                                        ]);
                                    } else {
                                        array_shift($table_data);
                                    }
                                    $table_data = array_merge($table_data, [
                                        _l('expense_dt_table_heading_reference_no'),
                                        _l('received'),
                                        _l('disbursement'),
                                        _l('balance'),
                                    ]);
                                    $custom_fields = get_custom_fields('cf_expenses', ['show_on_table' => 1]);
                                    foreach ($custom_fields as $field) {
                                        array_push($table_data, [
                                            'name'     => $field['name'],
                                            'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                        ]);
                                    }
                                    render_datatable($table_data, (isset($class) ? $class : 'cf_expenses'), [], [
                                        'data-last-order-identifier' => 'cf_expenses',
                                        'data-default-order'         => get_table_last_order('cf_expenses'),
                                        'id' => $table_id ?? 'cf_expenses',
                                    ]);
                                    ?>


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7 small-table-right-col">
                        <div id="expense" class="hide">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="expense_convert_helper_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('additional_action_required'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="radio radio-primary">
                    <input type="radio" checked id="expense_convert_invoice_type_1" value="save_as_draft_false"
                        name="expense_convert_invoice_type">
                    <label for="expense_convert_invoice_type_1"><?php echo _l('convert'); ?></label>
                </div>
                <div class="radio radio-primary">
                    <input type="radio" id="expense_convert_invoice_type_2" value="save_as_draft_true"
                        name="expense_convert_invoice_type">
                    <label for="expense_convert_invoice_type_2"><?php echo _l('convert_and_save_as_draft'); ?></label>
                </div>
                <div id="inc_field_wrapper">
                    <hr />
                    <p><?php echo _l('expense_include_additional_data_on_convert'); ?></p>
                    <p><b><?php echo _l('expense_add_edit_description'); ?> +</b></p>
                    <div class="checkbox checkbox-primary inc_note">
                        <input type="checkbox" id="inc_note">
                        <label for="inc_note"><?php echo _l('expense'); ?>
                            <?php echo _l('expense_add_edit_note'); ?></label>
                    </div>
                    <div class="checkbox checkbox-primary inc_name">
                        <input type="checkbox" id="inc_name">
                        <label for="inc_name"><?php echo _l('expense'); ?> <?php echo _l('expense_name'); ?></label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    id="expense_confirm_convert"><?php echo _l('confirm'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
    var hidden_columns = [4, 5, 6, 7, 8, 9];
    var buisness_id = "<?php echo $buisness_id ?>";
</script>
<?php init_tail(); ?>
<script>
    Dropzone.autoDiscover = false;
    $(function() {
        // Expenses additional server params
        var Expenses_ServerParams = {};
        var buisness_id = "<?php echo $buisness_id ?>";

        $.each($('._hidden_inputs._filters input'), function() {
            Expenses_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        initDataTable('.table-cf_expenses', admin_url + 'cash_flow/table/' + buisness_id, [0], [0], Expenses_ServerParams,[2,'DESC'])
            .column(1).visible(false, false).columns.adjust();

        init_cf_expense();

        $('#expense_convert_helper_modal').on('show.bs.modal', function() {
            var emptyNote = $('#tab_expense').attr('data-empty-note');
            var emptyName = $('#tab_expense').attr('data-empty-name');
            if (emptyNote == '1' && emptyName == '1') {
                $('#inc_field_wrapper').addClass('hide');
            } else {
                $('#inc_field_wrapper').removeClass('hide');
                emptyNote === '1' && $('.inc_note').addClass('hide') || $('.inc_note').removeClass('hide')
                emptyName === '1' && $('.inc_name').addClass('hide') || $('.inc_name').removeClass('hide')
            }
        });

        $('body').on('click', '#expense_confirm_convert', function() {
            var parameters = new Array();
            if ($('input[name="expense_convert_invoice_type"]:checked').val() == 'save_as_draft_true') {
                parameters['save_as_draft'] = 'true';
            }
            parameters['include_name'] = $('#inc_name').prop('checked');
            parameters['include_note'] = $('#inc_note').prop('checked');
            window.location.href = buildUrl(admin_url + 'expenses/convert_to_invoice/' + $('body').find(
                '.expense_convert_btn').attr('data-id'), parameters);
        });
    });



    function init_expenses_total() {

        if ($("#expenses_total").length === 0) {
            return;
        }
        var currency = $("body").find('select[name="expenses_total_currency"]').val();
        var _years = $("body")
            .find('select[name="expenses_total_years"]')
            .selectpicker("val");
        var years = [];
        $.each(_years, function(i, _y) {
            if (_y !== "") {
                years.push(_y);
            }
        });
        var customer_id = " ";
        var _customer_id = $('.customer_profile input[name="userid"]').val();
        if (typeof customer_id != "undefined") {
            customer_id = _customer_id;
        }
        var buisness_id = "<?php echo $buisness_id ?>";


        var project_id = "";
        var _project_id = $('input[name="project_id"]').val();
        if (typeof project_id != "undefined") {
            project_id = _project_id;
        }

        $.post(admin_url + "cash_flow/get_expenses_total", {
            currency: currency,
            init_total: true,
            years: years,
            customer_id: customer_id,
            project_id: project_id,
            buisness_id: buisness_id,
        }).done(function(response) {
            $("#expenses_total").html(response);
        });
    }

    function init_cf_expense(id) {

        load_small_table_item(
            id,
            "#expense",
            "expenseid",
            "cash_flow/get_expense_data_ajax",
            ".table-expenses",
        );
    }
</script>
</body>

</html>