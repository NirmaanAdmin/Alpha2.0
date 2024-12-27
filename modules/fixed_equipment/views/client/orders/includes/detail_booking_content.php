<?php 
$tax_total_array = [];
$sub_total = 0;
 ?>
<div class="table-responsive s_table">
     <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
      <thead>
       <tr>
        <th width="50%" align="center"><?php echo _l('invoice_table_item_heading'); ?></th>
        <th width="20%" align="center"  valign="center"><?php echo _l('fe_rental_price'); ?></th>
        <th width="20%" align="center"  valign="center"><?php echo _l('fe_ownership_time'); ?></th>
        <th width="20%" align="center"><?php echo _l('fe_line_total'); ?></th>
      </tr>
    </thead>
    <tbody>
     <?php 
      $sub_total = 0; 
      $date = date('Y-m-d');
      ?>

      <?php
      $has_item_tax = false;
      foreach ($order_detait as $key => $item_cart) { 
        if($item_cart['tax']){
          $has_item_tax = true;
        }
        ?>
        <tr class="main">
          <td>
            <a href="#">
              <?php 
              $discount_price = 0;
              $type_item = 'models';
              $item_id = fe_get_model_item($item_cart['product_id']);
              $type = fe_get_type_item($item_cart['product_id']);
              if($type != 'asset'){
                $type_item = $type;
                $item_id = $item_cart['product_id'];
              }
              $src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
              ?>
              <img class="product pic" src="<?php echo fe_htmldecode($src); ?>">  
              <strong>
                <?php   
                echo fe_htmldecode($item_cart['product_name']);
                ?>
              </strong>
            </a>
          </td>
          <td align="center" class="middle">
            <?php 
            echo app_format_money($item_cart['prices'],'').'/ '.(is_numeric($item_cart['renting_period']) ? $item_cart['renting_period'] + 0 : $item_cart['renting_period']).' '._l('fe_'.$item_cart['renting_unit'].'_s');
            ?>
          </td>
          <td align="center" class="middle">
            <?php 
            $ownership_string = '';
            if($item_cart['renting_unit'] == 'hour'){
              $ownership_string = _d($item_cart['rental_start_date']).' ('.$item_cart['pickup_time'].':00 - '.$item_cart['dropoff_time'].':00)';
            }
            else{
              $ownership_string = _d($item_cart['rental_start_date']).' '._l('fe_to').' '._d($item_cart['rental_end_date']);
            }
            echo fe_htmldecode($ownership_string); 
            ?>
          </td>
          <td align="center" class="middle">
            <strong class="line_total_<?php echo fe_htmldecode($key); ?>">
              <?php
              $line_total = $item_cart['rental_value'];
              $sub_total += $line_total;
              echo app_format_money($line_total,''); ?>
            </strong>

          </td>
        </tr>
      <?php     } ?>
</tbody>
</table>
</div>

<div class="col-md-8 col-md-offset-4">
 <table class="table text-right">
  <tbody>
   <tr id="subtotal">
    <td><span class="bold"><?php echo _l('fe_subtotal'); ?> :</span>
    </td>
    <td class="subtotal">
      <?php echo app_format_money($order->sub_total,''); ?>
    </td>
  </tr>
  <?php
  if($order->discount){
    if($order->discount>0){
      if($order->discount_type == 1){
        $voucher = '';
        if($order->voucher){
          if($order->voucher!=''){
            $voucher = '<span class="text-danger">'.$order->voucher.'</span>';
          }
        }
        ?>
        <tr>
          <td><span class="bold"><?php echo _l('fe_discount').' ('.$voucher.' -'.$order->discount.'%)'; ?> :</span>
          </td>
          <td>
            <?php

            $price_discount = $order->sub_total * $order->discount/100;
            echo '-'.app_format_money($price_discount,''); ?>
          </td>
        </tr>
      <?php  }if($order->discount_type == 2){ 
       ?>
       <tr>
        <td><span class="bold"><?php echo _l('fe_discount'); ?> :</span>
        </td>
        <td>
          <?php
          echo '-'.app_format_money($order->discount,''); ?>
        </td>
      </tr>
      <?php 
    }
  }
} ?>
<?php foreach ($tax_total_array as $tax_item_row) {
  ?>
  <tr>
    <td>
      <span class="bold"><?php echo fe_htmldecode($tax_item_row['name']); ?> :</span>
    </td>
    <td>
      <?php echo app_format_money($tax_item_row['value'],''); ?>
    </td>
  </tr>
  <?php 
}
?>
<?php 
if($order->shipping > 0){ ?>
  <tr>
    <td><span class="bold"><?php echo _l('fe_shipping_fee'); ?> :</span>
    </td>
    <td>
     <?php echo app_format_money($order->shipping,''); ?>
   </td>
 </tr>
<?php } ?>
<tr>
  <td>
    <span class="bold"><?php echo _l('fe_total'); ?> :</span>
  </td>
  <td class="total">
   <?php echo app_format_money($order->total,''); ?>
 </td>
</tr>
</tbody>
</table> 
</div>