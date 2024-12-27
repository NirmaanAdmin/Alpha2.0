  <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
  <?php init_head(); ?>
  <div id="wrapper">
   <div class="content">
    <div class="row">
      <div class="col-md-12">
       <h4><i class="fa fa-list-ul">&nbsp;&nbsp;</i><?php echo fe_htmldecode($title); ?></h4>
     </div>
   </div>
   <!-- Main content -->
   <div class="row">
    <div class="col-md-12">
      <?php echo form_open_multipart(admin_url('fixed_equipment/order_manual/'.$id), array('id'=>'add_order_manual')); ?>
      <div class="panel-body">
        <input type="hidden" name="id" value="<?php echo fe_htmldecode($id); ?>">
        <?php 
        if(isset($order)){
          echo form_hidden('isedit');
        }
        ?>
        <div class="row">
         <div class="col-md-6">
          <div class="row">
            <div class="col-md-12 form-group">
              <label for="userid"><?php echo _l('client'); ?></label>
              <select name="userid" id="userid" class="selectpicker"data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" >
                <option value=""></option>
                <?php foreach ($customers as $s) { ?>
                  <option value="<?php echo fe_htmldecode($s['userid']); ?>" <?php if (isset($order) && $order->userid == $s['userid']) {echo 'selected';}?>><?php echo fe_htmldecode($s['company']); ?></option>
                <?php }?>
              </select>
            </div>

            <div class="col-md-12">
              <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
              data-target="#billing_and_shipping_details"><i class="fa-regular fa-pen-to-square"></i></a>
              <?php include_once(APPPATH . 'views/admin/estimates/billing_and_shipping_template.php'); ?>
            </div>

            <div class="col-md-6">
              <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
              <address>
               <span class="billing_street">
                 <?php
                 $billing_street = (isset($order) ? $order->billing_street : '--');?>
                 <?php $billing_street = ($billing_street == '' ? '--' : $billing_street);?>
                 <?php echo fe_htmldecode($billing_street); ?></span><br>
                 <span class="billing_city">
                   <?php $billing_city = (isset($order) ? $order->billing_city : '--');?>
                   <?php $billing_city = ($billing_city == '' ? '--' : $billing_city);?>
                   <?php echo fe_htmldecode($billing_city); ?></span>,
                   <span class="billing_state">
                     <?php $billing_state = (isset($order) ? $order->billing_state : '--');?>
                     <?php $billing_state = ($billing_state == '' ? '--' : $billing_state);?>
                     <?php echo fe_htmldecode($billing_state); ?></span>
                     <br/>
                     <span class="billing_country">
                       <?php $billing_country = (isset($order) ? get_country_short_name($order->billing_country) : '--');?>
                       <?php $billing_country = ($billing_country == '' ? '--' : $billing_country);?>
                       <?php echo fe_htmldecode($billing_country); ?></span>,
                       <span class="billing_zip">
                         <?php $billing_zip = (isset($order) ? $order->billing_zip : '--');?>
                         <?php $billing_zip = ($billing_zip == '' ? '--' : $billing_zip);?>
                         <?php echo fe_htmldecode($billing_zip); ?></span>
                       </address>
                     </div>
                     <div class="col-md-6">
                      <p class="bold"><?php echo _l('ship_to'); ?></p>
                      <address>
                       <span class="shipping_street">
                         <?php $shipping_street = (isset($order) ? $order->shipping_street : '--');?>
                         <?php $shipping_street = ($shipping_street == '' ? '--' : $shipping_street);?>
                         <?php echo fe_htmldecode($shipping_street); ?></span><br>
                         <span class="shipping_city">
                           <?php $shipping_city = (isset($order) ? $order->shipping_city : '--');?>
                           <?php $shipping_city = ($shipping_city == '' ? '--' : $shipping_city);?>
                           <?php echo fe_htmldecode($shipping_city); ?></span>,
                           <span class="shipping_state">
                             <?php $shipping_state = (isset($order) ? $order->shipping_state : '--');?>
                             <?php $shipping_state = ($shipping_state == '' ? '--' : $shipping_state);?>
                             <?php echo fe_htmldecode($shipping_state); ?></span>
                             <br/>
                             <span class="shipping_country">
                               <?php $shipping_country = (isset($order) ? get_country_short_name($order->shipping_country) : '--');?>
                               <?php $shipping_country = ($shipping_country == '' ? '--' : $shipping_country);?>
                               <?php echo fe_htmldecode($shipping_country); ?></span>,
                               <span class="shipping_zip">
                                 <?php $shipping_zip = (isset($order) ? $order->shipping_zip : '--');?>
                                 <?php $shipping_zip = ($shipping_zip == '' ? '--' : $shipping_zip);?>
                                 <?php echo fe_htmldecode($shipping_zip); ?></span>
                               </address>
                             </div>

                             <div class="col-md-12">
                              <?php $order_number = isset($order) ? $order->order_number : $order_number_code; ?>
                              <?php echo render_input('order_number', 'fe_order_number', $order_number); ?>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="row">
                            <div class="col-md-12">
                              <?php $allowed_payment_modes = isset($order) ? $order->allowed_payment_modes : ''; ?>
                              <?php 
                              echo render_select('allowed_payment_modes', $payment_modes, array('id', 'name'), 'payment_methods', $allowed_payment_modes, [], [], '', '', false);
                              ?>  
                            </div>  
                          </div>

                          <div class="row">
                            <div class="col-md-6">
                             <?php
                             $discount_type = (isset($order) ? (int)$order->discount_type : 1);
                             $currency_attr = array('disabled'=>true,'data-show-subtext'=>true);
                             $currency_attr = apply_filters_deprecated('invoice_currency_disabled', [$currency_attr], '2.3.0', 'invoice_currency_attributes');
                             $selected = '';
                             foreach($currencies as $currency){
                               if($currency['isdefault'] == 1){
                                 $currency_attr['data-base'] = $currency['id'];
                               }
                               if(isset($order)){
                                if($currency['id'] == $order->currency){
                                 $selected = $currency['id'];
                               }
                             } else {
                               if($currency['isdefault'] == 1){
                                 $selected = $currency['id'];
                               }
                             }
                           }
                           ?>
                           <?php echo render_select('currency', $currencies, array('id','name','symbol'), 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                         </div>
                         <div class="col-md-6">
                           <?php
                           $i = 0;
                           $selected = '';
                           foreach($staff as $member){
                             if(isset($order)){
                               if($order->seller == $member['staffid']) {
                                 $selected = $member['staffid'];
                               }
                             }else{
                              if($member['staffid'] == get_staff_user_id()){
                                $selected = $member['staffid'];
                              }
                            }
                            $i++;
                          }
                          echo render_select('seller',$staff,array('staffid',array('firstname','lastname')),'sale_agent_string',$selected);
                          ?>
                        </div>
                      </div>
                      <div class="row">
                       <div class="col-md-12">
                        <?php $staff_note = (isset($order) ? $order->staff_note : '');?>
                        <?php echo render_textarea('staff_note', 'admin_note', $staff_note); ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- List -->
              <div class="panel-body mtop10 invoice-item">
                <div class="row">
                 <div class="col-md-4 mbot25">
                  <div class="">
                    <div class="items-select-wrapper">
                     <select name="item_select" class="selectpicker no-margin" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
                      <option value=""></option>
                      <?php foreach ($items as $group_id => $_items) {?>
                        <optgroup data-group-id="<?php echo fe_htmldecode($group_id); ?>" label="<?php echo fe_htmldecode($_items[0]['category_name']); ?>">
                         <?php foreach ($_items as $item) {?>
                           <option value="<?php echo fe_htmldecode($item['id']); ?>" data-subtext="<?php echo strip_tags(mb_substr($item['item_no'] ?? '', 0, 200)) . '...'; ?>"> <?php echo fe_htmldecode($item['item_name']); ?></option>
                         <?php }?>
                       </optgroup>
                     <?php }?>
                   </select>
                 </div>
               </div>
             </div>

           </div>

           <div class="table-responsive s_table">
             <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
              <thead>
               <tr>
                <th></th>
                <th width="50%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('invoice_table_item_heading'); ?></th>
                <th width="10%" align="right" class="qty"><?php echo _l('fe_available_quantity'); ?></th>
                <th width="10%" align="right" class="qty"><?php echo _l('quantity'); ?></th>
                <th width="15%" align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>

                <th width="15%" align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                <th align="center"><i class="fa fa-cog"></i></th>
              </tr>
            </thead>
            <tbody>
              <?php echo html_entity_decode($order_manual_row_template); ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-8 col-md-offset-4">
         <table class="table text-right">
          <tbody>
           <tr id="subtotal">
            <td><span class="bold"><?php echo _l('invoice_subtotal'); ?> :</span>
            </td>
            <td class="subtotal">
            </td>
          </tr>

          <tr>
            <td><span class="bold"><?php echo _l('invoice_total'); ?> :</span>
            </td>
            <td class="total">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="removed-items"></div>
  </div>
  <!-- End list -->
  <div class="row">
    <div class="col-md-12 mtop10">
     <div class="panel-body bottom-transaction">
      <div class="form-group" app-field-wrapper="clientnote">
        <?php $value = (isset($order) ? $order->notes : get_option('predefined_clientnote_invoice'));?>
        <?php echo render_textarea('notes', 'client_note', $value); ?>
      </div>                        
      <div class="form-group mtop15" app-field-wrapper="terms">
        <?php $value = (isset($order) ? $order->terms : get_option('predefined_terms_invoice'));?>
        <?php echo render_textarea('terms', 'terms_conditions', $value); ?>
      </div>            
    </div>
  </div>
</div>

<?php if ( has_permission('fixed_equipment_order_list', '', 'create') || has_permission('fixed_equipment_order_list', '', 'edit') || is_admin()) { ?>

 <div class="row">
  <div class="col-md-12 mtop15">
   <div class="panel-body bottom-transaction">
    <div class="btn-bottom-toolbar text-right">
      <a href="<?php echo admin_url('fixed_equipment/order_list'); ?>"class="btn btn-default text-right mright5"><?php echo _l('close'); ?></a>

        <button type="submit" class="btn-tr btn btn-info"><?php echo _l('submit'); ?></button>

    </div>
  </div>
</div>
</div>

<?php } ?>

<?php echo form_close(); ?>
</div>
</div>


</div>
</div>
<div id="modal_wrapper"></div>
<?php init_tail(); ?>
<?php require 'modules/fixed_equipment/assets/js/orders/manual_order_js.php';?>
</body>
</html>
