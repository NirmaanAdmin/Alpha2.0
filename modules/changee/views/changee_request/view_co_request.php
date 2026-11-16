<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php $csrf = array(
              'name' => $this->security->get_csrf_token_name(),
              'hash' => $this->security->get_csrf_hash(),
            );
            ?>

            <input type="hidden" id="csrf_token_name" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>" />
            <?php if ($co_request->currency != 0) {

              $base_currency = changee_pur_get_currency_by_id($co_request->currency);
            } else {
              $base_currency = $base_currency;
            } ?>
            <?php if ($co_request->status == 1) { ?>
              <div class="ribbon info"><span class="fontz9"><?php echo _l('changee_draft'); ?></span></div>
            <?php } elseif ($co_request->status == 2) { ?>
              <div class="ribbon success"><span><?php echo _l('changee_approved'); ?></span></div>
            <?php } elseif ($co_request->status == 3) { ?>
              <div class="ribbon danger"><span><?php echo _l('changee_reject'); ?></span></div>
            <?php } ?>
            <h4 class=""><?php echo _l($title); ?>
            </h4>
            <div class="row">
              <div class="horizontal-scrollable-tabs preview-tabs-top">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                  <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                    <li role="presentation" class="<?php if ($this->input->get('tab') != 'attachment') {
                                                      echo 'active';
                                                    } ?>">
                      <a href="#information" aria-controls="information" role="tab" data-toggle="tab">
                        <?php echo _l('pur_information'); ?>
                      </a>
                    </li>

                    <li role="presentation" class="<?php if ($this->input->get('tab') == 'attachment') {
                                                      echo 'active';
                                                    } ?> ">
                      <a href="#attachment" aria-controls="attachment" role="tab" data-toggle="tab">
                        <?php echo _l('pur_attachment'); ?>
                      </a>
                    </li>

                    <?php $quotations = changee_get_quotations_by_co_request($co_request->id); ?>
                    <li role="presentation" class="">
                      <a href="#compare_quotes" aria-controls="compare_quotes" role="tab" data-toggle="tab">
                        <?php echo _l('compare_quotes') . '(' . count($quotations) . ')'; ?>
                      </a>
                    </li>



                  </ul>
                </div>
              </div>
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane ptop10 <?php if ($this->input->get('tab') != 'attachment') {
                                                              echo 'active';
                                                            } ?>" id="information">

                  <div class="row">
                    <div class="col-md-12">
                      <p class="bold col-md-4 p_style"><?php echo _l('information'); ?></p>
                      <div>
                        <?php if ($co_request->status == 2) { ?>
                          <a href="<?php echo admin_url('changee/pur_order?pr=' . $co_request->id); ?>" class="btn btn-info save_detail pull-right" target="_blank"><?php echo _l('convert_to_co'); ?></a>
                        <?php } ?>
                      </div>
                      <div class="col-md-3 pull-right">
                        <div class="task-info task-status task-info-status pull-right">
                          <?php if ($check_approval_setting) { ?>
                            <h5>
                              <i class="fa fa-<?php if ($co_request->status == 2) {
                                                echo 'star';
                                              } else if ($co_request->status == 1) {
                                                echo 'star-o';
                                              } else {
                                                echo 'star-half-o';
                                              } ?> pull-left task-info-icon fa-fw fa-lg mtop10"></i><?php echo _l('task_status'); ?>:
                              <?php if (has_permission('changee_request_change_approve_status', '', 'edit')) { ?>
                                <span class="task-single-menu task-menu-status">
                                  <span class="trigger pointer manual-popover text-has-action">
                                    <?php echo changee_pur_format_approve_status($co_request->status, true); ?>
                                  </span>
                                  <span class="content-menu hide">
                                    <ul>
                                      <?php
                                      for ($pur_status = 1; $pur_status <= 3; $pur_status++) { ?>
                                        <?php if ($co_request->status != $pur_status) { ?>
                                          <li>
                                            <a href="#" onclick="changee_request_mark_as(<?php echo $pur_status; ?>,<?php echo $co_request->id; ?>); return false;">
                                              <?php echo _l('changee_request_mark_as', changee_get_status_approve_str($pur_status)); ?>
                                            </a>
                                          </li>
                                        <?php } ?>
                                      <?php } ?>
                                    </ul>
                                  </span>
                                </span>
                              <?php } else { ?>
                                <?php echo changee_pur_format_approve_status($co_request->status, true); ?>
                              <?php } ?>
                            </h5>
                          <?php } ?>
                        </div>

                      </div>
                      <div class=" col-md-12">
                        <hr class="hr_style" />
                      </div>
                    </div>
                  </div>

                  <div class=" col-md-12">
                    <table class="table border table-striped martop0">
                      <tbody>
                        <!-- Row 1 -->
                        <tr class="project-overview">
                          <td width="50%">
                            <span class="bold"><?php echo _l('pur_rq_code'); ?> :</span>
                            <span><?php echo changee_pur_html_entity_decode($co_request->pur_rq_code); ?></span>
                          </td>
                          <td width="50%">
                            <span class="bold"><?php echo _l('group_pur'); ?> :</span>
                            <span><?php foreach ($commodity_groups_request as $group) {
                                    if ($group['id'] == $co_request->group_pur) {
                                      echo $group['name'];
                                    }
                                  } ?></span>
                          </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="project-overview">
                          <td width="50%">
                            <span class="bold"><?php echo _l('pur_rq_name'); ?> :</span>
                            <span><?php echo changee_pur_html_entity_decode($co_request->pur_rq_name); ?></span>
                          </td>
                          <td width="50%">
                            <span class="bold"><?php echo _l('sub_groups_pur'); ?> :</span>
                            <span><?php foreach ($sub_groups_request as $group) {
                                    if ($group['id'] == $co_request->sub_groups_pur) {
                                      echo $group['sub_group_name'];
                                    }
                                  } ?></span>
                          </td>
                        </tr>
                        <!-- Row 3 -->
                        <tr class="project-overview">
                          <td width="50%">
                            <span class="bold"><?php echo _l('changee_requestor'); ?> :</span>
                            <span>
                              <?php $_data = '<a href="' . admin_url('staff/profile/' . $co_request->requester) . '">' . staff_profile_image($co_request->requester, ['staff-profile-image-small']) . '</a>';
                              $_data .= ' <a href="' . admin_url('staff/profile/' . $co_request->requester) . '">' . get_staff_full_name($co_request->requester) . '</a>';
                              echo changee_pur_html_entity_decode($_data); ?>
                            </span>
                          </td>
                          <td width="50%">
                            <span class="bold"><?php echo _l('area_pur'); ?> :</span>
                            <span><?php foreach ($area_request as $area) {
                                    if ($area['id'] == $co_request->area_pur) {
                                      echo $area['area_name'];
                                    }
                                  } ?></span>
                          </td>
                        </tr>

                        <tr class="project-overview">
                          <td width="50%">
                            <span class="bold"><?php echo _l('request_date'); ?> :</span>
                            <span><?php echo _dt($co_request->request_date); ?></span>
                          </td>
                          <td width="50%">
                            <span class="bold"><?php echo _l('pdf'); ?> : </span>
                            <span>
                              <div class="btn-group">
                                <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf"></i><?php if (is_mobile()) {
                                                                                                                                                                                      echo ' PDF';
                                                                                                                                                                                    } ?> <span class="caret"></span></a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                  <li class="hidden-xs"><a href="<?php echo admin_url('changee/co_request_pdf/' . $co_request->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                  <li class="hidden-xs"><a href="<?php echo admin_url('changee/co_request_pdf/' . $co_request->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                  <li><a href="<?php echo admin_url('changee/co_request_pdf/' . $co_request->id); ?>"><?php echo _l('download'); ?></a></li>
                                </ul>
                            </span>


                          </td>
                          </td>
                        </tr>
                        <tr class="project-overview">
                          <td width="50%">
                            <span class="bold"><?php echo _l('project'); ?> :</span>
                            <span><?php echo get_project_name_by_id($co_request->project); ?></span>
                          </td>
                          <td width="100%" class="d-flex">
                            <span class="bold"><?php echo _l('public_link'); ?></span>
                            <span>
                              <div class="pull-right _buttons mright5">
                                <a href="javascript:void(0)" onclick="copy_public_link(<?php echo changee_pur_html_entity_decode($co_request->id); ?>); return false;" class="btn btn-warning btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('copy_public_link'); ?>" data-placement="bottom"><i class="fa fa-clone "></i></a>
                              </div>
                              <div class="col-md-9">
                                <?php if ($co_request->hash != '' && $co_request->hash != null) {
                                  echo render_input('link_public', '', site_url('changee/vendors_portal/co_request/' . $co_request->id . '/' . $co_request->hash));
                                } else {
                                  echo render_input('link_public', '', '');
                                } ?>
                              </div>
                            </span>
                          </td>
                        </tr>
                        <tr>

                        </tr>
                        <tr class="project-overview">

                        </tr>
                        <tr class="project-overview">
                          <td class="bold" width="50%">
                            <span class="bold"><?php echo _l('rq_description'); ?> :</span>
                            <span><?php echo changee_pur_html_entity_decode($co_request->rq_description); ?></span>
                          </td>
                          <td class="bold" width="50%">
                            <span class="bold"><?php echo _l('kind'); ?> :</span>
                            <span><?php echo $co_request->kind; ?></span>
                          </td>      

                        </tr>

                      </tbody>
                    </table>
                  </div>





                  <div class="col-md-12">
                    <p class=" p_style"><?php echo _l('pur_detail'); ?></p>
                    <hr class="hr_style" />

                    <div class="table-responsive">
                      <table class="table items items-preview estimate-items-preview" data-type="estimate">
                        <thead>
                          <tr>

                            <th width="15%" align="left"><?php echo _l('debit_note_table_item_heading'); ?></th>
                            <th width="15%" align="right" class="qty"><?php echo _l('decription'); ?></th>
                            <th width="10%" align="right" class="qty"><?php echo _l('original_quantity'); ?></th>
                            <th width="10%" align="right" class="qty"><?php echo _l('updated_quantity'); ?></th>
                            <th width="10%" align="right"><?php echo _l('original_unit_price'); ?></th>
                            <th width="10%" align="right"><?php echo _l('updated_unit_price'); ?></th>

                            <th width="10%" align="right"><?php echo _l('contract_value_before_tax'); ?></th>
                            <th width="10%" align="right"><?php echo _l('updated_subtotal_before_tax'); ?></th>
                            <th width="10%" align="right"><?php echo _l('debit_note_table_tax_heading'); ?></th>
                            <th width="10%" align="right"><?php echo _l('tax_value'); ?></th>
                            <th width="10%" align="right"><?php echo _l('debit_note_total'); ?></th>
                            <th width="10%" align="right"><?php echo _l('remarks'); ?></th>
                          </tr>
                        </thead>
                        <tbody class="ui-sortable">

                          <?php $_subtotal = 0;
                          $_total = 0;
                          if (count($co_request_detail) > 0) {
                            $count = 1;
                            $t_mn = 0;
                            foreach ($co_request_detail as $es) {
                              $_subtotal += $es['into_money_updated'];
                              $_total += $es['total'];
                          ?>
                              <tr nobr="true" class="sortable">

                                <td class="description" align="left;"><span><strong><?php
                                                                                    $item = changee_get_item_hp($es['item_code']);
                                                                                    if (isset($item) && isset($item->commodity_code) && isset($item->description)) {
                                                                                      echo changee_pur_html_entity_decode($item->commodity_code . ' - ' . $item->description);
                                                                                    } else {
                                                                                      echo changee_pur_html_entity_decode($es['item_text']);
                                                                                    }
                                                                                    ?></strong></td>
                                <?php
                                $diff =  $es['unit_price'] - $es['original_unit_price'];
                                $diff_unit = $es['quantity'] - $es['original_quantity'];
                                $unit_name = changee_pur_get_unit_name($es['unit_id']);
                                ?>
                                <td align="right"><?php echo nl2br($es['description']); ?></td>
                                <td align="right"><?php echo changee_pur_html_entity_decode($es['original_quantity']) . ' ' . $unit_name; ?></br><span>Amendment :<?php echo  $diff_unit; ?></span></td>
                                <td align="right"><?php echo changee_pur_html_entity_decode($es['quantity']) . ' ' . $unit_name; ?></td>
                                <td align="right"><?php echo app_format_money($es['original_unit_price'], $base_currency->symbol); ?></br><span>Amendment :<?php echo  $diff; ?></span></td>
                                <td align="right"><?php echo app_format_money($es['unit_price'], $base_currency->symbol); ?></td>
                                <td align="right"><?php echo app_format_money($es['into_money'], $base_currency->symbol); ?></td>
                                <td align="right"><?php echo app_format_money($es['into_money_updated'], $base_currency->symbol); ?></td>
                                <td align="right"><?php
                                                  if ($es['tax_name'] != '') {
                                                    echo changee_pur_html_entity_decode($es['tax_name']);
                                                  } else {
                                                    $this->load->model('changee/changee_model');
                                                    if ($es['tax'] != '') {
                                                      $tax_arr =  $es['tax'] != '' ? explode('|', $es['tax'] ?? '') : [];
                                                      $tax_str = '';
                                                      if (count($tax_arr) > 0) {
                                                        foreach ($tax_arr as $key => $tax_id) {
                                                          if (($key + 1) < count($tax_arr)) {
                                                            $tax_str .= $this->changee_model->get_tax_name($tax_id) . '|';
                                                          } else {
                                                            $tax_str .= $this->changee_model->get_tax_name($tax_id);
                                                          }
                                                        }
                                                      }

                                                      echo changee_pur_html_entity_decode($tax_str);
                                                    }
                                                  }
                                                  ?></td>
                                <td align="right"><?php echo app_format_money($es['tax_value'], $base_currency->symbol); ?></td>

                                <td class="amount" align="right"><?php echo app_format_money($es['total'], $base_currency->symbol); ?></td>
                                <td class="remarks" align="right"><?php echo $es['remarks']; ?></td>
                              </tr>
                          <?php

                            }
                          } ?>
                        </tbody>
                      </table>
                    </div>


                  </div>
                  <div class="col-md-6 col-md-offset-6">
                    <table class="table text-right mbot0">
                      <tbody>
                        <tr id="subtotal">
                          <td class="td_style"><span class="bold"><?php echo _l('contract_value'); ?></span>
                          </td>
                          <td width="65%" id="total_td">

                            <?php echo app_format_money($_subtotal, $base_currency->symbol); ?>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <table class="table text-right">
                      <tbody id="tax_area_body">
                        <?php if (isset($co_request)) {
                          echo $taxes_data['html'];
                        ?>
                        <?php } ?>
                      </tbody>
                    </table>

                    <table class="table text-right">
                      <tbody id="tax_area_body">
                        <tr id="total">
                          <td class="td_style"><span class="bold"><?php echo _l('total'); ?></span>
                          </td>
                          <td width="65%" id="total_td">
                            <?php echo app_format_money($_total, $base_currency->symbol); ?>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <table class="table text-right">
                      <tbody id="tax_area_body">
                        <tr id="co_value">
                          <td class="td_style"><span class="bold"><?php echo _l('change_order_value'); ?></span>
                          </td>
                          <td width="65%" id="total_td">
                            <?php echo app_format_money($co_request->co_value, $base_currency->symbol); ?>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                  </div>
                  <?php echo form_hidden('request_detail'); ?>

                  <div class=" col-md-12">
                    <?php if (count($list_approve_status) > 0) { ?>
                      <p class=" p_style"><?php echo _l('pur_approval_infor'); ?></p>
                      <hr class="hr_style" />
                      <div class="project-overview-right">


                        <div class="row">
                          <div class="col-md-12 project-overview-expenses-finance">
                            <?php
                            $this->load->model('staff_model');
                            $enter_charge_code = 0;
                            foreach ($list_approve_status as $value) {
                              $value['staffid'] = explode(', ', $value['staffid'] ?? '');
                              if ($value['action'] == 'sign') {
                            ?>
                                <div class="col-md-3 apr_div">
                                  <p class="text-uppercase text-muted no-mtop bold">
                                    <?php
                                    $staff_name = '';
                                    $st = _l('status_0');
                                    $color = 'warning';
                                    foreach ($value['staffid'] as $key => $val) {
                                      if ($staff_name != '') {
                                        $staff_name .= ' or ';
                                      }
                                      $staff_name .= isset($this->staff_model->get($val)->firstname) ? $this->staff_model->get($val)->firstname : '';
                                    }
                                    echo changee_pur_html_entity_decode($staff_name);
                                    ?></p>
                                  <?php if ($value['approve'] == 2) {
                                  ?>
                                    <img src="<?php echo site_url(PURCHASE_PATH . 'co_request/signature/' . $co_request->id . '/signature_' . $value['id'] . '.png'); ?>" class="img_style">
                                    <br><br>
                                    <p class="bold text-center text-success"><?php echo _l('signed') . ' ' . _dt($value['date']); ?></p>
                                  <?php } ?>

                                </div>
                              <?php } else { ?>
                                <div class="col-md-3 apr_div">
                                  <p class="text-uppercase text-muted no-mtop bold">
                                    <?php
                                    $staff_name = '';
                                    foreach ($value['staffid'] as $key => $val) {
                                      if ($staff_name != '') {
                                        $staff_name .= ' or ';
                                      }
                                      $staff_name .= isset($this->staff_model->get($val)->firstname) ? $this->staff_model->get($val)->firstname : '';
                                    }
                                    echo changee_pur_html_entity_decode($staff_name);
                                    ?></p>
                                  <?php if ($value['approve'] == 2) {
                                  ?>
                                  <?php if($value['approve_by_admin'] == 1) { ?>
                                    <img src="<?php echo site_url(PURCHASE_PATH . 'approval/approved_by_admin.png'); ?>" class="img_style">
                                  <?php } else { ?>
                                    <img src="<?php echo site_url(PURCHASE_PATH . 'approval/approved.png'); ?>" class="img_style">
                                  <?php } ?>
                                  <?php } elseif ($value['approve'] == 3) { ?>
                                    <img src="<?php echo site_url(PURCHASE_PATH . 'approval/rejected.png'); ?>" class="img_style">
                                  <?php } ?>
                                  <br><br>
                                  <p><?php echo changee_pur_html_entity_decode($value['note']) ?></p>
                                  <p class="bold text-center text-<?php if ($value['approve'] == 2) {
                                                                    echo 'success';
                                                                  } elseif ($value['approve'] == 3) {
                                                                    echo 'danger';
                                                                  } ?>"><?php echo _dt($value['date']); ?></p>
                                </div>
                            <?php }
                            } ?>
                          </div>
                        </div>


                      </div>
                    <?php } ?>
                    <div class="pull-right">
                      <?php
                      if ($check_appr && $check_appr != false) {
                        if ($co_request->status == 1 && ($check_approve_status == false || $check_approve_status == 'reject')) { ?>
                          <a data-toggle="tooltip" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-success lead-top-btn lead-view" data-placement="top" href="#" onclick="send_request_approve(<?php echo changee_pur_html_entity_decode($co_request->id); ?>); return false;"><?php echo _l('send_request_approve_pur'); ?></a>
                        <?php }
                      }
                      if (isset($check_approve_status['staffid'])) {
                        ?>
                        <?php
                        if (in_array(get_staff_user_id(), $check_approve_status['staffid']) && !in_array(get_staff_user_id(), $get_staff_sign) && $co_request->status == 1) { ?>
                          <div class="btn-group">
                            <a href="#" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo _l('approve'); ?><span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right ul_style">
                              <li>
                                <div class="col-md-12">
                                  <?php echo render_textarea('reason', 'reason'); ?>
                                </div>
                              </li>
                              <li>
                                <div class="row text-right col-md-12">
                                  <a href="#" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="approve_request(<?php echo changee_pur_html_entity_decode($co_request->id); ?>); return false;" class="btn btn-success mright15"><?php echo _l('approve'); ?></a>
                                  <a href="#" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="deny_request(<?php echo changee_pur_html_entity_decode($co_request->id); ?>); return false;" class="btn btn-warning"><?php echo _l('deny'); ?></a>
                                </div>
                              </li>
                            </ul>
                          </div>
                        <?php }
                        ?>

                        <?php
                        if (in_array(get_staff_user_id(), $check_approve_status['staffid']) && in_array(get_staff_user_id(), $get_staff_sign)) { ?>
                          <button onclick="accept_action();" class="btn btn-success pull-right action-button"><?php echo _l('e_signature_sign'); ?></button>
                        <?php }
                        ?>
                      <?php
                      }
                      ?>
                    </div>

                  </div>

                </div>

                <div role="tabpanel" class="tab-pane  <?php if ($this->input->get('tab') == 'attachment') {
                                                        echo 'active';
                                                      } ?>" id="attachment">
                  <div class="col-md-12">
                    <?php
                    if (isset($attachments) && count($attachments) > 0) {
                      foreach ($attachments as $value) {
                        echo '<div class="col-md-3">';
                        $path = get_upload_path_by_type('changee') . 'co_request/' . $value['rel_id'] . '/' . $value['file_name'];
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
                </div>

                <div role="tabpanel" class="tab-pane ptop10 " id="compare_quotes">
                  <?php if (total_rows(db_prefix() . 'co_estimates', ['co_request' => $co_request->id]) > 0) { ?>

                    <!-- <div class="btn-group pull-right mright5" data-toggle="tooltip" title="<?php echo _l('compare_quotation_tooltip'); ?>">
                      <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo admin_url('changee/compare_quotation_pdf/' . $co_request->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('changee/compare_quotation_pdf/' . $co_request->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo admin_url('changee/compare_quotation_pdf/' . $co_request->id); ?>"><?php echo _l('download'); ?></a></li>
                      </ul>
                    </div> -->

                    <div class="col-md-6">
                      <table class="table border table-striped martop0">
                        <tbody>
                          <tr class="project-overview">
                            <td class="bold" width="30%">PR Code</td>
                            <td><?php echo changee_pur_html_entity_decode($co_request->pur_rq_code); ?><div class="btn-group  mright5" style="margin-left: 10px;" data-toggle="tooltip" title="" data-original-title="Preview/Download compare quotation pdf">
                                <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  <i class="fa fa-file-pdf"></i><span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                  <li class="hidden-xs"><a href="http://localhost/nirmaanbetacrm2/admin/changee/compare_quotation_pdf/6?output_type=I">View PDF</a></li>
                                  <li class="hidden-xs"><a href="http://localhost/nirmaanbetacrm2/admin/changee/compare_quotation_pdf/6?output_type=I" target="_blank">View PDF in New Tab</a></li>
                                  <li><a href="http://localhost/nirmaanbetacrm2/admin/changee/compare_quotation_pdf/6">Download</a></li>
                                </ul>
                              </div>
                            </td>
                          </tr>
                          <tr class="project-overview">
                            <td class="bold">PR Name</td>
                            <td><?php echo _l($co_request->pur_rq_name); ?></td>
                          </tr>
                          <tr class="project-overview">
                            <td class="bold"><?php echo _l('changee_requestor'); ?></td>
                            <td><?php $_data = '<a href="' . admin_url('staff/profile/' . $co_request->requester) . '">' . staff_profile_image($co_request->requester, [
                                  'staff-profile-image-small',
                                ]) . '</a>';
                                $_data .= ' <a href="' . admin_url('staff/profile/' . $co_request->requester) . '">' . get_staff_full_name($co_request->requester) . '</a>';
                                echo changee_pur_html_entity_decode($_data);
                                ?></td>
                          </tr>

                          <tr class="project-overview">
                            <td class="bold"><?php echo _l('request_date'); ?></td>
                            <td><?php echo _dt($co_request->request_date); ?></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="col-md-6">
                      <table class="table border table-striped martop0">
                        <tbody>

                          <tr class="project-overview">
                            <td class=""><span class="bold"><?= _l('group_pur') ?> :</span></td>
                            <td>
                              asdmamdad

                            </td>
                          </tr>
                          <tr class="project-overview">
                            <td class=""><span class="bold"><?= _l('sub_groups_pur'); ?> :</span></td>
                            <td>
                              asdmamdad

                            </td>
                          </tr>
                          <tr class="project-overview">
                            <td class=""><span class="bold"><?= _l('area_pur'); ?> :</span></td>
                            <td>
                              asdmamdad

                            </td>
                          </tr>

                          <tr class="project-overview">
                            <td colspan="3" class="bold text-center" width="30%"><?php echo _l('vendors'); ?></td>
                          </tr>
                          <?php

                          $arr_vendors = changee_get_arr_vendors_by_pr($co_request->id); ?>
                          <?php foreach ($arr_vendors as $vendor) { ?>
                            <tr class="project-overview">
                              <td class=""><span class="bold"><?php echo changee_pur_html_entity_decode($vendor->company); ?></span></td>
                              <td class=""><span class="bold"><?php echo _l('vendor_code') . ': ' ?></span><span class=""><?php echo changee_pur_html_entity_decode($vendor->vendor_code); ?></span></td>
                              <td class=""><span class="bold"><?php echo _l('phonenumber') . ': '; ?></span><span class=""><?php echo changee_pur_html_entity_decode($vendor->phonenumber); ?></span></td>
                            </tr>
                          <?php } ?>

                        </tbody>
                      </table>
                    </div>
                    <div class="col-md-12">
                      <hr>
                    </div>
                    <div class="col-md-12">
                      <div class="table-responsive">
                        <?php echo form_open(admin_url('changee/compare_quote_co_request/' . $co_request->id), array('id' => 'compare_quote_co_request-form'));  ?>
                        <table class="table table-bordered compare_quotes_table">
                          <thead class="bold">
                            <tr>
                              <th rowspan="2" scope="col"><span class="bold"><?php echo _l('items'); ?></span></th>
                              <th rowspan="2" scope="col"><span class="bold"><?php echo _l('pur_qty'); ?></span></th>
                              <th rowspan="2" scope="col"><span class="bold"><?php echo _l('unit'); ?></span></th>
                              <th rowspan="2" scope="col"><span class="bold"><?php echo _l('description'); ?></span></th>

                              <?php foreach ($quotations as $quote) { ?>
                                <th colspan="2" class="text-center"><span class="bold text-danger"><?php echo changee_format_pur_estimate_number($quote['id']) . ' - ' . changee_get_vendor_company_name($quote['vendor']); ?></span></th>
                              <?php } ?>
                            </tr>

                            <tr>
                              <?php foreach ($quotations as $quote) { ?>
                                <th class="text-right"><span class="bold"><?php echo _l('changee_unit_price'); ?></span></th>
                                <th class="text-right"><span class="bold"><?php echo _l('total') ?></span></th>
                              <?php } ?>

                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $this->load->model('changee/changee_model');
                            $list_items = $this->changee_model->get_co_request_detail($co_request->id);
                            ?>
                            <?php foreach ($list_items as $key => $item) { ?>
                              <tr>
                                <td><?php echo changee_pur_html_entity_decode($key + 1); ?></td>
                                <td><?php echo changee_pur_html_entity_decode($item['quantity']); ?></td>
                                <td><?php $unit_name = isset(changee_get_unit_type_item($item['unit_id'])->unit_name) ? changee_get_unit_type_item($item['unit_id'])->unit_name : '';
                                    echo changee_pur_html_entity_decode($unit_name); ?></td>
                                <td><?php $item_name = isset(changee_get_item_hp($item['item_code'])->description) ? changee_get_item_hp($item['item_code'])->description : '';
                                    echo changee_pur_html_entity_decode($item_name); ?></td>

                                <?php foreach ($quotations as $quote) { ?>
                                  <?php
                                  $_currency = $base_currency;
                                  if ($quote['currency'] != 0) {
                                    $_currency = changee_pur_get_currency_by_id($quote['currency']);
                                  }
                                  ?>
                                  <?php $item_quote = changee_get_item_detail_in_quote($item['item_code'], $quote['id']); ?>
                                  <?php if (isset($item_quote)) { ?>
                                    <td class="text-right"><?php echo app_format_money($item_quote->unit_price, $_currency->name); ?></td>
                                    <td class="text-right"><?php echo app_format_money($item_quote->total_money, $_currency->name); ?></td>
                                  <?php } else { ?>
                                    <td>-</td>
                                    <td>-</td>
                                  <?php } ?>
                                <?php } ?>

                              </tr>
                            <?php } ?>
                            <tr>
                              <td colspan="4" class="text-center"><span class="bold"><?php echo _l('mark_a_contract'); ?></span></td>
                              <?php foreach ($quotations as $quote) { ?>
                                <td colspan="2"><input name="mark_a_contract[<?php echo changee_pur_html_entity_decode($quote['id']); ?>]" type="text" value="<?php echo changee_pur_html_entity_decode($quote['make_a_contract']); ?>" /></td>
                              <?php } ?>
                            </tr>
                            <tr>
                              <td colspan="4" class="text-center"><span class="bold"><?php echo _l('total_changee_amount'); ?></span></td>
                              <?php foreach ($quotations as $quote) { ?>
                                <?php
                                $_currency = $base_currency;
                                if ($quote['currency'] != 0) {
                                  $_currency = changee_pur_get_currency_by_id($quote['currency']);
                                }
                                ?>
                                <td colspan="2" class="text-right">
                                  <span class="bold text-info"><?php echo app_format_money($quote['total'], $_currency->name); ?></span>
                                  <?php
                                  if ($_currency->id != $base_currency->id) {
                                    $convert_rate = changee_pur_get_currency_rate($_currency->name, $base_currency->name);
                                    $convert_value = round(($quote['total'] * $convert_rate), 2);
                                    echo '<br><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="' . _l('pur_convert_from') . ' ' . $_currency->name . ' ' . _l('pur_to') . ' ' . $base_currency->name . ' ' . _l('pur_with_currency_rate') . ': ' . $convert_rate . '"></i>&nbsp;&nbsp;<span class="bold text-info">' . app_format_money($convert_value, $base_currency->name) . '</span>';
                                  }
                                  ?>
                                </td>
                              <?php } ?>
                            </tr>
                          </tbody>

                        </table>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <br>
                      <p><span class="bold"><?php echo _l('changee_request_description') . ': '; ?></span><span><?php echo changee_pur_html_entity_decode($co_request->rq_description); ?></span></p>
                      <?php echo render_textarea('compare_note', 'comparison_notes', clear_textarea_breaks($co_request->compare_note)) ?>
                    </div>
                    <div class="col-md-12">
                      <button id="sm_btn" class="btn btn-info save_detail pull-right"><?php echo _l('pur_confirm'); ?></button>
                    </div>
                    <?php echo form_close(); ?>

                  <?php } else { ?>

                    <div class="col-md-12">
                      <span class="text-bold"><?php echo _l('this_changee_request_does_not_have_a_quote_yet'); ?></span>
                    </div>


                  <?php } ?>
                </div>

              </div>


            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="add_action" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-body">
        <p class="bold" id="signatureLabel"><?php echo _l('signature'); ?></p>
        <div class="signature-pad--body">
          <canvas id="signature" height="130" width="550"></canvas>
        </div>
        <input type="text" class="ip_style" tabindex="-1" name="signature" id="signatureInput">
        <div class="dispay-block">
          <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" onclick="signature_clear();"><?php echo _l('clear'); ?></button>

        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
        <button onclick="sign_request(<?php echo changee_pur_html_entity_decode($co_request->id); ?>);" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-success"><?php echo _l('e_signature_sign'); ?></button>
      </div>


    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php init_tail(); ?>
</body>

</html>
<?php require 'modules/changee/assets/js/view_co_request_js.php'; ?>