<?php hooks()->do_action('head_element_client'); ?>
<?php if($detailt_product != null) { ?>
	<?php $id = $detailt_product->id;
	$currency_name = '';
	if(isset($base_currency)){
		$currency_name = $base_currency->name;
	}
	$array_list_id = [];
	if(isset($_COOKIE['fe_cart_id_list'])){
		$list_id = $_COOKIE['fe_cart_id_list'];
		if($list_id){
			$array_list_id = explode(',',$list_id);
		}
	}


	$array_list_id_booking = [];
	if(isset($_COOKIE['fe_cart_id_list_booking'])){
		$list_id = $_COOKIE['fe_cart_id_list_booking'];
		if($list_id){
			$array_list_id_booking = explode(',',$list_id);
		}
	}


	$tax_value = 0;
	
	$user_id = '';
	if(is_client_logged_in()) {
		$user_id = get_client_user_id();
	}
	$date = date('Y-m-d');
	$discount_percent = 0;
	$prices_discount  = 0;
	$has_classify = ($detailt_product->for_sell == 1 && $detailt_product->for_rent != 1 ? 0 : 1);
	?>
	<div class="wrapper row">
		<input type="hidden" name="parent_id" value="<?php echo htmlentities($id); ?>">
		<input type="hidden" name="id" value="<?php echo htmlentities($id); ?>">
		<div class="preview col-md-6">						
			<div class="preview-pic tab-content">
				<?php 
				$date = date('Y-m-d');
				$html_listimage = '';
				$active = 'active';
				$type_item = 'models';
				$item_id = $detailt_product->model_id;
				if($detailt_product->type != 'asset'){
					$type_item = $detailt_product->type;
					$item_id = $detailt_product->id;
				}
				$src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item); ?>
				<div class="contain_image tab-pane <?php echo fe_htmldecode($active); ?>" id="pic-1">
					<img src="<?php echo fe_htmldecode($src); ?>" />
				</div>
				<?php
				$active = '';
				$html_listimage.='<li class="'.fe_htmldecode($active).'"><a data-target="#pic-1" data-toggle="tab"><img src="'.fe_htmldecode($src).'" /></a></li>';
				?>		  	  
			</div>		
		</div>

		<div class="details col-md-6">
			<h3 class="product-title"><?php echo ($detailt_product->series != '' ? $detailt_product->series.' ' : "").''.$detailt_product->assets_name; ?></h3>
			<h3 class="product-title sub hide"></h3>
			<span class="product-description"><a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/index/1/'.$group_id); ?>"><?php echo _l('fe_group').': '.$group; ?></a></span>

			<h5 class="alert alert-warning">
				<span class="new-price text-success display-grid">
					<?php if($detailt_product->for_rent == 1 && $detailt_product->for_sell == 1){ ?>
						<span class="price text-danger">
							<?php echo _l('fe_selling_price').': '.app_format_money($detailt_product->selling_price, $currency_name); ?>	
						</span>	
						<span class="price text-danger">
							<?php echo _l('fe_rental_price').': '.app_format_money($detailt_product->rental_price, $currency_name).'/ '.(is_numeric($detailt_product->renting_period) ? $detailt_product->renting_period + 0 : '').' '._l('fe_'.$detailt_product->renting_unit.'_s'); ?>	
						</span>
					<?php } elseif($detailt_product->for_rent != 1 && $detailt_product->for_sell == 1) { ?>
						<span class="price text-danger">
							<?php echo _l('fe_selling_price').': '.app_format_money($detailt_product->selling_price, $currency_name); ?>	
						</span>	
					<?php } elseif($detailt_product->for_rent == 1 && $detailt_product->for_sell != 1){ ?>
						<span class="price text-danger">
							<?php echo _l('fe_rental_price').': '.app_format_money($detailt_product->rental_price, $currency_name).'/ '.(is_numeric($detailt_product->renting_period) ? $detailt_product->renting_period + 0 : '').' '._l('fe_'.$detailt_product->renting_unit.'_s'); ?>	
						</span>
					<?php } ?>

					<?php //echo app_format_money($price, $currency_name); ?>
				</span>	  
			</h5>
			<input type="hidden" name="quantity_available" value="<?php echo fe_htmldecode($amount_in_stock); ?>">
			<input type="hidden" name="item_type" value="<?php echo fe_htmldecode($detailt_product->type); ?>">
			<input type="hidden" name="renting_unit" value="<?php echo fe_htmldecode($detailt_product->renting_unit); ?>">
			<input type="hidden" name="has_classify" value="<?php echo fe_htmldecode($has_classify); ?>">
			<input type="hidden" name="msg_classify" value="<?php echo _l('please_choose'); ?>">

			<div class="action row">
				<div class="col-md-12">
					<div class="pb-1 display-grid">
						<?php 	
						$rental_date = date('Y-m-d');
						$rental_time = date('Y-m-d').' to '.date('Y-m-d');
						$pickup_time = '';
						$dropoff_time = '';
						$booked = false;
						if(in_array($id, $array_list_id_booking)){
							$rental_unit = fe_get_cookie_value_from_id($id, 'fe_cart_id_list_booking', 'fe_cart_renting_unit_list_booking');
							if($rental_unit == 'hour'){
								$rental_date = fe_get_cookie_value_from_id($id, 'fe_cart_id_list_booking', 'fe_cart_rental_date_list_booking');
								$pickup_time = fe_get_cookie_value_from_id($id, 'fe_cart_id_list_booking', 'fe_cart_pickup_time_list_booking');
								$dropoff_time = fe_get_cookie_value_from_id($id, 'fe_cart_id_list_booking', 'fe_cart_dropoff_time_list_booking');
							}
							else{
								$rental_time = fe_get_cookie_value_from_id($id, 'fe_cart_id_list_booking', 'fe_cart_rental_time_list_booking');
							}
							$booked = true;
						}

						$selled = false;
						if(in_array($id, $array_list_id)){
							$selled = true;
						}
						$rental_item_class = '';
						if($selled == true){
							$rental_item_class .= ' lock';
						}
						if($detailt_product->for_rent != 1){
							$rental_item_class .= ' hide';
						}
						?>
						<!-- Rental -->
						<div class="rental_item_frame border-light p10<?php echo fe_htmldecode($rental_item_class) ?>">
							<input type="hidden" name="parent_id">
							<input type="hidden" name="check_classify" value="1">
							<input type="hidden" name="has_variation" value="">
							<div class="alert alert-danger hide enter_full_info_alert">
								<?php echo _l('fe_please_enter_full_information'); ?>
							</div>
							<div class="row rent_by_day<?php echo ($detailt_product->renting_unit != 'hour' ? '' : ' hide') ?>">
								<div class="col-md-12">
									<div class="form-group">
										<label for='start_date'><small class="req text-danger">* </small><?php echo _l('fe_rental_time'); ?></label>
										<input type="text" name="rental_time" class="form-control" value="<?php echo fe_htmldecode($rental_time); ?>">
									</div>
								</div>
							</div>

							<?php 
							$hour = [];
							for ($i=1; $i <= 24; $i++) { 
								$hour[] = ['value' => $i, 'name' => str_pad($i, 2, '0', STR_PAD_LEFT).':00'];
							}				
							?>
							<div class="row rent_by_hour<?php echo ($detailt_product->renting_unit == 'hour' ? '' : ' hide') ?>" >
								<div class="col-md-12">
									<div class="form-group">
										<label for='start_date'><small class="req text-danger">* </small><?php echo _l('fe_rental_date'); ?></label>
										<input type="text" name="rental_date" class="form-control" value="<?php echo fe_htmldecode($rental_date); ?>">
									</div>
								</div>
								<div class="col-md-6">
									<?php echo render_select('pickup_time', $hour, array('value', 'name'), '<small class="req text-danger">* </small> '._l('fe_pickup_time'), $pickup_time); ?>
								</div>
								<div class="col-md-6">
									<?php echo render_select('dropoff_time', $hour, array('value', 'name'), '<small class="req text-danger">* </small> '._l('fe_dropoff_time'), $dropoff_time); ?>
								</div>
							</div>
								<?php if($amount_in_stock > 0){ ?>
								<button type="button" class="rental_item rental_item_1 btn btn-primary pull-right mtop10 <?php echo ($booked == true ? '' : ' hide') ?>" data-id=""><i class="fa fa-check"></i> <?php echo _l('fe_update'); ?></button>
								<button type="button" class="rental_item rental_item_2 btn btn-success pull-right mtop10 w160px <?php echo ($booked == true ? ' hide' : '') ?>" data-id=""><i class="fa fa-angle-right"></i> <?php echo _l('fe_booking'); ?></button>
								<?php }else{ ?>			
									<button class="btn btn-default pull-right w160px mleft10" type="button"><?php echo _l('out_of_stock'); ?></button>	
								<?php } ?>	
						</div>
						<!-- END Rental -->


						<!-- Buy -->
						<?php 
						$buy_item_class = '';
						if($booked == true){
							$buy_item_class .= ' lock';
						}
						if($detailt_product->for_sell != 1){
							$buy_item_class .= ' hide';
						}
						if(!($detailt_product->for_sell == 1 && $detailt_product->for_rent != 1)){
							$buy_item_class .= ' border-light p10';
						}
						$read_only_input = '';
						if($amount_in_stock == 1){
							$read_only_input = ' readonly';
						}
						?>
						<div class="buy_item_frame mtop10<?php echo fe_htmldecode($buy_item_class) ?>">
							<div class="row">
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon minus<?php echo ($read_only_input == '' ? '' : ' cursor-not-allowed') ?>" <?php echo ($read_only_input == '' ? 'onclick="change_qty(-1);"' : '') ?> >
											<i class="fa fa-minus"></i>
										</span>
										<input id="quantity" class="form-control text-center" type="number" value="1" min="1" max="<?php echo fe_htmldecode($amount_in_stock); ?>" <?php echo fe_htmldecode($read_only_input); ?>>
										<span class="input-group-addon plus<?php echo ($read_only_input == '' ? '' : ' cursor-not-allowed') ?>" <?php echo ($read_only_input == '' ? 'onclick="change_qty(1);"' : '') ?>>
											<i class="fa fa-plus"></i>				      
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<?php if($amount_in_stock > 0){ ?>
										<button class="btn btn-primary pull-right w160px mleft10 add_to_cart add_to_cart_1 <?php if(in_array($id, $array_list_id)){ echo ''; }else{ echo 'hide'; } ?>" type="button">
											<i class="fa fa-check"></i> <?php echo _l('added'); ?>
										</button>	
										<button class="btn btn-success pull-right w160px mleft10 add_to_cart add_to_cart_2 <?php if(in_array($id, $array_list_id)){ echo 'hide'; }else{ echo ''; } ?>" type="button">
											<i class="fa fa-angle-right"></i> <?php echo _l('fe_add_to_cart'); ?>
										</button>
										<?php 
									}
									else{ ?>			
										<button class="btn btn-default pull-right w160px mleft10" type="button"><?php echo _l('out_of_stock'); ?></button>	
									<?php } ?>	
								</div>
							</div>
						</div>
						<!-- END Buy -->


					</div>









				</div>
			</div>
		</div>
	</div>

	<?php 
	if($detailt_product->description != ''){ ?>
		<hr>
		<div class="col-md-12">	
			<div class="wrap_contents long_descriptions" >
				<?php echo nl2br($detailt_product->description); ?>
			</div>
			<div class="wrap_contents long_descriptions sub hide">
			</div>
			<br>
		</div>
	<?php } ?>

	<?php 
	if(count($product) > 0){ 
		?>
		<div class="right-detail">
			<div class="line"><?php echo _l('fe_suggested'); ?></div>
			<div id="slidehind">    
				<div class="frame-slide">
					<div class="frame" id="frameslide">
						<?php 
						foreach ($product as $key => $item) { ?>
							<a href="<?php 	echo site_url('fixed_equipment/fixed_equipment_client/detailt/'.$item['id']); ?>">
								<?php 
								$type_item = 'models';
								$item_id = $item['model_id'];
								if($item['type'] != 'asset'){
									$type_item = $item['type'];
									$item_id = $item['id'];
								}
								?>
								<div class="contain-image">
									<img src="<?php echo $this->fixed_equipment_model->get_image_items($item_id, $type_item); ?>">									
								</div>
								<div class="name"><?php echo fe_htmldecode($item['name']); ?></div>
								<div class="price"><?php echo app_format_money($item['price'],$currency_name); ?></div>
							</a>
						<?php } ?>               
					</div>
				</div>
				<button class="btn btn-primary leftLst" onclick="scroll_slide(-1);"><i class="fa fa-chevron-left"></i></button>
				<button class="btn btn-primary rightLst" onclick="scroll_slide(1);"><i class="fa fa-chevron-right"></i></button>      	
			</div>
		</div>
	<?php } ?>

	<div class="modal fade" id="alert_add" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 alert_content">
							<div class="clearfix"></div>
							<br>
							<br>
							<center class="add_success hide"><h4><?php echo _l('fe_added_to_cart'); ?></h4></center>
							<center class="update_success hide"><h4><?php echo _l('fe_updated_rental_information'); ?></h4></center>
							<center class="add_error hide"><h4><?php echo _l('fe_sorry_the_number_of_current_products_is_not_enough'); ?></h4></center>
							<br>
							<br>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>              
			</div>
		</div>
	</div>
	<input type="hidden" name="token_name" value="<?php echo fe_htmldecode($this->security->get_csrf_token_name()); ?>">
	<input type="hidden" name="token_hash" value="<?php echo fe_htmldecode($this->security->get_csrf_hash()); ?>">
<?php }
else{ ?>
	<br>
	<br>
	<br>
	<br>
	<center>
		<h4>
			<?php echo _l('data_does_not_exist'); ?>			
		</h4>
	</center>

	<br>
	<div class="col-md-12 text-center">
		<a href="javascript:history.back()" class="btn btn-primary">
			<i class="fa fa-long-arrow-left" aria-hidden="true"></i> <?php echo _l('return_to_the_previous_page'); ?></a>
		</div>
	</div>
<?php } ?>

<input type="hidden" name="added_to_cart" value="<?php echo _l('fe_added_to_cart'); ?>">
<input type="hidden" name="updated_rental_information" value="<?php echo _l('fe_updated_rental_information'); ?>">
<input type="hidden" name="sorry_the_number_of_current_products_is_not_enough" value="<?php echo _l('fe_sorry_the_number_of_current_products_is_not_enough'); ?>">

<?php hooks()->do_action('client_pt_footer_js'); ?>

