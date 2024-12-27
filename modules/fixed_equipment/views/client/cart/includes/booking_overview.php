 
<?php 
if(isset($client)){ ?>
 <div class="row">
  <div class="col-md-4">
   <input type="hidden" name="userid" value="<?php echo html_entity_decode($client->userid); ?>">
   <h4 class="no-mtop">
     <i class="fa fa-user"></i>
     <?php echo _l('customer_details'); ?>
   </h4>
   <hr />
   <?php  echo ( isset($client) ? $client->company : ''); ?><br>
   <?php  echo ( isset($client) ? $client->phonenumber : ''); ?><br>
   <?php echo ( isset($client) ? $client->address : ''); ?><br>
   <?php echo ( isset($client) ? $client->city : ''); ?> <?php echo ( isset($client) ? $client->state : ''); ?><br>
   <?php echo isset($client) ? get_country_short_name($client->country) : ''; ?> <?php echo ( isset($client) ? $client->zip : ''); ?><br>
 </div>
 <div class="col-md-4">
   <h4 class="no-mtop">
     <i class="fa fa-map"></i>
     <?php echo _l('billing_address'); ?>
   </h4>
   <hr />
   <address class="invoice-html-customer-shipping-info">
    <?php echo isset($client) ? $client->billing_street : ''; ?>
    <br><?php echo isset($client) ? $client->billing_city : ''; ?> <?php echo isset($client) ? $client->billing_state : ''; ?>
    <br><?php echo isset($client) ? get_country_short_name($client->billing_country) : ''; ?> <?php echo isset($client) ? $client->billing_zip : ''; ?>
  </address>
</div>
<div class="col-md-4">
  <h4 class="no-mtop">
    <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/client/'.$client->userid); ?>" class="btn btn-primary pull-right go_to_edit_link"><i class="fa fa-edit"></i></a>
    <i class="fa fa-street-view"></i>
    <?php echo _l('shipping_address'); ?>
  </h4>
  <hr />
  <address class="invoice-html-customer-shipping-info">
    <?php echo isset($client) ? $client->shipping_street : ''; ?>
    <br><?php echo isset($client) ? $client->shipping_city : ''; ?> <?php echo isset($client) ? $client->shipping_state : ''; ?>
    <br><?php echo isset($client) ? get_country_short_name($client->shipping_country) : ''; ?> <?php echo isset($client) ? $client->shipping_zip : ''; ?>
  </address>
</div>
</div>
<div class="row">
 <?php
 $price_tax = 0;
 $currency_name = '';
 if(isset($base_currency)){
  $currency_name = $base_currency->name;
}

$cart_empty = 0;
$list_id = '';
if(isset($_COOKIE['fe_cart_id_list_booking'])){
  $list_id = $_COOKIE['fe_cart_id_list_booking'];
  if(!$list_id){
   $cart_empty = 1;
 }
}
$sub_total = 0;
$date = date('Y-m-d');
$discount_price = 0;
$count_item = 0;
?>
<div class="clearfix"></div>
<br><br>    
<div class="col-md-4">
</div> 
<div class="col-md-4"></div> 
<div class="col-md-4">
  <div id="discount_area">
    <?php
    $shipping_fee = get_option('omni_portal_shipping_fee');
    echo form_hidden('discount_type', ''); 
    echo form_hidden('discount', '0'); 
    echo form_hidden('discount_total','0'); 
    echo form_hidden('other_discount','0'); 
    ?>
  </div> 
</div>      
</div>      
<br><br>     

<div class="invoice accounting-template fr1 <?php if($cart_empty == 1){ echo 'hide'; } ?>">
  <div class="row">

  </div>


  <div class="fr1">
    <div class="table-responsive s_table">
     <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
      <thead>
       <tr>
        <th width="50%" align="center"><?php echo _l('invoice_table_item_heading'); ?></th>
        <th width="20%" align="center"  valign="center"><?php echo _l('fe_rental_price').' ('.$currency_name.')'; ?></th>
        <th width="20%" align="center"  valign="center"><?php echo _l('fe_ownership_time'); ?></th>
        <th width="20%" align="center"><?php echo _l('fe_line_total').' ('.$currency_name.')'; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if($list_id){
        $array_list_id = explode(',',$list_id);
        $list_rental_time = $_COOKIE['fe_cart_rental_time_list_booking'];
        $list_rental_date = $_COOKIE['fe_cart_rental_date_list_booking'];
        $list_pickup_time = $_COOKIE['fe_cart_pickup_time_list_booking'];
        $list_dropoff_time = $_COOKIE['fe_cart_dropoff_time_list_booking'];
        $array_list_rental_time = explode(',', $list_rental_time);
        $array_list_rental_date = explode(',', $list_rental_date); 
        $array_list_pickup_time = explode(',', $list_pickup_time); 
        $array_list_dropoff_time = explode(',', $list_dropoff_time);
        $tax_total_array = [];
        $number_day_array = [];
        ?>

        <input type="hidden" name="list_id_product" value="<?php echo html_entity_decode($list_id); ?>">
        <input type="hidden" name="list_rental_time" value="<?php echo html_entity_decode($list_rental_time); ?>">
        <input type="hidden" name="list_rental_date" value="<?php echo html_entity_decode($list_rental_date); ?>">
        <input type="hidden" name="list_pickup_time" value="<?php echo html_entity_decode($list_pickup_time); ?>">
        <input type="hidden" name="list_dropoff_time" value="<?php echo html_entity_decode($list_dropoff_time); ?>">

        <?php foreach ($array_list_id as $key => $product_id) { 
          $data_product = $this->fixed_equipment_model->get_assets($product_id);
          if($data_product){
            $prices  = $data_product->selling_price;
            $type_item = 'models';
            $item_id = $data_product->model_id;
            if($data_product->type != 'asset'){
              $type_item = $data_product->type;
              $item_id = $data_product->id;
            }
            $src =  $this->fixed_equipment_model->get_image_items($item_id, $type_item);
            ?>
            <tr class="main">
              <td>
                <a href="#">
                  <?php 
                  $count_item += 1;
                  ?>
                  <img class="product pic" src="<?php echo html_entity_decode($src); ?>">  
                  <strong>
                    <?php
                    echo (($data_product->series != '' ? $data_product->series.' ' : '').''.$data_product->assets_name);
                    $array_product = [];
                    echo form_hidden('data_log', $array_product);
                    ?>
                  </strong>
                </a>
              </td>
              <td align="center" class="middle">
                <strong>
                  <?php echo app_format_money($data_product->rental_price, '').'/ '.(is_numeric($data_product->renting_period) ? $data_product->renting_period + 0 : '').' '._l('fe_'.$data_product->renting_unit.'_s'); ?>
                </strong>
              </td>
              <td align="right" class="middle prices_<?php echo html_entity_decode($product_id); ?>">
                <?php 
                if($data_product->renting_unit == 'hour'){
                  echo html_entity_decode($array_list_rental_date[$key].' ('.str_pad($array_list_pickup_time[$key], 2, '0', STR_PAD_LEFT).':00'.' - '.str_pad($array_list_dropoff_time[$key], 2, '0', STR_PAD_LEFT).':00)');
                }else{
                  echo $array_list_rental_time[$key];
                }
                $line_obj = fe_line_total_booking($data_product->rental_price, $data_product->renting_unit, $data_product->renting_period, $array_list_rental_time[$key], $array_list_dropoff_time[$key], $array_list_pickup_time[$key]);
                $line_total = $line_obj->line_total;
                $number_day = $line_obj->number_day;
                $number_day_array[] = $number_day;
                ?>
              </td>
              <td align="right" class="middle">
               <strong class="line_total_<?php echo html_entity_decode($key); ?>">
                 <?php
                 $sub_total += $line_total;
                 echo app_format_money($line_total,'');
                 ?>

               </strong>
             </td>

             <?php 
           }
         }
         echo form_hidden('tax',$price_tax);
       }else{ $cart_empty = 1; ?>

        <center><?php echo _l('cart_empty'); ?></center>
      <?php  } ?>
    </tbody>
  </table>
</div>


<?php
$shipping_fee = 0;
$shipping_form = 'fixed';
$shipping_value = 0;

if($shipping_form == 'fixed'){
  $shipping_fee = $shipping_value;
}else{
  $shipping_fee = ((float)$sub_total*(float)$shipping_fee/100);
}
echo form_hidden('shipping',$shipping_fee); 
echo form_hidden('shipping_form',$shipping_form); 
echo form_hidden('shipping_value',$shipping_value); 
echo form_hidden('list_number_day', implode(',', $number_day_array)); 
?>


<div class="row">
  <div class="col-md-8 col-md-offset-4">
   <table class="table text-right">
    <tbody>
     <tr id="subtotal" class="hide">
      <td><span class="bold"><?php echo _l('invoice_subtotal'); ?> :</span>
      </td>
      <td class="subtotal">
        <?php echo app_format_money($sub_total,''); ?>
        <?php echo form_hidden('sub_total', $sub_total);?>
        <?php $total = $sub_total; ?>
      </td>
    </tr>
    <tr class="discount_area_discount hide">
      <td>
       <span class="promotions"><?php echo _l('discount'); ?> :</span>
     </td>
     <td class="promotions_discount_price promotions">
     </td>
   </tr>
   <?php
   foreach ($tax_total_array as $tax_item_row) {
    ?>
    <tr class="discount_area_discount">
      <td>
        <span><?php echo html_entity_decode($tax_item_row['name']); ?> :</span>
      </td>
      <td>
        <?php echo app_format_money($tax_item_row['value'],''); ?>
      </td>
    </tr>
    <?php 
  }
  ?>
  <tr class="discount_area_discount hide">
    <td>
     <span class="promotions"><?php echo _l('tax'); ?> :</span>
   </td>
   <td class="promotions_tax_price promotions"><?php 
   echo app_format_money(round($price_tax, 2),''); 
   ?>
 </td>
</tr>
<?php 
$show_shipping_fee = 'hide';
if($shipping_fee > 0){
  $show_shipping_fee = '';
}
?>
<tr class="<?php echo html_entity_decode($show_shipping_fee); ?>">
  <td><?php echo _l('omni_shipping_fee'); ?></td>
  <td class="shipping_fee">
    <?php echo app_format_money(round($shipping_fee, 2),''); ?>
  </td>
</tr>
<tr>
  <td>
    <?php echo _l('total'); ?>
  </td>
  <td class="total">
    <?php 
    echo app_format_money(round($total, 2),'');
    echo form_hidden('total', $total);
    ?>
  </td>
</tr>
</tbody>
</table>
</div>
</div>

</div>
<div class="row">
  <div class="col-md-6 mtop15">
    <h4 class="no-mtop">
     <i class="fa fa-edit"></i>
     <?php echo _l('notes'); ?>
   </h4>
   <hr />
   <?php echo render_textarea('notes','','');  ?>                        
   <div class="clearfix"></div>
   <br>
 </div>
 <div class="col-md-6 mtop15">
  <h4 class="no-mtop">
   <i class="fa fa-credit-card"></i>
   <?php echo _l('payment_methods'); ?>
 </h4>
 <hr />
 <?php if(count($payment_modes) > 0){ ?>
  <select class="selectpicker"
  name="payment_methods"
  data-width="100%"
  data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
  <?php foreach($payment_modes as $key => $mode){ ?>
   <option value="<?php echo html_entity_decode($mode['id']); ?>" <?php if($key == 0){ echo 'selected'; } ?>><?php echo html_entity_decode($mode['name']); ?></option>
 <?php } ?>
</select>
<?php } ?>
<div class="clearfix"></div>
<br>
<table class="table text-right">
  <tbody>
   <tr>
    <td><span class="bold"><?php echo _l('total_items'); ?> :</span>
    </td>
    <td class="total_items">
      <?php echo html_entity_decode($count_item); ?>
    </td>
    <td><span class="bold"><?php echo _l('total_payable').' ('.$currency_name.')'; ?> :</span>
    </td>
    <td class="total_payable">
    </td>
  </tr>
</tbody>
</table>
</div> 

</div>
<div class="clearfix"></div>
<div class="row">
  <div class="col-md-12">
    <hr>
    <button id="sm_btn2" type="submit" class="btn btn-primary w160px pull-right"><i class="fa fa-check" aria-hidden="true"></i> <?php echo _l('fe_booking'); ?></button>
  </div>                
</div>               
<div class="content fr2 <?php if($cart_empty != 1){ echo 'hide'; } ?>">
  <div class="panel_s">
   <div class="panel-body">
     <div class="col-md-12 text-center">
      <h4><?php echo _l('cart_empty'); ?></h4>                    
    </div>
    <br>
    <br>
    <br>
    <br>
    <div class="col-md-12 text-center">
      <a href="javascript:history.back()" class="btn btn-primary">
       <i class="fa fa-long-arrow-left" aria-hidden="true"></i> <?php echo _l('return_to_the_previous_page'); ?></a>
     </div>
   </div>
 </div>
</div>
</div>
</div>
<?php  }else{ ?>
  <div class="row">
    <div class="col-md-12">
      <center>
        <a href="<?php echo site_url('fixed_equipment/fixed_equipment_client/index/1/0'); ?>" class="btn btn-primary"><?php echo _l('return_to_the_previous_page'); ?></a>
      </center>
    </div>
  </div>
<?php   } ?> 
<?php hooks()->do_action('client_pt_footer_js'); ?>
