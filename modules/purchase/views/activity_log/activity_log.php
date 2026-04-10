<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$module_name = 'module_activity_log'; ?>
<style>
   .n_width {
      width: 20% !important;
   }
   .dashboard_stat_title {
      font-size: 19px;
      font-weight: bold;
   }
   .dashboard_stat_value {
      font-size: 19px;
   }
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
                        <div class="col-md-12">
                           <button class="btn btn-info display-block" type="button" data-toggle="collapse" data-target="#ac-charts-section" aria-expanded="true" aria-controls="ac-charts-section">
                              <?php echo _l('Activity Log Charts'); ?> <i class="fa fa-chevron-down toggle-icon"></i>
                           </button>
                        </div>
                     </div>

                     <div id="ac-charts-section" class="collapse in">
                        <div class="row">
                           <div class="col-md-12 mtop20">
                              <div class="row">
                                 <div class="quick-stats-invoices col-md-3 tw-mb-2 sm:tw-mb-0 n_width">
                                   <div class="top_stats_wrapper">
                                     <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_title">Total Activities Logged</span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                     <div class="tw-text-neutral-800 mtop15 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_value total_activities_logged"></span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                   </div>
                                 </div>
                                 <div class="quick-stats-invoices col-md-3 tw-mb-2 sm:tw-mb-0 n_width">
                                   <div class="top_stats_wrapper">
                                     <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_title">Active Staff Count</span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                     <div class="tw-text-neutral-800 mtop15 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_value active_staff_count"></span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                   </div>
                                 </div>
                                 <div class="quick-stats-invoices col-md-3 tw-mb-2 sm:tw-mb-0 n_width">
                                   <div class="top_stats_wrapper">
                                     <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_title">Most Active Person</span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                     <div class="tw-text-neutral-800 mtop15 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_value most_active_person"></span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                   </div>
                                 </div>
                                 <div class="quick-stats-invoices col-md-3 tw-mb-2 sm:tw-mb-0 n_width">
                                   <div class="top_stats_wrapper">
                                     <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_title">Activities Today</span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                     <div class="tw-text-neutral-800 mtop15 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_value activities_today"></span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                   </div>
                                 </div>
                                 <div class="quick-stats-invoices col-md-3 tw-mb-2 sm:tw-mb-0 n_width">
                                   <div class="top_stats_wrapper">
                                     <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_title">Last Updated (timestamp)</span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                     <div class="tw-text-neutral-800 mtop15 tw-flex tw-items-center tw-justify-between">
                                       <div class="tw-font-medium tw-inline-flex text-neutral-600 tw-items-center tw-truncate">
                                         <span class="tw-truncate dashboard_stat_value last_updated"></span>
                                       </div>
                                       <span class="tw-font-semibold tw-text-neutral-600 tw-shrink-0"></span>
                                     </div>
                                   </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row mtop20">
                           <div class="col-md-6">
                              <p class="mbot15 dashboard_stat_title">Staff Contribution</p>
                              <div style="width: 100%; height: 400px;">
                                <canvas id="barChartTopStaffs"></canvas>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <p class="mbot15 dashboard_stat_title">Activity Type Breakdown</p>
                              <div style="width: 100%; height: 400px;">
                                <canvas id="lineChartOverTime"></canvas>
                              </div>
                           </div>
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
                           if (isset($_GET['module']) && $_GET['module'] == 'stckrec') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'stckiss') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'inv_app') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'ex') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'po') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'pur_invoice') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'ven') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'inv_payment') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'invoices') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'estimates') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'timesheets') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           if (isset($_GET['module']) && $_GET['module'] == 'forms') {
                              $module_name_filter_val = $_GET['module'];
                           }
                           $module_name_list = [
                              ['id' => 'pr', 'name' => _l('purchase_request')],
                              ['id' => 'pur_app', 'name' => _l('Purchase approval')],
                              ['id' => 'stckrec', 'name' => _l('stock_import')],
                              ['id' => 'stckiss', 'name' => _l('stock_export')],
                              ['id' => 'inv_app', 'name' => _l('Inventory approval')],
                              ['id' => 'ex', 'name' => _l('Expense')],
                              ['id' => 'po', 'name' => _l('purchase_order')],
                              ['id' => 'pur_invoice', 'name' => _l('pur_invoice')],
                              ['id' => 'ven', 'name' => _l('vendor')],
                              ['id' => 'inv_payment', 'name' => _l('payment')],
                              ['id' => 'invoices', 'name' => _l('invoices')],
                              ['id' => 'estimates', 'name' => _l('estimates')],
                              ['id' => 'timesheets', 'name' => _l('Leaves')],
                              ['id' => 'forms', 'name' => _l('Progress Report')],
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

      $('#ac-charts-section').on('shown.bs.collapse', function () {
         $('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      });

      $('#ac-charts-section').on('hidden.bs.collapse', function () {
         $('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      });

      get_activity_log_dashboard();

      $(document).on('change', 'select[name="module_name[]"], select[name="staff[]"]', function() {
        get_activity_log_dashboard();
      });

      var lineChartOverTime;

      function get_activity_log_dashboard() {
        "use strict";

        var data = {
          module_name: $('select[name="module_name[]"]').val(),
          staff: $('select[name="staff[]"]').val(),
        }

        $.post(admin_url + 'purchase/get_activity_log_charts', data).done(function(response){
          response = JSON.parse(response);

          // Update value summaries
          $('.total_activities_logged').text(response.total_activities_logged);
          $('.active_staff_count').text(response.active_staff_count);
          $('.most_active_person').text(response.most_active_person);
          $('.activities_today').text(response.activities_today);
          $('.last_updated').text(response.last_updated);

          // Staff Contribution
          var staffBarCtx = document.getElementById('barChartTopStaffs').getContext('2d');
          var staffLabels = response.bar_top_staff_name;
          var staffData = response.bar_top_staff_value;

          if (window.barTopStaffsChart) {
            barTopStaffsChart.data.labels = staffLabels;
            barTopStaffsChart.data.datasets[0].data = staffData;
            barTopStaffsChart.update();
          } else {
            window.barTopStaffsChart = new Chart(staffBarCtx, {
              type: 'bar',
              data: {
                labels: staffLabels,
                datasets: [{
                  label: 'Total Count',
                  data: staffData,
                  backgroundColor: '#1E90FF',
                  borderColor: '#1E90FF',
                  borderWidth: 1
                }]
              },
              options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false
                  }
                },
                scales: {
                  x: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Total Count'
                    }
                  },
                  y: {
                    ticks: {
                      autoSkip: false
                    },
                    title: {
                      display: true,
                      text: 'Staffs'
                    }
                  }
                }
              }
            });
          }

          // Activity Type Breakdown
          var lineCtx = document.getElementById('lineChartOverTime').getContext('2d');

          if (lineChartOverTime) {
            lineChartOverTime.data.labels = response.line_order_date;
            lineChartOverTime.data.datasets[0].data = response.line_order_total;
            lineChartOverTime.update();
          } else {
            lineChartOverTime = new Chart(lineCtx, {
              type: 'line',
              data: {
                labels: response.line_order_date,
                datasets: [{
                  label: 'Total Count',
                  data: response.line_order_total,
                  fill: false,
                  borderColor: 'rgba(54, 162, 235, 1)',
                  backgroundColor: 'rgba(54, 162, 235, 0.2)',
                  tension: 0.3
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: true,
                    position: 'bottom'
                  },
                  tooltip: {
                    mode: 'index',
                    intersect: false
                  }
                },
                scales: {
                  x: {
                    title: {
                      display: true,
                      text: 'Weekly'
                    }
                  },
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Total Count'
                    }
                  }
                }
              }
            });
          }

        });
      }
   });
</script>
<script src="<?php echo module_dir_url(PURCHASE_MODULE_NAME, 'assets/plugins/charts/chart.js'); ?>?v=<?php echo PURCHASE_REVISION; ?>"></script>
</body>

</html>