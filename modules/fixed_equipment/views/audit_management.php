<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'fe_audit'; ?>
<div id="wrapper">
  <div class="content">
    <div class="row panel">
      <div class="col-md-12">
        <h4>
          <br>
          <?php echo fe_htmldecode($title); ?>
          <hr>          
        </h4>
        <?php 
        if(is_admin() || has_permission('fixed_equipment_audit', '', 'create')){
         ?>
         <a href="<?php echo admin_url('fixed_equipment/audit_request'); ?>" class="btn btn-primary" onclick="add();"><?php echo _l('add'); ?></a>
         <div class="clearfix"></div>
         <hr>
       <?php } ?>
       <div class="row all_filters">
        <?php 
        if(has_permission('fixed_equipment_audit', '', 'view') || is_admin()){ ?>
          <div class="col-md-3">
            <?php
            $auditor = get_module_filter($module_name, 'auditor');
            $auditor_filter_val = !empty($auditor) ? explode(",", $auditor->filter_value) : [];
            echo render_select('auditor_filter[]', $staffs, array('staffid', array('firstname', 'lastname')), 'fe_auditor', $auditor_filter_val, array('multiple' => true, 'data-actions-box' => true),[],'','',false);
            ?>
          </div>
        <?php } ?>

        <div class="col-md-3">
          <?php
          $status = get_module_filter($module_name, 'status');
          $status_filter_val = !empty($status) ? $status->filter_value : '';
          $status_approve = [
            ['id' => 3, 'label' => _l('fe_new')],
            ['id' => 1, 'label' => _l('fe_approved')],
            ['id' => 2, 'label' => _l('fe_rejected')],
          ];
          echo render_select('status_filter', $status_approve, array('id', 'label'), 'fe_status', $status_filter_val); ?>
        </div>

        <div class="col-md-3">
          <?php
          $from_date = get_module_filter($module_name, 'from_date');
          $from_date_filter_val = '';
          if(!empty($from_date)) {
            if(!empty($from_date->filter_value)) {
              $from_date_filter_val = date('d-m-Y', strtotime($from_date->filter_value));
            }
          }
          echo render_date_input('audit_from_date_filter', 'fe_audit_from_date', $from_date_filter_val);
          ?>
        </div>

        <div class="col-md-3">
          <?php
          $to_date = get_module_filter($module_name, 'to_date');
          $to_date_filter_val = '';
          if(!empty($to_date)) {
            if(!empty($to_date->filter_value)) {
              $to_date_filter_val = date('d-m-Y', strtotime($to_date->filter_value));
            }
          }
          echo render_date_input('audit_to_date_filter', 'fe_audit_to_date', $to_date_filter_val);
          ?>
        </div>

        <div class="col-md-1 form-group reset-filter-wrapper">
          <a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_filters">
            <?php echo _l('reset_filter'); ?>
          </a>
        </div>
      </div>
      <hr>
      <div class="clearfix"></div>
      <?php 
      if(is_admin() || has_permission('fixed_equipment_audit', '', 'delete')){
        ?>
        <a href="#" onclick="bulk_delete(); return false;"  data-toggle="modal" data-table=".table-audit_management" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('fe_bulk_delete'); ?></a> 
      <?php } ?>
      <table class="table table-audit_management scroll-responsive">
       <thead>
         <tr>
          <th><input type="checkbox" id="mass_select_all" data-to-table="checkout_managements"></th>
          <th><?php echo  _l('fe_title'); ?></th>
          <th><?php echo  _l('fe_auditor'); ?></th>
          <th><?php echo  _l('fe_audit_date'); ?></th>
          <th><?php echo  _l('fe_status'); ?></th>
          <th><?php echo  _l('fe_created_at'); ?></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>



  </div>
</div>
</div>
</div>
<input type="hidden" name="are_you_sure_you_want_to_delete_these_items" value="<?php echo _l('fe_are_you_sure_you_want_to_delete_these_items') ?>">
<input type="hidden" name="please_select_at_least_one_item_from_the_list" value="<?php echo _l('please_select_at_least_one_item_from_the_list') ?>">
<input type="hidden" name="check">
<?php init_tail(); ?>
</body>
</html>
