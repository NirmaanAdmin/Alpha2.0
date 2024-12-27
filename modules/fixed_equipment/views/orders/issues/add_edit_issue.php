<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="">
					<?php $id = ''; ?>
					<?php $id = isset($ticket) ? $ticket->id : ''; ?>
					<?php
					$code = isset($ticket)? $ticket->code: $ticket_code; ?>
					<?php $client_id = isset($ticket)? $ticket->client_id: $order->userid; ?>
					<?php $asset_id = isset($ticket)? $ticket->asset_id: '';

					$current_day = date("Y-m-d");
					$created_id = get_staff_user_id();
					$datecreated = date("Y-m-d H:i:s");
					$claim_information_detail_id = '';

					if(isset($ticket)){
						$id = $ticket->id;
						$created_id = $ticket->created_id;
						$datecreated =  $ticket->datecreated ;
					}
					$issue_summary = (isset($ticket) ? $ticket->issue_summary : ''); 
					$assigned_id = isset($ticket) ? $ticket->assigned_id : get_staff_user_id();
					$internal_note = isset($ticket) ? $ticket->internal_note : '';
					$ticket_subject = isset($ticket) ? $ticket->ticket_subject : '';
					?>

					<?php echo form_open_multipart(admin_url('fixed_equipment/add_edit_issue/'.$id), array('id'=>'add_ticket', 'class' => 'dropzone dropzone-manual dz-max-files-reached')); ?>
					<div class="panel-body">

						<div class="row">
							<div class="col-md-12">
								<h4 class="no-margin font-bold "><i class="fa fa-object-ungroup menu-icon" aria-hidden="true"></i> <?php echo new_html_entity_decode($title); ?>: <?php echo html_entity_decode($code); ?></h4>
								<h5><?php echo _l('fe_related');  ?></h5>
								<h5><?php echo _l('fe_order_number');  ?>: <?php  echo ( isset($order) ? $order->order_number : ''); ?></h5>
								<h5><?php echo _l('customer_name');  ?>: <b><?php  echo ( isset($order) ? get_company_name($order->userid) : ''); ?></b></h5>
								<hr>
							</div>
						</div>

						<input type="hidden" name="id" value="<?php echo new_html_entity_decode($id); ?>">
						<input type="hidden" name="cart_id" value="<?php echo new_html_entity_decode($orderid); ?>">
						<input type="hidden" name="created_type" value="staff">
						<input type="hidden" name="created_id" value="<?php echo new_html_entity_decode($created_id != null ? $created_id : 0); ?>">
						<input type="hidden" name="ticket_source" value="web">

						<div class="row" >
							<div class="col-md-12">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="asset_id"><?php echo _l('fe_asset'); ?></label>
											<select name="asset_id" id="asset_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >
												<?php if(isset($order_details)){ ?>
													<?php foreach($order_details as $order_detail) { ?>
														<option value="<?php echo new_html_entity_decode($order_detail['product_id']); ?>" <?php if($asset_id == $order_detail['product_id']){ echo 'selected'; } ?>><?php echo html_entity_decode($order_detail['product_name']); ?></option>
													<?php } ?>
												<?php } ?>

											</select>
										</div>
									</div>
									<div class="col-md-6 hide">
										<?php echo render_input('code', 'fe_code_label',$code,'',array('readonly' => 'true')) ?>
									</div>

									<div class="col-md-3">
										<?php echo render_datetime_input('datecreated','fe_date_created', _dt($datecreated)) ?>
									</div>

									<div class="col-md-3">
										<?php echo render_select('assigned_id', $staffs, ['staffid', array('firstname', 'lastname')], 'fe_assigned_to', $assigned_id); ?>
									</div>

									<br>
									<div class="col-md-6 hide">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="client_id"><?php echo _l('customer_name'); ?></label>
													<select name="client_id" id="client_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>"  >
														<option value=""></option>
														<?php foreach($clients as $s) { ?>
															<option value="<?php echo new_html_entity_decode($s['userid']); ?>" <?php if($client_id == $s['userid']){ echo 'selected'; } ?>><?php echo new_html_entity_decode($s['company']); ?></option>
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12 mtop15">
							<div class="panel-body bottom-transaction">
								
								<?php echo render_input('ticket_subject', 'fe_issue_subject', $ticket_subject); ?>
								<?php echo render_textarea('issue_summary','fe_brief_description',$issue_summary,array(),array(),'mtop15'); ?>

								<div class="row">
									<div class="col-md-12">
										<div id="dropzoneDragArea" class="dz-default dz-message">
											<span><?php echo _l('fe_attach_file'); ?></span>
										</div>
										<div class="dropzone-previews"></div>
									</div>
								</div>

								<?php if(isset($issue_attachments) && count($issue_attachments) > 0){ ?>
								<div class="row">
									<div class="col-md-12">
										
										<div id="contract_attachments" class="mtop30 ">

											<?php
											$data = '<div class="row" id="attachment_file">';
											foreach($issue_attachments as $attachment) {
												$data .= '<div class="col-md-6">';
												$href_url = site_url('modules/fixed_equipment/uploads/issues/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
												if(!empty($attachment['external'])){
													$href_url = $attachment['external_link'];
												}
												$data .= '<div class="display-block contract-attachment-wrapper">';
												$data .= '<div class="col-md-11">';
												$data .= '<div class="col-md-1 mright10">';
												$data .= '<a name="preview-btn" onclick="preview_file(this); return false;" rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'._l("preview_file").'">';
												$data .= '<i class="fa fa-eye"></i>'; 
												$data .= '</a>';
												$data .= '</div>';
												$data .= '<div class=col-md-9>';
												$data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
												$data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
												$data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
												$data .= '</div>';
												$data .= '</div>';
												$data .= '<div class="col-md-1 text-right">';
												if(is_admin() || has_permission('fixed_equipment_order_list', '', 'delete') ){
													$data .= '<a href="#" class="text-danger" onclick="delete_issue_pdf_file(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
												}
												$data .= '</div>';
												$data .= '<div class="clearfix"></div><hr>';
												$data .= '</div>';
												$data .= '</div>';
											}
											$data .= '</div>';
											echo new_html_entity_decode($data);
											?>
											<!-- check if edit contract => display attachment file end-->

										</div>
										<div id="pdf_file_data"></div>
									</div>
								</div>
							<?php } ?>

								<div class="btn-bottom-toolbar text-right">
									<a href="<?php echo admin_url('fixed_equipment/view_order_detailt/'.$order->id); ?>"class="btn btn-default text-right mright5"><?php echo _l('close'); ?></a>
									
									<?php if (is_admin() || has_permission('fixed_equipment_order_list', '', 'edit') || has_permission('fixed_equipment_order_list', '', 'create')) { ?>
										<button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>
									<?php } ?>

								</div>
							</div>
							<div class="btn-bottom-pusher"></div>
						</div>
					</div>

				</div>

			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<div id="modal_wrapper"></div>
<div id="change_serial_modal_wrapper"></div>

<?php init_tail(); ?>
<?php require 'modules/fixed_equipment/assets/js/orders/issues/add_edit_issue_js.php';?>
</body>
</html>



