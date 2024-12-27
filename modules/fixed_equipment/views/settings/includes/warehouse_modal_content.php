 <?php 
 $id = '';
 $code = '';
 $name = '';
 $address = '';
 $city = '';
 $state = '';
 $zip_code = '';
 $country = '';
 $order = 1;
 $display = 1;
 $note = '';
 if(isset($warehouse)){
  $id = $warehouse->id;
  $code = $warehouse->code;
  $name = $warehouse->name;
  $address = $warehouse->address;
  $city = $warehouse->city;
  $state = $warehouse->state;
  $zip_code = $warehouse->zip_code;
  $country = $warehouse->country;
  $order = $warehouse->order;
  $display = $warehouse->display;
  $note = $warehouse->note;
}
?>
<input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
<div class="row">
  <div class="col-md-12">
   <div id="color_id_t"></div>   
   <div class="form"> 
    <div class="col-md-12">
      <?php echo render_input('code', 'fe_code', $code, 'text', ['maxlength' => 100]); ?>
    </div>

    <div class="col-md-12">
      <?php echo render_input('name', 'fe_name', $name); ?>
    </div>

    <div class="col-md-12">
      <?php echo render_textarea('address', 'fe_address', $address); ?>
    </div>

    <div class="col-md-6">
      <?php echo render_input('city', 'fe_city', $city); ?>
    </div>

    <div class="col-md-6">
      <?php echo render_input('state', 'fe_state', $state); ?>
    </div>
    <div class="col-md-6">
      <?php echo render_input('zip_code', 'fe_postal_code', $zip_code); ?>
    </div>
    <div class="col-md-6">
      <?php $countries= get_all_countries();
      $customer_default_country = get_option('customer_default_country');
      $selected = (isset($warehouse) ? $country : $customer_default_country);
      echo render_select( 'country',$countries,array( 'country_id',array( 'short_name')), 'clients_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex')));
      ?>
    </div>
    <div class="col-md-12">
      <?php echo render_textarea('note', 'fe_note', $note); ?>

    </div>
    <div class="col-md-6">
      <?php 
      $min_p =[];
      $min_p['min']='0';
      $min_p['required']='true';
      $min_p['step']= 1;
      $min_p['maxlength']= 10;
      ?>
      <?php echo render_input('order','fe_sequence', $order,'number', $min_p) ?>
    </div>
    <div class="col-md-6 ptop15">
      <br>
      <div class="checkbox checkbox-inline checkbox-primary">
        <input type="checkbox" name="display" id="display" value="1" <?php echo ($display == 1 ? 'checked' : ''); ?>>
        <label for="display"><?php echo _l('fe_display'); ?></label>
      </div> 
    </div>
  </div>
</div>
</div>