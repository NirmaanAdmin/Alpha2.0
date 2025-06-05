<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <div class="col-md-12">

          <div class="row">
             <div class="col-md-12">
                <h4 class="no-margin font-bold"><i class="fa fa-clipboard" aria-hidden="true"></i> <?php echo _l('daily_progress_report'); ?></h4>
                <hr />
             </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive s_table">
                <table class="table items no-mtop" style="border: 1px solid #dee2e6;">
                    <tbody>
                        <tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;">
                          <td align="left">Row Labels</td>
                          <?php 
                          if (!empty($progress_report_sub_type)) {
                            foreach ($progress_report_sub_type as $pkey => $pvalue) { ?>
                              <td align="right">
                                <?php echo $pvalue['name']; ?>
                              </td>
                            <?php }
                          } ?>
                        </tr>
                        <?php 
                        if (!empty($forms) && !empty($progress_report_sub_type) && !empty($sub_type_array)) {
                            foreach ($forms as $key => $value) {
                                $formid = $value['formid'];
                                ?>
                                <tr>
                                    <td align="left"><?php echo date('d-m-Y', strtotime($value['date'])); ?></td>
                                    <?php
                                    foreach ($progress_report_sub_type as $pkey => $pvalue) {
                                        $sub_type_id = $pvalue['id'];
                                        $sub_type_filtered = array_filter($sub_type_array, function ($item) use ($formid, $sub_type_id) {
                                            return $item['formid'] == $formid && $item['sub_type'] == $sub_type_id;
                                        });

                                        $sub_type_filtered = array_values($sub_type_filtered);
                                        ?>
                                        <td align="right">
                                            <?php echo !empty($sub_type_filtered) ? $sub_type_filtered[0]['total'] : 0; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
              </div>
            </div>
          </div>

          <br><br>

          <div class="row">
            <div class="col-md-12">
              <div class="table-responsive s_table">
                <table class="table items no-mtop" style="border: 1px solid #dee2e6;">
                    <tbody>
                        <tr style="font-weight: bold; background: #f1f5f9; color: #1e293b;">
                          <td align="left">Row Labels</td>
                          <?php 
                          if (!empty($progress_report_type)) {
                            foreach ($progress_report_type as $pkey => $pvalue) { ?>
                              <td align="right">
                                <?php echo $pvalue['name']; ?>
                              </td>
                            <?php }
                          } ?>
                        </tr>
                        <?php 
                        if (!empty($forms) && !empty($progress_report_type) && !empty($type_array)) {
                            foreach ($forms as $key => $value) {
                                $formid = $value['formid'];
                                ?>
                                <tr>
                                    <td align="left"><?php echo date('d-m-Y', strtotime($value['date'])); ?></td>
                                    <?php
                                    foreach ($progress_report_type as $pkey => $pvalue) {
                                        $type_id = $pvalue['id'];
                                        $type_filtered = array_filter($type_array, function ($item) use ($formid, $type_id) {
                                            return $item['formid'] == $formid && $item['type'] == $type_id;
                                        });

                                        $type_filtered = array_values($type_filtered);
                                        ?>
                                        <td align="right">
                                            <?php echo !empty($type_filtered) ? $type_filtered[0]['total'] : 0; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
              </div>
            </div>
          </div>

          <br><br>

          <div class="row">
            <div class="col-md-12">
                <canvas id="totalWorkforceChart" height="120"></canvas>
            </div>
          </div>

          <br><br>

          <div class="row">
            <div class="col-md-12">
              <canvas id="stackedLaborChart" height="130"></canvas>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const totalWorkforceChart = document.getElementById('totalWorkforceChart').getContext('2d');
  const chart = new Chart(totalWorkforceChart, {
      type: 'bar',
      data: {
        labels: <?= json_encode($total_workforce_labels['labels']); ?>,
        datasets: [
          <?php
          $i = 0;
          $count = count($total_workforce_values);
          foreach ($total_workforce_values as $label => $data) {
              $hue = ($i * (360 / $count)) % 360;
              $bgColor = "hsl($hue, 70%, 60%)";
              $type = 'bar';

              echo '{';
              echo "label: '$label',";
              echo 'data: ' . json_encode($data) . ',';
              echo "type: '$type',";
              echo "backgroundColor: '$bgColor',";
              echo "borderColor: '$bgColor',";
              echo 'borderWidth: 1';
              echo '}';
              if (++$i < $count) echo ',';
          }
          ?>
        ]
      },
      options: {
          responsive: true,
          plugins: {
              title: {
                  display: true,
                  text: 'Total Workforce'
              }
          },
          scales: {
              y: {
                  beginAtZero: true
              }
          }
      }
  });
</script>
<script>
  const stackedLaborChart = document.getElementById('stackedLaborChart').getContext('2d');
  const datasets = [
  <?php
    $total = count($stacked_labor_values);
    $index = 0;
    foreach ($stacked_labor_values as $label => $values) {
        $hue = ($index * (360 / $total)) % 360;
        $bgColor = "hsl($hue, 70%, 60%)";
        echo "{";
        echo "label: '" . htmlspecialchars($label, ENT_QUOTES) . "',";
        echo "data: " . json_encode($values) . ",";
        echo "backgroundColor: '$bgColor',";
        echo "borderWidth: 1";
        echo "}";
        if (++$index < $total) echo ",";
    }
    ?>
  ];

  const stackedChart = new Chart(stackedLaborChart, {
      type: 'bar',
      data: {
          labels: <?= json_encode($stacked_labor_labels['labels']); ?>,
          datasets: datasets
      },
      options: {
          responsive: true,
          plugins: {
              title: {
                  display: true,
                  text: 'Stacked Workforce by Category'
              },
              tooltip: {
                  mode: 'index',
                  intersect: false
              }
          },
          scales: {
              x: {
                  stacked: true
              },
              y: {
                  stacked: true,
                  beginAtZero: true
              }
          }
      }
  });
</script>
