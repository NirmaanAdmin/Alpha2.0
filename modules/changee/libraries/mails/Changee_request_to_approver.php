<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Changee_request_to_approver extends App_mail_template
{
    protected $for = 'changee';

    protected $data;

    public $slug = 'changee-request-to-approver';

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;
        $this->set_merge_fields('changee_request_to_approver_merge_fields', $this->data);
    }
    public function build()
    {
        $this->to($this->data->mail_to);
    }
}

?>