<style type="text/css">
    .daily_report_title,
    .daily_report_activity {
        font-weight: bold;
        text-align: center;
        background-color: lightgrey;
    }

    .daily_report_title {
        font-size: 17px;
    }

    .daily_report_activity {
        font-size: 16px;
    }

    .daily_report_head {
        font-size: 14px;
    }

    .daily_report_label {
        font-weight: bold;
    }

    .daily_center {
        text-align: center;
    }

    .table-responsive {
        overflow-x: visible !important;
        scrollbar-width: none !important;
    }

    .laber-type .dropdown-menu .open,
    .agency .dropdown-menu .open {
        width: max-content !important;
    }

    .agency .dropdown-toggle,
    .laber-type .dropdown-toggle {
        width: 90px !important;
    }

    /* Responsive Stacked Layout */
    @media (max-width: 768px) {
        .dpr-items-table {
            display: block;
        }

        .dpr-items-table thead {
            display: none;
        }

        .dpr-items-table tbody {
            display: block;
        }

        .dpr-items-table tbody tr {
            display: block;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .dpr-items-table tbody tr td {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 8px 5px;
            border: none;
            border-bottom: 1px solid #eee;
            width: 100%;
        }

        .dpr-items-table tbody tr td:last-child {
            border-bottom: none;
        }

        .dpr-items-table tbody tr td:before {
            content: attr(data-label);
            font-weight: bold;
            min-width: 120px;
            padding-right: 10px;
            font-size: 13px;
            color: #333;
        }

        .dpr-items-table tbody tr td input,
        .dpr-items-table tbody tr td select {
            flex: 1;
            min-width: 150px;
        }

        .dpr-items-table tbody tr td .bootstrap-select {
            width: 100% !important;
            flex: 1;
            min-width: 150px;
        }

        .dpr-items-table tbody tr td .dropdown-toggle {
            width: 100% !important;
        }

        .dpr-items-table tbody tr td .btn-group {
            width: 100% !important;
        }

        /* Action button */
        .dpr-items-table tbody tr td:last-child {
            justify-content: flex-end;
        }

        .dpr-items-table tbody tr td:last-child:before {
            display: none;
        }

        /* Header row */
        .dpr-items-table tbody tr.item {
            display: block;
        }

        /* Remove button styling */
        .dpr-items-table tbody tr td .btn-danger {
            margin-left: auto;
        }

        /* Header sections */
        .table-main-dpr-edit {
            display: block;
        }

        /* Project and submission date */
        .table-main-dpr-edit thead tr:first-child th,
        .table-main-dpr-edit thead tr:nth-child(2) th,
        .table-main-dpr-edit thead tr:nth-child(3) th,
        .table-main-dpr-edit thead tr:nth-child(4) th {
            display: block;
            width: 100%;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .table-main-dpr-edit thead tr:first-child th {
            text-align: center;
        }

        .table-main-dpr-edit thead tr:nth-child(2) th,
        .table-main-dpr-edit thead tr:nth-child(3) th,
        .table-main-dpr-edit thead tr:nth-child(4) th {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }

        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            width: 100%;
        }

        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label input,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label input,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label input {
            width: auto !important;
            min-width: 120px;
            flex: 1;
        }

        /* Activity header */
        .table-main-dpr-edit thead tr:nth-child(5) th {
            display: block;
            width: 100%;
            text-align: center;
        }

        /* Hide the second header row with column labels */
        .table-main-dpr-edit thead tr:nth-child(6) {
            display: none;
        }

        /* Data labels for each column */
        .dpr-items-table tbody tr td:nth-child(1):before { content: "Location"; }
        .dpr-items-table tbody tr td:nth-child(2):before { content: "Agency"; }
        .dpr-items-table tbody tr td:nth-child(3):before { content: "Type"; }
        .dpr-items-table tbody tr td:nth-child(4):before { content: "Sub Type"; }
        .dpr-items-table tbody tr td:nth-child(5):before { content: "Work Execute"; }
        .dpr-items-table tbody tr td:nth-child(6):before { content: "Material Consumption"; }
        .dpr-items-table tbody tr td:nth-child(7):before { content: "Male"; }
        .dpr-items-table tbody tr td:nth-child(8):before { content: "Female"; }
        .dpr-items-table tbody tr td:nth-child(9):before { content: "Total"; }
        .dpr-items-table tbody tr td:nth-child(10):before { content: "Machinery"; }
        .dpr-items-table tbody tr td:nth-child(11):before { content: "Total Machinery"; }
        .dpr-items-table tbody tr td:nth-child(12):before { content: "Actions"; }
    }

    /* For very small screens */
    @media (max-width: 480px) {
        .dpr-items-table tbody tr td:before {
            min-width: 80px;
            font-size: 12px;
        }

        .dpr-items-table tbody tr td input,
        .dpr-items-table tbody tr td select {
            min-width: 100px;
            font-size: 12px;
        }

        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label select,
        .table-main-dpr-edit thead tr:nth-child(2) th .daily_report_label input,
        .table-main-dpr-edit thead tr:nth-child(3) th .daily_report_label input,
        .table-main-dpr-edit thead tr:nth-child(4) th .daily_report_label input {
            width: 100% !important;
        }
    }
</style>

<div class="col-md-12">
    <hr class="hr-panel-separator" />
</div>

<div class="col-md-12">
    <div class="table-responsive invoice-item">
        <?php
        if (isset($dpr_form)) {
            echo form_hidden('isedit');
        }
        ?>
        <table class="table dpr-items-table items table-main-dpr-edit has-calculations no-mtop">
            <thead>
                <tr>
                    <th colspan="13" class="daily_report_title">DAILY PROGRESS REPORT</th>
                </tr>
                <tr>
                    <th colspan="9" class="daily_report_head">
                        <span class="daily_report_label">Project: <span class="view_project_name"></span></span>
                    </th>
                    <th colspan="4" class="daily_report_head">
                        <span class="daily_report_label">Submission Date: </span>
                        <?php echo isset($dpr_main_form->date) ? date('d-m-Y', strtotime($dpr_main_form->date)) : date('d-m-Y'); ?>
                    </th>
                </tr>
                <tr>
                    <th colspan="5" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Client: <?php echo render_select('client_id', get_client_listing(), array('userid', 'company'), '', isset($dpr_form->client_id) ? $dpr_form->client_id : ''); ?></span>
                    </th>
                    <th colspan="4" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">PMC: <?php echo render_input('pmc', '', isset($dpr_form->pmc) ? $dpr_form->pmc : '', 'text', ['style' => 'width:150px;']); ?></span>
                    </th>
                    <th colspan="4" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Weather: <?php echo render_select('weather', get_weather_listing(), array('id', 'name'), '', isset($dpr_form->weather) ? $dpr_form->weather : ''); ?></span>
                    </th>
                </tr>
                <tr>
                    <th colspan="5" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Consultant: <?php echo render_input('consultant', '', isset($dpr_form->consultant) ? $dpr_form->consultant : '', 'text', ['style' => 'width:150px;']); ?></span>
                    </th>
                    <th colspan="4" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Contractor: <?php echo render_input('contractor', '', isset($dpr_form->contractor) ? $dpr_form->contractor : '', 'text', ['style' => 'width:150px;']); ?></span>
                    </th>
                    <th colspan="4" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Work Stop: <?php echo render_select('work_stop', get_work_stop_listing(), array('id', 'name'), '', isset($dpr_form->work_stop) ? $dpr_form->work_stop : ''); ?></span>
                    </th>
                </tr>
                <tr>
                    <th colspan="13" class="daily_report_activity">ACTIVITY WITH LOCATION & OUTPUT</th>
                </tr>
                <tr>
                    <th rowspan="2" class="daily_report_head daily_center" style="width: 160px;">
                        <span class="daily_report_label">Location</span>
                    </th>
                    <th rowspan="2" class="daily_report_head daily_center" style="width: 160px;">
                        <span class="daily_report_label">Agency</span>
                    </th>
                    <th rowspan="2" class="daily_report_head daily_center" style="width: 160px;">
                        <span class="daily_report_label">Type</span>
                    </th>
                    <th rowspan="2" class="daily_report_head daily_center" style="width: 160px;">
                        <span class="daily_report_label">Sub Type</span>
                    </th>
                    <th colspan="2" class="daily_report_head daily_center">
                        <span class="daily_report_label">Work Progress</span>
                    </th>
                    <th colspan="3" class="daily_report_head daily_center">
                        <span class="daily_report_label">Manpower</span>
                    </th>
                    <th colspan="3" class="daily_report_head daily_center">
                        <span class="daily_report_label"></span>
                    </th>
                </tr>
                <tr>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Work Execute (smt/Rmt/Cmt)</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Material Consumption</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Male</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Female</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Total</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Machinary</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Total Machinary</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label"><i class="fa fa-cog"></i></span>
                    </th>
                </tr>
            </thead>
            <tbody class="dpr_body">
                <?php echo pur_html_entity_decode($dpr_row_template); ?>
            </tbody>
        </table>
        <div id="removed-items"></div>
    </div>
</div>

<script type="text/javascript">
    get_selected_project();
    $(document).on('change', "select[name='project_id']", function(event) {
        get_selected_project();
    });

    function get_selected_project() {
        var selectedText = $("select[name='project_id']").find("option:selected").text();
        $('.view_project_name').html(selectedText);
    }

    $(document).on('click', '.dpr-add-item-to-table', function(event) {
        "use strict";

        var data = 'undefined';
        data = typeof(data) == 'undefined' || data == 'undefined' ? dpr_get_item_preview_values() : data;
        var table_row = '';
        var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('.dpr-items-table tbody .item').length + 1;
        lastAddedItemKey = item_key;

        dpr_get_item_row_template('newitems[' + item_key + ']', data.location, data.agency, data.type, data.sub_type, data.work_execute, data.material_consumption, data.male, data.female, data.total, data.machinery, data.total_machinery, item_key).done(function(output) {
            table_row += output;

            $('.dpr_body').append(table_row);

            setTimeout(function() {
                dpr_calculate_total();
            }, 15);

            init_selectpicker();
            pur_clear_item_preview_values();
            $('body').find('#items-warning').remove();
            $("body").find('.dt-loader').remove();
            $('#item_select').selectpicker('val', '');

            return true;
        });
        return false;
    });

    function dpr_get_item_row_template(name, location, agency, type, sub_type, work_execute, material_consumption, male, female, total, machinery, total_machinery, item_key) {
        "use strict";

        jQuery.ajaxSetup({
            async: false
        });

        var d = $.post(admin_url + 'forms/get_dpr_row_template', {
            name: name,
            location: location,
            agency: agency,
            type: type,
            sub_type: sub_type,
            work_execute: work_execute,
            material_consumption: material_consumption,
            male: male,
            female: female,
            total: total,
            machinery: machinery,
            total_machinery: total_machinery,
            item_key: item_key
        });
        jQuery.ajaxSetup({
            async: true
        });
        return d;
    }

    function dpr_get_item_preview_values() {
        "use strict";

        var response = {};
        response.location = $('.dpr-items-table input[name="location"]').val();
        response.agency = $('.dpr-items-table select[name="agency"]').selectpicker('val');
        response.type = $('.dpr-items-table select[name="type"]').selectpicker('val');
        response.sub_type = $('.dpr-items-table select[name="sub_type"]').selectpicker('val');
        response.work_execute = $('.dpr-items-table input[name="work_execute"]').val();
        response.material_consumption = $('.dpr-items-table input[name="material_consumption"]').val();
        response.male = $('.dpr-items-table input[name="male"]').val();
        response.female = $('.dpr-items-table input[name="female"]').val();
        response.total = $('.dpr-items-table input[name="total"]').val();
        response.machinery = $('.dpr-items-table select[name="machinery"]').val();
        response.total_machinery = $('.dpr-items-table input[name="total_machinery"]').val();

        return response;
    }

    function pur_clear_item_preview_values() {
        "use strict";

        var previewArea = $('.dpr_body .main');
        previewArea.find('input').val('');
        previewArea.find('textarea').val('');
        previewArea.find('select').val('').selectpicker('refresh');
    }

    function dpr_calculate_total() {
        "use strict";
        var rows = $('.dpr_body tr.item');

        $.each(rows, function() {
            var male = parseFloat($(this).find('td.male input').val()) || 0;
            var female = parseFloat($(this).find('td.female input').val()) || 0;
            var total = male + female;
            $(this).find('td.total input').val(total);
        });
    }

    function dpr_delete_item(row, itemid, parent) {
        "use strict";

        $(row).parents('tr').addClass('animated fadeOut', function() {
            setTimeout(function() {
                $(row).parents('tr').remove();
                dpr_calculate_total();
            }, 50);
        });
        if (itemid && $('input[name="isedit"]').length > 0) {
            $(parent + ' #removed-items').append(hidden_input('removed_items[]', itemid));
        }
    }
</script>