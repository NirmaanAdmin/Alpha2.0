<div class="col-md-12 head_title pt-2">
		<div><?php echo fe_htmldecode($title_group); ?></div>
	</div>
<div class="product_list">			    		
<?php
 	  $this->load->view('client/list_product/list_product_partial');  ?>
 </div> 	  
<br>	
<br>	
<div class="clearfix"></div>
<div class="row text-right">
<?php
 for ($i=1; $i <= $total_page; $i++) {
 	$active = '';
 	if($page == $i){
 		$active = 'active';
 	}
   ?> 
 		<button class="btn btn_page <?php echo fe_htmldecode($active); ?>" data-page="<?php echo fe_htmldecode($i); ?>"><?php echo fe_htmldecode($i); ?></button>
<?php } ?>	
</div>
<input type="hidden" name="group_id" value="<?php echo fe_htmldecode($group_id); ?>">
