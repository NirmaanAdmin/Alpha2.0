<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (changee_row_changee_options_exist('"item_by_vendor"') == 0){
		  $CI->db->query('INSERT INTO `tblchangee_option` (`option_name`, `option_val`, `auto`) VALUES ("item_by_vendor", "0", "1");
		');
		}
    }
}