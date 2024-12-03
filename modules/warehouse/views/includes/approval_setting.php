<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<?php if (has_permission('warehouse', '', 'create') || is_admin() ) { ?>
		<a href="#" class="btn btn-info pull-left" onclick="new_approval_setting(); return false;"><?php echo _l('new_approval_setting'); ?></a>
	<?php } ?>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('project'); ?></th>
		<th><?php echo _l('name'); ?></th>
		<th><?php echo _l('related'); ?></th>
		<th><?php echo _l('options'); ?></th>
	</thead>
	<tbody>
	<?php foreach($approval_setting as $key => $value){ ?>
		<tr>
		   <td><?php echo $key + 1; ?></td>
		   <td><?php echo get_project_name_by_id($value['project_id']); ?></td>
		   <td><?php echo pur_html_entity_decode($value['name']); ?></td>
		    <?php 
			$related ='';
			if($value['related'] == 1){
				$related = _l('stock_import');
			}elseif($value['related'] == 2){
				$related = _l('stock_export');

			}elseif($value['related'] == 3){
				$related = _l('loss_adjustment');
			}elseif($value['related'] == 4){
				$related = _l('internal_delivery_note');
			}elseif($value['related'] == 5){
				$related = _l('wh_packing_list');
			}elseif($value['related'] == 6){
				$related = _l('inventory_receipt_inventory_delivery_returns_goods');
			}
			?>
		   <td><?php echo html_entity_decode($related); ?></td>
		   <td>
		     <?php if (is_admin() || has_permission('warehouse', '', 'edit') ) { ?>
		     		<a href="#" onclick="edit_approval_setting(this,<?php echo pur_html_entity_decode($value['id']); ?>); return false" data-name="<?php echo pur_html_entity_decode($value['name']); ?>" data-related="<?php echo html_entity_decode($value['related']); ?>" data-project='<?php echo html_entity_decode($value['project_id']); ?>' data-approver='<?php echo html_entity_decode($value['approver']); ?>' class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>
		     	<?php } ?>

		     	<?php if (is_admin() || has_permission('warehouse', '', 'delete') ) { ?>
		      	<a href="<?php echo admin_url('warehouse/delete_approval_setting/'.$value['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
		      <?php } ?>
		   </td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<?php
$hr_record_status = 0; 
if(get_status_modules_pur('hr_profile') == true){
	$hr_record_status = 1;
} ?>

<div class="modal fade" id="approval_setting_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_approval_setting'); ?></span>
					<span class="add-title"><?php echo _l('new_approval_setting'); ?></span>
				</h4>
			</div>
			<?php echo form_open('warehouse/approval_setting',array('id'=>'approval-setting-form')); ?>
			<?php echo form_hidden('approval_setting_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_select('project_id', $projects, array('id','name'), 'project'); ?>
						<?php echo render_input('name','subject','','text'); ?>
						<?php $related = [ 
								0 => ['id' => '1', 'name' => _l('stock_import')],
								1 => ['id' => '2', 'name' => _l('stock_export')],
							]; ?>
						<?php echo render_select('related',$related,array('id','name'),'task_single_related'); ?>
						<div class="select-placeholder form-group">
							<label for="approver" class="control-label"><?php echo _l('approver'); ?></label>
							<select name="approver[]" id="approver" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple="true" data-actions-box="true">
	                        </select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>

