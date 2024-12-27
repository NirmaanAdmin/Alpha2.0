<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>



<?php echo form_open_multipart(admin_url('fixed_equipment/prefix_setting'),array('class'=>'prefix_setting','autocomplete'=>'off')); ?>

<div class="row">
	<div class="col-md-12">
    <h4 class="heading"><?php echo _l('fe_inventory_receiving') ?></h5>
      <hr class="hr-color">
    </div>
  </div>

  <div class="col-md-6">
    <label><?php echo _l('fe_inventory_receiving_prefix'); ?></label>
    <div  class="form-group" app-field-wrapper="fe_inventory_receiving_prefix">
      <input type="text" id="fe_inventory_receiving_prefix" name="fe_inventory_receiving_prefix" class="form-control" value="<?php echo get_option('fe_inventory_receiving_prefix'); ?>"></div>
    </div>

    <div class="col-md-6">
      <label><?php echo _l('fe_next_inventory_receiving_mumber'); ?></label>
      <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('fe_next_delivery_received_mumber_tooltip'); ?>"></i>
      <div  class="form-group" app-field-wrapper="fe_next_inventory_receiving_mumber">
        <input type="number" min="0" id="fe_next_inventory_receiving_mumber" name="fe_next_inventory_receiving_mumber" class="form-control" value="<?php echo get_option('fe_next_inventory_receiving_mumber'); ?>">
      </div>
    </div>


    <div class="row">
      <div class="col-md-12">
        <h4 class="heading"><?php echo _l('fe_inventory_delivery') ?></h5>
          <hr class="hr-color">
        </div>
      </div>

      <div class="col-md-6">
        <label><?php echo _l('fe_inventory_delivery_prefix'); ?></label>
        <div class="form-group" app-field-wrapper="fe_inventory_delivery_prefix">
          <input type="text" id="fe_inventory_delivery_prefix" name="fe_inventory_delivery_prefix" class="form-control" value="<?php echo get_option('fe_inventory_delivery_prefix'); ?>"></div>
        </div>

        <div class="col-md-6">
          <label> <?php echo _l('fe_next_inventory_delivery_mumber'); ?></label>
          <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('fe_next_delivery_received_mumber_tooltip'); ?>"></i>

          <div  class="form-group" app-field-wrapper="fe_next_inventory_delivery_mumber">
            <input type="number" min="0" id="fe_next_inventory_delivery_mumber" name="fe_next_inventory_delivery_mumber" class="form-control" value="<?php echo get_option('fe_next_inventory_delivery_mumber'); ?>">
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <h4 class="heading"><?php echo _l('fe_packing_list') ?></h4>
            <hr class="hr-color">
          </div>
        </div>

        <div class="col-md-6">
          <label><?php echo _l('fe_packing_list_prefix'); ?></label>
          <div class="form-group" app-field-wrapper="fe_packing_list_prefix">
            <input type="text" id="fe_packing_list_prefix" name="fe_packing_list_prefix" class="form-control" value="<?php echo get_option('fe_packing_list_prefix'); ?>"></div>
          </div>

          <div class="col-md-6">
            <label> <?php echo _l('fe_next_packing_list_number'); ?></label>
            <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('fe_next_delivery_received_mumber_tooltip'); ?>"></i>

            <div  class="form-group" app-field-wrapper="fe_next_packing_list_number">
              <input type="number" min="0" id="fe_next_packing_list_number" name="fe_next_packing_list_number" class="form-control" value="<?php echo get_option('fe_next_packing_list_number'); ?>">
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <h4 class="no-margin font-bold h5-color"><?php echo _l('fe_serial_numbers') ?></h4>
              <hr class="hr-color">
            </div>
          </div>
          <div class="col-md-12">

            <div class="form-group">
              <label> <?php echo _l('fe_next_serial_number'); ?></label>
              <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('next_delivery_received_mumber_tooltip'); ?>"></i>

              <div  class="form-group" app-field-wrapper="fe_next_serial_number">
                <input type="number" min="0" id="fe_next_serial_number" name="fe_next_serial_number" class="form-control" value="<?php echo get_option('fe_next_serial_number'); ?>"></div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="fe_serial_number_format" class="control-label clearfix"><?php echo _l('fe_serial_number_format'); ?></label>
                <div class="radio radio-primary radio-inline">
                  <input type="radio" id="number_based" name="fe_serial_number_format" value="1"  <?php if(get_option('fe_serial_number_format') == 1 ){ echo 'checked';} ?>>
                  <label for="number_based"><?php echo _l('fe_serial_number_format_number_based'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                  <input type="radio" name="fe_serial_number_format" value="2" id="year_month_based"  <?php if(get_option('fe_serial_number_format') == 2 ){ echo 'checked';} ?>>
                  <label for="year_month_based">YYYYMMDD000001</label>
                </div>
              </div>
            </div>

             <div class="row">
            <div class="col-md-12">
              <h4 class="no-margin font-bold h5-color"><?php echo _l('fe_issue_numbers') ?></h4>
              <hr class="hr-color">
            </div>
          </div>

          <div class="col-md-6">
          <label><?php echo _l('fe_issue_prefix'); ?></label>
          <div class="form-group" app-field-wrapper="fe_issue_prefix">
            <input type="text" id="fe_issue_prefix" name="fe_issue_prefix" class="form-control" value="<?php echo get_option('fe_issue_prefix'); ?>"></div>
          </div>

          <div class="col-md-6">

            <div class="form-group">
              <label> <?php echo _l('fe_next_issue_number'); ?></label>
              <i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('next_delivery_received_mumber_tooltip'); ?>"></i>

              <div  class="form-group" app-field-wrapper="fe_next_issue_number">
                <input type="number" min="0" id="fe_next_issue_number" name="fe_next_issue_number" class="form-control" value="<?php echo get_option('fe_next_issue_number'); ?>"></div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label for="fe_issue_number_format" class="control-label clearfix"><?php echo _l('fe_issue_number_format'); ?></label>
                <div class="radio radio-primary radio-inline">
                  <input type="radio" id="issue_number_based" name="fe_issue_number_format" value="1"  <?php if(get_option('fe_issue_number_format') == 1 ){ echo 'checked';} ?>>
                  <label for="issue_number_based"><?php echo _l('fe_issue_number_format_number_based'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                  <input type="radio" name="fe_issue_number_format" value="2" id="issue_year_month_based"  <?php if(get_option('fe_issue_number_format') == 2 ){ echo 'checked';} ?>>
                  <label for="issue_year_month_based">YYYYMMDD000001</label>
                </div>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>


          </body>
          </html>


