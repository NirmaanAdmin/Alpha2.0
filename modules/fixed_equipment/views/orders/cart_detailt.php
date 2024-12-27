<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php 
$inv = '';
$inv_id = '';
$hash = '';
if(isset($invoice)){
	$inv_id = $invoice->id;
	$hash = $invoice->hash;
} 

?>
<input type="hidden" name="order_id" value="<?php echo fe_htmldecode($order->id); ?>">
<div id="wrapper">
	<div class="content">

		<div class="panel_s">
			<div class="panel-body">
				<!-- Show Tab if this is return order and approved -->
				<?php if(false){ ?>
					<div class="horizontal-scrollable-tabs preview-tabs-top">
						<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
						<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
						<div class="horizontal-tabs">
							<ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
								<li role="presentation" class="active">
									<a href="#order_detail" aria-controls="order_detail" role="tab" data-toggle="tab">
										<?php echo _l('fe_order_detail'); ?>
									</a>
								</li>
								<li role="presentation">
									<a href="#refund" aria-controls="refund" role="tab" data-toggle="tab">
										<?php echo _l('fe_refund'); ?>
										<?php $count_refund = fe_count_refund($order->id); 
										if($count_refund > 0){ ?>
											<span class="badge badge-portal bg-warning mleft5">
												<?php echo fe_htmldecode($count_refund); ?>
											</span>
										<?php }	?>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane ptop10 active" id="order_detail">
						<?php } ?>



						<div class="row">
							<div class="col-md-12">

								<div class="row">
									<div class="col-md-6">
										<h5><?php echo _l('fe_order_number');  ?>: <?php  echo ( isset($order) ? $order->order_number : ''); ?></h5>
										<?php if(isset($order) && $order->seller > 0){ ?>
											<span class="mright15"><?php echo _l('seller');  ?>: <?php echo get_staff_full_name($order->seller); ?></span><br>
										<?php } ?>
										<span><?php echo _l('fe_order_date');  ?>: <?php  echo (isset($order) ? $order->datecreator : ''); ?></span><br>
										<span><?php echo _l('fe_order_type');  ?>: <?php  echo (isset($order) ? _l('fe_'.$order->type) : ''); ?></span><br>
										<?php 
										if(!$is_return_order){
											if(isset($invoice)){ ?>
												<span><?php echo _l('invoice');  ?>: <a href="<?php echo admin_url('invoices#'.$invoice->id) ?>"><?php echo fe_htmldecode($order->invoice); ?></a></span><br>
											<?php	}
										}else{ ?>
											<span><?php echo _l('from_order');  ?>: <a href="<?php echo admin_url('fixed_equipment/view_order_detailt/'.$order->original_order_id) ?>"><?php echo fe_get_order_number($order->original_order_id); ?></a></span><br>
										<?php }	?>
										<input type="hidden" name="order_number" value="<?php echo fe_htmldecode($order->order_number); ?>">
										<?php 
										if(isset($order)){
											$payment_method =  $order->payment_method_title;
											if($payment_method == ''){
												$this->load->model('payment_modes_model');	
												$data_payment = $this->payment_modes_model->get($order->allowed_payment_modes);
												if($data_payment){
													$name = isset($data_payment->name) ? $data_payment->name : '';
													if($name !=''){
														$payment_method = $name;              
													}            
												}
											}	
											if($payment_method != ''){ ?>
												<span><?php echo _l('payment_method');  ?>: <span class="text-primary"><?php echo fe_htmldecode($payment_method); ?></span></span><br>
											<?php }		
										}
										?>

										<?php
										if(isset($order) && $order->estimate_id != null &&  is_numeric($order->estimate_id)){ ?>
											<span><?php echo _l('estimates');  ?>: <a href="<?php echo admin_url('estimates#'.$order->estimate_id) ?>"><?php echo format_estimate_number($order->estimate_id); ?></a></span><br>
										<?php	}
										?>

									</div>
									<div class="col-md-6 status_order">
										<div class="row">
											<?php
											$currency_name = '';
											if(isset($base_currency)){
												$currency_name = $base_currency->name;
											}
											$status = fe_get_status_by_index($order->status);    
											?>


											<div class="col-md-12">
												<div class="reasion pull-left">
													<div class="text-danger">
														<?php 
														if(in_array($order->status, [11,6,12,13])){
															$return_order = fe_get_return_order_of_parent($order->id);
															if($return_order){
																echo _l('fe_the_order_was_returned_for_some_reason').': '.$return_order->return_reason.'<br><a target="_blank" href="'.admin_url('fixed_equipment/view_order_detailt/'.$return_order->id).'">'._l('fe_view_detail_return_order').'</a>';
															}
														}
														?>
														<?php 
														if($order->status == 8){ 
															if($order->admin_action == 0){
																echo _l('was_canceled_by_you_for_a_reason').': '._l($order->reason); 
															}
															else
															{
																echo _l('was_canceled_by_us_for_a_reason').': '._l($order->reason);  
															} 
														} ?> 
													</div>
												</div>
												<!-- add hook display shipment -->
												<?php if(count($order_issues_closed) > 0){ ?>
													<div class="col-md-6">
														<div class="form-group">
															<select name="view_issue_closes" id="view_issue_closes" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('fe_The_issue_is_closed'); ?>"  >
																<option value=""></option>
																<?php foreach($order_issues_closed as $issues_closed) { ?>
																	<?php 
																	$get_status          = get_fe_issue_status_by_id($issues_closed['status'], 'staff');
																	$outputStatus = '['.$get_status['name'].']';
																	?>
																	<option value="<?php echo admin_url('fixed_equipment/issue_detail/'.$issues_closed['id']) ; ?>"><?php  echo html_entity_decode($outputStatus); ?> <?php echo html_entity_decode($issues_closed['code'].' '.$issues_closed['ticket_subject']); ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
												<?php } ?>
												<div class="btn-group pull-right">
													<?php if($order->status){ ?>

														<?php if(count($issue_open) > 0){ ?>
															<a href="<?php echo admin_url('fixed_equipment/issue_detail/'.$issue_open[0]['id']); ?>" class="btn btn-warning"><?php echo _l('fe_view_issue'); ?></a>
														<?php }else{ ?>
															<a href="<?php echo admin_url('fixed_equipment/add_edit_issue/0/'.$order->id); ?>" class="btn btn-danger"><?php echo _l('fe_new_issue'); ?></a>
														<?php } ?>
													<?php } ?>

													<?php 
													$shipment = $this->fixed_equipment_model->get_shipment_by_order($id);
													if(!$is_return_order && $order->status > 0 && $shipment){
														?>
														<a href="<?php echo admin_url('fixed_equipment/shipment_detail/'.$order->id); ?>" class="btn btn-primary"><?php echo _l('fe_shipment'); ?></a>
													<?php }	?>
													<button href="#" class="dropdown-toggle btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true" >
														<?php echo _l($status); ?>  <span class="caret" data-toggle="" data-placement="top" data-original-title="<?php echo _l('change_status'); ?>"></span>
													</button>
													<ul class="dropdown-menu animated fadeIn">
														<li class="customers-nav-item-edit-profile">
															<?php									
															foreach(fe_status_list($is_return_order) as $item){ ?>
																<?php if(has_permission('fixed_equipment_order_list', '', 'edit') || is_admin()){ ?>
																	<a href="#" class="change_status" data-status="<?php echo fe_htmldecode($item['id']);?>">
																		<?php echo fe_htmldecode($item['label']);?>
																	</a> 
																<?php }else{ ?>
																	<a href="#" class="" data-status="<?php echo fe_htmldecode($item['id']);?>">
																		<?php echo fe_htmldecode($item['label']);?>
																	</a> 
																<?php } ?>
															<?php } ?>
														</li> 
													</ul>
												</div>

											</div>
											<br>
										</div>
									</div>
								</div>


								<div class="clearfix"></div>
								<div class="row">
									<div class="col-md-12">
										<hr>  
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="row">
									<div class="col-md-4">
										<input type="hidden" name="userid" value="<?php echo fe_htmldecode($order->userid); ?>">
										<h4 class="no-mtop">
											<i class="fa fa-user"></i>
											<?php echo _l('fe_customer_details'); ?>
										</h4>
										<hr />
										<?php echo (isset($order) ? $order->company : ''); ?><br>
										<?php echo (isset($order) ? $order->phonenumber : ''); ?><br>
										<?php echo (isset($order) ? $order->address : ''); ?><br>
										<?php echo (isset($order) ? $order->city : ''); ?> <?php echo ( isset($order) ? $order->state : ''); ?><br>
										<?php echo isset($order) ? get_country_short_name($order->country) : ''; ?> <?php echo ( isset($order) ? $order->zip : ''); ?><br>
									</div>
									<div class="col-md-4">
										<h4 class="no-mtop">
											<i class="fa fa-map"></i>
											<?php echo _l('billing_address'); ?>
										</h4>
										<hr />
										<address class="invoice-html-customer-shipping-info">
											<?php echo isset($order) ? $order->billing_street : ''; ?>
											<br><?php echo isset($order) ? $order->billing_city : ''; ?> <?php echo isset($order) ? $order->billing_state : ''; ?>
											<br><?php echo isset($order) ? get_country_short_name($order->billing_country) : ''; ?> <?php echo isset($order) ? $order->billing_zip : ''; ?>
										</address>
									</div>
									<div class="col-md-4">
										<h4 class="no-mtop">
											<i class="fa fa-street-view"></i>
											<?php echo _l('shipping_address'); ?>
										</h4>
										<hr />
										<address class="invoice-html-customer-shipping-info">
											<?php echo isset($order) ? $order->shipping_street : ''; ?>
											<br><?php echo isset($order) ? $order->shipping_city : ''; ?> <?php echo isset($order) ? $order->shipping_state : ''; ?>
											<br><?php echo isset($order) ? get_country_short_name($order->shipping_country) : ''; ?> <?php echo isset($order) ? $order->shipping_zip : ''; ?>
										</address>
									</div>
								</div>
							</div>

						</div>


						<div class="row">
							<?php
							$tax_total_array = [];
							$sub_total = 0;
							?>
							<div class="clearfix"></div>
							<br>       
							<div class="invoice accounting-template">
								<div class="row">
								</div>
								<div class="fr1">
									<div class="col-md-8">
									</div>
									<div class="col-md-4">
										<span class="pull-right mbot10 italic"><?php echo _l('fe_currency').': '.$currency_name; ?></span>																
									</div>
									<!-- Order content -->
									<?php 
									if($order->type == 'order'){
										$this->load->view('orders/includes/detail_order_content.php');
									}
									else{
										$this->load->view('orders/includes/detail_booking_content.php');
									}
									?>
									<!-- End Order content -->
								</div>

								<?php if($is_return_order){ ?>
									<div class="col-md-12">
										<hr>
									</div>
									<div class="col-md-12">
										<div class="panel-body bottom-transaction">
											<div class="row">
												<div class="col-md-6">
													<?php 
													if($is_return_order){ 
														echo '-'._l('fe_'.$order->return_reason_type);
														if($order->return_reason != ''){
															echo '</br>-'._l('fe_describe_in_detail_the_reason').': '.$order->return_reason;
														}
													} ?> 
												</div>
												<div class="col-md-6">
													<div class="pull-right row text-center">												


														<?php if(has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit') || is_admin()){ 
															if($order->return_reason_type == 'return_and_get_money_back'){ ?>
																<div class="col-md-12">

																	<?php if($order->stock_import_number == 0){ ?>
																		<?php if((has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit')) && $order->return_reason_type != 'return_for_maintenance_repair') { ?>
																			<button class="btn btn-success create_import_stock pull-right mright15">
																				<?php echo _l('fe_create_import_stock'); ?>													
																			</button>
																		<?php } ?>
																	<?php }else{ ?>
																		<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_receiving#'.$order->stock_import_number); ?>" class="btn pull-right"><?php echo _l('fe_view_import_stock'); ?></a>
																	<?php } ?>


																	<?php if($order->stock_import_number != 0){ ?>
																		<?php if(!is_numeric($order->audit_id)){ ?>
																			<a href="<?php echo admin_url('fixed_equipment/create_audit_order/'.$order->id); ?>" class="btn btn-warning">
																				<?php echo _l('fe_create_audit_request'); ?>
																			</a>
																			<?php 
																		}
																		else{ ?>
																			<a href="<?php echo admin_url('fixed_equipment/audit/'.$order->audit_id); ?>" class="btn pull-right">
																				<?php echo _l('fe_view_audit'); ?>
																			</a>
																			<?php if($order->approve_status == 0){ ?>
																				<div class="row">
																					<div class="col-md-6 mtop10">
																						<button class="btn btn-success" onclick="approve_return_order(1)"><?php echo _l('fe_accept'); ?></button>
																					</div>
																					<div class="col-md-6 mtop10">
																						<button class="btn btn-warning" onclick="approve_return_order(-1)"><?php echo _l('fe_reject'); ?></button>
																					</div>
																				</div>
																			<?php } else { 

																				if($order->approve_status == 1){
																					// Create credit note if order is approve
																						if(!is_numeric($order->credit_note_id)){ ?>
																							<a href="<?php echo admin_url('fixed_equipment/create_credit_note_order/'.$order->id); ?>" class="btn btn-warning pull-right">
																								<?php echo _l('fe_create_credit_note'); ?>
																							</a>
																						<?php }
																						else{ ?>
																							<a href="<?php echo admin_url('credit_notes#'.$order->credit_note_id); ?>" class="btn pull-right">
																								<?php echo _l('fe_view_credit_note'); ?>
																							</a>
																						<?php }
																						$original_cart_data = $this->fixed_equipment_model->get_cart($order->original_order_id);
																						if($order->process_invoice == 'off' && $original_cart_data->invoice != '' && $order->return_reason_type == 'return_and_get_money_back'){
																							if($order->return_type == 'partially'){ ?>
																								<?php if(has_permission('fixed_equipment_order_list', '', 'edit')){ ?>
																									<a href="<?php echo admin_url('fixed_equipment/update_invoice/'.$id.'/'.$order->original_order_id); ?>" class="btn btn-warning pull-right mright15">
																										<?php echo _l('fe_update_invoice'); ?>
																									</a>
																								<?php } ?>
																							<?php }
																							else{ ?>
																								<?php if(has_permission('fixed_equipment_order_list', '', 'edit')){ ?>
																									<a href="<?php echo admin_url('fixed_equipment/cancel_invoice/'.$id.'/'.$order->original_order_id); ?>" class="btn btn-warning pull-right mright15">
																										<?php echo _l('fe_cancel_invoice'); ?>
																									</a>
																								<?php } ?>
																							<?php }
																						}
																				}
																				else{ 
																					// Create export stock when order is reject
																					if ($order->stock_export_number == '') { ?>
																						<?php if(has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit')){ ?>							
																							<a href="javascript:void(0)" onclick="create_export_stock(<?php echo fe_htmldecode($id); ?>); return false;" class="btn btn-warning pull-right mright15">
																								<?php echo _l('create_export_stock'); ?>
																							</a>															
																						<?php } ?>
																					<?php }	else if($order->stock_export_number !=''){ ?>
																						<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_delivery#'.$order->stock_export_number); ?>" class="btn pull-right"><?php echo _l('view_export_stock'); ?></a>
																					<?php } ?>	
																				<?php }	?>
																			<?php }	?>
																		<?php }	?>
																	<?php }	?>
																</div>
															<?php }
															else {
																$estimate_data = fe_check_estimate_order($order->id);
																if($is_return_order && $order->return_reason_type == 'return_for_maintenance_repair' && count($estimate_data) > 0){
																	$estimate_tootip = '';
																	$estimate_tootip = count($estimate_data).' '._l('fe_item');

																	if(!is_numeric($order->estimate_id)){ ?>
																		<div class="col-md-12">
																			<a href="<?php echo admin_url('fixed_equipment/create_estimate_order/'.$order->id); ?>" 
																				data-toggle="tooltip" 
																				data-placement="top" 
																				data-original-title="<?php echo fe_htmldecode($estimate_tootip); ?>" 
																				class="btn btn-primary pull-right">
																				<?php echo _l('create_new_estimate'); ?>
																			</a>
																		</div>
																	<?php } else { ?>
																		<div class="col-md-12">
																			<a href="<?php echo admin_url('estimates/list_estimates#'.$order->estimate_id); ?>" 
																				data-toggle="tooltip" 
																				data-html="true"
																				data-placement="bottom" 
																				data-original-title="<?php echo _l('view_estimate'); ?>" 
																				class="btn pull-right">
																				<?php echo _l('view_estimate'); ?>
																			</a>
																		</div>
																	<?php } 
																}
																if($order->approve_status == 0){ ?>
																	<div class="col-md-6">
																		<button class="btn btn-success" onclick="approve_return_order(1)"><?php echo _l('fe_accept'); ?></button>
																	</div>
																	<div class="col-md-6">
																		<button class="btn btn-warning" onclick="approve_return_order(-1)"><?php echo _l('fe_reject'); ?></button>
																	</div>
																<?php } ?>
															<?php }	?>
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<hr>
									</div>
								<?php } ?>

								<div class="col-md-12">
									<div class="panel-body bottom-transaction">
										<a href="<?php echo admin_url('fixed_equipment/order_list'); ?>" class="btn btn-default"><?php echo _l('close'); ?></a>
										<?php
										if(!$is_return_order){
											if($order->number_invoice == ""){ ?>
												<?php if(has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit')){ ?>
													<a href="<?php echo admin_url('fixed_equipment/create_invoice_detail_order/'.$id); ?>" class="btn btn-primary pull-right">
														<?php echo _l('create_invoice'); ?>
													</a>
												<?php } ?>
											<?php }else{ ?>
												<a href="<?php echo admin_url('invoices#'.$invoice->id); ?>" class="btn pull-right"><?php echo _l('view_invoice'); ?></a>
											<?php }} ?>

											<?php 
											if($order->approve_status == 1 && $is_return_order && !in_array($order->return_reason_type, ['return_and_get_money_back'])){ 

												$original_cart_data = $this->fixed_equipment_model->get_cart($order->original_order_id);
												if($order->process_invoice == 'off' && $original_cart_data->invoice != '' && $order->return_reason_type == 'return_and_get_money_back'){
													if($order->return_type == 'partially'){ ?>
														<?php if(has_permission('fixed_equipment_order_list', '', 'edit')){ ?>
															<a href="<?php echo admin_url('fixed_equipment/update_invoice/'.$id.'/'.$order->original_order_id); ?>" class="btn btn-warning pull-right mright15">
																<?php echo _l('fe_update_invoice'); ?>
															</a>
														<?php } ?>
													<?php }
													else{ ?>
														<?php if(has_permission('fixed_equipment_order_list', '', 'edit')){ ?>
															<a href="<?php echo admin_url('fixed_equipment/cancel_invoice/'.$id.'/'.$order->original_order_id); ?>" class="btn btn-warning pull-right mright15">
																<?php echo _l('fe_cancel_invoice'); ?>
															</a>
														<?php } ?>
													<?php }
												}
												if(!in_array($order->return_reason_type, ['return_and_get_money_back'])){ 
													if($order->stock_import_number == 0){ ?>
														<?php if((has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit')) && $order->return_reason_type != 'return_for_maintenance_repair') { ?>
															<button class="btn btn-success create_import_stock pull-right mright15">
																<?php echo _l('fe_create_import_stock'); ?>													
															</button>
														<?php } ?>
													<?php }else{ ?>
														<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_receiving#'.$order->stock_import_number); ?>" class="btn pull-right"><?php echo _l('fe_view_import_stock'); ?></a>
													<?php } ?>
												<?php } ?>
											<?php } ?>

											<?php 
											if(!$is_return_order){ 
												if (($order->stock_export_number == '' || !$goods_delivery_exist) && $order->number_invoice != '') { ?>
													<?php if(has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit')){ ?>							
														<a href="javascript:void(0)" onclick="create_export_stock(<?php echo fe_htmldecode($id); ?>); return false;" class="btn btn-warning pull-right mright15">
															<?php echo _l('create_export_stock'); ?>
														</a>															
													<?php } ?>
												<?php }	else if($order->stock_export_number !=''){ ?>
													<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_delivery#'.$order->stock_export_number); ?>" class="btn pull-right"><?php echo _l('view_export_stock'); ?></a>
												<?php } ?>	
											<?php }	?>




											<?php if($order->channel_id == 6 && !$is_return_order){ 
												if(fe_get_status_modules('purchase') == true){ 
													if(fe_get_status_modules('warehouse') == true){ 
														?>
														<button class="btn btn-danger pull-right mright15 inventory_check" onclick="inventory_check('<?php echo fe_htmldecode($order->order_number) ?>')">
															<?php 	echo _l('fe_inventory_check'); ?>
														</button>
														<?php 
													}
												}
												if($order->status == 0){ ?>
													<div class="pull-right">
														<?php echo form_open(admin_url('fixed_equipment/pre_order_hand_over'),array('id'=>'form_pre_order_hand_over')); ?>	            
														<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
														<button class="btn btn-success pull-right mright15">
															<?php echo _l('omni_hand_over'); ?>
														</button>
														<div class="pull-right mright15">
															<div class="form-group hanover_option no-mbot">
																<select class="selectpicker display-block" required data-width="100%" name="seller" data-none-selected-text="<?php echo _l('staff'); ?>" data-live-search="true">
																	<option value=""></option>
																	<?php foreach ($staffs as $key => $value) { ?>
																		<option value="<?php echo fe_htmldecode($value['staffid']); ?>"><?php echo fe_htmldecode($value['firstname'].' '.$value['lastname']); ?></option>
																	<?php } ?>
																</select>
															</div>
														</div>
														<?php echo form_close(); ?>	 
													</div>
												<?php } } ?>
											</div>
										</div>
									</div>
								</div>  



								<?php if(false){ ?>
								</div>
								<div role="tabpanel" class="tab-pane ptop10" id="refund">
									<?php $this->load->view('orders/includes/refund'); ?>
								</div>
							</div>
						<?php } ?>             
					</div>
				</div>

				<div class="modal fade" id="chosse" tabindex="-1" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<span class="add-title"><?php echo _l('please_let_us_know_the_reason_for_canceling_the_order') ?></span>
								</h4>
							</div>
							<div class="modal-body">
								<div class="col-md-12">
									<?php echo render_textarea('cancel_reason','cancel_reason',''); ?>
								</div>
							</div>
							<div class="clearfix">               
								<br>
								<br>
								<div class="clearfix">               
								</div>
								<div class="modal-footer">
									<button class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
									<button type="button" data-status="8" class="btn btn-danger cancell_order"><?php echo _l('cancell'); ?></button>
								</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
				</div><!-- /.modal -->

				<div class="modal fade" id="inventory_check" tabindex="-1" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<span class="add-title"><?php echo _l('fe_inventory_check') ?></span>
								</h4>
							</div>
							<div class="modal-body">
								<table class="table inventory_check_table">
									<thead>
										<tr>
											<th scope="col"></th>
											<th scope="col"><?php echo _l('fe_item'); ?></th>
											<th scope="col"><?php echo _l('fe_quantity'); ?></th>
											<th scope="col"><?php echo _l('fe_quantity_in_stock'); ?></th>
											<th scope="col"><?php echo _l('fe_difference'); ?></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							<div class="clearfix">               
								<br>
								<br>
								<div class="clearfix">               
								</div>
								<div class="modal-footer">
									<?php echo form_open(admin_url('fixed_equipment/create_purchase_request'),array('id'=>'form_create_purchase_request')); ?>	            
									<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
									<button class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
									<button type="submit" class="btn btn-danger" ><?php echo _l('fe_create_purchase_request'); ?></button>
									<?php echo form_close(); ?>	 
								</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
				</div><!-- /.modal -->

				<div class="modal fade" id="reject_reason" tabindex="-1" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<span class="add-title"><?php echo _l('fe_please_enter_the_reason_for_the_refusal') ?></span>
								</h4>
							</div>
							<div class="modal-body">
								<div class="col-md-12">
									<?php echo render_textarea('return_reason','',''); ?>
								</div>
							</div>
							<div class="clearfix">               
								<br>
								<br>
								<div class="clearfix">               
								</div>
								<div class="modal-footer">
									<button class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
									<button type="button" data-status="8" class="btn btn-danger reject_order"><?php echo _l('fe_reject'); ?></button>
								</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
				</div><!-- /.modal -->



				<div class="modal fade" id="create_import_stock_modal" tabindex="-1" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<span class="add-title"><?php echo _l('omni_please_select_a_warehouse') ?></span>
								</h4>
							</div>
							<div class="modal-body">
								<div class="col-md-12">
									<?php echo render_select('warehouse_id', $warehouses, array('id', array('code', 'name'))); ?>
								</div>
							</div>
							<div class="clearfix">               
								<br>
								<br>
								<div class="clearfix">               
								</div>
								<div class="modal-footer">
									<button class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
									<?php if(has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit') || is_admin()){ ?>
										<button type="button" data-status="8" class="btn btn-danger create_import_stock_btn"><?php echo _l('create_import_stock'); ?></button>
									<?php } ?>
								</div>
							</div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
				</div><!-- /.modal -->


				<div class="modal fade" id="add_new_assets_maintenances" tabindex="-1" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">
									<span class="add-title hide"><?php echo _l('fe_create_asset_maintenance'); ?></span>
									<span class="edit-title"><?php echo _l('fe_edit_asset_maintenance'); ?></span>
								</h4>
							</div>
							<?php echo form_open(admin_url('fixed_equipment/assets_maintenances?redirect='.urlencode('fixed_equipment/view_order_detailt/'.$order->id).'&rel_type=cart_detailt&rel_id='.$order->id),array('id'=>'assets_maintenances-form')); ?>
							<div class="modal-body">
								<?php 
								$this->load->view('maintenance/maintenance_modal_content.php');
								?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
								<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
							</div>
							<?php echo form_close(); ?>                 
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->

				<input type="hidden" name="goods_delivery_id" value="<?php echo fe_htmldecode($order->stock_export_number) ?>">
				<input type="hidden" name="are_you_sure_you_want_to_accept_returns" value="<?php echo _l('fe_are_you_sure_you_want_to_accept_returns'); ?>">
				<input type="hidden" name="please_select_a_warehouse" value="<?php echo _l('omni_please_select_a_warehouse'); ?>">


				<?php init_tail(); ?>
				<?php require 'modules/fixed_equipment/assets/js/orders/view_order_detail_js.php';?>

			</body>
			</html>