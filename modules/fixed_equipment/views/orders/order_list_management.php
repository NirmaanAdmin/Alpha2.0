<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php 
$group_product_id = '';
$product_id = '';
?>
<div id="wrapper">
 <div class="content">
   <div class="panel_s">
    <div class="panel-body">
      <div class="clearfix"></div><br>
      <div class="row">
        <div class="col-md-12">
         <h4><i class="fa fa-list-ul">&nbsp;&nbsp;</i><?php echo fe_htmldecode($title); ?></h4>
         <hr>
       </div>
     </div>
     <?php if(has_permission('fixed_equipment_order_list', '', 'create') || is_admin()){ ?>
      <div class="row"> 
        <div class="col-md-3"> 
          <a class="btn btn-primary pull-left mright10" href="<?php echo admin_url('fixed_equipment/order_manual'); ?>">
            <?php echo _l('fe_create_order'); ?>                
          </a>
        </div>
      </div>
    <?php } ?>

    <br>
    <div class="clearfix"></div>
    <div class="row">
      <div class="col-md-3">
        <?php echo render_date_input('start_date','fe_start_date',''); ?>
      </div>
      <div class="col-md-3">
        <?php echo render_date_input('end_date','fe_end_date',''); ?>
      </div>

      <?php if(is_admin()){ ?>
        <div class="col-md-3">
          <div class="form-group">
            <label class="control-label" for="seller"><?php echo _l('fe_seller'); ?></label>
            <select class="selectpicker display-block" data-width="100%" name="seller" data-none-selected-text="<?php echo _l('fe_no_seller'); ?>" data-live-search="true">
              <option value=""></option>
              <?php foreach ($staff as $key => $value) { ?>
                <option value="<?php echo fe_htmldecode($value['staffid']); ?>"><?php echo fe_htmldecode($value['lastname'].' '.$value['firstname']); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
      <?php } ?>

      <div class="col-md-3">
        <div class="form-group">
          <label class="control-label" for="channel"><?php echo _l('fe_channel'); ?></label>
          <select class="selectpicker display-block" data-width="100%" name="channel" data-none-selected-text="<?php echo _l('fe_no_channel'); ?>" data-live-search="true">
            <option value=""></option>
            <option value="2">Portal</option>
            <option value="4"><?php echo _l('fe_manual'); ?></option>
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label class="control-label" for="invoice"><?php echo _l('fe_invoice'); ?></label>
          <select class="selectpicker display-block" data-width="100%" name="invoice" data-none-selected-text="<?php echo _l('fe_no_invoice'); ?>" data-live-search="true">
            <option value=""></option>
            <?php foreach ($invoices as $key => $value) { ?>
              <?php  
              $_invoice_number = str_pad($value['number'], get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
              ?>
              <option value="<?php echo fe_htmldecode($value['id']); ?>"><?php echo fe_htmldecode($prefix); ?>
              <?php echo fe_htmldecode($_invoice_number); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          <label class="control-label" for="customer"><?php echo _l('fe_customer'); ?></label>
          <select class="selectpicker display-block" data-width="100%" name="customer" data-none-selected-text="<?php echo _l('fe_no_customer'); ?>" data-live-search="true">
            <option value=""></option>
            <?php foreach ($customers as $key => $value) { ?>
              <option value="<?php echo fe_htmldecode($value['userid']); ?>"><?php echo fe_htmldecode($value['company']); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          <label class="control-label" for="status"><?php echo _l('fe_status'); ?></label>
          <select class="selectpicker display-block" data-width="100%" name="status" data-none-selected-text="<?php echo _l('fe_no_status'); ?>" data-live-search="true">
            <option value=""></option>
            <?php foreach(fe_status_list() as $item){ ?>
              <option value="<?php echo fe_htmldecode($item['id']);?>"><?php echo fe_htmldecode($item['label']);?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="col-md-3">
         <div class="form-group">
          <label class="control-label" for="order_type"><?php echo _l('fe_order_type'); ?></label>
          <select class="selectpicker display-block" data-width="100%" name="order_type" data-none-selected-text="<?php echo _l('fe_no_order_type'); ?>" data-live-search="true">
            <option value=""></option>
            <option value="order"><?php echo _l('fe_sale_order');?></option>
            <option value="booking"><?php echo _l('fe_booking');?></option>
            <option value="return"><?php echo _l('fe_return_order');?></option>
          </select>
        </div>
      </div>
    </div>
    <div class="clearfix"></div>
    <table class="table table-order_list scroll-responsive">
      <thead>
        <th>ID#</th>
        <th><?php echo _l('fe_order_number'); ?></th>
        <th><?php echo _l('fe_order_date'); ?></th>
        <th><?php echo _l('fe_customer'); ?></th>
        <th><?php echo _l('fe_group_customer'); ?></th>
        <th><?php echo _l('fe_order_type'); ?></th>
        <th><?php echo _l('fe_payment_method'); ?></th>
        <th><?php echo _l('fe_channel'); ?></th>
        <th><?php echo _l('fe_status'); ?></th>
        <th><?php echo _l('fe_invoice'); ?></th>
        <th><?php echo _l('fe_options'); ?></th>
      </thead>
      <tbody></tbody>
      <tfoot>
       <td></td>
       <td></td>
       <td></td>
       <td></td>
       <td></td>
       <td></td>
       <td></td>
       <td></td>
       <td></td>      
       <td></td>      
       <td></td>      
     </tfoot>
   </table>

 </div>
</div>
</div>
</div>
<?php init_tail(); ?>
</body>
</html>
