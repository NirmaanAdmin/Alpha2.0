<?php if(isset($item)){ 
	?>
	<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
	<?php init_head(); 
	?>
	<div id="wrapper">
		<div class="content">

			<div class="panel_s">
				<div class="panel-body">
					<div class="row">

						<div class="col-md-12">
							<h4 class="pull-left"><?php echo htmldecode($item->name); ?></h4>
							<?php if(!(strpos($item->name, '.xlsx') === false) || !(strpos($item->name, '.xls') === false)){ ?>
								<a href="<?php echo admin_url('document_management/preview?id='.$item->id) ?>" target="_blank" class="btn btn-default pull-right mleft5">
									<i class="fa fa-eye"></i> <?php echo _l('dmg_view_in_excel'); ?>
								</a>
							<?php } ?>

							<?php if(!(strpos($item->name, '.docx') === false) || !(strpos($item->name, '.doc') === false)){ ?>
								<a href="<?php echo admin_url('document_management/preview?id='.$item->id) ?>" target="_blank" class="btn btn-default pull-right mleft5">
									<i class="fa fa-eye"></i> <?php echo _l('dmg_view_in_word'); ?>
								</a>
							<?php } ?>

							<?php if(!(strpos($item->name, '.pdf') === false)){ ?>
								<a href="<?php echo admin_url('document_management/preview?id='.$item->id) ?>" target="_blank" class="btn btn-default pull-right mleft5">
									<i class="fa fa-eye"></i> <?php echo _l('dmg_view_pdf'); ?>
								</a>
							<?php } ?>

							<?php if(!(strpos($item->filetype, 'image') === false)){ ?>
								<a href="<?php echo admin_url('document_management/preview?id='.$item->id) ?>" target="_blank" class="btn btn-default pull-right mleft5">
									<i class="fa fa-eye"></i> <?php echo _l('dmg_view_image'); ?>
								</a>
							<?php } ?>
							<?php 
							$video_path = DOCUMENT_MANAGEMENT_MODULE_UPLOAD_FOLDER.'/files/'.$item->parent_id.'/'.$item->name;
							if(is_html5_video($video_path)){ ?>
								<a href="<?php echo admin_url('document_management/preview?id='.$item->id) ?>" target="_blank" class="btn btn-default pull-right mleft5">
									<i class="fa fa-eye"></i> <?php echo _l('dmg_view_video'); ?>
								</a>
							<?php } ?>
							<a class="btn btn-default pull-right" href="<?php echo site_url('modules/document_management/uploads/files/'.$item->parent_id.'/'.$item->name); ?>" download><i class="fa fa-download"></i> <?php echo _l('dmg_dowload'); ?></a>

							<div class="clearfix"></div>
							<hr>
						</div>
						<div class="col-md-12">
							<input type="hidden" name="id" value="<?php echo htmldecode($item->id); ?>">
							<input type="hidden" name="folder_id" value="<?php echo htmldecode($item->parent_id); ?>">

							<?php if($item->show_files_metadata == 1){ ?>
								<table class="table">

									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_tags'); ?></td>
										<td><?php 
										$tag_html = '';
										if(!($item->tag == '' && $item->tag == null)){
											$tag_arr = explode(',', $item->tag);
											foreach ($tag_arr as $key => $text) {
												$tag_html .= '<span class="badge badge-light mleft5">'.$text.'</span>';
											}
										}
										echo htmldecode($tag_html); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_signed_by'); ?></td>
										<td><?php 
										$signed_by_html = '';
										if(!($item->signed_by == '' && $item->signed_by == null)){
											$signed_by_arr = explode(',', $item->signed_by);
											foreach ($signed_by_arr as $key => $text) {
												$signed_by_html .= '<span class="badge badge-light mleft5">'.$text.'</span>';
											}
										}
										echo htmldecode($signed_by_html); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_date'); ?></td>
										<td><?php echo _dt($item->dateadded); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_due_date'); ?></td>
										<td><?php echo _dt($item->duedate); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_ocr_language'); ?></td>
										<td><?php echo ufirst($item->ocr_language); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_document_number'); ?></td>
										<td><?php echo ufirst($item->document_number); ?></td>
									</tr>
									<tr>
										<td class="text-nowrap"><?php echo _l('dmg_notes'); ?></td>
										<td><?php echo nlbr($item->note); ?></td>
									</tr>

									<?php 
									$data_custom_field = [];
									if(!($item->custom_field == '' || $item->custom_field == null)){
										$data_custom_field = json_decode($item->custom_field); 
										if(count($data_custom_field) > 0){
											foreach ($data_custom_field as $key => $customfield) { 
												$item_html = '<tr>';
												$item_html .= '<td class="text-nowrap">'.$customfield->title.'</td>';
												$item_html .= '<td>'.dmg_convert_custom_field_value_to_string($customfield->value, $customfield->type).'</td>';
												$item_html .= '</tr>';
												echo htmldecode($item_html);
											} 
										} 
									} 
									?>


								</table>
							<?php } ?>
							<!-- Resolution -->
							<div class="panel panel-default">
								<div class="panel-heading"><?php echo _l('dmg_resolution'); ?></div>
								<div class="panel-body no-border"><?php echo nlbr($item->resolution); ?></div>
							</div>
							<!-- Approve area -->
							<div class="col-md-12">
								<div class="project-overview-right">
									<div class="project-overview-right">
										<?php
										if(count($data_approve) > 0){ ?>
											<div class="row">
												<div class="col-md-12 project-overview-expenses-finance">
													<?php 
													$has_deny = false;
													$current_approve = false;
													foreach ($data_approve as $value) {
														?>
														<div class="col-md-4 text-center">
															<p class="text-uppercase text-muted no-mtop bold"><?php echo get_staff_full_name($value['staffid']); ?></p>

															<?php if($value['approve'] == 1){ 
																$file_path  = site_url(DOCUMENT_MANAGEMENT_PATH.'sign_documents/'.$value['id'].'/signature.png');
																?>
																<img src="<?php echo htmldecode($file_path); ?>">
																<br><br>
																<p class="bold text-center"><?php echo htmldecode($value['note']); ?></p> 
																<p class="bold text-center text-<?php if($value['approve'] == 1){ echo 'success'; }elseif($value['approve'] == 2){ echo 'danger'; } ?>"><?php echo _dt($value['date']); ?>
															<?php }else{
																if($current_approve == false && $has_deny == false){ 
																	$current_approve = true;
																	if(get_staff_user_id() == $value['staffid']){ 
																		$staff = $this->staff_model->get($value['staffid']);
																		?>
																		<div class="row text-center" >
																			<button 
																			class="btn btn-success mtop20" 
																			data-firstname="<?php echo htmldecode($staff->firstname); ?>" 
																			data-lastname="<?php echo htmldecode($staff->lastname); ?>" 
																			data-email="<?php echo htmldecode($staff->email); ?>" 
																			onclick="staff_sign_document(this,<?php echo htmldecode($value['rel_id'].','.$value['id']); ?>)">
																			<?php echo _l('dmg_sign') ?>
																		</button>
																	</div>
																	<?php 
																}
															}
														} ?> 
													</p>
												</div>
												<?php
											} ?>
										</div>
									</div>
								<?php }else{
									if(isset($process)){
										if($process == 'choose'){
											$html = '<div class="row">';
											$html .= '<div class="col-md-9"><select name="approver" class="selectpicker" data-live-search="true" id="approver_c" data-width="100%" data-none-selected-text="'. _l('fe_please_choose_approver').'"> 
											<option value=""></option>'; 
											$current_user = get_staff_user_id();
											foreach($staffs as $staff){ 
												if($staff['staffid'] != $current_user){
													$html .= '<option value="'.$staff['staffid'].'">'.$staff['staff_identifi'].' - '.$staff['firstname'].' '.$staff['lastname'].'</option>';                  
												}
											}
											$html .= '</select></div>';
											$html .= '<div class="col-md-3"><a href="javascript:void(0)" onclick="choose_approver();" class="btn btn-success lead-top-btn lead-view">'._l('choose').'</a></div>';
											$html .= '</div>';
											echo htmldecode($html);
										}
									}
								} ?>
							</div>
						</div>
					</div>
					<!-- End approve area -->


				</div>


			</div>
		</div>
	</div>
</div>
</div>



<div class="modal fade" tabindex="-1" role="dialog" id="identityConfirmationModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<?php echo form_open(admin_url('document_management/staff_sign_document'), array('id'=>'identityConfirmationForm','class'=>'form-horizontal')); ?>
			<input type="hidden" name="id" value="">
			<input type="hidden" name="rel_id" value="">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo _l('signature'); ?> &amp; <?php echo _l('confirmation_of_identity'); ?></h4>
			</div>
			<div class="modal-body">
				<div id="identity_fields">
					<div class="form-group">
						<label for="firstname" class="control-label col-sm-2">
							<span class="text-left inline-block full-width">
								<?php echo _l('client_firstname'); ?>
							</span>
						</label>
						<div class="col-sm-10">
							<input type="text" name="firstname" id="firstname" class="form-control" required="true" value="" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="lastname" class="control-label col-sm-2">
							<span class="text-left inline-block full-width">
								<?php echo _l('client_lastname'); ?>
							</span>
						</label>
						<div class="col-sm-10">
							<input type="text" name="lastname" id="lastname" class="form-control" required="true" value="" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="email" class="control-label col-sm-2">
							<span class="text-left inline-block full-width">
								<?php echo _l('client_email'); ?>
							</span>
						</label>
						<div class="col-sm-10">
							<input type="email" name="email" id="email" class="form-control" required="true" value="" readonly>
						</div>
					</div>
					<p class="bold" id="signatureLabel"><?php echo _l('signature'); ?></p>
					<div class="signature-pad--body">
						<canvas id="signature" height="130" width="550"></canvas>
					</div>
					<input type="text" class="hide" tabindex="-1" name="signature" id="signatureInput">
					<div class="dispay-block mtop10">
						<button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear"><?php echo _l('clear'); ?></button>
						<button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo"><?php echo _l('undo'); ?></button>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<p class="text-left text-muted e-sign-legal-text">
					<?php echo _l(get_option('e_sign_legal_text'),'', false); ?>
				</p>
				<hr />
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
				<button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#identityConfirmationForm" class="btn btn-success"><?php echo _l('e_signature_sign'); ?></button>
			</div>
			<?php echo form_close(); ?>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php
$this->app_scripts->theme('signature-pad','assets/plugins/signature-pad/signature_pad.min.js');
?>



<?php 
init_tail();
} ?>     

