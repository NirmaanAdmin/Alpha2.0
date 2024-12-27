<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php 

$currency_name = '';
if(isset($base_currency)){
	$currency_name = $base_currency->name;
}

$arr_ship_from = []; 
$arr_ship_to = []; 
if($packing_list->billing_street != ''){
	$arr_ship_from[] = $packing_list->billing_street; 
}
if($packing_list->billing_city != ''){
	$arr_ship_from[] = $packing_list->billing_city; 
}
if($packing_list->billing_state != ''){
	$arr_ship_from[] = $packing_list->billing_state; 
}
if($packing_list->billing_zip != ''){
	$arr_ship_from[] = $packing_list->billing_zip; 
}
if($packing_list->billing_country != ''){
	$arr_ship_from[] = get_country_short_name($packing_list->billing_country); 
}

if($packing_list->shipping_street != ''){
	$arr_ship_to[] = $packing_list->shipping_street; 
}
if($packing_list->shipping_city != ''){
	$arr_ship_to[] = $packing_list->shipping_city; 
}
if($packing_list->shipping_state != ''){
	$arr_ship_to[] = $packing_list->shipping_state; 
}
if($packing_list->shipping_zip != ''){
	$arr_ship_to[] = $packing_list->shipping_zip; 
}
if($packing_list->shipping_country != ''){
	$arr_ship_to[] = get_country_short_name($packing_list->shipping_country); 
}

$ship_from = ((count($arr_ship_from) > 0) ? implode(', ', $arr_ship_from) : ''); 
$ship_to = ((count($arr_ship_to) > 0) ? implode(', ', $arr_ship_to) : ''); 


?>
<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-12">
						<h4 class="heading"><?php echo fe_htmldecode($title); ?></h4>
						<hr class="hr-color">
					</div>
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-12">
								<table class="table">
									<tbody>
										<?php 
										if($packing_list->packing_list_number != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_packing_list_number'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->packing_list_number) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->sales_order_reference != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_sales_order_reference'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->sales_order_reference) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->clientid != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_customer'); ?></strong></td>
												<td><?php echo get_contact_full_name($packing_list->clientid) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->datecreated != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_date_created'); ?></strong></td>
												<td><?php echo _dt($packing_list->datecreated) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($ship_from != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_ship_from'); ?></strong></td>
												<td><?php echo fe_htmldecode($ship_from) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($ship_to != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_ship_to'); ?></strong></td>
												<td><?php echo fe_htmldecode($ship_to) ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->width != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_width'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->width).' '._l('fe_millimeter'); ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->height != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_height'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->height).' '._l('fe_millimeter'); ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->lenght != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_lenght'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->lenght).' '._l('fe_millimeter'); ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->weight != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_weight'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->weight).' '._l('fe_gram'); ?></td>
											</tr>
										<?php } ?>

										<?php 
										if($packing_list->volume != ''){ ?>
											<tr>
												<td width="30%"><strong><?php echo _l('fe_volume'); ?></strong></td>
												<td><?php echo fe_htmldecode($packing_list->volume).' m3'; ?></td>
											</tr>
										<?php } ?>

										

									</tbody>
								</table>

							</div>
							<div class="col-md-12">
								<span class="pull-right mbot10 italic"><?php echo _l('fe_currency').': '.$currency_name; ?></span>																
							</div>
							<!-- Content -->
							<div class="col-md-12">
								<div class="table-responsive s_table">
									<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
										<thead>
											<tr>
												<th width="55%" align="left"><?php echo _l('fe_item'); ?></th>
												<th width="10%" align="left" class="qty"><?php echo _l('fe_quantity'); ?></th>
												<th width="15%" align="left"  valign="center"><?php echo _l('fe_price'); ?></th>
												<th width="15%" align="left"><?php echo _l('fe_line_total'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php 
											if(isset($packing_list_detailts)){ 
												foreach ($packing_list_detailts as $key => $value) { ?>
													<tr>
														<td><?php echo fe_htmldecode($value['commodity_name']); ?></td>
														<td><?php echo fe_htmldecode($value['quantity']); ?></td>
														<td><?php echo fe_htmldecode($value['unit_price']); ?></td>
														<td><?php echo fe_htmldecode($value['total_amount']); ?></td>
													</tr>
												<?php }} ?>
											</tbody>
										</table>
									</div>
								</div>
								<!-- End content -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php init_tail(); ?>
</body>
</html>