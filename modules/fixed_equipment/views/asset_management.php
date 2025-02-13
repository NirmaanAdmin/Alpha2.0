<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  .show_hide_columns {
    position: absolute;
    z-index: 999;
    left: 375px
  }

  .bulk-checkout {
    position: absolute;
    z-index: 999;
    left: 423px
  }
</style>
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
        if (has_permission('fixed_equipment_assets', '', 'create') || is_admin()) {  ?>
          <button class="btn btn-primary" onclick="add();"><?php echo _l('add'); ?></button>
          <a class="btn btn-warning mleft10" href="<?php echo admin_url('fixed_equipment/bulk_upload/asset'); ?>"><?php echo _l('fe_bulk_upload'); ?></a>
        <?php } ?>

        <?php if (
          is_admin() ||
          has_permission('fixed_equipment_setting_model', '', 'create') ||
          has_permission('fixed_equipment_assets', '', 'create') ||
          has_permission('fixed_equipment_licenses', '', 'create') ||
          has_permission('fixed_equipment_accessories', '', 'create') ||
          has_permission('fixed_equipment_consumables', '', 'create')
        ) { ?>

          <button class="btn btn-primary pull-right" onclick="add_model(); return false;"><?php echo _l('fe_add_model'); ?></button>
        <?php } ?>
        <div class="clearfix"></div>
        <br>


        <div class="row">
          <div class="col-md-3">
            <?php echo render_select('model_filter', $models, array('id', 'model_name'), 'fe_model'); ?>
          </div>

          <div class="col-md-3">
            <?php echo render_select('status_filter', $status_labels, array('id', 'name'), 'fe_status'); ?>
          </div>

          <div class="col-md-3">
            <?php echo render_select('supplier_filter', $suppliers, array('id', 'supplier_name'), 'fe_supplier'); ?>
          </div>

          <div class="col-md-3">
            <?php echo render_select('location_filter', $locations, array('id', 'location_name'), 'fe_default_location'); ?>
          </div>
        </div>

        <div class="clearfix"></div>
        <a href="#" onclick="bulk_print(); return false;" data-toggle="modal" data-table=".table-assets_management" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('fe_print_qrcode'); ?></a>
        <?php
        if (is_admin() || has_permission('fixed_equipment_components', '', 'delete')) {
        ?>
          <a href="#" onclick="bulk_delete(); return false;" data-toggle="modal" data-table=".table-assets_management" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('fe_bulk_delete'); ?></a>
        <?php } ?>
        <div class="btn-group show_hide_columns" id="show_hide_columns">
          <!-- Settings Icon -->
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 4px 7px;">
            <i class="fa fa-cog"></i> <?php  ?> <span class="caret"></span>
          </button>
          <!-- Dropdown Menu with Checkboxes -->
          <div class="dropdown-menu" style="padding: 10px; min-width: 250px;">
            <!-- Select All / Deselect All -->
            <div>
              <input type="checkbox" id="select-all-columns"> <strong><?php echo _l('select_all'); ?></strong>
            </div>
            <hr>
            <!-- Column Checkboxes -->
            <?php
            $columns = [
              _l('chcekbox'),
              _l('fe_checkin_checkout'),
              _l('id'),
              _l('fe_asset_name'),
              _l('fe_image'),
              _l('fe_serial'),
              _l('fe_model'),
              _l('fe_model_no'),
              _l('fe_category'),
              _l('fe_status'),
              _l('fe_checkout_to'),
              _l('fe_location'),
              _l('fe_default_location'),
              _l('fe_manufacturer'),
              _l('fe_supplier'),
              _l('fe_purchase_date'),
              _l('fe_purchase_cost'),
              _l('fe_order_number'),
              _l('fe_warranty'),
              _l('fe_warranty_expires'),
              _l('fe_notes'),
              _l('fe_checkouts'),
              _l('fe_checkins'),
              _l('fe_requests'),
              _l('fe_created_at'),
              _l('fe_updated_at'),
              _l('fe_checkout_date'),
              _l('fe_expected_checkin_date'),
              _l('fe_last_audit'),
              _l('fe_next_audit_date'),
            ];
            ?>
            <div>
              <?php foreach ($columns as $key => $label): ?>
                <input type="checkbox" class="toggle-column" value="<?php echo $key; ?>" checked>
                <?php echo $label; ?><br>
              <?php endforeach; ?>
            </div>

          </div>
        </div>
        <div class="btn btn-default bulk-checkout" id="bulk-checkout" style="padding:4px 10px;">
          <a href="#" onclick="bulk_checkout(); return false;" class=" bulk-actions-btn table-btn" style="color: #000000;">
            <?php echo _l('fe_bulk_checkout'); ?>
          </a>
        </div>
        <table class="table table-assets_management scroll-responsive">
          <thead>
            <tr>
              <th><input type="checkbox" id="mass_select_all" data-to-table="checkout_managements"></th>
              <th><?php echo  _l('fe_checkin_checkout'); ?></th>
              <th>ID</th>
              <th><?php echo  _l('fe_asset_name'); ?></th>
              <th><?php echo  _l('fe_image'); ?></th>
              <th><?php echo  _l('fe_serial'); ?></th>
              <th><?php echo  _l('fe_model'); ?></th>
              <th><?php echo  _l('fe_model_no'); ?></th>
              <th><?php echo  _l('fe_category'); ?></th>
              <th><?php echo  _l('fe_status'); ?></th>
              <th><?php echo  _l('fe_checkout_to'); ?></th>
              <th><?php echo  _l('fe_location'); ?></th>
              <th><?php echo  _l('fe_default_location'); ?></th>
              <th><?php echo  _l('fe_manufacturer'); ?></th>
              <th><?php echo  _l('fe_supplier'); ?></th>
              <th><?php echo  _l('fe_purchase_date'); ?></th>
              <th><?php echo  _l('fe_purchase_cost'); ?></th>
              <th><?php echo  _l('fe_order_number'); ?></th>
              <th><?php echo  _l('fe_warranty'); ?></th>
              <th><?php echo  _l('fe_warranty_expires'); ?></th>
              <th><?php echo  _l('fe_notes'); ?></th>
              <th><?php echo  _l('fe_checkouts'); ?></th>
              <th><?php echo  _l('fe_checkins'); ?></th>
              <th><?php echo  _l('fe_requests'); ?></th>
              <th><?php echo  _l('fe_created_at'); ?></th>
              <th><?php echo  _l('fe_updated_at'); ?></th>
              <th><?php echo  _l('fe_checkout_date'); ?></th>
              <th><?php echo  _l('fe_expected_checkin_date'); ?></th>
              <th><?php echo  _l('fe_last_audit'); ?></th>
              <th><?php echo  _l('fe_next_audit_date'); ?></th>
              <?php
              if (is_admin() || has_permission('fixed_equipment_assets', '', 'create')) {
              ?>

              <?php } ?>
            </tr>
          </thead>
          <tbody></tbody>
        </table>



      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="add_new_assets" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="edit-title hide"><?php echo _l('fe_edit_asset'); ?></span>
          <span class="add-title"><?php echo _l('fe_add_asset'); ?></span>
        </h4>
      </div>
      <?php echo form_open_multipart(admin_url('fixed_equipment/add_assets'), array('id' => 'assets-form', 'onsubmit' => 'return validateForm()')); ?>
      <div class="modal-body">
        <?php $this->load->view('includes/new_asset_modal'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="bulk_checkout_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><?php echo _l('fe_bulk_checkout'); ?></h4>
      </div>
      <?php echo form_open(admin_url('fixed_equipment/bulk_checkout'), array('id' => 'bulk_checkout_form')); ?>
      <input type="hidden" name="item_ids" id="item_ids" value="">
      <input type="hidden" name="type" value="checkout">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <!-- Display aggregated models -->
            <div class="form-group">
              <label for="bulk_models"><?php echo _l('fe_model'); ?></label>
              <input type="text" class="form-control" id="bulk_models" name="bulk_models" readonly>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <!-- Display aggregated asset names -->
            <div class="form-group">
              <label for="bulk_asset_names"><?php echo _l('fe_asset_name'); ?></label>
              <input type="text" class="form-control" id="bulk_asset_names" name="bulk_asset_names" readonly>
            </div>
          </div>
        </div>
        <!-- Additional checkout fields (similar to your check out modal) -->
        <div class="row">
          <div class="col-md-12">
            <?php echo render_select('status', $status_label_checkout, array('id', 'name'), 'fe_status'); ?>
          </div>
        </div>
        <div class="row mbot15">
          <div class="col-md-12">
            <label for="location" class="control-label"><?php echo _l('fe_checkout_to'); ?></label>
          </div>
          <div class="col-md-12">
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to_bulk" id="checkout_to_user_bulk" value="user" checked>
                <label for="checkout_to_user_bulk"><?php echo _l('fe_staffs'); ?></label>
              </div>
            </div>
            <!-- Uncomment if needed -->
            <!--
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to_bulk" id="checkout_to_customer_bulk" value="customer">
                <label for="checkout_to_customer_bulk"><?php echo _l('fe_customer'); ?></label>
              </div>
            </div>
            -->
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to_bulk" id="checkout_to_asset_bulk" value="asset">
                <label for="checkout_to_asset_bulk"><?php echo _l('fe_asset'); ?></label>
              </div>
            </div>
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to_bulk" id="checkout_to_location_bulk" value="location">
                <label for="checkout_to_location_bulk"><?php echo _l('fe_location'); ?></label>
              </div>
            </div>
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to_bulk" id="checkout_to_project_bulk" value="project">
                <label for="checkout_to_project_bulk"><?php echo _l('fe_project'); ?></label>
              </div>
            </div>
          </div>
        </div>

        <!-- Conditional fields that are shown based on the selected option -->
        <div class="row">
          <div class="col-md-12 checkout_bulk_to_fr checkout_to_location_fr hide">
            <?php echo render_select('location_id', $locations, array('id', 'location_name'), 'fe_location'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_bulk_to_fr checkout_to_asset_fr hide">
            <?php echo render_select('asset_id', $assets, array('id', array('series', 'assets_name')), 'fe_asset'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_bulk_to_fr checkout_to_customer_fr hide">
            <?php echo render_select('customer_id', $customers, array('userid', 'company'), 'fe_customer'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_bulk_to_fr checkout_to_bulk_staff_fr">
            <?php echo render_select('staff_id', $staffs, array('staffid', array('firstname', 'lastname')), 'fe_staff'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_bulk_to_fr checkout_to_project_fr">
            <?php echo render_select('project_id', $projects, array('id', array('name', 'project_created')), 'fe_project'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_date_input('checkin_date', 'fe_checkout_date'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_date_input('expected_checkin_date', 'fe_expected_checkin_date'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_textarea('notes', 'fe_notes'); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('fe_checkout'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" id="check_in" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"></span>
        </h4>
      </div>
      <?php echo form_open(admin_url('fixed_equipment/check_in_assets'), array('id' => 'check_in_assets-form')); ?>
      <div class="modal-body">
        <input type="hidden" name="item_id" value="">
        <input type="hidden" name="type" value="checkin">
        <div class="row">
          <div class="col-md-12">
            <?php echo render_input('model', 'fe_model', '', 'text', array('readonly' => true)); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_input('asset_name', 'fe_asset_name'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_select('status', $status_labels, array('id', 'name'), 'fe_status'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_select('location_id', $locations, array('id', 'location_name'), 'fe_locations'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_date_input('checkin_date', 'fe_checkin_date'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_textarea('notes', 'fe_notes'); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('fe_checkin'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="check_out" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"></span>
        </h4>
      </div>
      <?php echo form_open(admin_url('fixed_equipment/check_in_assets'), array('id' => 'check_out_assets-form')); ?>
      <div class="modal-body">
        <input type="hidden" name="item_id" value="">
        <input type="hidden" name="type" value="checkout">
        <div class="row">
          <div class="col-md-12">
            <?php echo render_input('model', 'fe_model', '', 'text', array('readonly' => true)); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_input('asset_name', 'fe_asset_name'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_select('status', $status_label_checkout, array('id', 'name'), 'fe_status'); ?>
          </div>
        </div>

        <div class="row mbot15">
          <div class="col-md-12">
            <label for="location" class="control-label"><?php echo _l('fe_checkout_to'); ?></label>
          </div>
          <div class="col-md-12">

            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to" id="checkout_to_user" value="user" checked>
                <label for="checkout_to_user"><?php echo _l('fe_staffs'); ?></label>
              </div>
            </div>
            <!-- <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to" id="checkout_to_customer" value="customer">
                <label for="checkout_to_customer"><?php echo _l('fe_customer'); ?></label>
              </div>
            </div> -->
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to" id="checkout_to_asset" value="asset">
                <label for="checkout_to_asset"><?php echo _l('fe_asset'); ?></label>
              </div>
            </div>
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to" id="checkout_to_location" value="location">
                <label for="checkout_to_location"><?php echo _l('fe_location'); ?></label>
              </div>
            </div>
            <div class="pull-left">
              <div class="checkbox">
                <input type="radio" name="checkout_to" id="checkout_to_project" value="project">
                <label for="checkout_to_project"><?php echo _l('fe_project'); ?></label>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 checkout_to_fr checkout_to_location_fr hide">
            <?php echo render_select('location_id', $locations, array('id', 'location_name'), 'fe_location'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_to_fr checkout_to_asset_fr hide">
            <?php echo render_select('asset_id', $assets, array('id', array('series', 'assets_name')), 'fe_asset'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_to_fr checkout_to_customer_fr hide">
            <?php echo render_select('customer_id', $customers, array('userid', 'company'), 'fe_customer'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_to_fr checkout_to_staff_fr">
            <?php echo render_select('staff_id', $staffs, array('staffid', array('firstname', 'lastname')), 'fe_staff'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 checkout_to_fr checkout_to_project_fr">
            <?php echo render_select('project_id', $projects, array('id', array('name', 'project_created')), 'fe_project'); ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <?php echo render_date_input('checkin_date', 'fe_checkout_date'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_date_input('expected_checkin_date', 'fe_expected_checkin_date'); ?>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <?php echo render_textarea('notes', 'fe_notes'); ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('fe_checkout'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<input type="hidden" name="check">
<div class="modal fade" id="add" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('fe_add_model'); ?></span>
          <span class="edit-title hide"><?php echo _l('fe_edit_model'); ?></span>
        </h4>
      </div>
      <?php echo form_open_multipart(admin_url('fixed_equipment/add_models'), array('id' => 'form_models')); ?>
      <div class="modal-body content">
        <input type="hidden" name="assets" value="1">
        <?php $this->load->view('settings/includes/models_modal_content'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
  $(document).ready(function() {
    var table = $('.table-assets_management').DataTable();

    // Handle "Select All" checkbox
    $('#select-all-columns').on('change', function() {
      var isChecked = $(this).is(':checked');
      $('.toggle-column').prop('checked', isChecked).trigger('change');
    });

    // Handle individual column visibility toggling
    $('.toggle-column').on('change', function() {
      var column = table.column($(this).val());
      column.visible($(this).is(':checked'));

      // Sync "Select All" checkbox state
      var allChecked = $('.toggle-column').length === $('.toggle-column:checked').length;
      $('#select-all-columns').prop('checked', allChecked);
    });

    // Sync checkboxes with column visibility on page load
    table.columns().every(function(index) {
      var column = this;
      $('.toggle-column[value="' + index + '"]').prop('checked', column.visible());
    });

    // Prevent dropdown from closing when clicking inside
    $('.dropdown-menu').on('click', function(e) {
      e.stopPropagation();
    });
  });
</script>
<?php
require('modules/fixed_equipment/assets/js/asset_management_js.php');

?>
</body>

</html>