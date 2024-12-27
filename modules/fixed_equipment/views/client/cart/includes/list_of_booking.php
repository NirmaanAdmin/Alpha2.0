<div class="row booking-list">
	<div class="col-md-12">	
		<div class="panel_s section-heading section-proposals no-mbot">
			<div class="panel-body">
				<h4 class="no-margin section-text"><?php echo _l('fe_bookings'); ?></h4>
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
	if(isset($_COOKIE['fe_cart_id_list_booking'])){
		$list_id = $_COOKIE['fe_cart_id_list_booking'];
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
									<th width="50%" align="center"><?php echo _l('invoice_table_item_heading'); ?></th>
									<th width="20%" align="center"  valign="center"><?php echo _l('fe_rental_price').' ('.$currency_name.')'; ?></th>
									<th width="20%" align="center"  valign="center"><?php echo _l('fe_ownership_time'); ?></th>
									<th width="20%" align="center"><?php echo _l('fe_line_total').' ('.$currency_name.')'; ?></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if($list_id){
									$list_rental_time = $_COOKIE['fe_cart_rental_time_list_booking'];
									$list_rental_date = $_COOKIE['fe_cart_rental_date_list_booking'];
									$list_pickup_time = $_COOKIE['fe_cart_pickup_time_list_booking'];
									$list_dropoff_time = $_COOKIE['fe_cart_dropoff_time_list_booking'];
									$array_list_rental_time = explode(',', $list_rental_time);
									$array_list_rental_date = explode(',', $list_rental_date); 
									$array_list_pickup_time = explode(',', $list_pickup_time); 
									$array_list_dropoff_time = explode(',', $list_dropoff_time);
									$array_list_id = explode(',',$list_id); 
									?>

									<input type="hidden" name="list_id" value="<?php echo fe_htmldecode($list_id); ?>">
									<input type="hidden" name="list_rental_time" value="<?php echo fe_htmldecode($list_rental_time); ?>">
									<input type="hidden" name="list_rental_date" value="<?php echo fe_htmldecode($list_rental_date); ?>">
									<input type="hidden" name="list_pickup_time" value="<?php echo fe_htmldecode($list_pickup_time); ?>">
									<input type="hidden" name="list_dropoff_time" value="<?php echo fe_htmldecode($list_dropoff_time); ?>">

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
													<strong>
														<?php echo app_format_money($data_product->rental_price, '').'/ '.(is_numeric($data_product->renting_period) ? $data_product->renting_period + 0 : '').' '._l('fe_'.$data_product->renting_unit.'_s'); ?>
													</strong>
												</td>
												<td align="center" class="middle">
													<?php 
													if($data_product->renting_unit == 'hour'){
														echo fe_htmldecode($array_list_rental_date[$key].' ('.str_pad($array_list_pickup_time[$key], 2, '0', STR_PAD_LEFT).':00'.' - '.str_pad($array_list_dropoff_time[$key], 2, '0', STR_PAD_LEFT).':00)');
													}else{
														echo $array_list_rental_time[$key];
													}
													$line_obj = fe_line_total_booking($data_product->rental_price, $data_product->renting_unit, $data_product->renting_period, $array_list_rental_time[$key], $array_list_dropoff_time[$key], $array_list_pickup_time[$key]);
													$line_total = $line_obj->line_total;
													$number_day = $line_obj->number_day;
												?>  
												</td>
												<td align="center" class="middle line_data" data-price="<?php echo fe_htmldecode($data_product->rental_price); ?>" data-number_day="<?php echo fe_htmldecode($number_day); ?>">
													<strong class="line_total">
														<?php
														$sub_total += $line_total;
														echo app_format_money($line_total,''); ?>
													</strong>
												</td>
												<td align="center" class="middle text-danger">
													<i onclick="delete_item_booking(this);" data-id="<?php echo fe_htmldecode($row_id); ?>" data-key="<?php echo fe_htmldecode($key); ?>" data-toggle="tooltip" data-title="<?php echo _l('delete'); ?>" data-placement="top" class=" fa fa-times"></i></td>
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
							<a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/check_out/'.$logged.'/booking'); ?>" class="btn btn-danger pull-right w160px">
								<i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo _l('fe_booking'); ?>
							</a>
						</div>
						<div class="btn-bottom-pusher"></div>
					</div>
				</div>
			</div>
			<div class="content mtop10 fr2 <?php if($cart_empty == 1){ echo 'hide'; } ?>">
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