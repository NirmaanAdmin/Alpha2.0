<?php hooks()->do_action('head_element_client'); ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">     

         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <?php if(isset($client)){ ?>
                     <?php echo form_hidden('isedit'); ?>
                     <?php echo form_hidden('userid', $client->userid); ?>
                     <div class="clearfix"></div>
                  <?php } ?>
                  <div>
                     <div class="tab-content">
                     	<?php echo form_open($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
                        <input type="hidden" name="type" value="<?php echo fe_htmldecode($type); ?>">
                        <?php 
                        if($type == 'order'){
                           $this->load->view('client/cart/includes/order_overview.php'); 
                        }
                        else{
                           $this->load->view('client/cart/includes/booking_overview.php');                            
                        }
                        ?>
                        <?php echo form_close(); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/clients/client_js'); ?>

