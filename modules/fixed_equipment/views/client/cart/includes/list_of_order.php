<div class="row order-list">
	<div class="col-md-12">	
		<div class="panel_s section-heading section-proposals no-mbot">
			<div class="panel-body">
				<h4 class="no-margin section-text"><?php echo _l('fe_orders'); ?></h4>
			</div>
		</div>
	</div>
	<?php
	$currency_name = '';
	if(isset($base_currency)){
		$currency_name = $base_currency->name;
	}

	$cart_empty = 0;
	$list_id = [];
	if(isset($_COOKIE['fe_cart_id_list'])){
		$list_id = $_COOKIE['fe_cart_id_list'];
		if($list_id){
			$cart_empty = 1;
		}
	}
	$sub_total = 0;
	$date = date('Y-m-d');
	$user_id = '';
	if(is_client_logged_in()) {
		$user_id = get_client_user_id();
	}
	?>
	<div class="col-md-12">	
		<div class="panel_s invoice accounting-template fr1 <?php if($cart_empty == 0){ echo 'hide'; } ?>">
			<div class="panel-body mtop10">
				<div class="row">

				</div>
				<div class="fr1">
					<div class="table-responsive s_table">
						<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
							<thead>
								<tr>
									<th width="50%" align="center"><?php echo _l('fe_item'); ?></th>
									<th width="10%" align="center" class="qty"><?php echo _l('fe_quantity'); ?></th>
									<th width="20%" align="center"  valign="center"><?php echo _l('fe_price').' ('.$currency_name.')'; ?></th>
									<th width="20%" align="center"><?php echo _l('fe_line_total').' ('.$currency_name.')'; ?></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if($list_id){
									$array_list_id = explode(',',$list_id);
									$list_qty = $_COOKIE['fe_cart_qty_list'];
									$array_list_qty = explode(',',$list_qty); ?>
									<input type="hidden" name="list_id_product" value="<?php echo fe_htmldecode($list_id); ?>">
									<input type="hidden" name="list_qty_product" value="<?php echo fe_htmldecode($list_qty); ?>">
									<?php foreach ($array_list_id as $key => $row_id) { 
										$data_product = $this->fixed_equipment_model->get_assets($row_id);
										if($data_product){

											$w_qty = 1;
											if($data_product->type == 'license'){
												$avail = 0;
												$data_total = $this->fixed_equipment_model->count_total_avail_seat($row_id);
												if($data_total){
													$avail = $data_total->avail;
												}
												$w_qty = $avail;
											}
											elseif($data_product->type == 'accessory'){
												$w_qty = $data_product->quantity - $this->fixed_equipment_model->count_checkin_asset_by_parents($row_id);
											}
											elseif($data_product->type == 'component'){
												$w_qty = $data_product->quantity - $this->fixed_equipment_model->count_checkin_component_by_parents($row_id);
											}
											elseif($data_product->type == 'consumable'){
												$w_qty = $data_product->quantity - $this->fixed_equipment_model->count_checkin_asset_by_parents($row_id);
											}

											$prices  = $data_product->selling_price;
											$type_item = 'models';
											$item_id = $data_product->model_id;
											if($data_product->type != 'asset'){
												$type_item = $data_product->type;
												$item_id = $data_product->id;
											}
											$src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
											?>
											<tr class="main">
												<td>
													<a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/detailt/'.$row_id); ?>">
														<img class="product pic" src="<?php echo fe_htmldecode($src); ?>">  
														<strong>
															<?php
															echo (($data_product->series != '' ? $data_product->series.' ' : '').''.$data_product->assets_name);
															?>
														</strong>
													</a>
												</td>
												<td align="center" class="middle">
													<input type="number" onchange="change_cart_qty(this);" min="1" max="<?php echo fe_htmldecode($w_qty); ?>" value="<?php echo fe_htmldecode($array_list_qty[$key]); ?>" data-price="<?php echo fe_htmldecode($prices); ?>" data-key="<?php echo fe_htmldecode($key); ?>" class="form-control line_data qty" placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
												</td>
												<td align="center" class="middle">
													<strong><?php echo app_format_money($prices,''); ?></strong>
												</td>
												<td align="center" class="middle">
													<strong class="line_total_order">
														<?php
														$line_total = (int)$array_list_qty[$key]*$prices;
														$sub_total += $line_total;
														echo app_format_money($line_total,''); ?>
													</strong>
												</td>
												<td align="center" class="middle text-danger">
													<i onclick="delete_item(this);" data-id="<?php echo fe_htmldecode($row_id); ?>" data-key="<?php echo fe_htmldecode($key); ?>" data-toggle="tooltip" data-title="<?php echo _l('delete'); ?>" data-placement="top" class=" fa fa-times"></i></td>
												</tr>
												<?php     
											}
										}
									}else{ ?>

										<center><?php echo _l('cart_empty'); ?></center>
									<?php  } ?>
								</tbody>
							</table>
						</div>

						<div class="col-md-8 col-md-offset-4">
							<table class="table text-right">
								<tbody>
									<tr id="subtotal">
										<td><span class="bold"><?php echo _l('invoice_total'); ?> :</span>
										</td>
										<td class="subtotal">
											<?php echo app_format_money($sub_total,''); ?>
										</td>
									</tr>



								</tbody>
							</table>
						</div>
					</div>


				</div>
				<div class="row">
					<div class="col-md-12 mtop10">
						<div class="panel-body bottom-transaction">
							<?php 
							$logged = 0;
							if(is_client_logged_in()){
								$logged = 1;
							}
							?>
							<a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/check_out/'.$logged.'/order'); ?>" class="btn btn-danger pull-right w160px">
								<i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo _l('fe_order'); ?>
							</a>
						</div>
						<div class="btn-bottom-pusher"></div>
					</div>
				</div>
			</div>
			<div class="content fr2 mtop10 <?php if($cart_empty == 1){ echo 'hide'; } ?>">
				<div class="panel_s">
					<div class="panel-body">
						<div class="col-md-12 text-center">
							<h4><?php echo _l('cart_empty'); ?></h4>	   		    		
						</div>
						<br>
						<br>
						<br>
						<br>
						<div class="col-md-12 text-center">
							<a href="javascript:history.back()" class="btn btn-primary">
								<i class="fa fa-long-arrow-left" aria-hidden="true"></i> <?php echo _l('return_to_the_previous_page'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>