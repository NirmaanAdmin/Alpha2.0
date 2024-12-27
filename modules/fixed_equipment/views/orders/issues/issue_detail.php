<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
				<div class="row">

					<div class="col-md-12">

						<div class="card">
							<div class="row">
								<div class="col-md-12">
									<div class="card-header no-padding-top">
										<div class="row">
											<?php 
											$reporter = '';
											if($ticket->created_type == 'staff'){
												$reporter = get_staff_full_name($ticket->created_id);
											}else{
												$reporter = get_contact_full_name($ticket->created_id);
											}
											?>
											<div class="col-md-6">
												<?php echo render_fe_issue_status_html($ticket->id, 'staff', $ticket->status); ?>
												<h4><strong><?php  echo html_entity_decode($ticket->code.' '.$ticket->ticket_subject); ?></strong></h4>
												<h6><?php echo _l('fe_reporter'); ?>: <?php  echo html_entity_decode($reporter); ?></h6>
											</div>
											
											<div class="col-md-6 ">
												<div class="pull-right">

													<?php if($ticket->status == 'open'){ ?>
														<a href="<?php echo admin_url('fixed_equipment/add_edit_issue/'.$ticket->id); ?>" class="btn btn-sm btn-primary mleft5" ><span class="fa-regular fa-pen-to-square"></span> <?php echo _l('edit'); ?></a>

														<a href="<?php echo admin_url('fixed_equipment/delete_issue/'.$ticket->id); ?>" class="btn btn-sm btn-primary mleft5 _delete" ><span class="fa fa-trash"></span> <?php echo _l('delete'); ?></a>
													<?php } ?>

												</div>
											</div>

										</div>
									</div>
								</div>
							</div>
							<hr class="no-margin">

						</div>

					</div>
				</div>

			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="panel_s mtop15">
						<div class="panel-body">
							<h4><strong><?php echo _l('fe_ticket_information'); ?></strong></h4>

							<div class="row">
								<div class="col-md-6">
									<table class="table border table-striped no-mtop">
										<tbody>

											<tr class="project-overview">
												<td class="bold"><?php echo _l('datecreated'); ?></td>
												<td><?php echo _dt($ticket->datecreated) ; ?></td>
											</tr>
											<tr class="project-overview">
												<td class="bold"><?php echo _l('fe_device_name'); ?></td>
												<td>
													<?php
													if($ticket->asset_id != null){
														echo $this->fixed_equipment_model->get_asset_name($ticket->asset_id) ;

													}
													?>
												</td>
											</tr>
											<tr class="project-overview">
												<td class="bold"><?php echo _l('fe_assigned_to'); ?></td>
												<td><?php echo get_staff_full_name($ticket->assigned_id) ; ?></td>
											</tr>
											
											<tr class="project-overview">
												<td class="bold"><?php echo _l('fe_last_update_time'); ?></td>
												<td><?php echo _dt($ticket->last_update_time);   ?></td>
											</tr> 
											
											<tr class="project-overview">
												<td class="bold"><?php echo _l('fe_first_reply_time'); ?></td>
												<td><?php echo _dt($ticket->first_reply_time);  ?></td>
											</tr> 
										</tbody>
									</table>
								</div>
								<div class="col-md-6">
									<table class="table border table-striped no-mtop">
										<tbody>
											<?php if($ticket->created_type == 'staff'){ ?>
												<tr class="project-overview">
													<td class="bold" width="30%"><?php echo _l('staff'); ?></td>
													<td><?php echo get_staff_full_name($ticket->staffid) ; ?></td>
												</tr>
												<tr class="project-overview">
													<td class="bold"><?php echo _l('email'); ?></td>
													<td><?php echo fe_get_staff_email($ticket->staffid) ; ?></td>
												</tr>
											<?php }else{ ?>
												<tr class="project-overview">
													<td class="bold" width="30%"><?php echo _l('contact'); ?></td>
													<td><?php echo get_contact_full_name($ticket->created_id) ; ?></td>
												</tr>
												<tr class="project-overview">
													<td class="bold"><?php echo _l('email'); ?></td>
													<td><?php echo fe_get_contact_email('contact', $ticket->created_id) ; ?></td>
												</tr>
											<?php } ?>
											
											<tr class="project-overview">
												<td class="bold"><?php echo _l('client'); ?></td>
												<td><?php echo get_company_name($ticket->client_id) ; ?></td>
											</tr>
											
											
											<tr class="project-overview">
												<td class="bold"><?php echo _l('phone'); ?></td>
												<td><?php 
												$phonenumber = '';
												$get_client = get_client($ticket->client_id);
												if($get_client){
													$phonenumber = $get_client->phonenumber;

												}
												echo html_entity_decode($phonenumber) ;
											?></td>
										</tr>
										
										<tr class="project-overview hide">
											<td class="bold"><?php echo _l('fe_last_message_time'); ?></td>
											<td><?php echo _dt($ticket->last_message_time) ; ?></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-md-12">
								<label><strong><?php echo _l('fe_brief_description'); ?></strong></label><br>
								<html><?php echo html_entity_decode($ticket->issue_summary); ?></html>
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
						</div>

						<h4><strong><?php echo _l('fe_ticket_detail'); ?></strong></h4>

						<div class="row">
							<div class="horizontal-scrollable-tabs preview-tabs-top">
								<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
								<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
								<div class="horizontal-tabs">
									<ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">


										<li role="presentation" class="active">
											<a href="#ticket_actions" aria-controls="ticket_actions" role="tab" data-toggle="tab">
												<span class="fa-solid fa-bolt"></span>&nbsp;<?php echo _l('fe_ticket_actions'); ?>
											</a>
										</li>
										<li role="presentation">
											<a href="#ticket_history" aria-controls="ticket_history" role="tab" data-toggle="tab">
												<span class="fa fa-history"></span>&nbsp;<?php echo _l('fe_ticket_history'); ?>
											</a>
										</li>
										<?php if(1==2){ ?>
											<li role="presentation ">
												<a href="#time_spent" aria-controls="time_spent" role="tab" data-toggle="tab">
													<span class="fa fa-hourglass-1"></span>&nbsp;<?php echo _l('fe_time_spent'); ?>
												</a>
											</li>
										<?php } ?>
										<li role="presentation">
											<a href="#customer_related_information" aria-controls="customer_related_information" role="tab" data-toggle="tab">
												<span class="fa fa-info-circle"></span>&nbsp;<?php echo _l('fe_issues'); ?>
											</a>
										</li>

										<li role="presentation" class="">
											<a href="#tasks" onclick="init_rel_tasks_table(<?php echo new_html_entity_decode($ticket->id); ?>,'fe_issue'); return false;" aria-controls="tasks" role="tab" data-toggle="tab">
												<span class="fa-regular fa-circle-check menu-icon"></span>&nbsp;<?php echo _l('tasks'); ?>
											</a>
										</li>


									</ul>
								</div>
							</div>
							<br>


							<div class="tab-content">
								

								<div role="tabpanel" class="tab-pane active" id="ticket_actions">
									<div class="col-md-12">

										<?php echo form_open_multipart(admin_url('fixed_equipment/issue_post_internal_reply'),array('class'=>'post_internal_reply','autocomplete'=>'off')); ?>
										<div class="col-md-5 col-md-offset-1">
											<input type="hidden" name="ticket_id" value="<?php echo html_entity_decode($ticket->id) ?>">

											<?php echo render_input('note_title', 'fe_note_title') ?>
											<?php echo render_textarea('note_details', 'fe_note_details') ?>

											<?php echo render_select('fe_ticket_status', fe_ticket_status(), array('id', 'name'), 'fe_ticket_status', '', ['title' => 'fe_post_internal_ticket_status_title'], [], '', '', true) ?>
											<div class="form-group hide">
												<label><?php echo _l('fe_resolution'); ?></label>

												<div class="checkbox checkbox-primary">
													<input type="checkbox" id="internal_resolution" name="internal_resolution"value="resolution">
													<label for="internal_resolution"><?php echo _l('fe_set_reply_as_resolution'); ?>
													<a href="#" class="pull-right display-block input_method"><i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('fe_resolution_tooltip'); ?>"></i></a>
												</label>
											</div>
										</div>

										<button type="submit" class="btn btn-info"><?php echo _l('fe_post_note'); ?></button>

									</div>
									<?php echo form_close(); ?>

									<div class="col-md-5 ">
										<div class="activity-feed">
											<?php if(count($ticket_post_internal_histories) > 0){ ?>
												<?php foreach($ticket_post_internal_histories as $post_internal_history){ ?>
													<?php 
													$id = $post_internal_history['id'];
													$date_create = '';
													$staff_id = '';
													$description = '';
													$rel_type = '';

													if(isset($post_internal_history['note_title'])){
														$date_create = $post_internal_history['datecreated'];

														$created_label = '';
														if($post_internal_history['created_type'] == 'staff'){
															$staff_id = $post_internal_history['staffid'];
															$created_label = '<strong>'._l('fe_staff').'</strong> - '.get_staff_full_name($post_internal_history['staffid']);
														}else{
															$created_label = '<strong>'._l('fe_client').'</strong> - '.get_contact_full_name($post_internal_history['staffid']);
														}

														$description = $created_label .' <br>'._l('fe_post_internal_reply').': <br>'.$post_internal_history['note_title'].' <br><strong>'. $post_internal_history['note_details'].'</strong><br>';
														$rel_type = 'post_internal';


													}

													?>
													<div class="feed-item">
														<div class="date">
															<span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($date_create); ?>">
																<?php echo time_ago($date_create); ?>
															</span>
															<?php if(is_admin()){ ?>
																<a href="#" class="pull-right text-danger" onclick="delete_issue_history(this,<?php echo html_entity_decode($id); ?>, '<?php echo html_entity_decode($rel_type); ?>');return false;"><i class="fa fa fa-times"></i></a>
															<?php } ?>
														</div>

														<div class="text">
															<?php if(isset($staff_id)){ ?>
																<?php if($staff_id != 0){ ?>
																	<a href="<?php echo admin_url('profile/'.$staff_id); ?>">
																		<?php echo staff_profile_image($staff_id,array('staff-profile-xs-image pull-left mright5'));
																		?>
																	</a>
																	<?php
																}
															}
															echo html_entity_decode($description);

															?>
														</div>

													</div>
												<?php } ?>
											<?php }else{ ?>
												<h5><?php echo _l('fe_there_is_no_history_for_this_ticket'); ?></h5>
											<?php } ?>
										</div>
									</div>
									<ul class="nav nav-tabs  hide" id="myTab" role="tablist">

										<li class="nav-item active">
											<a class="nav-link" id="post_internal_reply-tab" data-toggle="tab" href="#post_internal_reply" role="tab" aria-controls="post_internal_reply" aria-selected="false"><?php echo _l('fe_post_internal_reply'); ?></a>
										</li>

										<li class="nav-item hide">
											<a class="nav-link" id="assign_ticket-tab" data-toggle="tab" href="#assign_ticket" role="tab" aria-controls="contact" aria-selected="false"><?php echo _l('fe_assign_ticket'); ?></a>
										</li>

									</ul>
									<div class="tab-content" id="myTabContent">

										<div class="tab-pane fade active in" id="post_internal_reply" role="tabpanel" aria-labelledby="post_internal_reply-tab">


										</div>


										<div class="tab-pane fade hide" id="assign_ticket" role="tabpanel" aria-labelledby="assign_ticket-tab">

											<?php echo form_open_multipart(admin_url('fixed_equipment/ticket_reassign'),array('class'=>'reassign_ticket','autocomplete'=>'off')); ?>
											<input type="hidden" name="ticket_id" value="<?php echo html_entity_decode($ticket->id) ?>">

											<div class="col-md-8 col-md-offset-2">

												<?php echo render_textarea('re_comment', 'fe_comments', '', ['title' => 'fe_enter_reasons_for_the_assignment_or_instruction_for_assignee', 'placeholder' => _l('fe_enter_reasons_for_the_assignment_or_instruction_for_assignee')]) ?>

												<?php echo render_select('assignee_id', $staffs, array('staffid', array('firstname', 'lastname') ), 'fe_assigned_to', '', ['title' => 'fe_select_staff_member'], [], '', '', true) ?>

												<span><?php echo _l('fe_ticket_is_currently_assigned_to').' <strong>'.get_staff_full_name($ticket->assigned_id).'</strong> ' ?></span><br>
												<button type="submit" class="btn btn-info"><?php echo _l('fe_reassign'); ?></button>

											</div>
											<?php echo form_close(); ?>
										</div>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane " id="ticket_history">
								<div class="col-md-12">
									<div class="activity-feed">
										<?php if(count($ticket_histories) > 0){ ?>
											<?php foreach($ticket_histories as $ticket_history){ ?>
												<?php 
												$id = $ticket_history['id'];
												$date_create = '';
												$staff_id = '';
												$description = '';
												$rel_type = '';

												if(isset($ticket_history['assignee_id'])){
													$date_create = $ticket_history['datecreated'];
													$staff_id = $ticket_history['staffid'];
													$description = get_staff_full_name($ticket_history['staffid']) .' '._l('fe_assigned_this_ticket_for').': <strong>'. get_staff_full_name($ticket_history['assignee_id']).'</strong><br>'. _l('fe_reason'). ': '. $ticket_history['comment'];
													$rel_type = 'assign_ticket';


												}elseif(isset($ticket_history['note_title'])){
													$date_create = $ticket_history['datecreated'];

													$created_label = '';
													if($ticket_history['created_type'] == 'staff'){
														$staff_id = $ticket_history['staffid'];
														$created_label = '<strong>'._l('fe_staff').'</strong> - '.get_staff_full_name($ticket_history['staffid']);
													}else{
														$created_label = '<strong>'._l('fe_client').'</strong> - '.get_contact_full_name($ticket_history['staffid']);
													}

													$description = $created_label .' '._l('fe_post_internal_reply').': '.$ticket_history['note_title'].' <strong>'. $ticket_history['note_details'].'</strong><br>';
													$rel_type = 'post_internal';


												}elseif(isset($ticket_history['response'])){
													$date_create = $ticket_history['datecreated'];
													$staff_id = $ticket_history['staffid'];
													$description = get_staff_full_name($ticket_history['staffid']) .' '._l('fe_post_reply'). _l('fe_to').': <strong>'.' '.get_staff_full_name($ticket_history['to_staff_id']).'</strong> '.$ticket_history['response'].'<br>';
													$rel_type = 'post_reply';
												}elseif(isset($ticket_history['created_type'])){
													$date_create = $ticket_history['date'];

													if($ticket_history['created_type'] == 'staff'){
														$staff_id = $ticket_history['staffid'];
														$full_name = $ticket_history['full_name'];

													}elseif($ticket_history['created_type'] == 'client'){
														$full_name = $ticket_history['full_name'];
													}else{
														$full_name = $ticket_history['created_type'];
													}

													$description = $full_name .': '.$ticket_history['description'];

													$rel_type = 'ticket_timeline_log';
												}


												?>
												<div class="feed-item">
													<div class="date">
														<span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($date_create); ?>">
															<?php echo time_ago($date_create); ?>
														</span>
														<?php if(is_admin()){ ?>
															<a href="#" class="pull-right text-danger" onclick="delete_issue_history(this,<?php echo html_entity_decode($id); ?>, '<?php echo html_entity_decode($rel_type); ?>');return false;"><i class="fa fa fa-times"></i></a>
														<?php } ?>
													</div>

													<div class="text">
														<?php if(isset($staff_id)){ ?>
															<?php if($staff_id != 0){ ?>
																<a href="<?php echo admin_url('profile/'.$staff_id); ?>">
																	<?php echo staff_profile_image($staff_id,array('staff-profile-xs-image pull-left mright5'));
																	?>
																</a>
																<?php
															}
														}
														echo html_entity_decode($description);

														?>
													</div>

												</div>
											<?php } ?>
										<?php }else{ ?>
											<h5><?php echo _l('fe_there_is_no_history_for_this_ticket'); ?></h5>
										<?php } ?>
									</div>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane " id="time_spent">
								<div class="col-md-12">
									<?php if(isset($time_spents) && count($time_spents) > 0){ ?>

									<?php }else{ ?>
										<h5><?php echo _l('fe_there_is_no_time_logs_for_this_ticket'); ?></h5>
									<?php } ?>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane " id="customer_related_information">
								<div class="col-md-12">
									
									<!-- show data related this problem -->
									<?php if(count($issue_the_sames) > 0){ ?>
										<table class="table items table-bordered no-margin">
											<thead>
												<tr>
													<th align="left"><strong><?php echo _l('fe_issue_subject') ?></strong></th>
													<th align="left"><strong><?php echo _l('fe_brief_description') ?></strong></th>
													<th align="left"><strong><?php echo _l('fe_assigned_to') ?></strong></th>
													<th align="left"><strong><?php echo _l('datecreated') ?></strong></th>
												</tr>

											</thead>
											<tbody>
												<?php foreach ($issue_the_sames as $key => $ticket_the_same) { ?>
													<tr>
														<td ><strong><a href="<?php echo admin_url('fixed_equipment/issue_detail/'.$ticket_the_same['id']); ?>"><?php echo ($ticket_the_same['code'].' '.$ticket_the_same['ticket_subject']) ?></strong></a></td>
														<td>
															<?php echo ($ticket_the_same['issue_summary']) ?>
														</td>
														<td>
															<?php echo (get_staff_full_name($ticket_the_same['assigned_id'])) ?>
														</td>
														<td>
															<?php echo _dt($ticket_the_same['datecreated']) ?>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									<?php } ?>

								</div>
							</div>

							<div role="tabpanel" class="tab-pane " id="tasks">
								<?php init_relation_tasks_table(array('data-new-rel-id'=>$ticket->id,'data-new-rel-type'=>'fe_issue')); ?>
							</div>

						</div>

					</div>
				</div>
			</div>


		</div>
	</div>



	<div class="row">
		<div class="col-md-12 ">
			<div class="panel-body bottom-transaction">
				<div class="btn-bottom-toolbar text-right">
					<a href="<?php echo admin_url('fixed_equipment/view_order_detailt/'.$ticket->cart_id); ?>"class="btn btn-info text-right"><?php echo _l('close'); ?></a>
				</div>
			</div>
			<div class="btn-bottom-pusher"></div>
		</div>
	</div>

</div>
<div id="modal_wrapper"></div>
</div>
</div>

<?php init_tail(); ?>
<?php require 'modules/fixed_equipment/assets/js/orders/issues/issue_detail_js.php';?>
<?php require 'modules/fixed_equipment/assets/js/orders/issues/issue_js.php';?>
