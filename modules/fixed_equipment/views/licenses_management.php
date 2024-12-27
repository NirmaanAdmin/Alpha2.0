<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
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
							if(is_admin() || has_permission('fixed_equipment_licenses', '', 'create')){
								?>
								<button class="btn btn-primary" onclick="add();"><?php echo _l('add'); ?></button>
								<a class="btn btn-warning mleft10" href="<?php echo admin_url('fixed_equipment/bulk_upload/license'); ?>"><?php echo _l('fe_bulk_upload'); ?></a>
							<?php } ?>
						</div>

						<div class="col-md-3">
						</div>

						<div class="col-md-3">
						</div>

						<div class="col-md-3">
							<?php echo render_select('manufacturer_filter', $manufacturers, array('id', 'name'), 'fe_manufacturer'); ?>
						</div>
					</div>
					<?php 
					if(is_admin() || has_permission('fixed_equipment_components', '', 'delete')){
						?>
						<a href="#" onclick="bulk_delete(); return false;"  data-toggle="modal" data-table=".table-licenses" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('fe_bulk_delete'); ?></a> 
					<?php } ?>
					<table class="table table-licenses scroll-responsive">
						<thead>
							<tr>
								<th><input type="checkbox" id="mass_select_all" data-to-table="checkout_managements"></th>
								<th>ID</th>
								<th><?php echo  _l('fe_license'); ?></th>
								<th><?php echo  _l('fe_product_key'); ?></th>
								<th><?php echo  _l('fe_expiration_date'); ?></th>
								<th><?php echo  _l('fe_licensed_to_email'); ?></th>
								<th><?php echo  _l('fe_licensed_to_name'); ?></th>
								<th><?php echo  _l('fe_manufacturer'); ?></th>
								<th><?php echo  _l('fe_total').'('._l('fe_seats').')'; ?></th>
								<th><?php echo  _l('fe_avail').'('._l('fe_seats').')'; ?></th>
								<th><?php echo  _l('fe_inventory_qty'); ?></th>
								<?php 
								if(is_admin() || has_permission('fixed_equipment_licenses', '', 'create')){
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

<div class="modal fade" id="add_new_licenses" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="edit-title hide"><?php echo _l('fe_edit_license'); ?></span>
					<span class="add-title"><?php echo _l('fe_add_license'); ?></span>
				</h4>
			</div>
			<?php echo form_open_multipart(admin_url('fixed_equipment/licenses'),array('id'=>'licenses-form', 'onsubmit'=>'return validateForm()')); ?>
			<div class="modal-body">
				<input type="hidden" value="license" name="type">
				<input type="hidden" name="id">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('assets_name', 'fe_software_name') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('category_id', $categories, array('id','category_name'), 'fe_category_name') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_textarea('product_key', 'fe_product_key') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('seats', 'fe_seats', '', 'number') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('manufacturer_id', $manufacturers, array('id','name'), 'fe_manufacturer') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<?php echo render_input('licensed_to_name', 'fe_licensed_to_name') ?>
					</div>
					<div class="col-md-6">
						<?php echo render_input('licensed_to_email', 'fe_licensed_to_email') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 ptop10">
						<div class="checkbox checkbox-inline checkbox-primary">
							<input type="checkbox" name="reassignable" id="reassignable" value="1">
							<label for="reassignable"><?php echo _l('fe_reassignable'); ?></label>
						</div> 
						<br>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('supplier_id', $suppliers, array('id','supplier_name'), 'fe_supplier') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<?php echo render_input('order_number', 'fe_order_number') ?>
					</div>
					<div class="col-md-6">
						<?php echo render_input('purchase_order_number', 'fe_purchase_order_number') ?>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="gst"><?php echo _l('fe_purchase_cost'); ?></label>            
							<div class="input-group">
								<input type="text" class="form-control" data-type="currency" name="unit_price" value="">
								<span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<?php echo render_date_input('date_buy', 'fe_purchase_date') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<?php echo render_date_input('expiration_date', 'fe_expiration_date') ?>
					</div>
					<div class="col-md-6">
						<?php echo render_date_input('termination_date', 'fe_termination_date') ?>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('depreciation', $depreciations, array('id','name'), 'fe_depreciation') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 ptop10">
						<div class="checkbox checkbox-inline checkbox-primary">
							<input type="checkbox" name="maintained" id="maintained" value="1">
							<label for="maintained"><?php echo _l('fe_maintained'); ?></label>
						</div> 
						<br>
						<br>
					</div>
					<div class="col-md-4 ptop10">
						<div class="checkbox checkbox-inline checkbox-primary">
							<input type="checkbox" name="for_sell" id="for_sell" value="1">
							<label for="for_sell"><?php echo _l('fe_for_sell'); ?></label>
						</div> 
						<br>
						<br>
					</div>
					<div class="col-md-6 for_sell_fr hide">
						<div class="form-group">
							<label for="selling_price"><?php echo _l('fe_selling_price'); ?></label>            
							<div class="input-group">
								<input data-type="currency" type="text" class="form-control" name="selling_price" value="">
								<span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
							</div>
						</div>
					</div>
					
				</div>



				<div class="row">
					<div class="col-md-12">
						<?php echo render_textarea('description', 'fe_notes') ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
			<?php echo form_close(); ?>                 
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" id="check_in" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="add-title"><?php echo _l('fe_checkin'); ?></span>
				</h4>
			</div>
			<?php echo form_open(admin_url('fixed_equipment/check_in_license'),array('id'=>'check_in_assets-form')); ?>
			<div class="modal-body">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="item_id" value="">
				<input type="hidden" name="type" value="checkin">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('asset_name','fe_software_name', '', 'text', array('readonly' => true)); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_textarea('notes','fe_notes'); ?>
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
					<span class="add-title"><?php echo _l('fe_checkout'); ?></span>
				</h4>
			</div>
			<?php echo form_open(admin_url('fixed_equipment/check_in_license'),array('id'=>'check_out_license-form')); ?>
			<div class="modal-body">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="item_id" value="">
				<input type="hidden" name="type" value="checkout">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('asset_name','fe_software_name', '', 'text', array('readonly' => true)); ?>
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
						<div class="pull-left">
							<div class="checkbox">
								<input type="radio" name="checkout_to" id="checkout_to_asset" value="asset">
								<label for="checkout_to_asset"><?php echo _l('fe_asset'); ?></label>
							</div>  
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 checkout_to_fr checkout_to_asset_fr hide">
						<?php echo render_select('asset_id', $assets, array('id',array('series', 'assets_name')), 'fe_assets'); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 checkout_to_fr checkout_to_staff_fr">
						<?php echo render_select('staff_id', $staffs, array('staffid', array('firstname', 'lastname')), 'fe_staffs'); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo render_textarea('notes','fe_notes'); ?>
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
<input type="hidden" name="are_you_sure_you_want_to_delete_these_items" value="<?php echo _l('fe_are_you_sure_you_want_to_delete_these_items') ?>">
<input type="hidden" name="please_select_at_least_one_item_from_the_list" value="<?php echo _l('please_select_at_least_one_item_from_the_list') ?>">

<input type="hidden" name="check">
<?php init_tail(); ?>
</body>
</html>
