<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="type" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('cash_flow/buisness_type'), ['id' => 'buisness-type-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('buisness_type_edit'); ?></span>
                    <span class="add-title"><?php echo _l('new_buisness_type'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('buisness_name', 'buisness_type_name'); ?>
                    </div>
                </div>
                <div class="form-group select-placeholder>">
                    <label for="assignees"><?php echo _l('task_single_assignees'); ?></label>
                    <select name="assignees[]" id="assignees" class="selectpicker" data-width="100%"
                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple
                        data-live-search="true">
                        <?php foreach ($staffs as $staff) { ?>
                            <option value="<?php echo ($staff['staffid']); ?>" <?php if ((get_option('new_task_auto_assign_current_member') == '1') && get_staff_user_id() == $staff['staffid']) {
                                   echo 'selected';
                               } ?>>
                                <?php echo ($staff['firstname'] . ' ' . $staff['lastname']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_color_picker('buisness_color', _l('buisness_color')); ?>
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