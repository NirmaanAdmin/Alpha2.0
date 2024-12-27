<?php 
$tax_total_array = [];
$sub_total = 0;
 ?>
<div class="table-responsive s_table">
     <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
      <thead>
       <tr>
        <th width="50%" align="center"><?php echo _l('invoice_table_item_heading'); ?></th>
        <th width="10%" align="center" class="qty"><?php echo _l('quantity'); ?></th>
        <th width="20%" align="center"  valign="center"><?php echo _l('price'); ?></th>
        <th width="15%" align="center"  valign="center"><?php echo _l('tax'); ?></th>
        <th width="20%" align="center"><?php echo _l('fe_line_total'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $sub_total = 0; 
      ?>

      <?php foreach ($order_detait as $key => $item_cart) { 
        $product_id = $item_cart['product_id'];
        $type_item = 'models';
        $item_id = fe_get_model_item($product_id);
        if(fe_get_type_item($product_id) != 'asset' && $type_item = fe_get_type_item($product_id)){
          $item_id = $product_id;
        }
        $src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
        ?>
        <tr class="main">
          <td>
            <a href="#">
              <img class="product pic" src="<?php echo fe_htmldecode($src); ?>">  
              <strong>
                <?php   
                echo fe_htmldecode($item_cart['product_name']);
                ?>
              </strong>
            </a>
          </td>
          <td align="center" class="middle">
            <?php echo fe_htmldecode($item_cart['quantity']); ?>
          </td>
          <td align="center" class="middle">
           <strong><?php 
           echo app_format_money($item_cart['prices'],'');
         ?></strong>
       </td>
       <td align="center" class="middle">
        <?php 
        if($item_cart['tax']){
          $list_tax = json_decode($item_cart['tax']);
          $tax_name = '';
          foreach ($list_tax as $tax_item) {
            $tax_name .= $tax_item->name.' ('.$tax_item->rate.'%)<br>'; 
            $array_tax_index = $tax_item->rate.'_'.$tax_item->id;
            if(isset($tax_total_array[$array_tax_index])){
              $old_value_tax = $tax_total_array[$array_tax_index]['value'];
              $tax_total_array[$array_tax_index] = ['value' => ($old_value_tax + $tax_item->value), 'name' => $tax_item->name.' ('.$tax_item->rate.'%)'];
            }
            else{
              $tax_total_array[$array_tax_index] = ['value' => $tax_item->value, 'name' => $tax_item->name.' ('.$tax_item->rate.'%)'];
            }
          }
          echo fe_htmldecode($tax_name);                           
        }
        ?>
      </td>
      <td align="center" class="middle">
       <strong class="line_total_<?php echo fe_htmldecode($key); ?>">
         <?php
         $line_total = (int)$item_cart['quantity']*$item_cart['prices'];
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
    <td><span class="bold"><?php echo fe_htmldecode($tax_item_row['name']); ?> :</span>
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
  <td><span class="bold"><?php echo _l('fe_total'); ?> :</span>
  </td>
  <td class="total">
   <?php echo app_format_money($order->total,''); ?>
 </td>
</tr>
</tbody>
</table> 
</div>