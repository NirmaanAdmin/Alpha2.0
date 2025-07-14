<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
  .hamburger-menu {
    position: relative;
    display: inline-block;
  }

  .hamburger-icon {
    font-size: 24px;
    cursor: pointer;
    user-select: none;
  }

  .download-options {
    display: none;
    position: absolute;
    top: 30px;
    left: 0;
    background-color: #f9f9f9;
    min-width: 140px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
  }

  .download-options button {
    width: 100%;
    padding: 10px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
  }

  .download-options button:hover {
    background-color: #ddd;
  }
</style>
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
              <div class="col-md-2 pull-right" style="padding-right: 0px;">
                <?php
                $default_project = !empty($projects) ? $projects[0]['id'] : '';
                echo render_select('projects', $projects, array('id', 'name'), 'projects', $default_project);
                ?>
              </div>

              <div class="col-md-2 pull-right" style="padding-right: 0px;">
                <?php
                $default_end_date = date('d-m-Y'); // Today's date
                $end_date_value = isset($end_date) ? $end_date : $default_end_date;
                echo render_date_input('end_date', 'End Date', $end_date_value);
                ?>
              </div>
              <div class="col-md-2 pull-right" style="padding-right: 0px;">
                <?php
                $default_start_date = date('01-m-Y'); // First day of current month
                $start_date_value = isset($start_date) ? $start_date : $default_start_date;
                echo render_date_input('start_date', 'Start Date', $start_date_value);
                ?>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-12">
              <div class="hamburger-menu">
                <div class="hamburger-icon">&#9776;</div>
                <div class="download-options">
                  <button id="downloadTotalWorkforceChart">Download Image</button>
                  <button id="downloadTotalWorkforcePDF">Download PDF</button>
                </div>
              </div>
              <canvas id="totalWorkforceChart" height="120"></canvas>
            </div>
          </div>
          <br><br>


          <div class="row">
            <div class="col-md-12">
              <div class="hamburger-menu">
                <div class="hamburger-icon">&#9776;</div>
                <div class="download-options">
                  <button id="downloadStackedLaborChart">Download Image</button>
                  <button id="downloadStackedLaborPDF">Download PDF</button>
                </div>
              </div>
              <canvas id="stackedLaborChart" height="130"></canvas>
            </div>
          </div>


          <br><br>

          <div class="row">
            <span style="padding: 0px; margin-bottom: 12px;">
              <button id="export-csv" class="btn btn-primary pull-right">Export to CSV</button>
            </span>
            <div class="col-md-12" style="margin-top: 10px;">
              <div class="preport_sub_type_html">
              </div>
            </div>
          </div>

          <br><br>

          <div class="row">
            <span style="padding: 0px; margin-bottom: 12px;">
              <button id="export-csv-new" class="btn btn-primary pull-right">Export to CSV</button>
            </span>
            <div class="col-md-12" style="margin-top: 10px;">
              <div class="preport_type_html">
              </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
  document.getElementById('export-csv').addEventListener('click', function() {
    try {
      // Select the table
      const table = document.querySelector('.preportSubTypeTable');
      if (!table) {
        throw new Error('Table with class "items-preview" not found');
      }

      const rows = Array.from(table.querySelectorAll('tr'));

      // Initialize CSV content with UTF-8 BOM
      let csvContent = '\uFEFF';

      // Loop through each row
      rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        const rowContent = cells.map(cell => {
          // Escape quotes by doubling them and wrap in quotes
          const text = cell.textContent.trim().replace(/"/g, '""');
          return `"${text}"`;
        }).join(',');
        csvContent += rowContent + '\r\n'; // Using \r\n for Windows compatibility
      });

      // Create a Blob and downloadable link
      const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
      });
      const url = URL.createObjectURL(blob);

      // Create a temporary link and trigger download
      const link = document.createElement('a');
      link.href = url;
      link.download = 'workforce_by_category.csv';
      link.style.display = 'none';

      // Add link to DOM and trigger click
      document.body.appendChild(link);
      link.click();

      // Clean up
      setTimeout(() => {
        document.body.removeChild(link);
        URL.revokeObjectURL(url); // Release the object URL
      }, 100);
    } catch (error) {
      console.error('Error exporting to CSV:', error);
      alert('An error occurred while exporting to CSV. Please check the console for details.');
    }
  });

  document.getElementById('export-csv-new').addEventListener('click', function() {
    try {
      // Select the table
      const table = document.querySelector('.preportTypeTable');
      if (!table) {
        throw new Error('Table with class "items-preview" not found');
      }

      const rows = Array.from(table.querySelectorAll('tr'));

      // Initialize CSV content with UTF-8 BOM
      let csvContent = '\uFEFF';

      // Loop through each row
      rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        const rowContent = cells.map(cell => {
          // Escape quotes by doubling them and wrap in quotes
          const text = cell.textContent.trim().replace(/"/g, '""');
          return `"${text}"`;
        }).join(',');
        csvContent += rowContent + '\r\n'; // Using \r\n for Windows compatibility
      });

      // Create a Blob and downloadable link
      const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
      });
      const url = URL.createObjectURL(blob);

      // Create a temporary link and trigger download
      const link = document.createElement('a');
      link.href = url;
      link.download = 'total_workforce.csv';
      link.style.display = 'none';

      // Add link to DOM and trigger click
      document.body.appendChild(link);
      link.click();

      // Clean up
      setTimeout(() => {
        document.body.removeChild(link);
        URL.revokeObjectURL(url); // Release the object URL
      }, 100);
    } catch (error) {
      console.error('Error exporting to CSV:', error);
      alert('An error occurred while exporting to CSV. Please check the console for details.');
    }
  });
  $('select[name="projects"]').on('change', function() {
    get_dpr_dashboard();
  });

  $('input[name="start_date"]').on('change', function() {
    get_dpr_dashboard();
  });

  $('input[name="end_date"]').on('change', function() {
    get_dpr_dashboard();
  });

  get_dpr_dashboard();

  function get_dpr_dashboard() {
    "use strict";
    var data = {
      projects: $('select[name="projects"]').val(),
      start_date: $('input[name="start_date"]').val(),
      end_date: $('input[name="end_date"]').val()
    };
    $.post(admin_url + 'forms/get_dpr_dashboard', data).done(function(res) {
      var response = JSON.parse(res);
      $('.preport_sub_type_html').html(response.preport_sub_type_html);
      $('.preport_type_html').html(response.preport_type_html);

      // === Total Workforce Chart ===
      if (window.totalWorkforceChartInstance) {
        window.totalWorkforceChartInstance.destroy();
      }
      const ctx = document.getElementById('totalWorkforceChart').getContext('2d');
      const totalDatasets = response.total_workforce_values.map(function(ds, i, arr) {
        var hue = (i * 360 / arr.length) % 360;
        var bg = 'hsl(' + hue + ', 70%, 60%)';
        return {
          label: ds.label,
          data: ds.data,
          backgroundColor: bg,
          borderColor: bg,
          borderWidth: 1
        };
      });
      window.totalWorkforceChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: response.total_workforce_labels,
          datasets: totalDatasets
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
      // Toggle download options on hamburger click
      document.querySelector('.hamburger-icon').addEventListener('click', function() {
        var options = document.querySelector('.download-options');
        options.style.display = options.style.display === 'block' ? 'none' : 'block';
      });

      // Optional: Close the menu if clicked outside
      window.addEventListener('click', function(e) {
        if (!document.querySelector('.hamburger-menu').contains(e.target)) {
          document.querySelector('.download-options').style.display = 'none';
        }
      });

      document.getElementById('downloadTotalWorkforceChart').addEventListener('click', function() {
        const link = document.createElement('a');
        link.download = 'total_workforce_chart.png';
        link.href = document.getElementById('totalWorkforceChart').toDataURL('image/png');
        link.click();
      });

      document.getElementById('downloadTotalWorkforcePDF').addEventListener('click', function() {
        const {
          jsPDF
        } = window.jspdf;
        const pdf = new jsPDF();

        const chartCanvas = document.getElementById('totalWorkforceChart');
        const imgData = chartCanvas.toDataURL('image/png', 1.0);

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (chartCanvas.height / chartCanvas.width) * pdfWidth;

        pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight);
        pdf.text("Total Workforce Chart", 10, 10);
        pdf.save("total_workforce_chart.pdf");
      });


      // === Stacked Labor Chart ===
      if (window.stackedLaborChartInstance) {
        window.stackedLaborChartInstance.destroy();
      }
      const stackedLaborCtx = document.getElementById('stackedLaborChart').getContext('2d');
      const stackedDatasets = Object.keys(response.stacked_labor_values).map(function(label, i, arr) {
        var hue = (i * 360 / arr.length) % 360;
        var bg = 'hsl(' + hue + ', 70%, 60%)';
        return {
          label: label,
          data: response.stacked_labor_values[label],
          backgroundColor: bg,
          borderWidth: 1
        };
      });
      window.stackedLaborChartInstance = new Chart(stackedLaborCtx, {
        type: 'bar',
        data: {
          labels: response.stacked_labor_labels,
          datasets: stackedDatasets
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

    }).fail(function(xhr) {
      console.error("Error loading dashboard data:", xhr.responseText);
    });
    // Hamburger toggle for stackedLaborChart
    document.querySelectorAll('.hamburger-icon').forEach(function(icon) {
      icon.addEventListener('click', function(e) {
        var options = this.nextElementSibling;
        options.style.display = options.style.display === 'block' ? 'none' : 'block';
        e.stopPropagation();
      });
    });

    // Close any open menus on outside click
    window.addEventListener('click', function() {
      document.querySelectorAll('.download-options').forEach(function(menu) {
        menu.style.display = 'none';
      });
    });
    // Download Stacked Labor Chart Image
    document.getElementById('downloadStackedLaborChart').addEventListener('click', function() {
      const link = document.createElement('a');
      link.download = 'stacked_workforce_by_category.png';
      link.href = document.getElementById('stackedLaborChart').toDataURL('image/png');
      link.click();
    });

    // Download Stacked Labor Chart as PDF
    document.getElementById('downloadStackedLaborPDF').addEventListener('click', function() {
      const {
        jsPDF
      } = window.jspdf;
      const pdf = new jsPDF();

      const chartCanvas = document.getElementById('stackedLaborChart');
      const imgData = chartCanvas.toDataURL('image/png', 1.0);

      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (chartCanvas.height / chartCanvas.width) * pdfWidth;

      pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight);
      pdf.text("Stacked Workforce by Category", 10, 10);
      pdf.save("stacked_workforce_by_category.pdf");
    });
  }
</script>