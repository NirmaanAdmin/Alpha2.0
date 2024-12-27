<div class="row">
  <div class="col-md-12">
  <div class="row">    
   <div class="_buttons col-md-3">
    <?php if(!isset($invoice_id)){ ?>
     <?php if (has_permission('fixed_equipment_inventory', '', 'create') || is_admin()) { ?>
       <!-- <a href="<?php //echo admin_url('fixed_equipment/add_edit_shipment'); ?>"class="btn btn-info pull-left mright10 display-block">
         <?php // echo _l('add'); ?>
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
    _l('fe_shipment_number'),
    _l('fe_order'),
    _l('fe_shipment_status'),
    _l('fe_date_created')
  ),'table_shipment',['delivery_sm' => 'delivery_sm']); ?>
</div>
</div>
<script>var hidden_columns = [3,4,5];</script>

