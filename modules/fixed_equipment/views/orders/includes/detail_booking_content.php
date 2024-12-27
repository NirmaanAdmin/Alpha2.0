<div class="table-responsive s_table">
	<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
		<thead>
			<tr>
				<th width="55%" align="center"><?php echo _l('fe_item'); ?></th>
				<th width="10%" align="center" class="qty"><?php echo _l('fe_rental_price'); ?></th>
				<th width="15%" align="center"  valign="center"><?php echo _l('fe_ownership_time'); ?></th>
				<th width="15%" align="center"><?php echo _l('fe_line_total'); ?></th>
				<?php 
				if($is_return_order && $order->return_reason_type == 'return_for_maintenance_repair' && $order->approve_status == 1){ ?>
					<th width="15%" align="center"><?php echo _l('fe_maintenance'); ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php 
			$sub_total = 0; 
			$date = date('Y-m-d');
			?>

			<?php
			$has_item_tax = false;
			foreach ($order_detait as $key => $item_cart) { 
				if($item_cart['tax']){
					$has_item_tax = true;
				}
				?>
				<tr class="main">
					<td>
						<a href="#">
							<?php 
							$discount_price = 0;
							$type_item = 'models';
							$item_id = fe_get_model_item($item_cart['product_id']);
							$type = fe_get_type_item($item_cart['product_id']);
							if($type != 'asset'){
								$type_item = $type;
								$item_id = $item_cart['product_id'];
							}
							$src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
							?>
							<img class="product pic" src="<?php echo fe_htmldecode($src); ?>">  
							<strong>
								<?php   
								echo fe_htmldecode($item_cart['product_name']);
								?>
							</strong>
						</a>
					</td>
					<td align="center" class="middle">
						<?php 
						echo app_format_money($item_cart['prices'],'').'/ '.(is_numeric($item_cart['renting_period']) ? $item_cart['renting_period'] + 0 : $item_cart['renting_period']).' '._l('fe_'.$item_cart['renting_unit'].'_s');
						?>
					</td>
					<td align="center" class="middle">
						<?php 
						$ownership_string = '';
						if($item_cart['renting_unit'] == 'hour'){
							$ownership_string = _d($item_cart['rental_start_date']).' ('.$item_cart['pickup_time'].':00 - '.$item_cart['dropoff_time'].':00)';
						}
						else{
							$ownership_string = _d($item_cart['rental_start_date']).' '._l('fe_to').' '._d($item_cart['rental_end_date']);
						}
						echo fe_htmldecode($ownership_string); 
						?>
					</td>
					<td align="center" class="middle">
						<strong class="line_total_<?php echo fe_htmldecode($key); ?>">
							<?php
							$line_total = $item_cart['rental_value'];
							$sub_total += $line_total;
							echo app_format_money($line_total,''); ?>
						</strong>
					</td>
					<?php if($is_return_order && $order->return_reason_type == 'return_for_maintenance_repair' && $type == 'asset' && $order->approve_status == 1){ 
						$maintenance_click_event = 'create_maintenance(this)';
						$maintenance_btn_tooltip = _l('fe_create_asset_maintenance');
						$maintenance_btn_class = 'btn-primary';
						if(is_numeric($item_cart['maintenance_id'])){ 
							$maintenance_click_event = 'edit_maintenance(this)';
							$maintenance_btn_tooltip = _l('fe_edit_asset_maintenance');
							$maintenance_btn_class = 'btn-warning';
						} ?>
						<td>
							<button class="btn mtop30 <?php echo fe_htmldecode($maintenance_btn_class); ?>" 
								data-toggle="tooltip" 
								data-placement="bottom" 
								data-original-title="<?php echo fe_htmldecode($maintenance_btn_tooltip); ?>" 
								onclick="<?php echo fe_htmldecode($maintenance_click_event); ?>" 
								data-maintenance_id="<?php echo fe_htmldecode($item_cart['maintenance_id']); ?>" 
								data-id="<?php echo fe_htmldecode($item_cart['id']); ?>" 
								data-product_id="<?php echo fe_htmldecode($item_cart['product_id']); ?>">
								<i class="fa fa-wrench menu-icon"></i>
							</button>
						</td>
					<?php } ?>
				</tr>
			<?php     } ?>
		</tbody>
	</table>
</div>

<div class="col-md-8 col-md-offset-4">
	<table class="table text-right">
		<tbody>
			<tr id="subtotal">
				<td width="50%"><span class="bold"><?php echo _l('invoice_subtotal'); ?> :</span>
				</td>
				<td class="subtotal_s" width="50%">
					<?php
					$sub_total = 0;
					if($order->sub_total){
						$sub_total = $order->sub_total;
					}
					echo app_format_money($sub_total,''); ?>
				</td>
			</tr>
			<?php if($order->discount){
				if($order->discount>0){ ?>
					<tr>
						<td><span class="bold"><?php echo _l('discount'); ?> :</span>
						</td>
						<td>
							<?php

							$price_discount = $order->sub_total * $order->discount/100;
							echo '-'.app_format_money($order->discount,''); ?>
						</td>
					</tr>
				<?php }	} ?>

				<?php if($order->channel == 'manual'){ ?>
					<?php if(is_sale_discount_applied($order)){ ?>
						<tr>
							<td>
								<span class="bold"><?php echo _l('invoice_discount').' :'; ?>
								<?php if(is_sale_discount($order,'percent')){ ?>
									(<?php echo app_format_number($order->discount_percent,true); ?>%)
									<?php } ?></span>
								</td>
								<td class="discount">
									<?php echo '-' . app_format_money($order->discount_total, ''); ?>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>


					<?php if((int)$order->adjustment != 0){ ?>
						<tr>
							<td>
								<span class="bold"><?php echo _l('invoice_adjustment').' :'; ?></span>
							</td>
							<td class="adjustment_t">
								<?php echo app_format_money($order->adjustment, ''); ?>
							</td>
						</tr>
					<?php } ?>

					<?php 
					if(isset($order->shipping)){
						if($order->shipping != "0.00"){ ?>
							<tr>
								<td><span class="bold"><?php echo _l('shipping'); ?> :</span>
								</td>
								<td>
									<?php echo app_format_money($order->shipping,''); ?>
								</td>
							</tr>
						<?php 	}
					}
					?>
					<?php 
					if(isset($order->shipping_tax)){
						if($order->shipping != "0.00"){ ?>
							<tr>
								<td>
									<span class="bold"><?php echo _l('shipping_tax'); ?> :</span>
								</td>
								<td>
									<?php echo app_format_money($order->shipping_tax,''); ?>
								</td>
							</tr>
						<?php 	}
					}
					?>
					<?php 

					if(is_numeric($order->fee_for_return_order) && $order->fee_for_return_order > 0){ ?>
						<tr id="fee_for_return_order">
							<td>
								<span class="bold"><?php echo _l('omni_fee_for_return_order'); ?> :</span>
							</td>
							<td>
								<?php echo app_format_money($order->fee_for_return_order, ''); ?>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<td><span class="bold"><?php echo _l('invoice_total'); ?> :</span>
						</td>
						<td class="total_s">			                              	
							<?php
							$total_s = $order->total;
							echo app_format_money($total_s,''); 
							?>
						</td>
					</tr>
					<?php 
					$invoice_id = '';
					if($order->number_invoice != ''){
						$this->load->model('fixed_equipment/fixed_equipment_model');
						$invoice_id = $this->fixed_equipment_model->get_id_invoice($order->number_invoice); 
					}

					if(is_numeric($invoice_id)){
						$this->load->model('invoices_model');
						$invoice = $this->invoices_model->get($invoice_id);
						$total_paid = sum_from_table(db_prefix().'invoicepaymentrecords',array('field'=>'amount','where'=>array('invoiceid'=>$invoice->id)));
					}
					?>

					<?php if(is_numeric($invoice_id)){ ?>
						<tr class="hide">
							<td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
							<td>
								<?php echo app_format_money($total_paid, $invoice->currency_name); ?>
							</td>
						</tr>

						<tr class="hide">
							<td><span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger ';} ?>bold"><?php echo _l('invoice_amount_due'); ?></span></td>
							<td>
								<span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger';} ?>">
									<?php echo app_format_money($invoice->total_left_to_pay, $invoice->currency_name); ?>
								</span>
							</td>
						</tr>
					<?php } ?>

					<?php
					$total_refund = fe_get_total_refund($order->id);
					if($is_return_order){ ?>
						<tr id="total_refund" class="hide">
							<td><span class="bold"><?php echo _l('omni_total_refund'); ?> :</span>
							</td>
							<td>
								<?php 
								echo app_format_money($total_refund, '');
								?>
							</td>
						</tr>
						<tr id="total_refund"  class="hide">
							<td><span class="bold"><?php echo _l('omni_amount_due'); ?> :</span>
							</td>
							<td>
								<?php 
								$amount_due_s = $total_s - $total_refund;
								if($amount_due_s < 0){
									$amount_due_s = 0;
								}
								echo app_format_money($amount_due_s, '');
								?>
							</td>
						</tr>
					<?php } ?>

					<?php if($order->notes != ''){ ?>
						<tr>
							<td><span class="bold"><?php echo _l('note'); ?> :</span></td>
							<td><?php echo fe_htmldecode($order->notes); ?></td>
						</tr>
					<?php } ?>
					<?php if($order->duedate != '' && $order->channel_id == 6){ ?>
						<tr>
							<td><span class="bold"><?php echo _l('omni_expiration_date'); ?> :</span></td>
							<td><?php echo _d($order->duedate); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>	