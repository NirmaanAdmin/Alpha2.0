<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
<div id="wrapper">
	<div class="content">
		<div class="row panel_s">
			<div class="panel-body">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-12">
							<h4 class="mtop15 pull-left">
								<?php echo _l('fe_assign_asset').' - '.$title; ?>
							</h4>
							<a href="<?php echo admin_url('fixed_equipment/detail_predefined_kits/'.$id); ?>" class="btn btn-default pull-right mtop5"><?php echo _l('fe_back'); ?></a>
						</div>
					</div>
					<hr>
					<?php 
					if(is_admin() || has_permission('fixed_equipment_predefined_kits', '', 'create')){
						?>
						<button class="btn btn-primary" onclick="add();"><?php echo _l('add'); ?></button>
						<div class="clearfix"></div>
						<br>
						<div class="clearfix"></div>
					<?php } ?>
					<table class="table table-model_predefined_kits scroll-responsive">
						<thead>
							<tr>
								<th>ID</th>
								<th><?php echo  _l('fe_kit_name'); ?></th>
								<th><?php echo  _l('fe_detail'); ?></th>
								<th><?php echo  _l('fe_date_created'); ?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="assign_asset" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="add-title"><?php echo _l('fe_assign_asset'); ?></span>
					<span class="edit-title hide"><?php echo _l('fe_edit_assign_asset'); ?></span>
				</h4>
			</div>
			<?php echo form_open_multipart(admin_url('fixed_equipment/assign_asset_predefined_kits'),array('id'=>'model_predefined_kits-form', 'onsubmit'=>'return validateForm()')); ?>
			<div class="modal-body">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="parent_id" value="<?php echo fe_htmldecode($id); ?>">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('name', 'fe_kit_name',''); ?>
					</div>
				</div>

                <div class="row">
                    <div class="col-md-12" id="assign-content">
                        <?php $this->load->view('predefined_kits/includes/assign_asset_modal_content.php', ['id' => $id]); ?>
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

<?php init_tail(); ?>
</body>
</html>
