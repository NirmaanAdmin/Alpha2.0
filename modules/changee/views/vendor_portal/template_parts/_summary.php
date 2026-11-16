<?php defined('BASEPATH') or exit('No direct script access allowed');
$where = array('vendor'=>changee_get_vendor_user_id()); ?>
<div class="col-md-2 list-status projects-status">
	<a href="#" class="">
		<h3 class="bold"><?php echo total_rows(db_prefix().'pur_contracts',$where); ?></h3>
		<span class="text-primary">
			<?php echo _l('contracts'); ?>
	</a>
</div>
<div class="col-md-2 list-status projects-status">
	<a href="#" class="">
		<h3 class="bold"><?php echo total_rows(db_prefix().'co_orders',$where); ?></h3>
		<span class="text-danger">
			<?php echo _l('changee_order'); ?>
	</a>
</div>
<div class="col-md-2 list-status projects-status">
	<a href="#" class="">
		<h3 class="bold"><?php echo total_rows(db_prefix().'co_estimates',$where); ?></h3>
		<span class="text-warning">
			<?php echo _l('quotations'); ?>
	</a>
</div>
<div class="col-md-2 list-status projects-status">
	<a href="#" class="">
		<h3 class="bold"><?php echo count($payment); ?></h3>
		<span class="text-success">
			<?php echo _l('payments'); ?>
	</a>
</div>
