<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$dmg_allows_customers_to_manage_documents = '';
$public_page = get_option('dmg_allows_customers_to_manage_documents');
if($public_page){
  $dmg_allows_customers_to_manage_documents = $public_page;
} 
?>
<div  class="row">
  <div class="col-md-12">
    <div class="panel-body">
      <?php echo form_open(admin_url('drawing_management/other_setting'),array('id'=>'other_setting-form')); ?>
         <div class="checkbox checkbox-inline checkbox-primary">
          <input type="checkbox" name="dmg_allows_customers_to_manage_documents" id="dmg_allows_customers_to_manage_documents" value="1" <?php echo ($dmg_allows_customers_to_manage_documents == 1 ? 'checked' : ''); ?>>
          <label for="dmg_allows_customers_to_manage_documents"><?php echo _l('dmg_allows_customers_to_manage_documents'); ?></label>
        </div>              
      <div class="row">
        <div class="col-md-12">
          <hr>
          <button class="btn btn-primary pull-right">
            <?php echo _l('dmg_save'); ?>
          </button>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>


