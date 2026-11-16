<div class="col-md-12">

  <div class="col-md-6">
   
      <div class="checkbox checkbox-primary">
        <input onchange="changee_order_setting(this); return false" type="checkbox" id="changee_order_setting" name="changee_setting[changee_order_setting]" <?php if(changee_get_changee_option('changee_order_setting') == 1 ){ echo 'checked';} ?> value="changee_order_setting">
        <label for="changee_order_setting"><?php echo _l('create_changee_order_non_create_changee_request_quotation'); ?>

        <a href="#" class="pull-right display-block input_method"><i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('changee_order_tooltip'); ?>"></i></a>
        </label>
      </div>

   
      <div class="checkbox checkbox-primary">
        <input onchange="item_by_vendor(this); return false" type="checkbox" id="item_by_vendor" name="changee_setting[item_by_vendor]" <?php if(changee_get_changee_option('item_by_vendor') == 1 ){ echo 'checked';} ?> value="item_by_vendor">
        <label for="item_by_vendor"><?php echo _l('load_item_by_vendor'); ?>

        </label>
      </div>


    
      <div class="checkbox checkbox-primary">
        <input onchange="po_only_prefix_and_number(this); return false" type="checkbox" id="po_only_prefix_and_number" name="changee_setting[po_only_prefix_and_number]" <?php if(get_option('po_only_prefix_and_number') == 1 ){ echo 'checked';} ?> value="po_only_prefix_and_number">
        <label for="po_only_prefix_and_number"><?php echo _l('po_only_prefix_and_number'); ?>

        </label>
      </div>


      <div class="checkbox checkbox-primary">
        <input onchange="allow_vendors_to_register(this); return false" type="checkbox" id="allow_vendors_to_register" name="changee_setting[allow_vendors_to_register]" <?php if(get_option('allow_vendors_to_register') == 1 ){ echo 'checked';} ?> value="allow_vendors_to_register">
        <label for="allow_vendors_to_register"><?php echo _l('allow_vendors_to_register'); ?>

        </label>
      </div>

 
</div>
<div class="col-md-6">
  <div class="checkbox checkbox-primary">
    <input onchange="show_tax_column(this); return false" type="checkbox" id="show_changee_tax_column" name="changee_setting[show_changee_tax_column]" <?php if(get_option('show_changee_tax_column') == 1 ){ echo 'checked';} ?> value="show_changee_tax_column">
    <label for="show_changee_tax_column"><?php echo _l('show_changee_tax_column'); ?>

    </label>
  </div>

  <div class="checkbox checkbox-primary">
    <input onchange="send_email_welcome_for_new_contact(this); return false" type="checkbox" id="send_email_welcome_for_new_contact" name="changee_setting[send_email_welcome_for_new_contact]" <?php if(get_option('send_email_welcome_for_new_contact') == 1 ){ echo 'checked';} ?> value="send_email_welcome_for_new_contact">
    <label for="send_email_welcome_for_new_contact"><?php echo _l('send_email_welcome_for_new_contact'); ?>

    </label>
  </div>

  <div class="checkbox checkbox-primary">
    <input onchange="reset_changee_order_number_every_month(this); return false" type="checkbox" id="reset_changee_order_number_every_month" name="changee_setting[reset_changee_order_number_every_month]" <?php if(get_option('reset_changee_order_number_every_month') == 1 ){ echo 'checked';} ?> value="reset_changee_order_number_every_month">
    <label for="reset_changee_order_number_every_month"><?php echo _l('reset_changee_order_number_every_month'); ?>

    </label>
  </div>

</div>

 

  <?php echo form_open_multipart(admin_url('changee/reset_data'), array('id'=>'reset_data')); ?>
  <div class="_buttons">
      <?php if (is_admin()) { ?>
          <div class="row">
              <div class="col-md-12">
                  <button type="button" class="btn btn-danger intext-btn" onclick="reset_data(this); return false;" ><?php echo _l('reset_data'); ?></button>
                  <a href="#" class="input_method"><i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('reset_data_title_pur'); ?>"></i></a>
              </div>
          </div>
      <?php } ?>
  </div>
  <?php echo form_close(); ?>
</div>