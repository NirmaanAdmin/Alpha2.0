<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('head_element_client'); ?>
<div class="col-md-3 left_bar">
	<ul class="nav-tabs--vertical nav" role="navigation">
		<li class="head text-center">
			<h5><?php echo _l('category'); ?></h5>
			<a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/index/1/0/0'); ?>" class="view_all"><?php echo _l('fe_all_items'); ?></a> 
		</li>
		<?php 
		$data['title_group'] = $title_group;
		foreach ($group_product as $key => $value) {
			if($this->fixed_equipment_model->has_product_group($value['id'])){
				$active = '';
				if($value['id'] == $group_id){
					$active = 'active';
					$data['title_group'] = $value['name'];
				}    		
				?>
				<li class="nav-item <?php echo fe_htmldecode($active); ?>">
					<a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/index/1/'.$value['id'].'/0'); ?>" class="nav-link">
						<?php echo fe_htmldecode($value['name']); ?>
					</a>
				</li>
				<?php	
			}
		}
		?>					
	</ul>
</div>
<div class="col-md-9 right_bar">

	<div class="row">
		<?php echo form_open(site_url('fixed_equipment/fixed_equipment_client/search_product/'.$group_id),array('id'=>'invoice-form','class'=>'_transaction_form invoice-form')); ?>
		<div class="col-md-11">
			<input type="text" name="keyword" class="form-control" placeholder="Search for products here ..." value="<?php echo ((isset($keyword) && ($keyword != '')) ? $keyword : '') ?>">
			
		</div>
		<div class="col-md-1">
			<button type="submit" class="btn btn-info pull-right"><i class="fa fa-search"></i></button>
		</div>
		<?php echo form_close(); ?>

	</div>
	<?php $this->load->view('client/list_product/list_product_with_page',$data); ?>
	<hr>
</div>

<div class="modal fade" id="select_variation" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12 title">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">
						</h4>
						<hr>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 image">
						<img src="">
					</div>
					<div class="col-md-8">
						<div class="row">
							<div class="col-md-12 prices"></div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="pb-1 display-grid">
									<!-- Rental -->
									<div class="rental_item_frame border-light p10">
										<input type="hidden" name="parent_id">
										<input type="hidden" name="check_classify" value="1">
										<input type="hidden" name="has_variation" value="">
										<input type="hidden" name="quantity_available" value="">
										<input type="hidden" name="item_type" value="">
										<input type="hidden" name="renting_unit" value="">

										<div class="alert alert-danger hide enter_full_info_alert">
											<?php echo _l('fe_please_enter_full_information'); ?>
										</div>
										<div class="row rent_by_day hide">
											<div class="col-md-12">
												<div class="form-group">
													<label for='start_date'><small class="req text-danger">* </small><?php echo _l('fe_rental_time'); ?></label>
													<input type="text" name="rental_time" class="form-control" value="">
												</div>
											</div>
										</div>

										<?php 
										$hour = [];

										for ($i=1; $i <= 24; $i++) { 
											$hour[] = ['value' => $i, 'name' => str_pad($i, 2, '0', STR_PAD_LEFT).':00'];
										}
										?>
										<div class="row rent_by_hour hide" >
											<div class="col-md-12">
												<div class="form-group">
													<label for='start_date'><small class="req text-danger">* </small><?php echo _l('fe_rental_date'); ?></label>
													<input type="text" name="rental_date" class="form-control" value="">
												</div>
											</div>
											<div class="col-md-6">
												<?php echo render_select('pickup_time', $hour, array('value', 'name'), '<small class="req text-danger">* </small> '._l('fe_pickup_time')); ?>
											</div>
											<div class="col-md-6">
												<?php echo render_select('dropoff_time', $hour, array('value', 'name'), '<small class="req text-danger">* </small> '._l('fe_dropoff_time')); ?>
											</div>
										</div>

										<button type="button" class="rental_item rental_item_1 btn btn-primary pull-right mtop10 hide" data-id=""><i class="fa fa-check"></i> <?php echo _l('fe_update'); ?></button>
										<button type="button" class="rental_item rental_item_2 btn btn-success pull-right mtop10 w160px" data-id=""><i class="fa fa-angle-right"></i> <?php echo _l('fe_booking'); ?></button>
									</div>
									<!-- END Rental -->

									<!-- Buy -->
									<div class="buy_item_frame border-light p10 mtop10 hide">
										<div class="form-group">
											<label for="start_date"><small class="req text-danger">* </small><?php echo _l('fe_quantity'); ?></label>
											<input type="number" name="qty" class="form-control qty" value="1" min="1" max="0" data-w_quantity="0">
										</div>

										<button class="btn btn-primary pull-right w160px mleft10 add_to_cart add_to_cart_1 hide" type="button">
											<i class="fa fa-check"></i> <?php echo _l('added'); ?>
										</button>	
										<button class="btn btn-success pull-right w160px mleft10 add_to_cart add_to_cart_2" type="button">
											<i class="fa fa-angle-right"></i> <?php echo _l('fe_add_to_cart'); ?>
										</button>
									</div>
									<!-- END Buy -->


								</div>
							</div>
						</div>
					</div>
				</div>
			</div>              
		</div>
	</div>
</div>
<input type="hidden" name="msg_classify" value="<?php echo _l('please_choose'); ?>">


<input type="hidden" name="added_to_cart" value="<?php echo _l('fe_added_to_cart'); ?>">
<input type="hidden" name="updated_rental_information" value="<?php echo _l('fe_updated_rental_information'); ?>">
<input type="hidden" name="sorry_the_number_of_current_products_is_not_enough" value="<?php echo _l('fe_sorry_the_number_of_current_products_is_not_enough'); ?>">
<input type="hidden" name="added_text" value="<?php echo _l('fe_added') ?>">

<?php hooks()->do_action('client_pt_footer_js'); ?>




