<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-3">
        <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
          <li
          <?php if($tab == 'inventory_receiving'){echo " class='active'"; } ?>>
          <a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_receiving'); ?>">
            <?php echo _l('fe_inventory_receiving'); ?>
          </a>
        </li>
        <li
        <?php if($tab == 'inventory_delivery'){echo " class='active'"; } ?>>
        <a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_delivery'); ?>">
          <?php echo _l('fe_inventory_delivery'); ?>
        </a>
      </li>
      <li
      <?php if($tab == 'shipments'){echo " class='active'"; } ?>>
      <a href="<?php echo admin_url('fixed_equipment/inventory?tab=shipments'); ?>">
        <?php echo _l('fe_shipments'); ?>
      </a>
    </li>
  <li
  <?php if($tab == 'packing_list'){echo " class='active'"; } ?>>
  <a href="<?php echo admin_url('fixed_equipment/inventory?tab=packing_list'); ?>">
    <?php echo _l('fe_packing_list'); ?>
  </a>
</li>
<li
<?php if($tab == 'warehouse_management'){echo " class='active'"; } ?>>
<a href="<?php echo admin_url('fixed_equipment/inventory?tab=warehouse_management'); ?>">
  <?php echo _l('fe_warehouse_management'); ?>
</a>
</li>
<li
<?php if($tab == 'inventory_history'){echo " class='active'"; } ?>>
<a href="<?php echo admin_url('fixed_equipment/inventory?tab=inventory_history'); ?>">
  <?php echo _l('fe_inventory_history'); ?>
</a>
</li>
</ul>
</div>

<div class="col-md-9">
  <div class="panel_s">
    <div class="panel-body">
      <?php $this->load->view('warehouses/tabs/'.$tab); ?>  
    </div>
  </div>
</div>


<div class="clearfix"></div>
</div>
<div class="btn-bottom-pusher"></div>
</div>
</div>
<div id="new_version"></div>
<!-- box loading -->
<div id="box-loading">
  <img src="<?php echo site_url('modules/fixed_equipment/assets/images/loading.gif'); ?>" alt="">
</div>
<?php init_tail(); ?>
</body>
</html>

<?php 
require 'modules/fixed_equipment/assets/js/warehouses/inventory_manage_js.php';  
if($tab == 'inventory_delivery'){
  require 'modules/fixed_equipment/assets/js/warehouses/manage_delivery_js.php';  
}
if($tab == 'shipments'){
  require 'modules/fixed_equipment/assets/js/shipments/shipment_management_js.php';
}
if($tab == 'inventory_history'){
  require 'modules/fixed_equipment/assets/js/shipments/inventory_history_js.php';
}
if($tab == 'packing_list'){
  require 'modules/fixed_equipment/assets/js/packing_lists/packing_list_js.php';
}
?>
