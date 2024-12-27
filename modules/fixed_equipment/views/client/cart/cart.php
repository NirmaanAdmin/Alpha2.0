<?php hooks()->do_action('head_element_client'); ?>
<div id="wrapper">
	<div class="content">
		<?php 
		$this->load->view('client/cart/includes/list_of_booking.php');  
		?>
		<hr>	
		<br>	
		<?php 
		$this->load->view('client/cart/includes/list_of_order.php');  
		?>
	</div>
</div>
<?php hooks()->do_action('client_pt_footer_js'); ?>