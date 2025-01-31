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
</style>
<div class="col-md-12">
    <hr class="hr-panel-separator" />
</div>

<div class="col-md-12">
    <div class="table-responsive">
        <table class="table dpr-items-table items table-main-dpr-edit has-calculations no-mtop">

            <thead>
                <tr>
                    <th colspan="5" class="daily_report_title">Monthly First Aid Box Inspection</th>
                </tr>
                <tr>
                    <th colspan="2" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Checked by: <?php echo render_select('checked_by', get_staff_list(), array('staffid', 'name'), '', isset($mfa_form->checked_by) ? $mfa_form->checked_by : ''); ?></span>
                    </th>
                    <th colspan="3" class="daily_report_head">
                        <span class="daily_report_label">Date: </span><input type="datetime-local" class="form-control" name="date" value="<?= isset($mfa_form->date) ? date('Y-m-d\TH:i', strtotime($mfa_form->date)) : '' ?>">
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Designation: <?php echo render_input('designation', '', isset($mfa_form->designation) ? $mfa_form->designation : '', 'text', ['style' => 'width:150px;']); ?></span>
                    </th>
                    <th colspan="3" class="daily_report_head">
                        <span class="daily_report_label" style="display: ruby;">Location: <?php echo render_input('location', '', isset($mfa_form->location) ? $mfa_form->location : '', 'text', ['style' => 'width:150px;']); ?></span>
                    </th>
                </tr>

                <tr class="main">
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">S.No.</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Contents</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Required Amount</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Available Amount</span>
                    </th>
                    <th class="daily_report_head daily_center">
                        <span class="daily_report_label">Remarks</span>
                    </th>
                </tr>
            </thead>
            
            <?php $get_items_required_amount_mfa = get_items_required_amount_mfa(); ?>
            <tbody>
                
                <?php $sr = 1;
                
                foreach ($form_items as $key => $value): 
                $id = isset($mfa_form_detail) ? $mfa_form_detail[$key]['id'] : '';
                ?>
                    <tr class="main">
                        <input type="hidden" class="ids" name="items[<?= $sr ?>][id]" value="<?= $id  ?>">
                        <td><?= $sr ?></td>
                        <td style="font-weight: 600;font-size: 16px;"><?= $value['name'] ?></td>
                        <td style="font-weight: 600;font-size: 16px;"><?= $get_items_required_amount_mfa[$key]['name'] ?></td>
                        <td>
                            <?php if ($sr == 1): ?>
                                <div style="display: flex; gap: 5px;">
                                    <?php
                                    echo render_input('items[' . $sr . '][small]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['small'] : '', 'text', ['style' => 'width:50px;', 'placeholder' => 'S'], [], '', 'number');
                                    echo '/';
                                    echo render_input('items[' . $sr . '][medium]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['medium'] : '', 'text', ['style' => 'width:50px;', 'placeholder' => 'M'], [], '', 'number');
                                    echo '/';
                                    echo render_input('items[' . $sr . '][large]',  '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['large'] : '', 'text', ['style' => 'width:50px;', 'placeholder' => 'L'], [], '', 'number');
                                    ?>
                                </div>
                            <?php elseif ($sr == 3):  ?>
                                <div style="display: flex; gap: 5px;">
                                    <?php
                                    echo render_input('items[' . $sr . '][10cm]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['10cm'] : '', 'text', ['style' => 'width:50px;', 'placeholder' => 'cm'], [], '', 'number');
                                    echo '/';
                                    echo render_input('items[' . $sr . '][5cm]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['5cm'] : '', 'text', ['style' => 'width:50px;', 'placeholder' => 'cm'], [], '', 'number');
                                    ?>
                                </div>
                            <?php else: ?>
                                <span class="daily_report_label" style="display: ruby;">
                                    <?php echo render_input('items[' . $sr . '][available_amount]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['available_amount'] : '', 'text', ['style' => 'width:150px;'], [], '', 'number'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="daily_report_label" style="display: ruby;">
                                <?php echo render_textarea('items[' . $sr . '][remarks]', '', isset($mfa_form_detail) ? $mfa_form_detail[$key]['remarks'] : '',  ['style' => 'width:150px;height:35px']); ?>
                            </span>
                        </td>
                    </tr>
                <?php $sr++;
                endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    $('#project_id').on('change', function() {
        // var project_id = $(this).val();
        var project_name = $('#project_id option:selected').text();
        $('.view_project_name').html(project_name);
    });


    $(document).ready(function() {
        $('input.number').keypress(function(e) {
            var code = e.which || e.keyCode;

            // Allow backspace, tab, delete, and '/'
            if (code === 8 || code === 9 || code === 46 || code === 47) {
                return true;
            }

            // Allow letters (A-Z, a-z) and numbers (0-9)
            if (
                (code >= 48 && code <= 57) || // Numbers 0-9
                (code >= 65 && code <= 90) || // Uppercase A-Z
                (code >= 97 && code <= 122) // Lowercase a-z
            ) {
                return true;
            }

            // Block all other characters
            return false;
        });
    });
</script>