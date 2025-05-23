<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
  <div class="col-md-4">
    <label for="start_year_for_annual_leave_cycle" class="control-label"><?php echo _l('staff'); ?></label>
    <select name="leave_filter_staff[]" class="selectpicker" id="leave_filter_staff" onchange="filter_hanson();" data-width="100%" data-none-selected-text="" data-live-search="true" multiple>
      <?php
      foreach ($staff as $value) { ?>
        <option value="<?php echo html_entity_decode($value['staffid']); ?>"><?php echo html_entity_decode($value['staffid']) . ' # ' . $value['firstname'] . ' ' . $value['lastname']; ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-4">
    <label for="start_year_for_annual_leave_cycle" class="control-label"><?php echo _l('department'); ?></label>
    <select name="leave_filter_department[]" class="selectpicker" id="leave_filter_department" onchange="filter_hanson();" data-width="100%" data-none-selected-text="" data-live-search="true" multiple>
      <?php
      foreach ($department as $value) { ?>
        <option value="<?php echo html_entity_decode($value['departmentid']); ?>"><?php echo html_entity_decode($value['name']); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-4">
    <label for="start_year_for_annual_leave_cycle" class="control-label"><?php echo _l('role'); ?></label>
    <select name="leave_filter_roles[]" class="selectpicker" id="leave_filter_roles" onchange="filter_hanson();" data-width="100%" data-none-selected-text="" data-live-search="true" multiple>
      <?php
      foreach ($role as $value) { ?>
        <option value="<?php echo html_entity_decode($value['roleid']); ?>"><?php echo html_entity_decode($value['name']); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="clearfix"></div>
  <br>
  <div class="clearfix"></div>
  <div class="col-md-12">
    <?php echo form_open(admin_url('timesheets/set_leave'), array('id' => 'setting-leave-form')); ?>
    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <?php
          $type_of_leave_selected = 8;
          $data_type_of_leave = get_timesheets_option('type_of_leave_selected');
          if ($data_type_of_leave) {
            $type_of_leave_selected = $data_type_of_leave;
          }
          ?>
          <label for="type_of_leave" class="control-label"><?php echo _l('type_of_leave'); ?></label>
          <select name="type_of_leave" class="selectpicker" id="rel_type" data-width="100%" data-none-selected-text="<?php echo _l('none_type'); ?>" onchange="filter_hanson()">
            <!-- <option value="8" <?php echo ($type_of_leave_selected == 8 ? 'selected' : '') ?>><?php echo _l('annual_leave') ?></option> -->
            <!-- <option value="2" <?php echo ($type_of_leave_selected == 2 ? 'selected' : '') ?>><?php echo _l('maternity_leave') ?></option> -->
            <!-- <option value="4" <?php echo ($type_of_leave_selected == 4 ? 'selected' : '') ?>><?php echo _l('private_work_without_pay') ?></option> -->
            <option value="1" <?php echo ($type_of_leave_selected == 1 ? 'selected' : '') ?>><?php echo _l('sick_leave') ?>(SL)</option>
            <?php
            foreach ($type_of_leave as $value) { ?>
              <option value="<?php echo html_entity_decode($value['slug']); ?>" <?php echo ($type_of_leave_selected == $value['slug'] ? 'selected' : '') ?>><?php echo html_entity_decode($value['type_name']); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <?php
        $start_year_for_annual_leave_cycle = date('Y');
        $data_option = get_timesheets_option('start_year_for_annual_leave_cycle');
        if ($data_option) {
          $start_year_for_annual_leave_cycle = $data_option;
        }
        ?>
      </div>
      <div class="col-md-4"></div>
      <div class="col-md-8 font-italic">
        <div class="row">
          <div class="col-md-12">
            <button class="btn pull-right mtop15 handle_year" type="button" data-year="<?php echo ($start_year_for_annual_leave_cycle + 1); ?>">
              <i class="fa fa-arrow-right"></i>
            </button>
            <div class="alert alert-primary pull-right mtop7 no-mbot">
              <?php
              $date_data = $this->timesheets_model->get_date_leave($start_year_for_annual_leave_cycle);
              echo _d($date_data->from_date) . ' - ' . _d($date_data->ending_date);
              ?>
            </div>
            <button class="btn pull-right mtop15 handle_year" type="button" data-year="<?php echo ($start_year_for_annual_leave_cycle - 1); ?>">
              <i class="fa fa-arrow-left"></i>
            </button>

            <input type="hidden" name="start_year_for_annual_leave_cycle" value="<?php echo html_entity_decode($start_year_for_annual_leave_cycle); ?>">
            <input type="hidden" name="not_show_notify" value="0">

          </div>
        </div>
      </div>
    </div>
    <div class="clearfix"></div>
    <br>
    <div class="clearfix"></div>
    <?php echo form_hidden('leave_of_the_year_data'); ?>
    <div class="hot handsontable htColumnHeaders" id="example">
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="col-md-12 mtop5">
      <button class="btn btn-primary save_leave_table pull-right" onclick="get_data_hanson();"><?php echo _l('save'); ?></button>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>