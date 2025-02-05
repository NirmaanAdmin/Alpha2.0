<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
	.show_hide_columns {
		position: absolute;
		z-index: 9999;
		left: 278px
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row panel_s">
			<div class="panel-body">
				<div class="col-md-12">
					<h4 class="heading">
						<?php echo fe_htmldecode($title); ?>
					</h4>
					<hr>
					<div class="row">
						<div class="col-md-3">
							<?php
							if (is_admin() || has_permission('fixed_equipment_consumables', '', 'create')) {
							?>
								<button class="btn btn-primary" onclick="add();"><?php echo _l('add'); ?></button>
								<a class="btn btn-warning mleft10" href="<?php echo admin_url('fixed_equipment/bulk_upload/consumable'); ?>"><?php echo _l('fe_bulk_upload'); ?></a>
							<?php } ?>
							<button class="btn btn-primary" onclick="check_out(this);">Site</button>
						</div>

						<div class="col-md-3">
							<?php echo render_select('manufacturer_filter', $manufacturers, array('id', 'name'), 'fe_manufacturer'); ?>
						</div>

						<div class="col-md-3">
							<?php echo render_select('category_filter', $categories, array('id', 'category_name'), 'fe_categories'); ?>
						</div>

						<div class="col-md-3">
							<?php echo render_select('location_filter', $locations, array('id', 'location_name'), 'fe_location'); ?>
						</div>




					</div>
					<?php
					if (is_admin() || has_permission('fixed_equipment_components', '', 'delete')) {
					?>
						<a href="#" onclick="bulk_delete(); return false;" data-toggle="modal" data-table=".table-consumables" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('fe_bulk_delete'); ?></a>
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
								_l('checkbox'),
								_l('id'),
								_l('fe_image'),
								_l('fe_name'),
								_l('fe_category'),
								_l('fe_model_no'),
								_l('fe_manufacturer'),
								_l('fe_location'),
								_l('fe_total'),
								_l('fe_min_quantity'),
								_l('fe_avail'),
								_l('fe_purchase_cost'),
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
					<table class="table table-consumables scroll-responsive">
						<thead>
							<tr>
								<th><input type="checkbox" id="mass_select_all" data-to-table="checkout_managements"></th>
								<th>ID</th>
								<th><?php echo  _l('fe_image'); ?></th>
								<th><?php echo  _l('fe_name'); ?></th>
								<th><?php echo  _l('fe_category'); ?></th>
								<th><?php echo  _l('fe_model_no'); ?></th>
								<th><?php echo  _l('fe_manufacturer'); ?></th>
								<th><?php echo  _l('fe_location'); ?></th>
								<th><?php echo  _l('fe_total'); ?></th>
								<th><?php echo  _l('fe_min_quantity'); ?></th>
								<th><?php echo  _l('fe_avail'); ?></th>
								<th><?php echo  _l('fe_purchase_cost'); ?></th>
								<?php
								if (is_admin() || has_permission('fixed_equipment_consumables', '', 'create')) {
								?>
									<th><?php echo  _l('fe_checkin_checkout'); ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="add_new_consumables" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="edit-title hide"><?php echo _l('fe_edit_consumables'); ?></span>
					<span class="add-title"><?php echo _l('fe_add_consumables'); ?></span>
				</h4>
			</div>
			<?php echo form_open_multipart(admin_url('fixed_equipment/consumables'), array('id' => 'consumables-form', 'onsubmit' => 'return validateForm()')); ?>
			<div class="modal-body">
				<?php $this->load->view('includes/new_consumables_modal'); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
<div class="modal fade" id="check_out" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<!-- <span class="add-title"></span> -->
					Check Out
				</h4>
			</div>
			<?php echo form_open(admin_url('fixed_equipment/check_in_consumables'), array('id' => 'check_out_assets-form')); ?>
			<div class="modal-body">
				<input type="hidden" name="item_id" value="">
				<input type="hidden" name="type" value="checkout">
				<input type="hidden" name="item_type" value="consumable">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('model', 'fe_model', '', 'text', array('readonly' => true)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('asset_name', 'fe_consumables_name'); ?>
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
					<div class="col-md-6">
						<?php echo render_input('consumable_quantity', 'fe_quantity', '', 'number'); ?>
					</div>
					<div class="col-md-6">
						<?php echo render_input('avl_quantity', 'fe_avl_quantity', '', 'number', ['readonly' => true]); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_date_input('checkin_date', 'fe_checkout_date'); ?>
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
<!-- <div class="modal fade" id="check_out" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="add-title"><?php echo _l('fe_checkout'); ?></span>
				</h4>
			</div>
			<?php echo form_open(admin_url('fixed_equipment/check_in_consumables'), array('id' => 'check_out_consumables-form')); ?>
			<div class="modal-body">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="item_id" value="">
				<input type="hidden" name="type" value="checkout">
				<input type="hidden" name="status" value="2">		
				<input type="hidden" name="checkout_to" value="user">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('asset_name', 'fe_accessory_name', '', 'text', array('readonly' => true)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('staff_id', $staffs, array('staffid', array('firstname', 'lastname')), 'fe_staffs'); ?>
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
</div> -->

<!-- /.modal -->
<input type="hidden" name="check">
<input type="hidden" name="are_you_sure_you_want_to_delete_these_items" value="<?php echo _l('fe_are_you_sure_you_want_to_delete_these_items') ?>">
<input type="hidden" name="please_select_at_least_one_item_from_the_list" value="<?php echo _l('please_select_at_least_one_item_from_the_list') ?>">

<?php init_tail();

require('modules/fixed_equipment/assets/js/consumables_js.php');
?>
<script>
	$(document).ready(function() {
		var table = $('.table-consumables').DataTable();

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
</body>

</html>