<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Changee_send_rejected extends App_mail_template
{

    protected $data;

    public $slug = 'changee-send-rejected';

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;
        // For SMS and merge fields for email
        $this->set_merge_fields('changee_approve_merge_fields', $this->data);
    }
    public function build()
    {
        $this->to($this->data->mail_to);
    }
}
