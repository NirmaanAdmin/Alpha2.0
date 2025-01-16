<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once APPPATH . 'libraries/pdf/App_pdf.php';

/**
 * Export_employee PDF Generator
 */
class Export_employee_pdf extends App_pdf {
    /**
     * @var object Employee data to export
     */
    protected $export_employee;

    /**
     * Constructor
     * 
     * @param object $export_employee Data to be used for PDF generation
     */
    public function __construct($export_employee) {
        // Apply filters to modify or validate the data
        $export_employee = hooks()->apply_filters('request_html_pdf_data', $export_employee);

        // Make data available globally if needed
        $GLOBALS['export_employee_pdf'] = $export_employee;

        // Initialize parent class
        parent::__construct();

        $this->export_employee = $export_employee;

        // Set the PDF title
        $this->SetTitle('export_employee');

        // Ensure the data is formatted for the editor
        $this->export_employee = $this->fix_editor_html($this->export_employee);
    }

    /**
     * Prepare the PDF content
     * 
     * @return string PDF content
     */
    public function prepare() {
        // Set variables for the PDF view
        $this->set_view_vars('export_employee', $this->export_employee);

        // Build the PDF and return content
        return $this->build();
    }

    /**
     * Get the type of the PDF
     * 
     * @return string PDF type identifier
     */
    protected function type() {
        return 'export_employee';
    }

    /**
     * Determine the file path for the PDF template
     * 
     * @return string Path to the PDF template
     */
    protected function file_path() {
        // Define custom and default paths
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $defaultPath = APP_MODULES_PATH . '/hr_payroll/views/employee_payslip/export_employee_pdf.php';

        // Use custom path if it exists
        if (file_exists($customPath)) {
            return $customPath;
        }

        return $defaultPath;
    }
}