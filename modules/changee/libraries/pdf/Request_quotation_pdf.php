<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Request_quotation_pdf extends App_pdf
{
    protected $co_request;

    public function __construct($co_request)
    {
        $co_request                = hooks()->apply_filters('request_html_pdf_data', $co_request);
        $GLOBALS['co_request_pdf'] = $co_request;

        parent::__construct();

        $this->co_request = $co_request;

        $this->SetTitle('co_request');
        # Don't remove these lines - important for the PDF layout
        $this->co_request = $this->fix_editor_html($this->co_request);
    }

    public function prepare()
    {
        $this->set_view_vars('co_request', $this->co_request);

        return $this->build();
    }

    protected function type()
    {
        return 'co_request';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/changee/views/changee_request/request_quotationpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}