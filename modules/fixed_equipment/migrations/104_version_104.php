<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
	public function up()
	{    
		$CI = &get_instance();
		if (!$CI->db->table_exists(db_prefix() . 'fe_depreciation_items')) {
			$CI->db->query('CREATE TABLE `' . db_prefix() .'fe_depreciation_items` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`item_id` INT(11) NOT NULL,
				`value` DECIMAL(15,2) NOT NULL DEFAULT 0,
				`date` datetime NULL,
				PRIMARY KEY (`id`));');
		}

		if (!$CI->db->table_exists(db_prefix() . 'fe_cron_log')) {
			$CI->db->query('CREATE TABLE `' . db_prefix() . "fe_cron_log` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`date` datetime NULL,
				`cron_name` varchar(200) NUll,
				`rel_id` int(11) NULL,
				`rel_type` varchar(45) NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
		}

		if ($CI->db->field_exists('value', db_prefix() . 'fe_depreciation_items')) {
			$CI->db->query('ALTER TABLE `' . db_prefix() . 'fe_depreciation_items`
			MODIFY `value` float NULL'
			);
		}
	}
}


