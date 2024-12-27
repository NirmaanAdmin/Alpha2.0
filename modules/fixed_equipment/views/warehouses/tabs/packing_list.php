<div class="row">
  <div class="col-md-12">
    <div class="row">    
     <div class="_buttons col-md-3">
      <?php if(!isset($invoice_id)){ ?>
       <?php if (has_permission('fixed_equipment_inventory', '', 'create') || is_admin()) { ?>
       <!--   <a href="<?php echo admin_url('fixed_equipment/add_edit_packing_list'); ?>"class="btn btn-info pull-left mright10 display-block">
           <?php //echo _l('add'); ?>
         </a> -->
       <?php } ?>
     <?php } ?>

   </div>

 </div>
 <br/>
</div>
<div class="col-md-12" id="small-table">
  <?php render_datatable(array(
    _l('id'),
    _l('fe_packing_list_number'),
    _l('fe_sales_order_reference'),
    _l('fe_customer'),
    _l('fe_ship_from'),
    _l('fe_ship_to'),
    _l('fe_date_created'),
    _l('fe_delivery_status'),
  ),'table_packing_list',['delivery_sm' => 'delivery_sm']); ?>
</div>
</div>
<script>var hidden_columns = [3,4,5];</script>

