<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <?php if (staff_can('create', 'cash_flow')) { ?>
                        <a href="#" onclick="newcompany(); return false;" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_buisiness'); ?>
                        </a>
                    <?php } ?>

                </div>
                <div class="panel_s card-wrp-ara">
                    <div class="panel-body">
                        <h4><i class="fa fa-briefcase" aria-hidden="true"></i> <?= _l('my_buss_lbl'); ?> </h4>
                        <hr class="hr-panel-heading" />
                        <div id="dashboard-data">
                            <div class="row">
                                <?php if (isset($companies) && !empty($companies)) { ?>
                                    <?php foreach ($companies as $company) { ?>

                                        <div class="col-lg-3 col-xs-12 col-md-12 total-column">
                                            <div class="bsns-card">
                                                <span class="counter-ara">
                                                    <?php echo get_expenses_count_by_buisness_id($company['id']) ?></span>
                                                <div class="card-ttl" style="background-color: <?= $company['buisness_color']; ?>;">
                                                    <h3>
                                                        <?php echo $company['buisness_name'] ?>
                                                    </h3>
                                                </div>

                                                <div class="bsns-card-bd">
                                                    <a class="cash-register"
                                                        href="<?php echo admin_url('cash_flow/list_cf_expenses/' . $company['id']); ?>"><?= _l('open_c_link'); ?></a>
                                                </div>

                                                <div class="btn-group" role="group" aria-label="Actions">
                                                    <?php if (staff_can('edit', 'cash_flow')) { ?>
                                                        <button type="button" class="btn btn-sm btn-warning" data-color="<?= $company['buisness_color']; ?>"
                                                            onclick="editCompany(this, <?= $company['id']; ?>, '<?= addslashes($company['buisness_name']); ?>')">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <?php if (staff_can('delete', 'cash_flow')) { ?>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="deleteCompany(<?= $company['id']; ?>)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>


                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>

                <?php if (staff_can('view', 'cash_flow')) { ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin">
                                <i class="fa-solid fa-money-bill-transfer"></i> Total Project Expenses.
                            </h4>
                            <hr class="hr-panel-heading" />
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-vertical-scroll-- wrap_data_table_cl">
                                        <?php
                                        render_datatable(array(
                                            _l('Business'),
                                            _l('Cash In'),
                                            _l('Cash Out'),
                                            _l('Final Balance')
                                        ), 'business_expense_total');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/cf_buisness_types_modal'); ?>
<?php init_tail(); ?>
<script>
    var ExpenseSeverParams = {};
    $(function() {
        initDataTable('.table-business_expense_total ', window.location.href, [], [], ExpenseSeverParams);
    });
    window.addEventListener('load', function() {
        appValidateForm($('#buisness-type-form'), {
            name: 'required'
        }, manage_buisness_types);
        $('#type').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#type input[name="buisness_name"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function manage_buisness_types(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                if ($('body').hasClass('buisness') && typeof(response.id) != 'undefined') {
                    var ctype = $('#buisness_type');
                    ctype.find('option:first').after('<option value="' + response.id + '">' + response.name + '</option>');
                    ctype.selectpicker('val', response.id);
                    ctype.selectpicker('refresh');
                }
            }
            location.reload();
            $('#type').modal('hide');
        });
        return false;
    }

    function newcompany() {
        $('#type').modal('show');
        $('.edit-title').addClass('hide');
    }

    function editCompany(invoker, id, buisness_name) {
        var name = buisness_name;
        $('#additional').append(hidden_input('id', id));
        $('#type input[name="buisness_name"]').val(name);
        $('#type input[name="buisness_color"]').val($(invoker).data('color'));
        $('#type').modal('show');
        $('.add-title').addClass('hide');

    }

    function deleteCompany(companyId) {
        if (confirm('Are you sure you want to delete this company?')) {
            window.location.href = "<?php echo admin_url('cash_flow/cash_flow/delete_buisness/'); ?>" + companyId;
        }
    }
</script>
</body>
</html>