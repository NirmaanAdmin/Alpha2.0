<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="panel_s">
					<?php echo form_open_multipart(admin_url('fixed_equipment/manage_goods_receipt'), array('id'=>'add_goods_receipt')); ?>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<h4 class="no-margin font-bold"><i class="fa fa-clone menu-icon menu-icon" aria-hidden="true"></i> <?php echo _l($title); ?></h4>
								<hr>
							</div>
						</div>

						<?php 
						$id = '';
						if(isset($goods_receipt)){
							$id = $goods_receipt->id;
							echo form_hidden('isedit');
						}
						?>
						<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
						<input type="hidden" name="save_and_send_request" value="false">
						<!-- start-->
						<div class="row">
							<div class="col-md-6">
								<?php $goods_receipt_code = isset($goods_receipt) ? $goods_receipt->goods_receipt_code : (isset($goods_code) ? $goods_code : '');?>
								<?php echo render_input('goods_receipt_code', 'stock_received_docket_number',$goods_receipt_code,'',array('disabled' => 'true')) ?>
							</div>
							<div class="col-md-3">
								<?php $date_c =  isset($goods_receipt) ? $goods_receipt->date_c : $current_day?>
								<?php echo render_date_input('date_c','accounting_date', _d($date_c)) ?>
							</div>
							<div class="col-md-3">
								<?php $date_add =  isset($goods_receipt) ? $goods_receipt->date_add : $current_day?>
								<?php echo render_date_input('date_add','day_vouchers', _d($date_add)) ?>
							</div>



							<div class="col-md-6 <?php if($pr_orders_status == false){ echo 'hide';} ;?>" >
								<div class="form-group">
									<label for="supplier_code"><?php echo _l('supplier_name'); ?></label>
									<select  name="supplier_code" id="supplier_code" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
										<option value=""></option>

										<?php if(isset($vendors)){ ?>
											<?php foreach($vendors as $s) { ?>
												<option value="<?php echo fe_htmldecode($s['userid']); ?>" <?php if(isset($goods_receipt) && $goods_receipt->supplier_code == $s['userid']){ echo 'selected'; } ?>><?php echo fe_htmldecode($s['company']); ?></option>
											<?php } ?>
										<?php } ?>

									</select>
								</div>
							</div>

							<div class="col-md-6 <?php if($pr_orders_status == true){ echo 'hide';} ;?>" >

								<?php $supplier_name =  isset($goods_receipt) ? $goods_receipt->supplier_name : ''?>
								<?php 
								echo render_input('supplier_name','supplier_name', $supplier_name) ?>
							</div>

							<div class=" col-md-3">
								<div class="form-group">
									<label for="buyer_id" class="control-label"><?php echo _l('Buyer'); ?></label>
									<select name="buyer_id" class="selectpicker" id="buyer_id" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"> 
										<option value=""></option> 
										<?php foreach($staff as $s){ ?>
											<option value="<?php echo fe_htmldecode($s['staffid']); ?>" <?php if(isset($goods_receipt) && ($goods_receipt->buyer_id == $s['staffid'])){ echo 'selected' ;} ?>> <?php echo fe_htmldecode($s['firstname'].''.$s['lastname']); ?></option>                  
										<?php }?>
									</select>
								</div>
							</div>

							<div class=" col-md-3">
								<?php $deliver_name = (isset($goods_receipt) ? $goods_receipt->deliver_name : '');
								echo render_input('deliver_name','deliver_name',$deliver_name) ?>
							</div>

							<div class="col-md-3 ">
								<?php $warehouse_id_value = (isset($goods_receipt) ? $goods_receipt->warehouse_id : '');?>
								<a href="#" class="pull-right display-block input_method"><i class="fa fa-question-circle skucode-tooltip"  data-toggle="tooltip" title="" data-original-title="<?php echo _l('goods_receipt_warehouse_tooltip'); ?>"></i></a>
								<?php echo render_select('warehouse_id_m',$warehouses,array('id','name'),'warehouse_name', $warehouse_id_value); ?>
							</div>
		
							<div class="col-md-3 form-group" >
								<?php $invoice_no = (isset($goods_receipt) ? $goods_receipt->invoice_no : '');
								echo render_input('invoice_no','invoice_no',$invoice_no) ?>
							</div>
						</div>
					</div>


					<div class="panel-body mtop10 invoice-item">
						<div class="row">
							<div class="col-md-4">
								<?php $this->load->view('warehouses/includes/item_select'); ?>
							</div>
							<!-- <div class="col-md-8 text-right">
								<label class="bold mtop10 text-right" data-toggle="tooltip" title="" data-original-title="<?php // echo _l('support_barcode_scanner_tooltip'); ?>"><?php // echo _l('support_barcode_scanner'); ?>
								<i class="fa fa-question-circle i_tooltip"></i></label>
							</div> -->
						</div>

						<div>
							<table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
								<thead>
									<tr>
										<th></th>
										<th width="20%" align="left">
											<i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> 
											<?php echo _l('invoice_table_item_heading'); ?>
										</th>
										<th width="15%" align="left"><?php echo _l('warehouse_name'); ?></th>
										<th width="15%" align="right" class="qty"><?php echo _l('quantity'); ?></th>
										<th width="15%" align="right"><?php echo _l('unit_price'); ?></th>
										<th width="10%" align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
										<th width="10%" align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
										<th width="15%" align="right"><?php echo _l('fe_asset_tag'); ?></th>

										<th align="center"><i class="fa fa-cog"></i></th>
									</tr>
								</thead>
								<tbody>
									<?php echo fe_htmldecode($goods_receipt_row_template); ?>
								</tbody>
							</table>
						</div>
						<div class="col-md-8 col-md-offset-4">
							<table class="table text-right">
								<tbody>
									<tr id="subtotal">
										<td><span class="bold"><?php echo _l('total_goods_money'); ?> :</span>
										</td>
										<td class="wh-subtotal">
										</td>
									</tr>
									<tr id="totalmoney">
										<td><span class="bold"><?php echo _l('total_money'); ?> :</span>
										</td>
										<td class="wh-total">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="removed-items"></div>
					</div>

					<div class="row">
						<div class="col-md-12 mtop15">
							<div class="panel-body ">
								<?php $description = (isset($goods_receipt) ? $goods_receipt->description : ''); ?>
								<?php echo render_textarea('description','note',$description,array(),array(),'mtop15'); ?>

								<div class="btn-bottom-toolbar text-right">
									<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_receiving'); ?>"class="btn btn-default text-right mright5"><?php echo _l('close'); ?></a>
									<?php if (is_admin() || has_permission('fixed_equipment_inventory', '', 'edit') || has_permission('fixed_equipment_inventory', '', 'create')) { ?>
										<?php if(isset($goods_receipt) && $goods_receipt->approval == 0){ ?>
											<a href="javascript:void(0)"class="btn btn-info pull-right mright5 add_goods_receipt" ><?php echo _l('submit'); ?></a>
										<?php }elseif(!isset($goods_receipt)){ ?>
											<a href="javascript:void(0)"class="btn btn-info pull-right mright5 add_goods_receipt" ><?php echo _l('submit'); ?></a>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
							<div class="btn-bottom-pusher"></div>
						</div>
					</div>

 			</div>

 			<?php echo form_close(); ?>

 		</div>
 	</div>
 </div>
</div>
</div>
</div>
<div id="modal_wrapper"></div>
<?php init_tail(); ?>
<?php require 'modules/fixed_equipment/assets/js/warehouses/purchase_js.php'; ?>
</body>
</html>

