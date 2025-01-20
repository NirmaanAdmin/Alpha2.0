<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$hrp_payslip_salary_allowance = hrp_payslip_json_data_decode($payslip_detail['json_data'], $payslip);
$hrp_cl_leaves = get_staff_leaves($employee['staff_id'], 'casual-leave-cl', $payslip_detail['month']);
$hrp_sl_leaves = get_staff_leaves($employee['staff_id'], 'sick_leave', $payslip_detail['month']);
$hrp_rl_leaves = get_staff_leaves($employee['staff_id'], 'religiuos-leave-l', $payslip_detail['month']);
$hrp_lwp_leaves = get_staff_leaves($employee['staff_id'], 'private_work_without_pay', $payslip_detail['month']);
$hrp_coff_leaves = get_staff_leaves($employee['staff_id'], 'compensatory-leave-comp-off', $payslip_detail['month']);
// Extract total salary and allowance
$total_formal_salary = $hrp_payslip_salary_allowance['formal_salary'] ?? 0;
$total_formal_allowance = $hrp_payslip_salary_allowance['formal_allowance'] ?? 0;
$total_formal_contract = $total_formal_salary + $total_formal_allowance;


$formal_contract = isset($hrp_payslip_salary_allowance['formal_contract_list']) ? $hrp_payslip_salary_allowance['formal_contract_list'] : '';

$formal_rows = explode('</tr>', $formal_contract);

$earnings_data = [
	['label' => _l('Gross pay'), 'value' => isset($payslip_detail) ? $payslip_detail['gross_pay'] : '0'],
	['label' => _l('Commission amount'), 'value' => isset($payslip_detail) ? $payslip_detail['commission_amount'] : '0'],
	['label' => _l('Bonus kpi'), 'value' => isset($payslip_detail) ? $payslip_detail['bonus_kpi'] : '0'],
	
];

$leave_data = [
	['label' => _l('sick_leaves'), 'value' => isset($hrp_sl_leaves) ? $hrp_sl_leaves : '0'],
	['label' => _l('casual_leave'), 'value' => isset($hrp_cl_leaves) ? $hrp_cl_leaves : '0'],
	['label' => _l('lwp'), 'value' => isset($hrp_lwp_leaves) ? $hrp_lwp_leaves + ((float)$get_data_for_month[3] - (float)$get_data_for_month[4] - (float)$hrp_cl_leaves - (float)$hrp_sl_leaves - (float)$hrp_coff_leaves) : '0'],
	['label' => _l('religiuos_leave'), 'value' => isset($hrp_rl_leaves) ? $hrp_rl_leaves : '0'],
	['label' => _l('comp_off'), 'value' => isset($hrp_coff_leaves) ? $hrp_coff_leaves : '0'],
];

// Fix malformed HTML by wrapping it in a parent element
$html = '<table>' . $formal_contract . '</table>';

// Load HTML using DOMDocument
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
$dom->loadHTML($html);
libxml_clear_errors();

// Initialize result array
$result = [];

// Create a DOMXPath object
$xpath = new DOMXPath($dom);

// Find all <tr> elements
$rows = $xpath->query('//tr');

foreach ($rows as $row) {
	// Get all <td> elements within the row
	$cells = $xpath->query('.//td', $row);
	if (count($cells) === 2) { // Ensure there are exactly 2 <td> elements
		$label = trim($cells[0]->nodeValue); // Get text from the first <td>
		$value = trim($cells[1]->nodeValue); // Get text from the second <td>
		if (!empty($label) || !empty($value)) {
			$numeric_value = (float)str_replace(',', '', $value); // Convert value to a float
			$total_actual_salary += $numeric_value; // Add to total
			$result[] = [
				'label' => $label,
				'value' => $value,
			];
		}
	}
}
$total_row = [
	'label' => _l('total'),
	'value' => $total_actual_salary,
];
// Dynamically insert $result before "Gross Pay" in $earnings_data
$gross_pay_index = array_search('Gross pay', array_column($earnings_data, 'label'));
$earnings_data = array_merge(
	array_slice($earnings_data, 0, $gross_pay_index), // Before "Gross Pay"
	$result, // Insert $formal_rows (already formatted)
	array_slice($earnings_data, $gross_pay_index) // After "Gross Pay"
);

