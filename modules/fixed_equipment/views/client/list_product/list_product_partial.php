<?php 
$date = date('Y-m-d');
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


$user_id = '';
if(is_client_logged_in()) {
	$user_id = get_client_user_id();
}

?>

<div class="row">
	<?php foreach ($product as $item) { ?>
		<div class="col-md-3 grid col-sm-6">
			<div class="grid product-cell">
				<?php 
				$discount_percent = 0;
				$prices_discount  = 0;
				if($discount_percent > 0){ ?>
					<ul class="tag-item right">
						<li class="list-item">
							<div class="primary">
								<div class="content text-white">
									<span class="fs-13 font-italic"><?php echo '-'.$discount_percent.'%' ?></span>
								</div>
							</div>
						</li>					
					</ul>
				<?php } ?>


				<div class="product-image"> 
					<a href="<?php 	echo site_url('fixed_equipment/fixed_equipment_client/detailt/'.$item['id']); ?>"> 
						<?php 
						$type_item = 'models';
						$item_id = $item['model_id'];
						if($item['type'] != 'asset'){
							$type_item = $item['type'];
							$item_id = $item['id'];
						}
						$src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
						?>
						<img class="pic-1" src="<?php echo fe_htmldecode($src); ?>">
					</a>               					                  
				</div>

				<div class="product-content">
					<div class="title"><a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/detailt/'.$item['id']); ?>"><?php echo fe_htmldecode($item['name']); ?></a></div> 
					<div class="price_w">
						<?php if($item['for_rent'] == 1 && $item['for_sell'] == 1){ ?>
							<span class="price text-danger">
								<?php echo _l('fe_selling_price').': '.app_format_money($item['price'], $currency_name); ?>	
							</span>	
							<span class="price text-danger">
								<?php echo _l('fe_rental_price').': '.app_format_money($item['rental_price'], $currency_name).'/ '.(is_numeric($item['renting_period']) ? $item['renting_period'] + 0 : '').' '._l('fe_'.$item['renting_unit'].'_s'); ?>	
							</span>
						<?php } elseif($item['for_rent'] != 1 && $item['for_sell'] == 1) { ?>
							<span class="price text-danger">
								<?php echo _l('fe_selling_price').': '.app_format_money($item['price'], $currency_name); ?>	
							</span>	
						<?php } elseif($item['for_rent'] == 1 && $item['for_sell'] != 1){ ?>
							<span class="price text-danger">
								<?php echo _l('fe_rental_price').': '.app_format_money($item['rental_price'], $currency_name).'/ '.(is_numeric($item['renting_period']) ? $item['renting_period'] + 0 : '').' '._l('fe_'.$item['renting_unit'].'_s'); ?>	
							</span>
						<?php } ?>

					</div>
				</div>

				<div class="pb-1 add-cart">
					<input type="hidden" name="has_variation" value="<?php echo fe_htmldecode($item['has_variation']); ?>">
					<input type="hidden" name="for_sell" value="<?php echo fe_htmldecode($item['for_sell']); ?>">
					<input type="hidden" name="renting_unit" value="<?php echo fe_htmldecode($item['renting_unit']); ?>">
					<input type="hidden" name="item_type" value="<?php echo fe_htmldecode($item['type']); ?>">
					<?php
					$buyed = false;
					if(in_array($item['id'],$array_list_id)){
						$buyed = true;
					}

					$rented = false;
					if(in_array($item['id'],$array_list_id_booking)){
						$rented = true;
					}

					if($item['w_quantity'] != 0){  ?>

						<input type="number" name="qty" class="form-control qty<?php if(!($item['for_rent'] != 1 && $item['for_sell'] == 1)){ echo ' hide'; } ?>" value="1" min="1" max="<?php echo fe_htmldecode($item['w_quantity']); ?>" data-w_quantity="<?php echo fe_htmldecode($item['w_quantity']); ?>">

						<button type="button" class="added btn btn-primary add_to_cart_1 <?php echo (($buyed == true || $rented == true) ? '' : 'hide') ?>" 
							data-buyed="<?php echo fe_htmldecode($buyed); ?>"
							data-rented="<?php echo fe_htmldecode($rented); ?>" 
							data-id="<?php echo fe_htmldecode($item['id']); ?>">
								<span class="added_btn_text<?php echo (($buyed == true) ? '' : ' hide'); ?>"><i class="fa fa-check"></i> <?php echo _l('fe_added'); ?></span>
								<span class="booked_btn_text<?php echo (($rented == true) ? '' : ' hide'); ?>"><i class="fa fa-check"></i> <?php echo _l('fe_booked'); ?></span>
						</button>	

						<button type="button" class="add_cart btn btn-success <?php echo (($buyed == true || $rented == true) ? 'hide' : '') ?>" 
							data-buyed="<?php echo fe_htmldecode($buyed); ?>" 
							data-rented="<?php echo fe_htmldecode($rented); ?>" 
							data-id="<?php echo fe_htmldecode($item['id']); ?>">
							<i class="fa fa-shopping-cart"></i> <?php echo _l('fe_add_to_cart'); ?>
						</button>

					<?php } else { ?>
						<button class="btn btn-default"><?php echo _l('out_of_stock'); ?></button>
					<?php } ?>
				</div>

			</div>
		</div>
	<?php } ?>	             
</div>

