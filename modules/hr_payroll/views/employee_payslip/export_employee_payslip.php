<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

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
			<td class="width-30-height-27"><?php echo app_format_money((float)$get_data_for_month[3], '') ?></td>

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
			<td class="width-30-height-27"><?php echo app_format_money((float)$get_data_for_month[3] - (float)$get_data_for_month[4], ''); ?></td>
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

<?php
$hrp_payslip_salary_allowance = hrp_payslip_json_data_decode($payslip_detail['json_data'], $payslip);
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
	['label' => _l('Total'), 'value' => isset($payslip_detail) ? $payslip_detail['gross_pay'] + $payslip_detail['commission_amount'] + $payslip_detail['bonus_kpi'] : '0'],
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

// Find all <tr> elements
$rows = $dom->getElementsByTagName('tr');

foreach ($rows as $row) {
	// Get all <td> elements within the row
	$cells = $row->getElementsByTagName('td');
	if ($cells->length === 2) { // Ensure there are exactly 2 <td> elements
		$label = trim($cells->item(0)->nodeValue); // Get text from the first <td>
		$value = trim($cells->item(1)->nodeValue); // Get text from the second <td>
		if (!empty($label) || !empty($value)) {
			$result[] = [
				'label' => $label,
				'value' => $value,
			];
		}
	}
}

// Dynamically insert $result before "Gross Pay" in $earnings_data
$gross_pay_index = array_search('Gross pay', array_column($earnings_data, 'label'));
$earnings_data = array_merge(
	array_slice($earnings_data, 0, $gross_pay_index), // Before "Gross Pay"
	$result, // Insert $formal_rows (already formatted)
	array_slice($earnings_data, $gross_pay_index) // After "Gross Pay"
);

?>

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
					<!-- Table Header -->
					<tr style="background-color:rgb(28, 26, 26);color: #ffffff;">
						<th style=" padding: 5px;"><strong><?php echo _l('Actual salary'); ?></strong></th>
						<th style=" padding: 5px; "><strong><?php echo _l('Amount'); ?></strong></th>
						<th style=" padding: 5px;"><strong><?php echo _l('Earnings'); ?></strong></th>
						<th style=" padding: 5px; "><strong><?php echo _l('Amount'); ?></strong></th>
					</tr>

					<!-- Table Body -->
					<?php


					$max_rows = max(count($result), count($earnings_data)); // Use $result instead of $formal_rows after parsing

					for ($i = 0; $i < $max_rows; $i++) {
						// Add space before "Gross pay"
						if (isset($earnings_data[$i]['label']) && $earnings_data[$i]['label'] === 'Gross pay') {
							echo '<tr>';
							echo '<td colspan="2"></td>'; // Maintain left column structure
							echo '<td colspan="2" style="height: 10px;">&nbsp;</td>'; // Add space to the right side only
							echo '</tr>';
						}
					
						echo '<tr>';
					
						// Left Side (Formal Salary Rows)
						if (isset($result[$i])) { // Use $result for parsed formal rows
							echo '<td>' . htmlspecialchars($result[$i]['label']) . '</td>';
							echo '<td>₹' . number_format($result[$i]['value'],2) . '</td>';
						} else {
							echo '<td></td><td></td>'; // Empty cells if no formal row exists
						}
					
						// Right Side (Earnings)
						if (isset($earnings_data[$i])) {
							$label = $earnings_data[$i]['label'];
							$value = (float)str_replace(',', '', $earnings_data[$i]['value']); // Clean and cast value
							
							if (in_array($label, ['Basic', 'HRA'])) {
								// Calculate per-day value for "Basic" and "HRA"
								$per_day_value = ($value / (float)$get_data_for_month[3]) * (float)$get_data_for_month[4];
								echo '<td>' . htmlspecialchars($label) . '</td>';
								echo '<td>₹' . number_format($per_day_value, 2) . '</td>';
							} else {
								// Show other labels as-is
								echo '<td>' . htmlspecialchars($label) . '</td>';
								echo '<td>₹' . number_format($earnings_data[$i]['value'],2) . '</td>';
							}
						} else {
							echo '<td></td><td></td>'; // Empty cells if no earnings row exists
						}
					
						echo '</tr>';
					}
					
					

					?>
				</tbody>
			</table>


			<!-- <table class="table" style="width: 48%;">
				<tbody>
					<tr>
						<th class=" thead-dark"><?php echo _l('Earnings'); ?></th>
						<th class=" thead-dark" style="text-align: right;"><?php echo _l('hrp_amount'); ?></th>
					</tr>
					<tr class="project-overview">
						<td width="30%"><?php echo _l('ps_gross_pay'); ?></td>
						<td style="text-align: right;"><?php echo new_html_entity_decode(isset($payslip_detail) ?  currency_converter_value($payslip_detail['gross_pay'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0); ?></td>
					</tr>
					<tr class="project-overview">
						<td><?php echo _l('commission_amount'); ?></td>
						<td style="text-align: right;"><?php echo (isset($payslip_detail) ? currency_converter_value($payslip_detail['commission_amount'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0); ?></td>
					</tr>

					<tr class="project-overview">
						<td><?php echo _l('ps_bonus_kpi'); ?></td>
						<td style="text-align: right;"><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['bonus_kpi'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
					</tr>
					<tr class="project-overview">
						<td class="bold"><?php echo _l('total'); ?></td>
						<td style="text-align: right;"><?php echo isset($payslip_detail) ? currency_converter_value($payslip_detail['gross_pay'] + $payslip_detail['commission_amount'] + $payslip_detail['bonus_kpi'], $payslip->to_currency_rate, $payslip->to_currency_name ?? '', true) : 0; ?></td>
					</tr>

				</tbody>
			</table> -->

		<?php } ?>


	</div>
</div>
<div class="row">
	<div class="col-md-6">

		<table class="table">
			<tbody>
				<tr>
					<th class="thead-dark">Deductions</th>
					<th class="thead-dark"></th>
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


	</div>

	

</div>