<?php defined('BASEPATH') or exit('No direct script access allowed');

// Theese lines should aways at the end of the document left side. Dont indent these lines
$html = <<<EOF
<div>
$export_employee
</div>
EOF;
$pdf->SetAutoPageBreak(false, 0);
$pdf->writeHTML($html, true, false, true, false, '');
