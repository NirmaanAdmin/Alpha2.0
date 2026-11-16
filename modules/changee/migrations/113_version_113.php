<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        //Ver 1.1.3
		create_email_template('Changee Request', '<span style=\"font-size: 12pt;\"> Hello !. </span><br /><br /><span style=\"font-size: 12pt;\"> We would like to share with you a link of Changee Request information with the number {pr_number} </span><br /><br /><span style=\"font-size: 12pt;\"><br />Please click on the link to view information: {public_link}<br/ >{additional_content}
  </span><br /><br />', 'changee_order', 'Changee Request (Sent to contact)', 'changee-request-to-contact');

    }
}