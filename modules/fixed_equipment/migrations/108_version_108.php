<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
	public function up()
	{    
		$CI = &get_instance();
		if (!$CI->db->table_exists(db_prefix() . 'fe_assign_asset_predefined_kits')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "fe_assign_asset_predefined_kits` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(150) NULL,
                `assign_data` text NULL DEFAULT '',
                `parent_id` int(11) NULL,
                `datecreated` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }
        
        
        if (!$CI->db->field_exists('project_id' ,db_prefix() . 'fe_checkin_assets')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_checkin_assets`
            ADD COLUMN `project_id` int(11) NULL
            ');
        }
	}
}


