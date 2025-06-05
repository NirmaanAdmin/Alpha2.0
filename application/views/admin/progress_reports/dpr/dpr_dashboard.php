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
            <div class="col-md-4">
              <div class="table-responsive s_table">
                <table class="table items no-mtop" style="border: 1px solid #dee2e6;">
                    <tbody>
                        <?php 
                        if(!empty($sub_type_array)) {
                            foreach ($sub_type_array as $key => $value) { 
                              if(isset($value['name'])) {
                                $name = $value['name'];
                                unset($value['name']);
                              }
                              if(isset($value['is_bold'])) {
                                $is_bold = $value['is_bold'];
                                unset($value['is_bold']);
                              }
                              ?>
                                <tr<?php echo $is_bold ? ' style="font-weight: bold; background: #f1f5f9; color: #1e293b;"' : ''; ?>>
                                    <td align="left">
                                        <?php echo $name; ?>
                                    </td>
                                    <?php 
                                    foreach ($value as $vkey => $vvalue) { ?>
                                      <td align="right">
                                        <?php echo $vvalue; ?>
                                      </td>
                                    <?php } ?>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-8">
              <canvas id="totalWorkforceChart" height="120"></canvas>
            </div>
          </div>

          <br><br>

          <div class="row">
            <div class="col-md-5">
              <div class="table-responsive s_table">
                <table class="table items no-mtop" style="border: 1px solid #dee2e6;">
                    <tbody>
                        <?php 
                        if(!empty($type_array)) {
                            foreach ($type_array as $key => $value) { 
                              if(isset($value['name'])) {
                                $name = $value['name'];
                                unset($value['name']);
                              }
                              if(isset($value['is_bold'])) {
                                $is_bold = $value['is_bold'];
                                unset($value['is_bold']);
                              }
                              ?>
                                <tr<?php echo $is_bold ? ' style="font-weight: bold; background: #f1f5f9; color: #1e293b;"' : ''; ?>>
                                    <td align="left">
                                        <?php echo $name; ?>
                                    </td>
                                    <?php 
                                    foreach ($value as $vkey => $vvalue) { ?>
                                      <td align="right">
                                        <?php echo $vvalue; ?>
                                      </td>
                                    <?php } ?>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-7">
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
                $isUnskilled = strtolower($label) === 'unskilled';
                $background = $isUnskilled ? '#888' : ($i === 0 ? 'rgba(54, 162, 235, 0.7)' : 'rgba(255, 159, 64, 0.7)');
                $border = $isUnskilled ? '#888' : ($i === 0 ? 'rgba(54, 162, 235, 1)' : 'rgba(255, 159, 64, 1)');
                $type = $isUnskilled ? 'line' : 'bar';

                echo '{';
                echo "label: '$label',";
                echo 'data: ' . json_encode($data) . ',';
                echo "type: '$type',";
                echo "backgroundColor: '$background',";
                echo "borderColor: '$border',";
                echo 'borderWidth: 1,';
                if ($isUnskilled) {
                    echo "fill: false, tension: 0.3, yAxisID: 'y'";
                }
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
