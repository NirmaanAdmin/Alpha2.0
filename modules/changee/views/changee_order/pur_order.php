<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php
      echo form_open_multipart($this->uri->uri_string(), array('id' => 'pur_order-form', 'class' => '_transaction_form'));
      if (isset($pur_order)) {
        echo form_hidden('isedit');
      }
      ?>
      <div class="col-md-12">
        <div class="panel_s accounting-template estimate">
          <div class="panel-body">
            <div class="horizontal-scrollable-tabs preview-tabs-top">
              <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
              <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
              <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                    <a href="#general_infor" aria-controls="general_infor" role="tab" data-toggle="tab">
                      <?php echo _l('pur_general_infor'); ?>
                    </a>
                  </li>
                  <?php
                  $customer_custom_fields = false;
                  if (total_rows(db_prefix() . 'customfields', array('fieldto' => 'pur_order', 'active' => 1)) > 0) {
                    $customer_custom_fields = true;
                  ?>

                  <?php } ?>

                  <li role="presentation" class="">
                    <a href="#shipping_infor" aria-controls="shipping_infor" role="tab" data-toggle="tab">
                      <?php echo _l('pur_shipping_infor'); ?>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="general_infor">
                <div class="row">
                  <?php $additional_discount = 0; ?>
                  <input type="hidden" name="additional_discount" value="<?php echo changee_pur_html_entity_decode($additional_discount); ?>">

                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label for="po_order_id"><?php echo _l('purchase_order'); ?></label>
                        <select name="po_order_id" id="po_order_id" onchange="coppy_pur_orders(); return false;" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <?php foreach ($pr_orders as $pr_order) { ?>
                            <option value="<?php echo html_entity_decode($pr_order['id']); ?>" <?php if (isset($pur_order) && ($pur_order->po_order_id == $pr_order['id'])) {
                              echo 'selected';
                            } ?>><?php echo html_entity_decode($pr_order['pur_order_number'] . ' - ' . $pr_order['pur_order_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <?php
                      $project_id = '';
                      if ($this->input->get('project')) {
                        $project_id = $this->input->get('project');
                      }
                      ?>
                      <div class="col-md-6 form-group">
                        <input type="hidden" name="project" id="project_val" value="">
                        <label for="project"><?php echo _l('project'); ?></label>
                        <select name="project" id="project" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <?php foreach ($projects as $s) { ?>
                            <option value="<?php echo changee_pur_html_entity_decode($s['id']); ?>" <?php if (isset($pur_order) && $s['id'] == $pur_order->project) {
                              echo 'selected';
                            } else if (!isset($pur_order) && $s['id'] == $project_id) {
                              echo 'selected';
                            } ?>><?php echo changee_pur_html_entity_decode($s['name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <?php $pur_order_name = (isset($pur_order) ? $pur_order->pur_order_name : '');
                        echo render_input('pur_order_name', 'co_order_description', $pur_order_name); ?>
                      </div>
                      <div class="col-md-6 form-group">
                        <?php $prefix = changee_get_changee_option('pur_order_prefix');
                        $next_number = changee_get_changee_option('next_po_number');
                        $pur_order_number = (isset($pur_order) ? $pur_order->pur_order_number : '');
                        if (get_option('po_only_prefix_and_number') == 1) {
                          $pur_order_number = (isset($pur_order) ? $pur_order->pur_order_number : '');
                        }
                        $number = (isset($pur_order) ? $pur_order->number : $next_number);
                        echo form_hidden('number', $number); ?>
                        <label for="pur_order_number"><?php echo _l('co_order_number'); ?></label>
                        <input type="text" readonly class="form-control" name="pur_order_number" id="pur_order_number" value="<?php echo changee_pur_html_entity_decode($pur_order_number); ?>">
                      </div>
                    </div>

                    <div class="row">
                      <div class="form-group col-md-6">
                        <?php $vendor_name = changee_get_vendor_company_name($pur_order->vendor);
                        ?>
                        <label for="vendor"><?php echo _l('vendor'); ?></label>
                        <input type="text" class="form-control" id="vendor_name" value="<?= $vendor_name ?>" readonly>
                        <input type="hidden" id="vendor" name="vendor" value="<?= $pur_order->vendor ?>">
                      </div>

                      <?php
                      if ($convert_po && $selected_pr && $selected_project) {
                        $pur_order['co_request'] = $selected_pr;
                        $pur_order['project'] = $selected_project;
                        $pur_order = (object) $pur_order;
                      }
                      ?>
                      <div class="col-md-6 form-group">
                        <label for="co_request"><?php echo _l('co_request'); ?></label>
                        <select name="co_request" id="co_request" class="selectpicker" onchange="coppy_co_request(); return false;" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <?php foreach ($co_request as $s) { ?>
                            <option value="<?php echo changee_pur_html_entity_decode($s['id']); ?>" <?php if (isset($pur_order) && $pur_order->co_request != '' && $pur_order->co_request == $s['id']) {
                              echo 'selected';
                            } ?>><?php echo changee_pur_html_entity_decode($s['pur_rq_code'] . ' - ' . $s['pur_rq_name']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label for="budget"><?php echo _l('budget'); ?></label>
                        <select name="estimate" id="estimate" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <?php foreach ($estimates as $s) { ?>
                            <option value="<?php echo pur_html_entity_decode($s['id']); ?>" <?php if (isset($pur_order) && $s['id'] == $pur_order->estimate) {
                              echo 'selected';
                            } ?>><?php echo format_estimate_number($s['id']); ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6 form-group">
                        <label for="department"><?php echo _l('department'); ?></label>
                        <select name="department" id="department" class="selectpicker" <?php if (isset($pur_order)) {
                          echo 'disabled';
                        } ?> data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                        <option value=""></option>
                        <?php foreach ($departments as $s) { ?>
                          <option value="<?php echo changee_pur_html_entity_decode($s['departmentid']); ?>" <?php if (isset($pur_order) && $s['departmentid'] == $pur_order->department) {
                            echo 'selected';
                          } ?>><?php echo changee_pur_html_entity_decode($s['name']); ?></option>
                        <?php } ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-6 ">
                        <?php
                        $currency_attr = array('disabled'=>true,'data-show-subtext'=>true);

                        $selected = '';
                        foreach ($currencies as $currency) {
                          if (isset($pur_order) && $pur_order->currency != 0) {
                            if ($currency['id'] == $pur_order->currency) {
                              $selected = $currency['id'];
                            }
                          } else {
                            if ($currency['isdefault'] == 1) {
                              $selected = $currency['id'];
                            }
                          }
                        }

                        ?>
                        <?php echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                      </div>

                      <div class="col-md-6">
                        <?php $order_date = (isset($pur_order) ? _d($pur_order->order_date) : _d(date('Y-m-d')));
                        echo render_date_input('order_date', 'order_date', $order_date); ?>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 ">
                        <?php
                        $selected = '';
                        foreach ($staff as $member) {
                          if (isset($pur_order)) {
                            if ($pur_order->buyer == $member['staffid']) {
                              $selected = $member['staffid'];
                            }
                          } else {
                            if ($member['staffid'] == get_staff_user_id()) {
                              $selected = $member['staffid'];
                            }
                          }
                        }
                        echo render_select('buyer', $staff, array('staffid', array('firstname', 'lastname')), 'buyer', $selected);
                        ?>
                      </div>
                      <div class="col-md-6 ">
                        <div class="form-group select-placeholder">
                          <label for="discount_type"
                          class="control-label"><?php echo _l('discount_type'); ?></label>
                          <select name="discount_type" class="selectpicker" data-width="100%"
                          data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">

                          <option value="before_tax" <?php
                          if (isset($pur_order)) {
                            if ($pur_order->discount_type == 'before_tax' || $pur_order->discount_type == null) {
                              echo 'selected';
                            }
                          } else {
                            echo 'selected';
                          } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                          <option value="after_tax" <?php if (isset($pur_order)) {
                            if ($pur_order->discount_type == 'after_tax') {
                              echo 'selected';
                            }
                          } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label for="kind"><?php echo _l('kind'); ?></label>
                        <select name="kind" id="kind" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <option value="Client Supply" <?php if (isset($pur_order) && $pur_order->kind == 'Client Supply') {echo 'selected'; } ?>><?php echo _l('client_supply'); ?></option>
                          <option value="Bought out items" <?php if (isset($pur_order) && $pur_order->kind == 'Bought out items') {echo 'selected'; } ?>><?php echo _l('bought_out_items'); ?></option>
                        </select>
                      </div>
                      <div class="col-md-6 form-group">
                        <label for="type"><?php echo _l('type'); ?></label>
                        <select name="type" id="type" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                          <option value=""></option>
                          <option value="capex" <?php if (isset($pur_order) && $pur_order->type == 'capex') { echo 'selected'; } ?>><?php echo _l('capex'); ?></option>
                          <option value="opex" <?php if (isset($pur_order) && $pur_order->type == 'opex') {echo 'selected'; } ?>><?php echo _l('opex'); ?></option>
                        </select>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12 form-group">
                        <div id="inputTagsWrapper">
                          <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                          <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($pur_order) ? prep_tags_input(get_tags_in($pur_order->id, 'pur_order')) : ''); ?>" data-role="tagsinput">
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

                <?php if ($customer_custom_fields) { ?>
                  <?php $rel_id = (isset($pur_order) ? $pur_order->id : false); ?>
                  <?php echo render_custom_fields('pur_order', $rel_id); ?>
                <?php } ?>
              </div>

              <div role="tabpanel" class="tab-pane" id="shipping_infor">
                <div class="row">
                  <div class="col-md-6">
                    <?php $shipping_address = isset($pur_order) ? $pur_order->shipping_address : get_option('pur_company_address');
                    if ($shipping_address == '') {
                      $shipping_address = get_option('pur_company_address');
                    }
                    echo render_textarea('shipping_address', 'pur_company_address', $shipping_address, ['rows' => 7]); ?>
                    <?php $shipping_zip = isset($pur_order) ? $pur_order->shipping_zip : get_option('pur_company_zipcode');
                    if ($shipping_zip == '') {
                      $shipping_zip = get_option('pur_company_zipcode');
                    }
                    echo render_input('shipping_zip', 'pur_company_zipcode', $shipping_zip, 'text'); ?>
                  </div>

                  <div class="col-md-6">
                    <div class="row">
                      <div class="col-md-12">
                        <?php $shipping_city = isset($pur_order) ? $pur_order->shipping_city : get_option('pur_company_city');
                        if ($shipping_city == '') {
                          $shipping_city = get_option('pur_company_city');
                        }
                        echo render_input('shipping_city', 'pur_company_city', $shipping_city, 'text'); ?>
                      </div>
                      <div class="col-md-12">
                        <?php $shipping_state = isset($pur_order) ? $pur_order->shipping_state : get_option('pur_company_state');
                        if ($shipping_state == '') {
                          $shipping_state = get_option('pur_company_state');
                        }
                        echo render_input('shipping_state', 'pur_company_state', $shipping_state, 'text'); ?>
                      </div>
                      <div class="col-md-12">
                        <?php $shipping_country_text = isset($pur_order) ? $pur_order->shipping_country_text : get_option('pur_company_country_text');
                        if ($shipping_country_text == '') {
                          $shipping_country_text = get_option('pur_company_country_text');
                        }
                        echo render_input('shipping_country_text', 'pur_company_country_text', $shipping_country_text, 'text'); ?>
                      </div>
                      <div class="col-md-12">
                        <?php $countries = get_all_countries();
                        $pur_company_country_code = get_option('pur_company_country_code');
                        $selected = isset($pur_order) ? $pur_order->shipping_country : $pur_company_country_code;
                        if ($selected == '') {
                          $selected = $pur_company_country_code;
                        }
                        echo render_select('shipping_country', $countries, array('country_id', array('short_name')), 'pur_company_country_code', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                        ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="panel-body">
            <label for="attachment"><?php echo _l('attachment'); ?></label>
            <div class="attachments">
              <div class="attachment">
                <div class="col-md-5 form-group" style="padding-left: 0px;">
                  <div class="input-group">
                    <input type="file" extension="<?php echo str_replace(['.', ' '], '', get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
                    <span class="input-group-btn">
                      <button class="btn btn-success add_more_attachments p8" type="button"><i class="fa fa-plus"></i></button>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <br /> <br />

            <?php
            if (isset($attachments) && count($attachments) > 0) {
              foreach ($attachments as $value) {
                echo '<div class="col-md-3">';
                $path = get_upload_path_by_type('changee') . 'pur_order/' . $value['rel_id'] . '/' . $value['file_name'];
                $is_image = is_image($path);
                if ($is_image) {
                  echo '<div class="preview_image">';
                }
            ?>
                <a href="<?php echo site_url('download/file/changee/' . $value['id']); ?>" class="display-block mbot5" <?php if ($is_image) { ?> data-lightbox="attachment-changee-<?php echo $value['rel_id']; ?>" <?php } ?>>
                  <i class="<?php echo get_mime_class($value['filetype']); ?>"></i> <?php echo $value['file_name']; ?>
                  <?php if ($is_image) { ?>
                    <img class="mtop5" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type=' . $value['filetype']); ?>" style="height: 165px;">
                  <?php } ?>
                </a>
                <?php if ($is_image) {
                  echo '</div>';
                  echo '<a href="' . admin_url('changee/delete_attachment/' . $value['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                } ?>
            <?php echo '</div>';
              }
            } ?>
          </div>

          <div class="panel-body mtop10 invoice-item">

            <div class="row">
              <div class="col-md-4">
                <button type="button" class="btn btn-info enable_item_select mbot5">
                  <?php echo _l('add_non_tender_items'); ?>
                </button>
                <?php $this->load->view('changee/item_include/main_item_select'); ?>
              </div>
              <?php
              $po_currency = $base_currency;
              if (isset($pur_order) && $pur_order->currency != 0) {
                $po_currency = changee_pur_get_currency_by_id($pur_order->currency);
              }

              $from_currency = (isset($pur_order) && $pur_order->from_currency != null) ? $pur_order->from_currency : $base_currency->id;
              echo form_hidden('from_currency', $from_currency);

              ?>
              <div class="col-md-8 hide" id="currency_rate_div">
                <div class="col-md-10 text-right">

                  <p class="mtop10"><?php echo _l('currency_rate'); ?><span id="convert_str"><?php echo ' (' . $base_currency->name . ' => ' . $po_currency->name . '): ';  ?></span></p>
                </div>
                <div class="col-md-2 pull-right">
                  <?php $currency_rate = 1;
                  if (isset($pur_order) && $pur_order->currency != 0) {
                    $currency_rate = changee_pur_get_currency_rate($base_currency->name, $po_currency->name);
                  }
                  echo render_input('currency_rate', '', $currency_rate, 'number', [], [], '', 'text-right');
                  ?>
                </div>
              </div>
            </div>
            <div class="row">
              <!-- <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" id="item" class="active">
                    <a href="#items" aria-controls="items" role="tab" id="tab_items" data-toggle="tab">
                      Items
                    </a>
                  </li>
                  <li role="presentation">
                    <a href="#history" aria-controls="history" role="tab" id="tab_history" class="hide" data-toggle="tab">
                      History
                    </a>
                  </li>


                </ul>
              </div> -->
              <!-- <div class="tab-content">
                <div role="tabpanel" class="tab-pane" id="items"> -->
              <div class="col-md-12">
                <div class="table-responsive s_table ">
                  <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
                    <thead>
                      <tr>
                        <th></th>
                        <th align="left" style="min-width: 76px"><?php echo _l('serial_no'); ?></th>
                        <th width="15%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('Uniclass Code'); ?></th>
                        <th width="14%" align="right"><?php echo _l('description'); ?></th>
                        <th width="9%" align="right"><?php echo _l('awarded_rate'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th>
                        <th width="9%" align="right"><?php echo _l('rate_after_incl_co'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th>
                        <th width="9%" align="right" class="qty"><?php echo _l('awarded_qty'); ?></th>
                        <th width="9%" align="right" class="qty"><?php echo _l('qty_after_incl_co'); ?></th>
                        <th width="9%" align="right"><?php echo _l('contract_value'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th>
                        <th width="9%" align="right"><?php echo _l('updated_subtotal_before_tax'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th>
                        <th width="5%" align="right"><?php echo _l('debit_note_table_tax_heading'); ?></th>
                        <!-- <th width="5%" align="right"><?php echo _l('tax_value'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th> -->
                        <th width="9%" align="right"><?php echo _l('debit_note_total'); ?><span class="th_currency"><?php echo '(' . $base_currency->symbol . ')'; ?></span></th>
                        <th width="9%" align="right"><?php echo _l('variation'); ?></th>
                        <!-- <th width="5%" align="right"><?php echo _l('remarks'); ?></th> -->
                        <th align="right"><i class="fa fa-cog"></i></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php echo $pur_order_row_template; ?>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-8 col-md-offset-4">
                  <table class="table text-right">
                    <tbody>
                      <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('subtotal_wo_tax'); ?> :</span>
                          <?php echo form_hidden('total_mn', ''); ?>
                        </td>
                        <td class="wh-subtotal">
                        </td>
                      </tr>

                      <tr id="order_discount_percent">
                        <td>
                          <div class="row">
                            <div class="col-md-7">
                              <span class="bold"><?php echo _l('pur_discount'); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="<?php echo _l('discount_percent_note'); ?>"></i></span>
                            </div>
                            <div class="col-md-3">
                              <?php $discount_total = isset($pur_order) ? $pur_order->discount_total : '';
                              echo render_input('order_discount', '', $discount_total, 'number', ['onchange' => 'pur_calculate_total()', 'onblur' => 'pur_calculate_total()']); ?>
                            </div>
                            <div class="col-md-2">
                              <select name="add_discount_type" id="add_discount_type" class="selectpicker" onchange="pur_calculate_total(); return false;" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                                <option value="percent">%</option>
                                <option value="amount" selected><?php echo _l('amount'); ?></option>
                              </select>
                            </div>
                          </div>
                        </td>
                        <td class="order_discount_value">

                        </td>
                      </tr>

                      <tr id="total_discount">
                        <td><span class="bold"><?php echo _l('total_discount'); ?> :</span>
                          <?php echo form_hidden('dc_total', ''); ?>
                        </td>
                        <td class="wh-total_discount">
                        </td>
                      </tr>

                      <tr>
                        <td>
                          <div class="row">
                            <div class="col-md-9">
                              <span class="bold"><?php echo _l('pur_shipping_fee'); ?></span>
                            </div>
                            <div class="col-md-3">
                              <input type="number" onchange="pur_calculate_total()" data-toggle="tooltip" value="<?php if (isset($pur_order)) {
                                echo $pur_order->shipping_fee;
                              } else {
                                echo '0';
                              } ?>" class="form-control pull-left text-right" name="shipping_fee">
                            </div>
                          </div>
                        </td>
                        <td class="shiping_fee">
                        </td>
                      </tr>

                      <tr id="totalmoney">
                        <td><span class="bold"><?php echo _l('grand_total'); ?> :</span>
                          <?php echo form_hidden('grand_total', ''); ?>
                        </td>
                        <td class="wh-total">
                        </td>
                      </tr>

                      <tr id="co_value">
                        <td><span class="bold"><?php echo _l('change_order_value'); ?> :</span>
                          <?php echo form_hidden('co_value', ''); ?>
                        </td>
                        <td class="wh-co-value">
                        </td>
                      </tr>

                      <tr id="non_tender_total">
                        <td><span class="bold"><?php echo _l('non_tender_items_in_change_order'); ?> :</span>
                          <?php echo form_hidden('non_tender_total', ''); ?>
                        </td>
                        <td class="wh-non-tender-total">
                        </td>
                      </tr>

                    </tbody>
                  </table>
                </div>
                <div id="removed-items"></div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane " id="history" class="hide">
              
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 mtop15">
              <div class="panel-body bottom-transaction">
                <?php $value = (isset($pur_order) ? $pur_order->order_summary : changee_get_changee_option('order_summary')); ?>
                <?php echo render_textarea('order_summary', 'estimate_add_edit_order_summary', $value, array(), array(), 'mtop15', 'tinymce'); ?>
                <?php $value = (isset($pur_order) ? $pur_order->vendornote : changee_get_changee_option('vendor_note')); ?>
                <?php echo render_textarea('vendornote', 'estimate_add_edit_vendor_note', $value, array(), array(), 'mtop15', 'tinymce'); ?>
                <?php $value = (isset($pur_order) ? $pur_order->terms :  changee_get_changee_option('terms_and_conditions')); ?>
                <?php echo render_textarea('terms', 'terms_and_conditions', $value, array(), array(), 'mtop15', 'tinymce'); ?>
                <div id="vendor_data">

                </div>
                <div class="btn-bottom-toolbar text-right">
                  <button type="button" class="btn-tr save_detail btn btn-info mleft10 transaction-submit">
                    <?php echo _l('submit'); ?>
                  </button>
                </div>
              </div>
              <div class="btn-bottom-pusher"></div>
            </div>
          </div>
        </div>

      </div>
      <?php echo form_close(); ?>

    </div>
  </div>
</div>
</div>
<?php init_tail(); ?>
</body>

</html>

<script type="text/javascript">
  var convert_po = '<?php echo $convert_po; ?>';
  if (convert_po) {
    $('#project').attr('disabled', true);
    $('#co_request').attr('disabled', true);
    $('#project_val').css('display', 'block');
    $('#project_val').val($('#project').val());
  } else {
    $('#project').attr('disabled', false);
    $('#co_request').attr('disabled', false);
    $('#project_val').remove();
  }
</script>

<?php require 'modules/changee/assets/js/pur_order_js.php'; ?>