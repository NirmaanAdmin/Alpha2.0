<?php  defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
	
	<div class="col-md-12">
		<div class="panel_s">
			<div class="panel-body">
				<h4><?php echo changee_pur_html_entity_decode($title) ?></h4>
				<hr>
				<a href="<?php echo site_url('changee/vendors_portal/add_update_quotation'); ?>" class="btn btn-info"><?php echo _l('add_new'); ?></a>
				<br><br>
				<table class="table dt-table" >
		            <thead>
		               <tr>
		                  <th ><?php echo _l('quotations'); ?></th>
		                  <th ><?php echo _l('estimate_dt_table_heading_amount'); ?></th> 
		                  <th ><?php echo _l('estimates_total_tax'); ?></th>
		                  <th ><?php echo _l('changee_request'); ?></th>
		                  <th ><?php echo _l('estimate_dt_table_heading_date'); ?></th>
		                  <th ><?php echo _l('estimate_dt_table_heading_expirydate'); ?></th>
		                  <th ><?php echo _l('approval_status'); ?></th>
		                  <th ><?php echo _l('options'); ?></th>
		               </tr>
		            </thead>
		            <tbody>
		            	<?php 
		            	foreach($quotations as $p){ ?>
		            		<?php $base_currency = changee_get_base_currency_pur(); ?>
		            		<?php 
		            			if($p['currency'] != 0 ){
		            				$base_currency = changee_pur_get_currency_by_id($p['currency']);
		            			}
		            		?>
		            		<tr>
		            			<td><a href="<?php echo site_url('changee/vendors_portal/view_quotation/'.$p['id']); ?>"><?php echo changee_pur_html_entity_decode(changee_format_pur_estimate_number($p['id'])); ?></a></td>
		            			<td><?php echo changee_pur_html_entity_decode(app_format_money($p['total'], $base_currency->symbol)); ?></td>
		            			<td><?php echo app_format_money($p['total_tax'], $base_currency->symbol); ?></td>
		            			<td>
		            				<?php $pr = $this->changee_model->get_changee_request($p['co_request']) ?>
		            				<?php if($pr && !is_array($pr)){ ?>
		            					<a href="<?php echo site_url('changee/vendors_portal/co_request/'.$pr->id.'/'.$pr->hash); ?>"><?php echo changee_pur_html_entity_decode($pr->pur_rq_code); ?>
		            				<?php } ?>
		            			</td>
		            			<td><span class="label label-primary"><?php echo changee_pur_html_entity_decode(_d($p['date'])); ?></span></td>
		            			<td><span class="label label-danger"><?php echo changee_pur_html_entity_decode(_d($p['expirydate'])); ?></span></td>
		            			<td><?php echo changee_get_status_approve($p['status']); ?></td>
		            			<td>
		            				<a href="<?php echo site_url('changee/vendors_portal/view_quotation/'.$p['id']); ?>" class="btn btn-success btn-icon"><i class="fa fa-eye"></i></a>
		            				<?php if($p['status'] != 2){ ?>
		            				<a href="<?php echo site_url('changee/vendors_portal/add_update_quotation/'.$p['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-edit"></i></a>

		            				<a href="<?php echo site_url('changee/vendors_portal/delete_estimate/'.$p['id']); ?>" class="btn btn-danger btn-icon"><i class="fa fa-remove"></i></a>
		            				<?php } ?>
		            			</td>
		            		</tr>
		            	<?php } ?>
		            </tbody>
		         </table>
			</div>
		</div>
	</div>
</div>