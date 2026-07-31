(function ($) {
    "use strict";
    var table_invoice = $('.table-table_pur_invoices');
    var Params = {
        "from_date": 'input[name="from_date"]',
        "to_date": 'input[name="to_date"]',
        "contract": "[name='contract[]']",
        "pur_orders": "[name='pur_orders[]']",
        "vendors": "[name='vendor_ft[]']",
        "projects": "[name='project_ft[]']",
        "status": "[name='payment_status[]']",
    };

    initDataTable(table_invoice, admin_url + 'purchase/table_pur_invoices', [], [], Params);
    $.each(Params, function (i, obj) {
        $('select' + obj).on('change', function () {
            table_invoice.DataTable().ajax.reload()
                .columns.adjust()
                .responsive.recalc();
        });
    });

    $('input[name="from_date"]').on('change', function () {
        table_invoice.DataTable().ajax.reload()
            .columns.adjust()
            .responsive.recalc();
    });
    $('input[name="to_date"]').on('change', function () {
        table_invoice.DataTable().ajax.reload()
            .columns.adjust()
            .responsive.recalc();
    });

    $(document).on('change', 'select[name="vendor_ft[]"]', function () {
        get_vbt_dashboard();
    });

    get_vbt_dashboard();
})(jQuery);

function get_vbt_dashboard() {
    "use strict";

    var data = {
        vendors: $('select[name="vendor_ft[]"]').val(),
    }

    $.post(admin_url + 'purchase/get_vbt_dashboard', data).done(function (response) {
        response = JSON.parse(response);

        // Update value summaries
        $('.total_certified_amount').text(response.total_amount);
        $('.total_bills_not_tag_to_orders').text(response.paid_count);
        $('.total_uninvoice_bills').text(response.partially_paid_count);
        $('.total_pending_amount_to_be_invoice').text(response.unpaid_count);

        // BAR CHART - Top 10 Vendors by Total Certified Amount
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
                        label: 'Total Certified Value',
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
                                text: 'Total Value'
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

        // PIE CHART - Total Certified Amount per Budget Head
        var budgetPieCtx = document.getElementById('pieChartForBudget').getContext('2d');
        var budgetData = response.pie_total_value;
        var budgetLabels = response.pie_budget_name;

        if (window.budgetChart) {
            budgetChart.data.labels = budgetLabels;
            budgetChart.data.datasets[0].data = budgetData;
            budgetChart.update();
        } else {
            window.budgetChart = new Chart(budgetPieCtx, {
                type: 'pie',
                data: {
                    labels: budgetLabels,
                    datasets: [{
                        data: budgetData,
                        backgroundColor: budgetLabels.map((_, i) => `hsl(${i * 35 % 360}, 70%, 60%)`),
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.formattedValue;
                                }
                            }
                        }
                    }
                }
            });
        }

        // PIE CHART - Total Vendor Bills per Billing Status
        var billingPieCtx = document.getElementById('pieChartForBilling').getContext('2d');
        var billingData = response.pie_billing_value;
        var billingLabels = response.pie_billing_name;

        if (window.billingChart) {
            billingChart.data.labels = billingLabels;
            billingChart.data.datasets[0].data = billingData;
            billingChart.update();
        } else {
            window.billingChart = new Chart(billingPieCtx, {
                type: 'pie',
                data: {
                    labels: billingLabels,
                    datasets: [{
                        data: billingData,
                        backgroundColor: billingLabels.map((_, i) => `hsl(${i * 35 % 360}, 70%, 60%)`),
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.formattedValue;
                                }
                            }
                        }
                    }
                }
            });
        }

    });
}