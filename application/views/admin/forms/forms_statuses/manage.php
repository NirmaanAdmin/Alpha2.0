<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="#" onclick="new_status(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_form_status'); ?>
                    </a>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php if (count($statuses) > 0) { ?>
                        <table class="table dt-table">
                            <thead>
                                <th><?php echo _l('id'); ?></th>
                                <th><?php echo _l('form_statuses_dt_name'); ?></th>
                                <th><?php echo _l('options'); ?></th>
                            </thead>
                            <tbody>
                                <?php foreach ($statuses as $status) { ?>
                                <tr>
                                    <td><?php echo e($status['formstatusid']); ?></td>
                                    <td><a href="#"
                                            onclick="edit_status(this,<?php echo e($status['formstatusid']); ?>); return false"
                                            data-name="<?php echo e($status['name']); ?>"
                                            data-color="<?php echo e($status['statuscolor']); ?>"
                                            data-order="<?php echo e($status['statusorder']); ?>"><?php echo e($status['name']); ?></a><br />
                                        <?php echo _l('form_statuses_table_total', total_rows(db_prefix() . 'forms', ['status' => $status['formstatusid']])); ?>
                                    </td>
                                    <td>
                                        <div class="tw-flex tw-items-center tw-space-x-3">
                                            <a href="#"
                                                onclick="edit_status(this,<?php echo e($status['formstatusid']); ?>); return false"
                                                data-name="<?php echo e($status['name']); ?>"
                                                data-color="<?php echo e($status['statuscolor']); ?>"
                                                data-order="<?php echo e($status['statusorder']); ?>"
                                                class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                                                <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                            </a>
                                            <?php if ($status['isdefault'] == 0) { ?>
                                            <a href="<?php echo admin_url('forms/delete_form_status/' . $status['formstatusid']); ?>"
                                                class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                                <i class="fa-regular fa-trash-can fa-lg"></i>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <p class="no-margin"><?php echo _l('no_form_statuses_found'); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="form_status" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('forms/status')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('form_status_edit'); ?></span>
                    <span class="add-title"><?php echo _l('new_form_status'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'form_status_add_edit_name'); ?>
                        <?php echo render_color_picker('statuscolor', _l('form_status_add_edit_color')); ?>
                        <?php echo render_input('statusorder', 'form_status_add_edit_order', '', 'number'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
$(function() {
    appValidateForm($('form'), {
        name: 'required'
    }, manage_form_statuses);
    $('#form_status').on('hidden.bs.modal', function(event) {
        $('#additional').html('');
        $('#form_status input[name="name"]').val('');
        $('#form_status .colorpicker-input').colorpicker('setValue', '');
        $('#form_status input[name="statusorder"]').val('');
        $('.add-title').removeClass('hide');
        $('.edit-title').removeClass('hide');
    });
});

function manage_form_statuses(form) {
    var data = $(form).serialize();
    var url = form.action;
    $.post(url, data).done(function(response) {
        window.location.reload();
    });
    return false;
}

function new_status() {
    $('#form_status').modal('show');
    $('.edit-title').addClass('hide');
}

function edit_status(invoker, id) {
    var name = $(invoker).data('name');
    var color = $(invoker).data('color');
    var statusorder = $(invoker).data('order');
    $('#additional').append(hidden_input('id', id));
    $('#form_status input[name="name"]').val(name);
    $('#form_status .colorpicker-input').colorpicker('setValue', color);
    $('#form_status input[name="statusorder"]').val(statusorder);
    $('#form_status').modal('show');
    $('.add-title').addClass('hide');
}
</script>
</body>

</html>