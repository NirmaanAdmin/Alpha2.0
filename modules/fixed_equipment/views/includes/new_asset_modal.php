<input type="hidden" name="alow_add_component" value="0">
<?php
$asset_id = '';
$assets_name = '';
$model_id = '';
$status = '';
$supplier_id = '';
$date_buy = _d(date("Y-m-d"));
$order_number = '';
$unit_price = '';
$asset_location = '';
$warranty_period = '';
$description = '';
$qr_code = '';
$series = '';
$requestable = '';
$for_sell = 0;
$for_rent = 0;
$selling_price = '';
$rental_price = '';
$renting_period = '';
$renting_unit = '';
if(isset($asset)){
  $asset_id = $asset->id;    
  $assets_name = $asset->assets_name;    
  $model_id = $asset->model_id;    
  $status = $asset->status;    
  $supplier_id = $asset->supplier_id;    
  $date_buy = $asset->date_buy;    
  $order_number = $asset->order_number;    
  $unit_price = app_format_money($asset->unit_price, '');    
  $asset_location = $asset->asset_location;    
  $warranty_period = $asset->warranty_period;    
  $description = $asset->description;    
  $qr_code = $asset->qr_code;    
  $series = $asset->series;    
  $requestable = $asset->requestable;   
  $for_sell = $asset->for_sell;   
  $for_rent = $asset->for_rent;   
  $selling_price = app_format_money($asset->selling_price, '');   
  $rental_price = app_format_money($asset->rental_price, '');  
  $renting_period = (is_numeric($asset->renting_period) ? $asset->renting_period + 0 : '');   
  $renting_unit = $asset->renting_unit;             
} ?>
<input type="hidden" name="id" value="<?php echo fe_htmldecode($asset_id); ?>">
<div class="row">
  <div class="col-md-12">

    <div class="horizontal-scrollable-tabs preview-tabs-top">
      <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
      <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
      <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
          <li role="presentation" class="active">
           <a href="#general_infors" aria-controls="general_infors" role="tab" data-toggle="tab" aria-controls="general_infors">
             <?php echo _l('fe_general_infor'); ?>
           </a>
         </li>
         <li role="presentation">
           <a href="#serial-lists-items" aria-controls="serial-lists-items" role="tab" data-toggle="tab" aria-controls="serial-lists-items">
             <?php echo _l('fe_serial'); ?>
           </a>
         </li>
       </ul>
     </div>
   </div> 

   <div class="tab-content w-100">
    <div role="tabpanel" class="tab-pane active" id="general_infors">
      <br>
      <div class="row">
        <div class="col-md-12">
          <?php echo render_input('assets_name','fe_asset_name',$assets_name) ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <?php echo render_select('model_id', $models, array('id', 'model_name'), 'fe_model',$model_id); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 customfields_fr">
          <?php 
          $html = '';
          if($model_id != ''){
            $data_list_custom_field = $this->fixed_equipment_model->get_custom_field_value_assets($asset_id);
            if($data_list_custom_field){
              foreach ($data_list_custom_field as $key => $customfield) {
                switch ($customfield['type']) {
                  case 'select':
                  $data['option'] = $customfield['option'];
                  $data['title'] = $customfield['title'];
                  $data['id'] = $customfield['custom_field_id'];
                  $data['required'] = $customfield['required'];
                  $data['select'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/select', $data, true);
                  break;
                  case 'multi_select':
                  $data['option'] = $customfield['option'];
                  $data['title'] = $customfield['title'];
                  $data['id'] = $customfield['custom_field_id'];
                  $data['required'] = $customfield['required'];
                  $data['select'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/multi_select', $data, true);
                  break;
                  case 'checkbox':
                  $data['option'] = $customfield['option'];
                  $data['title'] = $customfield['title'];
                  $data['id'] = $customfield['custom_field_id'];
                  $data['required'] = $customfield['required'];
                  $data['select'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/checkbox', $data, true);
                  break;
                  case 'radio_button':
                  $data['option'] = $customfield['option'];
                  $data['title'] = $customfield['title'];
                  $data['id'] = $customfield['custom_field_id'];
                  $data['required'] = $customfield['required'];
                  $data['select'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/radio_button', $data, true);
                  break;
                  case 'textarea':
                  $data['id'] = $customfield['custom_field_id'];
                  $data['title'] = $customfield['title'];
                  $data['required'] = $customfield['required'];
                  $data['value'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/textarea', $data, true);
                  break;
                  case 'numberfield':
                  $data['id'] = $customfield['custom_field_id'];
                  $data['title'] = $customfield['title'];
                  $data['required'] = $customfield['required'];
                  $data['value'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/numberfield', $data, true);
                  break;
                  case 'textfield':
                  $data['id'] = $customfield['custom_field_id'];
                  $data['title'] = $customfield['title'];
                  $data['required'] = $customfield['required'];
                  $data['value'] =  $customfield['value'];
                  $html .= $this->load->view('includes/controls/textfield', $data, true);
                  break;
                }
              }
            }
          }
          echo fe_htmldecode($html);
          ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <?php echo render_select('status', $status_labels, array('id', 'name'), 'fe_status', $status); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <?php echo render_select('supplier_id', $suppliers, array('id', 'supplier_name'), 'fe_supplier', $supplier_id); ?>
        </div>
        <div class="col-md-6">
          <?php echo render_date_input('date_buy','fe_purchase_date', $date_buy) ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <?php echo render_input('order_number', 'fe_order_number', $order_number); ?>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="gst"><?php echo _l('fe_purchase_cost'); ?></label>            
            <div class="input-group">
              <input data-type="currency" class="form-control" name="unit_price" value="<?php echo fe_htmldecode($unit_price); ?>">
              <span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <?php echo render_select('asset_location', $locations, array('id', 'location_name'), 'fe_locations', $asset_location); ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
         <div class="form-group">
          <label for="gst"><?php echo _l('fe_warranty'); ?></label>            
          <div class="input-group">
            <input type="number" class="form-control" name="warranty_period" value="<?php echo fe_htmldecode($warranty_period); ?>">
            <span class="input-group-addon"><?php echo _l('fe_months'); ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 ptop10">
        <div class="checkbox checkbox-inline checkbox-primary">
          <input type="checkbox" name="requestable" id="requestable" value="1" <?php if($requestable == 1){ echo 'checked'; } ?>>
          <label for="requestable"><?php echo _l('fe_requestable'); ?></label>
        </div>  
         <br>
        <br>
      </div>
      <div class="col-md-4 ptop10">
       <div class="checkbox checkbox-inline checkbox-primary">
        <input type="checkbox" name="for_sell" id="for_sell" value="1" <?php if($for_sell == 1){ echo 'checked'; } ?>>
        <label for="for_sell"><?php echo _l('fe_for_sell'); ?></label>
      </div> 
       <br>
       <br>
    </div>
    <div class="col-md-4 ptop10">
     <div class="checkbox checkbox-inline checkbox-primary">
      <input type="checkbox" name="for_rent" id="for_rent" value="1" <?php if($for_rent == 1){ echo 'checked'; } ?>>
      <label for="for_rent"><?php echo _l('fe_for_rent'); ?></label>
    </div> 
       <br>
       <br>
  </div>
  <div class="col-md-6 for_sell_fr<?php if($for_sell != 1){ echo ' hide'; } ?>">
   <div class="form-group">
    <label for="selling_price"><?php echo _l('fe_selling_price'); ?></label>            
    <div class="input-group">
      <input data-type="currency" type="text" class="form-control" name="selling_price" value="<?php echo fe_htmldecode($selling_price); ?>">
      <span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
    </div>
  </div>
</div>
<div class="col-md-6 for_rent_fr<?php if($for_rent != 1){ echo ' hide'; } ?>">
 <div class="form-group">
  <label for="rental_price"><?php echo _l('fe_rental_price'); ?></label>            
  <div class="input-group">
    <input data-type="currency" type="text" class="form-control" name="rental_price" value="<?php echo fe_htmldecode($rental_price); ?>">
    <span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
  </div>
</div>
</div>
  <div class="col-md-6 for_rent_fr<?php if($for_rent != 1){ echo ' hide'; } ?>">
    <?php echo render_input('renting_period', 'fe_minimum_renting_period', $renting_period, 'number', ['step' => 'any']); ?>
  </div>
  <div class="col-md-6 for_rent_fr<?php if($for_rent != 1){ echo ' hide'; } ?>">
    <?php 
    $unit_list = [
      ['id' => 'hour', 'label' => _l('fe_hour_s')],
      ['id' => 'day', 'label' => _l('fe_day_s')],
      ['id' => 'week', 'label' => _l('fe_week_s')],
      ['id' => 'month', 'label' => _l('fe_month_s')],
      ['id' => 'year', 'label' => _l('fe_year_s')]
    ];
    echo render_select('renting_unit', $unit_list, array('id', 'label'), 'fe_unit', $renting_unit); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <?php echo render_textarea('description','fe_description', $description) ?>
  </div>
</div>
</div>

<div role="tabpanel" class="tab-pane serial-lists" id="serial-lists-items">
  <br>
  <div class="row serial-items">
    <div class="col-md-10">
      <?php echo render_input('serial[]','fe_serial', $series, 'text', array('onblur' => 'check_serial(this)', 'required' => true)) ?>
    </div>
    <div class="col-md-2 mtop6 pull-right">
      <br>
      <button type="button" class="btn btn-primary add"><i class="fa fa-plus"></i></button>
    </div>
  </div>  
</div>  

</div>   

</div>
</div>
