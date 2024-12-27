<?php if(isset($packing_lists) && count($packing_lists) > 0){ ?>
	<div role="tabpanel" class="tab-pane" id="packing_list">
		<div class="panel_s no-shadow">

			<div class="row">
				<div class="col-md-12">
					<div class="table-responsive">
						<table class="table items items-preview estimate-items-preview" data-type="estimate">
							<thead>
								<tr>
									<th  colspan="1" class="text-nowrap"><?php echo _l('fe_code') ?></th>
									<th  colspan="1" class="text-nowrap"><?php echo _l('customer_name') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('fe_dimension') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('volume_m3_label') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('total_amount') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('fe_discount_total') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('fe_total_after_discount') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('datecreated') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('status_label') ?></th>
									<th align="right" colspan="1" class="text-nowrap"><?php echo _l('delivery_status') ?></th>
								</tr>
							</thead>
							<tbody class="ui-sortable">
								<?php 
								$subtotal = 0 ;
								foreach ($packing_lists as $key => $packing_list) {
									?>

									<tr>
										<td  ><a href="<?php echo admin_url('fixed_equipment/view_packing_list/' . $packing_list['id'] ) ?>" ><?php echo fe_htmldecode($packing_list['packing_list_number']) ?></a></td class="text-nowrap">
										<td  ><?php echo get_company_name($packing_list['clientid']) ?></td>
										<td class="text-right text-nowrap" ><?php echo fe_htmldecode($packing_list['width'].' x '.$packing_list['height'].' x '.$packing_list['lenght']) ?></td>
										<td class="text-right text-nowrap" ><?php echo app_format_money($packing_list['volume'], '') ?></td>
										<td class="text-right text-nowrap" ><?php echo app_format_money($packing_list['total_amount'], '') ?></td>
										<td class="text-right text-nowrap" ><?php echo app_format_money($packing_list['discount_total']+$packing_list['additional_discount'], '') ?></td>
										<td class="text-right text-nowrap" ><?php echo app_format_money($packing_list['total_after_discount'], '') ?></td>
										<td class="text-right text-nowrap" ><?php echo _dt($packing_list['datecreated']) ?></td>
										<?php 
										$approve_data = '';
										if($packing_list['approval'] == 1){
											$approve_data = '<span class="label label-tag tag-id-1 label-tab1"><span class="tag">'._l('approved').'</span><span class="hide">, </span></span>&nbsp';
										}elseif($packing_list['approval'] == 0){
											$approve_data = '<span class="label label-tag tag-id-1 label-tab2"><span class="tag">'._l('not_yet_approve').'</span><span class="hide">, </span></span>&nbsp';
										}elseif($packing_list['approval'] == -1){
											$approve_data = '<span class="label label-tag tag-id-1 label-tab3"><span class="tag">'._l('reject').'</span><span class="hide">, </span></span>&nbsp';
										}
										?>
										<td class="text-right"><?php echo fe_htmldecode($approve_data); ?></td>
										<td class="text-right"><?php echo fe_render_delivery_status_html($packing_list['id'], 'packing_list', $packing_list['delivery_status'], false) ?></td>
									</tr>
								<?php  } ?>
							</tbody>
						</table>

					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>