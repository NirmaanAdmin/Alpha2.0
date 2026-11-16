<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Pur_order_pdf extends App_pdf
{
    protected $pur_order;
    protected $footer_text;

    public function __construct($pur_order, $footer_text = '')
    {
        $pur_order                = hooks()->apply_filters('request_html_pdf_data', $pur_order);
        $GLOBALS['pur_order_pdf'] = $pur_order;
        parent::__construct();
        
        $this->footer_text = $footer_text;
        $this->pur_order = $pur_order;
        
        $this->SetTitle(_l('pur_order'));
        # Don't remove these lines - important for the PDF layout
        $this->pur_order = $this->fix_editor_html($this->pur_order);
    }

    // Override the Footer method from TCPDF or FPDI
    public function Footer()
    {
        // Trigger the custom hook for the footer content
        hooks()->do_action('pdf_footer', ['pdf_instance' => $this, 'type' => $this->type]);
       
        $this->SetY(-20); // 15mm from the bottom
        $this->SetX(-15); // 15mm from the bottom
        $this->SetFont($this->get_font_name(), 'I', 8);
        $this->SetTextColor(142, 142, 142);
        $this->Cell(0, 15, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

        if($this->footer_text !== '') {
            // Set default footer position and font (if additional styling needed)
            $this->SetX(15); // 15mm from the bottom
            $this->SetY(-15); // 15mm from the bottom
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, $this->footer_text, 0, 0, 'L');
        }
    }


    public function prepare()
    {
        $this->set_view_vars('pur_order', $this->pur_order);

        return $this->build();
    }

    protected function type()
    {
        return 'pur_order';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/changee/views/changee_order/pur_orderpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}