<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'module_activity_log'; ?>
<style>
   b{
      font-weight: 700;
   }
</style>
<div id="wrapper">
   <div class="content">
      <div class="row">

         <div class="row">
            <div class="col-md-12" id="small-table">
               <div class="panel_s">
                  <div class="panel-body">
                     <div class="row">
                        <div class="col-md-12">
                           <h4 class="no-margin font-bold"><i class="fa fa-clipboard" aria-hidden="true"></i> <?php echo _l('activity_log'); ?></h4>
                           <hr />
                        </div>
                     </div>

                     <div class="row mtop20">
                        <div class="col-md-3">
                           <?php
                           $module_name_filter_val = [];
                           if (isset($_GET['module']) && $_GET['module'] == 'pr') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'pur_app') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           $module_name_list = [
                              ['id' => 'pr', 'name' => _l('purchase_request')],
                              ['id' => 'pur_app', 'name' => _l('Purchase approval')],
                           ];
                           echo render_select('module_name[]', $module_name_list, array('id', 'name'), '', $module_name_filter_val, array('data-width' => '100%', 'data-none-selected-text' => _l('module'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                           ?>
                        </div>
                        <div class="col-md-3 form-group">
                           <?php
                           echo render_select('staff[]', $staff, array('staffid', ['firstname','lastname']), '', [], array('data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                           ?>
                        </div>
                        <div class="col-md-3 form-group" id="report-time">
                          <select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                            <option value="this_month"><?php echo _l('this_month'); ?></option>
                            <option value="1"><?php echo _l('last_month'); ?></option>
                            <option value="this_year"><?php echo _l('this_year'); ?></option>
                            <option value="last_year"><?php echo _l('last_year'); ?></option>
                            <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                            <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                            <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                            <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                          </select>
                        </div>
                        <div id="date-range" class="hide mbot15">
                           <div class="col-md-3 form-group">
                              <div class="input-group date">
                                <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar calendar-icon"></i>
                                </div>
                              </div>
                           </div>
                           <div class="col-md-3 form-group">
                              <div class="input-group date">
                                <input type="text" class="form-control datepicker" disabled="disabled" id="report-to" name="report-to">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar calendar-icon"></i>
                                </div>
                              </div>
                           </div>
                        </div>
                        <?php $current_year = date('Y');
                        $y0 = (int)$current_year;
                        $y1 = (int)$current_year - 1;
                        $y2 = (int)$current_year - 2;
                        $y3 = (int)$current_year - 3;
                        ?>
                        <div class="form-group hide" id="year_requisition">
                          <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                          <select name="year_requisition" id="year_requisition" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('filter_by') . ' ' . _l('year'); ?>">
                            <option value="<?php echo pur_html_entity_decode($y0); ?>" <?php echo 'selected' ?>><?php echo _l('year') . ' ' . pur_html_entity_decode($y0); ?></option>
                            <option value="<?php echo pur_html_entity_decode($y1); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y1); ?></option>
                            <option value="<?php echo pur_html_entity_decode($y2); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y2); ?></option>
                            <option value="<?php echo pur_html_entity_decode($y3); ?>"><?php echo _l('year') . ' ' . pur_html_entity_decode($y3); ?></option>
                          </select>
                        </div>
                     </div>
                     <br>

                     <p style="font-style: italic; font-weight: bold;">Note: Activity logs will be deleted if they are older than 90 days.</p>

                     <table class="dt-table-loading table table-table_activity_log">
                        <thead>
                           <tr>
                              <th><?php echo _l('decription'); ?></th>
                              <th><?php echo _l('date'); ?></th>
                              <th><?php echo _l('staff'); ?></th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                        <tbody></tbody>
                     </table>

                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>
<script>
   $(document).ready(function() {
      var report_from = $('input[name="report-from"]');
      var report_to = $('input[name="report-to"]');
      var date_range = $('#date-range');
      var table_activity_log = $('.table-table_activity_log');
      var Params = {
         "module_name": "[name='module_name[]']",
         "staff": "[name='staff[]']",
         "report_months": "[name='months-report']",
         "report_from": "[name='report-from']",
         "report_to": "[name='report-to']",
         "year_requisition": "[name='year_requisition']",
      };
      initDataTable(table_activity_log, admin_url + 'purchase/table_activity_log', [], [], Params, [1, 'desc']);
      $.each(Params, function(i, obj) {
         $('select' + obj).on('change', function() {
            table_activity_log.DataTable().ajax.reload();
         });
      });

      $('select[name="year_requisition"]').on('change', function() {
        table_activity_log.DataTable().ajax.reload();
      });

      report_from.on('change', function() {
        var val = $(this).val();
        var report_to_val = report_to.val();
        if (val != '') {
          report_to.attr('disabled', false);
          if (report_to_val != '') {
            table_activity_log.DataTable().ajax.reload();
          }
        } else {
          report_to.attr('disabled', true);
        }
      });

      report_to.on('change', function() {
        var val = $(this).val();
        if (val != '') {
          table_activity_log.DataTable().ajax.reload();
        }
      });

      $('select[name="months-report"]').on('change', function() {
        var val = $(this).val();
        report_to.attr('disabled', true);
        report_to.val('');
        report_from.val('');
        if (val == 'custom') {
          date_range.addClass('fadeIn').removeClass('hide');
          return;
        } else {
          if (!date_range.hasClass('hide')) {
            date_range.removeClass('fadeIn').addClass('hide');
          }
        }
        table_activity_log.DataTable().ajax.reload();
      });
   });
</script>
</body>

</html>