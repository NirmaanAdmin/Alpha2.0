<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        //Ver 1.1.4

		create_email_template('Changee Quotation', '<span style=\"font-size: 12pt;\"> Hello !. </span><br /><br /><span style=\"font-size: 12pt;\"> We would like to share with you a link of Changee Quotation information with the number {pq_number} </span><br /><br /><span style=\"font-size: 12pt;\"><br />Please click on the link to view information: {quotation_link}<br/ >{additional_content}
  </span><br /><br />', 'changee_order', 'Changee Quotation (Sent to contact)', 'changee-quotation-to-contact');
    }
}