<button class="btn btn-primary pull-right" onclick="add();"><?php echo _l('add'); ?></button>
<div class="clearfix"></div>
<br>
<div class="clearfix"></div>
<table class="table table-customfield scroll-responsive">
	<thead>
		<tr>
			<th><?php echo  _l('dmg_title'); ?></th>
			<th><?php echo  _l('dmg_field_type'); ?></th>
			<th><?php echo  _l('dmg_options'); ?></th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

<div class="modal fade" id="add" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					<span class="add-title"><?php echo _l('dmg_new_custom_field'); ?></span>
					<span class="edit-title hide"><?php echo _l('dmg_edit_custom_field'); ?></span>
				</h4>
			</div>
			<?php echo form_open(admin_url('drawing_management/add_custom_field'),array('id'=>'add_custom_field-form')); ?>
			<div class="modal-body">
				<input type="hidden" name="id" value="">
				<div class="row">
					<div class="col-md-12">
						<?php echo render_input('title', 'dmg_title', ''); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php
						$type_option = [
							['id' => 'textfield', 'label' => _l('dmg_textfield')],
							['id' => 'numberfield', 'label' => _l('dmg_numberfield')],
							['id' => 'textarea', 'label' => _l('dmg_textarea')],
							['id' => 'select', 'label' => _l('dmg_select')],
							['id' => 'multi_select', 'label' => _l('dmg_multi_select')],
							['id' => 'checkbox', 'label' => _l('dmg_checkbox')],
							['id' => 'radio_button', 'label' => _l('dmg_radio_button')]
						];
						echo render_select('type', $type_option, array('id', 'label'), 'dmg_field_type'); ?>
					</div>
				</div>
				<div class="list-option hide">
					<div class="row">
						<div class="col-md-10">
							<?php echo render_input('option[]','','','text',array('placeholder' => _l('dmg_option'))); ?>
						</div>
						<div class="col-md-2">
							<button type="button" class="btn btn-success add_new_row">
								<i class="fa fa-plus"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('dmg_submit'); ?></button>
			</div>
			<?php echo form_close(); ?>                 
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->



