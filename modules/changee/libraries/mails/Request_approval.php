<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Request_approval extends App_mail_template
{

    protected $data;

    public $slug = 'changee-request-approval';

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;
        // For SMS and merge fields for email
        $this->set_merge_fields('changee_request_approval_merge_fields', $this->data);
    }
    public function build()
    {
        $this->to($this->data->mail_to);
    }
}
