<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="panel_s">
					<div class="panel-body">
						<?php echo form_hidden('internal_id', $internal_id); ?>
						<div class="row">
							<div class="col-md-12 ">
								<h4 class="no-margin font-bold"><i class="fa fa-shopping-basket" aria-hidden="true"></i> <?php echo _l($title); ?></h4>
								<hr />
							</div>
						</div>
						<div class="row">
							<div class="_buttons col-md-3">

								<?php if (has_permission('warehouse', '', 'create') || is_admin()) { ?>
									<a href="<?php echo admin_url('warehouse/add_update_internal_delivery'); ?>" class="btn btn-info pull-left mright10 display-block">
										<?php echo _l('_new'); ?>
									</a>
								<?php } ?>


							</div>
							<div class="col-md-1 pull-right">
								<a href="#" class="btn btn-default pull-right btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view_proposal('.internal_delivery_sm','#internal_delivery_sm_view'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
							</div>
						</div>

						<br />
						<div class="row all_ot_filters">
							<hr style="margin-top: 0px !important;">
							<?php
							$module_name = 'internal_delivery_note';
							$staff_filter = get_module_filter($module_name, 'staff_id');
							$staff_filter_val = !empty($staff_filter) ? explode(",", $staff_filter->filter_value) : [];
							?>
							<div class="col-md-3 form-group">
								<label for="staff_id"><?php echo _l('staff'); ?></label>
								<select name="staff_id[]" id="staff_id" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
									<?php foreach ($staff_list as $s) { ?>
										<option value="<?php echo pur_html_entity_decode($s['staffid']); ?>"
											<?php if (in_array($s['staffid'], $staff_filter_val)) {
												echo 'selected';
											} ?>>
											<?php echo pur_html_entity_decode($s['firstname'] . ' ' . $s['lastname']); ?>
										</option>
									<?php } ?>
								</select>
							</div>
							<?php
							$project_filter = get_module_filter($module_name, 'project');
							$project_filter_val = !empty($project_filter) ? explode(",", $project_filter->filter_value) : [];
							?>

							<div class="col-md-3 form-group">
								<label for="project"><?php echo _l('project'); ?></label>
								<select name="project[]" id="project" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
									<?php foreach ($projects as $project) { ?>
										<option value="<?php echo pur_html_entity_decode($project['id']); ?>"
											<?php if (in_array($project['id'], $project_filter_val)) {
												echo 'selected';
											} ?>>
											<?php echo pur_html_entity_decode($project['name']); ?>
										</option>
									<?php } ?>
								</select>
							</div>

							<?php
							$status_filter = get_module_filter($module_name, 'status');
							$status_filter_val = !empty($status_filter) ? explode(",", $status_filter->filter_value) : [];
							$status = [
								'0' => _l('not_yet_approve'),
								'1' => _l('approved'),
							];
							?>
							<div class="col-md-3 form-group">
								<label for="status"><?php echo _l('status'); ?></label>
								<select name="status[]" id="status" class="selectpicker" data-live-search="true" multiple="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" data-actions-box="true">
									<?php foreach ($status as $key => $value) { ?>
										<option value="<?php echo $key; ?>"
											<?php if (in_array($key, $status_filter_val)) {
												echo 'selected';
											} ?>>
											<?php echo $value; ?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-md-1 form-group " style="margin-top: 22px;">
								<a href="javascript:void(0)" class="btn btn-info btn-icon reset_all_ot_filters">
									<?php echo _l('reset_filter'); ?>
								</a>
							</div>
						</div>
						<?php render_datatable(array(
							_l('internal_delivery_note'),
							_l('deliver_name'),
							_l('addedfrom'),
							_l('datecreated'),
							// _l('total_amount'),
							_l('status_label')
						), 'table_internal_delivery', ['internal_delivery_sm' => 'internal_delivery_sm']); ?>


					</div>
				</div>
			</div>
			<div class="col-md-7 small-table-right-col">
				<div id="internal_delivery_sm_view" class="hide">
				</div>
			</div>

		</div>
	</div>
</div>
<script>
	var hidden_columns = [];
</script>
<?php init_tail(); ?>
<?php require 'modules/warehouse/assets/js/manage_internal_delivery_js.php'; ?>
</body>

</html>