?>

<table class="table">
	<tbody>
		<tr>
			<td width="15%" class="text_align_center candidate_name_widt_27">
				<?php echo pdf_logo_url(); ?>
			</td>
			<td width="85%" class="text_align_center logo_with"><?php echo format_organization_info() ?></td>
		</tr>
	</tbody>
</table>

<div class="text_align_center">
	<b>
		<h3 style="margin-bottom: 0% !important;"><?php echo _l('hrp_payslip_for') . ' ' . date('M-Y', strtotime($payslip_detail['month'])); ?> </h3>
	</b>
	<br>
	<p style="margin-top: 0% !important;font-size: 12px">Form IV B [ Rule 26(2) (b) ]</p>
</div>

<table border="1" class="width-100-height-55">
	<tbody>
		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('employee_name'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode($payslip_detail['employee_name']); ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('staff_code'); ?></strong></td>
			<td class="width-30-height-27"><?php echo $emp_code ?></td>
		</tr>

		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('job_title'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode(isset($employee['job_title']) ? $employee['job_title'] : '') ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('hrp_worked_day_new'); ?></strong></td>
			<td class="width-30-height-27"><?php echo (float)$get_data_for_month[3] ?></td>

		</tr>

		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('staff_departments'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode($list_department) ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('paid_days'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode($get_data_for_month[4]); ?></td>
		</tr>
		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('ps_pay_slip_number'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode($payslip_detail['pay_slip_number']); ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('unpaid_days'); ?></strong></td>
			<td class="width-30-height-27"><?php echo (float)$get_data_for_month[3] - (float)$get_data_for_month[4] - (float)$hrp_cl_leaves - (float)$hrp_sl_leaves - (float)$hrp_coff_leaves; ?></td>
		</tr>
		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('epf_no'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode($employee['epf_no']); ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('esi_no'); ?></strong></td>
			<td class="width-30-height-27"><?php echo $esi_no ?></td>
		</tr>
		<tr class="height-27">
			<td class="width-20-height-27 align_left"><strong><?php echo _l('doj'); ?></strong></td>
			<td class="width-30-height-27"><?php echo date('d M, Y', strtotime($employee['primary_effective'])); ?></td>
			<td class="width-20-height-27"><strong><?php echo _l('income_tax_number'); ?></strong></td>
			<td class="width-30-height-27"><?php echo new_html_entity_decode(isset($employee['income_tax_number']) ? $employee['income_tax_number'] : '') ?></td>

		</tr>

	</tbody>
</table>


<div class="row">
	<div class="col-md-6">
		<!-- <?php if ((float)($payslip_detail['actual_workday_probation']) > 0) { ?>
			<table class="table">
				<tbody>
					<tr>
						<th class=" thead-dark"><?php echo _l('hrp_probation_contract'); ?></th>
						<th class=" thead-dark"></th>
					</tr>

					<?php echo isset($hrp_payslip_salary_allowance['probation_contract_list']) ? $hrp_payslip_salary_allowance['probation_contract_list'] : '' ?>
				</tbody>
			</table>
		<?php } ?> -->

		<?php if ((float)($payslip_detail['actual_workday']) > 0) { ?>
			<table class="table">
				<tbody>

					<tr style="background-color:rgb(28, 26, 26);color: #ffffff;">
						<th style=" padding: 5px;"><strong><?php echo _l('Leave Details'); ?></strong></th>
						<th style=" padding: 5px; "><strong><?php echo _l('Amount'); ?></strong></th>
						<th style=" padding: 5px;"><strong><?php echo _l('Actual salary'); ?></strong></th>
						<th style=" padding: 5px; "><strong><?php echo _l('Amount'); ?></strong></th>
						<th style=" padding: 5px;"><strong><?php echo _l('Earnings'); ?></strong></th>
						<th style=" padding: 5px; "><strong><?php echo _l('Amount'); ?></strong></th>

					</tr>

					<!-- Table Body -->
					<?php
					$max_rows = max(count($result), count($earnings_data), count($leave_data));

					for ($i = 0; $i < $max_rows; $i++) {

						if (isset($earnings_data[$i]['label']) && $earnings_data[$i]['label'] === 'Gross pay') {
							echo '<tr>';
							echo '<td colspan="2"></td>';
							echo '<td colspan="2" style="height: 10px;">&nbsp;</td>';
							echo '</tr>';
						}

						echo '<tr>';

						if (isset($leave_data[$i])) {
							echo '<td>' . htmlspecialchars($leave_data[$i]['label']) . '</td>';
							echo '<td>' . $leave_data[$i]['value'] . '</td>';
						} else {
							echo '<td></td><td></td>';
						}

						if (isset($result[$i])) {
							echo '<td>' . htmlspecialchars($result[$i]['label']) . '</td>';
							echo '<td>₹' . number_format($result[$i]['value'], 2) . '</td>';
						} else {
							echo '<td></td><td></td>';
						}


						if (isset($earnings_data[$i])) {
							$label = $earnings_data[$i]['label'];
							$value = str_replace(',', '', $earnings_data[$i]['value']);

							if (in_array($label, ['Basic', 'HRA'])) {

								if (!empty($get_data_for_month[3]) && $get_data_for_month[3] > 0 && !empty($get_data_for_month[4])) {
									$per_day_value = ($value / (float)$get_data_for_month[3]) * (float)$get_data_for_month[4];
								} else {
									$per_day_value = 0; // Fallback value
								}

								echo '<td>' . htmlspecialchars($label) . '</td>';
								echo '<td>₹' . number_format($per_day_value, 2) . '</td>';
							} else {

								echo '<td>' . htmlspecialchars($label) . '</td>';
								echo '<td>₹' . number_format($earnings_data[$i]['value'], 2) . '</td>';
							}
						} else {
							echo '<td></td><td></td>';
						}

						echo '</tr>';
					}
					$er_total = isset($payslip_detail) ? $payslip_detail['gross_pay'] + $payslip_detail['commission_amount'] + $payslip_detail['bonus_kpi'] : '0';
					// Add Single Total Row for Both Actual Salary and Earnings
					echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
					echo '<td colspan="2"></td>'; // Empty cells for Leave Details
					echo '<td>' . htmlspecialchars($total_row['label']) . '</td>';
					echo '<td>₹' . number_format($total_row['value'], 2) . '</td>'; // Actual Salary Total
					echo '<td>' . htmlspecialchars($total_earnings_row['label']) . '</td>';
					echo '<td>₹' . number_format($er_total, 2) . '</td>'; // Earnings Total
					echo '</tr>';
					?>
				</tbody>
			</table>
			<table class="table">
				<tbody>
					<tr>
						<th class="thead-dark">Deductions</th>
						<th class="thead-dark">Amount</th>
					</tr>

					<?php echo isset($hrp_payslip_salary_allowance['formal_deduction_list']) ? $hrp_payslip_salary_allowance['formal_deduction_list'] : '' ?>


					<tr class="project-overview">
						<td width="30%"><?php echo _l('income_tax'); ?></td>
						<td class="text-left"><?php echo new_html_entity_decode(isset($payslip_detail) ? currency_converter_value($payslip_detail['income_tax_paye'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : ''); ?></td>
					</tr>
					<tr class="project-overview">
						<td><?php echo _l('hrp_insurrance'); ?></td>
						<td><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['total_insurance'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
					</tr>

					<!-- <tr class="project-overview">
					<td><?php echo _l('hrp_deduction_manage'); ?></td>
					<td><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['total_deductions'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
				</tr> -->
					<tr class="project-overview">
						<td class="bold"><?php echo _l('total'); ?></td>
						<td><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['income_tax_paye'] + $payslip_detail['total_insurance'] + $payslip_detail['total_deductions'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
					</tr>
					<tr class="project-overview">
						<td><?php echo _l('ps_net_pay'); ?></td>
						<td><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['net_pay'] - ($payslip_detail['income_tax_paye'] + $payslip_detail['total_insurance'] + $payslip_detail['total_deductions']), $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
					</tr>
				</tbody>
			</table>

		<?php } ?>
		<div class="col-md-12" style="text-align: center;">
			<h5>This is computer generated statement hence and does not require signature</h5>
		</div>
	</div>
</div>