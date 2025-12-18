<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_pdf.php');

class Invoiceform_pdf extends App_pdf
{
    protected $invoice_data;
    protected $ci;

    public function __construct($invoice_data)
    {
        parent::__construct();

        $this->ci = &get_instance();
        $this->invoice_data = $invoice_data;

        $this->SetTitle('Invoice Report');
    }

    public function prepare()
    {
        // FORCE LANDSCAPE
        $this->setPageFormat('A4', 'L');
        $this->SetPageOrientation('L');

        // ENABLE AUTO PAGE BREAK
        // $this->SetAutoPageBreak(true, 20);

        // ADD FIRST PAGE (important for auto break)
        // $this->AddPage('L');

        $this->set_view_vars([
            'invoice_data' => $this->invoice_data,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'Invoice Report';
    }

    protected function file_path()
    {
        return APPPATH . 'views/themes/' . active_clients_theme() . '/views/report_pdf/invoiceformpdf.php';
    }
}
