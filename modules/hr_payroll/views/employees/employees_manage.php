<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style type="text/css">
  .loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999;
  }

  .loader-gif {
    width: 100px;
    /* Adjust the size as needed */
    height: 100px;
  }
</style>
<div id="wrapper">
	<div class="content">
	<div class="loader-container hide" id="loader-container">
      <img src="<?php echo site_url('modules/hr_payroll/uploads/lodder/lodder.gif') ?>" alt="Loading..." class="loader-gif">
    </div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">

						<div class="row mb-5">
							<div class="col-md-12">
								<h4 class="no-margin"><?php echo _l('hrp_employees') ?> </h4>
							</div>
						</div>
						<br>
						<div class="row mb-4">   
							<div class="col-md-12">
								<!-- filter -->
								<div class="row filter_by">

									<div class="col-md-2">
										<?php echo render_input('month_employees','month',date('Y-m'), 'month'); ?>   
									</div>

									<div class="col-md-3 leads-filter-column pull-left">
										<?php echo render_select('department_employees',$departments,array('departmentid', 'name'),'department',''); ?>
									</div>

									<div class="col-md-3 leads-filter-column pull-left">
										<div class="form-group">
											<label for="role_employees" class="control-label"><?php echo _l('role'); ?></label>
											<select name="role_employees[]" class="form-control selectpicker" multiple="true" id="role_employees" data-actions-box="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true"> 
												<?php foreach ($roles as $key => $role) { ?>
													<option value="<?php echo new_html_entity_decode($role['roleid']); ?>" ><?php  echo new_html_entity_decode($role['name']); ?></option>
												<?php } ?>
											</select>
										</div>
									</div>

									<div class="col-md-3 leads-filter-column pull-left">

										<div class="form-group">
											<label for="staff_employees" class="control-label"><?php echo _l('staff'); ?></label>
											<select name="staff_employees[]" class="form-control selectpicker" multiple="true" id="staff_employees" data-actions-box="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true"> 
												<?php foreach ($staffs as $key => $staff) { ?>

													<option value="<?php echo new_html_entity_decode($staff['staffid']); ?>" ><?php  echo new_html_entity_decode($staff['firstname'].' '.$staff['lastname']); ?></option>
												<?php } ?>
											</select>
										</div>

									</div>

								</div>
								<!-- filter -->
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-12">
								<hr class="hr-color">
							</div>
						</div>

						<?php echo form_open(admin_url('hr_payroll/add_manage_employees'),array('id'=>'add_manage_employees')); ?>
						
						<div class="col-md-12">
							<small><?php echo _l('handsontable_scroll_horizontally') ?></small>
						</div>
						<div id="total_insurance_histtory" class="col-md-12">
							<div class="row">  
								<div id="hrp_employees_value" class="hot handsontable htColumnHeaders" >
								</div>
								<?php echo form_hidden('hrp_employees_value'); ?>
								<?php echo form_hidden('month', date('m-Y')); ?>
								<?php echo form_hidden('employees_fill_month'); ?>
								<?php echo form_hidden('department_employees_filter'); ?>
								<?php echo form_hidden('staff_employees_filter'); ?>
								<?php echo form_hidden('role_employees_filter'); ?>

								<!-- rel_type synchronization or update value -->
								<?php echo form_hidden('hrp_employees_rel_type'); ?>

							</div>
						</div>

						<div class="col-md-12">
							<div class="modal-footer">
								<?php if(has_permission('hrp_employee', '', 'create') || has_permission('hrp_employee', '', 'edit')){ ?>
									<button type="button" class="btn btn-info pull-right save_manage_employees mleft5 "><?php echo new_html_entity_decode($button_name); ?></button>
									<?php if(hrp_get_hr_profile_status() == 'hr_records'){ ?>
										
										<a href="#"class="btn btn-info pull-right display-block hrp_employees_synchronization" data-toggle="tooltip" title="<?php echo _l('synchronized_employees_title'); ?>"><?php echo _l('hrp_synchronized'); ?><i class=" pull-right fa fa-question-circle i_tooltip" ></i></a>
									<?php } ?>

									<a href="#" class=" btn mright5 btn-primary pull-right hrp_employees_copy" data-toggle="tooltip" title="<?php echo _l('copy_from_last_month'); ?>">
										<?php echo _l('hrp_copy'); ?>
									</a>
									<a href="<?php echo admin_url('hr_payroll/import_xlsx_employees'); ?>" class=" btn mright5 btn-default pull-right">
										<?php echo _l('hrp_import_excel'); ?>
									</a>
								<?php } ?>
								
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php echo form_close(); ?>

		</div>

	</div>
</div>


</div>
</div>
</div>


<?php init_tail(); ?>
<?php require 'modules/hr_payroll/assets/js/manage_employees/manage_employees_js.php'; ?>

</body>
</html>
