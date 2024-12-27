<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div  class="row">
  <div class="col-md-3">
    <?php if(is_admin() || 
      has_permission('fixed_equipment_setting_model', '', 'create') ||
      has_permission('fixed_equipment_assets', '', 'create') ||
      has_permission('fixed_equipment_licenses', '', 'create') ||
      has_permission('fixed_equipment_accessories', '', 'create') ||
      has_permission('fixed_equipment_consumables', '', 'create')
    )
    { ?>
      <br>
      <button class="btn btn-primary mtop10" onclick="add(); return false;"><?php echo _l('fe_add'); ?></button>
    <?php } ?>
  </div>
  <div class="col-md-3">
    <?php echo render_select('manufacturer_filter[]', $manufacturers, array('id', 'name'), 'fe_manufacturer','',array('multiple' => true)); ?>
  </div>
  <div class="col-md-3">
    <?php echo render_select('category_filter[]', $categories, array('id', 'category_name'), 'fe_category','',array('multiple' => true)); ?>
  </div>
  <div class="col-md-3">
    <?php echo render_select('depreciation_filter[]', $depreciations, array('id', 'name'), 'fe_depreciation','',array('multiple' => true)); ?>
  </div>
  <div class="clearfix"></div>
  <br>
  <div class="clearfix"></div>

  <?php echo form_open_multipart(admin_url('fixed_equipment/model_update_batch_rate'), array('id'=>'model_update_batch_rate')); ?>      
  <div class="modal bulk_actions" id="table_models_bulk_action" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
     <div class="modal-content">
      <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
       <h4 class="modal-title"><?php echo _l('fe_update_asset_selling_prices_in_bulk'); ?></h4>
     </div>
     <div class="modal-body">
       <?php if(has_permission('fixed_equipment_setting_model','','create') || is_admin()){ ?>

         <div class="row">
          <div class=" col-md-12">
            <?php echo render_select('model', $models, ['id', ['model_name']], 'fe_model', '', [], [], '', ''); ?>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="radio radio-primary radio-inline" >
                <input onchange="print_barcode_option(this); return false" type="radio" id="y_opt_1_" name="select_item" value="0" checked >
                <label for="y_opt_1_"><?php echo _l('select_all'); ?></label>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="radio radio-primary radio-inline" >
                <input onchange="print_barcode_option(this); return false" type="radio" id="y_opt_2_" name="select_item" value="1" >
                <label for="y_opt_2_"><?php echo _l('select_item'); ?></label>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="display-select-item hide ">
              <?php echo render_select('asset_id[]', [], array('id','name'),'fe_assets', '', ['multiple' => true, 'data-live-search' => true, 'data-actions-box' => true]); ?>
            </div>
          </div>
        </div>   
        <div class="row">
          <div class="col-md-4 ptop10">
            <div class="checkbox checkbox-inline checkbox-primary">
              <input type="checkbox" name="requestable" id="requestable" value="1">
              <label for="requestable"><?php echo _l('fe_requestable'); ?></label>
            </div>  
            <br>
            <br>
          </div>
          <div class="col-md-4 ptop10">
           <div class="checkbox checkbox-inline checkbox-primary">
            <input type="checkbox" name="for_sell" id="for_sell" value="1">
            <label for="for_sell"><?php echo _l('fe_for_sell'); ?></label>
          </div> 
          <br>
          <br>
        </div>
        <div class="col-md-4 ptop10">
         <div class="checkbox checkbox-inline checkbox-primary">
          <input type="checkbox" name="for_rent" id="for_rent" value="1">
          <label for="for_rent"><?php echo _l('fe_for_rent'); ?></label>
        </div> 
        <br>
        <br>
      </div>
      <div class="col-md-6 for_sell_fr hide">
       <div class="form-group">
        <?php
         $selling_price = 0;
         $rental_price = 0;
         $renting_unit = 0;
         $renting_period = 0;
         ?>
        <label for="selling_price"><?php echo _l('fe_selling_price'); ?></label>            
        <div class="input-group">
          <input data-type="currency" type="text" class="form-control" name="selling_price" value="<?php echo fe_htmldecode($selling_price); ?>">
          <span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-6 for_rent_fr hide">
     <div class="form-group">
      <label for="rental_price"><?php echo _l('fe_rental_price'); ?></label>            
      <div class="input-group">
        <input data-type="currency" type="text" class="form-control" name="rental_price" value="<?php echo fe_htmldecode($rental_price); ?>">
        <span class="input-group-addon"><?php echo fe_htmldecode($currency_name); ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-6 for_rent_fr hide">
    <?php echo render_input('renting_period', 'fe_minimum_renting_period', $renting_period, 'number', ['step' => 'any']); ?>
  </div>
  <div class="col-md-6 for_rent_fr hide">
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

      <?php } ?>
    </div>
    <div class="modal-footer">
     <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>

     <?php if(has_permission('fixed_equipment_setting_model','','create') || is_admin()){ ?>

       <button type="submit" class="btn btn-info" ><?php echo _l('confirm'); ?></button>
     <?php } ?>
   </div>
 </div>
</div>
</div>
<?php echo form_close(); ?>

<?php if(has_permission('fixed_equipment_setting_model', '', 'edit') || has_permission('fixed_equipment_setting_model', '', 'delete') ){ ?>
 <a href="#"  onclick="bulk_actions(); return false;" data-toggle="modal" data-table=".table-models" data-target="#leads_bulk_actions" class=" hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
<?php } ?>

<div  class="col-md-12">
  <table class="table table-models scroll-responsive">
   <thead>
     <tr>
      <th><?php echo _l('id'); ?></th>
      <th><?php echo _l('fe_name'); ?></th>
      <th><?php echo _l('fe_image'); ?></th>
      <th><?php echo _l('fe_manufacturer'); ?></th>
      <th><?php echo _l('fe_model_no'); ?></th>
      <th><?php echo _l('fe_assets'); ?></th>
      <th><?php echo _l('fe_depreciation'); ?></th>
      <th><?php echo _l('fe_category'); ?></th>
      <th>EOL</th>
      <th><?php echo _l('fe_notes'); ?></th>
    </tr>
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
 </tfoot>
</table>
</div>
</div>

<div class="modal fade" id="add" tabindex="-1" role="dialog">
 <div class="modal-dialog">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">
     <span class="add-title"><?php echo _l('fe_add_model'); ?></span>
     <span class="edit-title hide"><?php echo _l('fe_edit_model'); ?></span>
   </h4>
 </div>
 <?php echo form_open_multipart(admin_url('fixed_equipment/add_models'),array('id'=>'form_models')); ?>              
 <div class="modal-body content">
  <?php $this->load->view('settings/includes/models_modal_content'); ?>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>                   
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="ic_file_data"></div>

