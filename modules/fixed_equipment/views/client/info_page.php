<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('head_element_client'); ?>
<div id="wrapper">
 <div class="content">
   <div class="panel_s">
    <div class="panel-body">
    	<div class="col-md-12 infor_page">
    		<strong><?php echo fe_htmldecode($content); ?></strong>	   		    		
    	</div>
    	<br>
    	<br>
    	<br>
    	<br>
    	<div class="col-md-12 text-center">
    		<a href="<?php if($previous_link!=''){ echo fe_htmldecode($previous_link); }else{ echo 'javascript:history.back()'; } ?> " class="btn btn-primary">
    			<i class="fa fa-long-arrow-left" aria-hidden="true"></i>
                <?php 
                    if(isset($link_text)){
                        echo fe_htmldecode($link_text);
                    }
                    else{
                        echo _l('return_to_the_previous_page');
                    }
                 ?>
             </a>
             &nbsp;
             <?php 
                if(isset($custom_link)){ ?>
                     <a href="<?php echo fe_htmldecode($custom_link) ?>" class="btn btn-danger">
                        <?php 
                            if(isset($custom_link_text)){
                                echo fe_htmldecode($custom_link_text);
                            }                    
                         ?>
                     </a>
              <?php   }
              ?>            
    	</div>
	  </div>
  </div>
 </div>
</div>


