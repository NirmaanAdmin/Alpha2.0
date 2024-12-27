<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$googlemap_api_key = '';
$api_key = get_option('fe_googlemap_api_key');
if($api_key){
  $googlemap_api_key = $api_key;
} 

$show_public_page = '';
$public_page = get_option('fe_show_public_page');
$fe_show_customer_asset = get_option('fe_show_customer_asset');
if($public_page){
  $show_public_page = $public_page;
} 
?>
<div  class="row">
  <div class="col-md-12">
    <div class="panel-body">
      <?php echo form_open(admin_url('fixed_equipment/other_setting'),array('id'=>'other_setting-form')); ?>
      <?php echo render_input('fe_googlemap_api_key', 'fe_googlemap_api_key', $googlemap_api_key) ?>

         <div class="checkbox checkbox-inline checkbox-primary">
          <input type="checkbox" name="fe_show_public_page" id="show_public_page" value="1" <?php echo ($show_public_page == 1 ? 'checked' : ''); ?>>
          <label for="show_public_page"><?php echo _l('fe_show_public_page'); ?></label>
        </div>  
            <div class="checkbox checkbox-inline checkbox-primary">
          <input type="checkbox" name="fe_show_customer_asset" id="fe_show_customer_asset" value="1" <?php echo ($fe_show_customer_asset == 1 ? 'checked' : ''); ?>>
          <label for="fe_show_customer_asset"><?php echo _l('fe_show_customer_asset'); ?></label>
        </div>  
                    


      <div class="row">
        <div class="col-md-12">
          <hr>
          <button class="btn btn-primary pull-right">
            <?php echo _l('fe_save'); ?>
          </button>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>


