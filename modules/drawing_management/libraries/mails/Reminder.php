<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reminder extends App_mail_template
{
    protected $for = 'staff';

    protected $drawing_management;

    public $slug = 'reminder';

    public function __construct($drawing_management)
    {
        parent::__construct();

        $this->drawing_management = $drawing_management;
        // For SMS and merge fields for email
        $this->set_merge_fields('reminder_merge_fields', $this->drawing_management);
    }

    public function build()
    {
        $this->to($this->drawing_management->email);
    }
    
}
