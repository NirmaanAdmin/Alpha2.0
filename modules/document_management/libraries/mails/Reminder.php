<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reminder extends App_mail_template
{
    protected $for = 'staff';

    protected $document_management;

    public $slug = 'reminder';

    public function __construct($document_management)
    {
        parent::__construct();

        $this->document_management = $document_management;
        // For SMS and merge fields for email
        $this->set_merge_fields('reminder_merge_fields', $this->document_management);
    }

    public function build()
    {
        $this->to($this->document_management->email);
    }
    
}
