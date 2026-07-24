
"use strict";

var GoodsreceiptParams = {
    "staff_id": "[name='staff_id[]']",
    "vendor": "[name='vendor[]']",
    "status": "[name='status[]']",
    "pur_order": "[name='pur_order[]']",
};

var table_manage_goods_receipt = $('.table-table_manage_goods_receipt');

initDataTable('.table-table_manage_goods_receipt', admin_url + 'warehouse/table_manage_goods_receipt', [], [], GoodsreceiptParams, [0, 'desc']);

$('.purchase_sm').DataTable().columns([0]).visible(false, false);

$('#date_add').on('change', function () {
    table_manage_goods_receipt.DataTable().ajax.reload();
});

$.each(GoodsreceiptParams, function (i, obj) {
    $('select' + obj).on('change', function () {
        table_manage_goods_receipt.DataTable().ajax.reload();
    });
});
$(document).on('change', 'select[name="staff_id[]"]', function () {
    $('select[name="staff_id[]"]').selectpicker('refresh');
});

$(document).on('change', 'select[name="vendor[]"]', function () {
    $('select[name="vendor[]"]').selectpicker('refresh');
});

$(document).on('change', 'select[name="status[]"]', function () {
    $('select[name="status[]"]').selectpicker('refresh');
});

$(document).on('change', 'select[name="pur_order[]"]', function () {
    $('select[name="pur_order[]"]').selectpicker('refresh');
});
$(document).on('click', '.reset_all_ot_filters', function () {
    var filterArea = $('.all_ot_filters');
    filterArea.find('input').val("");
    filterArea.find('select').selectpicker("val", "");
    table_manage_goods_receipt.DataTable().ajax.reload();
    get_stock_received_dashboard();
});

$(document).on('change', 'select[name="vendor[]"], select[name="months-report"], input[name="report-from"], input[name="report-to"]', function () {
    get_stock_received_dashboard();
});

get_stock_received_dashboard();
init_goods_receipt();
function init_goods_receipt(id) {
    "use strict";
    load_small_table_item_proposal(id, '#purchase_sm_view', 'purchase_id', 'warehouse/view_purchase', '.purchase_sm');
}
var hidden_columns = [3, 4, 5];


function load_small_table_item_proposal(pr_id, selector, input_name, url, table) {
    "use strict";

    var _tmpID = $('input[name="' + input_name + '"]').val();
    // Check if id passed from url, hash is prioritized becuase is last
    if (_tmpID !== '' && !window.location.hash) {
        pr_id = _tmpID;
        // Clear the current id value in case user click on the left sidebar credit_note_ids
        $('input[name="' + input_name + '"]').val('');
    } else {
        // check first if hash exists and not id is passed, becuase id is prioritized
        if (window.location.hash && !pr_id) {
            pr_id = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
        }
    }
    if (typeof (pr_id) == 'undefined' || pr_id === '') { return; }
    if (!$("body").hasClass('small-table')) { toggle_small_view_proposal(table, selector); }
    $('input[name="' + input_name + '"]').val(pr_id);
    do_hash_helper(pr_id);
    $(selector).load(admin_url + url + '/' + pr_id);
    if (is_mobile()) {
        $('html, body').animate({
            scrollTop: $(selector).offset().top + 150
        }, 600);
    }

}


function toggle_small_view_proposal(table, main_data) {
    "use strict";

    $("body").toggleClass('small-table');
    var tablewrap = $('#small-table');
    if (tablewrap.length === 0) { return; }
    var _visible = false;
    if (tablewrap.hasClass('col-md-5')) {
        tablewrap.removeClass('col-md-5').addClass('col-md-12');
        _visible = true;
        $('.toggle-small-view').find('i').removeClass('fa fa-angle-double-right').addClass('fa fa-angle-double-left');
    } else {
        tablewrap.addClass('col-md-5').removeClass('col-md-12');
        $('.toggle-small-view').find('i').removeClass('fa fa-angle-double-left').addClass('fa fa-angle-double-right');
    }
    var _table = $(table).DataTable();
    // Show hide hidden columns
    _table.columns(hidden_columns).visible(_visible, false);
    _table.columns.adjust();
    $(main_data).toggleClass('hide');
    $(window).trigger('resize');

}

function get_stock_received_dashboard() {
    "use strict";

    var data = {
        vendors: $('select[name="vendor[]"]').val(),
        report_months: $('select[name="months-report"]').val(),
        report_from: $('input[name="report-from"]').val(),
        report_to: $('input[name="report-to"]').val(),
    }

    $.post(admin_url + 'warehouse/get_stock_received_dashboard', data).done(function (response) {
        response = JSON.parse(response);

        // Update value summaries
        $('.total_receipts').text(response.total_receipts);
        $('.total_received_po').text(response.total_received_po);
        $('.total_po').text(response.total_po);
        $('.total_quantity_received').text(response.total_quantity_received);
        $('.total_client_supply').text(response.total_client_supply);
        $('.total_bought_out_items').text(response.total_bought_out_items);
        $('.amount_vs_order_value').text(response.amount_vs_order_value + '%');
        $('.nos_vs_order_items').text(response.nos_vs_order_items + '%');
         var lineChartOverTime = null; 
        // LINE CHART - Receipts Over Time
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
                        label: 'Receipts',
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
                                text: 'Month'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Receipts'
                            }
                        }
                    }
                }
            });
        }

        // BAR CHART - Top 10 Suppliers by Receipts
        var vendorBarCtx = document.getElementById('barChartTopVendors').getContext('2d');
        var vendorLabels = response.bar_top_vendor_name;
        var vendorData = response.bar_top_vendor_value;

        if (window.barTopVendorsChart) {
            barTopVendorsChart.data.labels = vendorLabels;
            barTopVendorsChart.data.datasets[0].data = vendorData;
            barTopVendorsChart.update();
        } else {
            window.barTopVendorsChart = new Chart(vendorBarCtx, {
                type: 'bar',
                data: {
                    labels: vendorLabels,
                    datasets: [{
                        label: 'Receipts',
                        data: vendorData,
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
                                text: 'Receipts'
                            }
                        },
                        y: {
                            ticks: {
                                autoSkip: false
                            },
                            title: {
                                display: true,
                                text: 'Vendors'
                            }
                        }
                    }
                }
            });
        }

        

    });
}